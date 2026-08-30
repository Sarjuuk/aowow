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
    /** @var StatsContainer $itemStats importet from ::item_stats table */
    public readonly  StatsContainer $itemStats;

    public static int    $dbType    = Type::ENCHANTMENT;
    public static string $brickFile = 'enchantment';
    public static string $dataTable = '::itemenchantment';

    private ?SpellContainer $relSpells  = null;
    private ?array          $jsonStats  = null;
    private  array          $triggerIds = [];

    public const string QUERY_BASE = 'SELECT ie.*, ie.id AS ARRAY_KEY FROM ::itemenchantment ie';
    public const array  QUERY_OPTS = array(                 // 502 => Type::ENCHANTMENT
        'ie' => [['is']],
        'iv' => ['j' => ['::itemvisuals `iv` ON `ie`.`itemVisualId` = `iv`.`id`']],
        'is' => ['j' => ['::item_stats `is`  ON `is`.`type` = 502 AND `is`.`typeId` = `ie`.`id`', true], 's' => ', `is`.*'],
    );

    public function applyInitData(array $initData, array $opts) : bool
    {
        parent::applyInitData($initData, $opts);

        $this->cuFlags       = $initData['cuFlags'];
        $this->name          = new LocString($initData, 'name');
        $this->charges       = $initData['charges'];
        $this->procChance    = $initData['procChance'];
        $this->ppmRate       = $initData['ppmRate'];
        $this->type          = [$initData['type1'],   $initData['type2'],   $initData['type3']];
        $this->amount        = [$initData['amount1'], $initData['amount2'], $initData['amount3']];
        $this->object        = [$initData['object1'], $initData['object2'], $initData['object3']];
        $this->conditionId   = $initData['conditionId'];
        $this->skillLine     = $initData['skillLine'];
        $this->skillLevel    = $initData['skillLevel'];
        $this->requiredLevel = $initData['requiredLevel'];
        $this->itemStats     = (new StatsContainer)->fromJson($initData, true);

        $spells = [];
        for ($i = 1; $i < 4; $i++)
        {
            if ($initData['object'.$i] <= 0)
                continue;

            $spells[] = match ($initData['type'.$i])        // SPELL_TRIGGER_* just reused for wording
            {
                ENCHANTMENT_TYPE_EQUIP_SPELL  => [$initData['object'.$i], SPELL_TRIGGER_EQUIP, $initData['charges'], 0],
                ENCHANTMENT_TYPE_USE_SPELL    => [$initData['object'.$i], SPELL_TRIGGER_USE,   $initData['charges'], 0],
                ENCHANTMENT_TYPE_COMBAT_SPELL => [$initData['object'.$i], SPELL_TRIGGER_HIT,   $initData['charges'], -$initData['ppmRate'] ?: ($initData['procChance'] ?: $initData['amount'.$i])],
                default                       => null
            };
        }
        $this->spells = array_filter($spells);

        return true;
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        $data = array(
            'id'     => $this->id,
            'name'   => $this->name
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

        Util::arraySumByKey($data, $this->itemStats->toJson(Stat::FLAG_ITEM, false));

        // do not include 0-amount stats
        Util::arraySumByKey($data, array_filter($this->getStatGain()));

        return $data;
    }

    /**
     * @param int $addMask mask of data to be exposed to javascript
     *  - always add enchantment to @return
     *  - GLOBALINFO_RELATED - add applied and triggered spells to @return
     *
     * @return array<int, array<int, int|array>[]>[] [type => [typeId => placeholder or data]]
     */
    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
    {
        $data[self::$dbType][$this->id] = ['name' => $this->name];

        if ($addMask & GLOBALINFO_RELATED)
        {
            if ($this->initRelSpells())
                $data += $this->relSpells->getJSGlobals();

            foreach ($this->triggerIds as $tId)
                $data[Type::SPELL][$tId] ??= $tId;
        }

        return $data;
    }

    public function getStatGain() : array
    {
        $this->initRelSpells();

        // issue with scaling stats enchantments
        // stats are stored as NOT NULL to be usable by the search filters and such become indistinguishable from scaling enchantments that _actually_ use the value 0
        // so we can't rely on ::item_stats and always have to calc stats
        return $this->jsonStats ??= (new StatsContainer($this->relSpells))->fromEnchantment($this)->toJson();
    }

    public function getRelSpell(int $id) : ?SpellEntry
    {
        if (!$this->initRelSpells())
            return null;

        return $this->relSpells->getEntry($id);
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
        return !$this->relSpells->error;
    }
}

?>
