<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 5: Profiler g_initPath()
//  tabId 1: Tools    g_initHeader()
class ArenaTeamPage extends GenericPage
{
    use TrProfiler;

    protected $lvTabs     = [];

    protected $type       = Type::ARENA_TEAM;

    protected $subject    = null;
    protected $redButtons = [];
    protected $extraHTML  = null;

    protected $tabId      = 1;
    protected $path       = [1, 5, 3];
    protected $tpl        = 'roster';
    protected $scripts    = array(
        [SC_JS_FILE,  'js/profile_all.js'],
        [SC_JS_FILE,  'js/profile.js'],
        [SC_CSS_FILE, 'css/Profiler.css']
    );

    public function __construct($pageCall, $pageParam)
    {
        parent::__construct($pageCall, $pageParam);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->error();

        $params = array_map('urldecode', explode('.', $pageParam));
        if ($params[0])
            $params[0] = Profiler::urlize($params[0]);
        if (isset($params[1]))
            $params[1] = Profiler::urlize($params[1]);

        if (count($params) == 1 && intval($params[0]))
        {
            $this->subject = new LocalArenaTeamList(array(['at.id', intval($params[0])]));
            if ($this->subject->error)
                $this->notFound();

            header('Location: '.$this->subject->getProfileUrl(), true, 302);
        }
        else if (count($params) == 3)
        {
            $this->getSubjectFromUrl($pageParam);
            if (!$this->subjectName)
                $this->notFound();

            // 3 possibilities
            // 1) already synced to aowow
            if ($subject = DB::Aowow()->selectRow('SELECT id, realmGUID, cuFlags FROM ?_profiler_arena_team WHERE realm = ?d AND nameUrl = ?', $this->realmId, Profiler::urlize($this->subjectName)))
            {
                if ($subject['cuFlags'] & PROFILER_CU_NEEDS_RESYNC)
                {
                    $this->handleIncompleteData($subject['realmGUID']);
                    return;
                }

                $this->subjectGUID = $subject['id'];
                $this->subject     = new LocalArenaTeamList(array(['id', $subject['id']]));
                if ($this->subject->error)
                    $this->notFound();

                $this->name = sprintf(Lang::profiler('arenaRoster'), $this->subject->getField('name'));
            }
            // 2) not yet synced but exists on realm (wont work if we get passed an urlized name, but there is nothing we can do about it)
            else if ($team = DB::Characters($this->realmId)->selectRow('SELECT at.arenaTeamId AS realmGUID, at.name, at.type FROM arena_team at WHERE at.name = ?', Util::ucFirst($this->subjectName)))
            {
                $team['realm']   = $this->realmId;
                $team['cuFlags'] = PROFILER_CU_NEEDS_RESYNC;

                // create entry from realm with basic info
                DB::Aowow()->query('INSERT IGNORE INTO ?_profiler_arena_team (?#) VALUES (?a)', array_keys($team), array_values($team));

                $this->handleIncompleteData($team['realmGUID']);
            }
            // 3) does not exist at all
            else
                $this->notFound();
        }
        else
            $this->notFound();
    }

    protected function generateTitle()
    {
        $team  = !empty($this->subject) ? $this->subject->getField('name') : $this->subjectName;
        $team .= ' ('.$this->realm.' - '.Lang::profiler('regions', $this->region).')';

        array_unshift($this->title, $team, Util::ucFirst(Lang::profiler('profiler')));
    }

    protected function generateContent()
    {
        if ($this->doResync)
            return;

        $this->addScript([SC_JS_FILE, '?data=realms.weight-presets']);

        $this->redButtons[BUTTON_RESYNC] = [$this->subjectGUID, 'arena-team'];

        /****************/
        /* Main Content */
        /****************/


        // statistic calculations here


        /**************/
        /* Extra Tabs */
        /**************/

        // tab: members
        $member = new LocalProfileList(array(['atm.arenaTeamId', $this->subjectGUID]));
        if (!$member->error)
        {
            $this->lvTabs[] = [ProfileList::$brickFile, array(
                'data'        => array_values($member->getListviewData(PROFILEINFO_CHARACTER | PROFILEINFO_ARENA)),
                'sort'        => [-15],
                'visibleCols' => ['race', 'classs', 'level', 'talents', 'gearscore', 'rating', 'wins', 'losses'],
                'hiddenCols'  => ['guild', 'location']
            )];
        }
    }

    public function notFound(string $title = '', string $msg = '') : void
    {
        parent::notFound($title ?: Util::ucFirst(Lang::profiler('profiler')), $msg ?: Lang::profiler('notFound', 'arenateam'));
    }

    private function handleIncompleteData($teamGuid)
    {
        //display empty page and queue status
        $newId = Profiler::scheduleResync(Type::ARENA_TEAM, $this->realmId, $teamGuid);

        $this->doResync = ['arena-team', $newId];
        $this->initialSync();
    }
}

?>
