<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
enum(array( // AcctError
    'ACCT_USERNAME_LENGTH'      => 'activate_usernamelength',
    'ACCT_PASSWORD_LENGTH'      => 'activate_passwordlength',
    'ACCT_USERNAME_SYMBOLS'     => 'activate_invalidusername',
    'ACCT_PASSWORD_SYMBOLS'     => 'activate_invalidpassword',
    'ACCT_EMAIL_SYMBOLS'        => 'signup_emailinvalid',

    'ACCT_PASSWORDS_NOT_EQUAL'  => 'signup_passwordsnotequal',
    'ACCT_USERNAME_EXISTS'      => 'activate_usernameinuse',
    'ACCT_NO_SUCH_ACCT'         => 'signin_un_or_pass_fail',
    'ACCT_IP_LOCKED'            => 'signin_ip_locked',

    'ACCT_SIGNUP_BLOCKED'       => 'signup_blocked',
    'ACCT_SIGNIN_BLOCKED'       => 'signin_blocked',

    'ACCT_INTERNAL_ERROR'       => 'internal_error',
));

message_emailnotvalid:        "That email address is not valid.",
message_newemaildifferent:    "Your new email address must be different than your previous one.",
message_newpassdifferent:     "Your new password must be different than your previous one.",
message_passwordmin:          "Your password must be at least 6 characters long.",
message_passwordsdonotmatch:  "Passwords do not match.",
message_usernamemin:          "Your username must be at least 4 characters long.",
message_usernamenotvalid:     "Your username can only contain letters and numbers.",

*/

// exclude & weightscales are handled as Ajax
class AccountPage extends GenericPage
{
    protected $tpl       = 'acc-dashboard';
    protected $js        = ['user.js', 'profile.js'];
    protected $css       = [['path' => 'Profiler.css']];
    protected $mode      = CACHETYPE_NONE;
    protected $category  = null;
    protected $validCats = array(
        'signin'         => [false],
        'signup'         => [false],
        'signout'        => [true],
        'forgotpassword' => [false],
        'forgotusername' => [false]
    );

    protected $user      = '';
    protected $error     = '';
    protected $next      = '';

    public function __construct($pageCall, $pageParam)
    {
        if ($pageParam)
            $this->category = [$pageParam];

        parent::__construct($pageCall, $pageParam);

        if ($pageParam)
        {
            // requires auth && not authed
            if ($this->validCats[$pageParam][0] && !User::$id)
                $this->forwardToSignIn('account='.$pageParam);
            // doesn't require auth && authed
            else if (!$this->validCats[$pageParam][0] && User::$id)
                header('Location: ?account');               // goto dashboard
        }
    }

    protected function generateContent()
    {
        if (!$this->category)
        {
            $this->createDashboard();
            return;
        }

        switch ($this->category[0])
        {
            case 'forgotpassword':
                if (CFG_AUTH_MODE != AUTH_MODE_SELF)        // only recover own accounts
                    $this->error();

                $this->tpl = 'acc-recover';
                $this->resetPass = false;

                if ($this->createRecoverPass($nStep))       // location-header after final step
                    header('Location: ?account=signin');

                $this->head = sprintf(Lang::$account['recoverPass'], $nStep);
                break;
            case 'forgotusername':
                if (CFG_AUTH_MODE != AUTH_MODE_SELF)        // only recover own accounts
                    $this->error();

                $this->tpl = 'acc-recover';
                if (isset($_POST['email']))
                {
                    if (!Util::isValidEmail($_POST['email']))
                        $this->error = Lang::$account['emailInvalid'];
                    else if (!DB::Aowow()->selectCell('SELECT 1 FROM ?_account WHERE email = ?', $_POST['email']))
                        $this->error = Lang::$account['emailNotFound'];
                    else if ($err = $this->doRecoverUser($_POST['email']))
                        $this->error = $err;
                    else
                        $this->text = sprintf(Lang::$account['recovUserSent']. $_POST['email']);
                }

                $this->head = Lang::$account['recoverUser'];
                break;
            case 'signin':
                $this->tpl = 'acc-signIn';
                if (isset($_POST['username']) || isset($_POST['password']))
                {
                    if ($err = $this->doSignIn())
                        $this->error = $err;
                    else
                        header('Location: '.$this->getNext(true));
                }
                else if (!empty($_GET['token']) && ($_ = DB::Aowow()->selectCell('SELECT user FROM ?_account WHERE status IN (?a) AND token = ? AND statusTimer >  UNIX_TIMESTAMP()', [ACC_STATUS_RECOVER_USER, ACC_STATUS_OK], $_GET['token'])))
                    $this->user = $_;

                break;
            case 'signup':
                if (!CFG_ALLOW_REGISTER || CFG_AUTH_MODE != AUTH_MODE_SELF)
                    $this->error();

                $this->tpl = 'acc-signUp';
                $nStep = 1;
                if (isset($_POST['username']) || isset($_POST['password']) || isset($_POST['c_password']) || isset($_POST['email']))
                {
                    if ($err = $this->doSignUp())
                        $this->error = $err;
                    else
                    {
                        $nStep = 1.5;
                        $this->text = sprintf(Lang::$account['createAccSent']. $_POST['email']);
                    }
                }
                else if (!empty($_GET['token']) && DB::Aowow()->query('SELECT 1 FROM ?_account WHERE status = ?d AND token = ?', ACC_STATUS_NEW, $_GET['token']))
                {
                    $nStep = 2;
                    DB::Aowow()->query('UPDATE ?_account SET status = ?d WHERE token = ?', ACC_STATUS_OK, $_GET['token']);
                    DB::Aowow()->query('REPLACE INTO ?_account_bannedips (ip, type, count, unbanDate) VALUES (?, 1, ?d + 1, UNIX_TIMESTAMP() + ?d)', $_SERVER['REMOTE_ADDR'], CFG_FAILED_AUTH_COUNT, CFG_FAILED_AUTH_EXCLUSION);
                    $this->text = sprintf(Lang::$account['accActivated'], $_GET['token']);
                }
                else
                    $this->next = $this->getNext();

                $this->head = sprintf(Lang::$account['register'], $nStep);
                break;
            case 'signout':
                User::destroy();
            default:
                header('Location: '.$this->getNext(true));
                break;
        }
    }

