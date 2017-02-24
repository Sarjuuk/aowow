<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

class AjaxComment extends AjaxHandler
{
    const COMMENT_LENGTH_MIN = 10;
    const COMMENT_LENGTH_MAX = 7500;
    const REPLY_LENGTH_MIN   = 15;
    const REPLY_LENGTH_MAX   = 600;

    protected $_post = array(
        'id'          => [FILTER_CALLBACK,            ['options' => 'AjaxComment::checkId']],
        'body'        => [FILTER_UNSAFE_RAW,          null],// escaped by json_encode
        'commentbody' => [FILTER_UNSAFE_RAW,          null],// escaped by json_encode
        'response'    => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW],
        'reason'      => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW],
        'remove'      => [FILTER_SANITIZE_NUMBER_INT, null],
        'commentId'   => [FILTER_SANITIZE_NUMBER_INT, null],
        'replyId'     => [FILTER_SANITIZE_NUMBER_INT, null],
     // 'username'    => [FILTER_SANITIZE_STRING,     0xC]  // FILTER_FLAG_STRIP_LOW | *_HIGH
    );

    protected $_get  = array(
        'id'     => [FILTER_CALLBACK, ['options' => 'AjaxHandler::checkInt']],
        'type'   => [FILTER_CALLBACK, ['options' => 'AjaxHandler::checkInt']],
        'typeid' => [FILTER_CALLBACK, ['options' => 'AjaxHandler::checkInt']],
        'rating' => [FILTER_SANITIZE_NUMBER_INT, null]
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        if (!$this->params || count($this->params) != 1)
            return;

        // note: return values must be formated as STRICT json!

        // select handler
        if ($this->params[0] == 'add')
            $this->handler = 'handleCommentAdd';
        else if ($this->params[0] == 'edit')
            $this->handler = 'handleCommentEdit';
        else if ($this->params[0] == 'delete')
            $this->handler = 'handleCommentDelete';
        else if ($this->params[0] == 'undelete')
            $this->handler = 'handleCommentUndelete';
        else if ($this->params[0] == 'rating')              // up/down - distribution
            $this->handler = 'handleCommentRating';
        else if ($this->params[0] == 'vote')                // up, down and remove
            $this->handler = 'handleCommentVote';
        else if ($this->params[0] == 'sticky')              // toggle flag
            $this->handler = 'handleCommentSticky';
        else if ($this->params[0] == 'out-of-date')         // toggle flag
            $this->handler = 'handleCommentOutOfDate';
        else if ($this->params[0] == 'show-replies')
            $this->handler = 'handleCommentShowReplies';
        else if ($this->params[0] == 'add-reply')           // also returns all replies on success
            $this->handler = 'handleReplyAdd';
        else if ($this->params[0] == 'edit-reply')          // also returns all replies on success
            $this->handler = 'handleReplyEdit';
        else if ($this->params[0] == 'detach-reply')
            $this->handler = 'handleReplyDetach';
        else if ($this->params[0] == 'delete-reply')
            $this->handler = 'handleReplyDelete';
        else if ($this->params[0] == 'flag-reply')
            $this->handler = 'handleReplyFlag';
        else if ($this->params[0] == 'upvote-reply')
            $this->handler = 'handleReplyUpvote';
        else if ($this->params[0] == 'downvote-reply')
            $this->handler = 'handleReplyDownvote';
    }

    // i .. have problems believing, that everything uses nifty ajax while adding comments requires a brutal header(Loacation: <wherever>), yet, thats how it is
    protected function handleCommentAdd()
    {
        if (!$this->_get['typeid'] || !$this->_get['type'] || !isset(Util::$typeStrings[$this->_get['type']]))
            return;                                         // whatever, we cant even send him back

        // trim to max length
        if (!User::isInGroup(U_GROUP_MODERATOR) && mb_strlen($this->_post['commentbody']) > (self::COMMENT_LENGTH_MAX * (User::isPremium() ? 3 : 1)))
            $this->post['commentbody'] = mb_substr($this->_post['commentbody'], 0, (self::COMMENT_LENGTH_MAX * (User::isPremium() ? 3 : 1)));

        if (User::canComment() && !empty($this->_post['commentbody']) && mb_strlen($this->_post['commentbody']) >= self::COMMENT_LENGTH_MIN)
        {
            if ($postIdx = DB::Aowow()->query('INSERT INTO ?_comments (type, typeId, userId, roles, body, date) VALUES (?d, ?d, ?d, ?d, ?, UNIX_TIMESTAMP())', $this->_get['type'], $this->_get['typeid'], User::$id, User::$groups, $this->_post['commentbody']))
            {
                Util::gainSiteReputation(User::$id, SITEREP_ACTION_COMMENT, ['id' => $postIdx]);

                // every comment starts with a rating of +1 and i guess the simplest thing to do is create a db-entry with the system as owner
                DB::Aowow()->query('INSERT INTO ?_comments_rates (commentId, userId, value) VALUES (?d, 0, 1)', $postIdx);

                // flag target with hasComment
                if (Util::$typeClasses[$this->_get['type']] && ($tbl = (new Util::$typeClasses[$this->_get['type']](null))::$dataTable))
                    DB::Aowow()->query('UPDATE '.$tbl.' SET cuFlags = cuFlags | ?d WHERE id = ?d', CUSTOM_HAS_COMMENT, $this->_get['typeid']);
            }
        }

        $this->doRedirect = true;
        return '?'.Util::$typeStrings[$this->_get['type']].'='.$this->_get['typeid'].'#comments';
    }

    protected function handleCommentEdit()
    {
        if ((!User::canComment() && !User::isInGroup(U_GROUP_MODERATOR)) || !$this->_get['id'] || !$this->_post['body'])
            return;

        if (mb_strlen($this->_post['body']) < self::COMMENT_LENGTH_MIN)
            return;

        // trim to max length
        if (!User::isInGroup(U_GROUP_MODERATOR) && mb_strlen($this->_post['body']) > (self::COMMENT_LENGTH_MAX * (User::isPremium() ? 3 : 1)))
            $this->post['body'] = mb_substr($this->_post['body'], 0, (self::COMMENT_LENGTH_MAX * (User::isPremium() ? 3 : 1)));

        $update = array(
            'body'       => $this->_post['body'],
            'editUserId' => User::$id,
            'editDate'   => time()
        );

        if (User::isInGroup(U_GROUP_MODERATOR))
        {
            $update['responseBody']   = !$this->_post['response'] ? '' : $this->_post['response'];
            $update['responseUserId'] = !$this->_post['response'] ? 0  : User::$id;
            $update['responseRoles']  = !$this->_post['response'] ? 0  : User::$groups;
        }

        DB::Aowow()->query('UPDATE ?_comments SET editCount = editCount + 1, ?a WHERE id = ?d', $update, $this->_get['id']);
    }

    protected function handleCommentDelete()
    {
        if (!$this->_post['id'] || !User::$id)
            return;

        // in theory, there is a username passed alongside...   lets just use the current user (see user.js)
        $ok = DB::Aowow()->query('UPDATE ?_comments SET flags = flags | ?d, deleteUserId = ?d, deleteDate = UNIX_TIMESTAMP() WHERE id IN (?a){ AND userId = ?d}',
            CC_FLAG_DELETED,
            User::$id,
            $this->_post['id'],
            User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id
        );

        // deflag hasComment
        if ($ok)
        {
            $coInfo = DB::Aowow()->selectRow('SELECT IF(BIT_OR(~b.flags) & ?d, 1, 0) as hasMore, b.type, b.typeId FROM ?_comments a JOIN ?_comments b ON a.type = b.type AND a.typeId = b.typeId WHERE a.id = ?d',
                CC_FLAG_DELETED,
                $this->_post['id']
            );

            if (!$coInfo['hasMore'] && Util::$typeClasses[$coInfo['type']] && ($tbl = (new Util::$typeClasses[$coInfo['type']](null))::$dataTable))
                DB::Aowow()->query('UPDATE '.$tbl.' SET cuFlags = cuFlags & ~?d WHERE id = ?d', CUSTOM_HAS_COMMENT, $coInfo['typeId']);
        }
    }

    protected function handleCommentUndelete()
    {
        if (!$this->_post['id'] || !User::$id)
            return;

        // in theory, there is a username passed alongside...   lets just use the current user (see user.js)
        $ok = DB::Aowow()->query('UPDATE ?_comments SET flags = flags & ~?d WHERE id IN (?a){ AND userId = deleteUserId AND deleteUserId = ?d}',
            CC_FLAG_DELETED,
            $this->_post['id'],
            User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id
        );

        // reflag hasComment
        if ($ok)
        {
            $coInfo = DB::Aowow()->selectRow('SELECT type, typeId FROM ?_comments WHERE id = ?d', $this->_post['id']);
            if (Util::$typeClasses[$coInfo['type']] && ($tbl = (new Util::$typeClasses[$coInfo['type']](null))::$dataTable))
                DB::Aowow()->query('UPDATE '.$tbl.' SET cuFlags = cuFlags | ?d WHERE id = ?d', CUSTOM_HAS_COMMENT, $coInfo['typeId']);
        }
    }

    protected function handleCommentRating()
    {
        if (!$this->_get['id'])
            return Util::toJSON(['success' => 0]);

        if ($votes = DB::Aowow()->selectRow('SELECT 1 AS success, SUM(IF(value > 0, value, 0)) AS up, SUM(IF(value < 0, -value, 0)) AS down FROM ?_comments_rates WHERE commentId = ?d and userId <> 0 GROUP BY commentId', $this->_get['id']))
            return Util::toJSON($votes);
        else
            return Util::toJSON(['success' => 1, 'up' => 0, 'down' => 0]);
    }

    protected function handleCommentVote()
    {
        if (!User::$id || !$this->_get['id'] || !$this->_get['rating'])
            return Util::toJSON(['error' => 1, 'message' => Lang::main('genericError')]);

        $target = DB::Aowow()->selectRow('SELECT c.userId AS owner, cr.value FROM ?_comments c LEFT JOIN ?_comments_rates cr ON cr.commentId = c.id AND cr.userId = ?d WHERE c.id = ?d', User::$id, $this->_get['id']);
        $val    = User::canSupervote() ? 2 : 1;
        if ($this->_get['rating'] < 0)
            $val *= -1;

        if (User::getCurDailyVotes() <= 0)
            return Util::toJSON(['error' => 1, 'message' => Lang::main('tooManyVotes')]);
        else if (!$target || $val != $this->_get['rating'])
            return Util::toJSON(['error' => 1, 'message' => Lang::main('genericError')]);
        else if (($val > 0 && !User::canUpvote()) || ($val < 0 && !User::canDownvote()))
            return Util::toJSON(['error' => 1, 'message' => Lang::main('bannedRating')]);

        $ok = false;
        // old and new have same sign; undo vote (user may have gained/lost access to superVote in the meantime)
        if ($target['value'] && ($target['value'] < 0) == ($val < 0))
            $ok = DB::Aowow()->query('DELETE FROM ?_comments_rates WHERE commentId = ?d AND userId = ?d', $this->_get['id'], User::$id);
        else                                                // replace, because we may be overwriting an old, opposing vote
            if ($ok = DB::Aowow()->query('REPLACE INTO ?_comments_rates (commentId, userId, value) VALUES (?d, ?d, ?d)', (int)$this->_get['id'], User::$id, $val))
                User::decrementDailyVotes();                // do not refund retracted votes!

        if (!$ok)
            return Util::toJSON(['error' => 1, 'message' => Lang::main('genericError')]);

        if ($val > 0)                                       // gain rep
            Util::gainSiteReputation($target['owner'], SITEREP_ACTION_UPVOTED, ['id' => $this->_get['id'], 'voterId' => User::$id]);
        else if ($val < 0)
            Util::gainSiteReputation($target['owner'], SITEREP_ACTION_DOWNVOTED, ['id' => $this->_get['id'], 'voterId' => User::$id]);

        return Util::toJSON(['error' => 0]);
    }

    protected function handleCommentSticky()
    {
        if (!$this->_post['id'] || !User::isInGroup(U_GROUP_MODERATOR))
            return;

        if ($this->_post['sticky'])
            DB::Aowow()->query('UPDATE ?_comments SET flags = flags |  ?d WHERE id = ?d', CC_FLAG_STICKY, $this->_post['id'][0]);
        else
            DB::Aowow()->query('UPDATE ?_comments SET flags = flags & ~?d WHERE id = ?d', CC_FLAG_STICKY, $this->_post['id'][0]);
    }

    protected function handleCommentOutOfDate()
    {
        $this->contentType = 'text/plain';

        if (!$this->_post['id'])
            return 'The comment does not exist.';

        $ok = false;
        if (User::isInGroup(U_GROUP_MODERATOR))             // directly mark as outdated
        {
            if (!$this->_post['remove'])
                $ok = DB::Aowow()->query('UPDATE ?_comments SET flags = flags |  0x4 WHERE id = ?d', $this->_post['id'][0]);
            else
                $ok = DB::Aowow()->query('UPDATE ?_comments SET flags = flags & ~0x4 WHERE id = ?d', $this->_post['id'][0]);
        }
        else if (User::$id && !$this->_post['reason'] || mb_strlen($this->_post['reason']) < self::REPLY_LENGTH_MIN)
            return 'Your message is too short.';
        else if (User::$id)                               // only report as outdated
        {
            $ok = DB::Aowow()->query(
                'INSERT INTO ?_reports (userId, mode, reason, subject, ip, description, userAgent, appName) VALUES (?d, 1, 17, ?d, ?, "<automated comment report>", ?, ?)',
                User::$id,
                $this->_post['id'][0],
                User::$ip,
                $_SERVER['HTTP_USER_AGENT'],
                get_browser(null, true)['browser']
            );
        }

        if ($ok)                                          // this one is very special; as in: completely retarded
            return 'ok';                                  // the script expects the actual characters 'ok' not some string like "ok"

        return Lang::main('genericError');
    }

    protected function handleCommentShowReplies()
    {
        return Util::toJSON(!$this->_get['id'] ? [] : CommunityContent::getCommentReplies($this->_get['id']));
    }

    protected function handleReplyAdd()
    {
        $this->contentType = 'text/plain';

        if (!User::canComment())
            return 'You are not allowed to reply.';

        else if (!$this->_post['commentId'] || !DB::Aowow()->selectCell('SELECT 1 FROM ?_comments WHERE id = ?d', $this->_post['commentId']))
            return Lang::main('genericError');

        else if (!$this->_post['body'] || mb_strlen($this->_post['body']) < self::REPLY_LENGTH_MIN || mb_strlen($this->_post['body']) > self::REPLY_LENGTH_MAX)
            return 'Your reply has '.mb_strlen($this->_post['body']).' characters and must have at least '.self::REPLY_LENGTH_MIN.' and at most '.self::REPLY_LENGTH_MAX.'.';

        else if (DB::Aowow()->query('INSERT INTO ?_comments (`userId`, `roles`, `body`, `date`, `replyTo`) VALUES (?d, ?d, ?, UNIX_TIMESTAMP(), ?d)', User::$id, User::$groups, $this->_post['body'], $this->_post['commentId']))
            return Util::toJSON(CommunityContent::getCommentReplies($this->_post['commentId']));

        else
            return Lang::main('genericError');
    }

    protected function handleReplyEdit()
    {
        $this->contentType = 'text/plain';

        if (!User::canComment())
            return 'You are not allowed to reply.';

        else if (!$this->_post['replyId'] || !$this->_post['commentId'])
            return Lang::main('genericError');

        else if (!$this->_post['body'] || mb_strlen($this->_post['body']) < self::REPLY_LENGTH_MIN || mb_strlen($this->_post['body']) > self::REPLY_LENGTH_MAX)
            return 'Your reply has '.mb_strlen($this->_post['body']).' characters and must have at least '.self::REPLY_LENGTH_MIN.' and at most '.self::REPLY_LENGTH_MAX.'.';

        if (DB::Aowow()->query('UPDATE ?_comments SET body = ?, editUserId = ?d, editDate = UNIX_TIMESTAMP(), editCount = editCount + 1 WHERE id = ?d AND replyTo = ?d{ AND userId = ?d}',
            $this->_post['body'], User::$id, $this->_post['replyId'], $this->_post['commentId'], User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id))
                return Util::toJSON(CommunityContent::getCommentReplies($this->_post['commentId']));
        else
            return Lang::main('genericError');
    }

    protected function handleReplyDetach()
    {
        if (!User::isInGroup(U_GROUP_MODERATOR) || !$this->_post['id'])
            return;

        DB::Aowow()->query('UPDATE ?_comments c1, ?_comments c2 SET c1.replyTo = 0, c1.type = c2.type, c1.typeId = c2.typeId WHERE c1.replyTo = c2.id AND c1.id = ?d', $this->_post['id'][0]);
    }

    protected function handleReplyDelete()
    {
        if (!User::$id || !$this->_post['id'])
            return;

        if (DB::Aowow()->query('DELETE FROM ?_comments WHERE id = ?d{ AND userId = ?d}', $this->_post['id'][0], User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id))
            DB::Aowow()->query('DELETE FROM ?_comments_rates WHERE commentId = ?d', $this->_post['id'][0]);
    }

    protected function handleReplyFlag()
    {
        if (!User::$id || !$this->_post['id'])
            return;

        DB::Aowow()->query(
            'INSERT INTO ?_reports (userId, mode, reason, subject, ip, description, userAgent, appName) VALUES (?d, 1, 19, ?d, ?, "<automated commentreply report>", ?, ?)',
            User::$id,
            $this->_post['id'][0],
            User::$ip,
            $_SERVER['HTTP_USER_AGENT'],
            get_browser(null, true)['browser']
        );
    }

    protected function handleReplyUpvote()
    {
        if (!$this->_post['id'] || !User::canUpvote())
            return;

        $owner = DB::Aowow()->selectCell('SELECT userId FROM ?_comments WHERE id = ?d', $this->_post['id'][0]);
        if (!$owner)
            return;

        $ok = DB::Aowow()->query(
            'INSERT INTO ?_comments_rates (commentId, userId, value) VALUES (?d, ?d, ?d)',
            $this->_post['id'][0],
            User::$id,
            User::canSupervote() ? 2 : 1
        );

        if ($ok)
        {
            Util::gainSiteReputation($owner, SITEREP_ACTION_UPVOTED, ['id' => $this->_post['id'][0], 'voterId' => User::$id]);
            User::decrementDailyVotes();
        }
    }

    protected function handleReplyDownvote()
    {
        if (!$this->_post['id'] || !User::canDownvote())
            return;

        $owner = DB::Aowow()->selectCell('SELECT userId FROM ?_comments WHERE id = ?d', $this->_post['id'][0]);
        if (!$owner)
            return;

        $ok = DB::Aowow()->query(
            'INSERT INTO ?_comments_rates (commentId, userId, value) VALUES (?d, ?d, ?d)',
            $this->_post['id'][0],
            User::$id,
            User::canSupervote() ? -2 : -1
        );

        if ($ok)
        {
            Util::gainSiteReputation($owner, SITEREP_ACTION_DOWNVOTED, ['id' => $this->_post['id'][0], 'voterId' => User::$id]);
            User::decrementDailyVotes();
        }
    }

    protected function checkId($val)
    {
        // expecting id-list
        if (preg_match('/\d+(,\d+)*/', $val))
            return array_map('intVal', explode(',', $val));

        return null;
    }
}
?>