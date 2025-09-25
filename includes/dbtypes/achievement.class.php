<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AchievementList extends DBTypeList
{
    use listviewHelper;

    public static int    $type      = Type::ACHIEVEMENT;
    public static string $brickFile = 'achievement';
    public static string $dataTable = '?_achievement';
    public        array  $criteria  = [];

    protected string $queryBase = 'SELECT `a`.*, `a`.`id` AS ARRAY_KEY FROM ?_achievement a';
    protected array  $queryOpts = array(
                        'a'  => [['ic'], 'o' => 'orderInGroup ASC'],
                        'ic' => ['j' => ['?_icons ic ON ic.id = a.iconId', true], 's' => ', ic.name AS iconString'],
                        'ac' => ['j' => ['?_achievementcriteria AS `ac` ON `ac`.`refAchievementId` = `a`.`id`', true], 'g' => '`a`.`id`']
                    );

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        // post processing
        $rewards = DB::World()->select(
           'SELECT    ar.`ID` AS ARRAY_KEY, ar.`TitleA`, ar.`TitleH`, ar.`ItemID`, ar.`Sender` AS "sender", ar.`MailTemplateID`,
                      ar.`Subject` AS "subject_loc0", IFNULL(arl2.`Subject`, "") AS "subject_loc2", IFNULL(arl3.`Subject`, "") AS "subject_loc3", IFNULL(arl4.`Subject`, "") AS "subject_loc4", IFNULL(arl6.`Subject`, "") AS "subject_loc6", IFNULL(arl8.`Subject`, "") AS "subject_loc8",
                      ar.`Body`    AS "text_loc0",    IFNULL(arl2.`Body`,    "") AS "text_loc2",    IFNULL(arl3.`Body`,    "") AS "text_loc3",    IFNULL(arl4.`Body`,    "") AS "text_loc4",    IFNULL(arl6.`Body`,    "") AS "text_loc6",    IFNULL(arl8.`Body`,    "") AS "text_loc8"
            FROM      achievement_reward ar
            LEFT JOIN achievement_reward_locale arl2 ON arl2.`ID` = ar.`ID` AND arl2.`Locale` = "frFR"
            LEFT JOIN achievement_reward_locale arl3 ON arl3.`ID` = ar.`ID` AND arl3.`Locale` = "deDE"
            LEFT JOIN achievement_reward_locale arl4 ON arl4.`ID` = ar.`ID` AND arl4.`Locale` = "zhCN"
            LEFT JOIN achievement_reward_locale arl6 ON arl6.`ID` = ar.`ID` AND arl6.`Locale` = "esES"
            LEFT JOIN achievement_reward_locale arl8 ON arl8.`ID` = ar.`ID` AND arl8.`Locale` = "ruRU"
            WHERE     ar.`ID` IN (?a)',
            $this->getFoundIDs()
        );

        foreach ($this->iterate() as $_id => &$_curTpl)
        {
            $_curTpl['rewards'] = [];

            if (!empty($rewards[$_id]))
            {
                $_curTpl = array_merge($rewards[$_id], $_curTpl);

                $_curTpl['mailTemplate'] = $rewards[$_id]['MailTemplateID'];

                if ($rewards[$_id]['MailTemplateID'])
                {
                    // using class Loot creates an inifinite loop cirling between Loot, ItemList and SpellList or something
                    // $mailSrc = new Loot();
                    // $mailSrc->getByContainer(LOOT_MAIL, $rewards[$_id]['MailTemplateID']);
                    // foreach ($mailSrc->iterate() as $loot)
                        // $_curTpl['rewards'][] = [Type::ITEM, $loot['id']];

                    // lets just assume for now, that mailRewards for achievements do not contain references
                    $mailRew = DB::World()->selectCol('SELECT `Item` FROM mail_loot_template WHERE `Reference` <= 0 AND `entry` = ?d', $rewards[$_id]['MailTemplateID']);
                    foreach ($mailRew AS $mr)
                        $_curTpl['rewards'][] = [Type::ITEM, $mr];
                }
            }

            //"rewards":[[11,137],[3,138]]   [type, typeId]
            if (!empty($_curTpl['ItemID']))
                $_curTpl['rewards'][] = [Type::ITEM, $_curTpl['ItemID']];
            if (!empty($_curTpl['itemExtra']))
                $_curTpl['rewards'][] = [Type::ITEM, $_curTpl['itemExtra']];
            if (!empty($_curTpl['TitleA']))
                $_curTpl['rewards'][] = [Type::TITLE, $_curTpl['TitleA']];
            if (!empty($_curTpl['TitleH']))
                if (empty($_curTpl['TitleA']) || $_curTpl['TitleA'] != $_curTpl['TitleH'])
                    $_curTpl['rewards'][] = [Type::TITLE, $_curTpl['TitleH']];

            // icon
            $_curTpl['iconString'] = $_curTpl['iconString'] ?: 'trade_engineering';
        }
    }

    public function getJSGlobals(int $addMask = GLOBALINFO_ANY) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            if ($addMask & GLOBALINFO_SELF)
                $data[Type::ACHIEVEMENT][$this->id] = ['icon' => $this->curTpl['iconString'], 'name' => $this->getField('name', true)];

            if ($addMask & GLOBALINFO_REWARDS)
                foreach ($this->curTpl['rewards'] as $_)
                    $data[$_[0]][$_[1]] = $_[1];
        }

        return $data;
    }

    public function getListviewData(int $addInfoMask = 0x0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'          => $this->id,
                'name'        => $this->getField('name', true),
                'description' => $this->getField('description', true),
                'points'      => $this->curTpl['points'],
                'side'        => $this->curTpl['faction'],
                'category'    => $this->curTpl['category'],
                'parentcat'   => $this->curTpl['parentCat'],
            );

            if ($addInfoMask & ACHIEVEMENTINFO_PROFILE)
                $data[$this->id]['icon'] = $this->curTpl['iconString'];

            // going out on a limb here: type = 1 if in level 3 of statistics tree, so, IF (statistic AND parentCat NOT statistic (1)) i guess
            if ($this->curTpl['flags'] & ACHIEVEMENT_FLAG_COUNTER && $this->curTpl['parentCat'] != 1)
                $data[$this->id]['type'] = 1;

            if ($_ = $this->curTpl['rewards'])
                $data[$this->id]['rewards'] = $_;
            else if ($_ = $this->getField('reward', true))
                $data[$this->id]['reward'] = $_;
        }

        return $data;
    }

    // only for current template
    public function getCriteria() : array
    {
        if (isset($this->criteria[$this->id]))
            return $this->criteria[$this->id];

        $result = DB::Aowow()->Select('SELECT * FROM ?_achievementcriteria WHERE `refAchievementId` = ?d ORDER BY `order` ASC', $this->curTpl['refAchievement'] ?: $this->id);
        if (!$result)
            return [];

        $this->criteria[$this->id] = $result;

        return $this->criteria[$this->id];
    }

    public function renderTooltip() : ?string
    {
        $criteria = $this->getCriteria();
        $tmp  = [];
        $rows = [];
        $i    = 0;
        foreach ($criteria as $_row)
        {
            if ($i++ % 2)
                $tmp[] = $_row;
            else
                $rows[] = $_row;
        }
        if ($tmp)
            $rows = array_merge($rows, $tmp);

        $description = $this->getField('description', true);
        $name        = $this->getField('name', true);
        $criteria    = '';

        $i = 0;
        foreach ($rows as $crt)
        {
            $obj = (int)$crt['value1'];
            $qty = (int)$crt['value2'];

            // we could show them, but the tooltips are cluttered
            if (($crt['completionFlags'] & ACHIEVEMENT_CRITERIA_FLAG_HIDDEN) && User::isInGroup(U_GROUP_STAFF))
                continue;

            $crtName = Util::localizedString($crt, 'name');
            switch ($crt['type'])
            {
                // link to quest
                case ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUEST:
                    if (!$crtName)
                        $crtName = QuestList::getName($obj);
                    break;
                // link to spell (/w icon)
                case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET:
                case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2:
                case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL:
                case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL:
                case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2:
                    if (!$crtName)
                        $crtName = SpellList::getName($obj);
                    break;
                // link to item (/w icon)
                case ACHIEVEMENT_CRITERIA_TYPE_OWN_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_USE_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_LOOT_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_EQUIP_ITEM:
                    if (!$crtName)
                        $crtName = ItemList::getName($obj);
                    break;
                // link to faction (/w target reputation)
                case ACHIEVEMENT_CRITERIA_TYPE_GAIN_REPUTATION:
                    if (!$crtName)
                        $crtName = FactionList::getName($obj);
                    break;
            }

            $criteria .= '<!--cr'.$crt['id'].':'.$crt['type'].':'.$crt['value1'].'-->- '.$crtName;

            if ($crt['completionFlags'] & ACHIEVEMENT_CRITERIA_FLAG_MONEY_COUNTER)
                $criteria .= '&nbsp;<span class="moneygold">'.Lang::nf($crt['value2' ] / 10000).'</span>';

            $criteria .= '<br />';

            if (++$i == round(count($rows)/2))
                $criteria .= '</small></td><th class="q0" style="white-space: nowrap; text-align: left"><small>';
        }

        $x  = '<table><tr><td><b class="q">';
        $x .= $name;
        $x .= '</b></td></tr></table>';
        if ($description || $criteria)
            $x .= '<table><tr><td>';

        if ($description)
            $x .= '<br />'.$description.'<br />';

        if ($criteria)
        {
            $x .= '<br /><span class="q">'.Lang::achievement('criteria').':</span>';
            $x .= '<table width="100%"><tr><td class="q0" style="white-space: nowrap"><small>'.$criteria.'</small></th></tr></table>';
        }
        if ($description || $criteria)
            $x .= '</td></tr></table>';

        return $x;
    }

    public function getSourceData(int $id = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            if ($id && $id != $this->id)
                continue;

            $data[$this->id] = array(
                "n"  => $this->getField('name', true),
                "s"  => $this->curTpl['faction'],
                "t"  => Type::ACHIEVEMENT,
                "ti" => $this->id
            );
        }

        return $data;
    }
}


