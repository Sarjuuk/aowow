<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via confirmation email link
 * write status to session and redirect to account settings
 */

// 2025 - no longer in use?
class AccountConfirmpasswordResponse extends TemplateResponse
{
    protected string $template = 'text-page-generic';
    protected string $pageName = 'confirm-password';

    protected  array  $expectedGET = array(
        'key' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[a-zA-Z0-9]{40}$/']]
    );

    private bool $success = false;

    protected function generate() : void
    {
        parent::generate();

        if (User::isBanned())
            return;

        $msg = $this->confirm();

        $this->inputbox = ['inputbox-status', array(
            'head'    => Lang::account('inputbox', 'head', $this->success ? 'success' : 'error'),
            'message' => $this->success ? $msg : '',
            'error'   => $this->success ? '' : $msg,
        )];
    }

    private function confirm() : string
    {
        if (!$this->assertGET('key'))
            return Lang::main('intError');

        $acc = DB::Aowow()->selectRow('SELECT `updateValue`, `status`, `statusTimer` FROM ?_account WHERE `token` = ?', $this->_get['key']);
        if (!$acc || $acc['status'] != ACC_STATUS_CHANGE_PASS || $acc['statusTimer'] < time())
            return Lang::account('inputbox', 'error', 'passTokenUsed');

        // 0 changes == error
        if (!DB::Aowow()->query('UPDATE ?_account SET `passHash` = `updateValue`, `status` = ?d, `statusTimer` = 0, `token` = "", `updateValue` = "" WHERE `token` = ?', ACC_STATUS_NONE, $this->_get['key']))
            return Lang::main('intError');

        $this->success = true;
        return Lang::account('inputbox', 'message', 'passChangeOk');
    }
}

?>
