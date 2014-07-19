<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 13: Race     g_initPath()
//  tabId  0: Database g_initHeader()
class RacePage extends GenericPage
{
    use DetailPage;

    protected $type          = TYPE_RACE;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 13];
    protected $tabId         = 0;
    protected $mode          = CACHETYPE_PAGE;
    protected $js            = ['swfobject.js'];

    public function __construct($__, $id)
    {
        parent::__construct();

        $this->typeId = intVal($id);

        $this->subject = new CharRaceList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::$game['race']);

        $this->name = $this->subject->getField('name', true);
    }

    protected function generatePath()
    {
        $this->path[] = $this->typeId;
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->subject->getField('name', true), Util::ucFirst(Lang::$game['class']));
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
            $infobox[] = Lang::$main['side'].Lang::$main['colon'].'[span class=icon-'.($_ == 2 ? 'horde' : 'alliance').']'.Lang::$game['si'][$_].'[/span]';

        // faction
        if ($_ = $this->subject->getField('factionId'))
        {
            $fac = new FactionList(array(['ft.id', $_]));
            $this->extendGlobalData($fac->getJSGlobals());
            $infobox[] = Util::ucFirst(Lang::$game['faction']).Lang::$main['colon'].'[faction='.$fac->id.']';
        }

        // leader
        if ($_ = $this->subject->getField('leader'))
        {
            $this->extendGlobalIds(TYPE_NPC, $_);
            $infobox[] = Lang::$class['racialLeader'].Lang::$main['colon'].'[npc='.$_.']';
        }

        // start area
        if ($_ = $this->subject->getField('startAreaId'))
        {
            $this->extendGlobalIds(TYPE_ZONE, $_);
            $infobox[] = Lang::$class['startZone'].Lang::$main['colon'].'[zone='.$_.']';
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
            BUTTON_LINKS   => true
        );


        /**************/
        /* Extra Tabs */
        /**************/

        // Classes
        $classes = new CharClassList(array(['racemask', $_mask, '&']));
        if (!$classes->error)
        {
            $this->extendGlobalData($classes->getJSGlobals());
            $this->lvTabs[] = array(
                'file'   => 'class',
                'data'   => $classes->getListviewData(),
                'params' => []
            );
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
            $this->lvTabs[] = array(
                'file'   => 'spell',
                'data'   => $tongues->getListviewData(),
                'params' => array(
                    'id'          => 'languages',
                    'name'        => '$LANG.tab_languages',
                    'hiddenCols'  => "$['reagents']"
                )
            );
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
            $this->lvTabs[] = array(
                'file'   => 'spell',
                'data'   => $racials->getListviewData(),
                'params' => array(
                    'id'          => 'racial-traits',
                    'name'        => '$LANG.tab_racialtraits',
                    'hiddenCols'  => "$['reagents']"
                )
            );
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
            $this->lvTabs[] = array(
                'file'   => 'quest',
                'data'   => $quests->getListviewData(),
                'params' => []
            );
        }

        // Mounts
        // ok, this sucks, but i rather hardcode the trainer, than fetch items by namepart
        $items = isset($mountVendors[$this->typeId]) ? DB::Aowow()->selectCol('SELECT item FROM npc_vendor WHERE entry IN (?a)', $mountVendors[$this->typeId]) : 0;

        $conditions = array(
            ['i.id', $items],
            ['i.class', ITEM_CLASS_MISC],
            ['i.subClass', 5],                              // mounts
        );

        $mounts = new ItemList($conditions);
        if (!$mounts->error)
        {
            $this->extendGlobalData($mounts->getJSGlobals());
            $this->lvTabs[] = array(
                'file'   => 'item',
                'data'   => $mounts->getListviewData(),
                'params' => array(
                    'id'         => 'mounts',
                    'name'       => '$LANG.tab_mounts',
                    'hiddenCols' => "$['slot', 'type']"
                )
            );
        }
    }
}


?>
