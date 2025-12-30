<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class UserBaseResponse extends TemplateResponse
{
    protected string $template = 'user';
    protected string $pageName = 'user';

    protected array  $scripts  = array(
        [SC_JS_FILE,  'js/user.js'],
        [SC_JS_FILE,  'js/profile.js'],
        [SC_CSS_FILE, 'css/Profiler.css']
    );

    // for PageTemplate
    public ?InfoboxMarkup $infobox;
    public ?InfoboxMarkup $contributions    = null;
    public ?Markup        $description      = null;
    public  array         $userIcon         = [];
    public  string        $username         = '';
    public  array         $charactersLvData = [];
    public  array         $profilesLvData   = [];

    private array $user = [];

    public function __construct($pageParam)
    {
        parent::__construct($pageParam);

        if (!$pageParam && User::isLoggedIn())
            $this->forward('?user='.User::$username);

        if (!$pageParam)
            $this->forwardToSignIn('user');

        if ($user = DB::Aowow()->selectRow('SELECT a.`id`, a.`username`, a.`consecutiveVisits`, a.`userGroups`, a.`avatar`, a.`avatarborder`, a.`wowicon`, a.`title`, a.`description`, a.`joinDate`, a.`prevLogin`, IFNULL(SUM(ar.`amount`), 0) AS "sumRep", a.`prevIP`, a.`email` FROM ?_account a LEFT JOIN ?_account_reputation ar ON a.`id` = ar.`userId` WHERE LOWER(a.`username`) = LOWER(?) GROUP BY a.`id`', $pageParam))
            $this->user = $user;
        else
            $this->generateNotFound(Lang::user('notFound', [$pageParam]));

        // do not display system account
        if (!$this->user['id'])
            $this->generateNotFound(Lang::user('notFound', [$pageParam]));
    }

    protected function generate() : void
    {
        /*********/
        /* Title */
        /*********/

        array_unshift($this->title, Lang::user('profileTitle', [$this->user['username']]));


        /***********/
        /* Infobox */
        /***********/

        $infobox = $contrib = $groups = [];

        foreach (Lang::account('groups') as $idx => $grp)
            if ($idx >= 0 && $this->user['userGroups'] & (1 << $idx))
                $groups[] = (!fMod(count($groups) + 1, 3) ? '[br]' : '').$grp;

        if (User::isInGroup(U_GROUP_STAFF))
        {
            $infobox[] = Lang::account('lastIP'). $this->user['prevIP'];
            $infobox[] = Lang::account('email') . Lang::main('colon') . $this->user['email'];
        }

        if ($this->user['joinDate'])
            $infobox[] = Lang::user('joinDate') . '[tooltip name=joinDate]'. date('l, G:i:s', $this->user['joinDate']). '[/tooltip][span class=tip tooltip=joinDate]'.(new DateTime())->formatDate($this->user['joinDate']). '[/span]';
        if ($this->user['prevLogin'])
            $infobox[] = Lang::user('lastLogin') . '[tooltip name=lastLogin]'.date('l, G:i:s', $this->user['prevLogin']).'[/tooltip][span class=tip tooltip=lastLogin]'.(new DateTime())->formatDate($this->user['prevLogin']).'[/span]';
        if ($groups)
            $infobox[] = Lang::user('userGroups') . implode(', ', $groups);

        $infobox[] = Lang::user('consecVisits'). $this->user['consecutiveVisits'];

        if ($this->user['sumRep'])
            $infobox[] = Lang::main('siteRep') . Lang::nf($this->user['sumRep']);

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF], 'infobox-contents0');

        if ($_ = $this->getCommentStats())
            $contrib[] = $_;

        if ($_ = $this->getScreenshotStats())
            $contrib[] = $_;

        if ($_ = $this->getVideoStats())
            $contrib[] = $_;

        if ($_ = $this->getForumStats())
            $contrib[] = $_;

        // $contrib[] = [url=http://www.wowhead.com/client]Data uploads: n [small]([tooltip=tooltip_totaldatauploads]xx.y MB[/tooltip])[/small][/url]

        if ($contrib)
            $this->contributions = new InfoboxMarkup($contrib, ['allow' => Markup::CLASS_STAFF], 'infobox-contents1');


        /****************/
        /* Main Content */
        /****************/

        $this->h1 = $this->user['title'] ? $this->user['username'].'&nbsp;&lt;'.$this->user['title'].'&gt;' : Lang::user('profileTitle', [$this->user['username']]);

        if ($this->user['avatar'])
        {
            $avatarMore = match ((int)$this->user['avatar'])
            {
                1       => $this->user['wowicon'],
                2       => DB::Aowow()->selectCell('SELECT `id` FROM ?_account_avatars WHERE `current` = 1 AND `userId` = ?d', $this->user['id']),
                default => ''
            };

            if (!($this->user['userGroups'] & U_GROUP_PREMIUM))
                $this->user['avatarborder'] = 2;

            $this->userIcon = array(                        // JS: Icon.createUser()
                $this->user['avatar'],                      // avatar: 1(iconString), 2(customId)
                $avatarMore,                                // avatarMore: iconString or customId
                IconElement::SIZE_MEDIUM,                   // size: (always medium)
                null,                                       // url: (always null)
                $this->user['avatarborder'],                // premiumLevel: affixes css class ['-premium', '-gold', '', '-premiumred', '-red']
                false,                                      // noBorder: always false
                '$Icon.getPrivilegeBorder('.$this->user['sumRep'].')' // reputationLevel: calculated in js from passed rep points
            );
        }

        $this->username = $this->user['username'];

        if ($this->user['description'])                                 // seen CLASS_STAFF, but wouldn't dare.. filtered for restricted tags before sent?
            $this->description = new Markup($this->user['description'], ['allow' => ($this->user['userGroups'] & U_GROUP_PREMIUM) ? Markup::CLASS_PREMIUM : Markup::CLASS_USER], 'description-generic');


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // [unused] Site Achievements

        // Reputation changelog (params only for comment-events)
        if (User::$id == $this->user['id'] || User::isInGroup(U_GROUP_MODERATOR))
            if ($repData = DB::Aowow()->select('SELECT `action`, `amount`, `date` AS "when", IF(`action` IN (3, 4, 5), `sourceA`, 0) AS "param" FROM ?_account_reputation WHERE `userId` = ?d', $this->user['id']))
            {
                array_walk($repData, fn(&$x) => $x['when'] = date(Util::$dateFormatInternal, $x['when']));
                $this->lvTabs->addListviewTab(new Listview(['data' => $repData], 'reputationhistory'));
            }

        // Comments
        if ($_ = CommunityContent::getCommentPreviews(['user' => $this->user['id'], 'comments' => true], $nFound, resultLimit: Listview::DEFAULT_SIZE))
        {
            $tabData = array(
                'data'           => $_,
                'hiddenCols'     => ['author'],
                'onBeforeCreate' => '$Listview.funcBox.beforeUserComments',
                '_totalCount'    => $nFound
            );

            if ($nFound > Listview::DEFAULT_SIZE)
            {
                $tabData['name'] = '$LANG.tab_latestcomments';
                $tabData['note'] = '$$WH.sprintf(LANG.lvnote_usercomments, '.$nFound.')';
            }

            $this->lvTabs->addListviewTab(new Listview($tabData, 'commentpreview'));
        }

        // Comment Replies
        if ($_ = CommunityContent::getCommentPreviews(['user' => $this->user['id'], 'replies' => true], $nFound, resultLimit: Listview::DEFAULT_SIZE))
        {
            $tabData = array(
                'data'           => $_,
                'hiddenCols'     => ['author'],
                'onBeforeCreate' => '$Listview.funcBox.beforeUserComments',
                '_totalCount'    => $nFound
            );

            if ($nFound > Listview::DEFAULT_SIZE)
            {
                $tabData['name'] = '$LANG.tab_latestreplies';
                $tabData['note'] = '$$WH.sprintf(LANG.lvnote_userreplies, '.$nFound.')';
            }

            $this->lvTabs->addListviewTab(new Listview($tabData, 'replypreview'));
        }

        // Screenshots
        if ($_ = CommunityContent::getScreenshots(-$this->user['id'], 0, $nFound, resultLimit: Listview::DEFAULT_SIZE))
        {
            $tabData = array(
                'data'        => $_,
                '_totalCount' => $nFound
            );

            if ($nFound > Listview::DEFAULT_SIZE)
            {
                $tabData['name'] = '$LANG.tab_latestscreenshots';
                $tabData['note'] = '$$WH.sprintf(LANG.lvnote_userscreenshots, '.$nFound.')';
            }

            $this->lvTabs->addListviewTab(new Listview($tabData, 'screenshot'));
        }

        // Videos
        if ($_ = CommunityContent::getVideos(-$this->user['id'], 0, $nFound, resultLimit: Listview::DEFAULT_SIZE))
        {
            $tabData = array(
                'data'        => $_,
                '_totalCount' => $nFound
            );

            if ($nFound > Listview::DEFAULT_SIZE)
            {
                $tabData['name'] = '$LANG.tab_latestvideos';
                $tabData['note'] = '$$WH.sprintf(LANG.lvnote_uservideos, '.$nFound.')';
            }

            $this->lvTabs->addListviewTab(new Listview($tabData, 'video'));
        }

        // forum -> latest topics  [unused]

        // forum -> latest replies [unused]

        if (Cfg::get('PROFILER_ENABLE'))
        {
            $conditions = [['user', $this->user['id']]];
            if (User::$id != $this->user['id'] && !User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
                $conditions[] = ['cuFlags', PROFILER_CU_PUBLISHED, '&'];
            if (!User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
                $conditions[] = ['deleted', 0];

            $profiles = new LocalProfileList($conditions);
            if (!$profiles->error)
            {
                $this->addDataLoader('weight-presets');

                if ($prof = $profiles->getListviewData(PROFILEINFO_PROFILE | PROFILEINFO_USER))
                    $this->profilesLvData = array_values($prof);
            }

            $conditions = [['ap.accountId', $this->user['id']]];
            if (User::$id != $this->user['id'] && !User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
                $conditions[] = ['ap.extraFlags', PROFILER_CU_PUBLISHED, '&'];

            $characters = new LocalProfileList($conditions);
            if (!$characters->error)
            {
                $this->addDataLoader('weight-presets');

                if ($chars = $characters->getListviewData(PROFILEINFO_CHARACTER | PROFILEINFO_USER))
                    $this->charactersLvData = array_values($chars);
            }

            // signatures
            /*  $this->lvTabs->addListviewTab(new Listview(array(
             *      'id'             => 'signatures',
             *      'name'           => '$LANG.tab_signatures',
             *      'hiddenCols'     => ['name','faction','location','guild'],
             *      'extraCols'      => ['$Listview.extraCols.signature'],
             *      'onBeforeCreate' => '$Listview.funcBox.beforeUserSignatures',
             *      'data'           => [ ProfileList->getListviewData() ] // no extra signature related data observed
             *  ), 'profile'));
             */
        }

        // My Guides
        $guides = new GuideList(['status', [GuideMgr::STATUS_APPROVED, GuideMgr::STATUS_ARCHIVED]], ['userId', $this->user['id']]);
        if (!$guides->error)
        {
            $this->lvTabs->addListviewTab(new Listview(array(
                'data'       => $guides->getListviewData(),
                'hiddenCols' => ['patch']
            ), GuideList::$brickFile));
        }

        parent::generate();
    }

    private function getCommentStats() : ?string
    {
        $co = DB::Aowow()->selectRow(
           'SELECT COUNT(DISTINCT c.`id`) AS "0", SUM(IFNULL(ur.`value`, 0)) AS "1" FROM ?_comments c LEFT JOIN ?_user_ratings ur ON ur.`entry` = c.`id` AND ur.`type` = ?d AND ur.`userId` <> 0 WHERE c.`replyTo` = 0 AND c.`userId` = ?d',
            RATING_COMMENT, $this->user['id']
        );

        if (!$co)
            return null;

        [$sum, $nRatings] = $co;

        if (!$sum)
            return null;

        return Lang::user('comments').$sum.($nRatings ? ' [small]([tooltip=tooltip_totalratings]'.$nRatings.'[/tooltip])[/small]' : '');
    }

    private function getScreenshotStats() : ?string
    {
        $ss = DB::Aowow()->selectRow(
           'SELECT COUNT(*) AS "0", SUM(IF(`status` & ?d, 1, 0)) AS "1", SUM(IF(`status` & ?d, 0, 1)) AS "2" FROM ?_screenshots WHERE `userIdOwner` = ?d AND (`status` & ?d) = 0',
            CC_FLAG_STICKY, CC_FLAG_APPROVED, $this->user['id'], CC_FLAG_DELETED
        );

        if (!$ss)
            return null;

        [$sum, $nSticky, $nPending] = $ss;

        if (!$sum)
            return null;

        $buff = [];
        if ($nSticky || $nPending)
        {
            if ($normal = ($sum - $nSticky - $nPending))
                $buff[] = '[tooltip=tooltip_normal]'.$normal.'[/tooltip]';

            if ($nSticky)
                $buff[] = '[tooltip=tooltip_sticky]'.$nSticky.'[/tooltip]';

            if ($nPending)
                $buff[] = '[tooltip=tooltip_pending]'.$nPending.'[/tooltip]';
        }

        return  Lang::user('screenshots').$sum.($buff ? ' [small]('.implode(' + ', $buff).')[/small]' : '');
    }

    private function getVideoStats() : ?string
    {
        $vi = DB::Aowow()->selectRow(
           'SELECT COUNT(*) AS "0", SUM(IF(`status` & ?d, 1, 0)) AS "1", SUM(IF(`status` & ?d, 0, 1)) AS "2" FROM ?_videos WHERE `userIdOwner` = ?d AND (`status` & ?d) = 0',
            CC_FLAG_STICKY, CC_FLAG_APPROVED, $this->user['id'], CC_FLAG_DELETED
        );

        if (!$vi)
            return null;

        [$sum, $nSticky, $nPending] = $vi;

        if (!$sum)
            return null;

        $buff = [];
        if ($nSticky || $nPending)
        {
            if ($normal = ($sum - $nSticky - $nPending))
                $buff[] = '[tooltip=tooltip_normal]'.$normal.'[/tooltip]';

            if ($nSticky)
                $buff[] = '[tooltip=tooltip_sticky]'.$nSticky.'[/tooltip]';

            if ($nPending)
                $buff[] = '[tooltip=tooltip_pending]'.$nPending.'[/tooltip]';
        }

        return Lang::user('videos').$sum.($buff ? ' [small]('.implode(' + ', $buff).')[/small]' : '');
    }

    private function getForumStats() : ?string
    {
        $fo = null;                                         // some query

        if (!$fo)
            return null;

        [$nTopics, $nReplies] = $fo;

        $buff = [];
        if ($nTopics)
            $buff[] = '[tooltip=topics]'.$nTopics.'[/tooltip]';

        if ($nReplies)
            $buff[] = '[tooltip=replies]'.$nReplies.'[/tooltip]';

        if (!$buff)
            return null;

        return Lang::user('posts').($nTopics + $nReplies).($buff ? ' [small]('.implode(' + ', $buff).')[/small]' : '');
    }
}

?>
