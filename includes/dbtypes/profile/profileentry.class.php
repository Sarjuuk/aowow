<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class ProfileEntry extends DBTypeEntry
{
    use TrProfilerHelper;

    public static int $contribute = CONTRIBUTE_NONE;

    public readonly  string $name;
    public readonly  int    $cuFlags;
    public readonly  int    $race;
    public readonly  int    $class;
    public readonly  int    $gender;
    public readonly  int    $level;
    public readonly  int    $title;
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

    private array $extraColData = [];

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->name = new LocString($initData, 'name', pruneFromSrc: true);

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                    continue 2;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }
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

        // Filter asked for skills - add them
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

    public function getJSGlobal(int $addMask = 0) : array
    {
        if (($addMask & LISTVIEWINFO_PROFILE) && $this->isCustom())
        {
            $profile = array(
                'id'     => $this->id,
                'name'   => $this->name,
                'race'   => $this->race,
                'classs' => $this->class,
                'level'  => $this->level,
                'gender' => $this->gender
            );

            if ($this->icon)
                $profile['icon'] = $this->icon;

            return $profile;
        }

        $realms = Profiler::getRealms();
        if ($addMask & LISTVIEWINFO_CHARACTER && !$this->isCustom() && isset($realms[$this->realmId]))
        {
            return array(
                'id'        => $this->id,
                'name'      => $this->name,
                'realmname' => $realms[$this->realmId]['name'],
                'region'    => $realms[$this->realmId]['region'],
                'realm'     => Profiler::urlize($realms[$this->realmId]['name']),
                'race'      => $this->race,
                'classs'    => $this->class,
                'level'     => $this->level,
                'gender'    => $this->gender,
                'pinned'    => $this->cuFlags & PROFILER_CU_PINNED ? 1 : 0
            );
        }

        return [];
    }

    public function renderTooltip() : ?string
    {
        if (!$this->curTpl)
            return null;

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

    public static function getName(int $id) : ?LocString { return null; }
}


class RemoteProfileEntry extends ProfileEntry
{
    public readonly string $battlegroup;

    public const /* string */ QUERY_BASE = 'SELECT `c`.*, `c`.`guid` AS ARRAY_KEY FROM characters c';
    public const /* array  */ QUERY_OPTS = array(
        'c'   => [['gm', 'g', 'cap']],                                                             // 12698: use criteria of Achievement 4496 as shortcut to get total achievement points
        'cap' => ['j' => ['character_achievement_progress cap ON cap.`guid` = c.`guid` AND cap.`criteria` = 12698', true], 's' => ', IFNULL(cap.`counter`, 0) AS "achievementpoints"'],
        'gm'  => ['j' => ['guild_member gm ON gm.`guid` = c.`guid`', true], 's' => ', gm.`rank` AS "guildrank"'],
        'g'   => ['j' => ['guild g ON g.`guildid` = gm.`guildid`', true], 's' => ', g.`guildid` AS "guild", g.`name` AS "guildname"'],
        'atm' => ['j' => ['arena_team_member atm ON atm.`guid` = c.`guid`', true], 's' => ', atm.`personalRating` AS "rating"'],
        'at'  => [['atm'], 'j' => 'arena_team at ON atm.`arenaTeamId` = at.`arenaTeamId`', 's' => ', at.`name` AS "arenateam", IF(at.`captainGuid` = c.`guid`, 1, 0) AS "captain"']
    );

    private array $rnItr = [];                              // rename iterator [name => nCharsWithThisName]

    public function __construct(array $conditions = [], array $miscData = [])
    {
        // select DB by realm
        if (!$dbNames = self::getRealmDBs($miscData))
        {
            trigger_error('RemoteProfileEntry::__construct - cannot access any realm.', E_USER_WARNING);
            return;
        }

        parent::__construct($conditions, $miscData, $dbNames);
    }

