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
    protected $mode     = CACHETYPE_PAGE;
    protected $js       = ['filters.js'];

    public function __construct($pageCall, $pageParam)
    {
        $this->filterObj = new ItemsetListFilter();
        $this->getCategoryFromUrl($pageParam);

        parent::__construct();

        $this->name = Util::ucFirst(Lang::$game['itemsets']);
    }

    protected function generateContent()
    {
        $itemsets = new ItemsetList($this->filterObj->getConditions());
        $this->extendGlobalData($itemsets->getJSGlobals());

        // recreate form selection
        $this->filter = array_merge($this->filterObj->getForm('form'), $this->filter);
        $this->filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : NULL;
        $this->filter['fi']    =  $this->filterObj->getForm();

        $this->addJS('?data=weight-presets&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        $lv = array(
            'file'   => 'itemset',
            'data'   => $itemsets->getListviewData(),       // listview content
            'params' => []
        );

        if (!empty($this->filter['fi']['extraCols']))
            $lv['params']['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

        // create note if search limit was exceeded
        if ($itemsets->getMatches() > CFG_SQL_LIMIT_DEFAULT)
        {
            $lv['params']['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_itemsetsfound', $itemsets->getMatches(), CFG_SQL_LIMIT_DEFAULT);
            $lv['params']['_truncated'] = 1;
        }

        if ($this->filterObj->error)
            $lv['params']['_errors'] = '$1';

        $this->lvTabs[] = $lv;

        // sort for dropdown-menus
        asort(Lang::$itemset['notes'], SORT_NATURAL);
        asort(Lang::$game['cl']);
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);

        $form = $this->filterObj->getForm('form');
        if (isset($form['cl']))
            array_unshift($this->title, Lang::$game['cl'][$form['cl']]);
    }

    protected function generatePath()
    {
        $form = $this->filterObj->getForm('form');
        if (isset($form['cl']))
            $this->path[] = $form['cl'];
    }
}

?>
