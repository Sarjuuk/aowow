<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AreatriggerBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType         = CACHE_TYPE_DETAIL_PAGE;
    protected  int    $requiredUserGroup = U_GROUP_STAFF;

    protected  string $template          = 'detail-page-generic';
    protected  string $pageName          = 'areatrigger';
    protected ?int    $activeTab         = parent::TAB_DATABASE;
    protected  array  $breadcrumb        = [0, 102];

    public int $type   = Type::AREATRIGGER;
    public int $typeId = 0;

    private AreaTriggerList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new AreaTriggerList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('areatrigger'), Lang::areatrigger('notFound'));

        $this->h1 = $this->subject->getField('name') ?: 'Areatrigger #'.$this->typeId;

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );


        /*************/
        /* Menu Path */
        /*************/

        $this->breadcrumb[] = $this->subject->getField('type');


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucFirst(Lang::game('areatrigger')));


        /****************/
        /* Main Content */
        /****************/

        $_type = $this->subject->getField('type');

        // get spawns
        if ($spawns = $this->subject->getSpawns(SPAWNINFO_FULL))
        {
            $this->addDataLoader('zones');
            $this->map = array(
                ['parent' => 'mapper-generic'],             // Mapper
                $spawns,                                    // mapperData
                null,                                       // ShowOnMap
                [Lang::areatrigger('foundIn')]              // foundIn
            );
            foreach ($spawns as $areaId => $_)
                $this->map[3][$areaId] = ZoneList::getName($areaId);
        }

        // Smart AI
        if ($_type == AT_TYPE_SMART)
        {
            $sai = new SmartAI(SmartAI::SRC_TYPE_AREATRIGGER, $this->typeId, ['teleportTargetArea' => $this->subject->getField('areaId')]);
            if ($sai->prepare())
            {
                $this->extendGlobalData($sai->getJSGlobals());
                $this->smartAI = $sai->getMarkup();
            }
        }

        $this->redButtons = array(
            BUTTON_LINKS   => false,
            BUTTON_WOWHEAD => false
        );


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: conditions
        $cnd = new Conditions();
        $cnd->getBySourceEntry($this->typeId, Conditions::SRC_AREATRIGGER_CLIENT)->prepare();
        if ($tab = $cnd->toListviewTab())
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }

        if ($_type == AT_TYPE_OBJECTIVE)
        {
            $relQuest = new QuestList(array(['id', $this->subject->getField('quest')]));
            if (!$relQuest->error)
            {
                $this->extendGlobalData($relQuest->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));
                $this->lvTabs->addListviewTab(new Listview(['data' => $relQuest->getListviewData()], QuestList::$brickFile));
            }
        }
        else if ($_type == AT_TYPE_TELEPORT)
        {
            $relZone = new ZoneList(array(['id', $this->subject->getField('areaId')]));
            if (!$relZone->error)
                $this->lvTabs->addListviewTab(new Listview(['data' => $relZone->getListviewData()], ZoneList::$brickFile));
        }
        else if ($_type == AT_TYPE_SCRIPT)
        {
            $relTrigger = new AreaTriggerList(array(['id', $this->typeId, '!'], ['name', $this->subject->getField('name')]));
            if (!$relTrigger->error)
                $this->lvTabs->addListviewTab(new Listview(['data' => $relTrigger->getListviewData(), 'name' => Util::ucFirst(Lang::game('areatrigger'))]), AreaTriggerList::$brickFile, 'areatrigger');
        }

        parent::generate();
    }
}

?>
