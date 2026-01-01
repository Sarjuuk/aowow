<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// custom handler
class AccountConfirmdeleteResponse extends TemplateResponse
{
    protected string $template      = 'delete';
    protected string $pageName      = 'confirm-delete';

    protected array  $scripts       = array(
        [SC_CSS_FILE, 'css/delete.css'],
        [SC_CSS_STRING, '[type="submit"] { margin: 0px 10px; }']
    );

    protected array $expectedGET    = array(
        'key' => [FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[a-zA-Z0-9]{40}$/']]
    );
    protected array $expectedPOST   = array(
        'submit'  => [FILTER_UNSAFE_RAW                                                       ],
        'cancel'  => [FILTER_UNSAFE_RAW                                                       ],
        'confirm' => [FILTER_CALLBACK,        'options' => [self::class, 'checkEmptySet']     ],
        'key'     => [FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[a-zA-Z0-9]{40}$/']]
    );

    public  bool   $confirm = true;                          // just to select the correct localized brick
    public  string $username         = '';
    public  string $deleteFormTarget = '?account=confirm-delete';
    public ?array  $inputbox         = null;
    public  string $key              = '';

    private bool $success = false;

    public function __construct(string $rawParam)
    {
        if (Cfg::get('ACC_AUTH_MODE') != AUTH_MODE_SELF)
            $this->generateError();

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        array_unshift($this->title, Lang::account('accDelete'));

        $this->username = User::$username;

        parent::generate();

        $msg = Lang::account('inputbox', 'error', 'purgeTokenUsed');

        // display default confirm template
        if ($this->assertGET('key') && DB::Aowow()->selectCell('SELECT 1 FROM ?_account WHERE `status` = ?d AND `statusTimer` > UNIX_TIMESTAMP() AND `token` = ?', ACC_STATUS_PURGING, $this->_get['key']))
        {
            $this->key = $this->_get['key'];
            return;
        }

        // perform action and display status
        if ($this->assertPOST('key') && ($userId = DB::Aowow()->selectCell('SELECT `id` FROM ?_account WHERE `status` = ?d AND `statusTimer` > UNIX_TIMESTAMP() AND `token` = ?', ACC_STATUS_PURGING, $this->_post['key'])))
        {
            if ($this->_post['cancel'])
                $msg = $this->cancel($userId);
            else if ($this->_post['submit'] && $this->_post['confirm'])
                $msg = $this->purge($userId);
        }

        // throw error and display in status
        $this->inputbox = ['inputbox-status', array(
            'head' => Lang::account('inputbox', 'head', $this->success ? 'success' : 'error'),
            'message' => $this->success ? $msg : '',
            'error'   => $this->success ? '' : $msg
        )];
    }

    private function cancel(int $userId) : string
    {
        if (DB::Aowow()->query('UPDATE ?_account SET `status` = ?d, `statusTimer` = 0, `token` = "" WHERE `id` = ?d', ACC_STATUS_NONE, $userId))
        {
            $this->success = true;
            return Lang::account('inputbox', 'message', 'deleteCancel');
        }

        return Lang::main('intError');
    }

    private function purge(int $userId) : string
    {
        // empty all user settings and cookies
        DB::Aowow()->query('DELETE FROM ?_account_cookies WHERE `userId` = ?d', $userId);
        DB::Aowow()->query('DELETE FROM ?_account_avatars WHERE `userId` = ?d', $userId);
        DB::Aowow()->query('DELETE FROM ?_account_excludes WHERE `userId` = ?d', $userId);
        DB::Aowow()->query('DELETE FROM ?_account_favorites WHERE `userId` = ?d', $userId);
        DB::Aowow()->query('DELETE FROM ?_account_reputation WHERE `userId` = ?d', $userId);
        DB::Aowow()->query('DELETE FROM ?_account_weightscales WHERE `userId` = ?d', $userId); // cascades to aowow_account_weightscale_data

        // delete profiles, unlink chars
        DB::Aowow()->query('DELETE pp FROM ?_profiler_profiles pp JOIN ?_account_profiles ap ON ap.`profileId` = pp.`id` WHERE ap.`accountId` = ?d', $userId);
     // DB::Aowow()->query('DELETE FROM ?_account_profiles WHERE `accountId` = ?d', $userId); // already deleted via FK?

        // delete all sessions and bans
        DB::Aowow()->query('DELETE FROM ?_account_banned WHERE `userId` = ?d', $userId);
        DB::Aowow()->query('DELETE FROM ?_account_sessions WHERE `userId` = ?d', $userId);

        // delete forum posts (msg: This post was from a user who has deleted their account. (no translations at src); comments/replies are unaffected)
        // ...

        // replace username with userId and empty fields
        DB::Aowow()->query(
           'UPDATE ?_account SET
                `login`       = "", `passHash`    = "", `username` = `id`, `email`       = NULL, `userGroups`     = 0,  `userPerms`   = 0,
                `curIp`       = "", `prevIp`      = "", `curLogin` = 0,    `prevLogin`   = 0,
                `locale`      = 0,  `debug`       = 0,  `avatar`   = 0,    `wowicon`     = "",   `title`          = "", `description` = "", `excludeGroups` = 0,
                `status`      = ?d, `statusTimer` = 0,  `token`    = "",   `updateValue` = "",   `renameCooldown` = 0
            WHERE `id` = ?d',
            ACC_STATUS_DELETED, $userId
        );

        $this->success = true;
        return Lang::account('inputbox', 'message', 'deleteOk');
    }
}

?>
