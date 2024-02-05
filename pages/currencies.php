<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 15: Currency g_initPath()
//  tabId  0: Database g_initHeader()
class CurrenciesPage extends GenericPage
{
    use TrListPage;

    protected $type          = Type::CURRENCY;
    protected $tpl           = 'list-page-generic';
    protected $path          = [0, 15];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $validCats     = [1, 2, 3, 22];

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('currencies'));
    }

    protected function generateContent()
    {
        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
            $conditions[] = ['category', (int)$this->category[0]];

        $money = new CurrencyList($conditions);
        $this->lvTabs[] = [CurrencyList::$brickFile, ['data' => array_values($money->getListviewData())]];
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
        if ($this->category)
            array_unshift($this->title, Lang::currency('cat', $this->category[0]));
    }

    protected function generatePath()
    {
        if ($this->category)
            $this->path[] = $this->category[0];
    }
}

?>
