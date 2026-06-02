<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EmoteBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'detail-page-generic';
    protected  string $pageName   = 'emote';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 100];

    public int $type   = Type::EMOTE;
    public int $typeId = 0;

    private EmoteEntry $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        /*
         * id > 0: player text emote
         * id < 0: creature emote
        */

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new EmoteEntry($this->typeId);
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('emote'), Lang::emote('notFound'));

        $this->h1 = Util::ucFirst($this->subject->cmd);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucFirst(Lang::game('emote')));


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->cuFlags);

        // has Animation
        if ($this->subject->isAnimated && !$this->subject->stateParam)
        {
            $infobox[] = Lang::emote('isAnimated');

            // anim state
            $state = Lang::emote('state', $this->subject->state);
            if ($this->subject->state == 1)
                $state .= Lang::main('colon').Lang::unit('bytes1', 0, $this->subject->stateParam);
            $infobox[] = $state;
        }

        if (User::isInGroup(U_GROUP_STAFF | U_GROUP_TESTER))
        {
            // player emote: point to internal data
            if ($_ = $this->subject->parentEmote)
            {
                $this->extendGlobalIds(Type::EMOTE, $_);
                $infobox[] = '[emote='.$_.']';
            }

            if ($flags = $this->subject->flags)
            {
                $box = Lang::game('flags').Lang::main('colon').'[ul]';
                foreach (Lang::emote('flags') as $bit => $str)
                    if ($bit & $flags)
                        $box .= '[li][tooltip name=hint-'.$bit.']'.Util::asHex($bit).'[/tooltip][span class=tip tooltip=hint-'.$bit.']'.$str.'[/span][/li]';
                $infobox[] = $box.'[/ul]';
            }
        }

        // id
        $infobox[] = Lang::emote('id') . $this->typeId;

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');


        /****************/
        /* Main Content */
        /****************/

        $text = '';

        if ($this->subject->cuFlags & EMOTE_CU_MISSING_CMD)
            $text .= Lang::emote('noCommand').'[br][br]';
        else if ($aliasses = DB::Aowow()->selectCol('SELECT `command` FROM ::emotes_aliasses WHERE `id` = %i AND `locales` & %i', $this->typeId, 1 << Lang::getLocale()->value))
        {
            $text .= '[h3]'.Lang::emote('aliases').'[/h3][ul]';
            foreach ($aliasses as $a)
                $text .= '[li]/'.$a.'[/li]';

            $text .= '[/ul][br][br]';
        }

        $target = $noTarget = [];
        if (!$this->subject->extToExt->isEmpty())
            $target[] = $this->prepare('extToExt');
        if (!$this->subject->extToMe->isEmpty())
            $target[] = $this->prepare('extToMe');
        if (!$this->subject->meToExt->isEmpty())
            $target[] = $this->prepare('meToExt');
        if (!$this->subject->extToNone->isEmpty())
            $noTarget[] = $this->prepare('extToNone');
        if (!$this->subject->meToNone->isEmpty())
            $noTarget[] = $this->prepare('meToNone');

        if (!$target && !$noTarget)
            $text .= '[div][i class=q0]'.Lang::emote('noText').'[/i][/div]';

        if ($target)
        {
            $text .= '[pad][b]'.Lang::emote('targeted').'[/b][ul]';
            foreach ($target as $t)
                $text .= '[li][span class=s4]'.$t.'[/span][/li]';
            $text .= '[/ul]';
        }

        if ($noTarget)
        {
            $text .= '[pad][b]'.Lang::emote('untargeted').'[/b][ul]';
            foreach ($noTarget as $t)
                $text .= '[li][span class=s4]'.$t.'[/span][/li]';
            $text .= '[/ul]';
        }

        // event sound
        if ($_ = $this->subject->soundId)
        {
            $this->extendGlobalIds(Type::SOUND, $_);
            $text .= '[h3]'.Lang::emote('eventSound').'[/h3][sound='.$_.']';
        }

        if ($text)
            $this->extraText = new Markup($text, ['dbpage' => true, 'allow' => Markup::CLASS_ADMIN], 'text-generic');

        $this->redButtons = array(
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_WOWHEAD => false
        );


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: achievement
        $condition = array(
            ['ac.type', ACHIEVEMENT_CRITERIA_TYPE_DO_EMOTE],
            ['ac.value1', $this->typeId],
        );
        $acv = new AchievementContainer($condition);
        if (!$acv->error)
        {
            $this->extendGlobalData($acv->getJSGlobals());
            $this->lvTabs->addListviewTab(new Listview(['data' => $acv->getListviewData()], AchievementEntry::$brickFile));
        }

        // tab: sound
        $ems = DB::Aowow()->selectAssoc(
           'SELECT   `soundId` AS ARRAY_KEY, BIT_OR(1 << (`raceId` - 1)) AS "raceMask", BIT_OR(1 << (`gender` - 1)) AS "gender"
            FROM     ::emotes_sounds
            WHERE    %if', $this->typeId < 0, '-`emoteId` = %i OR', $this->subject->parentEmote, '%end `emoteId` = %i
            GROUP BY `soundId`',
            $this->typeId,
        );

        if ($ems)
        {
            $sounds = new SoundContainer(array(['id', array_keys($ems)]));
            if (!$sounds->error)
            {
                $this->extendGlobalData($sounds->getJSGlobals(GLOBALINFO_SELF));
                $data = $sounds->getListviewData();
                foreach ($data as $id => &$d)
                {
                    $d['races']  = $ems[$id]['raceMask'];
                    $d['gender'] = $ems[$id]['gender'];
                }

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'      => $data,
                    //               gender                                  races
                    'extraCols' => ['$Listview.templates.title.columns[1]', '$Listview.templates.classs.columns[1]']
                ), SoundEntry::$brickFile));
            }
        }

        parent::generate();
    }

    private function prepare(string $emote) : string
    {
        return preg_replace('/%\d?\$?s/', '<'.Util::ucFirst(Lang::main('name')).'>', $this->subject?->$emote);
    }
}

?>
