<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfileLinkResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedGET   = array(
        'id' => ['filter' => FILTER_VALIDATE_INT]
    );

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generate404();
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
        return:
            null
    */
    protected function generate() : void                    // links char with account
    {
        if (!$this->assertGET('id'))
        {
            trigger_error('ProfileLinkResponse - profileId empty', E_USER_ERROR);
            return;
        }

        // only link characters, not custom profiles
        $newId = DB::Aowow()->query(
           'REPLACE INTO ?_account_profiles (`accountId`, `profileId`, `extraFlags`)
            SELECT ?d, p.`id`, 0 FROM ?_profiler_profiles p WHERE p.`id` = ?d AND `custom` = 0',
            User::$id, $this->_get['id']
        );

        if (!is_int($newId))
            trigger_error('ProfileLinkResponse - some of the profileIds were custom or do not exist', E_USER_ERROR);
    }
}

?>
