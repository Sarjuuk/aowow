<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AchievementEntry extends DBTypeEntry implements ISource, ITooltip
{
    use TrSourceHelper;

    public readonly int       $cuFlags;
    public readonly LocString $name;
    public readonly LocString $description;
    public readonly LocString $rewardText;
    public readonly int       $points;
    public readonly int       $category;
    public readonly int       $parentCat;
    public readonly int       $iconId;
    public readonly string    $icon;
    public readonly int       $faction;
    public readonly int       $refAchievement;
    public readonly int       $flags;
    public readonly int       $chainId;
    public readonly int       $chainPos;
    public readonly int       $reqCriteriaCount;
    public readonly int       $itemExtra;

    public private(set) ?array $criteria = null {
        get => $this->criteria ??= self::fetchCriteria($this->refAchievement ?: $this->id); // should only ever be needed for single instances (detail page or tooltip)
    }

    public private(set) ?array $rewards = null {
        get => $this->rewards ?? ($this->setRewards(current(self::fetchRewards($this->id)) ?: []) ?? $this->rewards);
    }

    public static int    $dbType    = Type::ACHIEVEMENT;
    public static string $brickFile = 'achievement';
    public static string $dataTable = '::achievement';

    public const string QUERY_BASE = 'SELECT a.*, a.`id` AS ARRAY_KEY FROM ::achievement a';
    public const array  QUERY_OPTS = array(
        'a'  => [['ic'], 'o' => '`orderInGroup` ASC'],
        'ic' => ['j' => ['::icons ic ON ic.`id` = a.`iconId`', true], 's' => ', ic.`name` AS "icon"'],
        'ac' => ['j' => ['::achievementcriteria ac ON ac.`refAchievementId` = a.`id`', true], 'g' => 'a.`id`']
    );

    public function applyInitData(array $initData, array $opts) : void
    {
        parent::applyInitData($initData, $opts);

        $this->cuFlags          = $initData['cuFlags'];
        $this->name             = new LocString($initData, 'name');
        $this->description      = new LocString($initData, 'description');
        $this->rewardText       = new LocString($initData, 'reward');
        $this->points           = $initData['points'];
        $this->category         = $initData['category'];
        $this->parentCat        = $initData['parentCat'];
        $this->iconId           = $initData['iconId'];
        $this->icon             = $initData['icon'] ?: 'trade_engineering';
        $this->faction          = $initData['faction'];
        $this->refAchievement   = $initData['refAchievement'];
        $this->flags            = $initData['flags'];
        $this->chainId          = $initData['chainId'];
        $this->chainPos         = $initData['chainPos'];
        $this->reqCriteriaCount = $initData['reqCriteriaCount'];
        $this->itemExtra        = $initData['itemExtra'] ?? 0;
    }

    /**
     * @param int $addInfoMask
     * * `0x0080 - LISTVIEWINFO_DATASET`: included icon (otherwise fetched from jsGlobals)
     */
    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        $data = array(
            'id'          => $this->id,
            'name'        => $this->name,                   // name
            'description' => $this->description,            // description
            'points'      => $this->points,                 // points
            'side'        => $this->faction,                // faction
            'category'    => $this->category,               // category
            'parentcat'   => $this->parentCat               // parentCat
        );

        if ($addInfoMask & LISTVIEWINFO_DATASET)
            $data['icon'] = $this->icon;

        // going out on a limb here: type = 1 if in level 3 of statistics tree, so, IF (statistic AND parentCat AND NOT statistic (1)) i guess
        if ($this->isStatistic() && $this->parentCat != 1)
            $data['type'] = 1;

        if ($this->rewards)
            $data['rewards'] = $this->rewards;              // rewards data
        else if (!$this->rewardText->isEmpty())
            $data['reward'] = $this->rewardText;            // rewards text display

        return $data;
    }

    /**
     * @param int $addMask mask of data to be exposed to javascript
     *  - always add achievement to @return
     *  - GLOBALINFO_REWARDS - add rewarded items and titles to @return
     *
     * @return array<int, array<int, int|array>[]>[] [type => [typeId => placeholder or data]]
     */
    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
    {
        $data[self::$dbType][$this->id] = ['icon' => $this->icon, 'name' => $this->name];

        if ($addMask & GLOBALINFO_REWARDS)
            foreach ($this->rewards as [$type, $typeId])
                $data[$type][$typeId] = $typeId;

        return $data;
    }

    public function getSourceData() : array
    {
        return array(
            'n'  => $this->name,
            's'  => $this->faction,
            't'  => self::$dbType,
            'ti' => $this->id
        );
    }

    public function renderTooltip() : ?string
    {
        $col1 =
        $col2 = [];

        foreach ($this->criteria as $i => $row)
            ${($i % 2) ? 'col2' : ' col1'}[] = $row;

        $rows = array_merge($col1, $col2);

        $criteria = '';

        $i = 0;
        foreach ($rows as $crt)
        {
            $type = (int)$crt['type'];
            $obj  = (int)$crt['value1'];
            $qty  = (int)$crt['value2'];

            // we could show them, but the tooltips are cluttered
            if (($crt['completionFlags'] & ACHIEVEMENT_CRITERIA_FLAG_HIDDEN) && !User::isInGroup(U_GROUP_STAFF))
                continue;

            $crtName = Util::localizedString($crt, 'name') ?: match ($type)
            {
                // link to spell (/w icon)
                ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET,
                ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2,
                ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL,
                ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL,
                ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2     => SpellEntry::getName($obj),
                // link to item (/w icon)
                ACHIEVEMENT_CRITERIA_TYPE_OWN_ITEM,
                ACHIEVEMENT_CRITERIA_TYPE_USE_ITEM,
                ACHIEVEMENT_CRITERIA_TYPE_LOOT_ITEM,
                ACHIEVEMENT_CRITERIA_TYPE_EQUIP_ITEM      => ItemEntry::getName($obj),
                // link to faction (/w target reputation)
                ACHIEVEMENT_CRITERIA_TYPE_GAIN_REPUTATION => FactionEntry::getName($obj),
                // link to quest
                ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUEST  => QuestEntry::getName($obj),
                default                                   => 'UNK Criteria Type'
            };

            $criteria .= '<!--cr'.$crt['id'].':'.$type.':'.$obj.'-->- '.$crtName;

            if ($crt['completionFlags'] & ACHIEVEMENT_CRITERIA_FLAG_MONEY_COUNTER)
                $criteria .= '&nbsp;<span class="moneygold">'.Lang::nf($qty * MONEY_GOLD).'</span>';

            $criteria .= '<br />';

            if (++$i == round(count($rows) / 2))
                $criteria .= '</small></td><th class="q0" style="white-space: nowrap; text-align: left"><small>';
        }

        $tooltip = '<table><tr><td><b class="q">'.$this->name.'</b></td></tr></table>';
        if ($this->description || $criteria)
            $tooltip .= '<table><tr><td>';

        if ($this->description)
            $tooltip .= '<br />'.$this->description.'<br />';

        if ($criteria)
        {
            $tooltip .= '<br /><span class="q">'.Lang::achievement('criteria').Lang::main('colon').'</span>';
            $tooltip .= '<table width="100%"><tr><td class="q0" style="white-space: nowrap"><small>'.$criteria.'</small></th></tr></table>';
        }
        if ($this->description || $criteria)
            $tooltip .= '</td></tr></table>';

        return $tooltip;
    }

    public function setRewards(array $rewards) : void
    {
        if ($this->itemExtra)
            $rewards[Type::ITEM][] = $this->itemExtra;

        $this->rewards = $rewards;
    }

    public function isStatistic() : bool
    {
        return $this->flags & ACHIEVEMENT_FLAG_COUNTER;
    }

    public function isRealmFirst() : bool
    {
        return $this->flags & ACHIEVEMENT_FLAG_REALM_FIRST;
    }

    public static function fetchCriteria(int $id) : array
    {
        if ($id)
            return DB::Aowow()->selectAssoc('SELECT ac.* FROM ::achievementcriteria ac WHERE ac.`refAchievementId` = %i ORDER BY ac.`order` ASC', $id) ?: [];
        return [];
    }

    public static function fetchRewards(int ...$ids) : array
    {
        if (!$ids)
            return [];

        // this assumes no ref-loot in mail loot (which is true for a clean TDB)
        // also, the js does not expect an item amount so that's skipped
        $result = DB::World()->selectAssoc(
           'SELECT    ar.`ID` AS ARRAY_KEY, ar.`TitleA`, ar.`TitleH`, ar.`ItemID`, GROUP_CONCAT(mlt.`Item`) AS "mailItems"
            FROM      achievement_reward ar
            LEFT JOIN mail_loot_template mlt ON mlt.`entry` = ar.`MailTemplateID` AND mlt.`Reference` = 0
            WHERE     ar.`ID` IN %in
            GROUP BY  ar.`ID`',
            $ids
        ) ?: [];

        $rewards = [];
        foreach ($result as $aId => $rewardData)
        {
            if ($rewardData['ItemID'])
                $rewards[$aId][] = [Type::ITEM, $rewardData['ItemID']];
            if ($rewardData['TitleA'])
                $rewards[$aId][] = [Type::TITLE, $rewardData['TitleA']];
            if ($rewardData['TitleH'] && $rewardData['TitleA'] != $rewardData['TitleH'])
                $rewards[$aId][] = [Type::TITLE, $rewardData['TitleH']];
            if ($rewardData['mailItems'])
                foreach (explode(',', $rewardData['mailItems']) as $i => $item)
                    $rewards[$aId][] = [Type::ITEM, $item];
        }

        return $rewards;
    }

    public static function getSourceMore(int ...$ids) : array
    {
        $data = DB::Aowow()->SelectAssoc('SELECT `id` AS ARRAY_KEY, `faction`, `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8` FROM %n WHERE `id` IN %in', static::$dataTable, $ids) ?: [];

        foreach ($data as $i => $d)
            $sm[$i] = array(
                'n'  => new LocString($d),
                's'  => $d['faction'],
                't'  => Type::ACHIEVEMENT,
                'ti' => $i
            );

        return $sm ?? [];
    }
}

?>
