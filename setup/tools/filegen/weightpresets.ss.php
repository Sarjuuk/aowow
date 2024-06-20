<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


// Creates 'weight-presets'-file
CLISetup::registerSetup("build", new class extends SetupScript
{
    protected $info = array(
        'weightpresets' => [[], CLISetup::ARGV_PARAM, 'Generates stat weight presets file for the item comparison tool.']
    );

    protected $requiredDirs = ['datasets/'];

    public function generate() : bool
    {
        $wtPresets = [];
        $scales    = DB::Aowow()->select('SELECT id, name, icon, class FROM ?_account_weightscales WHERE userId = 0 ORDER BY class, id ASC');

        foreach ($scales as $s)
        {
            if ($weights = DB::Aowow()->selectCol('SELECT field AS ARRAY_KEY, val FROM ?_account_weightscale_data WHERE id = ?d', $s['id']))
                $wtPresets[$s['class']]['pve'][$s['name']] = array_merge(['__icon' => $s['icon']], $weights);
            else
            {
                CLI::write('[weightpresets] Scale \''.CLI::bold($s['name']).'\' has no data set.', CLI::LOG_WARN);
                $wtPresets[$s['class']]['pve'][$s['name']] = ['__icon' => $s['icon']];
            }
        }

        $toFile = "var wt_presets = ".Util::toJSON($wtPresets).";";
        $file   = 'datasets/weight-presets';

        if (!CLISetup::writeFile($file, $toFile))
            return false;

        return true;
    }
});

?>
