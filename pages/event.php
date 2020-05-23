<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 11: Worldevent g_initPath()
//  tabId  0: Database   g_initHeader()
class EventPage extends GenericPage
{
    use TrDetailPage;

    protected $type          = TYPE_WORLDEVENT;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 11];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    private   $powerTpl      = '$WowheadPower.registerHoliday(%d, %d, %s);';
    private   $hId           = 0;
    private   $eId           = 0;

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        // temp locale
        if ($this->mode == CACHE_TYPE_TOOLTIP && isset($_GET['domain']))
            Util::powerUseLocale($_GET['domain']);

        $this->typeId = intVal($id);

        $this->subject = new WorldEventList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('event'), Lang::event('notFound'));

        $this->hId   = $this->subject->getField('holidayId');
        $this->eId   = $this->typeId;
        $this->name  = $this->subject->getField('name', true);
        $this->dates = array(
            'firstDate' => $this->subject->getField('startTime'),
            'lastDate'  => $this->subject->getField('endTime'),
            'length'    => $this->subject->getField('length'),
            'rec'       => $this->subject->getField('occurence')
        );
    }

    protected function generatePath()
    {
        switch ($this->subject->getField('scheduleType'))
        {
            case '': $this->path[] = 0; break;
            case -1: $this->path[] = 1; break;
            case  0:
            case  1: $this->path[] = 2; break;
            case  2: $this->path[] = 3; break;
        }
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->subject->getField('name', true), Util::ucFirst(Lang::game('event')));
    }

    protected function generateContent()
    {
        $this->addJS('?data=zones&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        /***********/
        /* Infobox */
        /***********/

        $this->infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // boss
        if ($_ = $this->subject->getField('bossCreature'))
        {
            $this->extendGlobalIds(TYPE_NPC, $_);
            $this->infobox[] = Lang::npc('rank', 3).Lang::main('colon').'[npc='.$_.']';
        }

        // display internal id to staff
        if (User::isInGroup(U_GROUP_STAFF))
            $this->infobox[] = 'Event-Id'.Lang::main('colon').$this->eId;

        /****************/
        /* Main Content */
        /****************/

        // no entry in ?_articles? use default HolidayDescription
        if ($this->hId && empty($this->article))
            $this->article = ['text' => Util::jsEscape($this->subject->getField('description', true)), 'params' => []];

        $this->headIcons  = [$this->subject->getField('iconString')];
        $this->redButtons = array(
            BUTTON_WOWHEAD => $this->hId > 0,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId]
        );

        /**************/
        /* Extra Tabs */
        /**************/

        $hasFilter = in_array($this->hId, [372, 283, 285, 353, 420, 400, 284, 201, 374, 409, 141, 324, 321, 424, 335, 327, 341, 181, 404, 398, 301]);

        // tab: npcs
        if ($npcIds = DB::World()->selectCol('SELECT id AS ARRAY_KEY, IF(ec.eventEntry > 0, 1, 0) AS added FROM creature c, game_event_creature ec WHERE ec.guid = c.guid AND ABS(ec.eventEntry) = ?d', $this->eId))
        {
            $creatures = new CreatureList(array(['id', array_keys($npcIds)]));
            if (!$creatures->error)
            {
                $data = $creatures->getListviewData();
                foreach ($data as &$d)
                    $d['method'] = $npcIds[$d['id']];

                $tabData = ['data' => array_values($data)];

                if ($hasFilter)
                    $tabData['note'] = sprintf(Util::$filterResultString, '?npcs&filter=cr=38;crs='.$this->hId.';crv=0');

                $this->lvTabs[] = ['creature', $tabData];
            }
        }

        // tab: objects
        if ($objectIds = DB::World()->selectCol('SELECT id AS ARRAY_KEY, IF(eg.eventEntry > 0, 1, 0) AS added FROM gameobject g, game_event_gameobject eg WHERE eg.guid = g.guid AND ABS(eg.eventEntry) = ?d', $this->eId))
        {
            $objects = new GameObjectList(array(['id', array_keys($objectIds)]));
            if (!$objects->error)
            {
                $data = $objects->getListviewData();
                foreach ($data as &$d)
                    $d['method'] = $objectIds[$d['id']];

                $tabData = ['data' => array_values($data)];

                if ($hasFilter)
                    $tabData['note'] = sprintf(Util::$filterResultString, '?objects&filter=cr=16;crs='.$this->hId.';crv=0');

                $this->lvTabs[] = ['object', $tabData];
            }
        }

        // tab: achievements
        if ($_ = $this->subject->getField('achievementCatOrId'))
        {
            $condition = $_ > 0 ? [['category', $_]] : [['id', -$_]];
            $acvs = new AchievementList($condition);
            if (!$acvs->error)
            {
                $this->extendGlobalData($acvs->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                $tabData = array(
                    'data'        => array_values($acvs->getListviewData()),
                    'visibleCols' => ['category']
                );

                if ($hasFilter)
                    $tabData['note'] = sprintf(Util::$filterResultString, '?achievements&filter=cr=11;crs='.$this->hId.';crv=0');

                $this->lvTabs[] = ['achievement', $tabData];
            }
        }

        $itemCnd = [];
        if ($this->hId)
        {
            $itemCnd = array(
                'OR',
                ['eventId', $this->eId],                    // direct requirement on item
            );

            // tab: quests (by table, go & creature)
            $quests = new QuestList(array(['eventId', $this->eId]));
            if (!$quests->error)
            {
                $this->extendGlobalData($quests->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));

                $tabData = ['data'=> array_values($quests->getListviewData())];

                if ($hasFilter)
                    $tabData['note'] = sprintf(Util::$filterResultString, '?quests&filter=cr=33;crs='.$this->hId.';crv=0');

                $this->lvTabs[] = ['quest', $tabData];

                $questItems = [];
                foreach (array_column($quests->rewards, TYPE_ITEM) as $arr)
                    $questItems = array_merge($questItems, $arr);

                foreach (array_column($quests->requires, TYPE_ITEM) as $arr)
                    $questItems = array_merge($questItems, $arr);

                if ($questItems)
                    $itemCnd[] = ['id', $questItems];
            }
        }

        // items from creature
        if ($npcIds && !$creatures->error)
        {
            // vendor
            $cIds = $creatures->getFoundIDs();
            if ($sells = DB::World()->selectCol('SELECT item FROM npc_vendor nv WHERE entry IN (?a) UNION SELECT item FROM game_event_npc_vendor genv JOIN creature c ON genv.guid = c.guid WHERE c.id IN (?a)', $cIds, $cIds))
                $itemCnd[] = ['id', $sells];
        }

        // tab: items
        // not checking for loot ... cant distinguish between eventLoot and fillerCrapLoot
        if ($itemCnd)
        {
            $eventItems = new ItemList($itemCnd);
            if (!$eventItems->error)
            {
                $this->extendGlobalData($eventItems->getJSGlobals(GLOBALINFO_SELF));

                $tabData = ['data'=> array_values($eventItems->getListviewData())];

                if ($hasFilter)
                    $tabData['note'] = sprintf(Util::$filterResultString, '?items&filter=cr=160;crs='.$this->hId.';crv=0');

                $this->lvTabs[] = ['item', $tabData];
            }
        }

        // tab: see also (event conditions)
        if ($rel = DB::World()->selectCol('SELECT IF(eventEntry = prerequisite_event, NULL, IF(eventEntry = ?d, prerequisite_event, -eventEntry)) FROM game_event_prerequisite WHERE prerequisite_event = ?d OR eventEntry = ?d', $this->eId, $this->eId, $this->eId))
        {
            $list = [];
            array_walk($rel, function($v, $k) use (&$list) {
                if ($v > 0)
                    $list[] = $v;
                else if ($v === null)
                    trigger_error('game_event_prerequisite: this event has itself as prerequisite', E_USER_WARNING);
            });

            if ($list)
            {
                $relEvents = new WorldEventList(array(['id', $list]));
                $this->extendGlobalData($relEvents->getJSGlobals());
                $relData   = $relEvents->getListviewData();
                foreach ($relEvents->getFoundIDs() as $id)
                    $relData[$id]['condition'][0][$this->typeId][] = [[-CND_ACTIVE_EVENT, $this->eId]];

                $this->extendGlobalData($this->subject->getJSGlobals());
                foreach ($rel as $r)
                {
                    if ($r <= 0)
                        continue;

                    $this->extendGlobalIds(TYPE_WORLDEVENT, $r);

                    $d = $this->subject->getListviewData();
                    $d[$this->eId]['condition'][0][$this->typeId][] = [[-CND_ACTIVE_EVENT, $r]];

                    $relData = array_merge($relData, $d);
                }

                $this->lvTabs[] = ['event', array(
                    'data'       => array_values($relData),
                    'id'         => 'see-also',
                    'name'       => '$LANG.tab_seealso',
                    'hiddenCols' => ['date'],
                    'extraCols'  => ['$Listview.extraCols.condition']
                )];
            }
        }
    }

    protected function generateTooltip() : string
    {
        $power = new StdClass();
        if (!$this->subject->error)
        {
            $power->{'name_'.User::$localeString} = $this->subject->getField('name', true);

            if ($this->subject->getField('iconString') != 'trade_engineering')
                $power->icon = rawurlencode($this->subject->getField('iconString', true, true));

            $power->{'tooltip_'.User::$localeString} = $this->subject->renderTooltip();
        }

        return sprintf($this->powerTpl, $this->typeId, User::$localeId, Util::toJSON($power, JSON_AOWOW_POWER));
    }

    protected function postCache()
    {
        // update dates to now()
        $updated = WorldEventList::updateDates($this->dates);

        if ($this->mode == CACHE_TYPE_TOOLTIP)
        {
            return array(
                date(Lang::main('dateFmtLong'), $updated['start']),
                date(Lang::main('dateFmtLong'), $updated['end'])
            );
        }
        else
        {
            if ($this->hId)
                Util::$wowheadLink = 'http://'.Util::$subDomains[User::$localeId].'.wowhead.com/event='.$this->hId;

            /********************/
            /* finalize infobox */
            /********************/

            // start
            if ($updated['start'])
                array_push($this->infobox, Lang::event('start').Lang::main('colon').date(Lang::main('dateFmtLong'), $updated['start']));

            // end
            if ($updated['end'])
                array_push($this->infobox, Lang::event('end').Lang::main('colon').date(Lang::main('dateFmtLong'), $updated['end']));

            // occurence
            if ($updated['rec'] > 0)
                array_push($this->infobox, Lang::event('interval').Lang::main('colon').Util::formatTime($updated['rec'] * 1000));

            // in progress
            if ($updated['start'] < time() && $updated['end'] > time())
                array_push($this->infobox, '[span class=q2]'.Lang::event('inProgress').'[/span]');

            $this->infobox = '[ul][li]'.implode('[/li][li]', $this->infobox).'[/li][/ul]';

            /***************************/
            /* finalize related events */
            /***************************/

            foreach ($this->lvTabs as &$view)
            {
                if ($view[0] !=  WorldEventList::$brickFile)
                    continue;

                foreach ($view[1]['data'] as &$data)
                {
                    $updated = WorldEventList::updateDates($data['_date']);
                    unset($data['_date']);
                    $data['startDate'] = $updated['start'] ? date(Util::$dateFormatInternal, $updated['start']) : false;
                    $data['endDate']   = $updated['end']   ? date(Util::$dateFormatInternal, $updated['end'])   : false;
                    $data['rec']       = $updated['rec'];
                }

            }
        }
    }
}

?>
