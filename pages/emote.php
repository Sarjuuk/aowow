<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 100: Emotes   g_initPath()
//  tabid   0: Database g_initHeader()
class EmotePage extends GenericPage
{
    use TrDetailPage;

    protected $type          = Type::EMOTE;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 100];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    public function __construct($pageCall, $id)
    {
        /*
         * id > 0: player text emote
         * id < 0: creature emote
        */

        parent::__construct($pageCall, $id);

        $this->typeId = intVal($id);

        $this->subject = new EmoteList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('emote'), Lang::emote('notFound'));

        $this->name = Util::ucFirst($this->subject->getField('cmd'));
    }

    protected function generatePath() { }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('emote')));
    }

    protected function generateContent()
    {
        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // has Animation
        if ($this->subject->getField('isAnimated') && !$this->subject->getField('stateParam'))
        {
            $infobox[] = Lang::emote('isAnimated');

            // anim state
            $state = Lang::emote('state', $this->subject->getField('state'));
            if ($this->subject->getField('state') == 1)
                $state .= Lang::main('colon').Lang::unit('bytes1', 0, $this->subject->getField('stateParam'));
            $infobox[] = $state;
        }

        if (User::isInGroup(U_GROUP_STAFF | U_GROUP_TESTER))
        {
            // player emote: point to internal data
            if ($_ = $this->subject->getField('parentEmote'))
            {
                $this->extendGlobalIds(Type::EMOTE, $_);
                $infobox[] = '[emote='.$_.']';
            }

            if ($flags = $this->subject->getField('flags'))
            {
                $box = Lang::game('flags').Lang::main('colon').'[ul]';
                foreach (Lang::emote('flags') as $bit => $str)
                    if ($bit & $flags)
                        $box .= '[li][tooltip name=hint-'.$bit.']'.Util::asHex($bit).'[/tooltip][span class=tip tooltip=hint-'.$bit.']'.$str.'[/span][/li]';
                $infobox[] = $box.'[/ul]';
            }
        }

        /****************/
        /* Main Content */
        /****************/

        $text = '';

        if ($this->subject->getField('cuFlags') & EMOTE_CU_MISSING_CMD)
            $text .= Lang::emote('noCommand').'[br][br]';
        else if ($aliasses = DB::Aowow()->selectCol('SELECT command FROM ?_emotes_aliasses WHERE id = ?d AND locales & ?d', $this->typeId, 1 << User::$localeId))
        {
            $text .= '[h3]'.Lang::emote('aliases').'[/h3][ul]';
            foreach ($aliasses as $a)
                $text .= '[li]/'.$a.'[/li]';

            $text .= '[/ul][br][br]';
        }

        $target = $noTarget = [];
        if ($_ = $this->subject->getField('extToExt', true))
            $target[] = $this->prepare($_);
        if ($_ = $this->subject->getField('extToMe', true))
            $target[] = $this->prepare($_);
        if ($_ = $this->subject->getField('meToExt', true))
            $target[] = $this->prepare($_);
        if ($_ = $this->subject->getField('extToNone', true))
            $noTarget[] = $this->prepare($_);
        if ($_ = $this->subject->getField('meToNone', true))
            $noTarget[] =$this->prepare($_);

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
        if ($_ = $this->subject->getField('soundId'))
        {
            $this->extendGlobalIds(Type::SOUND, $_);
            $text .= '[h3]'.Lang::emote('eventSound').'[/h3][sound='.$_.']';
        }

        $this->extraText = $text;
        $this->infobox   = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null;
        $this->redButtons = array(
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_WOWHEAD => false
        );

        /**************/
        /* Extra Tabs */
        /**************/

        // tab: achievement
        $condition = array(
            ['ac.type', ACHIEVEMENT_CRITERIA_TYPE_DO_EMOTE],
            ['ac.value1', $this->typeId],
        );
        $acv = new AchievementList($condition);
        if (!$acv->error)
        {
            $this->lvTabs[] = [AchievementList::$brickFile, ['data' => array_values($acv->getListviewData())]];

            $this->extendGlobalData($acv->getJsGlobals());
        }

        // tab: sound
        if ($em = DB::Aowow()->select('SELECT soundId AS ARRAY_KEY, BIT_OR(1 << (raceId - 1)) AS raceMask, BIT_OR(1 << (gender - 1)) AS gender FROM ?_emotes_sounds WHERE -emoteId = ?d GROUP BY soundId', $this->typeId > 0 ? $this->subject->getField('parentEmote') : $this->typeId))
        {
            $sounds = new SoundList(array(['id', array_keys($em)]));
            if (!$sounds->error)
            {
                $this->extendGlobalData($sounds->getJSGlobals(GLOBALINFO_SELF));
                $data = $sounds->getListviewData();
                foreach($data as $id => &$d)
                {
                    $d['races']  = $em[$id]['raceMask'];
                    $d['gender'] = $em[$id]['gender'];
                }

                $this->lvTabs[] = [SoundList::$brickFile, array(
                    'data'      => array_values($data),
                    //               gender                                  races
                    'extraCols' => ['$Listview.templates.title.columns[1]', '$Listview.templates.classs.columns[1]']
                )];
            }
        }
    }

    private function prepare(string $emote) : string
    {
        $emote = Util::parseHtmlText($emote, true);
        return preg_replace('/%\d?\$?s/', '<'.Util::ucFirst(Lang::main('name')).'>', $emote);
    }
}

?>
