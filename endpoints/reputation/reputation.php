<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ReputationBaseResponse extends TemplateResponse
{
    protected  bool   $requiresLogin = true;

    protected  string $template      = 'list-page-generic';
    protected  string $pageName      = 'reputation';
    protected ?int    $activeTab     = parent::TAB_COMMUNITY;
    protected  array  $breadcrumb    = [3, 10];

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if ($rawParam)
            $this->generateError();
    }

    protected function generate() : void
    {
        $this->h1 = Lang::main('moreTitles', $this->pageName);

        array_unshift($this->title, $this->h1);

        if ($repData = DB::Aowow()->select('SELECT `action`, `amount`, `date` AS "when", IF(`action` IN (?a), `sourceA`, 0) AS "param" FROM ?_account_reputation WHERE `userId` = ?d',
            [SITEREP_ACTION_COMMENT, SITEREP_ACTION_UPVOTED, SITEREP_ACTION_DOWNVOTED], User::$id))
        {
            array_walk($repData, fn(&$x) => $x['when'] = date(Util::$dateFormatInternal, $x['when']));

            $this->tabsTitle = Lang::main('yourRepHistory');
            $this->lvTabs    = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], __forceTabs: true);

            $this->lvTabs->addListviewTab(new Listview(array(
                'id'   => 'reputation-history',
                'name' => '$LANG.reputationhistory',
                'data' => $repData
            ), 'reputationhistory'));
        }

        parent::generate();

        $this->result->registerDisplayHook('article', [self::class, 'articleHook']);
    }

    public static function articleHook(Template\PageTemplate &$pt, Markup &$article) : void
    {
        $article->apply(Cfg::applyToString(...));
    }
}

?>