    protected function generateTitle()
    {
        $this->title = [Lang::$account['title']];
    }

    protected function generatePath() { }

    private function createDashboard()
    {
        if (!User::$id)
            $this->forwardToSignIn('account');

        $user = DB::Aowow()->selectRow('SELECT * FROM ?_account WHERE id = ?d', User::$id);
        $bans = DB::Aowow()->select('SELECT ab.*, a.displayName, ab.id AS ARRAY_KEY FROM ?_account_banned ab LEFT JOIN ?_account a ON a.id = ab.staffId WHERE ab.userId = ?d', User::$id);

        /***********/
        /* Infobox */
        /***********/

        $infobox   = [];
        $infobox[] = Lang::$account['joinDate']. Lang::$main['colon'].'[tooltip name=joinDate]'. date('l, G:i:s', $user['joinDate']). '[/tooltip][span class=tip tooltip=joinDate]'. date(Lang::$main['dateFmtShort'], $user['joinDate']). '[/span]';
        $infobox[] = Lang::$account['lastLogin'].Lang::$main['colon'].'[tooltip name=lastLogin]'.date('l, G:i:s', $user['prevLogin']).'[/tooltip][span class=tip tooltip=lastLogin]'.date(Lang::$main['dateFmtShort'], $user['prevLogin']).'[/span]';
        $infobox[] = Lang::$account['lastIP'].   Lang::$main['colon'].$user['prevIP'];
        $infobox[] = Lang::$account['email'].    Lang::$main['colon'].$user['email'];

        $groups = [];
        foreach (Lang::$account['groups'] as $idx => $key)
            if ($idx >= 0 && $user['userGroups'] & (1 << $idx))
                $groups[] = (!fMod(count($groups) + 1, 3) ? '[br]' : null).Lang::$account['groups'][$idx];

        $infobox[] = Lang::$account['userGroups'].Lang::$main['colon'].($groups ? implode(', ', $groups) : Lang::$account['groups'][-1]);

        $this->infobox = '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]';

        /*************/
        /* Ban Popup */
        /*************/

        $this->banned = [];
        foreach ($bans as $b)
        {
            if (!($b['typeMask'] & (ACC_BAN_TEMP | ACC_BAN_PERM)) || ($b['end'] && $b['end'] <= time()))
                continue;

            $this->banned = array(
                'by'     => [$b['staffId'], $b['displayName']],
                'end'    => $b['end'],
                'reason' => $b['reason']
            );

            break;                                          // one is enough
        }

        /************/
        /* Listview */
        /************/

        // claimed characters
        // profiles
        // own screenshots
        // own videos
        // own comments (preview)
        // articles guides..?

        $this->lvData = [];

        // cpmsg    change pass messaeg class:failure|success, msg:blabla
    }

