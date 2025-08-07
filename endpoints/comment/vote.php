<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// up, down and remove
class CommentVoteResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedGET   = array(
        'id'     => ['filter' => FILTER_VALIDATE_INT                                                    ],
        'rating' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => -2, 'max_range' => 2]]
    );

    protected function generate(): void
    {
        if (!$this->assertGET('id', 'rating'))
        {
            trigger_error('CommentVoteResponse - malformed request received', E_USER_ERROR);
            $this->result = Util::toJSON(['error' => 1, 'message' => Lang::main('genericError')]);
            return;
        }

        if (User::getCurrentDailyVotes() <= 0)
        {
            $this->result = Util::toJSON(['error' => 1, 'message' => Lang::main('tooManyVotes')]);
            return;
        }

        $target = DB::Aowow()->selectRow(
           'SELECT c.`userId` AS "owner", ur.`value`, IF(c.`flags` & ?d, 1, 0) AS "deleted" FROM ?_comments c LEFT JOIN ?_user_ratings ur ON ur.`type` = ?d AND ur.`entry` = c.id AND ur.`userId` = ?d WHERE c.id = ?d',
            CC_FLAG_DELETED, RATING_COMMENT, User::$id, $this->_get['id']
        );
        if (!$target)
        {
            trigger_error('CommentVoteResponse - target comment #'.$this->_get['id'].' not found', E_USER_ERROR);
            $this->result = Util::toJSON(['error' => 1, 'message' => Lang::main('genericError')]);
            return;
        }

        $val = User::canSupervote() ? 2 : 1;
        if ($this->_get['rating'] < 0)
            $val *= -1;

        if (User::$id == $target['owner'] || $val != $this->_get['rating'] || $target['deleted'])
        {
            // circumvented the checks in JS
            $this->result = Util::toJSON(['error' => 1, 'message' => Lang::main('genericError')]);
            return;
        }

        if (($val > 0 && !User::canUpvote()) || ($val < 0 && !User::canDownvote()))
        {
            $this->result = Util::toJSON(['error' => 1, 'message' => Lang::main('bannedRating')]);
            return;
        }

        $ok = false;
        // old and new have same sign; undo vote (user may have gained/lost access to superVote in the meantime)
        if ($target['value'] && ($target['value'] < 0) == ($val < 0))
            $ok = DB::Aowow()->query('DELETE FROM ?_user_ratings WHERE `type` = ?d AND `entry` = ?d AND `userId` = ?d', RATING_COMMENT, $this->_get['id'], User::$id);
        else                                                // replace, because we may be overwriting an old, opposing vote
            if ($ok = DB::Aowow()->query('REPLACE INTO ?_user_ratings (`type`, `entry`, `userId`, `value`) VALUES (?d, ?d, ?d, ?d)', RATING_COMMENT, $this->_get['id'], User::$id, $val))
                User::decrementDailyVotes();                // do not refund retracted votes!

        if ($ok)
        {
            if ($val > 0)                                   // gain rep
                Util::gainSiteReputation($target['owner'], SITEREP_ACTION_UPVOTED, ['id' => $this->_get['id'], 'voterId' => User::$id]);
            else if ($val < 0)
                Util::gainSiteReputation($target['owner'], SITEREP_ACTION_DOWNVOTED, ['id' => $this->_get['id'], 'voterId' => User::$id]);

            $this->result = Util::toJSON(['error' => 0]);
        }
        else
            $this->result = Util::toJSON(['error' => 1, 'message' => Lang::main('intError')]);
    }
}

?>
