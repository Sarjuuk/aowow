<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


class ItemStatSetup extends ItemList
{
    private $statCols = [];

    public function __construct($start, $limit, array $ids, array $enchStats)
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

        $this->enchParsed = $enchStats;
    }

    public function writeStatsTable()
    {
        $enchantments = [];                                 // buffer Ids for lookup id => src; src>0: socketBonus; src<0: gemEnchant

        foreach ($this->iterate() as $__)
        {
            $this->itemMods[$this->id] = [];

            // also occurs as seperate field (gets summed in calculation but not in tooltip)
            if ($_ = $this->getField('block'))
                $this->itemMods[$this->id][ITEM_MOD_BLOCK_VALUE] = $_;

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
                if (!(in_array($this->curTpl['class'],  [ITEM_CLASS_WEAPON, ITEM_CLASS_ARMOR]) && $this->curTpl['spellTrigger'.$h] == SPELL_TRIGGER_EQUIP) &&
                    !(         $this->curTpl['class'] == ITEM_CLASS_CONSUMABLE                 && $this->curTpl['spellTrigger'.$h] == SPELL_TRIGGER_USE))
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
        // and merge enchantments back
        foreach ($enchantments as $eId => $items)
        {
            if (empty($this->enchParsed[$eId]))
                continue;

            foreach ($items as $item)
            {
                if ($item > 0)                          // apply socketBonus
                    $this->json[$item]['socketbonusstat'] = $this->enchParsed[$eId];
                else /* if ($item < 0) */               // apply gemEnchantment
                    Util::arraySumByKey($this->json[-$item], $this->enchParsed[$eId]);
            }
        }

        // collect data and write to DB
        foreach ($this->iterate() as $__)
        {
            $updateFields = ['type' => Type::ITEM, 'typeId' => $this->id];

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
                    if ($str = Game::$itemMods[$k])
                        $updateFields[$str] = number_format($v, 2, '.', '');
                }
            }

            DB::Aowow()->query('REPLACE INTO ?_item_stats (?#) VALUES (?a)', array_keys($updateFields), array_values($updateFields));
        }
    }
}

SqlGen::register(new class extends SetupScript
{
    protected $command = 'item_stats';                      // and enchantment stats

    protected $tblDependencyAowow = ['items', 'spell'];
    protected $dbcSourceFiles     = ['spellitemenchantment'];

    private function enchantment_stats() : array
    {
        $statCols   = DB::Aowow()->selectCol('SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_NAME` LIKE "%item_stats"');
        $enchants   = DB::Aowow()->select('SELECT *, id AS ARRAY_KEY FROM dbc_spellitemenchantment');
        $spells     = [];
        $spellStats = [];

        foreach ($enchants as $eId => $e)
        {
            for ($i = 1; $i <=3; $i++)
            {
                // trigger: onEquip + valid SpellId
                if ($e['object'.$i] > 0 && $e['type'.$i] == 3)
                    $spells[] = $e['object'.$i];
            }
        }

        if ($spells)
            $spellStats = (new SpellList(array(['id', $spells], CFG_SQL_LIMIT_NONE)))->getStatGain();

        $result = [];
        foreach ($enchants as $eId => $e)
        {
            // parse stats
            $result[$eId] = [];
            for ($h = 1; $h <= 3; $h++)
            {
                $obj = (int)$e['object'.$h];
                $val = (int)$e['amount'.$h];

                switch ($e['type'.$h])
                {
                    case 6:                                 // TYPE_TOTEM               +AmountX as DPS (Rockbiter)
                        $result[$eId]['dps'] = $val;        // we do not use dps as itemMod, so apply it directly
                        $obj = null;
                        break;
                    case 2:                                 // TYPE_DAMAGE              +AmountX damage
                        $obj = ITEM_MOD_WEAPON_DMG;
                        break;
                 // case 1:                                 // TYPE_COMBAT_SPELL        proc spell from ObjectX (amountX == procChance)
                 // case 7:                                 // TYPE_USE_SPELL           Engineering gadgets
                    case 3:                                 // TYPE_EQUIP_SPELL         Spells from ObjectX (use of amountX?)
                        if (!empty($spellStats[$obj]))
                            foreach ($spellStats[$obj] as $mod => $val)
                                if ($str = Game::$itemMods[$mod])
                                    Util::arraySumByKey($result[$eId], [$str => $val]);

                        $obj = null;
                        break;
                    case 4:                                 // TYPE_RESISTANCE          +AmountX resistance for ObjectX School
                        switch ($obj)
                        {
                            case 0:                         // Physical
                                $obj = ITEM_MOD_ARMOR;
                                break;
                            case 1:                         // Holy
                                $obj = ITEM_MOD_HOLY_RESISTANCE;
                                break;
                            case 2:                         // Fire
                                $obj = ITEM_MOD_FIRE_RESISTANCE;
                                break;
                            case 3:                         // Nature
                                $obj = ITEM_MOD_NATURE_RESISTANCE;
                                break;
                            case 4:                         // Frost
                                $obj = ITEM_MOD_FROST_RESISTANCE;
                                break;
                            case 5:                         // Shadow
                                $obj = ITEM_MOD_SHADOW_RESISTANCE;
                                break;
                            case 6:                         // Arcane
                                $obj = ITEM_MOD_ARCANE_RESISTANCE;
                                break;
                            default:
                                $obj = null;
                        }
                        break;
                    case 5:                                 // TYPE_STAT                +AmountX for Statistic by type of ObjectX
                        if ($obj < 2)                       // [mana, health] are on [0, 1] respectively and are expected on [1, 2] ..
                            $obj++;                         // 0 is weaponDmg .. ehh .. i messed up somewhere

                        break;                              // stats are directly assigned below
                    case 8:                                 // TYPE_PRISMATIC_SOCKET    Extra Sockets AmountX as socketCount (ignore)
                        $result[$eId]['nsockets'] = $val;   // there is no itemmod for sockets, so apply it directly
                    default:                                // TYPE_NONE                dnd stuff; skip assignment below
                        $obj = null;
                }

                if ($obj !== null)
                    if ($str = Game::$itemMods[$obj])       // check if we use these mods
                        Util::arraySumByKey($result[$eId], [$str => $val]);
            }

            $updateCols = ['type' => Type::ENCHANTMENT, 'typeId' => $eId];
            foreach ($result[$eId] as $k => $v)
            {
                if (!in_array($k, $statCols) || !$v || $k == 'id')
                    continue;

                $updateCols[$k] = number_format($v, 2, '.', '');
            }

            DB::Aowow()->query('REPLACE INTO ?_item_stats (?#) VALUES (?a)', array_keys($updateCols), array_values($updateCols));
        }

        return $result;
    }

    public function generate(array $ids = []) : bool
    {
        $offset = 0;

        CLI::write(' - applying stats for enchantments');
        $enchStats = $this->enchantment_stats();
        CLI::write('   '.count($enchStats).' enchantments parsed');
        CLI::write(' - applying stats for items');

        $i = 0;
        while (true)
        {
            $items = new ItemStatSetup($offset, SqlGen::$sqlBatchSize, $ids, $enchStats);
            if ($items->error)
                break;

            CLI::write(' * batch #' . ++$i . ' (' . count($items->getFoundIDs()) . ')', CLI::LOG_BLANK, true, true);

            $offset = max($items->getFoundIDs());

            $items->writeStatsTable();
        }

        return true;
    }
});

?>
