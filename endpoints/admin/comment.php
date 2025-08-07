<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminCommentResponse extends TextResponse
{
    private const /* int */ ERR_NONE          = 1;
    private const /* int */ ERR_WRITE_DB      = 0;
    private const /* int */ ERR_MISCELLANEOUS = 999;

    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_MOD;

    protected array $expectedPOST      = array(
        'id'     => ['filter' => FILTER_VALIDATE_INT                                                   ],
        'status' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 0, 'max_range' => 1]]
    );

    protected function generate() : void
    {
        if (!$this->assertPOST('id', 'status'))
        {
            trigger_error('AdminCommentResponse - malformed request received', E_USER_ERROR);
            $this->result = self::ERR_MISCELLANEOUS;
            return;
        }

        // check if is marked as outdated CC_FLAG_OUTDATED?

        $ok = false;
        if ($this->_post['status'])                         // outdated, mark as deleted and clear other flags (sticky + outdated)
        {
            if ($ok = DB::Aowow()->query('UPDATE ?_comments SET `flags` = ?d, `deleteUserId` = ?d, `deleteDate` = ?d WHERE `id` = ?d', CC_FLAG_DELETED, User::$id, time(), $this->_post['id']))
                if ($rep = new Report(Report::MODE_COMMENT, Report::CO_OUT_OF_DATE, $this->_post['id']))
                    $rep->close(Report::STATUS_CLOSED_SOLVED);
        }
        else                                                // up to date
        {
            if ($ok = DB::Aowow()->query('UPDATE ?_comments SET `flags` = `flags` & ~?d WHERE `id` = ?d', CC_FLAG_OUTDATED, $this->_post['id']))
                if ($rep = new Report(Report::MODE_COMMENT, Report::CO_OUT_OF_DATE, $this->_post['id']))
                    $rep->close(Report::STATUS_CLOSED_WONTFIX);
        }

        $this->result = $ok ? self::ERR_NONE : self::ERR_WRITE_DB;
    }
}

?>
