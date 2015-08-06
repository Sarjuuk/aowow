<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class HomePage extends GenericPage
{
    protected $tpl      = 'home';
    protected $js       = ['home.js'];
    protected $css      = [['path' => 'home.css']];

    protected $news     = [];
    protected $oneliner = '';

    public function __construct()
    {
        parent::__construct('home');
    }

    protected function generateContent()
    {
        $this->addCSS(['string' => '.announcement { margin: auto; max-width: 1200px; padding: 0px 15px 15px 15px }']);

        // load oneliner
        if ($_ = DB::Aowow()->selectRow('SELECT * FROM ?_home_oneliner WHERE active = 1 LIMIT 1'))
            $this->oneliner = Util::jsEscape(Util::localizedString($_, 'text'));

        // load news
        $this->news = DB::Aowow()->selectRow('SELECT id as ARRAY_KEY, n.* FROM ?_home_featuredbox n WHERE active = 1 ORDER BY id DESC LIMIT 1');
        if (!$this->news)
            return;

        $this->news['text'] = Util::localizedString($this->news, 'text', true);

        if ($_ = (new Markup($this->news['text']))->parseGlobalsFromText())
            $this->extendGlobalData($_);

        if (empty($this->news['bgImgUrl']))
            $this->news['bgImgUrl'] = STATIC_URL.'/images/'.User::$localeString.'/mainpage-bg-news.jpg';
        else
            $this->news['bgImgUrl'] = strtr($this->news['bgImgUrl'], ['HOST_URL' => HOST_URL, 'STATIC_URL' => STATIC_URL]);

        // load overlay links
        $this->news['overlays'] = DB::Aowow()->select('SELECT * FROM ?_home_featuredbox_overlay WHERE featureId = ?d', $this->news['id']);
        foreach ($this->news['overlays'] as &$o)
        {
            $o['title'] = Util::localizedString($o, 'title', true);
            $o['title'] = strtr($o['title'], ['HOST_URL' => HOST_URL, 'STATIC_URL' => STATIC_URL]);
        }
    }

    protected function generateTitle()
    {
        if ($_ = DB::Aowow()->selectRow('SELECT * FROM ?_home_titles WHERE active = 1 AND title_loc?d <> "" ORDER BY RAND() LIMIT 1', User::$localeId))
            $this->title[0] .= Lang::main('colon').Util::localizedString($_, 'title');
    }

    protected function generatePath() {}
}

?>
