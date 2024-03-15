<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*  Notes:
*       maybe for future use:
*           - wring something useful/displayable out of world.achievement_criteria_data
*           - display on firstkills if achieved yet or not
*
*       create bars with
*       g_createProgressBar(c)
*       var c = {
*           text: "",
*           hoverText: "",
*           color: "",      // cssClassName rep[0-7] | ach[0|1]
*           width: ,        // 0 <=> 100
*       }
*/

// menuId 9: Achievement g_initPath()
//  tabId 0: Database    g_initHeader()
class AchievementPage extends GenericPage
{
    use TrDetailPage;

    protected $mail          = [];
    protected $series        = null;
    protected $description   = '';
    protected $criteria      = [];
    protected $rewards       = [];

    protected $type          = Type::ACHIEVEMENT;
    protected $typeId        = 0;
    protected $tpl           = 'achievement';
    protected $path          = [0, 9];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    protected $_get          = ['domain' => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkDomain']];

    private   $powerTpl      = '$WowheadPower.registerAchievement(%d, %d, %s);';

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        // temp locale
        if ($this->mode == CACHE_TYPE_TOOLTIP && $this->_get['domain'])
            Util::powerUseLocale($this->_get['domain']);

        $this->typeId = intVal($id);

        $this->subject = new AchievementList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('achievement'), Lang::achievement('notFound'));

        $this->extendGlobalData($this->subject->getJSGlobals(GLOBALINFO_REWARDS));

