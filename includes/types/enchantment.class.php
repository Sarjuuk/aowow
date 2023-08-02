<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EnchantmentList extends BaseType
{
    use listviewHelper;

    public static   $type       = Type::ENCHANTMENT;
    public static   $brickFile  = 'enchantment';
    public static   $dataTable  = '?_itemenchantment';

    private         $jsonStats  = [];
    private         $relSpells  = [];
    private         $triggerIds = [];

    protected       $queryBase  = 'SELECT ie.*, ie.id AS ARRAY_KEY FROM ?_itemenchantment ie';
    protected       $queryOpts  = array(                    // 502 => Type::ENCHANTMENT
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
            foreach (Game::$itemMods as $str)
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
        $n = DB::Aowow()->SelectRow('SELECT name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8 FROM ?_itemenchantment WHERE id = ?d', $id );
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

        foreach (Game::$itemMods as $str)
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
                                if ($str = Game::$itemMods[$mod])
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
                    if ($str = Game::$itemMods[$obj])       // check if we use these mods
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
                $data[Type::ENCHANTMENT][$this->id] = ['name' => $this->getField('name', true)];

        if ($addMask & GLOBALINFO_RELATED)
        {
            if ($this->relSpells)
                $data = $this->relSpells->getJSGlobals(GLOBALINFO_SELF);

            foreach ($this->triggerIds as $tId)
                if (empty($data[Type::SPELL][$tId]))
                    $data[Type::SPELL][$tId] = $tId;
        }

        return $data;
    }

    public function renderTooltip() { }
}


class EnchantmentListFilter extends Filter
{
    protected $enums         = array(
        3 => parent::ENUM_PROFESSION                        // requiresprof
    );

