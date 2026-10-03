<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/************************************************/
/* automaticly synchronize with TC world tables */
/************************************************/

CLISetup::registerUtility(new class extends UtilityScript
{
    public int $argvFlags = CLISetup::ARGV_ARRAY | CLISetup::ARGV_OPTIONAL;
    public int $optGroup  = CLISetup::OPT_GRP_UTIL;

    public const string COMMAND     = 'sync';
    public const string DESCRIPTION = 'Regenerate tables/files that depend on given world DB table.';
    public const string APPENDIX    = '=<worldTableList,>';

    public const array  REQUIRED_DB = [DB_AOWOW, DB_WORLD];

    public const int LOCK_SITE = CLISetup::LOCK_RESTORE;

    // sqlToDo, buildToDo, null, null // iinn
    public function run(array &$args) : bool
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

        foreach (['sql' => $s, 'build' => $b] as $command => $requested)
        {
            if (!$requested) continue;
            $requested = is_array($requested) ? $requested : [$requested];
            $todoKey = $command === 'sql' ? 'doSql' : 'doBuild';
            $doneKey = $command === 'sql' ? 'doneSql' : 'doneBuild';
            $io = [$todoKey => $requested, $doneKey => []];
            if (!CLISetup::run($command, $io) || array_diff($requested, $io[$doneKey]))
            {
                CLI::write('[sync] '.$command.' generation incomplete; pending work retained.', CLI::LOG_ERROR);
                return false;
            }
            if (!$this->complete($command, array_intersect($requested, $io[$doneKey])))
                return false;
        }
        return true;
    }

    // Preserve unrelated pending work; acknowledge only verified successful generators.
    private function complete(string $column, array $done) : bool
    {
        $db = DB::Aowow();
        try
        {
            $db->query('START TRANSACTION');
            $rows = $db->query('SELECT %n FROM ::dbversion FOR UPDATE', $column)->fetchAll();
            if (count($rows) !== 1) throw new \RuntimeException();
            $current = trim((string)$rows[0][$column]);
            $pending = $current === '' ? [] : preg_split('/[^a-z_\-]+/i', $current, -1, PREG_SPLIT_NO_EMPTY);
            $value = implode(' ', array_diff($pending, $done));
            $db->query('UPDATE ::dbversion SET %n = %s', $column, $value);
            if ((string)$db->query('SELECT %n FROM ::dbversion', $column)->fetchSingle() !== $value)
                throw new \RuntimeException();
            $db->query('COMMIT');
            return true;
        }
        catch (\Throwable $e)
        {
            try { $db->query('ROLLBACK'); } catch (\Throwable) { }
            CLI::write('[sync] Could not acknowledge pending work (code '.(int)$e->getCode().').', CLI::LOG_ERROR);
            return false;
        }
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
