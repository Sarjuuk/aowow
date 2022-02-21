<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


trait TrDBCcopy
{
    public function generate() : bool
    {
        if (!$this->dbcSourceFiles)
        {
            CLI::write('   SetupScript '.$this->command.' is set up for DBCcopy but has no source set!', CLI::LOG_ERROR);
            return false;
        }
        else if (count($this->dbcSourceFiles) != 1)
            CLI::write('   SetupScript '.$this->command.' is set up for DBCcopy but has multiple sources set!', CLI::LOG_WARN);

        CLI::write('SqlGen::generate() - copying '.$this->dbcSourceFiles[0].'.dbc into aowow_'.$this->command);

        $dbc = new DBC($this->dbcSourceFiles[0], ['temporary' => false, 'tableName' => 'aowow_'.$this->command]);
        if ($dbc->error)
            return false;

        return !!$dbc->readFile();
    }
}

trait TrCustomData
{
    // apply post generator custom data
    public function applyCustomData() : bool
    {
        $ok = true;
        $this->customData = $this->customData ?? [];
        if ($cd = DB::Aowow()->selectCol('SELECT `entry` AS ARRAY_KEY, `field` AS ARRAY_KEY2, `value` FROM ?_setup_custom_data WHERE `command` = ?', $this->command))
            $this->customData += $cd;

        foreach ($this->customData as $id => $data)
        {
            try
            {
                DB::Aowow()->query('UPDATE ?_'.$this->command.' SET ?a WHERE id = ?d', $data, $id);
            }
            catch (Exception $e)
            {
                trigger_error('Custom Data for entry #'.$id.': '.$e->getMessage(), E_USER_ERROR);
                $ok = false;
            }
        }

        return $ok;
    }
}

abstract class SetupScript
{
    protected $fileTemplatePath = '';
    protected $fileTemplateFile = '';

    protected $tblDependencyAowow = [];
    protected $tblDependencyTC    = [];

    protected $dbcSourceFiles     = [];

    // abstract protected $command;

    abstract public function generate() : bool;

    public function getRequiredDBCs() : array
    {
        return $this->dbcSourceFiles;
    }

    public function getDependencies(bool $aowow) : array
    {
        return $aowow ? $this->tblDependencyAowow : $this->tblDependencyTC;
    }

    public function getName() : string
    {
        return $this->command;
    }
}

?>