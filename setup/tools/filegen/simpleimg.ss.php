<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


// quest icons from GossipFrame have an alphaChannel that cannot be handled by this script
// lfgFrame/lfgIcon-*.blp .. candidates for zonePage, but in general too detailed to scale them down from 128 to 56, 36, ect
// linked by lfgDungeons.dbc/28 ?

CLISetup::registerSetup("build", new class extends SetupScript
{
    use TrImageProcessor;

    protected $info = array(
        'simpleimg'      => [[   ], CLISetup::ARGV_PARAM,    'Converts and resizes BLP2 images smaller than 255x255 into required formats (mostly icons)'],
        'icons'          => [['1'], CLISetup::ARGV_OPTIONAL, 'Generate icons for spells, items, classes, races, ect.'],
        'glyphs'         => [['2'], CLISetup::ARGV_OPTIONAL, 'Generate decorative glyph symbols displayed on related item and spell pages.'],
        'pagetexts'      => [['3'], CLISetup::ARGV_OPTIONAL, 'Generate images contained in text on readable items and gameobjects.'],
        'loadingscreens' => [['4'], CLISetup::ARGV_OPTIONAL, 'Generate loading screen images (not used on page; skipped by default)']
    );

    protected $dbcSourceFiles = ['holidays', 'spellicon', 'itemdisplayinfo'];
    protected $setupAfter     = [['icons'], []];

    private const ICON_DIRS = array(
        ['static/images/wow/icons/large/',  'jpg',  0, 56, 4],
        ['static/images/wow/icons/medium/', 'jpg',  0, 36, 4],
        ['static/images/wow/icons/small/',  'jpg',  0, 18, 4],
        ['static/images/wow/icons/tiny/',   'gif',  0, 15, 4]
    );

    private $genSteps = array(
      //       srcPath,           realPath, localized, [pattern, isIcon, tileSize],                             [[dest, ext, srcSize, destSize, borderOffset]]
         0 => ['Icons/',                  null, false, ['.*\.(blp|png)$',                           true,   0], self::ICON_DIRS,                                                 ],
         1 => ['Spellbook/',              null, false, ['UI-Glyph-Rune-?\d+.(blp|png)$',            false,  0], [['static/images/wow/Interface/Spellbook/',     'png', 0,  0, 0]]],
         2 => ['PaperDoll/',              null, false, ['UI-(Backpack|PaperDoll)-.*\.(blp|png)$',   true,   0], self::ICON_DIRS,                                                 ],
         3 => ['GLUES/CHARACTERCREATE/',  null, false, ['UI-CharacterCreate-Races\.(blp|png)',      true,  64], self::ICON_DIRS,                                                 ],
         4 => ['GLUES/CHARACTERCREATE/',  null, false, ['UI-CharacterCreate-CLASSES\.(blp|png)',    true,  64], self::ICON_DIRS,                                                 ],
         5 => ['GLUES/CHARACTERCREATE/',  null, false, ['UI-CharacterCreate-Factions\.(blp|png)',   true,  64], self::ICON_DIRS,                                                 ],
      // 6 => ['Minimap/'               , null, false, ['OBJECTICONS.(BLP|png)',                    true,  32], [['static/images/wow/icons/tiny/',              'gif', 0, 16, 2]]],
         7 => ['FlavorImages/',           null, false, ['.*\.(blp|png)$',                           false,  0], [['static/images/wow/Interface/FlavorImages/',  'png', 0,  0, 0]]],
         8 => ['Pictures/',               null, false, ['.*\.(blp|png)$',                           false,  0], [['static/images/wow/Interface/Pictures/',      'png', 0,  0, 0]]],
         9 => ['PvPRankBadges/',          null, false, ['.*\.(blp|png)$',                           false,  0], [['static/images/wow/Interface/PvPRankBadges/', 'png', 0,  0, 0]]],
        10 => ['Calendar/Holidays/',      null, false, ['.*(start|[ayhs])\.(blp|png)$',             true,   0], self::ICON_DIRS,                                                 ],
        11 => ['GLUES/LOADINGSCREENS/',   null, false, ['lo.*\.(blp|png)$',                         false,  0], [['cache/loadingscreens/',                      'png', 0,  0, 0]]],
        12 => ['PVPFrame/',               null, false, ['PVP-(ArenaPoints|Currency).*\.(blp|png)$', true,   0], self::ICON_DIRS,                                                 ]
    );

    // textures are composed of 64x64 icons
    // numeric indexed arrays mimick the position on the texture
    private $cuNames = array(
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

    public function __construct()
    {
        $this->imgPath = CLISetup::$srcDir.$this->imgPath;
        $this->maxExecTime = ini_get('max_execution_time');

        // init directories to be checked when registered
        foreach (array_column($this->genSteps, self::GEN_IDX_DEST_INFO) as $subDirs)
            foreach ($subDirs as $sd)
                $this->requiredDirs[] = $sd[0];

        // fix genSteps 2 [icons] - no tiny inventory backgrounds
        $this->genSteps[2][self::GEN_IDX_DEST_INFO] = array_slice($this->genSteps[2][self::GEN_IDX_DEST_INFO], 0, 3);

        // fix genSteps 12 [pvp money icons] - smaller border offset for pvp currency icons
        array_walk($this->genSteps[12][self::GEN_IDX_DEST_INFO], function(&$x) { $x[4] = 2; });

        // fix genSteps 10 [holoday icons] - img src size is 90px
        array_walk($this->genSteps[10][self::GEN_IDX_DEST_INFO], function(&$x) { $x[2] = 90; });
    }

    public function generate() : bool
    {
        // find out what to generate
        $groups = [];
        if (CLISetup::getOpt('icons'))
            array_push($groups, 0, 2, 3, 4, 5, 10, 12);
        if (CLISetup::getOpt('glyphs'))
            $groups[] = 1;
        if (CLISetup::getOpt('pagetexts'))
            array_push($groups, 7, 8, 9);
        if (CLISetup::getOpt('loadingscreens'))
            $groups[] = 11;

        if (!$groups)                                       // by default do not generate loadingscreens
            $groups = [0, 1, 2, 3, 4, 5, 7, 8, 9, 10, 12];

        // removed unused generators and reset realPaths (in case of retry from failed attempt)
        foreach ($this->genSteps as $idx => $_)
        {
            if (!in_array($idx, $groups))
                unset($this->genSteps[$idx]);
            else
                $this->genSteps[$idx][self::GEN_IDX_SRC_REAL] = null;
        }

        if (!$this->checkSourceDirs())
        {
            CLI::write('[simpleimg] one or more required directories are missing:', CLI::LOG_ERROR);
            return false;
        }

        sleep(2);

        $allPaths = [];
        foreach ($this->genSteps as $i => [, $path, , [$pattern, $isIcon, $tileSize], $outInfo])
        {
            $search = CLI::nicePath('', $path);
            if ($pattern)
                $search = '/'.strtr($search, ['\\' => '\\\\', '/' => '\\/']).$pattern.'/i';

            $files    = CLISetup::filesInPath($search, !!$pattern);
            $allPaths = array_merge($allPaths, array_map(function ($x) { return substr($x, 0, -4); }, $files));

            if (!$files)
            {
                CLI::write('[simpleimg] source directory "'.CLI::bold($search).'" does not contain files matching "'.CLI::bold($pattern), CLI::LOG_ERROR);
                $this->success = false;
                continue;
            }

            CLI::write('[simpleimg] processing '.count($files).' files in '.$path.'...');

            $j = 0;
            foreach ($files as $f)
            {
                ini_set('max_execution_time', $this->maxExecTime);

                $src   = null;
                $na    = explode(DIRECTORY_SEPARATOR, $f);
                $img   = explode('.', array_pop($na));
                array_pop($img);                            // there are a hand full of images with multiple file endings or random dots in the name
                $img   = implode('.', $img);

                if (!empty($this->cuNames[$i]))             // file not from dbc -> name from array or skip file
                {
                    if (!empty($this->cuNames[$i][strtolower($img)]))
                        $img = $this->cuNames[$i][strtolower($img)];
                    else if (!$tileSize)
                    {
                        $j += count($outInfo);
                        CLI::write('[simpleimg] skipping extraneous file '.$img.' (+'.count($outInfo).')');
                        continue;
                    }
                }

                $nFiles = count($outInfo) * ($tileSize ? array_sum(array_map('count', $this->cuNames[$i])) : count($files));

                foreach ($outInfo as [$dest, $ext, $srcSize, $destSize, $borderOffset])
                {
                    if ($tileSize)
                    {
                        foreach ($this->cuNames[$i] as $y => $row)
                        {
                            foreach ($row as $x => $name)
                            {
                                $j++;
                                $outFile = CLI::nicePath(($isIcon ? strtolower($name) : $name).'.'.$ext, $dest);

                                $this->status = ' - '.str_pad($j.'/'.$nFiles, 12).str_pad('('.number_format($j * 100 / $nFiles, 2).'%)', 9);

                                if (!CLISetup::getOpt('force') && file_exists($outFile))
                                {
                                    CLI::write('[simpleimg] '.$this->status.' - file '.$outFile.' was already processed', CLI::LOG_BLANK, true, true);
                                    continue;
                                }

                                if (!$src)
                                    $src = $this->loadImageFile($f, $noSrcFile);

                                if (!$src)                  // error should be created by imagecreatefromblp
                                {
                                    if (!$noSrcFile)        // there are a couple of void file references in dbc, so this can't be a hard error.
                                        $this->success = false;

                                    continue;
                                }

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

                                if (!$this->writeImageFile($src, $outFile, $from, $to))
                                   $this->success = false;
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

                            if (imagegif($dest, $dest.'quest_startend.gif'))
                                CLI::write('                extra - image '.$dest.'quest_startend.gif written', CLI::LOG_OK);
                            else
                            {
                                CLI::write('                extra - could not create image '.$dest.'quest_startend.gif', CLI::LOG_ERROR);
                                $this->success = false;
                            }

                            imagedestroy($dest);
                        }
                        */
                    }
                    else
                    {
                        $j++;
                        $this->status = ' - '.str_pad($j.'/'.$nFiles, 12).str_pad('('.number_format($j * 100 / $nFiles, 2).'%)', 9);
                        $outFile = CLI::nicePath(($isIcon ? strtolower($img) : $img).'.'.$ext, $dest);

                        if (!CLISetup::getOpt('force') && file_exists($outFile))
                        {
                            CLI::write('[simpleimg] '.$this->status.' - file '.$outFile.' was already processed', CLI::LOG_BLANK, true, true);
                            continue;
                        }

                        $src = $this->loadImageFile($f, $noSrcFile);
                        if (!$src)                          // error should be created by imagecreatefromblp
                        {
                            if (!$noSrcFile)                // there are a couple of void file references in dbc, so this can't be a hard error.
                                $this->success = false;

                            continue;
                        }

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

                        if (!$this->writeImageFile($src, $outFile, $from, $to))
                            $this->success = false;
                    }
                }

                unset($src);
            }
        }

        // scan ItemDisplayInfo.dbc and SpellIcon.dbc for expected images and save them to an array
        // load all icon paths into another array and xor these two
        // excess entries for the directory are fine, excess entries for the dbc's are not
        $dbcEntries = [];
        $gens = array_keys($this->genSteps);

        if (in_array(0, $gens))                             // generates icons
        {
            if ($siRows = DB::Aowow()->selectCol('SELECT `iconPath` FROM dbc_spellicon WHERE `iconPath` NOT LIKE "%glyph-rune%"'))
                foreach ($siRows as $icon)
                    if (stristr($icon, $this->genSteps[0][self::GEN_IDX_SRC_PATH]))  // Icons/
                        $dbcEntries[] = strtolower($this->genSteps[0][self::GEN_IDX_SRC_REAL].substr(strrchr($icon, '\\'), 1));

            if ($itemIcons = DB::Aowow()->selectCol('SELECT `inventoryIcon1` FROM dbc_itemdisplayinfo WHERE `inventoryIcon1` <> ""'))
                foreach ($itemIcons as $icon)
                    $dbcEntries[] = strtolower($this->genSteps[0][self::GEN_IDX_SRC_REAL].DIRECTORY_SEPARATOR.$icon);
        }

        if (in_array(1, $gens))                             // generates glyphs
            if ($siRows = DB::Aowow()->selectCol('SELECT `iconPath` FROM dbc_spellicon WHERE `iconPath` LIKE "%glyph-rune%"'))
                foreach ($siRows as $icon)
                    if (stristr($icon, $this->genSteps[1][self::GEN_IDX_SRC_PATH]))  // Spellbook/
                        $dbcEntries[] = strtolower($this->genSteps[1][self::GEN_IDX_SRC_REAL].substr(strrchr($icon, '\\'), 1));

        if (in_array(10, $gens))                            // generates holiday icons
            if ($eventIcons = DB::Aowow()->selectCol('SELECT `textureString` FROM dbc_holidays WHERE `textureString` <> ""'))
                foreach ($eventIcons as $icon)
                    $dbcEntries[] = strtolower($this->genSteps[10][self::GEN_IDX_SRC_REAL].DIRECTORY_SEPARATOR.$icon.'start');

        // case-insensitive array_unique *vomits silently into a corner*
        $dbcEntries = array_intersect_key($dbcEntries, array_unique($dbcEntries));

        if ($missing = array_diff(array_map('strtolower', $dbcEntries), array_map('strtolower', $allPaths)))
        {
            // hide affected icons from listviews
            $iconNames = array_map(function($path) {
                preg_match('/\/([^\/]+)\.blp$/i', $path, $m);
                return $m ? $m[1] : null;
            }, $missing);

            DB::Aowow()->query('UPDATE ?_icons SET `cuFlags` = `cuFlags` | ?d WHERE `name` IN (?a)', CUSTOM_EXCLUDE_FOR_LISTVIEW, $iconNames);

            CLI::write('[simpleimg] the following '.count($missing).' images where referenced by DBC but not in the mpqData directory. They may need to be converted by hand later on.', CLI::LOG_WARN);
            foreach ($missing as $m)
                CLI::write(' - '.$m);
        }

        return $this->success;
    }
});

?>
