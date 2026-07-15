<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class ArenateamEntry extends DBTypeEntry implements IProfiler
{
    use TrProfilerHelper;

    public readonly string $name;
    public readonly int    $faction;
    public readonly int    $type;
    public readonly int    $rank;
    public readonly int    $seasonWins;
    public readonly int    $seasonGames;
    public readonly int    $rating;
    /** @var array{int, int, bool}[] $members [charGUID => [name, classId, isCaptain], ...] */
    public readonly array  $members;

    public static int $contribute = CONTRIBUTE_NONE;
    public static int $dbType     = Type::ARENA_TEAM;

 // unsure if required or not .. values are placeholder
 // public static string $brickFile = 'arenateam';
 // public static string $dataTable = '::arenateam';

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        return array(
            'name'              => $this->name,
            'realm'             => Profiler::urlize($this->realmName, true),
            'realmname'         => $this->realmName,
         // 'battlegroup'       => Profiler::urlize($this->battlegroup), // was renamed to subregion somewhere around cata release
         // 'battlegroupname'   => $this->battlegroup,
            'region'            => Profiler::urlize($this->region),
            'faction'           => $this->faction,
            'size'              => $this->type,
            'rank'              => $this->rank,
            'wins'              => $this->seasonWins,
            'games'             => $this->seasonGames,
            'rating'            => $this->rating,
            'members'           => $this->members
        );
    }

    public function renderTooltip() : ?string { return null; }
    public function getJSGlobal(int $addMask = 0) : array { return []; }

    public function getProfileUrl() : string
    {
        return '?arena-team=' . $this->region . '.' . Profiler::urlize($this->realmName, true) . '.' . Profiler::urlize($this->name);
    }

    public static function getName(int|string $id) : ?LocString { return null; }
}


class RemoteArenateamEntry extends ArenateamEntry
{
    public const /* string */ QUERY_BASE = 'SELECT `at`.*, `at`.`arenaTeamId` AS ARRAY_KEY FROM arena_team at';
    public const /* array  */ QUERY_OPTS = array(
        'at'  => [['atm', 'c'], 'g' => 'ARRAY_KEY', 'o' => 'rating DESC'],
        'atm' => ['j' => 'arena_team_member atm ON atm.`arenaTeamId` = at.`arenaTeamId`'],
        'c'   => ['j' => 'characters c ON c.`guid` = atm.`guid` AND c.`deleteInfos_Account` IS NULL AND c.`level` <= 80 AND (c.`extra_flags` & '.Profiler::CHAR_GMFLAGS.') = 0', 's' => ', BIT_OR(IF(c.`race` IN (1, 3, 4, 7, 11), 1, 2)) - 1 AS "faction"']
    );

    private array $rankOrder = [];

    public function __construct(array $conditions = [], array $miscData = [])
    {
        // select DB by realm
        if (!$dbNames = self::getRealmDBs($miscData))
        {
            trigger_error('RemoteArenateamList::__construct - cannot access any realm.', E_USER_WARNING);
            return;
        }

        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        // ranks in DB are inaccurate. recalculate from rating (fetched as DESC from DB)
        foreach ($this->dbNames as $rId => $__)
            foreach ([2, 3, 5] as $type)
                $this->rankOrder[$rId][$type] = DB::Characters($rId)->selectCol('SELECT `arenaTeamId` FROM arena_team WHERE `type` = %i ORDER BY `rating` DESC', $type);

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
                trigger_error('arena team #'.$guid.' belongs to nonexistent realm #'.$r[0], E_USER_WARNING);
                unset($this->templates[$guid]);
                continue;
            }

            // empty name
            if (!$curTpl['name'])
            {
                trigger_error('arena team #'.$guid.' on realm #'.$r[0].' has empty name.', E_USER_WARNING);
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
            $teams = DB::Characters($realmId)->selectAssoc(
               'SELECT at.`arenaTeamId` AS ARRAY_KEY, c.`guid` AS ARRAY_KEY2, c.`name` AS "0", c.`class` AS "1", IF(at.`captainguid` = c.`guid`, 1, 0) AS "2"
                FROM   arena_team at
                JOIN   arena_team_member atm ON atm.`arenaTeamId` = at.`arenaTeamId` JOIN characters c ON c.`guid` = atm.`guid`
                WHERE  at.`arenaTeamId` IN %in AND c.`deleteInfos_Account` IS NULL AND c.`level` <= %i AND (c.`extra_flags` & %i) = 0',
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
}


class LocalArenateamEntry extends ArenateamEntry
{
    public const /* string */ QUERY_BASE = 'SELECT at.*, at.id AS ARRAY_KEY FROM ::profiler_arena_team at';
    public const /* array  */ QUERY_OPTS = array(
        'at'  => [['atm', 'c'], 'g' => 'ARRAY_KEY', 'o' => 'rating DESC'],
        'atm' => ['j' => '::profiler_arena_team_member atm ON atm.`arenaTeamId` = at.`id`'],
        'c'   => ['j' => '::profiler_profiles c ON c.`id` = atm.`profileId`', 's' => ', BIT_OR(IF(c.`race` IN (1, 3, 4, 7, 11), 1, 2)) - 1 AS "faction"']
    );

    public function __construct(
                  int|array $initData,
        protected array     $extraOpts = [],
                  array     $targetDBs = ['Aowow']
    )
    {
        $realms = Profiler::getRealms();

        // graft realm selection from miscData onto conditions
        if (isset($miscData['sv']))
            $realms = array_filter($realms, fn($x) => Profiler::urlize($x['name']) == Profiler::urlize($miscData['sv']));

        if (isset($miscData['rg']))
            $realms = array_filter($realms, fn($x) => $x['region'] == $miscData['rg']);

        if (!$realms)
        {
            trigger_error('LocalArenateamList::__construct - cannot access any realm.', E_USER_WARNING);
            return;
        }

        if ($conditions)
        {
            array_unshift($conditions, DB::AND);
            $conditions = [DB::AND, ['realm', array_keys($realms)], $conditions];
        }
        else
            $conditions = [['realm', array_keys($realms)]];

        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        // post processing
        $this->members = DB::Aowow()->selectAssoc(
           'SELECT `arenaTeamId` AS ARRAY_KEY, p.`id` AS ARRAY_KEY2, p.`name` AS "0", p.`class` AS "1", atm.`captain` AS "2"
            FROM   ::profiler_arena_team_member atm
            JOIN   ::profiler_profiles p ON p.`id` = atm.`profileId`
            WHERE  `arenaTeamId` IN %in',
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

            $curTpl['members'] = array_values($this->members[$id]);
        }
    }
}


?>
