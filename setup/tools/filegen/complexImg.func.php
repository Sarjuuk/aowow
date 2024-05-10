<?php
/*
    generate maps - code for extracting regular maps for AoWoW
    This file is a part of AoWoW project.
    Copyright (C) 2010  Mix <ru-mangos.ru>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');

    // note: for the sake of simplicity, this function handles all images, that must be stitched together (which are mostly maps)

    $reqDBC = ['talenttab', 'chrclasses', 'worldmapoverlay', 'worldmaparea'];

    function complexImg()
    {
        $mapWidth  = 1002;
        $mapHeight = 668;
        $threshold = 95;                                    // alpha threshold to define subZones: set it too low and you have unspawnable areas inside a zone; set it too high and the border regions overlap
        $runTime   = ini_get('max_execution_time');
        $locStr    = null;
        $imgPath   = CLISetup::$srcDir.'%sInterface/';
        $destDir   = 'static/images/wow/';
        $success   = true;
        $modeMask  = 0x7;                                   // talentBGs, regular maps, spawn-related alphaMaps
        $paths     = array(
            0x16 => ['WorldMap/',     true,  null],
            0x01 => ['TalentFrame/',  false, null],
            0x08 => ['Glues/Credits/',false, null]
        );

        $createAlphaImage = function($w, $h)
        {
            $img = imagecreatetruecolor($w, $h);

            imagesavealpha($img, true);
            imagealphablending($img, false);

            $bgColor = imagecolorallocatealpha($img, 0, 0, 0, 127);
            imagefilledrectangle($img, 0, 0, imagesx($img) - 1, imagesy($img) - 1, $bgColor);

            imagecolortransparent($img, $bgColor);
            imagealphablending($img, true);

            imagecolordeallocate($img, $bgColor);

            return $img;
        };

        // prefer manually converted PNG files (as the imagecreatefromblp-script has issues with some formats)
        // alpha channel issues observed with locale deDE Hilsbrad and Elwynn - maps
        // see: https://github.com/Kanma/BLPConverter
        $loadImageFile = function($path)
        {
            $result = null;

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

        $assembleImage = function($baseName, $order, $w, $h) use ($loadImageFile)
        {
            $dest = imagecreatetruecolor($w, $h);
            imagesavealpha($dest, true);
            imagealphablending($dest, false);

            $_h = $h;
            foreach ($order as $y => $row)
            {
                $_w = $w;
                foreach ($row as $x => $suffix)
                {
                    $src = $loadImageFile($baseName.$suffix);
                    if (!$src)
                    {
                        CLI::write(' - complexImg: tile '.$baseName.$suffix.'.blp missing.', CLI::LOG_ERROR);
                        unset($dest);
                        return null;
                    }

                    imagecopyresampled($dest, $src, 256 * $x, 256 * $y, 0, 0, min($_w, 256), min($_h, 256), min($_w, 256), min($_h, 256));
                    $_w -= 256;

                    unset($src);
                }
                $_h -= 256;
            }

            return $dest;
        };

        $writeImage = function($name, $ext, $src, $w, $h, $done)
        {
            $ok   = false;
            $dest = imagecreatetruecolor($w, $h);
            imagesavealpha($dest, true);
            imagealphablending($dest, false);
            imagecopyresampled($dest, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));

            switch ($ext)
            {
                case 'jpg':
                    $ok = imagejpeg($dest, $name.'.'.$ext, 85);
                    break;
                case 'png':
                    $ok = imagepng($dest, $name.'.'.$ext);
                    break;
                default:
                    CLI::write($done.' - unsupported file fromat: '.$ext, CLI::LOG_WARN);
            }

            imagedestroy($dest);

            if ($ok)
            {
                chmod($name.'.'.$ext, Util::FILE_ACCESS);
                CLI::write($done.' - image '.$name.'.'.$ext.' written', CLI::LOG_OK, true, true);
            }
            else
                CLI::write($done.' - could not create image '.$name.'.'.$ext, CLI::LOG_ERROR);

            return $ok;
        };

        $createSpawnMap = function($img, $zoneId) use ($mapHeight, $mapWidth, $threshold)
        {
            CLI::write(' - creating spawn map');

            $tmp = imagecreate(1000, 1000);
            $cbg = imagecolorallocate($tmp, 255, 255, 255);
            $cfg = imagecolorallocate($tmp, 0, 0, 0);

            for ($y = 0; $y < 1000; $y++)
            {
                for ($x = 0; $x < 1000; $x++)
                {
                    $a = imagecolorat($img, ($x * $mapWidth) / 1000, ($y * $mapHeight) / 1000) >> 24;
                    imagesetpixel($tmp, $x, $y, $a < $threshold ? $cfg : $cbg);
                }
            }

            imagepng($tmp, 'setup/generated/alphaMaps/' . $zoneId . '.png');

            imagecolordeallocate($tmp, $cbg);
            imagecolordeallocate($tmp, $cfg);
            imagedestroy($tmp);
        };

        $checkSourceDirs = function($sub) use ($imgPath, &$paths, $modeMask)
        {
            $hasMissing = false;
            foreach ($paths as $idx => [$subDir, $isLocalized, $realPath])
            {
                if ($realPath && !$isLocalized)
                    continue;

                $p = sprintf($imgPath, $sub).$subDir;
                if (CLISetup::fileExists($p))
                {
                    if ($isLocalized)
                        $paths[$idx][2][substr($sub, 0, -1)] = $p;
                    else
                        $paths[$idx][2] = $p;
                }
                else
                    $hasMissing = true;
            }

            return !$hasMissing;
        };

        // do not change order of params!
        $o = CLISetup::getOpt('talentbgs', 'maps', 'spawn-maps', 'artwork', 'area-maps');
        $m = 0x0;
        $i = 0;
        foreach ($o as $k => $v)
        {
            if ($v)
                $m |= 1 << $i;
            $i++;
        }

        if ($m)
            $modeMask = $m;

        foreach ($paths as $mode => $__)
            if (!($mode & $modeMask))
                unset($paths[$mode]);

        foreach (CLISetup::$expectedPaths as $xp => $locId)
        {
            if (!in_array($locId, CLISetup::$localeIds))
                continue;

            if ($xp)                                        // if in subDir add trailing slash
                $xp .= '/';

            $checkSourceDirs($xp);                          // do not break; maps are localized
        }

        $locList = [];
        foreach (CLISetup::$expectedPaths as $xp => $locId)
            if (in_array($locId, CLISetup::$localeIds))
                $locList[] = $xp;

        CLI::write('required resources overview:', CLI::LOG_INFO);
        foreach ($paths as [$path, $isLocalized, $realPath])
        {
            if (!$realPath)
                CLI::write(CLI::red('MISSING').' - '.str_pad($path, 14).' @ '.sprintf($imgPath, '['.implode(',', $locList).']/').$path);
            else if ($isLocalized)
            {
                $foundLoc = [];
                foreach (CLISetup::$localeIds as $locId)
                    foreach (CLISetup::$expectedPaths as $xp => $lId)
                        if ($locId == $lId && isset($realPath[$xp]) && !isset($foundLoc[$locId]))
                            $foundLoc[$locId] = $xp;

                if ($diff = array_diff(CLISetup::$localeIds, array_keys($foundLoc)))
                {
                    $buff = [];
                    foreach ($diff as $d)
                        $buff[] = CLI::yellow(Util::$localeStrings[$d]);
                    foreach ($foundLoc as $str)
                        $buff[] = CLI::green($str);

                    CLI::write(CLI::yellow('PARTIAL').' - '.str_pad($path, 14).' @ '.sprintf($imgPath, '['.implode(',', $buff).']/').$path);
                }
                else
                    CLI::write(CLI::green(' FOUND ').' - '.str_pad($path, 14).' @ '.sprintf($imgPath, '['.implode(',', $foundLoc).']/').$path);
            }
            else
                CLI::write(CLI::green(' FOUND ').' - '.str_pad($path, 14).' @ '.$realPath);
        }

        CLI::write();

        // if no subdir had sufficient data, diaf
        if (count(array_filter(array_column($paths, 2))) != count($paths))
        {
            CLI::write('one or more required directories are missing:', CLI::LOG_ERROR);
            return;
        }
        else
            sleep(1);

        /**************/
        /* TalentTabs */
        /**************/

        if ($modeMask & 0x01)
        {
            if (CLISetup::writeDir($destDir.'hunterpettalents/') && CLISetup::writeDir($destDir.'talents/backgrounds/'))
            {
                // [classMask, creatureFamilyMask, tabNr, textureStr]

                $tTabs = DB::Aowow()->select('SELECT tt.creatureFamilyMask, tt.textureFile, tt.tabNumber, cc.fileString FROM dbc_talenttab tt LEFT JOIN dbc_chrclasses cc ON cc.id = (LOG(2, tt.classMask) + 1)');
                $order = array(
                    ['-TopLeft',    '-TopRight'],
                    ['-BottomLeft', '-BottomRight']
                );

                if ($tTabs)
                {
                    $sum   = 0;
                    $total = count($tTabs);
                    CLI::write('Processing '.$total.' files from TalentFrame/ ...');

                    foreach ($tTabs as $tt)
                    {
                        ini_set('max_execution_time', 30);  // max 30sec per image (loading takes the most time)
                        $sum++;
                        $done = ' - '.str_pad($sum.'/'.$total, 8).str_pad('('.number_format($sum * 100 / $total, 2).'%)', 9);

                        if ($tt['creatureFamilyMask'])      // is PetCalc
                        {
                            $size = [244, 364];
                            $name = $destDir.'hunterpettalents/bg_'.(log($tt['creatureFamilyMask'], 2) + 1);
                        }
                        else
                        {
                            $size = [204, 554];
                            $name = $destDir.'talents/backgrounds/'.strtolower($tt['fileString']).'_'.($tt['tabNumber'] + 1);
                        }

                        if (!CLISetup::getOpt('force') && file_exists($name.'.jpg'))
                        {
                            CLI::write($done.' - file '.$name.'.jpg was already processed', CLI::LOG_BLANK, true, true);
                            continue;
                        }

                        $im = $assembleImage($paths[0x1][2].'/'.$tt['textureFile'], $order, 256 + 44, 256 + 75);
                        if (!$im)
                        {
                            CLI::write(' - could not assemble file '.$tt['textureFile'], CLI::LOG_ERROR);
                            continue;
                        }

                        if (!$writeImage($name, 'jpg', $im, $size[0], $size[1], $done))
                            $success = false;
                    }
                }
                else
                    $success = false;

                ini_set('max_execution_time', $runTime);
            }
            else
                $success = false;
        }

        /************/
        /* Worldmap */
        /************/

        if ($modeMask & 0x16)
        {
            $mapDirs = array(
                ['maps/%snormal/',   'jpg',  488, 325],
                ['maps/%soriginal/', 'jpg',    0,   0],     // 1002, 668
                ['maps/%ssmall/',    'jpg',  224, 149],
                ['maps/%szoom/',     'jpg',  772, 515]
            );

            // as the js expects them
            $baseLevelFix = array(
                // WotLK maps
                // Halls of Stone; The Nexus; Violet Hold; Gundrak; Obsidian Sanctum; Eye of Eternity; Vault of Archavon; Trial of the Champion; The Forge of Souls; Pit of Saron; Halls of Reflection
                4264 => 1, 4265 => 1, 4415 => 1, 4416 => 1, 4493 => 0, 4500 => 1, 4603 => 1, 4723 => 1, 4809 => 1, 4813 => 1, 4820 => 1,
                // Cata maps for WotLK instances
                // TheStockade; TheBloodFurnace; Ragefire; TheUnderbog; TheBotanica; WailingCaverns; TheSlavePens; TheShatteredHalls; HellfireRamparts; RazorfenDowns; RazorfenKraul; ManaTombs
                // ShadowLabyrinth; TheTempleOfAtalHakkar (simplified layout); BlackTemple; TempestKeep; MoltenCore; GruulsLair; CoilfangReservoir; MagtheridonsLair; OnyxiasLair; SunwellPlateau;
                 717 => 1, 3713 => 1, 2437 => 1, 3716 => 1, 3847 => 1,  718 => 1, 3717 => 1, 3714 => 1, 3562 => 1,  722 => 1,  491 => 1, 3792 => 1,
                3789 => 1, 1477 => 1, 3959 => 0, 3845 => 1, 2717 => 1, 3923 => 1, 3607 => 1, 3836 => 1, 2159 => 1, 4075 => 0
            );

            $wmo = DB::Aowow()->select('SELECT *, worldMapAreaId AS ARRAY_KEY, id AS ARRAY_KEY2 FROM dbc_worldmapoverlay WHERE textureString <> ""');
            $wma = DB::Aowow()->select('SELECT * FROM dbc_worldmaparea');
            if (!$wma || !$wmo)
            {
                $success = false;
                CLI::write(' - could not read required dbc files: WorldMapArea.dbc ['.count($wma).' entries]; WorldMapOverlay.dbc  ['.count($wmo).' entries]', CLI::LOG_ERROR);
                return;
            }

            // fixups...
            foreach ($wma as &$a)
            {
                if ($a['areaId'])
                    continue;

                switch ($a['id'])
                {
                    case 13:  $a['areaId'] = -6; break;     // Kalimdor
                    case 14:  $a['areaId'] = -3; break;     // Eastern Kingdoms
                    case 466: $a['areaId'] = -2; break;     // Outland
                    case 485: $a['areaId'] = -5; break;     // Northrend
                }
            }
            array_unshift($wma, ['id' => -1, 'areaId' => -1, 'nameINT' => 'World'], ['id' => -4, 'areaId' => -4, 'nameINT' => 'Cosmic']);

            $sumMaps = count(CLISetup::$localeIds) * count($wma);

            CLI::write('Processing '.$sumMaps.' files from WorldMap/ ...');

            foreach (CLISetup::$localeIds as $progressLoc => $l)
            {
                // create destination directories
                $dirError = false;
                foreach ($mapDirs as $md)
                    if (!CLISetup::writeDir($destDir . sprintf($md[0], strtolower(Util::$localeStrings[$l]).'/')))
                        $dirError = true;

                if ($modeMask & 0x04)
                    if (!CLISetup::writeDir('setup/generated/alphaMaps'))
                        $dirError = true;

                if ($dirError)
                {
                    $success = false;
                    CLI::write(' - complexImg: could not create map directories for locale '.$l.'. skipping...', CLI::LOG_ERROR);
                    continue;
                }


                // source for mapFiles
                $mapSrcDir = null;
                $locDirs   = array_reverse(array_filter(CLISetup::$expectedPaths, function($var) use ($l) { return !$var || $var == $l; }), true);
                foreach ($locDirs as $mapLoc => $__)
                {
                    if(!isset($paths[0x16][2][$mapLoc]))
                        continue;

                    $p = sprintf($imgPath, $mapLoc.'/').$paths[0x16][0];
                    if (CLISetup::fileExists($p))
                    {
                        CLI::write(' - using files from '.($mapLoc ?: '/').' for locale '.Util::$localeStrings[$l], CLI::LOG_INFO);
                        $mapSrcDir = $p.'/';
                        break;
                    }
                }

                if ($mapSrcDir === null)
                {
                    $success = false;
                    CLI::write(' - no suitable localized map files found for locale '.$l, CLI::LOG_ERROR);
                    continue;
                }


                foreach ($wma as $progressArea => $areaEntry)
                {
                    $curMap   = $progressArea + count($wma) * $progressLoc;
                    $progress = ' - ' . str_pad($curMap.'/'.($sumMaps), 10) . str_pad('('.number_format($curMap * 100 / $sumMaps, 2).'%)', 9);

                    $wmaId      = $areaEntry['id'];
                    $zoneId     = $areaEntry['areaId'];
                    $textureStr = $areaEntry['nameINT'];

                    $path = $mapSrcDir.$textureStr;
                    if (!CLISetup::fileExists($path))
                    {
                        $success = false;
                        CLI::write('worldmap file '.$path.' missing for selected locale '.Util::$localeStrings[$l], CLI::LOG_ERROR);
                        continue;
                    }

                    $fmt = array(
                        [1,  2,  3,  4],
                        [5,  6,  7,  8],
                        [9, 10, 11, 12]
                    );

                    CLI::write($textureStr . " [" . $zoneId . "]");

                    $overlay = $createAlphaImage($mapWidth, $mapHeight);

                    // zone has overlays (is in open world; is not multiLeveled)
                    if (isset($wmo[$wmaId]))
                    {
                        CLI::write(' - area has '.count($wmo[$wmaId]).' overlays');

                        foreach ($wmo[$wmaId] as &$row)
                        {
                            $i = 1;
                            $y = 0;
                            while ($y < $row['h'])
                            {
                                $x = 0;
                                while ($x < $row['w'])
                                {
                                    $img = $loadImageFile($path . '/' . $row['textureString'] . $i);
                                    if (!$img)
                                    {
                                        CLI::write(' - complexImg: tile '.$path.'/'.$row['textureString'].$i.'.blp missing.', CLI::LOG_ERROR);
                                        break 2;
                                    }

                                    imagecopy($overlay, $img, $row['x'] + $x, $row['y'] + $y, 0, 0, imagesx($img), imagesy($img));

                                    // prepare subzone image
                                    if ($modeMask & 0x10)
                                    {
                                        if (!isset($row['maskimage']))
                                        {
                                            $row['maskimage'] = $createAlphaImage($row['w'], $row['h']);
                                            $row['maskcolor'] = imagecolorallocatealpha($row['maskimage'], 255, 64, 192, 64);
                                        }

                                        for ($my = 0; $my < imagesy($img); $my++)
                                            for ($mx = 0; $mx < imagesx($img); $mx++)
                                                if ((imagecolorat($img, $mx, $my) >> 24) < $threshold)
                                                    imagesetpixel($row['maskimage'], $x + $mx, $y + $my, $row['maskcolor']);
                                    }

                                    imagedestroy($img);

                                    $x += 256;
                                    $i++;
                                }
                                $y += 256;
                            }
                        }

                        // create spawn-maps if wanted
                        if ($modeMask & 0x04)
                            $createSpawnMap($overlay, $zoneId);
                    }

                    // check, if the current zone is multiLeveled
                    // if there are also files present without layer-suffix assume them as layer: 0
                    $multiLeveled = false;
                    $multiLevel   = 0;
                    do
                    {
                        if (!CLISetup::filesInPath('/'.$textureStr.'\/'.$textureStr.($multiLevel + 1).'_\d\.(blp|png)/i', true))
                            break;

                        $multiLevel++;
                        $multiLeveled = true;
                    }
                    while ($multiLevel < 18);               // Karazhan has 17 frickin floors

                    // check if we can create base map anyway
                    $png = $path.'/'.$textureStr.'1.png';
                    $blp = $path.'/'.$textureStr.'1.blp';
                    $hasBaseMap = CLISetup::fileExists($blp) || CLISetup::fileExists($png);

                    CLI::write(' - area has '.($multiLeveled ? $multiLevel . ' levels' : 'only base level'));

                    $map = null;
                    for ($i = 0; $i <= $multiLevel; $i++)
                    {
                        ini_set('max_execution_time', 120); // max 120sec per image

                        $file = $path.'/'.$textureStr;

                        if (!$i && !$hasBaseMap)
                            continue;

                        // if $multiLeveled also suffix -0 to baseMap if it exists
                        if ($i && $multiLeveled)
                            $file .= $i.'_';

                        $doSkip  = 0x0;
                        $outFile = [];

                        foreach ($mapDirs as $idx => $info)
                        {
                            $outFile[$idx] = $destDir . sprintf($info[0], strtolower(Util::$localeStrings[$l]).'/') . $zoneId;

                            $floor = $i;
                            if ($zoneId == 4100)            // ToCStratholme: map order fix
                                $floor += 1;

                            if ($multiLeveled && !(isset($baseLevelFix[$zoneId]) && $i == $baseLevelFix[$zoneId]))
                                $outFile[$idx] .= '-'.$floor;

                            if (!CLISetup::getOpt('force') && file_exists($outFile[$idx].'.'.$info[1]))
                            {
                                CLI::write($progress.' - file '.$outFile[$idx].'.'.$info[1].' was already processed', CLI::LOG_BLANK, true, true);
                                $doSkip |= (1 << $idx);
                            }
                        }

                        if ($doSkip == 0xF)
                            continue;

                        $map = $assembleImage($file, $fmt, $mapWidth, $mapHeight);
                        if (!$map)
                        {
                            $success = false;
                            CLI::write(' - could not create image resource for map '.$zoneId.($multiLevel ? ' level '.$i : ''));
                            continue;
                        }

                        if (!$multiLeveled)
                        {
                            imagecopymerge($map, $overlay, 0, 0, 0, 0, imagesx($overlay), imagesy($overlay), 100);
                            imagedestroy($overlay);
                        }

                        // create map
                        if ($modeMask & 0x02)
                        {
                            foreach ($mapDirs as $idx => $info)
                            {
                                if ($doSkip & (1 << $idx))
                                    continue;

                                if (!$writeImage($outFile[$idx], $info[1], $map, $info[2] ?: $mapWidth, $info[3] ?: $mapHeight, $progress))
                                    $success = false;
                            }
                        }
                    }

                    // also create subzone-maps
                    if ($map && isset($wmo[$wmaId]) && $modeMask & 0x10)
                    {
                        foreach ($wmo[$wmaId] as &$row)
                        {
                            $doSkip  = 0x0;
                            $outFile = [];

                            foreach ($mapDirs as $idx => $info)
                            {
                                $outFile[$idx] = $destDir . sprintf($info[0], strtolower(Util::$localeStrings[$l]).'/') . $row['areaTableId'];
                                if (!CLISetup::getOpt('force') && file_exists($outFile[$idx].'.'.$info[1]))
                                {
                                    CLI::write($progress.' - file '.$outFile[$idx].'.'.$info[1].' was already processed', CLI::LOG_BLANK, true, true);
                                    $doSkip |= (1 << $idx);
                                }
                            }

                            if ($doSkip == 0xF)
                                continue;

                            $subZone = imagecreatetruecolor($mapWidth, $mapHeight);
                            imagecopy($subZone, $map, 0, 0, 0, 0, imagesx($map), imagesy($map));
                            imagecopy($subZone, $row['maskimage'], $row['x'], $row['y'], 0, 0, imagesx($row['maskimage']), imagesy($row['maskimage']));

                            foreach ($mapDirs as $idx => $info)
                            {
                                if ($doSkip & (1 << $idx))
                                    continue;

                                if (!$writeImage($outFile[$idx], $info[1], $subZone, $info[2] ?: $mapWidth, $info[3] ?: $mapHeight, $progress))
                                    $success = false;
                            }

                            imagedestroy($subZone);
                        }
                    }

                    if ($map)
                        imagedestroy($map);

                    // this takes a while; ping mysql just in case
                    DB::Aowow()->selectCell('SELECT 1');
                }
            }
        }

        /***********/
        /* Credits */
        /***********/

        if ($modeMask & 0x08)                               // optional tidbits (not used by default)
        {
            if (CLISetup::writeDir($destDir.'Interface/Glues/Credits/'))
            {
                // tile ordering
                $order = array(
                    1 => array(
                        [1]
                    ),
                    2 => array(
                        [1],
                        [2]
                    ),
                    4 => array(
                        [1, 2],
                        [3, 4]
                    ),
                    6 => array(
                        [1, 2, 3],
                        [4, 5, 6]
                    ),
                    8 => array(
                        [1, 2, 3, 4],
                        [5, 6, 7, 8]
                    )
                );

                $imgGroups = [];
                $files     = CLISetup::filesInPath('/'.str_replace('/', '\\/', $paths[0x8][2]).'/i', true);
                foreach ($files as $f)
                {
                    if (preg_match('/([^\/]+)(\d).(blp|png)/i', $f, $m))
                    {
                        if ($m[1] && $m[2])
                        {
                            if (!isset($imgGroups[$m[1]]))
                                $imgGroups[$m[1]] = $m[2];
                            else if ($imgGroups[$m[1]] < $m[2])
                                $imgGroups[$m[1]] = $m[2];
                        }
                    }
                }

                // errör-korrekt
                $imgGroups['Desolace'] = 4;
                $imgGroups['BloodElf_Female'] = 6;

                $total = count($imgGroups);
                $sum   = 0;

                CLI::write('Processing '.$total.' files from Glues/Credits/...');

                foreach ($imgGroups as $file => $fmt)
                {
                    ini_set('max_execution_time', 30);      // max 30sec per image (loading takes the most time)

                    $sum++;
                    $done = ' - '.str_pad($sum.'/'.$total, 8).str_pad('('.number_format($sum * 100 / $total, 2).'%)', 9);
                    $name = $destDir.'Interface/Glues/Credits/'.$file;

                    if (!CLISetup::getOpt('force') && file_exists($name.'.png'))
                    {
                        CLI::write($done.' - file '.$name.'.png was already processed', CLI::LOG_BLANK, true, true);
                        continue;
                    }

                    if (!isset($order[$fmt]))
                    {
                        CLI::write(' - pattern for file '.$name.' not set. skipping', CLI::LOG_WARN);
                        continue;
                    }

                    $im = $assembleImage($paths[0x8][2].'/'.$file, $order[$fmt], count($order[$fmt][0]) * 256, count($order[$fmt]) * 256);
                    if (!$im)
                    {
                        CLI::write(' - could not assemble file '.$name, CLI::LOG_ERROR);
                        continue;
                    }

                    if (!$writeImage($name, 'png', $im, count($order[$fmt][0]) * 256, count($order[$fmt]) * 256, $done))
                        $success = false;
                }

                ini_set('max_execution_time', $runTime);
            }
            else
                $success = false;
        }

        return $success;
    }

?>
