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


        // tab: Spells (howto: actual spell sounds?)
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

        // tab: Races (VocalUISounds (containing error voice overs) EmotesTextSound (containing emote audio))




        // now here is the interesting part
        // there is a crapton of sound-related dbc files
        // how can we link sounds and events
        // anything goes .. probably
        // used by: item (material sounds), creature (dialog + activities),
    }
}


?>
