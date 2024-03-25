<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 14: Skill    g_initPath()
//  tabId  0: Database g_initHeader()
class SkillsPage extends GenericPage
{
    use TrListPage;

    protected $type      = Type::SKILL;
    protected $tpl       = 'list-page-generic';
    protected $path      = [0, 14];
    protected $tabId     = 0;
    protected $mode      = CACHE_TYPE_PAGE;
    protected $validCats = [-6, -5, -4, 6, 7, 8, 9, 10, 11];

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('skills'));
    }

    protected function generateContent()
    {
        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
            $conditions[] = ['typeCat', $this->category[0]];

        $skills = new SkillList($conditions);

        $this->lvTabs[] = [SkillList::$brickFile, ['data' => array_values($skills->getListviewData())]];
    }

    protected function generateTitle()
    {
        if ($this->category)
            array_unshift($this->title, Lang::skill('cat', $this->category[0]));
    }

    protected function generatePath()
    {
        if ($this->category)
            $this->path[] = $this->category[0];
    }
}

?>
