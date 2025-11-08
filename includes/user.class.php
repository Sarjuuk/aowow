<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class User
{
    public static  int    $id         = 0;
    public static  string $username   = '';
    public static  int    $banStatus  = 0x0;                // see ACC_BAN_* defines
    public static  int    $status     = 0x0;
    public static  int    $groups     = 0x0;
    public static  int    $perms      = 0;
    public static ?string $email      = null;
    public static  int    $dailyVotes = 0;
    public static  bool   $debug      = false;              // show ids in lists (used to be debug, is now user setting)
    public static ?string $ip         = null;
    public static ?string $agent      = null;
    public static  Locale $preferedLoc;

    private static  int              $reputation    = 0;
    private static  string           $dataKey       = '';
    private static  int              $excludeGroups = 1;
    private static  int              $avatarborder  = 2;    // 2 is default / reputation colored
    private static ?LocalProfileList $profiles      = null;

    public static function init()
    {
        # set ip #

        $ipAddr = '';
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $env)
        {
            if ($rawIp = getenv($env))
            {
                if ($env == 'HTTP_X_FORWARDED')
                    $rawIp = explode(',', $rawIp)[0];       // [ip, proxy1, proxy2]

                if ($ipAddr = filter_var($rawIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
                    break;

                if ($ipAddr = filter_var($rawIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
                    break;
            }
        }

        self::$ip = $ipAddr ?: null;


        # set locale #

        if (isset($_SESSION['locale']) && $_SESSION['locale'] instanceof Locale)
            self::$preferedLoc = $_SESSION['locale']->validate() ?? Locale::getFallback();
        else if (!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && ($loc = Locale::tryFromHttpAcceptLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"])))
            self::$preferedLoc = $loc;
        else
            self::$preferedLoc = Locale::getFallback();


        # set basic data #

        if (empty($_SESSION['dataKey']))                    // session have a dataKey to access the JScripts (yes, also the anons)
            $_SESSION['dataKey'] = Util::createHash();      // just some random numbers for identification purpose

        self::$dataKey = $_SESSION['dataKey'];
        self::$agent   = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (!self::$ip)
            return false;


        # check IP bans #

        if ($ipBan = DB::Aowow()->selectRow('SELECT `count`, IF(`unbanDate` > UNIX_TIMESTAMP(), 1, 0) AS "active" FROM ?_account_bannedips WHERE `ip` = ? AND `type` = ?d', self::$ip, IP_BAN_TYPE_LOGIN_ATTEMPT))
        {
            if ($ipBan['count'] > Cfg::get('ACC_FAILED_AUTH_COUNT') && $ipBan['active'])
                return false;
            else if (!$ipBan['active'])
                DB::Aowow()->query('DELETE FROM ?_account_bannedips WHERE `ip` = ?', self::$ip);
        }


        # try to restore session #

        if (empty($_SESSION['user']))
            return false;

        $session  = DB::Aowow()->selectRow('SELECT `userId`, `expires` FROM ?_account_sessions WHERE `status` = ?d AND `sessionId` = ?', SESSION_ACTIVE, session_id());
        $userData = DB::Aowow()->selectRow(
           'SELECT    a.`id`, a.`passHash`, a.`username`, a.`locale`, a.`userGroups`, a.`userPerms`, BIT_OR(ab.`typeMask`) AS "bans", IFNULL(SUM(r.`amount`), 0) AS "reputation", a.`dailyVotes`, a.`excludeGroups`, a.`status`, a.`statusTimer`, a.`email`, a.`debug`, a.`avatar`, a.`avatarborder`
            FROM      ?_account a
            LEFT JOIN ?_account_banned ab    ON a.`id` = ab.`userId` AND ab.`end` > UNIX_TIMESTAMP()
            LEFT JOIN ?_account_reputation r ON a.`id` =  r.`userId`
            WHERE     a.`id` = ?d
            GROUP BY  a.`id`',
            $_SESSION['user']
        );

        if (!$session || !$userData)
        {
            self::destroy();
            return false;
        }
        else if ($session['expires'] && $session['expires'] < time())
        {
            DB::Aowow()->query('UPDATE ?_account_sessions SET `touched` = ?d, `status` = ?d WHERE `sessionId` = ?', time(), SESSION_EXPIRED, session_id());
            self::destroy();
            return false;
        }
        else if ($session['userId'] != $userData['id'])        // what in the name of fuck..?
        {
            // Don't know why, don't know how .. doesn't matter, both parties are out.
            DB::Aowow()->query('UPDATE ?_account_sessions SET `touched` = ?d, `status` = ?d WHERE `userId` IN (?a) AND `status` = ?d', time(), SESSION_FORCED_LOGOUT, [$userData['id'], $session['userId']], SESSION_ACTIVE);
            trigger_error('User::init - tried to resume session "'.session_id().'" of user #'.$_SESSION['user'].' linked to session data for user #'.$session['userId'].' Kicked both!', E_USER_ERROR);
            self::destroy();
            return false;
        }

        DB::Aowow()->query('UPDATE ?_account_sessions SET `touched` = ?d, `expires` = IF(`expires`, ?d, 0) WHERE `sessionId` = ?', time(), time() + Cfg::get('SESSION_TIMEOUT_DELAY'), session_id());

        if ($loc = Locale::tryFrom($userData['locale']))
            self::$preferedLoc = $loc;

        // reset expired account statuses
        if ($userData['statusTimer'] && $userData['statusTimer'] < time() && $userData['status'] != ACC_STATUS_NEW)
        {
            DB::Aowow()->query('UPDATE ?_account SET `status` = ?d, `statusTimer` = 0, `token` = "", `updateValue` = "" WHERE `id` = ?d', ACC_STATUS_NONE, User::$id);
            $userData['status'] = ACC_STATUS_NONE;
        }


        /*******************************/
        /* past here we are logged in */
        /*******************************/

        self::$id            = intVal($userData['id']);
        self::$username      = $userData['username'];
        self::$reputation    = $userData['reputation'];
        self::$banStatus     = $userData['bans'];
        self::$groups        = self::isBanned() ? 0 : intval($userData['userGroups']);
        self::$perms         = self::isBanned() ? 0 : intval($userData['userPerms']);
        self::$dailyVotes    = $userData['dailyVotes'];
        self::$excludeGroups = $userData['excludeGroups'];
        self::$status        = $userData['status'];
        self::$debug         = $userData['debug'];
        self::$email         = $userData['email'];
        self::$avatarborder  = $userData['avatarborder'];


        # reset premium options #

        if (!self::isPremium())
        {
            if ($userData['avatar'] == 2)
            {
                DB::Aowow()->query('UPDATE ?_account SET `avatar` = 1 WHERE `id` = ?d', self::$id);
                DB::Aowow()->query('UPDATE ?_account_avatars SET `current` = 0 WHERE `userId` = ?d', self::$id);
            }

            // avatar borders
            // do not reset, it's just not sent to the browser
        }


        # update daily limits #

        if (!self::isBanned())
        {
            $lastLogin = DB::Aowow()->selectCell('SELECT `curLogin` FROM ?_account WHERE `id` = ?d', self::$id);
            // either the day changed or the last visit was >24h ago
            if (date('j', $lastLogin) != date('j') || (time() - $lastLogin) > 1 * DAY)
            {
                // - daily votes (we need to reset this one)
                self::$dailyVotes = self::getMaxDailyVotes();

                DB::Aowow()->query(
                   'UPDATE  ?_account
                    SET     `dailyVotes` = ?d, `prevLogin` = `curLogin`, `curLogin` = UNIX_TIMESTAMP(), `prevIP` = `curIP`, `curIP` = ?
                    WHERE   `id` = ?d',
                    self::$dailyVotes,
                    self::$ip,
                    self::$id
                );

                // - gain reputation for daily visit
                if (!(self::isBanned()) && !self::isInGroup(U_GROUP_PENDING))
                    Util::gainSiteReputation(self::$id, SITEREP_ACTION_DAILYVISIT);

                // - increment consecutive visits (next day or first of new month and not more than 48h)
                if ((date('j', $lastLogin) + 1 == date('j') || (date('j') == 1 && date('n', $lastLogin) != date('n'))) && (time() - $lastLogin) < 2 * DAY)
                    DB::Aowow()->query('UPDATE ?_account SET `consecutiveVisits` = `consecutiveVisits` + 1 WHERE `id` = ?d', self::$id);
                else
                    DB::Aowow()->query('UPDATE ?_account SET `consecutiveVisits` = 0 WHERE `id` = ?d', self::$id);
            }
        }

        return true;
    }

    public static function save(bool $toDB = false)
    {
        $_SESSION['user']    = self::$id;
        $_SESSION['locale']  = self::$preferedLoc;
        // $_SESSION['dataKey'] does not depend on user login status and is set in User::init()

        if (self::isLoggedIn() && $toDB)
            DB::Aowow()->query('UPDATE ?_account SET `locale` = ? WHERE `id` = ?', self::$preferedLoc->value, self::$id);
    }

    public static function destroy()
    {
        session_regenerate_id(true);                        // session itself is not destroyed; status changed => regenerate id
        session_unset();

        $_SESSION['locale']  = self::$preferedLoc;          // keep locale
        $_SESSION['dataKey'] = self::$dataKey;              // keep dataKey

        self::$id       = 0;
        self::$username = '';
        self::$perms    = 0;
        self::$groups   = U_GROUP_NONE;
    }


    /*******************/
    /* auth mechanisms */
    /*******************/

    public static function authenticate(string $login, #[\SensitiveParameter] string $password) : int
    {
        $userId = 0;

        $result = match (Cfg::get('ACC_AUTH_MODE'))
        {
            AUTH_MODE_SELF     => self::authSelf($login, $password, $userId),
            AUTH_MODE_REALM    => self::authRealm($login, $password, $userId),
            AUTH_MODE_EXTERNAL => self::authExtern($login, $password, $userId),
            default            => AUTH_INTERNAL_ERR
        };

        // also banned? its a feature block, not login block..
        if ($result == AUTH_OK || $result == AUTH_BANNED)
        {
            session_unset();
            $_SESSION['user'] = $userId;
            self::$id = $userId;
        }

        return $result;
    }

    private static function authSelf(string $nameOrEmail, #[\SensitiveParameter] string $password, int &$userId) : int
    {
        if (!self::$ip)
            return AUTH_INTERNAL_ERR;

        // handle login try limitation
        $ipBan = DB::Aowow()->selectRow('SELECT `ip`, `count`, IF(`unbanDate` > UNIX_TIMESTAMP(), 1, 0) AS "active" FROM ?_account_bannedips WHERE `type` = ?d AND `ip` = ?', IP_BAN_TYPE_LOGIN_ATTEMPT, self::$ip);
        if (!$ipBan || !$ipBan['active'])                   // no entry exists or time expired; set count to 1
            DB::Aowow()->query('REPLACE INTO ?_account_bannedips (`ip`, `type`, `count`, `unbanDate`) VALUES (?, ?d, 1, UNIX_TIMESTAMP() + ?d)', self::$ip, IP_BAN_TYPE_LOGIN_ATTEMPT, Cfg::get('ACC_FAILED_AUTH_BLOCK'));
        else                                                // entry already exists; increment count
            DB::Aowow()->query('UPDATE ?_account_bannedips SET `count` = `count` + 1, `unbanDate` = UNIX_TIMESTAMP() + ?d WHERE `ip` = ?', Cfg::get('ACC_FAILED_AUTH_BLOCK'), self::$ip);

        if ($ipBan && $ipBan['count'] >= Cfg::get('ACC_FAILED_AUTH_COUNT') && $ipBan['active'])
            return AUTH_IPBANNED;

        $email = filter_var($nameOrEmail, FILTER_VALIDATE_EMAIL);

        $query = DB::Aowow()->SelectRow(
           'SELECT    a.`id`, a.`passHash`, BIT_OR(ab.`typeMask`) AS "bans", a.`status`
            FROM      ?_account a
            LEFT JOIN ?_account_banned ab ON a.`id` = ab.`userId` AND ab.`end` > UNIX_TIMESTAMP()
            WHERE     { a.`email` = ? } { a.`login` = ? } AND `status` <> ?d
            GROUP BY  a.`id`',
             $email ?: DBSIMPLE_SKIP,
            !$email ? $nameOrEmail : DBSIMPLE_SKIP,
            ACC_STATUS_DELETED
        );

        if (!$query)
            return AUTH_WRONGUSER;

        if (!self::verifyCrypt($password, $query['passHash']))
            return AUTH_WRONGPASS;

        // successfull auth; clear bans for this IP
        DB::Aowow()->query('DELETE FROM ?_account_bannedips WHERE `type` = ?d AND `ip` = ?', IP_BAN_TYPE_LOGIN_ATTEMPT, self::$ip);

        if ($query['bans'] & (ACC_BAN_PERM | ACC_BAN_TEMP))
            return AUTH_BANNED;

        $userId = $query['id'];

        return AUTH_OK;
    }

    private static function authRealm(string $name, #[\SensitiveParameter] string $password, int &$userId) : int
    {
        if (!DB::isConnectable(DB_AUTH))
            return AUTH_INTERNAL_ERR;

        $wow = DB::Auth()->selectRow('SELECT a.id, a.salt, a.verifier, ab.active AS hasBan FROM account a LEFT JOIN account_banned ab ON ab.id = a.id AND active <> 0 WHERE username = ? LIMIT 1', $name);
        if (!$wow)
            return AUTH_WRONGUSER;

        if (!self::verifySRP6($name, $password, $wow['salt'], $wow['verifier']))
            return AUTH_WRONGPASS;

        if ($wow['hasBan'])
            return AUTH_BANNED;

        if ($_ = self::checkOrCreateInDB($wow['id'], $name))
            $userId = $_;
        else
            return AUTH_INTERNAL_ERR;

        return AUTH_OK;
    }

    private static function authExtern(string $nameOrEmail, #[\SensitiveParameter] string $password, int &$userId) : int
    {
        if (!file_exists('config/extAuth.php'))
        {
            trigger_error('User::authExtern - AUTH_MODE_EXTERNAL is selected but config/extAuth.php does not exist!', E_USER_ERROR);
            return AUTH_INTERNAL_ERR;
        }

        require 'config/extAuth.php';

        if (!function_exists('\extAuth'))
        {
            trigger_error('User::authExtern - AUTH_MODE_EXTERNAL is selected but function extAuth() is not defined!', E_USER_ERROR);
            return AUTH_INTERNAL_ERR;
        }

        $extGroup = -1;
        $extId    = 0;
        $result   = \extAuth($nameOrEmail, $password, $extId, $extGroup);

        // assert we don't have an email passed back from extAuth
        if (filter_var($nameOrEmail, FILTER_VALIDATE_EMAIL))
            return AUTH_WRONGUSER;

        if ($result == AUTH_OK && $extId)
        {
            if ($_ = self::checkOrCreateInDB($extId, $nameOrEmail, $extGroup))
                $userId = $_;
            else
                return AUTH_INTERNAL_ERR;
        }

        return $result;
    }

    // create a linked account for our settings if necessary
    private static function checkOrCreateInDB(int $extId, string $name, int $userGroup = -1) : int
    {
        if ($_ = DB::Aowow()->selectCell('SELECT `id` FROM ?_account WHERE `extId` = ?d', $extId))
        {
            if ($userGroup >= U_GROUP_NONE)
                DB::Aowow()->query('UPDATE ?_account SET `userGroups` = ?d WHERE `extId` = ?d', $userGroup, $extId);
            return $_;
        }

        $newId = DB::Aowow()->query('INSERT IGNORE INTO ?_account (`extId`, `passHash`, `username`, `joinDate`, `prevIP`, `prevLogin`, `locale`, `status`, `userGroups`) VALUES (?d, "", ?, UNIX_TIMESTAMP(), ?, UNIX_TIMESTAMP(), ?d, ?d, ?d)',
            $extId,
            $name,
            $_SERVER["REMOTE_ADDR"] ?? '',
            self::$preferedLoc->value,
            ACC_STATUS_NONE,
            $userGroup >= U_GROUP_NONE ? $userGroup : U_GROUP_NONE
        );

        if ($newId)
            Util::gainSiteReputation($newId, SITEREP_ACTION_REGISTER);

        return $newId ?: 0;
    }

    // crypt used by us
    public static function hashCrypt(#[\SensitiveParameter] string $pass) : string
    {
        return password_hash($pass, PASSWORD_BCRYPT, ['cost' => 15]);
    }

    public static function verifyCrypt(#[\SensitiveParameter] string $pass, string $hash) : bool
    {
        return password_verify($pass, $hash);
    }

    // SRP6 used by TC
    private static function verifySRP6(string $user, string $pass, string $salt, string $verifier) : bool
    {
        $g = gmp_init(7);
        $N = gmp_init('894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7', 16);
        $x = gmp_import(
            sha1($salt . sha1(strtoupper($user . ':' . $pass), TRUE), TRUE),
            1,
            GMP_LSW_FIRST
        );
        $v = gmp_powm($g, $x, $N);
        return ($verifier === str_pad(gmp_export($v, 1, GMP_LSW_FIRST), 32, chr(0), STR_PAD_RIGHT));
    }


    /*********************/
    /* access management */
    /*********************/

    public static function isInGroup(int $group) : bool
    {
        return $group == U_GROUP_NONE || (self::$groups & $group) != U_GROUP_NONE;
    }

    public static function canComment() : bool
    {
        if (!self::isLoggedIn() || self::isBanned(ACC_BAN_COMMENT))
            return false;

        return self::$perms || self::$reputation >= Cfg::get('REP_REQ_COMMENT');
    }

    public static function canReply() : bool
    {
        if (!self::isLoggedIn() || self::isBanned(ACC_BAN_COMMENT))
            return false;

        return self::$perms || self::$reputation >= Cfg::get('REP_REQ_REPLY');
    }

    public static function canUpvote() : bool
    {
        if (!self::isLoggedIn() || self::isBanned(ACC_BAN_COMMENT))
            return false;

        return self::$perms || (self::$reputation >= Cfg::get('REP_REQ_UPVOTE') && self::$dailyVotes > 0);
    }

    public static function canDownvote() : bool
    {
        if (!self::isLoggedIn() || self::isBanned(ACC_BAN_RATE))
            return false;

        return self::$perms || (self::$reputation >= Cfg::get('REP_REQ_DOWNVOTE') && self::$dailyVotes > 0);
    }

    public static function canSupervote() : bool
    {
        if (!self::isLoggedIn() || self::isBanned(ACC_BAN_RATE) || self::isInGroup(U_GROUP_PENDING))
            return false;

        return self::$reputation >= Cfg::get('REP_REQ_SUPERVOTE');
    }

    public static function canUploadScreenshot() : bool
    {
        if (!self::isLoggedIn() || self::isBanned(ACC_BAN_SCREENSHOT) || self::isInGroup(U_GROUP_PENDING))
            return false;

        return true;
    }

    public static function canWriteGuide() : bool
    {
        if (!self::isLoggedIn() || self::isBanned(ACC_BAN_GUIDE) || self::isInGroup(U_GROUP_PENDING))
            return false;

        return true;
    }

    public static function canSuggestVideo() : bool
    {
        if (!self::isLoggedIn() || self::isBanned(ACC_BAN_VIDEO) || self::isInGroup(U_GROUP_PENDING))
            return false;

        return true;
    }

    public static function isPremium() : bool
    {
        return !self::isBanned() && (self::isInGroup(U_GROUP_PREMIUM) || self::$reputation >= Cfg::get('REP_REQ_PREMIUM'));
    }

    public static function isLoggedIn() : bool
    {
        return self::$id > 0;                               // more checks? maybe check pending email verification here? (self::isInGroup(U_GROUP_PENDING))
    }

    public static function isBanned(int $addBanMask = 0x0) : bool
    {
        return self::$banStatus & (ACC_BAN_TEMP | ACC_BAN_PERM | $addBanMask);
    }

    public static function isRecovering() : bool
    {
        return self::$status != ACC_STATUS_NONE && self::$status != ACC_STATUS_NEW;
    }


    /**************/
    /* js-related */
    /**************/

    public static function decrementDailyVotes() : void
    {
        if (!self::isLoggedIn() || self::isBanned(ACC_BAN_RATE))
            return;

        self::$dailyVotes--;
        DB::Aowow()->query('UPDATE ?_account SET `dailyVotes` = ?d WHERE `id` = ?d', self::$dailyVotes, self::$id);
    }

    public static function getCurrentDailyVotes() : int
    {
        if (!self::isLoggedIn() || self::isBanned(ACC_BAN_RATE) || self::$dailyVotes < 0)
            return 0;

        return self::$dailyVotes;
    }

    public static function getMaxDailyVotes() : int
    {
        if (!self::isLoggedIn() || self::isBanned(ACC_BAN_RATE))
            return 0;

        $threshold = Cfg::get('REP_REQ_VOTEMORE_BASE');
        $extra     = Cfg::get('REP_REQ_VOTEMORE_ADD');
        $base      = Cfg::get('USER_MAX_VOTES');

        return $base + max(0, intVal((self::$reputation - $threshold + $extra) / $extra));
    }

    public static function getReputation() : int
    {
        if (!self::isLoggedIn() || self::$reputation < 0)
            return 0;

        return self::$reputation;
    }

    public static function getUserGlobal() : array
    {
        $gUser = array(
            'id'          => self::$id,
            'name'        => self::$username,
            'roles'       => self::$groups,
            'permissions' => self::$perms,
            'cookies'     => []
        );

        if (!self::isLoggedIn() || self::isBanned())
            return $gUser;

        $gUser['commentban']        = !self::canComment();
        $gUser['canUpvote']         = self::canUpvote();
        $gUser['canDownvote']       = self::canDownvote();
        $gUser['canPostReplies']    = self::canReply();
        $gUser['superCommentVotes'] = self::canSupervote();
        $gUser['downvoteRep']       = Cfg::get('REP_REQ_DOWNVOTE');
        $gUser['upvoteRep']         = Cfg::get('REP_REQ_UPVOTE');
        $gUser['characters']        = self::getCharacters();
        $gUser['completion']        = self::getCompletion();
        $gUser['excludegroups']     = self::$excludeGroups;

        if (self::$debug)
            $gUser['debug'] = true;                         // csv id-list output option on listviews

        if (self::isPremium())
        {
            $gUser['premium']  = 1;
            $gUser['settings'] = ['premiumborder' => self::$avatarborder];
        }
        else
            $gUser['settings'] = (new \StdClass);           // existence is checked in Profiler.js before g_user.excludegroups is applied; should this contain - "defaultModel":{"gender":2,"race":6} ?

        if ($_ = self::getProfilerExclusions())
            $gUser = array_merge($gUser, $_);

        if ($_ = self::getProfiles())
            $gUser['profiles'] = $_;

        if ($_ = self::getGuides())
            $gUser['guides'] = $_;

        if ($_ = self::getWeightScales())
            $gUser['weightscales'] = $_;

        if ($_ = self::getCookies())
            $gUser['cookies'] = $_;

        return $gUser;
    }

    public static function getWeightScales() : array
    {
        $result = [];

        if (!self::isLoggedIn() || self::isBanned())
            return $result;

        $res = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `name` FROM ?_account_weightscales WHERE `userId` = ?d', self::$id);
        if (!$res)
            return $result;

        $weights = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `field` AS ARRAY_KEY2, `val` FROM ?_account_weightscale_data WHERE `id` IN (?a)', array_keys($res));
        foreach ($weights as $id => $data)
            $result[] = array_merge(['name' => $res[$id], 'id' => $id], $data);

        return $result;
    }

    public static function getProfilerExclusions() : array
    {
        $result = [];

        if (!self::isLoggedIn() || self::isBanned())
            return $result;

        if (!Cfg::get('PROFILER_ENABLE'))
            return $result;

        $modes  = [1 => 'excludes', 2 => 'includes'];
        foreach ($modes as $mode => $field)
            if ($ex = DB::Aowow()->selectCol('SELECT `type` AS ARRAY_KEY, `typeId` AS ARRAY_KEY2, `typeId` FROM ?_account_excludes WHERE `mode` = ?d AND `userId` = ?d', $mode, self::$id))
                foreach ($ex as $type => $ids)
                    $result[$field][$type] = array_values($ids);

        return $result;
    }

    public static function getCharacters() : array
    {
        if (!self::loadProfiles())
            return [];

        return self::$profiles->getJSGlobals(PROFILEINFO_CHARACTER);
    }

    public static function getProfiles() : array
    {
        if (!self::loadProfiles())
            return [];

        return self::$profiles->getJSGlobals(PROFILEINFO_PROFILE);
    }

    public static function getPinnedCharacter() : array
    {
        if (!self::loadProfiles())
            return [];

        $realms = Profiler::getRealms();

        foreach (self::$profiles->iterate() as $id => $_)
            if (self::$profiles->getField('cuFlags') & PROFILER_CU_PINNED)
                if (isset($realms[self::$profiles->getField('realm')]))
                    return [
                        $id,
                        self::$profiles->getField('name'),
                        self::$profiles->getField('region') . '.' . Profiler::urlize($realms[self::$profiles->getField('realm')]['name'], true) . '.' . Profiler::urlize(self::$profiles->getField('name'), true, true)
                    ];

        return [];
    }

    public static function getGuides() : array
    {
        $result = [];

        if (!self::isLoggedIn() || self::isBanned(ACC_BAN_GUIDE))
            return $result;

        if ($guides = DB::Aowow()->select('SELECT `id`, `title`, `url` FROM ?_guides WHERE `userId` = ?d AND `status` <> ?d', self::$id, GuideMgr::STATUS_ARCHIVED))
        {
            // fix url
            array_walk($guides, fn(&$x) => $x['url'] = '?guide='.($x['url'] ?: $x['id']));
            $result = $guides;
        }

        return $result;
    }

    public static function getCookies() : array
    {
        if (!self::isLoggedIn())
            return [];

        return DB::Aowow()->selectCol('SELECT `name` AS ARRAY_KEY, `data` FROM ?_account_cookies WHERE `userId` = ?d', self::$id);
    }

    public static function getFavorites() : array
    {
        if (!self::isLoggedIn() || self::isBanned())
            return [];

        $res = DB::Aowow()->selectCol('SELECT `type` AS ARRAY_KEY, `typeId` AS ARRAY_KEY2, `typeId` FROM ?_account_favorites WHERE `userId` = ?d', self::$id);
        if (!$res)
            return [];

        $data = [];
        foreach ($res as $type => $ids)
        {
            $tc = Type::newList($type, [['id', array_values($ids)]]);
            if (!$tc || $tc->error)
                continue;

            $entities = [];
            foreach ($tc->iterate() as $id => $__)
                $entities[] = [$id, $tc->getField('name', true, true)];

            if ($entities)
                $data[] = ['id' => $type, 'entities' => $entities];
        }

        return $data;
    }

    public static function getCompletion() : array
    {
        if (!self::loadProfiles())
            return [];

        $ids = [];
        foreach (self::$profiles->iterate() as $_)
            if (!self::$profiles->isCustom())
                $ids[] = self::$profiles->id;

        if (!$ids)
            return [];

        $completion = [];

        $x = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `questId` AS ARRAY_KEY2, `questId` FROM ?_profiler_completion_quests WHERE `id` IN (?a)', $ids);
        $completion[Type::QUEST] = $x ? array_map(array_values(...), $x) : [];

        $x = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `achievementId` AS ARRAY_KEY2, `achievementId` FROM ?_profiler_completion_achievements WHERE `id` IN (?a)', $ids);
        $completion[Type::ACHIEVEMENT] = $x ? array_map(array_values(...), $x) : [];

        $x = DB::Aowow()->selectCol('SELECT `id` AS ARRAY_KEY, `titleId` AS ARRAY_KEY2, `titleId` FROM ?_profiler_completion_titles WHERE `id` IN (?a)', $ids);
        $completion[Type::TITLE] = $x ? array_map(array_values(...), $x) : [];

        $completion[Type::ITEM] = [];

        $spells = DB::Aowow()->select(
           'SELECT    pcs.`id` AS ARRAY_KEY, pcs.`spellId` AS ARRAY_KEY2, pcs.`spellId`, i.`id` AS "itemId"
            FROM      ?_spell s
            JOIN      ?_profiler_completion_spells pcs ON s.`id` = pcs.`spellId`
            LEFT JOIN ?_items i ON i.`spellId1` IN (?a) AND i.`spellId2` = pcs.`spellId`
            WHERE     s.`typeCat` IN (?a) AND pcs.`id` IN (?a)',
            LEARN_SPELLS, [-5, -6, 9, 11], $ids
        );

        if ($spells)
        {
            $completion[Type::SPELL] = array_map(fn($x) => array_column($x, 'spellId'), $spells);

            if ($recipes = array_map(fn($x) => array_filter(array_column($x, 'itemId')),  $spells))
                foreach ($ids as $id)                       // array_merge_recursive does not respect numeric keys
                    $completion[Type::ITEM][$id] = array_merge($completion[Type::ITEM][$id] ?? [], $recipes[$id] ?? []);
        }
        else
            $completion[Type::SPELL] = [];

        // init empty result sets
        foreach ($completion as &$c)
            foreach ($ids as $id)
                if (!isset($c[$id]))
                    $c[$id] = [];

        return $completion;
    }

    private static function loadProfiles() : bool
    {
        if (!Cfg::get('PROFILER_ENABLE'))
            return false;

        if (self::$profiles === null)
        {
            $ap = DB::Aowow()->selectCol('SELECT `profileId` FROM ?_account_profiles WHERE `accountId` = ?d', self::$id);

            // the old approach ['OR', ['user', self::$id], ['ap.accountId', self::$id]] caused keys to not get used
            $conditions = $ap ? [['OR', ['user', self::$id], ['id', $ap]]] : ['user', self::$id];
            if (!self::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
                $conditions[] = ['deleted', 0];

            self::$profiles = (new LocalProfileList($conditions));
        }

        return !!self::$profiles->getFoundIDs();
    }
}

?>
