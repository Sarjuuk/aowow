<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GameObjectList extends BaseType
{
    use listviewHelper, spawnHelper;

    public static   $type      = TYPE_OBJECT;
    public static $brickFile   = 'object';

    protected       $queryBase = 'SELECT o.*, o.id AS ARRAY_KEY FROM ?_objects o';
    protected       $queryOpts = array(
                        'o'   => [['ft', 'qr']],
                        'ft'  => ['j' => ['?_factiontemplate ft ON ft.id = o.faction', true], 's' => ', ft.factionId, ft.A, ft.H'],
                        'qr'  => ['j' => ['gameobject_questrelation qr ON qr.id = o.id', true], 's' => ', qr.quest', 'g' => 'o.id'],     // started by GO
                        'ir'  => ['j' => ['gameobject_involvedrelation ir ON ir.id = o.id', true]]                                       // ends at GO
                    );

    public function __construct($conditions = [], $applyFilter = false)
    {
        parent::__construct($conditions, $applyFilter);

        if ($this->error)
            return;

        // post processing
        foreach ($this->iterate() as $_id => &$curTpl)
        {
            // unpack miscInfo:
            $curTpl['lootStack']    = [];
            $curTpl['spells']       = [];

            if (in_array($curTpl['type'], [OBJECT_GOOBER, OBJECT_RITUAL, OBJECT_SPELLCASTER, OBJECT_FLAGSTAND, OBJECT_FLAGDROP, OBJECT_AURA_GENERATOR, OBJECT_TRAP]))
                $curTpl['spells'] = array_combine(['onUse', 'onSuccess', 'aura', 'triggered'], [$curTpl['onUseSpell'], $curTpl['onSuccessSpell'], $curTpl['auraSpell'], $curTpl['triggeredSpell']]);

            if (!$curTpl['miscInfo'])
                continue;

            switch ($curTpl['type'])
            {
                case OBJECT_CHEST:
                case OBJECT_FISHINGHOLE:
                    $curTpl['lootStack'] = explode(' ', $curTpl['miscInfo']);
                    break;
                case OBJECT_CAPTURE_POINT:
                    $curTpl['capture'] = explode(' ', $curTpl['miscInfo']);
                    break;
                case OBJECT_MEETINGSTONE:
                    $curTpl['mStone'] = explode(' ', $curTpl['miscInfo']);
                    break;
            }
        }
    }

    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('
            SELECT
                name,
                name_loc2,
                name_loc3,
                name_loc6,
                name_loc8
            FROM
                ?_objects
            WHERE
                id = ?d',
            $id
        );
        return Util::localizedString($n, 'name');
    }

    public function getListviewData()
    {
        $data = [];
        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'   => $this->id,
                'name' => $this->getField('name', true),
                'type' => $this->curTpl['typeCat']
            );

            if (!empty($this->curTpl['reqSkill']))
                $data[$this->id]['skill'] = $this->curTpl['reqSkill'];

            if ($this->curTpl['quest'])
                $data[$this->id]['hasQuests'] = 1;

        }

        return $data;
    }

    public function renderTooltip($interactive = false)
    {
        if (!$this->curTpl)
            return array();

        if (isset($this->tooltips[$this->id]))
            return $this->tooltips[$this->id];

        $x  = '<table>';
        $x .= '<tr><td><b class="q">'.$this->getField('name', true).'</b></td></tr>';
        if ($_ = @Lang::$gameObject['type'][$this->curTpl['typeCat']])
            $x .= '<tr><td>'.$_.'</td></tr>';

        if (isset($this->curTpl['lockId']))
            if ($locks = Lang::getLocks($this->curTpl['lockId']))
                foreach ($locks as $l)
                    $x .= '<tr><td>'.$l.'</td></tr>';

        $x .= '</table>';

        $this->tooltips[$this->id] = $x;

        return $this->tooltips[$this->id];
    }

    public function addGlobalsToJScript(&$template, $addMask = 0)
    {
        foreach ($this->iterate() as $id => $__)
            $template->extendGlobalData(self::$type, [$id => ['name' => $this->getField('name', true)]]);
    }
}


class GameObjectListFilter extends Filter
{
    protected $genericFilter = array(
        15 => [FILTER_CR_NUMERIC,   'entry',    null],      // id
         7 => [FILTER_CR_NUMERIC,   'reqSkill', null],      // requiredskilllevel
    );

/*
        { id: 1,   name: 'foundin',             type: 'zone' },
        { id: 16,  name: 'relatedevent',        type: 'event-any+none' },
*/

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
            case  4:
                if (!$this->int2Bool($cr[1]))
                    break;

                return $cr[1] ? ['OR', ['flags', 0x2, '&'], ['type', 3]] : ['AND', [['flags', 0x2, '&'], 0], ['type', 3, '!']];
            case  5:                                        // averagemoneycontained [op] [int]         GOs don't contain money .. eval to 0 == true
                if (!$this->isSaneNumeric($cr[2], false) || !$this->int2Op($cr[1]))
                    break;

                return eval('return ('.$cr[2].' '.$cr[1].' 0)') ? [1] : [0];
            case 2:                                         // startsquest [side]
                switch ($cr[1])
                {
                    case 1:                                 // any
                        return ['qr.id', null, '!'];
                    case 2:                                 // alliance only
                        return ['AND', ['qr.id', null, '!'], ['ft.A', -1, '!'], ['ft.H', -1]];
                    case 3:                                 // horde only
                        return ['AND', ['qr.id', null, '!'], ['ft.A', -1], ['ft.H', -1, '!']];
                    case 4:                                 // both
                        return ['AND', ['qr.id', null, '!'], ['OR', ['faction', 0], ['AND', ['ft.A', -1, '!'], ['ft.H', -1, '!']]]];
                    case 5:                                 // none
                        return ['qr.id', null];
                }
                break;
            case 3:                                         // endsquest [side]
                switch ($cr[1])
                {
                    case 1:                                 // any
                        return ['qi.id', null, '!'];
                    case 2:                                 // alliance only
                        return ['AND', ['qi.id', null, '!'], ['ft.A', -1, '!'], ['ft.H', -1]];
                    case 3:                                 // horde only
                        return ['AND', ['qi.id', null, '!'], ['ft.A', -1], ['ft.H', -1, '!']];
                    case 4:                                 // both
                        return ['AND', ['qi.id', null, '!'], ['OR', ['faction', 0], ['AND', ['ft.A', -1, '!'], ['ft.H', -1, '!']]]];
                    case 5:                                 // none
                        return ['qi.id', null];
                }
                break;
            case 13:                                        // hascomments [yn]
                break;
            case 11:                                        // hasscreenshots [yn]
                break;
            case 18:                                        // hasvideos [yn]
                break;
        }

        unset($cr);
        $this->error = 1;
        return [1];
    }

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = $this->fiData['v'];

        // name
        if (isset($_v['na']))
            $parts[] = ['name_loc'.User::$localeId, $_v['na']];

        return $parts;
    }
}

?>
