<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class HomePage extends GenericPage
{
    protected $tpl      = 'home';
    protected $scripts  = array(
        [SC_JS_FILE,    'js/home.js'],
        [SC_CSS_FILE,   'css/home.css'],
        [SC_CSS_STRING, '.announcement { margin: auto; max-width: 1200px; padding: 0px 15px 15px 15px }']
    );

    protected $featuredBox = [];
    protected $oneliner    = '';
    protected $homeTitle   = '';

    public function __construct()
    {
        parent::__construct('home');
    }

    protected function generateContent()
    {
        // load oneliner
        if ($_ = DB::Aowow()->selectRow('SELECT * FROM ?_home_oneliner WHERE active = 1 LIMIT 1'))
            $this->oneliner = Util::jsEscape(Util::localizedString($_, 'text'));

        // load featuredBox (user web server time)
        $this->featuredBox = DB::Aowow()->selectRow('SELECT id as ARRAY_KEY, n.* FROM ?_home_featuredbox n WHERE ?d BETWEEN startDate AND endDate ORDER BY id DESC LIMIT 1', time());
        if (!$this->featuredBox)
            return;

        $this->featuredBox = Util::defStatic($this->featuredBox);

        $this->featuredBox['text'] = Util::localizedString($this->featuredBox, 'text', true);

        if ($_ = (new Markup($this->featuredBox['text']))->parseGlobalsFromText())
            $this->extendGlobalData($_);

        if (empty($this->featuredBox['boxBG']))
            $this->featuredBox['boxBG'] = Cfg::get('STATIC_URL').'/images/'.User::$localeString.'/mainpage-bg-news.jpg';

        // load overlay links
        $this->featuredBox['overlays'] = DB::Aowow()->select('SELECT * FROM ?_home_featuredbox_overlay WHERE featureId = ?d', $this->featuredBox['id']);
        foreach ($this->featuredBox['overlays'] as &$o)
        {
            $o['title'] = Util::localizedString($o, 'title', true);
            $o['title'] = Util::defStatic($o['title']);
        }
    }

    protected function generateTitle()
    {
        if ($_ = DB::Aowow()->selectCell('SELECT title FROM ?_home_titles WHERE active = 1 AND locale = ?d ORDER BY RAND() LIMIT 1', User::$localeId))
            $this->homeTitle = Cfg::get('NAME').Lang::main('colon').$_;
    }

    protected function generatePath() {}
}

?>