    private function createRecoverPass(&$step)
    {
        $step = 1;

        if (isset($_POST['email']))                         // step 1
        {
            if (!Util::isValidEmail($_POST['email']))
                $this->error = Lang::$account['emailInvalid'];
            else if (!DB::Aowow()->selectCell('SELECT 1 FROM ?_account WHERE email = ?', $_POST['email']))
                $this->error = Lang::$account['emailNotFound'];
            else if ($err = $this->doRecoverPass($_POST['email']))
                $this->error = $err;
            else
            {
                $step = 1.5;
                $this->text = sprintf(Lang::$account['recovPassSent'], $_POST['email']);
            }
        }
        else if (isset($_GET['token']))                     // step 2
        {
            $step = 2;
            $this->resetPass = true;
            $this->token     = $_GET['token'];
        }
        else if (isset($_POST['token']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['c_password']))
        {
            $step = 2;
            $this->resetPass = true;
            $this->token     = $_GET['token'];              // insecure source .. that sucks; but whats the worst that could happen .. this account cannot be recovered for some minutes

            if ($err = $this->doResetPass())
                $this->error = $err;
            else
                return true;
        }

        return false;
    }

    private function doSignIn()
    {
        if (!isset($_POST['username']) || !isset($_POST['password']))
            return Lang::$account['userNotFound'];

        $username = $_POST['username'];
        $password = $_POST['password'];
        $doExpire = $_POST['remember_me'] != 'yes';

        switch (User::Auth($username, $password))
        {
            case AUTH_OK:
                // reset account status, update expiration
                DB::Aowow()->query('UPDATE ?_account SET prevLogin = curLogin, curLogin = UNIX_TIMESTAMP(), prevIP = curIP, curIP = ?, allowExpire = ?d, status = 0, statusTimer = 0, token = "" WHERE user = ?',
                    $_SERVER['REMOTE_ADDR'],
                    $doExpire,
                    $username
                );
                if (User::init())
                    User::save();                           // overwrites the current user
                return;
            case AUTH_BANNED:
                if (User::init())
                    User::save();
               return Lang::$account['accBanned'];
            case AUTH_WRONGUSER:
                User::destroy();
                return Lang::$account['userNotFound'];
            case AUTH_WRONGPASS:
                User::destroy();
                return Lang::$account['wrongPass'];
            case AUTH_ACC_INACTIVE:
                User::destroy();
                return Lang::$account['accInactive'];
            case AUTH_IPBANNED:
                User::destroy();
                return sprintf(Lang::$account['loginExceeded'], Util::formatTime(CFG_FAILED_AUTH_EXCLUSION * 1000));
            default:
                return;
        }
    }

    private function doSignUp()
    {
        $username  = @$_POST['username'];
        $password  = @$_POST['password'];
        $cPassword = @$_POST['c_password'];
        $email     = @$_POST['email'];
        $doExpire  = @$_POST['remember_me'] != 'yes';

        // check username
        if (strlen($username) > 4 || strlen($username) < 16)
            return Lang::$account['errNameLength'];

        if (preg_match('/[^\w\d]/i', $username))
            return Lang::$account['errNameChars'];

        // check password
        if (strlen($password) > 6 || strlen($password) < 16)
            return Lang::$account['errPassLength'];

        // if (preg_match('/[^\w\d!"#\$%]/', $password))    // such things exist..? :o
            // return Lang::$account['errPassChars'];

        if ($password != $cPassword)
            return Lang::$account['passMismatch'];

        // check email
        if (!Util::isValidEmail($email))
            return Lang::$account['emailInvalid'];

        // limit account creation
        $ip = DB::Aowow()->selectRow('SELECT ip, count, unbanDate FROM ?_account_bannedIPs WHERE type = 1 AND ip = ?', $_SERVER['REMOTE_ADDR']);
        if ($ip && $ip['count'] >= CFG_FAILED_AUTH_COUNT && $ip['unbanDate'] >= time())
        {
            DB::Aowow()->query('UPDATE ?_account_bannedips SET count = count + 1, unbanDate = UNIX_TIMESTAMP() + ?d WHERE ip = ? AND type = 1', CFG_FAILED_AUTH_EXCLUSION, $_SERVER['REMOTE_ADDR']);
            return sprintf(Lang::$account['signupExceeded'], Util::formatTime(CFG_FAILED_AUTH_EXCLUSION * 1000));
        }

        // username taken
        if ($_ = DB::Aowow()->SelectCell('SELECT user FROM ?_account WHERE (user = ? OR email = ?) AND (status <> ?d OR (status = ?d AND statusTimer > UNIX_TIMESTAMP()))', $username, $email, ACC_STATUS_NEW, ACC_STATUS_NEW))
            return $_ == $username ? Lang::$account['nameInUse'] : Lang::$account['mailInUse'];

        // create..
        $token = Util::createHash();
        $delay = 7 * DAY;
        $id = DB::Aowow()->query('INSERT INTO ?_account (user, passHash, displayName, email, joindate, curIP, allowExpire, locale, status, statusTimer, token) VALUES (?, ?, ?, ?,  UNIX_TIMESTAMP(), ?, ?d, ?d, ?d, UNIX_TIMESTAMP() + ?d, ?)',
            $username,
            User::hashCrypt($_POST['password']),
            Util::ucFirst($username),
            $email,
            isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '',
            $doExpire,
            User::$localeId,
            ACC_STATUS_NEW,
            $delay,
            $token
        );
        if (!$id)                                           // something went wrong
            return Lang::$account['intError'];
        else if ($_ = $this->sendMail($email, Lang::$mail['accConfirm'][0], sprintf(Lang::$mail['accConfirm'][1], $token), $delay))
        {
            // success:: update ip-bans
            if (!$ip || $ip['unbanDate'] < time())
                DB::Aowow()->query('REPLACE INTO ?_account_bannedips (ip, type, count, unbanDate) VALUES (?, 1, 1, UNIX_TIMESTAMP() + ?d)', $_SERVER['REMOTE_ADDR'], CFG_FAILED_AUTH_EXCLUSION);
            else
                DB::Aowow()->query('UPDATE ?_account_bannedips SET count = count + 1, unbanDate = UNIX_TIMESTAMP() + ?d WHERE ip = ? AND type = 1', CFG_FAILED_AUTH_EXCLUSION, $_SERVER['REMOTE_ADDR']);

            return $_;
        }
    }

