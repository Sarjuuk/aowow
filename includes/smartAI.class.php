<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SmartAI
{
    private $jsGlobals  = [];
    private $rawData    = [];
    private $result     = [];
    private $tabs       = [];
    private $itr        = [];

    private $srcType    = 0;
    private $entry      = 0;

    private $rowKey     = '';

    private $miscData   = [];
    private $quotes     = [];
    private $summons    = null;

    /*********************/
    /* Lookups by action */
    /*********************/

    public static function getOwnerOfNPCSummon(int $npcId, int $typeFilter = 0) : array
    {
        if ($npcId <= 0)
            return [];

        $lookup = array(
            SAI_ACTION_SUMMON_CREATURE         => [1 => $npcId],
            SAI_ACTION_MOUNT_TO_ENTRY_OR_MODEL => [1 => $npcId]
        );

        if ($npcGuids = DB::Aowow()->selectCol('SELECT guid FROM ?_spawns WHERE `type` = ?d AND `typeId` = ?d', Type::NPC, $npcId))
            if ($groups = DB::World()->selectCol('SELECT `groupId` FROM spawn_group WHERE `spawnType` = 0 AND `spawnId` IN (?a)', $npcGuids))
                foreach ($groups as $g)
                    $lookup[SAI_ACTION_SPAWN_SPAWNGROUP][1] = $g;

        $result = self::getActionOwner($lookup, $typeFilter);

        // can skip lookups for SAI_ACTION_SUMMON_CREATURE_GROUP as creature_summon_groups already contains summoner info
        if ($sgs = DB::World()->select('SELECT `summonerType` AS "0", `summonerId` AS "1" FROM creature_summon_groups WHERE `entry` = ?d', $npcId))
            foreach ($sgs as [$type, $typeId])
                $result[$type][] = $typeId;

        return $result;
    }

    public static function getOwnerOfObjectSummon(int $objectId, int $typeFilter = 0) : array
    {
        if ($objectId <= 0)
            return [];

        $lookup = array(
            SAI_ACTION_SUMMON_GO => [1 => $objectId]
        );

        if ($objGuids = DB::Aowow()->selectCol('SELECT guid FROM ?_spawns WHERE `type` = ?d AND `typeId` = ?d', Type::OBJECT, $objectId))
            if ($groups = DB::World()->selectCol('SELECT `groupId` FROM spawn_group WHERE `spawnType` = 1 AND `spawnId` IN (?a)', $objGuids))
                foreach ($groups as $g)
                    $lookup[SAI_ACTION_SPAWN_SPAWNGROUP][1] = $g;

        return self::getActionOwner($lookup, $typeFilter);
    }

    public static function getOwnerOfSpellCast(int $spellId, int $typeFilter = 0) : array
    {
        if ($spellId <= 0)
            return [];

        $lookup = array(
            SAI_ACTION_CAST         => [1 => $spellId],
            SAI_ACTION_ADD_AURA     => [1 => $spellId],
            SAI_ACTION_SELF_CAST    => [1 => $spellId],
            SAI_ACTION_CROSS_CAST   => [1 => $spellId],
            SAI_ACTION_INVOKER_CAST => [1 => $spellId]
        );

        return self::getActionOwner($lookup, $typeFilter);
    }

    public static function getOwnerOfSoundPlayed(int $soundId, int $typeFilter = 0) : array
    {
        if ($soundId <= 0)
            return [];

        $lookup = array(
            SAI_ACTION_SOUND => [1 => $soundId]
        );

        return self::getActionOwner($lookup, $typeFilter);
    }

    private static function getActionOwner(array $lookup, int $typeFilter = 0) : array
    {
        $qParts   = [];
        $result   = [];
        $genLimit = $talLimit = [];
        switch ($typeFilter)
        {
            case Type::NPC:
                $genLimit = [SAI_SRC_TYPE_CREATURE, SAI_SRC_TYPE_ACTIONLIST];
                $talLimit = [SAI_SRC_TYPE_CREATURE];
                break;
            case Type::OBJECT:
                $genLimit = [SAI_SRC_TYPE_OBJECT, SAI_SRC_TYPE_ACTIONLIST];
                $talLimit = [SAI_SRC_TYPE_OBJECT];
                break;
            case Type::AREATRIGGER:
                $genLimit = [SAI_SRC_TYPE_AREATRIGGER, SAI_SRC_TYPE_ACTIONLIST];
                $talLimit = [SAI_SRC_TYPE_AREATRIGGER];
                break;
        }

        foreach ($lookup as $action => $params)
        {
            $aq = '(`action_type` = '.(int)$action.' AND (';
            $pq = [];
            foreach ($params as $idx => $p)
                $pq[] = '`action_param'.(int)$idx.'` = '.(int)$p;

            if ($pq)
                $qParts[] = $aq.implode(' OR ', $pq).'))';
        }

        $smartS = DB::World()->select(sprintf('SELECT `source_type` AS "0", `entryOrGUID` AS "1" FROM smart_scripts WHERE (%s){ AND `source_type` IN (?a)}', $qParts ? implode(' OR ', $qParts) : '0'), $genLimit ?: DBSIMPLE_SKIP);

        // filter for TAL shenanigans
        if ($smartTAL = array_filter($smartS, function ($x) {return $x[0] == SAI_SRC_TYPE_ACTIONLIST;}))
        {
            $smartS = array_diff_key($smartS, $smartTAL);

            $q = [];
            foreach ($smartTAL as [, $eog])
            {
                // SAI_ACTION_CALL_TIMED_ACTIONLIST
                $q[] = '`action_type` = '.SAI_ACTION_CALL_TIMED_ACTIONLIST.' AND `action_param1` = '.$eog;

                // SAI_ACTION_CALL_RANDOM_TIMED_ACTIONLIST
                $q[] = '`action_type` = '.SAI_ACTION_CALL_RANDOM_TIMED_ACTIONLIST.' AND (`action_param1` = '.$eog.' OR `action_param2` = '.$eog.' OR `action_param3` = '.$eog.' OR `action_param4` = '.$eog.' OR `action_param5` = '.$eog.')';

                // SAI_ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST
                $q[] = '`action_type` = '.SAI_ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST.' AND `action_param1` <= '.$eog.' AND `action_param2` >= '.$eog;
            }

            if ($_ = DB::World()->select(sprintf('SELECT `source_type` AS "0", `entryOrGUID` AS "1" FROM smart_scripts WHERE ((%s)){ AND `source_type` IN (?a)}', $q ? implode(') OR (', $q) : '0'), $talLimit ?: DBSIMPLE_SKIP))
                $smartS = array_merge($smartS, $_);
        }

        // filter guids for entries
        if ($smartG = array_filter($smartS, function ($x) {return $x[1] < 0;}))
        {
            $smartS = array_diff_key($smartS, $smartG);

            $q = [];
            foreach ($smartG as [$st, $eog])
            {
                if ($st == SAI_SRC_TYPE_CREATURE)
                    $q[] = '`type` = '.Type::NPC.' AND `guid` = '.-$eog;
                else if ($st == SAI_SRC_TYPE_OBJECT)
                    $q[] = '`type` = '.Type::OBJECT.' AND `guid` = '.-$eog;
            }

            if ($q)
            {
                $owner = DB::Aowow()->select(sprintf('SELECT `type`, `typeId` FROM ?_spawns WHERE (%s)', implode(') OR (', $q)));
                foreach ($owner as $o)
                    $result[$o['type']][] = $o['typeId'];
            }
        }

        foreach ($smartS as [$st, $eog])
        {
            if ($st == SAI_SRC_TYPE_CREATURE)
                $result[Type::NPC][] = $eog;
            else if ($st == SAI_SRC_TYPE_OBJECT)
                $result[Type::OBJECT][] = $eog;
            else if ($st == SAI_SRC_TYPE_AREATRIGGER)
                $result[Type::AREATRIGGER][] = $eog;
        }

        return $result;
    }


    /********************/
    /* Lookups by owner */
    /********************/

    public static function getNPCSummonsForOwner(int $entry, int $srcType = SAI_SRC_TYPE_CREATURE) : array
    {
        // action => paramIdx with npcIds/spawnGoupIds
        $lookup = array(
            SAI_ACTION_SUMMON_CREATURE         => [1],
            SAI_ACTION_MOUNT_TO_ENTRY_OR_MODEL => [1],
            SAI_ACTION_SPAWN_SPAWNGROUP        => [1]
        );

        $result = self::getOwnerAction($srcType, $entry, $lookup);

        // can skip lookups for SAI_ACTION_SUMMON_CREATURE_GROUP as creature_summon_groups already contains summoner info
        if ($srcType == SAI_SRC_TYPE_CREATURE || $srcType == SAI_SRC_TYPE_OBJECT)
        {
            $st = $srcType == SAI_SRC_TYPE_CREATURE ? 0 : 1;// 0:SUMMONER_TYPE_CREATURE; 1:SUMMONER_TYPE_GAMEOBJECT
            if ($csg = DB::World()->selectCol('SELECT `entry` FROM creature_summon_groups WHERE `summonerType` = ?d AND `summonerId` = ?d', $st, $entry))
                $result = array_merge($result, $csg);
        }

        if (!empty($moreInfo[SAI_ACTION_SPAWN_SPAWNGROUP]))
        {
            $grp = $moreInfo[SAI_ACTION_SPAWN_SPAWNGROUP];
            if ($sgs = DB::World()->selectCol('SELECT `spawnId` FROM spawn_group WHERE `spawnType` = ?d AND `groupId` IN (?a)', 0 /*0:SUMMONER_TYPE_CREATURE*/, $grp))
                if ($ids = DB::Aowow()->selectCol('SELECT DISTINCT `typeId` FROM ?_spawns WHERE `type` = ?d AND `guid` IN (?a)', Type::NPC, $sgs))
                    $result = array_merge($result, $ids);
        }

        return $result;
    }

    public static function getObjectSummonsForOwner(int $entry, int $srcType = SAI_SRC_TYPE_CREATURE) : array
    {
        // action => paramIdx with gobIds/spawnGoupIds
        $lookup = array(
            SAI_ACTION_SUMMON_GO        => [1],
            SAI_ACTION_SPAWN_SPAWNGROUP => [1]
        );

        $result = self::getOwnerAction($srcType, $entry, $lookup, $moreInfo);

        if (!empty($moreInfo[SAI_ACTION_SPAWN_SPAWNGROUP]))
        {
            $grp = $moreInfo[SAI_ACTION_SPAWN_SPAWNGROUP];
            if ($sgs = DB::World()->selectCol('SELECT `spawnId` FROM spawn_group WHERE `spawnType` = ?d AND `groupId` IN (?a)', 1 /*1:SUMMONER_TYPE_GAMEOBJECT*/, $grp))
                if ($ids = DB::Aowow()->selectCol('SELECT DISTINCT `typeId` FROM ?_spawns WHERE `type` = ?d AND `guid` IN (?a)', Type::OBJECT, $sgs))
                    $result = array_merge($result, $ids);
        }

        return $result;
    }

    public static function getSpellCastsForOwner(int $entry, int $srcType = SAI_SRC_TYPE_CREATURE) : array
    {
        // action => paramIdx with spellIds
        $lookup = array(
            SAI_SRC_TYPE_CREATURE   => [1],
            SAI_ACTION_CAST         => [1],
            SAI_ACTION_ADD_AURA     => [1],
            SAI_ACTION_INVOKER_CAST => [1],
            SAI_ACTION_CROSS_CAST   => [1]
        );

        return self::getOwnerAction($srcType, $entry, $lookup);
    }

    public static function getSoundsPlayedForOwner(int $entry, int $srcType = SAI_SRC_TYPE_CREATURE) : array
    {
        // action => paramIdx with soundIds
        $lookup = [SAI_ACTION_SOUND => [1]];

        return self::getOwnerAction($srcType, $entry, $lookup);
    }

    private static function getOwnerAction(int $sourceType, int $entry, array $lookup, ?array &$moreInfo = []) : array
    {
        if ($entry < 0)                                     // please not individual entities :(
            return [];

        $smartScripts = DB::World()->select('SELECT action_type, action_param1, action_param2, action_param3, action_param4, action_param5, action_param6 FROM smart_scripts WHERE source_type = ?d AND action_type IN (?a) AND entryOrGUID = ?d', $sourceType, array_merge(array_keys($lookup), SAI_ACTION_ALL_TIMED_ACTION_LISTS), $entry);
        $smartResults = [];
        $smartTALs    = [];
        foreach ($smartScripts as $s)
        {
            if ($s['action_type'] == SAI_ACTION_SPAWN_SPAWNGROUP)
                $moreInfo[SAI_ACTION_SPAWN_SPAWNGROUP][] = $s['action_param1'];
            else if (in_array($s['action_type'], array_keys($lookup)))
            {
                foreach ($lookup[$s['action_type']] as $p)
                    $smartResults[] = $s['action_param'.$p];
            }
            else if ($s['action_type'] == SAI_ACTION_CALL_TIMED_ACTIONLIST)
                $smartTALs[] = $s['action_param1'];
            else if ($s['action_type'] == SAI_ACTION_CALL_RANDOM_TIMED_ACTIONLIST)
            {
                for ($i = 1; $i < 7; $i++)
                    if ($s['action_param'.$i])
                        $smartTALs[] = $s['action_param'.$i];
            }
            else if ($s['action_type'] == SAI_ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST)
            {
                for ($i = $s['action_param1']; $i <= $s['action_param2']; $i++)
                    $smartTALs[] = $i;
            }
        }

        if ($smartTALs)
        {
            if ($TALActList = DB::World()->select('SELECT action_type, action_param1, action_param2, action_param3, action_param4, action_param5, action_param6 FROM smart_scripts WHERE source_type = ?d AND action_type IN (?a) AND entryOrGUID IN (?a)', SAI_SRC_TYPE_ACTIONLIST, array_keys($lookup), $smartTALs))
            {
                foreach ($TALActList as $e)
                {
                    foreach ($lookup[$e['action_type']] as $i)
                    {
                        if ($e['action_type'] == SAI_ACTION_SPAWN_SPAWNGROUP)
                            $moreInfo[SAI_ACTION_SPAWN_SPAWNGROUP][] = $e['action_param'.$i];
                        else
                            $smartResults[] = $e['action_param'.$i];
                    }
                }
            }
        }

        return $smartResults;
    }


    /******************************/
    /* Structured Lisview Display */
    /******************************/

    public function __construct(int $srcType, int $entry, array $miscData = [])
    {
        $this->srcType  = $srcType;
        $this->entry    = $entry;
        $this->miscData = $miscData;

        $raw = DB::World()->select('SELECT id, link, event_type, event_phase_mask, event_chance, event_flags, event_param1, event_param2, event_param3, event_param4, event_param5, action_type, action_param1, action_param2, action_param3, action_param4, action_param5, action_param6, target_type, target_param1, target_param2, target_param3, target_param4, target_x, target_y, target_z, target_o FROM smart_scripts WHERE entryorguid = ?d AND source_type = ?d ORDER BY id ASC', $this->entry, $this->srcType);
        foreach ($raw as $r)
        {
            $this->rawData[$r['id']] = array(
                'id'     => $r['id'],
                'link'   => $r['link'],
                'event'  => array(
                    'type'   => $r['event_type'],
                    'phases' => Util::mask2bits($r['event_phase_mask'], 1) ?: [0],
                    'chance' => $r['event_chance'],
                    'flags'  => $r['event_flags'],
                    'param'  => [$r['event_param1'], $r['event_param2'], $r['event_param3'], $r['event_param4'], $r['event_param5']]
                ),
                'action' => array(
                    'type'  => $r['action_type'],
                    'param' => [$r['action_param1'], $r['action_param2'], $r['action_param3'], $r['action_param4'], $r['action_param5'], $r['action_param6']]
                ),
                'target' => array(
                    'type'  => $r['target_type'],
                    'param' => [$r['target_param1'], $r['target_param2'], $r['target_param3'], $r['target_param4']],
                    'pos'   => [$r['target_x'], $r['target_y'], $r['target_z'], $r['target_o']]
                )
            );
        }
    }

    public function prepare() : bool
    {
        if (!$this->rawData)
            return false;

        if ($this->result)
            return true;

        $hidePhase  =
        $hideChance = true;

        foreach ($this->iterate() as $_)
        {
            $this->rowKey = Util::createHash(8);

            if ($ts = $this->getTalkSource())
                $this->getQuotes($ts);

            [$evtBody, $evtFooter] = $this->event();
            [$actBody, $actFooter] = $this->action();

            if ($ef = $this->eventFlags())
            {
                if ($evtFooter)
                    $evtFooter = $ef.', '.$evtFooter;
                else
                    $evtFooter = $ef;
            }

            if ($this->itr['event']['phases'] != [0])
                $hidePhase = false;

            if ($this->itr['event']['chance'] != 100)
                $hideChance = false;

            $this->result[] = array(
                $this->itr['id'],
                implode(', ', $this->itr['event']['phases']),
                $evtBody.($evtFooter ? '[div float=right margin=0px][i][small class=q0]'.$evtFooter.'[/small][/i][/div]' : null),
                $this->itr['event']['chance'].'%',
                $actBody.($actFooter ? '[div float=right margin=0px clear=both][i][small class=q0]'.$actFooter.'[/small][/i][/div]' : null)
            );
        }

        $th = array(
            '#'      => 16,
            'Phase'  => 32,
            'Event'  => 350,
            'Chance' => 24,
            'Action' => 0
        );

        if ($hidePhase)
        {
            unset($th['Phase']);
            foreach ($this->result as &$r)
                unset($r[1]);
        }
        unset($r);

        if ($hideChance)
        {
            unset($th['Chance']);
            foreach ($this->result as &$r)
                unset($r[3]);
        }
        unset($r);

        $tbl = '[tr]';
        foreach ($th as $n => $w)
            $tbl .= '[td header '.($w ? 'width='.$w.'px' : null).']'.$n.'[/td]';
        $tbl .= '[/tr]';

        foreach ($this->result as $r)
            $tbl .= '[tr][td]'.implode('[/td][td]', $r).'[/td][/tr]';

        if ($this->srcType == SAI_SRC_TYPE_ACTIONLIST)
            $this->tabs[$this->entry] = $tbl;
        else
            $this->tabs[0] = $tbl;

        return true;
    }

    public function getMarkdown() : string
    {
        # id | event (footer phase) | chance | action + target

        if (!$this->rawData)
            return '';

        $return = '[style]#text-generic .grid { clear:left; } #text-generic .tabbed-contents { padding:0px; clear:left; }[/style][pad][h3][toggler id=sai]SmartAI'.(!empty($this->miscData['title']) ? $this->miscData['title'] : null).'[/toggler][/h3][div id=sai clear=left]%s[/div]';
        if (count($this->tabs) > 1)
        {
            $wrapper = '[tabs name=sai width=942px]%s[/tabs]';
            $tabs    = '';
            foreach ($this->tabs as $guid => $data)
            {
                $buff = '[tab name=\"'.($guid ? 'ActionList #'.$guid : 'Main').'\"][table class=grid width=940px]'.$data.'[/table][/tab]';
                if ($guid)
                    $tabs .= $buff;
                else
                    $tabs = $buff . $tabs;
            }

            return sprintf($return, sprintf($wrapper, $tabs));
        }
        else
            return sprintf($return, '[table class=grid width=940px]'.$this->tabs[0].'[/table]');
    }

    public function getJSGlobals() : array
    {
        return $this->jsGlobals;
    }

    public function getTabs() : array
    {
        return $this->tabs;
    }


    private function &iterate() : iterable
    {
        reset($this->rawData);

        foreach ($this->rawData as $k => $__)
        {
            $this->itr = &$this->rawData[$k];

            yield $this->itr;
        }
    }

    private function numRange(string $f, int $n, bool $isTime = false) : string
    {
        if (!isset($this->itr[$f]['param'][$n]) || !isset($this->itr[$f]['param'][$n + 1]))
            return 0;

        if (empty($this->itr[$f]['param'][$n]) && empty($this->itr[$f]['param'][$n + 1]))
            return 0;

        $str = $isTime ? Util::formatTime($this->itr[$f]['param'][$n], true) : $this->itr[$f]['param'][$n];
        if ($this->itr[$f]['param'][$n + 1] > $this->itr[$f]['param'][$n])
            $str .= ' &ndash; '.($isTime ? Util::formatTime($this->itr[$f]['param'][$n + 1], true) : $this->itr[$f]['param'][$n + 1]);

        return $str;
    }

    private function getQuotes(int $creatureId) : void
    {
        if (isset($this->quotes[$creatureId]))
            return;

        [$quotes, , ] = Game::getQuotesForCreature($creatureId);

        $this->quotes[$creatureId] = $quotes;

        if (!empty($this->quotes[$creatureId]))
            $this->quotes[$creatureId]['src'] = CreatureList::getName($creatureId);
    }

    private function getTalkSource(bool &$emptySource = false) : int
    {
        if ($this->itr['action']['type'] != SAI_ACTION_TALK &&
            $this->itr['action']['type'] != SAI_ACTION_SIMPLE_TALK)
            return 0;

        switch ($this->itr['target']['type'])
        {
            case SAI_TARGET_CREATURE_GUID:
                if ($id = DB::World()->selectCell('SELECT id FROM creature WHERE guid = ?d', $this->itr['target']['param'][0]))
                    return $id;

                break;
            case SAI_TARGET_CREATURE_RANGE:
            case SAI_TARGET_CREATURE_DISTANCE:
            case SAI_TARGET_CLOSEST_CREATURE:
                return $this->itr['target']['param'][0];
            case SAI_TARGET_CLOSEST_PLAYER:
                $emptySource = true;
            case SAI_TARGET_SELF:
            case SAI_TARGET_ACTION_INVOKER:
            case SAI_TARGET_CLOSEST_FRIENDLY:               // unsure about this
            default:
                return empty($this->miscData['baseEntry']) ? $this->entry : $this->miscData['baseEntry'];
        }

        return 0;
    }

    private function eventFlags() : string
    {
        $ef = [];
        for ($i = 1; $i <= SAI_EVENT_FLAG_WHILE_CHARMED; $i <<= 1)
            if ($this->itr['event']['flags'] & $i)
                if ($x = Lang::smartAI('eventFlags', $i))
                    $ef[] = $x;

        return Lang::concat($ef);
    }

    private function castFlags(string $f, int $n) : string
    {
        $cf = [];
        for ($i = 1; $i <= SAI_CAST_FLAG_COMBAT_MOVE; $i <<= 1)
            if ($this->itr[$f]['param'][$n] & $i)
                if ($x = Lang::smartAI('castFlags', $i))
                    $cf[]  = $x;

        return Lang::concat($cf);
    }

    private function npcFlags(string $f, int $n) : string
    {
        $nf = [];
        for ($i = 1; $i <= NPC_FLAG_MAILBOX; $i <<= 1)
            if ($this->itr[$f]['param'][$n] & $i)
                if ($x = Lang::npc('npcFlags', $i))
                    $nf[] = $x;

        return Lang::concat($nf ?: [Lang::smartAI('empty')]);
    }

    private function unitFlags(string $f, int $n) : string
    {
        $uf = [];
        for ($i = 1; $i <= UNIT_FLAG_UNK_31; $i <<= 1)
            if ($this->itr[$f]['param'][$n] & $i)
                if ($x = Lang::unit('flags', $i))
                    $uf[] = $x;

        return Lang::concat($uf ?: [Lang::smartAI('empty')]);
   }

    private function unitFlags2(string $f, int $n) : string
    {
        $uf = [];
        for ($i = 1; $i <= UNIT_FLAG2_ALLOW_CHEAT_SPELLS; $i <<= 1)
            if ($this->itr[$f]['param'][$n] & $i)
                if ($x = Lang::unit('flags2', $i))
                    $uf[] = $x;

            return Lang::concat($uf ?: [Lang::smartAI('empty')]);
    }

    private function unitFieldBytes1(int $idx, int $val) : string
    {
        if ($idx === 0)
        {
            if ($standState = Lang::unit('bytes1', $idx, $val))
                return $standState;
            else
                return Lang::unit('bytes1', 'valueUNK', [$val, $idx]);
        }
        else if ($idx === 2 || $idx == 3)
        {
            $buff = [];
            for ($i = 1; $i <= 0x10; $i <<= 1)
                if ($val & $i)
                    if ($x = Lang::unit('bytes1', $idx, $val))
                        $buff[] = $x;

            return $buff ? Lang::concat($buff) : Lang::unit('bytes1', 'valueUNK', [$val, $idx]);
        }
        else
            return Lang::unit('bytes1', 'idxUNK', [$idx]);
    }

    private function summonType(int $summonType) : string
    {
        if ($summonType = Lang::smartAI('summonTypes', $summonType))
            return $summonType;
        else
            return Lang::smartAI('summonType', 'summonTypeUNK', [$summonType]);
    }

    private function dynFlags(string $f, int $n) : string
    {
        $df = [];
        for ($i = 1; $i <= UNIT_DYNFLAG_TAPPED_BY_ALL_THREAT_LIST; $i <<= 1)
            if ($this->itr[$f]['param'][$n] & $i)
                if ($x = Lang::unit('dynFlags', $i))
                    $df[] = $x;

        return Lang::concat($df ?: [Lang::smartAI('empty')]);
    }

    private function goFlags(string $f, int $n) : string
    {
        $gf = [];
        for ($i = 1; $i <= GO_FLAG_DESTROYED; $i <<= 1)
            if ($this->itr[$f]['param'][$n] & $i)
                if ($x = Lang::gameObject('goFlags', $i))
                    $gf[] = $x;

        return Lang::concat($gf ?: [Lang::smartAI('empty')]);
    }

    private function spawnFlags(string $f, int $n) : string
    {
        $sf = [];
        for ($i = 1; $i <= SAI_SPAWN_FLAG_NOSAVE_RESPAWN; $i <<= 1)
            if ($this->itr[$f]['param'][$n] & $i)
                if ($x = Lang::smartAI('spawnFlags', $i))
                    $sf[] = $x;

        return Lang::concat($sf ?: [Lang::smartAI('empty')]);
    }

    private function aiTemplate(int $aiNum) : string
    {
        if ($standState = Lang::smartAI('aiTpl', $aiNum))
            return $standState;
        else
            return Lang::smartAI('aiTplUNK', [$aiNum]);
    }

    private function reactState(int $stateNum) : string
    {
        if ($reactState = Lang::smartAI('reactStates', $stateNum))
            return $reactState;
        else
            return Lang::smartAI('reactStateUNK', [$stateNum]);
    }

    private function target(array $override = []) : string
    {
        $target  = '';

        $t = $override ?: $this->itr['target'];

        $getDist = function ($min, $max) { return ($min && $max) ? min($min, $max).' &ndash; '.max($min, $max) : max($min, $max); };
        $tooltip = '[tooltip name=t-'.$this->rowKey.']'.Lang::smartAI('targetTT', array_merge([$t['type']], $t['param'], $t['pos'])).'[/tooltip][span class=tip tooltip=t-'.$this->rowKey.']%s[/span]';

        // additional parameters
        $t['param'] = array_pad($t['param'], 15, '');

        switch ($t['type'])
        {
            // direct param use
            case SAI_TARGET_SELF:                           // 1
            case SAI_TARGET_VICTIM:                         // 2
            case SAI_TARGET_HOSTILE_SECOND_AGGRO:           // 3
            case SAI_TARGET_HOSTILE_LAST_AGGRO:             // 4
            case SAI_TARGET_HOSTILE_RANDOM:                 // 5
            case SAI_TARGET_HOSTILE_RANDOM_NOT_TOP:         // 6
            case SAI_TARGET_ACTION_INVOKER:                 // 7
            case SAI_TARGET_POSITION:                       // 8
            case SAI_TARGET_STORED:                         // 12
            case SAI_TARGET_INVOKER_PARTY:                  // 16
            case SAI_TARGET_CLOSEST_PLAYER:                 // 21
            case SAI_TARGET_ACTION_INVOKER_VEHICLE:         // 22
            case SAI_TARGET_OWNER_OR_SUMMONER:              // 23
            case SAI_TARGET_THREAT_LIST:                    // 24
            case SAI_TARGET_CLOSEST_ENEMY:                  // 25
            case SAI_TARGET_CLOSEST_FRIENDLY:               // 26
            case SAI_TARGET_LOOT_RECIPIENTS:                // 27
            case SAI_TARGET_FARTHEST:                       // 28
                break;
            case SAI_TARGET_VEHICLE_PASSENGER:              // 29
                if ($t['param'][0])
                    $t['param'][10] = Lang::concat(Util::mask2bits($t['param'][0]));
                break;
            // distance
            case SAI_TARGET_PLAYER_RANGE:                   // 17
                $t['param'][10] = $getDist($t['param'][0], $t['param'][1]);
                break;
            case SAI_TARGET_PLAYER_DISTANCE:                // 18
                $t['param'][10] = $getDist(0, $t['param'][0]);
                break;
            // creature link
            case SAI_TARGET_CREATURE_RANGE:                 // 9
                if ($t['param'][0])
                    $this->jsGlobals[Type::NPC][] = $t['param'][0];

                $t['param'][10] = $getDist($t['param'][1], $t['param'][2]);
                break;
            case SAI_TARGET_CREATURE_GUID:                  // 10
                if ($t['param'][10] = DB::World()->selectCell('SELECT id FROM creature WHERE guid = ?d', $t['param'][0]))
                    $this->jsGlobals[Type::NPC][] = $t['param'][10];
                else
                    trigger_error('SmartAI::resloveTarget - creature with guid '.$t['param'][0].' not in DB');
                break;
            case SAI_TARGET_CREATURE_DISTANCE:              // 11
            case SAI_TARGET_CLOSEST_CREATURE:               // 19
                $t['param'][10] = $getDist(0, $t['param'][1]);

                if ($t['param'][0])
                    $this->jsGlobals[Type::NPC][] = $t['param'][0];
                break;
            // gameobject link
            case SAI_TARGET_GAMEOBJECT_GUID:                // 14
                if ($t['param'][10] = DB::World()->selectCell('SELECT id FROM gameobject WHERE guid = ?d', $t['param'][0]))
                    $this->jsGlobals[Type::OBJECT][] = $t['param'][10];
                else
                    trigger_error('SmartAI::resloveTarget - gameobject with guid '.$t['param'][0].' not in DB');
                break;
            case SAI_TARGET_GAMEOBJECT_RANGE:               // 13
                $t['param'][10] = $getDist($t['param'][1], $t['param'][2]);

                if ($t['param'][0])
                    $this->jsGlobals[Type::OBJECT][] = $t['param'][0];
                break;
            case SAI_TARGET_GAMEOBJECT_DISTANCE:            // 15
            case SAI_TARGET_CLOSEST_GAMEOBJECT:             // 20
            case SAI_TARGET_CLOSEST_UNSPAWNED_GO:           // 30
                $t['param'][10] = $getDist(0, $t['param'][1]);

                if ($t['param'][0])
                    $this->jsGlobals[Type::OBJECT][] = $t['param'][0];
                break;
            // error
            default:
                $target = Lang::smartAI('targetUNK', [$t['type']]);
        }

        $target = $target ?: Lang::smartAI('targets', $t['type'], $t['param']);

        // resolve conditionals
        $target = preg_replace_callback('/\(([^\)]*?)\)\?([^:]*):([^;]*);/i', function ($m) { return $m[1] ? $m[2] : $m[3]; }, $target);

        // wrap in tooltip (suspend action-tooltip)
        return '[/span]'.sprintf($tooltip, $target).'[span tooltip=a-'.$this->rowKey.']';
    }

    private function event() : array
    {
        $body   =
        $footer = '';

        $e = &$this->itr['event'];

        $tooltip = '[tooltip name=e-'.$this->rowKey.']'.Lang::smartAI('eventTT', array_merge([$e['type'], $e['phases'], $e['chance'], $e['flags']], $e['param'])).'[/tooltip][span tooltip=e-'.$this->rowKey.']%s[/span]';

        // additional parameters
        $e['param'] = array_pad($e['param'], 15, '');

        switch ($e['type'])
        {
            // simple
            case SAI_EVENT_AGGRO:                           // 4   -  On Creature Aggro
            case SAI_EVENT_DEATH:                           // 6   -  On Creature Death
            case SAI_EVENT_EVADE:                           // 7   -  On Creature Evade Attack
            case SAI_EVENT_RESPAWN:                         // 11  -  On Creature/Gameobject Respawn
            case SAI_EVENT_REACHED_HOME:                    // 21  -  On Creature Reached Home
            case SAI_EVENT_RESET:                           // 25  -  After Combat, On Respawn or Spawn
            case SAI_EVENT_CHARMED:                         // 29  -  On Creature Charmed
            case SAI_EVENT_CHARMED_TARGET:                  // 30  -  On Target Charmed
            case SAI_EVENT_MOVEMENTINFORM:                  // 34  -  WAYPOINT_MOTION_TYPE = 2,  POINT_MOTION_TYPE = 8
            case SAI_EVENT_CORPSE_REMOVED:                  // 36  -  On Creature Corpse Removed
            case SAI_EVENT_AI_INIT:                         // 37  -
            case SAI_EVENT_WAYPOINT_START:                  // 39  -  On Creature Waypoint ID Started
            case SAI_EVENT_WAYPOINT_REACHED:                // 40  -  On Creature Waypoint ID Reached
            case SAI_EVENT_AREATRIGGER_ONTRIGGER:           // 46  -
            case SAI_EVENT_JUST_SUMMONED:                   // 54  -  On Creature Just spawned
            case SAI_EVENT_WAYPOINT_PAUSED:                 // 55  -  On Creature Paused at Waypoint ID
            case SAI_EVENT_WAYPOINT_RESUMED:                // 56  -  On Creature Resumed after Waypoint ID
            case SAI_EVENT_WAYPOINT_STOPPED:                // 57  -  On Creature Stopped On Waypoint ID
            case SAI_EVENT_WAYPOINT_ENDED:                  // 58  -  On Creature Waypoint Path Ended
            case SAI_EVENT_TIMED_EVENT_TRIGGERED:           // 59  -
            case SAI_EVENT_JUST_CREATED:                    // 63  -
            case SAI_EVENT_FOLLOW_COMPLETED:                // 65  -
            case SAI_EVENT_GO_STATE_CHANGED:                // 70  -
            case SAI_EVENT_GO_EVENT_INFORM:                 // 71  -
            case SAI_EVENT_ACTION_DONE:                     // 72  -
            case SAI_EVENT_ON_SPELLCLICK:                   // 73  -
            case SAI_EVENT_COUNTER_SET:                     // 77  -  If the value of specified counterID is equal to a specified value
                break;
            // num range [+ time footer]
            case SAI_EVENT_HEALTH_PCT:                      // 2   -  Health Percentage
            case SAI_EVENT_MANA_PCT:                        // 3   -  Mana Percentage
            case SAI_EVENT_RANGE:                           // 9   -  On Target In Range
            case SAI_EVENT_TARGET_HEALTH_PCT:               // 12  -  On Target Health Percentage
            case SAI_EVENT_TARGET_MANA_PCT:                 // 18  -  On Target Mana Percentage
            case SAI_EVENT_DAMAGED:                         // 32  -  On Creature Damaged
            case SAI_EVENT_DAMAGED_TARGET:                  // 33  -  On Target Damaged
            case SAI_EVENT_RECEIVE_HEAL:                    // 53  -  On Creature Received Healing
            case SAI_EVENT_FRIENDLY_HEALTH_PCT:             // 74  -
                $e['param'][10] = $this->numRange('event', 0);
                // do not break;
            case SAI_EVENT_OOC_LOS:                         // 10  -  On Target In Distance Out of Combat
            case SAI_EVENT_FRIENDLY_HEALTH:                 // 14  -  On Friendly Health Deficit
            case SAI_EVENT_FRIENDLY_MISSING_BUFF:           // 16  -  On Friendly Lost Buff
            case SAI_EVENT_IC_LOS:                          // 26  -  On Target In Distance In Combat
            case SAI_EVENT_DATA_SET:                        // 38  -  On Creature/Gameobject Data Set, Can be used with SMART_ACTION_SET_DATA
                if ($time = $this->numRange('event', 2, true))
                    $footer = $time;
                break;
            // SAI updates
            case SAI_EVENT_UPDATE_IC:                       // 0   -  In combat.
            case SAI_EVENT_UPDATE_OOC:                      // 1   -  Out of combat.
                if ($this->srcType == SAI_SRC_TYPE_ACTIONLIST)
                    $e['param'][11] = 1;
                // do not break;
            case SAI_EVENT_UPDATE:                          // 60  -
                $e['param'][10] = $this->numRange('event', 0, true);
                if ($time = $this->numRange('event', 2, true))
                    $footer = $time;
                break;
            case SAI_EVENT_GOSSIP_HELLO:                    // 64  -  On Right-Click Creature/Gameobject that have gossip enabled.
                if ($this->srcType == SAI_SRC_TYPE_OBJECT)
                    $footer = array(
                        $e['param'][0] == 1,
                        $e['param'][0] == 2,
                    );
                break;
            case SAI_EVENT_KILL:                            // 5   -  On Creature Kill
                if ($time = $this->numRange('event', 0, true))
                    $footer = $time;

                if ($e['param'][3] && !$e['param'][2])
                    $this->jsGlobals[Type::NPC][] = $e['param'][3];
                break;
            case SAI_EVENT_SPELLHIT:                        // 8   -  On Creature/Gameobject Spell Hit
            case SAI_EVENT_HAS_AURA:                        // 23  -  On Creature Has Aura
            case SAI_EVENT_TARGET_BUFFED:                   // 24  -  On Target Buffed With Spell
            case SAI_EVENT_SPELLHIT_TARGET:                 // 31  -  On Target Spell Hit
                if ($time = $this->numRange('event', 2, true))
                    $footer = $time;

                if ($e['param'][1])
                    $e['param'][10] = Lang::getMagicSchools($e['param'][1]);

                if ($e['param'][0])
                    $this->jsGlobals[Type::SPELL][] = $e['param'][0];
                break;
            case SAI_EVENT_VICTIM_CASTING:                  // 13  -  On Target Casting Spell
                if ($e['param'][2])
                    $this->jsGlobals[Type::SPELL][$e['param'][2]];
                // do not break;
            case SAI_EVENT_PASSENGER_BOARDED:               // 27  -
            case SAI_EVENT_PASSENGER_REMOVED:               // 28  -
            case SAI_EVENT_IS_BEHIND_TARGET:                // 67  -  On Creature is behind target.
                if ($time = $this->numRange('event', 0, true))
                    $footer = $time;
                break;
            case SAI_EVENT_SUMMONED_UNIT:                   // 17  -  On Creature/Gameobject Summoned Unit
            case SAI_EVENT_SUMMONED_UNIT_DIES:              // 82  -  On Summoned Unit Dies
                if ($e['param'][0])
                    $this->jsGlobals[Type::NPC][] = $e['param'][0];
                // do not break;
            case SAI_EVENT_FRIENDLY_IS_CC:                  // 15  -
            case SAI_EVENT_SUMMON_DESPAWNED:                // 35  -  On Summoned Unit Despawned
                if ($time = $this->numRange('event', 1, true))
                    $footer = $time;
                break;
            case SAI_EVENT_ACCEPTED_QUEST:                  // 19  -  On Target Accepted Quest
            case SAI_EVENT_REWARD_QUEST:                    // 20  -  On Target Rewarded Quest
                if ($e['param'][0])
                    $this->jsGlobals[Type::QUEST][] = $e['param'][0];
                if ($time = $this->numRange('event', 1, true))
                    $footer = $time;
                break;
            case SAI_EVENT_RECEIVE_EMOTE:                   // 22  -  On Receive Player Emote.
                $this->jsGlobals[Type::EMOTE][] = $e['param'][0];

                if ($time = $this->numRange('event', 1, true))
                    $footer = $time;
                break;
            case SAI_EVENT_TEXT_OVER:                       // 52  -  On TEXT_OVER Event Triggered After SMART_ACTION_TALK
                if ($e['param'][1])
                    $this->jsGlobals[Type::NPC][] = $e['param'][1];
                break;
            case SAI_EVENT_LINK:                            // 61  -  Used to link together multiple events as a chain of events.
                $e['param'][10] = LANG::concat(DB::World()->selectCol('SELECT CONCAT("#[b]", id, "[/b]") FROM smart_scripts WHERE link = ?d AND entryorguid = ?d AND source_type = ?d', $this->itr['id'], $this->entry, $this->srcType), false);
                break;
            case SAI_EVENT_GOSSIP_SELECT:                   // 62  -  On gossip clicked (gossip_menu_option335).
                $gmo = DB::World()->selectRow('SELECT gmo.OptionText AS text_loc0 {, gmol.OptionText AS text_loc?d}
                    FROM gossip_menu_option gmo LEFT JOIN gossip_menu_option_locale gmol ON gmo.MenuID = gmol.MenuID AND gmo.OptionID = gmol.OptionID AND gmol.Locale = ?d
                    WHERE gmo.MenuId = ?d AND gmo.OptionID = ?d',
                    User::$localeId ? Util::$localeStrings[User::$localeId] : DBSIMPLE_SKIP,
                    User::$localeId,
                    $e['param'][0],
                    $e['param'][1]
                );

                if ($gmo)
                    $e['param'][10] = Util::localizedString($gmo, 'text');
                else
                    trigger_error('SmartAI::event - could not find gossip menu option for event #'.$e['type']);
                break;
            case SAI_EVENT_GAME_EVENT_START:                // 68  -  On game_event started.
            case SAI_EVENT_GAME_EVENT_END:                  // 69  -  On game_event ended.
                $this->jsGlobals[Type::WORLDEVENT][] = $e['param'][0];
                break;
            case SAI_EVENT_DISTANCE_CREATURE:               // 75  -  On creature guid OR any instance of creature entry is within distance.
                if ($e['param'][0])
                    $e['param'][10] = DB::World()->selectCell('SELECT id FROM creature WHERE guid = ?d', $e['param'][0]);
                // do not break;
            case SAI_EVENT_DISTANCE_GAMEOBJECT:             // 76  -  On gameobject guid OR any instance of gameobject entry is within distance.
                if ($e['param'][0] && !$e['param'][10])
                    $e['param'][10] = DB::World()->selectCell('SELECT id FROM gameobject WHERE guid = ?d', $e['param'][0]);
                else if ($e['param'][1])
                    $e['param'][10] = $e['param'][1];
                else if (!$e['param'][10])
                    trigger_error('SmartAI::event - entity for event #'.$e['type'].' not defined');

                if ($e['param'][10])
                    $this->jsGlobals[Type::NPC][] = $e['param'][10];

                if ($e['param'][3])
                    $footer = Util::formatTime($e['param'][3], true);
                break;
            case SAI_EVENT_EVENT_PHASE_CHANGE:              // 66  -  On event phase mask set
                $e['param'][10] = Lang::concat(Util::mask2bits($e['param'][0]), false);
                break;
            default:
                $body = '[span class=q10]Unhandled Event[/span] #'.$e['type'];
        }

        $body = $body ?: Lang::smartAI('events', $e['type'], 0, $e['param']);
        if ($footer)
            $footer = Lang::smartAI('events', $e['type'], 1, (array)$footer);

        // resolve conditionals
        $footer = preg_replace_callback('/\(([^\)]*?)\)\?([^:]*):([^;]*);/i', function ($m) { return $m[1] ? $m[2] : $m[3]; }, $footer);
        $body   = preg_replace_callback('/\(([^\)]*?)\)\?([^:]*):([^;]*);/i', function ($m) { return $m[1] ? $m[2] : $m[3]; }, $body);
        $body   = str_replace('#target#', $this->target(), $body);

        // wrap body in tooltip
        return [sprintf($tooltip, $body), $footer];
    }

    private function action() : array
    {
        $body   =
        $footer = '';

        $a = &$this->itr['action'];

        $tooltip = '[tooltip name=a-'.$this->rowKey.']'.Lang::smartAI('actionTT', array_merge([$a['type']], $a['param'])).'[/tooltip][span tooltip=a-'.$this->rowKey.']%s[/span]';

        // init additional parameters
        $a['param'] = array_pad($a['param'], 15, '');

        switch ($a['type'])
        {
            // simple
            case SAI_ACTION_ACTIVATE_GOBJECT:               // 9 -> any target
            case SAI_ACTION_AUTO_ATTACK:                    // 20 -> any target
            case SAI_ACTION_ALLOW_COMBAT_MOVEMENT:          // 21 -> self
            case SAI_ACTION_SET_EVENT_PHASE:                // 22 -> any target
            case SAI_ACTION_INC_EVENT_PHASE:                // 23 -> any target
            case SAI_ACTION_EVADE:                          // 24 -> any target
            case SAI_ACTION_COMBAT_STOP:                    // 27 -> self
            case SAI_ACTION_RANDOM_PHASE_RANGE:             // 31 -> self
            case SAI_ACTION_RESET_GOBJECT:                  // 32 -> any target
            case SAI_ACTION_SET_INST_DATA:                  // 34 -> self, invoker, irrelevant
            case SAI_ACTION_DIE:                            // 37 -> self
            case SAI_ACTION_SET_IN_COMBAT_WITH_ZONE:        // 38 -> self
            case SAI_ACTION_SET_INVINCIBILITY_HP_LEVEL:     // 42 -> self
            case SAI_ACTION_SET_DATA:                       // 45 -> any target
            case SAI_ACTION_ATTACK_STOP:                    // 46 -> self
            case SAI_ACTION_SET_VISIBILITY:                 // 47 -> any target
            case SAI_ACTION_SET_ACTIVE:                     // 48 -> any target
            case SAI_ACTION_ATTACK_START:                   // 49 -> any target
            case SAI_ACTION_KILL_UNIT:                      // 51 -> any target
            case SAI_ACTION_SET_RUN:                        // 59 -> self
            case SAI_ACTION_SET_DISABLE_GRAVITY:            // 60 -> self
            case SAI_ACTION_SET_SWIM:                       // 61 -> self
            case SAI_ACTION_SET_COUNTER:                    // 63 -> any target
            case SAI_ACTION_STORE_TARGET_LIST:              // 64 -> any target
            case SAI_ACTION_WP_RESUME:                      // 65 -> self
            case SAI_ACTION_PLAYMOVIE:                      // 68 -> invoker
            case SAI_ACTION_CLOSE_GOSSIP:                   // 72 -> any target .. doesn't matter though
            case SAI_ACTION_TRIGGER_TIMED_EVENT:            // 73 -> self
            case SAI_ACTION_REMOVE_TIMED_EVENT:             // 74 -> self
            case SAI_ACTION_OVERRIDE_SCRIPT_BASE_OBJECT:    // 76 -> any??
            case SAI_ACTION_RESET_SCRIPT_BASE_OBJECT:       // 77 -> self
            case SAI_ACTION_CALL_SCRIPT_RESET:              // 78 -> self
            case SAI_ACTION_SET_RANGED_MOVEMENT:            // 79 -> self
            case SAI_ACTION_RANDOM_MOVE:                    // 89 -> any target
            case SAI_ACTION_SEND_GO_CUSTOM_ANIM:            // 93 -> self
            case SAI_ACTION_SEND_GOSSIP_MENU:               // 98 -> invoker
            case SAI_ACTION_SEND_TARGET_TO_TARGET:          // 100 -> any target
            case SAI_ACTION_SET_HEALTH_REGEN:               // 102 -> any target
            case SAI_ACTION_SET_ROOT:                       // 103 -> any target
            case SAI_ACTION_DISABLE_EVADE:                  // 117 -> self
            case SAI_ACTION_SET_CAN_FLY:                    // 119 -> self
            case SAI_ACTION_SET_SIGHT_DIST:                 // 121 -> any target
            case SAI_ACTION_REMOVE_ALL_GAMEOBJECTS:         // 126 -> any target
            case SAI_ACTION_PLAY_CINEMATIC:                 // 135 -> player target
                break;
            case SAI_ACTION_PAUSE_MOVEMENT:                 // 127 -> any target [ye, not gonna resolve this nonsense]
                $a['param'][6] = Util::formatTime($a['param'][1], true);
                if ($a['param'][2])
                    $footer = true;
                break;
            // simple type as param[0]
            case SAI_ACTION_PLAY_EMOTE:                     // 5 -> any target
            case SAI_ACTION_SET_EMOTE_STATE:                // 17 -> any target
                if ($a['param'][0])
                {
                    $a['param'][0] *= -1;                   // handle creature emote
                    $this->jsGlobals[Type::EMOTE][] = $a['param'][0];
                }
                break;
            case SAI_ACTION_FAIL_QUEST:                     // 6 -> any target
            case SAI_ACTION_OFFER_QUEST:                    // 7 -> invoker
            case SAI_ACTION_CALL_AREAEXPLOREDOREVENTHAPPENS:// 15 -> any target
            case SAI_ACTION_CALL_GROUPEVENTHAPPENS:         // 26 -> invoker
                if ($a['param'][0])
                    $this->jsGlobals[Type::QUEST][] = $a['param'][0];
                break;
            case SAI_ACTION_REMOVEAURASFROMSPELL:           // 28 -> any target
                if ($a['param'][2])
                $footer = true;
            case SAI_ACTION_ADD_AURA:                       // 75 -> any target
                if ($a['param'][0])
                    $this->jsGlobals[Type::SPELL][] = $a['param'][0];
                break;
            case SAI_ACTION_CALL_KILLEDMONSTER:             // 33 -> any target
            case SAI_ACTION_UPDATE_TEMPLATE:                // 36 -> self
                if ($a['param'][0])
                    $this->jsGlobals[Type::NPC][] = $a['param'][0];
                break;
            case SAI_ACTION_ADD_ITEM:                       // 56 -> invoker
            case SAI_ACTION_REMOVE_ITEM:                    // 57 -> invoker
                if ($a['param'][0])
                    $this->jsGlobals[Type::ITEM][] = $a['param'][0];
                break;
            case SAI_ACTION_GAME_EVENT_STOP:                // 111 -> doesnt matter
            case SAI_ACTION_GAME_EVENT_START:               // 112 -> doesnt matter
                if ($a['param'][0])
                    $this->jsGlobals[Type::WORLDEVENT][] = $a['param'][0];
                break;
            // simple preparse from param[0] to param[6]
            case SAI_ACTION_SET_REACT_STATE:                // 8 -> any target
                $a['param'][6] = $this->reactState($a['param'][0]);
                break;
            case SAI_ACTION_SET_NPC_FLAG:                   // 81 -> any target
            case SAI_ACTION_ADD_NPC_FLAG:                   // 82 -> any target
            case SAI_ACTION_REMOVE_NPC_FLAG:                // 83 -> any target
                $a['param'][6] = $this->npcFlags('action', 0);
                break;
            case SAI_ACTION_SET_UNIT_FIELD_BYTES_1:         // 90 -> any target
            case SAI_ACTION_REMOVE_UNIT_FIELD_BYTES_1:      // 91 -> any target
                $a['param'][6] = $this->unitFieldBytes1($a['param'][1], $a['param'][0]);
                break;
            case SAI_ACTION_SET_DYNAMIC_FLAG:               // 94 -> any target
            case SAI_ACTION_ADD_DYNAMIC_FLAG:               // 95 -> any target
            case SAI_ACTION_REMOVE_DYNAMIC_FLAG:            // 96 -> any target
                $a['param'][6] = $this->dynFlags('action', 0);
                break;
            case SAI_ACTION_SET_GO_FLAG:                    // 104 -> any target
            case SAI_ACTION_ADD_GO_FLAG:                    // 105 -> any target
            case SAI_ACTION_REMOVE_GO_FLAG:                 // 106 -> any target
                $a['param'][6] = $this->goFlags('action', 0);
                break;
            case SAI_ACTION_SET_POWER:                      // 108 -> any target
            case SAI_ACTION_ADD_POWER:                      // 109 -> any target
            case SAI_ACTION_REMOVE_POWER:                   // 110 -> any target
                $a['param'][6] = Lang::spell('powerTypes', $a['param'][0]);
                break;
            // misc
            case SAI_ACTION_TALK:                           // 1 -> any target
                $noSrc = false;
                if ($src = $this->getTalkSource($noSrc))
                {
                    if ($a['param'][6] = isset($this->quotes[$src][$a['param'][0]]))
                    {
                        $quotes = $this->quotes[$src][$a['param'][0]];
                        foreach ($quotes as $quote)
                        {
                            $a['param'][7] .= sprintf($quote['text'], $noSrc ? '' : sprintf($quote['prefix'], $this->quotes[$src]['src']), $this->quotes[$src]['src']);
                            if ($a['param'][1])
                                $footer = [Util::formatTime($a['param'][1], true)];
                        }

                        // todo (low): undestand what action_param2 does
                    }
                }
                else
                    trigger_error('SmartAI::action - could not determine talk source for action #'.$a['type']);

                break;
            case SAI_ACTION_SET_FACTION:                    // 2 -> any target
                if ($a['param'][0])
                {
                    $a['param'][6] = DB::Aowow()->selectCell('SELECT factionId FROM ?_factiontemplate WHERE id = ?d', $a['param'][0]);
                    $this->jsGlobals[Type::FACTION][] = $a['param'][6];
                }
                break;
            case SAI_ACTION_MORPH_TO_ENTRY_OR_MODEL:        // 3 -> self
                if ($a['param'][0])
                    $this->jsGlobals[Type::NPC][] = $a['param'][0];
                else if (!$a['param'][1])
                    $a['param'][6] = 1;

                break;
            case SAI_ACTION_SOUND:                          // 4 -> self [param3 set in DB but not used in core?]
                $this->jsGlobals[Type::SOUND][] = $a['param'][0];
                if ($a['param'][2])
                    $footer = true;

                break;
            case SAI_ACTION_RANDOM_EMOTE:                   // 10 -> any target
                $buff = [];
                for ($i = 0; $i < 6; $i++)
                {
                    if (empty($a['param'][$i]))
                        continue;

                    $a['param'][$i] *= -1;                  // handle creature emote
                    $buff[] = '[emote='.$a['param'][$i].']';
                    $this->jsGlobals[Type::EMOTE][] = $a['param'][$i];
                }
                $a['param'][6] = Lang::concat($buff, false);
                break;
            case SAI_ACTION_CAST:                           // 11 -> any target
                $this->jsGlobals[Type::SPELL][] = $a['param'][0];
                if ($_ = $this->castFlags('action', 1))
                    $footer = $_;

                break;
            case SAI_ACTION_SUMMON_CREATURE:                // 12 -> any target
                $this->jsGlobals[Type::NPC][] = $a['param'][0];
                if ($a['param'][2])
                    $a['param'][6] = Util::formatTime($a['param'][2], true);

                $footer = $this->summonType($a['param'][1]);
                break;
            case SAI_ACTION_THREAT_SINGLE_PCT:              // 13 -> victim
            case SAI_ACTION_THREAT_ALL_PCT:                 // 14 -> self
            case SAI_ACTION_ADD_THREAT:                     // 123 -> any target
                $a['param'][6] = $a['param'][0] - $a['param'][1];
                break;
            case SAI_ACTION_SET_UNIT_FLAG:                  // 18 -> any target
            case SAI_ACTION_REMOVE_UNIT_FLAG:               // 19 -> any target
                $a['param'][6] = $a['param'][1] ? $this->unitFlags2('action', 0) : $this->unitFlags('action', 0);
                break;
            case SAI_ACTION_FLEE_FOR_ASSIST:                // 25 -> none
            case SAI_ACTION_CALL_FOR_HELP:                  // 39 -> self
                if ($a['param'][0])
                    $footer = true;
                break;
            case SAI_ACTION_FOLLOW:                         // 29 -> any target [what the heck are param 4 & 5]
                $this->jsGlobals[Type::NPC][] = $a['param'][2];
                if ($a['param'][1])
                    $a['param'][6] = Util::O2Deg($a['param'][1])[0];
                if ($a['param'][3] || $a['param'][4])
                    $a['param'][7] = 1;

                if ($a['param'][6] || $a['param'][7])
                    $footer = $a['param'];

                break;
            case SAI_ACTION_RANDOM_PHASE:                   // 30 -> self
                $buff = [];
                for ($i = 0; $i < 7; $i++)
                    if ($_ = $a['param'][$i])
                        $buff[] = $_;

                $a['param'][6] = Lang::concat($buff);
                break;
            case SAI_ACTION_SET_SHEATH:                     // 40 -> self
                if ($sheath = Lang::smartAI('sheaths', $a['param'][0]))
                    $a['param'][6] = $sheath;
                else
                    $a['param'][6] = lang::smartAI('sheathUNK', $a['param'][0]);

                break;
            case SAI_ACTION_FORCE_DESPAWN:                  // 41 -> any target
                $a['param'][6] = Util::formatTime($a['param'][0], true);
                $a['param'][7] = Util::formatTime($a['param'][1] * 1000, true);
                break;
            case SAI_ACTION_MOUNT_TO_ENTRY_OR_MODEL:        // 43 -> self
                if ($a['param'][0])
                    $this->jsGlobals[Type::NPC][] = $a['param'][0];
                else if (!$a['param'][1])
                    $a['param'][6] = 1;
                break;
            case SAI_ACTION_SET_INGAME_PHASE_MASK:          // 44 -> any target
                $a['param'][6] = $a['param'][0] ? Lang::concat(Util::mask2bits($a['param'][0])) : 0;
                break;
            case SAI_ACTION_SUMMON_GO:                      // 50 -> self, world coords
                $this->jsGlobals[Type::OBJECT][] = $a['param'][0];
                $a['param'][6] = Util::formatTime($a['param'][1] * 1000, true);

                if (!$a['param'][2])
                    $footer = true;

                break;
            case SAI_ACTION_ACTIVATE_TAXI:                  // 52 -> invoker
                $nodes = DB::Aowow()->selectRow('
                    SELECT tn1.name_loc0 AS start_loc0, tn1.name_loc?d AS start_loc?d, tn2.name_loc0 AS end_loc0, tn2.name_loc?d AS end_loc?d
                    FROM ?_taxipath tp
                    JOIN ?_taxinodes tn1 ON tp.startNodeId = tn1.id
                    JOIN ?_taxinodes tn2 ON tp.endNodeId = tn2.id
                    WHERE tp.id = ?d',
                    User::$localeId, User::$localeId, User::$localeId, User::$localeId, $a['param'][0]
                    );
                $a['param'][6] = Util::localizedString($nodes, 'start');
                $a['param'][7] = Util::localizedString($nodes, 'end');
                break;
            case SAI_ACTION_WP_START:                       // 53 -> any .. why tho?
                $a['param'][7] = $this->reactState($a['param'][5]);
                if ($a['param'][3])
                    $this->jsGlobals[Type::QUEST][] = $a['param'][3];
                if ($a['param'][4])
                    $a['param'][6] = Util::formatTime($a['param'][4], true);
                if ($a['param'][2])
                    $footer = true;

                break;
            case SAI_ACTION_WP_PAUSE:                       // 54 -> self
                $a['param'][6] = Util::formatTime($a['param'][0], true);
                break;
            case SAI_ACTION_WP_STOP:                        // 55 -> self
                if ($a['param'][0])
                    $a['param'][6] = Util::formatTime($a['param'][0], true);

                if ($a['param'][1])
                {
                    $this->jsGlobals[Type::QUEST][] = $a['param'][1];
                    $a['param'][$a['param'][2] ? 7 : 8] = 1;
                }

                break;
            case SAI_ACTION_INSTALL_AI_TEMPLATE:            // 58 -> self
                $a['param'][6] = $this->aiTemplate($a['param'][0]);
                break;
            case SAI_ACTION_TELEPORT:                       // 62 -> invoker [resolved coords already stored in areatrigger entry]
                if (isset($this->miscData['teleportA']))
                    $a['param'][6] = $this->miscData['teleportA'];
                else if ($pos = Game::worldPosToZonePos($a['param'][0], $this->itr['target']['pos'][0], $this->itr['target']['pos'][1]))
                    $a['param'][6] = $pos['areaId'];
                else if ($areaId = DB::Aowow()->selectCell('SELECT id FROM ?_zones WHERE mapId = ?d LIMIT 1', $a['param'][0]))
                    $a['param'][6] = $areaId;
                else
                    trigger_error('SmartAI::action - could not resolve teleport target: map:'.$a['param'][0].' x:'.$this->itr['target']['pos'][0].' y:'.$this->itr['target']['pos'][1]);

                $this->jsGlobals[Type::ZONE][] = $a['param'][6];
                break;
            case SAI_ACTION_SET_ORIENTATION:                // 66 -> any target
                if ($this->itr['target']['type'] == SAI_TARGET_POSITION)
                    $a['param'][6] = Util::O2Deg($this->itr['target']['pos'][3])[1];
                else if ($this->itr['target']['type'] != SAI_TARGET_SELF)
                    $a['param'][6] = '#target#';
                break;
            case SAI_ACTION_CREATE_TIMED_EVENT:             // 67 -> self
                $a['param'][6] = $this->numRange('action', 1, true);
                $a['param'][7] = ($a['param'][5] < 100);
                if ($repeat = $this->numRange('action', 3, true))
                    $footer = [$repeat];
                break;
            case SAI_ACTION_MOVE_TO_POS:                    // 69 -> any target
                if ($a['param'][2])
                    $footer = true;
                break;
            case SAI_ACTION_ENABLE_TEMP_GOBJ:               // 70 -> any target
            case SAI_ACTION_SET_CORPSE_DELAY:               // 116 -> ???
            case SAI_ACTION_FLEE:                           // 122 -> any target
                $a['param'][6] = Util::formatTime($a['param'][0] * 1000, true);
                break;
            case SAI_ACTION_EQUIP:                          // 71 -> any
                $buff = [];
                if ($a['param'][0])
                {
                    $slots = [1, 2, 3];
                    if ($a['param'][1])
                        $slots = Util::mask2bits($a['param'][1], 1);

                    $items = DB::World()->selectRow('SELECT ItemID1, ItemID2, ItemID3 FROM creature_equip_template WHERE CreatureID = ?d AND ID = ?d', $this->miscData['baseEntry'] ?: $this->entry, $a['param'][0]);
                    foreach ($items as $i)
                        $this->jsGlobals[Type::ITEM][] = $i;

                    foreach ($slots as $s)
                        if ($_ = $items['ItemID'.$s])
                            $buff[] = '[item='.$_.']';
                }
                else if ($a['param'][2] || $a['param'][3] || $a['param'][4])
                {
                    if ($_ = $a['param'][2])
                    {
                        $this->jsGlobals[Type::ITEM][] = $_;
                        $buff[] = '[item='.$_.']';
                    }
                    if ($_ = $a['param'][3])
                    {
                        $this->jsGlobals[Type::ITEM][] = $_;
                        $buff[] = '[item='.$_.']';
                    }
                    if ($_ = $a['param'][4])
                    {
                        $this->jsGlobals[Type::ITEM][] = $_;
                        $buff[] = '[item='.$_.']';
                    }
                }
                else
                    $a['param'][7] = 1;

                $a['param'][6] = Lang::concat($buff);

                $footer = true;

                break;
            case SAI_ACTION_CALL_TIMED_ACTIONLIST:          // 80 -> any target
                switch ($a['param'][1])
                {
                    case 0:
                    case 1:
                    case 2:
                        $a['param'][6] = Lang::smartAI('saiUpdate', $a['param'][1]);
                        break;
                    default:
                        $a['param'][6] = Lang::smartAI('saiUpdateUNK', [$a['param'][1]]);
                }

                $tal = new SmartAI(SAI_SRC_TYPE_ACTIONLIST, $a['param'][0], array_merge(['baseEntry' => $this->entry], $this->miscData));
                $tal->prepare();
                foreach ($tal->getJSGlobals() as $type => $data)
                {
                    if (empty($this->jsGlobals[$type]))
                        $this->jsGlobals[$type] = [];

                    $this->jsGlobals[$type] = array_merge($this->jsGlobals[$type], $data);
                }

                foreach ($tal->getTabs() as $guid => $tt)
                    $this->tabs[$guid] = $tt;

                break;
            case SAI_ACTION_SIMPLE_TALK:                    // 84 -> any target
                $noSrc = false;
                if ($src = $this->getTalkSource($noSrc))
                {
                    if (isset($this->quotes[$src][$a['param'][0]]))
                    {
                        $quotes = $this->quotes[$src][$a['param'][0]];
                        foreach ($quotes as $quote)
                            $a['param'][6] .= sprintf($quote['text'], $noSrc ? '' : sprintf($quote['prefix'], $this->quotes[$src]['src']), $this->quotes[$src]['src']);
                    }
                }
                else
                    trigger_error('SmartAI::action - could not determine talk source for action #'.$a['type']);

                break;
            case SAI_ACTION_CROSS_CAST:                     // 86 -> entity by TargetingBlock(param3, param4, param5, param6) cross cast spell <param1> at any target
                $a['param'][6] = $this->target(array(
                    'type'  => $a['param'][2],
                    'param' => [$a['param'][3], $a['param'][4], $a['param'][5], 0],
                    'pos'   => [0, 0, 0, 0]
                ));
                // do not break;
            case SAI_ACTION_SELF_CAST:                      // 85 -> self
            case SAI_ACTION_INVOKER_CAST:                   // 134 -> any target
                $this->jsGlobals[Type::SPELL][] = $a['param'][0];
                if ($_ = $this->castFlags('action', 1))
                    $footer = $_;
                break;
            case SAI_ACTION_CALL_RANDOM_TIMED_ACTIONLIST:   // 87 -> self
                $talBuff = [];
                for ($i = 0; $i < 6; $i++)
                {
                    if (!$a['param'][$i])
                        continue;

                    $talBuff[] = '<a href=#sai-actionlist-'.$a['param'][$i].' onclick=\\"\$(\\\'#dsf67g4d-sai\\\').find(\\\'[href=\\\\\'#sai-actionlist-'.$a['param'][$i].'\\\\\']\\\').click()\\">#'.$a['param'][$i].'</a>';

                    $tal = new SmartAI(SAI_SRC_TYPE_ACTIONLIST, $a['param'][$i], array_merge(['baseEntry' => $this->entry], $this->miscData));
                    $tal->prepare();
                    foreach ($tal->getJSGlobals() as $type => $data)
                    {
                        if (empty($this->jsGlobals[$type]))
                            $this->jsGlobals[$type] = [];

                        $this->jsGlobals[$type] = array_merge($this->jsGlobals[$type], $data);
                    }

                    foreach ($tal->getTabs() as $guid => $tt)
                        $this->tabs[$guid] = $tt;
                }
                $a['param'][6] = Lang::concat($talBuff, false);
                break;
            case SAI_ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST:// 88 -> self
                $talBuff = [];
                for ($i = $a['param'][0]; $i <= $a['param'][1]; $i++)
                {
                    $talBuff[] = '<a href=#sai-actionlist-'.$i.' onclick=\\"\$(\\\'#dsf67g4d-sai\\\').find(\\\'[href=\\\\\'#sai-actionlist-'.$i.'\\\\\']\\\').click()\\">#'.$i.'</a>';

                    $tal = new SmartAI(SAI_SRC_TYPE_ACTIONLIST, $i, array_merge(['baseEntry' => $this->entry], $this->miscData));
                    $tal->prepare();
                    foreach ($tal->getJSGlobals() as $type => $data)
                    {
                        if (empty($this->jsGlobals[$type]))
                            $this->jsGlobals[$type] = [];

                        $this->jsGlobals[$type] = array_merge($this->jsGlobals[$type], $data);
                    }

                    foreach ($tal->getTabs() as $guid => $tt)
                        $this->tabs[$guid] = $tt;
                }
                $a['param'][6] = Lang::concat($talBuff, false);

                break;
            case SAI_ACTION_INTERRUPT_SPELL:                // 92 -> self
                if ($_ = $a['param'][1])
                    $this->jsGlobals[Type::SPELL][] = $a['param'][1];

                if ($a['param'][0] || $a['param'][2])
                    $footer = [$a['param'][0]];

                break;
            case SAI_ACTION_SET_HOME_POS:                   // 101 -> self
                if ($this->itr['target']['type'] == SAI_TARGET_SELF)
                    $a['param'][9] = 1;
                // do not break;
            case SAI_ACTION_JUMP_TO_POS:                    // 97 -> self
            case SAI_ACTION_MOVE_OFFSET:                    // 114 -> self
                $a['param'][6] = $this->itr['target']['pos'][0];
                $a['param'][7] = $this->itr['target']['pos'][1];
                $a['param'][8] = $this->itr['target']['pos'][2];
                break;
            case SAI_ACTION_GO_SET_LOOT_STATE:              // 99 -> any target
                switch ($a['param'][0])
                {
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                        $a['param'][6] = Lang::smartAI('lootStates', $a['param'][0]);
                        break;
                    default:
                        $a['param'][6] = Lang::smartAI('lootStateUNK', [$a['param'][0]]);
                }
                break;

                break;
            case SAI_ACTION_SUMMON_CREATURE_GROUP:          // 107 -> untargeted
                if ($this->summons === null)
                    $this->summons = DB::World()->selectCol('SELECT groupId AS ARRAY_KEY, entry AS ARRAY_KEY2, COUNT(*) AS n FROM creature_summon_groups WHERE summonerId = ?d GROUP BY groupId, entry', empty($this->miscData['baseEntry']) ? $this->entry : $this->miscData['baseEntry']);

                $buff = [];
                if (!empty($this->summons[$a['param'][0]]))
                {
                    foreach ($this->summons[$a['param'][0]] as $id => $n)
                    {
                        $this->jsGlobals[Type::NPC][] = $id;
                        $buff[] = $n.'x [npc='.$id.']';
                    }
                }

                if ($buff)
                    $a['param'][6] = Lang::concat($buff);

                break;
            case SAI_ACTION_START_CLOSEST_WAYPOINT:         // 113 -> any target
                $buff = [];
                for ($i = 0; $i < 6; $i++)
                    if ($a['param'][$i])
                        $buff[] = '#[b]'.$a['param'][$i].'[/b]';

                $a['param'][6] = Lang::concat($buff, false);
                break;
            case SAI_ACTION_RANDOM_SOUND:                   // 115 -> self
                for ($i = 0; $i < 4; $i++)
                {
                    if ($x = $a['param'][$i])
                    {
                        $this->jsGlobals[Type::SOUND][] = $x;
                        $a['param'][6] .= '[sound='.$x.']';
                    }
                }

                if ($a['param'][5])
                    $footer = true;

                break;
            case SAI_ACTION_GO_SET_GO_STATE:                // 118 -> ???
                switch ($a['param'][0])
                {
                    case 0:
                    case 1:
                    case 2:
                        $a['param'][6] = Lang::smartAI('GOStates', $a['param'][0]);
                        break;
                    default:
                        $a['param'][6] = Lang::smartAI('GOStateUNK', [$a['param'][0]]);
                }
                break;
            case SAI_ACTION_REMOVE_AURAS_BY_TYPE:           // 120 -> any target
                $a['param'][6] = Lang::spell('auras', $a['param'][0]);
                break;
            case SAI_ACTION_LOAD_EQUIPMENT:                 // 124 -> any target
                $buff = [];
                if ($a['param'][0])
                {
                    $items = DB::World()->selectRow('SELECT ItemID1, ItemID2, ItemID3 FROM creature_equip_template WHERE CreatureID = ?d AND ID = ?d', $this->miscData['baseEntry'] ?: $this->entry, $a['param'][0]);
                    foreach ($items as $i)
                    {
                        if (!$i)
                            continue;

                        $this->jsGlobals[Type::ITEM][] = $i;
                        $buff[] = '[item='.$i.']';
                    }
                }
                else if (!$a['param'][1])
                    trigger_error('SmartAI::action - action #124 (SAI_ACTION_LOAD_EQIPMENT) is malformed');

                $a['param'][6] = Lang::concat($buff);
                $footer = true;

                break;
            case SAI_ACTION_TRIGGER_RANDOM_TIMED_EVENT:     // 125 -> self
                $a['param'][6] = $this->numRange('action', 0);
                break;
            case SAI_ACTION_SPAWN_SPAWNGROUP:               // 131
            case SAI_ACTION_DESPAWN_SPAWNGROUP:             // 132
                $a['param'][6] = DB::World()->selectCell('SELECT `GroupName` FROM spawn_group_template WHERE `groupId` = ?d', $a['param'][0]);
                $entities = DB::World()->select('SELECT `spawnType` AS "0", `spawnId` AS "1" FROM spawn_group WHERE `groupId` = ?d',  $a['param'][0]);

                $n = 5;
                foreach ($entities as [$spawnType, $guid])
                {
                    $type = Type::NPC;
                    if ($spawnType == 1)
                        $type == Type::OBJECT;

                    $a['param'][7] = $this->spawnFlags('action', 3);

                    if ($_ = DB::Aowow()->selectCell('SELECT `typeId` FROM ?_spawns WHERE `type` = ?d AND `guid` = ?d',  $type, $guid))
                    {
                        $this->jsGlobals[$type][] = $_;
                        $a['param'][8] .= '[li]['.Type::getFileString($type).'='.$_.'][small class=q0] (GUID: '.$guid.')[/small][/li]';
                    }
                    else
                        $a['param'][8] .= '[li]'.Lang::smartAI('entityUNK').'[small class=q0] (GUID: '.$guid.')[/small][/li]';

                    if (!--$n)
                        break;
                }

                if (count($entities) > 5)
                    $a['param'][8] .= '[li]+'.(count($entities) - 5).'…[/li]';

                $a['param'][8] = '[ul]'.$a['param'][8].'[/ul]';

                if ($time = $this->numRange('action', 1, true))
                    $footer = [$time];
                break;
            case SAI_ACTION_RESPAWN_BY_SPAWNID:             // 133
                $type = Type::NPC;
                if ($a['param'][0] == 1)
                    $type == Type::OBJECT;

                if ($_ = DB::Aowow()->selectCell('SELECT `typeId` FROM ?_spawns WHERE `type` = ?d AND `guid` = ?d',  $type, $a['param'][1]))
                    $a['param'][6] = '['.Type::getFileString($type).'='.$_.']';
                else
                    $a['param'][6] = Lang::smartAI('entityUNK');
                break;
            case SAI_ACTION_SET_MOVEMENT_SPEED:             // 136
                $a['param'][6] = $a['param'][1] + $a['param'][2] / pow(10, floor(log10($a['param'][2] ?: 1.0) + 1));  // i know string concatenation is a thing. don't @ me!
                break;
            case SAI_ACTION_OVERRIDE_LIGHT:                 // 138
                $this->jsGlobals[Type::ZONE][] = $a['param'][0];
                $footer = [Util::formatTime($a['param'][2], true)];
                break;
            case SAI_ACTION_OVERRIDE_WEATHER:               // 139
                $this->jsGlobals[Type::ZONE][] = $a['param'][0];
                if (!($a['param'][6] = Lang::smartAI('weatherStates', $a['param'][1])))
                    $a['param'][6] = Lang::smartAI('weatherStateUNK', [$a['param'][1]]);
                break;
            default:
                $body = Lang::smartAI('actionUNK', [$a['type']]);
        }

        $body = $body ?: Lang::smartAI('actions', $a['type'], 0, $a['param']);
        if (gettype($footer) != 'string')
            $footer = Lang::smartAI('actions', $a['type'], 1, (array)$footer);

        // resolve conditionals
        $footer = preg_replace_callback('/\(([^\)]*?)\)\?([^:]*):([^;]*);/i', function ($m) { return $m[1] ? $m[2] : $m[3]; }, $footer);
        $body   = preg_replace_callback('/\(([^\)]*?)\)\?([^:]*):([^;]*);/i', function ($m) { return $m[1] ? $m[2] : $m[3]; }, $body);
        $body   = str_replace('#target#', $this->target(), $body);

        // wrap body in tooltip
        return [sprintf($tooltip, $body), $footer];
    }
}

?>
