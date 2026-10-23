<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/*********************************/
/* automaticly apply sql-updates */
/*********************************/

CLISetup::registerUtility(new class extends UtilityScript
{
    public array  $argvOpts   = ['u'];
    public int    $optGroup   = CLISetup::OPT_GRP_SETUP;
    public string $followupFn = 'sync';

    public const string COMMAND     = 'update';
    public const string DESCRIPTION = 'Apply new sql updates fetched from Github and run --sync as needed.';

    public const array  REQUIRED_DB = [DB_AOWOW];

    public const int    SITE_LOCK   = CLISetup::LOCK_RESTORE;

    private int $date = 0;
    private int $part = 0;

    public function __construct()
    {
        if (DB::isConnected(DB_AOWOW))
            [$this->date, $this->part] = array_values(DB::Aowow()->selectRow('SELECT `date`, `part` FROM ::dbversion'));
    }

    // args: null, null, sqlToDo, buildToDo // nnoo
    public function run(array &$args) : bool
    {
        $sql   = &$args['doSql'];
        $build = &$args['doBuild'];

        CLI::write('[update] checking for sql updates...');

        $nFiles = 0;
        foreach (glob('setup/sql/updates/*.sql') as $file)
        {
            $pi = pathinfo($file);

            // invalid file
            if (!preg_match('/(\d{10})_(\d{2})/', $pi['filename'], $m))
                continue;

            $fDate = intVal($m[1]);
            $fPart = intVal($m[2]);

            if ($this->date && $fDate < $this->date)
                continue;
            else if ($this->part && $this->date && $fDate == $this->date && $fPart <= $this->part)
                continue;

            $nFiles++;

            $updQuery = '';
            $queries  = [];
            foreach (file($file) as $line)
            {
                if (($line = trim($line)) === '')
                    continue;

                // skip line comments entirely
                if (str_starts_with($line, '--'))
                    continue;

                if (preg_match('/\)[,;] *--/', $line, $m, PREG_OFFSET_CAPTURE))
                    $line = substr($line, 0, $m[0][1] + 2);

                $updQuery .= ' ' . $line;

                // semicolon at the end -> end of query
                if (str_ends_with($updQuery, ';'))
                {
                    $queries[] = $updQuery;
                    $updQuery  = '';
                }
            }

            if ($queries)
            {
                DB::Aowow()->begin();

                foreach ($queries as $i => $q)
                {
                    if (DB::Aowow()->nativeQuery($q) === null)
                    {
                        DB::Aowow()->rollback();
                        CLI::write('[update] failed to apply query #'.(++$i).' from file '.CLI::bold($file), CLI::LOG_ERROR);
                        return false;
                    }
                }

                DB::Aowow()->commit();
            }

            DB::Aowow()->qry('UPDATE ::dbversion SET `date`= %i, `part` = %i', $fDate, $fPart);
            CLI::write(' -> '.date('d.m.Y', $fDate).' #'.$fPart.': '.count($queries).' queries applied', CLI::LOG_OK);
        }

        CLI::write('[update] ' . ($nFiles ? 'applied '.$nFiles.' update(s)' : 'db is already up to date'), CLI::LOG_OK);

        // fetch sql/build after applying updates, as they may contain sync-prompts
        [$sql, $build] = DB::Aowow()->selectRow('SELECT `sql` AS "0", `build` AS "1" FROM ::dbversion');

        $sql   = trim($sql)   ? array_unique(explode(' ', trim(preg_replace('/[^a-z_\-]+/i', ' ', $sql))))   : [];
        $build = trim($build) ? array_unique(explode(' ', trim(preg_replace('/[^a-z_\-]+/i', ' ', $build)))) : [];

        sleep(1);

        if ($sql)
            CLI::write('[update] The following sql scripts have been scheduled: '.implode(', ', $sql));

        if ($build)
        {
            CLISetup::setOpt('force', true);                // overwrite existing images if demanded by update
            CLI::write('[update] The following build scripts have been scheduled: '.implode(', ', $build));
        }

        return true;
    }

    public function writeCLIHelp() : bool
    {
        CLI::write('  usage: php aowow --update', -1, false);
        CLI::write();
        CLI::write('  Checks /setup/sql/updates for new *.sql files and applies them. If required by an applied update, the --sql and --build command are triggered afterwards.', -1, false);
        CLI::write('  Use this after fetching the latest rev. from Github.', -1, false);

        if ($this->date)
        {
            CLI::write();
            CLI::write('  Last Update: '.date(Util::$dateFormatInternal, $this->date).' (Part #'.$this->part.')', -1, false);
        }

        CLI::write();
        CLI::write();

        return true;
    }
});

?>
