<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');


class AjaxHandler
{
    private $params;
    private $get;
    private $post;

    public function __construct($params)
    {
        $this->params = explode('.', $params);

        foreach ($_POST as $k => $v)
        {
            Util::checkNumeric($v);
            $this->post[$k] = $v;
        }

        foreach ($_GET as $k => $v)
            $this->get[$k] = Util::checkNumeric($v) ? $v : is_string($v) ? trim(urldecode($v)) : $v;
    }

    public function handle($what)
    {
        $f = 'handle'.ucFirst(str_replace(['-', '_'], '', $what));
        if (!$what || !method_exists($this, $f))
            return null;

        return $this->$f();
    }

    /* responses
        header()
    */
    private function handleGotocomment()
    {
        if (empty($this->get['id']))
            return;

        if ($_ = DB::Aowow()->selectRow('SELECT c2.id as zwei, c1.id as eins, IFNULL(c2.id, c1.id) AS id, IFNULL(c2.type, c1.type) AS type, IFNULL(c2.typeId, c1.typeId) AS typeId FROM ?_comments c1 LEFT JOIN ?_comments c2 ON c1.replyTo = c2.id WHERE c1.id = ?d', $this->get['id']))
            header('Location: ?'.Util::$typeStrings[$_['type']].'='.$_['typeId'].'#comments:id='.$_['id'].($_['id'] != $this->get['id'] ? ':reply='.$this->get['id'] : null));
    }

    /* responses
        <string>
    */
    private function handleData()
    {
        if (isset($this->get['locale']) && is_numeric($this->get['locale']))
            User::useLocale($this->get['locale']);

        $result = '';

        // different data can be strung together
        foreach ($this->params as $set)
        {
            // requires valid token to hinder automated access
            if ($set != 'item-scaling')
                if (empty($this->get['t']) || empty($_SESSION['dataKey']) || $this->get['t'] != $_SESSION['dataKey'])
                    continue;

            switch ($set)
            {
                /*  issue on no initial data:
                    when we loadOnDemand, the jScript tries to generate the catg-tree before it is initialized
                    it cant be initialized, without loading the data as empty catg are omitted
                    loading the data triggers the generation of the catg-tree
                */
                case 'factions':
                    $result .= $this->data_loadProfilerData($set);
                    break;
                case 'companions':
                    $result .= $this->data_loadProfilerData($set, '778');
                    break;
                case 'mounts':
                    $result .= $this->data_loadProfilerData($set, '777');
                    break;
                case 'quests':
                    // &partial: im not doing this right
                    // it expects a full quest dump on first lookup but will query subCats again if clicked..?
                    // for now omiting the detail clicks with empty results and just set catg update
                    $catg = isset($this->get['catg']) ? $this->get['catg'] : 'null';
                    if ($catg == 'null')
                        $result .= $this->data_loadProfilerData($set);
                    else if ($this->data_isLoadOnDemand())
                        $result .= "\n\$WowheadProfiler.loadOnDemand('quests', ".$catg.");\n";

                    break;
                case 'recipes':
                    if (!$this->data_isLoadOnDemand() || empty($this->get['skill']))
                        break;

                    $skills = array_intersect(explode(',', $this->get['skill']), [171, 164, 333, 202, 182, 773, 755, 165, 186, 393, 197, 185, 129, 356]);
                    if (!$skills)
                        break;

                    foreach ($skills as $s)
                        Util::loadStaticFile('p-recipes-'.$s, $result, true);

                    Util::loadStaticFile('p-recipes-sec', $result, true);
                    $result .= "\n\$WowheadProfiler.loadOnDemand('recipes', null);\n";

                    break;
                // locale independant
                case 'quick-excludes':                              // generated per character in profiler
                case 'zones':
                case 'weight-presets':
                case 'item-scaling':
                case 'realms':
                case 'statistics':
                    if (!Util::loadStaticFile($set, $result) && CFG_DEBUG)
                        $result .= "alert('could not fetch static data: ".$set."');";

                    $result .= "\n\n";
                    break;
                // localized
                case 'talents':
                    if (isset($this->get['class']))
                        $set .= "-".intVal($this->get['class']);
                case 'pet-talents':
                case 'glyphs':
                case 'gems':
                case 'enchants':
                case 'itemsets':
                case 'pets':
                    if (!Util::loadStaticFile($set, $result, true) && CFG_DEBUG)
                        $result .= "alert('could not fetch static data: ".$set." for locale: ".User::$localeString."');";

                    $result .= "\n\n";
                    break;
                default:
                    break;
            }
        }

        return $result;
    }

