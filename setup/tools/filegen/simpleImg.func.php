<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


    // note: for the sake of simplicity, this function handles all whole images (which are mostly icons)
    // quest icons from GossipFrame have an alphaChannel that cannot be handled by this script
    // lfgFrame/lfgIcon*.blp .. candidates for zonePage, but in general too detailed to scale them down from 128 to 56, 36, ect

    $reqDBC = ['holidays', 'spellicon', 'itemdisplayinfo'];

    function simpleImg()
    {
        // prefer manually converted PNG files (as the imagecreatefromblp-script has issues with some formats)
        // see: https://github.com/Kanma/BLPConverter
        $loadImageFile = function($path)
        {
            $result = null;

            if (in_array(mb_substr($path, -4, 4), ['.png', '.blp', '.BLP', '.PNG']))
                $path = mb_substr($path, 0, mb_strlen($path) - 4);

            $file = $path.'.png';
            if (CLISetup::fileExists($file))
            {
                CLI::write('manually converted png file present for '.$path.'.', CLI::LOG_INFO);
                $result = imagecreatefrompng($file);
            }

            if (!$result)
            {
                $file = $path.'.blp';
                if (CLISetup::fileExists($file))
                    $result = imagecreatefromblp($file);
            }

            return $result;
        };

        $groups   = [];
        $imgPath  = CLISetup::$srcDir.'%sInterface/';
        $destDir  = 'static/images/wow/';
        $success  = true;
        $iconDirs = array(
            ['icons/large/',  '.jpg',  0, 56, 4],
            ['icons/medium/', '.jpg',  0, 36, 4],
            ['icons/small/',  '.jpg',  0, 18, 4],
            ['icons/tiny/',   '.gif',  0, 15, 4]
        );
        $calendarDirs = array(
            ['icons/large/',  '.jpg', 90, 56, 4],
            ['icons/medium/', '.jpg', 90, 36, 4],
            ['icons/small/',  '.jpg', 90, 18, 4],
            ['icons/tiny/',   '.gif', 90, 15, 4]
        );
        $loadScreenDirs = array(
            ['loadingscreens/large/',    '.jpg', 0, 1024, 0],
            ['loadingscreens/medium/',   '.jpg', 0,  488, 0],
            ['loadingscreens/original/', '.png', 0,    0, 0],
            ['loadingscreens/small/',    '.jpg', 0,  244, 0]
        );
        $paths    = array(                                  // src, [dest, ext, srcSize, destSize, borderOffset], pattern, isIcon, tileSize, resourcePath
             0 => ['Icons/',                                                $iconDirs,                                                    '/.*\.blp$',                           true,   0, null],
             1 => ['Spellbook/',                                            [['Interface/Spellbook/',     '.png', 0,  0, 0]],             '/UI-Glyph-Rune-?\d+.blp$',            true,   0, null],
             2 => ['PaperDoll/',                                            array_slice($iconDirs, 0, 3),                                 '/UI-(Backpack|PaperDoll)-.*\.blp$',   true,   0, null],
             3 => ['GLUES/CHARACTERCREATE/UI-CharacterCreate-Races.blp',    $iconDirs,                                                    '',                                    true,  64, null],
             4 => ['GLUES/CHARACTERCREATE/UI-CharacterCreate-CLASSES.blp',  $iconDirs,                                                    '',                                    true,  64, null],
             5 => ['GLUES/CHARACTERCREATE/UI-CharacterCreate-Factions.blp', $iconDirs,                                                    '',                                    true,  64, null],
          // 6 => ['Minimap/OBJECTICONS.BLP',                               [['icons/tiny/',              '.gif', 0, 16, 2]],             '',                                    true,  32, null],
             7 => ['FlavorImages/',                                         [['Interface/FlavorImages/',  '.png', 0,  0, 0]],             '/.*\.blp$',                           false,  0, null],
             8 => ['Pictures/',                                             [['Interface/Pictures/',      '.png', 0,  0, 0]],             '/.*\.blp$',                           false,  0, null],
             9 => ['PvPRankBadges/',                                        [['Interface/PvPRankBadges/', '.png', 0,  0, 0]],             '/.*\.blp$',                           false,  0, null],
            10 => ['Calendar/Holidays/',                                    $calendarDirs,                                                '/.*(start|[ayhs])\.blp$',             true,   0, null],
            11 => ['GLUES/LOADINGSCREENS/',                                 $loadScreenDirs,                                              '/lo.*\.blp$',                         false,  0, null],
            12 => ['PVPFrame/',                                             array_map(function($x) { $x[4] = 2; return $x; }, $iconDirs), '/PVP-(ArenaPoints|Currency).*\.blp$', true,   0, null]
        );
        // textures are composed of 64x64 icons
        // numeric indexed arrays mimick the position on the texture
        $cuNames = array(
            2 => array(
                'ui-paperdoll-slot-chest'         => 'inventoryslot_chest',
                'ui-backpack-emptyslot'           => 'inventoryslot_empty',
                'ui-paperdoll-slot-feet'          => 'inventoryslot_feet',
                'ui-paperdoll-slot-finger'        => 'inventoryslot_finger',
                'ui-paperdoll-slot-hands'         => 'inventoryslot_hands',
                'ui-paperdoll-slot-head'          => 'inventoryslot_head',
                'ui-paperdoll-slot-legs'          => 'inventoryslot_legs',
                'ui-paperdoll-slot-mainhand'      => 'inventoryslot_mainhand',
                'ui-paperdoll-slot-neck'          => 'inventoryslot_neck',
                'ui-paperdoll-slot-secondaryhand' => 'inventoryslot_offhand',
                'ui-paperdoll-slot-ranged'        => 'inventoryslot_ranged',
                'ui-paperdoll-slot-relic'         => 'inventoryslot_relic',
                'ui-paperdoll-slot-shirt'         => 'inventoryslot_shirt',
                'ui-paperdoll-slot-shoulder'      => 'inventoryslot_shoulder',
                'ui-paperdoll-slot-tabard'        => 'inventoryslot_tabard',
                'ui-paperdoll-slot-trinket'       => 'inventoryslot_trinket',
                'ui-paperdoll-slot-waist'         => 'inventoryslot_waist',
                'ui-paperdoll-slot-wrists'        => 'inventoryslot_wrists'
            ),
            3 => array(                                     // uses nameINT from ChrRaces.dbc
                ['race_human_male',    'race_dwarf_male',     'race_gnome_male',   'race_nightelf_male',   'race_draenei_male'   ],
                ['race_tauren_male',   'race_scourge_male',   'race_troll_male',   'race_orc_male',        'race_bloodelf_male'  ],
                ['race_human_female',  'race_dwarf_female',   'race_gnome_female', 'race_nightelf_female', 'race_draenei_female' ],
                ['race_tauren_female', 'race_scourge_female', 'race_troll_female', 'race_orc_female',      'race_bloodelf_female']
            ),
            4 => array(                                     // uses nameINT from ChrClasses.dbc
                ['class_warrior', 'class_mage',       'class_rogue',  'class_druid'  ],
                ['class_hunter',  'class_shaman',     'class_priest', 'class_warlock'],
                ['class_paladin', 'class_deathknight'                                ]
            ),
            5 => array(
                ['faction_alliance', 'faction_horde']
            ),
            6 => array(
                [],
                [null, 'quest_start', 'quest_end', 'quest_start_daily', 'quest_end_daily']
            ),
            10 => array(                                    // really should have read holidays.dbc...
                'calendar_winterveilstart'            => 'calendar_winterveilstart',
                'calendar_noblegardenstart'           => 'calendar_noblegardenstart',
                'calendar_childrensweekstart'         => 'calendar_childrensweekstart',
                'calendar_fishingextravaganza'        => 'calendar_fishingextravaganzastart',
                'calendar_harvestfestivalstart'       => 'calendar_harvestfestivalstart',
                'calendar_hallowsendstart'            => 'calendar_hallowsendstart',
                'calendar_lunarfestivalstart'         => 'calendar_lunarfestivalstart',
                'calendar_loveintheairstart'          => 'calendar_loveintheairstart',
                'calendar_midsummerstart'             => 'calendar_midsummerstart',
                'calendar_brewfeststart'              => 'calendar_brewfeststart',
                'calendar_darkmoonfaireelwynnstart'   => 'calendar_darkmoonfaireelwynnstart',
                'calendar_darkmoonfairemulgorestart'  => 'calendar_darkmoonfairemulgorestart',
                'calendar_darkmoonfaireterokkarstart' => 'calendar_darkmoonfaireterokkarstart',
                'calendar_piratesday'                 => 'calendar_piratesdaystart',
                'calendar_wotlklaunch'                => 'calendar_wotlklaunchstart',
                'calendar_dayofthedeadstart'          => 'calendar_dayofthedeadstart',
                'calendar_fireworks'                  => 'calendar_fireworksstart'
            )
        );

        $writeImage = function($name, $ext, $src, $srcDims, $destDims, $done)
        {
            $ok   = false;
            $dest = imagecreatetruecolor($destDims['w'], $destDims['h']);

            imagesavealpha($dest, true);
            if ($ext == '.png')
                imagealphablending($dest, false);

            imagecopyresampled($dest, $src, $destDims['x'], $destDims['x'], $srcDims['x'], $srcDims['y'], $destDims['w'], $destDims['h'], $srcDims['w'], $srcDims['h']);

            switch ($ext)
            {
                case '.jpg':
                    $ok = imagejpeg($dest, $name.$ext, 85);
                    break;
                case '.gif':
                    $ok = imagegif($dest, $name.$ext);
                    break;
                case '.png':
                    $ok = imagepng($dest, $name.$ext);
                    break;
                default:
                    CLI::write($done.' - unsupported file fromat: '.$ext, CLI::LOG_WARN);
            }

            imagedestroy($dest);

            if ($ok)
            {
                chmod($name.$ext, Util::FILE_ACCESS);
                CLI::write($done.' - image '.$name.$ext.' written', CLI::LOG_OK, true, true);
            }
            else
                CLI::write($done.' - could not create image '.$name.$ext, CLI::LOG_ERROR);

            return $ok;
        };

        $checkSourceDirs = function($sub) use ($imgPath, &$paths)
        {
            $hasMissing = false;
            foreach ($paths as $pathIdx => [$subDir, , , , , $realPath])
            {
                if ($realPath)
                    continue;

                $p = sprintf($imgPath, $sub).$subDir;
                if (CLISetup::fileExists($p))
                    $paths[$pathIdx][5] = $p;
                else
                    $hasMissing = true;
            }

            return !$hasMissing;
        };

        if (CLISetup::getOpt('icons'))
            array_push($groups, 0, 2, 3, 4, 5, 10, 12);
        if (CLISetup::getOpt('glyphs'))
            $groups[] = 1;
        if (CLISetup::getOpt('pagetexts'))
            array_push($groups, 7, 8, 9);
        if (CLISetup::getOpt('loadingscreens'))
            $groups[] = 11;

        // filter by passed options
        if (!$groups)                                       // by default do not generate loadingscreens
            unset($paths[11]);
        else
            foreach (array_keys($paths) as $k)
                if (!in_array($k, $groups))
                    unset($paths[$k]);

        foreach (CLISetup::$expectedPaths as $xp => $locId)
        {
            if (!in_array($locId, CLISetup::$localeIds))
                continue;

            if ($xp)                                        // if in subDir add trailing slash
                $xp .= '/';

            if ($checkSourceDirs($xp))
                break;
        }

        $locList = [];
        foreach (CLISetup::$expectedPaths as $xp => $locId)
            if (in_array($locId, CLISetup::$localeIds))
                $locList[] = $xp;

        CLI::write('required resources overview:', CLI::LOG_INFO);
        foreach ($paths as [$path, , , , , $realPath])
        {
            if ($realPath)
                CLI::write(CLI::green(' FOUND ').' - '.str_pad($path, 53).' @ '.$realPath);
            else
                CLI::write(CLI::red('MISSING').' - '.str_pad($path, 53).' @ '.sprintf($imgPath, '['.implode(',', $locList).']/').$path);
        }

        CLI::write();

        // if no subdir had sufficient data, diaf
        if (count(array_filter(array_column($paths, 5))) != count($paths))
        {
            CLI::write('one or more required directories are missing:', CLI::LOG_ERROR);
            return;
        }
        else
            sleep(1);

        // init directories
        foreach (array_column($paths, 1) as $subDirs)
            foreach ($subDirs as $sd)
                if (!CLISetup::writeDir($destDir.$sd[0]))
                    $success = false;

        // ok, departure from std::procedure here
        // scan ItemDisplayInfo.dbc and SpellIcon.dbc for expected images and save them to an array
        // load all icon paths into another array and xor these two
        // excess entries for the directory are fine, excess entries for the dbc's are not
        $dbcEntries = [];

        if (isset($paths[0]) || isset($paths[1]))           // generates icons or glyphs
        {
            if (isset($paths[0]) && !isset($paths[1]))
                $siRows = DB::Aowow()->selectCol('SELECT iconPath FROM dbc_spellicon WHERE iconPath NOT LIKE "%glyph-rune%"');
            else if (!isset($paths[0]) && isset($paths[1]))
                $siRows = DB::Aowow()->selectCol('SELECT iconPath FROM dbc_spellicon WHERE iconPath LIKE "%glyph-rune%"');
            else
                $siRows = DB::Aowow()->selectCol('SELECT iconPath FROM dbc_spellicon');

            foreach ($siRows as $icon)
            {
                if (stristr($icon, $paths[0][0]))           // Icons/
                    $dbcEntries[] = strtolower($paths[0][5].substr(strrchr($icon, '\\'), 1));
                else if (stristr($icon, $paths[1][0]))      // Spellbook/
                    $dbcEntries[] = strtolower($paths[1][5].substr(strrchr($icon, '\\'), 1));
            }
        }

        if (isset($paths[0]))
        {
            $itemIcons = DB::Aowow()->selectCol('SELECT inventoryIcon1 FROM dbc_itemdisplayinfo WHERE inventoryIcon1 <> ""');
            foreach ($itemIcons as $icon)
                $dbcEntries[] = strtolower($paths[0][5].'/'.$icon.'.blp');

            $eventIcons = DB::Aowow()->selectCol('SELECT textureString FROM dbc_holidays WHERE textureString <> ""');
            foreach ($eventIcons as $icon)
                $dbcEntries[] = strtolower($paths[10][5].'/'.$icon.'Start.blp');
        }

        // case-insensitive array_unique *vomits silently into a corner*
        $dbcEntries = array_intersect_key($dbcEntries, array_unique($dbcEntries));

        $allPaths = [];
        foreach ($paths as $i => [$inPath, $outInfo, $pattern, $isIcon, $tileSize, $path])
        {
            $search = $path.$pattern;
            if ($pattern)
                $search = '/'.str_replace('/', '\\/', $search).'/i';

            $files    = CLISetup::filesInPath($search, !!$pattern);
            $allPaths = array_merge($allPaths, $files);

            CLI::write('processing '.count($files).' files in '.$path.'...');

            $j = 0;
            foreach ($files as $f)
            {
                ini_set('max_execution_time', 30);          // max 30sec per image (loading takes the most time)

                $src   = null;
                $na    = explode('/', $f);
                $img   = explode('.', array_pop($na));
                array_pop($img);                            // there are a hand full of images with multiple file endings or random dots in the name
                $img   = implode('.', $img);

                // file not from dbc -> name from array or skip file
                if (!empty($cuNames[$i]))
                {
                    if (!empty($cuNames[$i][strtolower($img)]))
                        $img = $cuNames[$i][strtolower($img)];
                    else if (!$tileSize)
                    {
                        $j += count($outInfo);
                        CLI::write('skipping extraneous file '.$img.' (+'.count($outInfo).')');
                        continue;
                    }
                }

                $nFiles = count($outInfo) * ($tileSize ? array_sum(array_map('count', $cuNames[$i])) : count($files));

                foreach ($outInfo as [$dest, $ext, $srcSize, $destSize, $borderOffset])
                {
                    if ($tileSize)
                    {
                        foreach ($cuNames[$i] as $y => $row)
                        {
                            foreach ($row as $x => $name)
                            {
                                $j++;
                                $img  = $isIcon ? strtolower($name) : $name;
                                $done = ' - '.str_pad($j.'/'.$nFiles, 12).str_pad('('.number_format($j * 100 / $nFiles, 2).'%)', 9);

                                if (!CLISetup::getOpt('force') && file_exists($destDir.$dest.$img.$ext))
                                {
                                    CLI::write($done.' - file '.$dest.$img.$ext.' was already processed', CLI::LOG_BLANK, true, true);
                                    continue;
                                }

                                if (!$src)
                                    $src = $loadImageFile($f);

                                if (!$src)                              // error should be created by imagecreatefromblp
                                    continue;

                                /*
                                    ready for some major bullshitery? well, here it comes anyway!
                                    the class-icon tile [idx: 4] isn't 64x64 but 63x64 .. the right side border is 1px short
                                    so if we don't watch out, the icons start to shift over and show the border
                                    also the icon border is displaced by 1px
                                */
                                $from = array(
                                    'x' => $borderOffset + 1 + ($tileSize - ($i == 4 ? 1 : 0)) * $x,
                                    'y' => $borderOffset + 1 +  $tileSize                      * $y,
                                    'w' => ($tileSize - ($i == 4 ? 1 : 0)) - $borderOffset * 2,
                                    'h' =>  $tileSize                      - $borderOffset * 2
                                );
                                $to   = array(
                                    'x' => 0,
                                    'y' => 0,
                                    'w' => $destSize,
                                    'h' => $destSize
                                );

                                if (!$writeImage($destDir.$dest.$img, $ext, $src, $from, $to, $done))
                                   $success = false;
                            }
                        }

                        // custom handle for combined icon 'quest_startend'
                        /* not used due to alphaChannel issues
                        if ($tileSize == 32)
                        {
                            $dest = imagecreatetruecolor(19, 16);
                            imagesavealpha($dest, true);
                            imagealphablending($dest, true);

                            // excalmationmark, questionmark
                            imagecopyresampled($dest, $src, 0, 1, 32 + 5, 32 + 2,  8, 15, 18, 30);
                            imagecopyresampled($dest, $src, 5, 0, 64 + 1, 32 + 1, 10, 16, 18, 28);

                            if (imagegif($dest, $destDir.$dest.'quest_startend.gif'))
                                CLI::write('                extra - image '.$destDir.$dest.'quest_startend.gif written', CLI::LOG_OK);
                            else
                            {
                                CLI::write('                extra - could not create image '.$destDir.$dest.'quest_startend.gif', CLI::LOG_ERROR);
                                $success = false;
                            }

                            imagedestroy($dest);
                        }
                        */
                    }
                    else
                    {
                        // icon -> lowercase
                        if ($isIcon)
                            $img = strtolower($img);

                        $j++;
                        $done = ' - '.str_pad($j.'/'.$nFiles, 12).str_pad('('.number_format($j * 100 / $nFiles, 2).'%)', 9);

                        if (!CLISetup::getOpt('force') && file_exists($destDir.$dest.$img.$ext))
                        {
                            CLI::write($done.' - file '.$dest.$img.$ext.' was already processed', CLI::LOG_BLANK, true, true);
                            continue;
                        }

                        if (!$src)
                            $src = $loadImageFile($f);

                        if (!$src)                              // error should be created by imagecreatefromblp
                            continue;

                        $from = array(
                            'x' => $borderOffset,
                            'y' => $borderOffset,
                            'w' => ($srcSize ?: imagesx($src)) - $borderOffset * 2,
                            'h' => ($srcSize ?: imagesy($src)) - $borderOffset * 2
                        );
                        $to   = array(
                            'x' => 0,
                            'y' => 0,
                            'w' => $destSize ?: imagesx($src),
                            'h' => $destSize ?: imagesy($src)
                        );

                        if (!$writeImage($destDir.$dest.$img, $ext, $src, $from, $to, $done))
                            $success = false;
                    }
                }

                unset($src);
            }
        }

        // reset execTime
        ini_set('max_execution_time', FileGen::$defaultExecTime);

        if ($missing = array_diff(array_map('strtolower', $dbcEntries), array_map('strtolower', $allPaths)))
        {
            // hide affected icons from listviews
            $iconNames = array_map(function($path) {
                preg_match('/\/([^\/]+)\.blp$/i', $path, $m);
                return $m ? $m[1] : null;
            }, $missing);

            DB::Aowow()->query('UPDATE ?_icons SET cuFlags = cuFlags | ?d WHERE name IN (?a)', CUSTOM_EXCLUDE_FOR_LISTVIEW, $iconNames);

            asort($missing);
            CLI::write('the following '.count($missing).' images where referenced by DBC but not in the mpqData directory. They may need to be converted by hand later on.', CLI::LOG_WARN);
            foreach ($missing as $m)
                CLI::write(' - '.$m);
        }

        return $success;
    }
