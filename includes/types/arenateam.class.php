<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ArenaTeamList extends BaseType
{
    use profilerHelper, listviewHelper;

    private $rankOrder = [];

    public function getListviewData()
    {
        $data = [];
        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'name'              => $this->curTpl['name'],
                'realm'             => Profiler::urlize($this->curTpl['realmName'], true),
                'realmname'         => $this->curTpl['realmName'],
             // 'battlegroup'       => Profiler::urlize($this->curTpl['battlegroup']),  // was renamed to subregion somewhere around cata release
             // 'battlegroupname'   => $this->curTpl['battlegroup'],
                'region'            => Profiler::urlize($this->curTpl['region']),
                'faction'           => $this->curTpl['faction'],
                'size'              => $this->curTpl['type'],
                'rank'              => $this->curTpl['rank'],
                'wins'              => $this->curTpl['seasonWins'],
                'games'             => $this->curTpl['seasonGames'],
                'rating'            => $this->curTpl['rating'],
                'members'           => $this->curTpl['members']
            );
        }

        return array_values($data);
    }

    public function renderTooltip() {}
    public function getJSGlobals($addMask = 0) {}
}


class ArenaTeamListFilter extends Filter
{
    public    $extraOpts     = [];
    protected $genericFilter = [];

    protected $inputFields = array(
        'na' => [FILTER_V_REGEX,    parent::PATTERN_NAME, false], // name - only printable chars, no delimiter
        'ma' => [FILTER_V_EQUAL,    1,                    false], // match any / all filter
        'ex' => [FILTER_V_EQUAL,    'on',                 false], // only match exact
        'si' => [FILTER_V_LIST,     [1, 2],               false], // side
        'sz' => [FILTER_V_LIST,     [2, 3, 5],            false], // tema size
        'rg' => [FILTER_V_CALLBACK, 'cbRegionCheck',      false], // region
        'sv' => [FILTER_V_CALLBACK, 'cbServerCheck',      false], // server
    );

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = $this->fiData['v'];

        // region (rg), battlegroup (bg) and server (sv) are passed to ArenaTeamList as miscData and handled there

        // name [str]
        if (!empty($_v['na']))
            if ($_ = $this->modularizeString(['at.name'], $_v['na'], !empty($_v['ex']) && $_v['ex'] == 'on'))
                $parts[] = $_;

        // side [list]
        if (!empty($_v['si']))
        {
            if ($_v['si'] == 1)
                $parts[] = ['c.race', [1, 3, 4, 7, 11]];
            else if ($_v['si'] == 2)
                $parts[] = ['c.race', [2, 5, 6, 8, 10]];
        }

        // size [int]
        if (!empty($_v['sz']))
            $parts[] = ['at.type', $_v['sz']];

        return $parts;
    }

    protected function cbRegionCheck(&$v)
    {
        if (in_array($v, Util::$regions))
        {
            $this->parentCats[0] = $v;                      // directly redirect onto this region
            $v = '';                                        // remove from filter

            return true;
        }

        return false;
    }

    protected function cbServerCheck(&$v)
    {
        foreach (Profiler::getRealms() as $realm)
            if ($realm['name'] == $v)
            {
                $this->parentCats[1] = Profiler::urlize($v);// directly redirect onto this server
                $v = '';                                    // remove from filter

                return true;
            }

        return false;
    }
}


class RemoteArenaTeamList extends ArenaTeamList
{
    protected   $queryBase = 'SELECT `at`.*, `at`.`arenaTeamId` AS ARRAY_KEY FROM arena_team at';
    protected   $queryOpts = array(
                    'at'  => [['atm', 'c'], 'g' => 'ARRAY_KEY', 'o' => 'rating DESC'],
                    'atm' => ['j' => 'arena_team_member atm ON atm.arenaTeamId = at.arenaTeamId'],
                    'c'   => ['j' => 'characters c ON c.guid = atm.guid AND c.deleteInfos_Account IS NULL AND c.level <= 80 AND (c.extra_flags & '.Profiler::CHAR_GMFLAGS.') = 0', 's' => ', BIT_OR(IF(c.race IN (1, 3, 4, 7, 11), 1, 2)) - 1 AS faction']
                );

    private     $members   = [];
    private     $rankOrder = [];

