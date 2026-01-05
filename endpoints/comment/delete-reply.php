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

        $where = [['`id` = %i', $this->_post['id']]];
        if (!User::isInGroup(U_GROUP_MODERATOR))
            $where[] = ['`userId` = %i',  User::$id];

        // flag as deleted
        if (DB::Aowow()->qry('UPDATE ::comments SET `flags` = `flags` | %i, `deleteUserId` = %i, `deleteDate` = UNIX_TIMESTAMP() WHERE %and', CC_FLAG_DELETED, User::$id, $where))
            DB::Aowow()->qry('DELETE FROM ::user_ratings WHERE `type` = %i AND `entry` = %i', RATING_COMMENT, $this->_post['id']);
        else
        {
            trigger_error('CommentDeletereplyResponse - deleting reply #'.$this->_post['id'].' by user #'.User::$id.' from db failed', E_USER_ERROR);
            $this->generate404(Lang::main('intError'));
        }
    }
}

?>
