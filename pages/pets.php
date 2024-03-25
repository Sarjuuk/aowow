<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 8: Pets     g_initPath()
//  tabid 0: Database g_initHeader()
class PetsPage extends GenericPage
{
    use TrListPage;

    protected $type          = Type::PET;
    protected $tpl           = 'list-page-generic';
    protected $path          = [0, 8];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $validCats     = [0, 1, 2];

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('pets'));
    }

    protected function generateContent()
    {
        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
            $conditions[] = ['type', (int)$this->category[0]];

        $data = [];
        $pets = new PetList($conditions);
        if (!$pets->error)
        {
            $this->extendGlobalData($pets->getJSGlobals(GLOBALINFO_RELATED));

            $data = array(
                'data'            => array_values($pets->getListviewData()),
                'visibleCols'     => ['abilities'],
                'computeDataFunc' => '$_'
            );

            if (!$pets->hasDiffFields(['type']))
                $data['hiddenCols'] = ['type'];
        };
        $this->lvTabs[] = [PetList::$brickFile, $data, 'petFoodCol'];
    }

    protected function generateTitle()
    {
        array_unshift($this->title, Util::ucFirst(Lang::game('pets')));
        if ($this->category)
            array_unshift($this->title, Lang::pet('cat', $this->category[0]));
    }

    protected function generatePath()
    {
        if ($this->category)
            $this->path[] = $this->category[0];
    }
}

?>
