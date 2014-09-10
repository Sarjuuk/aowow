<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 4: NPC      g_initPath()
//  tabId 0: Database g_initHeader()
class NpcsPage extends GenericPage
{
    use ListPage;

    protected $type          = TYPE_NPC;
    protected $tpl           = 'npcs';
    protected $path          = [0, 4];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $validCats     = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];
    protected $js            = ['filters.js'];

    public function __construct($pageCall, $pageParam)
    {
        $this->filterObj = new CreatureListFilter();
        $this->getCategoryFromUrl($pageParam);;

        parent::__construct($pageCall, $pageParam);

        $this->name   = Util::ucFirst(Lang::$game['npcs']);
        $this->subCat = $pageParam ? '='.$pageParam : '';
    }

    protected function generateContent()
    {
        $conditions = [];

        if ($this->category)
        {
            $conditions[] = ['type', $this->category[0]];
            $this->petFamPanel = $this->category[0] == 1;
        }
        else
            $this->petFamPanel = false;

        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        // beast subtypes are selected via filter
        $npcs = new CreatureList($conditions, ['extraOpts' => $this->filterObj->extraOpts]);

        // recreate form selection
        $this->filter = array_merge($this->filterObj->getForm('form'), $this->filter);
        $this->filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : NULL;
        $this->filter['fi']    =  $this->filterObj->getForm();

        $lv = array(
            'file'   => 'creature',
            'data'   => $npcs->getListviewData(),           // listview content
            'params' => []
        );

        if (!empty($this->filter['fi']['extraCols']))
            $lv['params']['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

        // create note if search limit was exceeded
        if ($npcs->getMatches() > CFG_SQL_LIMIT_DEFAULT)
        {
            $lv['params']['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_npcsfound', $npcs->getMatches(), CFG_SQL_LIMIT_DEFAULT);
            $lv['params']['_truncated'] = 1;
        }

        if ($this->filterObj->error)
            $lv['params']['_errors'] = '$1';

        $this->lvTabs[] = $lv;

        // sort for dropdown-menus
        asort(Lang::$game['fa']);
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
        if ($this->category)
            array_unshift($this->title, Lang::$npc['cat'][$this->category[0]]);
    }

    protected function generatePath()
    {
        if ($this->category)
            $this->path[] = $this->category[0];

        $form = $this->filterObj->getForm('form');
        if (isset($form['fa']) && !is_array($form['fa']))
            $this->path[] = $form['fa'];
    }
}

?>
