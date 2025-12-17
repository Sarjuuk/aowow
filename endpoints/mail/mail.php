<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class MailBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'detail-page-generic';
    protected  string $pageName   = 'mail';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 103];

    public int $type   = Type::MAIL;
    public int $typeId = 0;

    private MailList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new MailList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('mail'), Lang::mail('notFound'));

        $this->extendGlobalData($this->subject->getJSGlobals());

        $this->h1 = Util::htmlEscape($this->subject->getField('name', true));

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->subject->getField('name', true)
        );


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->subject->getField('name', true), Util::ucFirst(Lang::game('mail')));


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // sender + delay
        if ($this->typeId < 0)                              // def. achievement
        {
            if ($npcId = DB::World()->selectCell('SELECT `Sender` FROM achievement_reward WHERE `ID` = ?d', -$this->typeId))
            {
                $infobox[] = Lang::mail('sender', ['[npc='.$npcId.']']);
                $this->extendGlobalIds(Type::NPC, $npcId);
            }
        }
        else if ($mlr = DB::World()->selectRow('SELECT * FROM mail_level_reward WHERE `mailTemplateId` = ?d', $this->typeId))  // level rewards
        {
            if ($mlr['level'])
                $infobox[] = Lang::game('level').Lang::main('colon').$mlr['level'];

            $jsg = [];
            if ($r = Lang::getRaceString($mlr['raceMask'], $jsg, Lang::FMT_MARKUP))
            {
                $this->extendGlobalIds(Type::CHR_RACE, ...$jsg);
                $t = count($jsg) == 1 ? Lang::game('race') : Lang::game('races');
                $infobox[] = Util::ucFirst($t).Lang::main('colon').$r;
            }

            $infobox[] = Lang::mail('sender', ['[npc='.$mlr['senderEntry'].']']);
            $this->extendGlobalIds(Type::NPC, $mlr['senderEntry']);
        }
        else                                                // achievement or quest
        {
            if ($q = DB::Aowow()->selectRow('SELECT `id`, `rewardMailDelay` FROM ?_quests WHERE `rewardMailTemplateId` = ?d', $this->typeId))
            {
                if ($npcId= DB::World()->selectCell('SELECT `RewardMailSenderEntry` FROM quest_mail_sender WHERE `QuestId` = ?d', $q['id']))
                {
                    $infobox[] = Lang::mail('sender', ['[npc='.$npcId.']']);
                    $this->extendGlobalIds(Type::NPC, $npcId);
                }
                else if ($npcId = DB::Aowow()->selectCell('SELECT `typeId` FROM ?_quests_startend WHERE `questId` = ?d AND `type` = ?d AND `method` & ?d', $q['id'], Type::NPC, 0x2))
                {
                    $infobox[] = Lang::mail('sender', ['[npc='.$npcId.']']);
                    $this->extendGlobalIds(Type::NPC, $npcId);
                }

                if ($q['rewardMailDelay'] > 0)
                    $infobox[] = Lang::mail('delay', [DateTime::formatTimeElapsed($q['rewardMailDelay'] * 1000)]);
            }
            else if ($npcId = DB::World()->selectCell('SELECT `Sender` FROM achievement_reward WHERE `MailTemplateId` = ?d', $this->typeId))
            {
                $infobox[] = Lang::mail('sender', ['[npc='.$npcId.']']);
                $this->extendGlobalIds(Type::NPC, $npcId);
            }
        }

        // id
        $infobox[] = Lang::mail('id') . $this->typeId;

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons = array(
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_WOWHEAD => false
        );

        $this->extraText = new Markup(Util::parseHtmlText($this->subject->getField('text', true), true), ['dbpage' => true, 'allow' => Markup::CLASS_ADMIN], 'text-generic');


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: attachment
        if ($itemId = $this->subject->getField('attachment'))
        {
            $attachment = new ItemList(array(['id', $itemId]));
            if (!$attachment->error)
            {
                $this->extendGlobalData($attachment->getJsGlobals());
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $attachment->getListviewData(),
                    'name' => Lang::mail('attachment'),
                    'id'   => 'attachment'
                ), ItemList::$brickFile));
            }
        }

        if ($this->typeId < 0 ||                            // used by: achievement
           ($acvId = DB::World()->selectCell('SELECT `ID` FROM achievement_reward WHERE `MailTemplateId` = ?d', $this->typeId)))
        {
            $ubAchievements = new AchievementList(array(['id', $this->typeId < 0 ? -$this->typeId : $acvId]));
            if (!$ubAchievements->error)
            {
                $this->extendGlobalData($ubAchievements->getJsGlobals());
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $ubAchievements->getListviewData(),
                    'id'   => 'used-by-achievement'
                ), AchievementList::$brickFile));
            }
        }
        else                                                // used by: quest
        {
            $ubQuests = new QuestList(array(['rewardMailTemplateId', $this->typeId]));
            if (!$ubQuests->error)
            {
                $this->extendGlobalData($ubQuests->getJsGlobals());
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $ubQuests->getListviewData(),
                    'id'   => 'used-by-quest'
                ), QuestList::$brickFile));
            }
        }

        parent::generate();
    }
}

?>
