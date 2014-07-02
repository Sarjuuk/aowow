<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 14: Skill    g_initPath()
//  tabId  0: Database g_initHeader()
class SkillsPage extends GenericPage
{
    use ListPage;

    protected $type      = TYPE_SKILL;
    protected $tpl       = 'list-page-generic';
    protected $path      = [0, 14];
    protected $tabId     = 0;
    protected $mode      = CACHETYPE_PAGE;
    protected $validCats = [-6, -5, -4, 6, 7, 8, 9, 10, 11];

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);;

        parent::__construct();

        $this->name = Util::ucFirst(Lang::$game['skills']);
    }

    protected function generateContent()
    {
        $conditions = [];
        if (User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = ['categoryId', 12, '!'];        // GENERIC (DND)

        if ($this->category)
            $conditions[] = ['typeCat', $this->category[0]];

        $skills = new SkillList($conditions);

        $this->lvData[] = array(
            'file'   => 'skill',
            'data'   => $skills->getListviewData(),         // listview content
            'params' => []
        );
    }

    protected function generateTitle()
    {
        if ($this->category)
            array_unshift($this->title, Lang::$skill['cat'][$this->category[0]]);
    }

    protected function generatePath()
    {
        if ($this->category)
            $this->path[] = $this->category[0];
    }
}

?>
