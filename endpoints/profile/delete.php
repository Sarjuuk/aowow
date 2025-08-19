<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfileDeleteResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedGET   = array(
        'id' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdList']],
    );

    public function __construct(string $pageParam)
    {
        parent::__construct($pageParam);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generate404();
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
        return
            null
    */
    protected function generate() : void
    {
        if (!$this->assertGET('id'))
        {
            trigger_error('ProfileDeleteResponse - profileId empty', E_USER_WARNING);
            return;
        }

        // only flag as deleted; only custom profiles
        DB::Aowow()->query(
           'UPDATE ?_profiler_profiles SET `cuFlags` = `cuFlags` | ?d WHERE `id` IN (?a) AND `cuFlags` & ?d {AND `user` = ?d}',
            PROFILER_CU_DELETED,
            $this->_get['id'],
            PROFILER_CU_PROFILE,
            User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU) ? DBSIMPLE_SKIP : User::$id
        );
    }
}

?>
