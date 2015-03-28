<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// class CharacterList extends BaseType                     // new profiler-related parent: ProfilerType?; maybe a trait is enough => use ProfileHelper;
// class GuildList extends BaseType
// class ArenaTeamList extends BaseType
class ProfileList extends BaseType
{
    public static $type      = 0;                           // profiles dont actually have one
    public static $brickFile = 'profile';

    protected     $queryBase = ''; // SELECT p.*, p.id AS ARRAY_KEY FROM ?_profiles p';
    protected     $queryOpts = array(
                        'p'   => [['pa', 'pg']],
                        'pam' => [['?_profiles_arenateam_member pam ON pam.memberId = p.id', true], 's' => ', pam.status'],
                        'pa'  => ['?_profiles_arenateam pa ON pa.id = pam.teamId', 's' => ', pa.mode, pa.name'],
                        'pgm' => [['?_profiles_guid_member pgm ON pgm.memberId = p.Id', true], 's' => ', pgm.rankId'],
                        'pg'  => ['?_profiles_guild pg ON pg.if = pgm.guildId', 's' => ', pg.name']
                    );

    public function __construct($conditions = [], $miscData = null)
    {
        $character = array(
            'id'                => 2,
            'name'              => 'CharName',
            'region'            => ['eu', 'Europe'],
            'battlegroup'       => ['pure-pwnage', 'Pure Pwnage'],
            'realm'             => ['dafuque', 'da\'Fuqúe'],
            'level'             => 80,
            'classs'            => 11,
            'race'              => 6,
            'faction'           => 1,                           // 0:alliance; 1:horde
            'gender'            => 1,                           // 0:male, 1:female
            'skincolor'         => 0,                           // playerbytes  % 256
            'hairstyle'         => 0,                           // (playerbytes >> 16) % 256
            'haircolor'         => 0,                           // (playerbytes >> 24) % 256
            'facetype'          => 0,                           // (playerbytes >> 8) % 256                 [maybe features]
            'features'          => 0,                           // playerBytes2 % 256                       [maybe facetype]
            'source'            => 2,                           // source: used if you create a profile from a genuine character. It inherites region, realm and bGroup
            'sourcename'        => 'SourceCharName',            //  >   if these three are false we get a 'genuine' profile [0 for genuine characters..?]
            'user'              => 1,                           //  >   'genuine' is the parameter for _isArmoryProfile(allowCustoms)   ['' for genuine characters..?]
            'username'          => 'TestUser',                  //  >   also, if 'source' <> 0, the char-icon is requestet via profile.php?avatar
            'published'         => 1,                           // public / private
            'pinned'            => 1,                           // usable for some utility funcs on site
            'nomodel'           => 0x0,                         // unchecks DisplayOnCharacter by (1 << slotId - 1)
            'title'             => 0,                           // titleId currently in use or null
            'guild'             => 'GuildName',                 // only on chars; id or null
            'description'       => 'this is a profile',         // only on custom profiles
            'arenateams'        => [],                          // [size(2|3|5) => DisplayName]; DisplayName gets urlized to use as link
            'playedtime'        => 0,                           // exact to the day
            'lastupdated'       => 0,                           // timestamp in ms
            'achievementpoints' => 0,                           // max you have
            'talents'           => array(
                'builds' => array(
                    ['talents' => '', 'glyphs' => ''],          // talents:string of 0-5 points; glyphs: itemIds.join(':')
                ),
                'active'  => 1                                  // 1|2
            ),
            'customs'           => [],                          // custom profiles created from this char; profileId => [name, ownerId, iconString(optional)]
            'skills'            => [],                          // skillId => [curVal, maxVal]; can contain anything, should be limited to prim/sec professions
            'inventory'         => [],                          // slotId => [itemId, subItemId, permEnchantId, tempEnchantId, gemItemId1, gemItemId2, gemItemId3, gemItemId4]
            'auras'             => [],                          // custom list of buffs, debuffs [spellId]

            // completion lists: [subjectId => amount/timestamp/1]
            'reputation'        => [],                          // factionId => amount
            'titles'            => [],                          // titleId => 1
            'spells'            => [],                          // spellId => 1; recipes, pets, mounts
            'achievements'      => [],                          // achievementId => timestamp
            'quests'            => [],                          // questId => 1

            // UNKNOWN
            'bookmarks'         => [2],                         // UNK pinned or claimed userId => profileIds..?
            'statistics'        => [],                          // UNK all statistics?      [achievementId => killCount]
            'activity'          => [],                          // UNK recent achievements? [achievementId => killCount]
            'glyphs'            => [],                          // not really used .. i guess..?
            'pets'              => array(                       // UNK
                [],                                             // one array per pet, structure UNK
            ),
        );

        // parent::__construct($conditions, $miscData);
        @include('datasets/ProfilerExampleChar');       // tmp char data

        $this->templates[2] = $character;
        $this->curTpl = $character;

        if ($this->error)
            return;

        // post processing
        // foreach ($this->iterate() as $_id => &$curTpl)
        // {
        // }
    }

