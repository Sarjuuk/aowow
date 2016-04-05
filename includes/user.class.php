<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class User
{
    public static $id           = 0;
    public static $displayName  = '';
    public static $banStatus    = 0x0;                      // see ACC_BAN_* defines
    public static $groups       = 0x0;
    public static $perms        = 0;
    public static $localeId     = 0;
    public static $localeString = 'enus';
    public static $avatar       = 'inv_misc_questionmark';
    public static $dailyVotes   = 0;
    public static $ip           = null;

    private static $reputation  = 0;
    private static $dataKey     = '';
    private static $expires     = false;
    private static $passHash    = '';

    public static function init()
    {
        self::setIP();
        self::setLocale();

        // session have a dataKey to access the JScripts (yes, also the anons)
        if (empty($_SESSION['dataKey']))
            $_SESSION['dataKey'] = Util::createHash();      // just some random numbers for identifictaion purpose

        self::$dataKey = $_SESSION['dataKey'];

        if (!self::$ip)
            return false;

        // check IP bans
        if ($ipBan = DB::Aowow()->selectRow('SELECT count, unbanDate FROM ?_account_bannedips WHERE ip = ? AND type = 0', self::$ip))
        {
            if ($ipBan['count'] > CFG_ACC_FAILED_AUTH_COUNT && $ipBan['unbanDate'] > time())
                return false;
            else if ($ipBan['unbanDate'] <= time())
                DB::Aowow()->query('DELETE FROM ?_account_bannedips WHERE ip = ?', self::$ip);
        }

        // try to restore session
        if (empty($_SESSION['user']))
            return false;

        // timed out...
        if (!empty($_SESSION['timeout']) && $_SESSION['timeout'] <= time())
            return false;

        $query = DB::Aowow()->SelectRow('
            SELECT    a.id, a.passHash, a.displayName, a.locale, a.userGroups, a.userPerms, a.allowExpire, BIT_OR(ab.typeMask) AS bans, IFNULL(SUM(r.amount), 0) as reputation, a.avatar, a.dailyVotes
            FROM      ?_account a
            LEFT JOIN ?_account_banned ab ON a.id = ab.userId AND ab.end > UNIX_TIMESTAMP()
            LEFT JOIN ?_account_reputation r ON a.id = r.userId
            WHERE     a.id = ?d
            GROUP     BY a.id',
            $_SESSION['user']
        );

        if (!$query)
            return false;

        // password changed, terminate session
        if (AUTH_MODE_SELF && $query['passHash'] != $_SESSION['hash'])
        {
            self::destroy();
            return false;
        }

        self::$id          = intval($query['id']);
        self::$displayName = $query['displayName'];
        self::$passHash    = $query['passHash'];
        self::$expires     = (bool)$query['allowExpire'];
        self::$reputation  = $query['reputation'];
        self::$banStatus   = $query['bans'];
        self::$groups      = $query['bans'] & (ACC_BAN_TEMP | ACC_BAN_PERM) ? 0 : intval($query['userGroups']);
        self::$perms       = $query['bans'] & (ACC_BAN_TEMP | ACC_BAN_PERM) ? 0 : intval($query['userPerms']);
        self::$dailyVotes  = $query['dailyVotes'];

        if ($query['avatar'])
            self::$avatar = $query['avatar'];

        if (self::$localeId != $query['locale'])            // reset, if changed
            self::setLocale(intVal($query['locale']));

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

                DB::Aowow()->query('
                    UPDATE  ?_account
                    SET     dailyVotes = ?d, prevLogin = curLogin, curLogin = UNIX_TIMESTAMP(), prevIP = curIP, curIP = ?
                    WHERE   id = ?d',
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
                    DB::Aowow()->query('UPDATE ?_account SET consecutiveVisits = consecutiveVisits + 1 WHERE id = ?d', self::$id);
                else
                    DB::Aowow()->query('UPDATE ?_account SET consecutiveVisits = 0 WHERE id = ?d', self::$id);
            }
        }

        return true;
    }

    private static function setIP()
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
                if ($ipAddr = filter_var($rawIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_RES_RANGE))
                    break;

                // check IPv6
                if ($ipAddr = filter_var($rawIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_RES_RANGE))
                    break;
            }
        }

        self::$ip = $ipAddr ?: null;
    }

    /****************/
    /* set language */
    /****************/

    // set and save
    public static function setLocale($set = -1)
    {
        $loc = LOCALE_EN;

        // get
        if ($set != -1 && isset(Util::$localeStrings[$set]))
            $loc = $set;
        else if (isset($_SESSION['locale']) && isset(Util::$localeStrings[$_SESSION['locale']]))
            $loc = $_SESSION['locale'];
        else if (!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
        {
            $loc = strtolower(substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2));
            switch ($loc) {
                case 'ru': $loc = LOCALE_RU; break;
                case 'es': $loc = LOCALE_ES; break;
                case 'de': $loc = LOCALE_DE; break;
                case 'fr': $loc = LOCALE_FR; break;
                default:   $loc = LOCALE_EN;
            }
        }

        // check
        if ($loc != LOCALE_EN && !(CFG_LOCALES & (1 << $loc)))
            $loc = LOCALE_EN;

        // set
        if (self::$id)
            DB::Aowow()->query('UPDATE ?_account SET locale = ? WHERE id = ?', $loc, self::$id);

        self::useLocale($loc);
    }

    // only use once
    public static function useLocale($use)
    {
        self::$localeId     = isset(Util::$localeStrings[$use]) ? $use : LOCALE_EN;
        self::$localeString = self::localeString(self::$localeId);
    }

    private static function localeString($loc = -1)
    {
        if (!isset(Util::$localeStrings[$loc]))
            $loc = 0;

        return Util::$localeStrings[$loc];
    }

    /*******************/
    /* auth mechanisms */
    /*******************/

    public static function Auth($name, $pass)
    {
        $user = 0;
        $hash = '';

        switch (CFG_ACC_AUTH_MODE)
        {
            case AUTH_MODE_SELF:
            {
                if (!self::$ip)
                    return AUTH_INTERNAL_ERR;

                // handle login try limitation
                $ip = DB::Aowow()->selectRow('SELECT ip, count, unbanDate FROM ?_account_bannedips WHERE type = 0 AND ip = ?', self::$ip);
                if (!$ip || $ip['unbanDate'] < time())      // no entry exists or time expired; set count to 1
                    DB::Aowow()->query('REPLACE INTO ?_account_bannedips (ip, type, count, unbanDate) VALUES (?, 0, 1, UNIX_TIMESTAMP() + ?d)', self::$ip, CFG_ACC_FAILED_AUTH_BLOCK);
                else                                        // entry already exists; increment count
                    DB::Aowow()->query('UPDATE ?_account_bannedips SET count = count + 1, unbanDate = UNIX_TIMESTAMP() + ?d WHERE ip = ?', CFG_ACC_FAILED_AUTH_BLOCK, self::$ip);

                if ($ip && $ip['count'] >= CFG_ACC_FAILED_AUTH_COUNT && $ip['unbanDate'] >= time())
                    return AUTH_IPBANNED;

                $query = DB::Aowow()->SelectRow('
                    SELECT    a.id, a.passHash, BIT_OR(ab.typeMask) AS bans, a.status
                    FROM      ?_account a
                    LEFT JOIN ?_account_banned ab ON a.id = ab.userId AND ab.end > UNIX_TIMESTAMP()
                    WHERE     a.user = ?
                    GROUP     BY a.id',
                    $name
                );
                if (!$query)
                    return AUTH_WRONGUSER;

                self::$passHash = $query['passHash'];
                if (!self::verifyCrypt($pass))
                    return AUTH_WRONGPASS;

                // successfull auth; clear bans for this IP
                DB::Aowow()->query('DELETE FROM ?_account_bannedips WHERE type = 0 AND ip = ?', self::$ip);

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

                $wow = DB::Auth()->selectRow('SELECT a.id, a.sha_pass_hash, ab.active AS hasBan FROM account a LEFT JOIN account_banned ab ON ab.id = a.id AND active <> 0 WHERE username = ? LIMIT 1', $name);
                if (!$wow)
                    return AUTH_WRONGUSER;

                self::$passHash = $wow['sha_pass_hash'];
                if (!self::verifySHA1($name, $pass))
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
                    return AUTH_INTERNAL_ERR;

                require 'config/extAuth.php';
                $result = extAuth($name, $pass, $extId);

                if ($result == AUTH_OK && $extId)
                {
                    if ($_ = self::checkOrCreateInDB($extId, $name))
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

    // create a linked account for our settings if nessecary
    private static function checkOrCreateInDB($extId, $name)
    {
        if ($_ = DB::Aowow()->selectCell('SELECT id FROM ?_account WHERE extId = ?d', $extId))
            return $_;

        $newId = DB::Aowow()->query('INSERT IGNORE INTO ?_account (extId, user, displayName, joinDate, prevIP, prevLogin, locale, status) VALUES (?d, ?, ?, UNIX_TIMESTAMP(), ?, UNIX_TIMESTAMP(), ?d, ?d)',
            $extId,
            $name,
            Util::ucFirst($name),
            isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '',
            User::$localeId,
            ACC_STATUS_OK
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

    // sha1 used by TC / MaNGOS
    private static function hashSHA1($name, $pass)
    {
        return sha1(strtoupper($name).':'.strtoupper($pass));
    }

    private static function verifySHA1($name, $pass)
    {
        return strtoupper(self::$passHash) === strtoupper(self::hashSHA1($name, $pass));
    }

    public static function isValidName($name, &$errCode = 0)
    {
        $errCode = 0;

        // different auth modes require different usernames
        $min = 0;                                           // external case
        $max = 0;
        if (CFG_ACC_AUTH_MODE == AUTH_MODE_SELF)
        {
            $min = 4;
            $max = 16;
        }
        else if (CFG_ACC_AUTH_MODE == AUTH_MODE_REALM)
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
        if (mb_strlen($pass) < 6 && CFG_ACC_AUTH_MODE == AUTH_MODE_SELF)
            $errCode = 1;
     // else if (preg_match('/[^\w\d!"#\$%]/', $pass))    // such things exist..? :o
         // $errCode = 2;

        return $errCode == 0;
    }

    public static function save()
    {
        $_SESSION['user']    = self::$id;
        $_SESSION['hash']    = self::$passHash;
        $_SESSION['locale']  = self::$localeId;
        $_SESSION['timeout'] = self::$expires ? time() + CFG_SESSION_TIMEOUT_DELAY : 0;
        // $_SESSION['dataKey'] does not depend on user login status and is set in User::init()
    }

    public static function destroy()
    {
        session_regenerate_id(true);                        // session itself is not destroyed; status changed => regenerate id
        session_unset();

        $_SESSION['locale']  = self::$localeId;             // keep locale
        $_SESSION['dataKey'] = self::$dataKey;              // keep dataKey

        self::$id           = 0;
        self::$displayName  = '';
        self::$perms        = 0;
        self::$groups       = U_GROUP_NONE;
    }

    /*********************/
    /* access management */
    /*********************/

    public static function isInGroup($group)
    {
        return (self::$groups & $group) != 0;
    }

    public static function canComment()
    {
        if (!self::$id || self::$banStatus & (ACC_BAN_COMMENT | ACC_BAN_PERM | ACC_BAN_TEMP))
            return false;

        return self::$perms || self::$reputation >= CFG_REP_REQ_COMMENT;
    }

    public static function canUpvote()
    {
        if (!self::$id || self::$banStatus & (ACC_BAN_COMMENT | ACC_BAN_PERM | ACC_BAN_TEMP))
            return false;

        return self::$perms || (self::$reputation >= CFG_REP_REQ_UPVOTE && self::$dailyVotes > 0);
    }

    public static function canDownvote()
    {
        if (!self::$id || self::$banStatus & (ACC_BAN_RATE | ACC_BAN_PERM | ACC_BAN_TEMP))
            return false;

        return self::$perms || (self::$reputation >= CFG_REP_REQ_DOWNVOTE && self::$dailyVotes > 0);
    }

    public static function canSupervote()
    {
        if (!self::$id || self::$banStatus & (ACC_BAN_RATE | ACC_BAN_PERM | ACC_BAN_TEMP))
            return false;

        return self::$reputation >= CFG_REP_REQ_SUPERVOTE;
    }

    public static function isPremium()
    {
        return self::isInGroup(U_GROUP_PREMIUM) || self::$reputation >= CFG_REP_REQ_PREMIUM;
    }

    /**************/
    /* js-related */
    /**************/

    public static function decrementDailyVotes()
    {
        self::$dailyVotes--;
        DB::Aowow()->query('UPDATE ?_account SET dailyVotes = ?d WHERE id = ?d', self::$dailyVotes, self::$id);
    }

    public static function getCurDailyVotes()
    {
        return self::$dailyVotes;
    }

    public static function getMaxDailyVotes()
    {
        if (!self::$id || self::$banStatus & (ACC_BAN_PERM | ACC_BAN_TEMP))
            return 0;

        return CFG_USER_MAX_VOTES + (self::$reputation >= CFG_REP_REQ_VOTEMORE_BASE ? 1 + intVal((self::$reputation - CFG_REP_REQ_VOTEMORE_BASE) / CFG_REP_REQ_VOTEMORE_ADD) : 0);
    }

    public static function getReputation()
    {
        return self::$reputation;
    }

    public static function getUserGlobals()
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

        $gUser['commentban']        = (bool)(self::$banStatus & ACC_BAN_COMMENT);
        $gUser['canUpvote']         = self::canUpvote();
        $gUser['canDownvote']       = self::canDownvote();
        $gUser['canPostReplies']    = self::canComment();
        $gUser['superCommentVotes'] = self::canSupervote();
        $gUser['downvoteRep']       = CFG_REP_REQ_DOWNVOTE;
        $gUser['upvoteRep']         = CFG_REP_REQ_UPVOTE;
        $gUser['characters']        = self::getCharacters();

        if ($_ = self::getProfiles())
            $gUser['profiles'] = $_;

        if ($_ = self::getWeightScales())
            $gUser['weightscales'] = $_;

        if ($_ = self::getCookies())
            $gUser['cookies'] = $_;

        return $gUser;
    }

    public static function getWeightScales()
    {
        $result = [];

        $res = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, name FROM ?_account_weightscales WHERE userId = ?d', self::$id);
        if (!$res)
            return $result;

        $weights = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, `field` AS ARRAY_KEY2, val FROM ?_account_weightscale_data WHERE id IN (?a)', array_keys($res));
        foreach ($weights as $id => $data)
            $result[] = array_merge(['name' => $res[$id], 'id' => $id], $data);

        return $result;
    }

    public static function getCharacters()
    {
        // existing chars on realm(s)
        $characters = array(
            // array(
                // 'id'        => 22,
                // 'name'      => 'Example Char',
                // 'realmname' => 'Example Realm',
                // 'region'    => 'eu',
                // 'realm'     => 'example-realm',
                // 'race'      => 6,
                // 'classs'    => 11,
                // 'level'     => 80,
                // 'gender'    => 1,
                // 'pinned'    => 1
            // )
        );

        return $characters;
    }

    public static function getProfiles()
    {
        // chars build in profiler
        $profiles = array(
            // array('id' => 21, 'name' => 'Example Profile 1', 'race' => 4,  'classs' => 5, 'level' => 72, 'gender' => 1, 'icon' => 'inv_axe_04'),
            // array('id' => 23, 'name' => 'Example Profile 2', 'race' => 11, 'classs' => 3, 'level' => 17, 'gender' => 0)
        );

        return $profiles;
    }

    public static function getCookies()
    {
        $data = [];

        if (self::$id)
            $data = DB::Aowow()->selectCol('SELECT name AS ARRAY_KEY, data FROM ?_account_cookies WHERE userId = ?d', self::$id);

        return $data;
    }
}

?>
