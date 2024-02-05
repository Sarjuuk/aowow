<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 2: Itemset  g_initPath()
//  tabId 0: Database g_initHeader()
class ItemsetsPage extends GenericPage
{
    use TrListPage;

    protected $type     = Type::ITEMSET;
    protected $tpl      = 'itemsets';
    protected $path     = [0, 2];
    protected $tabId    = 0;
    protected $mode     = CACHE_TYPE_PAGE;
    protected $scripts  = [[SC_JS_FILE, 'js/filters.js']];

    protected $_get          = ['filter' => ['filter' => FILTER_UNSAFE_RAW]];

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);
        $this->filterObj = new ItemsetListFilter(false, ['parentCats' => $this->category]);

        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('itemsets'));
    }

    protected function generateContent()
    {
        $this->addScript([SC_JS_FILE, '?data=weight-presets']);

        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        $itemsets = new ItemsetList($conditions);
        $this->extendGlobalData($itemsets->getJSGlobals());

        // recreate form selection
        $this->filter             = $this->filterObj->getForm();
        $this->filter['query']    = $this->_get['filter'];
        $this->filter['initData'] = ['init' => 'itemsets'];

        if ($x = $this->filterObj->getSetCriteria())
            $this->filter['initData']['sc'] = $x;

        $xCols = $this->filterObj->getExtraCols();
        if ($xCols)
            $this->filter['initData']['ec'] = $xCols;

        $tabData = ['data' => array_values($itemsets->getListviewData())];

        if ($xCols)
            $tabData['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

        // create note if search limit was exceeded
        if ($itemsets->getMatches() > CFG_SQL_LIMIT_DEFAULT)
        {
            $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_itemsetsfound', $itemsets->getMatches(), CFG_SQL_LIMIT_DEFAULT);
            $tabData['_truncated'] = 1;
        }

        if ($this->filterObj->error)
            $tabData['_errors'] = 1;

        $this->lvTabs[] = [ItemsetList::$brickFile, $tabData];
    }

    protected function postCache()
    {
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
