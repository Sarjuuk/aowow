<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CommentDeleteResponse extends TextResponse
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
            trigger_error('CommentDeleteResponse - malformed request received', E_USER_ERROR);
            return;
        }

        // in theory, there is a username passed alongside if executed from userpage...   lets just use the current user (see user.js)
        $ok = DB::Aowow()->query('UPDATE ?_comments SET `flags` = `flags` | ?d, `deleteUserId` = ?d, `deleteDate` = UNIX_TIMESTAMP() WHERE `id` IN (?a) { AND `userId` = ?d }',
            CC_FLAG_DELETED,
            User::$id,
            $this->_post['id'],
            User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id
        );

        // unflag subject: hasComment
        if ($ok)
        {
            $coInfo = DB::Aowow()->select(
               'SELECT IF(BIT_OR(~b.`flags`) & ?d, 1, 0) AS "0", b.`type` AS "1", b.`typeId` AS "2" FROM ?_comments a JOIN ?_comments b ON a.`type` = b.`type` AND a.`typeId` = b.`typeId` WHERE a.`id` IN (?a) GROUP BY b.`type`, b.`typeId`',
                CC_FLAG_DELETED, $this->_post['id']
            );

            foreach ($coInfo as [$hasMore, $type, $typeId])
                if (!$hasMore && ($tbl = Type::getClassAttrib($type, 'dataTable')))
                    DB::Aowow()->query('UPDATE ?# SET `cuFlags` = `cuFlags` & ~?d WHERE `id` = ?d', $tbl, CUSTOM_HAS_COMMENT, $typeId);

            return;
        }

        trigger_error('CommentDeleteResponse - user #'.User::$id.' could not flag comment(s) #'.implode(', ', $this->_post['id']).' as deleted', E_USER_ERROR);
    }
}

?>
