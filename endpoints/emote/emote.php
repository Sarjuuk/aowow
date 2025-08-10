<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EmoteBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_PAGE;

    protected  string $template   = 'detail-page-generic';
    protected  string $pageName   = 'emote';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 100];

    public int $type   = Type::EMOTE;
    public int $typeId = 0;

    private EmoteList $subject;

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
        $this->subject = new EmoteList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('emote'), Lang::emote('notFound'));

        $this->h1 = Util::ucFirst($this->subject->getField('cmd'));

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

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');


        /****************/
        /* Main Content */
        /****************/

        $text = '';

        if ($this->subject->getField('cuFlags') & EMOTE_CU_MISSING_CMD)
            $text .= Lang::emote('noCommand').'[br][br]';
        else if ($aliasses = DB::Aowow()->selectCol('SELECT `command` FROM ?_emotes_aliasses WHERE `id` = ?d AND `locales` & ?d', $this->typeId, 1 << Lang::getLocale()->value))
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
            $noTarget[] = $this->prepare($_);

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
        $acv = new AchievementList($condition);
        if (!$acv->error)
        {
            $this->extendGlobalData($acv->getJsGlobals());
            $this->lvTabs->addListviewTab(new Listview(['data' => $acv->getListviewData()], AchievementList::$brickFile));
        }

        // tab: sound
        $ems = DB::Aowow()->select(
           'SELECT   `soundId` AS ARRAY_KEY, BIT_OR(1 << (`raceId` - 1)) AS "raceMask", BIT_OR(1 << (`gender` - 1)) AS "gender"
            FROM     ?_emotes_sounds
            WHERE    `emoteId` = ?d { OR -`emoteId` = ?d }
            GROUP BY `soundId`',
            $this->typeId,
            $this->typeId < 0 ? $this->subject->getField('parentEmote') : DBSIMPLE_SKIP
        );

        if ($ems)
        {
            $sounds = new SoundList(array(['id', array_keys($ems)]));
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
                ), SoundList::$brickFile));
            }
        }

        parent::generate();
    }

    private function prepare(string $emote) : string
    {
        $emote = Util::parseHtmlText($emote, true);
        return preg_replace('/%\d?\$?s/', '<'.Util::ucFirst(Lang::main('name')).'>', $emote);
    }
}

?>
