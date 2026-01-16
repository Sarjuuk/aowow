<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GameObjectList extends DBTypeList
{
    use listviewHelper, spawnHelper;

    public static int    $type      = Type::OBJECT;
    public static string $brickFile = 'object';
    public static string $dataTable = '?_objects';

    protected string $queryBase = 'SELECT o.*, o.`id` AS ARRAY_KEY FROM ?_objects o';
    protected array  $queryOpts = array(
                        'o'   => [['ft', 'qse']],
                        'ft'  => ['j' => ['?_factiontemplate ft ON ft.`id` = o.`faction`', true], 's' => ', ft.`factionId`, IFNULL(ft.`A`, 0) AS "A", IFNULL(ft.`H`, 0) AS "H"'],
                        'qse' => ['j' => ['?_quests_startend qse ON qse.`type` = 2 AND qse.`typeId` = o.id', true], 's' => ', IF(MIN(qse.`method`) = 1 OR MAX(qse.`method`) = 3, 1, 0) AS "startsQuests", IF(MIN(qse.`method`) = 2 OR MAX(qse.`method`) = 3, 1, 0) AS "endsQuests"', 'g' => 'o.`id`'],
                        'qt'  => ['j' => '?_quests qt ON qse.`questId` = qt.`id`'],
                        's'   => ['j' => '?_spawns s ON s.`type` = 2 AND s.`typeId` = o.`id`']
                    );

    public function __construct(array $conditions = [], array $miscData = [])
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
            $curTpl['lootStack'] = [];
            $curTpl['spells']    = [];

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

    public function getListviewData() : array
    {
        $data = [];
        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'       => $this->id,
                'name'     => Lang::unescapeUISequences($this->getField('name', true), Lang::FMT_RAW),
                'type'     => $this->getField('typeCat'),
                'location' => $this->getSpawns(SPAWNINFO_ZONES)
            );

            if (!empty($this->curTpl['reqSkill']))
                $data[$this->id]['skill'] = $this->curTpl['reqSkill'];

            if ($this->curTpl['startsQuests'])
                $data[$this->id]['hasQuests'] = 1;

        }

        return $data;
    }

    public function renderTooltip($interactive = false) : ?string
    {
        if (!$this->curTpl)
            return null;

        $x  = '<table>';
        $x .= '<tr><td><b class="q">'.Lang::unescapeUISequences($this->getField('name', true), Lang::FMT_HTML).'</b></td></tr>';
        if ($this->curTpl['typeCat'])
            if ($_ = Lang::gameObject('type', $this->curTpl['typeCat']))
                $x .= '<tr><td>'.$_.'</td></tr>';

        if (isset($this->curTpl['lockId']))
            if ($locks = Lang::getLocks($this->curTpl['lockId']))
                foreach ($locks as $l)
                    $x .= '<tr><td>'.Lang::game('requires', [$l]).'</td></tr>';

        $x .= '</table>';

        return $x;
    }

    public function getJSGlobals(int $addMask = 0) : array
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
    protected string $type  = 'objects';
    protected static array $enums = array(
         1 => parent::ENUM_ZONE,
        16 => parent::ENUM_EVENT,
        50 => [1, 2, 3, 4, 663, 883]
    );

    protected static array $genericFilter = array(
         1 => [parent::CR_ENUM,     's.areaId',        false,                true], // foundin
         2 => [parent::CR_CALLBACK, 'cbQuestRelation', 'startsQuests',       0x1 ], // startsquest [side]
         3 => [parent::CR_CALLBACK, 'cbQuestRelation', 'endsQuests',         0x2 ], // endsquest [side]
         4 => [parent::CR_CALLBACK, 'cbOpenable',      null,                 null], // openable [yn]
         5 => [parent::CR_NYI_PH,   null,              0                         ], // averagemoneycontained [op] [int] - GOs don't contain money, match against 0
         7 => [parent::CR_NUMERIC,  'reqSkill',        NUM_CAST_INT              ], // requiredskilllevel
        11 => [parent::CR_FLAG,     'cuFlags',         CUSTOM_HAS_SCREENSHOT     ], // hasscreenshots
        13 => [parent::CR_FLAG,     'cuFlags',         CUSTOM_HAS_COMMENT        ], // hascomments
        15 => [parent::CR_NUMERIC,  'id',              NUM_CAST_INT              ], // id
        16 => [parent::CR_CALLBACK, 'cbRelEvent',      null,                 null], // relatedevent (ignore removed by event)
        18 => [parent::CR_FLAG,     'cuFlags',         CUSTOM_HAS_VIDEO          ], // hasvideos
        50 => [parent::CR_ENUM,     'spellFocusId',    true,                 true], // spellfocus
    );

    protected static array $inputFields = array(
        'cr'  => [parent::V_LIST,  [[1, 5], 7, 11, 13, 15, 16, 18, 50],              true ], // criteria ids
        'crs' => [parent::V_LIST,  [parent::ENUM_NONE, parent::ENUM_ANY, [0, 5000]], true ], // criteria operators
        'crv' => [parent::V_REGEX, parent::PATTERN_INT,                              true ], // criteria values - only numeric input values expected
        'na'  => [parent::V_REGEX, parent::PATTERN_NAME,                             false], // name - only printable chars, no delimiter
        'ma'  => [parent::V_EQUAL, 1,                                                false]  // match any / all filter
    );

    public array $extraOpts = [];

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = $this->values;

        // name
        if ($_v['na'])
            if ($_ = $this->buildMatchLookup(['name_loc'.Lang::getLocale()->value]))
                $parts[] = $_;

        return $parts;
    }

    protected function cbOpenable(int $cr, int $crs, string $crv) : ?array
    {
        if ($this->int2Bool($crs))
            return $crs ? ['OR', ['flags', 0x2, '&'], ['type', 3]] : ['AND', [['flags', 0x2, '&'], 0], ['type', 3, '!']];

        return null;
    }

    protected function cbQuestRelation(int $cr, int $crs, string $crv, $field, $value) : ?array
    {
        switch ($crs)
        {
            case 1:                                 // any
                return ['AND', ['qse.method', $value, '&'], ['qse.questId', null, '!']];
            case 2:                                 // alliance only
                return ['AND', ['qse.method', $value, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', ChrRace::MASK_HORDE, '&'], 0], ['qt.reqRaceMask', ChrRace::MASK_ALLIANCE, '&']];
            case 3:                                 // horde only
                return ['AND', ['qse.method', $value, '&'], ['qse.questId', null, '!'], [['qt.reqRaceMask', ChrRace::MASK_ALLIANCE, '&'], 0], ['qt.reqRaceMask', ChrRace::MASK_HORDE, '&']];
            case 4:                                 // both
                return ['AND', ['qse.method', $value, '&'], ['qse.questId', null, '!'], ['OR', ['AND', ['qt.reqRaceMask', ChrRace::MASK_ALLIANCE, '&'], ['qt.reqRaceMask', ChrRace::MASK_HORDE, '&']], ['qt.reqRaceMask', 0]]];
            case 5:                                 // none         todo (low): broken, if entry starts and ends quests...
                $this->extraOpts['o']['h'][] = $field.' = 0';
                return [1];
        }

        return null;
    }

    protected function cbRelEvent(int $cr, int $crs, string $crv) : ?array
    {
        if ($crs == parent::ENUM_ANY)
        {
            if ($eventIds = DB::Aowow()->selectCol('SELECT `id` FROM ?_events WHERE `holidayId` <> 0'))
                if ($goGuids  = DB::World()->selectCol('SELECT DISTINCT `guid` FROM game_event_gameobject WHERE `eventEntry` IN (?a)', $eventIds))
                    return ['s.guid', $goGuids];

            return [0];
        }
        else if ($crs == parent::ENUM_NONE)
        {
            if ($eventIds = DB::Aowow()->selectCol('SELECT `id` FROM ?_events WHERE `holidayId` <> 0'))
                if ($goGuids  = DB::World()->selectCol('SELECT DISTINCT `guid` FROM game_event_gameobject WHERE `eventEntry` IN (?a)', $eventIds))
                    return ['s.guid', $goGuids, '!'];

            return [0];
        }
        else if (in_array($crs, self::$enums[$cr]))
        {
            if ($eventIds = DB::Aowow()->selectCol('SELECT `id` FROM ?_events WHERE `holidayId` = ?d', $crs))
                if ($goGuids  = DB::World()->selectCol('SELECT DISTINCT `guid` FROM game_event_gameobject WHERE `eventEntry` IN (?a)', $eventIds))
                    return ['s.guid', $goGuids];

            return [0];
        }

        return null;
    }
}

?>
