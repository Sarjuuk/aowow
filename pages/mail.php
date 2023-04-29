<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 103: Mail     g_initPath()
//  tabId   0: Database g_initHeader()
class MailPage extends GenericPage
{
    use TrDetailPage;

    protected $type          = Type::MAIL;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 103];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        $this->typeId = intVal($id);

        $this->subject = new MailList(array(['id', $this->typeId]));

        if ($this->subject->error)
            $this->notFound(lang::game('mail'), Lang::mail('notFound'));

        $this->extendGlobalData($this->subject->getJSGlobals());

        $this->name = Util::htmlEscape(Util::ucFirst($this->subject->getField('name', true)));
    }

    protected function generateContent()
    {
        /***********/
        /* Infobox */
        /***********/

        $infobox = [];

        // sender + delay
        if ($this->typeId < 0)                              // def. achievement
        {
            if ($npcId = DB::World()->selectCell('SELECT Sender FROM achievement_reward WHERE ID = ?d', -$this->typeId))
            {
                $infobox[] = Lang::mail('sender').Lang::main('colon').'[npc='.$npcId.']';
                $this->extendGlobalIds(Type::NPC, $npcId);
            }
        }
        else if ($mlr = DB::World()->selectRow('SELECT * FROM mail_level_reward WHERE mailTemplateId = ?d', $this->typeId))  // level rewards
        {
                if ($mlr['level'])
                    $infobox[] = Lang::game('level').Lang::main('colon').$mlr['level'];

                $rIds = [];
                if ($r = Lang::getRaceString($mlr['raceMask'], $rIds, Lang::FMT_MARKUP))
                {
                    $infobox[] = Lang::game('races').Lang::main('colon').$r;
                    $this->extendGlobalIds(Type::CHR_RACE, ...$rIds);
                }

                $infobox[] = Lang::mail('sender').Lang::main('colon').'[npc='.$mlr['senderEntry'].']';
                $this->extendGlobalIds(Type::NPC, $mlr['senderEntry']);
        }
        else                                                // achievement or quest
        {
            if ($q = DB::Aowow()->selectRow('SELECT id, rewardMailDelay FROM ?_quests WHERE rewardMailTemplateId = ?d', $this->typeId))
            {
                if ($npcId= DB::World()->selectCell('SELECT RewardMailSenderEntry FROM quest_mail_sender WHERE QuestId = ?d', $q['id']))
                {
                    $infobox[] = Lang::mail('sender').Lang::main('colon').'[npc='.$npcId.']';
                    $this->extendGlobalIds(Type::NPC, $npcId);
                }
                else if ($npcId = DB::Aowow()->selectCell('SELECT typeId FROM ?_quests_startend WHERE questId = ?d AND type = ?d AND method & ?d', $q['id'], Type::NPC, 0x2))
                {
                    $infobox[] = Lang::mail('sender').Lang::main('colon').'[npc='.$npcId.']';
                    $this->extendGlobalIds(Type::NPC, $npcId);
                }

                if ($q['rewardMailDelay'] > 0)
                    $infobox[] = Lang::mail('delay').Lang::main('colon').''.Util::formatTime($q['rewardMailDelay'] * 1000);
            }
            else if ($npcId = DB::World()->selectCell('SELECT Sender FROM achievement_reward WHERE MailTemplateId = ?d', $this->typeId))
            {
                $infobox[] = Lang::mail('sender').Lang::main('colon').'[npc='.$npcId.']';
                $this->extendGlobalIds(Type::NPC, $npcId);
            }
        }

        /****************/
        /* Main Content */
        /****************/

        $this->infobox    = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : '';
        $this->redButtons = array(
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_WOWHEAD => false
        );

        $this->extraText = Util::parseHtmlText($this->subject->getField('text', true), true);


        /**************/
        /* Extra Tabs */
        /**************/

        // tab: attachment
        if ($itemId = $this->subject->getField('attachment'))
        {
            $attachment = new ItemList(array(['id', $itemId]));
            if (!$attachment->error)
            {
                $this->extendGlobalData($attachment->getJsGlobals());
                $this->lvTabs[] = [ItemList::$brickFile, array(
                    'data' => array_values($attachment->getListviewData()),
                    'name' => Lang::mail('attachment'),
                    'id'   => 'attachment'
                )];
            }
        }


        if ($this->typeId < 0 ||                            // used by: achievement
           ($acvId = DB::World()->selectCell('SELECT ID FROM achievement_reward WHERE MailTemplateId = ?d', $this->typeId)))
        {
            $ubAchievements = new AchievementList(array(['id', $this->typeId < 0 ? -$this->typeId : $acvId]));
            if (!$ubAchievements->error)
            {
                $this->extendGlobalData($ubAchievements->getJsGlobals());
                $this->lvTabs[] = [AchievementList::$brickFile, array(
                    'data' => array_values($ubAchievements->getListviewData()),
                    'id'   => 'used-by-achievement'
                )];
            }
        }
        else if ($npcId = DB::World()->selectCell('SELECT ID FROM achievement_reward WHERE MailTemplateId = ?d', $this->typeId))
            {
                $infobox[] = '[Sender]: [npc='.$npcId.']';
                $this->extendGlobalIds(Type::NPC, $npcId);
            }

        else                                                // used by: quest
        {
            $ubQuests = new QuestList(array(['rewardMailTemplateId', $this->typeId]));
            if (!$ubQuests->error)
            {
                $this->extendGlobalData($ubQuests->getJsGlobals());
                $this->lvTabs[] = [QuestList::$brickFile, array(
                    'data' => array_values($ubQuests->getListviewData()),
                    'id'   => 'used-by-quest'
                )];
            }
        }
    }

    protected function generateTitle()
    {
        array_unshift($this->title, Util::ucFirst($this->subject->getField('name', true)), Util::ucFirst(Lang::game('mail')));
    }

    protected function generatePath() { }
}

?>
