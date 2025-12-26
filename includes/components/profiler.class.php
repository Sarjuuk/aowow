<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Profiler
{
    public const /* string */ PID_FILE     = 'config/pr-queue-pid';
    public const /* int    */ CHAR_GMFLAGS = 0x1 | 0x8 | 0x10 | 0x20; // PLAYER_EXTRA_ :: GM_ON | TAXICHEAT | GM_INVISIBLE | GM_CHAT

    public const /* array  */ REGIONS      = array(         // see cfg_categories.dbc
        'us' => [2, 3, 4, 5],                               // US (us, oceanic, latin america, americas - tournament)
        'kr' => [6, 7],                                     // KR (kr, tournament)
        'eu' => [8, 9, 10, 11, 12, 13],                     // EU (english, german, french, spanish, russian, eu - tournament)
        'tw' => [14, 15],                                   // TW (tw, tournament)
        'cn' => [16, 17, 18, 19, 20, 21, 22, 23, 24, 25],   // CN (cn, CN1-8, tournament)
       'dev' => [1, 26, 27, 28, 30]                         // Development, Test Server, Test Server - tournament, QA Server, Test Server 2
    );

    private static array $realms  = [];

    public static array $slot2InvType = array(
        1  => [INVTYPE_HEAD],                               // head
        2  => [INVTYPE_NECK],                               // neck
        3  => [INVTYPE_SHOULDERS],                          // shoulder
        4  => [INVTYPE_BODY],                               // shirt
        5  => [INVTYPE_CHEST, INVTYPE_ROBE],                // chest
        6  => [INVTYPE_WAIST],                              // waist
        7  => [INVTYPE_LEGS],                               // legs
        8  => [INVTYPE_FEET],                               // feet
        9  => [INVTYPE_WRISTS],                             // wrists
        10 => [INVTYPE_HANDS],                              // hands
        11 => [INVTYPE_FINGER],                             // finger1
        12 => [INVTYPE_FINGER],                             // finger2
        13 => [INVTYPE_TRINKET],                            // trinket1
        14 => [INVTYPE_TRINKET],                            // trinket2
        15 => [INVTYPE_CLOAK],                              // chest
        16 => [INVTYPE_WEAPONMAINHAND, INVTYPE_WEAPON, INVTYPE_2HWEAPON],                   // mainhand
        17 => [INVTYPE_WEAPONOFFHAND, INVTYPE_WEAPON, INVTYPE_HOLDABLE, INVTYPE_SHIELD],    // offhand
        18 => [INVTYPE_RANGED, INVTYPE_THROWN, INVTYPE_RELIC],                              // ranged + relic
        19 => [INVTYPE_TABARD],                             // tabard
    );

    public static array $raidProgression = array( // statisticAchievement => relevantCriterium ; don't forget to enable this in /js/Profiler.js as well
        1361 => 5100, 1362 => 5101, 1363 => 5102, 1365 => 5104, 1366 => 5108, 1364 => 5110, 1369 => 5112, 1370 => 5113, 1371 => 5114, 1372 => 5117, 1373 => 5119, 1374 => 5120, 1375 => 7805, 1376 => 5122, 1377 => 5123,  // Naxxramas 10
        1367 => 5103, 1368 => 5111, 1378 => 5124, 1379 => 5125, 1380 => 5126, 1381 => 5127, 1382 => 5128, 1383 => 7806, 1384 => 5130, 1385 => 5131, 1386 => 5132, 1387 => 5133, 1388 => 5134, 1389 => 5135, 1390 => 5136, // Naxxramas 25
        2856 => 9938, 2857 => 9939, 2858 => 9940, 2859 => 9941, 2861 => 9943, 2865 => 9947, 2866 => 9948, 2868 => 9950, 2869 => 9951, 2870 => 9952, 2863 => 10558, 2864 => 10559, 2862 => 10560, 2867 => 10565, 2860 => 10580, // Ulduar 10
        2872 => 9954, 2873 => 9955, 2874 => 9956, 2884 => 9957, 2875 => 9959, 2879 => 9963, 2880 => 9964, 2882 => 9966, 2883 => 9967, 3236 => 10542, 3257 => 10561, 3256 => 10562, 3258 => 10563, 2881 => 10566, 2885 => 10581, // Ulduar 25
        1098 => 3271,   // Onyxia's Lair 10
        1756 => 13345,  // Onyxia's Lair 25
        4031 => 12230, 4034 => 12234, 4038 => 12238, 4042 => 12242, 4046 => 12246, // Trial of the Crusader 25 nh
        4029 => 12231, 4035 => 12235, 4039 => 12239, 4043 => 12243, 4047 => 12247, // Trial of the Crusader 25 hc
        4030 => 12229, 4033 => 12233, 4037 => 12237, 4041 => 12241, 4045 => 12245, // Trial of the Crusader 10 hc
        4028 => 12228, 4032 => 12232, 4036 => 12236, 4040 => 12240, 4044 => 12244, // Trial of the Crusader 10 nh
        4642 => 13091, 4656 => 13106, 4661 => 13111, 4664 => 13114, 4667 => 13117, 4670 => 13120, 4673 => 13123, 4676 => 13126, 4679 => 13129, 4682 => 13132, 4685 => 13135, 4688 => 13138, // Icecrown Citadel 25 hc
        4641 => 13092, 4655 => 13105, 4660 => 13109, 4663 => 13112, 4666 => 13115, 4669 => 13118, 4672 => 13121, 4675 => 13124, 4678 => 13127, 4681 => 13130, 4683 => 13133, 4687 => 13136, // Icecrown Citadel 25 nh
        4640 => 13090, 4654 => 13104, 4659 => 13110, 4662 => 13113, 4665 => 13116, 4668 => 13119, 4671 => 13122, 4674 => 13125, 4677 => 13128, 4680 => 13131, 4684 => 13134, 4686 => 13137, // Icecrown Citadel 10 hc
        4639 => 13089, 4643 => 13093, 4644 => 13094, 4645 => 13095, 4646 => 13096, 4647 => 13097, 4648 => 13098, 4649 => 13099, 4650 => 13100, 4651 => 13101, 4652 => 13102, 4653 => 13103, // Icecrown Citadel 10 nh
        4823 => 13467, // Ruby Sanctum 25 hc
        4820 => 13465, // Ruby Sanctum 25 nh
        4822 => 13468, // Ruby Sanctum 10 hc
        4821 => 13466, // Ruby Sanctum 10 nh
    );

    public static function getBuyoutForItem(int $itemId) : int
    {
        if (!$itemId)
            return 0;

        // try, when having filled char-DB at hand
        // return DB::Characters()->selectCell('SELECT SUM(a.buyoutprice) / SUM(ii.count) FROM auctionhouse a JOIN item_instance ii ON ii.guid = a.itemguid WHERE ii.itemEntry = ?d', $itemId);
        return 0;
    }

    public static function queueStart(?string &$msg = '') : bool
    {
        $queuePID = self::queueStatus();

        if ($queuePID)
        {
            $msg = 'queue already running';
            return true;
        }

        if (OS_WIN)                                         // here be gremlins! .. suggested was "start /B php prQueue" as background process. but that closes itself
            pclose(popen('start php prQueue --log=cache/profiling.log', 'r'));
        else
            exec('php prQueue --log=cache/profiling.log > /dev/null 2>/dev/null &');

        usleep(500000);
        if (self::queueStatus())
            return true;
        else
        {
            $msg = 'failed to start queue';
            return false;
        }
    }

    public static function queueStatus() : int
    {
        if (!file_exists(self::PID_FILE))
            return 0;

        $pid = file_get_contents(self::PID_FILE);
        $cmd = OS_WIN ? 'tasklist /NH /FO CSV /FI "PID eq %d"' : 'ps --no-headers p %d';

        exec(sprintf($cmd, $pid), $out);
        if ($out && stripos($out[0], $pid) !== false)
            return $pid;

        // have pidFile but no process with this pid
        self::queueFree();
        return 0;
    }

    public static function queueLock(int $pid) : bool
    {
        $queuePID = self::queueStatus();
        if ($queuePID && $queuePID != $pid)
        {
            trigger_error('pSync - another queue with PID #'.$queuePID.' is already running', E_USER_ERROR);
            return false;
        }

        // no queue running; create or overwrite pidFile
        $ok = false;
        if ($fh = fopen(self::PID_FILE, 'w'))
        {
            if (fwrite($fh, $pid))
                $ok = true;

            fclose($fh);
        }

        return $ok;
    }

    public static function queueFree() : void
    {
        unlink(self::PID_FILE);
    }

    public static function urlize(string $str, bool $allowLocales = false, bool $profile = false) : string
    {
        $search  = ['<',    '>',    ' / ', "'"];
        $replace = ['&lt;', '&gt;', '-',   '' ];
        $str = str_replace($search, $replace, $str);

        if ($profile)
        {
            $str = str_replace(['(', ')'], ['', ''], $str);
            $accents = array(
                "ß" => "ss",
                "á" => "a", "ä" => "a", "à" => "a", "â" => "a",
                "è" => "e", "ê" => "e", "é" => "e", "ë" => "e",
                "í" => "i", "î" => "i", "ì" => "i", "ï" => "i",
                "ñ" => "n",
                "ò" => "o", "ó" => "o", "ö" => "o", "ô" => "o",
                "ú" => "u", "ü" => "u", "û" => "u", "ù" => "u",
                "œ" => "oe",
                "Á" => "A", "Ä" => "A", "À" => "A", "Â" => "A",
                "È" => "E", "Ê" => "E", "É" => "E", "Ë" => "E",
                "Í" => "I", "Î" => "I", "Ì" => "I", "Ï" => "I",
                "Ñ" => "N",
                "Ò" => "O", "Ó" => "O", "Ö" => "O", "Ô" => "O",
                "Ú" => "U", "Ü" => "U", "Û" => "U", "Ù" => "U",
                "Œ" => "Oe"
            );
            $str = strtr($str, $accents);
        }

        $str = trim($str);

        if ($allowLocales)
            $str = str_replace(' ', '-', $str);
        else
            $str = preg_replace('/[^a-z0-9]/i', '-', $str);

        $str = str_replace('--', '-', $str);
        $str = str_replace('--', '-', $str);

        $str = rtrim($str, '-');
        $str = strtolower($str);

        return $str;
    }

    public static function getRealms() : array
    {
        if (!DB::isConnectable(DB_AUTH) || self::$realms)
            return self::$realms;

        $realms = DB::Auth()->select(
           'SELECT `id` AS ARRAY_KEY,
                   `name`,
                   CASE WHEN `timezone` BETWEEN  2 AND  5 THEN "us" # US, Oceanic, Latin America, Americas-Tournament
                        WHEN `timezone` BETWEEN  6 AND  7 THEN "kr" # KR, KR-Tournament
                        WHEN `timezone` BETWEEN  8 AND 13 THEN "eu" # GB, DE, FR, ES, RU, EU-Tournament
                        WHEN `timezone` BETWEEN 14 AND 15 THEN "tw" # TW, TW-Tournament
                        WHEN `timezone` BETWEEN 16 AND 25 THEN "cn" # CN, CN1-8, CN-Tournament
                        ELSE "dev" END AS "region",                 # 1: Dev, 26: Test, 27: Test Tournament, 28: QA, 30: Test2, 31+: misc
                   `allowedSecurityLevel` AS "access"
            FROM   `realmlist`
            WHERE  `gamebuild` = ?d',
            WOW_BUILD
        );

        if (!$realms)
            return [];

        foreach ($realms as $rId => $rData)
        {
            // realm in db but no connection info set
            if (!DB::isConnectable(DB_CHARACTERS . $rId))
                continue;

            // filter by access level
            if ($rData['access'] == SEC_ADMINISTRATOR   && (CLI || User::isInGroup(U_GROUP_DEV | U_GROUP_ADMIN)))
                $rData['access'] = U_GROUP_DEV | U_GROUP_ADMIN;
            else if ($rData['access'] == SEC_GAMEMASTER && (CLI || User::isInGroup(U_GROUP_DEV | U_GROUP_ADMIN | U_GROUP_MOD)))
                $rData['access'] = U_GROUP_DEV | U_GROUP_ADMIN | U_GROUP_MOD;
            else if ($rData['access'] == SEC_MODERATOR  && (CLI || User::isInGroup(U_GROUP_DEV | U_GROUP_ADMIN | U_GROUP_MOD | U_GROUP_BUREAU)))
                $rData['access'] = U_GROUP_DEV | U_GROUP_ADMIN | U_GROUP_MOD | U_GROUP_BUREAU;
            else if ($rData['access'] > SEC_PLAYER && !CLI)
                continue;

            // filter dev realms
            if ($rData['region'] === 'dev')
            {
                if (CLI || User::isInGroup(U_GROUP_DEV | U_GROUP_ADMIN))
                    $rData['access'] = U_GROUP_DEV | U_GROUP_ADMIN;
                else
                    continue;
            }

            self::$realms[$rId] = $rData;
        }

        return self::$realms;
    }

    public static function getRegions() : array
    {
        self::getRealms();

        // sort depends on encountered order in `logon`.`realmlist`. Is that a problem?
        return array_unique(array_column(self::$realms, 'region'));
    }

    private static function queueInsert(int $realmId, int $guid, int $type, int $localId) : void
    {
        if ($rData = DB::Aowow()->selectRow('SELECT `requestTime` AS "time", `status` FROM ?_profiler_sync WHERE `realm` = ?d AND `realmGUID` = ?d AND `type` = ?d AND `typeId` = ?d AND `status` <> ?d', $realmId, $guid, $type, $localId, PR_QUEUE_STATUS_WORKING))
        {
            // not on already scheduled - recalc time and set status to PR_QUEUE_STATUS_WAITING
            if ($rData['status'] != PR_QUEUE_STATUS_WAITING)
            {
                $newTime = Cfg::get('DEBUG') ? time() : max($rData['time'] + Cfg::get('PROFILER_RESYNC_DELAY'), time());
                DB::Aowow()->query('UPDATE ?_profiler_sync SET `requestTime` = ?d, `status` = ?d, `errorCode` = 0 WHERE `realm` = ?d AND `realmGUID` = ?d AND `type` = ?d AND `typeId` = ?d', $newTime, PR_QUEUE_STATUS_WAITING, $realmId, $guid, $type, $localId);
            }
        }
        else
            DB::Aowow()->query('REPLACE INTO ?_profiler_sync (`realm`, `realmGUID`, `type`, `typeId`, `requestTime`, `status`, `errorCode`) VALUES (?d, ?d, ?d, ?d, UNIX_TIMESTAMP(), ?d, 0)', $realmId, $guid, $type, $localId, PR_QUEUE_STATUS_WAITING);
    }

    public static function scheduleResync(int $type, int $realmId, int $guid) : int
    {
        $newId = 0;

        switch ($type)
        {
            case Type::PROFILE:
                if ($newId = DB::Aowow()->selectCell('SELECT `id` FROM ?_profiler_profiles WHERE `realm` = ?d AND `realmGUID` = ?d', $realmId, $guid))
                    self::queueInsert($realmId, $guid, Type::PROFILE, $newId);

                break;
            case Type::GUILD:
                if ($newId = DB::Aowow()->selectCell('SELECT `id` FROM ?_profiler_guild WHERE `realm` = ?d AND `realmGUID` = ?d', $realmId, $guid))
                    self::queueInsert($realmId, $guid, Type::GUILD, $newId);

                break;
            case Type::ARENA_TEAM:
                if ($newId = DB::Aowow()->selectCell('SELECT `id` FROM ?_profiler_arena_team WHERE `realm` = ?d AND `realmGUID` = ?d', $realmId, $guid))
                    self::queueInsert($realmId, $guid, Type::ARENA_TEAM, $newId);

                break;
            default:
                trigger_error('scheduling resync for unknown type #'.$type.' omiting..', E_USER_WARNING);
                return 0;
        }

        if (!$newId)
            trigger_error('Profiler::scheduleResync() - tried to resync type #'.$type.' guid #'.$guid.' from realm #'.$realmId.' without preloaded data', E_USER_ERROR);
        else if (!self::queueStart($msg))
            trigger_error('Profiler::scheduleResync() - '.$msg, E_USER_ERROR);

        return $newId;
    }

    /*  return
            <status object> [
                nQueueProcesses,
                [statusCode, timeToRefresh, curQueuePos, errorCode, nResyncs],
                [<anotherStatus>]
                ...
            ]

            statusCode:
                0: end the request
                1: waiting
                2: working...
                3: ready; click to view
                4: error / retry
            timeToRefresh:
                msec till the client may ask for another update
            curQueuePos:
                position in the queue
            errorCode:
                0: unk error
                1: char does not exist
                2: armory gone
            nResyncs:
                ??? .. if !nResyncs && !timeToRefresh prints "Adding to queue..." but will not ping the server for updates...?
    */
    public static function resyncStatus(int $type, array $subjectGUIDs) : string
    {
        $response = [Cfg::get('PROFILER_ENABLE') ? 2 : 0];  // in theory you could have multiple queues; used as divisor in wait time estimation: (15 / x) + 2
        if (!$subjectGUIDs)
            $response[] = [PR_QUEUE_STATUS_ENDED, 0, 0, PR_QUEUE_ERROR_CHAR];
        else
        {
            // error out all profiles with status WORKING, that are older than 60sec
            DB::Aowow()->query('UPDATE ?_profiler_sync SET `status` = ?d, `errorCode` = ?d WHERE `status` = ?d AND `requestTime` < ?d', PR_QUEUE_STATUS_ERROR, PR_QUEUE_ERROR_UNK, PR_QUEUE_STATUS_WORKING, time() - MINUTE);

            $subjectStatus = DB::Aowow()->select('SELECT `typeId` AS ARRAY_KEY, `status`, `realm`, `errorCode`, `requestTime` FROM ?_profiler_sync WHERE `type` = ?d AND `typeId` IN (?a)', $type, $subjectGUIDs);
            $queue         = DB::Aowow()->selectCol('SELECT CONCAT(`type`, ":", `typeId`) FROM ?_profiler_sync WHERE `status` = ?d AND `requestTime` < UNIX_TIMESTAMP() ORDER BY `requestTime` ASC', PR_QUEUE_STATUS_WAITING);
            foreach ($subjectGUIDs as $guid)
            {
                if (empty($subjectStatus[$guid]))           // whelp, thats some error..
                    $response[] = [PR_QUEUE_STATUS_ERROR, 0, 0, PR_QUEUE_ERROR_UNK];
                else if ($subjectStatus[$guid]['status'] == PR_QUEUE_STATUS_ERROR)
                    $response[] = [PR_QUEUE_STATUS_ERROR, 0, 0, $subjectStatus[$guid]['errorCode']];
                else if ($subjectStatus[$guid]['requestTime'] > time())
                    $response[] = [PR_QUEUE_STATUS_READY, 0, 0, 0];
                else
                    $response[] = array(
                        $subjectStatus[$guid]['status'],
                        $subjectStatus[$guid]['status'] != PR_QUEUE_STATUS_READY ? Cfg::get('PROFILER_RESYNC_PING') : 0,
                        array_search($type.':'.$guid, $queue) + 1,
                        0,
                        1                                   // nResyncs - unsure about this one
                    );
            }
        }

        return Util::toJSON($response);
    }

    public static function getCharFromRealm(int $realmId, int $charGuid) : bool
    {
        $char = DB::Characters($realmId)->selectRow('SELECT c.* FROM characters c WHERE c.`guid` = ?d', $charGuid);
        if (!$char)
            return false;

        if (!$char['name'])
        {
            trigger_error('char #'.$charGuid.' on realm #'.$realmId.' has empty name. skipping...', E_USER_WARNING);
            return false;
        }

        // reminder: this query should not fail: a placeholder entry is created as soon as a char listview is created or profile detail page is called
        $profile = DB::Aowow()->selectRow('SELECT `id`, `lastupdated` FROM ?_profiler_profiles WHERE `realm` = ?d AND `realmGUID` = ?d', $realmId, $char['guid']);
        if (!$profile)
            return false;                                   // well ... it failed

        $profileId = $profile['id'];

        CLI::write('fetching char '.$char['name'].' (#'.$charGuid.') from realm #'.$realmId);

        if (!$char['online'] && $char['logout_time'] <= $profile['lastupdated'])
        {
            DB::Aowow()->query('UPDATE ?_profiler_profiles SET `lastupdated` = ?d WHERE `id` = ?d', time(), $profileId);
            CLI::write('char did not log in since last update. skipping...');
            return true;
        }

        CLI::write('writing...');

        $ra = ChrRace::from($char['race']);
        $cl = ChrClass::from($char['class']);


        /*************/
        /* equipment */
        /*************/

        /* enchantment-Indizes
         *  0: permEnchant
         *  3: tempEnchant
         *  6: gem1
         *  9: gem2
         * 12: gem3
         * 15: socketBonus [not used]
         * 18: extraSocket [only check existance]
         * 21 - 30: randomProp enchantments
         */


        DB::Aowow()->query('DELETE FROM ?_profiler_items WHERE `id` = ?d', $profileId);
        $items    = DB::Characters($realmId)->select('SELECT ci.`slot` AS ARRAY_KEY, ii.`itemEntry`, ii.`enchantments`, ii.`randomPropertyId` FROM character_inventory ci JOIN item_instance ii ON ci.`item` = ii.`guid` WHERE ci.`guid` = ?d AND `bag` = 0 AND `slot` BETWEEN 0 AND 18', $char['guid']);

        $gemItems = [];
        $permEnch = [];
        $mhItem   = 0;
        $ohItem   = 0;

        foreach ($items as $slot => $item)
        {
            $ench  = explode(' ', $item['enchantments']);
            $gEnch = [];
            foreach ([6, 9, 12] as $idx)
                if ($ench[$idx])
                    $gEnch[$idx] = $ench[$idx];

            if ($gEnch)
            {
                $gi = DB::Aowow()->selectCol('SELECT `gemEnchantmentId` AS ARRAY_KEY, `id` FROM ?_items WHERE `class` = ?d AND `gemEnchantmentId` IN (?a)', ITEM_CLASS_GEM, $gEnch);
                foreach ($gEnch as $eId)
                {
                    if (isset($gemItems[$eId]))
                        $gemItems[$eId][1]++;
                    else
                        $gemItems[$eId] = [$gi[$eId], 1];
                }
            }

            if ($slot + 1 == 16)
                $mhItem = $item['itemEntry'];
            if ($slot + 1 == 17)
                $ohItem = $item['itemEntry'];

            if ($ench[0])
                $permEnch[$slot] = $ench[0];

            $data = array(
                'id'          => $profileId,
                'slot'        => $slot + 1,
                'item'        => $item['itemEntry'],
                'subItem'     => $item['randomPropertyId'],
                'permEnchant' => $ench[0],
                'tempEnchant' => $ench[3],
                'extraSocket' => (int)!!$ench[18],
                'gem1'        => isset($gemItems[$ench[6]])  ? $gemItems[$ench[6]][0]  : 0,
                'gem2'        => isset($gemItems[$ench[9]])  ? $gemItems[$ench[9]][0]  : 0,
                'gem3'        => isset($gemItems[$ench[12]]) ? $gemItems[$ench[12]][0] : 0,
                'gem4'        => 0                  // serverside items cant have more than 3 sockets. (custom profile thing)
            );

            DB::Aowow()->query('INSERT INTO ?_profiler_items (?#) VALUES (?a)', array_keys($data), array_values($data));
        }

        CLI::write(' ..inventory');


        /**************/
        /* basic info */
        /**************/

        $data = array(
            'realm'             => $realmId,
            'realmGUID'         => $charGuid,
            'name'              => $char['name'],
            'renameItr'         => 0,
            'race'              => $char['race'],
            'class'             => $char['class'],
            'level'             => $char['level'],
            'gender'            => $char['gender'],
            'skincolor'         => $char['skin'],
            'facetype'          => $char['face'],           // maybe features
            'hairstyle'         => $char['hairStyle'],
            'haircolor'         => $char['hairColor'],
            'features'          => $char['facialStyle'],    // maybe facetype
            'title'             => $char['chosenTitle'] ? DB::Aowow()->selectCell('SELECT `id` FROM ?_titles WHERE `bitIdx` = ?d', $char['chosenTitle']) : 0,
            'playedtime'        => $char['totaltime'],
            'nomodelMask'       => ($char['playerFlags'] & 0x400 ? (1 << SLOT_HEAD) : 0) | ($char['playerFlags'] & 0x800 ? (1 << SLOT_BACK) : 0),
            'talenttree1'       => 0,
            'talenttree2'       => 0,
            'talenttree3'       => 0,
            'talentbuild1'      => '',
            'talentbuild2'      => '',
            'glyphs1'           => '',
            'glyphs2'           => '',
            'activespec'        => $char['activeTalentGroup'],
            'guild'             => null,
            'guildRank'         => null,
            'gearscore'         => 0,
            'achievementpoints' => 0
        );

        // char is flagged for rename
        if ($char['at_login'] & 0x1)
        {
            $ri = DB::Aowow()->selectCell('SELECT MAX(`renameItr`) FROM ?_profiler_profiles WHERE `realm` = ?d AND `custom` = 0 AND `name` = ?', $realmId, $char['name']);
            $data['renameItr'] = $ri ? ++$ri : 1;
        }


        /********************/
        /* talents + glyphs */
        /********************/

        $t = DB::Characters($realmId)->selectCol('SELECT `talentGroup` AS ARRAY_KEY, `spell` AS ARRAY_KEY2, `spell` FROM character_talent WHERE `guid` = ?d', $char['guid']);
        $g = DB::Characters($realmId)->select('SELECT `talentGroup` AS ARRAY_KEY, `glyph1` AS "g1", `glyph2` AS "g4", `glyph3` AS "g5", `glyph4` AS "g2", `glyph5` AS "g3", `glyph6` AS "g6" FROM character_glyphs WHERE `guid` = ?d', $char['guid']);
        for ($i = 0; $i < 2; $i++)
        {
            // talents
            for ($j = 0; $j < 3; $j++)
            {
                $_ = DB::Aowow()->selectCol('SELECT `spell` AS ARRAY_KEY, MAX(IF(`spell` IN (?a), `rank`, 0)) FROM ?_talents WHERE `class` = ?d AND `tab` = ?d GROUP BY `id` ORDER BY `row`, `col` ASC', $t[$i] ?? [0], $cl->value, $j);
                $data['talentbuild'.($i + 1)] .= implode('', $_);
                if ($data['activespec'] == $i)
                    $data['talenttree'.($j + 1)] = array_sum($_);
            }

            // glyphs
            if (isset($g[$i]))
            {
                $gProps = [];
                for ($j = 1; $j <= 6; $j++)
                    if ($g[$i]['g'.$j])
                        $gProps[$j] = $g[$i]['g'.$j];

                if ($gProps)
                {
                    $gItems = DB::Aowow()->selectCol(
                       'SELECT i.`id`
                        FROM ?_glyphproperties gp
                        JOIN ?_spell s ON s.`effect1MiscValue` = gp.`id` AND s.`effect1Id` = ?d
                        JOIN ?_items i ON i.`class` = ?d AND i.`spellId1` = s.`id` AND (i.`cuFlags` & ?d) = 0
                        WHERE gp.`id` IN (?a)',
                        SPELL_EFFECT_APPLY_GLYPH, ITEM_CLASS_GLYPH, CUSTOM_DISABLED | CUSTOM_UNAVAILABLE | CUSTOM_EXCLUDE_FOR_LISTVIEW, $gProps
                    );

                    if ($gItems)
                        $data['glyphs'.($i + 1)] = implode(':', $gItems);
                }
            }
        }

        $t = array(
            'spent' => [$data['talenttree1'], $data['talenttree2'], $data['talenttree3']],
            'spec'  => 0
        );
        if ($t['spent'][0] > $t['spent'][1] && $t['spent'][0] > $t['spent'][2])
            $t['spec'] = 1;
        else if ($t['spent'][1] > $t['spent'][0] && $t['spent'][1] > $t['spent'][2])
            $t['spec'] = 2;
        else if ($t['spent'][2] > $t['spent'][1] && $t['spent'][2] > $t['spent'][0])
            $t['spec'] = 3;

        // calc gearscore
        if ($items)
            $data['gearscore'] += (new ItemList(array(['id', array_column($items, 'itemEntry')])))->getScoreTotal($data['class'], $t, $mhItem, $ohItem);

        if ($gemItems)
        {
            $gemScores = new ItemList(array(['id', array_column($gemItems, 0)]));
            foreach ($gemItems as [$itemId, $mult])
                if (isset($gemScores->json[$itemId]['gearscore']))
                    $data['gearscore'] += $gemScores->json[$itemId]['gearscore'] * $mult;
        }

        if ($permEnch)                                      // fuck this shit .. we are guestimating this!
        {
            // enchantId => multiple spells => multiple items with varying itemlevels, quality, whatevs
            // cant reasonably get to the castItem from enchantId and slot

            $profSpec = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `skillLevel` AS "1", `skillLine` AS "0" FROM ?_itemenchantment WHERE `id` IN (?a)', $permEnch);
            foreach ($permEnch as $slot => $eId)
            {
                if (!isset($profSpec[$eId]))
                {
                    trigger_error('char #'.$charGuid.' on realm #'.$realmId.' has item in slot #'.$slot.' with invalid perm enchantment #'.CLI::bold($eId), E_USER_WARNING);
                    continue;
                }

                if ($x = Util::getEnchantmentScore(0, 0, !!$profSpec[$eId][1], $eId))
                    $data['gearscore'] += $x;
                else if ($profSpec[$eId][0] != 776)         // not runeforging
                    $data['gearscore'] += 17;               // assume high quality enchantment for unknown cases
            }
        }

        $data['lastupdated'] = time();

        CLI::write(' ..basic info');


        /***************/
        /* hunter pets */
        /***************/

        if ($cl == ChrClass::HUNTER)
        {
            DB::Aowow()->query('DELETE FROM ?_profiler_pets WHERE `owner` = ?d', $profileId);
            $pets = DB::Characters($realmId)->select('SELECT `id` AS ARRAY_KEY, `entry`, `modelId`, `name` FROM character_pet WHERE `owner` = ?d', $charGuid);
            foreach ($pets as $petGuid => $petData)
            {
                $petSpells = DB::Characters($realmId)->selectCol('SELECT `spell` FROM pet_spell WHERE `guid` = ?d', $petGuid);
                $morePet   = DB::Aowow()->selectRow(
                   'SELECT    IFNULL(c3.`id`, IFNULL(c2.`id`, IFNULL(c1.`id`, c.`id`))) AS "entry", p.`type`, c.`family`
                    FROM      ?_pet p
                    JOIN      ?_creature c ON c.`family` = p.`id`
                    LEFT JOIN ?_creature c1 ON c1.`difficultyEntry1` = c.`id`
                    LEFT JOIN ?_creature c2 ON c2.`difficultyEntry2` = c.`id`
                    LEFT JOIN ?_creature c3 ON c3.`difficultyEntry3` = c.`id`
                    WHERE     c.`id` = ?d',
                    $petData['entry']
                );

                if (!$morePet)
                {
                    trigger_error('char #'.$charGuid.' on realm #'.$realmId.' owns pet #'.$petGuid.' (creature entry: #'.$petData['entry'].') without pet family. skipping...', E_USER_WARNING);
                    continue;
                }

                $_ = DB::Aowow()->selectCol(
                   'SELECT    IFNULL(t2.`rank`, 0)
                    FROM      ?_talents t1
                    LEFT JOIN (SELECT `id`, `rank` FROM ?_talents WHERE `spell` IN (?a)) t2 ON t2.`id` = t1.`id`
                    WHERE     `class` = 0 AND `petTypeMask` = ?d
                    GROUP BY  t1.`id`
                    ORDER BY  t1.`row`, t1.`col`, t1.`id` ASC',
                    $petSpells ?: [0], 1 << $morePet['type']
                );

                $pet = array(
                    'id'        => $petGuid,
                    'owner'     => $profileId,
                    'name'      => $petData['name'],
                    'family'    => $morePet['family'],
                    'npc'       => $morePet['entry'],
                    'displayId' => $petData['modelId'],
                    'talents'   => implode('', $_)
                );

                DB::Aowow()->query('INSERT INTO ?_profiler_pets (?#) VALUES (?a)', array_keys($pet), array_values($pet));
            }

            CLI::write(' ..hunter pets');
        }


        /*******************/
        /* completion data */
        /*******************/

        // done quests //

        DB::Aowow()->query('DELETE FROM ?_profiler_completion_quests WHERE `id` = ?d', $profileId);

        if ($quests = DB::Characters($realmId)->select('SELECT ?d AS `id`, `quest` AS `questId` FROM character_queststatus_rewarded WHERE `guid` = ?d', $profileId, $char['guid']))
            foreach (Util::createSqlBatchInsert($quests) as $q)
                DB::Aowow()->query('INSERT INTO ?_profiler_completion_quests (?#) VALUES '.$q, array_keys($quests[0]));

        CLI::write(' ..quests');


        // known skills (professions only) //

        DB::Aowow()->query('DELETE FROM ?_profiler_completion_skills WHERE `id` = ?d', $profileId);

        $skAllowed = DB::Aowow()->selectCol('SELECT `id` FROM ?_skillline WHERE `typeCat` IN (9, 11) AND (`cuFlags` & ?d) = 0', CUSTOM_EXCLUDE_FOR_LISTVIEW);
        $skills    = DB::Characters($realmId)->select('SELECT ?d AS `id`, `skill` AS `skillId`, `value`, `max` FROM character_skills WHERE `guid` = ?d AND `skill` IN (?a)', $profileId, $char['guid'], $skAllowed);
        $racials   = DB::Aowow()->select('SELECT `effect1MiscValue` AS ARRAY_KEY, `effect1DieSides` + `effect1BasePoints` AS qty, `reqRaceMask`, `reqClassMask` FROM ?_spell WHERE `typeCat` = -4 AND `effect1Id` = ?d AND `effect1AuraId` = ?d', SPELL_EFFECT_APPLY_AURA, SPELL_AURA_MOD_SKILL_TALENT);

        foreach ($skills as &$sk)                           // apply racial profession bonuses
        {
            if (!isset($racials[$sk['skillId']]))
                continue;

            $r = $racials[$sk['skillId']];
            if ($ra->matches($r['reqRaceMask']) && $cl->matches($r['reqClassMask']))
            {
                $sk['value'] += $r['qty'];
                $sk['max']   += $r['qty'];
            }
        }
        unset($sk);

        if ($skills)
            foreach (Util::createSqlBatchInsert($skills) as $sk)
                DB::Aowow()->query('INSERT INTO ?_profiler_completion_skills (?#) VALUES '.$sk, array_keys($skills[0]));

        CLI::write(' ..professions');


        // reputation //

        DB::Aowow()->query('DELETE FROM ?_profiler_completion_reputation WHERE `id` = ?d', $profileId);

        // get base values for this race/class
        $reputation = [];
        $baseRep    = DB::Aowow()->selectCol(
           'SELECT `id` AS ARRAY_KEY, `baseRepValue1` FROM ?_factions WHERE `baseRepValue1` AND (`baseRepRaceMask1` & ?d OR (`baseRepClassMask1` AND NOT `baseRepRaceMask1`)) AND ((`baseRepClassMask1` & ?d) OR NOT `baseRepClassMask1`) UNION
            SELECT `id` AS ARRAY_KEY, `baseRepValue2` FROM ?_factions WHERE `baseRepValue2` AND (`baseRepRaceMask2` & ?d OR (`baseRepClassMask2` AND NOT `baseRepRaceMask2`)) AND ((`baseRepClassMask2` & ?d) OR NOT `baseRepClassMask2`) UNION
            SELECT `id` AS ARRAY_KEY, `baseRepValue3` FROM ?_factions WHERE `baseRepValue3` AND (`baseRepRaceMask3` & ?d OR (`baseRepClassMask3` AND NOT `baseRepRaceMask3`)) AND ((`baseRepClassMask3` & ?d) OR NOT `baseRepClassMask3`) UNION
            SELECT `id` AS ARRAY_KEY, `baseRepValue4` FROM ?_factions WHERE `baseRepValue4` AND (`baseRepRaceMask4` & ?d OR (`baseRepClassMask4` AND NOT `baseRepRaceMask4`)) AND ((`baseRepClassMask4` & ?d) OR NOT `baseRepClassMask4`)',
            $ra->toMask(), $cl->toMask(), $ra->toMask(), $cl->toMask(), $ra->toMask(), $cl->toMask(), $ra->toMask(), $cl->toMask()
        );

        if ($reputation = DB::Characters($realmId)->select('SELECT ?d AS `id`, `faction` AS `factionId`, `standing` FROM character_reputation WHERE `guid` = ?d AND (`flags` & 0x4) = 0', $profileId, $char['guid']))
        {
            // merge back base values for encountered factions
            foreach ($reputation as &$set)
            {
                if (empty($baseRep[$set['factionId']]))
                    continue;

                $set['standing'] += $baseRep[$set['factionId']];
                unset($baseRep[$set['factionId']]);
            }
        }

        // insert base values for not yet encountered factions
        foreach ($baseRep as $id => $val)
            $reputation[] = array(
                'id'        => $profileId,
                'factionId' => $id,
                'standing'  => $val
            );

        foreach (Util::createSqlBatchInsert($reputation) as $rep)
            DB::Aowow()->query('INSERT INTO ?_profiler_completion_reputation (?#) VALUES '.$rep, array_keys($reputation[0]));

        CLI::write(' ..reputation');


        // known titles //

        DB::Aowow()->query('DELETE FROM ?_profiler_completion_titles WHERE `id` = ?d', $profileId);

        if ($indizes = Util::indexBitBlob($char['knownTitles']))
            DB::Aowow()->query('INSERT INTO ?_profiler_completion_titles SELECT ?d, `id` FROM ?_titles WHERE `bitIdx` IN (?a)', $profileId, $indizes);

        CLI::write(' ..titles');


        // achievements //

        DB::Aowow()->query('DELETE FROM ?_profiler_completion_achievements WHERE `id` = ?d', $profileId);

        if ($achievements = DB::Characters($realmId)->select('SELECT ?d AS id, `achievement` AS `achievementId`, `date` FROM character_achievement WHERE `guid` = ?d', $profileId, $char['guid']))
        {
            foreach (Util::createSqlBatchInsert($achievements) as $a)
                DB::Aowow()->query('INSERT INTO ?_profiler_completion_achievements (?#) VALUES '.$a, array_keys($achievements[0]));

            $data['achievementpoints'] = DB::Aowow()->selectCell('SELECT SUM(`points`) FROM ?_achievement WHERE `id` IN (?a) AND (`flags` & ?d) = 0', array_column($achievements, 'achievementId'), ACHIEVEMENT_FLAG_COUNTER);
        }

        CLI::write(' ..achievements');


        // raid progression //

        DB::Aowow()->query('DELETE FROM ?_profiler_completion_statistics WHERE `id` = ?d', $profileId);

        if ($progress = DB::Characters($realmId)->select('SELECT ?d AS `id`, `criteria` AS `achievementId`, `date`, `counter` FROM character_achievement_progress WHERE `guid` = ?d AND `criteria` IN (?a)', $profileId, $char['guid'], self::$raidProgression))
        {
            array_walk($progress, function (&$val) { $val['achievementId'] = array_search($val['achievementId'], self::$raidProgression); });
            foreach (Util::createSqlBatchInsert($progress) as $p)
                DB::Aowow()->query('INSERT INTO ?_profiler_completion_statistics (?#) VALUES '.$p, array_keys($progress[0]));
        }

        CLI::write(' ..raid progression');


        // known spells //

        DB::Aowow()->query('DELETE FROM ?_profiler_completion_spells WHERE `id` = ?d', $profileId);

        if ($spells = DB::Characters($realmId)->select('SELECT ?d AS `id`, `spell` AS `spellId` FROM character_spell WHERE `guid` = ?d AND `disabled` = 0', $profileId, $char['guid']))
            foreach (Util::createSqlBatchInsert($spells) as $s)
                DB::Aowow()->query('INSERT INTO ?_profiler_completion_spells (?#) VALUES '.$s, array_keys($spells[0]));

        // apply auto-learned spells from trade skills
        if ($skills)
            DB::Aowow()->query(
               'INSERT INTO ?_profiler_completion_spells
                SELECT      ?d, `spellId`
                FROM        ?_skilllineability
                WHERE       `skillLineId` IN (?a) AND
                            `acquireMethod` = 1 AND
                            (`reqRaceMask`  = 0 OR `reqRaceMask`  & ?d) AND
                            (`reqClassMask` = 0 OR `reqClassMask` & ?d)',
                $profileId,
                array_column($skills, 'skillId'),
                $ra->toMask(),
                $cl->toMask()
            );

        CLI::write(' ..known spells (vanity pets & mounts)');


        /****************/
        /* related data */
        /****************/

        // guilds
        if ($guild = DB::Characters($realmId)->selectRow('SELECT g.`name` AS `name`, g.`guildid` AS `id`, gm.`rank` FROM guild_member gm JOIN guild g ON g.`guildid` = gm.`guildid` WHERE gm.`guid` = ?d', $char['guid']))
        {
            $guildId = 0;
            if (!($guildId = DB::Aowow()->selectCell('SELECT id FROM ?_profiler_guild WHERE realm = ?d AND realmGUID = ?d', $realmId, $guild['id'])))
            {
                $gData = array(                                  // only most basic data
                    'realm'     => $realmId,
                    'realmGUID' => $guild['id'],
                    'name'      => $guild['name'],
                    'nameUrl'   => self::urlize($guild['name']),
                    'stub'      => 1
                );

                $guildId = DB::Aowow()->query('INSERT IGNORE INTO ?_profiler_guild (?#) VALUES (?a)', array_keys($gData), array_values($gData));
            }

            $data['guild']     = $guildId;
            $data['guildRank'] = $guild['rank'];
        }

        CLI::write(' ..basic guild data');


        // arena teams
        $teams = DB::Characters($realmId)->select('SELECT at.`arenaTeamId` AS ARRAY_KEY, at.`name`, at.`type`, IF(at.`captainGuid` = atm.`guid`, 1, 0) AS `captain`, atm.* FROM arena_team at JOIN arena_team_member atm ON atm.`arenaTeamId` = at.`arenaTeamId` WHERE atm.`guid` = ?d', $char['guid']);
        foreach ($teams as $rGuid => $t)
        {
            $teamId = 0;
            if (!($teamId = DB::Aowow()->selectCell('SELECT `id` FROM ?_profiler_arena_team WHERE `realm` = ?d AND `realmGUID` = ?d', $realmId, $rGuid)))
            {
                $team = array(                                  // only most basic data
                    'realm'     => $realmId,
                    'realmGUID' => $rGuid,
                    'name'      => $t['name'],
                    'nameUrl'   => self::urlize($t['name']),
                    'type'      => $t['type'],
                    'stub'      => 1
                );

                $teamId = DB::Aowow()->query('INSERT IGNORE INTO ?_profiler_arena_team (?#) VALUES (?a)', array_keys($team), array_values($team));
            }

            $member = array(
                'arenaTeamId'    => $teamId,
                'profileId'      => $profileId,
                'captain'        => $t['captain'],
                'weekGames'      => $t['weekGames'],
                'weekWins'       => $t['weekWins'],
                'seasonGames'    => $t['seasonGames'],
                'seasonWins'     => $t['seasonWins'],
                'personalRating' => $t['personalRating']
            );

            // Delete members from other teams of the same type
            DB::Aowow()->query(
               'DELETE atm
                FROM   ?_profiler_arena_team_member atm
                JOIN   ?_profiler_arena_team at ON atm.`arenaTeamId` = at.`id` AND at.`type` = ?d
                WHERE  atm.`profileId` = ?d AND atm.`arenaTeamId` <> ?d',
                $t['type'], $profileId, $teamId
            );

            DB::Aowow()->query('INSERT INTO ?_profiler_arena_team_member (?#) VALUES (?a) ON DUPLICATE KEY UPDATE ?a', array_keys($member), array_values($member), array_slice($member, 2));
        }

        CLI::write(' ..associated arena teams');

        /*********************/
        /* mark char as done */
        /*********************/

        if (DB::Aowow()->query('UPDATE ?_profiler_profiles SET ?a WHERE `realm` = ?d AND `realmGUID` = ?d', $data, $realmId, $charGuid) !== null)
            DB::Aowow()->query('UPDATE ?_profiler_profiles SET `stub` = 0 WHERE `id` = ?d', $profileId);

        return true;
    }

    public static function getGuildFromRealm(int $realmId, int $guildGuid) : bool
    {
        $guild = DB::Characters($realmId)->selectRow('SELECT `guildId`, `name`, `createDate`, `info`, `backgroundColor`, `emblemStyle`, `emblemColor`, `borderStyle`, `borderColor` FROM guild WHERE `guildId` = ?d', $guildGuid);
        if (!$guild)
            return false;

        if (!$guild['name'])
        {
            trigger_error('guild #'.$guildGuid.' on realm #'.$realmId.' has empty name. skipping...', E_USER_WARNING);
            return false;
        }

        // reminder: this query should not fail: a placeholder entry is created as soon as a team listview is created or team detail page is called
        $guildId = DB::Aowow()->selectCell('SELECT `id` FROM ?_profiler_guild WHERE `realm` = ?d AND `realmGUID` = ?d', $realmId, $guild['guildId']);

        CLI::write('fetching guild #'.$guildGuid.' from realm #'.$realmId);
        CLI::write('writing...');


        /**************/
        /* Guild Data */
        /**************/

        unset($guild['guildId']);
        $guild['nameUrl'] = self::urlize($guild['name']);

        DB::Aowow()->query('UPDATE ?_profiler_guild SET ?a WHERE `realm` = ?d AND `realmGUID` = ?d', $guild, $realmId, $guildGuid);

        // ranks
        DB::Aowow()->query('DELETE FROM ?_profiler_guild_rank WHERE `guildId` = ?d', $guildId);
        if ($ranks = DB::Characters($realmId)->select('SELECT ?d AS `guildId`, `rid` AS "rank", `rname` AS "name" FROM guild_rank WHERE `guildid` = ?d', $guildId, $guildGuid))
            foreach (Util::createSqlBatchInsert($ranks) as $r)
                DB::Aowow()->query('INSERT INTO ?_profiler_guild_rank (?#) VALUES '.$r, array_keys(reset($ranks)));

        CLI::write(' ..guild data');


        /***************/
        /* Member Data */
        /***************/

        $conditions = array(
            ['g.guildid', $guildGuid],
            ['deleteInfos_Account', null],
            ['level', MAX_LEVEL, '<='],                     // prevents JS errors
            [['extra_flags', self::CHAR_GMFLAGS, '&'], 0]   // not a staff char
        );

        // this here should all happen within ProfileList
        $members = new RemoteProfileList($conditions, ['sv' => $realmId]);
        if (!$members->error)
            $members->initializeLocalEntries();
        else
            return false;

        CLI::write(' ..guild members');


        /*********************/
        /* mark guild as done */
        /*********************/

        DB::Aowow()->query('UPDATE ?_profiler_guild SET `stub` = 0 WHERE `id` = ?d', $guildId);

        return true;
    }

    public static function getArenaTeamFromRealm(int $realmId, int $teamGuid) : bool
    {
        $team = DB::Characters($realmId)->selectRow('SELECT `arenaTeamId`, `name`, `type`, `captainGuid`, `rating`, `seasonGames`, `seasonWins`, `weekGames`, `weekWins`, `rank`, `backgroundColor`, `emblemStyle`, `emblemColor`, `borderStyle`, `borderColor` FROM arena_team WHERE `arenaTeamId` = ?d', $teamGuid);
        if (!$team)
            return false;

        if (!$team['name'])
        {
            trigger_error('arena team #'.$teamGuid.' on realm #'.$realmId.' has empty name. skipping...', E_USER_WARNING);
            return false;
        }

        // reminder: this query should not fail: a placeholder entry is created as soon as a team listview is created or team detail page is called
        $teamId = DB::Aowow()->selectCell('SELECT `id` FROM ?_profiler_arena_team WHERE `realm` = ?d AND `realmGUID` = ?d', $realmId, $team['arenaTeamId']);

        CLI::write('fetching arena team #'.$teamGuid.' from realm #'.$realmId);
        CLI::write('writing...');


        /*************/
        /* Team Data */
        /*************/

        $captain = $team['captainGuid'];
        unset($team['captainGuid']);
        unset($team['arenaTeamId']);
        $team['nameUrl'] = self::urlize($team['name']);

        DB::Aowow()->query('UPDATE ?_profiler_arena_team SET ?a WHERE `realm` = ?d AND `realmGUID` = ?d', $team, $realmId, $teamGuid);

        CLI::write(' ..team data');


        /***************/
        /* Member Data */
        /***************/

        $members = DB::Characters($realmId)->select(
           'SELECT atm.`guid` AS ARRAY_KEY, atm.`arenaTeamId`, atm.`weekGames`, atm.`weekWins`, atm.`seasonGames`, atm.`seasonWins`, atm.`personalrating`
            FROM   arena_team_member atm
            JOIN   characters c ON c.`guid` = atm.`guid` AND
                   c.`deleteInfos_Account` IS NULL AND
                   c.`level` <= ?d AND
                   (c.`extra_flags` & ?d) = 0
            WHERE  `arenaTeamId` = ?d',
            MAX_LEVEL,
            self::CHAR_GMFLAGS,
            $teamGuid
        );

        $conditions = array(
            ['c.guid', array_keys($members)],
            ['deleteInfos_Account', null],
            ['level', MAX_LEVEL, '<='],                     // prevents JS errors
            [['extra_flags', self::CHAR_GMFLAGS, '&'], 0]   // not a staff char
        );

        $mProfiles = new RemoteProfileList($conditions, ['sv' => $realmId]);
        if ($mProfiles->error)
            return false;

        $mProfiles->initializeLocalEntries();
        foreach ($mProfiles->iterate() as $__)
        {

            $mGuid = $mProfiles->getField('guid');

            $members[$mGuid]['arenaTeamId'] = $teamId;
            $members[$mGuid]['captain']     = (int)($mGuid == $captain);
            $members[$mGuid]['profileId']   = $mProfiles->getField('id');
        }

        // Delete members from other teams of the same type...
        DB::Aowow()->query(
           'DELETE atm
            FROM   ?_profiler_arena_team_member atm
            JOIN   ?_profiler_arena_team at ON atm.`arenaTeamId` = at.`id` AND at.`type` = ?d
            WHERE  atm.`profileId` IN (?a)',
            $team['type'],
            array_column($members, 'profileId')
        );

        // ...and purge this teams member
        DB::Aowow()->query('DELETE FROM ?_profiler_arena_team_member WHERE `arenaTeamId` = ?d', $teamId);

        foreach (Util::createSqlBatchInsert($members) as $m)
            DB::Aowow()->query('INSERT INTO ?_profiler_arena_team_member (?#) VALUES '.$m, array_keys(reset($members)));

        CLI::write(' ..team members');


        /*********************/
        /* mark team as done */
        /*********************/

        DB::Aowow()->query('UPDATE ?_profiler_arena_team SET `stub` = 0 WHERE `id` = ?d', $teamId);

        return true;
    }
}

?>
