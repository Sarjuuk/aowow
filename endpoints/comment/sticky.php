<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// toggle flag
class CommentStickyResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_MODERATOR;

    protected array $expectedPOST      = array(
        'id'     => ['filter' => FILTER_VALIDATE_INT                                                   ],
        'sticky' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 0, 'max_range' => 1]]
    );

    protected function generate() : void
    {
        if (!$this->assertPOST('id', 'sticky'))
        {
            trigger_error('CommentStickyResponse - malformed request received', E_USER_ERROR);
            return;
        }

        if ($this->_post['sticky'])
            DB::Aowow()->query('UPDATE ?_comments SET `flags` = `flags` |  ?d WHERE `id` = ?d', CC_FLAG_STICKY, $this->_post['id']);
        else
            DB::Aowow()->query('UPDATE ?_comments SET `flags` = `flags` & ~?d WHERE `id` = ?d', CC_FLAG_STICKY, $this->_post['id']);
    }
}

?>
