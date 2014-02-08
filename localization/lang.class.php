<?php

class Lang
{
    public static $timeUnits;
    public static $main;
    public static $account;
    public static $game;
    public static $error;

    public static $search;
    public static $profiler;

    public static $achievement;
    public static $class;
    public static $currency;
    public static $event;
    public static $faction;
    public static $item;
    public static $itemset;
    public static $maps;
    public static $npc;
    public static $pet;
    public static $quest;
    public static $skill;
    public static $spell;
    public static $title;
    public static $zone;

    public static $colon;
    public static $dateFmtLong;
    public static $dateFmtShort;

    public static function load($loc)
    {
        if (!file_exists('localization/locale_'.$loc.'.php'))
            die('File for localization '.strToUpper($loc).' not found.');
        else
            require 'localization/locale_'.$loc.'.php';

        foreach ($lang as $k => $v)
            self::$$k = $v;

        // *cough* .. reuse-hack
        self::$item['cat'][2] = [self::$item['cat'][2], self::$spell['weaponSubClass']];
        self::$item['cat'][2][1][14] .= ' ('.self::$item['cat'][2][0].')';
    }

    // todo: expand
    public static function getInfoBoxForFlags($flags)
    {
        $tmp = [];

        if ($flags & CUSTOM_DISABLED)
            $tmp[] = '[tooltip name=disabledHint]'.Util::jsEscape(self::$main['disabledHint']).'[/tooltip][span class=tip tooltip=disabledHint]'.Util::jsEscape(self::$main['disabled']).'[/span]';

        if ($flags & CUSTOM_SERVERSIDE)
            $tmp[] = '[tooltip name=serversideHint]'.Util::jsEscape(self::$main['serversideHint']).'[/tooltip][span class=tip tooltip=serversideHint]'.Util::jsEscape(self::$main['serverside']).'[/span]';

        if ($flags & CUSTOM_UNAVAILABLE)
            $tmp[] = self::$main['unavailable'];

        return $tmp;
    }

    public static function getLocks($lockId, $interactive = false)
    {
        $locks = [];
        $lock  = DB::Aowow()->selectRow('SELECT * FROM ?_lock WHERE id = ?d', $lockId);
        if (!$lock)
            return '';

        for ($i = 1; $i <= 5; $i++)
        {
            $prop = $lock['properties'.$i];
            $rank = $lock['reqSkill'.$i];
            $name = '';

            if ($lock['type'.$i] == 1)                      // opened by item
            {
                $name = ItemList::getName($prop);
                if (!$name)
                    continue;

                if ($interactive)
                    $name = '<a class="q1" href="?item='.$prop.'">'.$name.'</a>';
            }
            else if ($lock['type'.$i] == 2)                 // opened by skill
            {
                // exclude unusual stuff
                if (!in_array($prop, [1, 2, 3, 4, 9, 16, 20]))
                    continue;

                $txt = DB::Aowow()->selectRow('SELECT * FROM ?_locktype WHERE id = ?d', $prop);         // todo (low): convert to static text
                $name = Util::localizedString($txt, 'name');
                if (!$name)
                    continue;

                if ($interactive)
                {
                    $skill = 0;
                    switch ($prop)
                    {
                        case  1: $skill = 633; break;   // Lockpicking
                        case  2: $skill = 182; break;   // Herbing
                        case  3: $skill = 186; break;   // Mining
                        case 20: $skill = 773; break;   // Scribing
                    }

                    if ($skill)
                        $name = '<a href="?skill='.$skill.'">'.$name.'</a>';
                }

                if ($rank > 0)
                    $name .= ' ('.$rank.')';
            }
            else
                continue;

            $locks[$lock['type'.$i] == 1 ? $i : -$i] = sprintf(Lang::$game['requires'], $name);
        }

        return $locks;
    }

    public static function getReputationLevelForPoints($pts)
    {
        if ($pts >= 41999)
            return self::$game['rep'][REP_EXALTED];
        else if ($pts >= 20999)
            return self::$game['rep'][REP_REVERED];
        else if ($pts >= 8999)
            return self::$game['rep'][REP_HONORED];
        else if ($pts >= 2999)
            return self::$game['rep'][REP_FRIENDLY];
        else /* if ($pts >= 1) */
            return self::$game['rep'][REP_NEUTRAL];
    }

