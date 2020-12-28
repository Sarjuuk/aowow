<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxProfile extends AjaxHandler
{
    private   $undo        = false;

    protected $validParams = ['link', 'unlink', 'pin', 'unpin', 'public', 'private', 'avatar', 'resync', 'status', 'save', 'delete', 'purge', 'summary', 'load'];
    protected $_get        = array(
        'id'         => [FILTER_CALLBACK,        ['options' => 'AjaxHandler::checkIdList']     ],
        'items'      => [FILTER_CALLBACK,        ['options' => 'AjaxProfile::checkItemList']   ],
        'size'       => [FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
        'guild'      => [FILTER_CALLBACK,        ['options' => 'AjaxHandler::checkEmptySet']   ],
        'arena-team' => [FILTER_CALLBACK,        ['options' => 'AjaxHandler::checkEmptySet']   ],
        'user'       => [FILTER_CALLBACK,        ['options' => 'AjaxProfile::checkUser']       ]
    );

    protected $_post        = array(
        'name'         => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkFulltext']                                       ],
        'level'        => [FILTER_SANITIZE_NUMBER_INT, null                                                                              ],
        'class'        => [FILTER_SANITIZE_NUMBER_INT, null                                                                              ],
        'race'         => [FILTER_SANITIZE_NUMBER_INT, null                                                                              ],
        'gender'       => [FILTER_SANITIZE_NUMBER_INT, null                                                                              ],
        'nomodel'      => [FILTER_SANITIZE_NUMBER_INT, null                                                                              ],
        'talenttree1'  => [FILTER_SANITIZE_NUMBER_INT, null                                                                              ],
        'talenttree2'  => [FILTER_SANITIZE_NUMBER_INT, null                                                                              ],
        'talenttree3'  => [FILTER_SANITIZE_NUMBER_INT, null                                                                              ],
        'activespec'   => [FILTER_SANITIZE_NUMBER_INT, null                                                                              ],
        'talentbuild1' => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH                                    ],
        'glyphs1'      => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH                                    ],
        'talentbuild2' => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH                                    ],
        'glyphs2'      => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH                                    ],
        'icon'         => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH                                    ],
        'description'  => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkFulltext']                                       ],
        'source'       => [FILTER_SANITIZE_NUMBER_INT, null                                                                              ],
        'copy'         => [FILTER_SANITIZE_NUMBER_INT, null                                                                              ],
        'public'       => [FILTER_SANITIZE_NUMBER_INT, null                                                                              ],
        'gearscore'    => [FILTER_SANITIZE_NUMBER_INT, null                                                                              ],
        'inv'          => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkIdListUnsigned', 'flags' => FILTER_REQUIRE_ARRAY]],
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        if (!$this->params)
            return;

        if (!CFG_PROFILER_ENABLE)
            return;

        switch ($this->params[0])
        {
            case 'unlink':
                $this->undo = true;
            case 'link':
                $this->handler = 'handleLink';              // always returns null
                break;
            case 'unpin':
                $this->undo = true;
            case 'pin':
                $this->handler = 'handlePin';               // always returns null
                break;
            case 'private':
                $this->undo = true;
            case 'public':
                $this->handler = 'handlePrivacy';           // always returns null
                break;
            case 'avatar':
                $this->handler = 'handleAvatar';            // sets an image header
                break;                                      // so it has to die here or another header will be set
            case 'resync':
                $this->handler = 'handleResync';            // always returns "1"
                break;
            case 'status':
                $this->handler = 'handleStatus';            // returns status object
                break;
            case 'save':
                $this->handler = 'handleSave';
                break;
            case 'delete':
                $this->handler = 'handleDelete';
                break;
            case 'purge':
                $this->handler = 'handlePurge';
                break;
            case 'summary':                                 // page is generated by jScript
                die();                                      // just be empty
            case 'load':
                $this->handler = 'handleLoad';
                break;
        }
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
            user: <string> [optional]
        return: null
    */
    protected function handleLink() : void                  // links char with account
    {
        if (!User::$id || empty($this->_get['id']))
        {
            trigger_error('AjaxProfile::handleLink - profileId empty or user not logged in', E_USER_ERROR);
            return;
        }

        $uid = User::$id;
        if ($this->_get['user'] && User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
        {
            if (!($uid = DB::Aowow()->selectCell('SELECT id FROM ?_account WHERE user = ?', $this->_get['user'])))
            {
                trigger_error('AjaxProfile::handleLink - user "'.$this->_get['user'].'" does not exist', E_USER_ERROR);
                return;
            }
        }

        if ($this->undo)
            DB::Aowow()->query('DELETE FROM ?_account_profiles WHERE accountId = ?d AND profileId IN (?a)', $uid, $this->_get['id']);
        else
        {
            foreach ($this->_get['id'] as $prId)            // only link characters, not custom profiles
            {
                if ($prId = DB::Aowow()->selectCell('SELECT id FROM ?_profiler_profiles WHERE id = ?d AND realm IS NOT NULL', $prId))
                    DB::Aowow()->query('INSERT IGNORE INTO ?_account_profiles VALUES (?d, ?d, 0)', $uid, $prId);
                else
                {
                    trigger_error('AjaxProfile::handleLink - profile #'.$prId.' is custom or does not exist', E_USER_ERROR);
                    return;
                }
            }
        }
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
            user: <string> [optional]
        return: null
    */
    protected function handlePin() : void                   // (un)favorite
    {
        if (!User::$id || empty($this->_get['id'][0]))
        {
            trigger_error('AjaxProfile::handlePin - profileId empty or user not logged in', E_USER_ERROR);
            return;
        }

        $uid = User::$id;
        if ($this->_get['user'] && User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
        {
            if (!($uid = DB::Aowow()->selectCell('SELECT id FROM ?_account WHERE user = ?', $this->_get['user'])))
            {
                trigger_error('AjaxProfile::handlePin - user "'.$this->_get['user'].'" does not exist', E_USER_ERROR);
                return;
            }
        }

        // since only one character can be pinned at a time we can reset everything
        DB::Aowow()->query('UPDATE ?_account_profiles  SET extraFlags = extraFlags & ?d WHERE accountId = ?d', ~PROFILER_CU_PINNED, $uid);
        // and set a single char if necessary
        if (!$this->undo)
            DB::Aowow()->query('UPDATE ?_account_profiles  SET extraFlags = extraFlags | ?d WHERE profileId = ?d AND accountId = ?d',  PROFILER_CU_PINNED, $this->_get['id'][0], $uid);
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
            user: <string> [optional]
        return: null
    */
    protected function handlePrivacy() : void               // public visibility
    {
        if (!User::$id || empty($this->_get['id'][0]))
        {
            trigger_error('AjaxProfile::handlePrivacy - profileId empty or user not logged in', E_USER_ERROR);
            return;
        }

        $uid = User::$id;
        if ($this->_get['user'] && User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
        {
            if (!($uid = DB::Aowow()->selectCell('SELECT id FROM ?_account WHERE user = ?', $this->_get['user'])))
            {
                trigger_error('AjaxProfile::handlePrivacy - user "'.$this->_get['user'].'" does not exist', E_USER_ERROR);
                return;
            }
        }

        if ($this->undo)
        {
            DB::Aowow()->query('UPDATE ?_account_profiles  SET extraFlags = extraFlags & ?d WHERE profileId IN (?a) AND accountId = ?d', ~PROFILER_CU_PUBLISHED, $this->_get['id'], $uid);
            DB::Aowow()->query('UPDATE ?_profiler_profiles SET cuFlags    = cuFlags    & ?d WHERE id        IN (?a) AND user      = ?d', ~PROFILER_CU_PUBLISHED, $this->_get['id'], $uid);
        }
        else
        {
            DB::Aowow()->query('UPDATE ?_account_profiles  SET extraFlags = extraFlags | ?d WHERE profileId IN (?a) AND accountId = ?d',  PROFILER_CU_PUBLISHED, $this->_get['id'], $uid);
            DB::Aowow()->query('UPDATE ?_profiler_profiles SET cuFlags    = cuFlags    | ?d WHERE id        IN (?a) AND user      = ?d',  PROFILER_CU_PUBLISHED, $this->_get['id'], $uid);
        }
    }

    /*  params
            id: <prId>
            size: <string> [optional]
        return: image-header
    */
    protected function handleAvatar() : void                // image
    {
        // something happened in the last years: those textures do not include tiny icons
        $sizes = [/* 'tiny' => 15, */'small' => 18, 'medium' => 36, 'large' => 56];
        $aPath = 'uploads/avatars/%d.jpg';
        $s     = $this->_get['size'] ?: 'medium';

        if (!$this->_get['id'] || !preg_match('/^([0-9]+)\.(jpg|gif)$/', $this->_get['id'][0], $matches) || !in_array($s, array_keys($sizes)))
        {
            trigger_error('AjaxProfile::handleAvatar - malformed request received', E_USER_ERROR);
            return;
        }

        $this->contentType = $matches[2] == 'png' ? MIME_TYPE_PNG : MIME_TYPE_JPEG;

        $id   = $matches[1];
        $dest = imageCreateTruecolor($sizes[$s], $sizes[$s]);

        if (file_exists(sprintf($aPath, $id)))
        {
            $offsetX = $offsetY = 0;

            switch ($s)
            {
                case 'tiny':
                    $offsetX += $sizes['small'];
                case 'small':
                    $offsetY += $sizes['medium'];
                case 'medium':
                    $offsetX += $sizes['large'];
            }

            $src = imageCreateFromJpeg(printf($aPath, $id));
            imagecopymerge($dest, $src, 0, 0, $offsetX, $offsetY, $sizes[$s], $sizes[$s], 100);
        }
        else
            trigger_error('AjaxProfile::handleAvatar - avatar file #'.$id.' not found', E_USER_ERROR);

        if ($matches[2] == 'gif')
            imageGif($dest);
        else
            imageJpeg($dest);
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
            user: <string> [optional, not used]
        return: 1
    */
    protected function handleResync() : string
    {
        if ($chars = DB::Aowow()->select('SELECT realm, realmGUID FROM ?_profiler_profiles WHERE id IN (?a)', $this->_get['id']))
        {
            foreach ($chars as $c)
                Profiler::scheduleResync(TYPE_PROFILE, $c['realm'], $c['realmGUID']);
        }
        else
            trigger_error('AjaxProfile::handleResync - profiles '.implode(', ', $this->_get['id']).' not found in db', E_USER_ERROR);

        return '1';
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
        return
            <status object>
            [
                nQueueProcesses,
                [statusCode, timeToRefresh, curQueuePos, errorCode, nResyncTries],
                [<anotherStatus>]
                ...
            ]

            not all fields are required, if zero they are omitted
            statusCode:
                0: end the request
                1: waiting
                2: working...
                3: ready; click to view
                4: error / retry
            errorCode:
                0: unk error
                1: char does not exist
                2: armory gone
    */
    protected function handleStatus() : string
    {
        // roster resync for this guild was requested -> get char list
        if ($this->_get['guild'])
            $ids = DB::Aowow()->selectCol('SELECT id FROM ?_profiler_profiles WHERE guild IN (?a)', $this->_get['id']);
        else if ($this->_get['arena-team'])
            $ids = DB::Aowow()->selectCol('SELECT profileId FROM ?_profiler_arena_team_member WHERE arenaTeamId IN (?a)', $this->_get['id']);
        else
            $ids = $this->_get['id'];

        if (!$ids)
        {
            trigger_error('AjaxProfile::handleStatus - no profileIds to resync'.($this->_get['guild'] ? ' for guild #'.$this->_get['guild'] : ($this->_get['arena-team'] ? ' for areana team #'.$this->_get['arena-team'] : '')), E_USER_ERROR);
            return Util::toJSON([1, [PR_QUEUE_STATUS_ERROR, 0, 0, PR_QUEUE_ERROR_CHAR]]);
        }

        $response = Profiler::resyncStatus(TYPE_PROFILE, $ids);
        return Util::toJSON($response);
    }

    /*  params (get))
            id: <prId1,0> [0: new profile]
        params (post)
            <various char data> [see below]
        return:
            proileId [onSuccess]
            -1       [onError]
    */
    protected function handleSave() : string                // unKill a profile
    {
        // todo (med): detail check this post-data
        $cuProfile = array(
            'user'         => User::$id,
         // 'userName'     => User::$displayName,
            'name'         => $this->_post['name'],
            'level'        => $this->_post['level'],
            'class'        => $this->_post['class'],
            'race'         => $this->_post['race'],
            'gender'       => $this->_post['gender'],
            'nomodelMask'  => $this->_post['nomodel'],
            'talenttree1'  => $this->_post['talenttree1'],
            'talenttree2'  => $this->_post['talenttree2'],
            'talenttree3'  => $this->_post['talenttree3'],
            'talentbuild1' => $this->_post['talentbuild1'],
            'talentbuild2' => $this->_post['talentbuild2'],
            'activespec'   => $this->_post['activespec'],
            'glyphs1'      => $this->_post['glyphs1'],
            'glyphs2'      => $this->_post['glyphs2'],
            'gearscore'    => $this->_post['gearscore'],
            'icon'         => $this->_post['icon'],
            'cuFlags'      => PROFILER_CU_PROFILE | ($this->_post['public'] ? PROFILER_CU_PUBLISHED : 0)
        );

        if (strstr($cuProfile['icon'], 'profile=avatar'))   // how the profiler is supposed to handle icons is beyond me
            $cuProfile['icon'] = '';

        if ($_ = $this->_post['description'])
            $cuProfile['description'] = $_;

        if ($_ = $this->_post['source'])                    // should i also set sourcename?
            $cuProfile['sourceId'] = $_;

        if ($_ = $this->_post['copy'])                      // gets set to source profileId when "save as" is clicked. Whats the difference to 'source' though?
        {
            // get character origin info if possible
            if ($r = DB::Aowow()->selectCell('SELECT realm FROM ?_profiler_profiles WHERE id = ?d AND realm IS NOT NULL', $_))
                $cuProfile['realm'] = $r;

            $cuProfile['sourceId'] = $_;
        }

        if ($cuProfile['sourceId'])
            $cuProfile['sourceName'] = DB::Aowow()->selectCell('SELECT name FROM ?_profiler_profiles WHERE id = ?d', $cuProfile['sourceId']);

        $charId = -1;
        if ($id = $this->_get['id'][0])                     // update
        {
            if ($charId = DB::Aowow()->selectCell('SELECT id FROM ?_profiler_profiles WHERE id = ?d', $id))
                DB::Aowow()->query('UPDATE ?_profiler_profiles SET ?a WHERE id = ?d', $cuProfile, $id);
        }
        else                                                // new
        {
            $nProfiles = DB::Aowow()->selectCell('SELECT COUNT(*) FROM ?_profiler_profiles WHERE user = ?d AND (cuFlags & ?d) = 0 AND realmGUID IS NULL', User::$id, PROFILER_CU_DELETED);
            if ($nProfiles < 10 || User::isPremium())
                if ($newId = DB::Aowow()->query('INSERT INTO ?_profiler_profiles (?#) VALUES (?a)', array_keys($cuProfile), array_values($cuProfile)))
                    $charId = $newId;
        }

        // update items
        if ($charId != -1)
        {
            // ok, 'funny' thing: wether an item has en extra prismatic sockel is determined contextual
            // either the socket is -1 or it has an itemId in a socket where there shouldn't be one
            $keys  = ['id', 'slot', 'item', 'subitem', 'permEnchant', 'tempEnchant', 'gem1', 'gem2', 'gem3', 'gem4'];

            // validate Enchantments
            $enchIds = array_merge(
                array_column($this->_post['inv'], 3),       // perm enchantments
                array_column($this->_post['inv'], 4)        // temp enchantments (not used..?)
            );
            $enchs = new EnchantmentList(array(['id', $enchIds]));

            // validate items
            $itemIds = array_merge(
                array_column($this->_post['inv'], 1),       // base item
                array_column($this->_post['inv'], 5),       // gem slot 1
                array_column($this->_post['inv'], 6),       // gem slot 2
                array_column($this->_post['inv'], 7),       // gem slot 3
                array_column($this->_post['inv'], 8)        // gem slot 4
            );

            $items = new ItemList(array(['id', $itemIds]));
            if (!$items->error)
            {
                foreach ($this->_post['inv'] as $slot => $itemData)
                {
                    if ($slot + 1 == array_sum($itemData))  // only slot definition set => empty slot
                    {
                        DB::Aowow()->query('DELETE FROM ?_profiler_items WHERE id = ?d AND slot = ?d', $charId, $itemData[0]);
                        continue;
                    }

                    // item does not exist
                    if (!$items->getEntry($itemData[1]))
                        continue;

                    // sub-item check
                    if (!$items->getRandEnchantForItem($itemData[1]))
                        $itemData[2] = 0;

                    // item sockets are fubar
                    $nSockets = $items->json[$itemData[1]]['nsockets'];
                    $nSockets += in_array($slot, [SLOT_WAIST, SLOT_WRISTS, SLOT_HANDS]) ? 1 : 0;
                    for ($i = 5; $i < 9; $i++)
                        if ($itemData[$i] > 0 && (!$items->getEntry($itemData[$i]) || $i >= (5 + $nSockets)))
                            $itemData[$i] = 0;

                    // item enchantments are borked
                    if ($itemData[3] && !$enchs->getEntry($itemData[3]))
                        $itemData[3] = 0;

                    if ($itemData[4] && !$enchs->getEntry($itemData[4]))
                        $itemData[4] = 0;

                    // looks good
                    array_unshift($itemData, $charId);
                    DB::Aowow()->query('REPLACE INTO ?_profiler_items (?#) VALUES (?a)', $keys, $itemData);
                }
            }
        }

        return (string)$charId;
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
        return
            null
    */
    protected function handleDelete() : void                // kill a profile
    {
        if (!User::$id || !$this->_get['id'])
        {
            trigger_error('AjaxProfile::handleDelete - profileId empty or user not logged in', E_USER_ERROR);
            return;
        }

        // only flag as deleted; only custom profiles
        DB::Aowow()->query(
            'UPDATE ?_profiler_profiles SET cuFlags = cuFlags | ?d WHERE id IN (?a) AND cuFlags & ?d {AND user = ?d}',
            PROFILER_CU_DELETED,
            $this->_get['id'],
            PROFILER_CU_PROFILE,
            User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU) ? DBSIMPLE_SKIP : User::$id
        );
    }

    /*  params
            id: profileId
            items: string       [itemIds.join(':')]
            unnamed: unixtime   [only to force the browser to reload instead of cache]
        return
            lots...
    */
    protected function handleLoad() : string
    {
        // titles, achievements, characterData, talents, pets
        // and some onLoad-hook to .. load it registerProfile($data)
        // everything else goes through data.php .. strangely enough

        if (!$this->_get['id'])
        {
            trigger_error('AjaxProfile::handleLoad - profileId empty', E_USER_ERROR);
            return '';
        }

        $pBase = DB::Aowow()->selectRow('SELECT pg.name AS guildname, p.* FROM ?_profiler_profiles p LEFT JOIN ?_profiler_guild pg ON pg.id = p.guild WHERE p.id = ?d', $this->_get['id'][0]);
        if (!$pBase)
        {
            trigger_error('Profiler::handleLoad - called with invalid profileId #'.$this->_get['id'][0], E_USER_WARNING);
            return '';
        }

        if (($pBase['cuFlags'] & PROFILER_CU_DELETED) && !User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
            return '';


        $rData = [];
        foreach (Profiler::getRealms() as $rId => $rData)
            if ($rId == $pBase['realm'])
                break;

        $profile = array(
            'id'                => $pBase['id'],
            'source'            => $pBase['id'],
            'level'             => $pBase['level'],
            'classs'            => $pBase['class'],
            'race'              => $pBase['race'],
            'faction'           => Game::sideByRaceMask(1 << ($pBase['race'] - 1)) - 1,
            'gender'            => $pBase['gender'],
            'skincolor'         => $pBase['skincolor'],
            'hairstyle'         => $pBase['hairstyle'],
            'haircolor'         => $pBase['haircolor'],
            'facetype'          => $pBase['facetype'],
            'features'          => $pBase['features'],
            'title'             => $pBase['title'],
            'name'              => $pBase['name'],
            'guild'             => "$'".$pBase['guildname']."'",
            'published'         => !!($pBase['cuFlags'] & PROFILER_CU_PUBLISHED),
            'pinned'            => !!($pBase['cuFlags'] & PROFILER_CU_PINNED),
            'nomodel'           => $pBase['nomodelMask'],
            'playedtime'        => $pBase['playedtime'],
            'lastupdated'       => $pBase['lastupdated'] * 1000,
            'talents'           => array(
                'builds' => array(                          // notice the bullshit to prevent the talent-string from becoming a float! NOTICE IT!!
                    ['talents' => '$"'.$pBase['talentbuild1'].'"', 'glyphs' => $pBase['glyphs1']],
                    ['talents' => '$"'.$pBase['talentbuild2'].'"', 'glyphs' => $pBase['glyphs2']]
                ),
                'active' => $pBase['activespec']
            ),
            // set later
            'inventory'         => [],
            'bookmarks'         => [],                      // list of userIds who claimed this profile (claiming and owning are two different things)

            // completion lists: [subjectId => amount/timestamp/1]
            'skills'            => [],                      // skillId => [curVal, maxVal]
            'reputation'        => [],                      // factionId => curVal
            'titles'            => [],                      // titleId => 1
            'spells'            => [],                      // spellId => 1; recipes, vanity pets, mounts
            'achievements'      => [],                      // achievementId => timestamp
            'quests'            => [],                      // questId => 1
            'achievementpoints' => 0,                       // max you have
            'statistics'        => [],                      // all raid activity    [achievementId => killCount]
            'activity'          => [],                      // recent raid activity [achievementId => 1] (is a subset of statistics)
        );

        if ($pBase['cuFlags'] & PROFILER_CU_PROFILE)
        {
            // this parameter is _really_ strange .. probably still not doing this right
            $profile['source']      = $pBase['realm'] ? $pBase['sourceId'] : 0;

            $profile['sourcename']  = $pBase['sourceName'];
            $profile['description'] = $pBase['description'];
            $profile['user']        = $pBase['user'];
            $profile['username']    = DB::Aowow()->selectCell('SELECT displayName FROM ?_account WHERE id = ?d', $pBase['user']);
        }

        // custom profiles inherit this when copied from real char :(
        if ($pBase['realm'])
        {
            $profile['region']      = [$rData['region'], Lang::profiler('regions', $rData['region'])];
            $profile['battlegroup'] = [Profiler::urlize(CFG_BATTLEGROUP), CFG_BATTLEGROUP];
            $profile['realm']       = [Profiler::urlize($rData['name']), $rData['name']];
        }

        // bookmarks
        if ($_ = DB::Aowow()->selectCol('SELECT accountId FROM ?_account_profiles WHERE profileId = ?d', $pBase['id']))
            $profile['bookmarks'] = $_;

        // arena teams - [size(2|3|5) => DisplayName]; DisplayName gets urlized to use as link
        if ($at = DB::Aowow()->selectCol('SELECT type AS ARRAY_KEY, name FROM ?_profiler_arena_team at JOIN ?_profiler_arena_team_member atm ON atm.arenaTeamId = at.id WHERE atm.profileId = ?d', $pBase['id']))
            $profile['arenateams'] = $at;

        // pets if hunter fields: [name:name, family:petFamily, npc:npcId, displayId:modelId, talents:talentString]
        if ($pets = DB::Aowow()->select('SELECT name, family, npc, displayId, talents FROM ?_profiler_pets WHERE owner = ?d', $pBase['id']))
            $profile['pets'] = $pets;

        // source for custom profiles; profileId => [name, ownerId, iconString(optional)]
        if ($customs = DB::Aowow()->select('SELECT id AS ARRAY_KEY, name, user, icon FROM ?_profiler_profiles WHERE sourceId = ?d AND sourceId <> id {AND (cuFlags & ?d) = 0}', $pBase['id'], User::isInGroup(U_GROUP_STAFF) ? DBSIMPLE_SKIP : PROFILER_CU_DELETED))
        {
            foreach ($customs as $id => $cu)
            {
                if (!$cu['icon'])
                    unset($cu['icon']);

                $profile['customs'][$id] = array_values($cu);
            }
        }


        /* $profile[]
            // CUSTOM
            'auras'             => [],                      // custom list of buffs, debuffs [spellId]

            // UNUSED
            'glyphs'            => [],                      // provided list of already known glyphs (post cataclysm feature)
        */


        $completion = DB::Aowow()->select('SELECT type AS ARRAY_KEY, typeId AS ARRAY_KEY2, cur, max FROM ?_profiler_completion WHERE id = ?d', $pBase['id']);
        foreach ($completion as $type => $data)
        {
            switch ($type)
            {
                case TYPE_FACTION:                          // factionId => amount
                    $profile['reputation'] = array_combine(array_keys($data), array_column($data, 'cur'));
                    break;
                case TYPE_TITLE:
                    foreach ($data as &$d)
                        $d = 1;

                    $profile['titles'] = $data;
                    break;
                case TYPE_QUEST:
                    foreach ($data as &$d)
                        $d = 1;

                    $profile['quests'] = $data;
                    break;
                case TYPE_SPELL:
                    foreach ($data as &$d)
                        $d = 1;

                    $profile['spells'] = $data;
                    break;
                case TYPE_ACHIEVEMENT:
                    $achievements = array_filter($data, function ($x) { return $x['max'] === null; });
                    $statistics   = array_filter($data, function ($x) { return $x['max'] !== null; });

                    // achievements
                    $profile['achievements']      = array_combine(array_keys($achievements), array_column($achievements, 'cur'));
                    $profile['achievementpoints'] = DB::Aowow()->selectCell('SELECT SUM(points) FROM ?_achievement WHERE id IN (?a)', array_keys($achievements));

                    // raid progression
                    $activity = array_filter($statistics, function ($x) { return $x['cur'] > (time() - MONTH); });
                    foreach ($activity as &$r)
                        $r = 1;

                    // ony .. subtract 10-man from 25-man

                    $profile['statistics'] = array_combine(array_keys($statistics), array_column($statistics, 'max'));
                    $profile['activity']   = $activity;
                    break;
                case TYPE_SKILL:
                    foreach ($data as &$d)
                        $d = [$d['cur'], $d['max']];

                    $profile['skills'] = $data;
                    break;
            }
        }

        $buff = '';

        $usedSlots = [];
        if ($this->_get['items'])
        {
            $phItems = new ItemList(array(['id', $this->_get['items']], ['slot', INVTYPE_NON_EQUIP, '!']));
            if (!$phItems->error)
            {
                $data  = $phItems->getListviewData(ITEMINFO_JSON | ITEMINFO_SUBITEMS);
                foreach ($phItems->iterate() as $iId => $__)
                {
                    $sl = $phItems->getField('slot');
                    foreach (Profiler::$slot2InvType as $slot => $invTypes)
                    {
                        if (in_array($sl, $invTypes) && !in_array($slot, $usedSlots))
                        {
                            // get and apply inventory
                            $buff .= 'g_items.add('.$iId.', {name_'.User::$localeString.":'".Util::jsEscape($phItems->getField('name', true))."', quality:".$phItems->getField('quality').", icon:'".$phItems->getField('iconString')."', jsonequip:".Util::toJSON($data[$iId])."});\n";
                            $profile['inventory'][$slot] = [$iId, 0, 0, 0, 0, 0, 0, 0];

                            $usedSlots[] = $slot;
                            break;
                        }
                    }
                }
            }
        }

        if ($items = DB::Aowow()->select('SELECT * FROM ?_profiler_items WHERE id = ?d', $pBase['id']))
        {
            $itemz = new ItemList(array(['id', array_column($items, 'item')], CFG_SQL_LIMIT_NONE));
            if (!$itemz->error)
            {
                $data  = $itemz->getListviewData(ITEMINFO_JSON | ITEMINFO_SUBITEMS);

                foreach ($items as $i)
                {
                    if ($itemz->getEntry($i['item']) && !in_array($i['slot'], $usedSlots))
                    {
                        // get and apply inventory
                        $buff .= 'g_items.add('.$i['item'].', {name_'.User::$localeString.":'".Util::jsEscape($itemz->getField('name', true))."', quality:".$itemz->getField('quality').", icon:'".$itemz->getField('iconString')."', jsonequip:".Util::toJSON($data[$i['item']])."});\n";
                        $profile['inventory'][$i['slot']] = [$i['item'], $i['subItem'], $i['permEnchant'], $i['tempEnchant'], $i['gem1'], $i['gem2'], $i['gem3'], $i['gem4']];
                    }
                }
            }
        }

        if ($buff)
            $buff .= "\n";


        // if ($au = $char->getField('auras'))
        // {
            // $auraz = new SpellList(array(['id', $char->getField('auras')], CFG_SQL_LIMIT_NONE));
            // $dataz = $auraz->getListviewData();
            // $modz  = $auraz->getProfilerMods();

            // // get and apply aura-mods
            // foreach ($dataz as $id => $data)
            // {
                // $mods = [];
                // if (!empty($modz[$id]))
                // {
                    // foreach ($modz[$id] as $k => $v)
                    // {
                        // if (is_array($v))
                            // $mods[] = $v;
                        // else if ($str = @Game::$itemMods[$k])
                            // $mods[$str] = $v;
                    // }
                // }

                // $buff .= 'g_spells.add('.$id.", {id:".$id.", name:'".Util::jsEscape(mb_substr($data['name'], 1))."', icon:'".$data['icon']."', modifier:".Util::toJSON($mods)."});\n";
            // }
            // $buff .= "\n";
        // }


        // load available titles
        Util::loadStaticFile('p-titles-'.$pBase['gender'], $buff, true);

        // add profile to buffer
        $buff .= "\n\n\$WowheadProfiler.registerProfile(".Util::toJSON($profile).");";

        return $buff."\n";
    }

    /*  params
            id: <prId>
            data: <mode>                [string, tabName]
        return
            null
    */
    protected function handlePurge() : void { }             // removes completion data (as uploaded by the wowhead client) Just fail silently if someone triggers this manually

    protected function checkItemList($val) : array
    {
        // expecting item-list
        if (preg_match('/\d+(:\d+)*/', $val))
            return array_map('intVal', explode(':', $val));

        return [];
    }

    protected function checkUser(string $val) : string
    {
        if (User::isValidName($val))
            return $val;

        return '';
    }
}

?>
