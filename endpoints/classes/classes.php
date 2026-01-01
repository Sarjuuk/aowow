<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ClassesBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type       = Type::CHR_CLASS;
    protected  int    $cacheType  = CACHE_TYPE_LIST_PAGE;

    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'classes';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 12];

    public function __construct(string $rawParam)
    {
        $this->getCategoryFromUrl($rawParam);

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('classes'));


        array_unshift($this->title, Util::ucFirst(Lang::game('classes')));


        $this->redButtons[BUTTON_WOWHEAD] = true;

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $classes = new CharClassList();
        if (!$classes->error)
            $this->lvTabs->addListviewTab(new Listview(['data' => $classes->getListviewData()], CharClassList::$brickFile));

        parent::generate();
    }
}

?>
