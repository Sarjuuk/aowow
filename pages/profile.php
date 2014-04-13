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

@include('datasets/ProfilerExampleChar');       // tmp char data

    $name       = $character['name'];
    $guild      = $character['guild'];
    $gRankName  = $character['guildrank'];
    $lvl        = $character['level'];
    $ra         = $character['race'];
    $cl         = $character['classs'];
    $gender     = $character['gender'];
    $desc       = $character['description'];
    $title      = (new TitleList(array(['id', $character['title']])))->getField($gender ? 'female' : 'male', true);

    if ($custom)
        $name .= ' (Custom Profile)';
    else if ($title)
        $name = sprintf($title, $name);

    $tt = '<table>';
    $tt .= '<tr><td><b class="q">'.$name.'</b></td></tr>';
    if ($guild)
        $tt .= '<tr><td>&lt;'.$guild.'&gt; ('.$gRankName.')</td></tr>';
    else if ($desc)
        $tt .= '<tr><td>'.$desc.'</td></tr>';

    $tt .= '<tr><td>'.Lang::$game['level'].' '.$lvl.' '.Lang::$game['ra'][$ra].' '.Lang::$game['cl'][$cl].'</td></tr>';
    $tt .= '</table>';

    $x = '$WowheadPower.registerProfile('.($custom ? $data : "'".implode('.', $data)."'").', '.User::$localeId.", {\n";
    $x .= "\tname_".User::$localeString.": '".Util::jsEscape($name)."',\n";
    $x .= "\ttooltip_".User::$localeString.": '".$tt."',\n";
    $x .= "\ticon: \$WH.g_getProfileIcon(".$ra.", ".$cl.", ".$gender.", ".$lvl."),\n";           // (race, class, gender, level, iconOrId, 'medium')
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

function handleResync($initNew = true)                      // ...
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
    // everything else goes through data.php .. strangely enough


    $character = array(
        'id'                => 2,
        'name'              => 'CharName',
        'region'            => ['eu', 'Europe'],
        'battlegroup'       => ['pure-pwnage', 'Pure Pwnage'],
        'realm'             => ['dafuque', 'da\'Fuqúe'],
        'level'             => 80,
        'classs'            => 11,
        'race'              => 6,
        'faction'           => 1,                           // 0:alliance; 1:horde
        'gender'            => 1,                           // 0:male, 1:female
        'skincolor'         => 0,                           // playerbytes  % 256
        'hairstyle'         => 0,                           // (playerbytes >> 16) % 256
        'haircolor'         => 0,                           // (playerbytes >> 24) % 256
        'facetype'          => 0,                           // (playerbytes >> 8) % 256                 [maybe features]
        'features'          => 0,                           // playerBytes2 % 256                       [maybe facetype]
        'source'            => 2,                           // source: used if you create a profile from a genuine character. It inherites region, realm and bGroup
        'sourcename'        => 'SourceCharName',            //  >   if these three are false we get a 'genuine' profile [0 for genuine characters..?]
        'user'              => 1,                           //  >   'genuine' is the parameter for _isArmoryProfile(allowCustoms)   ['' for genuine characters..?]
        'username'          => 'TestUser',                  //  >   also, if 'source' <> 0, the char-icon is requestet via profile.php?avatar
        'published'         => 1,                           // public / private
        'pinned'            => 1,                           // usable for some utility funcs on site
        'nomodel'           => 0x0,                         // unchecks DisplayOnCharacter by (1 << slotId - 1)
        'title'             => 0,                           // titleId currently in use or null
        'guild'             => 'GuildName',                 // only on chars; id or null
        'description'       => 'this is a profile',         // only on custom profiles
        'arenateams'        => [],                          // [size(2|3|5) => DisplayName]; DisplayName gets urlized to use as link
        'playedtime'        => 0,                           // exact to the day
        'lastupdated'       => 0,                           // timestamp in ms
        'achievementpoints' => 0,                           // max you have
        'talents'           => array(
            'builds' => array(
                ['talents' => '', 'glyphs' => ''],          // talents:string of 0-5 points; glyphs: itemIds.join(':')
            ),
            'active'  => 1                                  // 1|2
        ),
        'customs'           => [],                          // custom profiles created from this char; profileId => [name, ownerId, iconString(optional)]
        'skills'            => [],                          // skillId => [curVal, maxVal]; can contain anything, should be limited to prim/sec professions
        'inventory'         => [],                          // slotId => [itemId, subItemId, permEnchantId, tempEnchantId, gemItemId1, gemItemId2, gemItemId3, gemItemId4]
        'auras'             => [],                          // custom list of buffs, debuffs [spellId]

        // completion lists: [subjectId => amount/timestamp/1]
        'reputation'        => [],                          // factionId => amount
        'titles'            => [],                          // titleId => 1
        'spells'            => [],                          // spellId => 1; recipes, pets, mounts
        'achievements'      => [],                          // achievementId => timestamp
        'quests'            => [],                          // questId => 1

        // UNKNOWN
        'bookmarks'         => [2],                         // UNK pinned or claimed userId => profileIds..?
        'statistics'        => [],                          // UNK all statistics?      [achievementId => killCount]
        'activity'          => [],                          // UNK recent achievements? [achievementId => killCount]
        'glyphs'            => [],                          // not really used .. i guess..?
        'pets'              => array(                       // UNK
            [],                                             // one array per pet, structure UNK
        ),
    );

