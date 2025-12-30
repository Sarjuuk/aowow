<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuildList extends DBTypeList
{
    use profilerHelper, listviewHelper;

    public static int $contribute = CONTRIBUTE_NONE;

    public function getListviewData() : array
    {
        $this->getGuildScores();

        $data = [];
        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'name'              => '$"'.str_replace ('"', '', $this->curTpl['name']).'"',   // MUST be a string, omit any quotes in name
                'members'           => $this->curTpl['members'],
                'faction'           => $this->curTpl['faction'],
                'achievementpoints' => $this->getField('achievementpoints'),
                'gearscore'         => $this->getField('gearscore'),
                'realm'             => Profiler::urlize($this->curTpl['realmName'], true),
                'realmname'         => $this->curTpl['realmName'],
             // 'battlegroup'       => Profiler::urlize($this->curTpl['battlegroup']),          // was renamed to subregion somewhere around cata release
             // 'battlegroupname'   => $this->curTpl['battlegroup'],
                'region'            => Profiler::urlize($this->curTpl['region'])
            );
        }

        return $data;
    }

    private function getGuildScores() : void
    {
        /*
            Guild gear scores and achievement points are derived using a weighted average of all of the known characters in that guild.
            Guilds with at least 25 level 80 players receive full benefit of the top 25 characters' gear scores, while guilds with at least 10 level 80 characters receive a slight penalty,
            at least 1 level 80 a moderate penalty, and no level 80 characters a severe penalty. [...]
            Instead of being based on level, achievement point averages are based around 1,500 points, but the same penalties apply.
        */
        $guilds = array_column($this->templates, 'id');
        if (!$guilds)
            return;

        $stats = DB::Aowow()->select('SELECT `guild` AS ARRAY_KEY, `id` AS ARRAY_KEY2, `level`, `gearscore`, `achievementpoints` FROM ?_profiler_profiles WHERE `guild` IN (?a) AND `stub` = 0 ORDER BY `gearscore` DESC', $guilds);
        foreach ($this->iterate() as &$_curTpl)
        {
            $id = $_curTpl['id'];
            if (empty($stats[$id]))
                continue;

            $guildStats = $stats[$id];

            $nMaxLevel = count(array_filter($stats[$id], fn($x) => $x['level'] >= MAX_LEVEL));
            $levelMod  = 1.0;

            if ($nMaxLevel < 25)
                $levelMod = 0.85;
            if ($nMaxLevel < 10)
                $levelMod = 0.66;
            if ($nMaxLevel < 1)
                $levelMod = 0.20;

            $totalGS = $totalAP = $nMembers = 0;
            foreach ($guildStats as $gs)
            {
                $totalGS  += $gs['gearscore']         * $levelMod * min($gs['level'], MAX_LEVEL) / MAX_LEVEL;
                $totalAP  += $gs['achievementpoints'] * $levelMod * min($gs['achievementpoints'], 1500) / 1500;
                $nMembers += min($gs['level'], MAX_LEVEL) / MAX_LEVEL;
            }

            $_curTpl['gearscore']         = intval($totalGS / $nMembers);
            $_curTpl['achievementpoints'] = intval($totalAP / $nMembers);
        }
    }

    public static function getName(int $id) : ?LocString { return null; }

    public function renderTooltip() : ?string { return null; }
    public function getJSGlobals(int $addMask = 0) : array { return []; }
}


class GuildListFilter extends Filter
{
    use TrProfilerFilter;

    protected string $type          = 'guilds';
    protected static array $genericFilter = [];
    protected static array $inputFields   = array(
        'na' => [parent::V_REGEX,    parent::PATTERN_NAME,        false], // name - only printable chars, no delimiter
        'ma' => [parent::V_EQUAL,    1,                           false], // match any / all filter
        'ex' => [parent::V_EQUAL,    'on',                        false], // only match exact
        'si' => [parent::V_LIST,     [SIDE_ALLIANCE, SIDE_HORDE], false], // side
        'rg' => [parent::V_CALLBACK, 'cbRegionCheck',             false], // region
        'bg' => [parent::V_EQUAL,    null,                        false], // battlegroup - unsued here, but var expected by template
        'sv' => [parent::V_CALLBACK, 'cbServerCheck',             false]  // server
    );

    public array $extraOpts = [];

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = $this->values;

        // region (rg), battlegroup (bg) and server (sv) are passed to GuildList as miscData and handled there

