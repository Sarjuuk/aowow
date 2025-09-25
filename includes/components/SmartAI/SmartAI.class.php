<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// TrinityCore - SmartAI

trait SmartHelper
{
    private function resolveGuid(int $type, int $guid) : ?int
    {
        if ($_ = DB::Aowow()->selectCell('SELECT `typeId` FROM ?_spawns WHERE `type` = ?d AND `guid` = ?d', $type, $guid))
            return $_;

        trigger_error('SmartAI::resolveGuid - failed to resolve guid '.$guid.' of type '.$type, E_USER_WARNING);
        return null;
    }

    private function numRange(int $min, int $max, bool $isTime) : string
    {
        if (!$min && !$max)
            return '';

        $str = $isTime ? Util::formatTime($min, true) : $min;
        if ($max > $min)
            $str .= ' &ndash; '.($isTime ? Util::formatTime($max, true) : $max);

        return $str;
    }

    private function formatTime(int $time, int $_, bool $isMilliSec) : string
    {
        if (!$time)
            return '';

        return Util::formatTime($time * ($isMilliSec ? 1 : 1000), false);
    }

    private function castFlags(int $flags) : string
    {
        $cf = [];
        for ($i = 1; $i <= SmartAI::CAST_FLAG_COMBAT_MOVE; $i <<= 1)
            if (($flags & $i) && ($x = Lang::smartAI('castFlags', $i)))
                $cf[]  = $x;

        return Lang::concat($cf);
    }

    private function npcFlags(int $flags) : string
    {
        $nf = [];
        for ($i = 1; $i <= NPC_FLAG_MAILBOX; $i <<= 1)
            if (($flags & $i) && ($x = Lang::npc('npcFlags', $i)))
                $nf[] = $x;

        return Lang::concat($nf ?: [Lang::smartAI('empty')]);
    }

    private function dynFlags(int $flags) : string
    {
        $df = [];
        for ($i = 1; $i <= UNIT_DYNFLAG_TAPPED_BY_ALL_THREAT_LIST; $i <<= 1)
            if (($flags & $i) && ($x = Lang::unit('dynFlags', $i)))
                $df[] = $x;

        return Lang::concat($df ?: [Lang::smartAI('empty')]);
    }

    private function goFlags(int $flags) : string
    {
        $gf = [];
        for ($i = 1; $i <= GO_FLAG_DESTROYED; $i <<= 1)
            if (($flags & $i) && ($x = Lang::gameObject('goFlags', $i)))
                $gf[] = $x;

        return Lang::concat($gf ?: [Lang::smartAI('empty')]);
    }

    private function spawnFlags(int $flags) : string
    {
        $sf = [];
        for ($i = 1; $i <= SmartAI::SPAWN_FLAG_NOSAVE_RESPAWN; $i <<= 1)
            if (($flags & $i) && ($x = Lang::smartAI('spawnFlags', $i)))
                $sf[] = $x;

        return Lang::concat($sf ?: [Lang::smartAI('empty')]);
    }

    private function unitFlags(int $flags, int $flags2) : string
    {
        $field = $flags2 ? 'flags2' : 'flags';
        $max   = $flags2 ? UNIT_FLAG2_ALLOW_CHEAT_SPELLS : UNIT_FLAG_UNK_31;
        $uf    = [];

        for ($i = 1; $i <= $max; $i <<= 1)
            if (($flags & $i) && ($x = Lang::unit($field, $i)))
                $uf[] = $x;

        return Lang::concat($uf ?: [Lang::smartAI('empty')]);
    }

    private function unitFieldBytes1(int $flags, int $idx) : string
    {
        switch ($idx)
        {
            case 0:
            case 3:
                return Lang::unit('bytes1', 'bytesIdx', $idx).Lang::main('colon').(Lang::unit('bytes1', $idx, $flags) ?? Lang::unit('bytes1', 'valueUNK', [$flags, $idx]));
            case 2:
                $buff = [];
                for ($i = 1; $i <= 0x10; $i <<= 1)
                    if (($flags & $i) && ($x = Lang::unit('bytes1', $idx, $flags)))
                        $buff[] = $x;

                return Lang::unit('bytes1', 'bytesIdx', $idx).Lang::main('colon').($buff ? Lang::concat($buff) : Lang::unit('bytes1', 'valueUNK', [$flags, $idx]));
            default:
                return Lang::unit('bytes1', 'idxUNK', [$idx]);
        }
    }

