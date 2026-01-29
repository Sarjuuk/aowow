<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via signup link
 * self referencing
 */
class AccountSignupResponse extends TemplateResponse
{
    use TrGetNext;

    protected string $template     = 'text-page-generic';
    protected string $pageName     = 'signup';

    protected array  $expectedPOST = array(
        'username'    => ['filter' => FILTER_SANITIZE_SPECIAL_CHARS, 'flags' => FILTER_FLAG_STRIP_AOWOW         ],
        'email'       => ['filter' => FILTER_SANITIZE_EMAIL,         'flags' => FILTER_FLAG_STRIP_AOWOW         ],
        'password'    => ['filter' => FILTER_CALLBACK,             'options' => [self::class, 'checkTextLine']  ],
        'c_password'  => ['filter' => FILTER_CALLBACK,             'options' => [self::class, 'checkTextLine']  ],
        'remember_me' => ['filter' => FILTER_CALLBACK,             'options' => [self::class, 'checkRememberMe']]
    );

    protected array  $expectedGET  = array(
        'next' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[[:print:]]+$/']]
    );

    private bool $success = false;

    public function __construct()
    {
        // if the user is logged in goto account dashboard
        if (User::isLoggedIn())
            $this->forward('?account');

        // redirect to external registration page, if set
        if (Cfg::get('ACC_EXT_CREATE_URL'))
            $this->forward(Cfg::get('ACC_EXT_CREATE_URL'));

        parent::__construct();

        // registration not enabled on self
        if (!Cfg::get('ACC_ALLOW_REGISTER'))
            $this->generateError();

        if (Cfg::get('ACC_AUTH_MODE') != AUTH_MODE_SELF)
            $this->generateError();
    }

    protected function generate() : void
    {
        $this->title[] = Lang::account('title');

        // step 1 - no params > signup form
        // step 2 - any param > status box
        // step 3 - on ?account=activate

        $message = $this->doSignUp();

        if ($this->success)
        {
            $this->inputbox = ['inputbox-status', array(
                'head'    => Lang::account('inputbox', 'head', 'register', [1.5]),
                'message' => Lang::account('inputbox', 'message', 'createAccSent', [$this->_post['email']])
            )];
        }
        else
        {
            $this->inputbox = ['inputbox-form-signup', array(
                'head'       => Lang::account('inputbox', 'head', 'register', [1]),
                'error'      => $message,
                'action'     => '?account=signup&next='.$this->getNext(),
                'username'   => $this->_post['username'] ?? '',
                'email'      => $this->_post['email']    ?? '',
                'rememberMe' => !!$this->_post['remember_me'],
            )];
        }

        parent::generate();
    }

    private function doSignUp() : string
    {
        // no input yet. show clean form
        if (!$this->assertPOST('username', 'password', 'c_password') && is_null($this->_post['email']))
            return '';

        // truncated due to validation fail
        if (!$this->_post['email'])
            return Lang::account('emailInvalid');

        // check username
        if (!Util::validateUsername($this->_post['username'], $e))
            return Lang::account($e == 1 ? 'errNameLength' : 'errNameChars');

        // check password
        if (!Util::validatePassword($this->_post['password'], $e))
            return $e == 1 ? Lang::account('errPassLength') : Lang::main('intError');

        if ($this->_post['password'] !== $this->_post['c_password'])
            return Lang::account('passMismatch');

        // check ip
        if (!User::$ip)
            return Lang::main('intError');

        // limit account creation
        if (DB::Aowow()->selectRow('SELECT 1 FROM ?_account_bannedips WHERE `type` = ?d AND `ip` = ? AND `count` >= ?d AND `unbanDate` >= UNIX_TIMESTAMP()', IP_BAN_TYPE_REGISTRATION_ATTEMPT, User::$ip, Cfg::get('ACC_FAILED_AUTH_COUNT')))
        {
            DB::Aowow()->query('UPDATE ?_account_bannedips SET `count` = `count` + 1, `unbanDate` = UNIX_TIMESTAMP() + ?d WHERE `ip` = ? AND `type` = ?d', Cfg::get('ACC_FAILED_AUTH_BLOCK'), User::$ip, IP_BAN_TYPE_REGISTRATION_ATTEMPT);
            return Lang::account('inputbox', 'error', 'signupExceeded', [DateTime::formatTimeElapsedFloat(Cfg::get('ACC_FAILED_AUTH_BLOCK') * 1000)]);
        }

        // username / email taken
        if ($inUseData = DB::Aowow()->SelectRow('SELECT `id`, `username`, `status` = ?d AND `statusTimer` < UNIX_TIMESTAMP() AS "expired" FROM ?_account WHERE (LOWER(`username`) = LOWER(?) OR LOWER(`email`) = LOWER(?))', ACC_STATUS_NEW, $this->_post['username'], $this->_post['email']))
        {
            if ($inUseData['expired'])
                DB::Aowow()->query('DELETE FROM ?_account WHERE `id` = ?d', $inUseData['id']);
            else
                return Util::lower($inUseData['username']) == Util::lower($this->_post['username']) ? Lang::account('nameInUse') : Lang::account('mailInUse');
        }

        // create..
        $token  = Util::createHash();
        $userId = DB::Aowow()->query('INSERT INTO ?_account (`login`, `passHash`, `username`, `email`, `joindate`, `curIP`, `locale`, `userGroups`, `status`, `statusTimer`, `token`) VALUES (?, ?, ?, ?, UNIX_TIMESTAMP(), ?, ?d, ?d, ?d, UNIX_TIMESTAMP() + ?d, ?)',
            $this->_post['username'],
            User::hashCrypt($this->_post['password']),
            $this->_post['username'],
            $this->_post['email'],
            User::$ip,
            Lang::getLocale()->value,
            U_GROUP_PENDING,
            ACC_STATUS_NEW,
            Cfg::get('ACC_CREATE_SAVE_DECAY'),
            $token
        );

        if (!$userId)
            return Lang::main('intError');

        // create session tied to the token to store remember_me status
        DB::Aowow()->query('INSERT INTO ?_account_sessions (`userId`, `sessionId`, `created`, `expires`, `touched`, `deviceInfo`, `ip`, `status`) VALUES (?d, ?, ?d, ?d, ?d, ?, ?, ?d)',
            $userId, $token, time(), $this->_post['remember_me'] ? 0 : time() + Cfg::get('SESSION_TIMEOUT_DELAY'), time(), User::$agent, User::$ip, SESSION_ACTIVE);

        if (!Util::sendMail($this->_post['email'], 'activate-account', [$token], Cfg::get('ACC_CREATE_SAVE_DECAY')))
            return Lang::main('intError2', ['send mail']);

        // success: update ip-bans
        DB::Aowow()->query('INSERT INTO ?_account_bannedips (`ip`, `type`, `count`, `unbanDate`) VALUES (?, ?d, 1, UNIX_TIMESTAMP() + ?d) ON DUPLICATE KEY UPDATE `count` = `count` + 1, `unbanDate` = UNIX_TIMESTAMP() + ?d',
            User::$ip, IP_BAN_TYPE_REGISTRATION_ATTEMPT, Cfg::get('ACC_FAILED_AUTH_BLOCK'), Cfg::get('ACC_FAILED_AUTH_BLOCK'));

        Util::gainSiteReputation($userId, SITEREP_ACTION_REGISTER);

        $this->success = true;
        return '';
    }
}

?>
