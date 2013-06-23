<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
    Cookie-Content
    W X Y [Z] : base64
           Z : passHash                 length: ?   exampleValue: base64_encode('+RQGbSW7Yqyz6fTNAUrI3BE-Zt0FoiMh8ke1_sCmLDwOjgXda9JxH2KcPVu4l5vnp')
        Y : X chars accId as hex        length: X   exampleValue: 0xF29
      X : [1-9] num chars of Y as int   length: 1   exampleValue: 3
    W : locale [0, 2, 3, 6, 8]          length: 1   exampleValue: 0

    03F29K1JRR2JTVzdZcXl6NmZUTkFVckkzQkUtWnQwRm9pTWg4a2UxX3NDbUxEd09qZ1hkYTlKeEgyS2NQVnU0bDV2bnA=
*/

class User
{
    public static $id;
    public static $authId;
    public static $displayName;
    public static $email;

    public static $user;
    private static $passHash;
    private static $timeout;

    public static $lastIP;
    public static $lastLogin;
    public static $joindate;

    public static $groups;
    public static $perms;
    public static $localeId;
    public static $localeString;
    public static $profiles;
    public static $characters;
    public static $avatar;
    public static $description;

    /* public static $ratingBan; */
    /* public static $commentBan; .. jeez.. banflags..?  &1: banIP; &2: banUser; &4: disableRating; &8: disableComment; &16: disableUpload ?? */
    public static $bannedIP;
    public static $banned;
    public static $unbanDate;
    public static $bannedBy;
    public static $banReason;

    public static function init($userId)
    {
        self::$id = $userId;

        $ipBan = DB::Auth()->SelectRow('SELECT count, unbanDate AS unbanDateIP FROM ?_account_bannedIPs WHERE ip = ?s AND type = 0',
            $_SERVER['REMOTE_ADDR']
        );
        // explicit " > "; incremented first, checked after
        self::$bannedIP = $ipBan && $ipBan['count'] > $GLOBALS['AoWoWconf']['loginFailCount'] && $ipBan['unbanDateIP'] > time();

        $query = !$userId ? null : DB::Auth()->SelectRow('
                SELECT
                    a.id, a.authId, a.user, a.passHash, a.displayName, a.email, a.lastIP, a.lastLogin, a.joindate, a.locale, a.avatar, a.description, a.userGroups, a.userPerms, a.timeout,
                    ab.bannedBy, ab.banReason, ab.isActive, ab.unbanDate
                FROM
                    ?_account a
                LEFT JOIN
                    ?_account_banned ab ON a.id = ab.id AND ab.isActive = 1
                WHERE
                    a.id = ?d
                ORDER
                    BY ab.banDate DESC
                LIMIT
                    1
            ',
            $userId
        );

        if ($query)
        {
            self::$authId       = intval($query['authId']);
            self::$user         = $query['user'];
            self::$passHash     = $query['passHash'];
            self::$email        = $query['email'];
            self::$lastIP       = $query['lastIP'];
            self::$lastLogin    = intval($query['lastLogin']);
            self::$joindate     = intval($query['joindate']);
            self::$localeId     = intval($query['locale']);
            self::$localeString = self::localeString(self::$localeId);
            self::$timeout      = intval($query['timeout']);
            self::$groups       = intval($query['userGroups']);
            self::$perms        = intval($query['userPerms']);
            self::$banned       = $query['isActive'] == 1 && $query['unbanDate'] > time();
            self::$unbanDate    = intval($query['unbanDate']);
            self::$bannedBy     = intval($query['bannedBy']);
            self::$banReason    = $query['banReason'];

            self::$displayName  = $query['displayName'];
            self::$avatar       = $query['avatar'];
            self::$description  = $query['description'];
        }
        else
            self::setLocale();
    }