    private function handleProfile()
    {
        if (!$this->params)
            return null;

        switch ($this->params[0])
        {
            case 'link':
            case 'unlink':
                $this->profile_handleLink();                // always returns null
                return '';
            case 'pin':
            case 'unpin':
                $this->profile_handlePin();                 // always returns null
                return '';
            case 'public':
            case 'private':
                $this->profile_handlePrivacy();             // always returns null
                return '';
            case 'avatar':
                if ($this->profile_handleAvatar())          // sets an image header
                die();                                      // so it has to die here or another header will be set
            case 'resync':
            case 'status':
                return $this->profile_handleResync($this->params[0] == 'resync');
            case 'save':
                return $this->profile_handleSave();
            case 'delete':
                return $this->profile_handleDelete();
            case 'purge':
                return $this->profile_handlePurge();
            case 'summary':                                 // page is generated by jScript
                return '';                                  // just be empty
            case 'load':
                return $this->profile_handleLoad();
            default:
                return null;
        }
    }

    /* responses
        0: success
        $: silent error
    */
    private function handleCookie()
    {
        if (User::$id && $this->params && !empty($this->get[$this->params[0]]))
            if (DB::Aowow()->query('REPLACE INTO ?_account_cookies VALUES (?d, ?, ?)', User::$id, $this->params[0], $this->get[$this->params[0]]))
                return 0;

        return null;
    }

    /* responses
        0: success
        1: captcha invalid
        2: description too long
        3: reason missing
        7: already reported
        $: prints response
    */
    private function handleContactus()
    {
        $mode = @$this->post['mode'];
        $rsn  = @$this->post['reason'];
        $ua   = @$this->post['ua'];
        $app  = @$this->post['appname'];
        $url  = @$this->post['page'];
        $desc = @$this->post['desc'];

        $subj = @intVal($this->post['id']);

        $contexts = array(
            [1, 2, 3, 4, 5, 6, 7, 8],
            [15, 16, 17, 18, 19, 20],
            [30, 31, 32, 33, 34, 35, 36, 37],
            [45, 46, 47, 48],
            [60, 61],
            [45, 46, 47, 48],
            [45, 46, 48]
        );

        if ($mode === null || $rsn === null || $ua === null || $app === null || $url === null)
            return 'required field missing';

        if (!isset($contexts[$mode]) || !in_array($rsn, $contexts[$mode]))
            return 'mode invalid';

        if (!$desc)
            return 3;

        if (mb_strlen($desc) > 500)
            return 2;

        // check already reported
        $field = User::$id ? 'userId' : 'ip';
        if (DB::Aowow()->selectCell('SELECT 1 FROM ?_reports WHERE `mode` = ?d AND `reason`= ?d AND `subject` = ?d AND ?# = ?', $mode, $rsn, $subj, $field, User::$id ? User::$id : $_SERVER['REMOTE_ADDR']))
            return 7;

        $update = array(
            'userId'      => User::$id,
            'mode'        => $mode,
            'reason'      => $rsn,
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'description' => $desc,
            'userAgent'   => $ua,
            'appName'     => $app,
            'url'         => $url
        );

        if ($subj)
            $update['subject'] = $subj;

        if ($_ = @$this->post['relatedurl'])
            $update['relatedurl'] = $_;

        if ($_ = @$this->post['email'])
            $update['email'] = $_;

        if (DB::Aowow()->query('INSERT INTO ?_reports (?#) VALUES (?a)', array_keys($update), array_values($update)))
            return 0;

        return 'save to db unsuccessful';
    }

