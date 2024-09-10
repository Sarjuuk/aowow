<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


class ItemStatSetup extends ItemList
{
    private $relSpells   = [];
    private $relEnchants = [];

    public function __construct($start, $limit, array $ids, array $relEnchants, array $relSpells)
    {
        $this->queryOpts['i']['o'] = 'i.id ASC';
        unset($this->queryOpts['is']);                      // do not reference the stats table we are going to write to

        $conditions = array(
            ['i.id', $start, '>'],
            ['class', [ITEM_CLASS_WEAPON, ITEM_CLASS_GEM, ITEM_CLASS_ARMOR, ITEM_CLASS_CONSUMABLE, ITEM_CLASS_AMMUNITION]],
            $limit
        );

        if ($ids)
            $conditions[] = ['id', $ids];

        parent::__construct($conditions);

        $this->relSpells   = $relSpells;
        $this->relEnchants = $relEnchants;
    }

    public function writeStatsTable() : void
    {
        foreach ($this->iterate() as $id => $curTpl)
        {
            $spellIds  = [];

            for ($i = 1; $i <= 5; $i++)
                if ($this->curTpl['spellId'.$i] > 0 && !isset($this->relSpells[$this->curTpl['spellId'.$i]]) && (($this->curTpl['class'] == ITEM_CLASS_CONSUMABLE && $this->curTpl['spellTrigger'.$i] == SPELL_TRIGGER_USE) || $this->curTpl['spellTrigger'.$i] == SPELL_TRIGGER_EQUIP))
                    $spellIds[] = $this->curTpl['spellId'.$i];

            if ($spellIds)                                  // array_merge kills the keys
                $this->relSpells = array_replace($this->relSpells, DB::Aowow()->select('SELECT *, id AS ARRAY_KEY FROM ?_spell WHERE id IN (?a)', $spellIds));

            // fromItem: itemMods, spell, enchants from template - fromJson: calculated stats (feralAP, dps, ...)
            if ($stats = (new StatsContainer($this->relSpells, $this->relEnchants))->fromItem($curTpl)->fromJson($this->json[$id])->toJson(Stat::FLAG_ITEM | Stat::FLAG_SERVERSIDE))
            {
                // manually set stats 0 if empty to distinguish from items that cant have them
                $shared = ['dps' => 0, 'dmgmin1' => 0, 'dmgmax1' => 0, 'speed' => 0];
                if ($this->getField('class') == ITEM_CLASS_WEAPON)
                    $stats += $shared + ($this->isRangedWeapon() ? ['rgddps' => 0, 'rgddmgmin' => 0, 'rgddmgmax' => 0, 'rgdspeed' => 0] : ['mledps' => 0, 'mledmgmin' => 0, 'mledmgmax' => 0, 'mlespeed' => 0]);
                else if ($this->getField('class') == ITEM_CLASS_ARMOR)
                    $stats += ['armorbonus' => 0];          //ArmorDamageModifier only valid on armor(?)

                // apply PK
                $stats += ['type' => Type::ITEM, 'typeId' => $this->id];

                DB::Aowow()->query('INSERT INTO ?_item_stats (?#) VALUES (?a)', array_keys($stats), array_values($stats));
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

    private   $relSpells = [];

    private function enchantment_stats(?int &$total = 0, ?int &$effective = 0) : array
    {
        $enchants  = DB::Aowow()->select('SELECT *, `id` AS ARRAY_KEY FROM dbc_spellitemenchantment');
        $spells    = [];
        $result    = [];
        $effective = 0;
        $total     = count($enchants);

        foreach ($enchants as $eId => $e)
            for ($i = 1; $i <= 3; $i++)
                if ($e['object'.$i] > 0 && $e['type'.$i] == ENCHANTMENT_TYPE_EQUIP_SPELL)
                    $spells[] = $e['object'.$i];

        if ($spells)
            $this->relSpells = DB::Aowow()->select('SELECT *, id AS ARRAY_KEY FROM ?_spell WHERE id IN (?a)', $spells);

        foreach ($enchants as $eId => $e)
            if ($result[$eId] = (new StatsContainer($this->relSpells))->fromEnchantment($e)->toJson(Stat::FLAG_ITEM | Stat::FLAG_SERVERSIDE))
            {
                DB::Aowow()->query('INSERT INTO ?_item_stats (?#) VALUES (?a)', array_merge(['type', 'typeId'], array_keys($result[$eId])), array_merge([Type::ENCHANTMENT, $eId], array_values($result[$eId])));
                $effective++;
            }

        return $enchants;
    }

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE ?_item_stats');

        CLI::write('[stats] - applying stats for enchantments');

        $enchStats = $this->enchantment_stats($total, $effective);
        CLI::write('   '.$effective.'+'.($total - $effective).' enchantments parsed');

        CLI::write('[stats] - applying stats for items');

        $i = 0;
        $offset = 0;
        while (true)
        {
            $items = new ItemStatSetup($offset, CLISetup::SQL_BATCH, $ids, $enchStats, $this->relSpells);
            if ($items->error)
                break;

            CLI::write('[stats] * batch #' . ++$i . ' (' . count($items->getFoundIDs()) . ')', CLI::LOG_BLANK, true, true);

            $offset = max($items->getFoundIDs());

            $items->writeStatsTable();
        }

        return true;
    }
});

?>
