<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// the actual text is an article accessed by type + typeId
// menuId 2: More g_initPath()
//  tabid 2: More g_initHeader()

class MorePage extends GenericPage
{
    protected $tpl           = 'list-page-generic';
    protected $path          = [];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_NONE;
    protected $js            = ['swfobject.js'];

    private   $validPages    = array(                       // [tabId, path[, subPaths]]
        'whats-new'     => [2, [2,  7]],
        'searchbox'     => [2, [2, 16]],
        'tooltips'      => [2, [2, 10]],
        'faq'           => [2, [2,  3]],
        'aboutus'       => [2, [2,  0]],
        'searchplugins' => [2, [2,  8]],
        'help'          => [2, [2, 13], ['commenting-and-you', 'modelviewer', 'screenshots-tips-tricks', 'stat-weighting', 'talent-calculator', 'item-comparison', 'profiler', 'markup-guide', 'markup-guide-ext']],
        'reputation'    => [1, [3, 10]],
        'privilege'     => [1, [3, 10], [1, 2, 5, 9, 10, 11, 12, 13, 14, 15, 16]],
        'privileges'    => [1, [3, 10, 0]],
    );

    public function __construct($pageCall, $subPage)
    {
        parent::__construct($pageCall, $subPage);

        // chack if page is valid
        if (isset($this->validPages[$pageCall]))
        {
            $pageData = $this->validPages[$pageCall];

            $this->tab  = $pageData[0];
            $this->path = $pageData[1];

            if ($subPage && isset($pageData[2]))
            {
                $subPath = array_search($subPage, $pageData[2]);
                if (!$subPath)
                    $this->error();

                if (is_numeric($subPath))
                    $this->path[] = $subPath;
                else
                    $this->path[] = $subPage;

                $this->articleUrl = $subPage;
                $this->name = Lang::main('moreTitles', $pageCall, $subPage);
            }
            else
            {
                $this->articleUrl = $pageCall;
                $this->name = Lang::main('moreTitles', $pageCall);
            }
        }
        else
            $this->error();
    }

    protected function generateContent()
    {
        if ($this->articleUrl == 'reputation')
            $this->handleReputationPage();
        else if ($this->articleUrl == 'privileges')
            $this->handlePrivilegesPage();
    }

    protected function postArticle()
    {
        if ($this->articleUrl == 'reputation')
            $this->handleReputationArticle();
    }

    protected function generatePath() { }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
    }

    private function handleReputationPage()
    {
        if (!User::$id)
            return;

        if ($repData = DB::Aowow()->select('SELECT action, amount, date AS \'when\', IF(action IN (3, 4, 5), sourceA, 0) AS param FROM ?_account_reputation WHERE userId = ?d', User::$id))
        {
            foreach ($repData as &$r)
                $r['when'] = date(Util::$dateFormatInternal, $r['when']);

            $this->tabsTitle = Lang::main('yourRepHistory');
            $this->forceTabs = true;
            $this->lvTabs[] = ['reputationhistory', array(
                'id'   => 'reputation-history',
                'name' => '$LANG.reputationhistory',
                'data' => $repData
            )];
        }
    }

    private function handleReputationArticle()
    {
        $txt = &$this->article['text'];

        $consts = get_defined_constants(true);
        foreach ($consts['user'] as $k => $v)
            if (strstr($k, 'CFG_REP_'))
                $txt = str_replace($k, Lang::nf($v), $txt);
    }

    private function handlePrivilegesPage()
    {
        $this->tpl        = 'privileges';
        $this->privileges = [];

        $req2priv = array(
             1 => CFG_REP_REQ_COMMENT,                      // write comments
         //  2 => CFG_REP_REQ_XXX,                          // post external links
         //  4 => CFG_REP_REQ_XXX,                          // no captcha
             5 => CFG_REP_REQ_SUPERVOTE,                    // votes count for more
             9 => CFG_REP_REQ_VOTEMORE_BASE,                // more votes per day
            10 => CFG_REP_REQ_UPVOTE,                       // can upvote
            11 => CFG_REP_REQ_DOWNVOTE,                     // can downvote
            12 => CFG_REP_REQ_REPLY,                        // can reply
         // 13 => CFG_REP_REQ_XXX,                          // avatar border [NYI: checked by js, avatars not in use]
         // 14 => CFG_REP_REQ_XXX,                          // avatar border [NYI: checked by js, avatars not in use]
         // 15 => CFG_REP_REQ_XXX,                          // avatar border [NYI: checked by js, avatars not in use]
         // 16 => CFG_REP_REQ_XXX,                          // avatar border [NYI: checked by js, avatars not in use]
            17 => CFG_REP_REQ_PREMIUM                       // premium status
        );

        asort($req2priv);

        foreach ($req2priv as $id => $val)
            $this->privileges[$id] = array(
                User::getReputation() >= $val,
                Lang::privileges('_privileges', $id),
                $val
            );
    }
}

?>
