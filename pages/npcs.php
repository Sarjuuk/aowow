<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 4: NPC      g_initPath()
//  tabId 0: Database g_initHeader()
class NpcsPage extends GenericPage
{
    use TrListPage;

    protected $petFamPanel   = false;

    protected $type          = Type::NPC;
    protected $tpl           = 'npcs';
    protected $path          = [0, 4];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $validCats     = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];
    protected $scripts       = [[SC_JS_FILE, 'js/filters.js']];

    protected $_get          = ['filter' => ['filter' => FILTER_UNSAFE_RAW]];

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);
        $this->filterObj = new CreatureListFilter(false, ['parentCats' => $this->category]);

        parent::__construct($pageCall, $pageParam);

        $this->name   = Util::ucFirst(Lang::game('npcs'));
        $this->subCat = $pageParam ? '='.$pageParam : '';
    }

    protected function generateContent()
    {
        $this->addScript([SC_JS_FILE, '?data=zones']);

        $conditions = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
        {
            $conditions[] = ['type', $this->category[0]];
            $this->petFamPanel = $this->category[0] == 1;
        }

        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        // beast subtypes are selected via filter
        $npcs = new CreatureList($conditions, ['extraOpts' => $this->filterObj->extraOpts]);

        // recreate form selection
        $this->filter             = $this->filterObj->getForm();
        $this->filter['query']    = $this->_get['filter'];
        $this->filter['initData'] =  ['init' => 'npcs'];

        $rCols = $this->filterObj->getReputationCols();
        $xCols = $this->filterObj->getExtraCols();
        if ($rCols)
            $this->filter['initData']['rc'] = $rCols;

        if ($xCols)
            $this->filter['initData']['ec'] = $xCols;

        if ($x = $this->filterObj->getSetCriteria())
            $this->filter['initData']['sc'] = $x;

        $tabData = ['data' => array_values($npcs->getListviewData($rCols ? NPCINFO_REP : 0x0))];

        if ($rCols)                                         // never use pretty-print
            $tabData['extraCols'] = '$fi_getReputationCols('.Util::toJSON($rCols, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE).')';
        else if ($xCols)
            $tabData['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

        if ($this->category)
            $tabData['hiddenCols'] = ['type'];

        // create note if search limit was exceeded
        if ($npcs->getMatches() > CFG_SQL_LIMIT_DEFAULT)
        {
            $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_npcsfound', $npcs->getMatches(), CFG_SQL_LIMIT_DEFAULT);
            $tabData['_truncated'] = 1;
        }

        if ($this->filterObj->error)
            $tabData['_errors'] = 1;

        $this->lvTabs[] = [CreatureList::$brickFile, $tabData];
    }

    protected function postCache()
    {
        // sort for dropdown-menus
        Lang::sort('game', 'fa');
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
        if ($this->category)
            array_unshift($this->title, Lang::npc('cat', $this->category[0]));

        $form = $this->filterObj->getForm();
        if (isset($form['fa']) && !is_array($form['fa']))
            array_unshift($this->title, Lang::game('fa', $form['fa']));
    }

    protected function generatePath()
    {
        if ($this->category)
            $this->path[] = $this->category[0];

        $form = $this->filterObj->getForm();
        if (isset($form['fa']) && !is_array($form['fa']))
            $this->path[] = $form['fa'];
    }
}

?>
