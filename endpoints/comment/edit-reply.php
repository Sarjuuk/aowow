<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// returns all replies on success
// must have non-200 header on error
class CommentEditreplyResponse extends TextResponse
{
    protected bool   $requiresLogin = true;

    protected array  $expectedPOST  = array(
        'commentId' => ['filter' => FILTER_VALIDATE_INT                                         ],
        'replyId'   => ['filter' => FILTER_VALIDATE_INT                                         ],
        'body'      => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextBlob']]
    );

    protected function generate() : void
    {
        if (!$this->assertPOST('commentId', 'replyId', 'body'))
        {
            trigger_error('CommentEditreplyResponse - malformed request received', E_USER_ERROR);
            $this->generate404(User::isInGroup(U_GROUP_STAFF) ? 'request malformed' : '');
        }

        $ownerId = DB::Aowow()->selectCell('SELECT `userId` FROM ?_comments WHERE `id` = ?d AND `replyTo` = ?d', $this->_post['replyId'], $this->_post['commentId']);

        if (!User::canReply() || (User::$id != $ownerId && !User::isInGroup(U_GROUP_MODERATOR)))
            $this->generate404(Lang::main('cannotComment'));

        if (!$ownerId)
        {
            trigger_error('CommentEditreplyResponse - comment #'.$this->_post['commentId'].' or reply #'.$this->_post['replyId'].' does not exist', E_USER_ERROR);
            $this->generate404(Lang::main('intError'));
        }

        if (mb_strlen($this->_post['body']) < CommunityContent::REPLY_LENGTH_MIN || mb_strlen($this->_post['body']) > CommunityContent::REPLY_LENGTH_MAX)
            $this->generate404(Lang::main('textLength', [mb_strlen($this->_post['body']), CommunityContent::REPLY_LENGTH_MIN, CommunityContent::REPLY_LENGTH_MAX]));

        $update = array(
            'body'       => $this->_post['body'],
            'editUserId' => User::$id,
            'editDate'   => time()
        );
        if (User::$id == $ownerId)
            $update['roles'] = User::$groups;

        if (!DB::Aowow()->query('UPDATE ?_comments SET `editCount` = `editCount` + 1, ?a WHERE `id` = ?d AND `replyTo` = ?d { AND `userId` = ?d }',
            $update, $this->_post['replyId'], $this->_post['commentId'], User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id))
        {
            trigger_error('CommentEditreplyResponse - write to db failed', E_USER_ERROR);
            $this->generate404(Lang::main('intError'));
        }

        $this->result = Util::toJSON(CommunityContent::getCommentReplies($this->_post['commentId']));
    }
}

?>
