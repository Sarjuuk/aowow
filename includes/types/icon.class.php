<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class IconList extends BaseType
{
    use listviewHelper;

    public static   $type       = Type::ICON;
    public static   $brickFile  = 'icongallery';
    public static   $dataTable  = '?_icons';
    public static   $contribute = CONTRIBUTE_CO;

    private         $pseudoQry  = 'SELECT `iconId` AS ARRAY_KEY, COUNT(*) FROM ?# WHERE `iconId` IN (?a) GROUP BY `iconId`';
    private         $pseudoJoin = array(
        'nItems'        => '?_items',
        'nSpells'       => '?_spell',
        'nAchievements' => '?_achievement',
        'nCurrencies'   => '?_currencies',
        'nPets'         => '?_pet'
    );

    protected       $queryBase  = 'SELECT ic.*, ic.id AS ARRAY_KEY FROM ?_icons ic';
    /* this works, but takes ~100x more time than i'm comfortable with .. kept as reference
    protected       $queryOpts  = array(                    // 29 => Type::ICON
                        'ic'  => [['s', 'i', 'a', 'c', 'p'], 'g' => 'ic.id'],
                        'i'   => ['j' => ['?_items `i`  ON `i`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `i`.`id`) AS nItems'],
                        's'   => ['j' => ['?_spell `s`  ON `s`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `s`.`id`) AS nSpells'],
                        'a'   => ['j' => ['?_achievement `a`  ON `a`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `a`.`id`) AS nAchievements'],
                        'c'   => ['j' => ['?_currencies `c`  ON `c`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `c`.`id`) AS nCurrencies'],
                        'p'   => ['j' => ['?_pet `p`  ON `p`.`iconId` = `ic`.`id`', true], 's' => ', COUNT(DISTINCT `p`.`id`) AS nPets']
                    );
    */

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        if (!$this->getFoundIDs())
            return;

        foreach ($this->pseudoJoin as $var => $tbl)
        {
            $res = DB::Aowow()->selectCol($this->pseudoQry, $tbl, $this->getFoundIDs());
            foreach ($res as $icon => $qty)
                $this->templates[$icon][$var] = $qty;
        }
    }


    // use if you JUST need the name
    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('SELECT `name` FROM ?_icons WHERE `id` = ?d', $id );
        return Util::localizedString($n, 'name');
    }
    // end static use

    public function getListviewData($addInfoMask = 0x0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'                  => $this->id,
                'name'                => $this->getField('name', true, true),
                'icon'                => $this->getField('name', true, true),
                'itemcount'           => (int)$this->getField('nItems'),
                'spellcount'          => (int)$this->getField('nSpells'),
                'achievementcount'    => (int)$this->getField('nAchievements'),
                'npccount'            => 0,                 // UNUSED
                'petabilitycount'     => 0,                 // UNUSED
                'currencycount'       => (int)$this->getField('nCurrencies'),
                'missionabilitycount' => 0,                 // UNUSED
                'buildingcount'       => 0,                 // UNUSED
                'petcount'            => (int)$this->getField('nPets'),
                'threatcount'         => 0,                 // UNUSED
                'classcount'          => 0                  // class icons are hardcoded and not referenced in dbc
            );
        }

        return $data;
    }

    public function getJSGlobals($addMask = GLOBALINFO_ANY)
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[Type::ICON][$this->id] = ['name' => $this->getField('name', true, true), 'icon' => $this->getField('name', true, true)];

        return $data;
    }

    public function renderTooltip() { }
}


class IconListFilter extends Filter
{
    public $extraOpts = null;

    private $criterion2field = array(
          1 => '?_items',                                   // items [num]
          2 => '?_spell',                                   // spells [num]
          3 => '?_achievement',                             // achievements [num]
    //    4 => '',                                          // battlepets [num]
    //    5 => '',                                          // battlepetabilities [num]
          6 => '?_currencies',                              // currencies [num]
    //    7 => '',                                          // garrisonabilities [num]
    //    8 => '',                                          // garrisonbuildings [num]
          9 => '?_pet',                                     // hunterpets [num]
    //   10 => '',                                          // garrisonmissionthreats [num]
         11 => '',                                          // classes [num]
         13 => ''                                           // used [num]
    );
    private $totalUses       = [];

