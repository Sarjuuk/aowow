<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class UserPage extends GenericPage
{
    protected $tpl      = 'user';
    protected $js       = ['user.js', 'profile.js'];
    protected $css      = [['path' => 'Profiler.css']];
    protected $mode     = CACHE_TYPE_NONE;

    protected $typeId   = 0;
    protected $pageName = '';

    public function __construct($pageCall, $pageParam)
    {
        parent::__construct($pageCall, $pageParam);

        if ($pageParam)
        {
            // todo: check if account is disabled or something
            if ($user = DB::Aowow()->selectRow('SELECT a.id, a.user, a.displayName, a.consecutiveVisits, a.userGroups, a.avatar, a.title, a.description, a.joinDate, a.prevLogin, IFNULL(SUM(ar.amount), 0) AS sumRep FROM ?_account a LEFT JOIN ?_account_reputation ar ON a.id = ar.userId WHERE a.user = ? GROUP BY a.id', $pageParam))
                $this->user = $user;
            else
                $this->notFound(sprintf(Lang::user('notFound'), $pageParam));
        }
        else if (User::$id)
        {
            header('Location: ?user='.User::$displayName, true, 302);
            die();
        }
        else
            $this->forwardToSignIn('user');
   }

    protected function generateContent()
    {
        /***********/
        /* Infobox */
        /***********/

        $infobox = $contrib = $groups = [];
        foreach (Lang::account('groups') as $idx => $key)
            if ($idx >= 0 && $this->user['userGroups'] & (1 << $idx))
                $groups[] = (!fMod(count($groups) + 1, 3) ? '[br]' : null).Lang::account('groups', $idx);

        $infobox[] = Lang::user('joinDate'). Lang::main('colon').'[tooltip name=joinDate]'. date('l, G:i:s', $this->user['joinDate']). '[/tooltip][span class=tip tooltip=joinDate]'. date(Lang::main('dateFmtShort'), $this->user['joinDate']). '[/span]';
        $infobox[] = Lang::user('lastLogin').Lang::main('colon').'[tooltip name=lastLogin]'.date('l, G:i:s', $this->user['prevLogin']).'[/tooltip][span class=tip tooltip=lastLogin]'.date(Lang::main('dateFmtShort'), $this->user['prevLogin']).'[/span]';
        $infobox[] = Lang::user('userGroups').Lang::main('colon').($groups ? implode(', ', $groups) : Lang::account('groups', -1));
        $infobox[] = Lang::user('consecVisits').Lang::main('colon').$this->user['consecutiveVisits'];
        $infobox[] = Util::ucFirst(Lang::main('siteRep')).Lang::main('colon').Lang::nf($this->user['sumRep']);

        // contrib -> [url=http://www.wowhead.com/client]Data uploads: n [small]([tooltip=tooltip_totaldatauploads]xx.y MB[/tooltip])[/small][/url]

        $co = DB::Aowow()->selectRow(
            'SELECT COUNT(DISTINCT c.id) AS sum, SUM(IFNULL(cr.value, 0)) AS nRates FROM ?_comments c LEFT JOIN ?_comments_rates cr ON cr.commentId = c.id AND cr.userId <> 0 WHERE c.replyTo = 0 AND c.userId = ?d',
            $this->user['id']
        );
        if ($co['sum'])
            $contrib[] = Lang::user('comments').Lang::main('colon').$co['sum'].($co['nRates'] ? ' [small]([tooltip=tooltip_totalratings]'.$co['nRates'].'[/tooltip])[/small]' : null);

        $ss = DB::Aowow()->selectRow('SELECT COUNT(*) AS sum, SUM(IF(status & ?d, 1, 0)) AS nSticky, SUM(IF(status & ?d, 0, 1)) AS nPending FROM ?_screenshots WHERE userIdOwner = ?d AND (status & ?d) = 0',
            CC_FLAG_STICKY,
            CC_FLAG_APPROVED,
            $this->user['id'],
            CC_FLAG_DELETED
        );
        if ($ss['sum'])
        {
            $buff = [];
            if ($ss['nSticky'] || $ss['nPending'])
            {
                if ($normal = ($ss['sum'] - $ss['nSticky'] - $ss['nPending']))
                    $buff[] = '[tooltip=tooltip_normal]'.$normal.'[/tooltip]';

                if ($ss['nSticky'])
                    $buff[] = '[tooltip=tooltip_sticky]'.$ss['nSticky'].'[/tooltip]';

                if ($ss['nPending'])
                    $buff[] = '[tooltip=tooltip_pending]'.$ss['nPending'].'[/tooltip]';
            }

            $contrib[] = Lang::user('screenshots').Lang::main('colon').$ss['sum'].($buff ? ' [small]('.implode($buff, ' + ').')[/small]' : null);
        }

        $vi = DB::Aowow()->selectRow('SELECT COUNT(id) AS sum, SUM(IF(status & ?d, 1, 0)) AS nSticky, SUM(IF(status & ?d, 0, 1)) AS nPending FROM ?_videos WHERE userIdOwner = ?d AND (status & ?d) = 0',
            CC_FLAG_STICKY,
            CC_FLAG_APPROVED,
            $this->user['id'],
            CC_FLAG_DELETED
        );
        if ($vi['sum'])
        {
            $buff = [];
            if ($vi['nSticky'] || $vi['nPending'])
            {
                if ($normal = ($vi['sum'] - $vi['nSticky'] - $vi['nPending']))
                    $buff[] = '[tooltip=tooltip_normal]'.$normal.'[/tooltip]';

                if ($vi['nSticky'])
                    $buff[] = '[tooltip=tooltip_sticky]'.$vi['nSticky'].'[/tooltip]';

                if ($vi['nPending'])
                    $buff[] = '[tooltip=tooltip_pending]'.$vi['nPending'].'[/tooltip]';
            }

            $contrib[] = Lang::user('videos').Lang::main('colon').$vi['sum'].($buff ? ' [small]('.implode($buff, ' + ').')[/small]' : null);
        }

        // contrib -> Forum posts: 5769 [small]([tooltip=topics]579[/tooltip] + [tooltip=replies]5190[/tooltip])[/small]

        $this->infobox = '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]';

        if ($contrib)
            $this->contributions = '[ul][li]'.implode('[/li][li]', $contrib).'[/li][/ul]';


        /****************/
        /* Main Content */
        /****************/

        $this->name = $this->user['title'] ? $this->user['displayName'].'&nbsp;&lt;'.$this->user['title'].'&gt;' : sprintf(Lang::user('profileTitle'), $this->user['displayName']);

        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = [];
        $this->forceTabs = true;

        // [unused] Site Achievements

        // Reputation changelog (params only for comment-events)
        if ($repData = DB::Aowow()->select('SELECT action, amount, date AS \'when\', IF(action IN (3, 4, 5), sourceA, 0) AS param FROM ?_account_reputation WHERE userId = ?d', $this->user['id']))
        {
            foreach ($repData as &$r)
                $r['when'] = date(Util::$dateFormatInternal, $r['when']);

            $this->lvTabs[] = ['reputationhistory', ['data' => $repData]];
        }

        // Comments
        if ($_ = CommunityContent::getCommentPreviews(['user' => $this->user['id'], 'replies' => false], $nFound))
        {
            $tabData = array(
                'data'           => $_,
                'hiddenCols'     => ['author'],
                'onBeforeCreate' => '$Listview.funcBox.beforeUserComments',
                '_totalCount'    => $nFound
            );

            if ($nFound > CFG_SQL_LIMIT_DEFAULT)
            {
                $tabData['name'] = '$LANG.tab_latestcomments';
                $tabData['note'] = '$$WH.sprintf(LANG.lvnote_usercomments, '.$nFound.')';
            }

            $this->lvTabs[] = ['commentpreview', $tabData];
        }

        // Comment Replies
        if ($_ = CommunityContent::getCommentPreviews(['user' => $this->user['id'], 'replies' => true], $nFound))
        {
            $tabData = array(
                'data'           => $_,
                'hiddenCols'     => ['author'],
                'onBeforeCreate' => '$Listview.funcBox.beforeUserComments',
                '_totalCount'    => $nFound
            );

            if ($nFound > CFG_SQL_LIMIT_DEFAULT)
            {
                $tabData['name'] = '$LANG.tab_latestreplies';
                $tabData['note'] = '$$WH.sprintf(LANG.lvnote_userreplies, '.$nFound.')';
            }

            $this->lvTabs[] = ['replypreview', $tabData];
        }

        // Screenshots
        if ($_ = CommunityContent::getScreenshots(-$this->user['id'], 0, $nFound))
        {
            $tabData = array(
                'data'        => $_,
                '_totalCount' => $nFound
            );

            if ($nFound > CFG_SQL_LIMIT_DEFAULT)
            {
                $tabData['name'] = '$LANG.tab_latestscreenshots';
                $tabData['note'] = '$$WH.sprintf(LANG.lvnote_userscreenshots, '.$nFound.')';
            }

            $this->lvTabs[] = ['screenshot', $tabData];
        }

        // Videos
        if ($_ = CommunityContent::getVideos(-$this->user['id'], 0, $nFound))
        {
            $tabData = array(
                'data'        => $_,
                '_totalCount' => $nFound
            );

            if ($nFound > CFG_SQL_LIMIT_DEFAULT)
            {
                $tabData['name'] = '$LANG.tab_latestvideos';
                $tabData['note'] = '$$WH.sprintf(LANG.lvnote_uservideos, '.$nFound.')';
            }

            $this->lvTabs[] = ['video', $tabData];
        }

        // forum -> latest topics  [unused]

        // forum -> latest replies [unused]

        // Characters [todo]
        $this->user['characterData'] = [];
        /*
            us_addCharactersTab([
                {
                    id:763,
                    "name":"Lilywhite",
                    "achievementpoints":"0",
                    "guild":"whatever",
                    "guildrank":"0",
                    "realm":"draenor",
                    "realmname":"Draenor",
                    "battlegroup":"cyclone",
                    "battlegroupname":"Cyclone",
                    "region":"us",
                    "level":"10",
                    "race":"7",
                    "gender":"0",
                    "classs":"1",
                    "faction":"0",
                    "gearscore":"0",
                    "talenttree1":"0",
                    "talenttree2":"0",
                    "talenttree3":"0",
                    "talentspec":0,
                    "published":1,
                    "pinned":0
                }
            ]);
        */

        // Profiles [todo]
        $this->user['profileData'] = [];
    }

    protected function generateTitle()
    {
        array_unshift($this->title, sprintf(Lang::user('profileTitle'), $this->user['displayName']));
    }

    protected function generatePath() { }
}

?>