@include('datasets/ProfilerExampleChar');       // tmp char data

    $buff  = '';
    $itemz = new ItemList(array(['id', array_column($character ['inventory'], 0)]));
    $data  = $itemz->getListviewData(ITEMINFO_JSON | ITEMINFO_SUBITEMS);

    $auraz = new SpellList(array(['id', $character['auras']]));
    $dataz = $auraz->getListviewData();
    $modz  = $auraz->getProfilerMods();

    // get and apply inventory
    foreach ($itemz->iterate() as $iId => $__)
        $buff .= 'g_items.add('.$iId.', {name_'.User::$localeString.":'".Util::jsEscape($itemz->getField('name', true))."', quality:".$itemz->getField('quality').", icon:'".$itemz->getField('iconString')."', jsonequip:".json_encode($data[$iId], JSON_NUMERIC_CHECK)."});\n";

    $buff .= "\n";

    // get and apply aura-mods
    foreach ($dataz as $id => $data)
    {
        $mods = [];
        if (!empty($modz[$id]))
        {
            foreach ($modz[$id] as $k => $v)
            {
                if (is_array($v))
                    $mods[] = $v;
                else if ($str = @Util::$itemMods[$k])
                    $mods[$str] = $v;
            }
        }

        $buff .= 'g_spells.add('.$id.", {id:".$id.", name:'".Util::jsEscape($data['name'])."', icon:'".$data['icon']."', modifier:".json_encode($mods, JSON_NUMERIC_CHECK)."});\n";
    }
    $buff .= "\n";

    // load available titles
    Util::loadStaticFile('p-titles-'.$character['gender'], $buff, true);

    // load available achievements
    if (!Util::loadStaticFile('p-achievements', $buff, true))
    {
        $buff .= "\n\ng_achievement_catorder = [];";
        $buff .= "\n\ng_achievement_points = [0];";
    }

    // excludes; structure UNK type => [maskBit => [typeIds]] ?
    /*
        g_user.excludes = [type:[typeIds]]
        g_user.includes = [type:[typeIds]]
        g_user.excludegroups = groupMask        // requires g_user.settings != null

        maskBit are matched against fieldId from excludeGroups
        id: 1, label: LANG.dialog_notavail
		id: 2, label: LANG.dialog_tcg
		id: 4, label: LANG.dialog_collector
        id: 8, label: LANG.dialog_promo
		id: 16, label: LANG.dialog_nonus
		id: 96, label: LANG.dialog_faction
		id: 896, label: LANG.dialog_profession
		id: 1024, label: LANG.dialog_noexalted
    */
    // $buff .= "\n\ng_excludes = {};";

    // add profile to buffer
    $buff .= "\n\n\$WowheadProfiler.registerProfile(".json_encode($character).");"; // can't use JSON_NUMERIC_CHECK or the talent-string becomes a float

    return $buff."\n";
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
                    return 29413;
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

    // hey, still here? you're not a Tauren/Nelf as bear or cat, are you?
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
    case 'summary':                                         // page is generated by jScript
        die();
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
        else if ($_ = DB::Aowow()->selectCell('SELECT 2161862'))  // some query to validate existence of char
            $_profileId = $_;
        else
            Util::$pageTemplate->notFound(Util::ucFirst(Lang::$game['profile']), $pageParam);
}

    // required by progress in JScript move to handleLoad()?
    Util::$pageTemplate->extendGlobalIds(TYPE_NPC, [29120, 31134, 29306, 29311, 23980, 27656, 26861, 26723, 28923, 15991]);


$pageData = array(
    'page' => array(
        'profileId' => $_profileId,
        'dataKey'   => $_SESSION['dataKey'],
        'path'      => json_encode($_path, JSON_NUMERIC_CHECK),
        'title'     => Util::ucFirst(Lang::$game['profile']), // actual name is set per jScript
        'tab'       => 1,
        'region'    => 'eu',
        'realm'     => 'realm Name',
        'reqJS'     => array(
            'static/js/filters.js',
            'static/js/TalentCalc.js',
            'static/js/swfobject.js',
            'static/js/profile_all.js',
            'static/js/profile.js',
            'static/js/Profiler.js',
            '?data=enchants.gems.glyphs.itemsets.pets.pet-talents.quick-excludes.realms.statistics.weight-presets&locale='.User::$localeId.'&t='.$_SESSION['dataKey']
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
