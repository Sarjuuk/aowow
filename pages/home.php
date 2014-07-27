<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class HomePage extends GenericPage
{
    protected $tpl  = 'home';
    protected $js   = ['home.js'];
    protected $css  = [['path' => 'home.css']];

    protected $news = [];

    public function __construct()
    {
        parent::__construct('home');
    }

    protected function generateContent()
    {
        $this->addCSS(['string' => '.announcement { margin: auto; max-width: 1200px; padding: 0px 15px 15px 15px }']);

        // load news
        $this->news = DB::Aowow()->selectRow('SELECT id as ARRAY_KEY, n.* FROM ?_news n WHERE active = 1 ORDER BY id DESC LIMIT 1');
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
        $this->news['overlays'] = DB::Aowow()->select('SELECT * FROM ?_news_overlay WHERE newsId = ?d', $this->news['id']);
        foreach ($this->news['overlays'] as &$o)
        {
            $o['title'] = Util::localizedString($o, 'title', true);
            $o['title'] = strtr($o['title'], ['HOST_URL' => HOST_URL, 'STATIC_URL' => STATIC_URL]);
        }
    }

    protected function generateTitle() {}
    protected function generatePath() {}
}

?>
