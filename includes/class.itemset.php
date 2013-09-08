<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class ItemsetList extends BaseType
{
    use ListviewHelper;

    public static $type       = TYPE_ITEMSET;

    public        $pieceToSet = [];                             // used to build g_items and search
    private       $classes    = [];                             // used to build g_classes

    protected     $queryBase  = 'SELECT *, id AS ARRAY_KEY FROM ?_itemset `set`';
    protected     $queryOpts  = ['set' => ['o' => 'maxlevel DESC']];

    public function __construct($conditions = [], $applyFilter = false)
    {
        parent::__construct($conditions, $applyFilter);

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
                'name'     => $this->getField('name', true),
                'quality'  => 7 - $this->curTpl['quality'],
                'minlevel' => $this->curTpl['minLevel'],
                'maxlevel' => $this->curTpl['maxLevel'],
                'note'     => $this->curTpl['contentGroup'],
                'type'     => $this->curTpl['type'],
                'heroic'   => $this->curTpl['heroic'] == 1, // we want to be bool
                'reqclass' => $this->curTpl['classMask'],
                'classes'  => $this->curTpl['classes'],
                'pieces'   => $this->curTpl['pieces'],
                'heroic'   => $this->curTpl['heroic']
            );
        }

        return $data;
    }

    public function addGlobalsToJscript(&$template, $addMask = GLOBALINFO_ANY)
    {
        if ($this->classes && ($addMask & GLOBALINFO_RELATED))
            $template->extendGlobalIds(TYPE_CLASS, $this->classes);

        if ($this->pieceToSet && ($addMask & GLOBALINFO_SELF))
            $template->extendGlobalIds(TYPE_ITEM, array_keys($this->pieceToSet));
    }

    public function renderTooltip() { }
}


// missing filter: "Available to Players"
class ItemsetListFilter extends Filter
{
    // cr => [type, field, misc, extraCol]
    protected $genericFilter = array(                       // misc (bool): _NUMERIC => useFloat; _STRING => localized; _FLAG => match Value; _BOOLEAN => stringSet
         2 => [FILTER_CR_NUMERIC, 'id',        null, true], // id
         3 => [FILTER_CR_NUMERIC, 'npieces',             ], // pieces
         4 => [FILTER_CR_STRING,  'bonusText', true      ], // bonustext
         5 => [FILTER_CR_BOOLEAN, 'heroic',              ], // heroic
         6 => [FILTER_CR_ENUM,    'holidayId',           ], // relatedevent
    );

    protected function createSQLForCriterium(&$cr)
    {
        if (in_array($cr[0], array_keys($this->genericFilter)))
        {
            if ($genCR = $this->genericCriterion($cr))
                return $genCR;

            unset($cr);
            $this->error = true;
            return [1];
        }

        switch ($cr[0])
        {
            case 12:                                        // available to players [yn]                ugh .. scan loot, quest and vendor templates and write to ?_itemset
/* todo */      return [1];
            case 8:                                         // hascomments [yn]
/* todo */      return [1];
            case 9:                                         // hasscreenshots [yn]
/* todo */      return [1];
            case 10:                                        // hasvideos [yn]
/* todo */      return [1];
        }

        unset($cr);
        $this->error = 1;
        return [1];
    }

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = &$this->fiData['v'];

        // name [str]
        if (isset($_v['na']))
            $parts[] = ['name_loc'.User::$localeId, $_v['na']];

        // quality [enum]
        if (isset($_v['qu']))
            $parts[] = ['quality', (array)$_v['qu']];

        // type [enum]
        if (isset($_v['ty']))
            $parts[] = ['type', (array)$_v['ty']];

        // itemLevel min [int]
        if (isset($_v['minle']))
        {
            if (is_int($_v['minle']) && $_v['minle'] > 0)
                $parts[] = ['minLevel', $_v['minle'], '>='];
            else
                unset($_v['minle']);
        }

        // itemLevel max [int]
        if (isset($_v['maxle']))
        {
            if (is_int($_v['maxle']) && $_v['maxle'] > 0)
                $parts[] = ['maxLevel', $_v['maxle'], '<='];
            else
                unset($_v['maxle']);
        }

        // reqLevel min [int]
        if (isset($_v['minrl']))
        {
            if (is_int($_v['minrl']) && $_v['minrl'] > 0)
                $parts[] = ['reqLevel', $_v['minrl'], '>='];
            else
                unset($_v['minrl']);
        }

        // reqLevel max [int]
        if (isset($_v['maxrl']))
        {
            if (is_int($_v['maxrl']) && $_v['maxrl'] > 0)
                $parts[] = ['reqLevel', $_v['maxrl'], '<='];
            else
                unset($_v['maxrl']);
        }

        // class [enum]
        if (isset($_v['cl']))
        {
            if (in_array($_v['cl'], [1, 2, 3, 4, 5, 6, 7, 8, 9, 11]))
                $parts[] = ['classMask', $this->list2Mask($_v['cl']), '&'];
            else
                unset($_v['cl']);
        }

        // tag [enum]
        if (isset($_v['ta']))
        {
            if ($_v['ta'] > 0 && $_v['ta'] < 31)
                $parts[] = ['contentGroup', intVal($_v['ta'])];
            else
                unset($_v['ta']);
        }

        return $parts;
    }
}

?>
