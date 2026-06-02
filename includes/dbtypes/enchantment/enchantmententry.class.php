<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EnchantmentEntry extends DBTypeEntry
{
    public readonly int       $cuFlags;
    public readonly LocString $name;
    public readonly int       $charges;
    public readonly int       $procChance;
    public readonly float     $ppmRate;
    /** @var int[] $type - length: 3 */
    public readonly array     $type;
    /** @var int[] $amount - length: 3 */
    public readonly array     $amount;
    /** @var int[] $object - length: 3 */
    public readonly array     $object;
    /** @var int[][] $spells - length: 3 [spellId, triggerType, charges, chanceOrPPM] */
    public readonly array     $spells;
    public readonly int       $conditionId;
    public readonly int       $skillLine;
    public readonly int       $skillLevel;
    public readonly int       $requiredLevel;

    public static int    $dbType    = Type::ENCHANTMENT;
    public static string $brickFile = 'enchantment';
    public static string $dataTable = '::itemenchantment';

    private ?SpellContainer $relSpells  = null;
    private  array          $jsonStats  = [];
    private  array          $triggerIds = [];

    public const /* string */ QUERY_BASE = 'SELECT ie.*, ie.id AS ARRAY_KEY FROM ::itemenchantment ie';
    public const /* array  */ QUERY_OPTS = array(           // 502 => Type::ENCHANTMENT
        'ie' => [],
        'iv' => ['j' => ['::itemvisuals `iv` ON `ie`.`itemVisualId` = `iv`.`id`']],
        'is' => ['j' => ['::item_stats `is`  ON `is`.`type` = 502 AND `is`.`typeId` = `ie`.`id`', true], 's' => ', `is`.*'],
    );

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->name = new LocString($initData, 'name', pruneFromSrc: true);

        $this->type   = [$initData['type1'],   $initData['type2'],   $initData['type3']];
        $this->amount = [$initData['amount1'], $initData['amount2'], $initData['amount3']];
        $this->object = [$initData['object1'], $initData['object2'], $initData['object3']];

        $spells = [];
        for ($i = 1; $i < 4; $i++)
        {
            if ($initData['object'.$i] <= 0)
                continue;

            switch ($initData['type'.$i])                   // SPELL_TRIGGER_* just reused for wording
            {
                case ENCHANTMENT_TYPE_COMBAT_SPELL:
                    $proc = -$initData['ppmRate'] ?: ($initData['procChance'] ?: $initData['amount'.$i]);
                    $spells[] = [$initData['object'.$i], SPELL_TRIGGER_HIT, $initData['charges'], $proc];
                    break;
                case ENCHANTMENT_TYPE_EQUIP_SPELL:
                    $spells[] = [$initData['object'.$i], SPELL_TRIGGER_EQUIP, $initData['charges'], 0];
                    break;
                case ENCHANTMENT_TYPE_USE_SPELL:
                    $spells[] = [$initData['object'.$i], SPELL_TRIGGER_USE, $initData['charges'], 0];
                    break;
            }
        }
        $this->spells = $spells;

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'type':                                // col from ::stats
                case 'id':                                  // id defined by parent
                    continue 2;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        $data = array(
            'id'     => $this->id,
            'name'   => $this->name,
            'spells' => []
        );

        if ($this->skillLine > 0)
            $data['reqskill'] = $this->skillLine;

        if ($this->skillLevel > 0)
            $data['reqskillrank'] = $this->skillLevel;

        if ($this->requiredLevel > 0)
            $data['reqlevel'] = $this->requiredLevel;

        foreach ($this->spells as [$spellId, , $charges, ])
        {
            // spell is procing
            $trgSpell = 0;
            if ($this->relSpells && ($entry = $this->relSpells->getEntry($spellId)))
            {
                foreach ($entry->canTriggerSpell() as $idx)
                {
                    if ($trgSpell = $entry->effectTriggerSpell[$idx])
                    {
                        $this->triggerIds[] = $trgSpell;
                        $data['spells'][$trgSpell] = $charges;
                    }
                }
            }

            // spell was not proccing
            if (!$trgSpell)
                $data['spells'][$spellId] = $charges;
        }

        if (!$data['spells'])
            unset($data['spells']);

        // do not include 0-amount stats
        Util::arraySumByKey($data, array_filter($this->getStatGain()));

        return $data;
    }

    public function getJSGlobal(int $addMask = GLOBALINFO_ANY) : array
    {
        $data = [];

        if ($addMask & GLOBALINFO_SELF)
            $data[Type::ENCHANTMENT][$this->id] = ['name' => $this->name];

        if ($addMask & GLOBALINFO_RELATED)
        {
            if ($this->initRelSpells())
                $data = $this->relSpells->getJSGlobals(GLOBALINFO_SELF);

            foreach ($this->triggerIds as $tId)
                $data[Type::SPELL][$tId] ??= $tId;
        }

        return $data;
    }

    public function renderTooltip() : ?string { return null; }

    public function getStatGain() : array
    {
        if (!$this->initRelSpells())
            return [];

        // issue with scaling stats enchantments
        // stats are stored as NOT NULL to be usable by the search filters and such become indistinguishable from scaling enchantments that _actually_ use the value 0
        // so we can't rely on ::item_stats and always have to calc stats
        return $this->jsonStats ??= ((new StatsContainer($this->relSpells->export()))->fromEnchantment((array)$this))->toJson();
    }

    public function getRelSpell(int $id) : ?SpellEntry
    {
        if ($this->relSpells)
            return $this->relSpells->getEntry($id);

        return null;
    }

    public function setRelSpells(SpellEntry ...$entries) : void
    {
        if (!$this->relSpells)
            $this->relSpells = new SpellContainer(null);

        $this->relSpells->import(...$entries);
    }

    private function initRelSpells() : bool
    {
        if ($this->relSpells)
            return !$this->relSpells->error;

        if (!($spellIds = array_column($this->spells, 0)))
            return false;

        $this->relSpells = new SpellContainer(array(['id', $spellIds]));
        return $this->relSpells->error;
    }
}

?>