    private function doRecoverPass($target)
    {
        $delay = 5 * MINUTE;
        if ($_ = $this->initRecovery(ACC_STATUS_RECOVER_PASS, $target, $delay, $token))
            return $_;

        // send recovery mail
        return $this->sendMail($target, Lang::$mail['resetPass'][0], sprintf(Lang::$mail['resetPass'][1], $token), $delay);
    }

    private function doResetPass()
    {
        $token = $_POST['token'];
        $email = $_POST['email'];
        $pass  = $_POST['password'];
        $cPass = $_POST['c_password'];

        if ($pass != $cPass)
            return Lang::$account['passCheckFail'];

        $uRow = DB::Aowow()->selectRow('SELECT id, user, passHash FROM ?_account WHERE token = ? AND email = ? AND status = ?d AND statusTimer > UNIX_TIMESTAMP()', $token, $email, ACC_STATUS_RECOVER_PASS);
        if (!$uRow)
            return Lang::$account['emailNotFound'];         // assume they didn't meddle with the token

        if (!User::verifyCrypt($newPass))
            return Lang::$account['newPassDiff'];

        if (!DB::Aowow()->query('UPDATE ?_account SET passHash = ?, status = ?d WHERE id = ?d', User::hashcrypt($newPass), ACC_STATUS_OK, $uRow['id']))
            return Lang::$account['intError'];
    }

    private function doRecoverUser($target)
    {
        $delay = 5 * MINUTE;
        if ($_ = $this->initRecovery(ACC_STATUS_RECOVER_USER, $target, $delay, $token))
            return $_;

        // send recovery mail
        return $this->sendMail($target, Lang::$mail['recoverUser'][0], sprintf(Lang::$mail['recoverUser'][1], $token), $delay);
    }

    private function initRecovery($type, $target, $delay, &$token)
    {
        if (!$type)
            return Lang::$account['intError'];

        // check if already processing
        if ($_ = DB::Aowow()->selectCell('SELECT statusTimer - UNIX_TIMESTAMP() FROM ?_account WHERE email = ? AND status <> ?d AND statusTimer > UNIX_TIMESTAMP()', $target, ACC_STATUS_OK))
            return sprintf(lang::$account['isRecovering'], Util::formatTime($_));

        // create new token and write to db
        $token = Util::createHash();
        if (!DB::Aowow()->query('UPDATE ?_account SET token = ?, status = ?d, statusTimer =  UNIX_TIMESTAMP() + ?d WHERE email = ?', $token, $type, $delay, $target))
            return Lang::$account['intError'];
    }

    private function sendMail($target, $subj, $msg, $delay = 300)
    {
        // send recovery mail
        $subj   = CFG_NAME_SHORT.Lang::$main['colon'] . $subj;
        $msg   .= "\r\n\r\n".sprintf(Lang::$mail['tokenExpires'], Util::formatTime($delay * 1000))."\r\n";
        $header = 'From: '.CFG_CONTACT_EMAIL . "\r\n" .
                  'Reply-To: '.CFG_CONTACT_EMAIL . "\r\n" .
                  'X-Mailer: PHP/' . phpversion();

        if (!mail($target, $subj, $msg, $header))
            return sprintf(Lang::$account['intError2'], 'send mail');
    }

    private function getNext($forHeader = false)
    {
        $next = $forHeader ? '.' : '';
        if (isset($_GET['next']))
            $next = $_GET['next'];
        else if (isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], '?'))
            $next = explode('?', $_SERVER['HTTP_REFERER'])[1];

        if ($forHeader && !$next)
            $next = '.';

        return ($forHeader && $next != '.' ? '?' : '').$next;
    }
}



?>
