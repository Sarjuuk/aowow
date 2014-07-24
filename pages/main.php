<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
    todo (med):
        - different styles for newsbox
        - flags for news .. disabled, deleted, recurring, whatever..
*/

class MainPage extends GenericPage
{
    protected $tpl  = 'main';
    protected $news = [];

    protected function generateContent()
    {
        parent::__construct('home');

        // load news
        $rows = DB::Aowow()->select('SELECT * FROM ?_news ORDER BY time DESC, id DESC LIMIT 5');
        foreach ($rows as $i => $row)
            $this->news[$i]['text'] = Util::localizedString($row, 'text');
    }

    protected function generateTitle() {}
    protected function generatePath() {}
}

?>
