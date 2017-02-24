<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EnchantmentList extends BaseType
{
    use listviewHelper;

    public static   $type       = TYPE_ENCHANTMENT;
    public static   $brickFile  = 'enchantment';
    public static   $dataTable  = '?_itemenchantment';

    private         $jsonStats  = [];
    private         $relSpells  = [];
    private         $triggerIds = [];

    protected       $queryBase  = 'SELECT ie.*, ie.id AS ARRAY_KEY FROM ?_itemenchantment ie';
    protected       $queryOpts  = array(                    // 502 => TYPE_ENCHANTMENT
                        'ie'  => [['is']],
                        'is'  => ['j' => ['?_item_stats `is`  ON `is`.`type` = 502 AND `is`.`typeId` = `ie`.`id`', true], 's' => ', `is`.*'],
                    );

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

        // post processing
        foreach ($this->iterate() as &$curTpl)
        {
            $curTpl['spells'] = [];                         // [spellId, triggerType, charges, chanceOrPpm]
            for ($i = 1; $i <=3; $i++)
            {
                if ($curTpl['object'.$i] <= 0)
                    continue;

                switch ($curTpl['type'.$i])
                {
                    case 1:
                        $proc = -$this->getField('ppmRate') ?: ($this->getField('procChance') ?: $this->getField('amount'.$i));
                        $curTpl['spells'][$i] = [$curTpl['object'.$i], 2, $curTpl['charges'], $proc];
                        $this->relSpells[]    =  $curTpl['object'.$i];
                        break;
                    case 3:
                        $curTpl['spells'][$i] = [$curTpl['object'.$i], 1, $curTpl['charges'], 0];
                        $this->relSpells[]    =  $curTpl['object'.$i];
                        break;
                    case 7:
                        $curTpl['spells'][$i] = [$curTpl['object'.$i], 0, $curTpl['charges'], 0];
                        $this->relSpells[]    =  $curTpl['object'.$i];
                        break;
                }
            }

            // floats are fetched as string from db :<
            $curTpl['dmg'] = floatVal($curTpl['dmg']);
            $curTpl['dps'] = floatVal($curTpl['dps']);

            // remove zero-stats
            foreach (Util::$itemMods as $str)
                if ($curTpl[$str] == 0)                     // empty(0.0f) => true .. yeah, sure
                    unset($curTpl[$str]);

            if ($curTpl['dps'] == 0)
                unset($curTpl['dps']);
        }

        if ($this->relSpells)
            $this->relSpells = new SpellList(array(['id', $this->relSpells]));
    }

    // use if you JUST need the name
    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('SELECT name_loc0, name_loc2, name_loc3, name_loc6, name_loc8 FROM ?_itemenchantment WHERE id = ?d', $id );
        return Util::localizedString($n, 'name');
    }
    // end static use

    public function getListviewData($addInfoMask = 0x0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'     => $this->id,
                'name'   => $this->getField('name', true),
                'spells' => []
            );

            if ($this->curTpl['skillLine'] > 0)
                $data[$this->id]['reqskill'] = $this->curTpl['skillLine'];

            if ($this->curTpl['skillLevel'] > 0)
                $data[$this->id]['reqskillrank'] = $this->curTpl['skillLevel'];

            if ($this->curTpl['requiredLevel'] > 0)
                $data[$this->id]['reqlevel'] = $this->curTpl['requiredLevel'];

            foreach ($this->curTpl['spells'] as $s)
            {
                // enchant is procing or onUse
                if ($s[1] == 2 || $s[1] == 0)
                    $data[$this->id]['spells'][$s[0]] = $s[2];
                // spell is procing
                else if ($this->relSpells && $this->relSpells->getEntry($s[0]) && ($_ = $this->relSpells->canTriggerSpell()))
                {
                    foreach ($_ as $idx)
                    {
                        $this->triggerIds[] = $this->relSpells->getField('effect'.$idx.'TriggerSpell');
                        $data[$this->id]['spells'][$this->relSpells->getField('effect'.$idx.'TriggerSpell')] = $s[2];
                    }
                }
            }

            if (!$data[$this->id]['spells'])
                unset($data[$this->id]['spells']);

            Util::arraySumByKey($data[$this->id], $this->getStatGain());
        }

        return $data;
    }

    public function getStatGain($addScalingKeys = false)
    {
        $data = [];

        foreach (Util::$itemMods as $str)
            if (isset($this->curTpl[$str]))
                $data[$str] = $this->curTpl[$str];

        if (isset($this->curTpl['dps']))
            $data['dps'] = $this->curTpl['dps'];

        // scaling enchantments are saved as 0 to item_stats, thus return empty
        if ($addScalingKeys)
        {
            $spellStats = [];
            if ($this->relSpells)
                $spellStats = $this->relSpells->getStatGain();

            for ($h = 1; $h <= 3; $h++)
            {
                $obj = (int)$this->curTpl['object'.$h];

                switch ($this->curTpl['type'.$h])
                {
                    case 3:                                 // TYPE_EQUIP_SPELL         Spells from ObjectX (use of amountX?)
                        if (!empty($spellStats[$obj]))
                            foreach ($spellStats[$obj] as $mod => $_)
                                if ($str = Util::$itemMods[$mod])
                                    Util::arraySumByKey($data, [$str => 0]);

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
                    default:                                // TYPE_NONE                dnd stuff; skip assignment below
                        $obj = null;
                }

                if ($obj !== null)
                    if ($str = Util::$itemMods[$obj])       // check if we use these mods
                        Util::arraySumByKey($data, [$str => 0]);
            }
        }

        return $data;
    }

    public function getRelSpell($id)
    {
        if ($this->relSpells)
            return $this->relSpells->getEntry($id);

        return null;
    }

    public function getJSGlobals($addMask = GLOBALINFO_ANY)
    {
        $data = [];

        if ($addMask & GLOBALINFO_SELF)
            foreach ($this->iterate() as $__)
                $data[TYPE_ENCHANTMENT][$this->id] = ['name' => $this->getField('name', true)];

        if ($addMask & GLOBALINFO_RELATED)
        {
            if ($this->relSpells)
                $data = $this->relSpells->getJSGlobals(GLOBALINFO_SELF);

            foreach ($this->triggerIds as $tId)
                if (empty($data[TYPE_SPELL][$tId]))
                    $data[TYPE_SPELL][$tId] = $tId;
        }

        return $data;
    }

    public function renderTooltip() { }
}


