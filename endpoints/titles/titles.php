<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class TitlesBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type       = Type::TITLE;
    protected  int    $cacheType  = CACHE_TYPE_LIST_PAGE;

    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'titles';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 10];

    protected  array  $validCats  = [0, 1, 2, 3, 4, 5, 6];

    public function __construct(string $rawParam)
    {
        $this->getCategoryFromUrl($rawParam);

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('titles'));


        if ($this->category)
            $this->breadcrumb[] = $this->category[0];


        array_unshift($this->title, $this->h1);
        if ($this->category)
            array_unshift($this->title, Lang::title('cat', $this->category[0]));


        $this->redButtons[BUTTON_WOWHEAD] = true;

        $conditions = [Listview::DEFAULT_SIZE];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))             // hide unused titles
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
            $conditions[] = ['category', $this->category[0]];

        $tabData = ['data' => []];
        $titles  = new TitleList($conditions);
        if (!$titles->error)
        {
            $tabData['data'] = $titles->getListviewData();

            if ($titles->hasDiffFields('category'))
                $tabData['visibleCols'] = ['category'];

            if (!$titles->hasAnySource())
                $tabData['hiddenCols'] = ['source'];
        }

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview($tabData, TitleList::$brickFile));

        parent::generate();
    }
}

?>