    public function __construct($conditions = [], $miscData = null)
    {
        // select DB by realm
        if (!$this->selectRealms($miscData))
        {
            trigger_error('no access to auth-db or table realmlist is empty', E_USER_WARNING);
            return;
        }

        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        // ranks in DB are inaccurate. recalculate from rating (fetched as DESC from DB)
        foreach ($this->dbNames as $rId => $__)
            foreach ([2, 3, 5] as $type)
                $this->rankOrder[$rId][$type] = DB::Characters($rId)->selectCol('SELECT arenaTeamId FROM arena_team WHERE `type` = ?d ORDER BY rating DESC', $type);

        reset($this->dbNames);                              // only use when querying single realm
        $realmId     = key($this->dbNames);
        $realms      = Profiler::getRealms();
        $distrib     = [];

        // post processing
        foreach ($this->iterate() as $guid => &$curTpl)
        {
            // battlegroup
            $curTpl['battlegroup'] = Cfg::get('BATTLEGROUP');

            // realm, rank
            $r = explode(':', $guid);
            if (!empty($realms[$r[0]]))
            {
                $curTpl['realm']     = $r[0];
                $curTpl['realmName'] = $realms[$r[0]]['name'];
                $curTpl['region']    = $realms[$r[0]]['region'];
                $curTpl['rank']      = array_search($curTpl['arenaTeamId'], $this->rankOrder[$r[0]][$curTpl['type']]) + 1;
            }
            else
            {
                trigger_error('arena team #'.$guid.' belongs to nonexistant realm #'.$r, E_USER_WARNING);
                unset($this->templates[$guid]);
                continue;
            }

            // empty name
            if (!$curTpl['name'])
            {
                trigger_error('arena team #'.$guid.' on realm #'.$r.' has empty name.', E_USER_WARNING);
                unset($this->templates[$guid]);
                continue;
            }

            // team members
            $this->members[$r[0]][$r[1]] = $r[1];

            // equalize distribution
            if (empty($distrib[$curTpl['realm']]))
                $distrib[$curTpl['realm']] = 1;
            else
                $distrib[$curTpl['realm']]++;
        }

        // get team members
        foreach ($this->members as $realmId => &$teams)
            $teams = DB::Characters($realmId)->select('
                SELECT
                    at.arenaTeamId AS ARRAY_KEY, c.guid AS ARRAY_KEY2, c.name AS "0", c.class AS "1", IF(at.captainguid = c.guid, 1, 0) AS "2"
                FROM
                    arena_team at
                JOIN
                    arena_team_member atm ON atm.arenaTeamId = at.arenaTeamId JOIN characters c ON c.guid = atm.guid
                WHERE
                    at.arenaTeamId IN (?a) AND
                    c.deleteInfos_Account IS NULL AND
                    c.level <= ?d AND
                    (c.extra_flags & ?d) = 0',
                $teams,
                MAX_LEVEL,
                Profiler::CHAR_GMFLAGS
            );

        // equalize subject distribution across realms
        $limit = Cfg::get('SQL_LIMIT_DEFAULT');
        foreach ($conditions as $c)
            if (is_int($c))
                $limit = $c;

        $total = array_sum($distrib);
        foreach ($distrib as &$d)
            $d = ceil($limit * $d / $total);

        foreach ($this->iterate() as $guid => &$curTpl)
        {
            if ($limit <= 0 || $distrib[$curTpl['realm']] <= 0)
            {
                unset($this->templates[$guid]);
                continue;
            }

            $r = explode(':', $guid);
            if (isset($this->members[$r[0]][$r[1]]))
                $curTpl['members'] = array_values($this->members[$r[0]][$r[1]]);  // [name, classId, isCaptain]

            $distrib[$curTpl['realm']]--;
            $limit--;
        }
    }

