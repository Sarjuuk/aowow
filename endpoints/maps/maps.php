<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class MapsBaseResponse extends TemplateResponse
{
    protected  int    $cacheType  = CACHE_TYPE_PAGE;

    protected  string $template   = 'maps';
    protected  string $pageName   = 'maps';
    protected ?int    $activeTab  = parent::TAB_TOOLS;
    protected  array  $breadcrumb = [1, 1];

    protected  array  $dataLoader = ['zones'];
    protected  array  $scripts    = [[SC_JS_FILE, 'js/maps.js'], [SC_CSS_STRING, 'zone-picker { margin-left: 4px }']];

    protected function generate() : void
    {
        $this->h1 = Lang::maps('maps');

        array_unshift($this->title, $this->h1);

        parent::generate();
    }
}

?>
