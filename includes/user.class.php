<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class User
{
    public static int    $id           = 0;
    public static string $displayName  = '';
    public static int    $banStatus    = 0x0;               // see ACC_BAN_* defines
    public static int    $groups       = 0x0;
    public static int    $perms        = 0;
    public static string $avatar       = 'inv_misc_questionmark';
    public static int    $dailyVotes   = 0;
    public static $ip           = null;

    private static int    $reputation    = 0;
    private static string $dataKey       = '';
    private static bool   $expires       = false;
    private static string $passHash      = '';
    private static int    $excludeGroups = 1;

    public  static WoWLocale $preferedLoc;
    private static ?LocalProfileList $profiles = null;

    public static function init()
    {
        self::setIP();

        if (isset($_SESSION['locale']) && $_SESSION['locale'] instanceof WoWLocale)
            self::$preferedLoc = $_SESSION['locale']->validate() ?? WoWLocale::getFallback();
        else if (!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && ($loc = WoWLocale::tryFromHttpAcceptLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"])))
            self::$preferedLoc = $loc;
        else
            self::$preferedLoc = WoWLocale::getFallback();

        // session have a dataKey to access the JScripts (yes, also the anons)
        if (empty($_SESSION['dataKey']))
            $_SESSION['dataKey'] = Util::createHash();      // just some random numbers for identifictaion purpose

        self::$dataKey = $_SESSION['dataKey'];

        if (!self::$ip)
            return false;

        // check IP bans
        if ($ipBan = DB::Aowow()->selectRow('SELECT `count`, `unbanDate` FROM ?_account_bannedips WHERE `ip` = ? AND `type` = 0', self::$ip))
        {
            if ($ipBan['count'] > Cfg::get('ACC_FAILED_AUTH_COUNT') && $ipBan['unbanDate'] > time())
                return false;
            else if ($ipBan['unbanDate'] <= time())
                DB::Aowow()->query('DELETE FROM ?_account_bannedips WHERE `ip` = ?', self::$ip);
        }

        // try to restore session
        if (empty($_SESSION['user']))
            return false;

        // timed out...
        if (!empty($_SESSION['timeout']) && $_SESSION['timeout'] <= time())
            return false;

        $uData = DB::Aowow()->SelectRow(
           'SELECT    a.`id`, a.`passHash`, a.`displayName`, a.`locale`, a.`userGroups`, a.`userPerms`, a.`allowExpire`, BIT_OR(ab.`typeMask`) AS "bans", IFNULL(SUM(r.`amount`), 0) AS "reputation", a.`avatar`, a.`dailyVotes`, a.`excludeGroups`
            FROM      ?_account a
            LEFT JOIN ?_account_banned ab ON a.`id` = ab.`userId` AND ab.`end` > UNIX_TIMESTAMP()
            LEFT JOIN ?_account_reputation r ON a.`id` = r.`userId`
            WHERE     a.`id` = ?d
            GROUP BY  a.`id`',
            $_SESSION['user']
        );

        if (!$uData)
            return false;

        if ($loc = WoWLocale::tryFrom($uData['locale']))
            self::$preferedLoc = $loc;

        // password changed, terminate session
        if (AUTH_MODE_SELF && $uData['passHash'] != $_SESSION['hash'])
        {
            self::destroy();
            return false;
        }

        self::$id            = intVal($uData['id']);
        self::$displayName   = $uData['displayName'];
        self::$passHash      = $uData['passHash'];
        self::$expires       = (bool)$uData['allowExpire'];
        self::$reputation    = $uData['reputation'];
        self::$banStatus     = $uData['bans'];
        self::$groups        = $uData['bans'] & (ACC_BAN_TEMP | ACC_BAN_PERM) ? 0 : intval($uData['userGroups']);
        self::$perms         = $uData['bans'] & (ACC_BAN_TEMP | ACC_BAN_PERM) ? 0 : intval($uData['userPerms']);
        self::$dailyVotes    = $uData['dailyVotes'];
        self::$excludeGroups = $uData['excludeGroups'];

        $conditions = array(
            [['cuFlags', PROFILER_CU_DELETED, '&'], 0],
            ['OR', ['user', self::$id], ['ap.accountId', self::$id]]
        );

        if (self::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
            array_shift($conditions);

        self::$profiles = (new LocalProfileList($conditions));

        if ($uData['avatar'])
            self::$avatar = $uData['avatar'];

        // stuff, that updates on a daily basis goes here (if you keep you session alive indefinitly, the signin-handler doesn't do very much)
        // - conscutive visits
        // - votes per day
        // - reputation for daily visit
        if (self::$id)
        {
            $lastLogin = DB::Aowow()->selectCell('SELECT curLogin FROM ?_account WHERE id = ?d', self::$id);
            // either the day changed or the last visit was >24h ago
            if (date('j', $lastLogin) != date('j') || (time() - $lastLogin) > 1 * DAY)
            {
                // daily votes (we need to reset this one)
                self::$dailyVotes = self::getMaxDailyVotes();

                DB::Aowow()->query(
                   'UPDATE  ?_account
                    SET     `dailyVotes` = ?d, `prevLogin` = `curLogin`, `curLogin` = UNIX_TIMESTAMP(), `prevIP` = `curIP`, `curIP` = ?
                    WHERE   `id` = ?d',
                    self::$dailyVotes,
                    self::$ip,
                    self::$id
                );

                // gain rep for daily visit
                if (!(self::$banStatus & (ACC_BAN_TEMP | ACC_BAN_PERM)) && !self::isInGroup(U_GROUP_PENDING))
                    Util::gainSiteReputation(self::$id, SITEREP_ACTION_DAILYVISIT);

                // increment consecutive visits (next day or first of new month and not more than 48h)
                // i bet my ass i forgott a corner case
                if ((date('j', $lastLogin) + 1 == date('j') || (date('j') == 1 && date('n', $lastLogin) != date('n'))) && (time() - $lastLogin) < 2 * DAY)
                    DB::Aowow()->query('UPDATE ?_account SET `consecutiveVisits` = `consecutiveVisits` + 1 WHERE `id` = ?d', self::$id);
                else
                    DB::Aowow()->query('UPDATE ?_account SET `consecutiveVisits` = 0 WHERE `id` = ?d', self::$id);
            }
        }

        return true;
    }

    private static function setIP() : void
    {
        $ipAddr = '';
        $method = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

        foreach ($method as $m)
        {
            if ($rawIp = getenv($m))
            {
                if ($m == 'HTTP_X_FORWARDED')
                    $rawIp = explode(',', $rawIp)[0];       // [ip, proxy1, proxy2]

                // check IPv4
                if ($ipAddr = filter_var($rawIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
                    break;

                // check IPv6
                if ($ipAddr = filter_var($rawIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
                    break;
            }
        }

        self::$ip = $ipAddr ?: null;
    }

    public static function save(bool $toDB = false)
    {
        $_SESSION['user']    = self::$id;
        $_SESSION['hash']    = self::$passHash;
        $_SESSION['locale']  = self::$preferedLoc;
        $_SESSION['timeout'] = self::$expires ? time() + Cfg::get('SESSION_TIMEOUT_DELAY') : 0;
        // $_SESSION['dataKey'] does not depend on user login status and is set in User::init()

        if (self::$id && $toDB)
            DB::Aowow()->query('UPDATE ?_account SET `locale` = ? WHERE `id` = ?', self::$preferedLoc->value, self::$id);
    }

    public static function destroy()
    {
        session_regenerate_id(true);                        // session itself is not destroyed; status changed => regenerate id
        session_unset();

        $_SESSION['locale']  = self::$preferedLoc;          // keep locale
        $_SESSION['dataKey'] = self::$dataKey;              // keep dataKey

        self::$id           = 0;
        self::$displayName  = '';
        self::$perms        = 0;
        self::$groups       = U_GROUP_NONE;
    }


    /*******************/
    /* auth mechanisms */
    /*******************/

    public static function Auth($name, $pass)
    {
        $user = 0;
        $hash = '';

        switch (Cfg::get('ACC_AUTH_MODE'))
        {
            case AUTH_MODE_SELF:
            {
                if (!self::$ip)
                    return AUTH_INTERNAL_ERR;

                // handle login try limitation
                $ip = DB::Aowow()->selectRow('SELECT `ip`, `count`, `unbanDate` FROM ?_account_bannedips WHERE `type` = 0 AND `ip` = ?', self::$ip);
                if (!$ip || $ip['unbanDate'] < time())      // no entry exists or time expired; set count to 1
                    DB::Aowow()->query('REPLACE INTO ?_account_bannedips (`ip`, `type`, `count`, `unbanDate`) VALUES (?, 0, 1, UNIX_TIMESTAMP() + ?d)', self::$ip, Cfg::get('ACC_FAILED_AUTH_BLOCK'));
                else                                        // entry already exists; increment count
                    DB::Aowow()->query('UPDATE ?_account_bannedips SET `count` = `count` + 1, `unbanDate` = UNIX_TIMESTAMP() + ?d WHERE `ip` = ?', Cfg::get('ACC_FAILED_AUTH_BLOCK'), self::$ip);

                if ($ip && $ip['count'] >= Cfg::get('ACC_FAILED_AUTH_COUNT') && $ip['unbanDate'] >= time())
                    return AUTH_IPBANNED;

                $query = DB::Aowow()->SelectRow(
                   'SELECT    a.`id`, a.`passHash`, BIT_OR(ab.`typeMask`) AS "bans", a.`status`
                    FROM      ?_account a
                    LEFT JOIN ?_account_banned ab ON a.`id` = ab.`userId` AND ab.`end` > UNIX_TIMESTAMP()
                    WHERE     a.`user` = ?
                    GROUP BY  a.`id`',
                    $name
                );
                if (!$query)
                    return AUTH_WRONGUSER;

                self::$passHash = $query['passHash'];
                if (!self::verifyCrypt($pass))
                    return AUTH_WRONGPASS;

                // successfull auth; clear bans for this IP
                DB::Aowow()->query('DELETE FROM ?_account_bannedips WHERE `type` = 0 AND `ip` = ?', self::$ip);

                if ($query['bans'] & (ACC_BAN_PERM | ACC_BAN_TEMP))
                    return AUTH_BANNED;

                $user = $query['id'];
                $hash = $query['passHash'];
                break;
            }
            case AUTH_MODE_REALM:
            {
                if (!DB::isConnectable(DB_AUTH))
                    return AUTH_INTERNAL_ERR;

                $wow = DB::Auth()->selectRow('SELECT a.id, a.salt, a.verifier, ab.active AS hasBan FROM account a LEFT JOIN account_banned ab ON ab.id = a.id AND active <> 0 WHERE username = ? LIMIT 1', $name);
                if (!$wow)
                    return AUTH_WRONGUSER;

                if (!self::verifySRP6($name, $pass, $wow['salt'], $wow['verifier']))
                    return AUTH_WRONGPASS;

                if ($wow['hasBan'])
                    return AUTH_BANNED;

                if ($_ = self::checkOrCreateInDB($wow['id'], $name))
                    $user = $_;
                else
                    return AUTH_INTERNAL_ERR;

                break;
            }
            case AUTH_MODE_EXTERNAL:
            {
                if (!file_exists('config/extAuth.php'))
                {
                    trigger_error('config/extAuth.php not found');
                    return AUTH_INTERNAL_ERR;
                }

                require 'config/extAuth.php';

                if (!function_exists('extAuth'))
                {
                    trigger_error('external auth function extAuth() not defined in config/extAuth.php');
                    return AUTH_INTERNAL_ERR;
                }

                $extGroup = -1;
                $result   = extAuth($name, $pass, $extId, $extGroup);

                if ($result == AUTH_OK && $extId)
                {
                    if ($_ = self::checkOrCreateInDB($extId, $name, $extGroup))
                        $user = $_;
                    else
                        return AUTH_INTERNAL_ERR;

                    break;
                }

                return $result;
            }
            default:
                return AUTH_INTERNAL_ERR;
        }

        // kickstart session
        session_unset();
        $_SESSION['user'] = $user;
        $_SESSION['hash'] = $hash;

        return AUTH_OK;
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

        $newId = DB::Aowow()->query('INSERT IGNORE INTO ?_account (`extId`, `user`, `passHash`, `displayName`, `email`, `joinDate`, `allowExpire`, `prevIP`, `prevLogin`, `locale`, `status`, `userGroups`) VALUES (?d, ?, "", ?, "", UNIX_TIMESTAMP(), 0, ?, UNIX_TIMESTAMP(), ?d, ?d, ?d)',
            $extId,
            $name,
            Util::ucFirst($name),
            $_SERVER["REMOTE_ADDR"] ?? '',
            self::$preferedLoc->value,
            ACC_STATUS_OK,
            $userGroup >= U_GROUP_NONE ? $userGroup : U_GROUP_NONE
        );

        if ($newId)
            Util::gainSiteReputation($newId, SITEREP_ACTION_REGISTER);

        return $newId;
    }

    private static function createSalt()
    {
        $algo     = '$2a';
        $strength = '$09';
        $salt     = '$'.Util::createHash(22);

        return $algo.$strength.$salt;
    }

    // crypt used by aowow
    public static function hashCrypt($pass)
    {
        return crypt($pass, self::createSalt());
    }

    public static function verifyCrypt($pass, $hash = '')
    {
        $_ = $hash ?: self::$passHash;
        return $_ === crypt($pass, $_);
    }

    private static function verifySRP6($user, $pass, $salt, $verifier)
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

    public static function isValidName($name, &$errCode = 0)
    {
        $errCode = 0;

        // different auth modes require different usernames
        $min = 0;                                           // external case
        $max = 0;
        if (Cfg::get('ACC_AUTH_MODE') == AUTH_MODE_SELF)
        {
            $min = 4;
            $max = 16;
        }
        else if (Cfg::get('ACC_AUTH_MODE') == AUTH_MODE_REALM)
        {
            $min = 3;
            $max = 32;
        }

        if (($min && mb_strlen($name) < $min) || ($max && mb_strlen($name) > $max))
            $errCode = 1;
        else if (preg_match('/[^\w\d\-]/i', $name))
            $errCode = 2;

        return $errCode == 0;
    }

    public static function isValidPass($pass, &$errCode = 0)
    {
        $errCode = 0;

        // only enforce for own passwords
        if (mb_strlen($pass) < 6 && Cfg::get('ACC_AUTH_MODE') == AUTH_MODE_SELF)
            $errCode = 1;
     // else if (preg_match('/[^\w\d!"#\$%]/', $pass))    // such things exist..? :o
         // $errCode = 2;

        return $errCode == 0;
    }


    /*********************/
    /* access management */
    /*********************/

    public static function isInGroup($group) : bool
    {
        return (self::$groups & $group) != 0;
    }

    public static function canComment() : bool
    {
        if (!self::$id || self::$banStatus & (ACC_BAN_COMMENT | ACC_BAN_PERM | ACC_BAN_TEMP))
            return false;

        return self::$perms || self::$reputation >= Cfg::get('REP_REQ_COMMENT');
    }

    public static function canReply() : bool
    {
        if (!self::$id || self::$banStatus & (ACC_BAN_COMMENT | ACC_BAN_PERM | ACC_BAN_TEMP))
            return false;

        return self::$perms || self::$reputation >= Cfg::get('REP_REQ_REPLY');
    }

    public static function canUpvote() : bool
    {
        if (!self::$id || self::$banStatus & (ACC_BAN_COMMENT | ACC_BAN_PERM | ACC_BAN_TEMP))
            return false;

        return self::$perms || (self::$reputation >= Cfg::get('REP_REQ_UPVOTE') && self::$dailyVotes > 0);
    }

    public static function canDownvote() : bool
    {
        if (!self::$id || self::$banStatus & (ACC_BAN_RATE | ACC_BAN_PERM | ACC_BAN_TEMP))
            return false;

        return self::$perms || (self::$reputation >= Cfg::get('REP_REQ_DOWNVOTE') && self::$dailyVotes > 0);
    }

    public static function canSupervote() : bool
    {
        if (!self::$id || self::$banStatus & (ACC_BAN_RATE | ACC_BAN_PERM | ACC_BAN_TEMP))
            return false;

        return self::$reputation >= Cfg::get('REP_REQ_SUPERVOTE');
    }

    public static function canUploadScreenshot() : bool
    {
        if (!self::$id || self::$banStatus & (ACC_BAN_SCREENSHOT | ACC_BAN_PERM | ACC_BAN_TEMP))
            return false;

        return true;
    }

    public static function canWriteGuide() : bool
    {
        if (!self::$id || self::$banStatus & (ACC_BAN_GUIDE | ACC_BAN_PERM | ACC_BAN_TEMP))
            return false;

        return true;
    }

    public static function canSuggestVideo() : bool
    {
        if (!self::$id || self::$banStatus & (ACC_BAN_VIDEO | ACC_BAN_PERM | ACC_BAN_TEMP))
            return false;

        return true;
    }

    public static function isPremium() : bool
    {
        return self::isInGroup(U_GROUP_PREMIUM) || self::$reputation >= Cfg::get('REP_REQ_PREMIUM');
    }


    /**************/
    /* js-related */
    /**************/

    public static function decrementDailyVotes() : void
    {
        self::$dailyVotes--;
        DB::Aowow()->query('UPDATE ?_account SET `dailyVotes` = ?d WHERE `id` = ?d', self::$dailyVotes, self::$id);
    }

    public static function getCurrentDailyVotes() : int
    {
        return self::$dailyVotes;
    }

    public static function getMaxDailyVotes() : int
    {
        if (!self::$id || self::$banStatus & (ACC_BAN_PERM | ACC_BAN_TEMP))
            return 0;

        return Cfg::get('USER_MAX_VOTES') + (self::$reputation >= Cfg::get('REP_REQ_VOTEMORE_BASE') ? 1 + intVal((self::$reputation - Cfg::get('REP_REQ_VOTEMORE_BASE')) / Cfg::get('REP_REQ_VOTEMORE_ADD')) : 0);
    }

    public static function getReputation() : int
    {
        return self::$reputation;
    }

    public static function getUserGlobals() : array
    {
        $gUser = array(
            'id'          => self::$id,
            'name'        => self::$displayName,
            'roles'       => self::$groups,
            'permissions' => self::$perms,
            'cookies'     => []
        );

        if (!self::$id || self::$banStatus & (ACC_BAN_TEMP | ACC_BAN_PERM))
            return $gUser;

        $gUser['commentban']        = !self::canComment();
        $gUser['canUpvote']         = self::canUpvote();
        $gUser['canDownvote']       = self::canDownvote();
        $gUser['canPostReplies']    = self::canReply();
        $gUser['superCommentVotes'] = self::canSupervote();
        $gUser['downvoteRep']       = Cfg::get('REP_REQ_DOWNVOTE');
        $gUser['upvoteRep']         = Cfg::get('REP_REQ_UPVOTE');
        $gUser['characters']        = self::getCharacters();
        $gUser['excludegroups']     = self::$excludeGroups;
        $gUser['settings']          = (new StdClass);       // profiler requires this to be set; has property premiumborder (NYI)

        if (Cfg::get('DEBUG') && User::isInGroup(U_GROUP_DEV | U_GROUP_ADMIN | U_GROUP_TESTER))
            $gUser['debug'] = true;                         // csv id-list output option on listviews

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
        $modes  = [1 => 'excludes', 2 => 'includes'];
        foreach ($modes as $mode => $field)
            if ($ex = DB::Aowow()->selectCol('SELECT `type` AS ARRAY_KEY, `typeId` AS ARRAY_KEY2, `typeId` FROM ?_account_excludes WHERE `mode` = ?d AND `userId` = ?d', $mode, self::$id))
                foreach ($ex as $type => $ids)
                    $result[$field][$type] = array_values($ids);

        return $result;
    }

    public static function getCharacters() : array
    {
        if (!self::$profiles)
            return [];

        return self::$profiles->getJSGlobals(PROFILEINFO_CHARACTER);
    }

    public static function getProfiles() : array
    {
        if (!self::$profiles)
            return [];

        return self::$profiles->getJSGlobals(PROFILEINFO_PROFILE);
    }

    public static function getPinnedCharacter() : array
    {
        if (!self::$profiles)
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

        if ($guides = DB::Aowow()->select('SELECT `id`, `title`, `url` FROM ?_guides WHERE `userId` = ?d AND `status` <> ?d', self::$id, GUIDE_STATUS_ARCHIVED))
        {
            // fix url
            array_walk($guides, fn(&$x) => $x['url'] = '?guide='.($x['url'] ?? $x['id']));
            $result = $guides;
        }

        return $result;
    }

    public static function getCookies() : array
    {
        $data = [];

        if (self::$id)
            $data = DB::Aowow()->selectCol('SELECT `name` AS ARRAY_KEY, `data` FROM ?_account_cookies WHERE `userId` = ?d', self::$id);

        return $data;
    }

    public static function getFavorites() : array
    {
        if (!self::$id)
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
}

?>
