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
        $where = [['`id` IN %in', $this->_post['id']]];
        if (!User::isInGroup(U_GROUP_MODERATOR))
            $where[] = ['`userId` = %i',  User::$id];

        // flag as deleted; unflag subject: hasComment
        if (DB::Aowow()->qry('UPDATE ::comments SET `flags` = `flags` | %i, `deleteUserId` = %i, `deleteDate` = UNIX_TIMESTAMP() WHERE %and', CC_FLAG_DELETED, User::$id, $where))
        {
            $coInfo = DB::Aowow()->selectAssoc(
               'SELECT IF(BIT_OR(~b.`flags`) & %i, 1, 0) AS "0", b.`type` AS "1", b.`typeId` AS "2" FROM ::comments a JOIN ::comments b ON a.`type` = b.`type` AND a.`typeId` = b.`typeId` WHERE a.`id` IN %in GROUP BY b.`type`, b.`typeId`',
                CC_FLAG_DELETED, $this->_post['id']
            );

            foreach ($coInfo as [$hasMore, $type, $typeId])
                if (!$hasMore && ($tbl = Type::getClassAttrib($type, 'dataTable')))
                    DB::Aowow()->qry('UPDATE %n SET `cuFlags` = `cuFlags` & ~%i WHERE `id` = %i', $tbl, CUSTOM_HAS_COMMENT, $typeId);

            return;
        }

        trigger_error('CommentDeleteResponse - user #'.User::$id.' could not flag comment(s) #'.implode(', ', $this->_post['id']).' as deleted', E_USER_ERROR);
    }
}

?>
