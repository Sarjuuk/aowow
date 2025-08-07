<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed from character profiles, when setting exclusions on collections
 * always returns emptry string
 */

class AccountExcludeResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedPOST  = array(
        'mode'   => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 1, 'max_range' => 1]],
        'reset'  => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 1, 'max_range' => 1]],
        'id'     => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkIdList']        ],
        'type'   => ['filter' => FILTER_VALIDATE_INT                                                   ],
        'groups' => ['filter' => FILTER_VALIDATE_INT                                                   ]
    );

    protected function generate() : void
    {
        if (User::isBanned())
            return;

        if ($this->_post['mode'] == 1)                      // directly set exludes
            $this->excludeById();

        else if ($this->_post['reset'] == 1)                // defaults to unavailable
            $this->resetExcludes();

        else if ($this->_post['groups'])                    // exclude by group mask
            $this->updateGroups();
    }

    private function excludeById() : void
    {
        if (!$this->assertPOST('type', 'id'))
            return;

        if ($validIds = Type::validateIds($this->_post['type'], $this->_post['id']))
        {
            // ready for some bullshit? here it comes!
            // we don't get signaled whether an id should be added to or removed from either includes or excludes
            // so we throw everything into one table and toggle the mode if its already in here

            $includes = DB::Aowow()->selectCol('SELECT `typeId` FROM ?_profiler_excludes WHERE `type` = ?d AND `typeId` IN (?a)', $this->_post['type'], $validIds);

            foreach ($validIds as $typeId)
                DB::Aowow()->query('INSERT INTO ?_account_excludes (`userId`, `type`, `typeId`, `mode`) VALUES (?a) ON DUPLICATE KEY UPDATE `mode` = (`mode` ^ 0x3)',
                    [User::$id, $this->_post['type'], $typeId, in_array($typeId, $includes) ? 2 : 1]
            );
        }
        else
            trigger_error('AccountExcludeResponse::excludeById - validation failed [type: '.$this->_post['type'].', typeId: '.implode(',', $this->_post['id']).']', E_USER_NOTICE);
    }

    private function resetExcludes() : void
    {
        DB::Aowow()->query('DELETE FROM ?_account_excludes WHERE `userId` = ?d', User::$id);
        DB::Aowow()->query('UPDATE ?_account SET `excludeGroups` = ?d WHERE `id` = ?d', PR_EXCLUDE_GROUP_UNAVAILABLE, User::$id);
    }

    private function updateGroups() : void
    {
        if ($this->assertPOST('groups'))                    // clamp to real groups
            DB::Aowow()->query('UPDATE ?_account SET `excludeGroups` = ?d WHERE `id` = ?d', $this->_post['groups'] & PR_EXCLUDE_GROUP_ANY, User::$id);
    }
}

?>
