<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


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

// Create 'itemsets'-file for available locales
CLISetup::registerSetup("build", new class extends SetupScript
{
    protected $info = array(
        'itemsets' => [[], CLISetup::ARGV_PARAM, 'Compiles available item sets used throughout the page to file.']
    );

    protected $setupAfter   = [['itemset', 'spell'], []];
    protected $requiredDirs = ['datasets/'];
    protected $localized    = true;

    public function generate() : bool
    {
        $setList   = DB::Aowow()->Select('SELECT * FROM ?_itemset ORDER BY `refSetId` DESC');
        $jsonBonus = [];

        foreach (CLISetup::$locales as $loc)
        {
            Lang::load($loc);

            $itemsetOut = [];
            foreach ($setList as $set)
            {
                set_time_limit(15);

                $setOut = array(
                    'id'       => $set['id'],
                    'idbak'    => $set['refSetId'],
                    'name'     => (ITEM_QUALITY_HEIRLOOM - $set['quality']).Util::jsEscape(Util::localizedString($set, 'name')),
                    'pieces'   => [],
                    'heroic'   => !!$set['heroic'],         // should be bool
                    'maxlevel' => $set['maxLevel'],
                    'minlevel' => $set['minLevel'],
                    'type'     => $set['type'],
                    'setbonus' => []
                );

                if ($set['classMask'])
                {
                    $setOut['reqclass'] = $set['classMask'];
                    $setOut['classes']  = ChrClass::fromMask($set['classMask']);
                }

                if ($set['contentGroup'])
                    $setOut['note'] = $set['contentGroup'];

                for ($i = 1; $i < 11; $i++)
                    if ($set['item'.$i])
                        $setOut['pieces'][] = $set['item'.$i];

                $_spells = [];
                for ($i = 1; $i < 9; $i++)
                {
                    if (!$set['bonus'.$i] || isset($jsonBonus[$set['spell'.$i]]))
                        continue;

                    $_spells[] = $set['spell'.$i];
                }

                // costy and locale-independant -> cache
                if ($_spells)
                    $jsonBonus += (new SpellList(array(['s.id', $_spells])))->getStatGain();

                $setbonus = [];
                for ($i = 1; $i < 9; $i++)
                {
                    $itemQty = $set['bonus'.$i];
                    $itemSpl = $set['spell'.$i];
                    if (!$itemQty || !$itemSpl || !isset($jsonBonus[$itemSpl]))
                        continue;

                    if ($x = $jsonBonus[$itemSpl]->toJson(Stat::FLAG_ITEM))
                    {
                        if (!isset($setbonus[$itemQty]))
                            $setbonus[$itemQty] = [];

                        Util::arraySumByKey($setbonus[$itemQty], $x);
                    }
                }

                if ($setbonus)
                    $setOut['setbonus'] = $setbonus;

                $itemsetOut[$setOut['id']] = $setOut;
            }

            $toFile = "var g_itemsets = ".Util::toJSON($itemsetOut).";";
            $file   = 'datasets/'.$loc->json().'/itemsets';

            if (!CLISetup::writeFile($file, $toFile))
                $this->success = false;
        }

        return $this->success;
    }
});

?>
