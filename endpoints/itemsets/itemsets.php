<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemsetsBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type        = Type::ITEMSET;
    protected  int    $cacheType   = CACHE_TYPE_LIST_PAGE;

    protected  string $template    = 'itemsets';
    protected  string $pageName    = 'itemsets';
    protected ?int    $activeTab   = parent::TAB_DATABASE;
    protected  array  $breadcrumb  = [0, 2];

 // protected  array  $dataLoader  = ['weight-presets'];    // was here since day 1, but was never accessed..?
    protected  array  $scripts     = [[SC_JS_FILE, 'js/filters.js']];
    protected  array  $expectedGET = array(
        'filter' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Filter::PATTERN_PARAM]]
    );

    public function __construct(string $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageParam);

        $this->subCat = $pageParam !== '' ? '='.$pageParam : '';
        $this->filter = new ItemsetListFilter($this->_get['filter'] ?? '', ['parentCats' => $this->category]);
        if ($this->filter->shouldReload)
        {
            $_SESSION['error']['fi'] = $this->filter::class;
            $get = $this->filter->buildGETParam();
            $this->forward('?' . $this->pageName . $this->subCat . ($get ? '&filter=' . $get : ''));
        }
        $this->filterError = $this->filter->error;
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucWords(Lang::game('itemsets'));

        $conditions = [Listview::DEFAULT_SIZE];
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($_ = $this->filter->getConditions())
            $conditions[] = $_;


        /*************/
        /* Menu Path */
        /*************/

        if ($cl = $this->filter->values['cl'])
            $this->breadcrumb[] = $cl;


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1);

        if ($cl = $this->filter->values['cl'])
            array_unshift($this->title, Lang::game('cl', $cl));


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons[BUTTON_WOWHEAD] = true;
        if ($fiQuery = $this->filter->buildGETParam())
            $this->wowheadLink .= '&filter='.$fiQuery;

        $itemsets = new ItemsetList($conditions, ['calcTotal' => true]);
        $this->extendGlobalData($itemsets->getJSGlobals());

        $tabData = ['data' => $itemsets->getListviewData()];

        if ($this->filter->fiExtraCols)
            $tabData['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

        // create note if search limit was exceeded
        if ($itemsets->getMatches() > Listview::DEFAULT_SIZE)
        {
            $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_itemsetsfound', $itemsets->getMatches(), Listview::DEFAULT_SIZE);
            $tabData['_truncated'] = 1;
        }

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview($tabData, ItemsetList::$brickFile));

        parent::generate();

        $this->setOnCacheLoaded([self::class, 'onBeforeDisplay']);
    }

    public static function onBeforeDisplay() : void
    {
        // sort for dropdown-menus
        Lang::sort('itemset', 'notes', SORT_NATURAL);
        Lang::sort('game', 'cl');
    }
}

?>
