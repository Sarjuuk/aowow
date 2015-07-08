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
        if (isset(FileGen::$cliOpts['help']))
        {
            echo "\n";
            echo "available options for subScript 'complexImg':\n";                                         // modeMask
            echo "--talentbgs  (backgrounds for talent calculator)\n";                                      // 0x01
            echo "--maps       (generates worldmaps)\n";                                                    // 0x02
            echo "--spawn-maps (creates alphaMasks of each zone to check spawns against)\n";                // 0x04
            echo "--artwork    (optional: imagery from /glues/credits (not used, skipped by default))\n";   // 0x08
            echo "--area-maps  (optional: renders maps with highlighted subZones for each area)\n";         // 0x10

            return true;
        }

        $mapWidth  = 1002;
        $mapHeight = 668;
        $threshold = 95;                                    // alpha threshold to define subZones: set it too low and you have unspawnable areas inside a zone; set it too high and the border regions overlap
        $runTime   = ini_get('max_execution_time');
        $locStr    = null;
        $dbcPath   = CLISetup::$srcDir.'%sDBFilesClient/';
        $imgPath   = CLISetup::$srcDir.'%sInterface/';
        $destDir   = 'static/images/wow/';
        $success   = true;
        $paths     = ['WorldMap/', 'TalentFrame/', 'Glues/Credits/'];
        $modeMask  = 0x7;                                   // talentBGs, regular maps, spawn-related alphaMaps

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
                CLISetup::log('manually converted png file present for '.$path.'.', CLISetup::LOG_INFO);
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
                        CLISetup::log(' - complexImg: tile '.$baseName.$suffix.'.blp missing.', CLISetup::LOG_ERROR);
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
                    CLISetup::log($done.' - unsupported file fromat: '.$ext, CLISetup::LOG_WARN);
            }

            imagedestroy($dest);

            if ($ok)
            {
                chmod($name.'.'.$ext, Util::FILE_ACCESS);
                CLISetup::log($done.' - image '.$name.'.'.$ext.' written', CLISetup::LOG_OK);
            }
            else
                CLISetup::log($done.' - could not create image '.$name.'.'.$ext, CLISetup::LOG_ERROR);

            return $ok;
        };

        $createSpawnMap = function($img, $zoneId) use ($mapHeight, $mapWidth, $threshold)
        {
            CLISetup::log(' - creating spawn map');

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

        $checkSourceDirs = function($sub, &$missing = []) use ($imgPath, $dbcPath, $paths, &$modeMask)
        {
            $hasMissing = false;
            foreach ($paths as $idx => $subDir)
            {
                if ($idx == 0 && !($modeMask & 0x16))       // map related
                    continue;
                else if ($idx == 1 && !($modeMask & 0x1))   // talentBGs
                    continue;
                else if ($idx == 2 && !($modeMask & 0x8))   // artwork
                    continue;

                $p = sprintf($imgPath, $sub).$subDir;
                if (!CLISetup::fileExists($p))
                {
                    $hasMissing = true;
                    $missing[]  = $p;
                }
            }

            if ($modeMask & 0x17)
            {
                $p = sprintf($dbcPath, $sub);
                if (!CLISetup::fileExists($p))
                {
                    $hasMissing = true;
                    $missing[]  = $p;
                }
            }

            return !$hasMissing;
        };


        // do not change order of params!
        if ($_ = FileGen::hasOpt('talentbgs', 'maps', 'spawn-maps', 'artwork', 'area-maps'))
            $modeMask = $_;

        foreach (CLISetup::$expectedPaths as $xp => $__)
        {
            if ($xp)                                        // if in subDir add trailing slash
                $xp .= '/';

            if ($checkSourceDirs($xp, $missing))
            {
                $locStr = $xp;
                break;
            }
        }

        // if no subdir had sufficient data, diaf
        if ($locStr === null)
        {
            CLISetup::log('one or more required directories are missing:', CLISetup::LOG_ERROR);
            foreach ($missing as $m)
                CLISetup::log(' - '.$m, CLISetup::LOG_ERROR);

            return;
        }


        /**************/
        /* TalentTabs */
        /**************/

        if ($modeMask & 0x01)
        {
            if (CLISetup::writeDir($destDir.'hunterpettalents/') && CLISetup::writeDir($destDir.'talents/backgrounds/'))
            {
                // [classMask, creatureFamilyMask, tabNr, textureStr]

                $tTabs = DB::Aowow()->select('SELECT tt.creatureFamilyMask, tt.textureFile, tt.tabNumber, cc.fileString FROM dbc_talenttab tt LEFT JOIN dbc_chrclasses cc ON cc.Id = (LOG(2, tt.classMask) + 1)');
                $order = array(
                    ['-TopLeft',    '-TopRight'],
                    ['-BottomLeft', '-BottomRight']
                );

                if ($tTabs)
                {
                    $sum   = 0;
                    $total = count($tTabs);
                    CLISetup::log('Processing '.$total.' files from TalentFrame/ ...');

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

                        if (!isset(FileGen::$cliOpts['force']) && file_exists($name.'.jpg'))
                        {
                            CLISetup::log($done.' - file '.$name.'.jpg was already processed');
                            continue;
                        }

                        $im = $assembleImage(sprintf($imgPath, $locStr).'TalentFrame/'.$tt['textureFile'], $order, 256 + 44, 256 + 75);
                        if (!$im)
                        {
                            CLISetup::log(' - could not assemble file '.$tt['textureFile'], CLISetup::LOG_ERROR);
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
                ['maps/%ssmall/',    'jpg',  224, 163],
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

            $wmo = DB::Aowow()->select('SELECT *, worldMapAreaId AS ARRAY_KEY, Id AS ARRAY_KEY2 FROM dbc_worldmapoverlay WHERE textureString <> ""');
            $wma = DB::Aowow()->select('SELECT * FROM dbc_worldmaparea');
            if (!$wma || !$wmo)
            {
                $success = false;
                CLISetup::log(' - could not read required dbc files: WorldMapArea.dbc ['.count($wma).' entries]; WorldMapOverlay.dbc  ['.count($wmo).' entries]', CLISetup::LOG_ERROR);
                return;
            }

            // fixups...
            foreach ($wma as &$a)
            {
                if ($a['areaId'])
                    continue;

                switch ($a['Id'])
                {
                    case 13:  $a['areaId'] = -6; break;     // Kalimdor
                    case 14:  $a['areaId'] = -3; break;     // Eastern Kingdoms
                    case 466: $a['areaId'] = -2; break;     // Outland
                    case 485: $a['areaId'] = -5; break;     // Northrend
                }
            }
            array_unshift($wma, ['Id' => -1, 'areaId' => -1, 'nameINT' => 'World'], ['Id' => -4, 'areaId' => -4, 'nameINT' => 'Cosmic']);

            $sumMaps = count(CLISetup::$localeIds) * count($wma);

            CLISetup::log('Processing '.$sumMaps.' files from WorldMap/ ...');

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
                    CLISetup::log(' - complexImg: could not create map directories for locale '.$l.'. skipping...', CLISetup::LOG_ERROR);
                    continue;
                }


                // source for mapFiles
                $mapSrcDir = null;
                $locDirs   = array_filter(CLISetup::$expectedPaths, function($var) use ($l) { return !$var || $var == $l; });
                foreach ($locDirs as $mapLoc => $__)
                {
                    if ($mapLoc)                            // and trailing slash again
                        $mapLoc .= '/';

                    $p = sprintf($imgPath, $mapLoc).$paths[0];
                    if (CLISetup::fileExists($p))
                    {
                        CLISetup::log(' - using files from '.($mapLoc ?: '/').' for locale '.Util::$localeStrings[$l], CLISetup::LOG_INFO);
                        $mapSrcDir = $p.'/';
                        break;
                    }
                }

                if ($mapSrcDir === null)
                {
                    $success = false;
                    CLISetup::log(' - no suitable localized map files found for locale '.$l, CLISetup::LOG_ERROR);
                    continue;
                }


                foreach ($wma as $progressArea => $areaEntry)
                {
                    $curMap   = $progressArea + count($wma) * $progressLoc;
                    $progress = ' - ' . str_pad($curMap.'/'.($sumMaps), 10) . str_pad('('.number_format($curMap * 100 / $sumMaps, 2).'%)', 9);

                    $wmaId      = $areaEntry['Id'];
                    $zoneId     = $areaEntry['areaId'];
                    $textureStr = $areaEntry['nameINT'];

                    $path = $mapSrcDir.$textureStr;
                    if (!CLISetup::fileExists($path))
                    {
                        $success = false;
                        CLISetup::log('worldmap file '.$path.' missing for selected locale '.Util::$localeStrings[$l], CLISetup::LOG_ERROR);
                        continue;
                    }

                    $fmt = array(
                        [1,  2,  3,  4],
                        [5,  6,  7,  8],
                        [9, 10, 11, 12]
                    );

                    CLISetup::log($textureStr . " [" . $zoneId . "]");

                    $overlay = $createAlphaImage($mapWidth, $mapHeight);

                    // zone has overlays (is in open world; is not multiLeveled)
                    if (isset($wmo[$wmaId]))
                    {
                        CLISetup::log(' - area has '.count($wmo[$wmaId]).' overlays');

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
                                        CLISetup::log(' - complexImg: tile '.$path.'/'.$row['textureString'].$i.'.blp missing.', CLISetup::LOG_ERROR);
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
                        if (!CLISetup::filesInPath('/'.$textureStr.'\/'.$textureStr.($multiLevel + 1).'_\d\.blp/i', true))
                            break;

                        $multiLevel++;
                        $multiLeveled = true;
                    }
                    while ($multiLevel < 18);               // Karazhan has 17 frickin floors

                    // check if we can create base map anyway
                    $file       = $path.'/'.$textureStr.'1.blp';
                    $hasBaseMap = CLISetup::fileExists($file);

                    CLISetup::log(' - area has '.($multiLeveled ? $multiLevel . ' levels' : 'only base level'));

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

                            if (!isset(FileGen::$cliOpts['force']) && file_exists($outFile[$idx].'.'.$info[1]))
                            {
                                CLISetup::log($progress.' - file '.$outFile[$idx].'.'.$info[1].' was already processed');
                                $doSkip |= (1 << $idx);
                            }
                        }

                        if ($doSkip == 0xF)
                            continue;

                        $map = $assembleImage($file, $fmt, $mapWidth, $mapHeight);
                        if (!$map)
                        {
                            $success = false;
                            CLISetup::log(' - could not create image resource for map '.$zoneId.($multiLevel ? ' level '.$i : ''));
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
                                if (!isset(FileGen::$cliOpts['force']) && file_exists($outFile[$idx].'.'.$info[1]))
                                {
                                    CLISetup::log($progress.' - file '.$outFile[$idx].'.'.$info[1].' was already processed');
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
                $srcPath   = sprintf($imgPath, $locStr).'Glues/Credits/';
                $files     = CLISetup::filesInPath($srcPath);
                foreach ($files as $f)
                {
                    if (preg_match('/([^\/]+)(\d).blp/i', $f, $m))
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

                CLISetup::log('Processing '.$total.' files from Glues/Credits/...');

                foreach ($imgGroups as $file => $fmt)
                {
                    ini_set('max_execution_time', 30);      // max 30sec per image (loading takes the most time)

                    $sum++;
                    $done = ' - '.str_pad($sum.'/'.$total, 8).str_pad('('.number_format($sum * 100 / $total, 2).'%)', 9);
                    $name = $destDir.'Interface/Glues/Credits/'.$file;

                    if (!isset(FileGen::$cliOpts['force']) && file_exists($name.'.png'))
                    {
                        CLISetup::log($done.' - file '.$name.'.png was already processed');
                        continue;
                    }

                    if (!isset($order[$fmt]))
                    {
                        CLISetup::log(' - pattern for file '.$name.' not set. skipping', CLISetup::LOG_WARN);
                        continue;
                    }

                    $im = $assembleImage($srcPath.$file, $order[$fmt], count($order[$fmt][0]) * 256, count($order[$fmt]) * 256);
                    if (!$im)
                    {
                        CLISetup::log(' - could not assemble file '.$name, CLISetup::LOG_ERROR);
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
