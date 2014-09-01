<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


    // Create 'itemsets'-file for available locales
    // this script requires the following dbc-files to be parsed and available
    // GlyphProperties, Spells, SkillLineAbility

    /* Example
        "-447": {                                           // internal id, freely chosen
            "classes":["6"],                                // array
            "elite":true,
            "heroic":false,
            "id":"-447",
            "idbak":"924",                                  // actual setId
            "maxlevel":"390",
            "minlevel":"390",
            "name":"3Cataclysmic Gladiator's Desecration",
            "note":"37",                                    // contentGroup
            "pieces":["73742","73741","73740","73739","73738"],
            "reqclass":"32",                                // mask
            "type":"4",
            "setbonus":{
                "2":{"resirtng":"400","str":"70"},
                "4":{"str":"90"}
            }
        },
    */

    $setList   = DB::Aowow()->Select('SELECT * FROM ?_itemset ORDER BY refSetId DESC');
    $locales   = [LOCALE_EN, LOCALE_FR, LOCALE_DE, LOCALE_ES, LOCALE_RU];
    $jsonBonus = [];

    // check directory-structure
    foreach (Util::$localeStrings as $dir)
        if (!is_dir('datasets\\'.$dir))
            mkdir('datasets\\'.$dir, 0755, true);

    echo "script set up in ".Util::execTime()."<br>\n";

    foreach ($locales as $lId)
    {
        User::useLocale($lId);
        Lang::load(Util::$localeStrings[$lId]);

        $itemsetOut = [];
        foreach ($setList as $set)
        {
            set_time_limit(15);

            $setOut = array(
                'id'       => $set['id'],
                'name'     => (7 - $set['quality']).Util::jsEscape(Util::localizedString($set, 'name')),
                'pieces'   => [],
                'heroic'   => DB::Aowow()->SelectCell('SELECT IF (Flags & 0x8, "true", "false") FROM item_template WHERE entry = ?d', $set['item1']),
                'maxlevel' => $set['maxLevel'],
                'minlevel' => $set['minLevel'],
                'type'     => $set['type'],
                'setbonus' => []
            );

            if ($set['classMask'])
            {
                $setOut['reqclass'] = $set['classMask'];
                $setOut['classes']  = [];

                for ($i = 0; $i < 12; $i++)
                    if ($set['classMask'] & (1 << ($i - 1)))
                        $setOut['classes'][] = $i;
            }

            if ($set['contentGroup'])
                $setOut['note'] = $set['contentGroup'];

            if ($set['id'] < 0)
                $setOut['idbak'] = $set['refSetId'];

            for ($i = 1; $i < 11; $i++)
                if ($set['item'.$i])
                    $setOut['pieces'][] = $set['item'.$i];

            for ($i = 1; $i < 9; $i++)
            {
                if (!$set['bonus'.$i] || !$set['spell'.$i])
                    continue;

                // costy and locale-independant -> cache
                if (!isset($jsonBonus[$set['spell'.$i]]))
                    $jsonBonus[$set['spell'.$i]] = (new SpellList(array(['s.id', (int)$set['spell'.$i]])))->getStatGain()[$set['spell'.$i]];

                if (!isset($setOut['setbonus'][$set['bonus'.$i]]))
                    $setOut['setbonus'][$set['bonus'.$i]] = $jsonBonus[$set['spell'.$i]];
                else
                    foreach ($jsonBonus[$set['spell'.$i]] as $k => $v)
                        @$setOut['setbonus'][$set['bonus'.$i]][$k] += $v;
            }

            foreach ($setOut['setbonus'] as $k => $v)
            {
                if (empty($v))
                    unset($setOut['setbonus'][$k]);
                else
                {
                    foreach ($v as $sk => $sv)
                    {
                        if ($str = Util::$itemMods[$sk])
                        {
                            $setOut['setbonus'][$k][$str] = $sv;
                            unset($setOut['setbonus'][$k][$sk]);
                        }
                    }
                }
            }

            if (empty($setOut['setbonus']))
                unset($setOut['setbonus']);

            $itemsetOut[$setOut['id']] = $setOut;
        }

        $toFile  = "var g_itemsets = ";
        $toFile .= json_encode($itemsetOut, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        $toFile .= ";";
        $file    = 'datasets\\'.User::$localeString.'\\itemsets';

        $handle = fOpen($file, "w");
        fWrite($handle, $toFile);
        fClose($handle);

        echo "done itemsets loc: ".$lId." in ".Util::execTime()."<br>\n";
    }

    echo "<br>\nall done";

    Lang::load(Util::$localeStrings[LOCALE_EN]);

    $stats = DB::Aowow()->getStatistics();
    echo "<br>\n".$stats['count']." queries in: ".Util::formatTime($stats['time'] * 1000);
?>
