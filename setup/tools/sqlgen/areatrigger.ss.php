<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup('sql', new class extends SetupScript
{
    protected array $info = array(
        'areatrigger' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: Areatrigger from dbc and world db.']
    );

    protected array $dbcSourceFiles  = ['areatrigger'];
    protected array $worldDependency = ['areatrigger_involvedrelation', 'areatrigger_scripts', 'areatrigger_tavern', 'areatrigger_teleport', 'quest_template', 'quest_template_addon'];

    public function generate() : bool
    {
        DB::Aowow()->qry('TRUNCATE ::areatrigger');
        DB::Aowow()->qry('INSERT INTO ::areatrigger SELECT `id`, 0, 0, `orientation`, NULL, NULL FROM dbc_areatrigger');

        /* notes:
         * while areatrigger DO have dimensions, displaying them on a map is almost always futile,
         * as they are either too small to be noticable or larger than the map itself (looking at you, oculus dungeon exit)
         */

        // 1: Taverns
        CLI::write('[areatrigger] - fetching taverns');

        $addData = DB::World()->selectAssoc('SELECT `id` AS ARRAY_KEY, `name`, %i AS "type" FROM areatrigger_tavern', AT_TYPE_TAVERN);
        foreach ($addData as $id => $ad)
            DB::Aowow()->qry('UPDATE ::areatrigger SET %a WHERE `id` = %i', $ad, $id);


        // 2: Quest Objectives
        CLI::write('[areatrigger] - satisfying quest objectives');

        $addData = DB::World()->selectAssoc('SELECT atir.`id` AS ARRAY_KEY, `qt`.ID AS "quest", NULLIF(qt.`AreaDescription`, "") AS "name", qta.`SpecialFlags` FROM quest_template qt LEFT JOIN quest_template_addon qta ON qta.`ID` = qt.`ID` JOIN areatrigger_involvedrelation atir ON atir.`quest` = qt.`ID`');
        foreach ($addData as $id => $ad)
        {
            if (!($ad['SpecialFlags'] & QUEST_FLAG_SPECIAL_EXT_COMPLETE))
                CLI::write('[areatrigger]   '.str_pad('['.$id.']', 8).' is involved in quest '.CLI::bold($ad['quest']).', but quest is not flagged for external completion (SpecialFlags & '.Util::asHex(QUEST_FLAG_SPECIAL_EXT_COMPLETE).')', CLI::LOG_WARN);

            DB::Aowow()->qry('UPDATE ::areatrigger SET `name` = %s, type = %i, `quest` = %i WHERE `id` = %i', $ad['name'], AT_TYPE_OBJECTIVE, $ad['quest'], $id);
        }


        // 3: Teleporter
        CLI::write('[areatrigger] - teleporter type and name');

        $addData = DB::World()->selectAssoc(
           'SELECT   `ID`          AS ARRAY_KEY,  0 AS "cuFlags", `Name`         AS "name" FROM areatrigger_teleport UNION
            SELECT s.`entryorguid` AS ARRAY_KEY, %i AS "cuFlags", "SAI Teleport" AS "name" FROM smart_scripts s JOIN areatrigger_scripts a ON a.`entry` = s.`entryorguid` WHERE a.`ScriptName` = "SmartTrigger" AND s.`source_type` = %i AND s.`action_type` = %i',
            AREATRIGGER_CU_SMART, SmartAI::SRC_TYPE_AREATRIGGER, SmartAction::ACTION_TELEPORT
        );
        foreach ($addData as $id => $ad)
            DB::Aowow()->qry('UPDATE ::areatrigger SET `name` = %s, `type` = %i, `cuFlags`= `cuFlags` | %i WHERE `id` = %i', $ad['name'], AT_TYPE_TELEPORT, $ad['cuFlags'], $id);


        // 4/5 Scripted
        CLI::write('[areatrigger] - assigning scripts');

        $addData = DB::World()->selectAssoc(
           'SELECT `entry` AS ARRAY_KEY, IF(`ScriptName` = "SmartTrigger", NULL, `ScriptName`) AS "0", IF(`ScriptName` = "SmartTrigger", %i, %i) AS "1", IF(`ScriptName` = "SmartTrigger", %i, 0) AS "2" FROM areatrigger_scripts WHERE `entry` NOT IN %in',
            AT_TYPE_SMART, AT_TYPE_SCRIPT, AREATRIGGER_CU_SMART, array_keys($addData)
        );
        foreach ($addData as $id => [$name, $type, $cuFlags])
            DB::Aowow()->qry('UPDATE ::areatrigger SET `name` = %s, `type` = %i, `cuFlags` = `cuFLags` | %i WHERE `id` = %i', $name, $type, $cuFlags, $id);

        $this->reapplyCCFlags('areatrigger', Type::AREATRIGGER);

        return true;
    }
});

?>
