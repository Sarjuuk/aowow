<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// toggle flag
class CommentOutofdateResponse extends TextResponse
{
    protected bool   $requiresLogin = true;

    protected array  $expectedPOST  = array(
        'id'     => ['filter' => FILTER_VALIDATE_INT                                                   ],
        'remove' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 1, 'max_range' => 1]],
        'reason' => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkTextBlob']      ]
    );

    protected function generate() : void
    {
        if (!$this->assertPOST('id'))
        {
            trigger_error('CommentOutofdateResponse - malformed request received', E_USER_ERROR);
            if (User::isInGroup(U_GROUP_STAFF))
                $this->result = 'malformed request received';
        }

        $ok = false;
        if (User::isInGroup(U_GROUP_MODERATOR))             // directly mark as outdated
        {
            if (!$this->_post['remove'])
                $ok = DB::Aowow()->query('UPDATE ?_comments SET `flags` = `flags` |  ?d WHERE `id` = ?d', CC_FLAG_OUTDATED, $this->_post['id']);
            else
                $ok = DB::Aowow()->query('UPDATE ?_comments SET `flags` = `flags` & ~?d WHERE `id` = ?d', CC_FLAG_OUTDATED, $this->_post['id']);
        }
        else                                                // try to report as outdated
        {
            $report = new Report(Report::MODE_COMMENT, Report::CO_OUT_OF_DATE, $this->_post['id']);
            if (!$report->create($this->_post['reason']))
                $this->result = Lang::main('intError');

            if (count($report->getSimilar()) >= CommunityContent::REPORT_THRESHOLD_AUTO_OUT_OF_DATE)
                $ok = DB::Aowow()->query('UPDATE ?_comments SET `flags` = `flags` | ?d WHERE `id` = ?d', CC_FLAG_OUTDATED, $this->_post['id']);
        }

        if (!$ok)
        {
            trigger_error('CommentOutofdateResponse - failed to update comment in db', E_USER_ERROR);
            $this->result = Lang::main('intError');
            return;
        }

        $this->result = 'ok';                               // the js expects the actual characters 'ok' on success, not some json string like '"ok"'
    }
}

?>
