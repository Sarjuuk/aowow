<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AreatriggerBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache, TrSpawns;

    protected  int    $cacheType         = CACHE_TYPE_DETAIL_PAGE;
    protected  int    $requiredUserGroup = U_GROUP_STAFF;

    protected  string $template          = 'detail-page-generic';
    protected  string $pageName          = 'areatrigger';
    protected ?int    $activeTab         = parent::TAB_DATABASE;
    protected  array  $breadcrumb        = [0, 102];

    public int $type   = Type::AREATRIGGER;
    public int $typeId = 0;

    private AreaTrigger $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new AreaTrigger($this->typeId);
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('areatrigger'), Lang::areatrigger('notFound'));

        $this->h1 = $this->subject->name;

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );

        $_type = $this->subject->type;


        /*************/
        /* Menu Path */
        /*************/

        $this->breadcrumb[] = $_type;


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucFirst(Lang::game('areatrigger')));


        /****************/
        /* Main Content */
        /****************/

        // get spawns
        if ($spawns = self::createFullSpawns($this->subject))
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
            $sai = new SmartAI(SmartAI::SRC_TYPE_AREATRIGGER, $this->typeId, ['teleportTargetArea' => $this->subject->location]);
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
        $cnd->getBySource(Conditions::SRC_AREATRIGGER_CLIENT, entry: $this->typeId)->prepare();
        if ($tab = $cnd->toListviewTab())
        {
            $this->extendGlobalData($cnd->getJSGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }

        if ($_type == AT_TYPE_OBJECTIVE)
        {
            $relQuest = new QuestList(array(['id', $this->subject->quest]));
            if (!$relQuest->error)
            {
                $this->extendGlobalData($relQuest->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));
                $this->lvTabs->addListviewTab(new Listview(['data' => $relQuest->getListviewData()], QuestList::$brickFile));
            }
        }
        else if ($_type == AT_TYPE_TELEPORT)
        {
            $relZone = new ZoneList(array(['id', $this->subject->location]));
            if (!$relZone->error)
                $this->lvTabs->addListviewTab(new Listview(['data' => $relZone->getListviewData()], ZoneList::$brickFile));
        }
        else if ($_type == AT_TYPE_SCRIPT)
        {
            $relTrigger = new AreaTriggerContainer(array(['id', $this->typeId, '!'], ['name', $this->subject->name]));
            if (!$relTrigger->error)
                $this->lvTabs->addListviewTab(new Listview(['data' => $relTrigger->getListviewData(), 'name' => Util::ucFirst(Lang::game('areatrigger'))], AreaTrigger::$brickFile, 'areatrigger'));
        }

        parent::generate();
    }
}

?>
