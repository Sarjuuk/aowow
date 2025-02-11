<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 102: Areatrigger g_initPath()
//  tabid   0: Database    g_initHeader()
class AreaTriggerPage extends GenericPage
{
    use TrDetailPage;

    protected $type          = Type::AREATRIGGER;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 102];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $reqUGroup     = U_GROUP_STAFF;

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        $this->typeId = intVal($id);

        $this->subject = new AreaTriggerList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('areatrigger'), Lang::areatrigger('notFound'));

        $this->name = $this->subject->getField('name') ?: 'AT #'.$this->typeId;
    }

    protected function generatePath()
    {
        $this->path[] = $this->subject->getField('type');
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('areatrigger')));
    }

    protected function generateContent()
    {
        $this->addScript([SC_JS_FILE, '?data=zones']);

        $_type = $this->subject->getField('type');


        /****************/
        /* Main Content */
        /****************/

        // get spawns
        $map = null;
        if ($spawns = $this->subject->getSpawns(SPAWNINFO_FULL))
        {
            $map = ['data' => ['parent' => 'mapper-generic'], 'mapperData' => &$spawns];
            foreach ($spawns as $areaId => &$areaData)
                $map['extra'][$areaId] = ZoneList::getName($areaId);
        }

        // smart AI
        $sai = null;
        if ($_type == AT_TYPE_SMART)
        {
            $sai = new SmartAI(SmartAI::SRC_TYPE_AREATRIGGER, $this->typeId, ['teleportTargetArea' => $this->subject->getField('areaId')]);
            if ($sai->prepare())
                $this->extendGlobalData($sai->getJSGlobals());
        }

        $this->map        = $map;
        $this->infobox    = false;
        $this->smartAI    = $sai?->getMarkdown();
        $this->redButtons = array(
            BUTTON_LINKS   => false,
            BUTTON_WOWHEAD => false
        );


        /**************/
        /* Extra Tabs */
        /**************/

        // tab: conditions
        $cnd = new Conditions();
        $cnd->getBySourceEntry($this->typeId, Conditions::SRC_AREATRIGGER_CLIENT)->prepare();
        if ($tab = $cnd->toListviewTab())
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs[] = $tab;
        }

        if ($_type == AT_TYPE_OBJECTIVE)
        {
            $relQuest = new QuestList(array(['id', $this->subject->getField('quest')]));
            if (!$relQuest->error)
            {
                $this->extendGlobalData($relQuest->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));
                $this->lvTabs[] = [QuestList::$brickFile, ['data' => array_values($relQuest->getListviewData())]];
            }
        }
        else if ($_type == AT_TYPE_TELEPORT)
        {
            $relZone = new ZoneList(array(['id', $this->subject->getField('areaId')]));
            if (!$relZone->error)
            {
                $this->lvTabs[] = [ZoneList::$brickFile, ['data' => array_values($relZone->getListviewData())]];
            }
        }
        else if ($_type == AT_TYPE_SCRIPT)
        {
            $relTrigger = new AreaTriggerList(array(['id', $this->typeId, '!'], ['name', $this->subject->getField('name')]));
            if (!$relTrigger->error)
            {
                $this->lvTabs[] = [AreaTriggerList::$brickFile, ['data' => array_values($relTrigger->getListviewData()), 'name' => Util::ucFirst(Lang::game('areatrigger'))], 'areatrigger'];
            }
        }
    }
}

?>
