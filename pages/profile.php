<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$_path      = [1, 5, 1];
$_custom    = false;
$_profile   = null;
$_profileId = null;


/*********************/
/* Parameter-Handler */
/*********************/


function handlePower($custom, $data)                        // tooltip
{
    header('Content-type: application/x-javascript; charsetUTF-8');

    Util::powerUseLocale(@$_GET['domain']);

    $name = 'Test';
    if ($custom)
        $name .= ' (Custom Profile)';
    if (false /*hasSelectedTitle*/)
        $name = sprintf($title, $name);

    $guild     = 'Some Guild';
    $gRankName = 'Officer';


    $tt = '<table>';
    $tt .= '<tr><td><b class="q">'.$name.'</b></td></tr>';
    if (true /*CharacterHasGuild*/)
        $tt .= '<tr><td>&lt;'.$guild.'&gt; ('.$gRankName.')</td></tr>';
    else if (true /*ProfileHasDescription*/)
        $tt .= '<tr><td>'.$desc.'</td></tr>';

    $tt .= '<tr><td>Level 80 Tauren Druid (Player)</td></tr>';
    $tt .= '</table>';


    $x = '$WowheadPower.registerProfile('.($custom ? $data : "'".implode('.', $data)."'").', '.User::$localeId.", {\n";
    $x .= "\tname_".User::$localeString.": '".Util::jsEscape($name)."',\n";
    $x .= "\ttooltip_".User::$localeString.": '".$tt."',\n";
    $x .= "\ticon: \$WH.g_getProfileIcon(2, 1, 1, 60, 'class_druid'),\n";           // (race, class, gender, level, iconOrId, 'medium')
    $x .= "});";

    die($x);
}

function handleAvatar()                                     // image
{
    // something happened in the last years: those textures do not include tiny icons
    $s    = [/* 'tiny' => 15, */'small' => 18, 'medium' => 36, 'large' => 56];
    $size = empty($_GET['size']) ? 'medium' : $_GET['size'];

    if (empty($_GET['id']) || !preg_match('/^([0-9]+)\.(jpg|gif)$/', $_GET['id'], $matches) || !in_array($size, array_keys($s)))
        return;

    header('Content-Type: image/'.$matches[2]);

    $id = $matches[1];

    if (file_exists(getcwd().'/uploads/avatars/'.$id.'.jpg'))
    {
        $offsetX = $offsetY = 0;

        switch ($size)
        {
            case 'tiny':
                $offsetX += $s['small'];
            case 'small':
                $offsetY += $s['medium'];
            case 'medium':
                $offsetX += $s['large'];
        }

        $src  = imageCreateFromJpeg('uploads/avatars/'.$id.'.jpg');
        $dest = imageCreateTruecolor($s[$size], $s[$size]);

        imagecopymerge($dest, $src, 0, 0, $offsetX, $offsetY, $s[$size], $s[$size], 100);

        if ($matches[2] == 'gif')
            imageGif($dest);
        else
            imageJpeg($dest);
    }
}

function handlePinToggle($id, $mode)                        // (un)favorite
{
    /*  params
            id: <prId1,prId2,..,prIdN>
            user: <string> [optional]
        return: null
    */
}

function handleLinkToggle($id, $mode)                       // links char with account
{
    /*  params
            id: <prId1,prId2,..,prIdN>
            user: <string> [optional]
        return: null
    */
}

function handlePrivacyToggle($id, $mode)                    // ...
{
    /*  params
            id: <prId1,prId2,..,prIdN>
            user: <string> [optional]
        return: null
    */
}

function handleResync($initNew = true)                                     // ...
{
    /*  params
            id: <prId1,prId2,..,prIdN>
            user: <string> [optional]
        return
            null            [onOK]
            int or str      [onError]
    */

    if ($initNew)
        return '1';
    else
    {
        /*
            not all fields are required, if zero they are omitted
            statusCode:
                0: end the request
                1: waiting
                2: working...
                3: ready; click to view
                4: error / retry
            errorCode:
                0: unk error
                1: char does not exist
                2: armory gone

            [
                processId,
                [StatusCode, timeToRefresh, iCount, errorCode, iNResyncs],
                [<anotherStatus>]...
            ]
        */
        return '[0, [4, 10000, 1, 2]]';
    }
}

