<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via confirmation email link
 * write status to session and redirect to account settings
 */

// ?auth=email-change
class AccountConfirmemailaddressResponse extends TemplateResponse
{
    protected string $template = 'text-page-generic';
    protected string $pageName = 'confirm-email-address';

    protected  array  $expectedGET = array(
        'key' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[a-zA-Z0-9]{40}$/']]
    );

    private bool $success = false;

    protected function generate() : void
    {
        parent::generate();

        if (User::isBanned())
            return;

        $msg = $this->change();

        $this->inputbox = ['inputbox-status', array(
            'head'    => Lang::account('inputbox', 'head', $this->success ? 'success' : 'error'),
            'message' => $this->success ? $msg : '',
            'error'   => $this->success ? '' : $msg,
        )];
    }

    // this should probably leave change info intact for revert
    // todo - move personal settings changes to separate table
    private function change() : string
    {
        if (!$this->assertGET('key'))
            return Lang::main('intError');

        $acc = DB::Aowow()->selectRow('SELECT `updateValue`, `status`, `statusTimer` FROM ::account WHERE `token` = %s', $this->_get['key']);
        if (!$acc || $acc['status'] != ACC_STATUS_CHANGE_EMAIL || $acc['statusTimer'] < time())
            return Lang::account('inputbox', 'error', 'mailTokenUsed');

        // 0 changes == error
        if (!DB::Aowow()->qry('UPDATE ::account SET `email` = `updateValue`, `status` = %i, `statusTimer` = 0, `token` = "", `updateValue` = "" WHERE `token` = %s', ACC_STATUS_NONE, $this->_get['key']))
            return Lang::main('intError');

        $this->success = true;
        return Lang::account('inputbox', 'message', 'mailChangeOk');
    }
}

?>
