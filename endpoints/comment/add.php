<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CommentAddResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedPOST  = array(
        'commentbody' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextBlob']]
    );
    protected array $expectedGET   = array(
        'type'   => ['filter' => FILTER_VALIDATE_INT],
        'typeid' => ['filter' => FILTER_VALIDATE_INT]
    );

    // i .. have problems believing, that everything uses nifty ajax while adding comments requires a brutal header(Loacation: <wherever>), yet, thats how it is
    protected function generate() : void
    {
        if (!$this->assertGET('type', 'typeid') || !$this->assertPOST('commentbody') || !Type::validateIds($this->_get['type'], $this->_get['typeid']))
        {
            trigger_error('CommentAddResponse - malforemd request received', E_USER_ERROR);
            return;                                      // whatever, we cant even send him back
        }

        // we now have a valid return target
        $idOrUrl = $this->_get['typeid'];
        if ($this->_get['type'] == Type::GUIDE)
            if ($_ = DB::Aowow()->selectCell('SELECT `url` FROM ::guides WHERE `id` = %i', $this->_get['typeid']))
                $idOrUrl = $_;

        $this->redirectTo = '?'.Type::getFileString($this->_get['type']).'='.$idOrUrl.'#comments';

        // this type cannot be commented on
        if (!Type::checkClassAttrib($this->_get['type'], 'contribute', CONTRIBUTE_CO))
        {
            trigger_error('CommentAddResponse - tried to comment on unsupported type: '.Type::getFileString($this->_get['type']), E_USER_ERROR);
            $_SESSION['error']['co'] = Lang::main('intError');
            return;
        }

        if (!User::canComment())
        {
            $_SESSION['error']['co'] = Lang::main('cannotComment');
            return;
        }

        $len = mb_strlen($this->_post['commentbody']);

        if ((!User::isInGroup(U_GROUP_MODERATOR) && $len < CommunityContent::COMMENT_LENGTH_MIN) || ($len > CommunityContent::COMMENT_LENGTH_MAX * (User::isPremium() ? 3 : 1)))
        {
            $_SESSION['error']['co'] = Lang::main('textLength', [$len, CommunityContent::COMMENT_LENGTH_MIN, CommunityContent::COMMENT_LENGTH_MAX * (User::isPremium() ? 3 : 1)]);
            return;
        }

        if ($postId = DB::Aowow()->qry('INSERT INTO ::comments (`type`, `typeId`, `userId`, `roles`, `body`, `date`) VALUES (%i, %i, %i, %i, %s, UNIX_TIMESTAMP())', $this->_get['type'], $this->_get['typeid'], User::$id, User::$groups, $this->_post['commentbody']))
        {
            Util::gainSiteReputation(User::$id, SITEREP_ACTION_COMMENT, ['id' => $postId]);

            // every comment starts with a rating of +1 and i guess the simplest thing to do is create a db-entry with the system as owner
            DB::Aowow()->qry('INSERT INTO ::user_ratings (`type`, `entry`, `userId`, `value`) VALUES (%i, %i, 0, 1)', RATING_COMMENT, $postId);

            // flag target with hasComment
            if ($tbl = Type::getClassAttrib($this->_get['type'], 'dataTable'))
                DB::Aowow()->qry('UPDATE %n SET `cuFlags` = `cuFlags` | %i WHERE `id` = %i', $tbl, CUSTOM_HAS_COMMENT, $this->_get['typeid']);

            return;
        }

        trigger_error('CommentAddResponse - write to db failed', E_USER_ERROR);
        $_SESSION['error']['co'] = Lang::main('intError');
    }
}

?>
