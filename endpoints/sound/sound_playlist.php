<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SoundPlaylistResponse extends TemplateResponse
{
    protected  string $template   = 'sound-playlist';
    protected  string $pageName   = 'sound&playlist';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 19, 1000];

    protected function generate() : void
    {
        $this->h1 = Lang::sound('cat', 1000);

        array_unshift($this->title, $this->h1, Util::ucFirst(Lang::game('sound')));

        parent::generate();
    }
}

?>
