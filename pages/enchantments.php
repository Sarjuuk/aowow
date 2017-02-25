<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 101: Enchantment g_initPath()
//  tabId   0: Database    g_initHeader()
class EnchantmentsPage extends GenericPage
{
    use ListPage;

    protected $type          = TYPE_ENCHANTMENT;
    protected $tpl           = 'enchantments';
    protected $path          = [0, 101];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $js            = ['filters.js'];

    public function __construct($pageCall, $pageParam)
    {
        $this->filterObj = new EnchantmentListFilter();
        $this->getCategoryFromUrl($pageParam);;

        parent::__construct($pageCall, $pageParam);

        $this->name   = Util::ucFirst(Lang::game('enchantments'));
        $this->subCat = $pageParam !== null ? '='.$pageParam : '';
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
        $this->filter          = array_merge($this->filterObj->getForm('form'), $this->filter);
        $this->filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : NULL;
        $this->filter['fi']    =  $this->filterObj->getForm();

        $xCols = $this->filterObj->getForm('extraCols', true);
        foreach (Util::$itemFilter as $fiId => $str)
            if (array_column($tabData['data'], $str))
                $xCols[] = $fiId;

        if (array_column($tabData['data'], 'dmg'))
            $xCols[] = 34;

        if ($xCols)
            $this->filter['fi']['extraCols'] =  "fi_extraCols = ".Util::toJSON(array_values(array_unique($xCols))).";";

        if (!empty($this->filter['fi']['extraCols']))
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

        $this->lvTabs[] = ['enchantment', $tabData, 'enchantment'];
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
        if (isset($form['ty']) && !is_array($form['ty']))
            $this->path[] = $form['ty'];
    }
}

?>
