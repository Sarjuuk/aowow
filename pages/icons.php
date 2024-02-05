<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 31: Icons    g_initPath()
//  tabId  0: Database g_initHeader()
class IconsPage extends GenericPage
{
    use TrListPage;

    protected $type          = Type::ICON;
    protected $tpl           = 'icons';
    protected $path          = [0, 31];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $scripts       = [[SC_JS_FILE, 'js/filters.js']];

    protected $_get          = ['filter' => ['filter' => FILTER_UNSAFE_RAW]];

    public function __construct($pageCall)
    {
        $this->filterObj = new IconListFilter();

        parent::__construct($pageCall);

        $this->name   = Util::ucFirst(Lang::game('icons'));
    }

    protected function generateContent()
    {
        $tabData = array(
            'data' => [],
        );

        $sqlLimit = 600;                                    // fits better onto the grid

        $conditions = [$sqlLimit];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        $icons = new IconList($conditions);

        $tabData['data'] = array_values($icons->getListviewData());
        $this->extendGlobalData($icons->getJSGlobals());

        // recreate form selection
        $this->filter             = $this->filterObj->getForm();
        $this->filter['query']    = $this->_get['filter'];
        $this->filter['initData'] = ['init' => 'icons'];

        if ($x = $this->filterObj->getSetCriteria())
            $this->filter['initData']['sc'] = $x;

        if ($icons->getMatches() > $sqlLimit)
        {
            $tabData['note'] = sprintf(Util::$tryFilteringEntityString, $icons->getMatches(), 'LANG.types[29][3]', $sqlLimit);
            $tabData['_truncated'] = 1;
        }

        if ($this->filterObj->error)
            $tabData['_errors'] = 1;

        $this->lvTabs[] = [IconList::$brickFile, $tabData];
    }

    protected function generateTitle()
    {
        $setCrt = $this->filterObj->getSetCriteria();
        $title  = $this->name;
        if (isset($setCrt['cr']) && count($setCrt['cr']) == 1)
        {
            switch ($setCrt['cr'][0])
            {
                case 1:
                    $title = Util::ucFirst(Lang::game('item')).' '.$title;
                    break;
                case 2:
                    $title = Util::ucFirst(Lang::game('spell')).' '.$title;
                    break;
                case 3:
                    $title = Util::ucFirst(Lang::game('achievement')).' '.$title;
                    break;
                case 6:
                    $title = Util::ucFirst(Lang::game('currency')).' '.$title;
                    break;
                case 9:
                    $title = Util::ucFirst(Lang::game('pet')).' '.$title;
                    break;
                case 11:
                    $title = Util::ucFirst(Lang::game('class')).' '.$title;
                    break;
            }
        }

        array_unshift($this->title, $title);
    }

    protected function generatePath()
    {
        $setCrt = $this->filterObj->getSetCriteria();
        if (isset($setCrt['cr']) && count($setCrt['cr']) == 1)
            $this->path[] = $setCrt['cr'][0];
    }
}

?>
