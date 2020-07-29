<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command = 'taxi';                            // path & nodes

    protected $tblDependencyTC = ['creature', 'creature_template'];
    protected $dbcSourceFiles  = ['taxipath', 'taxinodes', 'worldmaparea', 'worldmaptransforms', 'factiontemplate'];

    public function generate(array $ids = []) : bool
    {
        /*********/
        /* paths */
        /*********/

        DB::Aowow()->query('REPLACE INTO ?_taxipath SELECT tp.id, tp.startNodeId, tp.endNodeId FROM dbc_taxipath tp WHERE tp.startNodeId > 0 AND tp.EndNodeId > 0');

        // paths are monodirectional and thus exist twice for regular flight travel (which is bidirectional)
        $paths = DB::Aowow()->select('SELECT id AS ARRAY_KEY, tp.* FROM ?_taxipath tp');
        foreach ($paths as $i => $p)
        {
            foreach ($paths as $j => $_)
            {
                if ($_['startNodeId'] == $p['endNodeId'] AND $_['endNodeId'] == $p['startNodeId'])
                {
                    DB::Aowow()->query('DELETE FROM ?_taxipath WHERE id = ?d', $j);
                    unset($paths[$j]);
                    unset($paths[$i]);
                    break;
                }
            }
        }


        /*********/
        /* nodes */
        /*********/

        // all sensible nodes
        $fNodes  = DB::Aowow()->select(
            'SELECT
                tn.id,
                tn.mapId,
                100 - ROUND((tn.posY - wma.right) * 100 / (wma.left - wma.right), 1) AS posX,
                100 - ROUND((tn.posX - wma.bottom) * 100 / (wma.top - wma.bottom), 1) AS poxY,
                1 AS type,
                0 AS typeId,
                1 AS reactA,
                1 AS reactH,
                tn.name_loc0, tn.name_loc2, tn.name_loc3, tn.name_loc4, tn.name_loc6, tn.name_loc8,
                tn.mapId AS origMap,
                tn.posX AS origPosX,
                tn.posY AS origPosY,
                IF (tn.id NOT IN (15, 148, 225, 235) AND
                    (
                        tn.id IN (64, 250) OR
                        (
                            tn.name_loc0 NOT LIKE "%Transport%" AND
                            tn.name_loc0 NOT LIKE "%Quest%" AND
                            tn.name_loc0 NOT LIKE "%Start%" AND
                            tn.name_loc0 NOT LIKE "%End%"
                        )
                    ), 0, 1) AS scripted
            FROM
                dbc_taxinodes tn
            JOIN
                dbc_worldmaparea wma ON ( tn.mapId = wma.mapId AND tn.posX BETWEEN wma.bottom AND wma.top AND tn.posY BETWEEN wma.right AND wma.left)
            WHERE
                wma.areaId = 0 AND
                wma.mapId = tn.mapId
            UNION
            SELECT
                tn.id,
                wmt.targetMapId,
                100 - ROUND((tn.posY + wmt.offsetY - wma.right) * 100 / (wma.left - wma.right), 1) AS posX,
                100 - ROUND((tn.posX + wmt.offsetX - wma.bottom) * 100 / (wma.top - wma.bottom), 1) AS poxY,
                1 AS type,
                0 AS typeId,
                1 AS reactA,
                1 AS reactH,
                tn.name_loc0, tn.name_loc2, tn.name_loc3, tn.name_loc4, tn.name_loc6, tn.name_loc8,
                tn.mapId AS origMap,
                tn.posX AS origPosX,
                tn.posY AS origPosY,
                IF (tn.name_loc0 NOT LIKE "%Transport%" AND tn.name_loc0 NOT LIKE "%Quest%" AND tn.name_loc0 NOT LIKE "%Start%" AND tn.name_loc0 NOT LIKE "%End%",
                    0,
                    1
                ) AS scripted
            FROM
                dbc_taxinodes tn
            JOIN
                dbc_worldmaptransforms wmt ON ( tn.mapId = wmt.sourceMapId AND tn.posX BETWEEN wmt.minX AND wmt.maxX AND tn.posY BETWEEN wmt.minY AND wmt.maxY)
            JOIN
                dbc_worldmaparea wma ON ( wmt.targetMapId = wma.mapId AND tn.posX + wmt.offsetX BETWEEN wma.bottom AND wma.top AND tn.posY + wmt.offsetY BETWEEN wma.right AND wma.left)
            WHERE
                wma.areaId = 0 AND
                wmt.sourcemapId = tn.mapId'
        );

        // all available flightmaster
        $fMaster = DB::World()->select(
            'SELECT ct.entry, ct.faction, c.map, c.position_x AS posX, c.position_y AS posY FROM creature_template ct JOIN creature c ON c.id = ct.entry WHERE ct.npcflag & ?d OR c.npcflag & ?d',
            NPC_FLAG_FLIGHT_MASTER, NPC_FLAG_FLIGHT_MASTER
        );

        // assign nearest flightmaster to node
        foreach ($fNodes as &$n)
        {
            foreach ($fMaster as &$c)
            {
                if ($c['map'] != $n['origMap'])
                    continue;

                $dist = pow($c['posX'] - $n['origPosX'], 2) + pow($c['posY'] - $n['origPosY'], 2);
                if ($dist > 1000)
                    continue;

                if (!isset($n['dist']) || $n['dist'] < $dist)
                {
                    $n['dist']    = $dist;
                    $n['typeId']  = $c['entry'];
                    $n['faction'] = $c['faction'];
                }
            }
        }

        unset($n);

        // fetch reactions per faction
        $factions = DB::Aowow()->query('
            SELECT
                id AS ARRAY_KEY,
                IF(enemyFactionId1 = 1 OR enemyFactionId2 = 1 OR enemyFactionId3 = 1 OR enemyFactionId4 = 1 OR hostileMask & 0x3, -1, 1) AS reactA,
                IF(enemyFactionId1 = 2 OR enemyFactionId2 = 2 OR enemyFactionId3 = 2 OR enemyFactionId4 = 2 OR hostileMask & 0x5, -1, 1) AS reactH
            FROM
                dbc_factiontemplate
            WHERE
                id IN (?a)',
            array_column($fNodes, 'faction')
        );

        foreach ($fNodes as $n)
        {
            // if (empty($n['faction']))
            // {
                // CLI::write(' - ['.$n['id'].'] "'.$n['name_loc0'].'" has no NPC assigned ... skipping', CLI::LOG_WARN);
                // continue;
            // }

            if ($n['scripted'] || empty($n['faction']))
                $n['type'] = $n['typeId'] = 0;
            else if (isset($factions[$n['faction']]))
            {
                $n['reactA'] = $factions[$n['faction']]['reactA'];
                $n['reactH'] = $factions[$n['faction']]['reactH'];
            }

            unset($n['faction'], $n['origMap'], $n['origPosX'], $n['origPosY'], $n['dist'], $n['scripted']);

            DB::Aowow()->query('REPLACE INTO ?_taxinodes VALUES (?a)', array_values($n));
        }


        return true;
    }
});

?>
