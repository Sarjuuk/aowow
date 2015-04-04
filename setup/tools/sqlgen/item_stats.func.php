<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/* deps:
 * ?_items finalized
 * ?_spell finalized
*/

$customData = array(
);
$reqDBC = [];

class ItemStatSetup extends ItemList
{
    private $statCols = [];

    public function __construct($start, $limit, array $ids)
    {
        $this->statCols = DB::Aowow()->selectCol('SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_NAME` LIKE "%item_stats"');
        $this->queryOpts['i']['o'] = 'i.id ASC';
        unset($this->queryOpts['is']);                      // do not reference the stats table we are going to write to

        $conditions = array(
            ['i.id', $start, '>'],
            ['class', [ITEM_CLASS_WEAPON, ITEM_CLASS_GEM, ITEM_CLASS_ARMOR, ITEM_CLASS_CONSUMABLE]],
            $limit
        );

        if ($ids)
            $conditions[] = ['id', $ids];

        parent::__construct($conditions);
    }

    public function writeStatsTable()
    {
        $enchantments = [];                                 // buffer Ids for lookup id => src; src>0: socketBonus; src<0: gemEnchant

        foreach ($this->iterate() as $__)
        {
            $this->itemMods[$this->id] = [];

            // convert itemMods to stats
            for ($h = 1; $h <= 10; $h++)
            {
                $mod = $this->curTpl['statType'.$h];
                $val = $this->curTpl['statValue'.$h];
                if (!$mod || !$val)
                    continue;

                Util::arraySumByKey($this->itemMods[$this->id], [$mod => $val]);
            }

            // convert spells to stats
            $equipSpells = [];
            for ($h = 1; $h <= 5; $h++)
            {
                if ($this->curTpl['spellId'.$h] <= 0)
                    continue;

                // armor & weapons only onEquip && consumables only onUse
                if (!(in_array($this->curTpl['class'],  [ITEM_CLASS_WEAPON, ITEM_CLASS_ARMOR]) && $this->curTpl['spellTrigger'.$h] == 1) &&
                    !(         $this->curTpl['class'] == ITEM_CLASS_CONSUMABLE                 && $this->curTpl['spellTrigger'.$h] == 0))
                    continue;

                $equipSpells[] = $this->curTpl['spellId'.$h];
            }

            if ($equipSpells)
            {
                $eqpSplList = new SpellList(array(['s.id', $equipSpells]));
                foreach ($eqpSplList->getStatGain() as $stats)
                    Util::arraySumByKey($this->itemMods[$this->id], $stats);
            }

            // prepare: convert enchantments to stats
            if (!empty($this->json[$this->id]['socketbonus']))
                $enchantments[$this->json[$this->id]['socketbonus']][] = $this->id;
            if ($geId = $this->curTpl['gemEnchantmentId'])
                $enchantments[$geId][] = -$this->id;
        }

        // execute: convert enchantments to stats
        if ($enchantments)
        {
            $parsed = Util::parseItemEnchantment(array_keys($enchantments));

            // and merge enchantments back
            foreach ($parsed as $eId => $stats)
            {
                foreach ($enchantments[$eId] as $item)
                {
                    if ($item > 0)                          // apply socketBonus
                        $this->json[$item]['socketbonusstat'] = $stats;
                    else /* if ($item < 0) */               // apply gemEnchantment
                        Util::arraySumByKey($this->json[-$item][$mod], $stats);
                }
            }
        }

        // collect data and write to DB
        foreach ($this->iterate() as $__)
        {
            $updateFields = ['id' => $this->id];

            foreach (@$this->json[$this->id] as $k => $v)
            {
                if (!in_array($k, $this->statCols) || !$v || $k == 'id')
                    continue;

                $updateFields[$k] = number_format($v, 2, '.', '');
            }

            if (isset($this->itemMods[$this->id]))
            {
                foreach ($this->itemMods[$this->id] as $k => $v)
                {
                    if (!$v)
                        continue;
                    if ($str = Util::$itemMods[$k])
                        $updateFields[$str] = number_format($v, 2, '.', '');
                }
            }

            if (count($updateFields) > 1)
                DB::Aowow()->query('REPLACE INTO ?_item_stats (?#) VALUES (?a)', array_keys($updateFields), array_values($updateFields), $this->id);
        }
    }
}

function item_stats(array $ids = [])
{
    $offset = 0;
    while (true)
    {
        $items = new ItemStatSetup($offset, SqlGen::$stepSize, $ids);
        if ($items->error)
            break;

        $max = max($items->getFoundIDs());
        $num = count($items->getFoundIDs());

        CLISetup::log(' * sets '.($offset + 1).' - '.($max));

        $offset = $max;

        $items->writeStatsTable();

    }

    return true;
}

?>
