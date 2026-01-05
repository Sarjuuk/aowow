<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CommentUndeleteResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedPOST  = array(
        'id'       => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdListUnsigned']],
     // 'username' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']      ]
    );

    protected function generate() : void
    {
        if (!$this->assertPOST('id'))
        {
            trigger_error('CommentUndeleteResponse - malformed request received', E_USER_ERROR);
            return;
        }

        // in theory, there is a username passed alongside if executed from userpage...   lets just use the current user (see user.js)
        $where = [['`id` IN %in', $this->_post['id']]];
        if (!User::isInGroup(U_GROUP_MODERATOR))
        {
            $where[] = ['`deleteUserId` = `userId'];
            $where[] = ['`deleteUserId` = %i', User::$id];
        }

        // unflag subject: hasComment
        if (DB::Aowow()->qry('UPDATE ::comments SET `flags` = `flags` & ~%i WHERE %and', CC_FLAG_DELETED, $where))
        {
            $coInfo = DB::Aowow()->selectAssoc('SELECT `type` AS "0", `typeId` AS "1" FROM ::comments WHERE `id` IN %in GROUP BY `type`, `typeId`', $this->_post['id']);
            foreach ($coInfo as [$type, $typeId])
                if ($tbl = Type::getClassAttrib($type, 'dataTable'))
                    DB::Aowow()->qry('UPDATE %n SET `cuFlags` = `cuFlags` | %i WHERE `id` = %i', $tbl, CUSTOM_HAS_COMMENT, $typeId);

            return;
        }

        trigger_error('CommentUndeleteResponse - user #'.User::$id.' could not unflag comment(s) #'.implode(', ', $this->_post['id']).' from deleted', E_USER_ERROR);
    }
}

?>
