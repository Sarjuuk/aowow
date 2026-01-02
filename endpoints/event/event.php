<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EventBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'detail-page-generic';
    protected  string $pageName   = 'event';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 11];

    public int   $type   = Type::WORLDEVENT;
    public int   $typeId = 0;
    public array $dates  = [];

    private WorldEventList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new WorldEventList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('event'), Lang::event('notFound'));

        $this->h1    = $this->subject->getField('name', true);
        $this->dates = array(
            'firstDate' => $this->subject->getField('startTime'),
            'lastDate'  => $this->subject->getField('endTime'),
            'length'    => $this->subject->getField('length'),
            'rec'       => $this->subject->getField('occurence')
        );

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );

        $_holidayId = $this->subject->getField('holidayId');


        /*************/
        /* Menu Path */
        /*************/

        $this->breadcrumb[] = match ($this->subject->getField('scheduleType'))
        {
            -1      => 1,
             0, 1   => 2,
             2      => 3,
            ''      => 0,
            default => 0
        };


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucWords(Lang::game('event')));


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // boss
        if ($_ = $this->subject->getField('bossCreature'))
        {
            $this->extendGlobalIds(Type::NPC, $_);
            $infobox[] = Lang::npc('rank', 3).Lang::main('colon').'[npc='.$_.']';
        }

        // id
        $infobox[] = Lang::event('id') . $this->typeId;

        // display holiday id to staff
        if ($_holidayId && User::isInGroup(U_GROUP_STAFF))
            $infobox[] = 'Holiday ID'.Lang::main('colon').$_holidayId;

        // icon
        if ($_ = $this->subject->getField('iconId'))
        {
            $infobox[] = Util::ucFirst(Lang::game('icon')).Lang::main('colon').'[icondb='.$_.' name=true]';
            $this->extendGlobalIds(Type::ICON, $_);
        }

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.$this->subject->getField('name_loc0').'[/copy][/li]';

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');


        /****************/
        /* Main Content */
        /****************/

        // no entry in ?_articles? use default HolidayDescription
        if ($_holidayId && empty($this->article))
            $this->article = new Markup($this->subject->getField('description', true), ['dbpage' => true]);

        if ($_holidayId)
            $this->wowheadLink = sprintf(WOWHEAD_LINK, Lang::getLocale()->domain(), 'event=', $_holidayId);

        $this->headIcons  = [$this->subject->getField('iconString')];
        $this->redButtons = array(
            BUTTON_WOWHEAD => $_holidayId > 0,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId]
        );

        parent::generate();


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: npcs
        if ($npcIds = DB::World()->selectCol('SELECT `id` AS ARRAY_KEY, IF(ec.`eventEntry` > 0, 1, 0) AS "added" FROM creature c, game_event_creature ec WHERE ec.`guid` = c.`guid` AND ABS(ec.`eventEntry`) = ?d', $this->typeId))
        {
            $creatures = new CreatureList(array(['id', array_keys($npcIds)]));
            if (!$creatures->error)
            {
                $data = $creatures->getListviewData();
                foreach ($data as &$d)
                    $d['method'] = $npcIds[$d['id']];

                $tabData = ['data' => $data];

                if ($_holidayId && CreatureListFilter::getCriteriaIndex(38, $_holidayId))
                    $tabData['note'] = sprintf(Util::$filterResultString, '?npcs&filter=cr=38;crs='.$_holidayId.';crv=0');

                $this->result->addDataLoader('zones');      // req. by secondary tooltip in this tab
                $this->lvTabs->addListviewTab(new Listview($tabData, CreatureList::$brickFile));
            }
        }

        // tab: objects
        if ($objectIds = DB::World()->selectCol('SELECT `id` AS ARRAY_KEY, IF(eg.`eventEntry` > 0, 1, 0) AS "added" FROM gameobject g, game_event_gameobject eg WHERE eg.`guid` = g.`guid` AND ABS(eg.`eventEntry`) = ?d', $this->typeId))
        {
            $objects = new GameObjectList(array(['id', array_keys($objectIds)]));
            if (!$objects->error)
            {
                $data = $objects->getListviewData();
                foreach ($data as &$d)
                    $d['method'] = $objectIds[$d['id']];

                $tabData = ['data' => $data];

                if ($_holidayId && GameObjectListFilter::getCriteriaIndex(16, $_holidayId))
                    $tabData['note'] = sprintf(Util::$filterResultString, '?objects&filter=cr=16;crs='.$_holidayId.';crv=0');

                $this->result->addDataLoader('zones');      // req. by secondary tooltip in this tab
                $this->lvTabs->addListviewTab(new Listview($tabData, GameObjectList::$brickFile));
            }
        }

        // tab: achievements
        $exclAcvs = [];
        if ($_ = $this->subject->getField('achievementCatOrId'))
        {
            $condition = $_ > 0 ? [['category', $_]] : [['id', -$_]];
            $acvs = new AchievementList($condition);
            if (!$acvs->error)
            {
                $this->extendGlobalData($acvs->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                $tabData = array(
                    'data'        => $acvs->getListviewData(),
                    'visibleCols' => ['category']
                );

                // don't reuse for criteria-of tab
                $exclAcvs = array_keys($tabData['data']);

                if ($_holidayId && AchievementListFilter::getCriteriaIndex(11, $_holidayId))
                    $tabData['note'] = sprintf(Util::$filterResultString, '?achievements&filter=cr=11;crs='.$_holidayId.';crv=0');

                $this->lvTabs->addListviewTab(new Listview($tabData, AchievementList::$brickFile));
            }
        }

        $itemCnd = [];
        if ($_holidayId)
        {
            // tab: criteria-of
            if ($extraCrt = DB::World()->selectCol('SELECT `criteria_id` FROM achievement_criteria_data WHERE `type` = ?d AND `value1` = ?d', ACHIEVEMENT_CRITERIA_DATA_TYPE_HOLIDAY, $_holidayId))
            {
                $condition = array(['ac.id', $extraCrt]);
                if ($exclAcvs)
                    $condition[] = ['a.id', $exclAcvs, '!'];

                $crtOf = new AchievementList($condition);
                if (!$crtOf->error)
                {
                    $this->extendGlobalData($crtOf->getJSGlobals());

                    $this->lvTabs->addListviewTab(new Listview(array(
                        'data' => $crtOf->getListviewData(),
                        'name' => '$LANG.tab_criteriaof',
                        'id'   => 'criteria-of'
                    ), AchievementList::$brickFile));
                }
            }

            $itemCnd[] = ['eventId', $this->typeId];        // direct requirement on item

            // tab: quests (by table, go & creature)
            $quests = new QuestList(array(['eventId', $this->typeId]));
            if (!$quests->error)
            {
                $this->extendGlobalData($quests->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));

                $tabData = ['data'=> $quests->getListviewData()];

                if (QuestListFilter::getCriteriaIndex(33, $_holidayId))
                    $tabData['note'] = sprintf(Util::$filterResultString, '?quests&filter=cr=33;crs='.$_holidayId.';crv=0');

                $this->lvTabs->addListviewTab(new Listview($tabData, QuestList::$brickFile));

                $questItems = [];
                foreach (array_column($quests->rewards, Type::ITEM) as $arr)
                    $questItems = array_merge($questItems, array_keys($arr));

                foreach (array_column($quests->choices, Type::ITEM) as $arr)
                    $questItems = array_merge($questItems, array_keys($arr));

                foreach (array_column($quests->requires, Type::ITEM) as $arr)
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
            if ($sells = DB::World()->selectCol(
               'SELECT     `item` FROM npc_vendor nv                                                               WHERE     `entry` IN (?a) UNION
                SELECT nv1.`item` FROM npc_vendor nv1             JOIN npc_vendor nv2 ON -nv1.`entry` = nv2.`item` WHERE nv2.`entry` IN (?a) UNION
                SELECT     `item` FROM game_event_npc_vendor genv JOIN creature   c   ON genv.`guid`  =   c.`guid` WHERE   c.`id`    IN (?a)',
                $cIds, $cIds, $cIds
            ))
                $itemCnd[] = ['id', $sells];
        }

        // tab: items
        // not checking for loot ... cant distinguish between eventLoot and fillerCrapLoot
        if ($itemCnd)
        {
            array_unshift($itemCnd, 'OR');
            $eventItems = new ItemList($itemCnd);
            if (!$eventItems->error)
            {
                $this->extendGlobalData($eventItems->getJSGlobals(GLOBALINFO_SELF));

                $tabData = ['data'=> $eventItems->getListviewData()];

                if ($_holidayId && ItemListFilter::getCriteriaIndex(160, $_holidayId))
                    $tabData['note'] = sprintf(Util::$filterResultString, '?items&filter=cr=160;crs='.$_holidayId.';crv=0');

                $this->lvTabs->addListviewTab(new Listview($tabData, ItemList::$brickFile));
            }
        }

        // tab: see also (event conditions)
        if ($rel = DB::World()->selectCol('SELECT IF(`eventEntry` = `prerequisite_event`, NULL, IF(`eventEntry` = ?d, `prerequisite_event`, -`eventEntry`)) FROM game_event_prerequisite WHERE `prerequisite_event` = ?d OR `eventEntry` = ?d', $this->typeId, $this->typeId, $this->typeId))
        {
            if (array_filter($rel, fn($x) => $x === null))
                trigger_error('game_event_prerequisite: this event has itself as prerequisite', E_USER_WARNING);

            if ($seeAlso = array_filter($rel, fn($x) => $x > 0))
            {
                $relEvents = new WorldEventList(array(['id', $seeAlso]));
                $this->extendGlobalData($relEvents->getJSGlobals());
                $relData   = $relEvents->getListviewData();
                foreach ($relEvents->getFoundIDs() as $id)
                    Conditions::extendListviewRow($relData[$id], Conditions::SRC_NONE, $this->typeId, [-Conditions::ACTIVE_EVENT, $this->typeId]);

                $this->extendGlobalData($this->subject->getJSGlobals());
                $d = $this->subject->getListviewData();
                foreach ($rel as $r)
                    if ($r > 0)
                        if (Conditions::extendListviewRow($d[$this->typeId], Conditions::SRC_NONE, $this->typeId, [-Conditions::ACTIVE_EVENT, $r]))
                            $this->extendGlobalIds(Type::WORLDEVENT, $r);

                $tabData = array(
                    'data'       => array_merge($relData, $d),
                    'id'         => 'see-also',
                    'name'       => '$LANG.tab_seealso',
                    'hiddenCols' => ['date'],
                    'extraCols'  => ['$Listview.extraCols.condition']
                );
                $this->lvTabs->addListviewTab(new Listview($tabData, WorldEventList::$brickFile));
            }
        }

        // tab: condition for
        $cnd = new Conditions();
        $cnd->getByCondition(Type::WORLDEVENT, $this->typeId)->prepare();
        if ($tab = $cnd->toListviewTab('condition-for', '$LANG.tab_condition_for'))
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }

        $this->result->registerDisplayHook('lvTabs', [self::class, 'tabsHook']);
        $this->result->registerDisplayHook('infobox', [self::class, 'infoboxHook']);
    }

    // update dates to now()
    public static function tabsHook(Template\PageTemplate &$pt, Tabs &$lvTabs) : void
    {
        foreach ($lvTabs->iterate() as &$listview)
            if (is_object($listview) && $listview?->getTemplate() == 'holiday')
                WorldEventList::updateListview($listview);
    }

    /* finalize infobox */
    public static function infoboxHook(Template\PageTemplate &$pt, ?InfoboxMarkup &$markup) : void
    {
        WorldEventList::updateDates($pt->dates, $start, $end, $rec);
        $infobox = [];

        // start
        if ($start)
            $infobox[] = Lang::event('start').date(Lang::main('dateFmtLong'), $start);

        // end
        if ($end)
            $infobox[] = Lang::event('end').date(Lang::main('dateFmtLong'), $end);

        // interval
        if ($rec > 0)
            $infobox[] = Lang::event('interval').DateTime::formatTimeElapsed($rec * 1000);

        // in progress
        if ($start < time() && $end > time())
            $infobox[] = '[span class=q2]'.Lang::event('inProgress').'[/span]';

        if ($infobox && !$markup)
            $markup = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');
        else if ($markup)
            foreach ($infobox as $ib)
                $markup->addItem($ib);
    }
}

?>
