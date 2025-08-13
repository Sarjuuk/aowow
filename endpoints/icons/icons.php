<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class IconsBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type        = Type::ICON;
    protected  int    $cacheType   = CACHE_TYPE_PAGE;

    protected  string $template    = 'icons';
    protected  string $pageName    = 'icons';
    protected ?int    $activeTab   = parent::TAB_DATABASE;
    protected  array  $breadcrumb  = [0, 31];

    protected  array  $scripts     = [[SC_JS_FILE, 'js/filters.js']];
    protected  array  $expectedGET = array(
        'filter' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Filter::PATTERN_PARAM]]
    );
    protected  array  $validCats   = [0, 1, 2, 3];

    public function __construct(string $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageParam);

        $this->subCat = $pageParam !== '' ? '='.$pageParam : '';
        $this->filter = new IconListFilter($this->_get['filter'] ?? '', ['parentCats' => $this->category]);
        $this->filterError = $this->filter->error;
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucWords(Lang::game('icons'));

        $conditions = [600];                                // LIMIT 600 - fits better onto the grid
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        $this->filter->evalCriteria();

        if ($_ = $this->filter->getConditions())
            $conditions[] = $_;

        $this->filterError = $this->filter->error;          // maybe the evalX() caused something


        /**************/
        /* Page Title */
        /**************/

        $title = $this->h1;
        $setCr = $this->filter->getSetCriteria(1, 2, 3, 6, 9, 11);
        if (count($setCr) == 1)
            $title = match ($setCr[0])
            {
                1  => Util::ucFirst(Lang::game('item')),
                2  => Util::ucFirst(Lang::game('spell')),
                3  => Util::ucFirst(Lang::game('achievement')),
                6  => Util::ucFirst(Lang::game('currency')),
                9  => Util::ucFirst(Lang::game('pet')),
                11 => Util::ucFirst(Lang::game('class')),
            } . ' ' . $this->h1;

        array_unshift($this->title, $title);


        /*************/
        /* Menu Path */
        /*************/

        if (count($setCr) == 1)
            $this->breadcrumb[] = $setCr[0];


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons[BUTTON_WOWHEAD] = true;
        if ($fiQuery = $this->filter->buildGETParam())
            $this->wowheadLink .= '&filter='.$fiQuery;

        $icons = new IconList($conditions, ['calcTotal' => true]);

        $tabData['data'] = $icons->getListviewData();
        $this->extendGlobalData($icons->getJSGlobals());

        if ($icons->getMatches() > $conditions[0])          // LIMIT
        {
            $tabData['note'] = sprintf(Util::$tryFilteringEntityString, $icons->getMatches(), 'LANG.types[29][3]', $conditions[0]);
            $tabData['_truncated'] = 1;
        }

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview($tabData, IconList::$brickFile));

        parent::generate();
    }
}

?>
