<?php

namespace Aowow;

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
        'signup'         => [false],
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
        'password'    => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\GenericPage::checkTextLine'],
        'c_password'  => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\GenericPage::checkTextLine'],
        'token'       => ['filter' => FILTER_SANITIZE_SPECIAL_CHARS, 'flags' => FILTER_FLAG_STRIP_AOWOW],
        'remember_me' => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AccountPage::rememberCallback'],
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
            if ($this->validCats[$pageParam][0] && !User::isLoggedIn())
                $this->forwardToSignIn('account='.$pageParam);
            // doesn't require auth && authed
            else if (!$this->validCats[$pageParam][0] && User::isLoggedIn())
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
                if (Cfg::get('ACC_AUTH_MODE') != AUTH_MODE_SELF)
                {
                    if (Cfg::get('ACC_EXT_RECOVER_URL'))
                        header('Location: '.Cfg::get('ACC_EXT_RECOVER_URL'), true, 302);
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
                if (Cfg::get('ACC_AUTH_MODE') != AUTH_MODE_SELF)
                {
                    if (Cfg::get('ACC_EXT_RECOVER_URL'))
                        header('Location: '.Cfg::get('ACC_EXT_RECOVER_URL'), true, 302);
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
            case 'signup':
                if (!Cfg::get('ACC_ALLOW_REGISTER'))
                    $this->error();

                if (Cfg::get('ACC_AUTH_MODE') != AUTH_MODE_SELF)
                {
                    if (Cfg::get('ACC_EXT_CREATE_URL'))
                        header('Location: '.Cfg::get('ACC_EXT_CREATE_URL'), true, 302);
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
                    DB::Aowow()->query('REPLACE INTO ?_account_bannedips (ip, type, count, unbanDate) VALUES (?, 1, ?d + 1, UNIX_TIMESTAMP() + ?d)', User::$ip, Cfg::get('ACC_FAILED_AUTH_COUNT'), Cfg::get('ACC_FAILED_AUTH_BLOCK'));

                    $this->text = sprintf(Lang::account('accActivated'), $this->_get['token']);
                }
                else
                    $this->next = $this->getNext();

                $this->head = sprintf(Lang::account('register'), $nStep);
                break;
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
        if (!User::isLoggedIn())
            $this->forwardToSignIn('account');

        $user = DB::Aowow()->selectRow('SELECT * FROM ?_account WHERE `id` = ?d', User::$id);
        $bans = DB::Aowow()->select('SELECT ab.*, a.`username`, ab.`id` AS ARRAY_KEY FROM ?_account_banned ab LEFT JOIN ?_account a ON a.`id` = ab.`staffId` WHERE ab.`userId` = ?d', User::$id);

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
                'by'     => [$b['staffId'], $b['username']],
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
        $ip = DB::Aowow()->selectRow('SELECT `ip`, `count`, `unbanDate` FROM ?_account_bannedips WHERE `type` = 1 AND `ip` = ?', User::$ip);
        if ($ip && $ip['count'] >= Cfg::get('ACC_FAILED_AUTH_COUNT') && $ip['unbanDate'] >= time())
        {
            DB::Aowow()->query('UPDATE ?_account_bannedips SET `count` = `count` + 1, `unbanDate` = UNIX_TIMESTAMP() + ?d WHERE `ip` = ? AND `type` = 1', Cfg::get('ACC_FAILED_AUTH_BLOCK'), User::$ip);
            return sprintf(Lang::account('signupExceeded'), Util::formatTime(Cfg::get('ACC_FAILED_AUTH_BLOCK') * 1000));
        }

        // username taken
        if ($_ = DB::Aowow()->SelectCell('SELECT `username` FROM ?_account WHERE (`username` = ? OR `email` = ?) AND (`status` <> ?d OR (`status` = ?d AND `statusTimer` > UNIX_TIMESTAMP()))', $this->_post['username'], $this->_post['email'], ACC_STATUS_NEW, ACC_STATUS_NEW))
            return $_ == $this->_post['username'] ? Lang::account('nameInUse') : Lang::account('mailInUse');

        // create..
        $token = Util::createHash();
        $ok = DB::Aowow()->query('REPLACE INTO ?_account (`login`, `passHash`, `username`, `email`, `joindate`, `curIP`, `locale`, `userGroups`, `status`, `statusTimer`, `token`) VALUES (?, ?, ?, ?, UNIX_TIMESTAMP(), ?, ?d, ?d, ?d, ?d, UNIX_TIMESTAMP() + ?d, ?)',
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
        if (!$ok)
            return Lang::main('intError');

        if (!Util::sendMail($this->_post['email'], 'activate-account', [$token], Cfg::get('ACC_RECOVERY_DECAY')))
            return Lang::main('intError2', ['send mail']);

        if ($id = DB::Aowow()->selectCell('SELECT id FROM ?_account WHERE token = ?', $token))
            Util::gainSiteReputation($id, SITEREP_ACTION_REGISTER);

        // success:: update ip-bans
        if (!$ip || $ip['unbanDate'] < time())
            DB::Aowow()->query('REPLACE INTO ?_account_bannedips (ip, type, count, unbanDate) VALUES (?, 1, 1, UNIX_TIMESTAMP() + ?d)', User::$ip, Cfg::get('ACC_FAILED_AUTH_BLOCK'));
        else
            DB::Aowow()->query('UPDATE ?_account_bannedips SET count = count + 1, unbanDate = UNIX_TIMESTAMP() + ?d WHERE ip = ? AND type = 1', Cfg::get('ACC_FAILED_AUTH_BLOCK'), User::$ip);
    }

    private function doRecoverPass()
    {
        if ($_ = $this->initRecovery(ACC_STATUS_RECOVER_PASS, Cfg::get('ACC_RECOVERY_DECAY'), $token))
            return $_;

        // send recovery mail
        if (!Util::sendMail($this->_post['email'], 'reset-password', [$token], Cfg::get('ACC_RECOVERY_DECAY')))
            return Lang::main('intError2', ['send mail']);
    }

    private function doResetPass()
    {
        if ($this->_post['password'] != $this->_post['c_password'])
            return Lang::account('passCheckFail');

        if (!Util::isValidEmail($this->_post['email']))
            return Lang::account('emailInvalid');

        $userData = DB::Aowow()->selectRow('SELECT `id, `passHash` FROM ?_account WHERE `token` = ? AND `email` = ? AND `status` = ?d AND `statusTimer` > UNIX_TIMESTAMP()',
            $this->_post['token'],
            $this->_post['email'],
            ACC_STATUS_RECOVER_PASS
        );
        if (!$userData)
            return Lang::account('emailNotFound');          // assume they didn't meddle with the token

        if (!User::verifyCrypt($this->_post['c_password'], $userData['passHash']))
            return Lang::account('newPassDiff');

        if (!DB::Aowow()->query('UPDATE ?_account SET `passHash` = ?, `status` = ?d WHERE `id` = ?d', User::hashCrypt($this->_post['c_password']), ACC_STATUS_OK, $userData['id']))
            return Lang::main('intError');
    }

    private function doRecoverUser()
    {
        if ($_ = $this->initRecovery(ACC_STATUS_RECOVER_USER, Cfg::get('ACC_RECOVERY_DECAY'), $token))
            return $_;

        if (!Util::sendMail($this->_post['email'], 'recover-user', [$token], Cfg::get('ACC_RECOVERY_DECAY')))
            return Lang::main('intError2', ['send mail']);
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
