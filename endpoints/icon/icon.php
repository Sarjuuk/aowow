<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class IconBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_PAGE;

    protected  string $template   = 'icon';
    protected  string $pageName   = 'icon';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 31];

    public int    $type   = Type::ICON;
    public int    $typeId = 0;
    public string $icon   = '';

    private IconList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new IconList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('icon'), Lang::icon('notFound'));

        $this->extendGlobalData($this->subject->getJSGlobals());

        $this->h1   = $this->subject->getField('name');
        $this->icon = $this->subject->getField('name', true, true);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );


        /*************/
        /* Menu Path */
        /*************/

        $cats  = [1 => 'nItems', 2 => 'nSpells', 3 => 'nAchievements', 6 => 'nCurrencies', 9 => 'nPets'/* , 11 => '' */];
        $crumb = '';
        foreach ($cats as $cat => $field)
        {
            if (!$this->subject->getField($field))
                continue;

            if ($crumb)
            {
                $crumb = 0;
                break;
            }

            $crumb = $cat;
        }

        if ($crumb)
            $this->breadcrumb[] = $crumb;


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
        $ubSpells = new SpellList(array(['iconId', $this->typeId]));
        if (!$ubSpells->error)
        {
            $this->extendGlobalData($ubSpells->getJsGlobals(GLOBALINFO_RELATED | GLOBALINFO_SELF));
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubSpells->getListviewData(),
                'id'   => 'used-by-spell'
            ), SpellList::$brickFile));
        }

        // used by: item
        $ubItems = new ItemList(array(['iconId', $this->typeId]));
        if (!$ubItems->error)
        {
            $this->extendGlobalData($ubItems->getJsGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubItems->getListviewData(),
                'id'   => 'used-by-item'
            ), ItemList::$brickFile));
        }

        // used by: achievement
        $ubAchievements = new AchievementList(array(['iconId', $this->typeId]));
        if (!$ubAchievements->error)
        {
            $this->extendGlobalData($ubAchievements->getJsGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubAchievements->getListviewData(),
                'id'   => 'used-by-achievement'
            ), AchievementList::$brickFile));
        }

        // used by: currency
        $ubCurrencies = new CurrencyList(array(['iconId', $this->typeId]));
        if (!$ubCurrencies->error)
        {
            $this->extendGlobalData($ubCurrencies->getJsGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubCurrencies->getListviewData(),
                'id'   => 'used-by-currency'
            ), CurrencyList::$brickFile));
        }

        // used by: hunter pet
        $ubPets = new PetList(array(['iconId', $this->typeId]));
        if (!$ubPets->error)
        {
            $this->extendGlobalData($ubPets->getJsGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $ubPets->getListviewData(),
                'id'   => 'used-by-pet'
            ), PetList::$brickFile));
        }

        parent::generate();
    }
}

?>
