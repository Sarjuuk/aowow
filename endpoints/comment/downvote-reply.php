<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// expects non-200 header on error
class CommentDownvotereplyResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedPOST  = array(
        'id' => ['filter' => FILTER_VALIDATE_INT]
    );

    protected function generate() : void
    {
        if (!$this->assertPOST('id'))
        {
            trigger_error('CommentDownvotereplyResponse - malformed request received', E_USER_ERROR);
            $this->generate404(User::isInGroup(U_GROUP_STAFF) ? 'request malformed' : '');
        }

        if (!User::canDownvote())
            $this->generate404(User::isInGroup(U_GROUP_STAFF) ? 'cannot downvote' : '');

        $comment = DB::Aowow()->selectRow('SELECT `userId`, IF(`flags` & ?d, 1, 0) AS "deleted" FROM ?_comments WHERE `id` = ?d', CC_FLAG_DELETED, $this->_post['id']);
        if (!$comment)
        {
            trigger_error('CommentDownvotereplyResponse - comment #'.$this->_post['id'].' not found in db', E_USER_ERROR);
            $this->generate404(User::isInGroup(U_GROUP_STAFF) ? 'replyID not found' : '');
        }

        if (User::$id == $comment['userId'])                // not worth logging?
            $this->generate404('LANG.voteself_tip');

        if ($comment['deleted'])
            $this->generate404('LANG.votedeleted_tip');

        $ok = DB::Aowow()->query(
           'INSERT INTO ?_user_ratings (`type`, `entry`, `userId`, `value`) VALUES (?d, ?d, ?d, ?d)',
            RATING_COMMENT,
            $this->_post['id'],
            User::$id,
            User::canSupervote() ? -2 : -1
        );

        if (!$ok)
        {
            trigger_error('CommentDownvotereplyResponse - write to db failed', E_USER_ERROR);
            $this->generate404(User::isInGroup(U_GROUP_STAFF) ? 'write to db failed' : '');
        }

        Util::gainSiteReputation($comment['userId'], SITEREP_ACTION_DOWNVOTED, ['id' => $this->_post['id'], 'voterId' => User::$id]);
        User::decrementDailyVotes();
    }
}

?>
