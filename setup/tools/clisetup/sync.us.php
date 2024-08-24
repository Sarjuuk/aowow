<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/************************************************/
/* automaticly synchronize with TC world tables */
/************************************************/

CLISetup::registerUtility(new class extends UtilityScript
{
    public $argvFlags   = CLISetup::ARGV_ARRAY | CLISetup::ARGV_OPTIONAL;
    public $optGroup    = CLISetup::OPT_GRP_UTIL;

    public const COMMAND     = 'sync';
    public const DESCRIPTION = 'Regenerate tables/files that depend on given world DB table.';
    public const APPENDIX    = '=<worldTableList,>';

    public const REQUIRED_DB = [DB_AOWOW, DB_WORLD];

    // sqlToDo, buildToDo, null, null // iinn
    public function run(&$args) : bool
    {
        $s = &$args['doSql'];
        $b = &$args['doBuild'];

        // called manually
        if ($s === null && $b === null)
        {
            [$s, $b] = $this->handleCLIOpt();
            if (!$s && !$b && !CLISetup::getOpt('setup'))
            {
                CLI::write('[sync] no valid table names supplied', CLI::LOG_ERROR);
                return false;
            }
        }

        if ($s)
        {
            $io = ['doSql' => $s, 'doneSql' => []];
            CLISetup::run('sql', $io);
            DB::Aowow()->query('UPDATE ?_dbversion SET `sql` = ?', implode(' ', array_diff($io['doSql'], $io['doneSql'])));
        }

        if ($b)
        {
            $io = ['doBuild' => $b, 'doneBuild' => []];
            CLISetup::run('build', $io);
            DB::Aowow()->query('UPDATE ?_dbversion SET `build` = ?', implode(' ', array_diff($io['doBuild'], $io['doneBuild'])));
        }

        return true;
    }

    private function handleCLIOpt() : array
    {
        $sql   = [];
        $build = [];

        $sync = CLISetup::getOpt('sync');
        if (!$sync)
            return [$sql, $build];

        foreach (CLISetup::getSubScripts() as $name => [$invoker, $ssRef])
            if (array_intersect($ssRef->getRemoteDependencies(), $sync))
                $$invoker[] = $name;

        do
        {
            $n = count($sql);
            foreach (CLISetup::getSubScripts('sql') as $name => [, $ssRef])
                if (!in_array($name, $sql) && array_intersect($ssRef->getSelfDependencies()[0], $sql))
                    $sql[] = $name;
        }
        while ($n != count($sql));

        if ($sql)
            foreach (CLISetup::getSubScripts('build') as $name => [, $ssRef])
                if (array_intersect($ssRef->getSelfDependencies()[0], $sql))
                    $build[] = $name;

        return [array_unique($sql), array_unique($build)];
    }

    public function writeCLIHelp() : bool
    {
        CLI::write('  usage: php aowow --sync=<tableList,> [--locales: --datasrc: --force -f]', -1, false);
        CLI::write();
        CLI::write('  Truncates and recreates AoWoW tables and static data files that depend on the given TC world table. Use this command after you updated your world database.', -1, false);
        CLI::write();
        CLI::write('  e.g.: "php aowow --sync=creature_queststarter" causes the table aowow_quests_startend to be recreated.', -1, false);
        CLI::write('  Also quest-related profiler files will be recreated as they depend on aowow_quests_startend and thus indirectly on creature_queststarter.', -1, false);
        CLI::write();
        CLI::write();

        return true;
    }
})

?>
