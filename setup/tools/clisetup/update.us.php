<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/*********************************/
/* automaticly apply sql-updates */
/*********************************/

CLISetup::registerUtility(new class extends UtilityScript
{
    public $argvOpts    = ['u'];
    public $optGroup    = CLISetup::OPT_GRP_SETUP;
    public $followupFn  = 'sync';

    public const COMMAND     = 'update';
    public const DESCRIPTION = 'Apply new sql updates fetched from Github and run --sync as needed.';

    public const REQUIRED_DB = [DB_AOWOW];

    public const SITE_LOCK   = CLISetup::LOCK_RESTORE;

    private $date = 0;
    private $part = 0;

    public function __construct()
    {
        if (DB::isConnected(DB_AOWOW))
            [$this->date, $this->part] = array_values(DB::Aowow()->selectRow('SELECT `date`, `part` FROM ?_dbversion'));
    }

    // args: null, null, sqlToDo, buildToDo // nnoo
    public function run(&$args) : bool
    {
        $sql   = &$args['doSql'];
        $build = &$args['doBuild'];

        CLI::write('[update] checking for sql updates...');

        $nFiles = 0;
        foreach (glob('setup/updates/*.sql') as $file)
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
            $nQuerys  = 0;
            foreach (file($file) as $line)
            {
                // skip comments
                if (substr($line, 0, 2) == '--' || $line == '')
                    continue;

                $updQuery .= $line;

                // semicolon at the end -> end of query
                if (substr(trim($line), -1, 1) == ';')
                {
                    if (DB::Aowow()->query($updQuery))
                        $nQuerys++;

                    $updQuery = '';
                }
            }

            DB::Aowow()->query('UPDATE ?_dbversion SET `date`= ?d, `part` = ?d', $fDate, $fPart);
            CLI::write(' -> '.date('d.m.Y', $fDate).' #'.$fPart.': '.$nQuerys.' queries applied', CLI::LOG_OK);
        }

        CLI::write('[update] ' . ($nFiles ? 'applied '.$nFiles.' update(s)' : 'db is already up to date'), CLI::LOG_OK);

        // fetch sql/build after applying updates, as they may contain sync-prompts
        [$sql, $build] = DB::Aowow()->selectRow('SELECT `sql` AS "0", `build` AS "1" FROM ?_dbversion');

        $sql   = trim($sql)   ? array_unique(explode(' ', trim(preg_replace('/[^a-z_\-]+/i', ' ', $sql))))   : [];
        $build = trim($build) ? array_unique(explode(' ', trim(preg_replace('/[^a-z_\-]+/i', ' ', $build)))) : [];

        sleep(1);

        if ($sql)
            CLI::write('[update] The following sql scripts have been scheduled: '.implode(', ', $sql));

        if ($build)
            CLI::write('[update] The following build scripts have been scheduled: '.implode(', ', $build));

        return true;
    }

    public function writeCLIHelp() : bool
    {
        CLI::write('  usage: php aowow --update', -1, false);
        CLI::write();
        CLI::write('  Checks /setup/updates for new *.sql files and applies them. If required by an applied update, the --sql and --build command are triggered afterwards.', -1, false);
        CLI::write('  Use this after fetching the latest rev. from Github.', -1, false);
        CLI::write();
        CLI::write('  Last Update: '.date(Util::$dateFormatInternal, $this->date).' (Part #'.$this->part.')', -1, false);
        CLI::write();
        CLI::write();

        return true;
    }
});

?>
