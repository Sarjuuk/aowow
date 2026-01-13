<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected $info = array(
        'taxi' => [[], CLISetup::ARGV_PARAM, 'Compiles supplemental data for type: NPC from dbc and world db.']
    );

    protected $dbcSourceFiles  = ['taxipath', 'taxinodes', 'worldmaparea', 'worldmaptransforms', 'factiontemplate'];
    protected $worldDependency = ['creature', 'creature_template'];
    protected $setupAfter      = [['dungeonmap', 'worldmaparea'], []]; // accessed by WorldPosition::toZonePos

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE ?_taxipath');
        DB::Aowow()->query('TRUNCATE ?_taxinodes');

        /*********/
        /* paths */
        /*********/

        DB::Aowow()->query('INSERT INTO ?_taxipath SELECT tp.id, tp.startNodeId, tp.endNodeId FROM dbc_taxipath tp WHERE tp.startNodeId > 0 AND tp.EndNodeId > 0');

        // paths are monodirectional and thus exist twice for regular flight travel (which is bidirectional)
        $paths = DB::Aowow()->select('SELECT id AS ARRAY_KEY, tp.* FROM ?_taxipath tp');
        foreach ($paths as $i => $p)
        {
            foreach ($paths as $j => $_)
            {
                if ($_['startNodeId'] != $p['endNodeId'] || $_['endNodeId'] != $p['startNodeId'])
                    continue;

                DB::Aowow()->query('DELETE FROM ?_taxipath WHERE id = ?d', $j);
                unset($paths[$j]);
                unset($paths[$i]);
                break;
            }
        }


        /*********/
        /* nodes */
        /*********/

        // all sensible nodes
        $fNodes  = DB::Aowow()->select(
           'SELECT tn.`id`,
                   tn.`mapId`,
                   100 - ROUND((tn.`posY` - wma.`right`)  * 100 / (wma.`left` - wma.`right`), 1) AS "mapX",
                   100 - ROUND((tn.`posX` - wma.`bottom`) * 100 / (wma.`top` - wma.`bottom`), 1) AS "mapY",
                   0 AS "areaId",
                   0 AS "areaX",
                   0 AS "areaY",
                   1 AS `type`, 0 AS `typeId`, 1 AS "reactA", 1 AS "reactH",
                   tn.`name_loc0`, tn.`name_loc2`, tn.`name_loc3`, tn.`name_loc4`, tn.`name_loc6`, tn.`name_loc8`,
                   tn.`mapId` AS "_mapId", tn.`posX` AS "_posX", tn.`posY` AS "_posY",
                   IF (tn.`id` NOT IN (15, 148, 225, 235) AND (
                          tn.`id` IN (64, 250) OR (
                              tn.`name_loc0` NOT LIKE "%Transport%" AND tn.`name_loc0` NOT LIKE "%Quest%" AND
                              tn.`name_loc0` NOT LIKE "%Start%"     AND tn.`name_loc0` NOT LIKE "%End%"
                          )
                      ), 0, 1) AS "_scripted"
            FROM   dbc_taxinodes tn
            JOIN   dbc_worldmaparea wma ON ( tn.`mapId` = wma.`mapId` AND tn.`posX` BETWEEN wma.`bottom` AND wma.`top` AND tn.`posY` BETWEEN wma.`right` AND wma.`left`)
            WHERE  wma.`areaId` = 0 AND wma.`mapId` = tn.`mapId`
            UNION
            SELECT tn.`id`,
                   wmt.`targetMapId`,
                   100 - ROUND((tn.`posY` + wmt.`offsetY` - wma.`right`)  * 100 / (wma.`left` - wma.`right`), 1) AS "mapX",
                   100 - ROUND((tn.`posX` + wmt.`offsetX` - wma.`bottom`) * 100 / (wma.`top` - wma.`bottom`), 1) AS "mapY",
                   0 AS "areaId",
                   0 AS "areaX",
                   0 AS "areaY",
                   1 AS `type`, 0 AS `typeId`, 1 AS "reactA", 1 AS "reactH",
                   tn.`name_loc0`, tn.`name_loc2`, tn.`name_loc3`, tn.`name_loc4`, tn.`name_loc6`, tn.`name_loc8`,
                   tn.`mapId` AS "_mapId", tn.`posX` AS "_posX", tn.`posY` AS "_posY",
                   IF (tn.name_loc0 NOT LIKE "%Transport%" AND tn.name_loc0 NOT LIKE "%Quest%" AND tn.name_loc0 NOT LIKE "%Start%" AND tn.name_loc0 NOT LIKE "%End%",
                       0, 1 ) AS "_scripted"
            FROM   dbc_taxinodes tn
            JOIN   dbc_worldmaptransforms wmt ON ( tn.`mapId` = wmt.`sourceMapId`  AND tn.`posX`                 BETWEEN wmt.`minX`   AND wmt.`maxX` AND tn.`posY`                 BETWEEN wmt.`minY`  AND wmt.`maxY`)
            JOIN   dbc_worldmaparea wma       ON ( wmt.`targetMapId` = wma.`mapId` AND tn.`posX` + wmt.`offsetX` BETWEEN wma.`bottom` AND wma.`top`  AND tn.`posY` + wmt.`offsetY` BETWEEN wma.`right` AND wma.`left`)
            WHERE  wma.`areaId` = 0 AND wmt.`sourcemapId` = tn.`mapId`'
        );

        // all available flightmaster
        $fMaster = DB::World()->select('SELECT ct.`entry`, ct.`faction`, c.`map`, c.`position_x` AS "posX", c.`position_y` AS "posY" FROM creature_template ct JOIN creature c ON c.`id` = ct.`entry` WHERE ct.`npcflag` & ?d OR c.`npcflag` & ?d',
            NPC_FLAG_FLIGHT_MASTER, NPC_FLAG_FLIGHT_MASTER
        );

        // fetch reactions per faction
        $factions = DB::Aowow()->query(
           'SELECT `id` AS ARRAY_KEY,
                   IF(`enemyFactionId1` = 1 OR `enemyFactionId2` = 1 OR `enemyFactionId3` = 1 OR `enemyFactionId4` = 1 OR `hostileMask` & 0x3, -1, 1) AS "reactA",
                   IF(`enemyFactionId1` = 2 OR `enemyFactionId2` = 2 OR `enemyFactionId3` = 2 OR `enemyFactionId4` = 2 OR `hostileMask` & 0x5, -1, 1) AS "reactH"
            FROM   dbc_factiontemplate
            WHERE  `id` IN (?a)',
        array_column($fMaster, 'faction'));

        foreach ($fNodes as $n)
        {
            // assign nearest flightmaster and its reaction to node
            if ($n['_scripted'])
                $n['type'] = $n['typeId'] = 0;
            else
            {
                foreach ($fMaster as &$c)
                {
                    if ($c['map'] != $n['_mapId'])
                        continue;

                    $dist = pow($c['posX'] - $n['_posX'], 2) + pow($c['posY'] - $n['_posY'], 2);
                    if ($dist > 1000)
                        continue;

                    if (!isset($n['_dist']) || $n['_dist'] < $dist)
                    {
                        $n['_dist']  = $dist;
                        $n['typeId'] = $c['entry'];
                        $n['reactA'] = $factions[$c['faction']]['reactA'] ?? null;
                        $n['reactH'] = $factions[$c['faction']]['reactH'] ?? null;
                    }
                }
            }

            // calculate zone pos
            if ($points = WorldPosition::toZonePos($n['_mapId'], $n['_posX'], $n['_posY']))
            {
                $n['areaId'] = $points[0]['areaId'];
                $n['areaX']  = $points[0]['posX'];
                $n['areaY']  = $points[0]['posY'];
            }

            unset($n['_mapId'], $n['_posX'], $n['_posY'], $n['_dist'], $n['_scripted']);

            DB::Aowow()->query('INSERT INTO ?_taxinodes VALUES (?a)', array_values($n));
        }

        return true;
    }
});

?>
