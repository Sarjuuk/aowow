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
        $ok = DB::Aowow()->query('UPDATE ?_comments SET `flags` = `flags` & ~?d WHERE `id` IN (?a) { AND `userId` = `deleteUserId` AND `deleteUserId` = ?d }',
            CC_FLAG_DELETED,
            $this->_post['id'],
            User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id
        );

        // unflag subject: hasComment
        if ($ok)
        {
            $coInfo = DB::Aowow()->select('SELECT `type` AS "0", `typeId` AS "1" FROM ?_comments WHERE `id` IN (?a) GROUP BY `type`, `typeId`', $this->_post['id']);
            foreach ($coInfo as [$type, $typeId])
                if ($tbl = Type::getClassAttrib($type, 'dataTable'))
                    DB::Aowow()->query('UPDATE ?# SET `cuFlags` = `cuFlags` | ?d WHERE `id` = ?d', $tbl, CUSTOM_HAS_COMMENT, $typeId);

            return;
        }

        trigger_error('CommentUndeleteResponse - user #'.User::$id.' could not unflag comment(s) #'.implode(', ', $this->_post['id']).' from deleted', E_USER_ERROR);
    }
}

?>
