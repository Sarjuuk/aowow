<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command = 'areatrigger';

    protected $tblDependencyTC = ['areatrigger_involvedrelation', 'areatrigger_scripts', 'areatrigger_tavern', 'areatrigger_teleport', 'quest_template', 'quest_template_addon'];
    protected $dbcSourceFiles  = ['areatrigger', 'worldmaparea', 'dungeonmap'];

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE ?_areatrigger');
        DB::Aowow()->query('INSERT INTO ?_areatrigger SELECT id, 0, 0, NULL, orientation, NULL, NULL, NULL, NULL, NULL, NULL FROM dbc_areatrigger');

        /* notes:
         * while areatrigger DO have dimensions, displaying them on a map is almost always futile,
         * as they are either too small to be noticable or larger than the map itself (looking at you, oculus dungeon exit)
         */

        // 1: Taverns
        CLI::write('   - fetching taverns');

        $addData = DB::World()->select('SELECT id AS ARRAY_KEY, name, ?d AS `type` FROM areatrigger_tavern', AT_TYPE_TAVERN);
        foreach ($addData as $id => $ad)
            DB::Aowow()->query('UPDATE ?_areatrigger SET ?a WHERE id = ?d', $ad, $id);

        // 2: Teleporter + teleporting 4: Smart Trigger
        CLI::write('   - calculation teleporter coordinates');

        $addData = DB::World()->select('
            SELECT ID          AS ARRAY_KEY, Name  AS `name`,    target_map AS `map`, target_position_x AS `posY`, target_position_y AS `posX`, target_orientation AS `orientation` FROM areatrigger_teleport UNION
            SELECT entryorguid AS ARRAY_KEY, "TBD" AS `name`, action_param1 AS `map`, target_x          AS `posY`, target_y          AS `posX`, target_o AS           `orientation` FROM smart_scripts        WHERE source_type = 2 AND action_type = 62
        ');
        foreach ($addData as $id => $ad)
        {
            $points = Game::worldPosToZonePos($ad['map'], $ad['posX'], $ad['posY']/*, 0*/);
            if (!$points)
            {
                CLI::write('   * AT '.$id.' teleporter endpoint '.CLI::bold($ad['name']).' could not be matched to displayable area [M:'.$ad['map'].'; X:'.$ad['posY'].'; Y:'.$ad['posX'].']', CLI::LOG_WARN);
                DB::Aowow()->query('UPDATE ?_areatrigger SET `name` = ?, `type` = ?d WHERE id = ?d', $ad['name'], AT_TYPE_TELEPORT, $id);
                continue;
            }

            $update = array(
                'name'      => $ad['name'],
                'type'      => AT_TYPE_TELEPORT,
                'teleportA' => $points[0]['areaId'],
                'teleportX' => $points[0]['posX'],
                'teleportY' => $points[0]['posY'],
                'teleportO' => $ad['orientation'],
                'teleportF' => $points[0]['floor']
            );

            DB::Aowow()->query('UPDATE ?_areatrigger SET ?a WHERE id = ?d', $update, $id);
        }

        // 3: Quest Objectives
        CLI::write('   - satisfying quest objectives');

        $addData = DB::World()->select('SELECT atir.id AS ARRAY_KEY, qt.ID AS quest, NULLIF(qt.AreaDescription, "") AS `name`, qta.SpecialFlags FROM quest_template qt LEFT JOIN quest_template_addon qta ON qta.ID = qt.ID JOIN areatrigger_involvedrelation atir ON atir.quest = qt.ID');
        foreach ($addData as $id => $ad)
        {
            if (!($ad['SpecialFlags'] & QUEST_FLAG_SPECIAL_EXT_COMPLETE))
                CLI::write('   * Areatrigger '.CLI::bold($id).' is involved in Quest '.CLI::bold($ad['quest']).', but Quest is not flagged for external completion (SpecialFlags & '.Util::asHex(QUEST_FLAG_SPECIAL_EXT_COMPLETE).')', CLI::LOG_WARN);

            DB::Aowow()->query('UPDATE ?_areatrigger SET name = ?, type = ?d, quest = ?d WHERE id = ?d', $ad['name'], AT_TYPE_OBJECTIVE, $ad['quest'], $id);
        }

        // 4/5 Scripted
        CLI::write('   - assigning scripts');

        $addData = DB::World()->select('SELECT entry AS ARRAY_KEY, IF(ScriptName = "SmartTrigger", NULL, ScriptName) AS `name`, IF(ScriptName = "SmartTrigger", 4, 5) AS `type` FROM areatrigger_scripts');
        foreach ($addData as $id => $ad)
            DB::Aowow()->query('UPDATE ?_areatrigger SET ?a WHERE id = ?d', $ad, $id);

        return true;
    }
});

?>
