<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 5: Object   g_initPath()
//  tabId 0: Database g_initHeader()
class ObjectsPage extends GenericPage
{
    use ListPage;

    protected $type          = TYPE_OBJECT;
    protected $tpl           = 'objects';
    protected $path          = [0, 5];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $validCats     = [-2, -3, -4, -5, -6, 0, 3, 9, 25];
    protected $js            = ['filters.js'];

    public function __construct($pageCall, $pageParam)
    {
        $this->filterObj = new GameObjectListFilter();
        $this->getCategoryFromUrl($pageParam);;

        parent::__construct($pageCall, $pageParam);

        $this->name   = Util::ucFirst(Lang::game('objects'));
        $this->subCat = $pageParam ? '='.$pageParam : '';
    }

    protected function generateContent()
    {
        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
            $conditions[] = ['typeCat', (int)$this->category[0]];

        // recreate form selection
        $this->filter = $this->filterObj->getForm('form');
        $this->filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : null;
        $this->filter['fi']    =  $this->filterObj->getForm();

        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        $tabData = ['data' => []];
        $objects = new GameObjectList($conditions, ['extraOpts' => $this->filterObj->extraOpts]);
        if (!$objects->error)
        {
            $tabData['data'] = array_values($objects->getListviewData());
            if ($objects->hasSetFields(['reqSkill']))
                $tabData['visibleCols'] = ['skill'];

            // create note if search limit was exceeded
            if ($objects->getMatches() > CFG_SQL_LIMIT_DEFAULT)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_objectsfound', $objects->getMatches(), CFG_SQL_LIMIT_DEFAULT);
                $tabData['_truncated'] = 1;
            }

            if ($this->filterObj->error)
                $tabData['_errors'] = 1;
        }

        $this->lvTabs[] = ['object', $tabData];
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
        if ($this->category)
            array_unshift($this->title, Lang::gameObject('cat', $this->category[0]));
    }

    protected function generatePath()
    {
        if ($this->category)
            $this->path[] = $this->category[0];
    }
}

?>