    private function summonType(int $x) : string
    {
        return Lang::smartAI('summonTypes', $x) ?? Lang::smartAI('summonType', 'summonTypeUNK', [$x]);
    }

    private function sheathState(int $x) : string
    {
        return Lang::smartAI('sheaths', $x) ?? Lang::smartAI('sheathUNK', [$x]);
    }

    private function aiTemplate(int $x) : string
    {
        return Lang::smartAI('aiTpl', $x) ?? Lang::smartAI('aiTplUNK', [$x]);
    }

    private function reactState(int $x) : string
    {
        return Lang::smartAI('reactStates', $x) ?? Lang::smartAI('reactStateUNK', [$x]);
    }

    private function powerType(int $x) : string
    {
        return Lang::spell('powerTypes', $x) ?? Lang::smartAI('powerTypeUNK', [$x]);
    }

    private function hostilityMode(int $x) : string
    {
        return Lang::smartAI('hostilityModes', $x) ?? Lang::smartAI('hostilityModeUNK', [$x]);
    }

    private function motionType(int $x) : string
    {
        return Lang::smartAI('motionTypes', $x) ?? Lang::smartAI('motionTypeUNK', [$x]);
    }

    private function lootState(int $x) : string
    {
        return Lang::smartAI('lootStates', $x) ?? Lang::smartAI('lootStateUNK', [$x]);
    }
    private function weatherState(int $x) : string
    {
        return Lang::smartAI('weatherStates', $x) ?? Lang::smartAI('weatherStateUNK', [$x]);
    }

    private function magicSchool(int $x) : string
    {
        return Lang::getMagicSchools($x);
    }
}

class SmartAI
{
    public const SRC_TYPE_CREATURE    = 0;
    public const SRC_TYPE_OBJECT      = 1;
    public const SRC_TYPE_AREATRIGGER = 2;
    public const SRC_TYPE_ACTIONLIST  = 9;

    public const CAST_FLAG_INTERRUPT_PREV = 0x01;           // Interrupt any spell casting
    public const CAST_FLAG_TRIGGERED      = 0x02;           // Triggered (this makes spell cost zero mana and have no cast time)
//  public const CAST_FORCE_CAST          = 0x04;           // Forces cast even if creature is out of mana or out of range
//  public const CAST_NO_MELEE_IF_OOM     = 0x08;           // Prevents creature from entering melee if out of mana or out of range
//  public const CAST_FORCE_TARGET_SELF   = 0x10;           // the target to cast this spell on itself
    public const CAST_FLAG_AURA_MISSING   = 0x20;           // Only casts the spell if the target does not have an aura from the spell
    public const CAST_FLAG_COMBAT_MOVE    = 0x40;           // Prevents combat movement if cast successful. Allows movement on range, OOM, LOS

    public const REACT_PASSIVE    = 0;
    public const REACT_DEFENSIVE  = 1;
    public const REACT_AGGRESSIVE = 2;
    public const REACT_ASSIST     = 3;

    public const SUMMON_TIMED_OR_DEAD_DESPAWN   = 1;
    public const SUMMON_TIMED_OR_CORPSE_DESPAWN = 2;
    public const SUMMON_TIMED_DESPAWN           = 3;
    public const SUMMON_TIMED_DESPAWN_OOC       = 4;
    public const SUMMON_CORPSE_DESPAWN          = 5;
    public const SUMMON_CORPSE_TIMED_DESPAWN    = 6;
    public const SUMMON_DEAD_DESPAWN            = 7;
    public const SUMMON_MANUAL_DESPAWN          = 8;

    public const TEMPLATE_BASIC          = 0;               //
    public const TEMPLATE_CASTER         = 1;               //  +JOIN: target_param1 as castFlag
    public const TEMPLATE_TURRET         = 2;               //  +JOIN: target_param1 as castflag
    public const TEMPLATE_PASSIVE        = 3;               //
    public const TEMPLATE_CAGED_GO_PART  = 4;               //
    public const TEMPLATE_CAGED_NPC_PART = 5;               //

