<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfileUnlinkResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedGET   = array(
        'id'   => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdList']     ],
        'user' => ['filter' => FILTER_CALLBACK, 'options' => [Util::class, 'validateUsername']]
    );

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generate404();
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
            user: <string> [optional]                       // user page this is may be executed from
        return:
            null
    */
    protected function generate() : void                    // links char with account
    {
        if (!$this->assertGET('id'))
        {
            trigger_error('ProfileUnlinkResponse - profileId empty', E_USER_ERROR);
            return;
        }

        if ($this->_get['user'] && User::$username != $this->_get['user'] && !User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
        {
            trigger_error('ProfileUnlinkResponse - user #'.User::$id.' tried to unlink profiles from "'.$this->_get['user'], E_USER_ERROR);
            return;
        }

        $uid = 0;
        if (!$this->_get['user'] || User::$username == $this->_get['user'])
            $uid = User::$id;
        else if ($this->_get['user'] && User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
            $uid = DB::Aowow()->selectCell('SELECT `id` FROM ?_account WHERE LOWER(`username`) = LOWER(?)', $this->_get['user']);

        if (!$uid)
        {
            trigger_error('ProfileUnlinkResponse - user "'.$this->_get['user'].'" does not exist', E_USER_ERROR);
            return;
        }

        DB::Aowow()->query('DELETE FROM ?_account_profiles WHERE `accountId` = ?d AND `profileId` IN (?a)', $uid, $this->_get['id']);
    }
}

?>
