<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CurrenciesBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type       = Type::CURRENCY;
    protected  int    $cacheType  = CACHE_TYPE_LIST_PAGE;

    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'currencies';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 15];

    protected  array  $validCats  = [1, 2, 3, 22];

    public function __construct(string $rawParam)
    {
        $this->getCategoryFromUrl($rawParam);

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('currencies'));


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1);
        if ($this->category)
            array_unshift($this->title, Lang::currency('cat', $this->category[0]));


        /*************/
        /* Menu Path */
        /*************/

        if ($this->category)
            $this->breadcrumb[] = $this->category[0];


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons[BUTTON_WOWHEAD] = true;

        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
            $conditions[] = ['category', $this->category[0]];

        $money = new CurrencyList($conditions);

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview(['data' => $money->getListviewData()], CurrencyList::$brickFile));

        parent::generate();
    }
}

?>
