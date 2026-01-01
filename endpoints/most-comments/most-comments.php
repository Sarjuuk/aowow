<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class MostcommentsBaseResponse extends TemplateResponse
{
    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'most-comments';
    protected ?int    $activeTab  = parent::TAB_TOOLS;
    protected  array  $breadcrumb = [1, 8, 12];             // Tools > Util > Most Comments

    protected  array  $validCats  = [1, 7, 30];

    public function __construct($rawParam)
    {
        $this->getCategoryFromUrl($rawParam);

        parent::__construct($rawParam);
    }

    protected function onInvalidCategory() : never
    {
        $this->forward('?most-comments=1');
    }

    protected function generate() : void
    {
        $this->h1 = Lang::main('utilities', 12);
        if ($this->category && $this->category[0] > 1)
            $this->h1 .= Lang::main('colon') . Lang::main('mostComments', 1, $this->category);
        else
            $this->h1 .= Lang::main('colon') . Lang::main('mostComments', 0);

        $this->h1Link = '?' . $this->pageName.($this->category ? '='.$this->category[0] : '').'&rss' . (Lang::getLocale()->value ? '&locale='.Lang::getLocale()->value : '');
        $this->rss    = Cfg::get('HOST_URL').'/?' . $this->pageName.($this->category ? '='.$this->category[0] : '') . '&amp;rss' . (Lang::getLocale()->value ? '&amp;locale='.Lang::getLocale()->value : '');


        /*********/
        /* Title */
        /*********/

        array_unshift($this->title, $this->h1);


        /**************/
        /* Breadcrumb */
        /**************/

        $this->breadcrumb[] = $this->category[0] ?? 1;


        /****************/
        /* Main Content */
        /****************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], __forceTabs: true);

        $tabBase = array(
            'extraCols' => ["\$Listview.funcBox.createSimpleCol('ncomments', 'tab_comments', '10%', 'ncomments')"],
            'sort'      => ['-ncomments']
        );

        $hasTabs = false;
        foreach (Type::getClassesFor() as $type => $classStr)
        {
            $comments = DB::Aowow()->selectCol(
               'SELECT   `typeId` AS ARRAY_KEY, COUNT(1) FROM ?_comments
                WHERE    `replyTo` = 0 AND (`flags` & ?d) = 0 AND `type`= ?d AND `date` > (UNIX_TIMESTAMP() - ?d)
                GROUP BY `type`, `typeId`
                LIMIT    100',
                CC_FLAG_DELETED,
                $type,
                ($this->category[0] ?? 1) * DAY
            );
            if (!$comments)
                continue;

            $typeClass = new $classStr(array(['id', array_keys($comments)]));
            if ($typeClass->error)
                continue;

            $data = $typeClass->getListviewData();

            foreach ($data as $typeId => &$d)
                $d['ncomments'] = $comments[$typeId];

            $addIn = '';
            if (in_array($type, [Type::AREATRIGGER, Type::ENCHANTMENT, Type::ENCHANTMENT, Type::EMOTE]))
            {
                $addIn = Type::getFileString($type);
                $tabBase['name'] = '$LANG.types['.$type.'][2]';
            }

            $this->extendGlobalData($typeClass->getJSGlobals(GLOBALINFO_ANY));
            $this->lvTabs->addListviewTab(new Listview($tabBase + ['data' => $data], $typeClass::$brickFile, $addIn));
            $hasTabs = true;
        }

        if (!$hasTabs)
            $this->lvTabs->addListviewTab(new Listview(['data' => []], 'commentpreview'));

        parent::generate();
    }
}

?>
