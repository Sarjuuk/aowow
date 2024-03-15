<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 5: Profiler g_initPath()
//  tabId 1: Tools    g_initHeader()
class GuildsPage extends GenericPage
{
    use TrProfiler;

    private $filterObj  = null;

    protected $subCat   = '';
    protected $filter   = [];
    protected $lvTabs   = [];

    protected $type     = Type::GUILD;

    protected $tabId    = 1;
    protected $path     = [1, 5, 2];
    protected $tpl      = 'guilds';
    protected $scripts  = [[SC_JS_FILE, 'js/filters.js'], [SC_JS_FILE, 'js/profile_all.js'], [SC_JS_FILE, 'js/profile.js']];

    protected $_get     = ['filter' => ['filter' => FILTER_UNSAFE_RAW]];

    public function __construct($pageCall, $pageParam)
    {
        parent::__construct($pageCall, $pageParam);

        if (!CFG_PROFILER_ENABLE)
            $this->error();

        $this->getSubjectFromUrl($pageParam);

        $this->filterObj = new GuildListFilter();

        foreach (Profiler::getRealms() as $idx => $r)
        {
            if ($this->region && $r['region'] != $this->region)
                continue;

            if ($this->realm && $r['name'] != $this->realm)
                continue;

            $this->sumSubjects += DB::Characters($idx)->selectCell('SELECT COUNT(*) FROM guild');
        }

        $this->name   = Lang::profiler('guilds');
        $this->subCat = $pageParam ? '='.$pageParam : '';
    }

    protected function generateTitle()
    {
        if ($this->realm)
            array_unshift($this->title, $this->realm,/* CFG_BATTLEGROUP,*/ Lang::profiler('regions', $this->region), Lang::profiler('guilds'));
        else if ($this->region)
            array_unshift($this->title, Lang::profiler('regions', $this->region), Lang::profiler('guilds'));
        else
            array_unshift($this->title, Lang::profiler('guilds'));
    }

    protected function generateContent()
    {
        $this->addScript([SC_JS_FILE, '?data=realms']);

        $conditions = array(
            ['c.deleteInfos_Account', null],
            ['c.level', MAX_LEVEL, '<='],                   // prevents JS errors
            [['c.extra_flags', Profiler::CHAR_GMFLAGS, '&'], 0]
        );
        if ($_ = $this->filterObj->getConditions())
            $conditions[] = $_;

        // recreate form selection
        $this->filter = $this->filterObj->getForm();
        $this->filter['query']    = $this->_get['filter'];
        $this->filter['initData'] = ['type' => 'guilds'];

        $tabData = array(
            'id'          => 'guilds',
            'hideCount'   => 1,
            'sort'        => [-3],
            'visibleCols' => ['members', 'achievementpoints', 'gearscore'],
            'hiddenCols'  => ['guild'],
        );

        $miscParams = [];
        if ($this->realm)
            $miscParams['sv'] = $this->realm;
        if ($this->region)
            $miscParams['rg'] = $this->region;

        $guilds = new RemoteGuildList($conditions, $miscParams);
        if (!$guilds->error)
        {
            $guilds->initializeLocalEntries();

            $dFields = $guilds->hasDiffFields(['faction', 'type']);
            if (!($dFields & 0x1))
                $tabData['hiddenCols'][] = 'faction';

            if (($dFields & 0x2))
                $tabData['visibleCols'][] = 'size';

            $tabData['data'] = array_values($guilds->getListviewData());

            // create note if search limit was exceeded
            if ($this->filter['query'] && $guilds->getMatches() > CFG_SQL_LIMIT_DEFAULT)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_guildsfound2', $this->sumSubjects, $guilds->getMatches());
                $tabData['_truncated'] = 1;
            }
            else if ($guilds->getMatches() > CFG_SQL_LIMIT_DEFAULT)
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_guildsfound', $this->sumSubjects, 0);

            if ($this->filterObj->error)
                $tabData['_errors'] = 1;
        }

        $this->lvTabs[] = [GuildList::$brickFile, $tabData, 'membersCol'];
    }

    protected function postCache()
    {
        // sort for dropdown-menus
        Lang::sort('game', 'cl');
        Lang::sort('game', 'ra');
    }
}

?>
