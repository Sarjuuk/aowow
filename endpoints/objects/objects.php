<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ObjectsBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type        = Type::OBJECT;
    protected  int    $cacheType   = CACHE_TYPE_LIST_PAGE;

    protected  string $template    = 'objects';
    protected  string $pageName    = 'objects';
    protected ?int    $activeTab   = parent::TAB_DATABASE;
    protected  array  $breadcrumb  = [0, 5];

    protected  array  $dataLoader  = ['zones'];
    protected  array  $scripts     = [[SC_JS_FILE, 'js/filters.js']];
    protected  array  $expectedGET = array(
        'filter' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Filter::PATTERN_PARAM]]
    );
    protected  array  $validCats   = [-2, -3, -4, -5, -6, 0, 3, 6, 9, 25];

    public bool $petFamPanel = false;

    public function __construct(string $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageParam);

        $this->subCat = $pageParam !== '' ? '='.$pageParam : '';
        $this->filter = new GameObjectListFilter($this->_get['filter'] ?? '', ['parentCats' => $this->category]);
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
        $this->h1 = Util::ucFirst(Lang::game('objects'));

        $conditions = [Listview::DEFAULT_SIZE];
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($_ = $this->filter->getConditions())
            $conditions[] = $_;

        if ($this->category)
            $conditions[] = ['typeCat', (int)$this->category[0]];


        /*************/
        /* Menu Path */
        /*************/

        if ($this->category)
            $this->breadcrumb[] = $this->category[0];


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1);
        if ($this->category)
            array_unshift($this->title, Lang::gameObject('cat', $this->category[0]));


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons[BUTTON_WOWHEAD] = true;
        if ($fiQuery = $this->filter->buildGETParam())
            $this->wowheadLink .= '&filter='.$fiQuery;

        $tabData = ['data' => []];
        $objects = new GameObjectList($conditions, ['extraOpts' => $this->filter->extraOpts, 'calcTotal' => true]);
        if (!$objects->error)
        {
            $tabData['data'] = $objects->getListviewData();
            if ($objects->hasSetFields('reqSkill'))
                $tabData['visibleCols'] = ['skill'];

            // create note if search limit was exceeded
            if ($objects->getMatches() > Listview::DEFAULT_SIZE)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_objectsfound', $objects->getMatches(), Listview::DEFAULT_SIZE);
                $tabData['_truncated'] = 1;
            }
        }

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview($tabData, GameObjectList::$brickFile));

        parent::generate();
    }
}

?>
