<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuildList extends BaseType
{
    use profilerHelper, listviewHelper;

    public function getListviewData()
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

        return array_values($data);
    }

    private function getGuildScores()
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

        $stats = DB::Aowow()->select('SELECT guild AS ARRAY_KEY, id AS ARRAY_KEY2, level, gearscore, achievementpoints, IF(cuFlags & ?d, 0, 1) AS synced FROM ?_profiler_profiles WHERE guild IN (?a) ORDER BY gearscore DESC', PROFILER_CU_NEEDS_RESYNC, $guilds);
        foreach ($this->iterate() as &$_curTpl)
        {
            $id = $_curTpl['id'];
            if (empty($stats[$id]))
                continue;

            $guildStats = array_filter($stats[$id], function ($x) { return $x['synced']; } );
            if (!$guildStats)
                continue;

            $nMaxLevel = count(array_filter($stats[$id], function ($x) { return $x['level'] >= MAX_LEVEL; } ));
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

    public function renderTooltip() {}
    public function getJSGlobals($addMask = 0) {}
}


class GuildListFilter extends Filter
{
    public    $extraOpts     = [];
    protected $genericFilter = [];

    protected $inputFields = array(
        'na' => [FILTER_V_REGEX,    parent::PATTERN_NAME, false], // name - only printable chars, no delimiter
        'ma' => [FILTER_V_EQUAL,    1,                    false], // match any / all filter
        'ex' => [FILTER_V_EQUAL,    'on',                 false], // only match exact
        'si' => [FILTER_V_LIST,     [1, 2],               false], // side
        'rg' => [FILTER_V_CALLBACK, 'cbRegionCheck',      false], // region
        'sv' => [FILTER_V_CALLBACK, 'cbServerCheck',      false], // server
    );

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = $this->fiData['v'];

        // region (rg), battlegroup (bg) and server (sv) are passed to GuildList as miscData and handled there

        // name [str]
        if (!empty($_v['na']))
            if ($_ = $this->modularizeString(['g.name'], $_v['na'], !empty($_v['ex']) && $_v['ex'] == 'on'))
                $parts[] = $_;

        // side [list]
        if (!empty($_v['si']))
        {
            if ($_v['si'] == 1)
                $parts[] = ['c.race', [1, 3, 4, 7, 11]];
            else if ($_v['si'] == 2)
                $parts[] = ['c.race', [2, 5, 6, 8, 10]];
        }

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


class RemoteGuildList extends GuildList
{
    protected   $queryBase = 'SELECT `g`.*, `g`.`guildid` AS ARRAY_KEY FROM guild g';
    protected   $queryOpts = array(
                    'g'  => [['gm', 'c'], 'g' => 'ARRAY_KEY'],
                    'gm' => ['j' => 'guild_member gm ON gm.guildid = g.guildid', 's' => ', COUNT(1) AS members'],
                    'c'  => ['j' => 'characters c ON c.guid = gm.guid', 's' => ', BIT_OR(IF(c.race IN (1, 3, 4, 7, 11), 1, 2)) - 1 AS faction']
                );

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

        reset($this->dbNames);                              // only use when querying single realm
        $realmId     = key($this->dbNames);
        $realms      = Profiler::getRealms();
        $distrib     = [];

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
                trigger_error('guild #'.$guid.' belongs to nonexistant realm #'.$r, E_USER_WARNING);
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

            $distrib[$curTpl['realm']]--;
            $limit--;
        }
    }

    public function initializeLocalEntries()
    {
        $data = [];
        foreach ($this->iterate() as $guid => $__)
        {
            $data[$guid] = array(
                'realm'     => $this->getField('realm'),
                'realmGUID' => $this->getField('guildid'),
                'name'      => $this->getField('name'),
                'nameUrl'   => Profiler::urlize($this->getField('name')),
                'cuFlags'   => PROFILER_CU_NEEDS_RESYNC
            );
        }

        // basic guild data
        foreach (Util::createSqlBatchInsert($data) as $ins)
            DB::Aowow()->query('INSERT INTO ?_profiler_guild (?#) VALUES '.$ins.' ON DUPLICATE KEY UPDATE `id` = `id`', array_keys(reset($data)));

        // merge back local ids
        $localIds = DB::Aowow()->selectCol(
            'SELECT CONCAT(realm, ":", realmGUID) AS ARRAY_KEY, id FROM ?_profiler_guild WHERE realm IN (?a) AND realmGUID IN (?a)',
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
    protected       $queryBase = 'SELECT g.*, g.id AS ARRAY_KEY FROM ?_profiler_guild g';

    public function __construct($conditions = [], $miscData = null)
    {
        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        $realms = Profiler::getRealms();

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

    public function getProfileUrl()
    {
        $url = '?guild=';

        return $url.implode('.', array(
            Profiler::urlize($this->getField('region')),
            Profiler::urlize($this->getField('realmName')),
            Profiler::urlize($this->getField('name'))
        ));
    }
}


?>
