<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 101: Enchantment g_initPath()
//  tabId   0: Database    g_initHeader()
class EnchantmentsPage extends GenericPage
{
    use TrListPage;

    protected $type          = Type::ENCHANTMENT;
    protected $tpl           = 'enchantments';
    protected $path          = [0, 101];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $scripts       = [[SC_JS_FILE, 'js/filters.js']];

    protected $_get          = ['filter' => ['filter' => FILTER_UNSAFE_RAW]];

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);;
        $this->filterObj = new EnchantmentListFilter(false, ['parentCats' => $this->category]);

        parent::__construct($pageCall, $pageParam);

        $this->name   = Util::ucFirst(Lang::game('enchantments'));
        $this->subCat = $pageParam !== '' ? '='.$pageParam : '';
    }

    protected function generateContent()
    {
        $tabData = array(
            'data' => [],
            'name' => Util::ucFirst(Lang::game('enchantments'))
        );

        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        $ench = new EnchantmentList($conditions);

        $tabData['data'] = array_values($ench->getListviewData());
        $this->extendGlobalData($ench->getJSGlobals());

        // recreate form selection
        $this->filter             = $this->filterObj->getForm();
        $this->filter['query']    = $this->_get['filter'];
        $this->filter['initData'] = ['init' => 'enchantments'];

        if ($x = $this->filterObj->getSetCriteria())
            $this->filter['initData']['sc'] = $x;

        $xCols = $this->filterObj->getExtraCols();
        foreach (Util::$itemFilter as $fiId => $str)
            if (array_column($tabData['data'], $str))
                $xCols[] = $fiId;

        if (array_column($tabData['data'], 'dmg'))
            $xCols[] = 34;

        if ($xCols)
            $this->filter['initData']['ec'] = array_values(array_unique($xCols));

        if ($xCols)
            $tabData['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

        if ($ench->getMatches() > CFG_SQL_LIMIT_DEFAULT)
        {
            $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_enchantmentsfound', $ench->getMatches(), CFG_SQL_LIMIT_DEFAULT);
            $tabData['_truncated'] = 1;
        }

        if (array_filter(array_column($tabData['data'], 'spells')))
            $tabData['visibleCols'] = ['trigger'];

        if (!$ench->hasSetFields(['skillLine']))
            $tabData['hiddenCols'] = ['skill'];

        if ($this->filterObj->error)
            $tabData['_errors'] = '$1';

        $this->lvTabs[] = [EnchantmentList::$brickFile, $tabData, 'enchantment'];
    }

    protected function generateTitle()
    {
        $form = $this->filterObj->getForm('form');
        if (!empty($form['ty']) && intVal($form['ty']) && $form['ty'] > 0 && $form['ty'] < 9)
            array_unshift($this->title, Lang::enchantment('types', $form['ty']));

        array_unshift($this->title, $this->name);
    }

    protected function generatePath()
    {
        $form = $this->filterObj->getForm('form');
        if (isset($form['ty']) && count($form['ty']) == 1)
            $this->path[] = $form['ty'][0];
    }
}

?>
