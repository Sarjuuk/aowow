<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemsetList extends DBTypeList
{
    use ListviewHelper;

    public static int    $type       = Type::ITEMSET;
    public static string $brickFile  = 'itemset';
    public static string $dataTable  = '?_itemset';
    public        array  $pieceToSet = [];                  // used to build g_items and search

    private array $classes = [];                            // used to build g_classes

    protected string $queryBase  = 'SELECT `set`.*, `set`.`id` AS ARRAY_KEY FROM ?_itemset `set`';
    protected array  $queryOpts  = array(
                        'set' => ['o' => 'maxlevel DESC'],
                        'e'   => ['j' => ['?_events e ON `e`.`id` = `set`.`eventId`', true], 's' => ', e.`holidayId`'],
                        'src' => ['j' => ['?_source src ON `src`.`typeId` = `set`.`id` AND `src`.`type` = 4', true], 's' => ', `src1`, `src2`, `src3`, `src4`, `src5`, `src6`, `src7`, `src8`, `src9`, `src10`, `src11`, `src12`, `src13`, `src14`, `src15`, `src16`, `src17`, `src18`, `src19`, `src20`, `src21`, `src22`, `src23`, `src24`']
                    );

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        // post processing
        foreach ($this->iterate() as &$_curTpl)
        {
            $_curTpl['classes'] = ChrClass::fromMask($_curTpl['classMask']);
            $this->classes = array_merge($this->classes, $_curTpl['classes']);

            $_curTpl['pieces']  = [];
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

    public function getListviewData() : array
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

    public function getJSGlobals(int $addMask = GLOBALINFO_ANY) : array
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

    public function renderTooltip() : ?string
    {
        if (!$this->curTpl)
            return null;

        $x  = '<table><tr><td>';
        $x .= '<span class="q'.$this->getField('quality').'">'.$this->getField('name', true).'</span><br />';

        $nCl = 0;
        if ($_ = $this->getField('classMask'))
        {
            $jsg = [];
            $cl  = Lang::getClassString($_, $jsg);
            $t   = count($jsg) == 1 ? Lang::game('class') : Lang::game('classes');
            $x  .= Util::ucFirst($t).Lang::main('colon').$cl.'<br />';
        }

        if ($_ = $this->getField('contentGroup'))
            $x .= Lang::itemset('notes', $_).($this->getField('heroic') ? ' <i class="q2">('.Lang::item('heroic').')</i>' : '').'<br />';

        if (!$nCl || !$this->getField('type'))
            $x.= Lang::itemset('types', $this->getField('type')).'<br />';

        if ($bonuses = $this->getBonuses())
        {
            $x .= '<span>';

            foreach ($bonuses as [$nItems, , $text])
                $x .= '<br /><span class="q13">'.Lang::itemset('_pieces', [$nItems]).'</span>'.$text;

            $x .= '</span>';
        }

        $x .= '</td></tr></table>';

        return $x;
    }

    public function getBonuses() : array
    {
        $spells = [];
        for ($i = 1; $i < 9; $i++)
        {
            $spl = $this->getField('spell'.$i);
            $qty = $this->getField('bonus'.$i);

            // cant use spell as index, would change order
            if ($spl && $qty)
                $spells[] = [$qty, $spl];
        }

        // sort by required pieces ASC
        usort($spells, fn(array $a, array $b) => $a[0] <=> $b[0]);

        $setSpells = new SpellList(array(['s.id', array_column($spells, 1)]));
        foreach ($spells as &$s)
        {
            if ($setSpells->getEntry($s[1]))
                $s[2] = $setSpells->parseText('description', $this->getField('reqLevel') ?: MAX_LEVEL)[0];
            else
                $s[2] = Lang::spell('unkAura', [$s[1]]);
        }

        return $spells;
    }
}


// missing filter: "Available to Players"
class ItemsetListFilter extends Filter
{
    protected string $type  = 'itemsets';
    protected static array $enums = array(
         6 => parent::ENUM_EVENT
    );

    protected static array $genericFilter = array(
         2 => [parent::CR_NUMERIC,  'id',          NUM_CAST_INT,         true], // id
         3 => [parent::CR_NUMERIC,  'npieces',     NUM_CAST_INT              ], // pieces
         4 => [parent::CR_STRING,   'bonusText',   STR_LOCALIZED             ], // bonustext
         5 => [parent::CR_BOOLEAN,  'heroic'                                 ], // heroic
         6 => [parent::CR_ENUM,     'e.holidayId', true,                 true], // relatedevent
         8 => [parent::CR_FLAG,     'cuFlags',     CUSTOM_HAS_COMMENT        ], // hascomments
         9 => [parent::CR_FLAG,     'cuFlags',     CUSTOM_HAS_SCREENSHOT     ], // hasscreenshots
        10 => [parent::CR_FLAG,     'cuFlags',     CUSTOM_HAS_VIDEO          ], // hasvideos
        12 => [parent::CR_CALLBACK, 'cbAvaliable',                           ]  // available to players [yn]
    );

    protected static array $inputFields = array(
        'cr'    => [parent::V_RANGE, [2, 12],                                         true ], // criteria ids
        'crs'   => [parent::V_LIST,  [parent::ENUM_NONE, parent::ENUM_ANY, [0, 424]], true ], // criteria operators
        'crv'   => [parent::V_REGEX, parent::PATTERN_CRV,                             true ], // criteria values - only printable chars, no delimiters
        'na'    => [parent::V_REGEX, parent::PATTERN_NAME,                            false], // name / description - only printable chars, no delimiter
        'ma'    => [parent::V_EQUAL, 1,                                               false], // match any / all filter
        'qu'    => [parent::V_RANGE, [0, 7],                                          true ], // quality
        'ty'    => [parent::V_RANGE, [1, 12],                                         true ], // set type
        'minle' => [parent::V_RANGE, [0, 999],                                        false], // min item level
        'maxle' => [parent::V_RANGE, [0, 999],                                        false], // max itemlevel
        'minrl' => [parent::V_RANGE, [0, MAX_LEVEL],                                  false], // min required level
        'maxrl' => [parent::V_RANGE, [0, MAX_LEVEL],                                  false], // max required level
        'cl'    => [parent::V_LIST,  [[1, 9], 11],                                    false], // class
        'ta'    => [parent::V_RANGE, [1, 30],                                         false]  // tag / content group
    );

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = &$this->values;

        // name [str]
        if ($_v['na'])
            if ($_ = $this->tokenizeString(['name_loc'.Lang::getLocale()->value]))
                $parts[] = $_;

        // quality [enum]
        if ($_v['qu'])
            $parts[] = ['quality', $_v['qu']];

        // type [enum]
        if ($_v['ty'])
            $parts[] = ['type', $_v['ty']];

        // itemLevel min [int]
        if ($_v['minle'])
            $parts[] = ['minLevel', $_v['minle'], '>='];

        // itemLevel max [int]
        if ($_v['maxle'])
            $parts[] = ['maxLevel', $_v['maxle'], '<='];

        // reqLevel min [int]
        if ($_v['minrl'])
            $parts[] = ['reqLevel', $_v['minrl'], '>='];

        // reqLevel max [int]
        if ($_v['maxrl'])
            $parts[] = ['reqLevel', $_v['maxrl'], '<='];

        // class [enum]
        if ($_v['cl'])
            $parts[] = ['classMask', $this->list2Mask([$_v['cl']]), '&'];

        // tag [enum]
        if ($_v['ta'])
            $parts[] = ['contentGroup', intVal($_v['ta'])];

        return $parts;
    }

    protected function cbAvaliable(int $cr, int $crs, string $crv) : ?array
    {
        return match ($crs)
        {
            1 => ['src.typeId', null, '!'],                 // Yes
            2 => ['src.typeId', null],                      // No
            default => null
        };
    }
}

?>