    public function initializeLocalEntries()
    {
        $profiles = [];
        // init members for tooltips
        foreach ($this->members as $realmId => $teams)
        {
            $gladiators = [];
            foreach ($teams as $team)
                $gladiators = array_merge($gladiators, array_keys($team));

            $profiles[$realmId] = new RemoteProfileList(array(['c.guid', $gladiators], Cfg::get('SQL_LIMIT_NONE')), ['sv' => $realmId]);

            if (!$profiles[$realmId]->error)
                $profiles[$realmId]->initializeLocalEntries();
        }

        $data = [];
        foreach ($this->iterate() as $guid => $__)
        {
            $data[$guid] = array(
                'realm'     => $this->getField('realm'),
                'realmGUID' => $this->getField('arenaTeamId'),
                'name'      => $this->getField('name'),
                'nameUrl'   => Profiler::urlize($this->getField('name')),
                'type'      => $this->getField('type'),
                'rating'    => $this->getField('rating'),
                'cuFlags'   => PROFILER_CU_NEEDS_RESYNC
            );
        }

        // basic arena team data
        foreach (Util::createSqlBatchInsert($data) as $ins)
            DB::Aowow()->query('INSERT INTO ?_profiler_arena_team (?#) VALUES '.$ins.' ON DUPLICATE KEY UPDATE `id` = `id`', array_keys(reset($data)));

        // merge back local ids
        $localIds = DB::Aowow()->selectCol(
            'SELECT CONCAT(realm, ":", realmGUID) AS ARRAY_KEY, id FROM ?_profiler_arena_team WHERE realm IN (?a) AND realmGUID IN (?a)',
            array_column($data, 'realm'),
            array_column($data, 'realmGUID')
        );

        foreach ($this->iterate() as $guid => &$_curTpl)
            if (isset($localIds[$guid]))
                $_curTpl['id'] = $localIds[$guid];


        // profiler_arena_team_member requires profiles and arena teams to be filled
        foreach ($this->members as $realmId => $teams)
        {
            if (empty($profiles[$realmId]))
                continue;

            $memberData = [];
            foreach ($teams as $teamId => $team)
            {
                $clearMembers = [];
                foreach ($team as $memberId => $member)
                {
                    $clearMembers[] = $profiles[$realmId]->getEntry($realmId.':'.$memberId)['id'];
                    $memberData[]   = array(
                        'arenaTeamId' => $localIds[$realmId.':'.$teamId],
                        'profileId'   => $profiles[$realmId]->getEntry($realmId.':'.$memberId)['id'],
                        'captain'     => $member[2]
                    );
                }

                // Delete members from other teams of the same type
                DB::Aowow()->query(
                   'DELETE atm
                    FROM   ?_profiler_arena_team_member atm
                    JOIN   ?_profiler_arena_team at ON atm.`arenaTeamId` = at.`id` AND at.`type` = ?d
                    WHERE  atm.`profileId` IN (?a)',
                    $data[$realmId.':'.$teamId]['type'] ?? 0,
                    $clearMembers
                );
            }

            foreach (Util::createSqlBatchInsert($memberData) as $ins)
                DB::Aowow()->query('INSERT IGNORE INTO ?_profiler_arena_team_member (?#) VALUES '.$ins, array_keys(reset($memberData)));
        }
    }
}


class LocalArenaTeamList extends ArenaTeamList
{
    protected       $queryBase = 'SELECT at.*, at.id AS ARRAY_KEY FROM ?_profiler_arena_team at';

    public function __construct($conditions = [], $miscData = null)
    {
        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        $realms = Profiler::getRealms();

        // post processing
        $members = DB::Aowow()->selectCol('SELECT *, arenaTeamId AS ARRAY_KEY, profileId AS ARRAY_KEY2 FROM ?_profiler_arena_team_member WHERE arenaTeamId IN (?a)', $this->getFoundIDs());

        foreach ($this->iterate() as $id => &$curTpl)
        {
            if ($curTpl['realm'] && !isset($realms[$curTpl['realm']]))
                continue;

            if (isset($realms[$curTpl['realm']]))
            {
                $curTpl['realmName'] = $realms[$curTpl['realm']]['name'];
                $curTpl['region']    = $realms[$curTpl['realm']]['region'];
            }

            // battlegroup
            $curTpl['battlegroup'] = Cfg::get('BATTLEGROUP');

            $curTpl['members'] = $members[$id];
        }
    }

    public function getProfileUrl()
    {
        $url = '?arena-team=';

        return $url.implode('.', array(
            Profiler::urlize($this->getField('region')),
            Profiler::urlize($this->getField('realmName')),
            Profiler::urlize($this->getField('name'))
        ));
    }
}


?>
