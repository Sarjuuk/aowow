<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class RaceBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    private const MOUNT_VENDORS = array(                    // race => [starter, argent tournament]
        null,           [384,   33307], [3362,  33553], [1261,  33310],
        [4730,  33653], [4731,  33555], [3685,  33556], [7955,  33650],
        [7952,  33554], null,           [16264, 33557], [17584, 33657]
    );

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'detail-page-generic';
    protected  string $pageName   = 'race';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 13];

    public int     $type      = Type::CHR_RACE;
    public int     $typeId    = 0;
    public ?string $expansion = null;

    private CharRaceList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new CharRaceList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('race'), Lang::race('notFound'));

        $this->h1 = $this->subject->getField('name', true);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );


        /*************/
        /* Menu Path */
        /*************/

        $this->breadcrumb[] = $this->typeId;


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucFirst(Lang::game('race')));


        /***********/
        /* Infobox */
        /***********/

        $ra = ChrRace::from($this->typeId);

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // side
        if ($_ = $this->subject->getField('side'))
            $infobox[] = Lang::main('side').'[span class=icon-'.($_ == SIDE_HORDE ? 'horde' : 'alliance').']'.Lang::game('si', $_).'[/span]';

        // faction
        if ($_ = $this->subject->getField('factionId'))
        {
            $this->extendGlobalIds(Type::FACTION, $_);
            $infobox[] = Util::ucFirst(Lang::game('faction')).Lang::main('colon').'[faction='.$_.']';
        }

        // leader
        if ($_ = $this->subject->getField('leader'))
        {
            $this->extendGlobalIds(Type::NPC, $_);
            $infobox[] = Lang::race('racialLeader').'[npc='.$_.']';
        }

        // start area
        if ($_ = $this->subject->getField('startAreaId'))
        {
            $this->extendGlobalIds(Type::ZONE, $_);
            $infobox[] = Lang::race('startZone').Lang::main('colon').'[zone='.$_.']';
        }

        // id
        $infobox[] = Lang::race('id') . $this->typeId;

        // icon
        $mIcon = $this->subject->getField('iconId0');
        $fIcon = $this->subject->getField('iconId1');
        if ($mIcon || $fIcon)
        {
            $buff = '';
            if ($mIcon)
            {
                $buff .= '[icondb='.$mIcon.(!$fIcon ? ' name=true': '').']';
                $this->extendGlobalIds(Type::ICON, $mIcon);
            }
            if ($fIcon)
            {
                $buff .= '[icondb='.$fIcon.' name=true]';
                $this->extendGlobalIds(Type::ICON, $fIcon);
            }

            $infobox[] = Util::ucFirst(Lang::game('icon')).Lang::main('colon').$buff;
        }

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.$this->subject->getField('name_loc0').'[/copy][/li]';

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');


        /****************/
        /* Main Content */
        /****************/

        $this->expansion  = Util::$expansionString[$this->subject->getField('expansion')];
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId]
        );

        if ($_ = $this->subject->getField('iconStringMale'))
            $this->headIcons[] = $_;
        if ($_ = $this->subject->getField('iconStringFemale'))
            $this->headIcons[] = $_;


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: classes
        $classes = new CharClassList(array(['racemask', $ra->toMask(), '&']));
        if (!$classes->error)
        {
            $this->extendGlobalData($classes->getJSGlobals());
            $this->lvTabs->addListviewTab(new Listview(['data' => $classes->getListviewData()], CharClassList::$brickFile));
        }

        // tab: languages
        $conditions = array(
            ['typeCat', -11],                               // proficiencies
            ['reqRaceMask', $ra->toMask(), '&']             // only languages are race-restricted
        );

        $tongues = new SpellList($conditions);
        if (!$tongues->error)
        {
            $this->extendGlobalData($tongues->getJSGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data'       => $tongues->getListviewData(),
                'id'         => 'languages',
                'name'       => '$LANG.tab_languages',
                'hiddenCols' => ['reagents']
            ), SpellList::$brickFile));
        }

        // tab: racial-traits
        $conditions = array(
            ['typeCat', -4],                               // racial traits
            ['reqRaceMask', $ra->toMask(), '&']
        );

        $racials = new SpellList($conditions);
        if (!$racials->error)
        {
            $this->extendGlobalData($racials->getJSGlobals());
            $tabData = array(
                'data'       => $racials->getListviewData(),
                'id'         => 'racial-traits',
                'name'       => '$LANG.tab_racialtraits',
                'hiddenCols' => ['reagents']
            );
            if ($racials->hasDiffFields('reqClassMask'))
                $tabData['visibleCols'] = ['classes'];

            $this->lvTabs->addListviewTab(new Listview($tabData, SpellList::$brickFile));
        }

        // tab: quests
        $conditions = array(
            ['reqRaceMask', $ra->toMask(), '&'],
            [['reqRaceMask', ChrRace::MASK_HORDE, '&'], ChrRace::MASK_HORDE, '!'],
            [['reqRaceMask', ChrRace::MASK_ALLIANCE, '&'], ChrRace::MASK_ALLIANCE, '!']
        );

        $quests = new QuestList($conditions);
        if (!$quests->error)
        {
            $this->extendGlobalData($quests->getJSGlobals());
            $this->lvTabs->addListviewTab(new Listview(['data' => $quests->getListviewData()], QuestList::$brickFile));
        }

        // tab: mounts
        // ok, this sucks, but i rather hardcode the trainer, than fetch items by namepart
        if (isset(self::MOUNT_VENDORS[$this->typeId]))
        {
            if ($items = DB::World()->selectCol('SELECT `item` FROM npc_vendor WHERE `entry` IN (?a)', self::MOUNT_VENDORS[$this->typeId]))
            {
                $conditions = array(
                    ['i.id', $items],
                    ['i.class', ITEM_CLASS_MISC],
                    ['i.subClass', 5],                              // mounts
                );

                $mounts = new ItemList($conditions);
                if (!$mounts->error)
                {
                    $this->extendGlobalData($mounts->getJSGlobals());
                    $this->lvTabs->addListviewTab(new Listview(array(
                        'data'       => $mounts->getListviewData(),
                        'id'         => 'mounts',
                        'name'       => '$LANG.tab_mounts',
                        'hiddenCols' => ['slot', 'type']
                    ), ItemList::$brickFile));
                }
            }
        }

        // tab: sounds
        if ($vo = DB::Aowow()->selectCol('SELECT `soundId` AS ARRAY_KEY, `gender` FROM ?_races_sounds WHERE `raceId` = ?d', $this->typeId))
        {
            $sounds = new SoundList(array(['id', array_keys($vo)]));
            if (!$sounds->error)
            {
                $this->extendGlobalData($sounds->getJSGlobals(GLOBALINFO_SELF));
                $data = $sounds->getListviewData();
                foreach ($data as $id => &$d)
                    $d['gender'] = $vo[$id];

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $data,
                    'extraCols' => ['$Listview.templates.title.columns[1]']
                ), SoundList::$brickFile));
            }
        }

        // tab: criteria-of
        $conditions = array(
            'AND',
            ['ac.type', ACHIEVEMENT_CRITERIA_TYPE_HK_RACE],
            ['ac.value1', $this->typeId]
        );

        if ($extraCrt = DB::World()->selectCol('SELECT `criteria_id` FROM achievement_criteria_data WHERE `type` IN (?a) AND `value2` = ?d', [ACHIEVEMENT_CRITERIA_DATA_TYPE_S_PLAYER_CLASS_RACE, ACHIEVEMENT_CRITERIA_DATA_TYPE_T_PLAYER_CLASS_RACE], $this->typeId))
            $conditions = ['OR', $conditions, ['ac.id', $extraCrt]];

        $crtOf = new AchievementList($conditions);
        if (!$crtOf->error)
        {
            $this->extendGlobalData($crtOf->getJSGlobals());

            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $crtOf->getListviewData(),
                'name' => '$LANG.tab_criteriaof',
                'id'   => 'criteria-of'
            ), AchievementList::$brickFile));
        }

        // tab: condition-for
        $cnd = new Conditions();
        $cnd->getByCondition(Type::CHR_RACE, $this->typeId)->prepare();
        if ($tab = $cnd->toListviewTab('condition-for', '$LANG.tab_condition_for'))
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }

        parent::generate();
    }
}

?>
