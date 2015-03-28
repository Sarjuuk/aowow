<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// the actual text is an article accessed by type + typeId
// menuId 2: More g_initPath()
//  tabid 2: More g_initHeader()

class MorePage extends GenericPage
{
    protected $tpl           = 'text-page-generic';
    protected $path          = [2];
    protected $tabId         = 2;
    protected $mode          = CACHE_TYPE_NONE;
    protected $js            = ['swfobject.js'];

    private   $subPages      = [ -13 => ['commenting-and-you', 'modelviewer', 'screenshots-tips-tricks', 'stat-weighting', 'talent-calculator', 'item-comparison', 'profiler', 'markup-guide']];
    private   $validPages    = array(                       // [type, typeId, name]
        'whats-new'     => [ -7,    0, "What's New"],
        'searchbox'     => [-16,    0, 'Search Box'],
        'tooltips'      => [-10,    0, 'Tooltips'],
        'faq'           => [ -3,    0, 'Frequently Asked Questions'],
        'aboutus'       => [ -1,    0, 'What is AoWoW?'],
        'searchplugins' => [ -8,    0, 'Search Plugins'],
        'help'          => [-13, null, '']
    );

    public function __construct($pageCall, $subPage)
    {
        parent::__construct($pageCall, $subPage);

        // chack if page is valid
        if (isset($this->validPages[$pageCall]))
        {
            $_ = $this->validPages[$pageCall];

            // check if subpage is valid
            if (!isset($_[1]))
            {
                if (($_[1] = array_search($subPage, $this->subPages[$_[0]])) === false)
                    $this->error();

                if ($pageCall == 'help')                    // ye.. hack .. class definitions only allow static values
                    $_[2] = Lang::main('helpTopics', $_[1]);
            }
            $this->type      = $_[0];
            $this->typeId    = $_[1];
            $this->name      = $_[2];
            $this->gPageInfo = array(
                'type'   => $this->type,
                'typeId' => $this->typeId,
                'name'   => $this->name
            );
        }
        else
            $this->error();
    }

    protected function generatePath()
    {
        $this->path[] = abs($this->type);

        if ($this->typeId > -1)
            $this->path[] = $this->typeId;
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
    }

    protected function generateContent() {}                 // its just articles here
}

?>