    public static function getRequiredItems($class, $mask, $short = true)
    {
        if (!in_array($class, [ITEM_CLASS_MISC, ITEM_CLASS_ARMOR, ITEM_CLASS_WEAPON]))
            return '';

        // not checking weapon / armor here. It's highly unlikely that they overlap
        if ($short)
        {
            // misc - Mounts
            if ($class == ITEM_CLASS_MISC)
                return '';

            // all basic armor classes
            if ($class == ITEM_CLASS_ARMOR && ($mask & 0x1E) == 0x1E)
                return '';

            // all weapon classes
            if ($class == ITEM_CLASS_WEAPON && ($mask & 0x1DE5FF) == 0x1DE5FF)
                return '';

            foreach (Lang::$spell['subClassMasks'] as $m => $str)
                if ($mask == $m)
                    return $str;
        }

        if ($class == ITEM_CLASS_MISC)                      // yeah hardcoded.. sue me!
            return Lang::$spell['cat'][-5];

        $tmp  = [];
        $strs = Lang::$spell[$class == ITEM_CLASS_ARMOR ? 'armorSubClass' : 'weaponSubClass'];
        foreach ($strs as $k => $str)
            if ($mask & (1 << $k) && $str)
                $tmp[] = $str;

        return implode(', ', $tmp);
    }

    public static function getStances($stanceMask)
    {
        $stanceMask &= 0xFC27909F;                          // clamp to available stances/forms..

        $tmp = [];
        $i   = 1;

        while ($stanceMask)
        {
            if ($stanceMask & (1 << ($i - 1)))
            {
                $tmp[] = self::$game['st'][$i];
                $stanceMask &= ~(1 << ($i - 1));
            }
            $i++;
        }

        return implode(', ', $tmp);
    }

    public static function getMagicSchools($schoolMask)
    {
        $schoolMask &= SPELL_ALL_SCHOOLS;                   // clamp to available schools..
        $tmp = [];
        $i   = 0;

        while ($schoolMask)
        {
            if ($schoolMask & (1 << $i))
            {
                $tmp[] = self::$game['sc'][$i];
                $schoolMask &= ~(1 << $i);
            }
            $i++;
        }

        return implode(', ', $tmp);
    }

    public static function getClassString($classMask)
    {
        $classMask &= CLASS_MASK_ALL;                       // clamp to available classes..

        if ($classMask == CLASS_MASK_ALL)                   // available to all classes
            return false;

        $tmp = [];
        $i   = 1;

        while ($classMask)
        {
            if ($classMask & (1 << ($i - 1)))
            {
                $tmp[] = '<a href="?class='.$i.'" class="c'.$i.'">'.self::$game['cl'][$i].'</a>';
                $classMask &= ~(1 << ($i - 1));
            }
            $i++;
        }

        return implode(', ', $tmp);
    }

    public static function getRaceString($raceMask)
    {
        $raceMask &= RACE_MASK_ALL;                         // clamp to available races..

        if ($raceMask == RACE_MASK_ALL)                     // available to all races (we don't display 'both factions')
            return false;

        $tmp  = [];
        $side = 0;
        $i    = 1;

        if (!$raceMask)
            return array('side' => SIDE_BOTH,     'name' => self::$game['ra'][0]);

        if ($raceMask == RACE_MASK_HORDE)
            return array('side' => SIDE_HORDE,    'name' => self::$game['ra'][-2]);

        if ($raceMask == RACE_MASK_ALLIANCE)
            return array('side' => SIDE_ALLIANCE, 'name' => self::$game['ra'][-1]);

        if ($raceMask & RACE_MASK_HORDE)
            $side |= SIDE_HORDE;

        if ($raceMask & RACE_MASK_ALLIANCE)
            $side |= SIDE_ALLIANCE;

        while ($raceMask)
        {
            if ($raceMask & (1 << ($i - 1)))
            {
                $tmp[] = '<a href="?race='.$i.'" class="q1">'.self::$game['ra'][$i].'</a>';
                $raceMask &= ~(1 << ($i - 1));
            }
            $i++;
        }

        return array ('side' => $side, 'name' => implode(', ', $tmp));
    }
}

?>