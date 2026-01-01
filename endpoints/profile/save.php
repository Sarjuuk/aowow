<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfileSaveResponse extends TextResponse
{
    protected array $expectedGET  = array(
        'id'           => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdList']],
    );
    protected array $expectedPOST = array(
        'name'         => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']    ],
        'level'        => ['filter' => FILTER_VALIDATE_INT                                             ],
        'class'        => ['filter' => FILTER_VALIDATE_INT                                             ],
        'race'         => ['filter' => FILTER_VALIDATE_INT                                             ],
        'gender'       => ['filter' => FILTER_VALIDATE_INT                                             ],
        'nomodel'      => ['filter' => FILTER_VALIDATE_INT                                             ],
        'talenttree1'  => ['filter' => FILTER_VALIDATE_INT                                             ],
        'talenttree2'  => ['filter' => FILTER_VALIDATE_INT                                             ],
        'talenttree3'  => ['filter' => FILTER_VALIDATE_INT                                             ],
        'activespec'   => ['filter' => FILTER_VALIDATE_INT                                             ],
        'talentbuild1' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTalentString']],
        'glyphs1'      => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkGlyphString'] ],
        'talentbuild2' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTalentString']],
        'glyphs2'      => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkGlyphString'] ],
        'icon'         => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']    ],
        'description'  => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextBlob']    ],
        'source'       => ['filter' => FILTER_VALIDATE_INT                                             ],
        'copy'         => ['filter' => FILTER_VALIDATE_INT                                             ],
        'public'       => ['filter' => FILTER_VALIDATE_INT                                             ],
        'gearscore'    => ['filter' => FILTER_VALIDATE_INT                                             ],
        'inv'          => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdList'], 'flags' => FILTER_REQUIRE_ARRAY]
    );

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generate404();
    }

    /*  params (get))
            id: <prId1,0> [0: new profile]
        params (post)
            <various char data> [see below]
        return:
            proileId [onSuccess]
            -1       [onError]
    */
    protected function generate() : void
    {
        $cuProfile = array(
            'user'         => User::$id,
         // 'userName'     => User::$username,
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
            'custom'       => 1,
            'cuFlags'      => $this->_post['public'] ? PROFILER_CU_PUBLISHED : 0
        );

        // remnant of a conflict between wotlk generic icons and cata+ auto-generated, char-based icons (see profile=avatar)
        if (strstr($cuProfile['icon'], 'profile=avatar'))
            $cuProfile['icon'] = '';

        if ($_ = $this->_post['description'])
            $cuProfile['description'] = $_;

        if ($_ = $this->_post['source'])                    // should i also set sourcename?
            $cuProfile['sourceId'] = $_;

        if ($_ = $this->_post['copy'])                      // gets set to source profileId when "save as" is clicked. Whats the difference to 'source' though?
        {
            // get character origin info if possible
            if ($r = DB::Aowow()->selectCell('SELECT `realm` FROM ?_profiler_profiles WHERE `id` = ?d AND `custom` = 0', $_))
                $cuProfile['realm'] = $r;

            $cuProfile['sourceId'] = $_;
        }

        if (!empty($cuProfile['sourceId']))
            $cuProfile['sourceName'] = DB::Aowow()->selectCell('SELECT `name` FROM ?_profiler_profiles WHERE `id` = ?d', $cuProfile['sourceId']);

        $charId = -1;
        if ($id = $this->_get['id'][0])                     // update
        {
            if ($charId = DB::Aowow()->selectCell('SELECT `id` FROM ?_profiler_profiles WHERE `id` = ?d', $id))
                DB::Aowow()->query('UPDATE ?_profiler_profiles SET ?a WHERE `id` = ?d', $cuProfile, $id);
        }
        else                                                // new
        {
            $nProfiles = DB::Aowow()->selectCell('SELECT COUNT(*) FROM ?_profiler_profiles WHERE `user` = ?d AND `deleted` = 0 AND `custom` = 1', User::$id);
            if ($nProfiles < 10 || User::isPremium())
                if ($newId = DB::Aowow()->query('INSERT INTO ?_profiler_profiles (?#) VALUES (?a)', array_keys($cuProfile), array_values($cuProfile)))
                    $charId = $newId;
        }

        // update items
        if ($charId != -1)
        {
            // ok, 'funny' thing: whether an item has en extra prismatic socket is determined contextually
            // either the socket is -1 or it has an itemId on an index where there shouldn't be one
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
                    if (!$itemData)
                    {
                        trigger_error('ProfileSaveResponse::generate - skipping malformed inventory definition for slot #'.$slot.': '.Util::toString($itemData), E_USER_NOTICE);
                        continue;
                    }

                    if ($slot + 1 == array_sum($itemData))  // only slot definition set => empty slot
                    {
                        DB::Aowow()->query('DELETE FROM ?_profiler_items WHERE `id` = ?d AND `slot` = ?d', $charId, $itemData[0]);
                        continue;
                    }

                    // item does not exist
                    if (!$items->getEntry($itemData[1]))
                        continue;

                    // sub-item check
                    if (!$items->getRandEnchantForItem($itemData[1]))
                        $itemData[2] = 0;

                    // item sockets are fubar
                    $nSockets = $items->json[$itemData[1]]['nsockets'] ?? 0;
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

        $this->result = $charId;
    }

    protected static function checkTalentString(string $val) : string
    {
        if (preg_match('/^\d+$/', $val))
            return $val;

        return '';
    }

    protected static function checkGlyphString(string $val) : string
    {
        if (preg_match('/^\d+(:\d+)*$/', $val))
            return $val;

        return '';
    }
}

?>
