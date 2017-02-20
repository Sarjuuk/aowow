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

        // now here is the interesting part
        // there is a crapton of sound-related dbc files
        // how can we link sounds and events
        // anything goes .. probably
        // used by: spell (effect: play sound + actual spell effects), item (material sounds), zone (music + ambience), creature (dialog + activities), race (error text + emotes)
    }
}


?>
