<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*  Notes:
*       can create achievement progress bars with
*       g_createProgressBar(c)
*       var c = {
*           text: "",
*           hoverText: "",
*           color: "",      // cssClassName rep[0-7] | ach[0|1]
*           width: 0,       // 0 <=> 100
*       }
*/

class AchievementBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'achievement';
    protected  string $pageName   = 'achievement';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 9];

    public  int    $type        = Type::ACHIEVEMENT;
    public  int    $typeId      = 0;
    public  int    $reqCrtQty   = 0;
    public ?Mail   $mail        = null;
    public  string $description = '';
    public  array  $criteria    = [];
    public ?array  $rewards     = null;

    private Achievement $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new Achievement($this->typeId);
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('achievement'), Lang::achievement('notFound'));

        $this->extendGlobalData($this->subject->getJSGlobal(GLOBALINFO_REWARDS));

        $this->h1 = $this->subject->name;

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );


        /*************/
        /* Menu Path */
        /*************/

        // create page title and path
        $curCat  = $this->subject->category;
        $catPath = [];
        while ($curCat > 0)
        {
            $catPath[] = $curCat;
            $curCat = DB::Aowow()->SelectCell('SELECT `parentCat` FROM ::achievementcategory WHERE `id` = %i', $curCat);
        }

        $this->breadcrumb = array_merge($this->breadcrumb, array_reverse($catPath));


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->subject->name, Util::ucFirst(Lang::game('achievement')));


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->cuFlags);

        // points
        if (!$this->subject->isStatistic() && ($_ = $this->subject->points))
            $infobox[] = Lang::achievement('points').Lang::main('colon').'[achievementpoints='.$_.']';

        // location
            // todo (low)

        // faction
        $infobox[] = Lang::main('side') . match ($this->subject->faction)
        {
            SIDE_ALLIANCE => '[span class=icon-alliance]'.Lang::game('si', SIDE_ALLIANCE).'[/span]',
            SIDE_HORDE    => '[span class=icon-horde]'.Lang::game('si', SIDE_HORDE).'[/span]',
            default       => Lang::game('si', SIDE_BOTH)    // 0, 3
        };

        // id
        $infobox[] = Lang::achievement('id') . $this->typeId;

        // icon
        if ($_ = $this->subject->iconId)
        {
            $infobox[] = Util::ucFirst(Lang::game('icon')).Lang::main('colon').'[icondb='.$_.' name=true]';
            $this->extendGlobalIds(Type::ICON, $_);
        }

        // profiler relateed (note that this is part of the cache. I don't think this is important enough to calc for every view)
        if (Cfg::get('PROFILER_ENABLE') && !($this->subject->isStatistic()))
        {
            $x = DB::Aowow()->selectCell('SELECT COUNT(1) FROM ::profiler_completion_achievements WHERE `achievementId` = %i', $this->typeId);
            $y = DB::Aowow()->selectCell('SELECT COUNT(1) FROM ::profiler_profiles WHERE `custom` = 0 AND `stub` = 0');
            $infobox[] = Lang::profiler('attainedBy', [round(($x ?: 0) * 100 / ($y ?: 1))]);

            // completion row added by InfoboxMarkup
        }

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.($this->subject->name)(Locale::EN).'[/copy][/li]';

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0', !($this->subject->isStatistic()));


        /**********/
        /* Series */
        /**********/

        $series = [];
        if ($c = $this->subject->chainId)
        {
            $chainAcv = new AchievementSet(array(['chainId', $c]));

            foreach ($chainAcv->iterate() as $aId => $acv)
            {
                if (!isset($series[$acv->chainPos]))
                    $series[$acv->chainPos] = [];

                $series[$acv->chainPos][] = array(
                    'side'    => $acv->faction,
                    'typeStr' => Type::getFileString(Type::ACHIEVEMENT),
                    'typeId'  => $aId,
                    'name'    => $acv->name
                );
            }
        }

        if ($series)
            $this->series = [[array_values($series), null, false]];


        /****************/
        /* Main Content */
        /****************/

        $this->headIcons   = [$this->subject->icon];
        $this->description = $this->subject->description;
        $this->redButtons  = array(
            BUTTON_WOWHEAD => !($this->subject->cuFlags & CUSTOM_SERVERSIDE),
            BUTTON_LINKS   => array(
                'linkColor' => 'ffffff00',
                'linkId'    => Type::getFileString(Type::ACHIEVEMENT).':'.$this->typeId.':&quot;..UnitGUID(&quot;player&quot;)..&quot;:0:0:0:0:0:0:0:0',
                'linkName'  => $this->h1,
                'type'      => $this->type,
                'typeId'    => $this->typeId
            )
        );
        $this->reqCrtQty  = $this->subject->reqCriteriaCount;

        if ($this->createMail())
            $this->addScript([SC_CSS_FILE, 'css/Book.css']);

        // create rewards
        $rewItems = $rewTitles = [];
        if ($foo = $this->subject->getRewards())
        {
            if ($itemRewards = array_filter($foo, fn($x) => $x[0] == Type::ITEM))
            {
                $bar = new ItemList(array(['i.id', array_column($itemRewards, 1)]));
                foreach ($bar->iterate() as $id => $__)
                    $rewItems[] = new IconElement(Type::ITEM, $id, $bar->getField('name', true), quality: $bar->getField('quality'));
            }

            if ($titleRewards = array_filter($foo, fn($x) => $x[0] == Type::TITLE))
            {
                $bar = new TitleList(array(['id', array_column($titleRewards, 1)]));
                foreach ($bar->iterate() as $id => $__)
                    $rewTitles[] = Lang::achievement('titleReward', [$id, trim(str_replace('%s', '', $bar->getField('male', true)))]);
            }
        }

        if (($text = $this->subject->rewardText) || $rewItems || $rewTitles)
            $this->rewards = [$rewItems, $rewTitles, $text];

        // factionchange-equivalent
        if ($pendant = DB::World()->selectCell('SELECT IF(`horde_id` = %i, `alliance_id`, -`horde_id`) FROM player_factionchange_achievement WHERE `alliance_id` = %i OR `horde_id` = %i', $this->typeId, $this->typeId, $this->typeId))
        {
            $altAcv = new Achievement(abs($pendant));
            if (!$altAcv->error)
            {
                $this->transfer = Lang::achievement('_transfer', array(
                    $altAcv->id,
                    ITEM_QUALITY_NORMAL,
                    $altAcv->icon,
                    $altAcv->name,
                    $pendant > 0 ? 'alliance' : 'horde',
                    $pendant > 0 ? Lang::game('si', SIDE_ALLIANCE) : Lang::game('si', SIDE_HORDE)
                ));
            }
        }


        /*****************/
        /* Criteria List */
        /*****************/

        // serverside extra-Data (not sure why ACHIEVEMENT_CRITERIA_DATA_TYPE_NONE is set, let a lone a couple hundred times)
        if ($crtIds = array_column($this->subject->getCriteria(), 'id'))
            $crtExtraData = DB::World()->selectAssoc('SELECT `criteria_id` AS ARRAY_KEY, `type` AS ARRAY_KEY2, `value1`, `value2`, `ScriptName` FROM achievement_criteria_data WHERE `type` <> %i AND `criteria_id` IN %in', ACHIEVEMENT_CRITERIA_DATA_TYPE_NONE, $crtIds);
        else
            $crtExtraData = [];

        foreach ($this->subject->getCriteria() as $crt)
        {
            // hide hidden criteria for regular users (really do..?)
            // if (($crt['completionFlags'] & ACHIEVEMENT_CRITERIA_FLAG_HIDDEN) && !User::isInGroup(U_GROUP_STAFF))
                // continue;

            // alternative display option
            $crtName    = Util::localizedString($crt, 'name');
            $killSuffix = null;

            $obj = (int)$crt['value1'];
            $qty = (int)$crt['value2'];

            switch ($crt['type'])
            {
                // link to npc
                case ACHIEVEMENT_CRITERIA_TYPE_KILL_CREATURE:
                    $killSuffix = Lang::achievement('slain');
                case ACHIEVEMENT_CRITERIA_TYPE_KILLED_BY_CREATURE:
                    $crtIcon = new IconElement(Type::NPC, $obj, $crtName ?: Creature::getName($obj), size: IconElement::SIZE_SMALL, element: 'iconlist-icon', extraText: $crtName ? null : $killSuffix);
                    break;
                // link to area (by map)
                case ACHIEVEMENT_CRITERIA_TYPE_WIN_BG:
                case ACHIEVEMENT_CRITERIA_TYPE_WIN_ARENA:
                case ACHIEVEMENT_CRITERIA_TYPE_PLAY_ARENA:
                case ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_BATTLEGROUND:
                case ACHIEVEMENT_CRITERIA_TYPE_DEATH_AT_MAP:
                    $zoneId  = DB::Aowow()->selectCell('SELECT `id` FROM ::zones WHERE `mapId` = %s', $obj);
                    $crtIcon = new IconElement(Type::ZONE, $zoneId ?: 0, $crtName ?: ZoneList::getName($zoneId), size: IconElement::SIZE_SMALL, element: 'iconlist-icon');
                    break;
                // link to area
                case ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUESTS_IN_ZONE:
                case ACHIEVEMENT_CRITERIA_TYPE_HONORABLE_KILL_AT_AREA:
                    $crtIcon = new IconElement(Type::ZONE, $obj, $crtName ?: ZoneList::getName($obj), size: IconElement::SIZE_SMALL, element: 'iconlist-icon');
                    break;
                // link to skills
                case ACHIEVEMENT_CRITERIA_TYPE_REACH_SKILL_LEVEL:
                case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SKILL_LEVEL:
                case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SKILLLINE_SPELLS:
                case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SKILL_LINE:
                    $crtIcon = new IconElement(Type::SKILL, $obj, $crtName ?: SkillList::getName($obj), size: IconElement::SIZE_SMALL, element: 'iconlist-icon');
                    $this->extendGlobalIds(Type::SKILL, $obj);
                    break;
                // link to class
                case ACHIEVEMENT_CRITERIA_TYPE_HK_CLASS:
                    $crtIcon = new IconElement(Type::CHR_CLASS, $obj, $crtName ?: CharClass::getName($obj), size: IconElement::SIZE_SMALL, element: 'iconlist-icon');
                    break;
                // link to race
                case ACHIEVEMENT_CRITERIA_TYPE_HK_RACE:
                    $crtIcon = new IconElement(Type::CHR_RACE, $obj, $crtName ?: CharRace::getName($obj), size: IconElement::SIZE_SMALL, element: 'iconlist-icon');
                    break;
                // link to achivement
                case ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_ACHIEVEMENT:
                    $crtIcon = new IconElement(Type::ACHIEVEMENT, $obj, $crtName ?: Achievement::getName($obj), size: IconElement::SIZE_SMALL, element: 'iconlist-icon');
                    $this->extendGlobalIds(Type::ACHIEVEMENT, $obj);
                    break;
                // link to quest
                case ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUEST:
                    $crtIcon = new IconElement(Type::QUEST, $obj, $crtName ?: QuestList::getName($obj), size: IconElement::SIZE_SMALL, element: 'iconlist-icon');
                    break;
                // link to spell
                case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET:
                case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2:
                case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL:
                case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL:
                case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2:
                    $crtIcon = new IconElement(Type::SPELL, $obj, $crtName ?: SpellList::getName($obj), size: IconElement::SIZE_SMALL, element: 'iconlist-icon');
                    $this->extendGlobalIds(Type::SPELL, $obj);
                    break;
                // link to item
                case ACHIEVEMENT_CRITERIA_TYPE_OWN_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_USE_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_LOOT_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_EQUIP_ITEM:
                    $item = new ItemList([['id', $obj]]);
                    $crtIcon = new IconElement(Type::ITEM, $obj, $crtName ?: $item->getField('name', true), quality: $item->getField('quality'), size: IconElement::SIZE_SMALL, element: 'iconlist-icon');
                    $this->extendGlobalData($item->getJSGlobals());
                    break;
                // link to faction (/w target reputation)
                case ACHIEVEMENT_CRITERIA_TYPE_GAIN_REPUTATION:
                    $crtIcon = new IconElement(Type::FACTION, $obj, $crtName ?: FactionList::getName($obj), size: IconElement::SIZE_SMALL, element: 'iconlist-icon', extraText: '('.Lang::getReputationLevelForPoints($qty).')');
                    break;
                // link to GObject
                case ACHIEVEMENT_CRITERIA_TYPE_USE_GAMEOBJECT:
                case ACHIEVEMENT_CRITERIA_TYPE_FISH_IN_GAMEOBJECT:
                    $crtIcon = new IconElement(Type::OBJECT, $obj, $crtName ?: GameObjectList::getName($obj), size: IconElement::SIZE_SMALL, element: 'iconlist-icon');
                    break;
                // link to emote
                case ACHIEVEMENT_CRITERIA_TYPE_DO_EMOTE:
                    $crtIcon = new IconElement(Type::EMOTE, $obj, $crtName ?: EmoteList::getName($obj), size: IconElement::SIZE_SMALL, element: 'iconlist-icon');
                    break;
                default:
                    // Add a gold coin icon if required
                    if ($crt['completionFlags'] & ACHIEVEMENT_CRITERIA_FLAG_MONEY_COUNTER )
                        $crtIcon = new IconElement(0, 0, '', extraText: Util::formatMoney($qty));
                    else
                        $crtIcon = new IconElement(0, 0, $crtName);
                    break;
            }

            if (User::isInGroup(U_GROUP_STAFF))
                $crtIcon->extraText .= ' [CriteriaId: '.$crt['id'].']';

            $extraData = [];
            foreach ($crtExtraData[$crt['id']] ?? [] as $xType => $xData)
            {
                switch ($xType)
                {
                    case ACHIEVEMENT_CRITERIA_DATA_TYPE_T_CREATURE:
                        $extraData[] = Creature::makeLink($xData['value1']);
                        break;
                    case ACHIEVEMENT_CRITERIA_DATA_TYPE_T_PLAYER_CLASS_RACE:
                    case ACHIEVEMENT_CRITERIA_DATA_TYPE_S_PLAYER_CLASS_RACE:
                        if ($xData['value1'])
                            $extraData[] = CharClass::makeLink($xData['value1']);

                        if ($xData['value2'])
                            $extraData[] = CharRace::makeLink($xData['value2']);

                        break;
                    case ACHIEVEMENT_CRITERIA_DATA_TYPE_S_AURA:
                    case ACHIEVEMENT_CRITERIA_DATA_TYPE_T_AURA:
                        $extraData[] = SpellList::makeLink($xData['value1']);
                        break;
                    case ACHIEVEMENT_CRITERIA_DATA_TYPE_S_AREA:
                        $extraData[] = ZoneList::makeLink($xData['value1']);
                        break;
                    case ACHIEVEMENT_CRITERIA_DATA_TYPE_SCRIPT:
                        if ($xData['ScriptName'] && User::isInGroup(U_GROUP_STAFF))
                            $extraData[] = 'Script '.$xData['ScriptName'];
                        break;
                    case ACHIEVEMENT_CRITERIA_DATA_TYPE_HOLIDAY:
                        if ($we = new WorldEventList(array(['holidayId', $xData['value1']])))
                            $extraData[] = '<a href="?event='.$we->id.'">'.$we->getField('name', true).'</a>';
                        break;
                    case ACHIEVEMENT_CRITERIA_DATA_TYPE_MAP_ID:
                        $extraData[] = match((int)$xData['value1'])
                        {
                            0       => Lang::maps('EasternKingdoms'),
                            1       => Lang::maps('Kalimdor'),
                            530     => Lang::maps('Outland'),
                            571     => Lang::maps('Northrend'),
                            default => (function(int $mapId) {
                                $z = new ZoneList(array(['mapId', $mapId]));
                                return '<a href="?zone='.$z->id.'">'.$z->getField('name', true).'</a>';
                            })($xData['value1'])
                        };
                        break;
                    case ACHIEVEMENT_CRITERIA_DATA_TYPE_S_KNOWN_TITLE:
                        $extraData[] = TitleList::makeLink($xData['value1']);
                        break;
                    default:
                        if (User::isInGroup(U_GROUP_STAFF))
                            $extraData[] = 'has extra criteria data';
                }
            }

            if ($extraData)
                $crtIcon->extraText .= ' <br /><sup style="margin-left:8px;">('.implode(', ', $extraData).')</sup>';

            $this->criteria[] = $crtIcon;
        }


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: see also
        $conditions = array(
            ['name_loc'.Lang::getLocale()->value, (string)$this->subject->name],
            ['id', $this->typeId, '!']
        );
        $saList = new AchievementSet($conditions);
        if (!$saList->error)
        {
            $this->extendGlobalData($saList->getJSGlobals());

            $this->lvTabs->addListviewTab(new Listview(array(
                'data'        => $saList->getListviewData(),
                'id'          => 'see-also',
                'name'        => '$LANG.tab_seealso',
                'visibleCols' => ['category']
            ), Achievement::$brickFile));
        }

        // tab: criteria of
        $refs = DB::Aowow()->SelectCol('SELECT `refAchievementId` FROM ::achievementcriteria WHERE `type` = %i AND `value1` = %i',
            ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_ACHIEVEMENT,
            $this->typeId
        );

        if (!empty($refs))
        {
            $coList = new AchievementSet(array(['id', $refs]));
            if (!$coList->error)
            {
                $this->extendGlobalData($coList->getJSGlobals());

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'        => $coList->getListviewData(),
                    'id'          => 'criteria-of',
                    'name'        => '$LANG.tab_criteriaof',
                    'visibleCols' => ['category']
                ), Achievement::$brickFile));
            }
        }

        // tab: condition for
        $cnd = new Conditions();
        $cnd->getByCondition(Type::ACHIEVEMENT, $this->typeId)->prepare();
        if ($tab = $cnd->toListviewTab('condition-for', '$LANG.tab_condition_for'))
        {
            $this->extendGlobalData($cnd->getJSGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }

        parent::generate();

        if ($this->subject->isRealmFirst())
            $this->result->registerDisplayHook('infobox', [self::class, 'infoboxHook']);
    }

    private function createMail() : bool
    {
        $rewardMailData = DB::World()->selectRow(
           'SELECT    ar.`Sender`, ar.`MailTemplateID`,
                      ar.`Subject` AS "subject_loc0", IFNULL(arl2.`Subject`, "") AS "subject_loc2", IFNULL(arl3.`Subject`, "") AS "subject_loc3", IFNULL(arl4.`Subject`, "") AS "subject_loc4", IFNULL(arl6.`Subject`, "") AS "subject_loc6", IFNULL(arl8.`Subject`, "") AS "subject_loc8",
                      ar.`Body`    AS "text_loc0",    IFNULL(arl2.`Body`,    "") AS "text_loc2",    IFNULL(arl3.`Body`,    "") AS "text_loc3",    IFNULL(arl4.`Body`,    "") AS "text_loc4",    IFNULL(arl6.`Body`,    "") AS "text_loc6",    IFNULL(arl8.`Body`,    "") AS "text_loc8"
            FROM      achievement_reward ar
            LEFT JOIN achievement_reward_locale arl2 ON arl2.`ID` = ar.`ID` AND arl2.`Locale` = "frFR"
            LEFT JOIN achievement_reward_locale arl3 ON arl3.`ID` = ar.`ID` AND arl3.`Locale` = "deDE"
            LEFT JOIN achievement_reward_locale arl4 ON arl4.`ID` = ar.`ID` AND arl4.`Locale` = "zhCN"
            LEFT JOIN achievement_reward_locale arl6 ON arl6.`ID` = ar.`ID` AND arl6.`Locale` = "esES"
            LEFT JOIN achievement_reward_locale arl8 ON arl8.`ID` = ar.`ID` AND arl8.`Locale` = "ruRU"
            WHERE     ar.`ID` = %i',
            $this->typeId
        );

        if (!$rewardMailData || !$rewardMailData['Sender'])
            return false;

        $letter = $rewardMailData;
        if ($rewardMailData['MailTemplateID'])
            if (!($letter = DB::Aowow()->selectRow('SELECT * FROM ::mails WHERE `id` = %i', $rewardMailData['MailTemplateID'])))
                return false;

        $this->mail = new Mail(
            $rewardMailData['MailTemplateID'] ?: -$this->typeId,
            new LocString($letter, 'subject', UIText::formatHtml(...)),
            new LocString($letter, 'text',    UIText::formatHtml(...)),
            $rewardMailData['Sender']
        );

        /*
            we don't display mail attachements as:
            a) items from world.achievement_reward would have to be added here in a roundabout way
            b) displaying items from a) and mail loot would be redundant with the rewards paragraph
        */

        return true;
    }

    /* finalize infobox */
    public static function infoboxHook(Template\PageTemplate &$pt, ?InfoboxMarkup &$markup) : void
    {
        // realm first still available?
        if (!DB::isConnectable(DB_AUTH))
            return;

        $avlb = [];
        foreach (Profiler::getRealms() AS $rId => $rData)
            if (!DB::Characters($rId)->selectCell('SELECT 1 FROM character_achievement WHERE `achievement` = %i', $pt->typeId))
                $avlb[] = Util::ucWords($rData['name']);

        if (!$avlb)
            return;

        $addRow = Lang::achievement('rfAvailable').implode(', ', $avlb);

        if (!$markup)
            $markup = new InfoboxMarkup([$addRow], ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');
        else
            $markup->addItem($addRow);
    }
}

?>
