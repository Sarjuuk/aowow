<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GameObjectList extends BaseType
{
    use listviewHelper, spawnHelper;

    public static   $type      = Type::OBJECT;
    public static   $brickFile = 'object';
    public static   $dataTable = '?_objects';

    protected       $queryBase = 'SELECT o.*, o.id AS ARRAY_KEY FROM ?_objects o';
    protected       $queryOpts = array(
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
            if (!$curTpl['name_loc0'])
                $curTpl['name_loc0'] = 'Unnamed Object #' . $_id;

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
        $n = DB::Aowow()->SelectRow('SELECT name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8 FROM ?_objects WHERE id = ?d', $id);
        return Util::localizedString($n, 'name');
    }

    public function getListviewData()
    {
        $data = [];
        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'       => $this->id,
                'name'     => Lang::unescapeUISequences($this->getField('name', true), Lang::FMT_RAW),
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
        $x .= '<tr><td><b class="q">'.Lang::unescapeUISequences($this->getField('name', true), Lang::FMT_HTML).'</b></td></tr>';
        if ($this->curTpl['typeCat'])
            if ($_ = Lang::gameObject('type', $this->curTpl['typeCat']))
                $x .= '<tr><td>'.$_.'</td></tr>';

        if (isset($this->curTpl['lockId']))
            if ($locks = Lang::getLocks($this->curTpl['lockId']))
                foreach ($locks as $l)
                    $x .= '<tr><td>'.sprintf(Lang::game('requires'), $l).'</td></tr>';

        $x .= '</table>';

        return $x;
    }

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[Type::OBJECT][$this->id] = ['name' => Lang::unescapeUISequences($this->getField('name', true), Lang::FMT_RAW)];

        return $data;
    }

    public function getSourceData(int $id = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            if ($id && $id != $this->id)
                continue;

            $data[$this->id] = array(
                'n'  => $this->getField('name', true),
                't'  => Type::OBJECT,
                'ti' => $this->id
            );
        }

        return $data;
    }
}


class GameObjectListFilter extends Filter
{
    public    $extraOpts     = [];
    protected $enums         = array(
         1 => parent::ENUM_ZONE,
        16 => parent::ENUM_EVENT,
        50 => [1, 2, 3, 4, 663, 883]
    );

    protected $genericFilter = array(
         1 => [FILTER_CR_ENUM,     's.areaId',        false,                true], // foundin
         2 => [FILTER_CR_CALLBACK, 'cbQuestRelation', 'startsQuests',       0x1 ], // startsquest [side]
         3 => [FILTER_CR_CALLBACK, 'cbQuestRelation', 'endsQuests',         0x2 ], // endsquest [side]
         4 => [FILTER_CR_CALLBACK, 'cbOpenable',      null,                 null], // openable [yn]
         5 => [FILTER_CR_NYI_PH,   null,              0                         ], // averagemoneycontained [op] [int] - GOs don't contain money, match against 0
         7 => [FILTER_CR_NUMERIC,  'reqSkill',        NUM_CAST_INT              ], // requiredskilllevel
        11 => [FILTER_CR_FLAG,     'cuFlags',         CUSTOM_HAS_SCREENSHOT     ], // hasscreenshots
        13 => [FILTER_CR_FLAG,     'cuFlags',         CUSTOM_HAS_COMMENT        ], // hascomments
        15 => [FILTER_CR_NUMERIC,  'id',              NUM_CAST_INT              ], // id
        16 => [FILTER_CR_CALLBACK, 'cbRelEvent',      null,                 null], // relatedevent (ignore removed by event)
        18 => [FILTER_CR_FLAG,     'cuFlags',         CUSTOM_HAS_VIDEO          ], // hasvideos
        50 => [FILTER_CR_ENUM,     'spellFocusId',    true,                 true], // spellfocus
    );

    protected $inputFields = array(
        'cr'  => [FILTER_V_LIST,  [[1, 5], 7, 11, 13, 15, 16, 18, 50],            true ], // criteria ids
        'crs' => [FILTER_V_LIST,  [FILTER_ENUM_NONE, FILTER_ENUM_ANY, [0, 5000]], true ], // criteria operators
        'crv' => [FILTER_V_REGEX, parent::PATTERN_INT,                            true ], // criteria values - only numeric input values expected
        'na'  => [FILTER_V_REGEX, parent::PATTERN_NAME,                           false], // name - only printable chars, no delimiter
        'ma'  => [FILTER_V_EQUAL, 1,                                              false]  // match any / all filter
    );

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

    protected function cbOpenable($cr)
    {
        if ($this->int2Bool($cr[1]))
            return $cr[1] ? ['OR', ['flags', 0x2, '&'], ['type', 3]] : ['AND', [['flags', 0x2, '&'], 0], ['type', 3, '!']];

        return false;
    }

    protected function cbQuestRelation($cr, $field, $value)
    {
        switch ($cr[1])
        {
            case 1:                                 // any
                return ['AND', ['qse.method', $value, '&'], ['qse.questId', null, '!']];
            case 2:                                 // alliance only
                return ['AND', ['qse.method', $value, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', RACE_MASK_HORDE, '&'], 0], ['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&']];
            case 3:                                 // horde only
                return ['AND', ['qse.method', $value, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&'], 0], ['qt.reqRaceMask', RACE_MASK_HORDE, '&']];
            case 4:                                 // both
                return ['AND', ['qse.method', $value, '&'], ['qse.questId', null, '!'], ['OR', ['AND', ['qt.reqRaceMask', RACE_MASK_ALLIANCE, '&'], ['qt.reqRaceMask', RACE_MASK_HORDE, '&']], ['qt.reqRaceMask', 0]]];
            case 5:                                 // none         todo (low): broken, if entry starts and ends quests...
                $this->extraOpts['o']['h'][] = $field.' = 0';
                return [1];
        }

        return false;
    }

    protected function cbRelEvent($cr)
    {
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
        else if (in_array($cr[1], $this->enums[$cr[0]]))
        {
            if ($eventIds = DB::Aowow()->selectCol('SELECT id FROM ?_events WHERE holidayId = ?d', $cr[1]))
                if ($goGuids  = DB::World()->selectCol('SELECT DISTINCT guid FROM game_event_gameobject WHERE eventEntry IN (?a)', $eventIds))
                    return ['s.guid', $goGuids];

            return [0];
        }

        return false;
    }
}

?>
