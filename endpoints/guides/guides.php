<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuidesBaseResponse extends TemplateResponse // implements ICache
{
    use TrListPage/* , TrCache */;

 // protected  int    $cacheType  = CACHE_TYPE_LIST_PAGE; // really do? cache would need to be destroyed externally with each guide status update
    protected  int    $type       = Type::GUIDE;

    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'guides';
    protected ?int    $activeTab  = parent::TAB_GUIDES;
    protected  array  $breadcrumb = [6];

    protected  array  $validCats  = [null, 1, 2, 3, 4, 5, 6, 7, 8, 9];

    public function __construct(string $rawParam)
    {
        $this->getCategoryFromUrl($rawParam);

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('guides'));


        if ($this->category)
            $this->breadcrumb[] = $this->category[0];


        array_unshift($this->title, $this->h1);
        if ($this->category)
            array_unshift($this->title, Lang::guide('category', $this->category[0]));


        $conditions = array(
            ['locale', Lang::getLocale()->value],
            ['status', GuideMgr::STATUS_ARCHIVED, '!'],     // never archived guides
            [
                'OR',
                ['status', GuideMgr::STATUS_APPROVED],      // currently approved
                ['rev', 0, '>']                             // has previously approved revision
            ]
        );
        if ($this->category)
            $conditions[] = ['category', $this->category[0]];

        $this->redButtons = [BUTTON_GUIDE_NEW => User::canWriteGuide()];

        $guides = new GuideList($conditions);

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview(array(
            'data'       => $guides->getListviewData(),
            'name'       => Util::ucFirst(Lang::game('guides')),
            'hiddenCols' => ['patch'],                      // pointless: display date instead
            'extraCols'  => ['$Listview.extraCols.date']    // ok
        ), GuideList::$brickFile));

        parent::generate();
    }
}

?>
