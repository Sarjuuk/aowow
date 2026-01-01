<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfileLoadResponse extends TextResponse
{
    protected array  $expectedGET = array(
        'id'    => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdList']  ],
        'items' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkItemList']]
    );

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generate404();
    }

    /*  params
            id: profileId
            items: string       [itemIds.join(':')]
            unnamed: unixtime   [only to force the browser to reload instead of cache]
        return
            lots...
    */
    protected function generate() : void
    {
        // titles, achievements, characterData, talents, pets
        // and some onLoad-hook to .. load it registerProfile($data)
        // everything else goes through data.php .. strangely enough

        if (!$this->assertGET('id'))
        {
            trigger_error('ProfileLoadResponse - profileId empty', E_USER_ERROR);
            return;
        }

        $pBase = DB::Aowow()->selectRow('SELECT pg.`name` AS "guildname", p.* FROM ?_profiler_profiles p LEFT JOIN ?_profiler_guild pg ON pg.`id` = p.`guild` WHERE p.`id` = ?d', $this->_get['id'][0]);
        if (!$pBase)
        {
            trigger_error('ProfileLoadResponse - called with invalid profileId #'.$this->_get['id'][0], E_USER_WARNING);
            return;
        }

        if ($pBase['deleted'] && !User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
            return;


        $rData = [];
        foreach (Profiler::getRealms() as $rId => $rData)
            if ($rId == $pBase['realm'])
                break;

        if ($pBase['realm'] && !$rData)                     // realm doesn't exist or access is restricted
            return;

        $profile = array(
            'id'                => $pBase['id'],
            'source'            => $pBase['id'],
            'level'             => $pBase['level'],
            'classs'            => $pBase['class'],
            'race'              => $pBase['race'],
            'faction'           => ChrRace::tryFrom($pBase['race'])?->getTeam() ?? TEAM_NEUTRAL,
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

        if ($pBase['custom'])
        {
            // this parameter is _really_ strange .. probably still not doing this right
            $profile['source']      = $pBase['realm'] ? $pBase['sourceId'] : 0;

            $profile['sourcename']  = $pBase['sourceName'];
            $profile['description'] = $pBase['description'];
            $profile['user']        = $pBase['user'];
            $profile['username']    = DB::Aowow()->selectCell('SELECT `username` FROM ?_account WHERE `id` = ?d', $pBase['user']);
        }

        // custom profiles inherit this when copied from real char :(
        if ($pBase['realm'])
        {
            $profile['region']      = [$rData['region'], Lang::profiler('regions', $rData['region'])];
            $profile['battlegroup'] = [Profiler::urlize(Cfg::get('BATTLEGROUP')), Cfg::get('BATTLEGROUP')];
            $profile['realm']       = [Profiler::urlize($rData['name'], true), $rData['name']];
        }

        // bookmarks
        if ($_ = DB::Aowow()->selectCol('SELECT `accountId` FROM ?_account_profiles WHERE `profileId` = ?d', $pBase['id']))
            $profile['bookmarks'] = $_;

        // arena teams - [size(2|3|5) => name]; name gets urlized to use as link
        if ($at = DB::Aowow()->selectCol('SELECT `type` AS ARRAY_KEY, `name` FROM ?_profiler_arena_team at JOIN ?_profiler_arena_team_member atm ON atm.`arenaTeamId` = at.`id` WHERE atm.`profileId` = ?d', $pBase['id']))
            $profile['arenateams'] = $at;

        // pets if hunter fields: [name:name, family:petFamily, npc:npcId, displayId:modelId, talents:talentString]
        if ($pets = DB::Aowow()->select('SELECT `name`, `family`, `npc`, `displayId`, CONCAT("$\"", `talents`, "\"") AS "talents" FROM ?_profiler_pets WHERE `owner` = ?d', $pBase['id']))
            $profile['pets'] = $pets;

        // source for custom profiles; profileId => [name, ownerId, iconString(optional)]
        if ($customs = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `name`, `user`, `icon` FROM ?_profiler_profiles WHERE `sourceId` = ?d AND `sourceId` <> `id` {AND `deleted` = ?d}', $pBase['id'], User::isInGroup(U_GROUP_STAFF) ? DBSIMPLE_SKIP : 0))
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


        // questId => [cat1, cat2]
        $profile['quests'] = [];
        if ($quests = DB::Aowow()->selectCol('SELECT `questId` FROM ?_profiler_completion_quests WHERE `id` = ?d', $pBase['id']))
        {
            $qList = new QuestList(array(['id', $quests]));
            if (!$qList->error)
                foreach ($qList->iterate() as $id => $__)
                    $profile['quests'][$id] = [$qList->getField('cat1'), $qList->getField('cat2')];
        }

        // skillId => [value, max]
        $profile['skills'] = DB::Aowow()->select('SELECT `skillId` AS ARRAY_KEY, `value` AS "0", `max` AS "1" FROM ?_profiler_completion_skills WHERE `id` = ?d', $pBase['id']);

        // factionId => amount
        $profile['reputation'] = DB::Aowow()->selectCol('SELECT `factionId` AS ARRAY_KEY, `standing` FROM ?_profiler_completion_reputation WHERE `id` = ?d', $pBase['id']);

        // titleId => 1
        $profile['titles'] = DB::Aowow()->selectCol('SELECT `titleId` AS ARRAY_KEY, 1 FROM ?_profiler_completion_titles WHERE `id` = ?d', $pBase['id']);

        // achievementId => js date object
        $profile['achievements'] = DB::Aowow()->selectCol('SELECT `achievementId` AS ARRAY_KEY, CONCAT("$new Date(", `date` * 1000, ")") FROM ?_profiler_completion_achievements WHERE `id` = ?d', $pBase['id']);

        // just points
        $profile['achievementpoints'] = $profile['achievements'] ? DB::Aowow()->selectCell('SELECT SUM(`points`) FROM ?_achievement WHERE `id` IN (?a)', array_keys($profile['achievements'])) : 0;

        // achievementId => counter
        $profile['statistics'] = DB::Aowow()->selectCol('SELECT `achievementId` AS ARRAY_KEY, `counter` FROM ?_profiler_completion_statistics WHERE `id` = ?d', $pBase['id']);

        // achievementId => 1
        $profile['activity'] = DB::Aowow()->selectCol('SELECT `achievementId` AS ARRAY_KEY, 1 FROM ?_profiler_completion_statistics WHERE `id` = ?d AND `date` > ?d', $pBase['id'], time() - MONTH);

        // spellId => 1
        $profile['spells'] = DB::Aowow()->selectCol('SELECT `spellId` AS ARRAY_KEY, 1 FROM ?_profiler_completion_spells WHERE `id` = ?d', $pBase['id']);


        $gItems = [];

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
                            $gItems[$iId] = array(
                                'name_'.Lang::getLocale()->json() => $phItems->getField('name', true),
                                'quality'                         => $phItems->getField('quality'),
                                'icon'                            => $phItems->getField('iconString'),
                                'jsonequip'                       => $data[$iId]
                            );
                            $profile['inventory'][$slot] = [$iId, 0, 0, 0, 0, 0, 0, 0];

                            $usedSlots[] = $slot;
                            break;
                        }
                    }
                }
            }
        }

        if ($items = DB::Aowow()->select('SELECT * FROM ?_profiler_items WHERE `id` = ?d', $pBase['id']))
        {
            $itemz = new ItemList(array(['id', array_column($items, 'item')]));
            if (!$itemz->error)
            {
                $data = $itemz->getListviewData(ITEMINFO_JSON | ITEMINFO_SUBITEMS);

                foreach ($items as $i)
                {
                    if ($itemz->getEntry($i['item']) && !in_array($i['slot'], $usedSlots))
                    {
                        // get and apply inventory
                        $gItems[$i['item']] = array(
                            'name_'.Lang::getLocale()->json() => $itemz->getField('name', true),
                            'quality'                         => $itemz->getField('quality'),
                            'icon'                            => $itemz->getField('iconString'),
                            'jsonequip'                       => $data[$i['item']]
                        );
                        $profile['inventory'][$i['slot']] = [$i['item'], $i['subItem'], $i['permEnchant'], $i['tempEnchant'], $i['gem1'], $i['gem2'], $i['gem3'], $i['gem4']];
                    }
                }
            }
        }

        $buff = '';
        foreach ($gItems as $id => $item)
            $buff .= 'g_items.add('.$id.', '.Util::toJSON($item, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE).");\n";


        // if ($au = $char->getField('auras'))
        // {
            // $auraz = new SpellList(array(['id', $char->getField('auras')]));
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

                // $buff .= 'g_spells.add('.$id.", {id:".$id.", name:'".Util::jsEscape(mb_substr($data['name'], 1))."', icon:'".$data['icon']."', callback:".Util::toJSON($mods)."});\n";
            // }
            // $buff .= "\n";
        // }


        // load available titles
        Util::loadStaticFile('p-titles-'.$pBase['gender'], $buff, true);

        // add profile to buffer
        $buff .= "\n\n\$WowheadProfiler.registerProfile(".Util::toJSON($profile).");";

        $this->result = $buff."\n";
    }

    protected static function checkItemList(string $val) : array
    {
        // expecting item-list
        if (preg_match('/\d+(:\d+)*/', $val))
            return array_map('intVal', explode(':', $val));

        return [];
    }
}

?>
