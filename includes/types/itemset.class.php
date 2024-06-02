<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemsetList extends BaseType
{
    use ListviewHelper;

    public static   $type       = Type::ITEMSET;
    public static   $brickFile  = 'itemset';
    public static   $dataTable  = '?_itemset';

    public          $pieceToSet = [];                       // used to build g_items and search
    private         $classes    = [];                       // used to build g_classes

    protected       $queryBase  = 'SELECT `set`.*, `set`.id AS ARRAY_KEY FROM ?_itemset `set`';
    protected       $queryOpts  = array(
                        'set' => ['o' => 'maxlevel DESC'],
                        'e'   => ['j' => ['?_events e ON `e`.`id` = `set`.`eventId`', true], 's' => ', e.holidayId'],
                        'src' => ['j' => ['?_source src ON `src`.`typeId` = `set`.`id` AND `src`.`type` = 4', true], 's' => ', src1, src2, src3, src4, src5, src6, src7, src8, src9, src10, src11, src12, src13, src14, src15, src16, src17, src18, src19, src20, src21, src22, src23, src24']
                    );

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

        // post processing
        foreach ($this->iterate() as &$_curTpl)
        {
            $_curTpl['classes'] = [];
            $_curTpl['pieces']  = [];
            for ($i = 1; $i < 12; $i++)
            {
                if ($_curTpl['classMask'] & (1 << ($i - 1)))
                {
                    $this->classes[] = $i;
                    $_curTpl['classes'][] = $i;
                }
            }

            for ($i = 1; $i < 10; $i++)
            {
                if ($piece = $_curTpl['item'.$i])
                {
                    $_curTpl['pieces'][] = $piece;
                    $this->pieceToSet[$piece] = $this->id;
                }
            }
        }
        $this->classes = array_unique($this->classes);
    }

    public function getListviewData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'       => $this->id,
                'idbak'    => $this->curTpl['refSetId'],
                'name'     => (7 - $this->curTpl['quality']).$this->getField('name', true),
                'minlevel' => $this->curTpl['minLevel'],
                'maxlevel' => $this->curTpl['maxLevel'],
                'note'     => $this->curTpl['contentGroup'],
                'type'     => $this->curTpl['type'],
                'reqclass' => $this->curTpl['classMask'],
                'classes'  => $this->curTpl['classes'],
                'pieces'   => $this->curTpl['pieces'],
                'heroic'   => $this->curTpl['heroic']
            );
        }

        return $data;
    }

    public function getJSGlobals($addMask = GLOBALINFO_ANY)
    {
        $data = [];

        if ($this->classes && ($addMask & GLOBALINFO_RELATED))
            $data[Type::CHR_CLASS] = array_combine($this->classes, $this->classes);

        if ($this->pieceToSet && ($addMask & GLOBALINFO_SELF))
            $data[Type::ITEM] = array_combine(array_keys($this->pieceToSet), array_keys($this->pieceToSet));

        if ($addMask & GLOBALINFO_SELF)
            foreach ($this->iterate() as $id => $__)
                $data[Type::ITEMSET][$id] = ['name' => $this->getField('name', true)];

        return $data;
    }

    public function renderTooltip()
    {
        if (!$this->curTpl)
            return array();

        $x  = '<table><tr><td>';
        $x .= '<span class="q'.$this->getField('quality').'">'.$this->getField('name', true).'</span><br />';

        $nCl = 0;
        if ($_ = $this->getField('classMask'))
        {
            $jsg = [];
            $cl  = Lang::getClassString($_, $jsg);
            $nCl = count($jsg);
            $x .= Util::ucFirst($nCl > 1 ? Lang::game('classes') : Lang::game('class')).Lang::main('colon').$cl.'<br />';
        }

        if ($_ = $this->getField('contentGroup'))
            $x .= Lang::itemset('notes', $_).($this->getField('heroic') ? ' <i class="q2">('.Lang::item('heroic').')</i>' : '').'<br />';

        if (!$nCl || !$this->getField('type'))
            $x.= Lang::itemset('types', $this->getField('type')).'<br />';

        if ($bonuses = $this->getBonuses())
        {
            $x .= '<span>';

            foreach ($bonuses as $b)
                $x .= '<br /><span class="q13">'.$b['bonus'].' '.Lang::itemset('_pieces').Lang::main('colon').'</span>'.$b['desc'];

            $x .= '</span>';
        }

        $x .= '</td></tr></table>';

        return $x;
   }

   public function getBonuses()
   {
        $spells = [];
        for ($i = 1; $i < 9; $i++)
        {
            $spl = $this->getField('spell'.$i);
            $qty = $this->getField('bonus'.$i);

            // cant use spell as index, would change order
            if ($spl && $qty)
                $spells[] = ['id' => $spl, 'bonus' => $qty];
        }

        // sort by required pieces ASC
        usort($spells, function($a, $b) {
            if ($a['bonus'] == $b['bonus'])
                return 0;

            return ($a['bonus'] > $b['bonus']) ? 1 : -1;
        });

        $setSpells = new SpellList(array(['s.id', array_column($spells, 'id')]));
        foreach ($setSpells->iterate() as $spellId => $__)
        {
            foreach ($spells as &$s)
            {
                if ($spellId != $s['id'])
                    continue;

                $s['desc'] = $setSpells->parseText('description', $this->getField('reqLevel') ?: MAX_LEVEL)[0];
            }
        }

        return $spells;
   }
}


