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

    protected array $expectedPOST = array(
        'key' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[a-zA-Z0-9]{40}$/']]
    );

    private bool $success = false;

    protected function generate() : void
    {
        // Email scanners and link prefetches must not consume bearer tokens.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST')
        {
            $this->inputbox = ['inputbox-form-confirm', [
                'head' => Lang::account('title'),
                'action' => '?account=confirm-password',
                'key' => $this->_get['key'] ?: ''
            ]];
            parent::generate();
            return;
        }

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
        if (!$this->assertPOST('key'))
            return Lang::main('intError');

        $result = PasswordRecovery::confirm($this->_post['key']);
        if ($result === PasswordRecovery::INVALID_TOKEN)
            return Lang::account('inputbox', 'error', 'passTokenUsed');

        if ($result !== PasswordRecovery::OK)
            return Lang::main('intError');

        $this->success = true;
        return Lang::account('inputbox', 'message', 'passChangeOk');
    }
}

?>
