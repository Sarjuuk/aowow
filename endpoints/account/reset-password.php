<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via links on signin form and from recovery email
 *
 * A) redirect to external page
 * B) 1. click password reset link > display email form
 *    2. submit email form > send mail with recovery link
 *    3. click recovery link from mail > display password reset form
 *    4. submit password reset form > update password
 */

class AccountresetpasswordResponse extends TemplateResponse
{
    use TrRecoveryHelper, TrGetNext;

    protected string $template     = 'text-page-generic';
    protected string $pageName     = 'reset-password';

    protected array  $expectedGET  = array(
        'key'  => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[a-zA-Z0-9]{40}$/']],
        'next' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[[:print:]]+$/'   ]]
    );
    protected array  $expectedPOST = array(
        'key'        => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[a-zA-Z0-9]{40}$/']],
        'email'      => ['filter' => FILTER_VALIDATE_EMAIL,  'flags'   => FILTER_FLAG_STRIP_AOWOW            ],
        'password'   => ['filter' => FILTER_CALLBACK,        'options' => [self::class, 'checkTextLine']     ],
        'c_password' => ['filter' => FILTER_CALLBACK,        'options' => [self::class, 'checkTextLine']     ]
    );

    private bool $success = false;

    public function __construct()
    {
        $this->title[] = Lang::account('title');

        parent::__construct();

        // don't redirect logged in users
        // you can be forgetful AND logged in

        if (Cfg::get('ACC_EXT_RECOVER_URL'))
            $this->forward(Cfg::get('ACC_EXT_RECOVER_URL'));

        if (Cfg::get('ACC_AUTH_MODE') != AUTH_MODE_SELF)
            $this->generateError();
    }

    protected function generate() : void
    {
        parent::generate();

        $errMsg = '';
        if (!$this->assertGET('key') && !$this->assertPOST('key'))
            $errMsg = Lang::account('inputbox', 'error', 'passTokenLost');
        else if ($this->_get['key'] && !DB::Aowow()->selectCell('SELECT 1 FROM ?_account WHERE `token` = ? AND `status` = ?d AND `statusTimer` > UNIX_TIMESTAMP()', $this->_get['key'], ACC_STATUS_RECOVER_PASS))
            $errMsg = Lang::account('inputbox', 'error', 'passTokenUsed');

        if ($errMsg)
        {
            $this->inputbox = ['inputbox-status', array(
                'head'  => Lang::account('inputbox', 'head', 'error'),
                'error' => $errMsg
            )];

            return;
        }

        // step "2.5"
        $errMsg = $this->doResetPass();
        if ($this->success)
            $this->forward('?account=signin');

        // step 2
        $this->inputbox = ['inputbox-form-password', array(
            'head'   => Lang::account('inputbox', 'head', 'recoverPass', [2]),
            'token'  => $this->_post['key'] ?? $this->_get['key'],
            'action' => '?account=reset-password&next=account=signin',
            'error'  => $errMsg,
        )];
    }

    private function doResetPass() : string
    {
        // no input yet. show clean form
        if (!$this->assertPOST('key', 'password', 'c_password') && is_null($this->_post['email']))
            return '';

        // truncated due to validation fail
        if (!$this->_post['email'])
            return Lang::account('emailInvalid');

        if ($this->_post['password'] != $this->_post['c_password'])
            return Lang::account('passCheckFail');

        $userData = DB::Aowow()->selectRow('SELECT `id`, `passHash` FROM ?_account WHERE `token` = ? AND `email` = ? AND `status` = ?d AND `statusTimer` > UNIX_TIMESTAMP()',
            $this->_post['key'],
            $this->_post['email'],
            ACC_STATUS_RECOVER_PASS
        );
        if (!$userData)
            return Lang::account('inputbox', 'error', 'emailNotFound');

        if (!User::verifyCrypt($this->_post['c_password'], $userData['passHash']))
            return Lang::account('newPassDiff');

        if (!DB::Aowow()->query('UPDATE ?_account SET `passHash` = ?, `status` = ?d WHERE `id` = ?d', User::hashCrypt($this->_post['c_password']), ACC_STATUS_NONE, $userData['id']))
            return Lang::main('intError');

        $this->success = true;
        return '';
    }
}

?>
