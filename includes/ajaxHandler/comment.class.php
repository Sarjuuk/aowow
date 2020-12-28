<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxComment extends AjaxHandler
{
    const COMMENT_LENGTH_MIN = 10;
    const COMMENT_LENGTH_MAX = 7500;
    const REPLY_LENGTH_MIN   = 15;
    const REPLY_LENGTH_MAX   = 600;

    protected $_post = array(
        'id'          => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkIdListUnsigned']],
        'body'        => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkFulltext']      ],
        'commentbody' => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkFulltext']      ],
        'response'    => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW                            ],
        'reason'      => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW                            ],
        'remove'      => [FILTER_SANITIZE_NUMBER_INT, null                                             ],
        'commentId'   => [FILTER_SANITIZE_NUMBER_INT, null                                             ],
        'replyId'     => [FILTER_SANITIZE_NUMBER_INT, null                                             ],
        'sticky'      => [FILTER_SANITIZE_NUMBER_INT, null                                             ],
     // 'username'    => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH   ]
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
    protected function handleCommentAdd() : string
    {
        if (!$this->_get['typeid'] || !$this->_get['type'] || !isset(Util::$typeClasses[$this->_get['type']]))
        {
            trigger_error('AjaxComment::handleCommentAdd - malforemd request received', E_USER_ERROR);
            return '';                                      // whatever, we cant even send him back
        }

        // this type cannot be commented on
        if (!(get_class_vars(Util::$typeClasses[$this->_get['type']])['contribute'] & CONTRIBUTE_CO))
        {
            trigger_error('AjaxComment::handleCommentAdd - tried to comment on unsupported type #'.$this->_get['type'], E_USER_ERROR);
            return '';
        }

        // trim to max length
        if (!User::isInGroup(U_GROUP_MODERATOR) && mb_strlen($this->_post['commentbody']) > (self::COMMENT_LENGTH_MAX * (User::isPremium() ? 3 : 1)))
            $this->post['commentbody'] = mb_substr($this->_post['commentbody'], 0, (self::COMMENT_LENGTH_MAX * (User::isPremium() ? 3 : 1)));

        if (User::canComment())
        {
            if (!empty($this->_post['commentbody']) && mb_strlen($this->_post['commentbody']) >= self::COMMENT_LENGTH_MIN)
            {
                if ($postIdx = DB::Aowow()->query('INSERT INTO ?_comments (type, typeId, userId, roles, body, date) VALUES (?d, ?d, ?d, ?d, ?, UNIX_TIMESTAMP())', $this->_get['type'], $this->_get['typeid'], User::$id, User::$groups, $this->_post['commentbody']))
                {
                    Util::gainSiteReputation(User::$id, SITEREP_ACTION_COMMENT, ['id' => $postIdx]);

                    // every comment starts with a rating of +1 and i guess the simplest thing to do is create a db-entry with the system as owner
                    DB::Aowow()->query('INSERT INTO ?_comments_rates (commentId, userId, value) VALUES (?d, 0, 1)', $postIdx);

                    // flag target with hasComment
                    if ($tbl = get_class_vars(Util::$typeClasses[$this->_get['type']])['dataTable'])
                        DB::Aowow()->query('UPDATE '.$tbl.' SET cuFlags = cuFlags | ?d WHERE id = ?d', CUSTOM_HAS_COMMENT, $this->_get['typeid']);
                }
                else
                {
                    $_SESSION['error']['co'] = Lang::main('intError');
                    trigger_error('AjaxComment::handleCommentAdd - write to db failed', E_USER_ERROR);
                }
            }
            else
                $_SESSION['error']['co'] = Lang::main('textLength', [mb_strlen($this->_post['commentbody']), self::COMMENT_LENGTH_MIN, self::COMMENT_LENGTH_MAX]);
        }
        else
            $_SESSION['error']['co'] = Lang::main('cannotComment');

        $this->doRedirect = true;
        return '?'.Util::$typeStrings[$this->_get['type']].'='.$this->_get['typeid'].'#comments';
    }

    protected function handleCommentEdit() : void
    {
        if (!User::canComment() && !User::isInGroup(U_GROUP_MODERATOR))
        {
            trigger_error('AjaxComment::handleCommentEdit - user #'.User::$id.' not allowed to edit', E_USER_ERROR);
            return;
        }

        if (!$this->_get['id'] || !$this->_post['body'])
        {
            trigger_error('AjaxComment::handleCommentEdit - malforemd request received', E_USER_ERROR);
            return;
        }

        if (mb_strlen($this->_post['body']) < self::COMMENT_LENGTH_MIN)
            return;                                         // no point in reporting this trifle

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

    protected function handleCommentDelete() : void
    {
        if (!$this->_post['id'] || !User::$id)
        {
            trigger_error('AjaxComment::handleCommentDelete - commentId empty or user not logged in', E_USER_ERROR);
            return;
        }

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

            if (!$coInfo['hasMore'] && Util::$typeClasses[$coInfo['type']] && ($tbl = get_class_vars(Util::$typeClasses[$coInfo['type']])['dataTable']))
                DB::Aowow()->query('UPDATE '.$tbl.' SET cuFlags = cuFlags & ~?d WHERE id = ?d', CUSTOM_HAS_COMMENT, $coInfo['typeId']);
        }
        else
            trigger_error('AjaxComment::handleCommentDelete - user #'.User::$id.' could not flag comment #'.$this->_post['id'].' as deleted', E_USER_ERROR);
    }

    protected function handleCommentUndelete() : void
    {
        if (!$this->_post['id'] || !User::$id)
        {
            trigger_error('AjaxComment::handleCommentUndelete - commentId empty or user not logged in', E_USER_ERROR);
            return;
        }

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
            if (Util::$typeClasses[$coInfo['type']] && ($tbl = get_class_vars(Util::$typeClasses[$coInfo['type']])['dataTable']))
                DB::Aowow()->query('UPDATE '.$tbl.' SET cuFlags = cuFlags | ?d WHERE id = ?d', CUSTOM_HAS_COMMENT, $coInfo['typeId']);
        }
        else
            trigger_error('AjaxComment::handleCommentUndelete - user #'.User::$id.' could not unflag comment #'.$this->_post['id'].' as deleted', E_USER_ERROR);
    }

    protected function handleCommentRating() : string
    {
        if (!$this->_get['id'])
            return Util::toJSON(['success' => 0]);

        if ($votes = DB::Aowow()->selectRow('SELECT 1 AS success, SUM(IF(value > 0, value, 0)) AS up, SUM(IF(value < 0, -value, 0)) AS down FROM ?_comments_rates WHERE commentId = ?d and userId <> 0 GROUP BY commentId', $this->_get['id']))
            return Util::toJSON($votes);
        else
            return Util::toJSON(['success' => 1, 'up' => 0, 'down' => 0]);
    }

    protected function handleCommentVote() : string
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

    protected function handleCommentSticky() : void
    {
        if (!$this->_post['id'] || !User::isInGroup(U_GROUP_MODERATOR))
        {
            trigger_error('AjaxComment::handleCommentSticky - commentId empty or user #'.User::$id.' not moderator', E_USER_ERROR);
            return;
        }

        if ($this->_post['sticky'])
            DB::Aowow()->query('UPDATE ?_comments SET flags = flags |  ?d WHERE id = ?d', CC_FLAG_STICKY, $this->_post['id'][0]);
        else
            DB::Aowow()->query('UPDATE ?_comments SET flags = flags & ~?d WHERE id = ?d', CC_FLAG_STICKY, $this->_post['id'][0]);
    }

    protected function handleCommentOutOfDate() : string
    {
        $this->contentType = MIME_TYPE_TEXT;

        if (!$this->_post['id'])
        {
            trigger_error('AjaxComment::handleCommentOutOfDate - commentId empty', E_USER_ERROR);
            return Lang::main('intError');
        }

        $ok = false;
        if (User::isInGroup(U_GROUP_MODERATOR))             // directly mark as outdated
        {
            if (!$this->_post['remove'])
                $ok = DB::Aowow()->query('UPDATE ?_comments SET flags = flags |  0x4 WHERE id = ?d', $this->_post['id'][0]);
            else
                $ok = DB::Aowow()->query('UPDATE ?_comments SET flags = flags & ~0x4 WHERE id = ?d', $this->_post['id'][0]);
        }
        else if (DB::Aowow()->selectCell('SELECT 1 FROM ?_reports WHERE `mode` = ?d AND `reason`= ?d AND `subject` = ?d AND `userId` = ?d', 1, 17, $this->_post['id'][0], User::$id))
            return Lang::main('alreadyReport');
        else if (User::$id && !$this->_post['reason'] || mb_strlen($this->_post['reason']) < self::REPLY_LENGTH_MIN)
            return Lang::main('textTooShort');
        else if (User::$id)                                 // only report as outdated
            $ok = Util::createReport(1, 17, $this->_post['id'][0], '[Outdated Comment] '.$this->_post['reason']);

        if ($ok)                                            // this one is very special; as in: completely retarded
            return 'ok';                                    // the script expects the actual characters 'ok' not some string like "ok"
        else
            trigger_error('AjaxComment::handleCommentOutOfDate - failed to update comment in db', E_USER_ERROR);

        return Lang::main('intError');
    }

    protected function handleCommentShowReplies() : string
    {
        return Util::toJSON(!$this->_get['id'] ? [] : CommunityContent::getCommentReplies($this->_get['id']));
    }

    protected function handleReplyAdd() : string
    {
        $this->contentType = MIME_TYPE_TEXT;

        if (!User::canComment())
            return Lang::main('cannotComment');

        if (!$this->_post['commentId'] || !DB::Aowow()->selectCell('SELECT 1 FROM ?_comments WHERE id = ?d', $this->_post['commentId']))
        {
            trigger_error('AjaxComment::handleReplyAdd - comment #'.$this->_post['commentId'].' does not exist', E_USER_ERROR);
            return Lang::main('intError');
        }

        if (!$this->_post['body'] || mb_strlen($this->_post['body']) < self::REPLY_LENGTH_MIN || mb_strlen($this->_post['body']) > self::REPLY_LENGTH_MAX)
            return Lang::main('textLength', [mb_strlen($this->_post['body']), self::REPLY_LENGTH_MIN, self::REPLY_LENGTH_MAX]);

        if (DB::Aowow()->query('INSERT INTO ?_comments (`userId`, `roles`, `body`, `date`, `replyTo`) VALUES (?d, ?d, ?, UNIX_TIMESTAMP(), ?d)', User::$id, User::$groups, $this->_post['body'], $this->_post['commentId']))
            return Util::toJSON(CommunityContent::getCommentReplies($this->_post['commentId']));

        trigger_error('AjaxComment::handleReplyAdd - write to db failed', E_USER_ERROR);
        return Lang::main('intError');
    }

    protected function handleReplyEdit() : string
    {
        $this->contentType = MIME_TYPE_TEXT;

        if (!User::canComment())
            return Lang::main('cannotComment');

        if ((!$this->_post['replyId'] || !$this->_post['commentId']) && DB::Aowow()->selectCell('SELECT COUNT(1) FROM ?_comments WHERE id IN (?a)', [$this->_post['replyId'], $this->_post['commentId']]))
        {
            trigger_error('AjaxComment::handleReplyEdit - comment #'.$this->_post['commentId'].' or reply #'.$this->_post['replyId'].' does not exist', E_USER_ERROR);
            return Lang::main('intError');
        }

        if (!$this->_post['body'] || mb_strlen($this->_post['body']) < self::REPLY_LENGTH_MIN || mb_strlen($this->_post['body']) > self::REPLY_LENGTH_MAX)
            return Lang::main('textLength', [mb_strlen($this->_post['body']), self::REPLY_LENGTH_MIN, self::REPLY_LENGTH_MAX]);

        if (DB::Aowow()->query('UPDATE ?_comments SET body = ?, editUserId = ?d, editDate = UNIX_TIMESTAMP(), editCount = editCount + 1 WHERE id = ?d AND replyTo = ?d{ AND userId = ?d}',
            $this->_post['body'], User::$id, $this->_post['replyId'], $this->_post['commentId'], User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id))
                return Util::toJSON(CommunityContent::getCommentReplies($this->_post['commentId']));

        trigger_error('AjaxComment::handleReplyEdit - write to db failed', E_USER_ERROR);
        return Lang::main('intError');
    }

    protected function handleReplyDetach() : void
    {
        if (!$this->_post['id'] || !User::isInGroup(U_GROUP_MODERATOR))
        {
            trigger_error('AjaxComment::handleReplyDetach - commentId empty or user #'.User::$id.' not moderator', E_USER_ERROR);
            return;
        }

        DB::Aowow()->query('UPDATE ?_comments c1, ?_comments c2 SET c1.replyTo = 0, c1.type = c2.type, c1.typeId = c2.typeId WHERE c1.replyTo = c2.id AND c1.id = ?d', $this->_post['id'][0]);
    }

    protected function handleReplyDelete() : void
    {
        if (!User::$id || !$this->_post['id'])
        {
            trigger_error('AjaxComment::handleReplyDelete - commentId empty or user not logged in', E_USER_ERROR);
            return;
        }

        if (DB::Aowow()->query('DELETE FROM ?_comments WHERE id = ?d{ AND userId = ?d}', $this->_post['id'][0], User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id))
            DB::Aowow()->query('DELETE FROM ?_comments_rates WHERE commentId = ?d', $this->_post['id'][0]);
        else
            trigger_error('AjaxComment::handleReplyDelete - deleting comment #'.$this->_post['id'][0].' by user #'.User::$id.' from db failed', E_USER_ERROR);
    }

    protected function handleReplyFlag() : void
    {
        if (!User::$id || !$this->_post['id'])
        {
            trigger_error('AjaxComment::handleReplyFlag - commentId empty or user not logged in', E_USER_ERROR);
            return;
        }

        Util::createReport(1, 19, $this->_post['id'][0], '[General Reply Report]');
    }

    protected function handleReplyUpvote() : void
    {
        if (!$this->_post['id'] || !User::canUpvote())
        {
            trigger_error('AjaxComment::handleReplyUpvote - commentId empty or user not allowed to vote', E_USER_ERROR);
            return;
        }

        $owner = DB::Aowow()->selectCell('SELECT userId FROM ?_comments WHERE id = ?d', $this->_post['id'][0]);
        if (!$owner)
        {
            trigger_error('AjaxComment::handleReplyUpvote - comment #'.$this->_post['id'][0].' not found in db', E_USER_ERROR);
            return;
        }

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
        else
            trigger_error('AjaxComment::handleReplyUpvote - write to db failed', E_USER_ERROR);
    }

    protected function handleReplyDownvote() : void
    {
        if (!$this->_post['id'] || !User::canDownvote())
        {
            trigger_error('AjaxComment::handleReplyDownvote - commentId empty or user not allowed to vote', E_USER_ERROR);
            return;
        }

        $owner = DB::Aowow()->selectCell('SELECT userId FROM ?_comments WHERE id = ?d', $this->_post['id'][0]);
        if (!$owner)
        {
            trigger_error('AjaxComment::handleReplyDownvote - comment #'.$this->_post['id'][0].' not found in db', E_USER_ERROR);
            return;
        }

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
        else
            trigger_error('AjaxComment::handleReplyDownvote - write to db failed', E_USER_ERROR);
    }
}

?>
