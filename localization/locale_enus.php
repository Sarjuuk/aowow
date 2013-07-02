<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$lang = array(
    // page variables
    'main' => array(
        'name'          => "Name",
        'link'          => "Link",
        'signIn'        => "Sign in",
        'jsError'       => "Please make sure you have javascript enabled.",
        'searchButton'  => "Search",
        'language'      => "Language",
        'numSQL'        => "Number of MySQL queries",
        'timeSQL'       => "Time of MySQL queries",
        'noJScript'     => "<b>This site makes extensive use of JavaScript.</b><br />Please <a href=\"https://www.google.com/support/adsense/bin/answer.py?answer=12654\" target=\"_blank\">enable JavaScript</a> in your browser.",
        'profiles'      => "Your Characters",
        'pageNotFound'  => "This %s doesn't exist.",
        'gender'        => "Gender",
        'sex'           => [null, 'Male', 'Female'],
        'quickFacts'    => "Quick Facts",
        'screenshots'   => "Screenshots",
        'videos'        => "Videos",
        'side'          => "Side",
        'related'       => "Related",
        'contribute'    => "Contribute",
        // 'replyingTo'    => "The answer to a comment from",
        'submit'        => "Submit",
        'cancel'        => "Cancel",
        'rewards'       => "Rewards",
        'gains'         => "Gains",
        'login'         => "Login",
        'forum'         => "Forum",
        'days'          => "days",
        'hours'         => "hours",
        'minutes'       => "minutes",
        'seconds'       => "seconds",
        'millisecs'     => "milliseconds",
        'daysAbbr'      => "d",
        'hoursAbbr'     => "hr",
        'minutesAbbr'   => "min",
        'secondsAbbr'   => "sec",
        'millisecsAbbr' => "ms",

        'n_a'           => "n/a",

        // err_title = An error in AoWoW
        // un_err = Enter your username
        // pwd_err = Enter your password
        // signin_msg = Enter your game account
        // c_pwd = Repeat password
        // facts = Facts
        // This_Object_cant_be_found = Object map not available, Object may be spawned via a script

        // filter
        'extSearch'     => "Extended search",
        'addFilter'     => "Add another Filter",
        'match'         => "Match",
        'allFilter'     => "All filters",
        'oneFilter'     => "At least one",
        'applyFilter'   => "Apply filter",
        'resetForm'     => "Reset Form",
        'refineSearch'  => "Tip: Refine your search by browsing a <a href=\"javascript:;\" id=\"fi_subcat\">subcategory</a>.",

        // infobox
        'unavailable'   => "Not available to players",
        'disabled'      => "Disabled",
        'disabledHint'  => "Cannot be attained or completed",
        'serverside'    => "Serverside",
        'serversideHint' => "These informations are not in the Client and have been provided by sniffing and/or guessing.",

        // red buttons
        'links'         => "Links",
        'compare'       => "Compare",
        'view3D'        => "View in 3D"
    ),
    'search' => array(
        'search'        => "Search",
        'foundResult'   => "Search Results for",
        'noResult'      => "No Results for",
        'tryAgain'      => "Please try some different keywords or check your spelling.",
    ),
    'game' => array(
        'achievement'   => "achievement",
        'achievements'  => "Achievements",
        'class'         => "class",
        'classes'       => "Classes",
        'currency'      => "currency",
        'currencies'    => "Currencies",
        'difficulty'    => "Difficulty",
        'dispelType'    => "Dispel type",
        'duration'      => "Duration",
        'gameObject'    => "objects",
        'gameObjects'   => "Objects",
        'glyphType'     => "Glyph type",
        'race'          => "race",
        'races'         => "Races",
        'title'         => "title",
        'titles'        => "Titles",
        'eventShort'    => "Event",
        'event'         => "World Event",
        'events'        => "World Events",
        'cooldown'      => "%s cooldown",
        'itemset'       => "item Set",
        'itemsets'      => "Item Sets",
        'mechanic'      => "Mechanic",
        'mechAbbr'      => "Mech.",
        'pet'           => "Pet",
        'pets'          => "Hunter Pets",
        'petCalc'       => "Hunter Pet Calculator",
        'requires'      => "Requires %s",
        'requires2'     => "Requires",
        'reqLevel'      => "Requires Level %s",
        'reqLevelHlm'   => "Requires Level %s",
        'reqSkillLevel' => "Required skill level",
        'level'         => "Level",
        'school'        => "School",
        'spell'         => "spell",
        'spells'        => "Spells",
        'type'          => "Type",
        'valueDelim'    => " to ",
        'zone'          => "zone",
        'zones'         => "Zones",
        'expansions'    => array("Classic", "The Burning Crusade", "Wrath of the Lich King"),
        'stats'         => array("Strength", "Agility", "Stamina", "Intellect", "Spirit"),
        'languages'     => array(
            1 => "Orcish",      2 => "Darnassian",      3 => "Taurahe",     6 => "Dwarvish",        7 => "Common",          8 => "Demonic",         9 => "Titan",           10 => "Thalassian",
            11 => "Draconic",   12 => "Kalimag",        13 => "Gnomish",    14 => "Troll",          33 => "Gutterspeak",    35 => "Draenei",        36 => "Zombie",         37 => "Gnomish Binary",     38 => "Goblin Binary"
        ),
        'gl'            => array(null, "Major", "Minor"),
        'si'            => array(-2 => "Horde only", -1 => "Alliance only", null, "Alliance", "Horde", "Both"),
        'resistances'   => array(null, 'Holy Resistance', 'Fire Resistance', 'Nature Resistance', 'Frost Resistance', 'Shadow Resistance', 'Arcane Resistance'),
        'dt'            => array(null, "Magic", "Curse", "Disease", "Poison", "Stealth", "Invisibility", null, null, "Enrage"),
        'sc'            => array("Physical", "Holy", "Fire", "Nature", "Frost", "Shadow", "Arcane"),
        'cl'            => array(null, "Warrior", "Paladin", "Hunter", "Rogue", "Priest", "Death Knight", "Shaman", "Mage", "Warlock", null, "Druid"),
        'ra'            => array(-2 => "Horde", -1 => "Alliance", "Both", "Human", "Orc", "Dwarf", "Night Elf", "Undead", "Tauren", "Gnome", "Troll", null, "Blood Elf", "Draenei"),
        'rep'           => array("Hated", "Hostile", "Unfriendly", "Neutral", "Friendly", "Honored", "Revered", "Exalted"),
        'st'            => array(
            null,               "Cat Form",                     "Tree of Life",                 "Travel Form",                  "Aquatic Form",
            "Bear From",        null,                           null,                           "Dire Bear Form",               null,
            null,               null,                           null,                           "Shadowdance",                  null,
            null,               "Ghostwolf",                    "Battle Stance",                "Defensive Stance",             "Berserker Stance",
            null,               null,                           "Metamorphosis",                null,                           null,
            null,               null,                           "Swift Flight Form",            "Shadow Form",                  "Flight Form",
            "Stealth",          "Moonkin Form",                 "Spirit of Redemption"
        ),
        'me'            => array(
            null,                       "Charmed",                  "Disoriented",              "Disarmed",                 "Distracted",               "Fleeing",                  "Gripped",                  "Rooted",
            "Pacified",                 "Silenced",                 "Asleep",                   "Ensnared",                 "Stunned",                  "Frozen",                   "Incapacitated",            "Bleeding",
            "Healing",                  "Polymorphed",              "Banished",                 "Shielded",                 "Shackled",                 "Mounted",                  "Seduced",                  "Turned",
            "Horrified",                "Invulnerable",             "Interrupted",              "Dazed",                    "Discovery",                "Invulnerable",             "Sapped",                   "Enraged"
        ),
        'ct'            => array(
            "Uncategorized",            "Beast",                    "Dragonkin",                "Demon",                    "Elemental",                "Giant",                    "Undead",                   "Humanoid",
            "Critter",                  "Mechanical",               "Not specified",            "Totem",                    "Non-combat Pet",           "Gas Cloud"
        ),
        'fa'            => array(
            1 => "Wolf",                2 => "Cat",                 3 => "Spider",              4 => "Bear",                5 => "Boar",                6 => "Crocolisk",           7 => "Carrion Bird",        8 => "Crab",
            9 => "Gorilla",             11 => "Raptor",             12 => "Tallstrider",        20 => "Scorpid",            21 => "Turtle",             24 => "Bat",                25 => "Hyena",              26 => "Bird of Prey",
            27 => "Wind Serpent",       30 => "Dragonhawk",         31 => "Ravager",            32 => "Warp Stalker",       33 => "Sporebat",           34 => "Nether Ray",         35 => "Serpent",            37 => "Moth",
            38 => "Chimaera",           39 => "Devilsaur",          41 => "Silithid",           42 => "Worm",               43 => "Rhino",              44 => "Wasp",               45 => "Core Hound",         46 => "Spirit Beast"
        ),
        'pvpRank'       => array(
            null,                                       "Private / Scout",                      "Corporal / Grunt",
            "Sergeant / Sergeant",                      "Master Sergeant / Senior Sergeant",    "Sergeant Major / First Sergeant",
            "Knight / Stone Guard",                     "Knight-Lieutenant / Blood Guard",      "Knight-Captain / Legionnare",
            "Knight-Champion / Centurion",              "Lieutenant Commander / Champion",      "Commander / Lieutenant General",
            "Marshal / General",                        "Field Marshal / Warlord",              "Grand Marshal / High Warlord"
        ),
    ),
    'error' => array(
        'errNotFound'   => "Page not found",
        'errPage'       => "What? How did you... nevermind that!\n<br>\n<br>\nIt appears that the page you have requested cannot be found. At least, not in this dimension.\n<br>\n<br>\nPerhaps a few tweaks to the <span class=\"q4\">[WH-799 Major Confabulation Engine]</span> may result in the page suddenly making an appearance!\n<div class=\"pad\"></div>\n<div class=\"pad\"></div>\nOr, you can try \n<a href=\"http://www.wowhead.com/?aboutus#contact\">contacting us</a>\n- the stability of the WH-799 is debatable, and we wouldn't want another accident...",
        'goStart'       => "Return to the <a href=\"index.php\">homepage</a>",
        'goForum'       => "Feedback <a href=\"?forums&board=1\">forum</a>",
    ),
    'account' => array(
        'doSignIn'      => "Log in to your AoWoW Account",
        'user'          => "Username",
        'pass'          => "Password",
        'rememberMe'    => "Stay logged in",
        'forgot'        => "Forgot",
        'accNoneYet'    => "Don't have an account",
        'accCreateNow'  => "Create one now",
        'userNotFound'  => "Such user does not exists",
        'userBanned'    => "This Account was closed",
        'passMismatch'  => "Entered passwords does not match",
        'loginsExceeded' => "The maximum number of logins from this IP has been exceeded. Please try again in %s minutes.",
        'nameInUse'     => "Such user already exists",
        'email'         => "Email address",
        'unkError'      => "Unknown error on account create",
        'accCreate'     => "Create your account",
        'passConfirm'   => "Confirm password",
        'signup'        => "Signup",
        'requestName'   => "Username Request",
        'resetPass'     => "Password Reset",
        'emailInvalid'  => "This email address is invalid.",
        'emailUnknown'  => "The email address you entered is not associated with any account.<br><br>If you forgot the email you registered your account with email feedback@aowow.com for assistance.",
        'passJustSend'  => "An email containing a new password was just sent to %s",
        'nameJustSend'  => "An email containing your username was just sent to %s",
        'wrongPass'     => "Wrong Password",
        'ipAddress'     => "IP-Adress",
        'lastIP'        => "last used IP",
        'joinDate'      => "Joined",
        'lastLogin'     => "Last visit",
        'userGroups'    => "Rolle",
        'myAccount'     => "My Account",
        'editAccount'   => "Simply use the forms below to update your account information",
        'publicDesc'    => "Public Description",
        'viewPubDesc'   => "View your Public Description in your <a href=\"?user=%s\">Profile  Page</a>",


        // Please_enter_your_username = Enter your username (account)
        // Please_enter_your_password = Enter your password
        // Sign_in_to_your_Game_Account = Enter your game account:
        // Please_enter_your_confirm_password = Please enter your confirm password
    ),
    'npc'   => array(
        'rank'          => ['Normal', 'Elite', 'Rare Elite', 'Boss', 'Rare']
    ),
    'event' => array(
        'category'      => array("Uncategorized", "Holidays", "Recurring", "Player vs. Player")
    ),
    'achievement' => array(
        'criteria'      => "Criteria",
        'points'        => "Points",
        'series'        => "Series",
        'outOf'         => "out of",
        'criteriaType'  => "Criterium Type-Id:",
        'itemReward'    => "You will receive:",
        'titleReward'   => "You shall be granted the title \"<a href=\"?title=%d\">%s</a>\"",
        'slain'         => "slain",
    ),
    'compare' => array(
        'compare'       => "Item Comparison Tool",
    ),
    'talent' => array(
        'talentCalc'    => "Talent Calculator",
        'petCalc'       => "Hunter Pet Calculator",
        'chooseClass'   => "Choose a class",
        'chooseFamily'  => "Choose a pet family",
    ),
    'maps' => array(
        'maps'          => "Maps",
        'linkToThisMap' => "Link to this map",
        'clear'         => "Clear",
        'EasternKingdoms' => "Eastern Kingdoms",
        'Kalimdor'      => "Kalimdor",
        'Outland'       => "Outland",
        'Northrend'     => "Northrend",
        'Instances'     => "Instances",
        'Dungeons'      => "Dungeons",
        'Raids'         => "Raids",
        'More'          => "More ",
        'Battlegrounds' => "Battlegrounds",
        'Miscellaneous' => "Miscellaneous",
        'Azeroth'       => "Azeroth",
        'CosmicMap'     => "Cosmic Map",
    ),
    'zone' => array(
        // 'zone'          => "Zone",
        // 'zonePartOf'    => "This zone is part of",
        'cat'           => array(
            "Eastern Kingdoms",         "Kalimdor",                 "Dungeons",                 "Raids",                    "Unused",                   null,
            "Battlegrounds",            null,                       "Outland",                  "Arenas",                   "Northrend"
        )
    ),
    'quest' => array(
        'level'         => 'Level %s',
        'daily'         => 'Daily',
        'requirements'  => 'Requirements'
    ),
    'title' => array(
        'cat'           => array(
            'General',      'Player vs. Player',    'Reputation',       'Dungeons & Raids',     'Quests',       'Professions',      'World Events'
        )
    ),
    'currency' => array(
        'cat'           => array(
            1 => "Miscellaneous", 2 => "Player vs. Player", 4 => "Classic", 21 => "Wrath of the Lich King", 22 => "Dungeon and Raid", 23 => "Burning Crusade", 41 => "Test", 3 => "Unused"
        )
    ),
    'pet'      => array(
        'exotic'        => "Exotic",
        'cat'           => ["Ferocity", "Tenacity", "Cunning"]
    ),
    'itemset' => array(
        '_desc'         => "<b>%s</b> is the <b>%s</b>. It contains %s pieces.",
        '_descTagless'  => "<b>%s</b> is an item set that contains %s pieces.",
        '_setBonuses'   => "Set Bonuses",
        '_conveyBonus'  => "Wearing more pieces of this set will convey bonuses to your character.",
        '_pieces'       => "pieces",
        '_unavailable'  => "This item set is not available to players.",
        '_tag'          => "Tag",

        'notes'         => array(
            null,                                   "Dungeon Set 1",                        "Dungeon Set 2",                        "Tier 1 Raid Set",
            "Tier 2 Raid Set",                      "Tier 3 Raid Set",                      "Level 60 PvP Rare Set",                "Level 60 PvP Rare Set (Old)",
            "Level 60 PvP Epic Set",                "Ruins of Ahn'Qiraj Set",               "Temple of Ahn'Qiraj Set",              "Zul'Gurub Set",
            "Tier 4 Raid Set",                      "Tier 5 Raid Set",                      "Dungeon Set 3",                        "Arathi Basin Set",
            "Level 70 PvP Rare Set",                "Arena Season 1 Set",                   "Tier 6 Raid Set",                      "Arena Season 2 Set",
            "Arena Season 3 Set",                   "Level 70 PvP Rare Set 2",              "Arena Season 4 Set",                   "Tier 7 Raid Set",
            "Arena Season 5 Set",                   "Tier 8 Raid Set",                      "Arena Season 6 Set",                   "Tier 9 Raid Set",
            "Arena Season 7 Set",                   "Tier 10 Raid Set",                     "Arena Season 8 Set"
        ),
        'types'         => array(
            null,               "Cloth",                "Leather",              "Mail",                     "Plate",                    "Dagger",                   "Ring",
            "Fist Weapon",      "One-Handed Axe",       "One-Handed Mace",      "One-Handed Sword",         "Trinket",                  "Amulet"
        )
    ),
    'spell' => array(
        '_spellDetails' => "Spell Details",
        '_cost'         => "Cost",
        '_range'        => "Range",
        '_castTime'     => "Cast Time",
        '_cooldown'     => "Cooldown",
        '_distUnit'     => "yards",
        '_forms'        => "Forms",
        '_aura'         => "Aura",
        '_effect'       => "Effect",
        '_none'         => "None",
        '_gcd'          => "GCD",
        '_globCD'       => "Global Cooldown",
        '_gcdCategory'  => "GCD category",
        '_value'        => "Value",
        '_radius'       => "Radius",
        '_interval'     => "Interval",
        '_inSlot'       => "in slot",

        'starter'       => "Starter spell",
        'trainingCost'  => "Training cost",
        'remaining'     => "%s remaining",
        'untilCanceled' => "until canceled",
        'castIn'        => "%s sec cast",
        'instantPhys'   => "Instant",
        'instantMagic'  => "Instant cast",
        'channeled'     => "Channeled",
        'range'         => "%s yd range",
        'meleeRange'    => "Melee Range",
        'unlimRange'    => "Unlimited Range",
        'reagents'      => "Reagents",
        'tools'         => "Tools",
        'home'          => "&lt;Inn&gt;",
        'pctCostOf'     => "of base %s",
        'costPerSec'    => ", plus %s per sec",
        'costPerLevel'  => ", plus %s per level",
        'powerRunes'    => ["Frost", "Unholy", "Blood", "Death"],
        'powerTypes'    => array(
            -2 => "Health",   -1 => null,   "Mana",     "Rage",     "Focus",    "Energy",       "Happiness",        "Rune",    "Runic Power",
            'AMMOSLOT' => "Ammo",           'STEAM' => "Steam Pressure",        'WRATH' => "Wrath",                 'PYRITE' => "Pyrite",
            'HEAT' => "Heat",               'OOZE' => "Ooze",                   'BLOOD_POWER' => "Blood Power"
        ),
        'relItems'      => array (
            'base'    => "<small>Show %s related to <b>%s</b></small>",
            'link'    => " or ",
            'recipes' => "<a href=\"?items=9.%s\">recipe items</a>",
            'crafted' => "<a href=\"?items&filter=cr=86;crs=%s\">crafted items</a>"
        ),
        'cat'           => array(
              7 => "Class Skills",      // classList
            -13 => "Glyphs",            // classList
            -11 => array("Proficiencies", 8 => "Armor", 6 => "Weapon", 10 => "Languages"),
             -4 => "Racial Traits",
             -2 => "Talents",           // classList
             -6 => "Companions",
             -5 => "Mounts",
             -3 => array(
                "Pet Skills",               782 => "Ghoul",             270 => "Generic",               653 => "Bat",                       210 => "Bear",                  655 => "Bird of Prey",          211 => "Boar",
                213 => "Carrion Bird",      209 => "Cat",               780 => "Chimaera",              787 => "Core Hound",                214 => "Crab",                  212 => "Crocolisk",             781 => "Devilsaur",
                763 => "Dragonhawk",        215 => "Gorilla",           654 => "Hyena",                 775 => "Moth",                      764 => "Nether Ray",            217 => "Raptor",                767 => "Ravager",
                786 => "Rhino",             236 => "Scorpid",           768 => "Serpent",               783 => "Silithid",                  203 => "Spider",                788 => "Spirit Beast",          765 => "Sporebat",
                218 => "Tallstrider",       251 => "Turtle",            766 => "Warp Stalker",          785 => "Wasp",                      656 => "Wind Serpent",          208 => "Wolf",                  784 => "Worm",
                761 => "Felguard",          189 => "Felhunter",         188 => "Imp",                   205 => "Succubus",                  204 => "Voidwalker"
            ),
             -7 => array("Pet Talents", 410 => "Cunning", 411 => "Ferocity", 409 => "Tenacity"),
             11 => array(
                "Professions",
                171 => "Alchemy",
                164 => array("Blacksmithing", 9788 => "Armorsmithing", 9787 => "Weaponsmithing", 17041 => "Master Axesmithing", 17040 => "Master Hammersmithing", 17039 => "Master Swordsmithing"),
                333 => "Enchanting",
                202 => array("Engineering", 20219 => "Gnomish Engineering", 20222 => "Goblin Engineering"),
                182 => "Herbalism",
                773 => "Inscription",
                755 => "Jewelcrafting",
                165 => array("Leatherworking", 10656 => "Dragonscale Leatherworking", 10658 => "Elemental Leatherworking", 10660 => "Tribal Leatherworking"),
                186 => "Mining",
                393 => "Skinning",
                197 => array("Tailoring", 26798 => "Mooncloth Tailoring", 26801 => "Shadoweave Tailoring", 26797 => "Spellfire Tailoring"),
            ),
              9 => array ("Secondary Skills", 185 => "Cooking", 129 => "First Aid", 356 => "Fishing", 762 => "Riding"),
             -8 => "NPC Abilities",
             -9 => "GM Abilities",
              0 => "Uncategorized"
        ),
        'armorSubClass' => array(
            "Miscellaneous",        "Cloth Armor",      "Leather Armor",        "Mail Armor",                   "Plate Armor",
            null,                   "Shilds",           "Librams",              "Idols",                        "Totems",
            "Sigils"
        ),
        'weaponSubClass' => array(
            "One-Handed Axes",      "Two-Handed Axes",  "Bows",                 "Guns",                         "One-Handed Maces",
            "Two-Handed Maces",     "Polearms",         "One-Handed Swords",    "Two-Handed Swords",            null,
            "Staves",               null,               null,                   "Fist Weapons",                 "Miscellaneous",
            "Daggers",              "Thrown",           null,                   "Crossbows",                    "Wands",
            "Fishing Poles"
        ),
        'subClassMasks'      => array(
            0x02A5F3 => 'Melee Weapon',                 0x0060 => 'Shield',                         0x04000C => 'Ranged Weapon',                0xA091 => 'One-Handed Melee Weapon'
        ),
        'traitShort'    => array(
            'atkpwr'    => "AP",                        'rgdatkpwr' => "RAP",                                   'splpwr'    => "SP",
            'arcsplpwr' => "ArcP",                      'firsplpwr' => "FireP",                                 'frosplpwr' => "FroP",
            'holsplpwr' => "HolP",                      'natsplpwr' => "NatP",                                  'shasplpwr' => "ShaP",
            'splheal'   => "Heal"
        )
    ),
    'item' => array(
        'armor'         => "%s Armor",
        'block'         => "%s Block",
        'charges'       => "Charges",
        'expend'        => "expendable",
        'locked'        => "Locked",
        'ratingString'  => "%s&nbsp;@&nbsp;L%s",
        'heroic'        => "Heroic",
        'unique'        => "Unique",
        'uniqueEquipped'=> "Unique-Equipped",
        'startQuest'    => "This Item Begins a Quest",
        'bagSlotString' => "%d Slot %s",
        'dps'           => "damage per second",
        'dps2'          => "damage per second",
        'addsDps'       => "Adds",
        'fap'           => "Feral Attack Power",
        'durability'    => "Durability",
        'realTime'      => "real time",
        'conjured'      => "Conjured Item",
        'damagePhys'    => "%s Damage",
        'damageMagic'   => "%s %s Damage",
        'speed'         => "Speed",
        'sellPrice'     => "Sell Price",
        'itemLevel'     => "Item Level",
        'randEnchant'   => "&lt;Random enchantment&gt",
        'readClick'     => "&lt;Right Click To Read&gt",
        'set'           => "Set",
        'socketBonus'   => "Socket Bonus",
        'socket'        => array(
            "Meta Socket",          "Red Socket",       "Yellow Socket",        "Blue Socket",            -1 => "Prismatic Socket"
        ),
        'quality'       => array (
            "Poor",                 "Common",           "Uncommon",             "Rare",
            "Epic",                 "Legendary",        "Artifact",             "Heirloom"
        ),
        'trigger'       => array (
            "Use: ",                "Equip: ",          "Chance on hit: ",      null,                           null,
            null,                   null
        ),
        'bonding'       => array (
            "Binds to account",                         "Binds when picked up",                                 "Binds when equipped",
            "Binds when used",                          "Soulbound",                                            "Quest Item"
        ),
        "bagFamily"     => array(
            "Bag",                  "Quiver",           "Ammo Pouch",           "Soul Bag",                     "Leatherworking Bag",
            "Inscription Bag",      "Herb Bag",         "Enchanting Bag",       "Engineering Bag",              "Key",
            "Gem Bag",              "Mining Bag"
        ),
        'inventoryType' => array(
            null,                   "Head",             "Neck",                 "Shoulder",                     "Shirt",
            "Chest",                "Waist",            "Legs",                 "Feet",                         "Wrist",
            "Hands",                "Finger",           "Trinket",              "One-Hand",                     "Off Hand",
            "Ranged",               "Back",             "Two-Hand",             "Bag",                          "Tabard",
            "Chest",                "Main Hand",        "Off Hand",             "Held In Off-Hand",             "Projectile",
            "Thrown",               "Ranged",           "Quiver",               "Relic"
        ),
        'armorSubClass' => array(
            "Miscellaneous",        "Cloth",            "Leather",              "Mail",                         "Plate",
            null,                   "Shild",            "Libram",               "Idol",                         "Totem",
            "Sigil"
        ),
        'weaponSubClass' => array(
            "Axe",                  "Axe",              "Bow",                  "Gun",                          "Mace",
            "Mace",                 "Polearm",          "Sword",                "Sword",                        null,
            "Staff",                null,               null,                   "Fist Weapon",                  "Miscellaneous",
            "Dagger",               "Thrown",           null,                   "Crossbow",                     "Wand",
            "Fishing Pole"
        ),
        'projectileSubClass' => array(
            null,                   null,               "Arrow",                "Bullet",                        null
        ),
        'statType'  => array(
            "Increases your Mana by %d.",
            "Increases your Health by %d.",
            null,
            "Agility",
            "Strength",
            "Intellect",
            "Spirit",
            "Stamina",
            null, null, null, null,
            "Improves defense rating by %d.",
            "Increases your dodge rating by %d.",
            "Increases your parry rating by %d.",
            "Increases your shield block rating by %d.",
            "Improves melee hit rating by %d.",
            "Improves ranged hit rating by %d.",
            "Improves spell hit rating by %d.",
            "Improves melee critical strike rating by %d.",
            "Improves ranged critical strike rating by %d.",
            "Improves spell critical strike rating by %d.",
            "Improves melee hit avoidance rating by %d.",
            "Improves ranged hit avoidance rating by %d.",
            "Improves spell hit avoidance rating by %d.",
            "Improves melee critical avoidance rating by %d.",
            "Improves ranged critical avoidance rating by %d.",
            "Improves spell critical avoidance rating by %d.",
            "Improves melee haste rating by %d.",
            "Improves ranged haste rating by %d.",
            "Improves spell haste rating by %d.",
            "Improves hit rating by %d.",
            "Improves critical strike rating by %d.",
            "Improves hit avoidance rating by %d.",
            "Improves critical avoidance rating by %d.",
            "Increases your resilience rating by %d.",
            "Increases your haste rating by %d.",
            "Improves expertise rating by %d.",
            "Improves attack power by %d.",
            "Improves ranged attack power by %d.",
            "Improves attack power by %d in Cat, Bear, Dire Bear, and Moonkin forms only.",
            "Improves damage done by magical spells and effects by up to %d.",
            "Improves healing done by magical spells and effects by up to %d.",
            "Restores %d mana per 5 sec.",
            "Increases your armor penetration rating by %d.",
            "Improves spell power by %d.",
            "Restores %d health per 5 sec.",
            "Improves spell penetration by %d.",
            "Increases the block value of your shield by %d.",
            "Unknown Bonus #%d (%d)",
        )
    ),
    'colon'         => ': '
);

?>