    private function handleComment()
    {
        // post sizes
        $_minCmt = 10;
        $_maxCmt = 7500 * (User::isPremium() ? 3 : 1);

        $_minRpl = 15;
        $_maxRpl = 600;

        $result = null;
        /*
            note: return values must be formated as STRICT json!
        */
        switch ($this->params[0])
        {
            case 'add':                                     // i .. have problems believing, that everything uses nifty ajax while adding comments requires a brutal header(Loacation: <wherever>), yet, thats how it is
                if (empty($this->get['typeid']) || empty($this->get['type']) || !isset(Util::$typeStrings[$this->get['type']]))
                    return;                                 // whatever, we cant even send him back

                // trim to max length
                if (!User::isInGroup(U_GROUP_MODERATOR) && mb_strlen($this->post['commentbody']) > $_maxCmt)
                    $this->post['body'] = substr($this->post['body'], 0, $_maxCmt);

                if (User::canComment() && !empty($this->post['commentbody']) && mb_strlen($this->post['commentbody']) >= $_minCmt)
                {
                    if ($postIdx = DB::Aowow()->query('INSERT INTO ?_comments (type, typeId, userId, roles, body, date) VALUES (?d, ?d, ?d, ?d, ?, UNIX_TIMESTAMP())', $this->get['type'], $this->get['typeid'], User::$id, User::$groups, $this->post['commentbody']))
                    {
                        Util::gainSiteReputation(User::$id, SITEREP_ACTION_COMMENT, ['id' => $postIdx]);
                        // every comment starts with a rating of +1 and i guess the simplest thing to do is create a db-entry with the system as owner
                        DB::Aowow()->query('INSERT INTO ?_comments_rates (commentId, userId, value) VALUES (?d, 0, 1)', $postIdx);
                    }
                }

                header('Location: ?'.Util::$typeStrings[$this->get['type']].'='.$this->get['typeid'].'#comments');
                break;
            case 'edit':
                if ((!User::canComment() && !User::isInGroup(U_GROUP_MODERATOR)) || empty($this->get['id']) || empty($this->post['body']))
                    break;

                if (mb_strlen($this->post['body']) < $_minCmt)
                    break;

                // trim to max length
                if (!User::isInGroup(U_GROUP_MODERATOR) && mb_strlen($this->post['body']) > $_maxCmt)
                    $this->post['body'] = substr($this->post['body'], 0, $_maxCmt);

                $update = array(
                    'body'       => $this->post['body'],
                    'editUserId' => User::$id,
                    'editDate'   => time()
                );

                if (User::isInGroup(U_GROUP_MODERATOR))
                {
                    $update['responseBody']   = empty($this->post['response']) ? '' : $this->post['response'];
                    $update['responseUserId'] = empty($this->post['response']) ? 0  : User::$id;
                    $update['responseRoles']  = empty($this->post['response']) ? 0  : User::$groups;
                }

                DB::Aowow()->query('UPDATE ?_comments SET editCount = editCount + 1, ?a WHERE id = ?d', $update, $this->get['id']);
                break;
            case 'delete':                                  // user.js uses GET; global.js uses POST
                if (empty($this->post['id']) && empty($this->get['id']))
                    break;

                DB::Aowow()->query('UPDATE ?_comments SET flags = flags | 0x2, deleteUserId = ?d, deleteDate = UNIX_TIMESTAMP() WHERE id = ?d{ AND userId = ?d}',
                    User::$id,
                    empty($this->post['id']) ? $this->get['id'] : $this->post['id'],
                    User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id
                );

                break;
            case 'undelete':                                // user.js uses GET; global.js uses POST
                if (empty($this->post['id']) && empty($this->get['id']))
                    break;


                DB::Aowow()->query('UPDATE ?_comments SET flags = flags & ~0x2 WHERE id = ?d{ AND userId = deleteUserId AND deleteUserId = ?d}',
                    empty($this->post['id']) ? $this->get['id'] : $this->post['id'],
                    User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id
                );

                break;
            case 'rating':                                  // up/down - distribution
                if (empty($this->get['id']))
                {
                    $result = ['success' => 0];
                    break;
                }

                if ($votes = DB::Aowow()->selectRow('SELECT 1 AS success, SUM(IF(value > 0, value, 0)) AS up, SUM(IF(value < 0, -value, 0)) AS down FROM ?_comments_rates WHERE commentId = ?d GROUP BY commentId', $this->get['id']))
                    return json_encode($votes, JSON_NUMERIC_CHECK);

                $result = ['success' => 1, 'up' => 0, 'down' => 0];
                break;
            case 'vote':                                    // up, down and remove
                if (!User::$id || empty($this->get['id']) || empty($this->get['rating']))
                {
                    $result = ['error' => 1, 'message' => Lang::$main['genericError']];
                    break;
                }

                $target = DB::Aowow()->selectRow('SELECT c.userId AS owner, cr.value FROM ?_comments c LEFT JOIN ?_comments_rates cr ON cr.commentId = c.id AND cr.userId = ?d WHERE c.id = ?d', User::$id, $this->get['id']);
                $val    = User::canSupervote() ? 2 : 1;
                if ($this->get['rating'] < 0)
                    $val *= -1;

                if (User::getCurDailyVotes() <= 0)
                    $result = ['error' => 1, 'message' => Lang::$main['tooManyVotes']];

                else if (!$target || $val != $this->get['rating'])
                    $result = ['error' => 1, 'message' => Lang::$main['genericError']];

                else if (($val > 0 && !User::canUpvote()) || ($val < 0 && !User::canDownvote()))
                    $result = ['error' => 1, 'message' => Lang::$main['bannedRating']];

                if ($result)
                    break;

                $ok = false;
                // old and new have same sign; undo vote (user may have gained/lost access to superVote in the meantime)
                if ($target['value'] && ($target['value'] < 0) == ($val < 0))
                    $ok = DB::Aowow()->query('DELETE FROM ?_comments_rates WHERE commentId = ?d AND userId = ?d', $this->get['id'], User::$id);
                else                                        // replace, because we may be overwriting an old, opposing vote
                    if ($ok = DB::Aowow()->query('REPLACE INTO ?_comments_rates (commentId, userId, value) VALUES (?d, ?d, ?d)', (int)$this->get['id'], User::$id, $val))
                        User::decrementDailyVotes();        // do not refund retracted votes!

                if (!$ok)
                {
                    $result = ['error' => 1, 'message' => Lang::$main['genericError']];
                    break;
                }

                if ($val > 0)                               // gain rep
                    Util::gainSiteReputation($target['owner'], SITEREP_ACTION_UPVOTED, ['id' => $this->get['id'], 'voterId' => User::$id]);
                else if ($val < 0)
                    Util::gainSiteReputation($target['owner'], SITEREP_ACTION_DOWNVOTED, ['id' => $this->get['id'], 'voterId' => User::$id]);

                $result = ['error' => 0];
                break;
            case 'sticky':                                  // toggle flag
                if (empty($this->post['id']) || !User::isInGroup(U_GROUP_MODERATOR))
                    break;

                if (!empty($this->post['sticky']))
                    DB::Aowow()->query('UPDATE ?_comments SET flags = flags |  0x1 WHERE id = ?d', $this->post['id']);
                else
                    DB::Aowow()->query('UPDATE ?_comments SET flags = flags & ~0x1 WHERE id = ?d', $this->post['id']);

                break;
            case 'out-of-date':                             // toggle flag
                if (empty($this->post['id']))
                {
                    $result = 'The comment does not exist.';
                    break;
                }

                $ok = false;
                if (User::isInGroup(U_GROUP_MODERATOR))     // directly mark as outdated
                {
                    if (empty($this->post['remove']))
                        $ok = DB::Aowow()->query('UPDATE ?_comments SET flags = flags |  0x4 WHERE id = ?d', $this->post['id']);
                    else
                        $ok = DB::Aowow()->query('UPDATE ?_comments SET flags = flags & ~0x4 WHERE id = ?d', $this->post['id']);
                }
                else if (User::$id && empty($this->post['reason']) || mb_strlen($this->post['reason']) < 15)
                {
                    $result = 'Your message is too short.';
                    break;
                }
                else if (User::$id)                         // only report as outdated
                {
                    $ok = DB::Aowow()->query(
                        'INSERT INTO ?_reports (userId, mode, reason, subject, ip, description, userAgent, appName) VALUES (?d, 1, 17, ?d, ?, "<automated comment report>", ?, ?)',
                        User::$id,
                        $this->post['id'],
                        $_SERVER['REMOTE_ADDR'],
                        $_SERVER['HTTP_USER_AGENT'],
                        get_browser(null, true)['browser']
                    );
                }

                if ($ok)                                    // this one is very special; as in: completely retarded
                    return 'ok';                            // the script expects the actual characters 'ok' not some string like "ok"

                $result = Lang::$main['genericError'];
                break;
            case 'show-replies':
                $result = empty($this->get['id']) ? [] : CommunityContent::getCommentReplies($this->get['id']);
                break;
            case 'add-reply':                               // also returns all replies on success
                if (!User::canComment())
                    $result = 'You are not allowed to reply.';

                else if (empty($this->post['body']) || mb_strlen($this->post['body']) < $_minRpl || mb_strlen($this->post['body']) > $_maxRpl)
                    $result = 'Your reply has '.mb_strlen(@$this->post['body']).' characters and must have at least '.$_minRpl.' and at most '.$_maxRpl.'.';

                else if (empty($this->post['commentId']) || !DB::Aowow()->selectCell('SELECT 1 FROM ?_comments WHERE id = ?d', $this->post['commentId']))
                    $result = Lang::$main['genericError'];

                else if (DB::Aowow()->query('INSERT INTO ?_comments (`userId`, `roles`, `body`, `date`, `replyTo`) VALUES (?d, ?d, ?, UNIX_TIMESTAMP(), ?d)', User::$id, User::$groups, $this->post['body'], $this->post['commentId']))
                    $result = CommunityContent::getCommentReplies($this->post['commentId']);

                else
                    $result = Lang::$main['genericError'];

                break;
            case 'edit-reply':                              // also returns all replies on success
                if (!User::canComment())
                    $result = 'You are not allowed to reply.';

                else if (empty($this->post['replyId']) || empty($this->post['commentId']))
                    $result = Lang::$main['genericError'];

                else if (empty($this->post['body']) || mb_strlen($this->post['body']) < $_minRpl || mb_strlen($this->post['body']) > $_maxRpl)
                    $result = 'Your reply has '.mb_strlen(@$this->post['body']).' characters and must have at least '.$_minRpl.' and at most '.$_maxRpl.'.';

                if ($result)
                    break;

                $ok = DB::Aowow()->query(
                    'UPDATE ?_comments SET body = ?, editUserId = ?d, editDate = UNIX_TIMESTAMP(), editCount = editCount + 1 WHERE id = ?d AND replyTo = ?d{ AND userId = ?d}',
                    $this->post['body'],
                    User::$id,
                    $this->post['replyId'],
                    $this->post['commentId'],
                    User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id
                );

                $result = $ok ? CommunityContent::getCommentReplies($this->post['commentId']) : Lang::$main['genericError'];
                break;
            case 'detach-reply':
                if (!User::isInGroup(U_GROUP_MODERATOR) || empty($this->post['id']))
                    break;

                DB::Aowow()->query('UPDATE ?_comments c1, ?_comments c2 SET c1.replyTo = 0, c1.type = c2.type, c1.typeId = c2.typeId WHERE c1.replyTo = c2.id AND c1.id = ?d', $this->post['id']);
                break;
            case 'delete-reply':
                if (!User::$id || empty($this->post['id']))
                    break;

                if (DB::Aowow()->query('DELETE FROM ?_comments WHERE id = ?d{ AND userId = ?d}', $this->post['id'], User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id))
                    DB::Aowow()->query('DELETE FROM ?_comments_rates WHERE commentId = ?d', $this->post['id']);

                break;
            case 'flag-reply':
                if (!User::$id || empty($this->post['id']))
                    break;

                DB::Aowow()->query(
                    'INSERT INTO ?_reports (userId, mode, reason, subject, ip, description, userAgent, appName) VALUES (?d, 1, 19, ?d, ?, "<automated commentreply report>", ?, ?)',
                    User::$id,
                    $this->post['id'],
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['HTTP_USER_AGENT'],
                    get_browser(null, true)['browser']
                );

                break;
            case 'upvote-reply':
                if (empty($this->post['id']) || !User::canUpvote())
                    break;

                $ok = DB::Aowow()->query(
                    'INSERT INTO ?_comments_rates (commentId, userId, value) VALUES (?d, ?d, ?d)',
                    $this->post['id'],
                    User::$id,
                    User::canSupervote() ? 2 : 1
                );

                if ($ok)
                    User::decrementDailyVotes();

                break;
            case 'downvote-reply':
                if (empty($this->post['id']) || !User::canUpvote())
                    break;

                $ok = DB::Aowow()->query(
                    'INSERT INTO ?_comments_rates (commentId, userId, value) VALUES (?d, ?d, ?d)',
                    $this->post['id'],
                    User::$id,
                    User::canSupervote() ? -2 : -1
                );

                if ($ok)
                    User::decrementDailyVotes();
        }

        return json_encode($result, JSON_NUMERIC_CHECK);
    }