        // name [str]
        if ($_v['na'])
            if ($_ = $this->tokenizeString(['g.name'], $_v['na'], $_v['ex'] == 'on'))
                $parts[] = $_;

        // side [list]
        if ($_v['si'] == SIDE_ALLIANCE)
            $parts[] = ['c.race', ChrRace::fromMask(ChrRace::MASK_ALLIANCE)];
        else if ($_v['si'] == SIDE_HORDE)
            $parts[] = ['c.race', ChrRace::fromMask(ChrRace::MASK_HORDE)];

        return $parts;
    }
}


class RemoteGuildList extends GuildList
{
    protected string $queryBase = 'SELECT `g`.*, `g`.`guildid` AS ARRAY_KEY FROM guild g';
    protected array  $queryOpts = array(
                    'g'  => [['gm', 'c'], 'g' => 'ARRAY_KEY'],
                    'gm' => ['j' => 'guild_member gm ON gm.`guildid` = g.`guildid`', 's' => ', COUNT(1) AS "members"'],
                    'c'  => ['j' => 'characters c ON c.`guid` = gm.`guid`', 's' => ', BIT_OR(IF(c.`race` IN (1, 3, 4, 7, 11), 1, 2)) - 1 AS "faction"']
                );

    public function __construct(array $conditions = [], array $miscData = [])
    {
        // select DB by realm
        if (!$this->selectRealms($miscData))
        {
            trigger_error('RemoteGuildList::__construct - cannot access any realm.', E_USER_WARNING);
            return;
        }

        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        reset($this->dbNames);                              // only use when querying single realm
        $realms  = Profiler::getRealms();
        $distrib = [];

        // post processing
        foreach ($this->iterate() as $guid => &$curTpl)
        {
            // battlegroup
            $curTpl['battlegroup'] = Cfg::get('BATTLEGROUP');

            $r = explode(':', $guid)[0];
            if (!empty($realms[$r]))
            {
                $curTpl['realm']     = $r;
                $curTpl['realmName'] = $realms[$r]['name'];
                $curTpl['region']    = $realms[$r]['region'];
            }
            else
            {
                trigger_error('guild #'.$guid.' belongs to nonexistent realm #'.$r, E_USER_WARNING);
                unset($this->templates[$guid]);
                continue;
            }

            // empty name
            if (!$curTpl['name'])
            {
                trigger_error('guild #'.$guid.' on realm #'.$r.' has empty name.', E_USER_WARNING);
                unset($this->templates[$guid]);
                continue;
            }

            // equalize distribution
            if (empty($distrib[$curTpl['realm']]))
                $distrib[$curTpl['realm']] = 1;
            else
                $distrib[$curTpl['realm']]++;
        }

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

            $distrib[$curTpl['realm']]--;
            $limit--;
        }
    }

    public function initializeLocalEntries() : void
    {
        $data = [];
        foreach ($this->iterate() as $guid => $__)
        {
            $data[$guid] = array(
                'realm'     => $this->getField('realm'),
                'realmGUID' => $this->getField('guildid'),
                'name'      => $this->getField('name'),
                'nameUrl'   => Profiler::urlize($this->getField('name')),
                'stub'      => 1
            );
        }

        // basic guild data
        foreach (Util::createSqlBatchInsert($data) as $ins)
            DB::Aowow()->query('INSERT INTO ?_profiler_guild (?#) VALUES '.$ins.' ON DUPLICATE KEY UPDATE `id` = `id`', array_keys(reset($data)));

        // merge back local ids
        $localIds = DB::Aowow()->selectCol(
           'SELECT CONCAT(`realm`, ":", `realmGUID`) AS ARRAY_KEY, `id` FROM ?_profiler_guild WHERE `realm` IN (?a) AND `realmGUID` IN (?a)',
            array_column($data, 'realm'),
            array_column($data, 'realmGUID')
        );

        foreach ($this->iterate() as $guid => &$_curTpl)
            if (isset($localIds[$guid]))
                $_curTpl['id'] = $localIds[$guid];
    }
}


class LocalGuildList extends GuildList
{
    protected string $queryBase = 'SELECT g.*, g.`id` AS ARRAY_KEY FROM ?_profiler_guild g';

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
            trigger_error('LocalGuildList::__construct - cannot access any realm.', E_USER_WARNING);
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
        }
    }

    public function getProfileUrl() : string
    {
        $url = '?guild=';

        return $url.implode('.', array(
            $this->getField('region'),
            Profiler::urlize($this->getField('realmName'), true),
            Profiler::urlize($this->getField('name'))
        ));
    }
}


?>
