<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// returns all replies on success
// must have non-200 header on error
class CommentAddreplyResponse extends TextResponse
{
    protected bool   $requiresLogin = true;

    protected array  $expectedPOST  = array(
        'commentId' => ['filter' => FILTER_VALIDATE_INT                                         ],
        'replyId'   => ['filter' => FILTER_VALIDATE_INT                                         ],
        'body'      => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextBlob']]
    );

    protected function generate(): void
    {
        if (!$this->assertPOST('commentId', 'replyId', 'body'))
        {
            trigger_error('CommentAddreplyResponse - malformed request received', E_USER_ERROR);
            $this->generate404(User::isInGroup(U_GROUP_STAFF) ? 'request malformed' : '');
        }

        if (!User::canReply())
            $this->generate404(Lang::main('cannotComment'));

        if (!$this->_post['commentId'] || !DB::Aowow()->selectCell('SELECT 1 FROM ::comments WHERE `id` = %i', $this->_post['commentId']))
        {
            trigger_error('CommentAddreplyResponse - parent comment #'.$this->_post['commentId'].' does not exist', E_USER_ERROR);
            $this->generate404(Lang::main('intError'));
        }

        if (mb_strlen($this->_post['body']) < CommunityContent::REPLY_LENGTH_MIN || mb_strlen($this->_post['body']) > CommunityContent::REPLY_LENGTH_MAX)
            $this->generate404(Lang::main('textLength', [mb_strlen($this->_post['body']), CommunityContent::REPLY_LENGTH_MIN, CommunityContent::REPLY_LENGTH_MAX]));

        if (!DB::Aowow()->qry('INSERT INTO ::comments (`userId`, `roles`, `body`, `date`, `replyTo`) VALUES (%i, %i, %s, UNIX_TIMESTAMP(), %i)', User::$id, User::$groups, $this->_post['body'], $this->_post['commentId']))
        {
            trigger_error('CommentAddreplyResponse - write to db failed', E_USER_ERROR);
            $this->generate404(Lang::main('intError'));
        }

        $this->result = Util::toJSON(CommunityContent::getCommentReplies($this->_post['commentId']));
    }
}

?>