    public function getListviewData()
    {
        $data = [];
        foreach ($this->iterate() as $__)
        {
            $tDistrib = $this->getTalentDistribution();

            $data[$this->id] = array(
                'id'                => 0,
                'name'              => $this->curTpl['name'],
                'achievementpoints' => $this->curTpl['achievementpoints'],
                'guild'             => $this->curTpl['guild'], // 0 if none
                'guildRank'         => -1,
                'realm'             => $this->curTpl['realm'][0],
                'realmname'         => $this->curTpl['realm'][1],
                'battlegroup'       => $this->curTpl['battlegroup'][0],
                'battlegroupname'   => $this->curTpl['battlegroup'][0],
                'region'            => $this->curTpl['region'][0],
                'level'             => $this->curTpl['level'],
                'race'              => $this->curTpl['race'],
                'gender'            => $this->curTpl['gender'],
                'classs'            => $this->curTpl['classs'],
                'faction'           => $this->curTpl['faction'],
                'talenttree1'       => $tDistrib[0],
                'talenttree2'       => $tDistrib[1],
                'talenttree3'       => $tDistrib[2],
                'talentspec'        => $this->curTpl['talents']['active']
            );

            if (!empty($this->curTpl['description']))
                $data[$this->id]['description'] = $this->curTpl['description'];

            if (!empty($this->curTpl['icon']))
                $data[$this->id]['icon'] = $this->curTpl['icon'];

            if ($this->curTpl['cuFlags'] & PROFILE_CU_PUBLISHED)
                $data[$this->id]['published'] = 1;

            if ($this->curTpl['cuFlags'] & PROFILE_CU_PINNED)
                $data[$this->id]['pinned'] = 1;

            if ($this->curTpl['cuFlags'] & PROFILE_CU_DELETED)
                $data[$this->id]['deleted'] = 1;
        }

        return $data;
    }

    public function renderTooltip($interactive = false)
    {
        if (!$this->curTpl)
            return [];

        $x  = '<table>';
        $x .= '<tr><td><b class="q">'.$this->getField('name').'</b></td></tr>';
        if ($g = $this->getField('name'))
            $x .= '<tr><td>&lt;'.$g.'&gt; ('.$this->getField('guildrank').')</td></tr>';
        else if ($d = $this->getField('description'))
            $x .= '<tr><td>'.$d.'</td></tr>';
        $x .= '<tr><td>'.Lang::game('level').' '.$this->getField('level').' '.Lang::game('ra', $this->curTpl['race']).' '.Lang::game('cl', $this->curTpl['classs']).'</td></tr>';
        $x .= '</table>';

        return $x;
    }

    public function getJSGlobals($addMask = 0) {}

    private function getTalentDistribution()
    {
        if (!empty($this->tDistribution))
            $this->tDistribution[$this->curTpl['classId']] = DB::Aowow()->selectCol('SELECT COUNT(t.id) FROM dbc_talent t JOIN dbc_talenttab tt ON t.tabId = tt.id WHERE tt.classMask & ?d GROUP BY tt.id ORDER BY tt.tabNumber ASC', 1 << ($this->curTpl['classId'] - 1));

        $result = [];
        $start  = 0;
        foreach ($this->tDistribution[$this->curTpl['classId']] as $len)
        {
            $result[] = array_sum(str_split(substr($this->curTpl['talentString'], $start, $len)));
            $start += $len;
        }

        return $result;
    }
}


class ProfileListFilter extends Filter
{
    public    $extraOpts     = [];

    protected $genericFilter = array(
    );

    protected function createSQLForCriterium(&$cr)
    {
        if (in_array($cr[0], array_keys($this->genericFilter)))
        {
            if ($genCR = $this->genericCriterion($cr))
                return $genCR;

            unset($cr);
            $this->error = true;
            return [1];
        }

        switch ($cr[0])
        {
            default:
                break;
        }

        unset($cr);
        $this->error = 1;
        return [1];
    }

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = $this->fiData['v'];

        // name
        if (isset($_v['na']))
            if ($_ = $this->modularizeString(['name_loc'.User::$localeId]))
                $parts[] = $_;

        return $parts;
    }
}

?>