        $this->name = $this->subject->getField('name', true);
    }

    protected function generatePath()
    {
        // create page title and path
        $curCat = $this->subject->getField('category');
        do
        {
            array_unshift($this->path, $curCat);
            $curCat = DB::Aowow()->SelectCell('SELECT parentCat FROM ?_achievementcategory WHERE id = ?d', $curCat);
        }
        while ($curCat > 0);

        array_unshift($this->path, 0, 9);
        $this->path[] = $this->subject->getField('typeCat');
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->subject->getField('name', true), Util::ucFirst(Lang::game('achievement')));
    }

    protected function generateContent()
    {
        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // points
        if ($_ = $this->subject->getField('points'))
            $infobox[] = Lang::achievement('points').Lang::main('colon').'[achievementpoints='.$_.']';

        // location
            // todo (low)

        // faction
        switch ($this->subject->getField('faction'))
        {
            case 1:
                $infobox[] = Lang::main('side').Lang::main('colon').'[span class=icon-alliance]'.Lang::game('si', SIDE_ALLIANCE).'[/span]';
                break;
            case 2:
                $infobox[] = Lang::main('side').Lang::main('colon').'[span class=icon-horde]'.Lang::game('si', SIDE_HORDE).'[/span]';
                break;
            default:                                        // case 3
                $infobox[] = Lang::main('side').Lang::main('colon').Lang::game('si', SIDE_BOTH);
        }

        // icon
        if ($_ = $this->subject->getField('iconId'))
        {
            $infobox[] = Util::ucFirst(lang::game('icon')).Lang::main('colon').'[icondb='.$_.' name=true]';
            $this->extendGlobalIds(Type::ICON, $_);
        }

        // realm first available?
        if ($this->subject->getField('flags') & 0x100 && DB::isConnectable(DB_AUTH))
        {
            $avlb = [];
            foreach (Profiler::getRealms() AS $rId => $rData)
                if (!DB::Characters($rId)->selectCell('SELECT 1 FROM character_achievement WHERE achievement = ?d LIMIT 1', $this->typeId))
                    $avlb[] = Util::ucWords($rData['name']);

            if ($avlb)
                $infobox[] = Lang::achievement('rfAvailable').implode(', ', $avlb);
        }

        /**********/
        /* Series */
        /**********/

        $series = [];

        if ($c = $this->subject->getField('chainId'))
        {
            $chainAcv = new AchievementList(array(['chainId', $c]));

            foreach ($chainAcv->iterate() as $aId => $__)
            {
                $pos = $chainAcv->getField('chainPos');
                if (!isset($series[$pos]))
                    $series[$pos] = [];

                $series[$pos][] = array(
                    'side'    => $chainAcv->getField('faction'),
                    'typeStr' => Type::getFileString(Type::ACHIEVEMENT),
                    'typeId'  => $aId,
                    'name'    => $chainAcv->getField('name', true)
                );
            }
        }

        /****************/
        /* Main Content */
        /****************/

        $this->headIcons   = [$this->subject->getField('iconString')];
        $this->infobox     = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null;
        $this->mail        = $this->createMail($reqBook);
        $this->series      = $series ? [[array_values($series), null]] : null;
        $this->description = $this->subject->getField('description', true);
        $this->redButtons  = array(
            BUTTON_WOWHEAD => !($this->subject->getField('cuFlags') & CUSTOM_SERVERSIDE),
            BUTTON_LINKS   => array(
                'linkColor' => 'ffffff00',
                'linkId'    => Type::getFileString(Type::ACHIEVEMENT).':'.$this->typeId.':&quot;..UnitGUID(&quot;player&quot;)..&quot;:0:0:0:0:0:0:0:0',
                'linkName'  => $this->name,
                'type'      => $this->type,
                'typeId'    => $this->typeId
            )
        );
        $this->criteria    = array(
            'reqQty' => $this->subject->getField('reqCriteriaCount'),
            'icons'  => [],
            'data'   => []
        );

        if ($reqBook)
            $this->addScript([SC_CSS_FILE, 'css/Book.css']);

        // create rewards
        if ($foo = $this->subject->getField('rewards'))
        {
            if ($itemRewards = array_filter($foo, function($x) { return $x[0] == Type::ITEM; }))
            {
                $bar = new ItemList(array(['i.id', array_column($itemRewards, 1)]));
                foreach ($bar->iterate() as $id => $__)
                {
                    $this->rewards['item'][] = array(
                        'name'      => $bar->getField('name', true),
                        'quality'   => $bar->getField('quality'),
                        'typeStr'   => Type::getFileString(Type::ITEM),
                        'id'        => $id,
                        'globalStr' => Type::getJSGlobalString(Type::ITEM)
                    );
                }
            }

            if ($titleRewards = array_filter($foo, function($x) { return $x[0] == Type::TITLE; }))
            {
                $bar = new TitleList(array(['id', array_column($titleRewards, 1)]));
                foreach ($bar->iterate() as $__)
                    $this->rewards['title'][] = sprintf(Lang::achievement('titleReward'), $bar->id, trim(str_replace('%s', '', $bar->getField('male', true))));
            }
        }

        $this->rewards['text'] = $this->subject->getField('reward', true);

        // factionchange-equivalent
        if ($pendant = DB::World()->selectCell('SELECT IF(horde_id = ?d, alliance_id, -horde_id) FROM player_factionchange_achievement WHERE alliance_id = ?d OR horde_id = ?d', $this->typeId, $this->typeId, $this->typeId))
        {
            $altAcv = new AchievementList(array(['id', abs($pendant)]));
            if (!$altAcv->error)
            {
                $this->transfer = sprintf(
                    Lang::achievement('_transfer'),
                    $altAcv->id,
                    1,                                      // quality
                    $altAcv->getField('iconString'),
                    $altAcv->getField('name', true),
                    $pendant > 0 ? 'alliance' : 'horde',
                    $pendant > 0 ? Lang::game('si', 1) : Lang::game('si', 2)
                );
            }
        }

        /**************/
        /* Extra Tabs */
        /**************/

        // tab: see also
        $conditions = array(
            ['name_loc'.User::$localeId, $this->subject->getField('name', true)],
            ['id', $this->typeId, '!']
        );
        $saList = new AchievementList($conditions);
        if (!$saList->error)
        {
            $this->extendGlobalData($saList->getJSGlobals());

            $this->lvTabs[] = [AchievementList::$brickFile, array(
                'data'        => array_values($saList->getListviewData()),
                'id'          => 'see-also',
                'name'        => '$LANG.tab_seealso',
                'visibleCols' => ['category']
            )];
        }

        // tab: criteria of
        $refs = DB::Aowow()->SelectCol('SELECT refAchievementId FROM ?_achievementcriteria WHERE Type = ?d AND value1 = ?d',
            ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_ACHIEVEMENT,
            $this->typeId
        );
        if (!empty($refs))
        {
            $coList = new AchievementList(array(['id', $refs]));
            $this->lvTabs[] = [AchievementList::$brickFile, array(
                'data'        => array_values($coList->getListviewData()),
                'id'          => 'criteria-of',
                'name'        => '$LANG.tab_criteriaof',
                'visibleCols' => ['category']
            )];
            $this->extendGlobalData($coList->getJSGlobals());
        }

        /*****************/
        /* Criteria List */
        /*****************/

        $iconId   = 1;
        $rightCol = [];
        $scripts  = [];

        // serverside extra-Data
        if ($crtIds = array_column($this->subject->getCriteria(), 'id'))
        {
            Util::checkNumeric($crtIds);
            $crtExtraData = DB::World()->select('SELECT criteria_id AS ARRAY_KEY, type AS ARRAY_KEY2, value1, value2, ScriptName FROM achievement_criteria_data WHERE criteria_id IN (?a)', $crtIds);
        }
        else
            $crtExtraData = [];

        foreach ($this->subject->getCriteria() as $i => $crt)
        {
            // hide hidden criteria for regular users (really do..?)
            // if (($crt['completionFlags'] & ACHIEVEMENT_CRITERIA_FLAG_HIDDEN) && User::$perms > 0)
                // continue;

            // alternative display option
            $displayMoney = $crt['completionFlags'] & ACHIEVEMENT_CRITERIA_FLAG_MONEY_COUNTER;
            $crtName      = Util::localizedString($crt, 'name');
            $tmp          = array(
                'id'   => $crt['id'],
                'name' => $crtName,
                'type' => $crt['type'],
            );

            $obj = (int)$crt['value1'];
            $qty = (int)$crt['value2'];

            switch ($crt['type'])
            {
                // link to npc
                case ACHIEVEMENT_CRITERIA_TYPE_KILL_CREATURE:
                case ACHIEVEMENT_CRITERIA_TYPE_KILLED_BY_CREATURE:
                    $tmp['link'] = array(
                        'href' => '?npc='.$obj,
                        'text' => $crtName,
                    );
                    $tmp['extraText'] = Lang::achievement('slain');
                    break;
                // link to area (by map)
                case ACHIEVEMENT_CRITERIA_TYPE_WIN_BG:
                case ACHIEVEMENT_CRITERIA_TYPE_WIN_ARENA:
                case ACHIEVEMENT_CRITERIA_TYPE_PLAY_ARENA:
                case ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_BATTLEGROUND:
                case ACHIEVEMENT_CRITERIA_TYPE_DEATH_AT_MAP:
                    if ($zoneId = DB::Aowow()->selectCell('SELECT id FROM ?_zones WHERE mapId = ? LIMIT 1', $obj))
                        $tmp['link'] = array(
                            'href' => '?zone='.$zoneId,
                            'text' => $crtName,
                        );
                    else
                        $tmp['extraText'] = $crtName;
                    break;
                // link to area
                case ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUESTS_IN_ZONE:
                case ACHIEVEMENT_CRITERIA_TYPE_HONORABLE_KILL_AT_AREA:
                    $tmp['link'] = array(
                        'href' => '?zone='.$obj,
                        'text' => $crtName,
                    );
                    break;
                // link to skills
                case ACHIEVEMENT_CRITERIA_TYPE_REACH_SKILL_LEVEL:
                case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SKILL_LEVEL:
                case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SKILLLINE_SPELLS:
                case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SKILL_LINE:
                    $tmp['link'] = array(
                        'href' => '?skill='.$obj,
                        'text' => $crtName,
                    );
                    break;
                // link to class
                case ACHIEVEMENT_CRITERIA_TYPE_HK_CLASS:
                    $tmp['link'] = array(
                        'href' => '?class='.$obj,
                        'text' => $crtName,
                    );
                    break;
                // link to race
                case ACHIEVEMENT_CRITERIA_TYPE_HK_RACE:
                    $tmp['link'] = array(
                        'href' => '?race='.$obj,
                        'text' => $crtName,
                    );
                    break;
                // link to title - todo (low): crosslink
                case ACHIEVEMENT_CRITERIA_TYPE_EARNED_PVP_TITLE:
                    $tmp['extraText'] = Util::ucFirst(Lang::game('title')).Lang::main('colon').$crtName;
                    break;
                // link to achivement (/w icon)
                case ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_ACHIEVEMENT:
                    $tmp['link'] = array(
                        'href' => '?achievement='.$obj,
                        'text' => $crtName,
                    );
                    $tmp['icon'] = $iconId;
                    $this->criteria['icons'][] = array(
                        'itr'  => $iconId++,
                        'type' => Type::getJSGlobalString(Type::ACHIEVEMENT),
                        'id'   => $obj,
                    );
                    $this->extendGlobalIds(Type::ACHIEVEMENT, $obj);
                    break;
                // link to quest
                case ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUEST:
                    // $crtName = ;
                    $tmp['link'] = array(
                        'href' => '?quest='.$obj,
                        'text' => $crtName ?: QuestList::getName($obj),
                    );
                    break;
                // link to spell (/w icon)
                case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET:
                case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2:
                case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL:
                case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL:
                case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2:
                    $tmp['link'] = array(
                        'href' => '?spell='.$obj,
                        'text' => ($crtName ?: SpellList::getName($obj))
                    );
                    $this->extendGlobalIds(Type::SPELL, $obj);
                    $tmp['icon'] = $iconId;
                    $this->criteria['icons'][] = array(
                        'itr'  => $iconId++,
                        'type' => Type::getJSGlobalString(Type::SPELL),
                        'id'   => $obj,
                    );
                    break;
                // link to item (/w icon)
                case ACHIEVEMENT_CRITERIA_TYPE_OWN_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_USE_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_LOOT_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_EQUIP_ITEM:
                    $crtItm = new ItemList(array(['i.id', $obj]));
                    $tmp['link'] = array(
                        'href'    => '?item='.$obj,
                        'text'    => ($crtName ?: $crtItm->getField('name', true)),
                        'quality' => $crtItm->getField('quality'),
                        'count'   => $qty,
                    );
                    $this->extendGlobalData($crtItm->getJSGlobals());
                    $tmp['icon'] = $iconId;
                    $this->criteria['icons'][] = array(
                        'itr'   => $iconId++,
                        'type'  => Type::getJSGlobalString(Type::ITEM),
                        'id'    => $obj,
                        'count' => $qty,
                    );
                    break;
                // link to faction (/w target reputation)
                case ACHIEVEMENT_CRITERIA_TYPE_GAIN_REPUTATION:
                    $tmp['link'] = array(
                        'href' => '?faction='.$obj,
                        'text' => $crtName ?: FactionList::getName($obj),
                    );
                    $tmp['extraText'] = ' ('.Lang::getReputationLevelForPoints($qty).')';
                    break;
                // link to GObject
                case ACHIEVEMENT_CRITERIA_TYPE_USE_GAMEOBJECT:
                case ACHIEVEMENT_CRITERIA_TYPE_FISH_IN_GAMEOBJECT:
                    $tmp['link'] = array(
                        'href' => '?object='.$obj,
                        'text' => $crtName,
                    );
                    break;
                // link to emote
                case ACHIEVEMENT_CRITERIA_TYPE_DO_EMOTE:
                    $tmp['link'] = array(
                        'href' => '?emote='.$obj,
                        'text' => $crtName,
                    );
                    break;
                default:
                    // Add a gold coin icon if required
                    $tmp['extraText'] = $displayMoney ? Util::formatMoney($qty) : $crtName;
                    break;
            }

            if (!empty($crtExtraData[$crt['id']]))
            {
                $tmp['extraData'] = [];
                foreach ($crtExtraData[$crt['id']] as $xType => $xData)
                {
                    // just pick stuff, that can actually be linked
                    switch ($xType)
                    {
                        case 1:                             // TYPE_T_CREATURE
                            $tmp['extraData'][] = ['?npc='.$xData['value1'], CreatureList::getName($xData['value1'])];
                            break;
                        case 2:                             // TYPE_T_PLAYER_CLASS_RACE
                        case 21:                            // TYPE_S_PLAYER_CLASS_RACE
                            if ($xData['value1'])
                                $tmp['extraData'][] = ['?class='.$xData['value1'], (new CharClassList(array(['id', $xData['value1']])))->getField('name', true)];

                            if ($xData['value2'])
                                $tmp['extraData'][] = ['?race='.$xData['value2'], (new CharRaceList(array(['id', $xData['value2']])))->getField('name', true)];

                            break;
                        // case 3:                          // TYPE_T_PLAYER_LESS_HEALTH
                        // case 4:                          // TYPE_T_PLAYER_DEAD
                        case 5:                             // TYPE_S_AURA
                        case 7:                             // TYPE_T_AURA
                            $tmp['extraData'][] = ['?spell='.$xData['value1'], SpellList::getName($xData['value1'])];
                            break;
                        case 6:                             // TYPE_S_AREA
                            $tmp['extraData'][] = ['?zone='.$xData['value1'], ZoneList::getName($xData['value1'])];
                            break;
                        // case 8:                          // TYPE_VALUE
                        // case 9:                          // TYPE_T_LEVEL
                        // case 10:                         // TYPE_T_GENDER
                        case 11:                            // TYPE_SCRIPT
                            if ($xData['ScriptName'])
                                $scripts[] = $xData['ScriptName'];

                            break;
                        // case 12:                         // TYPE_MAP_DIFFICULTY
                        // case 13:                         // TYPE_MAP_PLAYER_COUNT
                        // case 14:                         // TYPE_T_TEAM
                        // case 15:                         // TYPE_S_DRUNK
                        case 16:                            // TYPE_HOLIDAY
                            if ($we = new WorldEventList(array(['holidayId', $xData['value1']])))
                                $tmp['extraData'][] = ['?event='.$we->id, $we->getField('name', true)];

                            break;
                        // case 17:                         // TYPE_BG_LOSS_TEAM_SCORE
                        // case 18:                         // TYPE_INSTANCE_SCRIPT
                        // case 19:                         // TYPE_S_EQUIPED_ITEM
                        case 20:                            // TYPE_MAP_ID
                            if ($z = new ZoneList(array(['mapIdBak', $xData['value1']])))
                                $tmp['extraData'][] = ['?zone='.$z->id, $z->getField('name', true)];

                            break;
                        // case 22:                         // TYPE_NTH_BIRTHDAY
                        case 23:                            // TYPE_S_KNOWN_TITLE
                            $tmp['extraData'][] = ['?title='.$xData['value1'], trim(str_replace('%s', '', (new TitleList(array(['id', $xData['value1']])))->getField('male', true)))];
                            break;
                    }

                    // moar stuffz

                }
            }

            // If the right column
            if ($i % 2)
                $this->criteria['data'][] = $tmp;
            else
                $rightCol[] = $tmp;
        }

        // If you found the second column - merge data from it to the end of the main body
        if ($rightCol)
            $this->criteria['data'] = array_merge($this->criteria['data'], $rightCol);

        // criteria have scripts
        if (User::isInGroup(U_GROUP_EMPLOYEE) && $scripts)
        {
            $s = '[li]Script'.Lang::main('colon').'[ul][li]'.implode('[/li][li]', array_unique($scripts)).'[/li][/ul][/li]';
            $this->infobox = substr_replace($this->infobox, $s, -5, 0);
        }
    }

    protected function generateTooltip()
    {
        $power = new StdClass();
        if (!$this->subject->error)
        {
            $power->{'name_'.User::$localeString}    = $this->subject->getField('name', true);
            $power->icon                             = rawurlencode($this->subject->getField('iconString', true, true));
            $power->{'tooltip_'.User::$localeString} = $this->subject->renderTooltip();
        }

        return sprintf($this->powerTpl, $this->typeId, User::$localeId, Util::toJSON($power, JSON_AOWOW_POWER));
    }

    private function createMail(&$reqCss = false)
    {
        $mail = [];

        if ($_ = $this->subject->getField('mailTemplate'))
        {
            $letter = DB::Aowow()->selectRow('SELECT * FROM ?_mails WHERE id = ?d', $_);
            if (!$letter)
                return [];

            $reqCss = true;
            $mail   = array(
                'id'          => $_,
                'delay'       => null,
                'sender'      => null,
                'attachments' => [],
                'subject'     => Util::parseHtmlText(Util::localizedString($letter, 'subject', true)),
                'text'        => Util::parseHtmlText(Util::localizedString($letter, 'text', true))
            );
        }
        else if ($_ = Util::parseHtmlText($this->subject->getField('text', true, true)))
        {
            $reqCss = true;
            $mail   = array(
                'id'          => -$this->typeId,
                'delay'       => null,
                'sender'      => null,
                'attachments' => [],
                'subject'     => Util::parseHtmlText($this->subject->getField('subject', true, true)),
                'text'        => $_
            );
        }

        if ($_ = CreatureList::getName($this->subject->getField('sender')))
            $mail['sender'] = sprintf(Lang::mail('mailBy'), $this->subject->getField('sender'), $_);

        return $mail;
    }
}

?>
