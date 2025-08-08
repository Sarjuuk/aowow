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
        $cnd = [[['cuFlags', CUSTOM_HAS_SCREENSHOT, '&'], 0], 200];
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
            $this->lvTabs->addListviewTab(new Listview(['data' => []], 'item'));

        parent::generate();
    }
}

?>
