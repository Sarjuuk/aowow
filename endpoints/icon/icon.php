<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class IconBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'icon';
    protected  string $pageName   = 'icon';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 31];

    public int    $type   = Type::ICON;
    public int    $typeId = 0;
    public string $icon   = '';

    private IconEntry $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new IconEntry($this->typeId);
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('icon'), Lang::icon('notFound'));

        $this->subject->setIconCounts();

        $this->extendGlobalData($this->subject->getJSGlobal());

        $this->h1   = $this->subject->name_source;
        $this->icon = $this->subject->name;

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );


        /*************/
        /* Menu Path */
        /*************/

        $catgs = array_filter(array(
            1  => $this->subject->nItems,
            2  => $this->subject->nSpells,
            3  => $this->subject->nAchievements,
            6  => $this->subject->nCurrencies,
            9  => $this->subject->nPets,
            11 => $this->subject->nClasses
        ));

        if (count($catgs) == 1)
            $this->breadcrumb[] = key($catgs);


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucFirst(Lang::game('icon')));


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons = array(
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_WOWHEAD => false
        );


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // used by: spell
        $ubSpells = new SpellContainer(array(['iconId', $this->typeId]));
        if (!$ubSpells->error)
        {
            $this->extendGlobalData($ubSpells->getJSGlobals(GLOBALINFO_RELATED | GLOBALINFO_SELF));
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubSpells->getListviewData(),
                'id'   => 'used-by-spell'
            ), SpellEntry::$brickFile));
        }

        // used by: item
        $ubItems = new ItemContainer(array(['iconId', $this->typeId]));
        if (!$ubItems->error)
        {
            $this->extendGlobalData($ubItems->getJsGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubItems->getListviewData(),
                'id'   => 'used-by-item'
            ), ItemEntry::$brickFile));
        }

        // used by: achievement
        $ubAchievements = new AchievementContainer(array(['iconId', $this->typeId]));
        if (!$ubAchievements->error)
        {
            $this->extendGlobalData($ubAchievements->getJSGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubAchievements->getListviewData(),
                'id'   => 'used-by-achievement'
            ), AchievementEntry::$brickFile));
        }

        // used by: currency
        $ubCurrencies = new CurrencyContainer(array(['iconId', $this->typeId]));
        if (!$ubCurrencies->error)
        {
            $this->extendGlobalData($ubCurrencies->getJSGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubCurrencies->getListviewData(),
                'id'   => 'used-by-currency'
            ), CurrencyEntry::$brickFile));
        }

        // used by: hunter pet
        $ubPets = new PetContainer(array(['iconId', $this->typeId]));
        if (!$ubPets->error)
        {
            $this->extendGlobalData($ubPets->getJsGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubPets->getListviewData(),
                'id'   => 'used-by-pet'
            ), PetEntry::$brickFile));
        }

        // used by: player class
        $ubClasses = new CharClassContainer(array(['iconId', $this->typeId]));
        if (!$ubClasses->error)
        {
            $this->extendGlobalData($ubClasses->getJSGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubClasses->getListviewData(),
                'id'   => 'used-by-class'
            ), CharClassEntry::$brickFile));
        }

        parent::generate();
    }
}

?>
