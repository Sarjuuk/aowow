<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class UnratedcommentsBaseResponse extends TemplateResponse
{
    protected  string $pageName   = 'unrated-comments';
    protected  string $template   = 'list-page-generic';
    protected ?int    $activeTab  = parent::TAB_TOOLS;
    protected  array  $breadcrumb = [1, 8, 5];              // Tools > Util > Unrated Comments

    protected function generate() : void
    {
        $this->h1 = Lang::main('utilities', 5);


        /*********/
        /* Title */
        /*********/

        array_unshift($this->title, $this->h1);


        /****************/
        /* Main Content */
        /****************/

        if ($data = CommunityContent::getCommentPreviews(['unrated' => true, 'comments' => true], resultLimit: Listview::DEFAULT_SIZE))
        {
            $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);
            $this->lvTabs->addListviewTab(new Listview(['data' => $data], 'commentpreview'));
        }
        else
            $this->extraHTML = 'No comments were found.';

        parent::generate();
    }

    protected function generateMetadata(bool $useArticle = true) : void
    {
        $this->metaTags[] = ['property' => 'og:title', 'content' => $this->h1];
        $this->metaTags[] = ['property' => 'og:type',  'content' => 'website'];

        array_unshift($this->metaTags, ['name' => 'keywords', 'content' => [Lang::main('comments'), ...Lang::meta('tags', 'generic')]]);

        $this->buildBasicMetadata();
    }
}

?>
