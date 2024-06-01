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
    'lang' => ['English', null, 'French', 'German', 'Chinese', null, 'Spanish', null, 'Russian'],
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
        'byUser'        => 'By <a href="HOST_URL/?user=%1$s"%2$s>%1$s</a> ', // mind the \s
        'help'          => "Help",
        'status'        => "Status",
        'yes'           => "Yes",
        'no'            => "No",

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
        'findUpgrades'  => "Find upgrades…",
        'report'        => "Report",
        'writeGuide'    => "Write New Guide",
        'edit'          => "Edit",
        'changelog'     => 'Changelog',

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
        'langOnly'   => "This page is only available in <b>%s</b>.",

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

        // search
        'search'        => "Search",
        'foundResult'   => "Search Results for",
        'noResult'      => "No Results for",
        'tryAgain'      => "Please try some different keywords or check your spelling.",
        'ignoredTerms'  => "The following words were ignored in your search: %s",

        // formating
        'colon'         => ': ',
        'dateFmtShort'  => "Y/m/d",
        'dateFmtLong'   => "Y/m/d \a\\t H:i A",
        'timeAgo'       => "%s ago",
        'nfSeparators'  => [',', '.'],

        // error
        'intError'      => "An internal error has occurred.",
        'intError2'     => "An internal error has occurred. (%s)",
        'genericError'  => "An error has occurred; refresh the page and try again. If the error persists email <a href=\"#contact\">feedback</a>", # LANG.genericerror
        'bannedRating'  => "You have been banned from rating comments.", # LANG.tooltip_banned_rating
        'tooManyVotes'  => "You have reached the daily voting cap. Come back tomorrow!", # LANG.tooltip_too_many_votes
        'alreadyReport' => "You've already reported this.", # LANG.ct_resp_error7
        'textTooShort'  => "Your message is too short.",
        'cannotComment' => "You have been banned from writing comments.",
        'textLength'    => "Your comment has %d characters and must have at least %d and at most %d characters.",

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
    'guide' => array(
        'myGuides'  => "My Guides",
        'editTitle' => "Edit your Guide",
        'newTitle'  => "Create New Guide",
        'author'    => "Author",
        'spec'      => "Specialization",
        'sticky'    => "Sticky Status",
        'views'     => "Views",
        'patch'     => "Patch",
        'added'     => "Added",
        'rating'    => "Rating",
        'votes'     => "[span id=guiderating-value]%d[/span]/5 ([span id=guiderating-votes][n5=%d][/span] votes) [span id=guiderating][/span]",
        'noVotes'   => "not enough votes [span id=guiderating][/span]",
        'byAuthor'  => "By %s",
        'notFound'  => "This guide doesn't exist.",
        'clTitle'     => 'Changelog For "<a href="?guide=%1$d">%2$s</a>"',
        'clStatusSet' => 'Status set to %s',
        'clCreated'   => 'Created',
        'clMinorEdit' => 'Minor Edit',
        'editor'    => array(
            'fullTitle'       => 'Full Title',
            'fullTitleTip'    => 'The full guide title will be used on the guide page and may include SEO-oriented phrasing.',
            'name'            => 'Name',
            'nameTip'         => 'This should be a simple and clear name of what the guide is, for use in places like menus and guide lists.',
            'description'     => 'Description',
            'descriptionTip'  => 'Description that will be used for search engines.&lt;br&gt;&lt;br&gt;If left empty, it will be generated automatically.',
        //  'commentEmail'    => 'Comment Emails',
        //  'commentEmailTip' => 'Should the author get emailed whenever a user comments on this guide?',
            'changelog'       => 'Changelog For This Edit',
            'changelogTip'    => 'Enter your changelog for this update here.',
            'save'            => 'Save',
            'submit'          => 'Submit for Review',
            'autoupdate'      => 'Autoupdate',
            'showAdjPrev'     => 'Show adjacent preview',
            'preview'         => 'Preview',
            'class-spec'      => 'Class / Spec',
            'category'        => 'Category',
            'testGuide'       => 'See how your guide will look',
            'images'          => 'Images',
            'statusTip'       => array(
                GUIDE_STATUS_DRAFT    => 'Your guide is in &quot;Draft&quot; status and you are the only one able to see it. Keep editing it as long as you like, and when you feel it&apos;s ready submit it for review.',
                GUIDE_STATUS_REVIEW   => 'Your guide is being reviewed.',
                GUIDE_STATUS_APPROVED => 'Your guide has been published.',
                GUIDE_STATUS_REJECTED => 'Your guide has been rejected. After it\'s shortcomings have been remedied you may resubmit it for review.',
                GUIDE_STATUS_ARCHIVED => 'Your guide is outdated and has been archived. Is will no longer be listed and can\'t be edited.',
            )
        ),
        'category'  => array(
            null,                           "Classes",                      "Professions",                  "World Events",                     "New Players & Leveling",
            "Raid & Boss Fights",           "Economy & Money",              "Achievements",                 "Vanity Items, Pets & Mounts",      "Other"
        ),
        'status'    => array(
            null,                           "Draft",                        "Waiting for Approval",         "Approved",                         "Rejected",                             "Archived"
        ),
    ),
    'profiler' => array(
        'realm'         => "Realm",
        'region'        => "Region",
        'viewCharacter' => "View Character",
        '_cpHint'       => "The <b>Character Profiler</b> lets you edit your character, find gear upgrades, check your gearscore and more!",
        '_cpHelp'       => "To get started, just follow the steps below. If you'd like more information, check out our extensive <a href=\"?help=profiler\">help page</a>.",
        '_cpFooter'     => "If you want a more refined search try out our <a href=\"?profiles\">advanced search</a> options. You can also create a <a href=\"?profile&amp;new\">new custom profile</a>.",
        'firstUseTitle' => "%s of %s",
        'complexFilter' => "Complex filter selected! Search results are limited to cached Characters.",
        'customProfile' => " (Custom Profile)",
        'resync'        => "Resync",
        'guildRoster'   => "Guild Roster for &lt;%s&gt;",
        'arenaRoster'   => "Arena Team Roster for &lt;%s&gt",
        'atCaptain'     => "Arena Team Captain",

        'profiler'      => "Character Profiler",
        'arenaTeams'    => "Arena Teams",
        'guilds'        => "Guilds",

        'notFound'      => array(
            'guild'     => "This Guild doesn't exist or is not yet in the database.",
            'arenateam' => "This Arena Team doesn't exist or is not yet in the database.",
            'profile'   => "This character doesn't exist or is not yet in the database."
        ),
        'regions' => array(
            'us' => "Americas",
            'eu' => "Europe",
            'kr' => "Korea",
            'tw' => "Taiwan",
            'cn' => "China",
            'dev' => "Development"
        ),
        'encounterNames'=> array(                           // from dungeonencounter.dbc
            243 => "The Seven",
            334 => "Grand Champions",
            629 => "Northrend Beasts", 637 => "Faction Champions", 641 => "Val'kyr Twins",
            692 => "The Four Horsemen",
            748 => "The Iron Council",
            847 => "Icecrown Gunship Battle"
        ),
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
            'tooSmall'    => "Your screenshot is way too small. (&lt; CFG_SCREENSHOT_MIN_SIZE x CFG_SCREENSHOT_MIN_SIZE).",
            'selectSS'    => "Please select the screenshot to upload.",
            'notAllowed'  => "You are not allowed to upload screenshots!",
        )
    ),
    'game' => array(
        // type strings
        'npc'           => "NPC",
        'npcs'          => "NPCs",
        'object'        => "object",
        'objects'       => "Objects",
        'item'          => "item",
        'items'         => "Items",
        'itemset'       => "item Set",
        'itemsets'      => "Item Sets",
        'quest'         => "quest",
        'quests'        => "Quests",
        'spell'         => "spell",
        'spells'        => "Spells",
        'zone'          => "zone",
        'zones'         => "Zones",
        'faction'       => "faction",
        'factions'      => "Factions",
        'pet'           => "Pet",
        'pets'          => "Hunter Pets",
        'achievement'   => "achievement",
        'achievements'  => "Achievements",
        'title'         => "title",
        'titles'        => "Titles",
        'event'         => "World Event",
        'events'        => "World Events",
        'class'         => "class",
        'classes'       => "Classes",
        'race'          => "race",
        'races'         => "Races",
        'skill'         => "skill",
        'skills'        => "Skills",
        'currency'      => "currency",
        'currencies'    => "Currencies",
        'sound'         => "sound",
        'sounds'        => "Sounds",
        'icon'          => "icon",
        'icons'         => "icons",
        'profile'       => "profile",
        'profiles'      => "Profiles",
        'guide'         => "Guide",
        'guides'        => "Guides",
        'emote'         => "emote",
        'emotes'        => "Emotes",
        'enchantment'   => "enchantment",
        'enchantments'  => "Enchantments",
        'areatrigger'   => "areatrigger",
        'areatriggers'  => "Areatrigger",
        'mail'          => "mail",
        'mails'         => "Mails",

        'cooldown'      => "%s cooldown",
        'difficulty'    => "Difficulty",
        'dispelType'    => "Dispel type",
        'duration'      => "Duration",
        'eventShort'    => "Event",
        'flags'         => "Flags",
        'glyphType'     => "Glyph type",
        'level'         => "Level",
        'mechanic'      => "Mechanic",
        'mechAbbr'      => "Mech.",
        'meetingStone'  => "Meeting Stone",
        'requires'      => "Requires %s",
        'requires2'     => "Requires",
        'reqLevel'      => "Requires Level %s",
        'reqSkillLevel' => "Required skill level",
        'school'        => "School",
        'type'          => "Type",
        'valueDelim'    => " to ",

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
        'timeAbbrev'    => array(                           // <time>S_ABBR
            '',
            "%d |4Sec:Sec;",
            "%d |4Min:Min;",
            "%d |4Hr:Hr;",
            "%d |4Day:Days;"
        ),
        'sources'       => array(
            "Unknown",                      "Crafted",                      "Drop",                         "PvP",                          "Quest",                        "Vendor",
            "Trainer",                      "Discovery",                    "Redemption",                   "Talent",                       "Starter",                      "Event",
            "Achievement",                  null,                           "Black Market",                 "Disenchanted",                 "Fished",                       "Gathered",
            "Milled",                       "Mined",                        "Prospected",                   "Pickpocketed",                 "Salvaged",                     "Skinned",
            "In-Game Store"
        ),
        'pvpSources'    => array(
             42 => "Arena Season 1",         52 => "Arena Season 2",         71 => "Arena Season 3",         80 => "Arena Season 4",        157 => "Arena Season 5",
            163 => "Arena Season 6",        167 => "Arena Season 7",        169 => "Arena Season 8",        177 => "2009 Arena Tournament"
        ),
        'languages'     => array(                           // Languages.dbc
             1 => "Orcish",                  2 => "Darnassian",              3 => "Taurahe",                 6 => "Dwarvish",                7 => "Common",                  8 => "Demonic",
             9 => "Titan",                  10 => "Thalassian",             11 => "Draconic",               12 => "Kalimag",                13 => "Gnomish",                14 => "Troll",
            33 => "Gutterspeak",            35 => "Draenei",                36 => "Zombie",                 37 => "Gnomish Binary",         38 => "Goblin Binary"
        ),
        'gl'            => [null, "Major", "Minor"],                                                                                                                                // MAJOR_GLYPH, MINOR_GLYPH
        'si'            => [1 => "Alliance", -1 => "Alliance only", 2 => "Horde", -2 => "Horde only", 3 => "Both"],
        'resistances'   => [null, 'Holy Resistance', 'Fire Resistance', 'Nature Resistance', 'Frost Resistance', 'Shadow Resistance', 'Arcane Resistance'],                         // RESISTANCE?_NAME
        'dt'            => [null, "Magic", "Curse", "Disease", "Poison", "Stealth", "Invisibility", "Magic, Curse, Disease, Poison", "Spell (NPC)", "Enrage"],                      // SpellDispalType.dbc
        'sc'            => ["Physical", "Holy", "Fire", "Nature", "Frost", "Shadow", "Arcane"],                                                                                     // STRING_SCHOOL_*
        'cl'            => [null, "Warrior", "Paladin", "Hunter", "Rogue", "Priest", "Death Knight", "Shaman", "Mage", "Warlock", null, "Druid"],                                   // ChrClasses.dbc
        'ra'            => [-2 => "Horde", -1 => "Alliance", null, "Human", "Orc", "Dwarf", "Night Elf", "Undead", "Tauren", "Gnome", "Troll", null, "Blood Elf", "Draenei"],       // ChrRaces.dbc
        'rep'           => ["Hated", "Hostile", "Unfriendly", "Neutral", "Friendly", "Honored", "Revered", "Exalted"],                                                              // FACTION_STANDING_LABEL*
        'st'            => array(                           // SpellShapeshiftForm.dbc // with minor deviations on 27, 28
            "Default",                      "Cat Form",                     "Tree of Life",                 "Travel Form",                  "Aquatic Form",                 "Bear From",
            "Ambient",                      "Ghoul",                        "Dire Bear Form",               "Steve's Ghoul",                "Tharon'ja Skeleton",           "Darkmoon - Test of Strength",
            "BLB Player",                   "Shadowdance",                  "Creature - Bear",              "Creature - Cat",               "Ghostwolf",                    "Battle Stance",
            "Defensive Stance",             "Berserker Stance",             "Test",                         "Zombie",                       "Metamorphosis",                null,
            null,                           "Undead",                       "Frenzy",                       "Swift Flight Form",            "Shadow Form",                  "Flight Form",
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
        'classSpecs'    => array(
            -1 => 'Untalented',
             0 => 'Hybrid',
             1 => ['Arms',             'Fury',         'Protection'  ],
             2 => ['Holy',             'Protection',   'Retribution' ],
             3 => ['Beast Mastery',    'Marksmanship', 'Survival'    ],
             4 => ['Assassination',    'Combat',       'Subtlety'    ],
             5 => ['Discipline',       'Holy',         'Shadow Magic'],
             6 => ['Blood',            'Frost',        'Unholy'      ],
             7 => ['Elemental Combat', 'Enhancement',  'Restoration' ],
             8 => ['Arcane',           'Fire',         'Frost'       ],
             9 => ['Affliction',       'Demonology',   'Destruction' ],
            11 => ['Balance',          'Feral Combat', 'Restoration' ]
        ),
        'pvpRank'       => array(                           // PVP_RANK_\d_\d(_FEMALE)?
            null,                                                           "Private / Scout",                                              "Corporal / Grunt",
            "Sergeant / Sergeant",                                          "Master Sergeant / Senior Sergeant",                            "Sergeant Major / First Sergeant",
            "Knight / Stone Guard",                                         "Knight-Lieutenant / Blood Guard",                              "Knight-Captain / Legionnare",
            "Knight-Champion / Centurion",                                  "Lieutenant Commander / Champion",                              "Commander / Lieutenant General",
            "Marshal / General",                                            "Field Marshal / Warlord",                                      "Grand Marshal / High Warlord"
        ),
        'orientation'   => ['North', 'Northeast', 'East', 'Southeast', 'South', 'Southwest', 'West', 'Northwest']
    ),
    'unit' => array(
        'flags'         => array(
            UNIT_FLAG_SERVER_CONTROLLED     => 'Server controlled',
            UNIT_FLAG_NON_ATTACKABLE        => 'Not attackable',
            UNIT_FLAG_REMOVE_CLIENT_CONTROL => 'Remove client control',
            UNIT_FLAG_PVP_ATTACKABLE        => 'PvP attackable',
            UNIT_FLAG_RENAME                => 'Rename',
            UNIT_FLAG_PREPARATION           => 'Arena preparation',
            UNIT_FLAG_UNK_6                 => 'UNK-6',
            UNIT_FLAG_NOT_ATTACKABLE_1      => 'Not Attackable',
            UNIT_FLAG_IMMUNE_TO_PC          => 'Immune to players',
            UNIT_FLAG_IMMUNE_TO_NPC         => 'Immune to creatures',
            UNIT_FLAG_LOOTING               => 'Loot animation',
            UNIT_FLAG_PET_IN_COMBAT         => 'Pet in combat',
            UNIT_FLAG_PVP                   => 'PvP',
            UNIT_FLAG_SILENCED              => 'Silenced',
            UNIT_FLAG_CANNOT_SWIM           => 'Cannot swim',
            UNIT_FLAG_UNK_15                => 'UNK-15 (can only swim)',
            UNIT_FLAG_UNK_16                => 'UNK-16 (cannot attack)',
            UNIT_FLAG_PACIFIED              => 'Pacified',
            UNIT_FLAG_STUNNED               => 'Stunned',
            UNIT_FLAG_IN_COMBAT             => 'In combat',
            UNIT_FLAG_TAXI_FLIGHT           => 'Taxi flight',
            UNIT_FLAG_DISARMED              => 'Disarmed',
            UNIT_FLAG_CONFUSED              => 'Confused',
            UNIT_FLAG_FLEEING               => 'Fleeing',
            UNIT_FLAG_PLAYER_CONTROLLED     => 'Player controlled',
            UNIT_FLAG_NOT_SELECTABLE        => 'Not selectable',
            UNIT_FLAG_SKINNABLE             => 'Skinnable',
            UNIT_FLAG_MOUNT                 => 'Mounted',
            UNIT_FLAG_UNK_28                => 'UNK-28',
            UNIT_FLAG_UNK_29                => 'UNK-29 (Prevent emotes)',
            UNIT_FLAG_SHEATHE               => 'Sheathe weapon',
            UNIT_FLAG_UNK_31                => 'UNK-31'
        ),
        'flags2'        => array(
            UNIT_FLAG2_FEIGN_DEATH                => 'Feign Death',
            UNIT_FLAG2_UNK1                       => 'UNK-1 (hide unit model)',
            UNIT_FLAG2_IGNORE_REPUTATION          => 'Ignore reputation',
            UNIT_FLAG2_COMPREHEND_LANG            => 'Comprehend language',
            UNIT_FLAG2_MIRROR_IMAGE               => 'Mirror Image',
            UNIT_FLAG2_INSTANTLY_APPEAR_MODEL     => 'Instant spawn',
            UNIT_FLAG2_FORCE_MOVEMENT             => 'Force movement',
            UNIT_FLAG2_DISARM_OFFHAND             => 'Disarm offhand weapon',
            UNIT_FLAG2_DISABLE_PRED_STATS         => 'Disable predicted stats',
            UNIT_FLAG2_DISARM_RANGED              => 'Disarm ranged weapon',
            UNIT_FLAG2_REGENERATE_POWER           => 'Regenerate power',
            UNIT_FLAG2_RESTRICT_PARTY_INTERACTION => 'Restrict party interaction',
            UNIT_FLAG2_PREVENT_SPELL_CLICK        => 'Prevent spell click',
            UNIT_FLAG2_ALLOW_ENEMY_INTERACT       => 'Allow enemy interaction',
            UNIT_FLAG2_DISABLE_TURN               => 'Disable turn',
            UNIT_FLAG2_UNK2                       => 'UNK-2',
            UNIT_FLAG2_PLAY_DEATH_ANIM            => 'Play special death animation',
            UNIT_FLAG2_ALLOW_CHEAT_SPELLS         => 'allow cheat spells'
        ),
        'dynFlags'      => array(
            UNIT_DYNFLAG_LOOTABLE                  => 'Lootable',
            UNIT_DYNFLAG_TRACK_UNIT                => 'Tracked',
            UNIT_DYNFLAG_TAPPED                    => 'Tapped',
            UNIT_DYNFLAG_TAPPED_BY_PLAYER          => 'Tapped by player',
            UNIT_DYNFLAG_SPECIALINFO               => 'Special info',
            UNIT_DYNFLAG_DEAD                      => 'Dead',
            UNIT_DYNFLAG_REFER_A_FRIEND            => 'Refer-a-friend',
            UNIT_DYNFLAG_TAPPED_BY_ALL_THREAT_LIST => 'Tapped by all threat list'
        ),
        'bytes1'        => array(
/*idx:0*/   ['Standing', 'Sitting on ground', 'Sitting on chair', 'Sleeping', 'Sitting on low chair', 'Sitting on medium chair', 'Sitting on high chair', 'Dead', 'Kneeing', 'Submerged'], // STAND_STATE_*
            null,
/*idx:2*/   array(
                UNIT_STAND_FLAGS_UNK1        => 'UNK-1',
                UNIT_STAND_FLAGS_CREEP       => 'Creep',
                UNIT_STAND_FLAGS_UNTRACKABLE => 'Untrackable',
                UNIT_STAND_FLAGS_UNK4        => 'UNK-4',
                UNIT_STAND_FLAGS_UNK5        => 'UNK-5'
            ),
/*idx:3*/   array(
                UNIT_BYTE1_FLAG_ALWAYS_STAND => 'Always standing',
                UNIT_BYTE1_FLAG_HOVER        => 'Hovering',
                UNIT_BYTE1_FLAG_UNK_3        => 'UNK-3'
            ),
            'valueUNK' => '[span class=q10]unhandled value [b class=q1]%d[/b] provided for UnitFieldBytes1 on offset [b class=q1]%d[/b][/span]',
            'idxUNK'   => '[span class=q10]unused offset [b class=q1]%d[/b] provided for UnitFieldBytes1[/span]'
        )
    ),
    'smartAI' => array(
        'eventUNK'      => '[span class=q10]Unknwon event #[b class=q1]%d[/b] in use.[/span]',
        'eventTT'       => '[b class=q1]EventType %d[/b][br][table][tr][td]PhaseMask[/td][td=header]0x%04X[/td][/tr][tr][td]Chance[/td][td=header]%d%%%%[/td][/tr][tr][td]Flags[/td][td=header]0x%04X[/td][/tr][tr][td]Param1[/td][td=header]%d[/td][/tr][tr][td]Param2[/td][td=header]%d[/td][/tr][tr][td]Param3[/td][td=header]%d[/td][/tr][tr][td]Param4[/td][td=header]%d[/td][/tr][tr][td]Param5[/td][td=header]%d[/td][/tr][/table]',
        'events'        => array(
            SAI_EVENT_UPDATE_IC             => ['(%12$d)?:When in combat, ;(%11$s)?After %11$s:Instantly;', 'Repeat every %s'],
            SAI_EVENT_UPDATE_OOC            => ['(%12$d)?:When out of combat, ;(%11$s)?After %11$s:Instantly;', 'Repeat every %s'],
            SAI_EVENT_HEALTH_PCT            => ['At %11$s%% Health', 'Repeat every %s'],
            SAI_EVENT_MANA_PCT              => ['At %11$s%% Mana', 'Repeat every %s'],
            SAI_EVENT_AGGRO                 => ['On Aggro', null],
            SAI_EVENT_KILL                  => ['On killing (%3$d)?player:;(%4$d)?[npc=%4$d]:any creature;', 'Cooldown: %s'],
            SAI_EVENT_DEATH                 => ['On death', null],
            SAI_EVENT_EVADE                 => ['When evading', null],
            SAI_EVENT_SPELLHIT              => ['When hit by (%11$s)?%11$s :;(%1$d)?[spell=%1$d]:Spell;', 'Cooldown: %s'],
            SAI_EVENT_RANGE                 => ['On target at %11$sm', 'Repeat every %s'],
/* 10*/     SAI_EVENT_OOC_LOS               => ['While out of combat, (%1$d)?friendly:hostile; (%5$d)?player:unit; enters line of sight within %2$dm', 'Cooldown: %s'],
            SAI_EVENT_RESPAWN               => ['On respawn', null],
            SAI_EVENT_TARGET_HEALTH_PCT     => ['On target at %11$s%% health', 'Repeat every %s'],
            SAI_EVENT_VICTIM_CASTING        => ['Current target is casting (%3$d)?[spell=%3$d]:any spell;', 'Repeat every %s'],
            SAI_EVENT_FRIENDLY_HEALTH       => ['Friendly NPC within %2$dm is at %1$d health', 'Repeat every %s'],
            SAI_EVENT_FRIENDLY_IS_CC        => ['Friendly NPC within %1$dm is crowd controlled', 'Repeat every %s'],
            SAI_EVENT_FRIENDLY_MISSING_BUFF => ['Friendly NPC within %2$dm is missing [spell=%1$d]', 'Repeat every %s'],
            SAI_EVENT_SUMMONED_UNIT         => ['Just summoned (%1$d)?[npc=%1$d]:any creature;', 'Cooldown: %s'],
            SAI_EVENT_TARGET_MANA_PCT       => ['On target at %11$s%% mana', 'Repeat every %s'],
            SAI_EVENT_ACCEPTED_QUEST        => ['Giving (%1$d)?[quest=%1$d]:any quest;', 'Cooldown: %s'],
/* 20*/     SAI_EVENT_REWARD_QUEST          => ['Rewarding (%1$d)?[quest=%1$d]:any quest;', 'Cooldown: %s'],
            SAI_EVENT_REACHED_HOME          => ['Arriving at home coordinates', null],
            SAI_EVENT_RECEIVE_EMOTE         => ['Being targeted with [emote=%1$d]', 'Cooldown: %s'],
            SAI_EVENT_HAS_AURA              => ['(%2$d)?Having %2$d stacks of:Missing aura; [spell=%1$d]', 'Repeat every %s'],
            SAI_EVENT_TARGET_BUFFED         => ['#target# has (%2$d)?%2$d stacks of:aura; [spell=%1$d]', 'Repeat every %s'],
            SAI_EVENT_RESET                 => ['On reset', null],
            SAI_EVENT_IC_LOS                => ['While in combat, (%1$d)?friendly:hostile; (%5$d)?player:unit; enters line of sight within %2$dm', 'Cooldown: %s'],
            SAI_EVENT_PASSENGER_BOARDED     => ['A passenger has boarded', 'Cooldown: %s'],
            SAI_EVENT_PASSENGER_REMOVED     => ['A passenger got off', 'Cooldown: %s'],
            SAI_EVENT_CHARMED               => ['(%1$d)?On being charmed:On charm wearing off;', null],
/* 30*/     SAI_EVENT_CHARMED_TARGET        => ['When charming #target#', null],
            SAI_EVENT_SPELLHIT_TARGET       => ['When #target# gets hit by (%11$s)?%11$s :;(%1$d)?[spell=%1$d]:Spell;', 'Cooldown: %s'],
            SAI_EVENT_DAMAGED               => ['After taking %11$s points of damage', 'Repeat every %s'],
            SAI_EVENT_DAMAGED_TARGET        => ['After #target# took %11$s points of damage', 'Repeat every %s'],
            SAI_EVENT_MOVEMENTINFORM        => ['Started moving to point #[b]%2$d[/b](%1$d)? using MotionType #[b]%1$d[/b]:;', null],
            SAI_EVENT_SUMMON_DESPAWNED      => ['Summoned [npc=%1$d] despawned', 'Cooldown: %s'],
            SAI_EVENT_CORPSE_REMOVED        => ['On corpse despawn', null],
            SAI_EVENT_AI_INIT               => ['AI initialized', null],
            SAI_EVENT_DATA_SET              => ['Data field #[b]%1$d[/b] is set to [b]%2$d[/b]', 'Cooldown: %s'],
            SAI_EVENT_WAYPOINT_START        => ['Start pathing on (%1$d)?waypoint #[b]%1$d[/b]:any waypoint;(%2$d)? on path #[b]%2$d[/b]:;', null],
/* 40*/     SAI_EVENT_WAYPOINT_REACHED      => ['Reaching (%1$d)?waypoint #[b]%1$d[/b]:any waypoint;(%2$d)? on path #[b]%2$d[/b]:;', null],
            null,
            null,
            null,
            null,
            null,
            SAI_EVENT_AREATRIGGER_ONTRIGGER => ['On activation', null],
            null,
            null,
            null,
/* 50*/     null,
            null,
            SAI_EVENT_TEXT_OVER             => ['(%2$d)?[npc=%2$d]:any creature; is done talking TextGroup #[b]%1$d[/b]', null],
            SAI_EVENT_RECEIVE_HEAL          => ['Received %11$s points of healing', 'Cooldown: %s'],
            SAI_EVENT_JUST_SUMMONED         => ['On being summoned', null],
            SAI_EVENT_WAYPOINT_PAUSED       => ['Pausing path on (%1$d)?waypoint #[b]%1$d[/b]:any waypoint;(%2$d)? on path #[b]%2$d[/b]:;', null],
            SAI_EVENT_WAYPOINT_RESUMED      => ['Resuming path on (%1$d)?waypoint #[b]%1$d[/b]:any waypoint;(%2$d)? on path #[b]%2$d[/b]:;', null],
            SAI_EVENT_WAYPOINT_STOPPED      => ['Stopping path on (%1$d)?waypoint #[b]%1$d[/b]:any waypoint;(%2$d)? on path #[b]%2$d[/b]:;', null],
            SAI_EVENT_WAYPOINT_ENDED        => ['Ending current path on (%1$d)?waypoint #[b]%1$d[/b]:any waypoint;(%2$d)? on path #[b]%2$d[/b]:;', null],
            SAI_EVENT_TIMED_EVENT_TRIGGERED => ['Timed event #[b]%1$d[/b] is triggered', null],
/* 60*/     SAI_EVENT_UPDATE                => ['(%11$s)?After %11$s:Instantly;', 'Repeat every %s'],
            SAI_EVENT_LINK                  => ['After Event %11$s', null],
            SAI_EVENT_GOSSIP_SELECT         => ['Selecting Gossip Option:[br](%11$s)?[span class=q1]%11$s[/span]:Menu #[b]%1$d[/b] - Option #[b]%2$d[/b];', null],
            SAI_EVENT_JUST_CREATED          => ['On being spawned for the first time', null],
            SAI_EVENT_GOSSIP_HELLO          => ['Opening Gossip', '(%1$d)?onGossipHello:;(%2$d)?onReportUse:;'],
            SAI_EVENT_FOLLOW_COMPLETED      => ['Finished following', null],
            SAI_EVENT_EVENT_PHASE_CHANGE    => ['Event Phase changed and matches %11$s', null],
            SAI_EVENT_IS_BEHIND_TARGET      => ['Facing the backside of #target#', 'Cooldown: %s'],
            SAI_EVENT_GAME_EVENT_START      => ['[event=%1$d] started', null],
            SAI_EVENT_GAME_EVENT_END        => ['[event=%1$d] ended', null],
/* 70*/     SAI_EVENT_GO_STATE_CHANGED      => ['State has changed', null],
            SAI_EVENT_GO_EVENT_INFORM       => ['Taxi path event #[b]%1$d[/b] trigered', null],
            SAI_EVENT_ACTION_DONE           => ['Executed action #[b]%1$d[/b] requested by script', null],
            SAI_EVENT_ON_SPELLCLICK         => ['Spellclick triggered', null],
            SAI_EVENT_FRIENDLY_HEALTH_PCT   => ['Health of #target# is at %12$s%%', 'Repeat every %s'],
            SAI_EVENT_DISTANCE_CREATURE     => ['[npc=%11$d](%1$d)? with GUID #%1$d:; enters range at or below %2$dm', 'Repeat every %s'],
            SAI_EVENT_DISTANCE_GAMEOBJECT   => ['[object=%11$d](%1$d)? with GUID #%1$d:; enters range at or below %2$dm', 'Repeat every %s'],
            SAI_EVENT_COUNTER_SET           => ['Counter #[b]%1$d[/b] is equal to [b]%2$d[/b]', null],
        ),
        'eventFlags'    => array(
            SAI_EVENT_FLAG_NO_REPEAT     => 'No Repeat',
            SAI_EVENT_FLAG_DIFFICULTY_0  => 'Normal Dungeon',
            SAI_EVENT_FLAG_DIFFICULTY_1  => 'Heroic Dungeon',
            SAI_EVENT_FLAG_DIFFICULTY_2  => 'Normal Raid',
            SAI_EVENT_FLAG_DIFFICULTY_3  => 'Heroic Raid',
            SAI_EVENT_FLAG_NO_RESET      => 'No Reset',
            SAI_EVENT_FLAG_WHILE_CHARMED => 'While Charmed'
        ),
        'actionUNK'     => '[span class=q10]Unknown action #[b class=q1]%d[/b] in use.[/span]',
        'actionTT'      => '[b class=q1]ActionType %d[/b][br][table][tr][td]Param1[/td][td=header]%d[/td][/tr][tr][td]Param2[/td][td=header]%d[/td][/tr][tr][td]Param3[/td][td=header]%d[/td][/tr][tr][td]Param4[/td][td=header]%d[/td][/tr][tr][td]Param5[/td][td=header]%d[/td][/tr][tr][td]Param6[/td][td=header]%d[/td][/tr][/table]',
        'actions'       => array(                           // [body, footer]
            null,
            SAI_ACTION_TALK                               => ['(%3$d)?Say:#target# says; (%7$d)?TextGroup:[span class=q10]unknown text[/span]; #[b]%1$d[/b] to #target#%8$s', 'Duration: %s'],
            SAI_ACTION_SET_FACTION                        => ['(%1$d)?Set faction of #target# to [faction=%7$d]:Reset faction of #target#;.', null],
            SAI_ACTION_MORPH_TO_ENTRY_OR_MODEL            => ['(%7$d)?Reset apperance.:Take the appearance of;(%1$d)? [npc=%1$d].:;(%2$d)?[model npc=%2$d border=1 float=right][/model]:;', null],
            SAI_ACTION_SOUND                              => ['Play sound(%2$d)? to invoking player:;:[div float=right width=270px][sound=%1$d][/div]', 'Played by environment.'],
            SAI_ACTION_PLAY_EMOTE                         => ['(%1$d)?Emote [emote=%1$d] to #target#.: End Emote.;', null],
            SAI_ACTION_FAIL_QUEST                         => ['Fail [quest=%1$d] for #target#.', null],
            SAI_ACTION_OFFER_QUEST                        => ['(%2$d)?Add [quest=%1$d] to #target#\'s log:Offer [quest=%1$d] to #target#;.', null],
            SAI_ACTION_SET_REACT_STATE                    => ['#target# becomes %7$s.', null],
            SAI_ACTION_ACTIVATE_GOBJECT                   => ['#target# becomes activated.', null],
/* 10*/     SAI_ACTION_RANDOM_EMOTE                       => ['Emote %7$s to #target#.', null],
            SAI_ACTION_CAST                               => ['Cast [spell=%1$d] at #target#.', null],
            SAI_ACTION_SUMMON_CREATURE                    => ['Summon [npc=%1$d](%3$d)? for %7$s:;(%4$d)?, attacking invoker.:;', null],
            SAI_ACTION_THREAT_SINGLE_PCT                  => ['Modify #target#\'s threat by %7$d%%.', null],
            SAI_ACTION_THREAT_ALL_PCT                     => ['Modify the threat of all targets by %7$d%%.', null],
            SAI_ACTION_CALL_AREAEXPLOREDOREVENTHAPPENS    => ['Exploration event of [quest=%1$d] is completed for #target#.', null],
            SAI_ACTION_SET_EMOTE_STATE                    => ['(%1$d)?Continuously emote [emote=%1$d] to #target#.:End emote state;', null],
            SAI_ACTION_SET_UNIT_FLAG                      => ['Set (%2$d)?UnitFlags2:UnitFlags; %7$s.', null],
            SAI_ACTION_REMOVE_UNIT_FLAG                   => ['Unset (%2$d)?UnitFlags2:UnitFlags; %7$s.', null],
/* 20*/     SAI_ACTION_AUTO_ATTACK                        => ['(%1$d)?Start:Stop; auto attacking #target#.', null],
            SAI_ACTION_ALLOW_COMBAT_MOVEMENT              => ['(%1$d)?Enable:Disable; combat movement.', null],
            SAI_ACTION_SET_EVENT_PHASE                    => ['Set Event Phase of #target# to [b]%1$d[/b].', null],
            SAI_ACTION_INC_EVENT_PHASE                    => ['(%1$d)?Increment:Decrement; Event Phase of #target#.', null],
            SAI_ACTION_EVADE                              => ['#target# evades to (%1$d)?last stored:respawn; position.', null],
            SAI_ACTION_FLEE_FOR_ASSIST                    => ['Flee for assistance.', 'Use default flee emote'],
            SAI_ACTION_CALL_GROUPEVENTHAPPENS             => ['Satisfy objective of [quest=%1$d] for #target#.', null],
            SAI_ACTION_COMBAT_STOP                        => ['End current combat.', null],
            SAI_ACTION_REMOVEAURASFROMSPELL               => ['Remove (%1$d)?all auras:auras of [spell=%1$d]; from #target#.', 'Only own auras'],
            SAI_ACTION_FOLLOW                             => ['Follow #target#(%1$d)? at %1$dm distance:;(%3$d)? until reaching [npc=%3$d]:;.', '(%7$d)?Angle\u003A %7$.2f°:;(%8$d)? Some form of Quest Credit is given:;'],
/* 30*/     SAI_ACTION_RANDOM_PHASE                       => ['Pick random Event Phase from %7$s.', null],
            SAI_ACTION_RANDOM_PHASE_RANGE                 => ['Pick random Event Phase between %1$d and %2$d.', null],
            SAI_ACTION_RESET_GOBJECT                      => ['Reset #target#.', null],
            SAI_ACTION_CALL_KILLEDMONSTER                 => ['A kill of [npc=%1$d] is credited to #target#.', null],
            SAI_ACTION_SET_INST_DATA                      => ['Set Instance (%3$d)?Boss State:Data Field; #[b]%1$d[/b] to [b]%2$d[/b].', null],
            null,                                           // SMART_ACTION_SET_INST_DATA64 = 35
            SAI_ACTION_UPDATE_TEMPLATE                    => ['Transform to become [npc=%1$d](%2$d)? with level [b]%2$d[/b]:;.', null],
            SAI_ACTION_DIE                                => ['Die…&nbsp;&nbsp;&nbsp;painfully.', null],
            SAI_ACTION_SET_IN_COMBAT_WITH_ZONE            => ['Set in combat with units in zone.', null],
            SAI_ACTION_CALL_FOR_HELP                      => ['Call for help.', 'Use default help emote'],
/* 40*/     SAI_ACTION_SET_SHEATH                         => ['Sheath %7$s weapons.', null],
            SAI_ACTION_FORCE_DESPAWN                      => ['Despawn #target#(%1$d)? after %7$s:;(%2$d)? and then respawn after %8$s:;', null],
            SAI_ACTION_SET_INVINCIBILITY_HP_LEVEL         => ['Become invincible below (%2$d)?%2$d%%:%1$d; HP.', null],
            SAI_ACTION_MOUNT_TO_ENTRY_OR_MODEL            => ['(%7$d)?Dismount.:Mount ;(%1$d)?[npc=%1$d].:;(%2$d)?[model npc=%2$d border=1 float=right][/model]:;', null],
            SAI_ACTION_SET_INGAME_PHASE_MASK              => ['Set visibility of #target# to phase %7$s.', null],
            SAI_ACTION_SET_DATA                           => ['[b]%2$d[/b] is stored in data field #[b]%1$d[/b] of #target#.', null],
            SAI_ACTION_ATTACK_STOP                        => ['Stop attacking.', null],
            SAI_ACTION_SET_VISIBILITY                     => ['#target# becomes (%1$d)?visible:invisible;.', null],
            SAI_ACTION_SET_ACTIVE                         => ['#target# becomes Grid (%1$d)?active:inactive;.', null],
            SAI_ACTION_ATTACK_START                       => ['Start attacking #target#.', null],
/* 50*/     SAI_ACTION_SUMMON_GO                          => ['Summon [object=%1$d](%2$d)? for %7$s:; at #target#.', 'Despawn linked to summoner'],
            SAI_ACTION_KILL_UNIT                          => ['#target# dies!', null],
            SAI_ACTION_ACTIVATE_TAXI                      => ['Fly from [span class=q1]%7$s[/span] to [span class=q1]%8$s[/span]', null],
            SAI_ACTION_WP_START                           => ['(%1$d)?Walk:Run; on waypoint path #[b]%2$d[/b].(%4$d)? Is linked to [quest=%4$d].:; React %8$s while following the path.(%5$d)? Despawn after %7$s:;', 'Repeatable'],
            SAI_ACTION_WP_PAUSE                           => ['Pause waypoint path for %7$s', null],
            SAI_ACTION_WP_STOP                            => ['End waypoint path(%1$d)? and despawn after %7$s:.;(%8$d)? [quest=%2$d] fails.:;(%9$d)? [quest=%2$d] is completed.:;', null],
            SAI_ACTION_ADD_ITEM                           => ['Give %2$d [item=%1$d] to #target#.', null],
            SAI_ACTION_REMOVE_ITEM                        => ['Remove %2$d [item=%1$d] from #target#.', null],
            SAI_ACTION_INSTALL_AI_TEMPLATE                => ['Behave as a %7$s.', null],
            SAI_ACTION_SET_RUN                            => ['(%1$d)?Enable:Disable; run speed.', null],
/* 60*/     SAI_ACTION_SET_DISABLE_GRAVITY                => ['(%1$d)?Defy:Respect; gravity!', null],
            SAI_ACTION_SET_SWIM                           => ['(%1$d)?Enable:Disable; swimming.', null],
            SAI_ACTION_TELEPORT                           => ['#target# is teleported to [zone=%7$d].', null],
            SAI_ACTION_SET_COUNTER                        => ['(%3$d)?Reset:Increase; Counter #[b]%1$d[/b] of #target#(%3$d)?: by [b]%2$d[/b];.', null],
            SAI_ACTION_STORE_TARGET_LIST                  => ['Store #target# as target in #[b]%1$d[/b].', null],
            SAI_ACTION_WP_RESUME                          => ['Continue on waypoint path.', null],
            SAI_ACTION_SET_ORIENTATION                    => ['Set orientation to (%7$s)?face %7$s:Home Position;.', null],
            SAI_ACTION_CREATE_TIMED_EVENT                 => ['(%8$d)?%6$d%% chance to:; Trigger timed event #[b]%1$d[/b](%7$s)? after %7$s:;.', 'Repeat every %s'],
            SAI_ACTION_PLAYMOVIE                          => ['Play Movie #[b]%1$d[/b] to #target#.', null],
            SAI_ACTION_MOVE_TO_POS                        => ['Move (%4$d)?within %4$dm of:to; Point #[b]%1$d[/b] at #target#(%2$d)? on a transport:;.', 'pathfinding disabled'],
/* 70*/     SAI_ACTION_ENABLE_TEMP_GOBJ                   => ['#target# is respawned for %7$s.', null],
            SAI_ACTION_EQUIP                              => ['(%8$d)?Unequip non-standard items:Equip %7$s;(%1$d)? from equipment template #[b]%1$d[/b]:; on #target#.', 'Note: creature items do not necessarily have an item template'],
            SAI_ACTION_CLOSE_GOSSIP                       => ['Close Gossip Window.', null],
            SAI_ACTION_TRIGGER_TIMED_EVENT                => ['Trigger previously defined timed event #[b]%1$d[/b].', null],
            SAI_ACTION_REMOVE_TIMED_EVENT                 => ['Delete previously defined timed event #[b]%1$d[/b].', null],
            SAI_ACTION_ADD_AURA                           => ['Apply aura from [spell=%1$d] on #target#.', null],
            SAI_ACTION_OVERRIDE_SCRIPT_BASE_OBJECT        => ['Set #target# as base for further SmartAI events.', null],
            SAI_ACTION_RESET_SCRIPT_BASE_OBJECT           => ['Reset base for SmartAI events.', null],
            SAI_ACTION_CALL_SCRIPT_RESET                  => ['Reset current SmartAI.', null],
            SAI_ACTION_SET_RANGED_MOVEMENT                => ['Set ranged attack distance to [b]%1$d[/b]m(%2$d)?, at %2$d°:;.', null],
/* 80*/     SAI_ACTION_CALL_TIMED_ACTIONLIST              => ['Call [html]<a href=#sai-actionlist-%1$d onclick=\\"\$(\\\'#dsf67g4d-sai\\\').find(\\\'[href=\\\\\'#sai-actionlist-%1$d\\\\\']\\\').click()\\">Timed Actionlist #%1$d</a>[/html]. Updates %7$s.', null],
            SAI_ACTION_SET_NPC_FLAG                       => ['Set #target#\'s npc flags to %7$s.', null],
            SAI_ACTION_ADD_NPC_FLAG                       => ['Add %7$s npc flags to #target#.', null],
            SAI_ACTION_REMOVE_NPC_FLAG                    => ['Remove %7$s npc flags from #target#.', null],
            SAI_ACTION_SIMPLE_TALK                        => ['#target# says (%7$s)?TextGroup:[span class=q10]unknown text[/span]; #[b]%1$d[/b] to #target#%7$s', null],
            SAI_ACTION_SELF_CAST                          => ['Self casts [spell=%1$d] at #target#.', null],
            SAI_ACTION_CROSS_CAST                         => ['%7$s casts [spell=%1$d] at #target#.', null],
            SAI_ACTION_CALL_RANDOM_TIMED_ACTIONLIST       => ['Call Timed Actionlist at random: [html]%7$s[/html]', null],
            SAI_ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST => ['Call Timed Actionlist at random from range: [html]%7$s[/html]', null],
            SAI_ACTION_RANDOM_MOVE                        => ['Move #target# to a random point within %1$dm.', null],
/* 90*/     SAI_ACTION_SET_UNIT_FIELD_BYTES_1             => ['Set UnitFieldBytes1 %7$s for #target#.', null],
            SAI_ACTION_REMOVE_UNIT_FIELD_BYTES_1          => ['Unset UnitFieldBytes1 %7$s for #target#.', null],
            SAI_ACTION_INTERRUPT_SPELL                    => ['Interrupt (%2$d)?cast of [spell=%2$d]:current spell cast;.', '(%1$d)?Instantly:Delayed;'],
            SAI_ACTION_SEND_GO_CUSTOM_ANIM                => ['Set animation progress to [b]%1$d[/b].', null],
            SAI_ACTION_SET_DYNAMIC_FLAG                   => ['Set Dynamic Flag to %7$s on #target#.', null],
            SAI_ACTION_ADD_DYNAMIC_FLAG                   => ['Add Dynamic Flag %7$s to #target#.', null],
            SAI_ACTION_REMOVE_DYNAMIC_FLAG                => ['Remove Dynamic Flag %7$s from #target#.', null],
            SAI_ACTION_JUMP_TO_POS                        => ['Jump to fixed position — [b]X: %7$.2f,  Y: %8$.2f,  Z: %9$.2f,  [i]v[/i][sub]xy[/sub]: %1$.2f  [i]v[/i][sub]z[/sub]: %2$.2f[/b]', null],
            SAI_ACTION_SEND_GOSSIP_MENU                   => ['Display Gossip entry #[b]%1$d[/b] / TextID #[b]%2$d[/b].', null],
            SAI_ACTION_GO_SET_LOOT_STATE                  => ['Set loot state of #target# to %7$s.', null],
/*100*/     SAI_ACTION_SEND_TARGET_TO_TARGET              => ['Send targets stored in #[b]%1$d[/b] to #target#.', null],
            SAI_ACTION_SET_HOME_POS                       => ['Set Home Position to (%10$d)?current position.:fixed position — [b]X: %7$.2f,  Y: %8$.2f,  Z: %9$.2f[/b];', null],
            SAI_ACTION_SET_HEALTH_REGEN                   => ['(%1$d)?Allow:Prevent; health regeneration for #target#.', null],
            SAI_ACTION_SET_ROOT                           => ['(%1$d)?Prevent:Allow; movement for #target#.', null],
            SAI_ACTION_SET_GO_FLAG                        => ['Set GameObject Flag to %7$s on #target#.', null],
            SAI_ACTION_ADD_GO_FLAG                        => ['Add GameObject Flag %7$s to #target#.', null],
            SAI_ACTION_REMOVE_GO_FLAG                     => ['Remove GameObject Flag %7$s from #target#.', null],
            SAI_ACTION_SUMMON_CREATURE_GROUP              => ['Summon Creature Group #[b]%1$d[/b](%2$d)?, attacking invoker:;.[br](%7$s)?[span class=breadcrumb-arrow]&nbsp;[/span]%7$s:[span class=q0]<empty group>[/span];', null],
            SAI_ACTION_SET_POWER                          => ['%7$s is set to [b]%2$d[/b] for #target#.', null],
            SAI_ACTION_ADD_POWER                          => ['Add [b]%2$d[/b] %7$s to #target#.', null],
/*110*/     SAI_ACTION_REMOVE_POWER                       => ['Remove [b]%2$d[/b] %7$s from #target#.', null],
            SAI_ACTION_GAME_EVENT_STOP                    => ['Stop [event=%1$d].', null],
            SAI_ACTION_GAME_EVENT_START                   => ['Start [event=%1$d].', null],
            SAI_ACTION_START_CLOSEST_WAYPOINT             => ['#target# starts moving along a defined waypoint path. Enter path on the closest of these nodes: %7$s.', null],
            SAI_ACTION_MOVE_OFFSET                        => ['Move to relative position — [b]X: %7$.2f,  Y: %8$.2f,  Z: %9$.2f[/b]', null],
            SAI_ACTION_RANDOM_SOUND                       => ['Play a random sound(%5$d)? to invoking player:;:[div float=right width=270px]%7$s[/div]', 'Played by environment.'],
            SAI_ACTION_SET_CORPSE_DELAY                   => ['Set corpse despawn delay for #target# to %7$s.', null],
            SAI_ACTION_DISABLE_EVADE                      => ['(%1$d)?Prevent:Allow; entering Evade Mode.', null],
            SAI_ACTION_GO_SET_GO_STATE                    => ['Set gameobject state to %7$s.'. null],
            SAI_ACTION_SET_CAN_FLY                        => ['(%1$d)?Enable:Disable; flight.', null],
/*120*/     SAI_ACTION_REMOVE_AURAS_BY_TYPE               => ['Remove all Auras with [b]%7$s[/b] from #target#.', null],
            SAI_ACTION_SET_SIGHT_DIST                     => ['Set sight range to %1$dm for #target#.', null],
            SAI_ACTION_FLEE                               => ['#target# flees for assistance for %7$s.', null],
            SAI_ACTION_ADD_THREAT                         => ['Modify threat level of #target# by %7$d points.', null],
            SAI_ACTION_LOAD_EQUIPMENT                     => ['(%2$d)?Unequip non-standard items:Equip %7$s; from equipment template #[b]%1$d[/b] on #target#.', 'Note: creature items do not necessarily have an item template'],
            SAI_ACTION_TRIGGER_RANDOM_TIMED_EVENT         => ['Trigger previously defined timed event in id range %7$s.', null],
            SAI_ACTION_REMOVE_ALL_GAMEOBJECTS             => ['Remove all gameobjects owned by #target#.', null],
            SAI_ACTION_PAUSE_MOVEMENT                     => ['Pause movement from slot #[b]%1$d[/b] for %7$s.', 'Forced'],
            null,                                           // SAI_ACTION_PLAY_ANIMKIT = 128,    // don't use on 3.3.5a
            null,                                           // SAI_ACTION_SCENE_PLAY = 129,    // don't use on 3.3.5a
/*130*/     null,                                           // SAI_ACTION_SCENE_CANCEL = 130,    // don't use on 3.3.5a
            SAI_ACTION_SPAWN_SPAWNGROUP                   => ['Spawn SpawnGroup [b]%7$s[/b] SpawnFlags: %8$s %9$s', 'Cooldown: %s'],    // Group ID, min secs, max secs, spawnflags
            SAI_ACTION_DESPAWN_SPAWNGROUP                 => ['Despawn SpawnGroup [b]%7$s[/b] SpawnFlags: %8$s %9$s', 'Cooldown: %s'],    // Group ID, min secs, max secs, spawnflags
            SAI_ACTION_RESPAWN_BY_SPAWNID                 => ['Respawn %7$s [small class=q0](GUID: %2$d)[/small]', null],    // spawnType, spawnId
            SAI_ACTION_INVOKER_CAST                       => ['Invoker casts [spell=%1$d] at #target#.', null],    // spellID, castFlags
            SAI_ACTION_PLAY_CINEMATIC                     => ['Play cinematic #[b]%1$d[/b] for #target#', null],    // cinematic
            SAI_ACTION_SET_MOVEMENT_SPEED                 => ['Set speed of MotionType #[b]%1$d[/b] to [b]%7$.2f[/b]', null],    // movementType, speedInteger, speedFraction
            null,                                           // SAI_ACTION_PLAY_SPELL_VISUAL_KIT',   // spellVisualKitId (RESERVED, PENDING CHERRYPICK)
            SAI_ACTION_OVERRIDE_LIGHT                     => ['Change skybox in [zone=%1$d] to #[b]%2$d[/b].', 'Transition: %s'],    // zoneId, overrideLightID, transitionMilliseconds
            SAI_ACTION_OVERRIDE_WEATHER                   => ['Change weather in [zone=%1$d] to %7$s at %3$d%% intensity.', null],    // zoneId, weatherId, intensity
        ),
        'targetUNK'     => '[span class=q10]unknown target #[b class=q1]%d[/b][/span]',
        'targetTT'      => '[b class=q1]TargetType %d[/b][br][table][tr][td]Param1[/td][td=header]%d[/td][/tr][tr][td]Param2[/td][td=header]%d[/td][/tr][tr][td]Param3[/td][td=header]%d[/td][/tr][tr][td]Param4[/td][td=header]%d[/td][/tr][tr][td]X[/td][td=header]%.2f[/td][/tr][tr][td]Y[/td][td=header]%.2f[/td][/tr][tr][td]Z[/td][td=header]%.2f[/td][/tr][tr][td]O[/td][td=header]%.2f[/td][/tr][/table]',
        'targets'       => array(
            null,
            SAI_TARGET_SELF                   => 'self',
            SAI_TARGET_VICTIM                 => 'current target',
            SAI_TARGET_HOSTILE_SECOND_AGGRO   => '2nd in threat list',
            SAI_TARGET_HOSTILE_LAST_AGGRO     => 'last in threat list',
            SAI_TARGET_HOSTILE_RANDOM         => 'random target',
            SAI_TARGET_HOSTILE_RANDOM_NOT_TOP => 'random non-tank target',
            SAI_TARGET_ACTION_INVOKER         => 'Invoker',
            SAI_TARGET_POSITION               => 'world coordinates',
            SAI_TARGET_CREATURE_RANGE         => '(%1$d)?random instance of [npc=%1$d]:arbitrary creature; within %11$sm(%4$d)? (max. %4$d targets):;',
/*10*/      SAI_TARGET_CREATURE_GUID          => '(%11$d)?[npc=%11$d]:NPC; with GUID #%1$d',
            SAI_TARGET_CREATURE_DISTANCE      => '(%1$d)?random instance of [npc=%1$d]:arbitrary creature; within %11$sm(%3$d)? (max. %3$d targets):;',
            SAI_TARGET_STORED                 => 'previously stored targets',
            SAI_TARGET_GAMEOBJECT_RANGE       => '(%1$d)?random instance of [object=%1$d]:arbitrary object; within %11$sm(%4$d)? (max. %4$d targets):;',
            SAI_TARGET_GAMEOBJECT_GUID        => '(%11$d)?[object=%11$d]:gameobject; with GUID #%1$d',
            SAI_TARGET_GAMEOBJECT_DISTANCE    => '(%1$d)?random instance of [object=%1$d]:arbitrary object; within %11$sm(%3$d)? (max. %3$d targets):;',
            SAI_TARGET_INVOKER_PARTY          => 'Invokers party',
            SAI_TARGET_PLAYER_RANGE           => 'random player within %11$sm',
            SAI_TARGET_PLAYER_DISTANCE        => 'random player within %11$sm',
            SAI_TARGET_CLOSEST_CREATURE       => 'closest (%3$d)?dead:alive; (%1$d)?[npc=%1$d]:arbitrary creature; within %11$sm',
/*20*/      SAI_TARGET_CLOSEST_GAMEOBJECT     => 'closest (%1$d)?[object=%1$d]:arbitrary gameobject; within %11$sm',
            SAI_TARGET_CLOSEST_PLAYER         => 'closest player within %1$dm',
            SAI_TARGET_ACTION_INVOKER_VEHICLE => 'Invokers vehicle',
            SAI_TARGET_OWNER_OR_SUMMONER      => 'Invokers owner or summoner',
            SAI_TARGET_THREAT_LIST            => 'all units engaged in combat with self',
            SAI_TARGET_CLOSEST_ENEMY          => 'closest attackable (%2$d)?player:enemy; within %1$dm',
            SAI_TARGET_CLOSEST_FRIENDLY       => 'closest friendly (%2$d)?player:creature; within %1$dm',
            SAI_TARGET_LOOT_RECIPIENTS        => 'all players eligible for loot',
            SAI_TARGET_FARTHEST               => 'furthest engaged (%2$d)?player:creature; within %1$dm(%3$d)? and line of sight:;',
            SAI_TARGET_VEHICLE_PASSENGER      => 'accessory in Invokers vehicle in (%1$d)?seat %11$s:all seats;',
/*30*/      SAI_TARGET_CLOSEST_UNSPAWNED_GO   => 'closest unspawned (%1$d)?[object=%1$d]:, arbitrary gameobject; within %11$sm'
        ),
        'castFlags'     => array(
            SAI_CAST_FLAG_INTERRUPT_PREV => 'Interrupt current cast',
            SAI_CAST_FLAG_TRIGGERED      => 'Triggered',
            SAI_CAST_FLAG_AURA_MISSING   => 'Aura missing',
            SAI_CAST_FLAG_COMBAT_MOVE    => 'Combat movement'
        ),
        'spawnFlags'    => array(
            SAI_SPAWN_FLAG_IGNORE_RESPAWN   => 'Override and reset respawn timer',
            SAI_SPAWN_FLAG_FORCE_SPAWN      => 'Force spawn if already in world',
            SAI_SPAWN_FLAG_NOSAVE_RESPAWN   => 'Remove respawn time on despawn'
        ),
        'GOStates'      => ['active', 'ready', 'active alternative'],
        'summonTypes'   => [null, 'Despawn timed or when corpse disappears', 'Despawn timed or when dying', 'Despawn timed', 'Despawn timed out of combat', 'Despawn when dying', 'Despawn timed after death', 'Despawn when corpse disappears', 'Despawn manually'],
        'aiTpl'         => ['basic AI', 'spell caster', 'turret', 'passive creature', 'cage for creature', 'caged creature'],
        'reactStates'   => ['passive', 'defensive', 'aggressive', 'assisting'],
        'sheaths'       => ['all', 'melee', 'ranged'],
        'saiUpdate'     => ['out of combat', 'in combat', 'always'],
        'lootStates'    => ['Not ready', 'Ready', 'Activated', 'Just Deactivated'],
        'weatherStates' => ['Fine', 'Fog', 'Drizzle', 'Light Rain', 'Medium Rain', 'Heavy Rain', 'Light Snow', 'Medium Snow', 'Heavy Snow', 22 => 'Light Sandstorm', 41=> 'Medium Sandstorm', 42 => 'Heavy Sandstorm', 86 => 'Thunders', 90 => 'Black Rain', 106 => 'Black Snow'],

        'GOStateUNK'      => '[span class=q10]unknown gameobject state #[b class=q1]%d[/b][/span]',
        'summonTypeUNK'   => '[span class=q10]unknown SummonType #[b class=q1]%d[/b][/span]',
        'aiTplUNK'        => '[span class=q10]unknown AI template #[b class=q1]%d[/b][/span]',
        'reactStateUNK'   => '[span class=q10]unknown ReactState #[b class=q1]%d[/b][/span]',
        'sheathUNK'       => '[span class=q10]unknown sheath #[b class=q1]%d[/b][/span]',
        'saiUpdateUNK'    => '[span class=q10]unknown update condition #[b class=q1]%d[/b][/span]',
        'lootStateUNK'    => '[span class=q10]unknown loot state #[b class=q1]%d[/b][/span]',
        'weatherStateUNK' => '[span class=q10]unknown weather state #[b class=q1]%d[/b][/span]',

        'entityUNK'     => '[b class=q10]unknown entity[/b]',

        'empty'         => '[span class=q0]<empty>[/span]'
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
        'ipAddress'     => "IP address",
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
        'emailNotFound' => "The email address you entered is not associated with any account.<br><br>If you forgot the email you registered your account with email CFG_CONTACT_EMAIL for assistance.",
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
        'posts'         => "Forum posts",
        // user mail
        'tokenExpires'  => "This token expires in %s.",
        'accConfirm'    => ["Account Confirmation", "Welcome to CFG_NAME_SHORT!\r\n\r\nClick the Link below to activate your account.\r\n\r\nHOST_URL?account=signup&token=%s\r\n\r\nIf you did not request this mail simply ignore it."],
        'recoverUser'   => ["User Recovery",        "Follow this link to log in.\r\n\r\nHOST_URL?account=signin&token=%s\r\n\r\nIf you did not request this mail simply ignore it."],
        'resetPass'     => ["Password Reset",       "Follow this link to reset your password.\r\n\r\nHOST_URL?account=forgotpassword&token=%s\r\n\r\nIf you did not request this mail simply ignore it."]
    ),
    'emote' => array(
        'notFound'      => "This Emote doesn't exist.",
//      'self'          => "To Yourself",
//      'target'        => "To others with a target",
//      'noTarget'      => "To others without a target",
        'targeted'      => "Used with target",
        'untargeted'    => "Used without target",
        'isAnimated'    => "Uses an animation",
        'eventSound'    => "Event Sound",
        'aliases'       => "Aliases",
        'noText'        => "This Emote has no text.",
        'noCommand'     => "This Emote has no /-command. It can not be triggered.",
        'flags'         => array(          // gm stuff - translation nice but not essential
            EMOTE_FLAG_ONLY_STANDING       => "Only while standig",
            EMOTE_FLAG_USE_MOUNT           => "Emote applies to mount",
            EMOTE_FLAG_NOT_CHANNELING      => "Not while channeling",
            EMOTE_FLAG_ANIM_TALK           => "Talk anim - talk",
            EMOTE_FLAG_ANIM_QUESTION       => "Talk anim - question",
            EMOTE_FLAG_ANIM_EXCLAIM        => "Talk anim - exclamation",
            EMOTE_FLAG_ANIM_SHOUT          => "Talk anim - shout",
            EMOTE_FLAG_NOT_SWIMMING        => "Not while swimming",
            EMOTE_FLAG_ANIM_LAUGH          => "Talk anim - laugh",
            EMOTE_FLAG_CAN_LIE_ON_GROUND   => "Usable while sleeping or dead",
            EMOTE_FLAG_NOT_FROM_CLIENT     => "Creature only",
            EMOTE_FLAG_NOT_CASTING         => "Not while casting",
            EMOTE_FLAG_END_MOVEMENT        => "Emote ends movement",
            EMOTE_FLAG_INTERRUPT_ON_ATTACK => "Interrupt on attacking",
            EMOTE_FLAG_ONLY_STILL          => "Only while still",
            EMOTE_FLAG_NOT_FLYING          => "Not while flying"
        ),
        'state'         => ['Oneshot', 'Continuous State', 'Continuous Emote']
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
    'areatrigger' => array(
        'notFound'      => "This areatrigger doesn't exist.",
        'foundIn'       => "This areatrigger can be found in",
        'types'         => ['Unused', 'Tavern', 'Teleporter', 'Quest Objective', 'Smart Trigger', 'Script']
    ),
    'gameObject' => array(
        'notFound'      => "This object doesn't exist.",
        'cat'           => [0 => "Other", 3 => "Containers", 6 => "Traps", 9 => "Books", 25 => "Fishing Pools", -5 => "Chests", -3 => "Herbs", -4 => "Mineral Veins", -2 => "Quest", -6 => "Tools"],
        'type'          => [              3 => "Container",  6 => "",      9 => "Book",  25 => "",              -5 => "Chest",  -3 => "Herb",  -4 => "Mineral Vein",  -2 => "Quest", -6 => ""],         // used for tooltip
        'unkPosition'   => "The location of this object is unknown.",
        'npcLootPH'     => 'The <b>%s</b> contains the loot from the fight against <a href="?npc=%d">%s</a>. It spawns after this NPC dies.',
        'key'           => "Key",
        'focus'         => "Spell Focus",
        'focusDesc'     => "Spells requiring this Focus can be cast near this Object",
        'trap'          => "Trap",
        'triggeredBy'   => "Triggered by",
        'capturePoint'  => "Capture Point",
        'foundIn'       => "This object can be found in",
        'restock'       => "Restocks every %s.",
        'goFlags'       => array(
            GO_FLAG_IN_USE         => 'In use',
            GO_FLAG_LOCKED         => 'Locked',
            GO_FLAG_INTERACT_COND  => 'Cannot interact',
            GO_FLAG_TRANSPORT      => 'Transport',
            GO_FLAG_NOT_SELECTABLE => 'Not selectable',
            GO_FLAG_TRIGGERED      => 'Triggered',
            GO_FLAG_DAMAGED        => 'Siege damaged',
            GO_FLAG_DESTROYED      => 'Siege destroyed'
        ),
        'actions'       => array(
            "None",                         "Animate Custom 0",             "Animate Custom 1",             "Animate Custom 2",             "Animate Custom 3",
            "Disturb / Trigger Trap",       "Unlock",                       "Lock",                         "Open",                         "Unlock & Open",
            "Close",                        "Toggle Open",                  "Destroy",                      "Rebuild",                      "Creation",
            "Despawn",                      "Make Inert",                   "Make Active",                  "Close & Lock",                 "Use ArtKit 0",
            "Use ArtKit 1",                 "Use ArtKit 2",                 "Use ArtKit 3",                 "Set Tap List"
        )
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
        'resistances'   => "Resistances",
        'foundIn'       => "This NPC can be found in",
        'tameable'      => "Tameable (%s)",
        'waypoint'      => "Waypoint",
        'wait'          => "Wait",
        'respawnIn'     => "Respawn in: %s",
        'despawnAfter'  => "Spawned by Script<br>Despawn after: %s",
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
        'npcFlags'      => array(
            NPC_FLAG_GOSSIP         => 'Gossip',
            NPC_FLAG_QUEST_GIVER    => 'Quest Giver',
            NPC_FLAG_TRAINER        => 'Trainer',
            NPC_FLAG_CLASS_TRAINER  => 'Class Trainer',
            NPC_PROFESSION_TRAINER  => 'Profession Trainer',
            NPC_FLAG_VENDOR         => 'Vendor',
            NPC_FLAG_VENDOR_AMMO    => 'Ammo Vendor',
            NPC_FLAG_VENDOR_FOOD    => 'Food Vendor',
            NPC_FLAG_VENDOR_POISON  => 'Poison Vendor',
            NPC_FLAG_VENDOR_REAGENT => 'Reagent Vendor',
            NPC_FLAG_REPAIRER       => 'Repair',
            NPC_FLAG_FLIGHT_MASTER  => 'Flight Master',
            NPC_FLAG_SPIRIT_HEALER  => 'Spirit Healer',
            NPC_FLAG_SPIRIT_GUIDE   => 'Spirit Guide',
            NPC_FLAG_INNKEEPER      => 'Innkeeper',
            NPC_FLAG_BANKER         => 'Banker',
            NPC_FLAG_PETITIONER     => 'Petitioner',
            NPC_FLAG_GUILD_MASTER   => 'Guild Master',
            NPC_FLAG_BATTLEMASTER   => 'Battle Master',
            NPC_FLAG_AUCTIONEER     => 'Auctioneer',
            NPC_FLAG_STABLE_MASTER  => 'Stable Master',
            NPC_FLAG_GUILD_BANK     => 'Guild Bank',
            NPC_FLAG_SPELLCLICK     => 'Spellclick',
            NPC_FLAG_MAILBOX        => 'Mailbox'
        )
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
        'criteriaType'  => "Criterium Type ID:",
        'itemReward'    => "You will receive",
        'titleReward'   => 'You shall be granted the title "<a href="?title=%d">%s</a>"',
        'slain'         => "slain",
        'reqNumCrt'     => "Requires",
        'rfAvailable'   => "Available on realm: ",
        '_transfer'     => 'This achievement will be converted to <a href="?achievement=%d" class="q%d icontiny tinyspecial" style="background-image: url(STATIC_URL/images/wow/icons/tiny/%s.gif)">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        'cat'           => array(
                1 => "Statistics",                                                   21 => "Player vs. Player",
               81 => "Feats of Strength",                                            92 => "General",
               95 => "Player vs. Player",                                            96 => "Quests",
               97 => "Exploration",                                                 122 => "Deaths",
              123 => "Arenas",                                                      124 => "Battlegrounds",
              125 => "Dungeons",                                                    126 => "World",
              127 => "Resurrection",                                                128 => "Kills",
              130 => "Character",                                                   131 => "Social",
              132 => "Skills",                                                      133 => "Quests",
              134 => "Travel",                                                      135 => "Creatures",
              136 => "Honorable Kills",                                             137 => "Killing Blows",
              140 => "Wealth",                                                      141 => "Combat",
              145 => "Consumables",                                                 147 => "Reputation",
              152 => "Rated Arenas",                                                153 => "Battlegrounds",
              154 => "World",                                                       155 => "World Events",
              156 => "Winter Veil",                                                 158 => "Hallow's End",
              159 => "Noblegarden",                                                 160 => "Lunar Festival",
              161 => "Midsummer",                                                   162 => "Brewfest",
              163 => "Children's Week",                                             165 => "Arena",
              168 => "Dungeons & Raids",                                            169 => "Professions",
              170 => "Cooking",                                                     171 => "Fishing",
              172 => "First Aid",                                                   173 => "Professions",
              178 => "Secondary Skills",                                            187 => "Love is in the Air",
              191 => "Gear",                                                        201 => "Reputation",
            14777 => "Eastern Kingdoms",                                          14778 => "Kalimdor",
            14779 => "Outland",                                                   14780 => "Northrend",
            14801 => "Alterac Valley",                                            14802 => "Arathi Basin",
            14803 => "Eye of the Storm",                                          14804 => "Warsong Gulch",
            14805 => "The Burning Crusade",                                       14806 => "Lich King Dungeon",
            14807 => "Dungeons & Raids",                                          14808 => "Classic",
            14821 => "Classic",                                                   14822 => "The Burning Crusade",
            14823 => "Wrath of the Lich King",                                    14861 => "Classic",
            14862 => "The Burning Crusade",                                       14863 => "Wrath of the Lich King",
            14864 => "Classic",                                                   14865 => "The Burning Crusade",
            14866 => "Wrath of the Lich King",                                    14881 => "Strand of the Ancients",
            14901 => "Wintergrasp",                                               14921 => "Lich King Heroic",
            14922 => "Lich King 10-Player Raid",                                  14923 => "Lich King 25-Player Raid",
            14941 => "Argent Tournament",                                         14961 => "Secrets of Ulduar 10-Player Raid",
            14962 => "Secrets of Ulduar 25-Player Raid",                          14963 => "Secrets of Ulduar",
            14981 => "Pilgrim's Bounty",                                          15001 => "Call of the Crusade 10-Player Raid",
            15002 => "Call of the Crusade 25-Player Raid",                        15003 => "Isle of Conquest",
            15021 => "Call of the Crusade",                                       15041 => "Fall of the Lich King 10-Player Raid",
            15042 => "Fall of the Lich King 25-Player Raid",                      15062 => "Fall of the Lich King"
        )
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
        'fishingSkill'  => "25 &ndash; 100% chance to catch a listed fish.",
        'instanceTypes' => ["Zone",     "Transit", "Dungeon",   "Raid",      "Battleground", "Dungeon",  "Arena", "Raid", "Raid"],
        'territories'   => ["Alliance", "Horde",   "Contested", "Sanctuary", "PvP",          "World PvP"],
        'cat'           => array(
            "Eastern Kingdoms",         "Kalimdor",                 "Dungeons",                 "Raids",                    "Unused",                   null,
            "Battlegrounds",            null,                       "Outland",                  "Arenas",                   "Northrend"
        ),
        'floors'        => array(
             206 => ["Norndir Preparation", "Dragonflayer Ascent", "Tyr's Terrace"],
             209 => ["The Courtyard", "Dining Hall", "The Vacant Den", "Lower Observatory", "Upper Observatory", "Lord Godfrey's Chamber", "The Wall Walk"],
             719 => ["The Pool of Ask'Ar", "Moonshrine Sanctum", "The Forgotten Pool"],
             721 => ["The Hall of Gears", "The Dormitory", "Launch Bay", "Tinkers' Court"],
             796 => ["Graveyard", "Library", "Armory", "Cathedral"],
            1196 => ["Lower Pinnacle", "Upper Pinnacle"],
            1337 => ["Hall of the Keepers", "Khaz'Goroth's Seat"],
            1581 => ["The Deadmines", "Ironclad Cove"],
            1583 => ["Tazz'Alaor", "Skitterweb Tunnels", "Hordemar City", "Hall of Blackhand", "Dragonspire Hall", "The Rookery", "Blackrock Stadium"],
            1584 => ["Detention Block", "Shadowforge City"],
            2017 => ["Crusader's Square", "The Gauntlet"],
            2057 => ["The Reliquary", "Chamber of Summoning", "The Headmaster's Study", "Barov Family Vault"],
            2100 => ["Caverns of Maraudon", "Zaetar's Grave"],
            2557 => ["Gordok Commons", "Capital Gardens", "Court of the Highborne", "Prison of Immol'Thar", "Warpwood Quarter", "The Shrine of Eldretharr"],
            2677 => ["Dragonmaw Garrison", "Halls of Strife", "Crimson Laboratories", "Nefarian's Lair"],
            3428 => ["The Hive Undergrounds", "The Temple Gates", "Vault of C'Thun"],
            3456 => ["The Construct Quarter", "The Arachnid Quarter", "The Military Quarter", "The Plague Quarter", "Overview", "Frostwyrm Lair"],
            3457 => ["Servant's Quarters", "Upper Livery Stables", "The Banquet Hall", "The Guest Chambers", "Opera Hall Balcony", "Master's Terrace", "Lower Broken Stair", "Upper Broken Stair", "The Menagerie", "Guardian's Library", "The Repository", "Upper Library", "The Celestial Watch", "Gamesman's Hall", "Medivh's Chambers", "The Power Station", "Netherspace"],
            3715 => ["The Steamvault", "The Cooling Pools"],
            3790 => ["Halls of the Hereafter", "Bridge of Souls"],
            3791 => ["Veil Sethekk", "Halls of Mourning"],
            3848 => ["Stasis Block: Trion", "Stasis Block: Maximus", "Containment Core"],
            3849 => ["The Mechanar", "Calculation Chamber"],
            3959 => ["Illidari Training Grounds", "Karabor Sewers", "Sanctuary of Shadows", "Halls of Anguish", "Gorefiend's Vigil", "Den of Mortal Delights", "Chamber of Command", "Temple Summit"],
            4075 => ["Sunwell Plateau", "Shrine of the Eclipse"],
            4100 => ["Outside Stratholme", "Stratholme City"],
            4131 => ["Grand Magister's Asylum", "Observation Grounds"],
            4196 => ["The Vestibules of Drak'Tharon", "Drak'Tharon Overlook"],
            4228 => ["Band of Variance", "Band of Acceleration", "Band of Transmutation", "Band of Alignment"],
            4272 => ["Unyielding Garrison", "Walk of the Makers"],
            4273 => ["The Grand Approach", "The Antechamber of Ulduar", "The Inner Sanctum of Ulduar", "The Prison of Yogg-Saron", "The Spark of Imagination", "The Mind's Eye"],
            4277 => ["The Brood Pit", "Hadronox's Lair", "The Gilded Gate"],
            4395 => ["Dalaran City", "The Underbelly"],
            4494 => ["Ahn'Kahet", "Level 2"],
            4722 => ["Crusaders' Coliseum", "The Icy Depths"],
            4812 => ["The Lower Citadel", "The Rampart of Skulls", "Deathbringer's Rise", "The Frost Queen's Lair", "The Upper Reaches", "Royal Quarters", "The Frozen Throne", "Frostmourne"]
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
        'opensQDesc'    => "Completing this quest will make the following quests available",
        'closesQ'       => "Closes Quests",
        'closesQDesc'   => "After completing this quest, you will not be able to take these quests",
        'enablesQ'      => "Enables",
        'enablesQDesc'  => "When this quest is active, these quests are also available",
        'enabledByQ'    => "Enabled by",
        'enabledByQDesc'=> "This quest is available only, when one of these quests are active",
        'gainsDesc'     => "Upon completion of this quest you will gain",
        'theTitle'      => 'the title "%s"',                                        // partly REWARD_TITLE
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
        'questPoolDesc' => 'Only %d |4Quest:Quests; from this tab will be available at a time',
        'autoaccept'    => 'Auto Accept',
        'questInfo'     => array(
             0 => "Normal",              1 => "Group",              21 => "Life",               41 => "PvP",                62 => "Raid",               81 => "Dungeon",            82 => "World Event",
            83 => "Legendary",          84 => "Escort",             85 => "Heroic",             88 => "Raid (10)",          89 => "Raid (25)"
        ),
        'cat'           => array(
            0 => array( "Eastern Kingdoms",
                    1 => "Dun Morogh",                       3 => "Badlands",                         4 => "Blasted Lands",                    8 => "Swamp of Sorrows",                 9 => "Northshire Valley",
                   10 => "Duskwood",                        11 => "Wetlands",                        12 => "Elwynn Forest",                   25 => "Blackrock Mountain",              28 => "Western Plaguelands",
                   33 => "Stranglethorn Vale",              36 => "Alterac Mountains",               38 => "Loch Modan",                      40 => "Westfall",                        41 => "Deadwind Pass",
                   44 => "Redridge Mountains",              45 => "Arathi Highlands",                46 => "Burning Steppes",                 47 => "The Hinterlands",                 51 => "Searing Gorge",
                   85 => "Tirisfal Glades",                130 => "Silverpine Forest",              132 => "Coldridge Valley",               139 => "Eastern Plaguelands",            154 => "Deathknell",
                  267 => "Hillsbrad Foothills",           1497 => "Undercity",                     1519 => "Stormwind City",                1537 => "Ironforge",                     2257 => "Deeprun Tram",
                 3430 => "Eversong Woods",                3431 => "Sunstrider Isle",               3433 => "Ghostlands",                    3487 => "Silvermoon City",               4080 => "Isle of Quel'Danas",
                 4298 => "The Scarlet Enclave"
            ),
            1 => array( "Kalimdor",
                   14 => "Durotar",                         15 => "Dustwallow Marsh",                16 => "Azshara",                         17 => "The Barrens",                    141 => "Teldrassil",
                  148 => "Darkshore",                      188 => "Shadowglen",                     215 => "Mulgore",                        220 => "Red Cloud Mesa",                 331 => "Ashenvale",
                  357 => "Feralas",                        361 => "Felwood",                        363 => "Valley of Trials",               400 => "Thousand Needles",               405 => "Desolace",
                  406 => "Stonetalon Mountains",           440 => "Tanaris",                        490 => "Un'Goro Crater",                 493 => "Moonglade",                      618 => "Winterspring",
                 1377 => "Silithus",                      1637 => "Orgrimmar",                     1638 => "Thunder Bluff",                 1657 => "Darnassus",                     1769 => "Timbermaw Hold",
                 3524 => "Azuremyst Isle",                3525 => "Bloodmyst Isle",                3526 => "Ammen Vale",                    3557 => "The Exodar",
             ),
            2 => array( "Dungeons",
                  206 => "Utgarde Keep",                   209 => "Shadowfang Keep",                491 => "Razorfen Kraul",                 717 => "The Stockade",                   718 => "Wailing Caverns",
                  719 => "Blackfathom Deeps",              721 => "Gnomeregan",                     722 => "Razorfen Downs",                 796 => "Scarlet Monastery",             1176 => "Zul'Farrak",
                 1196 => "Utgarde Pinnacle",              1337 => "Uldaman",                       1477 => "Sunken Temple",                 1581 => "The Deadmines",                 1583 => "Blackrock Spire",
                 1584 => "Blackrock Depths",              1941 => "Caverns of Time",               2017 => "Stratholme",                    2057 => "Scholomance",                   2100 => "Maraudon",
                 2366 => "The Black Morass",              2367 => "Old Hillsbrad Foothills",       2437 => "Ragefire Chasm",                2557 => "Dire Maul",                     3535 => "Hellfire Citadel",
                 3562 => "Hellfire Ramparts",             3688 => "Auchindoun",                    3713 => "The Blood Furnace",             3714 => "The Shattered Halls",           3715 => "The Steamvault",
                 3716 => "The Underbog",                  3717 => "The Slave Pens",                3789 => "Shadow Labyrinth",              3790 => "Auchenai Crypts",               3791 => "Sethekk Halls",
                 3792 => "Mana-Tombs",                    3842 => "Tempest Keep",                  3847 => "The Botanica",                  3848 => "The Arcatraz",                  3849 => "The Mechanar",
                 3905 => "Coilfang Reservoir",            4100 => "The Culling of Stratholme",     4131 => "Magisters' Terrace",            4196 => "Drak'Tharon Keep",              4228 => "The Oculus",
                 4264 => "Halls of Stone",                4265 => "The Nexus",                     4272 => "Halls of Lightning",            4277 => "Azjol-Nerub",                   4415 => "The Violet Hold",
                 4416 => "Gundrak",                       4494 => "Ahn'kahet: The Old Kingdom",    4522 => "Icecrown Citadel",              4723 => "Trial of the Champion",         4809 => "The Forge of Souls",
                 4813 => "Pit of Saron",                  4820 => "Halls of Reflection"
            ),
            3 => array( "Raids",
                 1977 => "Zul'Gurub",                     2159 => "Onyxia's Lair",                 2677 => "Blackwing Lair",                2717 => "Molten Core",                   3428 => "Temple of Ahn'Qiraj",
                 3429 => "Ruins of Ahn'Qiraj",            3456 => "Naxxramas",                     3457 => "Karazhan",                      3606 => "Hyjal Summit",                  3607 => "Serpentshrine Cavern",
                 3805 => "Zul'Aman",                      3836 => "Magtheridon's Lair",            3845 => "Tempest Keep",                  3923 => "Gruul's Lair",                  3959 => "Black Temple",
                 4075 => "Sunwell Plateau",               4273 => "Ulduar",                        4493 => "The Obsidian Sanctum",          4500 => "The Eye of Eternity",           4603 => "Vault of Archavon",
                 4722 => "Trial of the Crusader",         4812 => "Icecrown Citadel",              4987 => "The Ruby Sanctum"
            ),
            4 => array( "Classes",
                  -61 => "Warlock",                        -81 => "Warrior",                        -82 => "Shaman",                        -141 => "Paladin",                       -161 => "Mage",
                 -162 => "Rogue",                         -261 => "Hunter",                        -262 => "Priest",                        -263 => "Druid",                         -372 => "Death Knight"
                    ),
            5 => array( "Professions",
                  -24 => "Herbalism",                     -101 => "Fishing",                       -121 => "Blacksmithing",                 -181 => "Alchemy",                       -182 => "Leatherworking",
                 -201 => "Engineering",                   -264 => "Tailoring",                     -304 => "Cooking",                       -324 => "First Aid",                     -371 => "Inscription",
                 -373 => "Jewelcrafting"
            ),
            6 => array( "Battlegrounds",
                 2597 => "Alterac Valley",                3277 => "Warsong Gulch",                 3358 => "Arathi Basin",                  3820 => "Eye of the Storm",              4384 => "Strand of the Ancients",
                 4710 => "Isle of Conquest",               -25 => "All"
            ),
            7 => array( "Miscellaneous",
                   -1 => "Epic",                          -241 => "Tournament",                    -344 => "Legendary",                     -365 => "Ahn'Qiraj War",                 -367 => "Reputation",
                 -368 => "Invasion",                     -1010 => "Dungeon Finder"
            ),
            8 => array( "Outland",
                 3483 => "Hellfire Peninsula",            3518 => "Nagrand",                       3519 => "Terokkar Forest",               3520 => "Shadowmoon Valley",             3521 => "Zangarmarsh",
                 3522 => "Blade's Edge Mountains",        3523 => "Netherstorm",                   3679 => "Skettis",                       3703 => "Shattrath City"
            ),
            9 => array( "Seasonal",
                  -22 => "Seasonal",                       -41 => "Day of the Dead",               -364 => "Darkmoon Faire",                -366 => "Lunar Festival",                -369 => "Midsummer",
                 -370 => "Brewfest",                      -374 => "Noblegarden",                   -375 => "Pilgrim's Bounty",              -376 => "Love is in the Air",           -1001 => "Winter Veil",
                -1002 => "Children's Week",              -1003 => "Hallow's End",                 -1005 => "Harvest Festival"
            ),
            10 => array( "Northrend",
                   65 => "Dragonblight",                    66 => "Zul'Drak",                        67 => "The Storm Peaks",                210 => "Icecrown",                       394 => "Grizzly Hills",
                  495 => "Howling Fjord",                 3537 => "Borean Tundra",                 3711 => "Sholazar Basin",                4024 => "Coldarra",                      4197 => "Wintergrasp",
                 4395 => "Dalaran",                       4742 => "Hrothgar's Landing"
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
    'mail' => array(
        'notFound'      => "This mail doesn't exist.",
        'attachment'    => "Attachment",
        'mailDelivery'  => 'You will receive <a href="?mail=%d">this letter</a>%s%s',
        'mailBy'        => ' by <a href="?npc=%d">%s</a>',
        'mailIn'        => " after %s",
        'delay'         => "Delay",
        'sender'        => "Sender",
        'untitled'      => "Untitled Mail #%d"
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
        '_transfer'     => 'This spell will be converted to <a href="?spell=%d" class="q%d icontiny tinyspecial" style="background-image: url(STATIC_URL/images/wow/icons/tiny/%s.gif)">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        '_affected'     => "Affected Spells",
        '_seeMore'      => "See more",
        '_rankRange'    => "Rank:&nbsp;%d&nbsp;-&nbsp;%d",
        '_showXmore'    => "Show %d More",
        'currentArea'   => '&lt;current area&gt;',
        'discovered'    => "Learned via discovery",
        'ppm'           => "(%s procs per minute)",
        'procChance'    => "Proc chance",
        'starter'       => "Starter spell",
        'trainingCost'  => "Training cost",
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
        'instantPhys'   => "Instant",                       // SPELL_CAST_TIME_INSTANT_NO_MANA
        'castTime' => array(
            "Instant cast",                                 // SPELL_CAST_TIME_INSTANT
            "Wirken in %.3g Sek.",                          // SPELL_CAST_TIME_SEC
            "Wirken in %.3g Min."                           // SPELL_CAST_TIME_MIN
        ),
        'cooldown' => array(
            "Instant cooldown",                             // SPELL_RECAST_TIME_INSTANT not used?
            "%.3g sec cooldown",                            // SPELL_RECAST_TIME_SEC
            "%.3g min cooldown",                            // SPELL_RECAST_TIME_MIN
         // "%.3g hour cooldown",                           // SPELL_RECAST_TIME_HOURS not in 3.3.5
         // "%.3g day cooldown"                             // SPELL_RECAST_TIME_DAYS not in 3.3.5
        ),
        'duration'      => array(                           // SPELL_DURATION_*
            "until cancelled",
            "%.2G sec",
            "%.2G min",
            "%.2G |4hour:hrs;",
            "%.2G |4day:days;"
        ),
        'timeRemaining' => array(                           // SPELL_TIME_REMAINING_*
            "",
            "%d |4second:seconds; remaining",
            "%d |4minute:minutes; remaining",
            "%d |4hour:hours; remaining",
            "%d |4day:days; remaining"
        ),
        'powerCost'     => array(
            -2 => ["%d Health",      "%d Health, plus %d per sec"     ],    // HEALTH_COST        HEALTH_COST_PER_TIME
             0 => ["%d Mana",        "%d Mana, plus %d per sec"       ],    // MANA_COST          MANA_COST_PER_TIME
             1 => ["%d Rage",        "%d Rage, plus %d per sec"       ],    // RAGE_COST          RAGE_COST_PER_TIME
             2 => ["%d Focus",       "%d Focus, plus %d per sec"      ],    // FOCUS_COST         FOCUS_COST_PER_TIME
             3 => ["%d Energy",      "%d Energy, plus %d per sec"     ],    // ENERGY_COST        ENERGY_COST_PER_TIME
             6 => ["%d Runic Power", "%d Runic Power, plus %d per sec"],    // RUNIC_POWER_COST   RUNIC_POWER_COST_PER_TIME
        ),
        'powerDisplayCost' => ["%d %s", "%d %s, plus %d per sec"],          // POWER_DISPLAY_COST POWER_DISPLAY_COST_PER_TIME
        'powerCostRunes'=> ["%d Blood", "%d Unholy", "%d Frost"],   // RUNE_COST_*
        'powerRunes'    => ["Blood", "Unholy", "Frost", "Death"],   // COMBAT_TEXT_RUNE_*
        'powerTypes'    => array(
            // conventional - HEALTH, MANA, RAGE, FOCUS, ENERGY, HAPPINESS, RUNES, RUNIC_POWER / *_COST / *COST_PER_TIME
              -2 => "Health",              0 => "Mana",                1 => "Rage",                2 => "Focus",               3 => "Energy",              4 => "Happiness",
               5 => "Runes",               6 => "Runic Power",
            // powerDisplay - PowerDisplay.dbc -> GlobalStrings.lua POWER_TYPE_*
              -1 => "Ammo",              -41 => "Pyrite",            -61 => "Steam Pressure",   -101 => "Heat",             -121 => "Ooze",             -141 => "Blood Power",
            -142 => "Wrath"
        ),
        'scaling'       => array(
            'directSP' => "+%.2f%% of spell power to direct component",         'directAP' => "+%.2f%% of attack power to direct component",
            'dotSP'    => "+%.2f%% of spell power per tick",                    'dotAP'    => "+%.2f%% of attack power per tick"
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
            -11 => ["Proficiencies", 8 => "Armor", 6 => "Weapon", 10 => "Languages"],
             -4 => "Racial Traits",
             -2 => "Talents",                               // classList
             -6 => "Companions",
             -5 => ["Mounts", 1 => "Ground Mounts", 2 => "Flying Mounts", 3 => "Miscellaneous"],
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
            "Damage",                               "Duration",                             "Threat",                               "Effect 1",                             "Charges",
            "Range",                                "Radius",                               "Critical Hit Chance",                  "All Effects",                          "Casting Time loss",
            "Casting Time",                         "Cooldown",                             "Effect 2",                             "Ignore Armor",                         "Cost",
            "Critical Damage Bonus",                "Chance to Hit",                        "Jump Targets",                         "Proc Chance",                          "Intervall",
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
        'invisibilityType' => ["General", "UNK-1", "UNK-2", "Trap", "UNK-4", "UNK-5", "Drunk", "UNK-7", "UNK-8", "UNK-9", "UNK-10", "UNK-11"],
        'attributes'    => array(                           // index defined by filters
            69 => "All spell effects are harmful",
            57 => "Aura cannot be cancelled",
            51 => "Aura is hidden",
            95 => "Bandage spell",
            61 => "Can be used while dead",
            62 => "Can be used while mounted",
            64 => "Can be used while sitting",
            53 => "Can only be used during daytime",
            54 => "Can only be used during nighttime",
            55 => "Can only be used indoors",
            56 => "Can only be used outdoors",
            79 => "Can only target the player",
            60 => "Cannot be dodged, parried or blocked",
            67 => "Cannot be reflected",
            91 => "Cannot be used in a raid",
            33 => "Castable in combat",
            34 => "Chance to critically hit",
            35 => "Chance to miss",
            27 => "Channeled",
            66 => "Channeled 2",
            85 => "Continues while logged out",
            84 => "Does not appear in log",
            68 => "Does not break stealth",
            81 => "Does not engage target",
            77 => "Does not require shapeshift",
        //  46 => "Disregards immunity",
            47 => "Disregards school immunity",
            78 => "Food/Drink buff",
            71 => "Generates no threat",
            52 => "On next swing (npcs)",
            49 => "On next swing (players)",
            90 => "Only usable in arena",
            92 => "Paladin aura",
            50 => "Passive spell",
            36 => "Persists through death",
            72 => "Pickpocket spell",
            73 => "Remove auras on immunity",
            48 => "Requires a ranged weapon",
            82 => "Requires a wand",
            83 => "Requires an off-hand weapon",
            74 => "Requires fishing pole",
            41 => "Requires Metamorphosis",
            80 => "Requires main hand weapon",
            38 => "Requires Stealth",
            75 => "Requires untapped target",
            58 => "Spell damage depends on caster level",
            39 => "Spellstealable",
            63 => "Starts cooldown after aura fades",
            87 => "Starts ticking at aura application",
            59 => "Stops auto-attack",
        //  76 => "Target must be own item",
            70 => "The target cannot be in combat",
            93 => "Totem",
            42 => "Usable when stunned",
            88 => "Usable while confused",
            89 => "Usable while feared",
            65 => "Uses all power"
        ),
        'unkEffect'     => 'Unknown Effect (%1$d)',
        'effects'       => array(
/*0-5    */ 'None',                     'Instakill',                'School Damage',            'Dummy',                    'Portal Teleport',          'Teleport Units',
/*6+     */ 'Apply Aura',               'Environmental Damage',     'Drain Power',              'Drain Health',             'Heal',                     'Bind',
/*12+    */ 'Portal',                   'Ritual Base',              'Ritual Specialize',        'Ritual Activate Portal',   'Complete Quest',           'Weapon Damage - No School',
/*18+    */ 'Resurrect with % Health',  'Add Extra Attacks',        'Can Dodge',                'Can Evade',                'Can Parry',                'Can Block',
/*24+    */ 'Create Item',              'Can Use Weapon',           'Know Defense Skill',       'Persistent Area Aura',     'Summon',                   'Leap',
/*30+    */ 'Give Power',               'Weapon Damage - %',        'Trigger Missile',          'Open Lock',                'Transform Item',           'Apply Area Aura - Party',
/*36+    */ 'Learn Spell',              'Know Spell Defense',       'Dispel',                   'Learn Language',           'Dual Wield',               'Jump to Target',
/*42+    */ 'Jump Behind Target',       'Teleport Target to Caster','Learn Skill Step',         'Give Honor',               'Spawn',                    'Trade Skill',
/*48+    */ 'Stealth',                  'Detect Stealthed',         'Summon Object',            'Force Critical Hit',       'Guarantee Hit',            'Enchant Item Permanent',
/*54+    */ 'Enchant Item Temporary',   'Tame Creature',            'Summon Pet',               'Learn Spell - Pet',        'Weapon Damage - Flat',     'Open Item & Fast Loot',
/*60+    */ 'Proficiency',              'Send Script Event',        'Burn Power',               'Modify Threat - Flat',     'Trigger Spell',            'Apply Area Aura - Raid',
/*66+    */ 'Create Mana Gem',          'Heal to Full',             'Interrupt Cast',           'Distract',                 'Distract Move',            'Pickpocket',
/*72+    */ 'Far Sight',                'Forget Talents',           'Apply Glyph',              'Heal Mechanical',          'Summon Object - Temporary','Script Effect',
/*78+    */ 'Attack',                   'Abort All Pending Attacks','Add Combo Points',         'Create House',             'Bind Sight',               'Duel',
/*84+    */ 'Stuck',                    'Summon Player',            'Activate Object',          'Siege Damage',             'Repair Building',          'Siege Building Action',
/*90+    */ 'Kill Credit',              'Threat All',               'Enchant Held Item',        'Force Deselect',           'Self Resurrect',           'Skinning',
/*96+    */ 'Charge',                   'Cast Button',              'Knock Back',               'Disenchant',               'Inebriate',                'Feed Pet',
/*102+   */ 'Dismiss Pet',              'Give Reputation',          'Summon Object (Trap)',     'Summon Object (Battle S.)','Summon Object (#3)',       'Summon Object (#4)',
/*108+   */ 'Dispel Mechanic',          'Summon Dead Pet',          'Destroy All Totems',       'Durability Damage - Flat', 'Summon Demon',             'Resurrect with Flat Health',
/*114+   */ 'Taunt',                    'Durability Damage - %',    'Skin Player Corpse (PvP)', 'AoE Resurrect with % Health','Learn Skill',            'Apply Area Aura - Pet',
/*120+   */ 'Teleport to Graveyard',    'Normalized Weapon Damage', null,                       'Take Flight Path',         'Pull Towards',             'Modify Threat - %',
/*126+   */ 'Spell Steal ',             'Prospect',                 'Apply Area Aura - Friend', 'Apply Area Aura - Enemy',  'Redirect Done Threat %',   'Play Sound',
/*132+   */ 'Play Music',               'Unlearn Specialization',   'Kill Credit2',             'Call Pet',                 'Heal for % of Total Health','Give % of Total Power',
/*138+   */ 'Leap Back',                'Abandon Quest',            'Force Cast',               'Force Spell Cast with Value','Trigger Spell with Value','Apply Area Aura - Pet Owner',
/*144+   */ 'Knockback to Dest.',       'Pull Towards Dest.',       'Activate Rune',            'Fail Quest',               null,                       'Charge to Dest',
/*150+   */ 'Start Quest',              'Trigger Spell 2',          'Summon - Refer-A-Friend',  'Create Tamed Pet',         'Discover Flight Path',     'Dual Wield 2H Weapons',
/*156+   */ 'Add Socket to Item',       'Create Tradeskill Item',   'Milling',                  'Rename Pet',               null,                       'Change Talent Spec. Count',
/*162-167*/ 'Activate Talent Spec.',    null,                       'Remove Aura',              null,                       null,                       'Update Player Phase'
        ),
        'unkAura'       => 'Unknown Aura (%1$d)',
        'auras'         => array(
/*0-   */   'None',                                 'Bind Sight',                           'Possess',                              'Periodic Damage - Flat',               'Dummy',
/*5+   */   'Confuse',                              'Charm',                                'Fear',                                 'Periodic Heal',                        'Mod Attack Speed',
            'Mod Threat',                           'Taunt',                                'Stun',                                 'Mod Damage Done - Flat',               'Mod Damage Taken - Flat',
            'Damage Shield',                        'Stealth',                              'Mod Stealth Detection Level',          'Invisibility',                         'Mod Invisibility Detection Level',
            'Regenerate Health - %',                'Regenerate Power - %',                 'Mod Resistance - Flat',                'Periodically Trigger Spell',           'Periodically Give Power',
/*25+  */   'Pacify',                               'Root',                                 'Silence',                              'Reflect Spells',                       'Mod Stat - Flat',
            'Mod Skill - Temporary',                'Increase Run Speed %',                 'Mod Mounted Speed %',                  'Decrease Run Speed %',                 'Mod Maximum Health - Flat',
            'Mod Maximum Power - Flat',             'Shapeshift',                           'Spell Effect Immunity',                'Spell Aura Immunity',                  'Spell School Immunity',
            'Damage Immunity',                      'Dispel Type Immunity',                 'Proc Trigger Spell',                   'Proc Trigger Damage',                  'Track Creatures',
            'Track Resources',                      'Ignore All Gear',                      'Mod Parry %',                          null,                                   'Mod Dodge %',
/*50+  */   'Mod Critical Healing Amount %',        'Mod Block %',                          'Mod Physical Crit Chance',             'Periodically Drain Health',            'Mod Physical Hit Chance',
            'Mod Spell Hit Chance',                 'Transform',                            'Mod Spell Crit Chance',                'Increase Swim Speed %',                'Mod Damage Done Versus Creature',
            'Pacify & Silence',                     'Mod Size %',                           'Periodically Transfer Health',         'Periodic Transfer Power',              'Periodic Drain Power',
            'Mod Spell Haste % (not stacking)',     'Feign Death',                          'Disarm',                               'Stalked',                              'Mod Absorb School Damage',
            'Extra Attacks',                        'Mod Spell School Crit Chance',         'Mod Spell School Power Cost - %',      'Mod Spell School Power Cost - Flat',   'Reflect Spells School From School',
/*75+  */   'Force Language',                       'Far Sight',                            'Mechanic Immunity',                    'Mounted',                              'Mod Damage Done - %',
            'Mod Stat - %',                         'Split Damage - %',                     'Underwater Breathing',                 'Mod Base Resistance - Flat',           'Mod Health Regeneration - Flat',
            'Mod Power Regeneration - Flat',        'Create Item on Death',                 'Mod Damage Taken - %',                 'Mod Health Regeneration - %',          'Periodic Damage - %',
            'Mod Resist Chance',                    'Mod Aggro Range',                      'Prevent Fleeing',                      'Unattackable',                         'Interrupt Power Decay',
            'Ghost',                                'Spell Magnet',                         'Absorb Damage - Mana Shield',          'Mod Skill Value',                      'Mod Attack Power - Flat',
/*100+ */   'Always Show Debuffs',                  'Mod Resistance - %',                   'Mod Melee Attack Power vs Creature',   'Mod Total Threat - Temporary',         'Water Walking',
            'Feather Fall',                         'Levitate / Hover',                     'Add Modifier - Flat',                  'Add Modifier - %',                     'Proc Spell on Target',
            'Mod Power Regeneration - %',           'Intercept % of Attacks Against Target','Override Class Script',                'Mod Ranged Damage Taken - Flat',       'Mod Ranged Damage Taken - %',
            'Mod Healing Taken - Flat',             'Allow % of Health Regen During Combat','Mod Mechanic Resistance',              'Mod Healing Taken - %',                'Share Pet Tracking',
            'Untrackable',                          'Beast Lore',                           'Mod Offhand Damage Done %',            'Mod Target Resistance - Flat',         'Mod Ranged Attack Power - Flat',
/*125+ */   'Mod Melee Damage Taken - Flat',        'Mod Melee Damage Taken - %',           'Mod Attacker Ranged Attack Power',     'Possess Pet',                          'Increase Run Speed % - Stacking',
            'Incerase Mounted Speed % - Stacking',  'Mod Ranged Attack Power vs Creature',  'Mod Maximum Power - %',                'Mod Maximum Health - %',               'Allow % of Mana Regen During Combat',
            'Mod Healing Done - Flat',              'Mod Healing Done - %',                 'Mod Stat - %',                         'Mod Melee Haste %',                    'Force Reputation',
            'Mod Ranged Haste %',                   'Mod Ranged Ammo Haste %',              'Mod Base Resistance - %',              'Mod Resistance - Flat (not stacking)', 'Safe Fall',
            'Increase Pet Talent Points',           'Allow Exotic Pets Taming',             'Mechanic Immunity Mask',               'Retain Combo Points',                  'Reduce Pushback Time %',
/*150+ */   'Mod Shield Block Value - %',           'Track Stealthed',                      'Mod Player Aggro Range',               'Split Damage - Flat',                  'Mod Stealth Level',
            'Mod Underwater Breathing %',           'Mod All Reputation Gained by %',       'Done Pet Damage Multiplier',           'Mod Shield Block Value - Flat',        'No PvP Credit',
            'Mod AoE Avoidance',                    'Mod Health Regen During Combat',       'Mana Burn',                            'Mod Melee Critical Damage %',          null,
            'Mod Attacker Melee Attack Power',      'Mod Melee Attack Power - %',           'Mod Ranged Attack Power - %',          'Mod Damage Done vs Creature',          'Mod Crit Chance vs Creature',
            'Change Object Visibility for Player',  'Mod Run Speed (not stacking)',         'Mod Mounted Speed (not stacking)',     null,                                   'Mod Spell Power by % of Stat',
/*175+ */   'Mod Healing Power by % of Stat',       'Spirit of Redemption',                 'AoE Charm',                            'Mod Debuff Resistance - %',            'Mod Attacker Spell Crit Chance',
            'Mod Spell Power vs Creature',          null,                                   'Mod Resistance by % of Stat',          'Mod Threat % of Critical Hits',        'Mod Attacker Melee Hit Chance',
            'Mod Attacker Ranged Hit Chance',       'Mod Attacker Spell Hit Chance',        'Mod Attacker Melee Crit Chance',       'Mod Attacker Ranged Crit Chance',      'Mod Rating',
            'Mod Reputation Gained %',              'Limit Movement Speed',                 'Mod Attack Speed %',                   'Mod Haste % (gain)',                   'Mod Target School Absorb %',
            'Mod Target School Absorb for Ability', 'Mod Cooldowns',                        'Mod Attacker Crit Chance',             null,                                   'Mod Spell Hit Chance',
/*200+ */   'Mod Kill Experience Gained %',         'Can Fly',                              'Ignore Combat Result',                 'Mod Attacker Melee Crit Damage %',     'Mod Attacker Ranged Crit Damage %',
            'Mod Attacker Spell Crit Damage %',     'Mod Vehicle Flight Speed %',           'Mod Mounted Flight Speed %',           'Mod Flight Speed %',                   'Mod Mounted Flight Speed % (always)',
            'Mod Vehicle Speed % (always)',         'Mod Flight Speed % (not stacking)',    'Mod Ranged Attack Power by % of Stat', 'Mod Rage Generated from Damage Dealt', 'Tamed Pet Passive',
            'Arena Preparation',                    'Mod Spell Haste %',                    'Killing Spree',                        'Mod Ranged Haste %',                   'Mod Mana Regeneration by % of Stat',
            'Mod Combat Rating by % of Stat',       'Ignore Threat',                        null,                                   'Raid Proc from Charge',                null,
/*225+ */   'Raid Proc from Charge with Value',     'Periodic Dummy',                       'Periodically Trigger Spell with Value','Detect Stealth',                       'Mod AoE Damage Taken %',
            'Mod Maximum Health - Flat (no stacking)','Proc Trigger Spell with Value',      'Mod Mechanic Duration %',              'Change other Humanoid Display',        'Mod Mechanic Duration % (not stacking)',
            'Mod Dispel Resistance %',              'Control Vehicle',                      'Mod Spell Power by % of Attack Power', 'Mod Healing Power by % of Attack Power','Mod Size % (not stacking)',
            'Mod Expertise',                        'Force Move Forward',                   'Mod Spell & Healing Power by % of Int','Faction Override',                     'Comprehend Language',
            'Mod Aura Duration by Dispel Type',   'Mod Aura Duration by Dispel Type (not stacking)', 'Clone Caster',                'Mod Combat Result Chance',             'Convert Rune',
/*250+ */   'Mod Maximum Health - Flat (stacking)', 'Mod Enemy Dodge Chance',               'Mod Haste % (loss)',                   'Mod Critical Block Chance',            'Disarm Offhand',
            'Mod Mechanic Damage Taken %',          'No Reagent Cost',                      'Mod Target Resistance by Spell Class', 'Mod Spell Visual',                     'Mod Periodic Healing Taken %',
            'Screen Effect',                        'Phase',                                'Ability Ignore Aurastate',             'Allow Only Ability',                   null,
            null,                                   null,                                   'Cancel Aura Buffer at % of Caster Health','Mod Attack Power by % of Stat',     'Ignore Target Resistance',
            'Ignore Target Resistance for Ability', 'Mod Damage Taken % from Caster',       'Ignore Swing Timer Reset',             'X-Ray',                                'Ability Consume No Ammo',
/*275+ */   'Mod Ability Ignore Shapeshift',        'Mod Mechanic Damage Done %',           'Mod Max Affected Targets',             'Disarm Ranged Weapon',                 'Spawn Effect',
            'Mod Armor Penetration %',              'Mod Honor Gain %',                     'Mod Base Health %',                    'Mod Healing Taken % from Caster',      'Linked Aura',
            'Mod Attack Power by School Resistance','Allow Periodic Ability to Crit',       'Mod Spell Deflect Chance',             'Ignore Hit Direction',                 null,
            'Mod Crit Chance',                      'Mod Quest Experience Gained %',        'Open Stable',                          'Override Spells',                      'Prevent Power Regeneration',
            null,                                   'Set Vehicle Id',                       'Spirit Burst',                         'Strangulate',                          null,
/*300+ */   'Share Damage %',                       'Mod Absorb School Healing',            null,                                   'Mod Damage Done vs Aurastate - %',     'Fake Inebriate',
            'Mod Minimum Speed %',                  null,                                   'Heal Absorb Test',                     'Mod Critical Strike Chance for Caster',null,
            'Mod Pet AoE Damage Avoidance',         null,                                   null,                                   null,                                   'Prevent Ressurection',
/* -316*/   'Underwater Walking',                   'Periodic Haste'
        )
    ),
    'item' => array(
        'notFound'      => "This item doesn't exist.",
        'armor'         => "%s Armor",                      // ARMOR_TEMPLATE
        'block'         => "%s Block",                      // SHIELD_BLOCK_TEMPLATE
        'charges'       => "%d |4Charge:Charges;",          // ITEM_SPELL_CHARGES
        'locked'        => "Locked",                        // LOCKED
        'ratingString'  => '<!--rtg%%%1$d-->%2$.2F%%&nbsp;@&nbsp;L<!--lvl-->%3$d',
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
        '_transfer'     => 'This item will be converted to <a href="?item=%d" class="q%d icontiny tinyspecial" style="background-image: url(STATIC_URL/images/wow/icons/tiny/%s.gif)">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
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
        'duration'      => array(                           // ITEM_DURATION_*
            '',
            "Duration: %d sec",
            "Duration: %d min",
            "Duration: %d |4hour:hrs;",
            "Duration: %d |4day:days;"
        ),
        'cooldown'      => array(                           // ITEM_COOLDOWN_TOTAL*
            "(%s Cooldown)",
            "(%d Sec Cooldown)",
            "(%d Min Cooldown)",
            "(%d |4Hour:Hours; Cooldown)",
            "(%d |4Day:Days; Cooldown)"
        ),
        'damage'        => array(                           // *DAMAGE_TEMPLATE*
                        //  basic,                          basic /w school,                add basic,                  add basic /w school
            'single'    => ["%d Damage",                    "%d %s Damage",                 "+ %d Damage",              "+%d %s Damage"             ],
            'range'     => ["%d - %d Damage",               "%d - %d %s Damage",            "+ %d - %d Damage",         "+%d - %d %s Damage"        ],
            'ammo'      => ["Adds %g damage per second",    "Adds %g %s damage per second", "+ %g damage per second",   "+ %g %s damage per second" ]
        ),
        'gems'          => "Gems",
        'socketBonus'   => "Socket Bonus: %s",              // ITEM_SOCKET_BONUS
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
                 2 => "Companions",                  4 => "Other (Miscellaneous)"
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