    // set and use until further notice
    public static function setLocale($set = -1)
    {
        if ($set != -1)
        {
            $loc = isset(Util::$localeStrings[$set]) ? $set : 0;
            if (self::$id)
            {
                DB::Auth()->query('UPDATE ?_account SET locale = ? WHERE id = ?',
                    $loc,
                    self::$id
                );
            }
        }
        else if (isset($_COOKIE[COOKIE_AUTH]))
        {
            $loc = intval(substr($_COOKIE[COOKIE_AUTH], 0, 1));
            $loc = isset(Util::$localeStrings[$loc]) ? $loc : 0;
        }
        else
        {
            if (empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
                $loc = 0;
            else
            {
                $loc = strtolower(substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2));
                switch ($loc) {
                    case 'ru': $loc = 8; break;
                    case 'es': $loc = 6; break;
                    case 'de': $loc = 3; break;
                    case 'fr': $loc = 2; break;
                    default:   $loc = 0;
                }
            }
        }

        // set
        self::$localeId     = $loc;
        self::$localeString = self::localeString($loc);

        Lang::load(self::$localeString);
    }

    // only use this once
    public static function useLocale($use)
    {
        self::$localeId     = isset(Util::$localeStrings[$use]) ? $use : 0;
        self::$localeString = self::localeString(self::$localeId);

        Lang::load(self::$localeString);
    }

    public static function isInGroup($group)
    {
        return (self::$groups & $group) != 0;
    }

    public static function Auth($pass = '')
    {
        if (self::$bannedIP)
            return AUTH_IPBANNED;

        if (!$pass)                                         // pass not set, check against cookie
        {
            $offset = intVal($_COOKIE[COOKIE_AUTH][1]) + 2; // value of second char in string + 1 for locale
            $cookiePass = base64_decode(substr($_COOKIE[COOKIE_AUTH], $offset));
            if ($cookiePass != self::$passHash)
                return AUTH_WRONGPASS;

            // "stay logged in" unchecked; kill session in time() + 5min
            // if (self::$timeout > 0 && self::$timeout < time())
                // return AUTH_TIMEDOUT;

            if (self::$timeout > 0)
                DB::Auth()->query('UPDATE ?_account SET timeout = ?d WHERE id = ?d',
                    time() + $GLOBALS['AoWoWconf']['sessionTimeout'],
                    self::$id
                );
        }
        else
        {
            if (self::$passHash[0] == '$')                  // salted hash -> aowow-password
            {
                if (!self::verifyCrypt($pass))
                    return AUTH_WRONGPASS;
            }
            else                                            // assume sha1 hash; account got copied from wow database
            {
                if (self::verifySHA1($pass))
                    self::convertAuthInfo($pass);           // drop sha1 and generate with crypt
                else
                    return AUTH_WRONGPASS;
            }
        }

        if (self::$banned)
            return AUTH_BANNED;

        return AUTH_OK;
    }

    private static function localeString($loc = -1)
    {
        if (!isset(Util::$localeStrings[$loc]))
            $loc = 0;

        return Util::$localeStrings[$loc];
    }

    private static function createSalt()
    {
        static $seed = "./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $algo        = '$2a';
        $strength    = '$09';
        $salt        = '$';

        for ($i = 0; $i < 22; $i++)
            $salt .= substr($seed, mt_rand(0, 63), 1);

        return $algo.$strength.$salt;
    }

    private static function hashCrypt($pass)
    {
        return crypt($pass, self::createSalt());
    }

    private static function hashSHA1($pass)
    {
        return sha1(strtoupper(self::$user).':'.strtoupper($pass));
    }

    private static function verifyCrypt($pass)
    {
        return self::$passHash == crypt($pass, self::$passHash);
    }

    private static function verifySHA1($pass)
    {
        return self::$passHash == self::hashSHA1($pass);
    }

    private static function convertAuthInfo($pass)
    {
        self::$passHash = self::hashCrypt($pass);

        DB::Auth()->query('UPDATE ?_account SET passHash = ?s WHERE id = ?d',
            self::$passHash,
            self::$id
        );
    }

