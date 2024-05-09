<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/************************************************/
/* automaticly synchronize with TC world tables */
/************************************************/


require_once 'setup/tools/clisetup/sql.func.php';
require_once 'setup/tools/clisetup/build.func.php';

function sync(array $s = [], array $b = []) : void
{
    if ((!$s && !$b && !CLISetup::getOpt('sync')  && !CLISetup::getOpt('setup')) || CLISetup::getOpt('help'))
    {
        CLI::write();
        CLI::write('  usage: php aowow --sync=<tableList,> [--locales: --mpqDataDir: --force -f]', -1, false);
        CLI::write();
        CLI::write('  Truncates and recreates AoWoW tables and static data files that depend on the given TC world table. Use this command after you updated your world database.', -1, false);
        CLI::write();
        CLI::write('  e.g.: "php aowow --sync=creature_queststarter" causes the table aowow_quests_startend to be recreated.', -1, false);
        CLI::write('  Also quest-related profiler files will be recreated as they depend on aowow_quests_startend and thus indirectly on creature_queststarter.', -1, false);
        CLI::write();
        return;
    }

    if (!DB::isConnected(DB_AOWOW) || !DB::isConnected(DB_WORLD))
    {
        CLI::write('Database not yet set up!', CLI::LOG_WARN);
        CLI::write('Please use '.CLI::bold('"php aowow --dbconfig"').' for setup', CLI::LOG_BLANK);
        CLI::write();
        return;
    }

    $_s = sql($s);
    if ($s)
    {
        $_ = array_diff($s, $_s);
        DB::Aowow()->query('UPDATE ?_dbversion SET `sql` = ?', $_ ? implode(' ', $_) : '');
    }

    $_b = build($b);
    if ($b)
    {
        $_ = array_diff($b, $_b);
        DB::Aowow()->query('UPDATE ?_dbversion SET `build` = ?', $_ ? implode(' ', $_) : '');
    }

    if (!$s && !$_s && !$b && !$_b)
        CLI::write('no valid table names supplied', CLI::LOG_ERROR);
}

?>
