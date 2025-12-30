<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfileList extends DBTypeList
{
    use profilerHelper, listviewHelper;

    public static int $contribute = CONTRIBUTE_NONE;

    public function getListviewData(int $addInfoMask = 0, array $reqCols = []) : array
    {
        $data = [];
        foreach ($this->iterate() as $__)
        {
            if (!$this->isVisibleToUser())
                continue;

            if (($addInfoMask & PROFILEINFO_PROFILE) && !$this->isCustom())
                continue;

            if (($addInfoMask & PROFILEINFO_CHARACTER) && $this->isCustom())
                continue;

            $data[$this->id] = array(
                'id'                => $this->getField('id'),
                'name'              => $this->getField('name'),
                'race'              => $this->getField('race'),
                'classs'            => $this->getField('class'),
                'gender'            => $this->getField('gender'),
                'level'             => $this->getField('level'),
                'faction'           => ChrRace::tryFrom($this->getField('race'))?->isAlliance() ? 0 : 1,
                'talenttree1'       => $this->getField('talenttree1'),
                'talenttree2'       => $this->getField('talenttree2'),
                'talenttree3'       => $this->getField('talenttree3'),
                'talentspec'        => $this->getField('activespec') + 1,                       // 0 => 1; 1 => 2
                'achievementpoints' => $this->getField('achievementpoints'),
                'guild'             => $this->curTpl['guildname'] ? '$"'.str_replace ('"', '', $this->curTpl['guildname']).'"' : '', // force this to be a string
                'guildrank'         => $this->getField('guildrank'),
                'realm'             => Profiler::urlize($this->getField('realmName'), true),
                'realmname'         => $this->getField('realmName'),
             // 'battlegroup'       => Profiler::urlize($this->getField('battlegroup')),        // was renamed to subregion somewhere around cata release
             // 'battlegroupname'   => $this->getField('battlegroup'),
                'gearscore'         => $this->getField('gearscore')
            );

            if ($addInfoMask & PROFILEINFO_USER)
                $data[$this->id]['published'] = (int)!!($this->getField('cuFlags') & PROFILER_CU_PUBLISHED);

            // for the lv this determins if the link is profile=<id> or profile=<region>.<realm>.<name>
            if (!$this->isCustom())
                $data[$this->id]['region']    = Profiler::urlize($this->getField('region'));

            if ($addInfoMask & PROFILEINFO_ARENA)
            {
                $data[$this->id]['rating']  = $this->getField('rating');
                $data[$this->id]['captain'] = $this->getField('captain');
                $data[$this->id]['games']   = $this->getField('seasonGames');
                $data[$this->id]['wins']    = $this->getField('seasonWins');
            }

            // Filter asked for skills - add them
            foreach ($reqCols as $col)
                $data[$this->id][$col] = $this->getField($col);

            if ($addInfoMask & PROFILEINFO_PROFILE)
            {
                if ($_ = $this->getField('description'))
                    $data[$this->id]['description'] = $_;

                if ($_ = $this->getField('icon'))
                    $data[$this->id]['icon'] = $_;
            }

            if ($addInfoMask & PROFILEINFO_CHARACTER)
                if ($_ = $this->getField('renameItr'))
                    $data[$this->id]['renameItr'] = $_;

            if ($this->getField('cuFlags') & PROFILER_CU_PINNED)
                $data[$this->id]['pinned'] = 1;

            if ($this->getField('deleted'))
                $data[$this->id]['deleted'] = 1;
        }

        return $data;
    }

    public function renderTooltip() : ?string
    {
        if (!$this->curTpl)
            return null;

        $title = '';
        $name  = $this->getField('name');
        if ($_ = $this->getField('title'))
            $title = (new TitleList(array(['id', $_])))->getField($this->getField('gender') ? 'female' : 'male', true);

        if ($this->isCustom())
            $name .= Lang::profiler('customProfile');
        else if ($title)
            $name = sprintf($title, $name);

        $x  = '<table>';
        $x .= '<tr><td><b class="q">'.$name.'</b></td></tr>';
        if ($g = $this->getField('guildname'))
            $x .= '<tr><td>&lt;'.$g.'&gt;</td></tr>';
        else if ($d = $this->getField('description'))
            $x .= '<tr><td>'.$d.'</td></tr>';
        $x .= '<tr><td>'.Lang::game('level').' '.$this->getField('level').' '.Lang::game('ra', $this->getField('race')).' '.Lang::game('cl', $this->getField('class')).'</td></tr>';
        $x .= '</table>';

        return $x;
    }

    public function getJSGlobals(int $addMask = 0) : array
    {
        $data   = [];
        $realms = Profiler::getRealms();

        foreach ($this->iterate() as $id => $__)
        {
            if (($addMask & PROFILEINFO_PROFILE) && $this->isCustom())
            {
                $profile = array(
                    'id'     => $this->getField('id'),
                    'name'   => $this->getField('name'),
                    'race'   => $this->getField('race'),
                    'classs' => $this->getField('class'),
                    'level'  => $this->getField('level'),
                    'gender' => $this->getField('gender')
                );

                if ($_ = $this->getField('icon'))
                    $profile['icon'] = $_;

                $data[] = $profile;

                continue;
            }

            if ($addMask & PROFILEINFO_CHARACTER && !$this->isCustom())
            {
                if (!isset($realms[$this->getField('realm')]))
                    continue;

                $data[] = array(
                    'id'        => $this->getField('id'),
                    'name'      => $this->getField('name'),
                    'realmname' => $realms[$this->getField('realm')]['name'],
                    'region'    => $realms[$this->getField('realm')]['region'],
                    'realm'     => Profiler::urlize($realms[$this->getField('realm')]['name']),
                    'race'      => $this->getField('race'),
                    'classs'    => $this->getField('class'),
                    'level'     => $this->getField('level'),
                    'gender'    => $this->getField('gender'),
                    'pinned'    => $this->getField('cuFlags') & PROFILER_CU_PINNED ? 1 : 0
                );
            }
        }

        return $data;
    }

    public function isCustom() : bool
    {
        return $this->getField('custom');
    }

    public function isVisibleToUser() : bool
    {
        if (!$this->isCustom() || User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
            return true;

        if ($this->getField('deleted'))
            return false;

        if (User::$id == $this->getField('user'))
            return true;

        return (bool)($this->getField('cuFlags') & PROFILER_CU_PUBLISHED);
    }

    public function getIcon() : string
    {
        if ($_ = $this->getField('icon'))
            return $_;

        return sprintf('chr_%s_%s_%s%02d',
            ChrRace::from($this->getField('race'))->json(),
            $this->getField('gender') ? 'female' : 'male',
            ChrClass::from($this->getField('class'))->json(),
            max(1, floor(($this->getField('level') - 60) / 10) + 2)
        );
    }

    public static function getName(int $id) : ?LocString { return null; }
}


class ProfileListFilter extends Filter
{
    use TrProfilerFilter;

    protected string $type          = 'profiles';
    protected static array $genericFilter = array(
         2 => [parent::CR_NUMERIC,  'gearscore',         NUM_CAST_INT              ], // gearscore [num]
         3 => [parent::CR_CALLBACK, 'cbAchievs',         null,                 null], // achievementpoints [num]
         5 => [parent::CR_NUMERIC,  'talenttree1',       NUM_CAST_INT              ], // talenttree1 [num]
         6 => [parent::CR_NUMERIC,  'talenttree2',       NUM_CAST_INT              ], // talenttree2 [num]
         7 => [parent::CR_NUMERIC,  'talenttree3',       NUM_CAST_INT              ], // talenttree3 [num]
         9 => [parent::CR_STRING,   'g.name'                                       ], // guildname
        10 => [parent::CR_CALLBACK, 'cbHasGuildRank',    null,                 null], // guildrank
        12 => [parent::CR_CALLBACK, 'cbTeamName',        2,                    null], // teamname2v2
        15 => [parent::CR_CALLBACK, 'cbTeamName',        3,                    null], // teamname3v3
        18 => [parent::CR_CALLBACK, 'cbTeamName',        5,                    null], // teamname5v5
        13 => [parent::CR_CALLBACK, 'cbTeamRating',      2,                    null], // teamrtng2v2
        16 => [parent::CR_CALLBACK, 'cbTeamRating',      3,                    null], // teamrtng3v3
        19 => [parent::CR_CALLBACK, 'cbTeamRating',      5,                    null], // teamrtng5v5
        14 => [parent::CR_NYI_PH,   null,                0 /* 2 */                 ], // teamcontrib2v2 [num]
        17 => [parent::CR_NYI_PH,   null,                0 /* 3 */                 ], // teamcontrib3v3 [num]
        20 => [parent::CR_NYI_PH,   null,                0 /* 5 */                 ], // teamcontrib5v5 [num]
        21 => [parent::CR_CALLBACK, 'cbWearsItems',      null,                 null], // wearingitem [str]
        23 => [parent::CR_CALLBACK, 'cbCompletedAcv',    null,                 null], // completedachievement
        25 => [parent::CR_CALLBACK, 'cbProfession',      SKILL_ALCHEMY,        null], // alchemy [num]
        26 => [parent::CR_CALLBACK, 'cbProfession',      SKILL_BLACKSMITHING,  null], // blacksmithing [num]
        27 => [parent::CR_CALLBACK, 'cbProfession',      SKILL_ENCHANTING,     null], // enchanting [num]
        28 => [parent::CR_CALLBACK, 'cbProfession',      SKILL_ENGINEERING,    null], // engineering [num]
        29 => [parent::CR_CALLBACK, 'cbProfession',      SKILL_HERBALISM,      null], // herbalism [num]
        30 => [parent::CR_CALLBACK, 'cbProfession',      SKILL_INSCRIPTION,    null], // inscription [num]
        31 => [parent::CR_CALLBACK, 'cbProfession',      SKILL_JEWELCRAFTING,  null], // jewelcrafting [num]
        32 => [parent::CR_CALLBACK, 'cbProfession',      SKILL_LEATHERWORKING, null], // leatherworking [num]
        33 => [parent::CR_CALLBACK, 'cbProfession',      SKILL_MINING,         null], // mining [num]
        34 => [parent::CR_CALLBACK, 'cbProfession',      SKILL_SKINNING,       null], // skinning [num]
        35 => [parent::CR_CALLBACK, 'cbProfession',      SKILL_TAILORING,      null], // tailoring [num]
        36 => [parent::CR_CALLBACK, 'cbHasGuild',        null,                 null]  // hasguild [yn]
    );

    protected static array $inputFields = array(
        'cr'    => [parent::V_RANGE,    [1, 36],                                          true ], // criteria ids
        'crs'   => [parent::V_LIST,     [parent::ENUM_NONE, parent::ENUM_ANY, [0, 5000]], true ], // criteria operators
        'crv'   => [parent::V_REGEX,    parent::PATTERN_CRV,                              true ], // criteria values
        'na'    => [parent::V_REGEX,    parent::PATTERN_NAME,                             false], // name - only printable chars, no delimiter
        'ma'    => [parent::V_EQUAL,    1,                                                false], // match any / all filter
        'ex'    => [parent::V_EQUAL,    'on',                                             false], // only match exact
        'si'    => [parent::V_LIST,     [SIDE_ALLIANCE, SIDE_HORDE],                      false], // side
        'ra'    => [parent::V_LIST,     [[1, 8], 10, 11],                                 true ], // race
        'cl'    => [parent::V_LIST,     [[1, 9], 11],                                     true ], // class
        'minle' => [parent::V_RANGE,    [1, MAX_LEVEL],                                   false], // min level
        'maxle' => [parent::V_RANGE,    [1, MAX_LEVEL],                                   false], // max level
        'rg'    => [parent::V_CALLBACK, 'cbRegionCheck',                                  false], // region
        'bg'    => [parent::V_EQUAL,    null,                                             false], // battlegroup - unsued here, but var expected by template
        'sv'    => [parent::V_CALLBACK, 'cbServerCheck',                                  false]  // server
    );

    public bool  $useLocalList = false;
    public array $extraOpts    = [];

    /*  heads up!
        a couple of filters are too complex to be run against the characters database
        if they are selected, force useage of LocalProfileList
    */

    public function __construct(string|array $data, array $opts = [])
    {
        parent::__construct($data, $opts);

        if (!empty($this->values['cr']))
            if (array_intersect($this->values['cr'], [2, 5, 6, 7, 21]))
                $this->useLocalList = true;
    }

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = $this->values;

        // region (rg), battlegroup (bg) and server (sv) are passed to ProflieList as miscData and handled there

        // table key differs between remote and local :<
        $k = $this->useLocalList ? 'p' : 'c';

        // name [str] - the table is case sensitive. Since i don't want to destroy indizes, lets alter the search terms
        if ($_v['na'])
        {
            $lower  = $this->tokenizeString([$k.'.name'], Util::lower($_v['na']),   $_v['ex'] == 'on', true);
            $proper = $this->tokenizeString([$k.'.name'], Util::ucWords($_v['na']), $_v['ex'] == 'on', true);

            $parts[] = ['OR', $lower, $proper];
        }

        // side [list]
        if ($_v['si'] == SIDE_ALLIANCE)
            $parts[] = [$k.'.race', ChrRace::fromMask(ChrRace::MASK_ALLIANCE)];
        else if ($_v['si'] == SIDE_HORDE)
            $parts[] = [$k.'.race', ChrRace::fromMask(ChrRace::MASK_HORDE)];

        // race [list]
        if ($_v['ra'])
            $parts[] = [$k.'.race', $_v['ra']];

        // class [list]
        if ($_v['cl'])
            $parts[] = [$k.'.class', $_v['cl']];

        // min level [int]
        if ($_v['minle'])
            $parts[] = [$k.'.level', $_v['minle'], '>='];

        // max level [int]
        if ($_v['maxle'])
            $parts[] = [$k.'.level', $_v['maxle'], '<='];

        return $parts;
    }

    protected function cbProfession(int $cr, int $crs, string $crv, $skillId) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        $k   = 'sk_'.Util::createHash(12);
        $col = 'skill-'.$skillId;

        $this->fiExtraCols[$skillId] = $col;

        if ($this->useLocalList)
        {
            $this->extraOpts[$k] = array(
                'j' => [sprintf('?_profiler_completion_skills %1$s ON `%1$s`.`id` = p.`id` AND `%1$s`.`skillId` = %2$d AND `%1$s`.`value` %3$s %4$d', $k, $skillId, $crs, $crv), true],
                's' => [', '.$k.'.`value` AS "'.$col.'"']
            );
            return [$k.'.skillId', null, '!'];
        }
        else
        {
            $this->extraOpts[$k] = array(
                'j' => [sprintf('character_skills %1$s ON `%1$s`.`guid` = c.`guid` AND `%1$s`.`skill` = %2$d AND `%1$s`.`value` %3$s %4$d', $k, $skillId, $crs, $crv), true],
                's' => [', '.$k.'.`value` AS "'.$col.'"']
            );
            return [$k.'.skill', null, '!'];
        }
    }

    protected function cbCompletedAcv(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT))
            return null;

        if (!Type::validateIds(Type::ACHIEVEMENT, $crv))
            return null;

        $k = 'acv_'.Util::createHash(12);

        if ($this->useLocalList)
        {
            $this->extraOpts[$k] = ['j' => [sprintf('?_profiler_completion_achievements %1$s ON `%1$s`.`id` = p.`id` AND `%1$s`.`achievementId` = %2$d', $k, $crv), true]];
            return [$k.'.achievementId', null, '!'];
        }
        else
        {
            $this->extraOpts[$k] = ['j' => [sprintf('character_achievement %1$s ON `%1$s`.`guid` = c.`guid` AND `%1$s`.`achievement` = %2$d', $k, $crv), true]];
            return [$k.'.achievement', null, '!'];
        }
    }

    protected function cbWearsItems(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT))
            return null;

        if (!Type::validateIds(Type::ITEM, $crv))
            return null;

        $k = 'i_'.Util::createHash(12);

        $this->extraOpts[$k] = ['j' => [sprintf('?_profiler_items %1$s ON `%1$s`.`id` = p.`id` AND `%1$s`.`item` = %2$d', $k, $crv), true]];
        return [$k.'.item', null, '!'];
    }

    protected function cbHasGuild(int $cr, int $crs, string $crv) : ?array
    {
        if (!$this->int2Bool($crs))
            return null;

        if ($this->useLocalList)
            return ['p.guild', null, $crs ? '!' : null];
        else
            return ['gm.guildId', null, $crs ? '!' : null];
    }

    protected function cbHasGuildRank(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        if ($this->useLocalList)
            return ['p.guildrank', $crv, $crs];
        else
            return ['gm.rank', $crv, $crs];
    }

    protected function cbTeamName(int $cr, int $crs, string $crv, $size) : ?array
    {
        if ($_ = $this->tokenizeString(['at.name'], $crv))
            return ['AND', ['at.type', $size], $_];

        return null;
    }

    protected function cbTeamRating(int $cr, int $crs, string $crv, $size) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        return ['AND', ['at.type', $size], ['at.rating', $crv, $crs]];
    }

    protected function cbAchievs(int $cr, int $crs, string $crv) : ?array
    {
        if (!Util::checkNumeric($crv, NUM_CAST_INT) || !$this->int2Op($crs))
            return null;

        if ($this->useLocalList)
            return ['p.achievementpoints', $crv, $crs];
        else
            return ['cap.counter', $crv, $crs];
    }
}


