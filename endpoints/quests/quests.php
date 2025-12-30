<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class QuestsBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    private const SUB_SUB_CAT = array(
        // Quest Hubs
        3679 => 3519,   4024 => 3537,   25   => 46,     1769 => 361,
        // Startzones: Horde
        132  => 1,      9    => 12,     3431 => 3430,   154  => 85,
        // Startzones: Alliance
        3526 => 3524,   363  => 14,     220  => 215,    188  => 141,
        // Group: Caverns of Time
        2366 => 1941,   2367 => 1941,   4100 => 1941,
        // Group: Hellfire Citadell
        3562 => 3535,   3713 => 3535,   3714 => 3535,
        // Group: Auchindoun
        3789 => 3688,   3790 => 3688,   3791 => 3688,   3792 => 3688,
        // Group: Tempest Keep
        3847 => 3842,   3848 => 3842,   3849 => 3842,
        // Group: Coilfang Reservoir
        3715 => 3905,   3716 => 3905,   3717 => 3905,
        // Group: Icecrown Citadel
        4809 => 4522,   4813 => 4522,   4820 => 4522
    );

    protected  int    $type        = Type::QUEST;
    protected  int    $cacheType   = CACHE_TYPE_LIST_PAGE;

    protected  string $template    = 'quests';
    protected  string $pageName    = 'quests';
    protected ?int    $activeTab   = parent::TAB_DATABASE;
    protected  array  $breadcrumb  = [0, 3];

    protected  array  $scripts     = [[SC_JS_FILE, 'js/filters.js']];
    protected  array  $expectedGET = array(
        'filter' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Filter::PATTERN_PARAM]]
    );
    protected  array  $validCats   = Game::QUEST_CLASSES;

    public function __construct(string $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageParam);

        $this->subCat = $pageParam !== '' ? '='.$pageParam : '';
        $this->filter = new QuestListFilter($this->_get['filter'] ?? '', ['parentCats' => $this->category]);
        if ($this->filter->shouldReload)
        {
            $_SESSION['error']['fi'] = $this->filter::class;
            $get = $this->filter->buildGETParam();
            $this->forward('?' . $this->pageName . $this->subCat . ($get ? '&filter=' . $get : ''));
        }
        $this->filterError = $this->filter->error;
    }

    protected function generate() : void
    {
        $this->h1   = Util::ucFirst(Lang::game('quests'));

        $conditions = [Listview::DEFAULT_SIZE];
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($_ = $this->filter->getConditions())
            $conditions[] = $_;

        if (isset($this->category[1]))
            $conditions[] = ['zoneOrSort', $this->category[1]];
        else if (isset($this->category[0]))
            $conditions[] = ['zoneOrSort', $this->validCats[$this->category[0]]];


        /*************/
        /* Menu Path */
        /*************/

        foreach ($this->category as $c)
            $this->breadcrumb[] = $c;

        if (isset($this->category[1]) && isset(self::SUB_SUB_CAT[$this->category[1]]))
            array_splice($this->breadcrumb, 3, 0, self::SUB_SUB_CAT[$this->category[1]]);


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1);

        if (isset($this->category[1]))
            array_unshift($this->title, Lang::quest('cat', $this->category[0], $this->category[1]));
        else if (isset($this->category[0]))
        {
            $c0 = Lang::quest('cat', $this->category[0]);
            array_unshift($this->title, is_array($c0) ? $c0[0] : $c0);
        }


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons[BUTTON_WOWHEAD] = true;
        if ($fiQuery = $this->filter->buildGETParam())
            $this->wowheadLink .= '&filter='.$fiQuery;

        $quests = new QuestList($conditions, ['extraOpts' => $this->filter->extraOpts, 'calcTotal' => true]);

        $this->extendGlobalData($quests->getJSGlobals());

        $tabData = ['data' => $quests->getListviewData()];

        if ($rc = $this->filter->fiReputationCols)
            $tabData['extraCols'] = '$fi_getReputationCols('.json_encode($rc, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE).')';
        else if ($this->filter->fiExtraCols)
            $tabData['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

        // create note if search limit was exceeded
        if ($quests->getMatches() > Listview::DEFAULT_SIZE)
        {
            $tabData['note']       = sprintf(Util::$tryFilteringString, 'LANG.lvnote_questsfound', $quests->getMatches(), Listview::DEFAULT_SIZE);
            $tabData['_truncated'] = 1;
        }
        else if (isset($this->category[1]) && $this->category[1] > 0)
            $tabData['note'] = '$$WH.sprintf(LANG.lvnote_questgivers, '.$this->category[1].', g_zones['.$this->category[1].'], '.$this->category[1].')';

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview($tabData, QuestList::$brickFile));

        parent::generate();
    }
}

?>
