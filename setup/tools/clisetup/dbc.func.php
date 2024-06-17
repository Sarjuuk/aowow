<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/************************************************/
/* Create content from world tables / dbc files */
/************************************************/

function dbc($args) : bool
{
    if (!DB::isConnected(DB_AOWOW))
    {
        CLI::write('Database not yet set up!', CLI::LOG_WARN);
        CLI::write('Please use '.CLI::bold('"php aowow --dbconfig"').' for setup', CLI::LOG_BLANK);
        CLI::write();
        return false;
    }

    if (!CLISetup::getOpt('dbc'))
        return writeCLIHelp();

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
            CLI::write();
            return false;
        }

        if (!$dbc->readFile())
        {
            CLI::write('[dbc] DBC '.CLI::bold($n).'.dbc could not be written to DB!', CLI::LOG_ERROR);
            CLI::write();
            return false;
        }

        $self = DB::Aowow()->selectCell('SELECT DATABASE()');
        CLI::write('[dbc] DBC '.CLI::bold($n).'.dbc written to '.CLI::bold('`'.($self ?? 'NULL').'`.`'.$dbc->getTableName().'`').'!', CLI::LOG_OK);
        CLI::write();
    }

    return true;

    function writeCLIHelp() : bool
    {
        CLI::write('  usage: php aowow --dbc=<dbcfileList,..> [--locales=<regionCodes,..>] [tablename [wowbuild]]', -1, false);
        CLI::write();
        CLI::write('  Extract dbc files from mpqDataDir into sql table. If the dbc file contains a locale block, data from all available locales gets merged into the same table.', -1, false);
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
};

?>