    public function applyInitData(array $initData) : void
    {
        $realms       = Profiler::getRealms();
        $talentSpells = [];
        $talentLookup = [];
        $distrib      = [];

        // battlegroup
        $this->battlegroup = Cfg::get('BATTLEGROUP');

        // realm
        [$r, $g] = explode(':', $guid);
        if (!empty($realms[$r]))
        {
            $curTpl['realm']     = $r;
            $curTpl['realmName'] = $realms[$r]['name'];
            $curTpl['region']    = $realms[$r]['region'];
        }
        else
        {
            trigger_error('char #'.$guid.' belongs to nonexistent realm #'.$r, E_USER_WARNING);
            unset($this->templates[$guid]);
            continue;
        }

        // empty name
        if (!$curTpl['name'])
        {
            trigger_error('char #'.$guid.' on realm #'.$r.' has empty name.', E_USER_WARNING);
            unset($this->templates[$guid]);
            continue;
        }

        // temp id
        $curTpl['id'] = 0;

        // talent points pre
        $talentLookup[$r][$g] = [];
        $talentSpells[] = $curTpl['class'];
        $curTpl['activespec'] = $curTpl['activeTalentGroup'];

        // equalize distribution
        if (empty($distrib[$curTpl['realm']]))
            $distrib[$curTpl['realm']] = 1;
        else
            $distrib[$curTpl['realm']]++;

        // char is pending rename
        if ($curTpl['at_login'] & 0x1)
        {
            $this->rnItr[$curTpl['name']] ??= DB::Aowow()->selectCell('SELECT MAX(`renameItr`) FROM ::profiler_profiles WHERE `realm` = %i AND `custom` = 0 AND `name` = %s', $r, $curTpl['name']) ?: 0;

            // already saved as "pending rename"
            if ($rnItr = DB::Aowow()->selectCell('SELECT `renameItr` FROM ::profiler_profiles WHERE `realm` = %i AND `realmGUID` = %i', $r, $g))
                $curTpl['renameItr'] = $rnItr;
            // not yet recognized: get max itr
            else
                $curTpl['renameItr'] = ++$this->rnItr[$curTpl['name']];
        }
        else
            $curTpl['renameItr'] = 0;

        $curTpl['cuFlags'] = 0;
    }

    public function amendLocalData(array $localData) : void
    {
        $this->id        = $localData['id'] ?? 0;
        $this->gearscore = $localData['gearscore'] ?? 0;
    }
}


class LocalProfileEntry extends ProfileEntry
{
    public const /* string */ QUERY_BASE = 'SELECT p.*, p.`id` AS ARRAY_KEY FROM ::profiler_profiles p';
    public const /* array  */ QUERY_OPTS = array(
        'p'   => [['g'], 'g' => 'p.`id`'],
        'ap'  => ['j' => ['::account_profiles ap ON ap.`profileId` = p.`id`', true], 's' => ', (IFNULL(ap.`extraFlags`, 0) | p.`cuFlags`) AS "cuFlags"'],
        'atm' => ['j' => ['::profiler_arena_team_member atm ON atm.`profileId` = p.`id`', true], 's' => ', atm.`captain`, atm.`personalRating` AS "rating", atm.`seasonGames`, atm.`seasonWins`'],
        'at'  => [['atm'], 'j' => ['::profiler_arena_team at ON at.`id` = atm.`arenaTeamId`', true], 's' => ', at.`type`'],
        'g'   => ['j' => ['::profiler_guild g ON g.`id` = p.`guild`', true], 's' => ', g.`name` AS "guildname"']
    );

    public function __construct(
                  int|array $initData,
        protected array     $extraOpts = [],
                  array     $targetDBs = ['Aowow']
    )
    {
        $realms = Profiler::getRealms();

        // graft realm selection from miscData onto conditions
        $realmIds = [];
        if (isset($miscData['sv']))
            $realmIds = array_keys(array_filter($realms, fn($x) => Profiler::urlize($x['name']) == Profiler::urlize($miscData['sv'])));

        if (isset($miscData['rg']))
            $realmIds = array_merge($realmIds, array_keys(array_filter($realms, fn($x) => $x['region'] == $miscData['rg'])));

        if ($conditions && $realmIds)
        {
            array_unshift($conditions, DB::AND);
            $conditions = [DB::AND, ['realm', $realmIds], $conditions];
        }
        else if ($realmIds)
            $conditions = [['realm', $realmIds]];

        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        foreach ($this->iterate() as $id => &$curTpl)
        {
            if (!$curTpl['realm'])                          // custom profile w/o realminfo
                continue;

            if (!isset($realms[$curTpl['realm']]))
            {
                unset($this->templates[$id]);
                continue;
            }

            $curTpl['realmName']   = $realms[$curTpl['realm']]['name'];
            $curTpl['region']      = $realms[$curTpl['realm']]['region'];
            $curTpl['battlegroup'] = Cfg::get('BATTLEGROUP');
        }
    }

    public function getProfileUrl() : string
    {
        $url = '?profile=';

        if ($this->isCustom())
            return $url.$this->id;

        return $url.implode('.', array(
            $this->region,
            Profiler::urlize($this->realmName, true),
            urlencode($this->name)
        ));
    }
}


?>
