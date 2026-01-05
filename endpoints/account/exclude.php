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

            $includes = DB::Aowow()->selectCol('SELECT `typeId` FROM ::profiler_excludes WHERE `type` = %i AND `typeId` IN %in', $this->_post['type'], $validIds);
            $insert   = [];
            foreach ($validIds as $typeId)
            {
                $insert['userId'][] = User::$id;
                $insert['type'][]   = $this->_post['type'];
                $insert['typeId'][] = $typeId;
                $insert['mode'][]   = in_array($typeId, $includes) ? Profiler::COMPLETION_INCLUDE : Profiler::COMPLETION_EXCLUDE;
            };

            DB::Aowow()->qry('INSERT INTO ::account_excludes %m ON DUPLICATE KEY UPDATE `mode` = (`mode` ^ 0x3)', $insert);
        }
        else
            trigger_error('AccountExcludeResponse::excludeById - validation failed [type: '.$this->_post['type'].', typeId: '.implode(',', $this->_post['id']).']', E_USER_NOTICE);
    }

    private function resetExcludes() : void
    {
        DB::Aowow()->qry('DELETE FROM ::account_excludes WHERE `userId` = %i', User::$id);
        DB::Aowow()->qry('UPDATE ::account SET `excludeGroups` = %i WHERE `id` = %i', PR_EXCLUDE_GROUP_UNAVAILABLE, User::$id);
    }

    private function updateGroups() : void
    {
        if ($this->assertPOST('groups'))                    // clamp to real groups
            DB::Aowow()->qry('UPDATE ::account SET `excludeGroups` = %i WHERE `id` = %i', $this->_post['groups'] & PR_EXCLUDE_GROUP_ANY, User::$id);
    }
}

?>
