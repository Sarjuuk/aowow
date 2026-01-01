<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SkillsBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type       = Type::SKILL;
    protected  int    $cacheType  = CACHE_TYPE_LIST_PAGE;

    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'skills';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 14];

    protected  array  $validCats  = [-6, -5, -4, 6, 7, 8, 9, 10, 11];

    public function __construct(string $rawParam)
    {
        $this->getCategoryFromUrl($rawParam);

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('skills'));


        if ($this->category)
            $this->breadcrumb[] = $this->category[0];


        array_unshift($this->title, $this->h1);
        if ($this->category)
            array_unshift($this->title, Lang::skill('cat', $this->category[0]));


        $this->redButtons[BUTTON_WOWHEAD] = true;

        $conditions = [Listview::DEFAULT_SIZE];
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
            $conditions[] = ['typeCat', $this->category[0]];

        $tabData = ['data' => []];
        $skills  = new SkillList($conditions);
        if (!$skills->error)
            $tabData['data'] = $skills->getListviewData();

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview($tabData, SkillList::$brickFile));

        parent::generate();
    }
}

?>
