<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class NpcsBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type        = Type::NPC;
    protected  int    $cacheType   = CACHE_TYPE_LIST_PAGE;

    protected  string $template    = 'npcs';
    protected  string $pageName    = 'npcs';
    protected ?int    $activeTab   = parent::TAB_DATABASE;
    protected  array  $breadcrumb  = [0, 4];

    protected  array  $dataLoader  = ['zones'];
    protected  array  $scripts     = [[SC_JS_FILE, 'js/filters.js']];
    protected  array  $expectedGET = array(
        'filter' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Filter::PATTERN_PARAM]]
    );
    protected  array  $validCats   = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];

    public bool $petFamPanel = false;

    public function __construct(string $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageParam);

        $this->subCat = $pageParam !== '' ? '='.$pageParam : '';
        $this->filter = new CreatureListFilter($this->_get['filter'] ?? '', ['parentCats' => $this->category]);
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
        $this->h1 = Lang::game('npcs');

        $conditions = [Listview::DEFAULT_SIZE];
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($_ = $this->filter->getConditions())
            $conditions[] = $_;

        if ($this->category)
        {
            $conditions[] = ['type', $this->category[0]];
            $this->petFamPanel = $this->category[0] == 1;
        }

        $fiForm    = $this->filter->values;
        $fiRepCols = $this->filter->fiReputationCols;


        /*************/
        /* Menu Path */
        /*************/

        if ($this->category)
            $this->breadcrumb[] = $this->category[0];

        if (count($fiForm['fa']) == 1)
            $this->breadcrumb[] = $fiForm['fa'][0];


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1);
        if ($this->category)
            array_unshift($this->title, Lang::npc('cat', $this->category[0]));

        if (count($fiForm['fa']) == 1)
            array_unshift($this->title, Lang::game('fa', $fiForm['fa'][0]));


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons[BUTTON_WOWHEAD] = true;

        // beast subtypes are selected via filter
        $tabData = ['data' => []];
        $npcs    = new CreatureList($conditions, ['extraOpts' => $this->filter->extraOpts, 'calcTotal' => true]);
        if (!$npcs->error)
        {
            $tabData['data'] = $npcs->getListviewData($fiRepCols ? NPCINFO_REP : 0x0);
            if ($fiRepCols)                                 // never use pretty-print
                $tabData['extraCols'] = '$fi_getReputationCols('.Util::toJSON($fiRepCols, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE).')';
            else if ($this->filter->fiExtraCols)
                $tabData['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

            if ($this->category)
                $tabData['hiddenCols'] = ['type'];

            // create note if search limit was exceeded
            if ($npcs->getMatches() > Listview::DEFAULT_SIZE)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_npcsfound', $npcs->getMatches(), Listview::DEFAULT_SIZE);
                $tabData['_truncated'] = 1;
            }
        }

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview($tabData, CreatureList::$brickFile));

        parent::generate();

        $this->setOnCacheLoaded([self::class, 'onBeforeDisplay']);
    }

    public static function onBeforeDisplay() : void
    {
        // sort for dropdown-menus
        Lang::sort('game', 'fa');
    }
}

?>
