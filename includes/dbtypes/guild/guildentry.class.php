<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class GuildEntry extends DBTypeEntry
{
    public readonly  string $name;

    public private(set) ?int   $team = null {
        get => $this->team ?? ($this->setMembers() ?? $this->team);
    }
    public private(set) ?array $members = null {
        get => $this->members ?? ($this->setMembers() ?? $this->members);
    }

    use TrProfilerHelper;

    public static int $contribute = CONTRIBUTE_NONE;


    public function applyInitData(array $initData, array $opts) : bool
    {
        parent::applyInitData($initData, $opts);

        $this->name = $initData['name'];

        return true;
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        [$achivement, $gear] = $this->calcGuildScore();

        return array(
            'name'              => '$"'.str_replace('"', '', $this->name).'"', // MUST be a string, omit any quotes in name
            'members'           => $this->members,
            'faction'           => $this->team,
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

    public function getProfileUrl() : string
    {
        return '?guild=' . $this->region . '.' . Profiler::urlize($this->realmName, true) . '.' . Profiler::urlize($this->name);
    }

    public function setMembers(?array $memberData = null) : void
    {
        $memberData ??= self::fetchMembers($this->id);

        $this->members = $memberData[$this->id] ?? [];

        $mask = 0;
        foreach ($this->members as $m)
            $mask |= (1 << ($m['race'] - 1));

        $this->team = ChrRace::teamFromMask($mask);
    }

    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array { return []; }

    public static function fetchMembers(int ...$guildIds) : array
    {
        return DB::Aowow()->selectAssoc('SELECT `guild` AS ARRAY_KEY, `id` AS ARRAY_KEY2, `level`, `gearscore`, `achievementpoints`, `race` FROM ::profiler_profiles WHERE `guild` IN %in AND `stub` = 0 ORDER BY `gearscore` DESC', $guildIds);
    }

    public static function getName(int $id) : ?LocString { return null; }
}


class RemoteGuildEntry extends GuildEntry
{
    public const string QUERY_BASE = 'SELECT `g`.*, `g`.`guildid` AS ARRAY_KEY FROM guild g';
    public const array  QUERY_OPTS = array(
        'g'  => [['gm', 'c'], 'g' => 'ARRAY_KEY'],
        'gm' => ['j' => 'guild_member gm ON gm.`guildid` = g.`guildid`', 's' => ', COUNT(1) AS "members"'],
        'c'  => ['j' => 'characters c ON c.`guid` = gm.`guid`', 's' => ', BIT_OR(IF(c.`race` IN (1, 3, 4, 7, 11), 1, 2)) - 1 AS "faction"']
    );

    public function __construct(int|array $initData, array $extraOpts = [])
    {
        // if id lookup, select DB by realm
        $targetDBs = Profiler::getRealmDBs($extraOpts['rg'] ?? null, $extraOpts['sv'] ?? null);

        if (is_int($initData) && !$targetDBs)
        {
            trigger_error(__METHOD__.' - cannot access any realm.', E_USER_WARNING);
            return;
        }

        // warning: jank! - realmId is not already set by container only if the user preselected just the region in the search from and the region only contains a single realm
        if (is_array($initData) && $targetDBs && !isset($initData['realmId']))
            $initData['realmId'] = key($targetDBs);

        parent::__construct($initData, $extraOpts, $targetDBs);
    }

    public function applyInitData(array $initData, array $opts) : bool
    {
        if (!$initData['name'])
        {
            trigger_error('char #'.$initData['guildid'].' on realm #'.$initData['realmId'].' has empty name.', E_USER_WARNING);
            return false;
        }

        if (!(['name' => $realmName, 'region' => $region] = Profiler::getRealms()[$initData['realmId']] ?? null))
        {
            trigger_error(__METHOD__.' realm #'.$initData['realmId'].' is inaccessible or does not exist.', E_USER_WARNING);
            return false;
        }

        $this->region    = $region;
        $this->realmId   = $initData['realmId'];
        $this->realmGUID = $initData['guildid'];
        $this->realmName = $realmName;

        // rename to fit our structure
        $initData['cuFlags']       = 0;
        $initData['id']            = $this->subjectGUID;

        return parent::applyInitData($initData, $opts);
    }
}


class LocalGuildEntry extends GuildEntry
{
    public const string QUERY_BASE = 'SELECT g.*, g.`id` AS ARRAY_KEY FROM ::profiler_guild g';

    public function applyInitData(array $initData, array $opts) : bool
    {
        if ($initData['realm'] && !(['name' => $realmName, 'region' => $region] = Profiler::getRealms()[$initData['realm']] ?? null))
        {
            trigger_error(__METHOD__.' realm #'.$initData['realm'].' is inaccessible or does not exist.', E_USER_WARNING);
            return false;
        }

        $this->region    = $region                ?? '';
        $this->realmId   = $initData['realm']     ?? 0;
        $this->realmGUID = $initData['realmGUID'] ?? 0;
        $this->realmName = $realmName             ?? '';

        // rename to fit our structure

        return parent::applyInitData($initData, $opts);
    }
}


?>
