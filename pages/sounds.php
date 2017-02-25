<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 19: Sounds   g_initPath()
//  tabId  0: Database g_initHeader()
class SoundsPage extends GenericPage
{
    use ListPage;

    protected $type      = TYPE_SOUND;
    protected $tpl       = 'sounds';
    protected $path      = [0, 19];
    protected $tabId     = 0;
    protected $mode      = CACHE_TYPE_PAGE;
    protected $validCats = [1, 2, 3, 4, 6,/* 7, 8,*/ 9, 10, 12, /*13,*/ 14, 16, 17, /*18,*/ 19, 20, 21, 22, 23, 25, 26, 27, 28, 29, 30, 31, 50, 52, 53]; /* reality 404 */

    public function __construct($pageCall, $pageParam)
    {
        $this->filterObj = new SoundListFilter();

        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('sounds'));
    }

    protected function generateContent()
    {
        $this->addJs('filters.js');

        $this->redButtons = array(
            BUTTON_WOWHEAD  => true,
            BUTTON_PLAYLIST => true
        );

        $conditions = [];
        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        $this->filter = array_merge($this->filterObj->getForm('form'), $this->filter);
        $this->filter['query'] = isset($_GET['filter']) ? $_GET['filter'] : null;
        $this->filter['fi']    =  $this->filterObj->getForm();

        $sounds = new SoundList($conditions);
        $tabData = [];
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
        $this->lvTabs[] = ['sound', $tabData];

        Lang::sort('sound', 'cat');
    }

    protected function generateTitle()
    {
        $form = $this->filterObj->getForm('form');
        if (isset($form['ty']) && !is_array($form['ty']))
            array_unshift($this->title, Lang::sound('cat', $form['ty']));
    }

    protected function generatePath()
    {
        $form = $this->filterObj->getForm('form');
        if (isset($form['ty']) && !is_array($form['ty']))
            $this->path[] = $form['ty'];
    }
}

?>