class RemoteProfileList extends ProfileList
{
    protected string $queryBase = 'SELECT `c`.*, `c`.`guid` AS ARRAY_KEY FROM characters c';
    protected array  $queryOpts = array(
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
        if (!$this->selectRealms($miscData))
        {
            trigger_error('RemoteProfileList::__construct - cannot access any realm.', E_USER_WARNING);
            return;
        }

        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        reset($this->dbNames);                              // only use when querying single realm
        $realmId      = key($this->dbNames);
        $realms       = Profiler::getRealms();
        $talentSpells = [];
        $talentLookup = [];
        $distrib      = [];

        // post processing
        foreach ($this->iterate() as $guid => &$curTpl)
        {
            // battlegroup
            $curTpl['battlegroup'] = Cfg::get('BATTLEGROUP');

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
                if (!isset($this->rnItr[$curTpl['name']]))
                    $this->rnItr[$curTpl['name']] = DB::Aowow()->selectCell('SELECT MAX(`renameItr`) FROM ?_profiler_profiles WHERE `realm` = ?d AND `custom` = 0 AND `name` = ?', $r, $curTpl['name']) ?: 0;

                // already saved as "pending rename"
                if ($rnItr = DB::Aowow()->selectCell('SELECT `renameItr` FROM ?_profiler_profiles WHERE `realm` = ?d AND `realmGUID` = ?d', $r, $g))
                    $curTpl['renameItr'] = $rnItr;
                // not yet recognized: get max itr
                else
                    $curTpl['renameItr'] = ++$this->rnItr[$curTpl['name']];
            }
            else
                $curTpl['renameItr'] = 0;

            $curTpl['cuFlags'] = 0;
        }

