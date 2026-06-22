<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EmotesBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_LIST_PAGE;
    protected  int    $type       = Type::EMOTE;

    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'emotes';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 100];

    public function __construct(string $rawParam)
    {
        $this->getCategoryFromUrl($rawParam);

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('emotes'));


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1);


        /****************/
        /* Main Content */
        /****************/

        $cnd = [];                                          // don't limit, for we have no filter or category
        if (!User::isInGroup(U_GROUP_STAFF))
            $cnd[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $tabData = array(
            'data' => (new EmoteList($cnd))->getListviewData(),
            'name' => Util::ucFirst(Lang::game('emotes'))
        );

        $this->lvTabs->addListviewTab(new Listview($tabData, EmoteList::$brickFile, 'emote'));

        parent::generate();
    }

    protected function generateMetadata(bool $useArticle = true) : void
    {
        $this->metaTags[] = ['property' => 'og:title', 'content' => $this->h1];
        $this->metaTags[] = ['property' => 'og:type',  'content' => 'website'];

        array_unshift($this->metaTags, ['name' => 'keywords', 'content' => [$this->h1, ...Lang::meta('tags', 'generic')]]);

        $this->buildBasicMetadata(Lang::meta('description', 'genList', [$this->h1]));

        $this->buildLdJson();
    }
}

?>
