<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed after successful resend request
 * empty page with status box
 */

class AccountResendsubmitResponse extends TemplateResponse
{
    protected string $template     = 'text-page-generic';
    protected string $pageName     = 'resend';

    protected array  $expectedPOST = array(
        'email' => ['filter' => FILTER_VALIDATE_EMAIL, 'flags' => FILTER_FLAG_STRIP_AOWOW]
    );

    public function __construct(string $rawParam)
    {
        if (!Cfg::get('ACC_ALLOW_REGISTER') || Cfg::get('ACC_AUTH_MODE') != AUTH_MODE_SELF)
            $this->generateError();

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        $this->title[] = Lang::account('title');

        $error = $message = '';

        if ($this->assertPOST('email'))
            $message = Lang::account('inputbox', 'message', 'createAccSent', [$this->_post['email']]);
        else
            $error = Lang::main('intError');

        parent::generate();

        $this->inputbox = ['inputbox-status', array(
            'head'    => Lang::account('inputbox', 'head', 'register', [1.5]),
            'message' => $message,
            'error'   => $error
        )];
    }
}

?>
