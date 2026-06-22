<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class MailsBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type       = Type::MAIL;
    protected  int    $cacheType  = CACHE_TYPE_LIST_PAGE;

    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'mails';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 103];

    public function __construct(string $rawParam)
    {
        $this->getCategoryFromUrl($rawParam);

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('mails'));


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1);


        /****************/
        /* Main Content */
        /****************/

        $tabData = [];
        $mails = new MailList();
        if (!$mails->error)
            $tabData['data'] = $mails->getListviewData();

        $this->extendGlobalData($mails->getJSGlobals());

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview(['data' => $mails->getListviewData()], MailList::$brickFile, 'mail'));

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
