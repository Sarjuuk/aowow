<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class RacesBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type        = Type::CHR_RACE;
    protected  int    $cacheType   = CACHE_TYPE_LIST_PAGE;

    protected  string $template    = 'list-page-generic';
    protected  string $pageName    = 'races';
    protected ?int    $activeTab   = parent::TAB_DATABASE;
    protected  array  $breadcrumb  = [0, 13];

    public function __construct(string $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageParam);
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('races'));


        array_unshift($this->title, $this->h1);


        $this->redButtons[BUTTON_WOWHEAD] = true;

        $conditions = [Listview::DEFAULT_SIZE];
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $races = new CharRaceList($conditions);
        if (!$races->error)
            $this->lvTabs->addListviewTab(new Listview(['data' => $races->getListviewData()], CharRaceList::$brickFile));

        parent::generate();
    }
}

?>
