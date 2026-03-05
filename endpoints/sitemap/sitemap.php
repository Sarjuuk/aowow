<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SitemapBaseResponse extends TextResponse implements ICache
{
    use TrCache;

    protected string $contentType = MIME_TYPE_XML;
    protected int    $cacheType   = CACHE_TYPE_XML;

    protected array $expectedGET = array(
        'page' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_value' => 1]]
    );

    private string $page;

    public function __construct(string $pageParam)
    {
        $this->page = $pageParam;

        parent::__construct($pageParam);
    }

    protected function generate() : void
    {
        if ($xml = Sitemap::generate($this->page, $this->_get['page'] ?? 1))
            $this->result = $xml;
        else if (Sitemap::$maxPage)
            (new TemplateResponse($this->page))->generateNotFound(Sitemap::ERR_TITLE, sprintf(Sitemap::ERR_OFFSET, Sitemap::$maxPage));
        else
            (new TemplateResponse($this->page))->generateNotFound(Sitemap::ERR_TITLE, Sitemap::ERR_PAGE);
    }

    public function getCacheKeyComponents() : array
    {
        $misc = $this->page . serialize($this->_get['page'] ?? 1);

        return array(
            -1,                                             // DBType
            -1,                                             // DBTypeId/category
            -1,                                             // staff mask (content does not diff)
            md5($misc)                                      // misc
        );
    }
}

?>
