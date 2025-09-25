<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfileUnpinResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedGET   = array(
        'id'   => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdList']     ],
        'user' => ['filter' => FILTER_CALLBACK, 'options' => [Util::class, 'validateUsername']]
    );

    public function __construct(string $pageParam)
    {
        parent::__construct($pageParam);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generate404();
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
            user: <string> [optional]
        return: null
    */
    protected function generate() : void                   // (un)favorite
    {
        if (!$this->assertGET('id'))
        {
            trigger_error('ProfileUnpinResponse - profileId empty', E_USER_ERROR);
            return;
        }

        $uid = 0;
        if (!$this->_get['user'] || User::$username == $this->_get['user'])
            $uid = User::$id;
        else if ($this->_get['user'] && User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
            $uid = DB::Aowow()->selectCell('SELECT `id` FROM ?_account WHERE LOWER(`username`) = LOWER(?)', $this->_get['user']);

        if (!$uid)
        {
            trigger_error('ProfileUnpinResponse - user "'.$this->_get['user'].'" does not exist', E_USER_ERROR);
            return;
        }

        DB::Aowow()->query('UPDATE ?_account_profiles SET `extraFlags` = `extraFlags` & ~?d WHERE `accountId` = ?d', PROFILER_CU_PINNED, $uid);
    }
}

?>
