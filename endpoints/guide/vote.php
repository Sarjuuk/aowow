<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuideVoteResponse extends TextResponse
{
    protected string $contentType   = MIME_TYPE_TEXT;
    protected bool   $requiresLogin = true;

    protected array  $expectedPOST  = array(
        'id'     => ['filter' => FILTER_VALIDATE_INT                                                   ],
        'rating' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 0, 'max_range' => 5]]
    );

    protected function generate() : void
    {
        if (!$this->assertPOST('id', 'rating'))
        {
            trigger_error('GuideVoteResponse - malformed request received', E_USER_ERROR);
            $this->generate404();
        }

        if (!User::canUpvote() || !User::canDownvote())     // same logic as comments?
            $this->generate403();

        // by id, not own, published
        $points = $votes = 0;
        if ($g = DB::Aowow()->selectRow('SELECT `userId`, `cuFlags` FROM ?_guides WHERE `id` = ?d AND (`status` = ?d OR `rev` > 0)', $this->_post['id'], GuideMgr::STATUS_APPROVED))
        {
            // apparently you are allowed to vote on your own guide
            if ($g['cuFlags'] & GUIDE_CU_NO_RATING)
                $this->generate403();

            if (!$this->_post['rating'])
                DB::Aowow()->query('DELETE FROM ?_user_ratings WHERE `type` = ?d AND `entry` = ?d AND `userId` = ?d', RATING_GUIDE, $this->_post['id'], User::$id);
            else
                DB::Aowow()->query('REPLACE INTO ?_user_ratings (`type`, `entry`, `userId`, `value`) VALUES (?d, ?d, ?d, ?d)', RATING_GUIDE, $this->_post['id'], User::$id, $this->_post['rating']);

            [$points, $votes] = DB::Aowow()->selectRow('SELECT IFNULL(SUM(`value`), 0) AS "0", IFNULL(COUNT(*), 0) AS "1" FROM ?_user_ratings WHERE `type` = ?d AND `entry` = ?d', RATING_GUIDE, $this->_post['id']);
        }

        $this->result = Util::toJSON($votes ? ['rating' => $points / $votes, 'nvotes' => $votes] : ['rating' => 0, 'nvotes' => 0]);
    }
}

?>
