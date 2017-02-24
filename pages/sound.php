<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 19: Sound    g_initPath()
//  tabId  0: Database g_initHeader()
class SoundPage extends GenericPage
{
    use DetailPage;

    protected $type          = TYPE_SOUND;
    protected $typeId        = 0;
    protected $tpl           = 'sound';
    protected $path          = [0, 19];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    private   $cat           = 0;
    protected $special       = false;

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        // special case
        if (!$id && isset($_GET['playlist']))
        {
            $this->special = true;
            $this->name    = Lang::sound('cat', 1000);
            $this->cat     = 1000;
            $this->typeId  = -1000;
        }
        // regular case
        else
        {
            $this->typeId = intVal($id);

            $this->subject = new SoundList(array(['id', $this->typeId]));
            if ($this->subject->error)
                $this->notFound(Lang::game('sound'), Lang::sound('notFound'));

            $this->name = $this->subject->getField('name');
            $this->cat  = $this->subject->getField('cat');
        }
    }

    protected function generatePath()
    {
        $this->path[] = $this->cat;
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('sound')));
    }

    protected function generateContent()
    {
        if ($this->special)
            $this->generatePlaylistContent();
        else
            $this->generateDefaultContent();
    }

    private function generatePlaylistContent()
    {

    }

    private function generateDefaultContent()
    {
        /****************/
        /* Main Content */
        /****************/

        $this->headIcons  = [$this->subject->getField('iconString')];
        $this->redButtons = array(
            BUTTON_WOWHEAD  => true,
            BUTTON_PLAYLIST => true,
            BUTTON_LINKS    => array(
                'type'   => TYPE_SOUND,
                'typeId' => $this->typeId,
                'sound'  => $this->typeId
            )
        );

        $this->extendGlobalData($this->subject->getJSGlobals());

        /**************/
        /* Extra Tabs */
        /**************/


        // tab: Spells
        // todo: -> SpellVisual.dbc (missleSound, animEventSound, 8x link to SpellVisualKit.dbc)
        //       -> SpellVisualKit.dbc (soundId)
        $cnd = array(
            'OR',
            ['AND', ['effect1Id', 132], ['effect1MiscValue', $this->typeId]],
            ['AND', ['effect2Id', 132], ['effect2MiscValue', $this->typeId]],
            ['AND', ['effect3Id', 132], ['effect3MiscValue', $this->typeId]]
        );
        $spells = new SpellList($cnd);
        if (!$spells->error)
        {
            $data = $spells->getListviewData();
            $this->extendGlobalData($spells->getJSGlobals(GLOBALINFO_SELF));

            $this->lvTabs[] = ['spell', array(
                'data' => array_values($data),
            )];
        }

        // tab: Zones
        $cnd = array(
            'OR',
            ['soundAmbiDay',    $this->typeId],
            ['soundAmbiNight',  $this->typeId],
            ['soundMusicDay',   $this->typeId],
            ['soundMusicNight', $this->typeId],
            ['soundIntro',      $this->typeId]
        );
        $zones = new ZoneList($cnd);
        if (!$zones->error)
        {
            $this->extendGlobalData($zones->getJSGlobals(GLOBALINFO_SELF));

            $zoneData = $zones->getListviewData();
            $parents  = $zones->getAllFields('parentArea');

            $pIds = array_filter(array_unique(array_values($parents)));
            if ($pIds)
            {
                $pZones = new ZoneList(array(['id', $pIds]));
                if (!$pZones->error)
                {
                    $this->extendGlobalData($pZones->getJSGlobals(GLOBALINFO_SELF));

                    $pData = $pZones->getListviewData();
                    foreach ($parents as $child => $parent)
                    {
                        if (!$parent || empty($pData[$parent]))
                            continue;

                        if (!isset($pData[$parent]['subzones']))
                            $pData[$parent]['subzones'] = [];

                        $pData[$parent]['subzones'][] = $child;
                        unset($parents[$child]);
                    }

                    // these are original parents
                    foreach ($parents as $parent => $__)
                        if (empty($pData[$parent]))
                            $pData[$parent] = $zoneData[$parent];

                    $zoneData = $pData;
                }
            }

            $this->lvTabs[] = ['zone', array(
                'data'       => array_values($zoneData),
                'hiddenCols' => ['territory']
            )];

        }

        // tab: Races (VocalUISounds (containing error voice overs))
        if ($vo = DB::Aowow()->selectCol('SELECT raceId FROM ?_races_sounds WHERE soundId = ?d GROUP BY raceId', $this->typeId))
        {
            $races = new CharRaceList(array(['id', $vo]));
            if (!$races->error)
            {
                $this->extendGlobalData($races->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs[] = ['race', ['data' => array_values($races->getListviewData())]];
            }
        }

        // tab: Emotes (EmotesTextSound (containing emote audio))
        if ($em = DB::Aowow()->selectCol('SELECT emoteId FROM ?_emotes_sounds WHERE soundId = ?d GROUP BY emoteId', $this->typeId))
        {
            $races = new EmoteList(array(['id', $em]));
            if (!$races->error)
            {
                $this->extendGlobalData($races->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs[] = ['emote', array(
                    'data' => array_values($races->getListviewData()),
                    'name' => Util::ucFirst(Lang::game('emotes'))
                ), 'emote'];
            }
        }

        // tab: NPC (dialogues...?, generic creature sound)
        // lokaler Brotkasten text <- creature_text
        if ($ids = DB::World()->selectCol('SELECT entry FROM creature_text ct LEFT JOIN broadcast_text bct ON bct.ID = ct.BroadCastTextId WHERE bct.SoundId = ?d OR ct.sound = ?d', $this->typeId, $this->typeId))
        {
            $npcs = new CreatureList(array(['id', $ids]));
            if (!$npcs->error)
            {
                $this->addJS('?data=zones&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

                $this->extendGlobalData($npcs->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs[] = ['creature', ['data' => array_values($npcs->getListviewData())]];
            }
        }


        // tab: Item (material sounds), creature (dialog + activities),

    }
}


?>
