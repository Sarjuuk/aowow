<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// expects non-200 header on error
class CommentDeletereplyResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedPOST  = array(
        'id' => ['filter' => FILTER_VALIDATE_INT]
    );

    protected function generate() : void
    {
        if (!$this->assertPOST('id'))
        {
            trigger_error('CommentDeletereplyResponse - malformed request received', E_USER_ERROR);
            $this->generate404(User::isInGroup(U_GROUP_STAFF) ? 'request malformed' : '');
        }

        // flag as deleted (unset sticky (can a reply even be sticky?)
        $ok = DB::Aowow()->query('UPDATE ?_comments SET `flags` = `flags` & ~?d | ?d, `deleteUserId` = ?d, `deleteDate` = UNIX_TIMESTAMP() WHERE `id` = ?d { AND `userId` = ?d }',
            CC_FLAG_STICKY, CC_FLAG_DELETED,
            User::$id,
            $this->_post['id'],
            User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id
        );

        if ($ok)
            DB::Aowow()->query('DELETE FROM ?_user_ratings WHERE `type` = ?d AND `entry` = ?d', RATING_COMMENT, $this->_post['id']);
        else
        {
            trigger_error('CommentDeletereplyResponse - deleting reply #'.$this->_post['id'].' by user #'.User::$id.' from db failed', E_USER_ERROR);
            $this->generate404(Lang::main('intError'));
        }
    }
}

?>
