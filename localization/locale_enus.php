<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$lang = array(
    // page variables
    'timeUnits' => array(
        'sg'            => ["year",  "month",  "week",  "day",  "hour",  "minute",  "second",  "millisecond"],
        'pl'            => ["years", "months", "weeks", "days", "hours", "minutes", "seconds", "milliseconds"],
        'ab'            => ["yr",    "mo",     "wk",    "day",  "hr",    "min",     "sec",     "ms"]
    ),
    'main' => array(
        'help'          => "Help",
        'name'          => "name",
        'link'          => "Link",
        'signIn'        => "Sign in",
        'jsError'       => "Please make sure you have javascript enabled.",
        'searchButton'  => "Search",
        'language'      => "Language",
        'numSQL'        => "Number of MySQL queries",
        'timeSQL'       => "Time of MySQL queries",
        'noJScript'     => "<b>This site makes extensive use of JavaScript.</b><br />Please <a href=\"https://www.google.com/support/adsense/bin/answer.py?answer=12654\" target=\"_blank\">enable JavaScript</a> in your browser.",
        'profiles'      => "My Profiles",
        'pageNotFound'  => "This %s doesn't exist.",
        'gender'        => "Gender",
        'sex'           => [null, 'Male', 'Female'],
        'players'       => "Players",
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
        'n_a'           => "n/a",

        // filter
        'extSearch'     => "Extended search",
        'addFilter'     => "Add another Filter",
        'match'         => "Match",
        'allFilter'     => "All filters",
        'oneFilter'     => "At least one",
        'applyFilter'   => "Apply filter",
        'resetForm'     => "Reset Form",
        'refineSearch'  => "Tip: Refine your search by browsing a <a href=\"javascript:;\" id=\"fi_subcat\">subcategory</a>.",
        'clear'         => "clear",
        'exactMatch'    => "Exact match",
        '_reqLevel'     => "Required level",

        // infobox
        'unavailable'   => "Not available to players",      // alternative wording found: "No longer available to players" ... aw screw it <_<
        'disabled'      => "Disabled",
        'disabledHint'  => "Cannot be attained or completed",
        'serverside'    => "Serverside",
        'serversideHint'=> "These informations are not in the Client and have been provided by sniffing and/or guessing.",

        // red buttons
        'links'         => "Links",
        'compare'       => "Compare",
        'view3D'        => "View in 3D",
        'findUpgrades'  => "Find upgrades...",

        // misc Tools
        'subscribe'     => "Subscribe",
        'mostComments'  => ["Yesterday", "Past %d Days"],
        'utilities'     => array(
            "Latest Additions",                     "Latest Articles",                      "Latest Comments",                      "Latest Screenshots",                   null,
            "Unrated Comments",                     11 => "Latest Videos",                  12 => "Most Comments",                  13 => "Missing Screenshots"
        ),

        // article & infobox
        'englishOnly'   => "This page is only available in <b>English</b>.",

        // calculators
        'preset'        => "Preset",
        'addWeight'     => "Add another weight",
        'createWS'      => "Create a weight scale",
        'jcGemsOnly'    => "Include <span%s>JC-only</span> gems",
        'cappedHint'    => 'Tip: <a href="javascript:;" onclick="fi_presetDetails();">Remove</a> weights for capped statistics such as Hit rating.',
        'groupBy'       => "Group By",
        'gb'            => array(
            ['None', 'none'],         ['Slot', 'slot'],       ['Level', 'level'],     ['Source', 'source']
        ),
        'compareTool'   => "Item Comparison Tool",
        'talentCalc'    => "Talent Calculator",
        'petCalc'       => "Hunter Pet Calculator",
        'chooseClass'   => "Choose a class",
        'chooseFamily'  => "Choose a pet family"
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
        'gameObject'    => "object",
        'gameObjects'   => "Objects",
        'glyphType'     => "Glyph type",
        'race'          => "race",
        'races'         => "Races",
        'title'         => "title",
        'titles'        => "Titles",
        'eventShort'    => "Event",
        'event'         => "World Event",
        'events'        => "World Events",
        'faction'       => "faction",
        'factions'      => "Factions",
        'cooldown'      => "%s cooldown",
        'item'          => "item",
        'items'         => "Items",
        'itemset'       => "item Set",
        'itemsets'      => "Item Sets",
        'mechanic'      => "Mechanic",
        'mechAbbr'      => "Mech.",
        'meetingStone'  => "Meeting Stone",
        'npc'           => "NPC",
        'npcs'          => "NPCs",
        'pet'           => "Pet",
        'pets'          => "Hunter Pets",
        'profile'       => "profile",
        'profiles'      => "Profiles",
        'quest'         => "quest",
        'quests'        => "Quests",
        'requires'      => "Requires %s",
        'requires2'     => "Requires",
        'reqLevel'      => "Requires Level %s",
        'reqLevelHlm'   => "Requires Level %s",
        'reqSkillLevel' => "Required skill level",
        'level'         => "Level",
        'school'        => "School",
        'skill'         => "skill",
        'skills'        => "Skills",
        'spell'         => "spell",
        'spells'        => "Spells",
        'type'          => "Type",
        'valueDelim'    => " to ",
        'zone'          => "zone",
        'zones'         => "Zones",

        'heroClass'     => "Hero class",
        'resource'      => "Resource",
        'resources'     => "Resources",
        'role'          => "Role",
        'roles'         => "Roles",
        'specs'         => "Specs",
        '_roles'        => ['Healer', 'Melee DPS', 'Ranged DPS', 'Tank'],

        'modes'         => ['Normal / Normal 10', 'Heroic / Normal 25', 'Heroic 10', 'Heroic 25'],
        'expansions'    => array("Classic", "The Burning Crusade", "Wrath of the Lich King"),
        'stats'         => array("Strength", "Agility", "Stamina", "Intellect", "Spirit"),
        'languages'     => array(
            1 => "Orcish",      2 => "Darnassian",      3 => "Taurahe",     6 => "Dwarvish",        7 => "Common",          8 => "Demonic",         9 => "Titan",           10 => "Thalassian",
            11 => "Draconic",   12 => "Kalimag",        13 => "Gnomish",    14 => "Troll",          33 => "Gutterspeak",    35 => "Draenei",        36 => "Zombie",         37 => "Gnomish Binary",     38 => "Goblin Binary"
        ),
        'gl'            => array(null, "Major", "Minor"),
        'si'            => array(1 => "Alliance", -1 => "Alliance only", 2 => "Horde", -2 => "Horde only", 3 => "Both"),
        'resistances'   => array(null, 'Holy Resistance', 'Fire Resistance', 'Nature Resistance', 'Frost Resistance', 'Shadow Resistance', 'Arcane Resistance'),
        'dt'            => array(null, "Magic", "Curse", "Disease", "Poison", "Stealth", "Invisibility", null, null, "Enrage"),
        'sc'            => array("Physical", "Holy", "Fire", "Nature", "Frost", "Shadow", "Arcane"),
        'cl'            => array(null, "Warrior", "Paladin", "Hunter", "Rogue", "Priest", "Death Knight", "Shaman", "Mage", "Warlock", null, "Druid"),
        'ra'            => array(-2 => "Horde", -1 => "Alliance", "Both", "Human", "Orc", "Dwarf", "Night Elf", "Undead", "Tauren", "Gnome", "Troll", null, "Blood Elf", "Draenei"),
        'rep'           => array("Hated", "Hostile", "Unfriendly", "Neutral", "Friendly", "Honored", "Revered", "Exalted"),
        'st'            => array(
            "Default",          "Cat Form",                     "Tree of Life",                 "Travel Form",                  "Aquatic Form",
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
    ),
    'gameObject' => array(
        'cat'           => [0 => "Other", 9 => "Books", 3 => "Containers", -5 => "Chests", 25 => "Fishing Pools", -3 => "Herbs", -4 => "Mineral Veins", -2 => "Quest", -6 => "Tools"],
        'type'          => [              9 => "Book",  3 => "Container",  -5 => "Chest",  25 => "",              -3 => "Herb",  -4 => "Mineral Vein",  -2 => "Quest", -6 => ""],
        'unkPosition'   => "The location of this object is unknown.",
        'key'           => "Key",
        'focus'         => "Spell Focus",
        'focusDesc'     => "Spells requiring this Focus can be cast near this Object",
        'trap'          => "Trap",
        'triggeredBy'   => "Triggered by",
        'capturePoint'  => "Capture Point",
        'foundIn'       => "This object can be found in"
    ),
    'npc' => array(
        'classification'=> "Classification",
        'petFamily'     => "Pet familiy",
        'react'         => "React",
        'worth'         => "Worth",
        'unkPosition'   => "The location of this NPC is unknown.",
        'difficultyPH'  => "This NPC is a placeholder for a different mode of",
        'quotes'        => "Quotes",
        'gainsDesc'     => "After killing this NPC you will gain",
        'repWith'       => "reputation with",
        'stopsAt'       => "stops at %s",
        'vehicle'       => "Vehicle",
        'stats'         => "Stats",
        'melee'         => "Melee",
        'ranged'        => "Ranged",
        'armor'         => "Armor",
        'foundIn'       => "This NPC can be found in",
        'rank'          => [0 => "Normal", 1 => "Elite", 4 => "Rare", 2 => "Rare Elite", 3 => "Boss"],
        'textTypes'     => [null, "yells", "says", "whispers"],
        'modes'         => array(
            1 => ["Normal", "Heroic"],
            2 => ["10-player Normal", "25-player Normal", "10-player Heroic", "25-player Heroic"]
        ),
        'cat'           => array(
            "Uncategorized",            "Beasts",                   "Dragonkins",               "Demons",                   "Elementals",               "Giants",                   "Undead",                   "Humanoids",
            "Critters",                 "Mechanicals",              "Not specified",            "Totems",                   "Non-combat Pets",          "Gas Clouds"
        ),
    ),
    'event' => array(
        'start'         => "Start",
        'end'           => "End",
        'interval'      => "Interval",
        'inProgress'    => "Event is currently in progress",
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
        'reqNumCrt'     => "Requires"
    ),
    'class' => array(
        'racialLeader'  => "Racial leader",
        'startZone'     => "Starting zone",
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
        'selectorLink'  => " and ",
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
        'questLevel'    => 'Level %s',
        'daily'         => 'Daily',
        'requirements'  => 'Requirements',
        'questInfo'     => array(
             0 => 'Normal',              1 => 'Group',              21 => 'Life',               41 => 'PvP',                62 => 'Raid',               81 => 'Dungeon',            82 => 'World Event',
            83 => 'Legendary',          84 => 'Escort',             85 => 'Heroic',             88 => 'Raid (10)',          89 => 'Raid (25)'
        )
    ),
    'title' => array(
        'cat'           => array(
            'General',      'Player vs. Player',    'Reputation',       'Dungeons & Raids',     'Quests',       'Professions',      'World Events'
        )
    ),
    'skill' => array(
        'cat'           => array(
            -6 => 'Companions',         -5 => 'Mounts',             -4 => 'Racial Traits',      5 => 'Attributes',          6 => 'Weapon Skills',       7 => 'Class Skills',        8 => 'Armor Proficiencies',
             9 => 'Secondary Skills',   10 => 'Languages',          11 => 'Professions'
        )
    ),
    'currency' => array(
        'cap'           => "Total cap",
        'cat'           => array(
            1 => "Miscellaneous", 2 => "Player vs. Player", 4 => "Classic", 21 => "Wrath of the Lich King", 22 => "Dungeon and Raid", 23 => "Burning Crusade", 41 => "Test", 3 => "Unused"
        )
    ),
    'pet'      => array(
        'exotic'        => "Exotic",
        'cat'           => ["Ferocity", "Tenacity", "Cunning"]
    ),
    'faction' => array(
        'spillover'     => "Reputation Spillover",
        'spilloverDesc' => "Gaining Reputation with this faction also yields a proportional gain with the factions listed below.",
        'maxStanding'   => "Max. Standing",
        'quartermaster' => "Quartermaster",
        'cat'           => array(
            1118 => ["Classic", 469 => "Alliance", 169 => "Steamwheedle Cartel", 67 => "Horde", 891 => "Alliance Forces", 892 => "Horde Forces"],
            980  => ["The Burning Crusade", 936 => "Shattrath City"],
            1097 => ["Wrath of the Lich King", 1052 => "Horde Expedition", 1117 => "Sholazar Basin", 1037 => "Alliance Vanguard"],
            0    => "Other"
        )
    ),
    'itemset' => array(
        '_desc'         => "<b>%s</b> is the <b>%s</b>. It contains %s pieces.",
        '_descTagless'  => "<b>%s</b> is an item set that contains %s pieces.",
        '_setBonuses'   => "Set Bonuses",
        '_conveyBonus'  => "Wearing more pieces of this set will convey bonuses to your character.",
        '_pieces'       => "pieces",
        '_unavailable'  => "This item set is not available to players.",
        '_tag'          => "Tag",
        'summary'       => "Summary",
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
        '_castTime'     => "Cast time",
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
        '_collapseAll'  => "Collapse All",
        '_expandAll'    => "Expand All",

        'discovered'    => "Learned via discovery",
        'ppm'           => "%s procs per minute",
        'procChance'    => "Proc chance",
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
        '_scaling'      => "Scaling",
        'scaling'       => array(
            'directSP' => "+%.2f%% of spell power to direct component",         'directAP' => "+%.2f%% of attack power to direct component",
            'dotSP'    => "+%.2f%% of spell power per tick",                    'dotAP'    => "+%.2f%% of attack power per tick"
        ),
        'powerRunes'    => ["Frost", "Unholy", "Blood", "Death"],
        'powerTypes'    => array(
            -2 => "Health",   -1 => null,   "Mana",     "Rage",     "Focus",    "Energy",       "Happiness",        "Rune",    "Runic Power",
            'AMMOSLOT' => "Ammo",           'STEAM' => "Steam Pressure",        'WRATH'       => "Wrath",           'PYRITE' => "Pyrite",
            'HEAT'     => "Heat",           'OOZE'  => "Ooze",                  'BLOOD_POWER' => "Blood Power"
        ),
        'relItems'      => array (
            'base'    => "<small>Show %s related to <b>%s</b></small>",
            'link'    => " or ",
            'recipes' => "<a href=\"?items=9.%s\">recipe items</a>",
            'crafted' => "<a href=\"?items&filter=cr=86;crs=%s;crv=0\">crafted items</a>"
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
            "Miscellaneous",                        "Cloth Armor",                          "Leather Armor",                        "Mail Armor",                           "Plate Armor",
            null,                                   "Shields",                              "Librams",                              "Idols",                                "Totems",
            "Sigils"
        ),
        'weaponSubClass' => array(                          // ordered by content firts, then alphabeticaly
            15 => "Daggers",                        13 => "Fist Weapons",                    0 => "One-Handed Axes",                 4 => "One-Handed Maces",                7 => "One-Handed Swords",
             6 => "Polearms",                       10 => "Staves",                          1 => "Two-Handed Axes",                 5 => "Two-Handed Maces",                8 => "Two-Handed Swords",
             2 => "Bows",                           18 => "Crossbows",                       3 => "Guns",                           16 => "Thrown",                         19 => "Wands",
            20 => "Fishing Poles",                  14 => "Miscellaneous"
        ),
        'subClassMasks' => array(
            0x02A5F3 => 'Melee Weapon',             0x0060 => 'Shield',                     0x04000C => 'Ranged Weapon',            0xA091 => 'One-Handed Melee Weapon'
        ),
        'traitShort'    => array(
            'atkpwr'    => "AP",                    'rgdatkpwr' => "RAP",                   'splpwr'    => "SP",                    'arcsplpwr' => "ArcP",                  'firsplpwr' => "FireP",
            'frosplpwr' => "FroP",                  'holsplpwr' => "HolP",                  'natsplpwr' => "NatP",                  'shasplpwr' => "ShaP",                  'splheal'   => "Heal"
        ),
        'spellModOp'    => array(
            "Damage",                               "Duration",                             "Thread",                               "Effect 1",                             "Charges",
            "Range",                                "Radius",                               "Critical Hit Chance",                  "All Effects",                          "Casting Time loss",
            "Casting Time",                         "Cooldown",                             "Effect 2",                             "Ignore Armor",                         "Cost",
            "Critical Damage Bonus",                "Chance to Fail",                       "Jump Targets",                         "Proc Chance",                          "Intervall",
            "Multiplier (Damage)",                  "Global Cooldown",                      "Damage over Time",                     "Effect 3",                             "Multiplier (Bonus)",
            null,                                   "Procs per Minute",                     "Multiplier (Value)",                   "Chance to Resist Dispel",              "Critical Damage Bonus2",
            "Refund Cost on Fail"
        ),
        'combatRating'  => array(
            "Weapon Skill",                         "Defense Skill",                        "Dodge",                                "Parry",                                "Block",
            "Melee Hit Chance",                     "Ranged Hit Chance",                    "Spell Hit Chance",                     "Critical Melee Hit Chance",            "Critical Ranged Hit Chance",
            "Critical Spell Hit Chance",            "Taken Melee Hit Chance",               "Taken Ranged Hit Chance",              "Taken Spell Hit Chance",               "Taken Critical Melee Hit Chance",
            "Taken Critical Ranged Hit Chance",     "Taken Critical Spell Hit Chance",      "Melee Haste",                          "Ranged Haste",                         "Spell Haste",
            "Mainhand Weapon Skill",                "Offhand Weapon Skill",                 "Ranged Weapon Skill",                  "Expertise",                            "Armor Penetration"
        ),
        'lockType'      => array(
            null,                                   "Lockpicking",                          "Herbalism",                            "Mining",                               "Disarm Trap",
            "Open",                                 "Treasure (DND)",                       "Calcified Elven Gems (DND)",           "Close",                                "Arm Trap",
            "Quick Open",                           "Quick Close",                          "Open Tinkering",                       "Open Kneeling",                        "Open Attacking",
            "Gahz'ridian (DND)",                    "Blasting",                             "PvP Open",                             "PvP Close",                            "Fishing (DND)",
            "Inscription",                          "Open From Vehicle"
        ),
        'stealthType'   => ["General", "Trap"],
        'invisibilityType' => ["General", 3 => "Trap", 6 => "Drunk"]
    ),
    'item' => array(
        'armor'         => "%s Armor",
        'block'         => "%s Block",
        'charges'       => "Charges",
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
        'openClick'     => "&lt;Right Click To Open&gt",
        'set'           => "Set",
        'partyLoot'     => "Party loot",
        'smartLoot'     => "Smart loot",
        'indestructible'=> "Cannot be destroyed",
        'deprecated'    => "Deprecated",
        'useInShape'    => "Usable when shapeshifted",
        'useInArena'    => "Usable in arenas",
        'refundable'    => "Refundable",
        'noNeedRoll'    => "Cannot roll Need",
        'atKeyring'     => "Can be placed in the keyring",
        'worth'         => "Worth",
        'consumable'    => "Consumable",
        'nonConsumable' => "Non-consumable",
        'accountWide'   => "Account-wide",
        'millable'      => "Millable",
        'noEquipCD'     => "No equip cooldown",
        'prospectable'  => "Prospectable",
        'disenchantable'=> "Disenchantable",
        'cantDisenchant'=> "Cannot be disenchanted",
        'repairCost'    => "Repair cost",
        'tool'          => "Tool",
        'cost'          => "Cost",
        'content'       => "Content",
        '_transfer'     => 'This item will be converted to <a href="?item=%d" class="q%d icontiny tinyspecial" style="background-image: url(images/icons/tiny/%s.gif)">%s</a> if you transfer to <span class="%s-icon">%s</span>.',
        '_unavailable'  => "This item is not available to players.",
        '_rndEnchants'  => "Random Enchantments",
        '_chance'       => "(%s%% chance)",
        'reqRating'     => "Requires personal and team arena rating of %d<br />in 3v3 or 5v5 brackets",
        'slot'          => "Slot",
        '_quality'      => "Quality",
        'usableBy'      => "Usable by",
        'buyout'        => "Buyout price",
        'each'          => "each",
        'gems'          => "Gems",
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
            "Binds when used",                          "Quest Item",                                           "Quest Item"
        ),
        "bagFamily"     => array(
            "Bag",                  "Quiver",           "Ammo Pouch",           "Soul Bag",                     "Leatherworking Bag",
            "Inscription Bag",      "Herb Bag",         "Enchanting Bag",       "Engineering Bag",              null, /*Key*/
            "Gem Bag",              "Mining Bag"
        ),
        'inventoryType' => array(
            null,                   "Head",             "Neck",                 "Shoulder",                     "Shirt",
            "Chest",                "Waist",            "Legs",                 "Feet",                         "Wrist",
            "Hands",                "Finger",           "Trinket",              "One-Hand",                     "Off Hand", /*Shield*/
            "Ranged",               "Back",             "Two-Hand",             "Bag",                          "Tabard",
            null, /*Robe*/          "Main Hand",        "Off Hand",             "Held In Off-Hand",             "Projectile",
            "Thrown",               null, /*Ranged2*/   "Quiver",               "Relic"
        ),
        'armorSubClass' => array(
            "Miscellaneous",        "Cloth",            "Leather",              "Mail",                         "Plate",
            null,                   "Shield",           "Libram",               "Idol",                         "Totem",
            "Sigil"
        ),
        'weaponSubClass'=> array(
            "Axe",                  "Axe",              "Bow",                  "Gun",                          "Mace",
            "Mace",                 "Polearm",          "Sword",                "Sword",                        null,
            "Staff",                null,               null,                   "Fist Weapon",                  "Miscellaneous",
            "Dagger",               "Thrown",           null,                   "Crossbow",                     "Wand",
            "Fishing Pole"
        ),
        'projectileSubClass' => array(
            null,                   null,               "Arrow",                "Bullet",                        null
        ),
        'elixirType'    => [null, "Battle", "Guardian"],
        'cat'           => array(                           // ordered by content firts, then alphabeticaly
             2 => "Weapons",                                // self::$spell['weaponSubClass']
             4 => array("Armor", array(
                 1 => "Cloth Armor",                 2 => "Leather Armor",           3 => "Mail Armor",              4 => "Plate Armor",             6 => "Shields",                 7 => "Librams",
                 8 => "Idols",                       9 => "Totems",                 10 => "Sigils",                 -6 => "Cloaks",                 -5 => "Off-hand Frills",        -8 => "Shirts",
                -7 => "Tabards",                    -3 => "Amulets",                -2 => "Rings",                  -4 => "Trinkets",                0 => "Miscellaneous (Armor)",
            )),
             1 => array("Containers", array(
                 0 => "Bags",                        3 => "Enchanting Bags",         4 => "Engineering Bags",        5 => "Gem Bags",                2 => "Herb Bags",               8 => "Inscription Bags",
                 7 => "Leatherworking Bags",         6 => "Mining Bags",             1 => "Soul Bags"
            )),
             0 => array("Consumables", array(
                -3 => "Item Enhancements (Temporary)",                               6 => "Item Enhancements (Permanent)",                           2 => ["Elixirs", [1 => "Battle Elixirs", 2 => "Guardian Elixirs"]],
                 1 => "Potions",                     4 => "Scrolls",                 7 => "Bandages",                0 => "Consumables",             3 => "Flasks",                  5 => "Food & Drinks",
                 8 => "Other (Consumables)"
            )),
            16 => array("Glyphs", array(
                 1 => "Warrior Glyphs",              2 => "Paladin Glyphs",          3 => "Hunter Glyphs",           4 => "Rogue Glyphs",            5 => "Priest Glyphs",           6 => "Death Knight Glyphs",
                 7 => "Shaman Glyphs",               8 => "Mage Glyphs",             9 => "Warlock Glyphs",         11 => "Druid Glyphs"
            )),
             7 => array("Trade Goods", array(
                14 => "Armor Enchantments",          5 => "Cloth",                   3 => "Devices",                10 => "Elemental",              12 => "Enchanting",              2 => "Explosives",
                 9 => "Herbs",                       4 => "Jewelcrafting",           6 => "Leather",                13 => "Materials",               8 => "Meat",                    7 => "Metal & Stone",
                 1 => "Parts",                      15 => "Weapon Enchantments",    11 => "Other (Trade Goods)"
             )),
             6 => ["Projectiles", [                  2 => "Arrows",                  3 => "Bullets"     ]],
            11 => ["Quivers",     [                  2 => "Quivers",                 3 => "Ammo Pouches"]],
             9 => array("Recipes", array(
                 0 => "Books",                       6 => "Alchemy Recipes",         4 => "Blacksmithing Plans",     5 => "Cooking Recipes",         8 => "Enchanting Formulae",     3 => "Engineering Schematics",
                 7 => "First Aid Books",             9 => "Fishing Books",          11 => "Inscription Techniques", 10 => "Jewelcrafting Designs",   1 => "Leatherworking Patterns",12 => "Mining Guides",
                 2 => "Tailoring Patterns"
            )),
             3 => array("Gems", array(
                 6 => "Meta Gems",                   0 => "Red Gems",                1 => "Blue Gems",               2 => "Yellow Gems",             3 => "Purple Gems",             4 => "Green Gems",
                 5 => "Orange Gems",                 8 => "Prismatic Gems",          7 => "Simple Gems"
            )),
            15 => array("Miscellaneous", array(
                -2 => "Armor Tokens",                3 => "Holiday",                 0 => "Junk",                    1 => "Reagents",                5 => "Mounts",                 -7 => "Flying Mounts",
                 2 => "Small Pets",                  4 => "Other (Miscellaneous)"
            )),
            10 => "Currency",
            12 => "Quest",
            13 => "Keys",
        ),
        'statType'      => array(
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
    'colon'             => ': ',
    'dateFmtShort'      => "Y/m/d",
    'dateFmtLong'       => "Y/m/d \a\\t H:i"
);

?>
