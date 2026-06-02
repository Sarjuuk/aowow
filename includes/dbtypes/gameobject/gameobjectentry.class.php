<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GameobjectEntry extends DBTypeEntry
{
    public readonly  int       $cuFlags;
    public readonly  LocString $name;
    public readonly  int       $type;
    public readonly  int       $typeCat;
    public readonly  int       $event;
    public readonly  int       $displayId;
    /** @var int $faction - factiontemplate.dbc/id */
    public readonly  int       $faction;
    public readonly  int       $flags;
    public readonly  int       $lootId;
    public readonly  int       $lockId;
    public readonly  int       $reqSkill;
    public readonly  int       $pageTextId;
    public readonly  int       $linkedTrap;
    public readonly  int       $reqQuest;
    public readonly  int       $spellFocusId;
 // public readonly  int       $onUseSpell;
 // public readonly  int       $onSuccessSpell;
 // public readonly  int       $auraSpell;
 // public readonly  int       $triggeredSpell;
    public readonly ?string    $ScriptOrAI;
    public readonly ?string    $StringId;
    /** @var null|int[] $spells length: 4 - [onUse, onSuccess, aura, triggered] */
    public readonly ?array     $spells;
    /** @var null|int[] $meetingStone length: 3 - [minLevel, maxLevel, zoneId] */
    public readonly ?array     $meetingStone;
    /** @var null|int[] $capturePoint length: 3 - [minQty, maxQty, restockMinutes] */
    public readonly ?array     $capturePoint;
    /** @var null|int[] $lootStack length: 5 - [minPlayer, maxPlayer, minTime, maxTime, radius] */
    public readonly ?array     $lootStack;
    // ::faction
    /** @var int $factionId - faction.dbc/id */
    public readonly ?int       $factionId;
    public readonly ?int       $A;                          // react to alliance: -1: hostile, 0: neutral, 1: friendly
    public readonly ?int       $H;                          // react to horde: -1: hostile, 0: neutral, 1: friendly
    // quest startend
    public readonly  bool      $startsQuests;
 // public readonly  bool      $endsQuests;                 // only defined for filtering

    public static int    $dbType    = Type::OBJECT;
    public static string $brickFile = 'object';
    public static string $dataTable = '::objects';

    public const /* string */ QUERY_BASE = 'SELECT o.*, o.`id` AS ARRAY_KEY FROM ::objects o';
    public const /* array  */ QUERY_OPTS = array(
        'o'   => [['ft', 'qse']],
        'nml' => ['j' => ['::objects_search nml ON nml.`id` = o.`id` AND nml.`locale` = DB_LOC_I']],
        'ft'  => ['j' => ['::factiontemplate ft ON ft.`id` = o.`faction`', true], 's' => ', ft.`factionId`, IFNULL(ft.`A`, 0) AS "A", IFNULL(ft.`H`, 0) AS "H"'],
        'qse' => ['j' => ['::quests_startend qse ON qse.`type` = 2 AND qse.`typeId` = o.id', true], 's' => ', IF(MIN(qse.`method`) = 1 OR MAX(qse.`method`) = 3, 1, 0) AS "startsQuests", IF(MIN(qse.`method`) = 2 OR MAX(qse.`method`) = 3, 1, 0) AS "endsQuests"', 'g' => 'o.`id`'],
        'qt'  => ['j' => '::quests qt ON qse.`questId` = qt.`id`'],
        's'   => ['j' => '::spawns s ON s.`type` = 2 AND s.`typeId` = o.`id`']
    );

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $nameBak = ['name_loc'.Lang::getLocale()->value => Lang::gameObject('unnamed', [$initData['id']])];
        $n = new LocString($initData, 'name', pruneFromSrc: true);
        $this->name = !$n->isEmpty() ? $n : new LocString($nameBak);

        if (!in_array($initData['type'], [OBJECT_GOOBER, OBJECT_RITUAL, OBJECT_SPELLCASTER, OBJECT_FLAGSTAND, OBJECT_FLAGDROP, OBJECT_AURA_GENERATOR, OBJECT_TRAP]))
            $this->spells = null;
        else
            $this->spells = array(
                'onUse'     => $initData['onUseSpell'],
                'onSuccess' => $initData['onSuccessSpell'],
                'aura'      => $initData['auraSpell'],
                'triggered' => $initData['triggeredSpell']
            );

        // unpack miscInfo
        $meetStone =
        $capture   =
        $lootStack = null;
        $data = $initData['miscInfo'] ? explode(' ', $initData['miscInfo']) : null;

        match ($initData['type'])
        {
            OBJECT_CHEST,
            OBJECT_FISHINGHOLE   => $lootStack = $data,
            OBJECT_CAPTURE_POINT => $capture   = $data,
            OBJECT_MEETINGSTONE  => $meetStone = $data,
            default              => null
        };

        $this->meetingStone = $meetStone;
        $this->capturePoint = $capture;
        $this->lootStack    = $lootStack;

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                case 'miscInfo':                            // broken down earlier
                    continue 2;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }
    }

    public function getListviewRow(int $addInfoMask = 0x0, ?array $location = null) : array
    {
        if (count($location) > 3)
            array_splice($location, 3, replacement: -1);

        $data = array(
            'id'       => $this->id,
            'name'     => UIText::unescapeUISequences($this->name, Lang::FMT_RAW),
            'type'     => $this->typeCat,
            'location' => $location
        );

        if (!empty($this->reqSkill))
            $data['skill'] = $this->reqSkill;

        if ($this->startsQuests)
            $data['hasQuests'] = 1;

        return $data;
    }

    public function getJSGlobal(int $addMask = GLOBALINFO_ANY) : array
    {
        return [self::$dbType => [$this->id => array(
            'name' => UIText::unescapeUISequences($this->name, Lang::FMT_RAW)
        )]];
    }

    public function getSourceData() : array
    {
        return array(
            'n'  => $this->name,
            't'  => Type::OBJECT,
            'ti' => $this->id
        );
    }

    public function renderTooltip() : ?string
    {
        $x  = '<table>';
        $x .= '<tr><td><b class="q">'.UIText::unescapeUISequences($this->name, Lang::FMT_HTML).'</b></td></tr>';
        if ($this->typeCat)
            if ($_ = Lang::gameObject('type', $this->typeCat))
                $x .= '<tr><td>'.$_.'</td></tr>';

        if ($this->lockId)
            if ($locks = Lang::getLocks($this->lockId))
                foreach ($locks as $l)
                    $x .= '<tr><td>'.Lang::game('requires', [$l]).'</td></tr>';

        $x .= '</table>';

        return $x;
    }
}

?>
