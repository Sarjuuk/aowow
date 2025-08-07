<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// expects non-200 header on error
class CommentFlagreplyResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedPOST  = array(
        'id' => ['filter' => FILTER_VALIDATE_INT]
    );

    protected function generate() : void
    {
        if (!$this->assertPOST('id'))
        {
            trigger_error('CommentFlagreplyResponse - malformed request received', E_USER_ERROR);
            $this->generate404(User::isInGroup(U_GROUP_STAFF) ? 'request malformed' : '');
        }

        $replyOwner = DB::Aowow()->selectCell('SELECT `userId` FROM ?_commments WHERE `id` = ?d', $this->_post['id']);
        if (!$replyOwner)
        {
            trigger_error('CommentFlagreplyResponse - reply not found', E_USER_ERROR);
            $this->generate404(Lang::main('intError'));
        }

        // ui element should not be present
        if ($replyOwner == User::$id)
            $this->generate404();

        $report = new Report(Report::MODE_COMMENT, Report::CO_INAPPROPRIATE, $this->_post['id']);
        if (!$report->create('Report Reply Button Click'))
            $this->generate404('LANG.ct_resp_error'.$report->getError());
        else if (count($report->getSimilar()) >= CommunityContent::REPORT_THRESHOLD_AUTO_DELETE)
            DB::Aowow()->query('UPDATE ?_comments SET `flags` = `flags` | ?d WHERE `id` = ?d', CC_FLAG_DELETED, $this->_post['id']);
    }
}

?>
