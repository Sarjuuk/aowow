<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/*  provide these with basic content
    aowow_announcements
    aowow_articles
    aowow_config
    aowow_home_featuredbox
    aowow_home_featuredbox_overlay
    aowow_home_oneliners
    aowow_home_titles
    aowow_sourcestrings
*/


class SqlGen
{
    const MODE_NORMAL   = 1;
    const MODE_FIRSTRUN = 2;
    const MODE_UPDATE   = 3;

    private static $tables = array(                         // [dbcName, saveDbc, AowowDeps, TCDeps]
        'achievementcategory'      => ['achievement_category',          false, null, null],
        'achievementcriteria'      => ['achievement_criteria',          false, null, null],
        'itemenchantmentcondition' => ['spellitemenchantmentcondition', false, null, null],
        'itemextendedcost'         => ['itemextendedcost',              false, null, null],
        'itemlimitcategory'        => ['itemlimitcategory',             false, null, null],
        'itemrandomproppoints'     => ['randproppoints',                false, null, null],
        'lock'                     => ['lock',                          true,  null, null],
        'mailtemplate'             => ['mailtemplate',                  false, null, null],
        'scalingstatdistribution'  => ['scalingstatdistribution',       true,  null, null],
        'scalingstatvalues'        => ['scalingstatvalues',             true,  null, null],
        'spellfocusobject'         => ['spellfocusobject',              false, null, null],
        'spelloverride'            => ['overridespelldata',             false, null, null],
        'spellrange'               => ['spellrange',                    false, null, null],
        'spellvariables'           => ['spelldescriptionvariables',     false, null, null],
        'totemcategory'            => ['totemcategory',                 false, null, null],
        'icons'                    => [null, null, null,                             null],
        'glyphproperties'          => [null, true, ['icons'],                        null],
        'talents'                  => [null, null, null,                             null],
        'classes'                  => [null, null, null,                             null],
        'factions'                 => [null, null, null,                             null],
        'factiontemplate'          => [null, null, null,                             null],
        'holidays'                 => [null, null, null,                             null],
        'itemrandomenchant'        => [null, null, null,                             null],
        'races'                    => [null, null, null,                             null],
        'shapeshiftforms'          => [null, null, null,                             null],
        'skillline'                => [null, null, ['icons'],                        null],
        'emotes'                   => [null, null, null,                             null],
        'sounds'                   => [null, null, null,                             null],
        'itemenchantment'          => [null, null, null,                             ['spell_enchant_proc_data']],
        'achievement'              => [null, null, ['icons'],                        ['dbc_achievement', 'disables']],
        'creature'                 => [null, null, null,                             ['creature_template', 'creature_template_locale', 'creature_classlevelstats', 'instance_encounters']],
        'currencies'               => [null, null, null,                             ['item_template', 'item_template_locale']],
        'events'                   => [null, null, null,                             ['game_event', 'game_event_prerequisite']],
        'objects'                  => [null, null, null,                             ['gameobject_template', 'gameobject_template_locale', 'gameobject_questitem']],
        'pet'                      => [null, null, ['icons'],                        ['creature_template', 'creature']],
        'quests'                   => [null, null, null,                             ['quest_template', 'quest_template_addon', 'quest_template_locale', 'game_event', 'game_event_seasonal_questrelation', 'disables']],
        'quests_startend'          => [null, null, null,                             ['creature_queststarter', 'creature_questender', 'game_event_creature_quest', 'gameobject_queststarter', 'gameobject_questender', 'game_event_gameobject_quest', 'item_template']],
        'spell'                    => [null, null, ['icons'],                        ['skill_discovery_template', 'item_template', 'creature_template', 'creature_template_addon', 'smart_scripts', 'npc_trainer', 'disables', 'spell_ranks', 'spell_dbc']],
        'spelldifficulty'          => [null, null, null,                             ['spelldifficulty_dbc']],
        'taxi' /* nodes + paths */ => [null, null, null,                             ['creature_template', 'creature']],
        'titles'                   => [null, null, null,                             ['quest_template', 'game_event_seasonal_questrelation', 'game_event', 'achievement_reward']],
        'items'                    => [null, null, ['icons'],                        ['item_template', 'item_template_locale', 'spell_group', 'game_event']],
        'spawns' /* + waypoints */ => [null, null, null,                             ['creature', 'creature_addon', 'gameobject', 'gameobject_template', 'vehicle_accessory', 'vehicle_accessory_template', 'script_waypoint', 'waypoints', 'waypoint_data']],
        'zones'                    => [null, null, null,                             ['access_requirement']],
        'itemset'                  => [null, null, ['spell'],                        ['item_template', 'game_event']],
        'item_stats' /* + ench */  => [null, null, ['items', 'spell'],               null],
        'source'                   => [null, null, ['spell', 'achievement'],         ['npc_vendor', 'game_event_npc_vendor', 'creature', 'quest_template', 'quest_template_addon', 'playercreateinfo_item', 'npc_trainer', 'skill_discovery_template', 'playercreateinfo_skills', 'achievement_reward', 'skill_perfect_item_template']]
    );

