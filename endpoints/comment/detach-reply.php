<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// expects non-200 header on error
class CommentDetachreplyResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_MODERATOR;

    protected array $expectedPOST      = array(
        'id' => ['filter' => FILTER_VALIDATE_INT]
    );

    protected function generate() : void
    {
        if (!$this->assertPOST('id'))
        {
            trigger_error('CommentDetachreplyResponse - malformed request received', E_USER_ERROR);
            $this->generate404(User::isInGroup(U_GROUP_STAFF) ? 'request malformed' : '');
        }

        DB::Aowow()->query('UPDATE ?_comments c1, ?_comments c2 SET c1.`replyTo` = 0, c1.`type` = c2.`type`, c1.`typeId` = c2.`typeId` WHERE c1.`replyTo` = c2.`id` AND c1.`id` = ?d', $this->_post['id']);
    }
}

?>
