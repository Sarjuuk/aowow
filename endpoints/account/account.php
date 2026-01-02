<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AccountBaseResponse extends TemplateResponse
{
    protected string $template = 'account';
    protected string $pageName = 'account';

    protected array  $scripts  = [[SC_JS_FILE, 'js/account.js']];

    // display status of executed step (forwarding back to this page)
    public ?array    $generalMessage       = null;
    public ?array    $emailMessage         = null;
    public ?array    $usernameMessage      = null;
    public ?array    $passwordMessage      = null;
    public ?array    $communityMessage     = null;
    public ?array    $avatarMessage        = null;
    public ?array    $premiumborderMessage = null;

    // form fields
    public  int      $modelrace        = 0;
    public  int      $modelgender      = 0;
    public  int      $idsInLists       = 0;
    public  string   $curEmail         = '';
    public  string   $curName          = '';
    public  string   $renameCD         = '';
    public  string   $activeCD         = '';
    public  array    $description      = [];
    public  array    $signature        = [];
    public  int      $avMode           = 0;
    public  string   $wowicon          = '';
    public  int      $customicon       = 0;
    public  array    $customicons      = [];
    public  bool     $premium          = false;
    public  int      $reputation       = 0;
    public ?Listview $avatarManager    = null;

    public ?array    $bans;

    public function __construct($rawParam)
    {
        if (!User::isLoggedIn())
            $this->forwardToSignIn('account');

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        array_unshift($this->title, Lang::account('settings'));

        $user = DB::Aowow()->selectRow('SELECT `debug`, `email`, `description`, `avatar`, `wowicon`, `renameCooldown` FROM ?_account WHERE `id` = ?d', User::$id);

        Lang::sort('game', 'ra');

        parent::generate();


        /*************/
        /* Ban Popup */
        /*************/

        $b = DB::Aowow()->select(
           'SELECT    ab.`end` AS "0", ab.`reason` AS "1", a.`username` AS "2"
            FROM      ?_account_banned ab
            LEFT JOIN ?_account a ON a.`id` = ab.`staffId`
            WHERE     ab.`userId` = ?d AND ab.`typeMask` & ?d AND (ab.`end` = 0 OR ab.`end` > UNIX_TIMESTAMP())',
            User::$id, ACC_BAN_TEMP | ACC_BAN_PERM
        );

        $this->bans = $b ?: null;


        /*******************/
        /* Status Messages */
        /*******************/

        if (isset($_SESSION['msg']))
        {
            [$var, $status, $msg] = $_SESSION['msg'];
            if (property_exists($this, $var.'Message'))
                $this->{$var.'Message'} = [$status, $msg];
            else
                trigger_error('AccountBaseResponse::generate - unknown var in $_SESSION msg: '.$var, E_USER_WARNING);

            unset($_SESSION['msg']);
        }


        /*************/
        /* Form Data */
        /*************/

        /* GENERAL */

        // Modelviewer
        if ($_ = DB::Aowow()->selectCell('SELECT `data` FROM ?_account_cookies WHERE `name` = ? AND `userId` = ?d', 'default_3dmodel', User::$id))
            [$this->modelrace, $this->modelgender] = explode(',', $_);

        // Lists
        $this->idsInLists = $user['debug'] ? 1 : 0;

        /* PERSONAL */

        // Email address
        $this->curEmail = $user['email'] ?? '';

        // Username
        $this->curName  = User::$username;
        $this->renameCD = DateTime::formatTimeElapsedFloat(Cfg::get('ACC_RENAME_DECAY') * 1000);
        if ($user['renameCooldown'] > time())
        {
            $locCode = implode('_', str_split(Lang::getLocale()->json(), 2)); // ._.
            $this->activeCD = (new \IntlDateFormatter($locCode, pattern: Lang::main('dateFmtIntl')))->format($user['renameCooldown']);
        }

        /* COMMUNITY */

        // Public Description
        $this->description = ['body' => $user['description']];

        // Forum Signature
        // $this->signature = ['body' => $user['signature']];

        // Avatar
        $this->wowicon = $user['wowicon'];
        $this->avMode  = $user['avatar'];

        /* PREMIUM */

        $this->premium = User::isPremium();

        if (!$this->premium)
            return;

        // required by js to calc reputation border color in user selection
        $this->reputation = User::getReputation();

        // status [reviewing, ok, rejected]? (only 2: rejected processed in js)
        // * 'when': uploaded timestamp expected as msec for some reason
        // * 'caption': only used for getVisibleText, duplicates name?
        // * 'type': always 1 ?, Dialog-popup doesn't work without it
        if ($cuAvatars = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `id`, `name`, `name` AS "caption", `current`, `size`, `status`, `when` * 1000 AS "when", 1 AS "type" FROM ?_account_avatars WHERE `userId` = ?d', User::$id))
        {
            foreach ($cuAvatars as $id => $a)
                if ($a['status'] != AvatarMgr::STATUS_REJECTED)
                    $this->customicons[$id] = $a['name'];

            if ($id = array_find_key($cuAvatars, fn($x) => $x['current'] > 0 ))
                $this->customicon = $id;
        }

        // Avatar Manager
        $this->avatarManager = new Listview([
            'template' => 'avatar',
            'id'       => 'avatar',
            'name'     => '$LANG.tab_avatars',
            'parent'   => 'avatar-manage',
            'hideNav'  => 1 | 2,                            // top | bottom
            'data'     => $cuAvatars ?? [],
            'note'     => Lang::account('avatarSlots', [count($this->customicons), Cfg::get('acc_max_avatar_uploads')])
        ]);

        // Premium Border Selector
        // solved by js
    }
}

?>
