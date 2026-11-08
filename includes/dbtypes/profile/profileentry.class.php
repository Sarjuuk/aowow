<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class ProfileEntry extends DBTypeEntry implements ITooltip
{
    use TrProfilerHelper;

    public static int $dbType     = Type::PROFILE;
    public static int $contribute = CONTRIBUTE_NONE;

    public readonly  string $name;
    public readonly  int    $cuFlags;
    public readonly  int    $race;
    public readonly  int    $class;
    public readonly  int    $gender;
    public readonly  int    $level;
    public readonly ?int    $title;
    /** @var int[] $talenttree length:3 - points per tree */
    public readonly  array  $talenttree;
    public readonly  int    $activespec;
    public readonly  int    $achievementpoints;
    public readonly  int    $gearscore;
    // guild related
    public readonly ?int    $guild;
    public readonly ?string $guildname;
    public readonly ?int    $guildrank;
    // arena related (only set if requested..?)
    public readonly ?int    $arenateam;
    public readonly ?int    $rating;
    public readonly ?bool   $captain;
    public readonly ?int    $seasonGames;
    public readonly ?int    $seasonWins;
    // custom profile only
    /** @var ?int $user owning aowow account id */
    public readonly ?int    $user;
    public readonly ?string $description;
    public readonly ?string $icon;
    public readonly ?bool   $deleted;
    public readonly ?bool   $custom;
    // character only
    public readonly ?int    $renameItr;
    public readonly  bool   $renamePending;

    private array $extraColData = [];

    public function __construct(\Dibi\Row|int $initData, array $extraOpts = [], array $targetDBs = ['Aowow'])
    {
        parent::__construct($initData, $extraOpts, $targetDBs);

        if (is_int($initData))
            $this->setRenameItr();
    }

    public function applyInitData(\Dibi\Row $dbRow, array $opts) : bool
    {
        parent::applyInitData($dbRow, $opts);

        $this->name    = $dbRow->name;
        $this->cuFlags = $dbRow->cuFlags;
        $this->race    = $dbRow->race;
        $this->class   = $dbRow->class;
        $this->gender  = $dbRow->gender;
        $this->level   = $dbRow->level;
        $this->title   = $dbRow->title;

        // todo - currently only provided for local entries
        if (isset($dbRow->talenttree1))
            $this->talenttree        = [$dbRow->talenttree1, $dbRow->talenttree2, $dbRow->talenttree3];

        $this->activespec        = $dbRow->activespec        ?? 0;
        $this->achievementpoints = $dbRow->achievementpoints ?? 0;
        $this->gearscore         = $dbRow->gearscore         ?? 0;

        $this->guild     = $dbRow->guild     ?? null;
        $this->guildname = $dbRow->guildname ?? null;
        $this->guildrank = $dbRow->guildrank ?? null;

        $this->arenateam   = $dbRow->arenateam   ?? null;
        $this->rating      = $dbRow->rating      ?? null;
        $this->captain     = $dbRow->captain     ?? null;
        $this->seasonGames = $dbRow->seasonGames ?? null;
        $this->seasonWins  = $dbRow->seasonWins  ?? null;

        $this->user        = $dbRow->user        ?? null;
        $this->description = $dbRow->description ?? null;
        $this->icon        = $dbRow->icon        ?? null;
        $this->deleted     = $dbRow->deleted     ?? false;
        $this->custom      = $dbRow->custom      ?? false;

        $this->renamePending = $dbRow->renamePending ?? false;

        return true;
    }

    /**
     * @param int $addInfoMask
     * * `0x0100 - LISTVIEWINFO_PROFILE`: only include custom profiles
     * * `0x0200 - LISTVIEWINFO_CHARACTER`: only include genuine characters
     * * `0x0800 - LISTVIEWINFO_ARENA`: additional arena stats
     * * `0x1000 - LISTVIEWINFO_USER`: incuded published state
     */
    public function getListviewRow(int $addInfoMask = 0x0, array $reqCols = []) : array
    {
        if (!$this->isVisibleToUser())
            return [];

        if (($addInfoMask & LISTVIEWINFO_PROFILE) && !$this->isCustom())
            return [];

        if (($addInfoMask & LISTVIEWINFO_CHARACTER) && $this->isCustom())
            return [];

        $data = array(
            'id'                => $this->id,
            'name'              => $this->name,
            'race'              => $this->race,
            'classs'            => $this->class,
            'gender'            => $this->gender,
            'level'             => $this->level,
            'faction'           => ChrRace::tryFrom($this->race)?->getTeam(),
            'talenttree1'       => $this->talenttree[0],
            'talenttree2'       => $this->talenttree[1],
            'talenttree3'       => $this->talenttree[2],
            'talentspec'        => $this->activespec + 1,   // 0 => 1; 1 => 2
            'achievementpoints' => $this->achievementpoints,
            'guild'             => $this->guildname ? '$"'.str_replace ('"', '', $this->guildname).'"' : '', // force this to be reated as a string. Otherwise guild names starting with decimals will be implicitly converted to int; 72c1dacd3f405edb5b630ba06a6b6aa2662bbe3f implies there really was a guild with quotes in the name. dear god....
            'guildrank'         => $this->guildrank,
            'realm'             => Profiler::urlize($this->realmName, true),
            'realmname'         => $this->realmName,
         // 'battlegroup'       => Profiler::urlize($this->battlegroup), // was renamed to subregion somewhere around cata release
         // 'battlegroupname'   => $this->battlegroup,
            'gearscore'         => $this->gearscore
        );

        if ($addInfoMask & LISTVIEWINFO_USER)
            $data['published'] = $this->isPublished() ? 1 : 0;

        // for the lv this determines if the link is profile=<id> or profile=<region>.<realm>.<name>
        if (!$this->isCustom())
            $data['region'] = Profiler::urlize($this->region);

        if ($addInfoMask & LISTVIEWINFO_ARENA)
        {
            $data['rating']  = $this->rating;
            $data['captain'] = $this->captain;
            $data['games']   = $this->seasonGames;
            $data['wins']    = $this->seasonWins;
        }

        // DBTypeFilter asked for skills - add them
        foreach ($reqCols as $col)
            if (isset($this->extraColData[$col]))
                $data[$col] = $this->extraColData[$col];

        if ($addInfoMask & LISTVIEWINFO_PROFILE)
        {
            if ($this->description)
                $data['description'] = $this->description;

            if ($this->icon)
                $data['icon'] = $this->icon;
        }

        if ($addInfoMask & LISTVIEWINFO_CHARACTER)
            if ($this->renameItr)
                $data['renameItr'] = $this->renameItr;

        if ($this->cuFlags & PROFILER_CU_PINNED)
            $data['pinned'] = 1;

        if ($this->deleted)
            $data['deleted'] = 1;

        return $data;
    }

    /**
     * @param int $addMask exclusive switch what data to expose to javascript
     *  - GLOBALINFO_PROFILE - returns custom profile data
     *  - GLOBALINFO_CHARACTER - returns character data
     *
     * @return array<int, array<int, int|array>[]>[] [type => [typeId => placeholder or data]]
     */
    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
    {
        if (($addMask & GLOBALINFO_PROFILE) && $this->isCustom())
        {
            $profile = array(
             // 'id'     => $this->id,
                'name'   => $this->name,
                'race'   => $this->race,
                'classs' => $this->class,
                'level'  => $this->level,
                'gender' => $this->gender
            );

            if ($this->icon)
                $profile['icon'] = $this->icon;

            return [self::$dbType => [$this->id => $profile]];
        }

        $realms = Profiler::getRealms();
        if ($addMask & GLOBALINFO_CHARACTER && !$this->isCustom() && isset($realms[$this->realmId]))
        {
            return [self::$dbType => [$this->id => array(
             // 'id'        => $this->id,
                'name'      => $this->name,
                'realmname' => $realms[$this->realmId]['name'],
                'region'    => $realms[$this->realmId]['region'],
                'realm'     => Profiler::urlize($realms[$this->realmId]['name']),
                'race'      => $this->race,
                'classs'    => $this->class,
                'level'     => $this->level,
                'gender'    => $this->gender,
                'pinned'    => $this->cuFlags & PROFILER_CU_PINNED ? 1 : 0
            )]];
        }

        return [];
    }

    public function renderTooltip() : ?string
    {
        $title = null;
        $name  = $this->name;
        if ($this->title)
            $title = TitleEntry::getName($this->title, $this->gender);

        if ($this->isCustom())
            $name .= Lang::profiler('customProfile');
        else if ($title && !$title->isEmpty())
            $name = sprintf($title, $name);

        $x  = '<table>';
        $x .= '<tr><td><b class="q">'.$name.'</b></td></tr>';
        if ($g = $this->guildname)
            $x .= '<tr><td>&lt;'.$g.'&gt;</td></tr>';
        else if ($d = $this->description)
            $x .= '<tr><td>'.$d.'</td></tr>';
        $x .= '<tr><td>'.Lang::game('level').' '.$this->level.' '.Lang::game('ra', $this->race).' '.Lang::game('cl', $this->class).'</td></tr>';
        $x .= '</table>';

        return $x;
    }

    public function isCustom() : bool
    {
        return $this->custom;
    }

    public function isPublished() : bool
    {
        return $this->cuFlags & PROFILER_CU_PUBLISHED;
    }

    public function isVisibleToUser() : bool
    {
        if (!$this->isCustom() || User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
            return true;

        if ($this->deleted)
            return false;

        if (User::$id == $this->user)
            return true;

        return $this->isPublished();
    }

    public function getIcon() : string
    {
        if ($this->icon)
            return $this->icon;

        return sprintf('chr_%s_%s_%s%02d',
            ChrRace::from($this->race)->json(),
            $this->gender ? 'female' : 'male',
            ChrClass::from($this->class)->json(),
            max(1, floor(($this->level - 60) / 10) + 2)
        );
    }

    public function getProfileUrl() : string
    {
        if ($this->isCustom())
            return '?profile=' . $this->id;

        return '?profile=' . $this->region . '.' . Profiler::urlize($this->realmName, true) . '.' . urlencode($this->name) . ($this->renameItr ? '-' . $this->renameItr : '');
    }

    public function setRenameItr(?array &$itrData = []) : void
    {
        // already saved as "pending rename"
        if ($itr = DB::Aowow()->selectCell('SELECT `renameItr` FROM ::profiler_profiles WHERE `realm` = %i AND `realmGUID` = %i', $this->realmId, $this->realmGUID))
        {
            $this->renameItr = $itr;
            return;
        }

        // not yet recognized: get max itr
        if (($itrData ??= self::fetchRenameItrs($this->realmId, $this->name)) && isset($itrData[$this->name]))
        {
            $this->renameItr = ++$itrData[$this->name];
            return;
        }

        $this->renameItr = 0;
    }

    public static function fetchRenameItrs(int $realmId, string ...$names) : array
    {
        return DB::Aowow()->selectCol('SELECT `name` AS ARRAY_KEY, MAX(`renameItr`) FROM ::profiler_profiles WHERE `realm` = %i AND `custom` = 0 AND `name` IN %in GROUP BY `name`', $realmId, $names) ?: [];
    }

    public static function getName(int $id) : ?LocString { return null; }
}


class RemoteProfileEntry extends ProfileEntry
{
    public readonly string $battlegroup;

    public const string QUERY_BASE = 'SELECT c.`guid`, c.`name`, c.`race`, c.`class`, c.`gender`, c.`level`, c.`at_login`, c.`chosenTitle`, c.`activeTalentGroup` FROM characters c';
    public const array  QUERY_OPTS = array(
        'c'   => [['gm', 'g', 'cap']],                      // 12698: use criteria of Achievement 4496 as shortcut to get total achievement points
        'cap' => ['j' => ['character_achievement_progress cap ON cap.`guid` = c.`guid` AND cap.`criteria` = 12698', true], 's' => ', IFNULL(cap.`counter`, 0) AS "achievementpoints"'],
        'gm'  => ['j' => ['guild_member gm ON gm.`guid` = c.`guid`', true], 's' => ', gm.`rank` AS "guildrank"'],
        'g'   => ['j' => ['guild g ON g.`guildid` = gm.`guildid`', true], 's' => ', g.`guildid` AS "guild", g.`name` AS "guildname"'],
        'atm' => ['j' => ['arena_team_member atm ON atm.`guid` = c.`guid`', true], 's' => ', atm.`personalRating` AS "rating"'],
        'at'  => [['atm'], 'j' => 'arena_team at ON atm.`arenaTeamId` = at.`arenaTeamId`', 's' => ', at.`name` AS "arenateamname", at.`arenaTeamId` AS "arenateam", IF(at.`captainGuid` = c.`guid`, 1, 0) AS "captain"']
    );

    public function __construct(\Dibi\Row|int $initData, array $extraOpts = [])
    {
        // if id lookup, select DB by realm
        $targetDBs = Profiler::getRealmDBs($extraOpts['rg'] ?? null, $extraOpts['sv'] ?? null);

        if (is_int($initData) && !$targetDBs)
        {
            trigger_error(__METHOD__.' - cannot access any realm.', E_USER_WARNING);
            return;
        }

        // warning: jank! - realmId is not already set by container only if the user preselected just the region in the search from and the region only contains a single realm
        if ($initData instanceof \Dibi\Row && !isset($initData['realmId']))
        {
            if (isset($extraOpts['realmId']))
                $initData['realmId'] = $extraOpts['realmId'];
            elseif ($targetDBs)
                $initData['realmId'] = key($targetDBs);
        }

        parent::__construct($initData, $extraOpts, $targetDBs);
    }

    public function applyInitData(\Dibi\Row $dbRow, array $opts) : bool
    {
        if (!$dbRow->name)
        {
            trigger_error('char #'.$dbRow->guid.' on realm #'.$dbRow->realmId.' has empty name.', E_USER_WARNING);
            return false;
        }

        if (!(['name' => $realmName, 'region' => $region] = Profiler::getRealms()[$dbRow->realmId] ?? null))
        {
            trigger_error(__METHOD__.' realm #'.$dbRow->realmId.' is inaccessible or does not exist.', E_USER_WARNING);
            return false;
        }

        $this->region    = $region;
        $this->realmId   = $dbRow->realmId;
        $this->realmGUID = $dbRow->guid;
        $this->realmName = $realmName;

        // rename to fit our structure
        $dbRow['renamePending'] = $dbRow->at_login & 0x1 ? 1 : 0;
        $dbRow['cuFlags']       = 0;
        $dbRow['activespec']    = $dbRow->activeTalentGroup;
        $dbRow['id']            = $this->subjectGUID;
        $dbRow['title']         = $dbRow->chosenTitle;

        return parent::applyInitData($dbRow, $opts);
    }

    public function amendLocalData(array $localData) : void
    {
        $this->id        = $localData['id'] ?? 0;
        $this->gearscore = $localData['gearscore'] ?? 0;
    }

    public function setTalentDistribution(int ...$trees) : void
    {
        $this->talenttree = [...$trees];
    }
}


class LocalProfileEntry extends ProfileEntry
{
    public const string QUERY_BASE = 'SELECT p.*, p.`id` AS ARRAY_KEY FROM ::profiler_profiles p';
    public const array  QUERY_OPTS = array(
        'p'   => [['g'], 'g' => 'p.`id`'],
        'ap'  => ['j' => ['::account_profiles ap ON ap.`profileId` = p.`id`', true], 's' => ', (IFNULL(ap.`extraFlags`, 0) | p.`cuFlags`) AS "cuFlags"'],
        'atm' => ['j' => ['::profiler_arena_team_member atm ON atm.`profileId` = p.`id`', true], 's' => ', atm.`captain`, atm.`personalRating` AS "rating", atm.`seasonGames`, atm.`seasonWins`'],
        'at'  => [['atm'], 'j' => ['::profiler_arena_team at ON at.`id` = atm.`arenaTeamId`', true], 's' => ', at.`type`'],
        'g'   => ['j' => ['::profiler_guild g ON g.`id` = p.`guild`', true], 's' => ', g.`name` AS "guildname"']
    );

    public function applyInitData(\Dibi\Row $dbRow, array $opts) : bool
    {
        if (($dbRow->realm && !(['name' => $realmName, 'region' => $region] = Profiler::getRealms()[$dbRow->realm] ?? null)) && empty($dbRow->custom))
        {
            trigger_error(__METHOD__.' realm #'.$dbRow->realm.' is inaccessible or does not exist.', E_USER_WARNING);
            return false;
        }

        $this->region    = $region           ?? '';
        $this->realmId   = $dbRow->realm     ?? 0;
        $this->realmGUID = $dbRow->realmGUID ?? 0;
        $this->realmName = $realmName        ?? '';

        // rename to fit our structure
        $dbRow['renamePending'] = 0;

        return parent::applyInitData($dbRow, $opts);
    }
}


?>
