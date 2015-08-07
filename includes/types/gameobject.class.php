<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GameObjectList extends BaseType
{
    use listviewHelper, spawnHelper;

    public static $type      = TYPE_OBJECT;
    public static $brickFile = 'object';

    protected     $queryBase = 'SELECT o.*, o.id AS ARRAY_KEY FROM ?_objects o';
    protected     $queryOpts = array(
                        'o'   => [['ft', 'qse']],
                        'ft'  => ['j' => ['?_factiontemplate ft ON ft.id = o.faction', true], 's' => ', ft.factionId, ft.A, ft.H'],
                        'qse' => ['j' => ['?_quests_startend qse ON qse.type = 2 AND qse.typeId = o.id', true], 's' => ', IF(min(qse.method) = 1 OR max(qse.method) = 3, 1, 0) AS startsQuests, IF(min(qse.method) = 2 OR max(qse.method) = 3, 1, 0) AS endsQuests', 'g' => 'o.id'],
                        'qt'  => ['j' => '?_quests qt ON qse.questId = qt.id'],
                        's'   => ['j' => '?_spawns s ON s.type = 2 AND s.typeId = o.id']
                    );

    public function __construct($conditions = [], $miscData = null)
    {
        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        // post processing
        foreach ($this->iterate() as $_id => &$curTpl)
        {
            // unpack miscInfo
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
        $n = DB::Aowow()->SelectRow('SELECT name_loc0, name_loc2, name_loc3, name_loc6, name_loc8 FROM ?_objects WHERE id = ?d', $id);
        return Util::localizedString($n, 'name');
    }

    public function getListviewData()
    {
        $data = [];
        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'       => $this->id,
                'name'     => $this->getField('name', true),
                'type'     => $this->curTpl['typeCat'],
                'location' => $this->getSpawns(SPAWNINFO_ZONES)
            );

            if (!empty($this->curTpl['reqSkill']))
                $data[$this->id]['skill'] = $this->curTpl['reqSkill'];

            if ($this->curTpl['startsQuests'])
                $data[$this->id]['hasQuests'] = 1;

        }

        return $data;
    }

    public function renderTooltip($interactive = false)
    {
        if (!$this->curTpl)
            return array();

        $x  = '<table>';
        $x .= '<tr><td><b class="q">'.$this->getField('name', true).'</b></td></tr>';
        if ($this->curTpl['typeCat'])
            if ($_ = Lang::gameObject('type', $this->curTpl['typeCat']))
                $x .= '<tr><td>'.$_.'</td></tr>';

        if (isset($this->curTpl['lockId']))
            if ($locks = Lang::getLocks($this->curTpl['lockId']))
                foreach ($locks as $l)
                    $x .= '<tr><td>'.$l.'</td></tr>';

        $x .= '</table>';

        return $x;
    }

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[TYPE_OBJECT][$this->id] = ['name' => $this->getField('name', true)];

        return $data;
    }

    public function getSourceData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'n'  => $this->getField('name', true),
                't'  => TYPE_OBJECT,
                'ti' => $this->id
             // 'bd' => bossdrop
             // 'dd' => dungeondifficulty
            );
        }

        return $data;
    }
}


class GameObjectListFilter extends Filter
{
    public    $extraOpts     = [];

    protected $genericFilter = array(
         1 => [FILTER_CR_ENUM,    's.areaId', null                 ], // foundin
         7 => [FILTER_CR_NUMERIC, 'reqSkill', null                 ], // requiredskilllevel
        11 => [FILTER_CR_FLAG,    'cuFlags',  CUSTOM_HAS_SCREENSHOT], // hasscreenshots
        13 => [FILTER_CR_FLAG,    'cuFlags',  CUSTOM_HAS_COMMENT   ], // hascomments
        15 => [FILTER_CR_NUMERIC, 'id',       null                 ], // id
        18 => [FILTER_CR_FLAG,    'cuFlags',  CUSTOM_HAS_VIDEO     ], // hasvideos
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
                        return ['AND', ['qse.method', 0x1, '&'], ['qse.questId', null, '!']];
                    case 2:                                 // alliance only
                        return ['AND', ['qse.method', 0x1, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', RACE_MASK_HORDE, '&'], 0], ['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&']];
                    case 3:                                 // horde only
                        return ['AND', ['qse.method', 0x1, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&'], 0], ['qt.reqRaceMask', RACE_MASK_HORDE, '&']];
                    case 4:                                 // both
                        return ['AND', ['qse.method', 0x1, '&'], ['qse.questId', null, '!'], ['OR', ['AND', ['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&'], ['qt.reqRaceMask', RACE_MASK_HORDE, '&']], ['qt.reqRaceMask', 0]]];
                    case 5:                                 // none
                        $this->extraOpts['o']['h'][] = 'startsQuests = 0';
                        return [1];
                }
                break;
            case 3:                                         // endsquest [side]
                switch ($cr[1])
                {
                    case 1:                                 // any
                        return ['AND', ['qse.method', 0x2, '&'], ['qse.questId', null, '!']];
                    case 2:                                 // alliance only
                        return ['AND', ['qse.method', 0x2, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', RACE_MASK_HORDE, '&'], 0], ['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&']];
                    case 3:                                 // horde only
                        return ['AND', ['qse.method', 0x2, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&'], 0], ['qt.reqRaceMask', RACE_MASK_HORDE, '&']];
                    case 4:                                 // both
                        return ['AND', ['qse.method', 0x2, '&'], ['qse.questId', null, '!'], ['OR', ['AND', ['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&'], ['qt.reqRaceMask', RACE_MASK_HORDE, '&']], ['qt.reqRaceMask', 0]]];
                    case 5:                                 // none         todo: broken, if entry starts and ends quests...
                        $this->extraOpts['o']['h'][] = 'endsQuests = 0';
                        return [1];
                }
                break;
            case 16;                                        // relatedevent (ignore removed by event)
                if (!$this->isSaneNumeric($cr[1]))
                    break;

                if ($cr[1] == FILTER_ENUM_ANY)
                {
                    $eventIds = DB::Aowow()->selectCol('SELECT id FROM ?_events WHERE holidayId <> 0');
                    $goGuids  = DB::World()->selectCol('SELECT DISTINCT guid FROM game_event_gameobject WHERE eventEntry IN (?a)', $eventIds);
                    return ['s.guid', $goGuids];
                }
                else if ($cr[1] == FILTER_ENUM_NONE)
                {
                    $eventIds = DB::Aowow()->selectCol('SELECT id FROM ?_events WHERE holidayId <> 0');
                    $goGuids  = DB::World()->selectCol('SELECT DISTINCT guid FROM game_event_gameobject WHERE eventEntry IN (?a)', $eventIds);
                    return ['s.guid', $goGuids, '!'];
                }
                else if ($cr[1])
                {
                    $eventIds = DB::Aowow()->selectCol('SELECT id FROM ?_events WHERE holidayId = ?d', $cr[1]);
                    $goGuids  = DB::World()->selectCol('SELECT DISTINCT guid FROM game_event_gameobject WHERE eventEntry IN (?a)', $eventIds);
                    return ['s.guid', $goGuids];
                }

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
            if ($_ = $this->modularizeString(['name_loc'.User::$localeId]))
                $parts[] = $_;

        return $parts;
    }
}

?>