function handleSave()                                       // unKill a profile
{
    /*  params GET
            id: <prId1,prId2,..,prIdN>
        params POST
            name, level, class, race, gender, nomodel, talenttree1, talenttree2, talenttree3, activespec, talentbuild1, glyphs1, talentbuild2, glyphs2, gearscore, icon, public     [always]
            description, source, copy, inv { inventory: array containing itemLinks }                                                                                                [optional]
            }
        return
            int > 0     [profileId, if we came from an armoryProfile create a new one]
            int < 0     [onError]
            str         [onError]
    */

    return 'NYI';
}

function handleDelete()                                     // kill a profile
{
    /*  params
            id: <prId1,prId2,..,prIdN>
        return
            null
    */

    return 'NYI';
}

function handlePurge()                                      // removes certain saved information but not the entire character
{
    /*  params
            id: <prId1,prId2,..,prIdN>
            data: <mode>                [string, tabName?]
        return
            null
    */

    return 'NYI';
}

function handleSummary()                                    // can probably be removed
{
    /*  params
            null
        return
            null        [jScript sets content]
    */
}

function handleLoad()
{
    /*  params
            id: profileId
            items: string       [itemIds joined by :]
            unnamed: unixtime   [only to force the browser to reload instead of cache]
        return
            lots...
    */

    // titles, achievements, characterData, talents (, pets)
    // and some onLoad-hook to .. load it registerProfile($data)
    // check: equipItem, equipSubitem, socketItem, enchantItem, selectPet, updateMenu
    // everything ele goes through data.php .. strangely enough

    $buff = '';
    $character = array(
        'id'                => 2,
        'name'              => 'CharName',
        'region'            => ['eu', 'Europe'],
        'battlegroup'       => ['pure-pwnage', 'Pure Pwnage'],
        'realm'             => ['dafuque', 'da\'Fuqúe'],

        'level'             => 80,
        'classs'            => 11,
        'race'              => 6,
        'faction'           => 1,                                           // 0:alliance; 1:horde?
        'gender'            => 1,
        'skincolor'         => 0,                                           // playerbytes  % 256
        'hairstyle'         => 0,                                           // (playerbytes >> 16) % 256
        'haircolor'         => 0,                                           // (playerbytes >> 24) % 256
        'facetype'          => 0,                                           // faceStyle = (playerbytes >> 8) % 256     [maybe features]
        'features'          => 0,                                           // playerBytes2 % 256                       [maybe facetype]

        'source'            => 2,                                           // source: used if you create a profile from a genuine character. It inherites region, realm and bGroup
        'sourcename'        => 'SourceCharName',                            //  >   if these three are false we get a 'genuine' profile [0 for genuine characters..?]
        'user'              => 0, //User::$id,                              //  >   'genuine' is the parameter for _isArmoryProfile(allowCustoms)   ['' for genuine characters..?]
        'username'          => '', //User::$displayName,                    //  >   also, if 'source' <> 0, the char-icon is requestet via profile.php?avatar
        'published'         => 1,                                           // public / private ?
        'nomodel'           => 0xFFFF,                                      // remove slotIdx form modelvewer (so its a bitmask)
        'title'             => 22,                                          // titleId
        'guild'             => 'Godlike HD',
        'description'       => '',                                          // only in custom profiles

        'bookmarks'         => [2],                                         // UNK pinned or claimed profileIds..?
        'arenateams'        => [2 => 'Dead in the water', 3 => 'Hier kommt die Maus', 5 => 'High Five'],
        // 'lastupdated'       => 1394407364000,                            // UNK at some points it should be a date, at others an integer
        'talents'           => array(
            'builds' => array(
                ['talents' => '55322331200212', 'glyphs' => '45623:45625'],
                ['talents' => '51213102410', 'glyphs' => '45623:45625']
            ),
            'active'  => 0
        ),
        'pets'              => array(                                       // UNK
            [/*oneArrayPerPet*/],
        ),
        'skills'            => [333 => [150, 450]],                         // can contain anything, should be limited to prim/sec professions
        'reputation'        => [],
        'achievements'      => [],
        'achievementpoints' => 9001,                                        // max you have
        'statistics'        => [574 => 5, 575 => 20],                       // UNK all statistics   [id => cnt]
        'activity'          => [574 => 2],                                  // UNK recent achievements? [id => cnt]
        'titles'            => [111 => 1, 144 => 1],
        'quests'            => [],
        'spells'            => [],
        // 'glyphs'            => [],                                       // not really used .. i guess..?
        'inventory'         => [],
        'playedtime'        => 1 * YEAR + 10 * MONTH + 21 * DAY,            // exact to the day
        'auras'             => [770, 24858, 48470, 48560]                   // custom list of buffs, debuffs
    );

    $inventory = array(
         1 => [46161, 0, 3817, 0, 41398, 40112  ],
         2 => [44664, 0, 0,    0, 40112, 0      ],
         3 => [46157, 0, 3808, 0, 40112, 0      ],
         5 => [46159, 0, 3832, 0, 40112, 40112  ],
         9 => [40186, 0, 3756, 0, 0,     0      ],
         7 => [46160, 0, 3328, 0, 40112, 40112  ],
         8 => [45232, 0, 983,  0, 40112, 0      ],
         6 => [45547, 0, 0,    0, 40112, 0      ],
        10 => [46158, 0, 3222, 0, 40112, 0      ],
        11 => [43993, 0, 3839, 0, 49110, 0      ],
        12 => [45157, 0, 3839, 0, 0,     0      ],
        13 => [44253, 0, 0,    0, 0,     0      ],
        14 => [40256, 0, 0,    0, 0,     0      ],
        15 => [40403, 0, 1099, 0, 0,     0      ],
        16 => [45498, 0, 3789, 0, 0,     0      ],
        18 => [39757, 0, 0,    0, 0,     0      ],
        19 => [40643, 0, 0,    0, 0,     0      ]
    );

    $character['achievements'] = array(
         13 => 1226439600,
         12 => 1226439600,
         11 => 1226439600,
         10 => 1226439600,
          9 => 1226439600,
        883 => 1226439600,
          7 => 1226439600,
       1563 => 1226439600,
        705 => 1226439600,
         16 => 1226439600,
        546 => 1226439600
    );


    foreach ($inventory as &$iv)
        while (count($iv) < 8)
            $iv[] = 0;

    $character['inventory'] = $inventory;

    $itemz = new ItemList(array(['id', array_column($inventory, 0)]));
    $data = $itemz->getListviewData(ITEMINFO_JSON | ITEMINFO_SUBITEMS);
    foreach ($itemz->iterate() as $iId => $__)
    {
        if (empty($data[$iId]))
            continue;

        $buff .= "\r\ng_items.add(".$iId.', {name_'.User::$localeString.':"'.Util::jsEscape($itemz->getField('name', true)).'", quality:'.$itemz->getField('quality').', icon:"'.$itemz->getField('iconString').'", jsonequip:'.json_encode($data[$iId], JSON_NUMERIC_CHECK).'})';
    }

/* CUSTOM AURAS */

    $auraz = new SpellList(array(['id', $character['auras']]));
    $dataz = $auraz->getListviewData();
    $modz  = $auraz->getProfilerMods();

    $buff .= "\r\n";
    foreach ($dataz as $id => $data)
    {
        if (!empty($modz[$id]))
        {
            $mods = [];
            foreach ($modz[$id] as $k => $v)
                if ($str = @Util::$itemMods[$k])
                    $mods[$str] = $v;
                else
                    $mods[$k] = $v;

            $data['modifier'] = $mods;
        }

        $json = preg_replace('/"\$([^$"]+)"/', '\1', json_encode($data, JSON_NUMERIC_CHECK));
        $buff .= "\r\ng_spells.add(".$id.', '.$json.');';
    }

/*  END CUSTOM  */

    $mountz = new SpellList(array(['typeCat', -5]));
    $dataz = $mountz->getListviewData();
    foreach ($dataz as $id => $data)
        echo "\r\ng_spells.add(".$id.', '.json_encode($data, JSON_NUMERIC_CHECK).');';




/*** STATIC DATA ***/
/*** CACHE THIS! ***/
    // by locale and faction

    // buffer title
    $titlez = new TitleList(array(SQL_LIMIT_NONE, [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0])); // all available
    $dataz = $titlez->getListviewData();

    $buff .= "\r\n\r\nvar _ = g_titles;";
    foreach ($dataz as $id => $data)
    {
        $s = !empty($data[$id]['source']) ? ', source: '.($data[$id]['source']) : null;
        $buff .= "\r\n_[".$id."] = {name:'".Util::jsEscape($character['gender'] && !empty($data['namefemale']) ? $data['namefemale'] : $data['name'])."', gender:".$data['gender'].', category:'.$data['category'].$s.'};';
    }

    // buffer achievements / statistics
    $cnd = array(
        SQL_LIMIT_NONE,
        [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0], // no unachievable
        [['flags', 1, '&'], 0],                             // no statistics
        // [faction matches our faction]
    );
    $achievez = new AchievementList($cnd);
    $dataz = $achievez->getListviewData(ACHIEVEMENTINFO_PROFILE);

    $sumPoints = 0;
    $buff .= "\r\n\r\nvar _ = g_achievements;";
    foreach ($dataz as $id => $data)
    {
        $sumPoints += $data['points'];
        $buff .= "\r\n_[".$id.'] = '.json_encode($data, JSON_NUMERIC_CHECK).';';
    }

    // this list below is correct and expected. HOW THE HELL DOES THE SCRIPT GENERATE A TREE FROM THAT?! [ORDER BY parentId, posOrChildCount ASC]
    $buff .= "\r\n\r\ng_achievement_catorder = [96, 97, 95, 168, 169, 201, 155, 81, 1, 130, 141, 128, 122, 133, 14807, 132, 134, 131, 21, 152, 153, 154, 165, 14801, 14802, 14803, 14804, 14881, 14901, 15003, 14861, 14862, 14863, 14777, 14778, 14779, 14780, 123, 124, 125, 126, 127, 135, 136, 137, 140, 145, 147, 191, 178, 173, 160, 187, 159, 163, 161, 162, 158, 14981, 156, 14941, 14808, 14805, 14806, 14921, 14922, 14923, 14961, 14962, 15001, 15002, 15041, 15042, 170, 171, 172, 14864, 14865, 14866, 14821, 14822, 14823, 14963, 15021, 15062]";

    // max achievable achievementpoints come separately .. as array.. with only one element .. seriously?
    $buff .= "\r\n\r\ng_achievement_points = [".$sumPoints."];";

/*** END STATIC ***/


    // excludes
    $buff .= "\r\n\r\ng_excludes = {};";

    // add profile to buffer
    $buff .= "\r\n\r\n\$WowheadProfiler.registerProfile(".json_encode($character, JSON_PRETTY_PRINT, JSON_NUMERIC_CHECK).");";

    return $buff;
}

