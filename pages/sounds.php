<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 19: Sounds   g_initPath()
//  tabId  0: Database g_initHeader()
class SoundsPage extends GenericPage
{
    use TrListPage;

    protected $type      = Type::SOUND;
    protected $tpl       = 'sounds';
    protected $path      = [0, 19];
    protected $tabId     = 0;
    protected $mode      = CACHE_TYPE_PAGE;
    protected $validCats = [1, 2, 3, 4, 6, 9, 10, 12, 13, 14, 16, 17, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 50, 52, 53];
    protected $scripts   = [[SC_JS_FILE, 'js/filters.js']];

    protected $_get      = ['filter' => ['filter' => FILTER_UNSAFE_RAW]];

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);;
        if (isset($this->category[0]))
            header('Location: ?sounds&filter=ty='.$this->category[0], true, 302);

        $this->filterObj = new SoundListFilter();

        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('sounds'));
    }

    protected function generateContent()
    {
        $this->redButtons = array(
            BUTTON_WOWHEAD  => true,
            BUTTON_PLAYLIST => true
        );

        $conditions = [];
        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        $this->filter          = $this->filterObj->getForm();
        $this->filter['query'] = $this->_get['filter'];

        $tabData = [];
        $sounds  = new SoundList($conditions);
        if (!$sounds->error)
        {
            $tabData['data'] = array_values($sounds->getListviewData());

            // create note if search limit was exceeded; overwriting 'note' is intentional
            if ($sounds->getMatches() > CFG_SQL_LIMIT_DEFAULT)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_soundsfound', $sounds->getMatches(), CFG_SQL_LIMIT_DEFAULT);
                $tabData['_truncated'] = 1;
            }

            if ($this->filterObj->error)
                $tabData['_errors'] = 1;
        }
        $this->lvTabs[] = [SoundList::$brickFile, $tabData];
    }

    protected function postCache()
    {
        // sort for dropdown-menus
        Lang::sort('sound', 'cat');
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);

        $form = $this->filterObj->getForm();
        if (isset($form['ty']) && count($form['ty']) == 1)
            array_unshift($this->title, Lang::sound('cat', $form['ty'][0]));
    }

    protected function generatePath()
    {
        $form = $this->filterObj->getForm();
        if (isset($form['ty']) && count($form['ty']) == 1)
            $this->path[] = $form['ty'][0];
    }
}

?>
