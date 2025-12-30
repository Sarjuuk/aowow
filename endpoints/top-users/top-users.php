<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class TopusersBaseResponse extends TemplateResponse
{
    private const /* int */ MAX_RESULTS = 500;

    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'top-users';
    protected ?int    $activeTab  = parent::TAB_COMMUNITY;
    protected  array  $breadcrumb = [3, 11];

    public function __construct(string $pageParam)
    {
        parent::__construct($pageParam);

        if ($pageParam)
            $this->generateError();
    }

    protected function generate() : void
    {
        $this->h1 = Lang::main('moreTitles', $this->pageName);

        array_unshift($this->title, $this->h1);

        $tabs = array(
            [0,              'top-users-alltime', '$LANG.alltime_stc'  ],
            [time() - MONTH, 'top-users-monthly', '$LANG.lastmonth_stc'],
            [time() - WEEK,  'top-users-weekly',  '$LANG.lastweek_stc' ]
        );

        // expected by javascript but metrics are not used by us
        $nullFields = array(
            'uploads' => 0,                                 // wow client cache uploads
            'posts'   => 0,                                 // forum posts
            'gold'    => 0,                                 // site achievements
            'silver'  => 0,
            'copper'  => 0
        );

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], __forceTabs: true);

        foreach ($tabs as [$time, $tabId, $tabName])
        {
            // stuff received
            $res = DB::Aowow()->select(
               'SELECT   a.`id` AS ARRAY_KEY, a.`username`, a.`userGroups` AS "groups", a.`joinDate` AS "creation",
                         SUM(r.`amount`) AS "reputation", SUM(IF(r.`action` = ?d, 1, 0)) AS "comments", SUM(IF(r.`action` = ?d, 1, 0)) AS "screenshots", SUM(IF(r.`action` = ?d, 1, 0)) AS "reports"
                FROM     ?_account_reputation r
                JOIN     ?_account a ON a.`id` = r.`userId`
              { WHERE    r.`date` > ?d }
                GROUP BY a.`id`
                ORDER BY reputation DESC
                LIMIT    ?d',
                SITEREP_ACTION_COMMENT, SITEREP_ACTION_SUBMIT_SCREENSHOT, SITEREP_ACTION_GOOD_REPORT,
                $time ?: DBSIMPLE_SKIP, self::MAX_RESULTS
            );

            $data = [];
            if ($res)
            {
                // stuff given
                $votes = DB::Aowow()->selectCol('SELECT `sourceB` AS ARRAY_KEY, SUM(1) FROM ?_account_reputation WHERE `action` IN (?a) AND `sourceB` IN (?a) { AND `date` > ?d } GROUP BY `sourceB`',
                    [SITEREP_ACTION_UPVOTED, SITEREP_ACTION_DOWNVOTED], array_keys($res), $time ?: DBSIMPLE_SKIP
                );
                foreach ($res as $uId => &$r)
                {
                    $r['creation'] = date('c', $r['creation']);
                    $r['votes']    = empty($votes[$uId]) ? 0 : $votes[$uId];
                    $r += $nullFields;
                }

                $data = $res;
            }

            $this->lvTabs->addListviewTab(new Listview(array(
                'hiddenCols'  => ['achievements', 'posts', 'uploads', 'reports'],
                'visibleCols' => ['created'],
                'name'        => '$LANG.lastweek_stc',
                'name'        => $tabName,
                'id'          => $tabId,
                'data'        => $data
            ), 'topusers'));
        }

        parent::generate();
    }
}

?>
