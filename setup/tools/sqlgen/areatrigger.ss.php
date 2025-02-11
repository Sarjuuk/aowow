<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup('sql', new class extends SetupScript
{
    protected $info = array(
        'areatrigger' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: Areatrigger from dbc and world db.']
    );

    protected $dbcSourceFiles  = ['areatrigger'];
    protected $worldDependency = ['areatrigger_involvedrelation', 'areatrigger_scripts', 'areatrigger_tavern', 'areatrigger_teleport', 'quest_template', 'quest_template_addon'];

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE ?_areatrigger');
        DB::Aowow()->query('INSERT INTO ?_areatrigger SELECT `id`, 0, 0, `mapId`, `posX`, `posY`, `orientation`, NULL, NULL FROM dbc_areatrigger');

        /* notes:
         * while areatrigger DO have dimensions, displaying them on a map is almost always futile,
         * as they are either too small to be noticable or larger than the map itself (looking at you, oculus dungeon exit)
         */

        // 1: Taverns
        CLI::write('[areatrigger] - fetching taverns');

        $addData = DB::World()->select('SELECT `id` AS ARRAY_KEY, `name`, ?d AS `type` FROM areatrigger_tavern', AT_TYPE_TAVERN);
        foreach ($addData as $id => $ad)
            DB::Aowow()->query('UPDATE ?_areatrigger SET ?a WHERE `id` = ?d', $ad, $id);


        // 2: Teleporter
        CLI::write('[areatrigger] - teleporter type and name');

        $addData = DB::World()->select(
           'SELECT `ID`          AS ARRAY_KEY, `Name`         AS `name` FROM areatrigger_teleport UNION
            SELECT `entryorguid` AS ARRAY_KEY, "SAI Teleport" AS `name` FROM smart_scripts WHERE `source_type` = ?d AND `action_type` = ?d',
            SmartAI::SRC_TYPE_AREATRIGGER, SmartAction::ACTION_TELEPORT
        );
        foreach ($addData as $id => $ad)
            DB::Aowow()->query('UPDATE ?_areatrigger SET `name` = ?, `type` = ?d WHERE `id` = ?d', $ad['name'], AT_TYPE_TELEPORT, $id);


        // 3: Quest Objectives
        CLI::write('[areatrigger] - satisfying quest objectives');

        $addData = DB::World()->select('SELECT atir.`id` AS ARRAY_KEY, `qt`.ID AS `quest`, NULLIF(qt.`AreaDescription`, "") AS `name`, qta.`SpecialFlags` FROM quest_template qt LEFT JOIN quest_template_addon qta ON qta.`ID` = qt.`ID` JOIN areatrigger_involvedrelation atir ON atir.`quest` = qt.`ID`');
        foreach ($addData as $id => $ad)
        {
            if (!($ad['SpecialFlags'] & QUEST_FLAG_SPECIAL_EXT_COMPLETE))
                CLI::write('[areatrigger]   '.str_pad('['.$id.']', 8).' is involved in quest '.CLI::bold($ad['quest']).', but quest is not flagged for external completion (SpecialFlags & '.Util::asHex(QUEST_FLAG_SPECIAL_EXT_COMPLETE).')', CLI::LOG_WARN);

            DB::Aowow()->query('UPDATE ?_areatrigger SET `name` = ?, type = ?d, `quest` = ?d WHERE `id` = ?d', $ad['name'], AT_TYPE_OBJECTIVE, $ad['quest'], $id);
        }


        // 4/5 Scripted
        CLI::write('[areatrigger] - assigning scripts');

        $addData = DB::World()->select('SELECT `entry` AS ARRAY_KEY, IF(`ScriptName` = "SmartTrigger", NULL, `ScriptName`) AS `name`, IF(`ScriptName` = "SmartTrigger", 4, 5) AS `type` FROM areatrigger_scripts');
        foreach ($addData as $id => $ad)
            DB::Aowow()->query('UPDATE ?_areatrigger SET ?a WHERE `id` = ?d', $ad, $id);

        $this->reapplyCCFlags('areatrigger', Type::AREATRIGGER);

        return true;
    }
});

?>