        foreach ($talentLookup as $realm => $chars)
            $talentLookup[$realm] = DB::Characters($realm)->selectCol('SELECT `guid` AS ARRAY_KEY, `spell` AS ARRAY_KEY2, `talentGroup` FROM character_talent ct WHERE `guid` IN (?a)', array_keys($chars));

        $talentSpells = DB::Aowow()->select('SELECT `spell` AS ARRAY_KEY, `tab`, `rank` FROM ?_talents WHERE `class` IN (?a)', array_unique($talentSpells));

        // equalize subject distribution across realms
        $limit = 0;
        foreach ($conditions as $c)
            if (is_numeric($c))
                $limit = max(0, (int)$c);

        if (!$limit)                                        // int:0 means unlimited, so skip process
            $distrib = [];

        $total = array_sum($distrib);
        foreach ($distrib as &$d)
            $d = ceil($limit * $d / $total);

        foreach ($this->iterate() as $guid => &$curTpl)
        {
            if ($distrib)
            {
                if ($limit <= 0 || $distrib[$curTpl['realm']] <= 0)
                {
                    unset($this->templates[$guid]);
                    continue;
                }

                $distrib[$curTpl['realm']]--;
                $limit--;
            }

            [$r, $g] = explode(':', $guid);

            // talent points post
            $curTpl['talenttree1'] = 0;
            $curTpl['talenttree2'] = 0;
            $curTpl['talenttree3'] = 0;
            if (!empty($talentLookup[$r][$g]))
            {
                $talents = array_filter($talentLookup[$r][$g], function($v) use ($curTpl) { return $curTpl['activespec'] == $v; } );
                foreach (array_intersect_key($talentSpells, $talents) as $spell => $data)
                    $curTpl['talenttree'.($data['tab'] + 1)] += $data['rank'];
            }
        }
    }

    public function getListviewData(int $addInfoMask = 0, array $reqCols = []) : array
    {
        $data = parent::getListviewData($addInfoMask, $reqCols);

        // not wanted on server list
        foreach ($data as &$d)
            unset($d['published']);

        return $data;
    }

    public function initializeLocalEntries() : void
    {
        $baseData = $guildData = [];
        foreach ($this->iterate() as $guid => $__)
        {
            $realmId   = $this->getField('realm');
            $guildGUID = $this->getField('guild');

            $baseData[$guid] = array(
                'realm'     => $realmId,
                'realmGUID' => $this->getField('guid'),
                'name'      => $this->getField('name'),
                'renameItr' => $this->getField('renameItr'),
                'race'      => $this->getField('race'),
                'class'     => $this->getField('class'),
                'level'     => $this->getField('level'),
                'gender'    => $this->getField('gender'),
                'guild'     => $guildGUID ?: null,
                'guildrank' => $guildGUID ? $this->getField('guildrank') : null,
                'stub'      => 1
            );

            if ($guildGUID && empty($guildData[$realmId.'-'.$guildGUID]))
                $guildData[$realmId.'-'.$guildGUID] = array(
                    'realm'     => $realmId,
                    'realmGUID' => $guildGUID,
                    'name'      => $this->getField('guildname'),
                    'nameUrl'   => Profiler::urlize($this->getField('guildname')),
                    'stub'      => 1
                );
        }

        // basic guild data (satisfying table constraints)
        if ($guildData)
        {
            foreach (Util::createSqlBatchInsert($guildData) as $ins)
                DB::Aowow()->query('INSERT INTO ?_profiler_guild (?#) VALUES '.$ins.' ON DUPLICATE KEY UPDATE `id` = `id`', array_keys(reset($guildData)));

            // merge back local ids
            $localGuilds = DB::Aowow()->selectCol('SELECT `realm` AS ARRAY_KEY, `realmGUID` AS ARRAY_KEY2, `id` FROM ?_profiler_guild WHERE `realm` IN (?a) AND `realmGUID` IN (?a)',
                array_column($guildData, 'realm'), array_column($guildData, 'realmGUID')
            );

            foreach ($baseData as &$bd)
                if ($bd['guild'])
                    $bd['guild'] = $localGuilds[$bd['realm']][$bd['guild']];
        }

        // basic char data (enough for tooltips)
        if ($baseData)
        {
            foreach ($baseData as $ins)
                DB::Aowow()->query('INSERT INTO ?_profiler_profiles (?#) VALUES (?a) ON DUPLICATE KEY UPDATE `name` = ?, `renameItr` = ?d', array_keys($ins), array_values($ins), $ins['name'], $ins['renameItr']);

            // merge back local ids
            $localIds = DB::Aowow()->select(
               'SELECT CONCAT(`realm`, ":", `realmGUID`) AS ARRAY_KEY, `id`, `gearscore` FROM ?_profiler_profiles WHERE `custom` = 0 AND `realm` IN (?a) AND `realmGUID` IN (?a)',
                array_column($baseData, 'realm'),
                array_column($baseData, 'realmGUID')
            );

            foreach ($this->iterate() as $guid => &$_curTpl)
                if (isset($localIds[$guid]))
                    $_curTpl = array_merge($_curTpl, $localIds[$guid]);
        }
    }
}


