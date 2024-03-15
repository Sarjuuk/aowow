<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// the actual text is an article accessed by type + typeId
// menuId 2: More g_initPath()
//  tabid 2: More g_initHeader()

class MorePage extends GenericPage
{
    protected $articleUrl    = '';
    protected $tabsTitle     = '';
    protected $privReqPoints = '';
    protected $forceTabs     = true;
    protected $lvTabs        = [];
    protected $privileges    = [];

    protected $tpl           = 'list-page-generic';
    protected $path          = [];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_NONE;
    protected $scripts       = [[SC_JS_FILE, 'js/swfobject.js']];

    private   $page          = [];
    private   $req2priv      = array(
             1 => CFG_REP_REQ_COMMENT,                      // write comments
             2 => 0,                                        // NYI post external links
             4 => 0,                                        // NYI no captcha
             5 => CFG_REP_REQ_SUPERVOTE,                    // votes count for more
             9 => CFG_REP_REQ_VOTEMORE_BASE,                // more votes per day
            10 => CFG_REP_REQ_UPVOTE,                       // can upvote
            11 => CFG_REP_REQ_DOWNVOTE,                     // can downvote
            12 => CFG_REP_REQ_REPLY,                        // can reply
            13 => 0,                                        // avatar border [NYI: checked by js, avatars not in use]
            14 => 0,                                        // avatar border [NYI: checked by js, avatars not in use]
            15 => 0,                                        // avatar border [NYI: checked by js, avatars not in use]
            16 => 0,                                        // avatar border [NYI: checked by js, avatars not in use]
            17 => CFG_REP_REQ_PREMIUM                       // premium status
        );

    private   $validPages   = array(                        // [tabId, path[, subPaths]]
        'whats-new'     => [2, [2,  7]],
        'searchbox'     => [2, [2, 16]],
        'tooltips'      => [2, [2, 10]],
        'faq'           => [2, [2,  3]],
        'aboutus'       => [2, [2,  0]],
        'searchplugins' => [2, [2,  8]],
        'help'          => [2, [2, 13], ['commenting-and-you', 'modelviewer', 'screenshots-tips-tricks', 'stat-weighting', 'talent-calculator', 'item-comparison', 'profiler', 'markup-guide']],
        'reputation'    => [1, [3, 10]],
        'privilege'     => [1, [3, 10], [1, 2, 4, 5, 9, 10, 11, 12, 13, 14, 15, 16, 17]],
        'privileges'    => [1, [3, 10, 0]],
        'top-users'     => [1, [3, 11]]
    );

    public function __construct($pageCall, $subPage)
    {
        parent::__construct($pageCall, $subPage);

        // chack if page is valid
        if (isset($this->validPages[$pageCall]))
        {
            $pageData = $this->validPages[$pageCall];

            $this->tabId = $pageData[0];
            $this->path  = $pageData[1];
            $this->page  = [$pageCall, $subPage];

            if ($subPage && isset($pageData[2]))
            {
                $exists = array_search($subPage, $pageData[2]);
                if ($exists === false)
                    $this->error();

                if (is_numeric($subPage))
                    $this->articleUrl = $pageCall.'='.$subPage;
                else
                    $this->articleUrl = $subPage;

                $this->path[] = $subPage;
                $this->name   = Lang::main('moreTitles', $pageCall, $subPage);
            }
            else
            {
                $this->articleUrl = $pageCall;
                $this->name = Lang::main('moreTitles', $pageCall);
            }
        }
        else
            $this->error();

        // order by requirement ASC
        asort($this->req2priv);
    }

    protected function generateContent()
    {
        switch ($this->page[0])
        {
            case 'reputation':
                $this->handleReputationPage();
                return;
            case 'privileges':
                $this->handlePrivilegesPage();
                return;
            case 'privilege':
                $this->tpl = 'privilege';
                $this->privReqPoints = sprintf(Lang::privileges('reqPoints'), Lang::nf($this->req2priv[$this->page[1]]));
                return;
            case 'top-users':
                $this->handleTopUsersPage();
                return;
            default:
                return;
        }
    }

    protected function postArticle(string &$txt) : void
    {
        if ($this->page[0] != 'reputation' &&
            $this->page[0] != 'privileges' &&
            $this->page[0] != 'privilege')
            return;

        $consts = get_defined_constants(true);
        foreach ($consts['user'] as $k => $v)
        {
            if (strstr($k, 'CFG_REP_'))
                $txt = str_replace($k, Lang::nf($v), $txt);
            else if ($k == 'CFG_USER_MAX_VOTES' || $k == 'CFG_BOARD_URL')
                $txt = str_replace($k, $v, $txt);
        }
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
            $this->lvTabs[] = ['reputationhistory', array(
                'id'   => 'reputation-history',
                'name' => '$LANG.reputationhistory',
                'data' => $repData
            )];
        }
    }

    private function handlePrivilegesPage()
    {
        $this->tpl        = 'privileges';
        $this->privileges = [];

        foreach ($this->req2priv as $id => $val)
            if ($val)
                $this->privileges[$id] = array(
                    User::getReputation() >= $val,
                    Lang::privileges('_privileges', $id),
                    $val
                );
    }

    private function handleTopUsersPage()
    {
        $tabs = array(
            [0,              'top-users-alltime', '$LANG.alltime_stc'  ],
            [time() - MONTH, 'top-users-monthly', '$LANG.lastmonth_stc'],
            [time() - WEEK,  'top-users-weekly',  '$LANG.lastweek_stc' ]
        );

        $nullFields = array(
            'uploads' => 0,
            'posts'   => 0,
            'gold'    => 0,
            'silver'  => 0,
            'copper'  => 0
        );

        foreach ($tabs as [$t, $tabId, $tabName])
        {
            // stuff received
            $res = DB::Aowow()->select('
                SELECT
                    a.id AS ARRAY_KEY,
                    a.displayName AS username,
                    a.userGroups AS `groups`,
                    a.joinDate AS creation,
                    SUM(r.amount) AS reputation,
                    SUM(IF(r.`action` = 3, 1, 0)) AS comments,
                    SUM(IF(r.`action` = 6, 1, 0)) AS screenshots,
                    SUM(IF(r.`action` = 9, 1, 0)) AS reports
                FROM ?_account_reputation r
                JOIN ?_account a ON a.id = r.userId
                {WHERE r.date > ?d}
                GROUP BY a.id
                ORDER BY reputation DESC
                LIMIT ?d
            ', $t ?: DBSIMPLE_SKIP, CFG_SQL_LIMIT_SEARCH);

            $data = [];
            if ($res)
            {
                // stuff given
                $votes = DB::Aowow()->selectCol(
                    'SELECT sourceB AS ARRAY_KEY, SUM(1) FROM ?_account_reputation WHERE action IN (4, 5) AND sourceB IN (?a) {AND date > ?d} GROUP BY sourceB',
                    array_keys($res),
                    $t ?: DBSIMPLE_SKIP
                );
                foreach ($res as $uId => &$r)
                {
                    $r['creation'] = date('c', $r['creation']);
                    $r['votes']    = empty($votes[$uId]) ? 0 : $votes[$uId];
                    $r = array_merge($r, $nullFields);
                }

                $data = array_values($res);
            }

            $this->lvTabs[] = ['topusers', array(
                'hiddenCols'  => ['achievements', 'posts', 'uploads'],
                'visibleCols' => ['created'],
                'name'        => '$LANG.lastweek_stc',
                'name'        => $tabName,
                'id'          => $tabId,
                'data'        => $data
            )];
        }
    }
}

?>
