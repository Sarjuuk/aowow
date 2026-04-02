<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


class ItemStatSetup extends ItemContainer
{
    public const array NULL_JOIN = ['s' => [0 => []]];

    public function __construct(int $start, int $limit, int $itemClass, private bool $applyTriggered, private EnchantmentContainer $relEnchants, private SpellContainer $relSpells)
    {
        $conditions = array(
            ['i.id', $start, '>'],
            ['class', $itemClass],
            $limit
        );

        $queryOpts = ['i' => array(
             0  => [],                                      // do not join misc tables
            's' => [', "" AS "icon"'],                      // placeholder icon so ItemEntry does not crash
            'o' => ['']                                     // skip sorting
        )];
        parent::__construct($conditions, ['queryOpts' => $queryOpts]);
    }

    public function writeStatsTable() : void
    {
        foreach ($this->iterate() as $itemEntry)
        {
            // array_merge kills the keys
            if ($spellIds = array_column(array_filter($itemEntry->spells, fn($x) => $x[0] > 0 && !$this->relSpells->getEntry($x[0]) && (($itemEntry->class == ITEM_CLASS_CONSUMABLE && $x[1] == SPELL_TRIGGER_USE) || $x[1] == SPELL_TRIGGER_EQUIP)), 0))
            {
                $triggered = [];
                $newSpells = new SpellContainer([['id', $spellIds]], ['queryOpts' => self::NULL_JOIN]);

                foreach ($newSpells->iterate() as $spellEntry)
                {
                    $this->relSpells->import($spellEntry);

                    if ($this->applyTriggered)
                        foreach ($spellEntry->canTriggerSpell() as $effIdx)
                            $triggered[] = $spellEntry->effectTriggerSpell[$effIdx];
                }

                // include triggered spell to calculate nutritional values
                if ($t = array_diff($triggered, $this->relSpells->getFoundIds()))
                {
                    $foodSpells = new SpellContainer([['id', $t]], ['queryOpts' => self::NULL_JOIN]);
                    $this->relSpells->import(...$foodSpells->export());
                }
            }

            // fromItem: itemMods, spell, enchants from template - fromJson: calculated stats (feralAP, dps, ...)
            if ($stats = (new StatsContainer($this->relSpells, $this->relEnchants))->fromItem($itemEntry)->fromJson($itemEntry->json)->toJson(Stat::FLAG_ITEM | Stat::FLAG_SERVERSIDE))
            {
                // manually set stats 0 if empty to distinguish from items that cant have them
                $shared = ['dps' => 0, 'dmgmin1' => 0, 'dmgmax1' => 0, 'speed' => 0];
                if ($itemEntry->class == ITEM_CLASS_WEAPON)
                    $stats += $shared + ($itemEntry->isRangedWeapon() ? ['rgddps' => 0, 'rgddmgmin' => 0, 'rgddmgmax' => 0, 'rgdspeed' => 0] : ['mledps' => 0, 'mledmgmin' => 0, 'mledmgmax' => 0, 'mlespeed' => 0]);
                else if ($itemEntry->class == ITEM_CLASS_ARMOR)
                    $stats += ['armorbonus' => 0];          //ArmorDamageModifier only valid on armor(%s)

                DB::Aowow()->qry('INSERT INTO ::item_stats %v', ['type' => Type::ITEM, 'typeId' => $this->id] + $stats);
            }
        }
    }
}

CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected array $info = array(
        'stats' => [[], CLISetup::ARGV_PARAM, 'Compiles stats data for type: Item & Enchantment from dbc and world db.']
    );

    protected array $setupAfter = [['items', 'spell', 'itemenchantment'], []];

    private SpellContainer $relSpells;

    private function enchantment_stats(?int &$total = 0, ?int &$effective = 0) : EnchantmentContainer
    {
        $enchants  = new EnchantmentContainer(miscData: ['calcTotal' => true]);
        $spells    = [];
        $stats     = [];
        $effective = 0;
        $total     = $enchants->getMatches();

        foreach ($enchants->iterate() as $eId => $eEntry)
            for ($i = 0; $i < 3; $i++)
                if ($eEntry->object[$i] > 0 && $eEntry->type[$i] == ENCHANTMENT_TYPE_EQUIP_SPELL)
                    $spells[] = $eEntry->object[$i];

        $this->relSpells = new SpellContainer($spells ? [['id', $spells]] : null, ['queryOpts' => ItemStatSetup::NULL_JOIN]);

        foreach ($enchants->iterate() as $eId => $eEntry)
            if ($stats = (new StatsContainer($this->relSpells))->fromEnchantment($eEntry)->toJson(Stat::FLAG_ITEM | Stat::FLAG_SERVERSIDE))
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