    protected $genericFilter = array(
         1 => [parent::CR_CALLBACK, 'cbUseAny'  ],          // items [num]
         2 => [parent::CR_CALLBACK, 'cbUseAny'  ],          // spells [num]
         3 => [parent::CR_CALLBACK, 'cbUseAny'  ],          // achievements [num]
         6 => [parent::CR_CALLBACK, 'cbUseAny'  ],          // currencies [num]
         9 => [parent::CR_CALLBACK, 'cbUseAny'  ],          // hunterpets [num]
        11 => [parent::CR_NYI_PH,   null,      0],          // classes [num]
        13 => [parent::CR_CALLBACK, 'cbUseAll'  ]           // used [num]
    );

    protected $inputFields = array(
        'cr'  => [parent::V_LIST,  [1, 2, 3, 6, 9, 11, 13], true ], // criteria ids
        'crs' => [parent::V_RANGE, [1, 6],                  true ], // criteria operators
        'crv' => [parent::V_REGEX, parent::PATTERN_INT,     true ], // criteria values - all criteria are numeric here
        'na'  => [parent::V_REGEX, parent::PATTERN_NAME,    false], // name - only printable chars, no delimiter
        'ma'  => [parent::V_EQUAL, 1,                       false]  // match any / all filter
    );

    private function _getCnd(string $op, int $val, string $tbl) : ?array
    {
        switch ($op)
        {
            case '>':
            case '>=':
            case '=':
                $ids = DB::Aowow()->selectCol('SELECT `iconId` AS ARRAY_KEY, COUNT(*) AS "n" FROM ?# GROUP BY `iconId` HAVING n '.$op.' '.$val, $tbl);
                return $ids ? ['id', array_keys($ids)] : [1];
            case '<=':
                if ($val)
                    $op = '>';
                break;
            case '<':
                if ($val)
                    $op = '>=';
                break;
            case '!=':
                if ($val)
                    $op = '=';
                break;
            default:
                return null;
        }

        $ids = DB::Aowow()->selectCol('SELECT `iconId` AS ARRAY_KEY, COUNT(*) AS "n" FROM ?# GROUP BY `iconId` HAVING n '.$op.' '.$val, $tbl);
        return $ids ? ['id', array_keys($ids), '!'] : [1];
    }

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = &$this->fiData['v'];

        //string
        if (isset($_v['na']))
            if ($_ = $this->modularizeString(['name']))
                $parts[] = $_;

        return $parts;
    }

    protected function cbUseAny(int $cr, int $crs, string $crv) : ?array
    {
        if (Util::checkNumeric($crv, NUM_CAST_INT) && $this->int2Op($crs))
            return $this->_getCnd($crs, $crv, $this->criterion2field[$cr]);

        return null;
    }

    protected function cbUseAll(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        if (!$this->totalUses)
        {
            foreach ($this->criterion2field as $tbl)
            {
                if (!$tbl)
                    continue;

                $res = DB::Aowow()->selectCol('SELECT `iconId` AS ARRAY_KEY, COUNT(*) AS "n" FROM ?# GROUP BY `iconId`', $tbl);
                Util::arraySumByKey($this->totalUses, $res);
            }
        }

        if ($crs == '=')
            $crs = '==';

        $op = $crs;
        if ($crs == '<=' && $crv)
            $op = '>';
        else if ($crs == '<' && $crv)
            $op = '>=';
        else if ($crs == '!=' && $crv)
            $op = '==';
        $ids = array_filter($this->totalUses, fn($x) => eval('return '.$x.' '.$op.' '.$crv.';'));

        if ($crs != $op)
            return $ids ? ['id', array_keys($ids), '!'] : [1];
        else
            return $ids ? ['id', array_keys($ids)] : ['id', array_keys($this->totalUses), '!'];
    }
}

?>