/**********/
/* HALPer */
/**********/

function getModelForForm($form, $char)
{
    switch ($form)
    {
        case 1: // FORM_CAT
            if ($char['race'] == 4) // RACE_NIGHTELF
            {
                if ($char['hairColor'] >= 0 && $char['hairColor'] <= 2)
                    return 29407;
                else if ($char['hairColor'] == 3)
                    return 29406;
                else if ($char['hairColor'] == 4)
                    return 29408;
                else if ($char['hairColor'] == 7 || $char['hairColor'] == 8)
                    return 29405;
                else
                    return 892;
            }

            if ($char['race'] == 6) // RACE_TAUREN
            {
                if ($char['gender'] == GENDER_MALE)
                {
                    if ($char['skinColor'] >= 0 && $char['skinColor'] <= 5)
                        return 29412;
                    else if ($char['skinColor'] >= 0 && $char['skinColor'] <= 8)
                        return 29411;
                    else if ($char['skinColor'] >= 0 && $char['skinColor'] <= 11)
                        return 29410;
                    else if (in_array($char['skinColor'], [12, 13, 14, 18]))
                        return 29410;
                    else
                        return 8571;
                }
                else // if gender == GENDER_FEMALE
                {
                    if ($char['skinColor'] >= 0 && $char['skinColor'] <= 3)
                        return 29412;
                    else if ($char['skinColor'] >= 0 && $char['skinColor'] <= 5)
                        return 29411;
                    else if ($char['skinColor'] >= 0 && $char['skinColor'] <= 7)
                        return 29410;
                    else if ($char['skinColor'] == 10)
                        return 29410;
                    else
                        return 8571;
                }
            }
        case 5: // FORM_DIREBEAR
        case 8: // FORM_BEAR
            if ($char['race'] == 4) // RACE_NIGHTELF
            {
                if ($char['hairColor'] >= 0 && $char['hairColor'] <= 2)
                    return 29413;       // 29415
                else if ($char['hairColor'] == 3)
                    return 29417;
                else if ($char['hairColor'] == 4)
                    return 29416;
                else if ($char['hairColor'] == 6)
                    return 29414;
                else
                    return 2281;
            }

            if ($char['race'] == 6) // RACE_TAUREN
            {
                if ($char['gender'] == GENDER_MALE)
                {
                    if ($char['skinColor'] >= 0 && $char['skinColor'] <= 2)
                        return 29415;
                    else if (in_array($char['skinColor'], [3, 4, 5, 12, 13, 14]))
                        return 29419;
                    else if (in_array($char['skinColor'], [9, 10, 11, 15, 16, 17]))
                       return 29420;
                    else if ($char['skinColor'] == 18)
                        return 29421;
                    else
                        return 2289;
                }
                else // if gender == GENDER_FEMALE
                {
                    if ($char['skinColor'] == 0 && $char['skinColor'] == 1)
                        return 29418;
                    else if ($char['skinColor'] == 2 && $char['skinColor'] == 3)
                        return 29419;
                    else if ($char['skinColor'] >= 6 && $char['skinColor'] <= 9)
                        return 29420;
                    else if ($char['skinColor'] == 10)
                        return 29421;
                    else
                        return 2289;
                }
            }
    }

    // hey, still here? you're not a Tauren/Nelf as  bear or cat, are you?
    return DB::Aowow()->selectCell('SELECT IF(?d == 1, IFNULL(displayIdA, displayIdH), IFNULL(displayIdH, displayIdA)) FROM ?_shapeshiftForm WHERE id = ?d AND XXX', Util::sideByRaceMask(1 << ($char['race'] - 1)), $form);
}