    public const SPAWN_FLAG_NONE           = 0x00;
    public const SPAWN_FLAG_IGNORE_RESPAWN = 0x01;          // onSpawnIn - ignore & reset respawn timer
    public const SPAWN_FLAG_FORCE_SPAWN    = 0x02;          // onSpawnIn - force additional spawn if already in world
    public const SPAWN_FLAG_NOSAVE_RESPAWN = 0x04;          // onDespawn - remove respawn time

    private array $jsGlobals  = [];
    private array $rawData    = [];
    private array $result     = [];
    private array $tabs       = [];
    private array $itr        = [];

    private array  $quotes    = [];

    // misc data
    public readonly int    $baseEntry;                      // I'm a timed action list belonging to this entry
    public readonly string $title;                          // title appendix for the [toggle]
    public readonly int    $teleportTargetArea;             // precalculated areaId so we don't have to look it up right now

    public function __construct(public readonly int $srcType = 0, public readonly int $entry = 0, array $miscData = [])
    {
        $this->baseEntry          = $miscData['baseEntry']          ?? 0;
        $this->title              = $miscData['title']              ?? '';
        $this->teleportTargetArea = $miscData['teleportTargetArea'] ?? 0;

        $raw = DB::World()->select(
            'SELECT   `id`, `link`,
                      `event_type`, `event_phase_mask`, `event_chance`, `event_flags`, `event_param1`, `event_param2`, `event_param3`, `event_param4`, `event_param5`,
                      `action_type`, `action_param1`, `action_param2`, `action_param3`, `action_param4`, `action_param5`, `action_param6`,
                      `target_type`, `target_param1`, `target_param2`, `target_param3`, `target_param4`, `target_x`, `target_y`, `target_z`, `target_o`
             FROM     smart_scripts
             WHERE    `entryorguid` = ?d AND `source_type` = ?d
             ORDER BY `id` ASC',
            $this->entry, $this->srcType);

        foreach ($raw as $r)
        {
            $this->rawData[$r['id']] = array(
                'id'     => $r['id'],
                'link'   => $r['link'],
                'event'  => new SmartEvent($r['id'], $r['event_type'], $r['event_phase_mask'], $r['event_chance'], $r['event_flags'], [$r['event_param1'], $r['event_param2'], $r['event_param3'], $r['event_param4'], $r['event_param5']], $this),
                'action' => new SmartAction($r['id'], $r['action_type'], [$r['action_param1'], $r['action_param2'], $r['action_param3'], $r['action_param4'], $r['action_param5'], $r['action_param6']], $this),
                'target' => new SmartTarget($r['id'], $r['target_type'], [$r['target_param1'], $r['target_param2'], $r['target_param3'], $r['target_param4']], [$r['target_x'], $r['target_y'], $r['target_z'], $r['target_o']], $this)
            );
        }
    }


    /*********************/
    /* Lookups by action */
    /*********************/

    public static function getOwnerOfNPCSummon(int $npcId, int $typeFilter = 0) : array
    {
        if ($npcId <= 0)
            return [];

        $lookup = array(
            SmartAction::ACTION_SUMMON_CREATURE         => [1 => $npcId],
            SmartAction::ACTION_MOUNT_TO_ENTRY_OR_MODEL => [1 => $npcId]
        );

        if ($npcGuids = DB::Aowow()->selectCol('SELECT `guid` FROM ?_spawns WHERE `type` = ?d AND `typeId` = ?d', Type::NPC, $npcId))
            if ($groups = DB::World()->selectCol('SELECT `groupId` FROM spawn_group WHERE `spawnType` = 0 AND `spawnId` IN (?a)', $npcGuids))
                foreach ($groups as $g)
                    $lookup[SmartAction::ACTION_SPAWN_SPAWNGROUP][1] = $g;

        $result = self::getActionOwner($lookup, $typeFilter);

        // can skip lookups for SmartAction::ACTION_SUMMON_CREATURE_GROUP as creature_summon_groups already contains summoner info
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
            SmartAction::ACTION_SUMMON_GO => [1 => $objectId]
        );

