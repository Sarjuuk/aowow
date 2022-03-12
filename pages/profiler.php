<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfilerPage extends GenericPage
{
    protected $path     = [1, 5];
    protected $tabId    = 1;
    protected $tpl      = 'profiler';
    protected $gDataKey = true;
    protected $js       = [[JS_FILE, 'profile_all.js'], [JS_FILE, 'profile.js']];
    protected $css      = [[CSS_FILE, 'Profiler.css']];

    public function __construct($pageCall, $pageParam)
    {
        if (!CFG_PROFILER_ENABLE)
            $this->error();

        parent::__construct($pageCall, $pageParam);
    }

    protected function generateContent()
    {
        $this->addScript([JS_FILE, '?data=realms&locale='.User::$localeId.'&t='.$_SESSION['dataKey']]);
    }

    protected function generatePath() { }

    protected function generateTitle()
    {
        array_unshift($this->title, Util::ucFirst(Lang::profiler('profiler')));
    }
}

?>
