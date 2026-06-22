<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class MissingscreenshotsBaseResponse extends TemplateResponse
{
    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'missing-screenshots';
    protected ?int    $activeTab  = parent::TAB_TOOLS;
    protected  array  $breadcrumb = [1, 8, 13];             // Tools > Util > Missing Screenshots

    protected function generate() : void
    {
        $this->h1 = Lang::main('utilities', 13);


        /*********/
        /* Title */
        /*********/

        array_unshift($this->title, $this->h1);


        /****************/
        /* Main Content */
        /****************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        // limit to 200 entries each (it generates faster, consumes less memory and should be enough options)
        // meta description says it must have at least 1 comment
        $cnd = array(
            200,
            ['cuFlags', CUSTOM_HAS_COMMENT, '&'],
            [['cuFlags', CUSTOM_HAS_SCREENSHOT, '&'], 0]
        );
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $cnd[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        $hasTabs = false;
        foreach (Type::getClassesFor(Type::FLAG_RANDOM_SEARCHABLE, 'contribute', CONTRIBUTE_SS) as $classStr)
        {
            $typeObj = new $classStr($cnd);
            if ($typeObj->error)
                continue;

            $this->extendGlobalData($typeObj->getJSGlobals(GLOBALINFO_ANY));
            $this->lvTabs->addListviewTab(new Listview(['data' => $typeObj->getListviewData()], $typeObj::$brickFile));
            $hasTabs = true;
        }

        if (!$hasTabs)
        {
            // should be placed below the div.text, but w/e
            // it tickles me, that this basically unreachable condition was translated. (the other locales are lost to time though)
            // Пропавшие скриншоты не найдены. Погодите, что?
            $this->extraHTML = 'No missing screenshots were found. Wait, what?';
            unset($this->lvTabs);
        }

        parent::generate();
    }

    protected function generateMetadata(bool $useArticle = true) : void
    {
        $this->metaTags[] = ['property' => 'og:title', 'content' => $this->h1];
        $this->metaTags[] = ['property' => 'og:type',  'content' => 'website'];

        array_unshift($this->metaTags, ['name' => 'keywords', 'content' => [Lang::main('screenshots'), ...Lang::meta('tags', 'generic')]]);

        $this->buildBasicMetadata();
    }
}

?>