    private function handleLocale()                         // not sure if this should be here..
    {
        User::setLocale($this->params[0]);
        User::save();

        header('Location: '.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '.'));
    }

    private function handleAccount()
    {
        if (!$this->params || !User::$id)
            return null;

        switch ($this->params[0])
        {
            case 'exclude':
                // profiler completion exclude handler
                // $this->post['groups'] = bitMask of excludeGroupIds when using .. excludeGroups .. duh
                // should probably occur in g_user.excludegroups (dont forget to also set g_users.settings = {})
                return '';
            case 'weightscales':
                if (isset($this->post['save']))
                {
                    if (!isset($this->post['id']))
                    {
                        $res = DB::Aowow()->selectRow('SELECT max(id) as max, count(id) as num FROM ?_account_weightscales WHERE account = ?d', User::$id);
                        if ($res['num'] < 5)            // more or less hard-defined in LANG.message_weightscalesaveerror
                            $this->post['id'] = ++$res['max'];
                        else
                            return 0;
                    }

                    if (DB::Aowow()->query('REPLACE INTO ?_account_weightscales VALUES (?d, ?d, ?, ?)', intVal($this->post['id']), User::$id, $this->post['name'], $this->post['scale']))
                        return $this->post['id'];
                    else
                        return 0;
                }
                else if (isset($this->post['delete']) && isset($this->post['id']))
                    DB::Aowow()->query('DELETE FROM ?_account_weightscales WHERE id = ?d AND account = ?d', intVal($this->post['id']), User::$id);
                else
                    return 0;
        }


        return null;
    }

    /**********/
    /* Helper */
    /**********/

    private function data_isLoadOnDemand()
    {
        return substr(@$this->get['callback'], 0, 29) == '$WowheadProfiler.loadOnDemand';
    }

    private function data_loadProfilerData($file, $catg = 'null')
    {
        $result = '';
        if ($this->data_isLoadOnDemand())
            if (Util::loadStaticFile('p-'.$file, $result, true))
                $result .= "\n\$WowheadProfiler.loadOnDemand('".$file."', ".$catg.");\n";

        return $result;
    }

    private function profile_handleAvatar()                 // image
    {
        // something happened in the last years: those textures do not include tiny icons
        $s    = [/* 'tiny' => 15, */'small' => 18, 'medium' => 36, 'large' => 56];
        $size = empty($this->get['size']) ? 'medium' : $this->get['size'];

        if (empty($this->get['id']) || !preg_match('/^([0-9]+)\.(jpg|gif)$/', $this->get['id'], $matches) || !in_array($size, array_keys($s)))
            return false;

        $id = $matches[1];

        if (file_exists(CWD.'/uploads/avatars/'.$id.'.jpg'))
        {
            $offsetX = $offsetY = 0;

            switch ($size)
            {
                case 'tiny':
                    $offsetX += $s['small'];
                case 'small':
                    $offsetY += $s['medium'];
                case 'medium':
                    $offsetX += $s['large'];
            }

            $src  = imageCreateFromJpeg('uploads/avatars/'.$id.'.jpg');
            $dest = imageCreateTruecolor($s[$size], $s[$size]);

            imagecopymerge($dest, $src, 0, 0, $offsetX, $offsetY, $s[$size], $s[$size], 100);

            header('Content-Type: image/'.$matches[2]);

            if ($matches[2] == 'gif')
                imageGif($dest);
            else
                imageJpeg($dest);

            return true;
        }

        return false;
    }

    private function profile_handlePin($id, $mode)          // (un)favorite
    {
        /*  params
                id: <prId1,prId2,..,prIdN>
                user: <string> [optional]
            return: null
        */
    }

    private function profile_handleLink($id, $mode)         // links char with account
    {
        /*  params
                id: <prId1,prId2,..,prIdN>
                user: <string> [optional]
            return: null
        */
    }

    private function profile_handlePrivacy($id, $mode)      // public visibility
    {
        /*  params
                id: <prId1,prId2,..,prIdN>
                user: <string> [optional]
            return: null
        */
    }

    private function profile_handleResync($initNew = true)  // resync init and status requests
    {
        /*  params
                id: <prId1,prId2,..,prIdN>
                user: <string> [optional]
            return
                null            [onOK]
                int or str      [onError]
        */

        if ($initNew)
            return '1';
        else
        {
            /*
                not all fields are required, if zero they are omitted
                statusCode:
                    0: end the request
                    1: waiting
                    2: working...
                    3: ready; click to view
                    4: error / retry
                errorCode:
                    0: unk error
                    1: char does not exist
                    2: armory gone

                [
                    processId,
                    [StatusCode, timeToRefresh, iCount, errorCode, iNResyncs],
                    [<anotherStatus>]...
                ]
            */
            return '[0, [4, 10000, 1, 2]]';
        }
    }

    private function profile_handleSave()                   // unKill a profile
    {
        /*  params GET
                id: <prId1,prId2,..,prIdN>
            params POST
                name, level, class, race, gender, nomodel, talenttree1, talenttree2, talenttree3, activespec, talentbuild1, glyphs1, talentbuild2, glyphs2, gearscore, icon, public     [always]
                description, source, copy, inv { inventory: array containing itemLinks }                                                                                                [optional]
                }
            return
                int > 0     [profileId, if we came from an armoryProfile create a new one]
                int < 0     [onError]
                str         [onError]
        */

        return 'NYI';
    }

    private function profile_handleDelete()                 // kill a profile
    {
        /*  params
                id: <prId1,prId2,..,prIdN>
            return
                null
        */

        return 'NYI';
    }

    private function profile_handlePurge()                  // removes certain saved information but not the entire character
    {
        /*  params
                id: <prId1,prId2,..,prIdN>
                data: <mode>                [string, tabName?]
            return
                null
        */

        return 'NYI';
    }

    private function profile_handleLoad()
    {
        /*  params
                id: profileId
                items: string       [itemIds.join(':')]
                unnamed: unixtime   [only to force the browser to reload instead of cache]
            return
                lots...
        */

        // titles, achievements, characterData, talents (, pets)
        // and some onLoad-hook to .. load it registerProfile($data)
        // everything else goes through data.php .. strangely enough

        $char = new ProfileList(array(['id', $this->get['id']])); // or string or whatever

        // modify model from auras with profile_getModelForForm

        $buff = '';

        if ($it = array_column($char->getField('inventory'), 0))
        {
            $itemz = new ItemList(array(['id', $it, CFG_SQL_LIMIT_NONE]));
            $data  = $itemz->getListviewData(ITEMINFO_JSON | ITEMINFO_SUBITEMS);

            // get and apply inventory
            foreach ($itemz->iterate() as $iId => $__)
                $buff .= 'g_items.add('.$iId.', {name_'.User::$localeString.":'".Util::jsEscape($itemz->getField('name', true))."', quality:".$itemz->getField('quality').", icon:'".$itemz->getField('iconString')."', jsonequip:".json_encode($data[$iId], JSON_NUMERIC_CHECK)."});\n";

            $buff .= "\n";
        }

        if ($au = $char->getField('auras'))
        {
            $auraz = new SpellList(array(['id', $char->getField('auras')], CFG_SQL_LIMIT_NONE));
            $dataz = $auraz->getListviewData();
            $modz  = $auraz->getProfilerMods();

            // get and apply aura-mods
            foreach ($dataz as $id => $data)
            {
                $mods = [];
                if (!empty($modz[$id]))
                {
                    foreach ($modz[$id] as $k => $v)
                    {
                        if (is_array($v))
                            $mods[] = $v;
                        else if ($str = @Util::$itemMods[$k])
                            $mods[$str] = $v;
                    }
                }

                $buff .= 'g_spells.add('.$id.", {id:".$id.", name:'".Util::jsEscape(substr($data['name'], 1))."', icon:'".$data['icon']."', modifier:".json_encode($mods, JSON_NUMERIC_CHECK)."});\n";
            }
            $buff .= "\n";
        }

        /* depending on progress-achievements
            // required by progress in JScript move to handleLoad()?
            Util::$pageTemplate->extendGlobalIds(TYPE_NPC, [29120, 31134, 29306, 29311, 23980, 27656, 26861, 26723, 28923, 15991]);
        */

        // load available titles
        Util::loadStaticFile('p-titles-'.$char->getField('gender'), $buff, true);

        // load available achievements
        if (!Util::loadStaticFile('p-achievements', $buff, true))
        {
            $buff .= "\n\ng_achievement_catorder = [];";
            $buff .= "\n\ng_achievement_points = [0];";
        }

        // excludes; structure UNK type => [maskBit => [typeIds]] ?
        /*
            g_user.excludes = [type:[typeIds]]
            g_user.includes = [type:[typeIds]]
            g_user.excludegroups = groupMask        // requires g_user.settings != null

            maskBit are matched against fieldId from excludeGroups
            id: 1, label: LANG.dialog_notavail
            id: 2, label: LANG.dialog_tcg
            id: 4, label: LANG.dialog_collector
            id: 8, label: LANG.dialog_promo
            id: 16, label: LANG.dialog_nonus
            id: 96, label: LANG.dialog_faction
            id: 896, label: LANG.dialog_profession
            id: 1024, label: LANG.dialog_noexalted
        */
        // $buff .= "\n\ng_excludes = {};";

        // add profile to buffer
        $buff .= "\n\n\$WowheadProfiler.registerProfile(".json_encode($char->getEntry(2)).");"; // can't use JSON_NUMERIC_CHECK or the talent-string becomes a float

        return $buff."\n";
    }

    private function profile_getModelForForm($form, $char)
    {
        switch ($form)
        {
            case 1: // FORM_CAT
                if ($char['race'] == 4) // RACE_NIGHTELF
                {
                    if ($char['hairColor'] >= 0 && $char['hairColor'] <= 2)
                        return 29407;
                    else if ($char['hairColor'] == 3)
                        return 29406;
                    else if ($char['hairColor'] == 4)
                        return 29408;
                    else if ($char['hairColor'] == 7 || $char['hairColor'] == 8)
                        return 29405;
                    else
                        return 892;
                }

                if ($char['race'] == 6) // RACE_TAUREN
                {
                    if ($char['gender'] == GENDER_MALE)
                    {
                        if ($char['skinColor'] >= 0 && $char['skinColor'] <= 5)
                            return 29412;
                        else if ($char['skinColor'] >= 0 && $char['skinColor'] <= 8)
                            return 29411;
                        else if ($char['skinColor'] >= 0 && $char['skinColor'] <= 11)
                            return 29410;
                        else if (in_array($char['skinColor'], [12, 13, 14, 18]))
                            return 29410;
                        else
                            return 8571;
                    }
                    else // if gender == GENDER_FEMALE
                    {
                        if ($char['skinColor'] >= 0 && $char['skinColor'] <= 3)
                            return 29412;
                        else if ($char['skinColor'] >= 0 && $char['skinColor'] <= 5)
                            return 29411;
                        else if ($char['skinColor'] >= 0 && $char['skinColor'] <= 7)
                            return 29410;
                        else if ($char['skinColor'] == 10)
                            return 29410;
                        else
                            return 8571;
                    }
                }
            case 5: // FORM_DIREBEAR
            case 8: // FORM_BEAR
                if ($char['race'] == 4) // RACE_NIGHTELF
                {
                    if ($char['hairColor'] >= 0 && $char['hairColor'] <= 2)
                        return 29413;
                    else if ($char['hairColor'] == 3)
                        return 29417;
                    else if ($char['hairColor'] == 4)
                        return 29416;
                    else if ($char['hairColor'] == 6)
                        return 29414;
                    else
                        return 2281;
                }

                if ($char['race'] == 6) // RACE_TAUREN
                {
                    if ($char['gender'] == GENDER_MALE)
                    {
                        if ($char['skinColor'] >= 0 && $char['skinColor'] <= 2)
                            return 29415;
                        else if (in_array($char['skinColor'], [3, 4, 5, 12, 13, 14]))
                            return 29419;
                        else if (in_array($char['skinColor'], [9, 10, 11, 15, 16, 17]))
                           return 29420;
                        else if ($char['skinColor'] == 18)
                            return 29421;
                        else
                            return 2289;
                    }
                    else // if gender == GENDER_FEMALE
                    {
                        if ($char['skinColor'] == 0 && $char['skinColor'] == 1)
                            return 29418;
                        else if ($char['skinColor'] == 2 && $char['skinColor'] == 3)
                            return 29419;
                        else if ($char['skinColor'] >= 6 && $char['skinColor'] <= 9)
                            return 29420;
                        else if ($char['skinColor'] == 10)
                            return 29421;
                        else
                            return 2289;
                    }
                }
        }

        // hey, still here? you're not a Tauren/Nelf as bear or cat, are you?
        return DB::Aowow()->selectCell('SELECT IF(?d == 1, IFNULL(displayIdA, displayIdH), IFNULL(displayIdH, displayIdA)) FROM ?_shapeshiftForm WHERE id = ?d', Util::sideByRaceMask(1 << ($char['race'] - 1)), $form);
    }


}

?>
