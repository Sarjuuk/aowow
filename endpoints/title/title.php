<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class TitleBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'detail-page-generic';
    protected  array  $breadcrumb = [0, 10];
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  string $pageName   = 'title';

    public  int    $type      = Type::TITLE;
    public  int    $typeId    = 0;
    public ?string $expansion = null;

    private TitleList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new TitleList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('title'), Lang::title('notFound'));

        $this->h1 = $this->subject->getHtmlizedName();

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );

        $_title = Util::ucFirst(trim(strtr($this->subject->getField('male', true), ['%s' => '', ',' => ''])));


        /*************/
        /* Menu Path */
        /*************/

        $this->breadcrumb[] = $this->subject->getField('category');;


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $_title, Util::ucFirst(Lang::game('title')));


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        $infobox[] = Lang::main('side') . match ($this->subject->getField('side'))
        {
            SIDE_ALLIANCE => '[span class=icon-alliance]'.Lang::game('si', SIDE_ALLIANCE).'[/span]',
            SIDE_HORDE    => '[span class=icon-horde]'.Lang::game('si', SIDE_HORDE).'[/span]',
            default       => Lang::game('si', SIDE_BOTH)    // 0, 3
        };

        if ($g = $this->subject->getField('gender'))
            $infobox[] = Lang::main('gender').Lang::main('colon').'[span class=icon-'.($g == 2 ? 'female' : 'male').']'.Lang::main('sex', $g).'[/span]';

        if ($eId = $this->subject->getField('eventId'))
        {
            $this->extendGlobalIds(Type::WORLDEVENT, $eId);
            $infobox[] = Lang::game('eventShort', ['[event='.$eId.']']);
        }

        // id
        $infobox[] = Lang::title('id') . $this->typeId;

        // profiler relateed (note that this is part of the cache. I don't think this is important enough to calc for every view)
        if (Cfg::get('PROFILER_ENABLE'))
        {
            $x = DB::Aowow()->selectCell('SELECT COUNT(1) FROM ?_profiler_completion_titles WHERE `titleId` = ?d', $this->typeId);
            $y = DB::Aowow()->selectCell('SELECT COUNT(1) FROM ?_profiler_profiles WHERE `custom` = 0 AND `stub` = 0');
            $infobox[] = Lang::profiler('attainedBy', [round(($x ?: 0) * 100 / ($y ?: 1))]);

            // completion row added by InfoboxMarkup
        }

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.$this->subject->getField('name_loc0').'[/copy][/li]';

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0', 1);


        /****************/
        /* Main Content */
        /****************/

        $this->expansion  = Util::$expansionString[$this->subject->getField('expansion')];
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId]
        );

        // factionchange-equivalent
        if ($pendant = DB::World()->selectCell('SELECT IF(`horde_id` = ?d, `alliance_id`, -`horde_id`) FROM player_factionchange_titles WHERE `alliance_id` = ?d OR `horde_id` = ?d', $this->typeId, $this->typeId, $this->typeId))
        {
            $altTitle = new TitleList(array(['id', abs($pendant)]));
            if (!$altTitle->error)
            {
                $this->transfer = Lang::title('_transfer', array(
                    $altTitle->id,
                    $altTitle->getHtmlizedName(),
                    $pendant > 0 ? 'alliance' : 'horde',
                    $pendant > 0 ? Lang::game('si', SIDE_ALLIANCE) : Lang::game('si', SIDE_HORDE)
                ));
            }
        }


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: reward-from-quest
        $quests = new QuestList(array(['rewardTitleId', $this->typeId]));
        if (!$quests->error)
        {
            $this->extendGlobalData($quests->getJSGlobals(GLOBALINFO_REWARDS));

            $this->lvTabs->addListviewTab(new Listview(array(
                'data'        => $quests->getListviewData(),
                'id'          => 'reward-from-quest',
                'name'        => '$LANG.tab_rewardfrom',
                'hiddenCols'  => ['experience', 'money'],
                'visibleCols' => ['category']
            ), QuestList::$brickFile));
        }

        // tab: reward-from-achievement
        if ($aIds = DB::World()->selectCol('SELECT `ID` FROM achievement_reward WHERE `TitleA` = ?d OR `TitleH` = ?d', $this->typeId, $this->typeId))
        {
            $acvs = new AchievementList(array(['id', $aIds]));
            if (!$acvs->error)
            {
                $this->extendGlobalData($acvs->getJSGlobals());

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'        => $acvs->getListviewData(),
                    'id'          => 'reward-from-achievement',
                    'name'        => '$LANG.tab_rewardfrom',
                    'visibleCols' => ['category'],
                    'sort'        => ['reqlevel', 'name']
                ), AchievementList::$brickFile));
            }
        }

        // tab: criteria-of
        if ($crt = DB::World()->selectCol('SELECT `criteria_id` FROM achievement_criteria_data WHERE `type` = ?d AND `value1` = ?d', ACHIEVEMENT_CRITERIA_DATA_TYPE_S_KNOWN_TITLE, $this->typeId))
        {
            $acvs = new AchievementList(array(['ac.id', $crt]));
            if (!$acvs->error)
            {
                $this->extendGlobalData($acvs->getJSGlobals());

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'        => $acvs->getListviewData(),
                    'id'          => 'criteria-of',
                    'name'        => '$LANG.tab_criteriaof',
                    'visibleCols' => ['category']
                ), AchievementList::$brickFile));
            }
        }

        // tab: condition-for
        $cnd = new Conditions();
        $cnd->getByCondition(Type::TITLE, $this->typeId)->prepare();
        if ($tab = $cnd->toListviewTab('condition-for', '$LANG.tab_condition_for'))
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }

        parent::generate();
    }
}

?>
