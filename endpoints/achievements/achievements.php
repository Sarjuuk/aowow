<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AchievementsBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type        = Type::ACHIEVEMENT;
    protected  int    $cacheType   = CACHE_TYPE_LIST_PAGE;

    protected  string $template    = 'achievements';
    protected  string $pageName    = 'achievements';
    protected ?int    $activeTab   = parent::TAB_DATABASE;
    protected  array  $breadcrumb  = [0, 9];

    protected  array  $scripts     = [[SC_JS_FILE, 'js/filters.js']];
    protected  array  $expectedGET = array(
        'filter' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Filter::PATTERN_PARAM]]
    );
    protected  array  $validCats   = array(
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

    public function __construct(string $rawParam)
    {
        $this->getCategoryFromUrl($rawParam);

        parent::__construct($rawParam);

        if ($this->category)
            $this->subCat = '='.implode('.', $this->category);

        $this->filter = new AchievementListFilter($this->_get['filter'] ?? '', ['parentCats' => $this->category]);
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
        $this->h1 = Util::ucFirst(Lang::game('achievements'));

        $conditions = [Listview::DEFAULT_SIZE];
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        // include child categories if current category is empty
        if ($this->category)
            $conditions[] = ['category', end($this->category)];

        if ($fiCnd = $this->filter->getConditions())
            $conditions[] = $fiCnd;


        /*************/
        /* Menu Path */
        /*************/

        foreach ($this->category as $cat)
            $this->breadcrumb[] = $cat;


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, Util::ucFirst(Lang::game('achievements')));
        if ($this->category)
            array_unshift($this->title, Lang::achievement('cat', end($this->category)));


        /****************/
        /* Main Content */
        /****************/

        // fix modern client achievement category structure: top catg [1:char, 2:statistic, 3:guild]
        if ($this->category && $this->category[0] != 1)
            $link = '=1.'.implode('.', $this->category);
        else if ($this->category)
            $link = '=2'.(count($this->category) > 1 ? '.'.implode('.', array_slice($this->category, 1)) : '');
        else
            $link = '';

        $this->redButtons[BUTTON_WOWHEAD] = true;
        $this->wowheadLink = sprintf(WOWHEAD_LINK, Lang::getLocale()->domain(), $this->pageName, $link);

        if ($fiQuery = $this->filter->buildGETParam())
            $this->wowheadLink .= '&filter='.$fiQuery;

        $acvList = new AchievementList($conditions, ['calcTotal' => true]);
        if (!$acvList->getMatches() && $this->category)
        {
            // ToDo - we also branch into here if the filter prohibits results. That should be skipped.
            $conditions = [Listview::DEFAULT_SIZE];
            if ($fiCnd)
                $conditions[] = $fiCnd;
            if ($catList = DB::Aowow()->SelectCol('SELECT `id` FROM ?_achievementcategory WHERE `parentCat` IN (?a) OR `parentCat2` IN (?a) ', $this->category, $this->category))
                $conditions[] = ['category', $catList];

            $acvList = new AchievementList($conditions, ['calcTotal' => true]);
        }

        $tabData = [];
        if (!$acvList->error)
        {
            $tabData['data'] = $acvList->getListviewData();

            // fill g_items, g_titles, g_achievements
            $this->extendGlobalData($acvList->getJSGlobals());

            // if we are have different cats display field
            if ($acvList->hasDiffFields('category'))
                $tabData['visibleCols'] = ['category'];

            if ($this->filter->fiExtraCols)
                $tabData['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

            // create note if search limit was exceeded
            if ($acvList->getMatches() > Listview::DEFAULT_SIZE)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_achievementsfound', $acvList->getMatches(), Listview::DEFAULT_SIZE);
                $tabData['_truncated'] = 1;
            }
        }
        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview($tabData, AchievementList::$brickFile));

        parent::generate();

        $this->setOnCacheLoaded([self::class, 'onBeforeDisplay']);
    }

    public static function onBeforeDisplay()
    {
        // sort for dropdown-menus in filter
        Lang::sort('game', 'si');
    }
}

?>
