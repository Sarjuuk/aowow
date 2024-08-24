<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/************************************************/
/* Create content from world tables / dbc files */
/************************************************/

CLISetup::registerUtility(new class extends UtilityScript
{
    public $argvFlags   = CLISetup::ARGV_ARRAY | CLISetup::ARGV_REQUIRED;
    public $optGroup    = CLISetup::OPT_GRP_UTIL;

    public const COMMAND      = 'dbc';
    public const DESCRIPTION  = 'Extract dbc files from datasrc into sql table. Structural ini file must be defined in setup/tools/dbc/.';
    public const APPENDIX     = '=<dbcfileList,> [tablename [wowbuild]]';

    public const REQUIRED_DB = [DB_AOWOW];

    public const USE_CLI_ARGS = true;

    // args: tblName, wowbuild, null, null // iinn
    public function run(&$args) : bool
    {
        foreach (CLISetup::getOpt('dbc') as $n)
        {
            $n = str_ireplace('.dbc', '', trim($n));

            if (empty($n))
                continue;

            $opts = ['temporary' => false];
            if ($args[0])
                $opts['tableName'] = $args[0];

            $dbc = new DBC(strtolower($n), $opts, $args[1] ?: DBC::DEFAULT_WOW_BUILD);
            if ($dbc->error)
            {
                CLI::write('[dbc] required DBC '.CLI::bold($n).'.dbc not found!', CLI::LOG_ERROR);
                return false;
            }

            if (!$dbc->readFile())
            {
                CLI::write('[dbc] DBC '.CLI::bold($n).'.dbc could not be written to DB!', CLI::LOG_ERROR);
                return false;
            }

            $self = DB::Aowow()->selectCell('SELECT DATABASE()');
            CLI::write('[dbc] DBC '.CLI::bold($n).'.dbc written to '.CLI::bold('`'.($self ?? 'NULL').'`.`'.$dbc->getTableName().'`').'!', CLI::LOG_OK);
        }

        return true;
    }

    public function writeCLIHelp() : bool
    {
        CLI::write('  usage: php aowow --dbc=<dbcfileList,..> [--locales=<regionCodes,..>] [tablename [wowbuild]]', -1, false);
        CLI::write();
        CLI::write('  Extract dbc files from datasrc into sql table. If the dbc file contains a locale block, data from all available locales gets merged into the same table.', -1, false);
        CLI::write();
        CLI::write('  Known WoW builds:', -1, false);

        foreach (glob('setup/tools/dbc/*.ini') as $f)
        {
            $a = $b = [];
            preg_match('/(\d+)\.ini$/', $f, $a);
            if ($h = fopen($f, 'r'))
            {
                preg_match('/(\d\.\d.\d\.?\d*)/', fgets($h), $b);
                fclose($h);
            }

            CLI::write('    '.CLI::bold($a[1]).' > '.$b[1], -1, false);
        }

        CLI::write();
        CLI::write('  Known DBC files:', -1, false);

        $defs   = DBC::getDefinitions();
        $letter = '';
        $buff   = [];

        asort($defs);

        foreach ($defs as $d)
        {
            if (!$letter)
                $letter = $d[0];
            else if ($letter != $d[0])
            {
                CLI::write('    '.CLI::bold(Util::ucFirst($letter)).':', -1, false);
                foreach (explode("\n", Lang::breakTextClean(implode(', ', $buff), 120, Lang::FMT_RAW)) as $line)
                    CLI::write('      '.$line, -1, false);

                $buff = [$d];
                $letter = $d[0];
            }
            else
                $buff[] = $d;
        }

        CLI::write('    '.CLI::bold(Util::ucFirst($letter)).':', -1, false);
        foreach (explode("\n", Lang::breakTextClean(implode(', ', $buff), 120, Lang::FMT_RAW)) as $line)
            CLI::write('      '.$line, -1, false);

        CLI::write();
        CLI::write();

        return true;
    }
});

?>
