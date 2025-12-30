<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ArenaTeamList extends DBTypeList
{
    use profilerHelper, listviewHelper;

    public static int $contribute = CONTRIBUTE_NONE;

    public function getListviewData() : array
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

        return $data;
    }

    // plz dont..
    public static function getName(int|string $id) : ?LocString { return null; }

    public function renderTooltip() : ?string { return null; }
    public function getJSGlobals(int $addMask = 0) : array { return []; }
}


class ArenaTeamListFilter extends Filter
{
    use TrProfilerFilter;

    protected string $type          = 'arenateams';
    protected static array $genericFilter = [];
    protected static array $inputFields   = array(
        'na' => [parent::V_REGEX,    parent::PATTERN_NAME, false], // name - only printable chars, no delimiter
        'ma' => [parent::V_EQUAL,    1,                    false], // match any / all filter
        'ex' => [parent::V_EQUAL,    'on',                 false], // only match exact
        'si' => [parent::V_LIST,     [1, 2],               false], // side
        'sz' => [parent::V_LIST,     [2, 3, 5],            false], // tema size
        'rg' => [parent::V_CALLBACK, 'cbRegionCheck',      false], // region
        'bg' => [parent::V_EQUAL,    null,                 false], // battlegroup - unsued here, but var expected by template
        'sv' => [parent::V_CALLBACK, 'cbServerCheck',      false]  // server
    );

    public array $extraOpts = [];

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = $this->values;

        // region (rg), battlegroup (bg) and server (sv) are passed to ArenaTeamList as miscData and handled there

        // name [str]
        if ($_v['na'])
            if ($_ = $this->tokenizeString(['at.name'], $_v['na'], $_v['ex'] == 'on'))
                $parts[] = $_;

        // side [list]
        if ($_v['si'] == SIDE_ALLIANCE)
            $parts[] = ['c.race', ChrRace::fromMask(ChrRace::MASK_ALLIANCE)];
        else if ($_v['si'] == SIDE_HORDE)
            $parts[] = ['c.race', ChrRace::fromMask(ChrRace::MASK_HORDE)];

        // size [int]
        if ($_v['sz'])
            $parts[] = ['at.type', $_v['sz']];

        return $parts;
    }
}


class RemoteArenaTeamList extends ArenaTeamList
{
    protected string $queryBase = 'SELECT `at`.*, `at`.`arenaTeamId` AS ARRAY_KEY FROM arena_team at';
    protected array  $queryOpts = array(
                    'at'  => [['atm', 'c'], 'g' => 'ARRAY_KEY', 'o' => 'rating DESC'],
                    'atm' => ['j' => 'arena_team_member atm ON atm.`arenaTeamId` = at.`arenaTeamId`'],
                    'c'   => ['j' => 'characters c ON c.`guid` = atm.`guid` AND c.`deleteInfos_Account` IS NULL AND c.`level` <= 80 AND (c.`extra_flags` & '.Profiler::CHAR_GMFLAGS.') = 0', 's' => ', BIT_OR(IF(c.`race` IN (1, 3, 4, 7, 11), 1, 2)) - 1 AS "faction"']
                );

    private array $members   = [];
    private array $rankOrder = [];

