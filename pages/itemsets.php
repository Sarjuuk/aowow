<?php

namespace Aowow;

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

        parent::__construct($pageCall, $pageParam);

        $this->filterObj = new ItemsetListFilter($this->_get['filter'] ?? '', ['parentCats' => $this->category]);

        $this->name = Util::ucFirst(Lang::game('itemsets'));
    }

    protected function generateContent()
    {
        $this->addScript([SC_JS_FILE, '?data=weight-presets']);

        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        $this->filterObj->evalCriteria();

        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        $itemsets = new ItemsetList($conditions, ['calcTotal' => true]);
        $this->extendGlobalData($itemsets->getJSGlobals());

        $xCols = $this->filterObj->fiExtraCols;
        if ($xCols)
            $this->filter['initData']['ec'] = $xCols;

        $tabData = ['data' => array_values($itemsets->getListviewData())];

        if ($xCols)
            $tabData['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

        // create note if search limit was exceeded
        if ($itemsets->getMatches() > Cfg::get('SQL_LIMIT_DEFAULT'))
        {
            $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_itemsetsfound', $itemsets->getMatches(), Cfg::get('SQL_LIMIT_DEFAULT'));
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

        $form = $this->filterObj->values;
        if ($form['cl'])
            array_unshift($this->title, Lang::game('cl', $form['cl']));
    }

    protected function generatePath()
    {
        $form = $this->filterObj->values;
        if ($form['cl'])
            $this->path[] = $form['cl'];
    }
}

?>
