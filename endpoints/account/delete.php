<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via account settings link
 * empty page with status box
 */

class AccountDeleteResponse extends TemplateResponse
{
    protected bool   $requiresLogin = true;

    protected string $template      = 'delete';
    protected string $pageName      = 'delete';

    protected array  $scripts       = [[SC_CSS_FILE, 'css/delete.css']];

    protected array  $expectedPOST  = array(
        'proceed' => ['filter' => FILTER_UNSAFE_RAW]
    );

    public  string $username         = '';
    public  string $deleteFormTarget = '?account=delete';
    public ?array  $inputbox         = null;

    public function __construct(string $rawParam)
    {
        if (Cfg::get('ACC_AUTH_MODE') != AUTH_MODE_SELF)
            $this->generateError();

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        array_unshift($this->title, Lang::account('accDelete'));

        parent::generate();

        $this->username = User::$username;

        if ($this->_post['proceed'])
        {
            $error = false;
            if (!DB::Aowow()->selectCell('SELECT 1 FROM ?_account WHERE `status` NOT IN (?a) AND `statusTimer` > UNIX_TIMESTAMP() AND `id` = ?d', [ACC_STATUS_NEW, ACC_STATUS_NONE, ACC_STATUS_PURGING], User::$id))
            {
                $token = Util::createHash(40);

                DB::Aowow()->query('UPDATE ?_account SET `status` = ?d, `statusTimer` = UNIX_TIMESTAMP() + ?d, `token` = ? WHERE `id` = ?d',
                    ACC_STATUS_PURGING, Cfg::get('ACC_RECOVERY_DECAY'), $token, User::$id);

                Util::sendMail(User::$email, 'delete-account', [$token, User::$email, User::$username]);
            }
            else
                $error = true;

            $this->inputbox = ['inputbox-status', array(
                'head'    => Lang::account('inputbox', 'head', $error ? 'error' : 'success'),
                'message' => $error ? '' : Lang::account('inputbox', 'message', 'deleteAccSent', [User::$email]),
                'error'   => $error ? Lang::account('inputbox', 'error', 'isRecovering') : ''
            )];
        }
    }
}

?>
