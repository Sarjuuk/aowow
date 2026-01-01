<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfilePublicResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedGET   = array(
        'id'         => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdList']     ],
        'user'       => ['filter' => FILTER_CALLBACK, 'options' => [Util::class, 'validateUsername']],
     // 'bookmarked' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkEmptySet']   ] // something with signatures? (must have bookmarked profile to create signature from)
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
    protected function generate() : void
    {
        if (!$this->assertGET('id'))
        {
            trigger_error('ProfilePublicResponse - profileId empty', E_USER_ERROR);
            return;
        }

        if ($this->_get['user'] && User::$username != $this->_get['user'] && !User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
        {
            trigger_error('ProfilePublicResponse - user #'.User::$id.' tried to mark profiles of "'.$this->_get['user'].'" as public.', E_USER_ERROR);
            return;
        }

        $uid = 0;
        if (!$this->_get['user'] || User::$username == $this->_get['user'])
            $uid = User::$id;
        else if ($this->_get['user'] && User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
            $uid = DB::Aowow()->selectCell('SELECT `id` FROM ?_account WHERE LOWER(`username`) = LOWER(?)', $this->_get['user']);

        if (!$uid)
        {
            trigger_error('ProfilePublicResponse - user "'.$this->_get['user'].'" does not exist', E_USER_ERROR);
            return;
        }

        DB::Aowow()->query('UPDATE ?_account_profiles  SET `extraFlags` = `extraFlags` | ?d WHERE `profileId` IN (?a) AND `accountId` = ?d', PROFILER_CU_PUBLISHED, $this->_get['id'], $uid);
        DB::Aowow()->query('UPDATE ?_profiler_profiles SET `cuFlags`    = `cuFlags`    | ?d WHERE `id`        IN (?a) AND `user`      = ?d', PROFILER_CU_PUBLISHED, $this->_get['id'], $uid);
    }
}

?>
