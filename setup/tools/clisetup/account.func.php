<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

if (!CLI)
    die('not in cli mode');


/********************/
/* Account creation */
/********************/

function account()
{
    $fields = array(
        'name'  => ['Username',         false],
        'pass1' => ['Enter Password',   true ],
        'pass2' => ['Confirm Password', true ]
    );

    User::useLocale(LOCALE_EN);
    Lang::load(Util::$localeStrings[LOCALE_EN]);

    if (CLISetup::readInput($fields))
    {
        CLISetup::log();

        if (!User::isValidName($fields['name'], $e))
            CLISetup::log(Lang::account($e == 1 ? 'errNameLength' : 'errNameChars'), CLISetup::LOG_ERROR);
        else if (!User::isValidPass($fields['pass1'], $e))
            CLISetup::log(Lang::account($e == 1 ? 'errPassLength' : 'errPassChars'), CLISetup::LOG_ERROR);
        else if ($fields['pass1'] != $fields['pass2'])
            CLISetup::log(Lang::account('passMismatch'), CLISetup::LOG_ERROR);
        else if ($_ = DB::Aowow()->SelectCell('SELECT 1 FROM ?_account WHERE user = ? AND (status <> ?d OR (status = ?d AND statusTimer > UNIX_TIMESTAMP()))', $fields['name'], ACC_STATUS_NEW, ACC_STATUS_NEW))
            CLISetup::log(Lang::account('nameInUse'), CLISetup::LOG_ERROR);
        else
        {
            // write to db
            $ok = DB::Aowow()->query('REPLACE INTO ?_account (user, passHash, displayName, joindate, email, allowExpire, userGroups, userPerms) VALUES (?, ?, ?, UNIX_TIMESTAMP(), ?, 0, ?d, 1)',
                $fields['name'],
                User::hashCrypt($fields['pass1']),
                Util::ucFirst($fields['name']),
                CFG_CONTACT_EMAIL,
                U_GROUP_ADMIN
            );
            if ($ok)
            {
                $newId = DB::Aowow()->selectCell('SELECT id FROM ?_account WHERE user = ?', $fields['name']);
                Util::gainSiteReputation($newId, SITEREP_ACTION_REGISTER);

                CLISetup::log("account ".$fields['name']." created successfully", CLISetup::LOG_OK);
            }
            else                                            // something went wrong
                CLISetup::log(Lang::main('intError'), CLISetup::LOG_ERROR);
        }
    }
    else
    {
        CLISetup::log();
        CLISetup::log("account creation aborted", CLISetup::LOG_INFO);
    }
}

?>
