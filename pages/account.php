<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// exclude & weightscales are handled as Ajax
class AccountPage extends GenericPage
{
    protected $text      = '';
    protected $head      = '';
    protected $token     = '';
    protected $infobox   = [];
    protected $resetPass = false;
    protected $forceTabs = false;

    protected $tpl       = 'acc-dashboard';
    protected $scripts   = array(
        [SC_JS_FILE,  'js/user.js'],
        [SC_JS_FILE,  'js/profile.js'],
        [SC_CSS_FILE, 'css/Profiler.css']
    );
    protected $mode      = CACHE_TYPE_NONE;
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

    protected $lvTabs    = [];
    protected $banned    = [];

    protected $_get = array(
        'token' => ['filter' => FILTER_SANITIZE_SPECIAL_CHARS, 'flags' => FILTER_FLAG_STRIP_AOWOW],
        'next'  => ['filter' => FILTER_SANITIZE_SPECIAL_CHARS, 'flags' => FILTER_FLAG_STRIP_AOWOW],
    );

    protected $_post     = array(
        'username'    => ['filter' => FILTER_SANITIZE_SPECIAL_CHARS, 'flags' => FILTER_FLAG_STRIP_AOWOW],
        'password'    => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkTextLine'],
        'c_password'  => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkTextLine'],
        'token'       => ['filter' => FILTER_SANITIZE_SPECIAL_CHARS, 'flags' => FILTER_FLAG_STRIP_AOWOW],
        'remember_me' => ['filter' => FILTER_CALLBACK, 'options' => 'AccountPage::rememberCallback'],
        'email'       => ['filter' => FILTER_SANITIZE_EMAIL]
    );

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
                header('Location: ?account', true, 302);    // goto dashboard
        }
    }

    protected static function rememberCallback($val)
    {
        return $val == 'yes' ? $val : null;
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
                if (CFG_ACC_AUTH_MODE != AUTH_MODE_SELF)
                {
                    if (CFG_ACC_EXT_RECOVER_URL)
                        header('Location: '.CFG_ACC_EXT_RECOVER_URL, true, 302);
                    else
                        $this->error();
                }

                $this->tpl = 'acc-recover';
                $this->resetPass = false;

                if ($this->createRecoverPass($nStep))       // location-header after final step
                    header('Location: ?account=signin', true, 302);

                $this->head = sprintf(Lang::account('recoverPass'), $nStep);
                break;
            case 'forgotusername':
                if (CFG_ACC_AUTH_MODE != AUTH_MODE_SELF)
                {
                    if (CFG_ACC_EXT_RECOVER_URL)
                        header('Location: '.CFG_ACC_EXT_RECOVER_URL, true, 302);
                    else
                        $this->error();
                }

                $this->tpl = 'acc-recover';
                $this->resetPass = false;

                if ($this->_post['email'])
                {
                    if (!Util::isValidEmail($this->_post['email']))
                        $this->error = Lang::account('emailInvalid');
                    else if (!DB::Aowow()->selectCell('SELECT 1 FROM ?_account WHERE email = ?', $this->_post['email']))
                        $this->error = Lang::account('emailNotFound');
                    else if ($err = $this->doRecoverUser())
                        $this->error = $err;
                    else
                        $this->text = sprintf(Lang::account('recovUserSent'). $this->_post['email']);
                }

                $this->head = Lang::account('recoverUser');
                break;
            case 'signin':
                $this->tpl = 'acc-signIn';
                $this->next = $this->getNext();
                if ($this->_post['username'] || $this->_post['password'])
                {
                    if ($err = $this->doSignIn())
                        $this->error = $err;
                    else
                    {
                        session_regenerate_id(true);        // user status changed => regenerate id
                        header('Location: '.$this->getNext(true), true, 302);
                    }
                }
                else if ($this->_get['token'] && ($_ = DB::Aowow()->selectCell('SELECT user FROM ?_account WHERE status IN (?a) AND token = ? AND statusTimer >  UNIX_TIMESTAMP()', [ACC_STATUS_RECOVER_USER, ACC_STATUS_OK], $this->_get['token'])))
                    $this->user = $_;

                break;
            case 'signup':
                if (!CFG_ACC_ALLOW_REGISTER)
                    $this->error();

                if (CFG_ACC_AUTH_MODE != AUTH_MODE_SELF)
                {
                    if (CFG_ACC_EXT_CREATE_URL)
                        header('Location: '.CFG_ACC_EXT_CREATE_URL, true, 302);
                    else
                        $this->error();
                }

                $this->tpl = 'acc-signUp';
                $nStep = 1;
                if ($this->_post['username'] || $this->_post['password'] || $this->_post['c_password'] || $this->_post['email'])
                {
                    if ($err = $this->doSignUp())
                        $this->error = $err;
                    else
                    {
                        $nStep = 1.5;
                        $this->text = sprintf(Lang::account('createAccSent'), $this->_post['email']);
                    }
                }
                else if ($this->_get['token'] && ($newId = DB::Aowow()->selectCell('SELECT id FROM ?_account WHERE status = ?d AND token = ?', ACC_STATUS_NEW, $this->_get['token'])))
                {
                    $nStep = 2;
                    DB::Aowow()->query('UPDATE ?_account SET status = ?d, statusTimer = 0, token = 0, userGroups = ?d WHERE token = ?', ACC_STATUS_OK, U_GROUP_NONE, $this->_get['token']);
                    DB::Aowow()->query('REPLACE INTO ?_account_bannedips (ip, type, count, unbanDate) VALUES (?, 1, ?d + 1, UNIX_TIMESTAMP() + ?d)', User::$ip, CFG_ACC_FAILED_AUTH_COUNT, CFG_ACC_FAILED_AUTH_BLOCK);

                    $this->text = sprintf(Lang::account('accActivated'), $this->_get['token']);
                }
                else
                    $this->next = $this->getNext();

                $this->head = sprintf(Lang::account('register'), $nStep);
                break;
            case 'signout':
                User::destroy();
            default:
                header('Location: '.$this->getNext(true), true, 302);
                break;
        }
    }

    protected function generateTitle()
    {
        $this->title = [Lang::account('title')];
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
        $infobox[] = Lang::user('joinDate'). Lang::main('colon').'[tooltip name=joinDate]'. date('l, G:i:s', $user['joinDate']). '[/tooltip][span class=tip tooltip=joinDate]'. date(Lang::main('dateFmtShort'), $user['joinDate']). '[/span]';
        $infobox[] = Lang::user('lastLogin').Lang::main('colon').'[tooltip name=lastLogin]'.date('l, G:i:s', $user['prevLogin']).'[/tooltip][span class=tip tooltip=lastLogin]'.date(Lang::main('dateFmtShort'), $user['prevLogin']).'[/span]';
        $infobox[] = Lang::account('lastIP').Lang::main('colon').$user['prevIP'];
        $infobox[] = Lang::account('email'). Lang::main('colon').$user['email'];

        $groups = [];
        foreach (Lang::account('groups') as $idx => $key)
            if ($idx >= 0 && $user['userGroups'] & (1 << $idx))
                $groups[] = (!fMod(count($groups) + 1, 3) ? '[br]' : null).Lang::account('groups', $idx);
        $infobox[] = Lang::user('userGroups').Lang::main('colon').($groups ? implode(', ', $groups) : Lang::account('groups', -1));
        $infobox[] = Util::ucFirst(Lang::main('siteRep')).Lang::main('colon').User::getReputation();


        $this->infobox = '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]';

        /*************/
        /* Ban Popup */
        /*************/

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

        $this->forceTabs = true;

        // Reputation changelog (params only for comment-events)
        if ($repData = DB::Aowow()->select('SELECT action, amount, date AS \'when\', IF(action IN (3, 4, 5), sourceA, 0) AS param FROM ?_account_reputation WHERE userId = ?d', User::$id))
        {
            foreach ($repData as &$r)
                $r['when'] = date(Util::$dateFormatInternal, $r['when']);

            $this->lvTabs[] = ['reputationhistory', ['data' => $repData]];
        }

        // comments
        if ($_ = CommunityContent::getCommentPreviews(['user' => User::$id, 'comments' => true]))
        {
            // needs foundCount for params
            // _totalCount: 377,
            // note: $WH.sprintf(LANG.lvnote_usercomments, 377),

            $this->lvTabs[] = ['commentpreview', array(
                'data'           => $_,
                'hiddenCols'     => ['author'],
                'onBeforeCreate' => '$Listview.funcBox.beforeUserComments'
            )];
        }

        // replies
        if ($_ = CommunityContent::getCommentPreviews(['user' => User::$id, 'replies' => true]))
        {
            // needs commentid (parentComment) for data
            // needs foundCount for params
            // _totalCount: 377,
            // note: $WH.sprintf(LANG.lvnote_usercomments, 377),

            $this->lvTabs[] = ['replypreview', array(
                'data'       => $_,
                'hiddenCols' => ['author']
            )];
        }

/*
<div id="description" class="left"><div id="description-generic"></div>

<script type="text/javascript">//<![CDATA[
Markup.printHtml("description text here", "description-generic", { allow: Markup.CLASS_PREMIUM, roles: "256" });
//]]></script>

</div>
<script type="text/javascript">us_addDescription()</script>

*/
        // claimed characters
        // profiles
        // own screenshots
        // own videos
        // own comments (preview)
        // articles guides..?


        // cpmsg    change pass messaeg class:failure|success, msg:blabla
    }

    private function createRecoverPass(&$step)
    {
        $step = 1;

        if ($this->_post['email'])                          // step 1
        {
            if (!Util::isValidEmail($this->_post['email']))
                $this->error = Lang::account('emailInvalid');
            else if (!DB::Aowow()->selectCell('SELECT 1 FROM ?_account WHERE email = ?', $this->_post['email']))
                $this->error = Lang::account('emailNotFound');
            else if ($err = $this->doRecoverPass())
                $this->error = $err;
            else
            {
                $step = 1.5;
                $this->text = sprintf(Lang::account('recovPassSent'), $this->_post['email']);
            }
        }
        else if ($this->_get['token'])                      // step 2
        {
            $step = 2;
            $this->resetPass = true;
            $this->token     = $this->_get['token'];
        }
        else if ($this->_post['token'] && $this->_post['email'] && $this->_post['password'] && $this->_post['c_password'])
        {
            $step = 2;
            $this->resetPass = true;
            $this->token     = $this->_post['token'];       // insecure source .. that sucks; but whats the worst that could happen .. this account cannot be recovered for some minutes

            if ($err = $this->doResetPass())
                $this->error = $err;
            else
                return true;
        }

        return false;
    }

    private function doSignIn()
    {
        // check username
        if (!User::isValidName($this->_post['username']))
            return Lang::account('userNotFound');

        // check password
        if (!User::isValidPass($this->_post['password']))
            return Lang::account('wrongPass');

        switch (User::Auth($this->_post['username'], $this->_post['password']))
        {
            case AUTH_OK:
                if (!User::$ip)
                    return Lang::main('intError');

                // reset account status, update expiration
                DB::Aowow()->query('UPDATE ?_account SET prevIP = IF(curIp = ?, prevIP, curIP), curIP = IF(curIp = ?, curIP, ?), allowExpire = ?d, status = IF(status = ?d, status, 0), statusTimer = IF(status = ?d, statusTimer, 0), token = IF(status = ?d, token, "") WHERE user = ?',
                    User::$ip, User::$ip, User::$ip,
                    $this->_post['remember_me'] != 'yes',
                    ACC_STATUS_NEW, ACC_STATUS_NEW, ACC_STATUS_NEW,
                    $this->_post['username']
                );

                if (User::init())
                    User::save();                           // overwrites the current user

                return;
            case AUTH_BANNED:
                if (User::init())
                    User::save();
               return Lang::account('accBanned');
            case AUTH_WRONGUSER:
                User::destroy();
                return Lang::account('userNotFound');
            case AUTH_WRONGPASS:
                User::destroy();
                return Lang::account('wrongPass');
            case AUTH_IPBANNED:
                User::destroy();
                return sprintf(Lang::account('loginExceeded'), Util::formatTime(CFG_ACC_FAILED_AUTH_BLOCK * 1000));
            case AUTH_INTERNAL_ERR:
                User::destroy();
                return Lang::main('intError');
            default:
                return;
        }
    }

    private function doSignUp()
    {
        // check username
        if (!User::isValidName($this->_post['username'], $e))
            return Lang::account($e == 1 ? 'errNameLength' : 'errNameChars');

        // check password
        if (!User::isValidPass($this->_post['password'], $e))
            return Lang::account($e == 1 ? 'errPassLength' : 'errPassChars');

        if ($this->_post['password'] != $this->_post['c_password'])
            return Lang::account('passMismatch');

        // check email
        if (!Util::isValidEmail($this->_post['email']))
            return Lang::account('emailInvalid');

        // check ip
        if (!User::$ip)
            return Lang::main('intError');

        // limit account creation
        $ip = DB::Aowow()->selectRow('SELECT ip, count, unbanDate FROM ?_account_bannedips WHERE type = 1 AND ip = ?', User::$ip);
        if ($ip && $ip['count'] >= CFG_ACC_FAILED_AUTH_COUNT && $ip['unbanDate'] >= time())
        {
            DB::Aowow()->query('UPDATE ?_account_bannedips SET count = count + 1, unbanDate = UNIX_TIMESTAMP() + ?d WHERE ip = ? AND type = 1', CFG_ACC_FAILED_AUTH_BLOCK, User::$ip);
            return sprintf(Lang::account('signupExceeded'), Util::formatTime(CFG_ACC_FAILED_AUTH_BLOCK * 1000));
        }

        // username taken
        if ($_ = DB::Aowow()->SelectCell('SELECT user FROM ?_account WHERE (user = ? OR email = ?) AND (status <> ?d OR (status = ?d AND statusTimer > UNIX_TIMESTAMP()))', $this->_post['username'], $this->_post['email'], ACC_STATUS_NEW, ACC_STATUS_NEW))
            return $_ == $this->_post['username'] ? Lang::account('nameInUse') : Lang::account('mailInUse');

        // create..
        $token = Util::createHash();
        $ok = DB::Aowow()->query('REPLACE INTO ?_account (user, passHash, displayName, email, joindate, curIP, allowExpire, locale, userGroups, status, statusTimer, token) VALUES (?, ?, ?, ?, UNIX_TIMESTAMP(), ?, ?d, ?d, ?d, ?d, UNIX_TIMESTAMP() + ?d, ?)',
            $this->_post['username'],
            User::hashCrypt($this->_post['password']),
            Util::ucFirst($this->_post['username']),
            $this->_post['email'],
            User::$ip,
            $this->_post['remember_me'] != 'yes',
            User::$localeId,
            U_GROUP_PENDING,
            ACC_STATUS_NEW,
            CFG_ACC_CREATE_SAVE_DECAY,
            $token
        );
        if (!$ok)
            return Lang::main('intError');
        else if ($_ = $this->sendMail(Lang::user('accConfirm', 0), sprintf(Lang::user('accConfirm', 1), $token), CFG_ACC_CREATE_SAVE_DECAY))
        {
            if ($id = DB::Aowow()->selectCell('SELECT id FROM ?_account WHERE token = ?', $token))
                Util::gainSiteReputation($id, SITEREP_ACTION_REGISTER);

            // success:: update ip-bans
            if (!$ip || $ip['unbanDate'] < time())
                DB::Aowow()->query('REPLACE INTO ?_account_bannedips (ip, type, count, unbanDate) VALUES (?, 1, 1, UNIX_TIMESTAMP() + ?d)', User::$ip, CFG_ACC_FAILED_AUTH_BLOCK);
            else
                DB::Aowow()->query('UPDATE ?_account_bannedips SET count = count + 1, unbanDate = UNIX_TIMESTAMP() + ?d WHERE ip = ? AND type = 1', CFG_ACC_FAILED_AUTH_BLOCK, User::$ip);

            return $_;
        }
    }

    private function doRecoverPass()
    {
        if ($_ = $this->initRecovery(ACC_STATUS_RECOVER_PASS, CFG_ACC_RECOVERY_DECAY, $token))
            return $_;

        // send recovery mail
        return $this->sendMail(Lang::user('resetPass', 0), sprintf(Lang::user('resetPass', 1), $token), CFG_ACC_RECOVERY_DECAY);
    }

    private function doResetPass()
    {
        if ($this->_post['password'] != $this->_post['c_password'])
            return Lang::account('passCheckFail');

        if (!Util::isValidEmail($this->_post['email']))
            return Lang::account('emailInvalid');

        $uId = DB::Aowow()->selectCell('SELECT id FROM ?_account WHERE token = ? AND email = ? AND status = ?d AND statusTimer > UNIX_TIMESTAMP()',
            $this->_post['token'],
            $this->_post['email'],
            ACC_STATUS_RECOVER_PASS
        );
        if (!$uId)
            return Lang::account('emailNotFound');          // assume they didn't meddle with the token

        if (!User::verifyCrypt($this->_post['c_password']))
            return Lang::account('newPassDiff');

        if (!DB::Aowow()->query('UPDATE ?_account SET passHash = ?, status = ?d WHERE id = ?d', User::hashCrypt($this->_post['c_password']), ACC_STATUS_OK, $uId))
            return Lang::main('intError');
    }

    private function doRecoverUser()
    {
        if ($_ = $this->initRecovery(ACC_STATUS_RECOVER_USER, CFG_ACC_RECOVERY_DECAY, $token))
            return $_;

        // send recovery mail
        return $this->sendMail(Lang::user('recoverUser', 0), sprintf(Lang::user('recoverUser', 1), $token), CFG_ACC_RECOVERY_DECAY);
    }

    private function initRecovery($type, $delay, &$token)
    {
        if (!$type)
            return Lang::main('intError');

        // check if already processing
        if ($_ = DB::Aowow()->selectCell('SELECT statusTimer - UNIX_TIMESTAMP() FROM ?_account WHERE email = ? AND status <> ?d AND statusTimer > UNIX_TIMESTAMP()', $this->_post['email'], ACC_STATUS_OK))
            return sprintf(Lang::account('isRecovering'), Util::formatTime($_ * 1000));

        // create new token and write to db
        $token = Util::createHash();
        if (!DB::Aowow()->query('UPDATE ?_account SET token = ?, status = ?d, statusTimer =  UNIX_TIMESTAMP() + ?d WHERE email = ?', $token, $type, $delay, $this->_post['email']))
            return Lang::main('intError');
    }

    private function sendMail($subj, $msg, $delay = 300)
    {
        // send recovery mail
        $subj   = CFG_NAME_SHORT.Lang::main('colon') . $subj;
        $msg   .= "\r\n\r\n".sprintf(Lang::user('tokenExpires'), Util::formatTime($delay * 1000))."\r\n";
        $header = 'From: '.CFG_CONTACT_EMAIL . "\r\n" .
                  'Reply-To: '.CFG_CONTACT_EMAIL . "\r\n" .
                  'X-Mailer: PHP/' . phpversion();

        if (!mail($this->_post['email'], $subj, $msg, $header))
            return sprintf(Lang::main('intError2'), 'send mail');
    }

    private function getNext($forHeader = false)
    {
        $next = $forHeader ? '.' : '';
        if ($this->_get['next'])
            $next = $this->_get['next'];
        else if (isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], '?'))
            $next = explode('?', $_SERVER['HTTP_REFERER'])[1];

        if ($forHeader && !$next)
            $next = '.';

        return ($forHeader && $next != '.' ? '?' : '').$next;
    }
}

?>
