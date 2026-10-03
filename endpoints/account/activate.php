<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via activation email link
 * empty page with status box
 */

class AccountActivateResponse extends TemplateResponse
{
    protected string $template    = 'text-page-generic';
    protected string $pageName    = 'activate';

    protected array  $expectedGET = array(
        'key' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[a-zA-Z0-9]{40}$/']]
    );

    protected array $expectedPOST = array(
        'key' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[a-zA-Z0-9]{40}$/']]
    );

    private bool $success = false;

    public function __construct()
    {
        if (User::isLoggedIn())
            $this->forward('?user='.User::$username);

        parent::__construct();

        if (!Cfg::get('ACC_ALLOW_REGISTER') || Cfg::get('ACC_AUTH_MODE') != AUTH_MODE_SELF)
            $this->generateError();
    }

    protected function generate() : void
    {
        // Email scanners and link prefetches must not consume bearer tokens.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST')
        {
            $this->inputbox = ['inputbox-form-confirm', [
                'head' => Lang::account('title'),
                'action' => '?account=activate',
                'key' => $this->_get['key'] ?: ''
            ]];
            parent::generate();
            return;
        }

        $this->title[] = Lang::account('title');

        $msg = $this->activate();

        if ($this->success)
            $this->inputbox = ['inputbox-status', ['head' => Lang::account('inputbox', 'head', 'register', [2]), 'message' => $msg]];
        else
        {
            $_SESSION['error']['activate'] = $msg;
            $this->forward('?account=resend');
        }

        parent::generate();
    }

    private function activate() : string
    {
        if (!$this->assertPOST('key'))
            return Lang::main('intError');

        $activation = AccountActivation::activate($this->_post['key'], User::$ip);
        if ($activation['status'] === AccountActivation::OK)
        {
            $_SESSION['activationSignin'] = [
                'key' => $this->_post['key'], 'login' => $activation['login'], 'rememberMe' => $activation['rememberMe'],
                'expires' => time() + 5 * MINUTE
            ];

            $this->success = true;
            return Lang::account('inputbox', 'message', 'accActivated', [$this->_post['key']]);
        }

        // grace period expired and other user claimed name
        return Lang::main('intError');
    }
}

?>