    protected $genericFilter = array(
          2 => [FILTER_CR_NUMERIC, 'id',                 NUM_CAST_INT,         true], // id
          3 => [FILTER_CR_ENUM,    'skillLine'                                     ], // requiresprof
          4 => [FILTER_CR_NUMERIC, 'skillLevel',         NUM_CAST_INT              ], // reqskillrank
          5 => [FILTER_CR_BOOLEAN, 'conditionId'                                   ], // hascondition
         10 => [FILTER_CR_FLAG,    'cuFlags',            CUSTOM_HAS_COMMENT        ], // hascomments
         11 => [FILTER_CR_FLAG,    'cuFlags',            CUSTOM_HAS_SCREENSHOT     ], // hasscreenshots
         12 => [FILTER_CR_FLAG,    'cuFlags',            CUSTOM_HAS_VIDEO          ], // hasvideos
         20 => [FILTER_CR_NUMERIC, 'is.str',             NUM_CAST_INT,         true], // str
         21 => [FILTER_CR_NUMERIC, 'is.agi',             NUM_CAST_INT,         true], // agi
         22 => [FILTER_CR_NUMERIC, 'is.sta',             NUM_CAST_INT,         true], // sta
         23 => [FILTER_CR_NUMERIC, 'is.int',             NUM_CAST_INT,         true], // int
         24 => [FILTER_CR_NUMERIC, 'is.spi',             NUM_CAST_INT,         true], // spi
         25 => [FILTER_CR_NUMERIC, 'is.arcres',          NUM_CAST_INT,         true], // arcres
         26 => [FILTER_CR_NUMERIC, 'is.firres',          NUM_CAST_INT,         true], // firres
         27 => [FILTER_CR_NUMERIC, 'is.natres',          NUM_CAST_INT,         true], // natres
         28 => [FILTER_CR_NUMERIC, 'is.frores',          NUM_CAST_INT,         true], // frores
         29 => [FILTER_CR_NUMERIC, 'is.shares',          NUM_CAST_INT,         true], // shares
         30 => [FILTER_CR_NUMERIC, 'is.holres',          NUM_CAST_INT,         true], // holres
         32 => [FILTER_CR_NUMERIC, 'is.dps',             NUM_CAST_FLOAT,       true], // dps
         34 => [FILTER_CR_NUMERIC, 'is.dmg',             NUM_CAST_FLOAT,       true], // dmg
         37 => [FILTER_CR_NUMERIC, 'is.mleatkpwr',       NUM_CAST_INT,         true], // mleatkpwr
         38 => [FILTER_CR_NUMERIC, 'is.rgdatkpwr',       NUM_CAST_INT,         true], // rgdatkpwr
         39 => [FILTER_CR_NUMERIC, 'is.rgdhitrtng',      NUM_CAST_INT,         true], // rgdhitrtng
         40 => [FILTER_CR_NUMERIC, 'is.rgdcritstrkrtng', NUM_CAST_INT,         true], // rgdcritstrkrtng
         41 => [FILTER_CR_NUMERIC, 'is.armor'   ,        NUM_CAST_INT,         true], // armor
         42 => [FILTER_CR_NUMERIC, 'is.defrtng',         NUM_CAST_INT,         true], // defrtng
         43 => [FILTER_CR_NUMERIC, 'is.block',           NUM_CAST_INT,         true], // block
         44 => [FILTER_CR_NUMERIC, 'is.blockrtng',       NUM_CAST_INT,         true], // blockrtng
         45 => [FILTER_CR_NUMERIC, 'is.dodgertng',       NUM_CAST_INT,         true], // dodgertng
         46 => [FILTER_CR_NUMERIC, 'is.parryrtng',       NUM_CAST_INT,         true], // parryrtng
         48 => [FILTER_CR_NUMERIC, 'is.splhitrtng',      NUM_CAST_INT,         true], // splhitrtng
         49 => [FILTER_CR_NUMERIC, 'is.splcritstrkrtng', NUM_CAST_INT,         true], // splcritstrkrtng
         50 => [FILTER_CR_NUMERIC, 'is.splheal',         NUM_CAST_INT,         true], // splheal
         51 => [FILTER_CR_NUMERIC, 'is.spldmg',          NUM_CAST_INT,         true], // spldmg
         52 => [FILTER_CR_NUMERIC, 'is.arcsplpwr',       NUM_CAST_INT,         true], // arcsplpwr
         53 => [FILTER_CR_NUMERIC, 'is.firsplpwr',       NUM_CAST_INT,         true], // firsplpwr
         54 => [FILTER_CR_NUMERIC, 'is.frosplpwr',       NUM_CAST_INT,         true], // frosplpwr
         55 => [FILTER_CR_NUMERIC, 'is.holsplpwr',       NUM_CAST_INT,         true], // holsplpwr
         56 => [FILTER_CR_NUMERIC, 'is.natsplpwr',       NUM_CAST_INT,         true], // natsplpwr
         57 => [FILTER_CR_NUMERIC, 'is.shasplpwr',       NUM_CAST_INT,         true], // shasplpwr
         60 => [FILTER_CR_NUMERIC, 'is.healthrgn',       NUM_CAST_INT,         true], // healthrgn
         61 => [FILTER_CR_NUMERIC, 'is.manargn',         NUM_CAST_INT,         true], // manargn
         77 => [FILTER_CR_NUMERIC, 'is.atkpwr',          NUM_CAST_INT,         true], // atkpwr
         78 => [FILTER_CR_NUMERIC, 'is.mlehastertng',    NUM_CAST_INT,         true], // mlehastertng
         79 => [FILTER_CR_NUMERIC, 'is.resirtng',        NUM_CAST_INT,         true], // resirtng
         84 => [FILTER_CR_NUMERIC, 'is.mlecritstrkrtng', NUM_CAST_INT,         true], // mlecritstrkrtng
         94 => [FILTER_CR_NUMERIC, 'is.splpen',          NUM_CAST_INT,         true], // splpen
         95 => [FILTER_CR_NUMERIC, 'is.mlehitrtng',      NUM_CAST_INT,         true], // mlehitrtng
         96 => [FILTER_CR_NUMERIC, 'is.critstrkrtng',    NUM_CAST_INT,         true], // critstrkrtng
         97 => [FILTER_CR_NUMERIC, 'is.feratkpwr',       NUM_CAST_INT,         true], // feratkpwr
        101 => [FILTER_CR_NUMERIC, 'is.rgdhastertng',    NUM_CAST_INT,         true], // rgdhastertng
        102 => [FILTER_CR_NUMERIC, 'is.splhastertng',    NUM_CAST_INT,         true], // splhastertng
        103 => [FILTER_CR_NUMERIC, 'is.hastertng',       NUM_CAST_INT,         true], // hastertng
        114 => [FILTER_CR_NUMERIC, 'is.armorpenrtng',    NUM_CAST_INT,         true], // armorpenrtng
        115 => [FILTER_CR_NUMERIC, 'is.health',          NUM_CAST_INT,         true], // health
        116 => [FILTER_CR_NUMERIC, 'is.mana',            NUM_CAST_INT,         true], // mana
        117 => [FILTER_CR_NUMERIC, 'is.exprtng',         NUM_CAST_INT,         true], // exprtng
        119 => [FILTER_CR_NUMERIC, 'is.hitrtng',         NUM_CAST_INT,         true], // hitrtng
        123 => [FILTER_CR_NUMERIC, 'is.splpwr',          NUM_CAST_INT,         true]  // splpwr
    );

    protected $inputFields = array(
        'cr'  => [FILTER_V_RANGE, [2, 123],             true ], // criteria ids
        'crs' => [FILTER_V_RANGE, [1, 15],              true ], // criteria operators
        'crv' => [FILTER_V_REGEX, parent::PATTERN_INT,  true ], // criteria values - only numerals
        'na'  => [FILTER_V_REGEX, parent::PATTERN_NAME, false], // name - only printable chars, no delimiter
        'ma'  => [FILTER_V_EQUAL, 1,                    false], // match any / all filter
        'ty'  => [FILTER_V_RANGE, [1, 8],               true ]  // types
    );

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
            $parts[] = ['OR', ['type1', $_v['ty']], ['type2', $_v['ty']], ['type3', $_v['ty']]];

        return $parts;
    }
}

?>
