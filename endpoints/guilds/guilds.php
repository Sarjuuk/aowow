<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuildsBaseResponse extends TemplateResponse implements IProfilerList
{
    use TrProfilerList, TrListPage;

    protected  string $template    = 'guilds';
    protected  string $pageName    = 'guilds';
    protected ?int    $activeTab   = parent::TAB_TOOLS;
    protected  array  $breadcrumb  = [1, 5, 2];             // Tools > Profiler > Guilds

    protected  array  $dataLoader  = ['realms'];
    protected  array  $scripts     = array(
        [SC_JS_FILE, 'js/filters.js'],
        [SC_JS_FILE, 'js/profile_all.js'],
        [SC_JS_FILE, 'js/profile.js']
    );
    protected  array  $expectedGET = array(
        'filter' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Filter::PATTERN_PARAM]]
    );

    public int $type = Type::GUILD;

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

            $this->sumSubjects += DB::Characters($idx)->selectCell('SELECT count(*) FROM guild');
            $realms[] = $idx;
        }

        if ($this->category)
            $this->subCat = '='.implode('.', $this->category);

        $this->filter = new GuildListFilter($this->_get['filter'] ?? '', ['realms' => $realms]);
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
        $this->h1 = Lang::game('guilds');


        /*************/
        /* Menu Path */
        /*************/

        $this->followBreadcrumbPath();


        /**************/
        /* Page Title */
        /**************/

        if ($this->realm)
            array_unshift($this->title, $this->realm,/* Cfg::get('BATTLEGROUP'),*/ Lang::profiler('regions', $this->region), Lang::game('guilds'));
        else if ($this->region)
            array_unshift($this->title, Lang::profiler('regions', $this->region), Lang::game('guilds'));
        else
            array_unshift($this->title, Lang::game('guilds'));


        /****************/
        /* Main Content */
        /****************/

        $conditions = array(
            Listview::DEFAULT_SIZE,
            ['c.deleteInfos_Account', null],
            ['c.level', MAX_LEVEL, '<='],                   // prevents JS errors
            [['c.extra_flags', Profiler::CHAR_GMFLAGS, '&'], 0]
        );
        if ($_ = $this->filter->getConditions())
            $conditions[] = $_;

        $this->getRegions();

        $tabData = array(
            'id'          => 'guilds',
            'data'        => [],
            'hideCount'   => 1,
            'sort'        => [-3],
            'visibleCols' => ['members', 'achievementpoints', 'gearscore'],
            'hiddenCols'  => ['guild']
        );

        if ($this->filter->values['si'])
            $tabData['hiddenCols'][] = 'faction';

        $miscParams = ['calcTotal' => true];
        if ($this->realm)
            $miscParams['sv'] = $this->realm;
        if ($this->region)
            $miscParams['rg'] = $this->region;

        $guilds = new RemoteGuildList($conditions, $miscParams);
        if (!$guilds->error)
        {
            $guilds->initializeLocalEntries();

            $tabData['data'] = $guilds->getListviewData();

            // create note if search limit was exceeded
            if ($this->filter->query && $guilds->getMatches() > Listview::DEFAULT_SIZE)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_guildsfound2', $this->sumSubjects, $guilds->getMatches());
                $tabData['_truncated'] = 1;
            }
            else if ($guilds->getMatches() > Listview::DEFAULT_SIZE)
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_guildsfound', $this->sumSubjects, 0);
        }

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated');

        $this->lvTabs->addListviewTab(new Listview($tabData, GuildList::$brickFile, 'membersCol'));

        parent::generate();

        $this->result->registerDisplayHook('filter', [self::class, 'filterFormHook']);
    }

    public static function filterFormHook(Template\PageTemplate &$pt, GuildListFilter $filter) : void
    {
        // sort for dropdown-menus
        Lang::sort('game', 'cl');
        Lang::sort('game', 'ra');
    }
}

?>
