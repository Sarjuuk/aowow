<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$lang = array(
    // page variables
    'main' => array(
        'link'          => "Link",
        'signIn'        => "Sign in",
        'jsError'       => "Please make sure you have javascript enabled.",
        'searchButton'  => "Search",
        'language'      => "Language",
        'numSQL'        => "Number of MySQL queries",
        'timeSQL'       => "Time of MySQL queries",
        'noJScript'     => "<b>This site makes extensive use of JavaScript.</b><br />Please <a href=\"https://www.google.com/support/adsense/bin/answer.py?answer=12654\" target=\"_blank\">enable JavaScript</a> in your browser.",
        'profiles'      => "Your Characters",
        'links'         => "Links",
        'pageNotFound'  => "This %s doesn't exist.",
        'both'          => "Both",
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
        // err_title = An error in AoWoW
        // un_err = Enter your username
        // pwd_err = Enter your password
        // signin_msg = Enter your game account
        // c_pwd = Repeat password
        // create_filter = Create a filter
        // loading = Loading ...
        // soldby = Sold by
        // droppedby = Dropped by
        // containedinobject = Contained in
        // containedinitem = Contained in item
        // contain = Contains
        // objectiveof = Objective of
        // rewardof = Reward of
        // facts = Facts
        // pickpocketingloot = Pickpocketing
        // prospectedfrom = Prospect from
        // canbeplacedin = Can be placed in
        // minedfromobject = Mined from
        // gatheredfromobject = Gathered from
        // items = Items
        // objects = Objects
        // quests = Quests
        // npcs = NPCs
        // drop = Drop
        // starts = Starts
        // ends = Ends
        // skinning = Skinning
        // pickpocketing = Pickpocketing
        // sells = Sells
        // reputationwith = Reputation with
        // experience = experience
        // uponcompletionofthisquestyouwillgain = Upon completion of quests, get
        // reagentfor = Reagent for
        // skinnedfrom = Skinned from
        // disenchanting = Disenchanting
        // This_Object_cant_be_found = Object map not available, Object may be spawned via a script
        // itemsets = Item Sets
        // Spells = Spells
        // Items = Items
        // Quests = Quests
        // Factions = Factions
        // Item_Sets = Item sets
        // NPCs = NPCs
        // Objects = Objects
        // Compare = Item Comparison Tool
        // My_account = My account
        // Comments = Comments
        // Latest_Comments = Latest comments
        // day = days
        // hr = hr
        // min = min
        // sec = sec
        // Respawn = Respawn
        // Class = Class
        // class = class
        // race = race
        // Race = Race
        // Races = Races
        // name = name
        // Name = Name
        // slain = slain
        'name'          => "Name",
        'disabled'      => "Disabled",
        'disabledHint'  => "Cannot be attained or completed",
        'serverside'    => "Serverside",
        'serversideHint' => "These informations are not in the Client and have been provided by sniffing and/or guessing.",
    ),
    'search' => array(
        'search'        => "Search",
        'foundResult'   => "Search Results for",
        'noResult'      => "No Results for",
        'tryAgain'      => "Please try some different keywords or check your spelling.",
    ),
    'game' => array(
        'alliance'      => "Alliance",
        'horde'         => "Horde",
        'class'         => "class",
        'classes'       => "Classes",
        'races'         => "Races",
        'title'         => "Title",
        'titles'        => "Titles",
        'eventShort'    => "Event",
        'event'         => "World Event",
        'events'        => "World Events",
        'cooldown'      => "%s cooldown",
        'requires'      => "Requires",
        'reqLevel'      => "Requires Level %s",
        'reqLevelHlm'   => "Requires Level %s",
        'valueDelim'    => " to ",
        'resistances'   => array(null, 'Holy Resistance', 'Fire Resistance', 'Nature Resistance', 'Frost Resistance', 'Shadow Resistance', 'Arcane Resistance'),
        'di'            => array(null, "Magic", "Curse", "Disease", "Poison", "Stealth", "Invisibility", null, null, "Enrage"),
        'sc'            => array("Physical", "Holy", "Fire", "Nature", "Frost", "Shadow", "Arcane"),
        'cl'            => array("UNK_CL0", "Warrior", "Paladin", "Hunter", "Rogue", "Priest", "Death Knight", "Shaman", "Mage", "Warlock", 'UNK_CL10', "Druid"),
        'ra'            => array(-2 => "Horde", -1 => "Alliance", "Both", "Human", "Orc", "Dwarf", "Night Elf", "Undead", "Tauren", "Gnome", "Troll", 'UNK_RA9', "Blood Elf", "Draenei"),
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
        'pvpRank'       => array(
            null,                                       "Private / Scout",                      "Corporal / Grunt",
            "Sergeant / Sergeant",                      "Master Sergeant / Senior Sergeant",    "Sergeant Major / First Sergeant",
            "Knight / Stone Guard",                     "Knight-Lieutenant / Blood Guard",      "Knight-Captain / Legionnare",
            "Knight-Champion / Centurion",              "Lieutenant Commander / Champion",      "Commander / Lieutenant General",
            "Marshal / General",                        "Field Marshal / Warlord",              "Grand Marshal / High Warlord"
        ),
    ),
    'filter' => array(
        'extSearch'     => "Extended search",
        'onlyAlliance'  => "Alliance only",
        'onlyHorde'     => "Horde only",
        'addFilter'     => "Add another Filter",
        'match'         => "Match",
        'allFilter'     => "All filters",
        'oneFilter'     => "At least one",
        'applyFilter'   => "Apply filter",
        'resetForm'     => "Reset Form",
        'refineSearch'  => "Tip: Refine your search by browsing a <a href=\"javascript:;\" id=\"fi_subcat\">subcategory</a>.",
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
        'viewPublicDesc' => "View your Public Description in your <a href=\"?user=%s\">Profile  Page</a>",


        // Please_enter_your_username = Enter your username (account)
        // Please_enter_your_password = Enter your password
        // Sign_in_to_your_Game_Account = Enter your game account:
        // Please_enter_your_confirm_password = Please enter your confirm password
    ),
    'achievement' => array(
        'achievements'  => "achievements",
        'criteria'      => "Criteria",
        'achievement'   => "achievement",
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
        'zone'          => "Zone",
        'zonePartOf'    => "This zone is part of",
    ),
    'title' => array(
        'cat'           => array(
            'General',      'Player vs. Player',    'Reputation',       'Dungeons & Raids',     'Quests',       'Professions',      'World Events'
        )
    ),
    'spell' => array(
        'remaining'     => "%s remaining",
        'castIn'        => "%s sec cast",
        'instantPhys'   => "Instant",
        'instantMagic'  => "Instant cast",
        'channeled'     => "Channeled",
        'range'         => "%s yd range",
        'meleeRange'    => "Melee Range",
        'reagents'      => "Reagents",
        'tools'         => "Tools",
        'pctCostOf'     => "of base %s",
        'costPerSec'    => ", plus %s per second",
        'costPerLevel'  => ", plus %s per level",
        'powerTypes'    => array(
            -2 => "Health",   -1 => null,   "Mana",     "Rage",     "Focus",    "Energy",       "Happiness",        "Rune",    "Runic Power",
            'AMMOSLOT' => "Ammo",           'STEAM' => "Steam Pressure",        'WRATH' => "Wrath",                 'PYRITE' => "Pyrite",
            'HEAT' => "Heat",               'OOZE' => "Ooze",                   'BLOOD_POWER' => "Blood Power"
        )
    ),
    'item' => array(
        'armor'         => "Armor",
        'block'         => "Block",
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
        'duration'      => "Duration",
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
            "Hands",                "Finger",           "Trinket",              "One-hand",                     "Off Hand",
            "Ranged",               "Back",             "Two-hand",             "Bag",                          "Tabard",
            "Chest",                "Main Hand",        "Off Hand",             "Held In Off-Hand",             "Projectile",
            "Thrown",               "Ranged",           "Quiver",               "Relic"
        ),
        'armorSubclass' => array(
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
    )
);

?>
