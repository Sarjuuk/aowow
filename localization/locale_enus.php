<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');



// comments in CAPS point to items in \Interface\FrameXML\GlobalStrings.lua - lowercase sources are contextual



$lang = array(
    // page variables
    'timeUnits' => array(
        'sg'            => ["year",  "month",  "week",  "day",  "hour",  "minute",  "second",  "millisecond"],
        'pl'            => ["years", "months", "weeks", "days", "hours", "minutes", "seconds", "milliseconds"],
        'ab'            => ["yr",    "mo",     "wk",    "day",  "hr",    "min",     "sec",     "ms"]
    ),
    'main' => array(
        'name'          => "name",
        'link'          => "Link",
        'signIn'        => "Log in / Register",
        'jsError'       => "Please make sure you have javascript enabled.",
        'language'      => "Language",
        'feedback'      => "Feedback",
        'numSQL'        => "Number of MySQL queries",
        'timeSQL'       => "Time of MySQL queries",
        'noJScript'     => '<b>This site makes extensive use of JavaScript.</b><br />Please <a href="https://www.google.com/support/adsense/bin/answer.py?answer=12654" target="_blank">enable JavaScript</a> in your browser.',
        'userProfiles'  => "My Profiles",
        'pageNotFound'  => "This %s doesn't exist.",
        'gender'        => "Gender",
        'sex'           => [null, "Male", "Female"],
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
        'siteRep'       => "Reputation",
        'yourRepHistory'=> "Your Reputation History",
        'aboutUs'       => "About us & contact",
        'and'           => " and ",
        'or'            => " or ",
        'back'          => "Back",
        'reputationTip' => "Reputation points",
        'byUserTimeAgo' => 'By <a href="'.HOST_URL.'/?user=%s">%1$s</a> %s ago',
        'help'          => "Help",

        // filter
        'extSearch'     => "Extended search",
        'addFilter'     => "Add another Filter",
        'match'         => "Match",
        'allFilter'     => "All filters",
        'oneFilter'     => "At least one",
        'applyFilter'   => "Apply filter",
        'resetForm'     => "Reset Form",
        'refineSearch'  => 'Tip: Refine your search by browsing a <a href="javascript:;" id="fi_subcat">subcategory</a>.',
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
        'errPageTitle'  => "Page not found",
        'nfPageTitle'   => "Error",
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
            ["None", "none"],         ["Slot", "slot"],       ["Level", "level"],     ["Source", "source"]
        ),
        'compareTool'   => "Item Comparison Tool",
        'talentCalc'    => "Talent Calculator",
        'petCalc'       => "Hunter Pet Calculator",
        'chooseClass'   => "Choose a class",
        'chooseFamily'  => "Choose a pet family",

        // profiler
        'realm'         => "Realm",
        'region'        => "Region",
        'viewCharacter' => "View Character",
        '_cpHead'       => "Character Profiler",
        '_cpHint'       => "The <b>Character Profiler</b> lets you edit your character, find gear upgrades, check your gearscore and more!",
        '_cpHelp'       => "To get started, just follow the steps below. If you'd like more information, check out our extensive <a href=\"?help=profiler\">help page</a>.",
        '_cpFooter'     => "If you want a more refined search try out our <a href=\"?profiles\">advanced search</a> options. You can also create a <a href=\"?profile&amp;new\">new custom profile</a>.",

        // search
        'search'        => "Search",
        'searchButton'  => "Search",
        'foundResult'   => "Search Results for",
        'noResult'      => "No Results for",
        'tryAgain'      => "Please try some different keywords or check your spelling.",
        'ignoredTerms'  => "The following words were ignored in your search: %s",

        // formating
        'colon'         => ': ',
        'dateFmtShort'  => "Y/m/d",
        'dateFmtLong'   => "Y/m/d \a\\t H:i",

        // error
        'intError'      => "An internal error has occurred.",
        'intError2'     => "An internal error has occurred. (%s)",
        'genericError'  => "An error has occurred; refresh the page and try again. If the error persists email <a href=\"#contact\">feedback</a>", # LANG.genericerror
        'bannedRating'  => "You have been banned from rating comments.", # LANG.tooltip_banned_rating
        'tooManyVotes'  => "You have reached the daily voting cap. Come back tomorrow!", # LANG.tooltip_too_many_votes

        'moreTitles'    => array(
            'reputation'    => "Website Reputation",
            'whats-new'     => "What's New",
            'searchbox'     => "Search Box",
            'tooltips'      => "Tooltips",
            'faq'           => "Frequently Asked Questions",
            'aboutus'       => "What is AoWoW?",
            'searchplugins' => "Search Plugins",
            'privileges'    => "Privileges",
            'top-users'     => "Top Users",
            'help'          => array(
                'commenting-and-you' => "Commenting and You",               'modelviewer'       => "Model Viewer",              'screenshots-tips-tricks' => "Screenshots: Tips & Tricks",
                'stat-weighting'     => "Stat Weighting",                   'talent-calculator' => "Talent Calculator",         'item-comparison'         => "Item Comparison",
                'profiler'           => "Profiler",                         'markup-guide'      => "Markup Guide"
            )
        )
    ),
    'screenshot' => array(
        'submission'    => "Screenshot Submission",
        'selectAll'     => "Select all",
        'cropHint'      => "You may crop your screenshot and enter a caption.",
        'displayOn'     => "Displayed on:[br]%s - [%s=%d]",
        'caption'       => "Caption",
        'charLimit'     => "Optional, up to 200 characters",
        'thanks'        => array(
            'contrib' => "Thanks a lot for your contribution!",
            'goBack'  => '<a href="?%s=%d">Click here</a> to go back to the page you came from.',
            'note'    => "Note: Your screenshot will need to be approved before appearing on the site. This can take up to 72 hours."
        ),
        'error'         => array(
            'unkFormat'   => "Unknown image format.",
            'tooSmall'    => "Your screenshot is way too small. (&lt; ".CFG_SCREENSHOT_MIN_SIZE."x".CFG_SCREENSHOT_MIN_SIZE.").",
            'selectSS'    => "Please select the screenshot to upload.",
            'notAllowed'  => "You are not allowed to upload screenshots!",
        )
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
        'emote'         => "emote",
        'emotes'        => "Emotes",
        'enchantment'   => "enchantment",
        'enchantments'  => "Enchantments",
        'object'        => "object",
        'objects'       => "Objects",
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
        'icon'          => "icon",
        'icons'         => "icons",
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
        'reqSkillLevel' => "Required skill level",
        'level'         => "Level",
        'school'        => "School",
        'skill'         => "skill",
        'skills'        => "Skills",
        'sound'         => "sound",
        'sounds'        => "Sounds",
        'spell'         => "spell",
        'spells'        => "Spells",
        'type'          => "Type",
        'valueDelim'    => " to ",
        'zone'          => "zone",
        'zones'         => "Zones",

        'pvp'           => "PvP",                           // PVP
        'honorPoints'   => "Honor Points",                  // HONOR_POINTS
        'arenaPoints'   => "Arena Points",                  // ARENA_POINTS
        'heroClass'     => "Hero class",
        'resource'      => "Resource",
        'resources'     => "Resources",
        'role'          => "Role",                          // ROLE
        'roles'         => "Roles",                         // LFG_TOOLTIP_ROLES
        'specs'         => "Specs",
        '_roles'        => ["Healer", "Melee DPS", "Ranged DPS", "Tank"],

        'phases'        => "Phases",
        'mode'          => "Mode",
        'modes'         => [-1 => "Any", "Normal / Normal 10", "Heroic / Normal 25", "Heroic 10", "Heroic 25"],
        'expansions'    => ["Classic", "The Burning Crusade", "Wrath of the Lich King"],
        'stats'         => ["Strength", "Agility", "Stamina", "Intellect", "Spirit"],
        'sources'       => array(
            "Unknown",                      "Crafted",                      "Drop",                         "PvP",                          "Quest",                        "Vendor",
            "Trainer",                      "Discovery",                    "Redemption",                   "Talent",                       "Starter",                      "Event",
            "Achievement",                  null,                           "Black Market",                 "Disenchanted",                 "Fished",                       "Gathered",
            "Milled",                       "Mined",                        "Prospected",                   "Pickpocketed",                 "Salvaged",                     "Skinned",
            "In-Game Store"
        ),
        'languages'     => array(                           // Languages.dbc
             1 => "Orcish",                  2 => "Darnassian",              3 => "Taurahe",                 6 => "Dwarvish",                7 => "Common",                  8 => "Demonic",
             9 => "Titan",                  10 => "Thalassian",             11 => "Draconic",               12 => "Kalimag",                13 => "Gnomish",                14 => "Troll",
            33 => "Gutterspeak",            35 => "Draenei",                36 => "Zombie",                 37 => "Gnomish Binary",         38 => "Goblin Binary"
        ),
        'gl'            => [null, "Major", "Minor"],                                                                                                                                // MAJOR_GLYPH, MINOR_GLYPH
        'si'            => [1 => "Alliance", -1 => "Alliance only", 2 => "Horde", -2 => "Horde only", 3 => "Both"],
        'resistances'   => [null, 'Holy Resistance', 'Fire Resistance', 'Nature Resistance', 'Frost Resistance', 'Shadow Resistance', 'Arcane Resistance'],                         // RESISTANCE?_NAME
        'dt'            => [null, "Magic", "Curse", "Disease", "Poison", "Stealth", "Invisibility", null, null, "Enrage"],                                                          // SpellDispalType.dbc
        'sc'            => ["Physical", "Holy", "Fire", "Nature", "Frost", "Shadow", "Arcane"],                                                                                     // STRING_SCHOOL_*
        'cl'            => [null, "Warrior", "Paladin", "Hunter", "Rogue", "Priest", "Death Knight", "Shaman", "Mage", "Warlock", null, "Druid"],                                   // ChrClasses.dbc
        'ra'            => [-2 => "Horde", -1 => "Alliance", "Both", "Human", "Orc", "Dwarf", "Night Elf", "Undead", "Tauren", "Gnome", "Troll", null, "Blood Elf", "Draenei"],     // ChrRaces.dbc
        'rep'           => ["Hated", "Hostile", "Unfriendly", "Neutral", "Friendly", "Honored", "Revered", "Exalted"],                                                              // FACTION_STANDING_LABEL*
        'st'            => array(                           // SpellShapeshiftForm.dbc
            "Default",                      "Cat Form",                     "Tree of Life",                 "Travel Form",                  "Aquatic Form",                 "Bear From",
            null,                           null,                           "Dire Bear Form",               null,                           null,                           null,
            null,                           "Shadowdance",                  null,                           null,                           "Ghostwolf",                    "Battle Stance",
            "Defensive Stance",             "Berserker Stance",             null,                           null,                           "Metamorphosis",                null,
            null,                           null,                           null,                           "Swift Flight Form",            "Shadow Form",                  "Flight Form",
            "Stealth",                      "Moonkin Form",                 "Spirit of Redemption"
        ),
        'me'            => array(                           // SpellMechanic.dbc .. not quite
            null,                           "Charmed",                      "Disoriented",                  "Disarmed",                     "Distracted",                   "Fleeing",
            "Gripped",                      "Rooted",                       "Pacified",                     "Silenced",                     "Asleep",                       "Ensnared",
            "Stunned",                      "Frozen",                       "Incapacitated",                "Bleeding",                     "Healing",                      "Polymorphed",
            "Banished",                     "Shielded",                     "Shackled",                     "Mounted",                      "Seduced",                      "Turned",
            "Horrified",                    "Invulnerable",                 "Interrupted",                  "Dazed",                        "Discovery",                    "Invulnerable",
            "Sapped",                       "Enraged"
        ),
        'ct'            => array(                           // CreatureType.dbc
            "Uncategorized",                "Beast",                        "Dragonkin",                    "Demon",                        "Elemental",                    "Giant",
            "Undead",                       "Humanoid",                     "Critter",                      "Mechanical",                   "Not specified",                "Totem",
            "Non-combat Pet",               "Gas Cloud"
        ),
        'fa'            => array(                           // CreatureFamily.dbc
             1 => "Wolf",                    2 => "Cat",                     3 => "Spider",                  4 => "Bear",                    5 => "Boar",                    6 => "Crocolisk",
             7 => "Carrion Bird",            8 => "Crab",                    9 => "Gorilla",                11 => "Raptor",                 12 => "Tallstrider",            20 => "Scorpid",
            21 => "Turtle",                 24 => "Bat",                    25 => "Hyena",                  26 => "Bird of Prey",           27 => "Wind Serpent",           30 => "Dragonhawk",
            31 => "Ravager",                32 => "Warp Stalker",           33 => "Sporebat",               34 => "Nether Ray",             35 => "Serpent",                37 => "Moth",
            38 => "Chimaera",               39 => "Devilsaur",              41 => "Silithid",               42 => "Worm",                   43 => "Rhino",                  44 => "Wasp",
            45 => "Core Hound",             46 => "Spirit Beast"
        ),
        'pvpRank'       => array(                           // PVP_RANK_\d_\d(_FEMALE)?
            null,                                                           "Private / Scout",                                              "Corporal / Grunt",
            "Sergeant / Sergeant",                                          "Master Sergeant / Senior Sergeant",                            "Sergeant Major / First Sergeant",
            "Knight / Stone Guard",                                         "Knight-Lieutenant / Blood Guard",                              "Knight-Captain / Legionnare",
            "Knight-Champion / Centurion",                                  "Lieutenant Commander / Champion",                              "Commander / Lieutenant General",
            "Marshal / General",                                            "Field Marshal / Warlord",                                      "Grand Marshal / High Warlord"
        ),
    ),
    'account' => array(
        'title'         => "Aowow Account",
        'email'         => "Email address",
        'continue'      => "Continue",
        'groups'        => array(
            -1 => "None",                   "Tester",                       "Administrator",                "Editor",                       "Moderator",                    "Bureaucrat",
            "Developer",                    "VIP",                          "Blogger",                      "Premium",                      "Localizer",                    "Sales agent",
            "Screenshot manager",           "Video manager",                "API partner",                  "Pending"
        ),
        // signIn
        'doSignIn'      => "Log in to your AoWoW Account",
        'signIn'        => "Log In",
        'user'          => "Username",
        'pass'          => "Password",
        'rememberMe'    => "Stay logged in",
        'forgot'        => "Forgot",
        'forgotUser'    => "Username",
        'forgotPass'    => "Password",
        'accCreate'     => 'Don\'t have an account? <a href="?account=signup">Create one now!</a>',

        // recovery
        'recoverUser'   => "Username Request",
        'recoverPass'   => "Password Reset: Step %s of 2",
        'newPass'       => "New Password",

        // creation
        'register'      => "Registration - Step %s of 2",
        'passConfirm'   => "Confirm password",

        // dashboard
        'ipAddress'     => "IP-Adress",
        'lastIP'        => "last used IP",
        'myAccount'     => "My Account",
        'editAccount'   => "Simply use the forms below to update your account information",
        'viewPubDesc'   => 'View your Public Description in your <a href="?user=%s">Profile  Page</a>',

        // bans
        'accBanned'     => "This Account was closed",
        'bannedBy'      => "Banned by",
        'ends'          => "Ends on",
        'permanent'     => "The ban is permanent",
        'reason'        => "Reason",
        'noReason'      => "No reason was given.",

        // form-text
        'emailInvalid'  => "That email address is not valid.", // message_emailnotvalid
        'emailNotFound' => "The email address you entered is not associated with any account.<br><br>If you forgot the email you registered your account with email ".CFG_CONTACT_EMAIL." for assistance.",
        'createAccSent' => "An email was sent to <b>%s</b>. Simply follow the instructions to create your account.",
        'recovUserSent' => "An email was sent to <b>%s</b>. Simply follow the instructions to recover your username.",
        'recovPassSent' => "An email was sent to <b>%s</b>. Simply follow the instructions to reset your password.",
        'accActivated'  => 'Your account has been activated.<br>Proceed to <a href="?account=signin&token=%s">sign in</a>',
        'userNotFound'  => "The username you entered does not exists.",
        'wrongPass'     => "That password is not vaild.",
        // 'accInactive'   => "That account has not yet been confirmed active.",
        'loginExceeded' => "The maximum number of logins from this IP has been exceeded. Please try again in %s.",
        'signupExceeded'=> "The maximum number of signups from this IP has been exceeded. Please try again in %s.",
        'errNameLength' => "Your username must be at least 4 characters long.", // message_usernamemin
        'errNameChars'  => "Your username can only contain letters and numbers.", // message_usernamenotvalid
        'errPassLength' => "Your password must be at least 6 characters long.", // message_passwordmin
        'passMismatch'  => "The passwords you entered do not match.",
        'nameInUse'     => "That username is already taken.",
        'mailInUse'     => "That email is already registered to an account.",
        'isRecovering'  => "This account is already recovering. Follow the instructions in your email or wait %s for the token to expire.",
        'passCheckFail' => "Passwords do not match.", // message_passwordsdonotmatch
        'newPassDiff'   => "Your new password must be different than your previous one." // message_newpassdifferent
    ),
    'user' => array(
        'notFound'      => "User \"%s\" not found!",
        'removed'       => "(Removed)",
        'joinDate'      => "Joined",
        'lastLogin'     => "Last visit",
        'userGroups'    => "Role",
        'consecVisits'  => "Consecutive visits",
        'publicDesc'    => "Public Description",
        'profileTitle'  => "%s's Profile",
        'contributions' => "Contributions",
        'uploads'       => "Data uploads",
        'comments'      => "Comments",
        'screenshots'   => "Screenshots",
        'videos'        => "Videos",
        'posts'         => "Forum posts"
    ),
    'mail' => array(
        'tokenExpires'  => "This token expires in %s.",
        'accConfirm'    => ["Account Confirmation", "Welcome to ".CFG_NAME_SHORT."!\r\n\r\nClick the Link below to activate your account.\r\n\r\n".HOST_URL."?account=signup&token=%s\r\n\r\nIf you did not request this mail simply ignore it."],
        'recoverUser'   => ["User Recovery",        "Follow this link to log in.\r\n\r\n".HOST_URL."?account=signin&token=%s\r\n\r\nIf you did not request this mail simply ignore it."],
        'resetPass'     => ["Password Reset",       "Follow this link to reset your password.\r\n\r\n".HOST_URL."?account=forgotpassword&token=%s\r\n\r\nIf you did not request this mail simply ignore it."]
    ),
    'emote' => array(
        'notFound'      => "This Emote doesn't exist.",
        'self'          => "To Yourself",
        'target'        => "To others with a target",
        'noTarget'      => "To others without a target",
        'isAnimated'    => "Uses an animation",
        'aliases'       => "Aliases",
        'noText'        => "This Emote has no text.",
    ),
    'enchantment' => array(
        'details'       => "Details",
        'activation'    => "Activation",
        'notFound'      => "This enchantment doesn't exist.",
        'types'         => array(
            1 => "Proc Spell",              3 => "Equip Spell",             7 => "Use Spell",               8 => "Prismatic Socket",
            5 => "Statistics",              2 => "Weapon Damage",           6 => "DPS",                     4 => "Defense"
        )
    ),
    'gameObject' => array(
        'notFound'      => "This object doesn't exist.",
        'cat'           => [0 => "Other", 9 => "Books", 3 => "Containers", -5 => "Chests", 25 => "Fishing Pools", -3 => "Herbs", -4 => "Mineral Veins", -2 => "Quest", -6 => "Tools"],
        'type'          => [              9 => "Book",  3 => "Container",  -5 => "Chest",  25 => "",              -3 => "Herb",  -4 => "Mineral Vein",  -2 => "Quest", -6 => ""],
        'unkPosition'   => "The location of this object is unknown.",
        'npcLootPH'     => 'The <b>%s</b> contains the loot from the fight against <a href="?npc=%d">%s</a>. It spawns after his death.',
        'key'           => "Key",
        'focus'         => "Spell Focus",
        'focusDesc'     => "Spells requiring this Focus can be cast near this Object",
        'trap'          => "Trap",
        'triggeredBy'   => "Triggered by",
        'capturePoint'  => "Capture Point",
        'foundIn'       => "This object can be found in",
        'restock'       => "Restocks every %s."
    ),
    'npc' => array(
        'notFound'      => "This NPC doesn't exist.",
        'classification'=> "Classification",
        'petFamily'     => "Pet familiy",
        'react'         => "React",
        'worth'         => "Worth",
        'unkPosition'   => "The location of this NPC is unknown.",
        'difficultyPH'  => "This NPC is a placeholder for a different mode of",
        'seat'          => "Seat",
        'accessory'     => "Accessories",
        'accessoryFor'  => "This NPC is an accessory for vehicle",
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
        'tameable'      => "Tameable (%s)",
        'waypoint'      => "Waypoint",
        'wait'          => "Wait",
        'respawnIn'     => "Respawn in",
        'rank'          => [0 => "Normal", 1 => "Elite", 4 => "Rare", 2 => "Rare Elite", 3 => "Boss"],
        'textRanges'    => [null, "sent to area", "sent to zone", "sent to map", "sent to world"],
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
        'notFound'      => "This world event doesn't exist.",
        'start'         => "Start",
        'end'           => "End",
        'interval'      => "Interval",
        'inProgress'    => "Event is currently in progress",
        'category'      => ["Uncategorized", "Holidays", "Recurring", "Player vs. Player"]
    ),
    'achievement' => array(
        'notFound'      => "This achievement doesn't exist.",
        'criteria'      => "Criteria",
        'points'        => "Points",
        'series'        => "Series",
        'outOf'         => "out of",
        'criteriaType'  => "Criterium Type-Id:",
        'itemReward'    => "You will receive",
        'titleReward'   => 'You shall be granted the title "<a href="?title=%d">%s</a>"',
        'slain'         => "slain",
        'reqNumCrt'     => "Requires",
        'rfAvailable'   => "Available on realm: ",
        '_transfer'     => 'This achievement will be converted to <a href="?achievement=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
    ),
    'chrClass' => array(
        'notFound'      => "This class doesn't exist."
    ),
    'race' => array(
        'notFound'      => "This race doesn't exist.",
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
    ),
    'privileges' => array(
        'main'          => "Here on our Site you can generate <a href=\"?reputation\">reputation</a>. The main way to generate it is to get your comments upvotes.<br><br>So, reputation is a rough measure of how much you contributed to the community.<br><br>As you amass reputation you earn the community's trust and you will be granted with additional privileges. You can find a full list below.",
        'privilege'     => "Privilege",
        'privileges'    => "Privileges",
        'requiredRep'   => "Reputation Required",
        'reqPoints'     => "This privilege requires <b>%s</b> reputation points.",
        '_privileges'   => array(
            null,                                   "Post comments",                                "Post external links",                              null,
            "No CAPTCHAs",                          "Comment votes worth more",                     null,                                               null,
            null,                                   "More votes per day",                           "Upvote comments",                                  "Downvote comments",
            "Post comment replies",                 "Border: Uncommon",                             "Border: Rare",                                     "Border: Epic",
            "Border: Legendary",                    "AoWoW Premium"
        )
    ),
    'zone' => array(
        'notFound'      => "This zone doesn't exist.",
        'attunement'    => ["Attunement", "Heroic attunement"],
        'key'           => ["Key", "Heroic key"],
        'location'      => "Location",
        'raidFaction'   => "Raid faction",
        'boss'          => "Final boss",
        'reqLevels'     => "Required levels: [tooltip=instancereqlevel_tip]%d[/tooltip], [tooltip=lfgreqlevel_tip]%d[/tooltip]",
        'zonePartOf'    => "This zone is part of [zone=%s].",
        'autoRez'       => "Automatic resurrection",
        'city'          => "City",
        'territory'     => "Territory",
        'instanceType'  => "Instance type",
        'hcAvailable'   => "Heroic mode available&nbsp;(%d)",
        'numPlayers'    => "Number of players",
        'noMap'         => "There is no map available for this zone.",
        'instanceTypes' => ["Zone",     "Transit", "Dungeon",   "Raid",      "Battleground", "Dungeon",  "Arena", "Raid", "Raid"],
        'territories'   => ["Alliance", "Horde",   "Contested", "Sanctuary", "PvP",          "World PvP"],
        'cat'           => array(
            "Eastern Kingdoms",         "Kalimdor",                 "Dungeons",                 "Raids",                    "Unused",                   null,
            "Battlegrounds",            null,                       "Outland",                  "Arenas",                   "Northrend"
        )
    ),
    'quest' => array(
        'notFound'      => "This quest doesn't exist.",
        '_transfer'     => 'This quest will be converted to <a href="?quest=%d" class="q1">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        'questLevel'    => "Level %s",
        'requirements'  => "Requirements",
        'reqMoney'      => "Required money",                // REQUIRED_MONEY
        'money'         => "Money",
        'additionalReq' => "Additional requirements to obtain this quest",
        'reqRepWith'    => 'Your reputation with <a href="?faction=%d">%s</a> must be %s %s',
        'reqRepMin'     => "at least",
        'reqRepMax'     => "lower than",
        'progress'      => "Progress",
        'provided'      => "Provided",
        'providedItem'  => "Provided item",
        'completion'    => "Completion",
        'description'   => "Description",
        'playerSlain'   => "Players slain",
        'profession'    => "Profession",
        'timer'         => "Timer",
        'loremaster'    => "Loremaster",
        'suggestedPl'   => "Suggested players",
        'keepsPvpFlag'  => "Keeps you PvP flagged",
        'daily'         => "Daily",
        'weekly'        => "Weekly",
        'monthly'       => "Monthly",
        'sharable'      => "Sharable",
        'notSharable'   => "Not sharable",
        'repeatable'    => "Repeatable",
        'reqQ'          => "Requires",
        'reqQDesc'      => "To take this quest, you must complete all these quests",
        'reqOneQ'       => "Requires one of",
        'reqOneQDesc'   => "To take this quest, you must complete one of the following quests",
        'opensQ'        => "Opens Quests",
        'opensQDesc'    => "Completing this quest is requires to take this quests",
        'closesQ'       => "Closes Quests",
        'closesQDesc'   => "After completing this quest, you will not be able to take these quests",
        'enablesQ'      => "Enables",
        'enablesQDesc'  => "When this quest is active, these quests are also available",
        'enabledByQ'    => "Enabled by",
        'enabledByQDesc'=> "This quest is available only, when one of these quests are active",
        'gainsDesc'     => "Upon completion of this quest you will gain",
        'theTitle'      => 'the title "%s"',                                        // partly REWARD_TITLE
        'mailDelivery'  => "You will receive this letter%s%s",
        'mailBy'        => ' by <a href="?npc=%d">%s</a>',
        'mailIn'        => " after %s",
        'unavailable'   => "This quest was marked obsolete and cannot be obtained or completed.",
        'experience'    => "experience",
        'expConvert'    => "(or %s if completed at level %d)",
        'expConvert2'   => "%s if completed at level %d",
        'chooseItems'   => "You will be able to choose one of these rewards",       // REWARD_CHOICES
        'receiveItems'  => "You will receive",                                      // REWARD_ITEMS_ONLY
        'receiveAlso'   => "You will also receive",                                 // REWARD_ITEMS
        'spellCast'     => "The following spell will be cast on you",               // REWARD_AURA
        'spellLearn'    => "You will learn",                                        // REWARD_SPELL
        'bonusTalents'  => "%d talent |4point:points;",                             // partly LEVEL_UP_CHAR_POINTS
        'spellDisplayed'=> ' (<a href="?spell=%d">%s</a> is displayed)',
        'attachment'    => "Attachment",
        'questInfo'     => array(
             0 => "Normal",              1 => "Group",              21 => "Life",               41 => "PvP",                62 => "Raid",               81 => "Dungeon",            82 => "World Event",
            83 => "Legendary",          84 => "Escort",             85 => "Heroic",             88 => "Raid (10)",          89 => "Raid (25)"
        ),
        'cat'           => array(
            0 => array( "Eastern Kingdoms",
                  36 => "Alterac Mountains",              45 => "Arathi Highlands",                3 => "Badlands",                       25 => "Blackrock Mountain",              4 => "Blasted Lands",
                  46 => "Burning Steppes",               279 => "Dalaran Crater",                 41 => "Deadwind Pass",                2257 => "Deeprun Tram",                    1 => "Dun Morogh",
                  10 => "Duskwood",                      139 => "Eastern Plaguelands",            12 => "Elwynn Forest",                3430 => "Eversong Woods",               3433 => "Ghostlands",
                 267 => "Hillsbrad Foothills",          1537 => "Ironforge",                    4080 => "Isle of Quel'Danas",             38 => "Loch Modan",                     44 => "Redridge Mountains",
                  51 => "Searing Gorge",                3487 => "Silvermoon City",               130 => "Silverpine Forest",            1519 => "Stormwind City",                 33 => "Stranglethorn Vale",
                   8 => "Swamp of Sorrows",               47 => "The Hinterlands",              4298 => "The Scarlet Enclave",            85 => "Tirisfal Glades",              1497 => "Undercity",
                  28 => "Western Plaguelands",            40 => "Westfall",                       11 => "Wetlands"
            ),
            1 => array( "Kalimdor",
                  331 => "Ashenvale",                     16 => "Azshara",                      3524 => "Azuremyst Isle",               3525 => "Bloodmyst Isle",                148 => "Darkshore",
                 1657 => "Darnassus",                    405 => "Desolace",                       14 => "Durotar",                        15 => "Dustwallow Marsh",              361 => "Felwood",
                  357 => "Feralas",                      493 => "Moonglade",                     215 => "Mulgore",                      1637 => "Orgrimmar",                    1377 => "Silithus",
                  406 => "Stonetalon Mountains",         440 => "Tanaris",                       141 => "Teldrassil",                     17 => "The Barrens",                  3557 => "The Exodar",
                  457 => "The Veiled Sea",               400 => "Thousand Needles",             1638 => "Thunder Bluff",                 490 => "Un'Goro Crater",                618 => "Winterspring"
             ),
            8 => array( "Outland",
                3522 => "Blade's Edge Mountains",       3483 => "Hellfire Peninsula",           3518 => "Nagrand",                      3523 => "Netherstorm",                  3520 => "Shadowmoon Valley",
                 703 => "Shattrath City",               3679 => "Skettis",                      3519 => "Terokkar Forest",              3521 => "Zangarmarsh"
            ),
           10 => array( "Northrend",
                3537 => "Borean Tundra",                2817 => "Crystalsong Forest",           4395 => "Dalaran",                        65 => "Dragonblight",                  394 => "Grizzly Hills",
                 495 => "Howling Fjord",                4742 => "Hrothgar's Landing",            210 => "Icecrown",                     3711 => "Sholazar Basin",                 67 => "The Storm Peaks",
                4197 => "Wintergrasp",                    66 => "Zul'Drak"
            ),
            2 => array( "Dungeons",
                4494 => "Ahn'kahet: The Old Kingdom",   3790 => "Auchenai Crypts",              4277 => "Azjol-Nerub",                   719 => "Blackfathom Deeps",            1584 => "Blackrock Depths",
                1583 => "Blackrock Spire",              1941 => "Caverns of Time",              3905 => "Coilfang Reservoir",           2557 => "Dire Maul",                    4196 => "Drak'Tharon Keep",
                 721 => "Gnomeregan",                   4416 => "Gundrak",                      4272 => "Halls of Lightning",           4820 => "Halls of Reflection",          4264 => "Halls of Stone",
                3562 => "Hellfire Ramparts",            3535 => "Hellfire Citadel",             4131 => "Magisters' Terrace",           3792 => "Mana-Tombs",                   2100 => "Maraudon",
                2367 => "Old Hillsbrad Foothills",      4813 => "Pit of Saron",                 2437 => "Ragefire Chasm",                722 => "Razorfen Downs",                491 => "Razorfen Kraul",
                 796 => "Scarlet Monastery",            2057 => "Scholomance",                  3791 => "Sethekk Halls",                3789 => "Shadow Labyrinth",              209 => "Shadowfang Keep",
                2017 => "Stratholme",                   1477 => "Sunken Temple",                3845 => "Tempest Keep",                 3848 => "The Arcatraz",                 2366 => "The Black Morass",
                3713 => "The Blood Furnace",            3847 => "The Botanica",                 4100 => "The Culling of Stratholme",    1581 => "The Deadmines",                4809 => "The Forge of Souls",
                3849 => "The Mechanar",                 4120 => "The Nexus",                    4228 => "The Oculus",                   3714 => "The Shattered Halls",          3717 => "The Slave Pens",
                3715 => "The Steamvault",                717 => "The Stockade",                 3716 => "The Underbog",                 4415 => "The Violet Hold",              4723 => "Trial of the Champion",
                1337 => "Uldaman",                       206 => "Utgarde Keep",                 1196 => "Utgarde Pinnacle",              718 => "Wailing Caverns",              1176 => "Zul'Farrak"
            ),
            3 => array( "Raids",
                3959 => "Black Temple",                 2677 => "Blackwing Lair",               3923 => "Gruul's Lair",                 3606 => "Hyjal Summit",                 4812 => "Icecrown Citadel",
                3457 => "Karazhan",                     3836 => "Magtheridon's Lair",           2717 => "Molten Core",                  3456 => "Naxxramas",                    2159 => "Onyxia's Lair",
                3429 => "Ruins of Ahn'Qiraj",           3607 => "Serpentshrine Cavern",         4075 => "Sunwell Plateau",              3428 => "Temple of Ahn'Qiraj",          3842 => "The Eye",
                4500 => "The Eye of Eternity",          4493 => "The Obsidian Sanctum",         4722 => "Trial of the Crusader",        4273 => "Ulduar",                       4603 => "Vault of Archavon",
                3805 => "Zul'Aman",                     1977 => "Zul'Gurub"
            ),
            4 => array( "Classes",
                -372 => "Death Knight",                 -263 => "Druid",                        -261 => "Hunter",                       -161 => "Mage",                         -141 => "Paladin",
                -262 => "Priest",                       -162 => "Rogue",                         -82 => "Shaman",                        -61 => "Warlock",                       -81 => "Warrior"
                    ),
            5 => array( "Professions",
                -181 => "Alchemy",                      -121 => "Blacksmithing",                -304 => "Cooking",                      -201 => "Engineering",                  -324 => "First Aid",
                -101 => "Fishing",                       -24 => "Herbalism",                    -371 => "Inscription",                  -373 => "Jewelcrafting",                -182 => "Leatherworking",
                -264 => "Tailoring"
            ),
            6 => array( "Battlegrounds",
                 -25 => "All",                          2597 => "Alterac Valley",               3358 => "Arathi Basin",                 3820 => "Eye of the Storm",             4710 => "Isle of Conquest",
                4384 => "Strand of the Ancients",       3277 => "Warsong Gulch"
            ),
            9 => array( "Seasonal",
                -370 => "Brewfest",                    -1002 => "Children's Week",              -364 => "Darkmoon Faire",                -41 => "Day of the Dead",             -1003 => "Hallow's End",
               -1005 => "Harvest Festival",             -376 => "Love is in the Air",           -366 => "Lunar Festival",               -369 => "Midsummer",                   -1006 => "New Year's Eve",
                -375 => "Pilgrim's Bounty",             -374 => "Noblegarden",                 -1001 => "Winter Veil"
            ),
            7 => array( "Miscellaneous",
                -365 => "Ahn'Qiraj War Effort",         -241 => "Argent Tournament",           -1010 => "Dungeon Finder",                 -1 => "Epic",                         -344 => "Legendary",
                -367 => "Reputation",                   -368 => "Scourge Invasion"
            ),
           -2 => "Uncategorized"
        )
    ),
    'icon'  => array(
        'notFound'      => "This icon doesn't exist."
    ),
    'title' => array(
        'notFound'      => "This title doesn't exist.",
        '_transfer'     => 'This title will be converted to <a href="?title=%d" class="q1">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        'cat'           => array(
            "General",      "Player vs. Player",    "Reputation",       "Dungeons & Raids",     "Quests",       "Professions",      "World Events"
        )
    ),
    'skill' => array(
        'notFound'      => "This skill doesn't exist.",
        'cat'           => array(
            -6 => "Companions",         -5 => "Mounts",             -4 => "Racial Traits",      5 => "Attributes",          6 => "Weapon Skills",       7 => "Class Skills",        8 => "Armor Proficiencies",
             9 => "Secondary Skills",   10 => "Languages",          11 => "Professions"
        )
    ),
    'currency' => array(
        'notFound'      => "This currency doesn't exist.",
        'cap'           => "Total cap",
        'cat'           => array(
            1 => "Miscellaneous", 2 => "Player vs. Player", 4 => "Classic", 21 => "Wrath of the Lich King", 22 => "Dungeon and Raid", 23 => "Burning Crusade", 41 => "Test", 3 => "Unused"
        )
    ),
    'sound' => array(
        'notFound'      => "This sound doesn't exist.",
        'foundIn'       => "This sound can be found in",
        'goToPlaylist'  => "Go to My Playlist",
        'music'         => "Music",
        'intro'         => "Intro Music",
        'ambience'      => "Ambience",
        'cat'           => array(
            null,              "Spells",            "User Interface", "Footsteps",   "Weapons Impacts", null,      "Weapons Misses", null,            null,         "Pick Up/Put Down",
            "NPC Combat",      null,                "Errors",         "Nature",      "Objects",         null,      "Death",          "NPC Greetings", null,         "Armor",
            "Footstep Splash", "Water (Character)", "Water",          "Tradeskills", "Misc Ambience",   "Doodads", "Spell Fizzle",   "NPC Loops",     "Zone Music", "Emotes",
            "Narration Music", "Narration",         50 => "Zone Ambience", 52 => "Emitters", 53 => "Vehicles", 1000 => "My Playlist"
        )
    ),
    'pet'      => array(
        'notFound'      => "This pet family doesn't exist.",
        'exotic'        => "Exotic",
        'cat'           => ["Ferocity", "Tenacity", "Cunning"],
        'food'          => ["Meat", "Fish", "Cheese", "Bread", "Fungus", "Fruit", "Raw Meat", "Raw Fish"] // ItemPetFood.dbc
    ),
    'faction' => array(
        'notFound'      => "This faction doesn't exist.",
        'spillover'     => "Reputation Spillover",
        'spilloverDesc' => "Gaining reputation with this faction also yields a proportional gain with the factions listed below.",
        'maxStanding'   => "Max. Standing",
        'quartermaster' => "Quartermaster",
        'customRewRate' => "Custom Reward Rate",
        '_transfer'     => 'The reputation with this faction will be converted to <a href="?faction=%d" class="q1">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        'cat'           => array(
            1118 => ["Classic", 469 => "Alliance", 169 => "Steamwheedle Cartel", 67 => "Horde", 891 => "Alliance Forces", 892 => "Horde Forces"],
            980  => ["The Burning Crusade", 936 => "Shattrath City"],
            1097 => ["Wrath of the Lich King", 1052 => "Horde Expedition", 1117 => "Sholazar Basin", 1037 => "Alliance Vanguard"],
            0    => "Other"
        )
    ),
    'itemset' => array(
        'notFound'      => "This item set doesn't exist.",
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
        'notFound'      => "This spell doesn't exist.",
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
        '_transfer'     => 'This spell will be converted to <a href="?spell=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        'discovered'    => "Learned via discovery",
        'ppm'           => "%s procs per minute",
        'procChance'    => "Proc chance",
        'starter'       => "Starter spell",
        'trainingCost'  => "Training cost",
        'remaining'     => "%s remaining",                  // SPELL_TIME_REMAINING_*
        'untilCanceled' => "until cancelled",               // SPELL_DURATION_UNTIL_CANCELLED
        'castIn'        => "%s sec cast",                   // SPELL_CAST_TIME_SEC
        'instantPhys'   => "Instant",                       // SPELL_CAST_TIME_INSTANT_NO_MANA
        'instantMagic'  => "Instant cast",                  // SPELL_CAST_TIME_INSTANT
        'channeled'     => "Channeled",                     // SPELL_CAST_CHANNELED
        'range'         => "%s yd range",                   // SPELL_RANGE / SPELL_RANGE_DUAL
        'meleeRange'    => "Melee Range",                   // MELEE_RANGE
        'unlimRange'    => "Unlimited Range",               // SPELL_RANGE_UNLIMITED
        'reagents'      => "Reagents",                      // SPELL_REAGENTS
        'tools'         => "Tools",                         // SPELL_TOTEMS
        'home'          => "&lt;Inn&gt;",
        'pctCostOf'     => "of base %s",
        'costPerSec'    => ", plus %s per sec",             // see 'powerTypes'
        'costPerLevel'  => ", plus %s per level",           // not used?
        'stackGroup'    => "Stack Group",
        'linkedWith'    => "Linked with",
        '_scaling'      => "Scaling",
        'scaling'       => array(
            'directSP' => "+%.2f%% of spell power to direct component",         'directAP' => "+%.2f%% of attack power to direct component",
            'dotSP'    => "+%.2f%% of spell power per tick",                    'dotAP'    => "+%.2f%% of attack power per tick"
        ),
        'powerRunes'    => ["Frost", "Unholy", "Blood", "Death"], // RUNE_COST_* / COMBAT_TEXT_RUNE_*
        'powerTypes'    => array(
            // conventional - HEALTH, MANA, RAGE, FOCUS, ENERGY, HAPPINESS, RUNES, RUNIC_POWER / *_COST / *COST_PER_TIME
              -2 => "Health",              0 => "Mana",                1 => "Rage",                2 => "Focus",               3 => "Energy",              4 => "Happiness",
               5 => "Runes",               6 => "Runic Power",
            // powerDisplay - PowerDisplay.dbc -> GlobalStrings.lua POWER_TYPE_*
              -1 => "Ammo",              -41 => "Pyrite",            -61 => "Steam Pressure",   -101 => "Heat",             -121 => "Ooze",             -141 => "Blood Power",
            -142 => "Wrath"
        ),
        'relItems'      => array(
            'base'    => "<small>Show %s related to <b>%s</b></small>",
            'link'    => " or ",
            'recipes' => '<a href="?items=9.%s">recipe items</a>',
            'crafted' => '<a href="?items&filter=cr=86;crs=%s;crv=0">crafted items</a>'
        ),
        'cat'           => array(                           // as per menu in locale_enus.js
              7 => "Class Skills",                          // classList
            -13 => "Glyphs",                                // classList
            -11 => array("Proficiencies", 8 => "Armor", 6 => "Weapon", 10 => "Languages"),
             -4 => "Racial Traits",
             -2 => "Talents",                               // classList
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
             -7 => ["Pet Talents", 410 => "Cunning", 411 => "Ferocity", 409 => "Tenacity"],
             11 => array(
                "Professions",
                171 => "Alchemy",
                164 => ["Blacksmithing", 9788 => "Armorsmithing", 9787 => "Weaponsmithing", 17041 => "Master Axesmithing", 17040 => "Master Hammersmithing", 17039 => "Master Swordsmithing"],
                333 => "Enchanting",
                202 => ["Engineering", 20219 => "Gnomish Engineering", 20222 => "Goblin Engineering"],
                182 => "Herbalism",
                773 => "Inscription",
                755 => "Jewelcrafting",
                165 => ["Leatherworking", 10656 => "Dragonscale Leatherworking", 10658 => "Elemental Leatherworking", 10660 => "Tribal Leatherworking"],
                186 => "Mining",
                393 => "Skinning",
                197 => ["Tailoring", 26798 => "Mooncloth Tailoring", 26801 => "Shadoweave Tailoring", 26797 => "Spellfire Tailoring"],
            ),
              9 => ["Secondary Skills", 185 => "Cooking", 129 => "First Aid", 356 => "Fishing", 762 => "Riding"],
             -8 => "NPC Abilities",
             -9 => "GM Abilities",
              0 => "Uncategorized"
        ),
        'armorSubClass' => array(                           // ItemSubClass.dbc/2
            "Miscellaneous",                        "Cloth Armor",                          "Leather Armor",                        "Mail Armor",                           "Plate Armor",
            null,                                   "Shields",                              "Librams",                              "Idols",                                "Totems",
            "Sigils"
        ),
        'weaponSubClass' => array(                          // ItemSubClass.dbc/4; ordered by content firts, then alphabeticaly
            15 => "Daggers",                        13 => "Fist Weapons",                    0 => "One-Handed Axes",                 4 => "One-Handed Maces",                7 => "One-Handed Swords",
             6 => "Polearms",                       10 => "Staves",                          1 => "Two-Handed Axes",                 5 => "Two-Handed Maces",                8 => "Two-Handed Swords",
             2 => "Bows",                           18 => "Crossbows",                       3 => "Guns",                           16 => "Thrown",                         19 => "Wands",
            20 => "Fishing Poles",                  14 => "Miscellaneous"
        ),
        'subClassMasks' => array(
            0x02A5F3 => "Melee Weapon",             0x0060 => "Shield",                     0x04000C => "Ranged Weapon",            0xA091 => "One-Handed Melee Weapon"
        ),
        'traitShort'    => array(
            'atkpwr'    => "AP",                    'rgdatkpwr' => "RAP",                   'splpwr'    => "SP",                    'arcsplpwr' => "ArcP",                  'firsplpwr' => "FireP",
            'frosplpwr' => "FroP",                  'holsplpwr' => "HolP",                  'natsplpwr' => "NatP",                  'shasplpwr' => "ShaP",                  'splheal'   => "Heal",
            'str'       => "Str",                   'agi'       => "Agi",                   'sta'       => "Sta",                   'int'       => "Int",                   'spi'       => "Spi"
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
        'lockType'      => array(                           // lockType.dbc
            null,                                   "Lockpicking",                          "Herbalism",                            "Mining",                               "Disarm Trap",
            "Open",                                 "Treasure (DND)",                       "Calcified Elven Gems (DND)",           "Close",                                "Arm Trap",
            "Quick Open",                           "Quick Close",                          "Open Tinkering",                       "Open Kneeling",                        "Open Attacking",
            "Gahz'ridian (DND)",                    "Blasting",                             "PvP Open",                             "PvP Close",                            "Fishing (DND)",
            "Inscription",                          "Open From Vehicle"
        ),
        'stealthType'   => ["General", "Trap"],
        'invisibilityType' => ["General", 3 => "Trap", 6 => "Drunk"],
        'unkEffect'     => 'Unknown Effect',
        'effects'       => array(
/*0-5    */ 'None',                     'Instakill',                'School Damage',            'Dummy',                    'Portal Teleport',          'Teleport Units',
/*6+     */ 'Apply Aura',               'Environmental Damage',     'Power Drain',              'Health Leech',             'Heal',                     'Bind',
/*12+    */ 'Portal',                   'Ritual Base',              'Ritual Specialize',        'Ritual Activate Portal',   'Quest Complete',           'Weapon Damage NoSchool',
/*18+    */ 'Resurrect',                'Add Extra Attacks',        'Dodge',                    'Evade',                    'Parry',                    'Block',
/*24+    */ 'Create Item',              'Can Use Weapon',           'Defense',                  'Persistent Area Aura',     'Summon',                   'Leap',
/*30+    */ 'Energize',                 'Weapon Damage Percent',    'Trigger Missile',          'Open Lock',                'Summon Change Item',       'Apply Area Aura Party',
/*36+    */ 'Learn Spell',              'Spell Defense',            'Dispel',                   'Language',                 'Dual Wield',               'Jump',
/*42+    */ 'Jump Dest',                'Teleport Units Face Caster','Skill Step',              'Add Honor',                'Spawn',                    'Trade Skill',
/*48+    */ 'Stealth',                  'Detect',                   'Trans Door',               'Force Critical Hit',       'Guarantee Hit',            'Enchant Item Permanent',
/*54+    */ 'Enchant Item Temporary',   'Tame Creature',            'Summon Pet',               'Learn Pet Spell',          'Weapon Damage Flat',       'Create Random Item',
/*60+    */ 'Proficiency',              'Send Event',               'Power Burn',               'Threat',                   'Trigger Spell',            'Apply Area Aura Raid',
/*66+    */ 'Create Mana Gem',          'Heal Max Health',          'Interrupt Cast',           'Distract',                 'Pull',                     'Pickpocket',
/*72+    */ 'Add Farsight',             'Untrain Talents',          'Apply Glyph',              'Heal Mechanical',          'Summon Object Wild',       'Script Effect',
/*78+    */ 'Attack',                   'Sanctuary',                'Add Combo Points',         'Create House',             'Bind Sight',               'Duel',
/*84+    */ 'Stuck',                    'Summon Player',            'Activate Object',          'WMO Damage',               'WMO Repair',               'WMO Change',
/*90+    */ 'Kill Credit',              'Threat All',               'Enchant Held Item',        'Force Deselect',           'Self Resurrect',           'Skinning',
/*96+    */ 'Charge',                   'Cast Button',              'Knock Back',               'Disenchant',               'Inebriate',                'Feed Pet',
/*102+   */ 'Dismiss Pet',              'Reputation',               'Summon Object Slot1',      'Summon Object Slot2',      'Summon Object Slot3',      'Summon Object Slot4',
/*108+   */ 'Dispel Mechanic',          'Summon Dead Pet',          'Destroy All Totems',       'Durability Damage',        'Summon Demon',             'Resurrect Flat',
/*114+   */ 'Attack Me',                'Durability Damage Percent','Skin Player Corpse',       'Spirit Heal',              'Skill',                    'Apply Area Aura Pet',
/*120+   */ 'Teleport Graveyard',       'Weapon Damage Normalized', null,                       'Send Taxi',                'Pull Towards',             'Modify Threat Percent',
/*126+   */ 'Steal Beneficial Buff',    'Prospecting',              'Apply Area Aura Friend',   'Apply Area Aura Enemy',    'Redirect Threat',          'Play Sound',
/*132+   */ 'Play Music',               'Unlearn Specialization',   'Kill Credit2',             'Call Pet',                 'Heal Percent',             'Energize Percent',
/*138+   */ 'Leap Back',                'Clear Quest',              'Force Cast',               'Force Cast With Value',    'Trigger Spell With Value', 'Apply Area Aura Owner',
/*144+   */ 'Knock Back Dest',          'Pull Towards Dest',        'Activate Rune',            'Quest Fail',               null,                       'Charge Dest',
/*150+   */ 'Quest Start',              'Trigger Spell 2',          null,                       'Create Tamed Pet',         'Discover Taxi',            'Dual Wield 2H Weapons',
/*156+   */ 'Enchant Item Prismatic',   'Create Item 2',            'Milling',                  'Allow Rename Pet',         null,                       'Talent Spec Count',
/*162-164*/ 'Talent Spec Select',       null,                       'Remove Aura'
        ),
        'unkAura'       => 'Unknown Aura',
        'auras'         => array(
/*0-   */   'None',                                 'Bind Sight',                           'Mod Possess',                          'Periodic Damage',                      'Dummy',
/*5+   */   'Mod Confuse',                          'Mod Charm',                            'Mod Fear',                             'Periodic Heal',                        'Mod Attack Speed',
            'Mod Threat',                           'Taunt',                                'Stun',                                 'Mod Damage Done Flat',                 'Mod Damage Taken Flat',
            'Damage Shield',                        'Mod Stealth',                          'Mod Stealth Detection',                'Mod Invisibility',                     'Mod Invisibility Detection',
            'Mod Health Percent',                   'Mod Power Percent',                    'Mod Resistance Flat',                  'Periodic Trigger Spell',               'Periodic Energize',
/*25+  */   'Pacify',                               'Root',                                 'Silence',                              'Reflect Spells',                       'Mod Stat Flat',
            'Mod Skill',                            'Mod Increase Speed',                   'Mod Increase Mounted Speed',           'Mod Decrease Speed',                   'Mod Increase Health',
            'Mod Increase Power',                   'Shapeshift',                           'Spell Effect Immunity',                'Spell Aura Immunity',                  'School Immunity',
            'Damage Immunity',                      'Dispel Immunity',                      'Proc Trigger Spell',                   'Proc Trigger Damage',                  'Track Creatures',
            'Track Resources',                      'Mod Parry Skill',                      'Mod Parry Percent',                    null,                                   'Mod Dodge Percent',
/*50+  */    'Mod Critical Healing Amount',          'Mod Block Percent',                    'Mod Physical Crit Percent',            'Periodic Health Leech',                'Mod Hit Chance',
            'Mod Spell Hit Chance',                 'Transform',                            'Mod Spell Crit Chance',                'Mod Increase Swim Speed',              'Mod Damage Done Versus Creature',
            'Pacify Silence',                       'Mod Scale',                            'Periodic Health Funnel',               'Periodic Mana Funnel',                 'Periodic Mana Leech',
            'Mod Casting Speed (not stacking)',     'Feign Death',                          'Disarm',                               'Stalked',                              'School Absorb',
            'Extra Attacks',                        'Mod Spell Crit Chance School',         'Mod Power Cost School Percent',        'Mod Power Cost School Flat',           'Reflect Spells School',
/*75+  */   'Language',                             'Far Sight',                            'Mechanic Immunity',                    'Mounted',                              'Mod Damage Done Percent',
            'Mod Stat Percent',                     'Split Damage Percent',                 'Water Breathing',                      'Mod Base Resistance Flat',             'Mod Health Regeneration',
            'Mod Power Regeneration',               'Channel Death Item',                   'Mod Damage Taken Percent',             'Mod Health Regeneration Percent',      'Periodic Damage Percent',
            'Mod Resist Chance',                    'Mod Detect Range',                     'Prevent Fleeing',                      'Unattackable',                         'Interrupt Regeneration',
            'Ghost',                                'Spell Magnet',                         'Mana Shield',                          'Mod Skill Value',                      'Mod Attack Power',
/*100+ */   'Auras Visible',                        'Mod Resistance Percent',               'Mod Melee Attack Power Versus',        'Mod Total Threat',                     'Water Walk',
            'Feather Fall',                         'Hover',                                'Add Flat Modifier',                    'Add Percent Modifier',                 'Add Target Trigger',
            'Mod Power Regeneration Percent',       'Add Caster Hit Trigger',               'Override Class Scripts',               'Mod Ranged Damage Taken Flat',         'Mod Ranged Damage Taken Percent',
            'Mod Healing',                          'Mod Regeneration During Combat',       'Mod Mechanic Resistance',              'Mod Healing Taken Percent',            'Share Pet Tracking',
            'Untrackable',                          'Empathy',                              'Mod Offhand Damage Percent',           'Mod Target Resistance',                'Mod Ranged Attack Power',
/*125+ */   'Mod Melee Damage Taken Flat',          'Mod Melee Damage Taken Percent',       'Ranged Attack Power Attacker Bonus',   'Possess Pet',                          'Mod Speed Always',
            'Mod Mounted Speed Always',             'Mod Ranged Attack Power Versus',       'Mod Increase Energy Percent',          'Mod Increase Health Percent',          'Mod Mana Regeneration Interrupt',
            'Mod Healing Done Flat',                'Mod Healing Done Percent',             'Mod Total Stat Percentage',            'Mod Melee Haste',                      'Force Reaction',
            'Mod Ranged Haste',                     'Mod Ranged Ammo Haste',                'Mod Base Resistance Percent',          'Mod Resistance Exclusive',             'Safe Fall',
            'Mod Pet Talent Points',                'Allow Tame Pet Type',                  'Mechanic Immunity Mask',               'Retain Combo Points',                  'Reduce Pushback',
/*150+ */   'Mod Shield Blockvalue Percent',        'Track Stealthed',                      'Mod Detected Range',                   'Split Damage Flat',                    'Mod Stealth Level',
            'Mod Water Breathing',                  'Mod Reputation Gain',                  'Pet Damage Multi',                     'Mod Shield Blockvalue',                'No PvP Credit',
            'Mod AoE Avoidance',                    'Mod Health Regeneration In Combat',    'Power Burn Mana',                      'Mod Crit Damage Bonus',                null,
            'Melee Attack Power Attacker Bonus',    'Mod Attack Power Percent',             'Mod Ranged Attack Power Percent',      'Mod Damage Done Versus',               'Mod Crit Percent Versus',
            'Change Model',                         'Mod Speed (not stacking)',             'Mod Mounted Speed (not stacking)',     null,                                   'Mod Spell Damage Of Stat Percent',
/*175+ */   'Mod Spell Healing Of Stat Percent',    'Spirit Of Redemption',                 'AoE Charm',                            'Mod Debuff Resistance',                'Mod Attacker Spell Crit Chance',
            'Mod Spell Damage Versus',              null,                                   'Mod Resistance Of Stat Percent',       'Mod Critical Threat',                  'Mod Attacker Melee Hit Chance',
            'Mod Attacker Ranged Hit Chance',       'Mod Attacker Spell Hit Chance',        'Mod Attacker Melee Crit Chance',       'Mod Attacker Ranged Crit Chance',      'Mod Rating',
            'Mod Faction Reputation Gain',          'Use Normal Movement Speed',            'Mod Melee Ranged Haste',               'Mod Haste',                            'Mod Target Absorb School',
            'Mod Target Ability Absorb School',     'Mod Cooldown',                         'Mod Attacker Spell And Weapon Crit Chance', null,                              'Mod Increases Spell Percent to Hit',
/*200+ */   'Mod XP Percent',                       'Fly',                                  'Ignore Combat Result',                 'Mod Attacker Melee Crit Damage',       'Mod Attacker Ranged Crit Damage',
            'Mod School Crit Damage Taken',         'Mod Increase Vehicle Flight Speed',    'Mod Increase Mounted Flight Speed',    'Mod Increase Flight Speed',            'Mod Mounted Flight Speed Always',
            'Mod Vehicle Speed Always',             'Mod Flight Speed (not stacking)',      'Mod Ranged Attack Power Of Stat Percent', 'Mod Rage from Damage Dealt',        'Tamed Pet Passive',
            'Arena Preparation',                    'Haste Spells',                         'Killing Spree',                        'Haste Ranged',                         'Mod Mana Regeneration from Stat',
            'Mod Rating from Stat',                 'Ignore Threat',                        null,                                   'Raid Proc from Charge',                null,
/*225+ */   'Raid Proc from Charge With Value',     'Periodic Dummy',                       'Periodic Trigger Spell With Value',    'Detect Stealth',                       'Mod AoE Damage Avoidance',
            'Mod Increase Health',                  'Proc Trigger Spell With Value',        'Mod Mechanic Duration',                'Mod Display Model',                    'Mod Mechanic Duration (not stacking)',
            'Mod Dispel Resist',                    'Control Vehicle',                      'Mod Spell Damage Of Attack Power',     'Mod Spell Healing Of Attack Power',    'Mod Scale 2',
            'Mod Expertise',                        'Force Move Forward',                   'Mod Spell Damage from Healing',        'Mod Faction',                          'Comprehend Language',
            'Mod Aura Duration By Dispel',          'Mod Aura Duration By Dispel (not stacking)', 'Clone Caster',                   'Mod Combat Result Chance',             'Convert Rune',
/*250+ */   'Mod Increase Health 2',                'Mod Enemy Dodge',                      'Mod Speed Slow All',                   'Mod Block Crit Chance',                'Mod Disarm Offhand',
            'Mod Mechanic Damage Taken Percent',    'No Reagent Use',                       'Mod Target Resist By Spell Class',     'Mod Spell Visual',                     'Mod HoT Percent',
            'Screen Effect',                        'Phase',                                'Ability Ignore Aurastate',             'Allow Only Ability',                   null,
            null,                                   null,                                   'Mod Immune Aura Apply School',         'Mod Attack Power Of Stat Percent',     'Mod Ignore Target Resist',
            'Mod Ability Ignore Target Resist',     'Mod Damage Taken Percent From Caster', 'Ignore Melee Reset',                   'X Ray',                                'Ability Consume No Ammo',
/*275+ */   'Mod Ignore Shapeshift',                'Mod Mechanic Damage Done Percent',     'Mod Max Affected Targets',             'Mod Disarm Ranged',                    'Initialize Images',
            'Mod Armor Penetration Percent',        'Mod Honor Gain Percent',               'Mod Base Health Percent',              'Mod Healing Received',                 'Linked',
            'Mod Attack Power Of Armor',            'Ability Periodic Crit',                'Deflect Spells',                       'Ignore Hit Direction',                 null,
            'Mod Crit Percent',                     'Mod XP Quest Percent',                 'Open Stable',                          'Override Spells',                      'Prevent Power Regeneration',
            null,                                   'Set Vehicle Id',                       'Block Spell Family',                   'Strangulate',                          null,
/*300+ */   'Share Damage Percent',                 'School Heal Absorb',                   null,                                   'Mod Damage Done Versus Aurastate',     'Mod Fake Inebriate',
            'Mod Minimum Speed',                    null,                                   'Heal Absorb Test',                     'Hunter Trap',                          null,
            'Mod Creature AoE Damage Avoidance',    null,                                   null,                                   null,                                   'Prevent Ressurection',
/* -316*/   'Underwater Walking',                   'Periodic Haste'
        )
    ),
    'item' => array(
        'notFound'      => "This item doesn't exist.",
        'armor'         => "%s Armor",                      // ARMOR_TEMPLATE
        'block'         => "%s Block",                      // SHIELD_BLOCK_TEMPLATE
        'charges'       => "%d |4Charge:Charges;",          // ITEM_SPELL_CHARGES
        'locked'        => "Locked",                        // LOCKED
        'ratingString'  => "%s&nbsp;@&nbsp;L%s",
        'heroic'        => "Heroic",                        // ITEM_HEROIC
        'startQuest'    => "This Item Begins a Quest",      // ITEM_STARTS_QUEST
        'bagSlotString' => "%d Slot %s",                    // CONTAINER_SLOTS
        'fap'           => "Feral Attack Power",
        'durability'    => "Durability %d / %d",            // DURABILITY_TEMPLATE
        'realTime'      => "real time",
        'conjured'      => "Conjured Item",                 // ITEM_CONJURED
        'sellPrice'     => "Sell Price",                    // SELL_PRICE
        'itemLevel'     => "Item Level %d",                 // ITEM_LEVEL
        'randEnchant'   => "&lt;Random enchantment&gt",     // ITEM_RANDOM_ENCHANT
        'readClick'     => "&lt;Right Click To Read&gt",    // ITEM_READABLE
        'openClick'     => "&lt;Right Click To Open&gt",    // ITEM_OPENABLE
        'setBonus'      => "(%d) Set: %s",                  // ITEM_SET_BONUS_GRAY
        'setName'       => "%s (%d/%d)",                    // ITEM_SET_NAME
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
        'millable'      => "Millable",                      // ITEM_MILLABLE
        'noEquipCD'     => "No equip cooldown",
        'prospectable'  => "Prospectable",                  // ITEM_PROSPECTABLE
        'disenchantable'=> "Disenchantable",                // ITEM_DISENCHANT_ANY_SKILL
        'cantDisenchant'=> "Cannot be disenchanted",        // ITEM_DISENCHANT_NOT_DISENCHANTABLE
        'repairCost'    => "Repair cost",                   // REPAIR_COST
        'tool'          => "Tool",
        'cost'          => "Cost",                          // COSTS_LABEL
        'content'       => "Content",
        '_transfer'     => 'This item will be converted to <a href="?item=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        '_unavailable'  => "This item is not available to players.",
        '_rndEnchants'  => "Random Enchantments",
        '_chance'       => "(%s%% chance)",
        'slot'          => "Slot",
        '_quality'      => "Quality",                       // QUALITY
        'usableBy'      => "Usable by",
        'buyout'        => "Buyout price",                  // BUYOUT_PRICE
        'each'          => "each",
        'tabOther'      => "Other",
        'reqMinLevel'   => "Requires Level %d",             // ITEM_MIN_LEVEL
        'reqLevelRange' => "Requires level %d to %d (%s)",  // ITEM_LEVEL_RANGE_CURRENT
        'unique'        => ["Unique",          "Unique (%d)", "Unique: %s (%d)"         ],   // ITEM_UNIQUE, ITEM_UNIQUE_MULTIPLE, ITEM_LIMIT_CATEGORY
        'uniqueEquipped'=> ["Unique-Equipped", null,          "Unique-Equipped: %s (%d)"],   // ITEM_UNIQUE_EQUIPPABLE, null, ITEM_LIMIT_CATEGORY_MULTIPLE
        'speed'         => "Speed",                         // SPEED
        'dps'           => "(%.1f damage per second)",      // DPS_TEMPLATE
        'damage'        => array(                           // *DAMAGE_TEMPLATE*
                        //  basic,                          basic /w school,                add basic,                  add basic /w school
            'single'    => ["%d Damage",                    "%d %s Damage",                 "+ %d Damage",              "+%d %s Damage"             ],
            'range'     => ["%d - %d Damage",               "%d - %d %s Damage",            "+ %d - %d Damage",         "+%d - %d %s Damage"        ],
            'ammo'      => ["Adds %g damage per second",    "Adds %g %s damage per second", "+ %g damage per second",   "+ %g %s damage per second" ]
        ),
        'gems'          => "Gems",
        'socketBonus'   => "Socket Bonus",                  // ITEM_SOCKET_BONUS
        'socket'        => array(                           // EMPTY_SOCKET_*
            "Meta Socket",          "Red Socket",       "Yellow Socket",        "Blue Socket",          -1 => "Prismatic Socket"
        ),
        'gemColors'     => array(                           // *_GEM
            "meta",                 "red",              "yellow",               "blue"
        ),
        'gemConditions' => array(                           // ENCHANT_CONDITION_* in GlobalStrings.lua
            2 => "less than %d %s |4gem:gems;",
            3 => "more %s gems than %s gems",
            5 => "at least %d %s |4gem:gems;"
        ),
        'reqRating'     => array(                           // ITEM_REQ_ARENA_RATING*
            "Requires personal and team arena rating of %d",
            "Requires personal and team arena rating of %d|nin 3v3 or 5v5 brackets",
            "Requires personal and team arena rating of %d|nin 5v5 brackets"
        ),
        'quality'       => array(                           // ITEM_QUALITY?_DESC
            "Poor",                 "Common",           "Uncommon",             "Rare",
            "Epic",                 "Legendary",        "Artifact",             "Heirloom"
        ),
        'trigger'       => array(                           // ITEM_SPELL_TRIGGER_*
            "Use: ",                "Equip: ",          "Chance on hit: ",      "",                             "",
            "",                     ""
        ),
        'bonding'       => array(                           // ITEM_BIND_*
            "Binds to account",                         "Binds when picked up",                                 "Binds when equipped",
            "Binds when used",                          "Quest Item",                                           "Quest Item"
        ),
        "bagFamily"     => array(                           // ItemSubClass.dbc/1
            "Bag",                  "Quiver",           "Ammo Pouch",           "Soul Bag",                     "Leatherworking Bag",
            "Inscription Bag",      "Herb Bag",         "Enchanting Bag",       "Engineering Bag",              null, /*Key*/
            "Gem Bag",              "Mining Bag"
        ),
        'inventoryType' => array(                           // INVTYPE_*
            null,                   "Head",             "Neck",                 "Shoulder",                     "Shirt",
            "Chest",                "Waist",            "Legs",                 "Feet",                         "Wrist",
            "Hands",                "Finger",           "Trinket",              "One-Hand",                     "Off Hand", /*Shield*/
            "Ranged",               "Back",             "Two-Hand",             "Bag",                          "Tabard",
            null, /*Robe*/          "Main Hand",        "Off Hand",             "Held In Off-Hand",             "Projectile",
            "Thrown",               null, /*Ranged2*/   "Quiver",               "Relic"
        ),
        'armorSubClass' => array(                           // ItemSubClass.dbc/2
            "Miscellaneous",        "Cloth",            "Leather",              "Mail",                         "Plate",
            null,                   "Shield",           "Libram",               "Idol",                         "Totem",
            "Sigil"
        ),
        'weaponSubClass'=> array(                           // ItemSubClass.dbc/4
            "Axe",                  "Axe",              "Bow",                  "Gun",                          "Mace",
            "Mace",                 "Polearm",          "Sword",                "Sword",                        null,
            "Staff",                null,               null,                   "Fist Weapon",                  "Miscellaneous",
            "Dagger",               "Thrown",           null,                   "Crossbow",                     "Wand",
            "Fishing Pole"
        ),
        'projectileSubClass' => array(                      // ItemSubClass.dbc/6
            null,                   null,               "Arrow",                "Bullet",                        null
        ),
        'elixirType'    => [null, "Battle", "Guardian"],
        'cat'           => array(                           // ordered by content first, then alphabeticaly; item menu from locale_enus.js
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
        'statType'      => array(                           // ITEM_MOD_*
            "Mana",
            "Health",
            null,
            "Agility",
            "Strength",
            "Intellect",
            "Spirit",
            "Stamina",
            null, null, null, null,
            "Increases defense rating by %d.",
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
            "Increases expertise rating by %d.",
            "Increases attack power by %d.",
            "Increases ranged attack power by %d.",
            "Increases attack power by %d in Cat, Bear, Dire Bear, and Moonkin forms only.",
            "Increases damage done by magical spells and effects by up to %d.",
            "Increases healing done by magical spells and effects by up to %d.",
            "Restores %d mana per 5 sec.",
            "Increases your armor penetration rating by %d.",
            "Increases spell power by %d.",
            "Restores %d health per 5 sec.",
            "Increases spell penetration by %d.",
            "Increases the block value of your shield by %d.",
            "Unknown Bonus #%d (%d)",
        )
    )
);

?>
