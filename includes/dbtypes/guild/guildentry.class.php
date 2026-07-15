<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class GuildEntry extends DBTypeEntry implements IProfiler
{
    public readonly  string $name;

    public readonly  int    $faction;
    public readonly  array  $members;

    use TrProfilerHelper;

    public static int $contribute = CONTRIBUTE_NONE;

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        [$achivement, $gear] = $this->calcGuildScore();

        return array(
            'name'              => '$"'.str_replace('"', '', $this->name).'"', // MUST be a string, omit any quotes in name
            'members'           => $this->members,
            'faction'           => $this->faction,
            'achievementpoints' => $achivement,
            'gearscore'         => $gear,
            'realm'             => Profiler::urlize($this->realmName, true),
            'realmname'         => $this->realmName,
         // 'battlegroup'       => Profiler::urlize($this->battlegroup), // was renamed to subregion somewhere around cata release
         // 'battlegroupname'   => $this->battlegroup,
            'region'            => Profiler::urlize($this->region)
        );
    }

    /**
     *  from help page:
     *
     *  Guild gear scores and achievement points are derived using a weighted average of all of the known characters in that guild.
     *  Guilds with at least 25 level 80 players receive full benefit of the top 25 characters' gear scores, while guilds with at least 10 level 80 characters receive a slight penalty,
     *  at least 1 level 80 a moderate penalty, and no level 80 characters a severe penalty. [...]
     *  Instead of being based on level, achievement point averages are based around 1,500 points, but the same penalties apply.
    */
    private function calcGuildScore() : array
    {
        $this->members ??= DB::Aowow()->selectAssoc('SELECT `id` AS ARRAY_KEY, `level`, `gearscore`, `achievementpoints` FROM ::profiler_profiles WHERE `guild` = %i AND `stub` = 0 ORDER BY `gearscore` DESC', $this->id);

        if (!$this->members)                                // empty guilds may be a thing if the server owner fucks up
            return [0, 0];

        $nMaxLevel = count(array_filter($this->members, fn($x) => $x['level'] >= MAX_LEVEL));
        $levelMod  = match(true)
        {
            $nMaxLevel <  1 => 0.20,                        // all values guesssed. doesn't really matter.
            $nMaxLevel < 10 => 0.66,
            $nMaxLevel < 25 => 0.85,
            default         => 1.00
        };

        $top25 = array_slice($this->members, 0, 25);        // only top 25 contributors are considered

        $totalGS  = (array_sum(array_column($top25, 'gearscore'))         * $levelMod) / count($top25);
        $totalAP  = (array_sum(array_column($top25, 'achievementpoints')) * $levelMod) / count($top25);

        $normalGS = (array_sum(array_map(fn($x) => min($x, MAX_LEVEL), array_column($top25, 'level')))             / count($top25)) / MAX_LEVEL;
        $normalAP = (array_sum(array_map(fn($x) => min($x, 1500),      array_column($top25, 'achievementpoints'))) / count($top25)) / 1500;

        return array(
            intval($totalAP * $normalAP),
            intval($totalGS * $normalGS)
        );
    }

    public function renderTooltip() : ?string { return null; }
    public function getJSGlobal(int $addMask = 0) : array { return []; }

    public static function getName(int $id) : ?LocString { return null; }
}


class RemoteGuildEntry extends GuildEntry
{
    public const /* string */ QUERY_BASE = 'SELECT `g`.*, `g`.`guildid` AS ARRAY_KEY FROM guild g';
    public const /* array  */ QUERY_OPTS = array(
        'g'  => [['gm', 'c'], 'g' => 'ARRAY_KEY'],
        'gm' => ['j' => 'guild_member gm ON gm.`guildid` = g.`guildid`', 's' => ', COUNT(1) AS "members"'],
        'c'  => ['j' => 'characters c ON c.`guid` = gm.`guid`', 's' => ', BIT_OR(IF(c.`race` IN (1, 3, 4, 7, 11), 1, 2)) - 1 AS "faction"']
    );

    public function __construct(array $conditions = [], array $miscData = [])
    {
        // select DB by realm
        if (!$dbNames = self::getRealmDBs($miscData))
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
}


class LocalGuildEntry extends GuildEntry
{
    public const /* string */ QUERY_BASE = 'SELECT g.*, g.`id` AS ARRAY_KEY FROM ::profiler_guild g';

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
            trigger_error('LocalGuildList::__construct - cannot access any realm.', E_USER_WARNING);
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
            $this->region,
            Profiler::urlize($this->realmName, true),
            Profiler::urlize($this->name)
        ));
    }
}


?>
