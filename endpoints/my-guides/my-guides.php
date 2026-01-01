<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class MyguidesBaseResponse extends TemplateResponse
{
    use TrListPage;

    protected  int    $type       = Type::GUIDE;

    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'my-guides';
    protected ?int    $activeTab  = parent::TAB_GUIDES;
 // protected  array  $breadcrumb = [6];                    // breadcrumb menu not displayed by WH.?

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if (!User::isLoggedIn() || $rawParam)
            $this->generateError();
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::guide('myGuides'));

        array_unshift($this->title, $this->h1);

        $this->redButtons = [BUTTON_GUIDE_NEW => User::canWriteGuide()];

        $guides = new GuideList(array(['userId', User::$id]));

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview(array(
            'data'        => $guides->getListviewData(),
            'name'        => Util::ucFirst(Lang::game('guides')),
            'hiddenCols'  => ['patch', 'author'],
            'visibleCols' => ['status'],
            'extraCols'   => ['$Listview.extraCols.date']
        ), GuideList::$brickFile));

        parent::generate();
    }
}

?>