class AchievementListFilter extends Filter
{
    protected string $type  = 'achievements';
    protected static array $enums = array(
         4 => parent::ENUM_ZONE,                            // location
        11 => array(
              327 => 160,                                   // Lunar Festival
              423 => 187,                                   // Love is in the Air
              181 => 159,                                   // Noblegarden
              201 => 163,                                   // Children's Week
              341 => 161,                                   // Midsummer Fire Festival
              372 => 162,                                   // Brewfest
              324 => 158,                                   // Hallow's End
              404 => 14981,                                 // Pilgrim's Bounty
              141 => 156,                                   // Feast of Winter Veil
              409 => -3456,                                 // Day of the Dead
              398 => -3457,                                 // Pirates' Day
              parent::ENUM_ANY  => true,
              parent::ENUM_NONE => false,
              283 => -1,                                    // valid events without achievements
              285 => -1,   353 => -1,   420 => -1,
              400 => -1,   284 => -1,   374 => -1,
              321 => -1,   424 => -1,   301 => -1
        )
    );

    protected static array $genericFilter = array(
         2 => [parent::CR_BOOLEAN,   'reward_loc0', true                             ], // givesreward
         3 => [parent::CR_STRING,    'reward',      STR_LOCALIZED                    ], // rewardtext
         4 => [parent::CR_NYI_PH,    null,          1,                               ], // location [enum]
         5 => [parent::CR_CALLBACK,  'cbSeries',    ACHIEVEMENT_CU_FIRST_SERIES, null], // first in series [yn]
         6 => [parent::CR_CALLBACK,  'cbSeries',    ACHIEVEMENT_CU_LAST_SERIES,  null], // last in series [yn]
         7 => [parent::CR_BOOLEAN,   'chainId',                                      ], // partseries
         9 => [parent::CR_NUMERIC,   'id',          NUM_CAST_INT,                true], // id
        10 => [parent::CR_STRING,    'ic.name',                                      ], // icon
        11 => [parent::CR_CALLBACK,  'cbRelEvent', null,                         null], // related event [enum]
        14 => [parent::CR_FLAG,      'cuFlags',     CUSTOM_HAS_COMMENT               ], // hascomments
        15 => [parent::CR_FLAG,      'cuFlags',     CUSTOM_HAS_SCREENSHOT            ], // hasscreenshots
        16 => [parent::CR_FLAG,      'cuFlags',     CUSTOM_HAS_VIDEO                 ], // hasvideos
        18 => [parent::CR_STAFFFLAG, 'flags',                                        ]  // flags
    );