class LocalProfileList extends ProfileList
{
    protected string $queryBase = 'SELECT p.*, p.`id` AS ARRAY_KEY FROM ?_profiler_profiles p';
    protected array  $queryOpts = array(
                        'p'   => [['g'], 'g' => 'p.`id`'],
                        'ap'  => ['j' => ['?_account_profiles ap ON ap.`profileId` = p.`id`', true], 's' => ', (IFNULL(ap.`extraFlags`, 0) | p.`cuFlags`) AS "cuFlags"'],
                        'atm' => ['j' => ['?_profiler_arena_team_member atm ON atm.`profileId` = p.`id`', true], 's' => ', atm.`captain`, atm.`personalRating` AS "rating", atm.`seasonGames`, atm.`seasonWins`'],
                        'at'  => [['atm'], 'j' => ['?_profiler_arena_team at ON at.`id` = atm.`arenaTeamId`', true], 's' => ', at.`type`'],
                        'g'   => ['j' => ['?_profiler_guild g ON g.`id` = p.`guild`', true], 's' => ', g.`name` AS "guildname"']
                    );

    public function __construct(array $conditions = [], array $miscData = [])
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
            array_unshift($conditions, 'AND');
            $conditions = ['AND', ['realm', $realmIds], $conditions];
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
            return $url.$this->getField('id');

        return $url.implode('.', array(
            $this->getField('region'),
            Profiler::urlize($this->getField('realmName'), true),
            urlencode($this->getField('name'))
        ));
    }
}


?>