/******************/
/* select handler */
/******************/


switch ($pageParam)
{
    case 'link':
    case 'unlink':
        die(handleLinkToggle());
    case 'pin':
    case 'unpin':
        die(handlePinToggle());
    case 'public':
    case 'private':
        die(handlePrivacyToggle());
    case 'resync':
    case 'status':
        die(handleResync($pageParam == 'resync'));
    case 'save':
        die(handleSave());
    case 'delete':
        die(handleDelete());
    case 'purge':
        die(handlePurge());
    case 'summary':
        die(handleSummary());
    case 'avatar':
        die(handleAvatar());
    case 'load':
        die(handleLoad());
    case '':
        if (isset($_GET['new']))
        {
            $_profileId = 0;
            break;
        }

        die();
    default:
        $_ = explode('.', $pageParam);
        if (count($_) == 1 && intVal($_))
        {
            $_custom  = true;
            $_profile = intVal($_);
        }
        else if (count($_) == 3)
            $_profile = $_;
        else
            Util::$pageTemplate->error();

        // AowowPower-request
        if (isset($_GET['power']))
            handlePower($_custom, $_profile);
        else if ($_custom)                                  // validate existance of profile
            $_profileId = 0;
        else if ($_ = DB::Aowow()->selectCell('SELECT 2'))  // some query to validate existence of char
            $_profileId = $_;
        else
            Util::$pageTemplate->notFound(Util::ucFirst(Lang::$game['profile']), $pageParam);
}

    // required by progress in JScript
    Util::$pageTemplate->extendGlobalIds(TYPE_NPC, [29120, 31134, 29306, 29311, 23980, 27656, 26861, 26723, 28923, 15991]);


$pageData = array(
    'page' => array(
        'profileId' => $_profileId,
        'dataKey'   => $_profileId,                         // should be some unique integer to manage ?data=-requests
        'path'      => json_encode($_path, JSON_NUMERIC_CHECK),
        'title'     => Util::ucFirst(Lang::$game['profile']), // actual name is set per jScript
        'tab'       => 1,
        'reqJS'     => array(
            'static/js/filters.js',
            'static/js/TalentCalc.js',
            'static/js/swfobject.js',
            'static/js/profile_all.js',
            'static/js/profile.js',
            'static/js/Profiler.js',
            '?data=enchants.gems.glyphs.itemsets.pets.pet-talents.quick-excludes.realms.statistics.weight-presets'  // quick-excludes?!
            // ?data=user&1270968639
        ),
        'reqCSS'    => array(
            ['path' => 'static/css/TalentCalc.css'],
            ['path' => 'static/css/Profiler.css']
        )
    )
);


$smarty->updatePageVars($pageData['page']);
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, ['colon' => Lang::$colon]));

// load the page
$smarty->display('profile.tpl');



?>