    protected static array $inputFields = array(
        'cr'    => [parent::V_RANGE, [2, 18],                                                             true ], // criteria ids
        'crs'   => [parent::V_LIST,  [parent::ENUM_NONE, parent::ENUM_ANY, [0, 99999]],                   true ], // criteria operators
        'crv'   => [parent::V_REGEX, parent::PATTERN_CRV,                                                 true ], // criteria values - only printable chars, no delimiters
        'na'    => [parent::V_REGEX, parent::PATTERN_NAME,                                                false], // name / description - only printable chars, no delimiter
        'ex'    => [parent::V_EQUAL, 'on',                                                                false], // extended name search
        'ma'    => [parent::V_EQUAL, 1,                                                                   false], // match any / all filter
        'si'    => [parent::V_LIST,  [SIDE_ALLIANCE, SIDE_HORDE, SIDE_BOTH, -SIDE_ALLIANCE, -SIDE_HORDE], false], // side
        'minpt' => [parent::V_RANGE, [1, 99],                                                             false], // required level min
        'maxpt' => [parent::V_RANGE, [1, 99],                                                             false]  // required level max
    );

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = &$this->values;

        // name ex: +description, +rewards
        if ($_v['na'])
        {
            $_ = [];
            if ($_v['ex'] == 'on')
                $_ = $this->tokenizeString(['name_loc'.Lang::getLocale()->value, 'reward_loc'.Lang::getLocale()->value, 'description_loc'.Lang::getLocale()->value]);
            else
                $_ = $this->tokenizeString(['name_loc'.Lang::getLocale()->value]);

            if ($_)
                $parts[] = $_;
        }

