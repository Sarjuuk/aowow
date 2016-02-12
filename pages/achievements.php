<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 9: Object   g_initPath()
//  tabId 0: Database g_initHeader()
class AchievementsPage extends GenericPage
{
    use ListPage;

    protected $type          = TYPE_ACHIEVEMENT;
    protected $tpl           = 'achievements';
    protected $path          = [0, 9];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $js            = ['filters.js'];
    protected $validCats     = array(
        92  => true,
        96  => [14861, 14862, 14863],
        97  => [14777, 14778, 14779, 14780],
        95  => [165,   14801, 14802, 14803, 14804, 14881, 14901, 15003],
        168 => [14808, 14805, 14806, 14921, 14922, 14923, 14961, 14962, 15001, 15002, 15041, 15042],
        169 => [170,   171,   172],
        201 => [14864, 14865, 14866],
        155 => [160,   187,   159,   163,   161,   162,   158,   14981, 156,   14941],
        81  => true,
        1   => array (
            130   => [140,   145,   147,   191],
            141   => true,
            128   => [135,   136,   137],
            122   => [123,   124,   125,   126,   127],
            133   => true,
            14807 => [14821, 14822, 14823, 14963, 15021, 15062],
            132   => [178,   173],
            134   => true,
            131   => true,
            21    => [152,   153,   154]
        )
    );

    public function __construct($pageCall, $pageParam)
    {
        $this->filterObj = new AchievementListFilter();
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageCall, $pageParam);

        $this->name   = Util::ucFirst(Lang::game('achievements'));
        $this->subCat = $pageParam ? '='.$pageParam : '';
    }

    protected function generateContent()
    {
        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        // include child categories if current category is empty
        if ($this->category)
            $conditions[] = ['category', (int)end($this->category)];

        // recreate form selection
        $this->filter = $this->filterObj->getForm('form');
        $this->filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : null;
        $this->filter['fi']    =  $this->filterObj->getForm();

        if ($fiCnd = $this->filterObj->getConditions())
            $conditions[] = $fiCnd;

        $acvList = new AchievementList($conditions);
        if (!$acvList->getMatches())
        {
            $curCats = $catList = [!empty($this->category) ? (int)end($this->category) : 0];
            while ($curCats)
            {
                $curCats = DB::Aowow()->SelectCol('SELECT Id FROM ?_achievementcategory WHERE parentCategory IN (?a)', $curCats);
                $catList = array_merge($catList, $curCats);
            }
            $conditions = [];
            if ($fiCnd)
                $conditions[] = $fiCnd;
            if ($catList)
                $conditions[] = ['category', $catList];

            $acvList = new AchievementList($conditions);
        }

        $tabData = [];
        if (!$acvList->error)
        {
            $tabData['data'] = array_values($acvList->getListviewData());

            // fill g_items, g_titles, g_achievements
            $this->extendGlobalData($acvList->getJSGlobals());

            // if we are have different cats display field
            if ($acvList->hasDiffFields(['category']))
                $tabData['visibleCols'] = ['category'];

            if (!empty($this->filter['fi']['extraCols']))
                $tabData['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

            // create note if search limit was exceeded
            if ($acvList->getMatches() > CFG_SQL_LIMIT_DEFAULT)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_achievementsfound', $acvList->getMatches(), CFG_SQL_LIMIT_DEFAULT);
                $tabData['_truncated'] = 1;
            }

            if ($this->filterObj->error)
                $tabData['_errors'] = 1;
        }

        $this->lvTabs[] = ['achievement', $tabData];

        // sort for dropdown-menus in filter
        Lang::sort('game', 'si');
    }

    protected function generateTitle()
    {
        array_unshift($this->title, Util::ucFirst(Lang::game('achievements')));
        if ($this->category)
        {
            $catrow = DB::Aowow()->SelectRow('SELECT * FROM ?_achievementcategory WHERE id = ?d', end($this->category));
            array_unshift($this->title, Util::localizedString($catrow, 'name'));
        }
    }

    protected function generatePath()
    {
        if ($this->category)
        {
            $catrows = DB::Aowow()->SelectCol('SELECT id FROM ?_achievementcategory WHERE id IN (?a)', $this->category);
            foreach ($catrows as $cat)
                $this->path[] = $cat;
        }
    }
}

?>
