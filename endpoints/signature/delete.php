<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SignatureDeleteResponse extends TextResponse
{
    protected bool  $requiresLogin  = true;
    protected array $expectedGET    = array(
        'id' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdListUnsigned']]
    );

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        if (!$this->assertGET('id'))
            $this->generate404();

        // DB::Aowow()->query(DELETE FROM ?_account_signatures WHERE `id` IN (?a) { AND `accountId` = ?d }', $this->_get['id], User::isInGroup(U_GROUP_MODERATOR) ? DBSIMPLE_SKIP : User::$id);
    }
}

?>
