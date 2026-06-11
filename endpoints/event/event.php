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

    private WorldeventEntry $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new WorldeventEntry($this->typeId);
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('event'), Lang::event('notFound'));

        $this->h1    = $this->subject->name;
        $this->dates = array(
            'firstDate' => $this->subject->startTime,
            'lastDate'  => $this->subject->endTime,
            'length'    => $this->subject->length,
            'rec'       => $this->subject->occurence
        );

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );

        $_holidayId = $this->subject->holidayId;


        /*************/
        /* Menu Path */
        /*************/

        $this->breadcrumb[] = $this->subject->category;


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucWords(Lang::game('event')));


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->cuFlags);

        // boss
        if ($_ = $this->subject->bossCreature)
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
        if ($_ = $this->subject->iconId)
        {
            $infobox[] = Util::ucFirst(Lang::game('icon')).Lang::main('colon').'[icondb='.$_.' name=true]';
            $this->extendGlobalIds(Type::ICON, $_);
        }

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.($this->subject->name)(Locale::EN).'[/copy][/li]';

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');


        /****************/
        /* Main Content */
        /****************/

        // no entry in ::articles? use default HolidayDescription
        if ($_holidayId && empty($this->article) && !$this->subject->description->isEmpty())
            $this->article = new Markup($this->subject->description, ['dbpage' => true]);

        if ($_holidayId)
            $this->wowheadLink = sprintf(WOWHEAD_LINK, Lang::getLocale()->domain(), 'event=', $_holidayId);

        $this->headIcons  = [$this->subject->icon];
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
        $creatures = null;
        if ($npcIds = DB::World()->selectCol('SELECT `id` AS ARRAY_KEY, IF(ec.`eventEntry` > 0, 1, 0) AS "added" FROM creature c, game_event_creature ec WHERE ec.`guid` = c.`guid` AND ABS(ec.`eventEntry`) = %i', $this->typeId))
        {
            $creatures = new CreatureContainer(array(['id', array_keys($npcIds)]));
            if (!$creatures->error)
            {
                $data = $creatures->getListviewData();
                foreach ($data as &$d)
                    $d['method'] = $npcIds[$d['id']];

                $tabData = ['data' => $data];

                if ($_holidayId && CreatureFilter::getCriteriaIndex(38, $_holidayId))
                    $tabData['note'] = sprintf(Util::$filterResultString, '?npcs&filter=cr=38;crs='.$_holidayId.';crv=0');

                $this->result->addDataLoader('zones');      // req. by secondary tooltip in this tab
                $this->lvTabs->addListviewTab(new Listview($tabData, CreatureEntry::$brickFile));
            }
        }

        // tab: objects
        if ($objectIds = DB::World()->selectCol('SELECT `id` AS ARRAY_KEY, IF(eg.`eventEntry` > 0, 1, 0) AS "added" FROM gameobject g, game_event_gameobject eg WHERE eg.`guid` = g.`guid` AND ABS(eg.`eventEntry`) = %i', $this->typeId))
        {
            $objects = new GameobjectContainer(array(['id', array_keys($objectIds)]));
            if (!$objects->error)
            {
                $data = $objects->getListviewData();
                foreach ($data as &$d)
                    $d['method'] = $objectIds[$d['id']];

                $tabData = ['data' => $data];

                if ($_holidayId && GameobjectFilter::getCriteriaIndex(16, $_holidayId))
                    $tabData['note'] = sprintf(Util::$filterResultString, '?objects&filter=cr=16;crs='.$_holidayId.';crv=0');

                $this->result->addDataLoader('zones');      // req. by secondary tooltip in this tab
                $this->lvTabs->addListviewTab(new Listview($tabData, GameobjectEntry::$brickFile));
            }
        }

        // tab: achievements
        $exclAcvs = [];
        if ($_ = $this->subject->achievementCatOrId)
        {
            $condition = $_ > 0 ? [['category', $_]] : [['id', -$_]];
            $acvs = new AchievementContainer($condition);
            if (!$acvs->error)
            {
                $this->extendGlobalData($acvs->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                $tabData = array(
                    'data'        => $acvs->getListviewData(),
                    'visibleCols' => ['category']
                );

                // don't reuse for criteria-of tab
                $exclAcvs = array_keys($tabData['data']);

                if ($_holidayId && AchievementFilter::getCriteriaIndex(11, $_holidayId))
                    $tabData['note'] = sprintf(Util::$filterResultString, '?achievements&filter=cr=11;crs='.$_holidayId.';crv=0');

                $this->lvTabs->addListviewTab(new Listview($tabData, AchievementEntry::$brickFile));
            }
        }

        $itemCnd = [];
        if ($_holidayId)
        {
            // tab: criteria-of
            if ($extraCrt = DB::World()->selectCol('SELECT `criteria_id` FROM achievement_criteria_data WHERE `type` = %i AND `value1` = %i', ACHIEVEMENT_CRITERIA_DATA_TYPE_HOLIDAY, $_holidayId))
            {
                $condition = array(['ac.id', $extraCrt]);
                if ($exclAcvs)
                    $condition[] = ['a.id', $exclAcvs, '!'];

                $crtOf = new AchievementContainer($condition);
                if (!$crtOf->error)
                {
                    $this->extendGlobalData($crtOf->getJSGlobals());

                    $this->lvTabs->addListviewTab(new Listview(array(
                        'data' => $crtOf->getListviewData(),
                        'name' => '$LANG.tab_criteriaof',
                        'id'   => 'criteria-of'
                    ), AchievementEntry::$brickFile));
                }
            }

            $itemCnd[] = ['eventId', $this->typeId];        // direct requirement on item
        }

        // tab: quests (by table, go & creature)
        $quests = new QuestContainer(array(['eventId', $this->typeId]));
        if (!$quests->error)
        {
            $this->extendGlobalData($quests->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));

            $tabData = ['data'=> $quests->getListviewData()];

            if (QuestFilter::getCriteriaIndex(33, $_holidayId))
                $tabData['note'] = sprintf(Util::$filterResultString, '?quests&filter=cr=33;crs='.$_holidayId.';crv=0');

            $this->lvTabs->addListviewTab(new Listview($tabData, QuestEntry::$brickFile));

            $questItems = [];
            foreach ($quests->iterate() as $entry)
                $questItems = array_merge($questItems, array_filter($entry->rewardItemId), array_filter($entry->rewardChoiceItemId), array_filter($entry->reqItemId));

            if ($questItems)
                $itemCnd[] = ['id', $questItems];
        }

        // items from creature
        if ($creatures && !$creatures->error)
        {
            // vendor
            $cIds = $creatures->getFoundIDs();
            if ($sells = DB::World()->selectCol(
               'SELECT     `item` FROM npc_vendor nv                                                               WHERE     `entry` IN %in UNION
                SELECT nv1.`item` FROM npc_vendor nv1             JOIN npc_vendor nv2 ON -nv1.`entry` = nv2.`item` WHERE nv2.`entry` IN %in UNION
                SELECT     `item` FROM game_event_npc_vendor genv JOIN creature   c   ON genv.`guid`  =   c.`guid` WHERE   c.`id`    IN %in',
                $cIds, $cIds, $cIds
            ))
                $itemCnd[] = ['id', $sells];
        }

        // tab: items
        // not checking for loot ... cant distinguish between eventLoot and fillerCrapLoot
        if ($itemCnd)
        {
            array_unshift($itemCnd, DB::OR);
            $eventItems = new ItemContainer($itemCnd);
            if (!$eventItems->error)
            {
                $this->extendGlobalData($eventItems->getJSGlobals(GLOBALINFO_SELF));

                $tabData = ['data'=> $eventItems->getListviewData()];

                if ($_holidayId && ItemFilter::getCriteriaIndex(160, $_holidayId))
                    $tabData['note'] = sprintf(Util::$filterResultString, '?items&filter=cr=160;crs='.$_holidayId.';crv=0');

                $this->lvTabs->addListviewTab(new Listview($tabData, ItemEntry::$brickFile));
            }
        }

        // tab: see also (event conditions)
        if ($rel = DB::World()->selectCol('SELECT IF(`eventEntry` = `prerequisite_event`, NULL, IF(`eventEntry` = %i, `prerequisite_event`, -`eventEntry`)) FROM game_event_prerequisite WHERE `prerequisite_event` = %i OR `eventEntry` = %i', $this->typeId, $this->typeId, $this->typeId))
        {
            if (array_filter($rel, fn($x) => $x === null))
                trigger_error('game_event_prerequisite: this event has itself as prerequisite', E_USER_WARNING);

            if ($seeAlso = array_filter($rel, fn($x) => $x > 0))
            {
                $relEvents = new WorldeventContainer(array(['id', $seeAlso]));
                $this->extendGlobalData($relEvents->getJSGlobals());
                $relData   = $relEvents->getListviewData();
                foreach ($relEvents->getFoundIDs() as $id)
                    Conditions::extendListviewRow($relData[$id], Conditions::SRC_NONE, $this->typeId, [-Conditions::ACTIVE_EVENT, $this->typeId]);

                $this->extendGlobalData($this->subject->getJSGlobal());
                $d = $this->subject->getListviewRow();
                foreach ($rel as $r)
                    if ($r > 0)
                        if (Conditions::extendListviewRow($d, Conditions::SRC_NONE, $this->typeId, [-Conditions::ACTIVE_EVENT, $r]))
                            $this->extendGlobalIds(Type::WORLDEVENT, $r);

                $tabData = array(
                    'data'       => array_merge($relData, [$this->typeId => $d]),
                    'id'         => 'see-also',
                    'name'       => '$LANG.tab_seealso',
                    'hiddenCols' => ['date'],
                    'extraCols'  => ['$Listview.extraCols.condition']
                );
                $this->lvTabs->addListviewTab(new Listview($tabData, WorldeventEntry::$brickFile));
            }
        }

        // tab: condition for
        $cnd = new Conditions();
        $cnd->getByCondition(Type::WORLDEVENT, $this->typeId)->prepare();
        if ($tab = $cnd->toListviewTab('condition-for', '$LANG.tab_condition_for'))
        {
            $this->extendGlobalData($cnd->getJSGlobals());
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
                WorldeventEntry::updateListview($listview);
    }

    /* finalize infobox */
    public static function infoboxHook(Template\PageTemplate &$pt, ?InfoboxMarkup &$markup) : void
    {
        WorldeventEntry::updateDates($pt->dates, $start, $end, $rec);
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
