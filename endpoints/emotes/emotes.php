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

    public function __construct(string $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageParam);
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
}

?>
