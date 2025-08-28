<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
    2 modes
    A) show form
    B) execute login and forward to
        * self on failure
        * next on success
*/

class AccountSigninResponse extends TemplateResponse
{
    use TrGetNext;

    protected string $template     = 'text-page-generic';
    protected string $pageName     = 'signin';

    protected array  $expectedPOST = array(
        'username'    => ['filter' => FILTER_CALLBACK, 'options' => [Util::class, 'validateLogin']   ],
        'password'    => ['filter' => FILTER_CALLBACK, 'options' => [Util::class, 'validatePassword']],
        'remember_me' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkRememberMe'] ]
    );
    protected array  $expectedGET  = array(
        'key'  => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[a-zA-Z0-9]{40}$/']],
        'next' => ['filter' => FILTER_SANITIZE_URL,    'flags'   => FILTER_FLAG_STRIP_AOWOW            ]
    );

    private bool $success = false;

    public function __construct()
    {
        // if the user is logged in, goto user dashboard
        if (User::isLoggedIn())
            $this->forward('?user='.User::$username);

        parent::__construct();
    }

    protected function generate() : void
    {
        $username   =
        $error      = '';
        $rememberMe = !!$this->_post['remember_me'];

        $this->title = [Lang::account('title')];

        // coming from user recovery or creation, prefill username
        if ($this->_get['key'])
        {
            if ($userData = DB::Aowow()->selectRow('SELECT a.`login` AS "0", IF(s.`expires`, 0, 1) AS "1" FROM ?_account a LEFT JOIN ?_account_sessions s ON a.`id` = s.`userId` AND a.`token` = s.`sessionId` WHERE a.`status` IN (?a) AND a.`token` = ?',
                [ACC_STATUS_RECOVER_USER, ACC_STATUS_NONE], $this->_get['key']))
                [$username, $rememberMe] = $userData;
        }

        if ($this->doSignIn($error))
            $this->forward($this->getNext(true));

        if ($error)
            User::destroy();

        $this->inputbox = ['inputbox-form-signin', array(
            'head'        => Lang::account('inputbox', 'head', 'signin'),
            'action'      => '?account=signin&next='.$this->getNext(),
            'error'       => $error,
            'username'    => $username,
            'rememberMe'  => $rememberMe,
            'hasRecovery' => Cfg::get('ACC_EXT_RECOVER_URL') || Cfg::get('ACC_AUTH_MODE') == AUTH_MODE_SELF,
        )];

        parent::generate();
    }

    private function doSignIn(string &$error) : bool
    {
        if (is_null($this->_post['username']) && is_null($this->_post['password']))
            return false;

        if (!$this->assertPOST('username'))
        {
            $error = Lang::account('userNotFound');
            return false;
        }

        if (!$this->assertPOST('password'))
        {
            $error = Lang::account('wrongPass');
            return false;
        }

        $error = match (User::authenticate($this->_post['username'], $this->_post['password']))
        {
            AUTH_OK, AUTH_BANNED => $this->onAuthSuccess(),
         // AUTH_BANNED          => Lang::account('accBanned'); // ToDo: should this return an error? the actual account functionality should be blocked elsewhere
            AUTH_WRONGUSER       => Lang::account('userNotFound'),
            AUTH_WRONGPASS       => Lang::account('wrongPass'),
            AUTH_IPBANNED        => Lang::account('inputbox', 'error', 'loginExceeded', [Util::formatTime(Cfg::get('ACC_FAILED_AUTH_BLOCK') * 1000)]),
            AUTH_INTERNAL_ERR    => Lang::main('intError'),
            default              => Lang::main('intError')
        };

        return !$error;
    }

    private function onAuthSuccess() : string
    {
        if (!User::$ip)
        {
            trigger_error('AccountSigninResponse::onAuthSuccess() - tried to login user without ip set', E_USER_ERROR);
            return Lang::main('intError');
        }

        // reset account status, update expiration
        $ok = DB::Aowow()->query('UPDATE ?_account SET `prevIP` = IF(`curIp` = ?, `prevIP`, `curIP`), `curIP` = IF(`curIp` = ?, `curIP`, ?), `status` = IF(`status` = ?d, `status`, 0), `statusTimer` = IF(`status` = ?d, `statusTimer`, 0), `token` = IF(`status` = ?d, `token`, "") WHERE `id` = ?d',
            User::$ip, User::$ip, User::$ip,
            ACC_STATUS_NEW, ACC_STATUS_NEW, ACC_STATUS_NEW,
            User::$id                                       // available after successful User:authenticate
        );

        if (!is_int($ok))                                   // num updated fields or null on fail
        {
            trigger_error('AccountSigninResponse::onAuthSuccess() - failed to update account status', E_USER_ERROR);
            return Lang::main('intError');
        }

        // DELETE temp session
        if ($this->_get['key'])
            DB::Aowow()->query('DELETE FROM ?_account_sessions WHERE `sessionId` = ?', $this->_get['key']);

        session_regenerate_id(true);                        // user status changed => regenerate id

        // create new session entry
        DB::Aowow()->query('INSERT INTO ?_account_sessions (`userId`, `sessionId`, `created`, `expires`, `touched`, `deviceInfo`, `ip`, `status`) VALUES (?d, ?, ?d, ?d, ?d, ?, ?, ?d)',
            User::$id, session_id(), time(), $this->_post['remember_me'] ? 0 : time() + Cfg::get('SESSION_TIMEOUT_DELAY'), time(), User::$agent, User::$ip, SESSION_ACTIVE);

        if (User::init())                                   // reinitialize the user
            User::save();

        return '';
    }
}

?>
