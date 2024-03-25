<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 10: Title    g_initPath()
//  tabId  0: Database g_initHeader()
class TitlesPage extends GenericPage
{
    use TrListPage;

    protected $type          = Type::TITLE;
    protected $tpl           = 'list-page-generic';
    protected $path          = [0, 10];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $validCats     = [0, 1, 2, 3, 4, 5, 6];

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('titles'));
    }

    protected function generateContent()
    {
        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))             // hide unused titles
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
            $conditions[] = ['category', $this->category[0]];

        $tabData = ['data' => []];
        $titles  = new TitleList($conditions);
        if (!$titles->error)
        {
            $tabData['data'] = array_values($titles->getListviewData());

            if ($titles->hasDiffFields(['category']))
                $tabData['visibleCols'] = ['category'];

            if (!$titles->hasAnySource())
                $tabData['hiddenCols'] = ['source'];
        }

        $this->lvTabs[] = [TitleList::$brickFile, $tabData];
    }

    protected function generateTitle()
    {
        array_unshift($this->title, Util::ucFirst(Lang::game('titles')));
        if ($this->category)
            array_unshift($this->title, Lang::title('cat', $this->category[0]));
    }

    protected function generatePath()
    {
        if ($this->category)
            $this->path[] = $this->category[0];             // should be only one parameter anyway
    }
}

?>