    public function __construct(array $conditions = [], array $miscData = [])
    {
        // select DB by realm
        if (!$this->selectRealms($miscData))
        {
            trigger_error('RemoteArenaTeamList::__construct - cannot access any realm.', E_USER_WARNING);
            return;
        }

        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        // ranks in DB are inaccurate. recalculate from rating (fetched as DESC from DB)
        foreach ($this->dbNames as $rId => $__)
            foreach ([2, 3, 5] as $type)
                $this->rankOrder[$rId][$type] = DB::Characters($rId)->selectCol('SELECT `arenaTeamId` FROM arena_team WHERE `type` = ?d ORDER BY `rating` DESC', $type);

        reset($this->dbNames);                              // only use when querying single realm
        $realms  = Profiler::getRealms();
        $distrib = [];

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
                trigger_error('arena team #'.$guid.' belongs to nonexistent realm #'.$r, E_USER_WARNING);
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
            $teams = DB::Characters($realmId)->select(
               'SELECT at.`arenaTeamId` AS ARRAY_KEY, c.`guid` AS ARRAY_KEY2, c.`name` AS "0", c.`class` AS "1", IF(at.`captainguid` = c.`guid`, 1, 0) AS "2"
                FROM   arena_team at
                JOIN   arena_team_member atm ON atm.`arenaTeamId` = at.`arenaTeamId` JOIN characters c ON c.`guid` = atm.`guid`
                WHERE  at.`arenaTeamId` IN (?a) AND c.`deleteInfos_Account` IS NULL AND c.`level` <= ?d AND (c.`extra_flags` & ?d) = 0',
                $teams, MAX_LEVEL, Profiler::CHAR_GMFLAGS
            );

        // equalize subject distribution across realms
        $limit = 0;
        foreach ($conditions as $c)
            if (is_numeric($c))
                $limit = max(0, (int)$c);

        if (!$limit)                                        // int:0 means unlimited, so skip early
            return;

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

    public function initializeLocalEntries() : void
    {
        if (!$this->templates)
            return;

        $profiles = [];
        // init members for tooltips
        foreach ($this->members as $realmId => $teams)
        {
            $gladiators = [];
            foreach ($teams as $team)
                $gladiators = array_merge($gladiators, array_keys($team));

            $profiles[$realmId] = new RemoteProfileList(array(['c.guid', $gladiators]), ['sv' => $realmId]);

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
                'stub'      => 1
            );
        }

        // basic arena team data
        foreach (Util::createSqlBatchInsert($data) as $ins)
            DB::Aowow()->query('INSERT INTO ?_profiler_arena_team (?#) VALUES '.$ins.' ON DUPLICATE KEY UPDATE `id` = `id`', array_keys(reset($data)));

        // merge back local ids
        $localIds = DB::Aowow()->selectCol(
           'SELECT CONCAT(`realm`, ":", `realmGUID`) AS ARRAY_KEY, `id` FROM ?_profiler_arena_team WHERE `realm` IN (?a) AND `realmGUID` IN (?a)',
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
                DB::Aowow()->query('INSERT INTO ?_profiler_arena_team_member (?#) VALUES '.$ins.' ON DUPLICATE KEY UPDATE `profileId` = `profileId`', array_keys(reset($memberData)));
        }
    }
}


class LocalArenaTeamList extends ArenaTeamList
{
    protected string $queryBase = 'SELECT at.*, at.id AS ARRAY_KEY FROM ?_profiler_arena_team at';
    protected array  $queryOpts = array(
                    'at'  => [['atm', 'c'], 'g' => 'ARRAY_KEY', 'o' => 'rating DESC'],
                    'atm' => ['j' => '?_profiler_arena_team_member atm ON atm.`arenaTeamId` = at.`id`'],
                    'c'   => ['j' => '?_profiler_profiles c ON c.`id` = atm.`profileId`', 's' => ', BIT_OR(IF(c.`race` IN (1, 3, 4, 7, 11), 1, 2)) - 1 AS "faction"']
                );

    public function __construct(array $conditions = [], array $miscData = [])
    {
        $realms = Profiler::getRealms();

        // graft realm selection from miscData onto conditions
        if (isset($miscData['sv']))
            $realms = array_filter($realms, fn($x) => Profiler::urlize($x['name']) == Profiler::urlize($miscData['sv']));

        if (isset($miscData['rg']))
            $realms = array_filter($realms, fn($x) => $x['region'] == $miscData['rg']);

        if (!$realms)
        {
            trigger_error('LocalArenaTeamList::__construct - cannot access any realm.', E_USER_WARNING);
            return;
        }

        if ($conditions)
        {
            array_unshift($conditions, 'AND');
            $conditions = ['AND', ['realm', array_keys($realms)], $conditions];
        }
        else
            $conditions = [['realm', array_keys($realms)]];

        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        // post processing
        $members = DB::Aowow()->select(
           'SELECT `arenaTeamId` AS ARRAY_KEY, p.`id` AS ARRAY_KEY2, p.`name` AS "0", p.`class` AS "1", atm.`captain` AS "2"
            FROM   ?_profiler_arena_team_member atm
            JOIN   ?_profiler_profiles p ON p.`id` = atm.`profileId`
            WHERE  `arenaTeamId` IN (?a)',
            $this->getFoundIDs()
        );

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

            $curTpl['members'] = array_values($members[$id]);
        }
    }

    public function getProfileUrl() : string
    {
        $url = '?arena-team=';

        return $url.implode('.', array(
            $this->getField('region'),
            Profiler::urlize($this->getField('realmName'), true),
            Profiler::urlize($this->getField('name'))
        ));
    }
}


?>
