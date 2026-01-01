<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfilesBaseResponse extends TemplateResponse implements IProfilerList
{
    use TrProfilerList, TrListPage;

    protected  string $template    = 'profiles';
    protected  string $pageName    = 'profiles';
    protected ?int    $activeTab   = parent::TAB_TOOLS;
    protected  array  $breadcrumb  = [1, 5, 0];             // Tools > Profiler > Characters

    protected  array  $dataLoader  = ['weight-presets', 'realms'];
    protected  array  $scripts     = array(
        [SC_JS_FILE,  'js/filters.js'],
        [SC_JS_FILE,  'js/profile_all.js'],
        [SC_JS_FILE,  'js/profile.js'],
        [SC_CSS_FILE, 'css/Profiler.css']
    );
    protected  array  $expectedGET = array(
        'filter' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Filter::PATTERN_PARAM]],
        // 1 guild; 2,3,4 arenateam (4 => 5-man): puts a resync button on the lv (was probably used before arenateams and guilds had a dedicated page)
        'roster' => ['filter' => FILTER_VALIDATE_INT,    'options' => ['min_value' => 1, 'max_value' => 4]]
    );

    public int    $type   = Type::PROFILE;
    public string $roster = '';

    private int $sumSubjects = 0;

    public function __construct(string $rawParam)
    {
        $this->getSubjectFromUrl($rawParam);

        parent::__construct($rawParam);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generateError();

        $realms = [];
        foreach (Profiler::getRealms() as $idx => $r)
        {
            if ($this->region && $r['region'] != $this->region)
                continue;

            if ($this->realm && $r['name'] != $this->realm)
                continue;

            $this->sumSubjects += DB::Characters($idx)->selectCell('SELECT COUNT(*) FROM characters WHERE `deleteInfos_Name` IS NULL AND `level` <= ?d AND (`extra_flags` & ?) = 0', MAX_LEVEL, Profiler::CHAR_GMFLAGS);
            $realms[] = $idx;
        }

        if ($this->category)
            $this->subCat = '='.implode('.', $this->category);

        $this->filter = new ProfileListFilter($this->_get['filter'] ?? '', ['realms' => $realms]);
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
        $this->h1 = Util::ucFirst(Lang::game('profiles'));


        /*************/
        /* Menu Path */
        /*************/

        $this->followBreadcrumbPath();


        /**************/
        /* Page Title */
        /**************/

        if ($this->realm)
            array_unshift($this->title, $this->realm,/* Cfg::get('BATTLEGROUP'),*/ Lang::profiler('regions', $this->region), Lang::game('profiles'));
        else if ($this->region)
            array_unshift($this->title, Lang::profiler('regions', $this->region), Lang::game('profiles'));
        else
            array_unshift($this->title, Lang::game('profiles'));


        /****************/
        /* Main Content */
        /****************/

        $conditions = [Listview::DEFAULT_SIZE];
        if ($_ = $this->filter->getConditions())
            $conditions[] = $_;

        $fiExtraCols = $this->filter->fiExtraCols;

        $lvData        = [];
        $lvExtraCols   = [];
        $lvVisibleCols = ['race', 'classs', 'level', 'talents', 'achievementpoints', 'gearscore'];
        $lvHiddenCols  = [];
        $lvNote        = '';
        $lv_truncated  = 0;

        $this->getRegions();

        foreach ($fiExtraCols as $skill => $idx)
            $lvExtraCols[] = "\$Listview.funcBox.createSimpleCol('skill-' + ".$skill.", g_spell_skills[".$skill."], '7%', 'skill-' + ".$skill.")";

        if (!$this->filter->useLocalList)
        {
            $conditions[] = ['deleteInfos_Name', null];
            $conditions[] = ['level', MAX_LEVEL, '<='];     // prevents JS errors
            $conditions[] = [['extra_flags', Profiler::CHAR_GMFLAGS, '&'], 0];
        }

        $miscParams = ['calcTotal' => true];
        if ($this->realm)
            $miscParams['sv'] = $this->realm;
        if ($this->region)
            $miscParams['rg'] = $this->region;
        if ($_ = $this->filter->extraOpts)
            $miscParams['extraOpts'] = $_;

        if ($this->filter->useLocalList)
            $profiles = new LocalProfileList($conditions, $miscParams);
        else
            $profiles = new RemoteProfileList($conditions, $miscParams);

        if (!$profiles->error)
        {
            // init these chars on our side and get local ids
            if (!$this->filter->useLocalList)
                $profiles->initializeLocalEntries();

            // Roster only if single realm selected
            $roster = $this->realmId ? $this->_get['roster'] : 0;
            if (!$roster && $this->realmId)
                if (count($r = $this->filter->getSetCriteria(9, 12, 15, 18)) == 1)
                    $roster = ($r[0] - 6) / 3;              // 1, 2, 3, or 4

            $addInfoMask = PROFILEINFO_CHARACTER;

            // team rating filters
            if ($this->filter->getSetCriteria(13, 16, 19))
            {
                $lvVisibleCols[] = 'rating';
                $addInfoMask |= PROFILEINFO_ARENA;
            }

            // init roster-listview
            if ($roster == 1 && !$profiles->hasDiffFields('guild') && $profiles->getField('guild'))
            {
                $lvVisibleCols[] = 'guildrank';
                $lvHiddenCols[]  = 'guild';

                $this->roster = Lang::profiler('guildRoster', [$profiles->getField('guildname')]);
            }
            else if ($roster && !$profiles->hasDiffFields('arenateam') && $profiles->getField('arenateam'))
            {
                $lvVisibleCols[] = 'rating';

                $addInfoMask |= PROFILEINFO_ARENA;
                $this->roster = Lang::profiler('arenaRoster', [$profiles->getField('arenateam')]);
            }

            $lvData = $profiles->getListviewData($addInfoMask, $fiExtraCols);

            if ($this->filter->getSetCriteria(10) && !in_array('guildrank', $lvHiddenCols))
                $lvVisibleCols[] = 'guildrank';

            // create note if search limit was exceeded
            if ($this->filter->query && $profiles->getMatches() > Listview::DEFAULT_SIZE)
            {
                $lvNote = sprintf(Util::$tryFilteringString, 'LANG.lvnote_charactersfound2', $this->sumSubjects, $profiles->getMatches());
                $lv_truncated = 1;
            }
            else if ($profiles->getMatches() > Listview::DEFAULT_SIZE)
                $lvNote = sprintf(Util::$tryFilteringString, 'LANG.lvnote_charactersfound', $this->sumSubjects, 0);

            if ($this->filter->useLocalList)
            {
                if (!empty($lvNote))
                    $lvNote .= ' + "<br /><span class=\'r1 icon-report\'>'.Lang::profiler('complexFilter').'</span>"';
                else
                    $lvNote = '<span class="r1 icon-report">'.Lang::profiler('complexFilter').'</span>';
            }
        }

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated');

        $this->lvTabs->addListviewTab(new Listview(array(
            'id'             => 'characters',
            'data'           => $lvData,
            'hideCount'      => 1,
            'onBeforeCreate' => '$pr_initRosterListview',   // puts a resync button on the lv
            'extraCols'      => $lvExtraCols   ?: null,
            'visibleCols'    => $lvVisibleCols,
            'hiddenCols'     => $lvHiddenCols  ?: null,
            'note'           => $lvNote        ?: null,
            '_truncated'     => $lv_truncated  ?: null
        ), ProfileList::$brickFile));

        parent::generate();

        $this->result->registerDisplayHook('filter', [self::class, 'filterFormHook']);
    }

    public static function filterFormHook(Template\PageTemplate &$pt, ProfileListFilter $filter) : void
    {
        // sort for dropdown-menus
        Lang::sort('game', 'ra');
        Lang::sort('game', 'cl');
    }
}

?>