    public static function assignUserToTemplate(&$smarty)
    {
        $set = array(
            'id'       => self::$id,
            'locale'   => self::$localeId,
            'language' => self::$localeString,
            'name'     => self::$displayName ? self::$displayName : '',
            'perms'    => self::$perms ? self::$perms : 0,
            'roles'    => self::$groups ? self::$groups : 0,
        );

        if (self::$id > 0)
        {
            $subSet = array(
                'login'     => self::$user,
                'wow'       => self::$authId,               // todo: get account name from id
                'email'     => self::$email,
                'lastIP'    => self::$lastIP,
                'lastLogin' => self::$lastLogin,
                'joinDate'  => self::$joindate,
                'banned'    => self::$banned,               // todo: get duration, banner, reason
                'unbanDate' => self::$unbanDate,
                'bannedBy'  => self::$bannedBy,
                'banReason' => self::$banReason,
                'avatar'    => self::$avatar,
                'community' => self::$description,
                'chars'     => self::getCharacters(),
                'profiles'  => self::getProfiles()
            );

            if ($_ = self::getWeightScales())
                $subSet['weights'] = json_encode($_, JSON_NUMERIC_CHECK);

            $smarty->assign('user', array_merge($set, $subSet));
        }
        else
            $smarty->assign('user', $set);
    }

    public static function getWeightScales()
    {
        $data = [];

        $res = DB::Aowow()->select('SELECT * FROM ?_account_weightscales WHERE account = ?d', self::$id);
        foreach ($res as $i)
        {
            $set = array (
                'name' => $i['name'],
                'id'   => $i['id']
            );

            $weights = explode(',', $i['weights']);
            foreach ($weights as $weight)
            {
                $w = explode(':', $weight);

                if ($w[1] === 'undefined')
                    $w[1] = 0;

                $set[$w[0]] = $w[1];
            }

            $data[] = $set;
        }

        return $data;
    }

    public static function getCharacters($asJSON = true)
    {
        if (empty(self::$characters))
        {
            // todo: do after profiler
            // existing chars on realm(s)
            if ($asJSON)
                $chars = '[{"name":"ExampleChar", "realmname":"Example Realm", "region":"eu", "realm":"exrealm", icon:"inv_axe_04", "race":4, "gender":0, "classs":11, "level":80}]';
            else
                $chars = array(
                    array("name" => "ExampleChar", "realmname" => "Example Realm", "region" => "eu", "realm" => "exrealm", "icon" => "inv_axe_04", "race" => 4, "gender" => 0, "classs" => 11, "level" => 80)
                );

            self::$characters = $chars;
        }
        return self::$characters;
    }

    public static function getProfiles($asJSON = true)
    {
        if (empty(self::$profiles))
        {
            // todo =>  do after profiler
            // chars build in profiler
            if ($asJSON)
                $profiles = '[{"id":21, "name":"Example Profile 1", "race":4, "gender":1, "classs":5, "level":72,  icon:"inv_axe_04"},{"id":23, "name":"Example Profile 2", "race":11, "gender":0, "classs":3, "level":17}]';
            else
                $profiles = array(
                    array("id" => 21, "name" => "Example Profile 1", "race" => 4, "gender" => 1, "classs" => 5, "level" => 72, "icon" => "inv_axe_04"),
                    array("id" => 23, "name" => "Example Profile 2", "race" => 11, "gender" => 0, "classs" => 3, "level" => 17)
                );

            self::$profiles = $profiles;
        }
        return self::$profiles;
    }

    public static function writeCookie()
    {
        $cookie = self::$localeId.count(dechex(self::$id)).dechex(self::$id).base64_encode(self::$passHash);
        SetCookie(COOKIE_AUTH, $cookie, time() + YEAR);
    }

    public static function destroy()
    {
        $cookie = self::$localeId.'10';                       // id = 0, length of this is 1, empty base64_encode is 0
        SetCookie(COOKIE_AUTH, $cookie, time() + YEAR);

        self::$id = 0;
        self::$displayName = '';
        self::$perms = 0;
        self::$groups = 0;
        self::$characters = NULL;
        self::$profiles = NULL;
    }
}

?>