class EnchantmentListFilter extends Filter
{
    protected $enums         = array(
        3 => array(                                         // requiresprof
            null, 171, 164, 185, 333, 202, 129, 755, 165, 186, 197, true, false, 356, 182, 773
        )
    );

    protected $genericFilter = array(                       // misc (bool): _NUMERIC => useFloat; _STRING => localized; _FLAG => match Value; _BOOLEAN => stringSet
          2 => [FILTER_CR_NUMERIC, 'id',                 null,                 true], // id
          3 => [FILTER_CR_ENUM,    'skillLine'                                     ], // requiresprof
          4 => [FILTER_CR_NUMERIC, 'skillLevel',                                   ], // reqskillrank
          5 => [FILTER_CR_BOOLEAN, 'conditionId'                                   ], // hascondition
         10 => [FILTER_CR_FLAG,    'cuFlags',            CUSTOM_HAS_COMMENT        ], // hascomments
         11 => [FILTER_CR_FLAG,    'cuFlags',            CUSTOM_HAS_SCREENSHOT     ], // hasscreenshots
         12 => [FILTER_CR_FLAG,    'cuFlags',            CUSTOM_HAS_VIDEO          ], // hasvideos
         21 => [FILTER_CR_NUMERIC, 'is.agi',             null,                 true], // agi
         23 => [FILTER_CR_NUMERIC, 'is.int',             null,                 true], // int
         22 => [FILTER_CR_NUMERIC, 'is.sta',             null,                 true], // sta
         24 => [FILTER_CR_NUMERIC, 'is.spi',             null,                 true], // spi
         20 => [FILTER_CR_NUMERIC, 'is.str',             null,                 true], // str
        115 => [FILTER_CR_NUMERIC, 'is.health',          null,                 true], // health
        116 => [FILTER_CR_NUMERIC, 'is.mana',            null,                 true], // mana
         60 => [FILTER_CR_NUMERIC, 'is.healthrgn',       null,                 true], // healthrgn
         61 => [FILTER_CR_NUMERIC, 'is.manargn',         null,                 true], // manargn
         41 => [FILTER_CR_NUMERIC, 'is.armor'   ,        null,                 true], // armor
         44 => [FILTER_CR_NUMERIC, 'is.blockrtng',       null,                 true], // blockrtng
         43 => [FILTER_CR_NUMERIC, 'is.block',           null,                 true], // block
         42 => [FILTER_CR_NUMERIC, 'is.defrtng',         null,                 true], // defrtng
         45 => [FILTER_CR_NUMERIC, 'is.dodgertng',       null,                 true], // dodgertng
         46 => [FILTER_CR_NUMERIC, 'is.parryrtng',       null,                 true], // parryrtng
         79 => [FILTER_CR_NUMERIC, 'is.resirtng',        null,                 true], // resirtng
         77 => [FILTER_CR_NUMERIC, 'is.atkpwr',          null,                 true], // atkpwr
         97 => [FILTER_CR_NUMERIC, 'is.feratkpwr',       null,                 true], // feratkpwr
        114 => [FILTER_CR_NUMERIC, 'is.armorpenrtng',    null,                 true], // armorpenrtng
         96 => [FILTER_CR_NUMERIC, 'is.critstrkrtng',    null,                 true], // critstrkrtng
        117 => [FILTER_CR_NUMERIC, 'is.exprtng',         null,                 true], // exprtng
        103 => [FILTER_CR_NUMERIC, 'is.hastertng',       null,                 true], // hastertng
        119 => [FILTER_CR_NUMERIC, 'is.hitrtng',         null,                 true], // hitrtng
         94 => [FILTER_CR_NUMERIC, 'is.splpen',          null,                 true], // splpen
        123 => [FILTER_CR_NUMERIC, 'is.splpwr',          null,                 true], // splpwr
         52 => [FILTER_CR_NUMERIC, 'is.arcsplpwr',       null,                 true], // arcsplpwr
         53 => [FILTER_CR_NUMERIC, 'is.firsplpwr',       null,                 true], // firsplpwr
         54 => [FILTER_CR_NUMERIC, 'is.frosplpwr',       null,                 true], // frosplpwr
         55 => [FILTER_CR_NUMERIC, 'is.holsplpwr',       null,                 true], // holsplpwr
         56 => [FILTER_CR_NUMERIC, 'is.natsplpwr',       null,                 true], // natsplpwr
         57 => [FILTER_CR_NUMERIC, 'is.shasplpwr',       null,                 true], // shasplpwr
         32 => [FILTER_CR_NUMERIC, 'is.dps',             true,                 true], // dps
         34 => [FILTER_CR_NUMERIC, 'is.dmg',             true,                 true], // dmg
         25 => [FILTER_CR_NUMERIC, 'is.arcres',          null,                 true], // arcres
         26 => [FILTER_CR_NUMERIC, 'is.firres',          null,                 true], // firres
         28 => [FILTER_CR_NUMERIC, 'is.frores',          null,                 true], // frores
         30 => [FILTER_CR_NUMERIC, 'is.holres',          null,                 true], // holres
         27 => [FILTER_CR_NUMERIC, 'is.natres',          null,                 true], // natres
         29 => [FILTER_CR_NUMERIC, 'is.shares',          null,                 true], // shares
         37 => [FILTER_CR_NUMERIC, 'is.mleatkpwr',       null,                 true], // mleatkpwr
         84 => [FILTER_CR_NUMERIC, 'is.mlecritstrkrtng', null,                 true], // mlecritstrkrtng
         78 => [FILTER_CR_NUMERIC, 'is.mlehastertng',    null,                 true], // mlehastertng
         95 => [FILTER_CR_NUMERIC, 'is.mlehitrtng',      null,                 true], // mlehitrtng
         38 => [FILTER_CR_NUMERIC, 'is.rgdatkpwr',       null,                 true], // rgdatkpwr
         40 => [FILTER_CR_NUMERIC, 'is.rgdcritstrkrtng', null,                 true], // rgdcritstrkrtng
        101 => [FILTER_CR_NUMERIC, 'is.rgdhastertng',    null,                 true], // rgdhastertng
         39 => [FILTER_CR_NUMERIC, 'is.rgdhitrtng',      null,                 true], // rgdhitrtng
         49 => [FILTER_CR_NUMERIC, 'is.splcritstrkrtng', null,                 true], // splcritstrkrtng
        102 => [FILTER_CR_NUMERIC, 'is.splhastertng',    null,                 true], // splhastertng
         48 => [FILTER_CR_NUMERIC, 'is.splhitrtng',      null,                 true], // splhitrtng
         51 => [FILTER_CR_NUMERIC, 'is.spldmg',          null,                 true], // spldmg
         50 => [FILTER_CR_NUMERIC, 'is.splheal',         null,                 true]  // splheal
    );

    protected function createSQLForCriterium(&$cr)
    {
        if (in_array($cr[0], array_keys($this->genericFilter)))
        {
            if ($genCr = $this->genericCriterion($cr))
                return $genCr;

            unset($cr);
            $this->error = true;
            return [1];
        }
    }

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = &$this->fiData['v'];

        //string
        if (isset($_v['na']))
            if ($_ = $this->modularizeString(['name_loc'.User::$localeId]))
                $parts[] = $_;

        // type
        if (isset($_v['ty']))
        {
            $_ = (array)$_v['ty'];
            if (!array_diff($_, [1, 2, 3, 4, 5, 6, 7, 8]))
                $parts[] = ['OR', ['type1', $_], ['type2', $_], ['type3', $_]];
            else
                unset($_v['ty']);
        }

        return $parts;
    }
}

?>
