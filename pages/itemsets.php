<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 2: Itemset  g_initPath()
//  tabId 0: Database g_initHeader()
class ItemsetsPage extends GenericPage
{
    use ListPage;

    protected $type     = TYPE_ITEMSET;
    protected $tpl      = 'itemsets';
    protected $path     = [0, 2];
    protected $tabId    = 0;
    protected $mode     = CACHE_TYPE_PAGE;
    protected $js       = ['filters.js'];

    public function __construct($pageCall, $pageParam)
    {
        $this->filterObj = new ItemsetListFilter();
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('itemsets'));
    }

    protected function generateContent()
    {
        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        $itemsets = new ItemsetList($conditions);
        $this->extendGlobalData($itemsets->getJSGlobals());

        // recreate form selection
        $this->filter = array_merge($this->filterObj->getForm('form'), $this->filter);
        $this->filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : NULL;
        $this->filter['fi']    =  $this->filterObj->getForm();

        $this->addJS('?data=weight-presets&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        $tabData = ['data' => array_values($itemsets->getListviewData())];

        if (!empty($this->filter['fi']['extraCols']))
            $tabData['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

        // create note if search limit was exceeded
        if ($itemsets->getMatches() > CFG_SQL_LIMIT_DEFAULT)
        {
            $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_itemsetsfound', $itemsets->getMatches(), CFG_SQL_LIMIT_DEFAULT);
            $tabData['_truncated'] = 1;
        }

        if ($this->filterObj->error)
            $tabData['_errors'] = 1;

        $this->lvTabs[] = ['itemset', $tabData];

        // sort for dropdown-menus
        Lang::sort('itemset', 'notes', SORT_NATURAL);
        Lang::sort('game', 'si');
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);

        $form = $this->filterObj->getForm('form');
        if (isset($form['cl']))
            array_unshift($this->title, Lang::game('cl', $form['cl']));
    }

    protected function generatePath()
    {
        $form = $this->filterObj->getForm('form');
        if (isset($form['cl']))
            $this->path[] = $form['cl'];
    }
}

?>
