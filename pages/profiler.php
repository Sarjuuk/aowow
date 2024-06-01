<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfilerPage extends GenericPage
{
    protected $path     = [1, 5];
    protected $tabId    = 1;
    protected $tpl      = 'profiler';
    protected $gDataKey = true;
    protected $scripts  = array(
        [SC_JS_FILE,  'js/profile_all.js'],
        [SC_JS_FILE,  'js/profile.js'],
        [SC_CSS_FILE, 'css/Profiler.css']
    );

    public function __construct($pageCall, $pageParam)
    {
        parent::__construct($pageCall, $pageParam);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->error();
    }

    protected function generateContent()
    {
        $this->addScript([SC_JS_FILE, '?data=realms']);
    }

    protected function generatePath() { }

    protected function generateTitle()
    {
        array_unshift($this->title, Util::ucFirst(Lang::profiler('profiler')));
    }
}

?>
