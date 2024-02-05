<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 13: Race     g_initPath()
//  tabId  0: Database g_initHeader()
class RacePage extends GenericPage
{
    use TrDetailPage;

    protected $type          = Type::CHR_RACE;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 13];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $scripts       = [[SC_JS_FILE, 'js/swfobject.js']];

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        $this->typeId = intVal($id);

        $this->subject = new CharRaceList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('race'), Lang::race('notFound'));

        $this->name = $this->subject->getField('name', true);
    }

    protected function generatePath()
    {
        $this->path[] = $this->typeId;
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->subject->getField('name', true), Util::ucFirst(Lang::game('class')));
    }

    protected function generateContent()
    {
        $infobox      = [];
        $_mask        = 1 << ($this->typeId - 1);
        $mountVendors = array(                              // race => [starter, argent tournament]
            null,           [384,   33307], [3362,  33553], [1261,  33310],
            [4730,  33653], [4731,  33555], [3685,  33556], [7955,  33650],
            [7952,  33554], null,           [16264, 33557], [17584, 33657]
        );

        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // side
        if ($_ = $this->subject->getField('side'))
            $infobox[] = Lang::main('side').Lang::main('colon').'[span class=icon-'.($_ == 2 ? 'horde' : 'alliance').']'.Lang::game('si', $_).'[/span]';

        // faction
        if ($_ = $this->subject->getField('factionId'))
        {
            $fac = new FactionList(array(['f.id', $_]));
            $this->extendGlobalData($fac->getJSGlobals());
            $infobox[] = Util::ucFirst(Lang::game('faction')).Lang::main('colon').'[faction='.$fac->id.']';
        }

        // leader
        if ($_ = $this->subject->getField('leader'))
        {
            $this->extendGlobalIds(Type::NPC, $_);
            $infobox[] = Lang::race('racialLeader').Lang::main('colon').'[npc='.$_.']';
        }

        // start area
        if ($_ = $this->subject->getField('startAreaId'))
        {
            $this->extendGlobalIds(Type::ZONE, $_);
            $infobox[] = Lang::race('startZone').Lang::main('colon').'[zone='.$_.']';
        }


        /****************/
        /* Main Content */
        /****************/

        $this->infobox    = '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]';
        $this->expansion  = Util::$expansionString[$this->subject->getField('expansion')];
        $this->headIcons  = array(
            'race_'.strtolower($this->subject->getField('fileString')).'_male',
            'race_'.strtolower($this->subject->getField('fileString')).'_female'
        );
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId]
        );


        /**************/
        /* Extra Tabs */
        /**************/

        // Classes
        $classes = new CharClassList(array(['racemask', $_mask, '&']));
        if (!$classes->error)
        {
            $this->extendGlobalData($classes->getJSGlobals());
            $this->lvTabs[] = [CharClassList::$brickFile, ['data' => array_values($classes->getListviewData())]];
        }

        // Tongues
        $conditions = array(
            ['typeCat', -11],                               // proficiencies
            ['reqRaceMask', $_mask, '&']                    // only languages are race-restricted
        );

        $tongues = new SpellList($conditions);
        if (!$tongues->error)
        {
            $this->extendGlobalData($tongues->getJSGlobals());
            $this->lvTabs[] = [SpellList::$brickFile, array(
                'data'       => array_values($tongues->getListviewData()),
                'id'         => 'languages',
                'name'       => '$LANG.tab_languages',
                'hiddenCols' => ['reagents']
            )];
        }

        // Racials
        $conditions = array(
            ['typeCat', -4],                               // racial traits
            ['reqRaceMask', $_mask, '&']
        );

        $racials = new SpellList($conditions);
        if (!$racials->error)
        {
            $this->extendGlobalData($racials->getJSGlobals());
            $this->lvTabs[] = [SpellList::$brickFile, array(
                'data'       => array_values($racials->getListviewData()),
                'id'         => 'racial-traits',
                'name'       => '$LANG.tab_racialtraits',
                'hiddenCols' => ['reagents']
            )];
        }

        // Quests
        $conditions = array(
            ['reqRaceMask', $_mask, '&'],
            [['reqRaceMask', RACE_MASK_HORDE, '&'], RACE_MASK_HORDE, '!'],
            [['reqRaceMask', RACE_MASK_ALLIANCE, '&'], RACE_MASK_ALLIANCE, '!']
        );

        $quests = new QuestList($conditions);
        if (!$quests->error)
        {
            $this->extendGlobalData($quests->getJSGlobals());
            $this->lvTabs[] = [QuestList::$brickFile, ['data' => array_values($quests->getListviewData())]];
        }

        // Mounts
        // ok, this sucks, but i rather hardcode the trainer, than fetch items by namepart
        $items = isset($mountVendors[$this->typeId]) ? DB::World()->selectCol('SELECT item FROM npc_vendor WHERE entry IN (?a)', $mountVendors[$this->typeId]) : 0;

        $conditions = array(
            ['i.id', $items],
            ['i.class', ITEM_CLASS_MISC],
            ['i.subClass', 5],                              // mounts
        );

        $mounts = new ItemList($conditions);
        if (!$mounts->error)
        {
            $this->extendGlobalData($mounts->getJSGlobals());
            $this->lvTabs[] = [ItemList::$brickFile, array(
                'data'       => array_values($mounts->getListviewData()),
                'id'         => 'mounts',
                'name'       => '$LANG.tab_mounts',
                'hiddenCols' => ['slot', 'type']
            )];
        }

        // Sounds
        if ($vo = DB::Aowow()->selectCol('SELECT soundId AS ARRAY_KEY, gender FROM ?_races_sounds WHERE raceId = ?d', $this->typeId))
        {
            $sounds = new SoundList(array(['id', array_keys($vo)]));
            if (!$sounds->error)
            {
                $this->extendGlobalData($sounds->getJSGlobals(GLOBALINFO_SELF));
                $data = $sounds->getListviewData();
                foreach ($data as $id => &$d)
                    $d['gender'] = $vo[$id];

                $this->lvTabs[] = [SoundList::$brickFile, array(
                    'data' => array_values($data),
                    'extraCols' => ['$Listview.templates.title.columns[1]']
                )];
            }
        }
    }
}


?>
