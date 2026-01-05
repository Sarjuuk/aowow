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

        // DB::Aowow()->qry(DELETE FROM ::account_signatures WHERE %if', !User::isInGroup(U_GROUP_MODERATOR), '`accountId` = %i AND', User::$id, '%end `id` IN %in', $this->_get['id]);
    }
}

?>
