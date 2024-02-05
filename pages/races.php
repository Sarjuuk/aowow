<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 13: Race     g_initPath()
//  tabId  0: Database g_initHeader()
class RacesPage extends GenericPage
{
    use TrListPage;

    protected $type          = Type::CHR_RACE;
    protected $tpl           = 'list-page-generic';
    protected $path          = [0, 13];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    public function __construct($pageCall, $pageParam)
    {
        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('races'));
    }

    protected function generateContent()
    {
        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        $data  = [];
        $races = new CharRaceList($conditions);
        if (!$races->error)
            $data = array_values($races->getListviewData());

        $this->lvTabs[] = [CharRaceList::$brickFile, ['data' => $data]];
    }

    protected function generateTitle()
    {
        array_unshift($this->title, Util::ucFirst(Lang::game('races')));
    }

    protected function generatePath() {}
}

?>
