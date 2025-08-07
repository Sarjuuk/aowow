<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CommentEditResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedPOST  = array(
        'body'     => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextBlob']],
        'response' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextBlob']]
    );
    protected array $expectedGET   = array(
        'id' => ['filter' => FILTER_VALIDATE_INT]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id') || !$this->assertPOST('body'))
        {
            trigger_error('CommentEditResponse - malforemd request received', E_USER_ERROR);
            return;
        }

        $ownerId = DB::Aowow()->selectCell('SELECT `userId` FROM ?_comments WHERE `id` = ?d', $this->_get['id']);

        if (!User::canComment() || (User::$id != $ownerId && !User::isInGroup(U_GROUP_MODERATOR)))
        {
            trigger_error('CommentEditResponse - user #'.User::$id.' not allowed to edit comment #'.$this->_get['id'], E_USER_ERROR);
            return;
        }

        if (!User::isInGroup(U_GROUP_MODERATOR) && mb_strlen($this->_post['body']) < CommunityContent::COMMENT_LENGTH_MIN)
            return;                                         // no point in reporting this trifle

        // trim to max length
        if (!User::isInGroup(U_GROUP_MODERATOR))
            $this->_post['body'] = mb_substr($this->_post['body'], 0, (CommunityContent::COMMENT_LENGTH_MAX * (User::isPremium() ? 3 : 1)));

        $update = array(
            'body'       => $this->_post['body'],
            'editUserId' => User::$id,
            'editDate'   => time()
        );
        if (User::$id == $ownerId)
            $update['roles'] = User::$groups;

        if (User::isInGroup(U_GROUP_MODERATOR))
        {
            $update['responseBody']   = $this->_post['response'] ?? '';
            $update['responseUserId'] = User::$id;
            $update['responseRoles']  = User::$groups;
        }

        DB::Aowow()->query('UPDATE ?_comments SET `editCount` = `editCount` + 1, ?a WHERE `id` = ?d', $update, $this->_get['id']);
    }
}

?>
