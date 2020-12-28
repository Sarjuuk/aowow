<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 100: Emotes   g_initPath()
//  tabid   0: Database g_initHeader()
class EmotePage extends GenericPage
{
    use TrDetailPage;

    protected $type          = TYPE_EMOTE;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 100];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    public function __construct($pageCall, $id)
    {
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
        if ($this->subject->getField('isAnimated'))
            $infobox[] = Lang::emote('isAnimated');

        /****************/
        /* Main Content */
        /****************/

        $text = '';
        if ($aliasses = DB::Aowow()->selectCol('SELECT command FROM ?_emotes_aliasses WHERE id = ?d AND locales & ?d', $this->typeId, 1 << User::$localeId))
        {
            $text .= '[h3]'.Lang::emote('aliases').'[/h3][ul]';
            foreach ($aliasses as $a)
                $text .= '[li]/'.$a.'[/li]';

            $text .= '[/ul][br][br]';
        }

        $texts = [];
        if ($_ = $this->subject->getField('self', true))
            $texts[Lang::emote('self')] = $_;

        if ($_ = $this->subject->getField('target', true))
            $texts[Lang::emote('target')] = $_;

        if ($_ = $this->subject->getField('noTarget', true))
            $texts[Lang::emote('noTarget')] = $_;

        if (!$texts)
            $text .= '[div][i class=q0]'.Lang::emote('noText').'[/i][/div]';
        else
            foreach ($texts as $h => $t)
                $text .= '[pad][b]'.$h.'[/b][ul][li][span class=s4]'.preg_replace('/%\d?\$?s/', '<'.Util::ucFirst(Lang::main('name')).'>', $t).'[/span][/li][/ul]';

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

        $this->lvTabs[] = ['achievement', ['data' => array_values($acv->getListviewData())]];

        $this->extendGlobalData($acv->getJsGlobals());

        // tab: sound
        if ($em = DB::Aowow()->select('SELECT soundId AS ARRAY_KEY, BIT_OR(1 << (raceId - 1)) AS raceMask, BIT_OR(1 << (gender - 1)) AS gender FROM aowow_emotes_sounds WHERE emoteId = ?d GROUP BY soundId', $this->typeId))
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

                $this->lvTabs[] = ['sound', array(
                    'data'      => array_values($data),
                    //               gender                                  races
                    'extraCols' => ['$Listview.templates.title.columns[1]', '$Listview.templates.classs.columns[1]']
                )];
            }
        }
    }
}

?>
