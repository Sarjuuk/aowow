<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


class ItemStatSetup extends ItemList
{
    public function __construct(int $start, int $limit, int $itemClass, private bool $applyTriggered, private array $relEnchants, private array $relSpells)
    {
        $this->queryOpts['i']['o'] = 'i.id ASC';
        unset($this->queryOpts['is']);                      // do not reference the stats table we are going to write to

        $conditions = array(
            ['i.id', $start, '>'],
            ['class', $itemClass],
            $limit
        );

        parent::__construct($conditions);
    }

    public function writeStatsTable() : void
    {
        foreach ($this->iterate() as $id => $curTpl)
        {
            $spellIds = [];

            for ($i = 1; $i <= 5; $i++)
                if ($this->curTpl['spellId'.$i] > 0 && !isset($this->relSpells[$this->curTpl['spellId'.$i]]) && (($this->curTpl['class'] == ITEM_CLASS_CONSUMABLE && $this->curTpl['spellTrigger'.$i] == SPELL_TRIGGER_USE) || $this->curTpl['spellTrigger'.$i] == SPELL_TRIGGER_EQUIP))
                    $spellIds[] = $this->curTpl['spellId'.$i];

            if ($spellIds)                                  // array_merge kills the keys
            {
                $newSpells = DB::Aowow()->selectAssoc('SELECT *, id AS ARRAY_KEY FROM ::spell WHERE id IN %in', $spellIds);
                $this->relSpells = array_replace($this->relSpells, $newSpells);

                // include triggered spell to calculate nutritional values
                if ($this->applyTriggered)
                    if ($t = array_filter(array_merge(array_column($newSpells, 'effect1TriggerSpell'), array_column($newSpells, 'effect2TriggerSpell'), array_column($newSpells, 'effect3TriggerSpell'))))
                        if ($t = array_diff($t, array_keys($this->relSpells)))
                            $this->relSpells = array_replace($this->relSpells, DB::Aowow()->selectAssoc('SELECT *, id AS ARRAY_KEY FROM ::spell WHERE id IN %in', $t));
            }

            // fromItem: itemMods, spell, enchants from template - fromJson: calculated stats (feralAP, dps, ...)
            if ($stats = (new StatsContainer($this->relSpells, $this->relEnchants))->fromItem($curTpl)->fromJson($this->json[$id])->toJson(Stat::FLAG_ITEM | Stat::FLAG_SERVERSIDE))
            {
                // manually set stats 0 if empty to distinguish from items that cant have them
                $shared = ['dps' => 0, 'dmgmin1' => 0, 'dmgmax1' => 0, 'speed' => 0];
                if ($this->getField('class') == ITEM_CLASS_WEAPON)
                    $stats += $shared + ($this->isRangedWeapon() ? ['rgddps' => 0, 'rgddmgmin' => 0, 'rgddmgmax' => 0, 'rgdspeed' => 0] : ['mledps' => 0, 'mledmgmin' => 0, 'mledmgmax' => 0, 'mlespeed' => 0]);
                else if ($this->getField('class') == ITEM_CLASS_ARMOR)
                    $stats += ['armorbonus' => 0];          //ArmorDamageModifier only valid on armor(%s)

                DB::Aowow()->qry('INSERT INTO ::item_stats %v', ['type' => Type::ITEM, 'typeId' => $this->id] + $stats);
            }
        }
    }
}

CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected $info = array(
        'stats' => [[], CLISetup::ARGV_PARAM, 'Compiles stats data for type: Item & Enchantment from dbc and world db.']
    );

    protected $dbcSourceFiles = ['spellitemenchantment'];
    protected $setupAfter     = [['items', 'spell'], []];

    private array $relSpells = [];

    private function enchantment_stats(?int &$total = 0, ?int &$effective = 0) : array
    {
        $enchants  = DB::Aowow()->selectAssoc('SELECT *, `id` AS ARRAY_KEY FROM dbc_spellitemenchantment');
        $spells    = [];
        $stats     = [];
        $effective = 0;
        $total     = count($enchants);

        foreach ($enchants as $eId => $e)
            for ($i = 1; $i <= 3; $i++)
                if ($e['object'.$i] > 0 && $e['type'.$i] == ENCHANTMENT_TYPE_EQUIP_SPELL)
                    $spells[] = $e['object'.$i];

        if ($spells)
            $this->relSpells = DB::Aowow()->selectAssoc('SELECT *, id AS ARRAY_KEY FROM ::spell WHERE id IN %in', $spells);

        foreach ($enchants as $eId => $e)
            if ($stats = (new StatsContainer($this->relSpells))->fromEnchantment($e)->toJson(Stat::FLAG_ITEM | Stat::FLAG_SERVERSIDE))
            {
                DB::Aowow()->qry('INSERT INTO ::item_stats %v', ['type' => Type::ENCHANTMENT, 'typeId' => $eId] + $stats);
                $effective++;
            }

        return $enchants;
    }

    public function generate() : bool
    {
        DB::Aowow()->qry('TRUNCATE ::item_stats');

        CLI::write('[stats] - applying stats for enchantments');

        $enchStats = $this->enchantment_stats($total, $effective);
        CLI::write('   '.$effective.'+'.($total - $effective).' enchantments parsed');

        CLI::write('[stats] - applying stats for items');

        $classes = array(
            ITEM_CLASS_WEAPON     => [false, 'weapons'],
            ITEM_CLASS_ARMOR      => [false, 'armor'],
            ITEM_CLASS_GEM        => [false, 'gems'],
            ITEM_CLASS_CONSUMABLE => [true,  'consumables'],
            ITEM_CLASS_AMMUNITION => [false, 'ammunition']
        );
        foreach ($classes as $itemClass =>  [$applyTriggered, $name])
        {
            $i       = 0;
            $offset  = 0;
            while (true)
            {
                $items = new ItemStatSetup($offset, CLISetup::SQL_BATCH, $itemClass, $applyTriggered, $enchStats, $this->relSpells);
                if ($items->error)
                    break;

                CLI::write('[stats] * '.$name.' batch #' . ++$i . ' (' . count($items->getFoundIDs()) . ')', CLI::LOG_BLANK, true, true);

                $offset = max($items->getFoundIDs());

                $items->writeStatsTable();
            }
        }

        return true;
    }
});

?>
