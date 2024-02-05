<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 102: Areatrigger g_initPath()
//  tabid   0: Database    g_initHeader()
class AreaTriggersPage extends GenericPage
{
    use TrListPage;

    protected $type          = Type::AREATRIGGER;
    protected $tpl           = 'areatriggers';
    protected $path          = [0, 102];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $validCats     = [0, 1, 2, 3, 4, 5];
    protected $scripts       = [[SC_JS_FILE, 'js/filters.js']];
    protected $reqUGroup     = U_GROUP_STAFF;

    protected $_get          = ['filter' => ['filter' => FILTER_UNSAFE_RAW]];

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);;
        if (isset($this->category[0]))
            header('Location: ?areatriggers&filter=ty='.$this->category[0], true, 302);

        $this->filterObj = new AreaTriggerListFilter();

        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('areatriggers'));
    }

    protected function generateContent()
    {
        // recreate form selection
        $this->filter             = $this->filterObj->getForm();
        $this->filter['query']    = $this->_get['filter'];
        $this->filter['initData'] = ['init' => 'areatrigger'];

        if ($x = $this->filterObj->getSetCriteria())
            $this->filter['initData']['sc'] = $x;

        $conditions = [];
        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        $tabData = [];
        $trigger = new AreaTriggerList($conditions);
        if (!$trigger->error)
        {
            $tabData['data'] = array_values($trigger->getListviewData());

            // create note if search limit was exceeded; overwriting 'note' is intentional
            if ($trigger->getMatches() > CFG_SQL_LIMIT_DEFAULT)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringEntityString, $trigger->getMatches(), '"'.Lang::game('areatriggers').'"', CFG_SQL_LIMIT_DEFAULT);
                $tabData['_truncated'] = 1;
            }

            if ($this->filterObj->error)
                $tabData['_errors'] = 1;

        }

        $this->lvTabs[] = [AreaTriggerList::$brickFile, $tabData, 'areatrigger'];
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);

        $form = $this->filterObj->getForm();
        if (isset($form['ty']) && count($form['ty']) == 1)
            array_unshift($this->title, Lang::areatrigger('types', $form['ty'][0]));
    }

    protected function generatePath()
    {
        $form = $this->filterObj->getForm();
        if (isset($form['ty']) && count($form['ty']) == 1)
            $this->path[] = $form['ty'];
    }
}

?>
