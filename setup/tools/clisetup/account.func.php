<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/********************/
/* Account creation */
/********************/

function account() : void
{
    $fields = array(
        'name'  => ['Username',         false],
        'pass1' => ['Enter Password',   true ],
        'pass2' => ['Confirm Password', true ]
    );

    User::useLocale(LOCALE_EN);
    Lang::load(LOCALE_EN);

    if (CLI::read($fields))
    {
        CLI::write();

        if (!User::isValidName($fields['name'], $e))
            CLI::write(Lang::account($e == 1 ? 'errNameLength' : 'errNameChars'), CLI::LOG_ERROR);
        else if (!User::isValidPass($fields['pass1'], $e))
            CLI::write(Lang::account($e == 1 ? 'errPassLength' : 'errPassChars'), CLI::LOG_ERROR);
        else if ($fields['pass1'] != $fields['pass2'])
            CLI::write(Lang::account('passMismatch'), CLI::LOG_ERROR);
        else if ($_ = DB::Aowow()->SelectCell('SELECT 1 FROM ?_account WHERE user = ? AND (status <> ?d OR (status = ?d AND statusTimer > UNIX_TIMESTAMP()))', $fields['name'], ACC_STATUS_NEW, ACC_STATUS_NEW))
            CLI::write(Lang::account('nameInUse'), CLI::LOG_ERROR);
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

                CLI::write("account ".$fields['name']." created successfully", CLI::LOG_OK);
            }
            else                                            // something went wrong
                CLI::write(Lang::main('intError'), CLI::LOG_ERROR);
        }
    }
    else
    {
        CLI::write();
        CLI::write("account creation aborted", CLI::LOG_INFO);
    }
}

?>
