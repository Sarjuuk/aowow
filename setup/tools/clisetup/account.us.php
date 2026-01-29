<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/********************/
/* Account creation */
/********************/

CLISetup::registerUtility(new class extends UtilityScript
{
    public $argvOpts = ['a'];
    public $optGroup = CLISetup::OPT_GRP_SETUP;

    public const COMMAND      = 'account';
    public const DESCRIPTION  = 'Create an account with admin privileges.';
    public const APPENDIX     = ' [name [password [email]]]';
    public const PROMPT       = 'Please create your admin account.';
    public const NOTE_ERROR   = 'There is no user with administrator privileges in the DB.';

    public const REQUIRED_DB = [DB_AOWOW];

    public const USE_CLI_ARGS = true;

    public $runArgs = ['name', 'passw', 'email'];

    private $fields = array(
        'name'  => ['Username',         false],
        'pass1' => ['Enter Password',   true ],
        'pass2' => ['Confirm Password', true ],
        'email' => ['Email (optional)', false]
    );

    // args: username, password, email, null // iiin
    public function run(&$args) : bool
    {
        Lang::load(Locale::EN);

        $name  = $args[0] ?? '';
        $passw = $args[1] ?? '';
        $email = $args[2];

        if (Util::validateUsername($name))
            unset($this->fields['name']);
        else
            $name = '';

        if (Util::validatePassword($passw))
        {
            unset($this->fields['pass1']);
            unset($this->fields['pass2']);
        }
        else
            $passw = '';

        if (Util::validateEmail($email))
            unset($this->fields['email']);
        else
            $email = '';

        if ($this->fields && CLI::read($this->fields, $uiAccount))
        {
            CLI::write();

            if (!$name && !Util::validateUsername($uiAccount['name'], $e))
                CLI::write(Lang::account($e == 1 ? 'errNameLength' : 'errNameChars'), CLI::LOG_ERROR);
            else if (!$name)
                $name = $uiAccount['name'];

            if (!$passw && !Util::validatePassword($uiAccount['pass1'], $e))
                CLI::write($e == 1 ? Lang::account('errPassLength') : Lang::main('intError'), CLI::LOG_ERROR);
            else if (!$passw && $uiAccount['pass1'] != $uiAccount['pass2'])
                CLI::write(Lang::account('passMismatch'), CLI::LOG_ERROR);
            else if (!$passw)
                $passw = $uiAccount['pass1'];

            if (!$email && !empty($uiAccount['email']) && Util::validateEmail($uiAccount['email']))
                $email = $uiAccount['email'];
            else if (!$email && empty($uiAccount['email']))
            {
                $email = Cfg::get('CONTACT_EMAIL');
                CLI::write('[account] no email given, using default: ' . Cfg::get('CONTACT_EMAIL'), CLI::LOG_INFO);
            }
        }
        else if ($this->fields)
        {
            CLI::write();
            CLI::write("[account] admin creation aborted", CLI::LOG_INFO);
            CLI::write();
            return true;
        }

        if (!$name || !$passw || !$email)
            return false;

        if ($username = DB::Aowow()->selectCell('SELECT `username` FROM ?_account WHERE (LOWER(`username`) = LOWER(?) OR LOWER(`email`) = LOWER(?)) AND (`status` <> ?d OR (`status` = ?d AND `statusTimer` > UNIX_TIMESTAMP()))', $name, $email, ACC_STATUS_NEW, ACC_STATUS_NEW))
        {
            CLI::write('[account] ' . (Util::lower($name) == Util::lower($username) ? Lang::account('nameInUse') : Lang::account('mailInUse')), CLI::LOG_ERROR);
            CLI::write();
            return false;
        }

        if (DB::Aowow()->query('REPLACE INTO ?_account (`login`, `passHash`, `username`, `joindate`, `email`, `userGroups`, `userPerms`) VALUES (?, ?, ?, UNIX_TIMESTAMP(), ?, ?d, 1)',
            $name, User::hashCrypt($passw), $name, $email, U_GROUP_ADMIN))
        {
            $newId = DB::Aowow()->selectCell('SELECT `id` FROM ?_account WHERE LOWER(`username`) = LOWER(?)', $name);
            Util::gainSiteReputation($newId, SITEREP_ACTION_REGISTER);

            CLI::write("[account] admin ".$name." created successfully", CLI::LOG_OK);
            CLI::write();

            return true;
        }

        CLI::write('[account] ' . Lang::main('intError'), CLI::LOG_ERROR);
        CLI::write();

        return false;
    }

    public function test(?array &$error = []) : bool
    {
        $error = [];
        return !!DB::Aowow()->selectCell('SELECT `id` FROM ?_account WHERE `userPerms` = 1');
    }
});

?>
