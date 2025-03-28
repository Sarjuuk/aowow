<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    use TrComplexImage;

    protected $info = array(
        'img-maps'   => [[   ], CLISetup::ARGV_PARAM,    'Generate zone and continental maps and the corresponding \'zones\' datasets.'                                                          ],
/* 1 */ 'spawnmaps'  => [['1'], CLISetup::ARGV_OPTIONAL, 'Fallback to generate alpha masks for each zone to match creature and gameobject spawn points.'],
/* 2 */ 'subzones'   => [['2'], CLISetup::ARGV_OPTIONAL, 'Generate additional area maps with highlighting for subzones (optional; skipped by default)'  ],
/* 4 */ 'skip-zones' => [['3'], CLISetup::ARGV_OPTIONAL, 'Prevent default output of zone maps.'                                                         ]
    );

    protected $useGlobalStrings = true;
    protected $dbcSourceFiles   = ['worldmapoverlay', 'worldmaparea', 'dungeonmap'];
    protected $requiredDirs     = ['datasets/'];

    private const M_MAPS     = (1 << 0);
    private const M_SPAWNS   = (1 << 1);
    private const M_SUBZONES = (1 << 2);

    private $modeMask = self::M_SPAWNS | self::M_MAPS;

    private const SPAWNMAP_WH   = 1000;                     // it is square
    private const MAP_W         = 1002;
    private const MAP_H         = 668;
    private const A_THRESHOLD   = 95;                       // alpha threshold to define subZones: set it too low and you have unspawnable areas inside a zone; set it too high and the border regions overlap
    private const COLOR_WHITE   = [255, 255, 255];          // rgb
    private const COLOR_BLACK   = [  0,   0,   0];          // rgb
    private const COLOR_SUBZONE = [  0, 230, 255, 74];      // rgba - note: rgb is 0-255, a is 0-127

    private const AREA_FLAG_DEFAULT_FLOOR_TERRAIN = 0x004;  // Default Dungeon Floor is Terrain
    private const AREA_FLAG_NO_DEFAULT_FLOOR      = 0x100;  // Don't use Default Dungeon Floor (typically 1)

    private const CONTINENTS = [0, 1, 530, 571];            // Map.dbc/id

    private const DEST_DIRS = array(
        ['static/images/wow/maps/%snormal/',   488, 325],
        ['static/images/wow/maps/%soriginal/',   0,   0],   // 1002, 668
        ['static/images/wow/maps/%ssmall/',    224, 149],
        ['static/images/wow/maps/%szoom/',     772, 515]
    );

    private const TILEORDER = array(
        [1,  2,  3,  4],
        [5,  6,  7,  8],
        [9, 10, 11, 12]
    );

    private const MAP_FILE_PATTERN = '/((\w{4})\/interface\/worldmap(?:\/microdungeon\/([^\/]+))?\/([^\/]+)\/)(\4)(?:(\d{1,2})_)?(\d{1,2})\.(?:blp|png)/i';

    // src, resourcePath, localized, [tileOrder], [[dest, destW, destH]]
    private $genSteps = array(
        self::M_MAPS     => ['WorldMap/', null, true,  self::TILEORDER, self::DEST_DIRS             ],
        self::M_SPAWNS   => ['WorldMap/', null, true,  self::TILEORDER, [['cache/alphaMaps/', 0, 0]]],
        self::M_SUBZONES => ['WorldMap/', null, true,  self::TILEORDER, self::DEST_DIRS             ]
    );

    private $progress        = 0;
    private $wmOverlays      = [];
    private $dmFloorData     = [];
    private $wmAreas         = [];
    private $multiLevelZones = [];
    private $mapFiles        = [];                          // [nameINT][floorIdx][loc][tileIdx] => filePath
    private $microDungeons   = [];

    public function __construct()
    {
        $this->imgPath = CLISetup::$srcDir.$this->imgPath;
        $this->maxExecTime = ini_get('max_execution_time');

        // init directories
        foreach ($this->genSteps as [, , , , $outInfo])
        {
            foreach ($outInfo as $dir)
            {
                if (strpos($dir[0], '%s') === false)
                    $this->requiredDirs[] = $dir[0];
                else
                    foreach (CLISetup::$locales as $loc)
                        $this->requiredDirs[] = sprintf($dir[0], $loc->json().DIRECTORY_SEPARATOR);
            }
        }
    }

    public function generate() : bool
    {
        // find out what to generate
        $opts = array_slice(array_keys($this->info), 1);
        $getO = CLISetup::getOpt(...$opts);
        $mask = 0x0;

        if ($getO['spawnmaps'])
            $mask |= self::M_SPAWNS;
        if ($getO['subzones'])
            $mask |= self::M_SUBZONES;
        if (!$getO['skip-zones'])
            $mask |= self::M_MAPS;

        // unless manually prompted drop spawnmap generation if 90% of spawns have core generated area info
        $npcPct = DB::World()->selectCell('SELECT SUM(IF(`zoneId` > 0, 1, 0)) / COUNT(*) FROM creature')   ?? 0;
        $goPct  = DB::World()->selectCell('SELECT SUM(IF(`zoneId` > 0, 1, 0)) / COUNT(*) FROM gameobject') ?? 0;

        if (!($mask & self::M_SPAWNS) && $npcPct > 0.9 && $goPct > 0.9)
            $this->modeMask &= ~self::M_SPAWNS;

        $this->modeMask = $mask ?: $this->modeMask;

        if (!$this->modeMask)                               // why would you do this..?
            return true;

        // removed unused genSteps
        foreach ($this->genSteps as $idx => $_)
            if (!($idx & $this->modeMask))
                unset($this->genSteps[$idx]);

        if (!$this->checkSourceDirs())
        {
            CLI::write('[img-maps] One or more source directories are missing.', CLI::LOG_ERROR);
            $this->success = false;
            return false;
        }

        sleep(2);

        if ($this->prepare())
        {
            $this->buildMaps();
            $this->buildZonesFile();
        }

        return $this->success;
    }

    private function buildMapsFUTURE() : void
    {
        $sumFloors = array_sum(array_column($this->dmFloorData, 1));
        $sumAreas  = count($this->wmAreas);
        $sumMaps   = count(CLISetup::$locales) * ($sumAreas + $sumFloors);

        CLI::write('[img-maps] Processing '.$sumAreas.' zone maps and '.$sumFloors.' dungeon maps from Interface/WorldMap/ for locale: '.Lang::concat(CLISetup::$locales, callback: fn($x) => $x->name));

        /*  todo: retrain brain and generate maps by given files and GlobalStrings. Then assign dbc data to them not the other way round like it is now.
                foreach ($this->mapFiles as $name => [$floors, $isMultilevel])
                {
                    // skip redundant data of a microDungeons
                    if (in_array($name, $this->microDungeons))
                        continue;

                    $this->wmAreas = $this->wmAreas[$name] ?? [];
                    if (!$this->wmAreas)
                    {
                        CLI::write('[img-maps] no WMA data for map file '.CLI::bold($name), CLI::LOG_WARN);
                        continue;
                    }

                    $wmaId  = $this->wmAreas['id'];
                    $zoneId = $this->wmAreas['areaId'];
                    $mapId  = $this->wmAreas['mapId'];
                    $flags  = $this->wmAreas['flags'] ?? 0;                   // flags added in 4.x

                    if ($isMultilevel)
                        $this->multiLevelZones[$zoneId] = [];

                    // TODO
                    // - Ahn'Kahet (4494) has a secondary map file, that is not referenced in DungeonMap.dbc but looks nice. Lets manually reference it.
                    // if (isset($floorData[4494]))
                        // $floorData[4494][1] = 2;
                    if ($zoneId == 206)
                        var_dump($floors);


                    foreach ($floors as $locId => [$floorData, $basePath])
                    {
                        ksort($floorData);

                        $resOverlay = null;
                        if (!$isMultilevel)
                            $resOverlay = $this->generateOverlay($wmaId, $name, $basePath);

                        // create spawn-maps if wanted
                        if ($resOverlay && $this->modeMask & self::M_SPAWNS)
                        {
                            $outFile = $this->genSteps[self::M_SPAWNS][self::GEN_IDX_DEST_INFO][0][0] . $zoneId . '.png';
                            if (!$this->buildSpawnMap($resOverlay, $outFile))
                                $this->success = false;
                        }

                        foreach ($floorData as $floorIdx => $tileData)
                        {
                            $outFile = $zoneId;

                            // naming of the base floor file is a bit wonky. unsure when the -0 suffix should be implicit or explicit
                            // just note, that the floor names from GlobalStrings.lua always have a '0' as base level suffix

                            if (!$floorIdx && $isMultilevel && !($flags & self::AREA_FLAG_DEFAULT_FLOOR_TERRAIN))
                                CLI::write('[img-maps] zone '.$name.' is multilevel and has base level map file, but is not flagged for use', CLI::LOG_INFO);

                            if ($isMultilevel && !$floorIdx)
                            {
                                if (in_array($mapId, self::CONTINENTS))
                                    $outFile .= '-0';
                                else if ($this->wmAreas['defaultDungeonMapId'] < 0)
                                    $outFile .= '-0';
                                // else
                                    // implicit -0
                            }
                            else if ($isMultilevel)
                                $outFile .= '-'.$floorIdx;

                            if ($isMultilevel)
                                $this->multiLevelZones[$zoneId][$floorIdx] = $outFile;


                            foreach ($tileData as $tileIdx => $filePath)
                            {

                            }
                        }
                    }

                    if ($isMultilevel)
                        $this->multiLevelZones[$zoneId] = array_values($this->multiLevelZones[$zoneId]);

                    if ($this->modeMask & self::M_SUBZONES)
                    {
                        // get subzones for mapFile from wmaData and apply overlays
                    }
                }
        */

        $progressLoc = -1;
        foreach (CLISetup::$locales as $l => $loc)
        {
            $progressLoc++;

            // source for mapFiles
            $mapSrcDir = '';
            if ($this->modeMask & self::M_SPAWNS)
                $mapSrcDir = $this->genSteps[self::M_SPAWNS][1][$l] ?? '';
            if (!$mapSrcDir && $this->modeMask & self::M_SUBZONES)
                $mapSrcDir = $this->genSteps[self::M_SUBZONES][1][$l] ?? '';
            if (!$mapSrcDir)
                $mapSrcDir = $this->genSteps[self::M_MAPS][1][$l] ?? '';
            if (!$mapSrcDir)
            {
                $this->success = false;
                CLI::write(' - no suitable localized map files found for locale '.$l, CLI::LOG_ERROR);
                continue;
            }

            foreach ($this->wmAreas as $progressArea => $areaEntry)
            {
                $curMap   = $progressArea + count($this->wmAreas) * $progressLoc;
                $this->status = ' - ' . str_pad($curMap.'/'.($sumMaps), 10) . str_pad('('.number_format($curMap * 100 / $sumMaps, 2).'%)', 9);

                $wmaId      = $areaEntry['id'];
                $zoneId     = $areaEntry['areaId'];
                $mapId      = $areaEntry['mapId'];
                $textureStr = $areaEntry['nameINT'];
                $flags      = $areaEntry['flags'] ?? 0;     // flags added in 4.x

                [$floorStr, $nFloors] = $this->dmFloorData[in_array($mapId, self::CONTINENTS) ? -$wmaId : $mapId] ?? ['', 0];

                if ($nFloors && !isset($this->multiLevelZones))
                    $this->multiLevelZones[$zoneId] = [];

                CLI::write(
                    str_pad('['.$areaEntry['areaId'].']', 7) .
                    str_pad($areaEntry['nameINT'], 22) .
                    str_pad('Overlays: '.count($this->wmOverlays[$areaEntry['id']] ?? []), 14) .
                    str_pad('Dungeon Maps: '.($nFloors + ((($flags ?? 0) & self::AREA_FLAG_DEFAULT_FLOOR_TERRAIN) ? 1 : 0)), 18)
                );

                $srcPath = $mapSrcDir.DIRECTORY_SEPARATOR.$textureStr;
                if (!CLISetup::fileExists($srcPath))
                {
                    $this->success = false;
                    CLI::write('worldmap file '.$srcPath.' missing for selected locale '.$loc->name, CLI::LOG_ERROR);
                    continue;
                }

                $resOverlay = null;

                // zone has overlays (is in open world; is not multilevel)
                if (isset($this->wmOverlays[$wmaId]))
                {
                    $resOverlay = $this->generateOverlay($wmaId, $srcPath);

                    // create spawn-maps if wanted
                    if ($this->modeMask & self::M_SPAWNS)
                        $this->buildSpawnMap($resOverlay, $zoneId);
                }

                if (!($this->modeMask & self::M_MAPS))
                    continue;

                // check, if the current zone is multiLeveled
                $floors = [0];
                if ($floorStr)
                    $floors = array_merge($floors, explode(' ', $floorStr));

                // - Ahn'Kahet (4494) has a secondary map file, that is not referenced in DungeonMap.dbc but looks nice. Lets manually reference it.
                if ($zoneId == 4494)
                    $floors[] = 2;

                $resMap = null;
                foreach ($floors as $floorIdx)
                {
                    ini_set('max_execution_time', $this->maxExecTime);

                    $file = $srcPath.DIRECTORY_SEPARATOR.$textureStr;

                    // todo: Dalaran [4395] has no level 0 but is not skipped here
                    if (!$floorIdx && !($flags & self::AREA_FLAG_DEFAULT_FLOOR_TERRAIN) && !in_array($mapId, self::CONTINENTS))
                        continue;

                    if ($nFloors && ($floorIdx || $flags & self::AREA_FLAG_DEFAULT_FLOOR_TERRAIN))
                        $this->multiLevelZones[$zoneId][$floorIdx] = $zoneId . '-' . $floorIdx;

                    if ($floorIdx)
                        $file .= $floorIdx . '_';

                    $doSkip  = 0x0;
                    $outFile = [];

                    foreach (self::DEST_DIRS as $sizeIdx => [$path, $width, $height])
                    {
                        $outFile[$sizeIdx] = sprintf($path, $loc->json().DIRECTORY_SEPARATOR) . $zoneId;

                        /* dataset 'zones' requires that ...
                         * 3959 - Black Temple: starts with empty floor suffix
                         * 4075 - Sunwell: starts with empty floor suffix
                         * 4723 - Map 650 CoT: 5-man reuses raid map (649) but only the upper floor. Check DungeonMap.dbc
                         */

                        if ($nFloors && ($floorIdx || $flags & self::AREA_FLAG_DEFAULT_FLOOR_TERRAIN))
                            $outFile[$sizeIdx] .= '-'.$floorIdx;

                        $outFile[$sizeIdx] .= '.jpg';

                        if (!CLISetup::getOpt('force') && file_exists($outFile[$sizeIdx]))
                        {
                            CLI::write($this->status.' - file '.$outFile[$sizeIdx].' was already processed', CLI::LOG_BLANK, true, true);
                            $doSkip |= (1 << $sizeIdx);
                        }
                    }

                    if ($doSkip == 0xF)
                        continue;

                    $resMap = $this->assembleImage($file, self::TILEORDER, self::MAP_W, self::MAP_H);
                    if (!$resMap)
                    {
                        CLI::write(' - could not create image resource for zone '.$zoneId.($nFloors ? ' floor '.$floorIdx : ''), CLI::LOG_ERROR);
                        $this->success = false;
                        continue;
                    }

                    if ($resOverlay && !$floorIdx)
                    {
                        imagecopymerge($resMap, $resOverlay, 0, 0, 0, 0, imagesx($resOverlay), imagesy($resOverlay), 100);
                        imagedestroy($resOverlay);
                    }

                    // create map
                    if ($this->modeMask & self::M_MAPS)
                    {
                        foreach (self::DEST_DIRS as $sizeIdx => [, $width, $height])
                        {
                            if ($doSkip & (1 << $sizeIdx))
                                continue;

                            if (!$this->writeImageFile($resMap, $outFile[$sizeIdx], $width ?: self::MAP_W, $height ?: self::MAP_H))
                                $this->success = false;
                        }
                    }
                }

                // also create subzone-maps
                if ($resMap && isset($this->wmOverlays[$wmaId]) && $this->modeMask & self::M_SUBZONES)
                    $this->buildSubZones($resMap, $wmaId, $loc);

                if ($resMap)
                    imagedestroy($resMap);

                // this takes a while; ping mysql just in case
                DB::Aowow()->selectCell('SELECT 1');
            }
        }
    }

    private function prepare() : bool
    {
        $this->wmOverlays  = DB::Aowow()->select('SELECT *, `worldMapAreaId` AS ARRAY_KEY, `id` AS ARRAY_KEY2 FROM dbc_worldmapoverlay WHERE `textureString` <> ""');
        $this->wmAreas     = DB::Aowow()->select('SELECT `id`, `mapId`, `areaId`, UPPER(`nameINT`) AS `nameINT`, IF(`areaId`, `areaId`, -`id`) AS ARRAY_KEY FROM dbc_worldmaparea');
        $this->dmFloorData = DB::Aowow()->select('SELECT IF(`mapId` IN (?a), -`worldMapAreaId`, `mapId`) AS ARRAY_KEY, GROUP_CONCAT(DISTINCT `floor` SEPARATOR " ") AS "0", COUNT(DISTINCT `floor`) AS "1" FROM dbc_dungeonmap WHERE `worldMapAreaId` <> 0 GROUP BY ARRAY_KEY', self::CONTINENTS);
        if (!$this->wmOverlays || !$this->wmAreas || !$this->dmFloorData)
        {
            CLI::write('[img-maps] - could not read required dbc files: WorldMapArea.dbc ['.count($this->wmAreas ?: []).' entries]; WorldMapOverlay.dbc ['.count($this->wmOverlays ?: []).'] entries; DungeonMap.dbc ['.count($this->dmFloorData ?: []).' entries]', CLI::LOG_ERROR);
            $this->success = false;
            return false;
        }

        // DM fixups...
        // unpack + sort floors
        array_walk($this->dmFloorData, function (&$x) { $x[0] = explode(' ', $x[0]); sort($x[0]); });

        // move Dalaran from Howling Fjord to .. well .. Dalaran
        $this->dmFloorData[-4395] = $this->dmFloorData[-495];
        unset($this->dmFloorData[-495]);

        // "custom" - show second level of Ahn'Kahet not shown but present in-game
        $this->dmFloorData[619][0][] = 2;
        $this->dmFloorData[619][1]++;

        // WMA fixups...
        foreach ($this->wmAreas as &$a)
        {
            // flags added in 4.x but required for 3.3.5. Where are they? Derived from defaultDungeonMapId (also refered to as defaultDungeonFloor) being < 0 ?
            // no idea, hardcode this shit
            switch ($a['areaId'])
            {                                               //     i deem the missing '-0' a mistake > v < this will not be perpetuated
                case 4273:                                  // Ulduar         > base + 5 > 4273: ['4273-0', '4273-1', '4273-2', '4273-3', '4273-4', '4273-5']
                case 4075:                                  // SunwellPlateau > base + 1 > 4075: ['4075',   '4075-1'],
                case 3959:                                  // BlackTemple    > base + 7 > 3959: ['3959',   '3959-1', '3959-2', '3959-3', '3959-4', '3959-5', '3959-6', '3959-7'],
                    $a['flags'] = self::AREA_FLAG_DEFAULT_FLOOR_TERRAIN;
                    break;
                case 4100:                                  // CoTStratholme  > base + 1 > 4100: ['4100-1', '4100-2'],
                    $a['flags'] = self::AREA_FLAG_DEFAULT_FLOOR_TERRAIN | self::AREA_FLAG_NO_DEFAULT_FLOOR;
                    break;
                default:
                    $a['flags'] = $a['flags'] ?? 0;         // flags added in 4.x
            }

            if ($a['areaId'])
                continue;

            switch ($a['id'])
            {
                case 13:  $a['areaId'] = -6; break;         // Kalimdor
                case 14:  $a['areaId'] = -3; break;         // Eastern Kingdoms
                case 466: $a['areaId'] = -2; break;         // Outland
                case 485: $a['areaId'] = -5; break;         // Northrend
            }
        }
        $this->wmAreas[-1] = ['id' => -1, 'areaId' => -1, 'flags' => 0x0, 'mapId' => 0, 'nameINT' => 'World'];
        $this->wmAreas[-4] = ['id' => -4, 'areaId' => -4, 'flags' => 0x0, 'mapId' => 0, 'nameINT' => 'Cosmic'];

        ksort($this->wmAreas);                              // just so we can sift through the log more easily

        /*
            i should be walking through interface/worldmap first and THEN check the worldmaparea / dungeonmap from the file pattern
            floorIdx is optional and per map. (e.g. continents share their floors and yes continents can have dungeon maps)

            > <locStr>/interface/worldmap/<wma.NameINT>/<wma.NameINT>(_<dm.floorIdx>)<tileIdx>.blp
            > <locStr>/interface/worldmap/microdungeon/<parentWma.nameINT>/<wma.NameINT>/<wma.NameINT>(_<dm.floorIdx>)<tileIdx>.blp

            microdungeons (5.x+?) may be redundant with regluar map files.

            e.g.:
            > enGB/interface/worldmap/microdungeon/durotar/burningbladecoven/burningbladecoven8_12.blp

            from nameInt "durotar" we get wmaId = 4, areaTableId = 14 and mapId = 1 (floorIdx = 8 from file string)
            with mapId and floor (and wmaId) we get the coordinates from dungeonmap.dbc

            thus the map file name is: <areaTableId>-<floorIdx>.png > 14-8.png
            and the floor is named: DUNGEON_FLOOR_<wma.nameINT><floorIdx> > DUNGEON_FLOOR_DUROTAR8 (Aquelarre del Filo Ardiente) *nyak nyak nyak*


            note: some map file may have no floorIdx but the tileIdx is still separated by an underscore. Those files should be ignored.

        */
        /*  FUTURE
            foreach (CLISetup::filesInPath(self::MAP_FILE_PATTERN, true) as $file)
            {
                if (!preg_match(self::MAP_FILE_PATTERN, $file, $m))
                    continue;

                [, $basePath, $locStr, $mdParent, $nameINT, $nameINT, $floorIdx, $tileIdx] = $m;

                $loc = CLISetup::$expectedPaths[strtolower(substr($locStr, 0, 2)).strtoupper(substr($locStr, 2))] ?? LOCALE_EN;

                if ($mdParent)
                    $this->microDungeons[] = strtolower($nameINT);

                $key = strtolower($mdParent ?: $nameINT);

                $this->mapFiles[$key][0][$loc][0][$floorIdx ?: 0][$tileIdx] = $file;
                $this->mapFiles[$key][0][$loc][1] = $basePath;
                $this->mapFiles[$key][1] = ($this->mapFiles[$key][1] ?? false) ?: (($floorIdx ?: 0) > 1);
            }
        */

        return true;
    }

    private function buildMaps() : void
    {
        $sumFloors = array_sum(array_column($this->dmFloorData, 1));
        $sumAreas  = count($this->wmAreas);
        $sumMaps   = count(CLISetup::$locales) * ($sumAreas + $sumFloors);

        CLI::write('[img-maps] Processing '.$sumAreas.' zone maps and '.$sumFloors.' dungeon maps from Interface/WorldMap/ for locale: '.Lang::concat(CLISetup::$locales, callback: fn($x) => CLI::bold($x->name)));

        foreach (CLISetup::$locales as $l => $loc)
        {
            // source for mapFiles
            $mapSrcDir = '';
            if ($this->modeMask & self::M_SPAWNS)
                $mapSrcDir = $this->genSteps[self::M_SPAWNS][1][$l] ?? '';
            if (!$mapSrcDir && $this->modeMask & self::M_SUBZONES)
                $mapSrcDir = $this->genSteps[self::M_SUBZONES][1][$l] ?? '';
            if (!$mapSrcDir)
                $mapSrcDir = $this->genSteps[self::M_MAPS][1][$l] ?? '';
            if (!$mapSrcDir)
            {
                CLI::write('[img-maps] - No suitable localized map files found for locale '.CLI::bold($loc->name).'.', CLI::LOG_ERROR);
                $this->success = false;
                continue;
            }

            foreach ($this->wmAreas as $areaEntry)
            {
                $resOverlay = null;
                $resMap     = null;

                $wmaId      = $areaEntry['id'];
                $zoneId     = $areaEntry['areaId'];
                $mapId      = $areaEntry['mapId'];
                $textureStr = $areaEntry['nameINT'];
                $flags      = $areaEntry['flags'];

                [$dmFloors, $nFloors] = $this->dmFloorData[in_array($mapId, self::CONTINENTS) ? -$zoneId : $mapId] ?? [[0], 0];

                $this->progress += ($nFloors ?: 1) + ($flags & self::AREA_FLAG_DEFAULT_FLOOR_TERRAIN ? 1 : 0);
                $this->status    = ' - ' . str_pad($this->progress.'/'.($sumMaps), 10) . str_pad('('.number_format($this->progress * 100 / $sumMaps, 2).'%)', 9);

                // includes base level...
                if ($flags & self::AREA_FLAG_DEFAULT_FLOOR_TERRAIN)
                {
                    array_unshift($dmFloors, 0);            // 0 => 0, 1 => 1, etc.
                    $nFloors++;

                    // .. which is not set in dbc              0 => 1, 1 => 2, etc.
                    if ($flags & self::AREA_FLAG_NO_DEFAULT_FLOOR)
                        $dmFloors = array_combine($dmFloors, array_map(fn($x) => ++$x, $dmFloors));
                }
                else if ($dmFloors != [0])                  // 1 => 1, 2 => 2, etc.
                    $dmFloors = array_combine($dmFloors, $dmFloors);

                CLI::write(
                    '['.$loc->json().'] ' .
                    str_pad('['.$areaEntry['areaId'].']', 7) .
                    str_pad($areaEntry['nameINT'], 22) .
                    str_pad('Overlays: '.count($this->wmOverlays[$areaEntry['id']] ?? []), 14) .
                    str_pad('Dungeon Maps: '.$nFloors, 18)
                );

                $srcPath = $mapSrcDir.DIRECTORY_SEPARATOR.$textureStr;
                if (!CLISetup::fileExists($srcPath))
                {
                    CLI::write('[img-maps] - WorldMap file path '.$srcPath.' missing for selected locale '.CLI::bold($loc->name), CLI::LOG_ERROR);
                    $this->success = false;
                    continue;
                }

                $srcPath .= DIRECTORY_SEPARATOR;

                // zone has overlays (is in open world; is not multilevel)
                if (isset($this->wmOverlays[$wmaId]) && ($this->modeMask & (self::M_MAPS | self::M_SPAWNS | self::M_SUBZONES)))
                {
                    $resOverlay = $this->generateOverlay($wmaId, $srcPath);

                    // create spawn-maps if wanted
                    if ($resOverlay && ($this->modeMask & self::M_SPAWNS))
                        $this->buildSpawnMap($resOverlay, $zoneId);
                }

                // check if we can create base map anyway
                $png = $srcPath.$textureStr.'1.png';
                $blp = $srcPath.$textureStr.'1.blp';
                $hasBaseMap = CLISetup::fileExists($blp) || CLISetup::fileExists($png);

                foreach ($dmFloors as $srcFloorIdx => $outFloorIdx)
                {
                    ini_set('max_execution_time', $this->maxExecTime);

                    $doSkip   = 0x0;
                    $outPaths = [];
                    $srcFile  = $srcPath.$textureStr;
                    $outFile  = $zoneId;

                    if (!$srcFloorIdx && !$hasBaseMap)
                    {
                        CLI::write('[img-maps] - Zone has no base floor, but is referenced with base floor in dmFloors.', CLI::LOG_WARN);
                        continue;
                    }

                    if ($srcFloorIdx)
                        $srcFile .= $srcFloorIdx.'_';

                    if ($nFloors > 1)
                        if ($outFloorIdx || $flags & self::AREA_FLAG_DEFAULT_FLOOR_TERRAIN)
                            $outFile .= '-'.$outFloorIdx;

                    if ($nFloors > 1)
                        $this->multiLevelZones[$zoneId][$outFile] = $outFile;

                    if (!($this->modeMask & (self::M_MAPS | self::M_SUBZONES)))
                        continue;

                    foreach (self::DEST_DIRS as $sizeIdx => [$path, $width, $height])
                    {
                        $outPaths[$sizeIdx] = sprintf($path, strtolower($loc->json()).DIRECTORY_SEPARATOR) . $outFile . '.jpg';

                        if (!CLISetup::getOpt('force') && file_exists($outPaths[$sizeIdx]))
                        {
                            CLI::write($this->status.' - file '.$outPaths[$sizeIdx].' was already processed', CLI::LOG_BLANK, true, true);
                            $doSkip |= (1 << $sizeIdx);
                        }
                    }

                    // can't skip map creation if we are to generate subzones later. although they may already exist and get skipped anyway *shrug*
                    if ($doSkip == 0xF && !($this->modeMask & self::M_SUBZONES))
                        continue;

                    $resMap = $this->assembleImage($srcFile, self::TILEORDER, self::MAP_W, self::MAP_H);
                    if (!$resMap)
                    {
                        CLI::write('[img-maps] - Could not create image resource for '.($nFloors ? 'floor '.$srcFloorIdx : 'base level'), CLI::LOG_ERROR);
                        $this->success = false;
                        continue;
                    }

                    if ($resOverlay && !$nFloors)
                    {
                        imagecopymerge($resMap, $resOverlay, 0, 0, 0, 0, imagesx($resOverlay), imagesy($resOverlay), 100);
                        imagedestroy($resOverlay);
                    }

                    // create map
                    if ($this->modeMask & self::M_MAPS)
                    {
                        foreach (self::DEST_DIRS as $sizeIdx => [, $width, $height])
                        {
                            if ($doSkip & (1 << $sizeIdx))
                                continue;

                            if (!$this->writeImageFile($resMap, $outPaths[$sizeIdx], $width ?: self::MAP_W, $height ?: self::MAP_H))
                                $this->success = false;
                        }
                    }
                }

                // also create subzone-maps
                if ($resMap && isset($this->wmOverlays[$wmaId]) && $this->modeMask & self::M_SUBZONES)
                    $this->buildSubZones($resMap, $wmaId, $loc);

                if ($resMap)
                    imagedestroy($resMap);

                // this takes a while; ping mysql just in case
                DB::Aowow()->selectCell('SELECT 1');
            }
        }
    }

    private function buildZonesFile() : void
    {
        $areaNames = array_combine(
            array_column($this->wmAreas, 'areaId'),
            array_map(fn($x) => strtoupper($x), array_column($this->wmAreas, 'nameINT'))
        );

        if ($this->multiLevelZones)
        {
            ksort($this->multiLevelZones);
            $this->multiLevelZones = array_map('array_values', $this->multiLevelZones);
        }
        else
        {
            CLI::write('[img-maps] No data fetched from either WorldMapArea.dbc or DungeonMap.dbc. Multilevel zones will not display.', CLI::LOG_ERROR);
            $this->success = false;
        }

        $zoneAreas = [];
        // careful: nameINT may end in a number and have > 9 floors attached. see: KARAZHAN17, ULDUAR771
        foreach (CLISetup::searchGlobalStrings('/^DUNGEON_FLOOR_([a-z_]+(?:\d\d)?)(\d{1,2})\s=\s\"(.+)\";$/i') as $lId => [$_, $nameINT, $floor, $nameLOC])
        {
            // yes, multiple zones can point to the same map files
            if ($zoneIds = array_keys($areaNames, $nameINT))
            {
                foreach ($zoneIds as $zId)
                    if (isset($this->multiLevelZones[$zId]))
                        $zoneAreas[$lId][$zId][$floor] = $nameLOC;
            }
            else
                CLI::write('[img-maps] ['.$nameINT.'] from GlobalStrings.lua not found in WorldMapArea.dbc', CLI::LOG_WARN);
        }

        foreach (CLISetup::$locales as $lId => $loc)
        {
            Lang::load($loc);

            // "custom" - show second level of Ahn'Kahet not shown but present in-game
            if (isset($zoneAreas[$lId][4494]))
                $zoneAreas[$lId][4494][2] = Lang::maps('floorN', [2]);

            foreach ($zoneAreas[$lId] as $zoneId => $floorData)
            {
                $nStrings = count($floorData);
                $nFloors  = count($this->multiLevelZones[$zoneId] ?? []);
                if ($nStrings == $nFloors)
                    continue;

                // todo: just note for now, try to compensate later?
                CLI::write('[img-maps] ['.$loc->json().'] '.str_pad('['.$zoneId.']', 7).'floor count mismatch between GlobalStrings: '.$nStrings.' and image files: '.$nFloors, CLI::LOG_WARN);
            }

            ksort($zoneAreas[$lId]);

            $zoneAreas[$lId] = array_map('array_values', $zoneAreas[$lId]);

            // don't convert numbers to int in json
            $toFile  = "Mapper.multiLevelZones = ".Util::toJSON($this->multiLevelZones, 0x0).";\n\n";
            $toFile .= "var g_zone_areas = ".Util::toJSON($zoneAreas[$lId]).";";
            $file    = 'datasets/'.$loc->json().'/zones';

            if (!CLISetup::writeFile($file, $toFile))
                $this->success = false;
        }
    }

    private function buildSpawnMap(GdImage $resOverlay, int $zoneId) : void
    {
        $outFile = $this->genSteps[self::M_SPAWNS][self::GEN_IDX_DEST_INFO][0][0] . $zoneId . '.png';

        if (!CLISetup::getOpt('force') && file_exists($outFile))
        {
            CLI::write($this->status.' - file '.$outFile.' was already processed', CLI::LOG_BLANK, true, true);
            return;
        }

        $tmp = imagecreate(self::SPAWNMAP_WH, self::SPAWNMAP_WH);
        $cbg = imagecolorallocate($tmp, ...self::COLOR_WHITE);
        $cfg = imagecolorallocate($tmp, ...self::COLOR_BLACK);

        for ($y = 0; $y < self::SPAWNMAP_WH; $y++)
        {
            for ($x = 0; $x < self::SPAWNMAP_WH; $x++)
            {
                $a = imagecolorat($resOverlay, ($x * self::MAP_W) / self::SPAWNMAP_WH, ($y * self::MAP_H) / self::SPAWNMAP_WH) >> 24;
                imagesetpixel($tmp, $x, $y, $a < self::A_THRESHOLD ? $cfg : $cbg);
            }
        }

        imagecolordeallocate($tmp, $cbg);
        imagecolordeallocate($tmp, $cfg);

        if (!$this->writeImageFile($tmp, $outFile, self::SPAWNMAP_WH, self::SPAWNMAP_WH))
            $this->success = false;
    }

    private function buildSubZones(GdImage $resMap, int $wmaId, WoWLocale $loc) : void
    {
        foreach ($this->wmOverlays[$wmaId] as &$row)
        {
            $doSkip  = 0x0;
            $outFile = [];

            foreach (self::DEST_DIRS as $sizeIdx => [$path, , ])
            {
                $outFile[$sizeIdx] = sprintf($path, $loc->json() . DIRECTORY_SEPARATOR) . $row['areaTableId'].'.jpg';
                if (!CLISetup::getOpt('force') && file_exists($outFile[$sizeIdx]))
                {
                    CLI::write($this->status.' - file '.$outFile[$sizeIdx].' was already processed', CLI::LOG_BLANK, true, true);
                    $doSkip |= (1 << $sizeIdx);
                }
            }

            if ($doSkip == 0xF)
                continue;

            $subZone = imagecreatetruecolor(self::MAP_W, self::MAP_H);
            imagecopy($subZone, $resMap, 0, 0, 0, 0, imagesx($resMap), imagesy($resMap));
            imagecopy($subZone, $row['maskimage'], $row['x'], $row['y'], 0, 0, imagesx($row['maskimage']), imagesy($row['maskimage']));

            foreach (self::DEST_DIRS as $sizeIdx => [, $width, $height])
            {
                if ($doSkip & (1 << $sizeIdx))
                    continue;

                if (!$this->writeImageFile($subZone, $outFile[$sizeIdx], $width ?: self::MAP_W, $height ?: self::MAP_H))
                    $this->success = false;
            }

            imagedestroy($subZone);
        }
    }

    private function generateOverlay(int $wmaId, string $basePath) : ?GdImage
    {
        if (!isset($this->wmOverlays[$wmaId]))
            return null;

        $resOverlay = $this->createAlphaImage(self::MAP_W, self::MAP_H);

        foreach ($this->wmOverlays[$wmaId] as &$row)
        {
            $i = 1;
            $y = 0;
            while ($y < $row['h'])
            {
                $x = 0;
                while ($x < $row['w'])
                {
                    $img = $this->loadImageFile($basePath . $row['textureString'] . $i, $noSrcFile);
                    if (!$img)
                    {
                        if ($noSrcFile)
                            CLI::write('[img-maps] - overlay tile ' . $basePath . $row['textureString'] . $i . '.blp missing.', CLI::LOG_ERROR);

                        break 2;
                    }

                    imagecopy($resOverlay, $img, $row['x'] + $x, $row['y'] + $y, 0, 0, imagesx($img), imagesy($img));

                    // prepare subzone image
                    if ($this->modeMask & self::M_SUBZONES)
                    {
                        if (!isset($row['maskimage']))
                        {
                            $row['maskimage'] = $this->createAlphaImage($row['w'], $row['h']);
                            $row['maskcolor'] = imagecolorallocatealpha($row['maskimage'], ...self::COLOR_SUBZONE);
                        }

                        for ($my = 0; $my < imagesy($img); $my++)
                            for ($mx = 0; $mx < imagesx($img); $mx++)
                                if ((imagecolorat($img, $mx, $my) >> 24) < self::A_THRESHOLD)
                                    imagesetpixel($row['maskimage'], $x + $mx, $y + $my, $row['maskcolor']);
                    }

                    imagedestroy($img);

                    $x += 256;
                    $i++;
                }
                $y += 256;
            }
        }

        return $resOverlay;
    }
});

?>