        // points min
        if ($_v['minpt'])
            $parts[] = ['points', $_v['minpt'],  '>='];

        // points max
        if ($_v['maxpt'])
            $parts[] = ['points', $_v['maxpt'],  '<='];

        // faction (side)
        if ($_v['si'])
        {
            $parts[] = match ($_v['si'])
            {
                -SIDE_ALLIANCE,                             // equals faction
                -SIDE_HORDE     => ['faction', -$_v['si']],
                 SIDE_ALLIANCE,                             // includes faction
                 SIDE_HORDE,
                 SIDE_BOTH      => ['faction', $_v['si'], '&']
            };
        }

        return $parts;
    }

    protected function cbRelEvent(int $cr, int $crs, string $crv) : ?array
    {
        if (!isset(self::$enums[$cr][$crs]))
            return null;

        $_ = self::$enums[$cr][$crs];
        if (is_int($_))
            return ($_ > 0) ? ['category', $_] : ['id', abs($_)];
        else
        {
            $ids = array_filter(self::$enums[$cr], fn($x) => is_int($x) && $x > 0);

            return ['category', $ids, $_ ? null : '!'];
        }

        return null;
    }

    protected function cbSeries(int $cr, int $crs, string $crv, int $seriesFlag) : ?array
    {
        if ($this->int2Bool($crs))
            return $crs ? ['AND', ['chainId', 0, '!'], ['cuFlags', $seriesFlag, '&']] : ['AND', ['chainId', 0, '!'], [['cuFlags', $seriesFlag, '&'], 0]];

        return null;
    }
}

?>
