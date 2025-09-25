<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminGuideResponse extends TextResponse
{
    private const /* int */ ERR_NONE          = 0;
    private const /* int */ ERR_GUIDE         = 1;
    private const /* int */ ERR_STATUS        = 2;
    private const /* int */ ERR_WRITE_DB      = 3;
    private const /* int */ ERR_MISCELLANEOUS = 999;

    protected int   $requiredUserGroup = U_GROUP_STAFF;

    protected array $expectedPOST      = array(
        'id'     => ['filter' => FILTER_VALIDATE_INT                                                                                                   ],
        'status' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => GuideMgr::STATUS_APPROVED, 'max_range' => GuideMgr::STATUS_REJECTED]],
        'msg'    => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkTextBlob']                                                      ]
    );

    protected function generate() : void
    {
        if (!$this->assertPOST('id', 'status'))
        {
            trigger_error('AdminGuideResponse - malformed request received', E_USER_ERROR);
            $this->result = self::ERR_MISCELLANEOUS;
            return;
        }

        $guide = DB::Aowow()->selectRow('SELECT `userId`, `status` FROM ?_guides WHERE `id` = ?d', $this->_post['id']);
        if (!$guide)
        {
            trigger_error('AdminGuideResponse - guide #'.$this->_post['id'].' not found', E_USER_ERROR);
            $this->result = self::ERR_GUIDE;
            return;
        }

        if ($this->_post['status'] == $guide['status'])
        {
            trigger_error('AdminGuideResponse - guide #'.$this->_post['id'].' already has status #'.$this->_post['status'], E_USER_ERROR);
            $this->result = self::ERR_STATUS;
            return;
        }

        // status can only be APPROVED or REJECTED due to input validation
        if (!$this->update($this->_post['id'], $this->_post['status'], $this->_post['msg']))
        {
            trigger_error('AdminGuideResponse - write to db failed for guide #'.$this->_post['id'], E_USER_ERROR);
            $this->result = self::ERR_WRITE_DB;
            return;
        }

        if ($this->_post['status'] == GuideMgr::STATUS_APPROVED)
            Util::gainSiteReputation($guide['userId'], SITEREP_ACTION_ARTICLE, ['id' => $this->_post['id']]);

        $this->result = self::ERR_NONE;
    }

    private function update(int $id, int $status, ?string $msg = null) : bool
    {
        if ($status == GuideMgr::STATUS_APPROVED)           // set display rev to latest
            $ok = DB::Aowow()->query('UPDATE ?_guides SET `status` = ?d, `rev` = (SELECT `rev` FROM ?_articles WHERE `type` = ?d AND `typeId` = ?d ORDER BY `rev` DESC LIMIT 1), `approveUserId` = ?d, `approveDate` = ?d WHERE `id` = ?d', $status, Type::GUIDE, $id, User::$id, time(), $id);
        else
            $ok = DB::Aowow()->query('UPDATE ?_guides SET `status` = ?d WHERE `id` = ?d', $status, $id);

        if (!$ok)
            return false;

        DB::Aowow()->query('INSERT INTO ?_guides_changelog (`id`, `date`, `userId`, `status`) VALUES (?d, ?d, ?d, ?d)', $id, time(), User::$id, $status);
        if ($msg)
            DB::Aowow()->query('INSERT INTO ?_guides_changelog (`id`, `date`, `userId`, `msg`) VALUES (?d, ?d, ?d, ?)', $id, time(), User::$id, $msg);

        return true;
    }
}

?>
