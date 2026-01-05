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
        if ($this->assertGET('key') && DB::Aowow()->selectCell('SELECT 1 FROM ::account WHERE `status` = %i AND `statusTimer` > UNIX_TIMESTAMP() AND `token` = %s', ACC_STATUS_PURGING, $this->_get['key']))
        {
            $this->key = $this->_get['key'];
            return;
        }

        // perform action and display status
        if ($this->assertPOST('key') && ($userId = DB::Aowow()->selectCell('SELECT `id` FROM ::account WHERE `status` = %i AND `statusTimer` > UNIX_TIMESTAMP() AND `token` = %s', ACC_STATUS_PURGING, $this->_post['key'])))
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
        if (DB::Aowow()->qry('UPDATE ::account SET `status` = %i, `statusTimer` = 0, `token` = "" WHERE `id` = %i', ACC_STATUS_NONE, $userId))
        {
            $this->success = true;
            return Lang::account('inputbox', 'message', 'deleteCancel');
        }

        return Lang::main('intError');
    }

    private function purge(int $userId) : string
    {
        // empty all user settings and cookies
        DB::Aowow()->qry('DELETE FROM ::account_cookies WHERE `userId` = %i', $userId);
        DB::Aowow()->qry('DELETE FROM ::account_avatars WHERE `userId` = %i', $userId);
        DB::Aowow()->qry('DELETE FROM ::account_excludes WHERE `userId` = %i', $userId);
        DB::Aowow()->qry('DELETE FROM ::account_favorites WHERE `userId` = %i', $userId);
        DB::Aowow()->qry('DELETE FROM ::account_reputation WHERE `userId` = %i', $userId);
        DB::Aowow()->qry('DELETE FROM ::account_weightscales WHERE `userId` = %i', $userId); // cascades to aowow_account_weightscale_data

        // delete profiles, unlink chars
        DB::Aowow()->qry('DELETE pp FROM ::profiler_profiles pp JOIN ::account_profiles ap ON ap.`profileId` = pp.`id` WHERE ap.`accountId` = %i', $userId);
     // DB::Aowow()->qry('DELETE FROM ::account_profiles WHERE `accountId` = %i', $userId); // already deleted via FK?

        // delete all sessions and bans
        DB::Aowow()->qry('DELETE FROM ::account_banned WHERE `userId` = %i', $userId);
        DB::Aowow()->qry('DELETE FROM ::account_sessions WHERE `userId` = %i', $userId);

        // delete forum posts (msg: This post was from a user who has deleted their account. (no translations at src); comments/replies are unaffected)
        // ...

        // replace username with userId and empty fields
        DB::Aowow()->qry(
           'UPDATE ::account SET
                `login`       = "", `passHash`    = "", `username` = `id`, `email`       = NULL, `userGroups`     = 0,  `userPerms`   = 0,
                `curIp`       = "", `prevIp`      = "", `curLogin` = 0,    `prevLogin`   = 0,
                `locale`      = 0,  `debug`       = 0,  `avatar`   = 0,    `wowicon`     = "",   `title`          = "", `description` = "", `excludeGroups` = 0,
                `status`      = %i, `statusTimer` = 0,  `token`    = "",   `updateValue` = "",   `renameCooldown` = 0
            WHERE `id` = %i',
            ACC_STATUS_DELETED, $userId
        );

        $this->success = true;
        return Lang::account('inputbox', 'message', 'deleteOk');
    }
}

?>
