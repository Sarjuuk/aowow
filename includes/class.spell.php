<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Spell
{

    public $effectNames = array(
        0 => 'None',
        1 => 'Instakill',
        2 => 'School Damage',
        3 => 'Dummy',
        4 => 'Portal Teleport',
        5 => 'Teleport Units',
        6 => 'Apply Aura',
        7 => 'Environmental Damage',
        8 => 'Power Drain',
        9 => 'Health Leech',
        10 => 'Heal',
        11 => 'Bind',
        12 => 'Portal',
        13 => 'Ritual Base',
        14 => 'Ritual Specialize',
        15 => 'Ritual Activate Portal',
        16 => 'Quest Complete',
        17 => 'Weapon Damage NoSchool',
        18 => 'Resurrect',
        19 => 'Add Extra Attacks',
        20 => 'Dodge',
        21 => 'Evade',
        22 => 'Parry',
        23 => 'Block',
        24 => 'Create Item',
        25 => 'Weapon',
        26 => 'Defense',
        27 => 'Persistent Area Aura',
        28 => 'Summon',
        29 => 'Leap',
        30 => 'Energize',
        31 => 'Weapon Percent Damage',
        32 => 'Trigger Missile',
        33 => 'Open Lock',
        34 => 'Summon Change Item',
        35 => 'Apply Area Aura Party',
        36 => 'Learn Spell',
        37 => 'Spell Defense',
        38 => 'Dispel',
        39 => 'Language',
        40 => 'Dual Wield',
        41 => 'Jump',
        42 => 'Jump Dest',
        43 => 'Teleport Units Face Caster',
        44 => 'Skill Step',
        45 => 'Add Honor',
        46 => 'Spawn',
        47 => 'Trade Skill',
        48 => 'Stealth',
        49 => 'Detect',
        50 => 'Trans Door',
        51 => 'Force Critical Hit',
        52 => 'Guarantee Hit',
        53 => 'Enchant Item Permanent',
        54 => 'Enchant Item Temporary',
        55 => 'Tame Creature',
        56 => 'Summon Pet',
        57 => 'Learn Pet Spell',
        58 => 'Weapon Damage',
        59 => 'Create Random Item',
        60 => 'Proficiency',
        61 => 'Send Event',
        62 => 'Power Burn',
        63 => 'Threat',
        64 => 'Trigger Spell',
        65 => 'Apply Area Aura Raid',
        66 => 'Create Mana Gem',
        67 => 'Heal Max Health',
        68 => 'Interrupt Cast',
        69 => 'Distract',
        70 => 'Pull',
        71 => 'Pickpocket',
        72 => 'Add Farsight',
        73 => 'Untrain Talents',
        74 => 'Apply Glyph',
        75 => 'Heal Mechanical',
        76 => 'Summon Object Wild',
        77 => 'Script Effect',
        78 => 'Attack',
        79 => 'Sanctuary',
        80 => 'Add Combo Points',
        81 => 'Create House',
        82 => 'Bind Sight',
        83 => 'Duel',
        84 => 'Stuck',
        85 => 'Summon Player',
        86 => 'Activate Object',
        87 => 'WMO Damage',
        88 => 'WMO Repair',
        89 => 'WMO Change',
        90 => 'Kill Credit',
        91 => 'Threat All',
        92 => 'Enchant Held Item',
        93 => 'Force Deselect',
        94 => 'Self Resurrect',
        95 => 'Skinning',
        96 => 'Charge',
        97 => 'Cast Button',
        98 => 'Knock Back',
        99 => 'Disenchant',
        100 => 'Inebriate',
        101 => 'Feed Pet',
        102 => 'Dismiss Pet',
        103 => 'Reputation',
        104 => 'Summon Object Slot1',
        105 => 'Summon Object Slot2',
        106 => 'Summon Object Slot3',
        107 => 'Summon Object Slot4',
        108 => 'Dispel Mechanic',
        109 => 'Summon Dead Pet',
        110 => 'Destroy All Totems',
        111 => 'Durability Damage',
        112 => 'Summon Demon',
        113 => 'Resurrect New',
        114 => 'Attack Me',
        115 => 'Durability Damage Percent',
        116 => 'Skin Player Corpse',
        117 => 'Spirit Heal',
        118 => 'Skill',
        119 => 'Apply Area Aura Pet',
        120 => 'Teleport Graveyard',
        121 => 'Normalized Weapon Dmg',
        122 => 'Unknown Effect',
        123 => 'Send Taxi',
        124 => 'Pull Towards',
        125 => 'Modify Threat Percent',
        126 => 'Steal Beneficial Buff',
        127 => 'Prospecting',
        128 => 'Apply Area Aura Friend',
        129 => 'Apply Area Aura Enemy',
        130 => 'Redirect Threat',
        131 => 'Unknown Effect',
        132 => 'Play Music',
        133 => 'Unlearn Specialization',
        134 => 'Kill Credit2',
        135 => 'Call Pet',
        136 => 'Heal Percent',
        137 => 'Energize Percent',
        138 => 'Leap Back',
        139 => 'Clear Quest',
        140 => 'Force Cast',
        141 => 'Force Cast With Value',
        142 => 'Trigger Spell With Value',
        143 => 'Apply Area Aura Owner',
        144 => 'Knock Back Dest',
        145 => 'Pull Towards Dest',
        146 => 'Activate Rune',
        147 => 'Quest Fail',
        148 => 'Unknown Effect',
        149 => 'Charge Dest',
        150 => 'Quest Start',
        151 => 'Trigger Spell 2',
        152 => 'Unknown Effect',
        153 => 'Create Tamed Pet',
        154 => 'Discover Taxi',
        155 => 'Titan Grip',
        156 => 'Enchant Item Prismatic',
        157 => 'Create Item 2',
        158 => 'Milling',
        159 => 'Allow Rename Pet',
        160 => 'Unknown Effect',
        161 => 'Talent Spec Count',
        162 => 'Talent Spec Select',
        163 => 'Unknown Effect',
        164 => 'Remove Aura'
    );

    public $auraNames = array(
        0 => 'None',
        1 => 'Bind Sight',
        2 => 'Mod Possess',
        3 => 'Periodic Damage',
        4 => 'Dummy',
        5 => 'Mod Confuse',
        6 => 'Mod Charm',
        7 => 'Mod Fear',
        8 => 'Periodic Heal',
        9 => 'Mod Attack Speed',
        10 => 'Mod Threat',
        11 => 'Taunt',
        12 => 'Stun',
        13 => 'Mod Damage Done',
        14 => 'Mod Damage Taken',
        15 => 'Damage Shield',
        16 => 'Mod Stealth',
        17 => 'Mod Stealth Detection',
        18 => 'Mod Invisibility',
        19 => 'Mod Invisibility Detection',
        20 => 'Obsolete Mod Health',
        21 => 'Obsolete Mod Power',
        22 => 'Mod Resistance',
        23 => 'Periodic Trigger Spell',
        24 => 'Periodic Energize',
        25 => 'Pacify',
        26 => 'Root',
        27 => 'Silence',
        28 => 'Reflect Spells',
        29 => 'Mod Stat',
        30 => 'Mod Skill',
        31 => 'Mod Increase Speed',
        32 => 'Mod Increase Mounted Speed',
        33 => 'Mod Decrease Speed',
        34 => 'Mod Increase Health',
        35 => 'Mod Increase Energy',
        36 => 'Shapeshift',
        37 => 'Effect Immunity',
        38 => 'State Immunity',
        39 => 'School Immunity',
        40 => 'Damage Immunity',
        41 => 'Dispel Immunity',
        42 => 'Proc Trigger Spell',
        43 => 'Proc Trigger Damage',
        44 => 'Track Creatures',
        45 => 'Track Resources',
        46 => 'Mod Parry Skill',
        47 => 'Mod Parry Percent',
        48 => 'Mod Dodge Skill',
        49 => 'Mod Dodge Percent',
        50 => 'Mod Critical Healing Amount',
        51 => 'Mod Block Percent',
        52 => 'Mod Weapon Crit Percent',
        53 => 'Periodic Leech',
        54 => 'Mod Hit Chance',
        55 => 'Mod Spell Hit Chance',
        56 => 'Transform',
        57 => 'Mod Spell Crit Chance',
        58 => 'Mod Increase Swim Speed',
        59 => 'Mod Damage Done Creature',
        60 => 'Pacify Silence',
        61 => 'Mod Scale',
        62 => 'Periodic Health Funnel',
        63 => 'Periodic Mana Funnel',
        64 => 'Periodic Mana Leech',
        65 => 'Mod Casting Speed (not stacking)',
        66 => 'Feign Death',
        67 => 'Disarm',
        68 => 'Stalked',
        69 => 'School Absorb',
        70 => 'Extra Attacks',
        71 => 'Mod Spell Crit Chance School',
        72 => 'Mod Power Cost School Percent',
        73 => 'Mod Power Cost School',
        74 => 'Reflect Spells School',
        75 => 'Language',
        76 => 'Far Sight',
        77 => 'Mechanic Immunity',
        78 => 'Mounted',
        79 => 'Mod Damage Percent Done',
        80 => 'Mod Percent Stat',
        81 => 'Split Damage Percent',
        82 => 'Water Breathing',
        83 => 'Mod Base Resistance',
        84 => 'Mod Health Regeneration',
        85 => 'Mod Power Regeneration',
        86 => 'Channel Death Item',
        87 => 'Mod Damage Percent Taken',
        88 => 'Mod Health Regeneration Percent',
        89 => 'Periodic Damage Percent',
        90 => 'Mod Resist Chance',
        91 => 'Mod Detect Range',
        92 => 'Prevent Fleeing',
        93 => 'Unattackable',
        94 => 'Interrupt Regeneration',
        95 => 'Ghost',
        96 => 'Spell Magnet',
        97 => 'Mana Shield',
        98 => 'Mod Skill Talent',
        99 => 'Mod Attack Power',
        100 => 'Auras Visible',
        101 => 'Mod Resistance Percent',
        102 => 'Mod Melee Attack Power Versus',
        103 => 'Mod Total Threat',
        104 => 'Water Walk',
        105 => 'Feather Fall',
        106 => 'Hover',
        107 => 'Add Flat Modifier',
        108 => 'Add Percent Modifier',
        109 => 'Add Target Trigger',
        110 => 'Mod Power Regeneration Percent',
        111 => 'Add Caster Hit Trigger',
        112 => 'Override Class Scripts',
        113 => 'Mod Ranged Damage Taken',
        114 => 'Mod Ranged Damage Taken Percent',
        115 => 'Mod Healing',
        116 => 'Mod Regeneration During Combat',
        117 => 'Mod Mechanic Resistance',
        118 => 'Mod Healing Percent',
        119 => 'Share Pet Tracking',
        120 => 'Untrackable',
        121 => 'Empathy',
        122 => 'Mod Offhand Damage Percent',
        123 => 'Mod Target Resistance',
        124 => 'Mod Ranged Attack Power',
        125 => 'Mod Melee Damage Taken',
        126 => 'Mod Melee Damage Taken Percent',
        127 => 'Ranged Attack Power Attacker Bonus',
        128 => 'Possess Pet',
        129 => 'Mod Speed Always',
        130 => 'Mod Mounted Speed Always',
        131 => 'Mod Ranged Attack Power Versus',
        132 => 'Mod Increase Energy Percent',
        133 => 'Mod Increase Health Percent',
        134 => 'Mod Mana Regeneration Interrupt',
        135 => 'Mod Healing Done',
        136 => 'Mod Healing Done Percent',
        137 => 'Mod Total Stat Percentage',
        138 => 'Mod Melee Haste',
        139 => 'Force Reaction',
        140 => 'Mod Ranged Haste',
        141 => 'Mod Ranged Ammo Haste',
        142 => 'Mod Base Resistance Percent',
        143 => 'Mod Resistance Exclusive',
        144 => 'Safe Fall',
        145 => 'Mod Pet Talent Points',
        146 => 'Allow Tame Pet Type',
        147 => 'Mechanic Immunity Mask',
        148 => 'Retain Combo Points',
        149 => 'Reduce Pushback',
        150 => 'Mod Shield Blockvalue Percent',
        151 => 'Track Stealthed',
        152 => 'Mod Detected Range',
        153 => 'Split Damage Flat',
        154 => 'Mod Stealth Level',
        155 => 'Mod Water Breathing',
        156 => 'Mod Reputation Gain',
        157 => 'Pet Damage Multi',
        158 => 'Mod Shield Blockvalue',
        159 => 'No PvP Credit',
        160 => 'Mod AoE Avoidance',
        161 => 'Mod Health Regeneration In Combat',
        162 => 'Power Burn Mana',
        163 => 'Mod Crit Damage Bonus',
        164 => 'Unknown Aura',
        165 => 'Melee Attack Power Attacker Bonus',
        166 => 'Mod Attack Power Percent',
        167 => 'Mod Ranged Attack Power Percent',
        168 => 'Mod Damage Done Versus',
        169 => 'Mod Crit Percent Versus',
        170 => 'Detect Amore',
        171 => 'Mod Speed (not stacking)',
        172 => 'Mod Mounted Speed (not stacking)',
        173 => 'Unknown Aura',
        174 => 'Mod Spell Damage Of Stat Percent',
        175 => 'Mod Spell Healing Of Stat Percent',
        176 => 'Spirit Of Redemption',
        177 => 'AoE Charm',
        178 => 'Mod Debuff Resistance',
        179 => 'Mod Attacker Spell Crit Chance',
        180 => 'Mod Flat Spell Damage Versus',
        181 => 'Unknown Aura',
        182 => 'Mod Resistance Of Stat Percent',
        183 => 'Mod Critical Threat',
        184 => 'Mod Attacker Melee Hit Chance',
        185 => 'Mod Attacker Ranged Hit Chance',
        186 => 'Mod Attacker Spell Hit Chance',
        187 => 'Mod Attacker Melee Crit Chance',
        188 => 'Mod Attacker Ranged Crit Chance',
        189 => 'Mod Rating',
        190 => 'Mod Faction Reputation Gain',
        191 => 'Use Normal Movement Speed',
        192 => 'Mod Melee Ranged Haste',
        193 => 'Melee Slow',
        194 => 'Mod Target Absorb School',
        195 => 'Mod Target Ability Absorb School',
        196 => 'Mod Cooldown',
        197 => 'Mod Attacker Spell And Weapon Crit Chance',
        198 => 'Unknown Aura',
        199 => 'Mod Increases Spell Percent to Hit',
        200 => 'Mod XP Percent',
        201 => 'Fly',
        202 => 'Ignore Combat Result',
        203 => 'Mod Attacker Melee Crit Damage',
        204 => 'Mod Attacker Ranged Crit Damage',
        205 => 'Mod School Crit Dmg Taken',
        206 => 'Mod Increase Vehicle Flight Speed',
        207 => 'Mod Increase Mounted Flight Speed',
        208 => 'Mod Increase Flight Speed',
        209 => 'Mod Mounted Flight Speed Always',
        210 => 'Mod Vehicle Speed Always',
        211 => 'Mod Flight Speed (not stacking)',
        212 => 'Mod Ranged Attack Power Of Stat Percent',
        213 => 'Mod Rage from Damage Dealt',
        214 => 'Unknown Aura',
        215 => 'Arena Preparation',
        216 => 'Haste Spells',
        217 => 'Unknown Aura',
        218 => 'Haste Ranged',
        219 => 'Mod Mana Regeneration from Stat',
        220 => 'Mod Rating from Stat',
        221 => 'Detaunt',
        222 => 'Unknown Aura',
        223 => 'Raid Proc from Charge',
        224 => 'Unknown Aura',
        225 => 'Raid Proc from Charge With Value',
        226 => 'Periodic Dummy',
        227 => 'Periodic Trigger Spell With Value',
        228 => 'Detect Stealth',
        229 => 'Mod AoE Damage Avoidance',
        230 => 'Unknown Aura',
        231 => 'Proc Trigger Spell With Value',
        232 => 'Mechanic Duration Mod',
        233 => 'Unknown Aura',
        234 => 'Mechanic Duration Mod (not stacking)',
        235 => 'Mod Dispel Resist',
        236 => 'Control Vehicle',
        237 => 'Mod Spell Damage Of Attack Power',
        238 => 'Mod Spell Healing Of Attack Power',
        239 => 'Mod Scale 2',
        240 => 'Mod Expertise',
        241 => 'Force Move Forward',
        242 => 'Mod Spell Damage from Healing',
        243 => 'Mod Faction',
        244 => 'Comprehend Language',
        245 => 'Mod Aura Duration By Dispel',
        246 => 'Mod Aura Duration By Dispel (not stacking)',
        247 => 'Clone Caster',
        248 => 'Mod Combat Result Chance',
        249 => 'Convert Rune',
        250 => 'Mod Increase Health 2',
        251 => 'Mod Enemy Dodge',
        252 => 'Mod Speed Slow All',
        253 => 'Mod Block Crit Chance',
        254 => 'Mod Disarm Offhand',
        255 => 'Mod Mechanic Damage Taken Percent',
        256 => 'No Reagent Use',
        257 => 'Mod Target Resist By Spell Class',
        258 => 'Unknown Aura',
        259 => 'Mod HoT Percent',
        260 => 'Screen Effect',
        261 => 'Phase',
        262 => 'Ability Ignore Aurastate',
        263 => 'Allow Only Ability',
        264 => 'Unknown Aura',
        265 => 'Unknown Aura',
        266 => 'Unknown Aura',
        267 => 'Mod Immune Aura Apply School',
        268 => 'Mod Attack Power Of Stat Percent',
        269 => 'Mod Ignore Target Resist',
        270 => 'Mod Ability Ignore Target Resist',
        271 => 'Mod Damage from Caster',
        272 => 'Ignore Melee Reset',
        273 => 'X Ray',
        274 => 'Ability Consume No Ammo',
        275 => 'Mod Ignore Shapeshift',
        276 => 'Unknown Aura',
        277 => 'Mod Max Affected Targets',
        278 => 'Mod Disarm Ranged',
        279 => 'Initialize Images',
        280 => 'Mod Armor Penetration Percent',
        281 => 'Mod Honor Gain Percent',
        282 => 'Mod Base Health Percent',
        283 => 'Mod Healing Received',
        284 => 'Linked',
        285 => 'Mod Attack Power Of Armor',
        286 => 'Ability Periodic Crit',
        287 => 'Deflect Spells',
        288 => 'Ignore Hit Direction',
        289 => 'Unknown Aura',
        290 => 'Mod Crit Percent',
        291 => 'Mod XP Quest Percent',
        292 => 'Open Stable',
        293 => 'Override Spells',
        294 => 'Prevent Power Regeneration',
        295 => 'Unknown Aura',
        296 => 'Set Vehicle Id',
        297 => 'Block Spell Family',
        298 => 'Strangulate',
        299 => 'Unknown Aura',
        300 => 'Share Damage Percent',
        301 => 'School Heal Absorb',
        302 => 'Unknown Aura',
        303 => 'Mod Damage Done Versus Aurastate',
        304 => 'Mod Fake Inebriate',
        305 => 'Mod Minimum Speed',
        306 => 'Unknown Aura',
        307 => 'Heal Absorb Test',
        308 => 'Unknown Aura',
        309 => 'Unknown Aura',
        310 => 'Mod Creature AoE Damage Avoidance',
        311 => 'Unknown Aura',
        312 => 'Unknown Aura',
        313 => 'Unknown Aura',
        314 => 'Prevent Ressurection',
        315 => 'Underwater Walking',
        316 => 'Periodic Haste'
    );

    public $template    = array();
    public $tooltip     = '';
    public $buff        = '';
    public $Id          = 0;

    public function __construct($data)
    {
        if (is_array($data))
            $this->template = $data;
        else
            $this->template = DB::Aowow()->SelectRow("SELECT * FROM ?_spell WHERE `id` = ?", intval($data));

        if (!$this->template)
            return false;

        $this->Id = $this->template['Id'];
    }

    // use if you JUST need the name
    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('
            SELECT
                name_loc0,
                name_loc2,
                name_loc3,
                name_loc6,
                name_loc8
            FROM
                ?_spell
            WHERE
                id = ?d',
            $id
        );
        return Util::localizedString($n, 'name');
    }
    // end static use

    // required for item-sets-bonuses and socket-bonuses
    public function getStatGain()
    {
        $stats = [];
        for ($i = 1; $i <= 3; $i++)
        {
            if (!in_array($this->template["effect".$i."AuraId"], [13, 22, 29, 34, 35, 83, 84, 85, 99, 124, 135, 143, 158, 161, 189, 230, 235, 240, 250]))
                continue;

            $mv = $this->template["effect".$i."MiscValue"];
            $bp = $this->template["effect".$i."BasePoints"] + 1;

            switch ($this->template["effect".$i."AuraId"])
            {
                case 29:                                    // ModStat MiscVal:type
                {
                    if ($mv < 0)                            // all stats
                    {
                        for ($j = 0; $j < 5; $j++)
                            @$stats[ITEM_MOD_AGILITY + $j] += $bp;
                    }
                    else                                    // one stat
                        @$stats[ITEM_MOD_AGILITY + $mv] += $bp;

                    break;
                }
                case 34:                                    // Increase Health
                case 230:
                case 250:
                {
                    @$stats[ITEM_MOD_HEALTH] += $bp;
                    break;
                }
                case 13:                                    // damage splpwr + physical (dmg & any)
                {
                    if ($mv == 1)                           // + weapon damage
                    {
                        @$stats[ITEM_MOD_WEAPON_DMG] += $bp;
                        break;
                    }

                    if ($mv == 0x7E)                        // full magic mask, also counts towards healing
                    {
                        @$stats[ITEM_MOD_SPELL_POWER] += $bp;
                        @$stats[ITEM_MOD_SPELL_DAMAGE_DONE] += $bp;
                    }
                    else
                    {
                        if ($mv & (1 << 1))                 // HolySpellpower (deprecated; still used in randomproperties)
                            @$stats[ITEM_MOD_HOLY_POWER] += $bp;

                        if ($mv & (1 << 2))                 // FireSpellpower (deprecated; still used in randomproperties)
                            @$stats[ITEM_MOD_FIRE_POWER] += $bp;

                        if ($mv & (1 << 3))                 // NatureSpellpower (deprecated; still used in randomproperties)
                            @$stats[ITEM_MOD_NATURE_POWER] += $bp;

                        if ($mv & (1 << 4))                 // FrostSpellpower (deprecated; still used in randomproperties)
                            @$stats[ITEM_MOD_FROST_POWER] += $bp;

                        if ($mv & (1 << 5))                 // ShadowSpellpower (deprecated; still used in randomproperties)
                            @$stats[ITEM_MOD_SHADOW_POWER] += $bp;

                        if ($mv & (1 << 6))                 // ArcaneSpellpower (deprecated; still used in randomproperties)
                            @$stats[ITEM_MOD_ARCANE_POWER] += $bp;
                    }

                    break;
                }
                case 135:                                   // healing splpwr (healing & any) .. not as a mask..
                {
                    @$stats[ITEM_MOD_SPELL_POWER] += $bp;
                    @$stats[ITEM_MOD_SPELL_HEALING_DONE] += $bp;

                    break;
                }
                case 35:                                    // ModPower - MiscVal:type see defined Powers only energy/mana in use
                {
                    if ($mv == -2)
                        @$stats[ITEM_MOD_HEALTH] += $bp;
                    if ($mv == 3)
                        @$stats[ITEM_MOD_ENERGY] += $bp;
                    else if ($mv == 0)
                        @$stats[ITEM_MOD_MANA] += $bp;
                    else if ($mv == 6)
                        @$stats[ITEM_MOD_RUNIC_POWER] += $bp;

                    break;
                }
                case 189:                                   // CombatRating MiscVal:ratingMask
                    // special case: resilience -  consists of 3 ratings strung together. MOD_CRIT_TAKEN_MELEE|RANGED|SPELL (14,15,16)
                    if (($mv & 0x1C000) == 0x1C000)
                        @$stats[ITEM_MOD_RESILIENCE_RATING] += $bp;

                    for ($j = 0; $j < count(Util::$combatRatingToItemMod); $j++)
                    {
                        if (!Util::$combatRatingToItemMod[$j])
                            continue;

                        if (($mv & (1 << $j)) == 0)
                            continue;

                        @$stats[Util::$combatRatingToItemMod[$j]] += $bp;
                    }
                    break;
                case 143:                                   // Resistance MiscVal:school
                case 83:
                case 22:

                    if ($mv == 1)                           // Armor only if explixitly specified
                    {
                        @$stats[ITEM_MOD_ARMOR] += $bp;
                        break;
                    }

                    if ($mv == 2)                           // holy-resistance ONLY if explicitly specified (shouldn't even exist...)
                    {
                        @$stats[ITEM_MOD_HOLY_RESISTANCE] += $bp;
                        break;
                    }

                    for ($j = 0; $j < 7; $j++)
                    {
                        if (($mv & (1 << $j)) == 0)
                            continue;

                        switch ($j)
                        {
                            case 2:
                                @$stats[ITEM_MOD_FIRE_RESISTANCE] += $bp;
                                break;
                            case 3:
                                @$stats[ITEM_MOD_NATURE_RESISTANCE] += $bp;
                                break;
                            case 4:
                                @$stats[ITEM_MOD_FROST_RESISTANCE] += $bp;
                                break;
                            case 5:
                                @$stats[ITEM_MOD_SHADOW_RESISTANCE] += $bp;
                                break;
                            case 6:
                                @$stats[ITEM_MOD_ARCANE_RESISTANCE] += $bp;
                                break;
                        }
                    }
                    break;
                case 84:                                    // hp5
                case 161:
                    @$stats[ITEM_MOD_HEALTH_REGEN] += $bp;
                    break;
                case 85:                                    // mp5
                    @$stats[ITEM_MOD_MANA_REGENERATION] += $bp;
                    break;
                case 99:                                    // atkpwr
                    @$stats[ITEM_MOD_ATTACK_POWER] += $bp;
                    break;                                  // ?carries over to rngatkpwr?
                case 124:                                   // rngatkpwr
                    @$stats[ITEM_MOD_RANGED_ATTACK_POWER] += $bp;
                    break;
                case 158:                                   // blockvalue
                    @$stats[ITEM_MOD_BLOCK_VALUE] += $bp;
                    break;
                case 240:                                   // ModExpertise
                    @$stats[ITEM_MOD_EXPERTISE_RATING] += $bp;
                    break;
            }
        }
        return $stats;
    }

    // TODO: optimize, complete, not go stark raving mad
    public function parseText($type = 'description', $level = MAX_LEVEL)
    {
        $lastduration = array('durationBase' => $this->template['duration']);

        $signs = array('+', '-', '/', '*', '%', '^');

        $data = Util::localizedString($this->template, $type);
        if (empty($data) || $data =="[]")                   // empty tooltip shouldn't be displayed anyway
            return null;

        // line endings
        $data = strtr($data, array("\r" => '', "\n" => '<br />'));

        // colors
        $data = preg_replace('/\|cff([a-f0-9]{6})(.+?)\|r/i', '<span style="color: #$1;">$2</span>', $data);

        $pos = 0;
        $str = '';
        while(false!==($npos=strpos($data, '$', $pos)))
        {
            if($npos!=$pos)
                $str .= substr($data, $pos, $npos-$pos);
            $pos = $npos+1;

            if('$' == substr($data, $pos, 1))
            {
                $str .= '$';
                $pos++;
                continue;
            }

            if(!preg_match('/^((([+\-\/*])(\d+);)?(\d*)(?:([lg].*?:.*?);|(\w\d*)))/', substr($data, $pos), $result))
                continue;

            if(empty($exprData[0]))
                $exprData[0] = 1;

            $op = $result[3];
            $oparg = $result[4];
            $lookup = $result[5];
            $var = $result[6] ? $result[6] : $result[7];
            $pos += strlen($result[0]);

            if(!$var)
                continue;

            $exprType = strtolower(substr($var, 0, 1));
            $exprData = explode(':', substr($var, 1));
            switch($exprType)
            {
                case 'r':
                    // if(!IsSet($this->template['rangeMaxHostile']))
                        // $this->template = array_merge($this->template, DB::Aowow()->selectRow('SELECT * FROM ?_spellrange WHERE rangeID=? LIMIT 1', $this->template['rangeID']));

                    $base = $this->template['rangeMaxHostile'];

                    if($op && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= $base;
                    break;
                case 'z':
                    $str .= htmlspecialchars('<Home>');
                    break;
                case 'c': ###
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect'.$exprData[0].'BasePoints, effect'.$exprData[0].'AuraId, effect'.$exprData[0].'MiscValue FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $base = $spell['effect'.$exprData[0].'BasePoints']+1;

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }

                    // Aura giving combat ratings
                    $rType = 0;
                    if ($spell['effect'.$exprData[0].'AuraId'] == 189)
                    {
                        $mv = $spell['effect'.$exprData[0].'MiscValue'];
                        for ($j = 0; $j < count(Util::$combatRatingToItemMod); $j++)
                        {
                            if (!Util::$combatRatingToItemMod[$j])
                                continue;

                            if (($mv & (1 << $j)) == 0)
                                continue;

                            $rType = Util::$combatRatingToItemMod[$j];
                            break;
                        }
                    }
                    // Aura end

                    if ($rType)
                        $str .= '<!--rtg'.$rType.'-->'.$base."&nbsp;<small>(".Util::setRatingLevel($level, $rType, $base).")</small>";
                    else
                        $str .= $base;

                    $lastvalue = $base;
                    break;
                case 's': ###
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect'.$exprData[0].'BasePoints, effect'.$exprData[0].'AuraId, effect'.$exprData[0].'MiscValue, effect'.$exprData[0].'DieSides FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    if(!$exprData[0]) $exprData[0]=1;
                        @$base = $spell['effect'.$exprData[0].'BasePoints']+1;

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }

                    // Aura giving combat ratings
                    $rType = 0;
                    if (@$spell['effect'.$exprData[0].'AuraId'] == 189)
                    {
                        $mv = $spell['effect'.$exprData[0].'MiscValue'];
                        for ($j = 0; $j < count(Util::$combatRatingToItemMod); $j++)
                        {
                            if (!Util::$combatRatingToItemMod[$j])
                                continue;

                            if (($mv & (1 << $j)) == 0)
                                continue;

                            $rType = Util::$combatRatingToItemMod[$j];
                            break;
                        }
                    }
                    // Aura end

                    if ($rType && $spell['effect'.$exprData[0].'DieSides'] <= 1)
                        $str .= '<!--rtg'.$rType.'-->'.abs($base)."&nbsp;<small>(".Util::setRatingLevel($level, $rType, abs($base)).")</small>";
                    else
                        @$str .= abs($base).($spell['effect'.$exprData[0].'DieSides'] > 1 ? Lang::$game['valueDelim'].abs(($base+$spell['effect'.$exprData[0].'DieSides'])) : '');

                    $lastvalue = $base;
                    break;
                case 'o':
                    if($lookup > 0 && $exprData[0])
                    {
                        $spell = DB::Aowow()->selectRow('SELECT duration, effect'.$exprData[0].'BasePoints, effect'.$exprData[0].'Periode, effect'.$exprData[0].'DieSides FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                        // $lastduration = DB::Aowow()->selectRow('SELECT * FROM ?_spellduration WHERE durationID=? LIMIT 1', $spell['durationID']);
                    }
                    else
                        $spell = $this->template;

                    if(!$exprData[0]) $exprData[0] = 1;
                    $base = $spell['effect'.$exprData[0].'BasePoints']+1;

                    if($spell['effect'.$exprData[0].'Periode'] <= 0) $spell['effect'.$exprData[0].'Periode'] = 5000;

                    $str .= (($spell['duration'] / $spell['effect'.$exprData[0].'Periode']) * abs($base).($spell['effect'.$exprData[0].'DieSides'] > 1 ? '-'.abs(($base+$spell['effect'.$exprData[0].'DieSides'])) : ''));
                    break;
                case 't':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT * FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    if(!$exprData[0]) $exprData[0]=1;
                        $base = $spell['effect'.$exprData[0].'Periode']/1000;

                    // TODO!!
                    if($base==0)    $base=1;
                    // !!TODO

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    $lastvalue = $base;
                    break;
                case 'm': ###
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect'.$exprData[0].'BasePoints, effect'.$exprData[0].'AuraId, effect'.$exprData[0].'MiscValue FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    // TODO!!
                    if(!$exprData[0]) $exprData[0] = 1;

                    $base = $spell['effect'.$exprData[0].'BasePoints']+1;

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }

                    // Aura giving combat ratings
                    $rType = 0;
                    if ($spell['effect'.$exprData[0].'AuraId'] == 189)
                    {
                        $mv = $spell['effect'.$exprData[0].'MiscValue'];
                        for ($j = 0; $j < count(Util::$combatRatingToItemMod); $j++)
                        {
                            if (!Util::$combatRatingToItemMod[$j])
                                continue;

                            if (($mv & (1 << $j)) == 0)
                                continue;

                            $rType = Util::$combatRatingToItemMod[$j];
                            break;
                        }
                    }
                    // Aura end

                    if ($rType)
                        $str .= '<!--rtg'.$rType.'-->'.abs($base)."&nbsp;<small>(".Util::setRatingLevel($level, $rType, abs($base)).")</small>";
                    else
                        $str .= abs($base);

                    $lastvalue = $base;
                    break;
                case 'x':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect'.$exprData[0].'ChainTarget FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $base = $spell['effect'.$exprData[0].'ChainTarget'];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    $lastvalue = $base;
                    break;
                case 'q':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect'.$exprData[0].'MiscValue FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    if(!($exprData[0]))
                        $exprData[0]=1;
                    $base = $spell['effect'.$exprData[0].'MiscValue'];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    $lastvalue = $base;
                    break;
                case 'a':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect1RadiusMax, effect2RadiusMax, effect3RadiusMax FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $exprData[0] = 1; // TODO
                    $radius = $this->template['effect'.$exprData[0].'RadiusMax'];
                    $base = $radius;

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    break;
                case 'h':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT procChance FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $base = $spell['procChance'];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    break;
                case 'f':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT dmg_multiplier'.$exprData[0].' FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $base = $spell['dmg_multiplier'.$exprData[0]];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    break;
                case 'n':
                    if($lookup > 0)
                        $spell = DB::Aowow()->selectRow('SELECT procCharges FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $base = $spell['procCharges'];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    break;
                case 'd':
                    if($lookup > 0)
                    {
                        $spell = DB::Aowow()->selectRow('SELECT duration as durationBase FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                        @$base = ($spell['durationBase'] > 0 ? $spell['durationBase'] + 1 : 0);
                    }
                    else
                        $base = ($lastduration['durationBase'] > 0 ? $lastduration['durationBase'] : 0);

                    if($op && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= Util::formatTime($base, true);
                    break;
                case 'i':
                    // $base = $this->template['spellTargets'];
                    $base = $this->template['targets'];

                    if($op && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= $base;
                    break;
                case 'e':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect_'.$exprData[0].'_proc_value FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $base = $spell['effect_'.$exprData[0].'_proc_value'];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }

                    $str .= $base;
                    $lastvalue = $base;
                    break;
                case 'v':
                    $base = $spell['affected_target_level'];

                    if($op && $oparg > 0 && $base > 0)
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= $base;
                    break;
                case 'u':
                    if($lookup > 0 && $exprData[0])
                        $spell = DB::Aowow()->selectRow('SELECT effect'.$exprData[0].'MiscValue FROM ?_spell WHERE id=?d LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    // $base = $spell['effect_'.$exprData[0].'_misc_value'];
                    if(isset($spell['effect'.$exprData[0].'MiscValue']))
                        $base = $spell['effect'.$exprData[0].'MiscValue'];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    $lastvalue = $base;
                    break;
                case 'b': // only used at one spell (14179) should be 20, column 110/111/112?)
                    if($lookup > 0)
                        $spell = DB::Aowow()->selectRow('SELECT effect_'.$exprData[0].'_proc_chance FROM ?_spell WHERE id=? LIMIT 1', $lookup);
                    else
                        $spell = $this->template;

                    $base = $spell['effect'.$exprData[0].'PointsPerComboPoint'];

                    if(in_array($op, $signs) && is_numeric($oparg) && is_numeric($base))
                    {
                        $equation = $base.$op.$oparg;
                        eval("\$base = $equation;");
                    }
                    $str .= abs($base);
                    $lastvalue = $base;
                    break;
                case 'l':
                    if($lastvalue > 1)
                        $str .= $exprData[1];
                    else
                        $str .= $exprData[0];
                    break;
                case 'g':
                    $str .= $exprData[0];
                    break;
                default:
                    $str .= "[{$var} ($op::$oparg::$lookup::$exprData[0])]";
            }
        }
        $str .= substr($data, $pos);
        $str = @preg_replace_callback("|\{([^\}]+)\}|", create_function('$matches', 'return eval("return abs(".$matches[1].");");'), $str);

        return $str;
    }

    public function getBuff()
    {
        // doesn't have a buff
        if (!Util::localizedString($this->template, 'buff'))
            return false;

        $x = '<table><tr>';

        // spellName
        $x .= '<td><b class="q">'.Util::localizedString($this->template, 'name').'</b></td>';

        // dispelType (if applicable)
        if ($dispel = Lang::$game['di'][$this->template['dispelType']])
            $x .= '<th><b class="q">'.$dispel.'</b></th>';

        $x .= '</tr></table>';

        $x .= '<table><tr><td>';

        // parse Buff-Text
        $x .= $this->parseText('buff').'<br>';

        // duration
        if ($this->template['duration'])
            $x .= '<span class="q">'.sprintf(Lang::$spell['remaining'], Util::formatTime($this->template['duration'])).'<span>';

        $x .= '</td></tr></table>';

        $this->buff = $x;

        return true;
    }

    public function getTooltip()
    {
        // get reagents
        $reagents = array();
        for ($j = 1; $j <= 8; $j++)
        {
            if($this->template['reagent'.$j] <= 0)
                continue;

            $reagents[] = array(
                'id' => $this->template['reagent'.$j],
                'name' => Item::getName($this->template['reagent'.$j]),
                'count' => $this->template['reagentCount'.$j]          // if < 0 : require, but don't use
            );
        }
        $reagents = array_reverse($reagents);

        // get tools
        $tools = array();
        for ($i = 1; $i <= 2; $i++)
        {
            // Tools
            if ($this->template['tool'.$i])
                $tools[$i-1] = array('itemId' => $this->template['tool'.$i], 'name' => Item::getName($this->template['tool'.$i]));

            // TotemCategory
            if ($this->template['toolCategory'.$i])
            {
                $tc = DB::Aowow()->selectRow('SELECT * FROM aowow_totemcategory WHERE id = ?d', $this->template['toolCategory'.$i]);
                $tools[$i+1] = array('categoryMask' => $tc['categoryMask'], 'name' => Util::localizedString($tc, 'name'));
            }
        }
        $tools = array_reverse($tools);

        // get description
        $desc = $this->parseText('description');

        $reqWrapper = $this->template['rangeMaxHostile'] && ($this->template['powerCost'] > 0 || $this->template['powerCostPercent'] > 0);
        $reqWrapper2 = $reagents ||$tools || $desc;

        $x = '';
        $x .= '<table><tr><td>';

        $rankText = Util::localizedString($this->template, 'rank');

        if (!empty($rankText))
            $x .= '<table width="100%"><tr><td>';

        // name
        $x .= '<b>'.Util::localizedString($this->template, 'name').'</b>';

        // rank
        if (!empty($rankText))
            $x .= '<br /></td><th><b class="q0">'.$rankText.'</b></th></tr></table>';


        if ($reqWrapper)
            $x .= '<table width="100%"><tr><td>';

        // check for custom PowerDisplay
        $pt = $this->template['powerDisplayString'] ? $this->template['powerDisplayString'] : $this->template['powerType'];

        // power cost: pct over static
        if ($this->template['powerCostPercent'] > 0)
            $x .= $this->template['powerCostPercent']."% ".sprintf(Lang::$spell['pctCostOf'], strtolower(Lang::$spell['powerTypes'][$pt]));
        else if ($this->template['powerCost'] > 0 || $this->template['powerPerSecond'] > 0 || $this->template['powerCostPerLevel'] > 0)
            $x .= ($pt == 1 ? $this->template['powerCost'] / 10 : $this->template['powerCost']).' '.ucFirst(Lang::$spell['powerTypes'][$pt]);

        // append periodic cost
        if ($this->template['powerPerSecond'] > 0)
            $x .= sprintf(Lang::$spell['costPerSec'], $this->template['powerPerSecond']);

        // append level cost
        if ($this->template['powerCostPerLevel'] > 0)
            $x .= sprintf(Lang::$spell['costPerLevel'], $this->template['powerCostPerLevel']);

        $x .= '<br />';

        if ($reqWrapper)
            $x .= '</td><th>';

        // ranges
        if ($this->template['rangeMaxHostile'])
        {
            // minRange exists; show as range
            if ($this->template['rangeMinHostile'])
                $x .= sprintf(Lang::$spell['range'], $this->template['rangeMinHostile'].' - '.$this->template['rangeMaxHostile']).'<br />';
            // friend and hostile differ; do color
            else if ($this->template['rangeMaxHostile'] != $this->template['rangeMaxFriend'])
                $x .= sprintf(Lang::$spell['range'], '<span class="q10">'.$this->template['rangeMaxHostile'].'</span> - <span class="q2">'.$this->template['rangeMaxHostile']. '</span>').'<br />';
            // hardcode: "melee range"
            else if ($this->template['rangeMaxHostile'] == 5)
                $x .= Lang::$spell['meleeRange'].'<br />';
            // regular case
            else
                $x .= sprintf(Lang::$spell['range'], $this->template['rangeMaxHostile']).'<br />';
        }

        if ($reqWrapper)
            $x .= '</th></tr></table>';

        $x .= '<table width="100%"><tr><td>';

        // cast times
        if ($this->template['interruptFlagsChannel'])
            $x .= Lang::$spell['channeled'];
        else if ($this->template['castTime'])
            $x .= sprintf(Lang::$spell['castIn'], $this->template['castTime'] / 1000);
        else if ($this->template['attributes0'] & 0x10)     // SPELL_ATTR0_ABILITY instant ability.. yeah, wording thing only
            $x .= Lang::$spell['instantPhys'];
        else                                                // instant cast
            $x .= Lang::$spell['instantMagic'];

        $x .= '</td>';

        // cooldown or categorycooldown
        if ($this->template['recoveryTime'])
            $x.= '<th>'.sprintf(Lang::$game['cooldown'], Util::formatTime($this->template['recoveryTime'], true)).'</th>';
        else if ($this->template['recoveryCategory'])
            $x.= '<th>'.sprintf(Lang::$game['cooldown'], Util::formatTime($this->template['recoveryCategory'], true)).'</th>';

        $x .= '</tr>';

        if ($this->template['stanceMask'])
            $x.= '<tr><td colspan="2">'.Lang::$game['requires'].' '.Lang::getStances($this->template['stanceMask']).'</td></tr>';

        $x .= '</table>';
        $x .= '</td></tr></table>';

        if ($reqWrapper2)
            $x .= '<table>';

        if ($tools)
        {
            $x .= '<tr><td>';
            $x .= Lang::$spell['tools'].':<br/><div class="indent q1">';
            while ($tool = array_pop($tools))
            {
                if (isset($tool['itemId']))
                    $x .= '<a href="?item='.$tool['itemId'].'">'.$tool['name'].'</a>';
                else if (isset($tool['totemCategory']))
                    $x .= '<a href="?items&filter=cr=91;crs='.$tool['totemCategory'].';crv=0=">'.$tool['name'].'</a>';
                else
                    $x .= $tool['name'];

                if (!empty($tools))
                    $x .= ', ';
                else
                    $x .= '<br />';
            }
            $x .= '</div></td></tr>';
        }

        if ($reagents)
        {
            $x .= '<tr><td>';
            $x .= Lang::$spell['reagents'].':<br/><div class="indent q1">';
            while ($reagent = array_pop($reagents))
            {
                $x .= '<a href="?item='.$reagent['id'].'">'.$reagent['name'].'</a>';
                if ($reagent['count'] > 1)
                    $x .= ' ('.$reagent['count'].')';

                if(!empty($reagents))
                    $x .= ', ';
                else
                    $x .= '<br />';
            }
            $x .= '</div></td></tr>';
        }

        if($desc && $desc <> '_empty_')
            $x .= '<tr><td><span class="q">'.$desc.'</span></td></tr>';

        if ($reqWrapper2)
            $x .= "</table>";

        $this->tooltip = $x;

        return true;   // todo: false if error
    }

    public function getTalentHead()
    {
        // upper: cost :: range
        // lower: time :: cool
        $x = '';

        // power cost: pct over static
        $cost = '';

        if ($this->template['powerCostPercent'] > 0)
            $cost .= $this->template['powerCostPercent']."% ".sprintf(Lang::$spell['pctCostOf'], strtolower(Lang::$spell['powerTypes'][$this->template['powerType']]));
        else if ($this->template['powerCost'] > 0 || $this->template['powerPerSecond'] > 0 || $this->template['powerCostPerLevel'] > 0)
            $cost .= ($this->template['powerType'] == 1 ? $this->template['powerCost'] / 10 : $this->template['powerCost']).' '.ucFirst(Lang::$spell['powerTypes'][$this->template['powerType']]);

        // append periodic cost
        if ($this->template['powerPerSecond'] > 0)
            $cost .= sprintf(Lang::$spell['costPerSec'], $this->template['powerPerSecond']);

        // append level cost
        if ($this->template['powerCostPerLevel'] > 0)
            $cost .= sprintf(Lang::$spell['costPerLevel'], $this->template['powerCostPerLevel']);


        // ranges
        $range = '';

        if ($this->template['rangeMaxHostile'])
        {
            // minRange exists; show as range
            if ($this->template['rangeMinHostile'])
                $range .= sprintf(Lang::$spell['range'], $this->template['rangeMinHostile'].' - '.$this->template['rangeMaxHostile']);
            // friend and hostile differ; do color
            else if ($this->template['rangeMaxHostile'] != $this->template['rangeMaxFriend'])
                $range .= sprintf(Lang::$spell['range'], '<span class="q10">'.$this->template['rangeMaxHostile'].'</span> - <span class="q2">'.$this->template['rangeMaxHostile']. '</span>');
            // hardcode: "melee range"
            else if ($this->template['rangeMaxHostile'] == 5)
                $range .= Lang::$spell['meleeRange'];
            // regular case
            else
                $range .= sprintf(Lang::$spell['range'], $this->template['rangeMaxHostile']);
        }

        // cast times
        $time = '';

        if ($this->template['interruptFlagsChannel'])
            $time .= Lang::$spell['channeled'];
        else if ($this->template['castTime'])
            $time .= sprintf(Lang::$spell['castIn'], $this->template['castTime'] / 1000);
        else if ($this->template['attributes0'] & 0x10)     // SPELL_ATTR0_ABILITY instant ability.. yeah, wording thing only
            $time .= Lang::$spell['instantPhys'];
        else                                                // instant cast
            $time .= Lang::$spell['instantMagic'];

        // cooldown or categorycooldown
        $cool = '';

        if ($this->template['recoveryTime'])
            $cool.= sprintf(Lang::$game['cooldown'], Util::formatTime($this->template['recoveryTime'], true)).'</th>';
        else if ($this->template['recoveryCategory'])
            $cool.= sprintf(Lang::$game['cooldown'], Util::formatTime($this->template['recoveryCategory'], true)).'</th>';


        // assemble parts

        // upper
        if ($cost && $range)
            $x .= '<table width="100%"><tr><td>'.$cost.'</td><th>'.$range.'</th></tr></table>';
        else if ($cost)
            $x .= $cost;
        else if ($range)
            $x .= $range;

        if (($cost xor $range) && ($time xor $cool))
            $x .= '<br />';

        // lower
        if ($time && $cool)
            $x .= '<table width="100%"><tr><td>'.$time.'</td><th>'.$cool.'</th></tr></table>';
        else if ($time)
            $x .= $time;
        else if ($cool)
            $x .= $cool;

        return $x;
    }


    public function getListviewData()
    {
        return array(
            'id'       => $this->Id,
            'name'     => Util::localizedString($this->template, 'name'),
        );
    }

    public function getDetailedData()
    {
       return array(
            'id'       => $this->Id,
            'name'     => Util::localizedString($this->template, 'name'),
            'iconname' => $this->template['iconString'],
        );
    }

    public function addSelfToJScript(&$gSpells)
    {
        // todo: if the spell creates an item use the itemIcon instead
        // ...
        // FU!
        if ($this->template['effect1CreateItemId'])
            $iconString = DB::Aowow()->SelectCell('SELECT icon FROM ?_icons, item_template WHERE id = displayid AND entry = ?d LIMIT 1',
                $this->template['effect1CreateItemId']
            );
        else
            $iconString = $this->template['iconString'];

        $gSpells[$this->Id] = array(
            'icon' => $iconString,      // should be: $this->template['icon'],
            'name' => Util::localizedString($this->template, 'name'),
        );
    }
}



class SpellList
{

    public $spellList = array();
    public $filter = NULL;

    public function __construct($conditions)
    {
        // may be called without filtering
        if (class_exists('SpellFilter'))
        {
            $this->filter = new SpellFilter();
            if (($fiData = $this->filter->init()) === false)
                return;
        }

        $cnd = array();
        foreach ($conditions as $cond)
        {
            if ($cond[1] == 'IN' && is_array($cond[2]))
                $cnd[] = Util::sqlEscape($cond[0]).' IN ('.implode(',', Util::sqlEscape($cond[2])).')';
            else
                $cnd[] = Util::sqlEscape($cond[0]).' '.Util::sqlEscape($cond[1]).(is_string($cond[2]) ? ' "'.Util::sqlEscape($cond[2]).'"' : ' '.intval($cond[2]));
        }
        $filterQuery = $this->filter ? $this->filter->buildFilterQuery() : NULL;   // todo: add strings propperly without them being escaped by simpleDB..?
        $rows = DB::Aowow()->Select('SELECT * FROM ?_spell WHERE '.($filterQuery ? $filterQuery.' AND' : NULL).' '.(!empty($cnd) ? '('.implode(' AND ', $cnd).')' : '1').'
            GROUP BY id'
            //$this->filter->buildFilterQuery() ? $this->filter->query : DBSIMPLE_SKIP
        );

        foreach ($rows as $row)
        {
            $spl = new Spell($row);
            $this->spellList[] = $spl;
        }
    }

    public function getListviewData()
    {
        $tmp = array();
        // no extra queries required, just call recursively
        foreach ($this->spellList as $spl)
            $tmp[] = $spl->getListviewData();

        return $tmp;
    }

    public function addSelfToJScript(&$gAchievements)
    {
        // no extra queries required, just call recursively
        foreach ($this->spellList as $spl)
            $spl->addSelfToJScript($gAchievements);
    }
}

?>
