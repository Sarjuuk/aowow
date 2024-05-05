<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 5: Profiler g_initPath()
//  tabId 1: Tools    g_initHeader()
class ProfilesPage extends GenericPage
{
    use TrProfiler;

    private $filterObj  = null;

    protected $subCat   = '';
    protected $filter   = [];
    protected $lvTabs   = [];
    protected $roster   = 0;                                // $_GET['roster'] = 1|2|3|4 .. 2,3,4 arenateam-size (4 => 5-man), 1 guild .. it puts a resync button on the lv...

    protected $type     = Type::PROFILE;

    protected $tabId    = 1;
    protected $path     = [1, 5, 0];
    protected $tpl      = 'profiles';
    protected $scripts  = array(
        [SC_JS_FILE,  'js/filters.js'],
        [SC_JS_FILE,  'js/profile_all.js'],
        [SC_JS_FILE,  'js/profile.js'],
        [SC_CSS_FILE, 'css/Profiler.css']
    );

    protected $_get     = ['filter' => ['filter' => FILTER_UNSAFE_RAW]];

    public function __construct($pageCall, $pageParam)
    {
        $this->getSubjectFromUrl($pageParam);

        parent::__construct($pageCall, $pageParam);

        if (!CFG_PROFILER_ENABLE)
            $this->error();

        $realms = [];
        foreach (Profiler::getRealms() as $idx => $r)
        {
            if ($this->region && $r['region'] != $this->region)
                continue;

            if ($this->realm && $r['name'] != $this->realm)
                continue;

            $this->sumSubjects += DB::Characters($idx)->selectCell('SELECT count(*) FROM characters WHERE deleteInfos_Name IS NULL AND level <= ?d AND (extra_flags & ?) = 0', MAX_LEVEL, Profiler::CHAR_GMFLAGS);
            $realms[] = $idx;
        }

        $this->filterObj = new ProfileListFilter(false, ['realms' => $realms]);

        $this->name   = Util::ucFirst(Lang::game('profiles'));
        $this->subCat = $pageParam ? '='.$pageParam : '';
    }

    protected function generateTitle()
    {
        if ($this->realm)
            array_unshift($this->title, $this->realm,/* CFG_BATTLEGROUP,*/ Lang::profiler('regions', $this->region), Lang::game('profiles'));
        else if ($this->region)
            array_unshift($this->title, Lang::profiler('regions', $this->region), Lang::game('profiles'));
        else
            array_unshift($this->title, Lang::game('profiles'));
    }

    protected function generateContent()
    {
        $this->addScript([SC_JS_FILE, '?data=weight-presets.realms']);

        $conditions = [];

        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        if (!$this->filterObj->useLocalList)
        {
            $conditions[] = ['deleteInfos_Name', null];
            $conditions[] = ['level', MAX_LEVEL, '<='];     // prevents JS errors
            $conditions[] = [['extra_flags', Profiler::CHAR_GMFLAGS, '&'], 0];
        }

        // recreate form selection
        $this->filter             = $this->filterObj->getForm();
        $this->filter['query']    = $this->_get['filter'];
        $this->filter['initData'] = ['init' => 'profiles'];

        if ($x = $this->filterObj->getSetCriteria())
        {
            $this->filter['initData']['sc'] = $x;

            if ($r = array_intersect([9, 12, 15, 18], $x['cr']))
                if (count($r) == 1)
                    $this->roster = (reset($r) - 6) / 3;        // 1, 2, 3, or 4
        }

        $tabData = array(
            'id'          => 'characters',
            'hideCount'   => 1,
            'visibleCols' => ['race', 'classs', 'level', 'talents', 'achievementpoints', 'gearscore'],
            'onBeforeCreate' => '$pr_initRosterListview'        // puts a resync button on the lv
        );

        $extraCols = $this->filterObj->getExtraCols();
        if ($extraCols)
        {
            $xc = [];
            foreach ($extraCols as $idx => $col)
                if ($idx > 0)
                    $xc[] = "\$Listview.funcBox.createSimpleCol('Skill' + ".$idx.", g_spell_skills[".$idx."], '7%', 'skill' + ".$idx.")";

            $tabData['extraCols'] = $xc;
        }

        $miscParams = [];
        if ($this->realm)
            $miscParams['sv'] = $this->realm;
        if ($this->region)
            $miscParams['rg'] = $this->region;
        if ($_ = $this->filterObj->extraOpts)
            $miscParams['extraOpts'] = $_;

        if ($this->filterObj->useLocalList)
            $profiles = new LocalProfileList($conditions, $miscParams);
        else
            $profiles = new RemoteProfileList($conditions, $miscParams);

        if (!$profiles->error)
        {
            // init these chars on our side and get local ids
            if (!$this->filterObj->useLocalList)
                $profiles->initializeLocalEntries();

            $addInfoMask = PROFILEINFO_CHARACTER;

            // init roster-listview
            // $_GET['roster'] = 1|2|3|4 originally supplemented this somehow .. 2,3,4 arenateam-size (4 => 5-man), 1 guild
            if ($this->roster == 1 && !$profiles->hasDiffFields('guild') && $profiles->getField('guild'))
            {
                $tabData['roster']        = $this->roster;
                $tabData['visibleCols'][] = 'guildrank';
                $tabData['hiddenCols'][]  = 'guild';

                $this->roster  = Lang::profiler('guildRoster', [$profiles->getField('guildname')]);
            }
            else if ($this->roster && !$profiles->hasDiffFields('arenateam') && $profiles->getField('arenateam'))
            {
                $tabData['roster']        = $this->roster;
                $tabData['visibleCols'][] = 'rating';

                $addInfoMask |= PROFILEINFO_ARENA;
                $this->roster = Lang::profiler('arenaRoster', [$profiles->getField('arenateam')]);
            }
            else
                $this->roster = 0;

            $tabData['data'] = array_values($profiles->getListviewData($addInfoMask, array_filter($extraCols, function ($x) { return $x > 0; }, ARRAY_FILTER_USE_KEY)));

            if ($sc = $this->filterObj->getSetCriteria())
                if (in_array(10, $sc['cr']) && !in_array('guildrank', $tabData['visibleCols']))
                    $tabData['visibleCols'][] = 'guildrank';

            // create note if search limit was exceeded
            if ($this->filter['query'] && $profiles->getMatches() > CFG_SQL_LIMIT_DEFAULT)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_charactersfound2', $this->sumSubjects, $profiles->getMatches());
                $tabData['_truncated'] = 1;
            }
            else if ($profiles->getMatches() > CFG_SQL_LIMIT_DEFAULT)
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_charactersfound', $this->sumSubjects, 0);

            if ($this->filterObj->useLocalList)
            {
                if (!empty($tabData['note']))
                    $tabData['note'] .= ' + "<br><span class=\'r1 icon-report\'>'.Lang::profiler('complexFilter').'</span>"';
                else
                    $tabData['note'] = '<span class="r1 icon-report">'.Lang::profiler('complexFilter').'</span>';
            }

            if ($this->filterObj->error)
                $tabData['_errors'] = '$1';
        }
        else
            $this->roster = 0;


        $this->lvTabs[] = [ProfileList::$brickFile, $tabData];
    }

    protected function postCache()
    {
        // sort for dropdown-menus
        Lang::sort('game', 'cl');
        Lang::sort('game', 'ra');
    }
}

?>
