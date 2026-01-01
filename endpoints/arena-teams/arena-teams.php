<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ArenateamsBaseResponse extends TemplateResponse implements IProfilerList
{
    use TrProfilerList, TrListPage;

    protected  string $template    = 'arena-teams';
    protected  string $pageName    = 'arena-teams';
    protected ?int    $activeTab   = parent::TAB_TOOLS;
    protected  array  $breadcrumb  = [1, 5, 3];             // Tools > Profiler > Arena Teams

    protected  array  $dataLoader  = ['realms'];
    protected  array  $scripts     = array(
        [SC_JS_FILE, 'js/filters.js'],
        [SC_JS_FILE, 'js/profile_all.js'],
        [SC_JS_FILE, 'js/profile.js']
    );
    protected  array  $expectedGET = array(
        'filter' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Filter::PATTERN_PARAM]]
    );

    public int $type = Type::ARENA_TEAM;

    private int $sumSubjects = 0;

    public function __construct(string $rawParam)
    {
        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generateError();

        $this->getSubjectFromUrl($rawParam);

        parent::__construct($rawParam);

        $realms = [];
        foreach (Profiler::getRealms() as $idx => $r)
        {
            if ($this->region && $r['region'] != $this->region)
                continue;

            if ($this->realm && $r['name'] != $this->realm)
                continue;

            $this->sumSubjects += DB::Characters($idx)->selectCell('SELECT count(*) FROM arena_team');
            $realms[] = $idx;
        }

        if ($this->category)
            $this->subCat = '='.implode('.', $this->category);

        $this->filter = new ArenaTeamListFilter($this->_get['filter'] ?? '', ['realms' => $realms]);
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
        $this->h1 = Lang::game('arenateams');


        /*************/
        /* Menu Path */
        /*************/

        $this->followBreadcrumbPath();


        /**************/
        /* Page Title */
        /**************/

        if ($this->realm)
            array_unshift($this->title, $this->realm,/* Cfg::get('BATTLEGROUP'),*/ Lang::profiler('regions', $this->region), Lang::game('arenateams'));
        else if ($this->region)
            array_unshift($this->title, Lang::profiler('regions', $this->region), Lang::game('arenateams'));
        else
            array_unshift($this->title, Lang::game('arenateams'));


        /****************/
        /* Main Content */
        /****************/

        $conditions = [Listview::DEFAULT_SIZE];
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = ['at.seasonGames', 0, '>'];

        if ($_ = $this->filter->getConditions())
            $conditions[] = $_;

        $this->getRegions();

        $tabData = array(
            'id'          => 'arena-teams',
            'data'        => [],
            'hideCount'   => 1,
            'sort'        => [-16],
            'extraCols'   => ['$Listview.extraCols.members'],
            'visibleCols' => ['rank', 'wins', 'losses', 'rating'],
            'hiddenCols'  => ['arenateam', 'guild']
        );

        if (!$this->filter->values['sz'])
            $tabData['visibleCols'][] = 'size';

        if ($this->filter->values['si'])
            $tabData['hiddenCols'][] = 'faction';

        $miscParams = ['calcTotal' => true];
        if ($this->realm)
            $miscParams['sv'] = $this->realm;
        if ($this->region)
            $miscParams['rg'] = $this->region;

        $teams = new RemoteArenaTeamList($conditions, $miscParams);
        if (!$teams->error)
        {
            $teams->initializeLocalEntries();

            $tabData['data'] = $teams->getListviewData();

            // create note if search limit was exceeded
            if ($this->filter->query && $teams->getMatches() > Listview::DEFAULT_SIZE)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_arenateamsfound2', $this->sumSubjects, $teams->getMatches());
                $tabData['_truncated'] = 1;
            }
            else if ($teams->getMatches() > Listview::DEFAULT_SIZE)
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_arenateamsfound', $this->sumSubjects, 0);
        }

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated');

        $this->lvTabs->addListviewTab(new Listview($tabData, ArenaTeamList::$brickFile, 'membersCol'));

        parent::generate();

        $this->result->registerDisplayHook('filter', [self::class, 'filterFormHook']);
    }

    public static function filterFormHook(Template\PageTemplate &$pt, ArenaTeamListFilter $filter) : void
    {
        // sort for dropdown-menus
        Lang::sort('game', 'cl');
        Lang::sort('game', 'ra');
    }
}

?>
