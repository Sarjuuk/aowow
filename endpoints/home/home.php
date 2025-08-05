<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class HomeBaseResponse extends TemplateResponse
{
    protected string $template = 'home';
    protected string $pageName = 'home';

    protected array  $scripts  = array(
        [SC_JS_FILE,    'js/home.js'],
        [SC_CSS_FILE,   'css/home.css'],
        [SC_CSS_STRING, '.announcement { margin: auto; max-width: 1200px; padding: 0px 15px 15px 15px }']
    );

    public  array  $featuredBox = [];
    public ?Markup $oneliner    = null;
    public  string $homeTitle   = '';
    public ?string $altHomeLogo = null;

    protected function generate() : void
    {
        // set <title> element
        if ($_ = DB::Aowow()->selectCell('SELECT `title` FROM ?_home_titles WHERE `active` = 1 AND `locale` = ?d ORDER BY RAND()', Lang::getLocale()->value))
            $this->homeTitle = Util::jsEscape(Cfg::get('NAME').Lang::main('colon').$_);

        // load oneliner
        if ($_ = DB::Aowow()->selectRow('SELECT * FROM ?_home_oneliner WHERE `active` = 1 ORDER BY RAND() LIMIT 1'))
            $this->oneliner = new Markup(new LocString($_, 'text'), [], 'home-oneliner');

        if ($_ = $this->oneliner?->getJsGlobals())
            $this->extendGlobalData($_);

        // load featuredBox (user web server time)
        if ($box = DB::Aowow()->selectRow('SELECT * FROM ?_home_featuredbox WHERE ?d BETWEEN `startDate` AND `endDate` ORDER BY `id` DESC', time()))
        {
            // define text constants for all fields (STATIC_URL, HOST_URL, etc.)
            $box = Util::defStatic($box);

            if ($box['altHomeLogo'])
                $this->altHomeLogo = $box['altHomeLogo'];

            $this->featuredBox = array(
                'markup'   => new Markup(new LocString($box, 'text'), ['allow' => Markup::CLASS_ADMIN], 'news-generic'),
                'extended' => $box['extraWide'],
                'boxBG'    => $box['boxBG'] ?? Cfg::get('STATIC_URL').'/images/'.Lang::getLocale()->json().'/mainpage-bg-news.jpg',
                'overlays' => []
            );

            if ($_ = $this->featuredBox['markup']->getJsGlobals())
                $this->extendGlobalData($_);

            // load overlay links
            foreach (DB::Aowow()->select('SELECT * FROM ?_home_featuredbox_overlay WHERE `featureId` = ?d', $box['id']) as $ovl)
            {
                $ovl = Util::defStatic($ovl);

                $this->featuredBox['overlays'][] = array(
                    'url'   => $ovl['url'],
                    'left'  => $ovl['left'],
                    'width' => $ovl['width'],
                    'title' => new LocString($ovl, 'title')
                );
            }
        }

        parent::generate();
    }
}

?>
