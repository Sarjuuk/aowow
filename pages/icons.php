<?php

namespace Aowow;

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
        parent::__construct($pageCall);

        $this->filterObj = new IconListFilter($this->_get['filter'] ?? '');

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

        $this->filterObj->evalCriteria();

        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        $icons = new IconList($conditions, ['calcTotal' => true]);

        $tabData['data'] = array_values($icons->getListviewData());
        $this->extendGlobalData($icons->getJSGlobals());

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
        $title = $this->name;
        $setCr = $this->filterObj->getSetCriteria(1, 2, 3, 6, 9, 11);
        if (count($setCr) == 1)
        {
            $title = match($setCr[0])
            {
                1  => Util::ucFirst(Lang::game('item')),
                2  => Util::ucFirst(Lang::game('spell')),
                3  => Util::ucFirst(Lang::game('achievement')),
                6  => Util::ucFirst(Lang::game('currency')),
                9  => Util::ucFirst(Lang::game('pet')),
                11 => Util::ucFirst(Lang::game('class'))
            } . ' ' . $title;
        }

        array_unshift($this->title, $title);
    }

    protected function generatePath()
    {
        $setCr = $this->filterObj->getSetCriteria(1, 2, 3, 6, 9, 11);
        if (count($setCr) == 1)
            $this->path[] = $setCr[0];
    }
}

?>
