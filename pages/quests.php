<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

// menuId 3: Quest    g_initPath()
//  tabId 0: Database g_initHeader()
class QuestsPage extends GenericPage
{
    use ListPage;

    protected $type          = TYPE_QUEST;
    protected $tpl           = 'quests';
    protected $path          = [0, 3];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $validCats     = [];
    protected $js            = ['filters.js'];

    public function __construct($pageCall, $pageParam)
    {
        $this->validCats = Game::$questClasses;             // needs reviewing (not allowed to set this as default)

        $this->getCategoryFromUrl($pageParam);
        $this->filterObj = new QuestListFilter(false, ['parentCats' => $this->category]);

        parent::__construct($pageCall, $pageParam);

        $this->name   = Util::ucFirst(Lang::game('quests'));
        $this->subCat = $pageParam ? '='.$pageParam : '';
    }

    protected function generateContent()
    {
        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if (isset($this->category[1]))
            $conditions[] = ['zoneOrSort', $this->category[1]];
        else if (isset($this->category[0]))
            $conditions[] = ['zoneOrSort', $this->validCats[$this->category[0]]];

        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        $quests = new QuestList($conditions, ['extraOpts' => $this->filterObj->extraOpts]);

        $this->extendGlobalData($quests->getJSGlobals());

        // recreate form selection
        $this->filter             = $this->filterObj->getForm();
        $this->filter['query']    = isset($_GET['filter']) ? $_GET['filter'] : null;
        $this->filter['initData'] = ['init' => 'quests'];

        $rCols = $this->filterObj->getReputationCols();
        $xCols = $this->filterObj->getExtraCols();
        if ($rCols)
            $this->filter['initData']['rc'] = $rCols;

        if ($xCols)
            $this->filter['initData']['ec'] = $xCols;

        if ($x = $this->filterObj->getSetCriteria())
            $this->filter['initData']['sc'] = $x;

        $tabData = ['data' => array_values($quests->getListviewData())];

        if ($rCols)
            $tabData['extraCols'] = '$fi_getReputationCols('.json_encode($rCols, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE).')';
        else if ($xCols)
            $tabData['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

        // create note if search limit was exceeded
        if ($quests->getMatches() > CFG_SQL_LIMIT_DEFAULT)
        {
            $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_questsfound', $quests->getMatches(), CFG_SQL_LIMIT_DEFAULT);
            $tabData['_truncated'] = 1;
        }
        else if (isset($this->category[1]) && $this->category[1] > 0)
            $tabData['note'] = '$$WH.sprintf(LANG.lvnote_questgivers, '.$this->category[1].', g_zones['.$this->category[1].'], '.$this->category[1].')';

        if ($this->filterObj->error)
            $tabData['_errors'] = 1;

        $this->lvTabs[] = ['quest', $tabData];
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);

        if (isset($this->category[1]))
            array_unshift($this->title, Lang::quest('cat', $this->category[0], $this->category[1]));
        else if (isset($this->category[0]))
        {
            $c0 = Lang::quest('cat', $this->category[0]);
            array_unshift($this->title, is_array($c0) ? $c0[0] : $c0);
        }
    }

    protected function generatePath()
    {
        foreach ($this->category as $c)
            $this->path[] = $c;
    }
}

?>
