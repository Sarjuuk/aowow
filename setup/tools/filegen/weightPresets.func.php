<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


    // Creates 'weight-presets'-file

    function weightPresets()
    {
        // check directory-structure
        if (!CLISetup::writeDir('datasets/'))
            return false;

        $wtPresets = [];
        $scales    = DB::Aowow()->select('SELECT id, name, icon, class FROM ?_account_weightscales WHERE userId = 0 ORDER BY class, id ASC');

        foreach ($scales as $s)
        {
            $weights = DB::Aowow()->selectCol('SELECT field AS ARRAY_KEY, val FROM ?_account_weightscale_data WHERE id = ?d', $s['id']);
            if (!$weights)
            {
                CLISetup::log('WeightScale \''.CLISetup::bold($s['name']).'\' has no data set. Skipping...', CLISetup::LOG_WARN);
                continue;
            }

            $wtPresets[$s['class']]['pve'][$s['name']] = array_merge(['__icon' => $s['icon']], $weights);
        }

        $toFile = "var wt_presets = ".Util::toJSON($wtPresets).";";
        $file   = 'datasets/weight-presets';

        if (!CLISetup::writeFile($file, $toFile))
            return false;

        return true;
    }
?>