    public  static $cliOpts   = [];
    private static $shortOpts = 'h';
    private static $longOpts  = ['sql::', 'help', 'sync:']; // general
    private static $mode      = 0;

    public static $subScripts = [];

    public static $defaultExecTime = 30;
    public static $stepSize        = 1000;

    public static function init($mode = self::MODE_NORMAL, array $updScripts = [])
    {
        self::$defaultExecTime = ini_get('max_execution_time');
        $doScripts = null;

        if (getopt(self::$shortOpts, self::$longOpts) || $mode == self::MODE_FIRSTRUN)
            self::handleCLIOpts($doScripts);
        else if ($mode != self::MODE_UPDATE)
        {
            self::printCLIHelp();
            exit;
        }

        // check passed subscript names; limit to real scriptNames
        self::$subScripts = array_keys(self::$tables);
        if ($doScripts || $updScripts)
            self::$subScripts = array_intersect($doScripts ?: $updScripts, self::$subScripts);
        else if ($doScripts === null)
            self::$subScripts = [];

        if (!CLISetup::$localeIds /* && this script has localized text */)
        {
            CLISetup::log('No valid locale specified. Check your config or --locales parameter, if used', CLISetup::LOG_ERROR);
            exit;
        }

        self::$mode = $mode;
    }

    private static function handleCLIOpts(&$doTbls)
    {
        $doTbls = [];
        $_ = getopt(self::$shortOpts, self::$longOpts);

        if ((isset($_['help']) || isset($_['h'])) && empty($_['sql']))
        {
            self::printCLIHelp();
            exit;
        }

        // required subScripts
        if (!empty($_['sync']))
        {
            $sync = explode(',', $_['sync']);
            foreach (self::$tables as $name => $info)
                if (!empty($info[3]) && array_intersect($sync, $info[3]))
                    $doTbls[] = $name;

            // recursive dependencies
            foreach (self::$tables as $name => $info)
                if (!empty($info[2]) && array_intersect($doTbls, $info[2]))
                    $doTbls[] = $name;

            $doTbls = $doTbls ? array_unique($doTbls) : null;
        }
        else if (!empty($_['sql']))
            $doTbls = explode(',', $_['sql']);
    }

    public static function printCLIHelp()
    {
        echo "\nusage: php aowow --sql=<tableList,> [-h --help]\n\n";
        echo "--sql              : available tables:\n";
        foreach (self::$tables as $t => $info)
            echo " * ".str_pad($t, 24).(isset($info[3]) ? ' - TC deps: '.implode(', ', $info[3]) : '').(isset($info[2]) ? ' - Aowow deps: '.implode(', ', $info[2]) : '')."\n";

        echo "-h --help          : shows this info\n";
    }

    public static function generate($tableName, array $updateIds = [])
    {
        if (!isset(self::$tables[$tableName]))
        {
            CLISetup::log('SqlGen::generate - invalid table given', CLISetup::LOG_ERROR);
            return false;
        }

        if (!empty(self::$tables[$tableName][0]))           // straight copy from dbc source
        {
            $tbl = self::$tables[$tableName];               // shorthand
            CLISetup::log('SqlGen::generate() - copying '.$tbl[0].'.dbc into aowow_'.$tableName);

            $dbc = new DBC($tbl[0], ['temporary' => CLISetup::$tmpDBC, 'tableName' => 'aowow_'.$tableName]);
            if ($dbc->error)
                return false;

            return !!$dbc->readFile();
        }
        else if (file_exists('setup/tools/sqlgen/'.$tableName.'.func.php'))
        {
            $customData = $reqDBC = [];

            CLISetup::log('SqlGen::generate() - filling aowow_'.$tableName.' with data');

            require_once 'setup/tools/sqlgen/'.$tableName.'.func.php';

            if (function_exists($tableName))
            {
                // check for required auxiliary DBC files
                foreach ($reqDBC as $req)
                    if (!CLISetup::loadDBC($req))
                        return false;

                $success = $tableName($updateIds);

                // apply post generator custom data
                foreach ($customData as $id => $data)
                    if ($data)
                        DB::Aowow()->query('UPDATE ?_'.$tableName.' SET ?a WHERE id = ?d', $data, $id);
            }
            else
                CLISetup::log(' - subscript \''.$tableName.'\' not defined in included file', CLISetup::LOG_ERROR);

            return $success;
        }
        else
            CLISetup::log(sprintf(ERR_MISSING_INCL, $tableName, 'setup/tools/sqlgen/'.$tableName.'.func.php'), CLISetup::LOG_ERROR);

    }

    public static function getMode()
    {
        return self::$mode;
    }
}

?>
