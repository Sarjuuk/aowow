<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 31: Icons    g_initPath()
//  tabId  0: Database g_initHeader()
class IconPage extends GenericPage
{
    use TrDetailPage;

    protected $icon          = '';

    protected $type          = Type::ICON;
    protected $typeId        = 0;
    protected $tpl           = 'icon';
    protected $path          = [0, 31];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        $this->typeId = intVal($id);

        $this->subject = new IconList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('icon'), Lang::icon('notFound'));

        $this->extendGlobalData($this->subject->getJSGlobals());

        $this->name = $this->subject->getField('name');
        $this->icon = $this->subject->getField('name', true, true);
    }

    protected function generateContent()
    {
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

        // used by: spell
        $ubSpells = new SpellList(array(['iconId', $this->typeId]));
        if (!$ubSpells->error)
        {
            $this->extendGlobalData($ubSpells->getJsGlobals(GLOBALINFO_RELATED | GLOBALINFO_SELF));
            $this->lvTabs[] = [SpellList::$brickFile, array(
                'data' => array_values($ubSpells->getListviewData()),
                'id'   => 'used-by-spell'
            )];
        }

        // used by: item
        $ubItems = new ItemList(array(['iconId', $this->typeId]));
        if (!$ubItems->error)
        {
            $this->extendGlobalData($ubItems->getJsGlobals());
            $this->lvTabs[] = [ItemList::$brickFile, array(
                'data' => array_values($ubItems->getListviewData()),
                'id'   => 'used-by-item'
            )];
        }

        // used by: achievement
        $ubAchievements = new AchievementList(array(['iconId', $this->typeId]));
        if (!$ubAchievements->error)
        {
            $this->extendGlobalData($ubAchievements->getJsGlobals());
            $this->lvTabs[] = [AchievementList::$brickFile, array(
                'data' => array_values($ubAchievements->getListviewData()),
                'id'   => 'used-by-achievement'
            )];
        }

        // used by: currency
        $ubCurrencies = new CurrencyList(array(['iconId', $this->typeId]));
        if (!$ubCurrencies->error)
        {
            $this->extendGlobalData($ubCurrencies->getJsGlobals());
            $this->lvTabs[] = [CurrencyList::$brickFile, array(
                'data' => array_values($ubCurrencies->getListviewData()),
                'id'   => 'used-by-currency'
            )];
        }

        // used by: hunter pet
        $ubPets = new PetList(array(['iconId', $this->typeId]));
        if (!$ubPets->error)
        {
            $this->extendGlobalData($ubPets->getJsGlobals());
            $this->lvTabs[] = [PetList::$brickFile, array(
                'data' => array_values($ubPets->getListviewData()),
                'id'   => 'used-by-pet'
            )];
        }
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('icon')));
    }

    protected function generatePath() { }
}

?>