// missing filter: "Available to Players"
class ItemsetListFilter extends Filter
{
    protected $enums         = array(
         6 => parent::ENUM_EVENT
    );

    protected $genericFilter = array(
         2 => [FILTER_CR_NUMERIC,  'id',          NUM_CAST_INT,         true], // id
         3 => [FILTER_CR_NUMERIC,  'npieces',     NUM_CAST_INT              ], // pieces
         4 => [FILTER_CR_STRING,   'bonusText',   STR_LOCALIZED             ], // bonustext
         5 => [FILTER_CR_BOOLEAN,  'heroic'                                 ], // heroic
         6 => [FILTER_CR_ENUM,     'e.holidayId', true,                 true], // relatedevent
         8 => [FILTER_CR_FLAG,     'cuFlags',     CUSTOM_HAS_COMMENT        ], // hascomments
         9 => [FILTER_CR_FLAG,     'cuFlags',     CUSTOM_HAS_SCREENSHOT     ], // hasscreenshots
        10 => [FILTER_CR_FLAG,     'cuFlags',     CUSTOM_HAS_VIDEO          ], // hasvideos
        12 => [FILTER_CR_CALLBACK, 'cbAvaliable',                           ]  // available to players [yn]
    );

    protected $inputFields = array(
        'cr'    => [FILTER_V_RANGE, [2, 12],                                       true ], // criteria ids
        'crs'   => [FILTER_V_LIST,  [FILTER_ENUM_NONE, FILTER_ENUM_ANY, [0, 424]], true ], // criteria operators
        'crv'   => [FILTER_V_REGEX, parent::PATTERN_CRV,                           true ], // criteria values - only printable chars, no delimiters
        'na'    => [FILTER_V_REGEX, parent::PATTERN_NAME,                          false], // name / description - only printable chars, no delimiter
        'ma'    => [FILTER_V_EQUAL, 1,                                             false], // match any / all filter
        'qu'    => [FILTER_V_RANGE, [0, 7],                                        true ], // quality
        'ty'    => [FILTER_V_RANGE, [1, 12],                                       true ], // set type
        'minle' => [FILTER_V_RANGE, [1, 999],                                      false], // min item level
        'maxle' => [FILTER_V_RANGE, [1, 999],                                      false], // max itemlevel
        'minrl' => [FILTER_V_RANGE, [1, MAX_LEVEL],                                false], // min required level
        'maxrl' => [FILTER_V_RANGE, [1, MAX_LEVEL],                                false], // max required level
        'cl'    => [FILTER_V_LIST,  [[1, 9], 11],                                  false], // class
        'ta'    => [FILTER_V_RANGE, [1, 30],                                       false]  // tag / content group
    );

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = &$this->fiData['v'];

        // name [str]
        if (isset($_v['na']))
            if ($_ = $this->modularizeString(['name_loc'.User::$localeId]))
                $parts[] = $_;

        // quality [enum]
        if (isset($_v['qu']))
            $parts[] = ['quality', $_v['qu']];

        // type [enum]
        if (isset($_v['ty']))
            $parts[] = ['type', $_v['ty']];

        // itemLevel min [int]
        if (isset($_v['minle']))
            $parts[] = ['minLevel', $_v['minle'], '>='];

        // itemLevel max [int]
        if (isset($_v['maxle']))
            $parts[] = ['maxLevel', $_v['maxle'], '<='];

        // reqLevel min [int]
        if (isset($_v['minrl']))
            $parts[] = ['reqLevel', $_v['minrl'], '>='];

        // reqLevel max [int]
        if (isset($_v['maxrl']))
            $parts[] = ['reqLevel', $_v['maxrl'], '<='];

        // class [enum]
        if (isset($_v['cl']))
            $parts[] = ['classMask', $this->list2Mask([$_v['cl']]), '&'];

        // tag [enum]
        if (isset($_v['ta']))
            $parts[] = ['contentGroup', intVal($_v['ta'])];

        return $parts;
    }

    protected function cbAvaliable($cr)
    {
        switch ($cr[1])
        {
            case 1:                                         // Yes
                return ['src.typeId', null, '!'];
            case 2:                                         // No
                return ['src.typeId', null];
        }

        return false;
    }
}

?>