        if ($objGuids = DB::Aowow()->selectCol('SELECT `guid` FROM ?_spawns WHERE `type` = ?d AND `typeId` = ?d', Type::OBJECT, $objectId))
            if ($groups = DB::World()->selectCol('SELECT `groupId` FROM spawn_group WHERE `spawnType` = 1 AND `spawnId` IN (?a)', $objGuids))
                foreach ($groups as $g)
                    $lookup[SmartAction::ACTION_SPAWN_SPAWNGROUP][1] = $g;

        return self::getActionOwner($lookup, $typeFilter);
    }

    public static function getOwnerOfSpellCast(int $spellId, int $typeFilter = 0) : array
    {
        if ($spellId <= 0)
            return [];

        $lookup = array(
            SmartAction::ACTION_CAST         => [1 => $spellId],
            SmartAction::ACTION_ADD_AURA     => [1 => $spellId],
            SmartAction::ACTION_SELF_CAST    => [1 => $spellId],
            SmartAction::ACTION_CROSS_CAST   => [1 => $spellId],
            SmartAction::ACTION_INVOKER_CAST => [1 => $spellId]
        );

        return self::getActionOwner($lookup, $typeFilter);
    }

    public static function getOwnerOfSoundPlayed(int $soundId, int $typeFilter = 0) : array
    {
        if ($soundId <= 0)
            return [];

        $lookup = array(
            SmartAction::ACTION_SOUND => [1 => $soundId]
        );

        return self::getActionOwner($lookup, $typeFilter);
    }

    // lookup: SmartActionId => [[paramIdx => value], ...]
    private static function getActionOwner(array $lookup, int $typeFilter = 0) : array
    {
        $qParts    = [];
        $result    = [];
        $genFilter = $talFilter = [];
        switch ($typeFilter)
        {
            case Type::NPC:
                $genFilter = [self::SRC_TYPE_CREATURE, self::SRC_TYPE_ACTIONLIST];
                $talFilter = [self::SRC_TYPE_CREATURE];
                break;
            case Type::OBJECT:
                $genFilter = [self::SRC_TYPE_OBJECT, self::SRC_TYPE_ACTIONLIST];
                $talFilter = [self::SRC_TYPE_OBJECT];
                break;
            case Type::AREATRIGGER:
                $genFilter = [self::SRC_TYPE_AREATRIGGER, self::SRC_TYPE_ACTIONLIST];
                $talFilter = [self::SRC_TYPE_AREATRIGGER];
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

        $smartS = DB::World()->select(sprintf('SELECT `source_type` AS "0", `entryOrGUID` AS "1" FROM smart_scripts WHERE (%s){ AND `source_type` IN (?a)}', $qParts ? implode(' OR ', $qParts) : '0'), $genFilter ?: DBSIMPLE_SKIP);

        // filter for TAL shenanigans
        if ($smartTAL = array_filter($smartS, fn($x) => $x[0] == self::SRC_TYPE_ACTIONLIST))
        {
            $smartS = array_diff_key($smartS, $smartTAL);

            $q = [];
            foreach ($smartTAL as [, $eog])
            {
                // SmartAction::ACTION_CALL_TIMED_ACTIONLIST
                $q[] = '`action_type` = '.SmartAction::ACTION_CALL_TIMED_ACTIONLIST.' AND `action_param1` = '.$eog;

                // SmartAction::ACTION_CALL_RANDOM_TIMED_ACTIONLIST
                $q[] = '`action_type` = '.SmartAction::ACTION_CALL_RANDOM_TIMED_ACTIONLIST.' AND (`action_param1` = '.$eog.' OR `action_param2` = '.$eog.' OR `action_param3` = '.$eog.' OR `action_param4` = '.$eog.' OR `action_param5` = '.$eog.')';

                // SmartAction::ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST
                $q[] = '`action_type` = '.SmartAction::ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST.' AND `action_param1` <= '.$eog.' AND `action_param2` >= '.$eog;
            }

            if ($_ = DB::World()->select(sprintf('SELECT `source_type` AS "0", `entryOrGUID` AS "1" FROM smart_scripts WHERE ((%s)){ AND `source_type` IN (?a)}', $q ? implode(') OR (', $q) : '0'), $talFilter ?: DBSIMPLE_SKIP))
                $smartS = array_merge($smartS, $_);
        }

        // filter guids for entries
        if ($smartG = array_filter($smartS, fn($x) => $x[1] < 0))
        {
            $smartS = array_diff_key($smartS, $smartG);

            $q = [];
            foreach ($smartG as [$st, $eog])
            {
                if ($st == self::SRC_TYPE_CREATURE)
                    $q[] = '`type` = '.Type::NPC.' AND `guid` = '.-$eog;
                else if ($st == self::SRC_TYPE_OBJECT)
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
            if ($st == self::SRC_TYPE_CREATURE)
                $result[Type::NPC][] = $eog;
            else if ($st == self::SRC_TYPE_OBJECT)
                $result[Type::OBJECT][] = $eog;
            else if ($st == self::SRC_TYPE_AREATRIGGER)
                $result[Type::AREATRIGGER][] = $eog;
        }

        return $result;
    }


    /********************/
    /* Lookups by owner */
    /********************/

    public static function getNPCSummonsForOwner(int $entry, int $srcType = self::SRC_TYPE_CREATURE) : array
    {
        // action => paramIdx with npcIds/spawnGoupIds
        $lookup = array(
            SmartAction::ACTION_SUMMON_CREATURE         => [1],
            SmartAction::ACTION_MOUNT_TO_ENTRY_OR_MODEL => [1],
            SmartAction::ACTION_SPAWN_SPAWNGROUP        => [1]
        );

        $result = self::getOwnerAction($srcType, $entry, $lookup, $moreInfo);

        // can skip lookups for SmartAction::ACTION_SUMMON_CREATURE_GROUP as creature_summon_groups already contains summoner info
        if ($srcType == self::SRC_TYPE_CREATURE || $srcType == self::SRC_TYPE_OBJECT)
        {
            $st = $srcType == self::SRC_TYPE_CREATURE ? SUMMONER_TYPE_CREATURE : SUMMONER_TYPE_GAMEOBJECT;
            if ($csg = DB::World()->selectCol('SELECT `entry` FROM creature_summon_groups WHERE `summonerType` = ?d AND `summonerId` = ?d', $st, $entry))
                $result = array_merge($result, $csg);
        }

        if (!empty($moreInfo[SmartAction::ACTION_SPAWN_SPAWNGROUP]))
        {
            $grp = $moreInfo[SmartAction::ACTION_SPAWN_SPAWNGROUP];
            if ($sgs = DB::World()->selectCol('SELECT `spawnId` FROM spawn_group WHERE `spawnType` = ?d AND `groupId` IN (?a)', SUMMONER_TYPE_CREATURE, $grp))
                if ($ids = DB::Aowow()->selectCol('SELECT DISTINCT `typeId` FROM ?_spawns WHERE `type` = ?d AND `guid` IN (?a)', Type::NPC, $sgs))
                    $result = array_merge($result, $ids);
        }

        return $result;
    }

    public static function getObjectSummonsForOwner(int $entry, int $srcType = self::SRC_TYPE_CREATURE) : array
    {
        // action => paramIdx with gobIds/spawnGoupIds
        $lookup = array(
            SmartAction::ACTION_SUMMON_GO        => [1],
            SmartAction::ACTION_SPAWN_SPAWNGROUP => [1]
        );

        $result = self::getOwnerAction($srcType, $entry, $lookup, $moreInfo);

        if (!empty($moreInfo[SmartAction::ACTION_SPAWN_SPAWNGROUP]))
        {
            $grp = $moreInfo[SmartAction::ACTION_SPAWN_SPAWNGROUP];
            if ($sgs = DB::World()->selectCol('SELECT `spawnId` FROM spawn_group WHERE `spawnType` = ?d AND `groupId` IN (?a)', SUMMONER_TYPE_GAMEOBJECT, $grp))
                if ($ids = DB::Aowow()->selectCol('SELECT DISTINCT `typeId` FROM ?_spawns WHERE `type` = ?d AND `guid` IN (?a)', Type::OBJECT, $sgs))
                    $result = array_merge($result, $ids);
        }

        return $result;
    }

    public static function getSpellCastsForOwner(int $entry, int $srcType = self::SRC_TYPE_CREATURE) : array
    {
        // action => paramIdx with spellIds
        $lookup = array(
            SmartAction::ACTION_CAST         => [1],
            SmartAction::ACTION_ADD_AURA     => [1],
            SmartAction::ACTION_INVOKER_CAST => [1],
            SmartAction::ACTION_CROSS_CAST   => [1]
        );

        return self::getOwnerAction($srcType, $entry, $lookup);
    }

    public static function getSoundsPlayedForOwner(int $entry, int $srcType = self::SRC_TYPE_CREATURE) : array
    {
        // action => paramIdx with soundIds
        $lookup = array(
            SmartAction::ACTION_SOUND => [1]
        );

        return self::getOwnerAction($srcType, $entry, $lookup);
    }

    // lookup: [SmartActionId => [paramIdx, ...], ...]
    private static function getOwnerAction(int $sourceType, int $entry, array $lookup, ?array &$moreInfo = []) : array
    {
        if ($entry < 0)                                     // no lookup by GUID
            return [];

        $actionQuery = 'SELECT `action_type`, `action_param1`, `action_param2`, `action_param3`, `action_param4`, `action_param5`, `action_param6` FROM smart_scripts WHERE `source_type` = ?d AND `action_type` IN (?a) AND `entryOrGUID` IN (?a)';

        $smartScripts = DB::World()->select($actionQuery, $sourceType, array_merge(array_keys($lookup), SmartAction::ACTION_ALL_TIMED_ACTION_LISTS), [$entry]);
        $smartResults = [];
        $smartTALs    = [];
        foreach ($smartScripts as $s)
        {
            if ($s['action_type'] == SmartAction::ACTION_SPAWN_SPAWNGROUP)
                $moreInfo[SmartAction::ACTION_SPAWN_SPAWNGROUP][] = $s['action_param1'];
            else if (in_array($s['action_type'], array_keys($lookup)))
            {
                foreach ($lookup[$s['action_type']] as $p)
                    $smartResults[] = $s['action_param'.$p];
            }
            else if ($s['action_type'] == SmartAction::ACTION_CALL_TIMED_ACTIONLIST)
                $smartTALs[] = $s['action_param1'];
            else if ($s['action_type'] == SmartAction::ACTION_CALL_RANDOM_TIMED_ACTIONLIST)
            {
                for ($i = 1; $i < 7; $i++)
                    if ($s['action_param'.$i])
                        $smartTALs[] = $s['action_param'.$i];
            }
            else if ($s['action_type'] == SmartAction::ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST)
            {
                for ($i = $s['action_param1']; $i <= $s['action_param2']; $i++)
                    $smartTALs[] = $i;
            }
        }

        if ($smartTALs)
        {
            if ($TALActList = DB::World()->select($actionQuery, self::SRC_TYPE_ACTIONLIST, array_keys($lookup), $smartTALs))
            {
                foreach ($TALActList as $e)
                {
                    foreach ($lookup[$e['action_type']] as $i)
                    {
                        if ($e['action_type'] == SmartAction::ACTION_SPAWN_SPAWNGROUP)
                            $moreInfo[SmartAction::ACTION_SPAWN_SPAWNGROUP][] = $e['action_param'.$i];
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

    private function &iterate() : \Generator
    {
        reset($this->rawData);

        foreach ($this->rawData as $k => $__)
        {
            $this->itr = &$this->rawData[$k];

            yield $this->itr;
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

        foreach ($this->iterate() as $id => $__)
        {
            $rowIdx = Util::createHash(8);

            if ($this->itr['action']->type == SmartAction::ACTION_TALK || $this->itr['action']->type == SmartAction::ACTION_SIMPLE_TALK)
                if ($ts = $this->itr['target']->getTalkSource())
                    $this->initQuotes($ts);

            [$evtBody, $evtFooter] = $this->itr['event']->process();
            [$actBody, $actFooter] = $this->itr['action']->process();

            $evtBody = str_replace(['#target#', '#rowIdx#'], [$this->itr['target']->process(), $rowIdx], $evtBody);
            $actBody = str_replace(['#target#', '#rowIdx#'], [$this->itr['target']->process(), $rowIdx], $actBody);

            if (!$this->itr['event']->hasPhases())
                $hidePhase = false;

            if ($this->itr['event']->chance != 100)
                $hideChance = false;

            $this->result[] = array(
                $this->itr['id'],
                implode(', ', Util::mask2bits($this->itr['event']->phaseMask, 1)),
                $evtBody.($evtFooter ? '[div float=right margin=0px clear=both][i][small class=q0]'.$evtFooter.'[/small][/i][/div]' : null),
                $this->itr['event']->chance.'%',
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

        if ($this->srcType == self::SRC_TYPE_ACTIONLIST)
            $this->tabs[$this->entry] = $tbl;
        else
            $this->tabs[0] = $tbl;

        return true;
    }

    public function getMarkup() : ?Markup
    {
        # id | event (footer phase) | chance | action + target

        if (!$this->rawData)
            return null;

        $wrapper = '[table class=grid width=940px]%s[/table]';
        $return  = '[style]#smartai-generic .grid { clear:left; } #smartai-generic .tabbed-contents { padding:0px; clear:left; }[/style][pad][h3][toggler id=sai]SmartAI'.$this->title.'[/toggler][/h3][div id=sai clear=left]%s[/div]';
        $tabs    = '';
        if (count($this->tabs) > 1)
        {
            $wrapper = '[tabs name=sai width=942px]%s[/tabs]';
            $return  = "[script]function TalTabClick(id) { $('#dsf67g4d-sai').find('[href=\'#sai-actionlist-' + id + '\']').click(); }[/script]" . $return;
            foreach ($this->tabs as $guid => $data)
            {
                $buff = '[tab name="'.($guid ? 'ActionList #'.$guid : 'Main').'"][table class=grid width=940px]'.$data.'[/table][/tab]';
                if ($guid)
                    $tabs .= $buff;
                else
                    $tabs = $buff . $tabs;
            }
        }

        return new Markup(sprintf($return, sprintf($wrapper, $tabs ?: $this->tabs[0])), ['allow' => Markup::CLASS_ADMIN], 'smartai-generic');
    }

    public function addJsGlobals(array $jsg) : void
    {
        Util::mergeJsGlobals($this->jsGlobals, $jsg);
    }

    public function getJSGlobals() : array
    {
        return $this->jsGlobals;
    }

    public function getTabs() : array
    {
        return $this->tabs;
    }

    public function addTab(int $guid, string $tt) : void
    {
        $this->tabs[$guid] = $tt;
    }

    public function getTarget(int $id = -1) : ?SmartTarget
    {
        if ($id < 0)
            return $this->itr['target'];

        return $this->rawData[$id]['target'] ?? null;
    }

    public function getAction(int $id = -1) : ?SmartAction
    {
        if ($id < 0)
            return $this->itr['action'];

        return $this->rawData[$id]['action'] ?? null;
    }

    public function getEvent(int $id = -1) : ?SmartEvent
    {
        if ($id < 0)
            return $this->itr['event'];

        return $this->rawData[$id]['event'] ?? null;
    }

    public function getEntry() : int
    {
        return $this->baseEntry ?: $this->entry;
    }

    private function initQuotes(int $creatureId) : void
    {
        if (isset($this->quotes[$creatureId]))
            return;

        [$quotes, , ] = Game::getQuotesForCreature($creatureId);

        $this->quotes[$creatureId] = $quotes;

        if (!empty($this->quotes[$creatureId]))
            $this->quotes[$creatureId]['src'] = CreatureList::getName($creatureId);
    }

    public function getQuote(int $creatureId, int $group, ?string &$npcSrc) : array
    {
        if (isset($this->quotes[$creatureId][$group]))
        {
            $npcSrc = $this->quotes[$creatureId]['src'];
            return $this->quotes[$creatureId][$group];
        }

        return [];
    }
}

?>
