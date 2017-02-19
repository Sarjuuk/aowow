<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 19: Sounds   g_initPath()
//  tabId  0: Database g_initHeader()
class SoundsPage extends GenericPage
{
    use ListPage;

    protected $type      = TYPE_SOUND;
    protected $tpl       = 'list-page-generic';
    protected $path      = [0, 19];
    protected $tabId     = 0;
    protected $mode      = CACHE_TYPE_PAGE;
    protected $validCats = [0, 1, 2, 3, 4, 6, 7, 8, 9, 10, 12, 13, 14, 16, 17, 18, 19, 20, 21, 22, 23, 25, 26, 27, 28, 29, 30, 31, 50];

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);;

        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('sounds'));
    }

    protected function generateContent()
    {
        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
            $conditions[] = ['typeCat', $this->category[0]];

        $sounds = new SoundList($conditions);

        $this->lvTabs[] = ['sound', ['data' => array_values($sounds->getListviewData())]];
    }

    protected function generateTitle()
    {
        if ($this->category)
            array_unshift($this->title, Lang::sound('cat', $this->category[0]));
    }

    protected function generatePath()
    {
        if ($this->category)
            $this->path[] = $this->category[0];
    }
}

?>
