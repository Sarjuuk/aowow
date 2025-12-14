<?php

namespace Aowow;

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
        'numSQL'        => "Number of SQL queries",
        'timeSQL'       => "Time of SQL queries",
        'noJScript'     => '<b>This site makes extensive use of JavaScript.</b><br />Please <a href="https://www.google.com/support/adsense/bin/answer.py?answer=12654" target="_blank">enable JavaScript</a> in your browser.',
     // 'userProfiles'  => "My Profiles",
        'pageNotFound'  => "This %s doesn't exist.",
        'gender'        => "Gender",
        'sex'           => [null, "Male", "Female"],
        'players'       => "Players",
        'thePlayer'     => "The Player",
        'quickFacts'    => "Quick Facts",
        'screenshots'   => "Screenshots",
        'videos'        => "Videos",
        'side'          => "Side: ",
        'related'       => "Related",
        'contribute'    => "Contribute",
     // 'replyingTo'    => "The answer to a comment from",
        'submit'        => "Submit",
        'save'          => 'Save',
        'cancel'        => "Cancel",
        'rewards'       => "Rewards",
        'gains'         => "Gains",
     // 'login'         => "Login",
        'forum'         => "Forum",
        'siteRep'       => "Reputation: ",
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
        'any'           => "Any",
        'all'           => "All",

        // filter
        'extSearch'     => "Extended search",
        'addFilter'     => "Add another Filter",
        'match'         => "Match: ",
        'allFilter'     => "All filters",
        'oneFilter'     => "At least one",
        'applyFilter'   => "Apply filter",
        'resetForm'     => "Reset Form",
        'refineSearch'  => 'Tip: Refine your search by browsing a <a href="javascript:;" id="fi_subcat">subcategory</a>.',
        'clear'         => "clear",
        'exactMatch'    => "Exact match",
        '_reqLevel'     => "Required level: ",

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
        'preset'        => "Preset: ",
        'addWeight'     => "Add another weight",
        'createWS'      => "Create a weight scale",
        'jcGemsOnly'    => "Include <span%s>JC-only</span> gems",
        'cappedHint'    => 'Tip: <a href="javascript:;" onclick="fi_presetDetails();">Remove</a> weights for capped statistics such as Hit rating.',
        'groupBy'       => "Group By: ",
        'gb'            => array(
            ["None", "none"],         ["Slot", "slot"],       ["Level", "level"],     ["Source", "source"]
        ),
        'compareTool'   => "Item Comparison Tool",
        'talentCalc'    => "Talent Calculator",
        'petCalc'       => "Hunter Pet Calculator",
        'chooseClass'   => "Choose a class:",
        'chooseFamily'  => "Choose a pet family:",

        // search
        'search'        => "Search",
        'foundResult'   => "Search Results for",
        'noResult'      => "No Results for",
        'tryAgain'      => "Please try some different keywords or check your spelling.",
        'ignoredTerms'  => "The following words were ignored in your search: %s",

        // formating
        'colon'         => ': ',
        'dateFmtShort'  => "Y/m/d",
        'dateFmtLong'   => "Y/m/d \a\\t g:i A",
        'dateFmtIntl'   => "MMMM d, y",
        'nfSeparators'  => [',', '.'],
        'n_a'           => "n/a",

        // date time
        'date'          => "Date",
        'date_colon'    => "Date: ",
        'date_on'       => "on ",
        'date_ago'      => "%s ago",
        'date_at'       => " at ",
        'date_to'       => " to ",
        'date_simple'   => '%2$d/%1$d/%3$d',
        'unknowndate'   => "Unknown date",
        'ddaysago'      => "%d days ago",
        'today'         => "today",
        'yesterday'     => "yesterday",
        'noon'          => "noon",
        'midnight'      => "midnight",
        'am'            => "AM",
        'pm'            => "PM",

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
        'author'    => "Author: ",
        'spec'      => "Specialization: ",
        'sticky'    => "Sticky Status",
        'views'     => "Views: ",
        'patch'     => "Patch",
        'added'     => "Added: ",
        'rating'    => "Rating: ",
        'votes'     => "[span id=guiderating-value]%.2g[/span]/5 ([span id=guiderating-votes][n5=%d][/span] votes) [span id=guiderating][/span]",
        'noVotes'   => "not enough votes [span id=guiderating][/span]",
        'byAuthor'  => "By %s",
        'notFound'  => "This guide doesn't exist.",
        'clTitle'     => 'Changelog For "<a href="?guide=%1$d">%2$s</a>"',
        'clStatusSet' => 'Status set to %s: ',
        'clCreated'   => 'Created: ',
        'clMinorEdit' => 'Minor Edit',
        'editor'    => array(
            'fullTitle'       => 'Full Title',
            'fullTitleTip'    => 'The full guide title will be used on the guide page and may include SEO-oriented phrasing.',
            'name'            => 'Name',
            'nameTip'         => 'This should be a simple and clear name of what the guide is, for use in places like menus and guide lists.',
            'description'     => 'Description',
            'descriptionTip'  => "Description that will be used for search engines.<br /> <br />If left empty, it will be generated automatically.",
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
                GuideMgr::STATUS_DRAFT    => 'Your guide is in &quot;Draft&quot; status and you are the only one able to see it. Keep editing it as long as you like, and when you feel it&apos;s ready submit it for review.',
                GuideMgr::STATUS_REVIEW   => 'Your guide is being reviewed.',
                GuideMgr::STATUS_APPROVED => 'Your guide has been published.',
                GuideMgr::STATUS_REJECTED => 'Your guide has been rejected. After it\'s shortcomings have been remedied you may resubmit it for review.',
                GuideMgr::STATUS_ARCHIVED => 'Your guide is outdated and has been archived. Is will no longer be listed and can\'t be edited.',
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
        'atSize'        => "Size: ",
        'profiler'      => "Character Profiler",
        'completion'    => "Completion: ",
        'attainedBy'    => "Attained by %d%% of profiles",
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
    'video' => array(
        'submission'    => "Video Suggestion",
        'thanks'        => array(
            'contrib' => "Thanks a lot for your contribution!",
            'goBack'  => '<a href="?%s=%d">Click here</a> to go back to the page you came from.',
            'note'    => "Note: Your video will need to be approved before appearing on the site. This can take up to 72 hours."
        ),
        'error'         => array(
            'isPrivate'   => "The suggested video is private.",
            'noExist'     => "No video found at the provided Url.",
            'selectVI'    => "Please enter valid video information.", // message_novideo
            'notAllowed'  => "You are not allowed to suggest videos!",
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
        'guild'         => "Guild",
        'guilds'        => "Guilds",
        'arenateam'     => "Arena Team",
        'arenateams'    => "Arena Teams",
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
        'difficulty'    => "Difficulty: ",
        'dispelType'    => "Dispel type",
        'duration'      => "Duration",
        'eventShort'    => "Event: %s",
        'flags'         => "Flags",
        'glyphType'     => "Glyph type: ",
        'level'         => "Level",
        'mechanic'      => "Mechanic",
        'mechAbbr'      => "Mech.: ",
        'meetingStone'  => "Meeting Stone: ",
        'requires'      => "Requires %s",
        'requires2'     => "Requires",
        'reqLevel'      => "Requires Level %s",
        'reqSkillLevel' => "Required skill level: ",
        'school'        => "School",
        'type'          => "Type: ",
        'valueDelim'    => " to ",
        'target'        => "<target>",

        'pvp'           => "PvP",                           // PVP
        'honorPoints'   => "Honor Points",                  // HONOR_POINTS
        'arenaPoints'   => "Arena Points",                  // ARENA_POINTS
        'heroClass'     => "Hero class",
        'resource'      => "Resource: ",
        'resources'     => "Resources: ",
        'role'          => "Role: ",                        // ROLE
        'roles'         => "Roles: ",                       // LFG_TOOLTIP_ROLES
        'specs'         => "Specs: ",
        '_roles'        => ["Healer", "Melee DPS", "Ranged DPS", "Tank"],

        'phases'        => "Phases",
        'mode'          => "Mode: ",
        'modes'         => array(
            [-1 => "Any", "Normal / Normal 10", "Heroic / Normal 25", "Heroic 10", "Heroic 25"],
            ["Normal", "Heroic"],
            ["Normal 10", "Normal 25", "Heroic 10", "Heroic 25"]
        ),
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
            null,                                                           ["Scout", "Private"],                                           ["Grunt", "Corporal"],
            ["Sergeant", "Sergeant"],                                       ["Senior Sergeant", "Master Sergeant"],                         ["First Sergeant", "Sergeant Major"],
            ["Stone Guard", "Knight"],                                      ["Blood Guard", "Knight-Lieutenant"],                           ["Legionnaire", "Knight-Captain"],
            ["Centurion", "Knight-Champion"],                               ["Champion", "Lieutenant Commander"],                           ["Lieutenant General", "Commander"],
            ["General", "Marshal"],                                         ["Warlord", "Field Marshal"],                                   ["High Warlord", "Grand Marshal"]
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
/*idx:0*/   array(
                UNIT_STAND_STATE_STAND            => 'Standing',
                UNIT_STAND_STATE_SIT              => 'Sitting on ground',
                UNIT_STAND_STATE_SIT_CHAIR        => 'Sitting on chair',
                UNIT_STAND_STATE_SLEEP            => 'Sleeping',
                UNIT_STAND_STATE_SIT_LOW_CHAIR    => 'Sitting on low chair',
                UNIT_STAND_STATE_SIT_MEDIUM_CHAIR => 'Sitting on medium chair',
                UNIT_STAND_STATE_SIT_HIGH_CHAIR   => 'Sitting on high chair',
                UNIT_STAND_STATE_DEAD             => 'Dead',
                UNIT_STAND_STATE_KNEEL            => 'Kneeing',
                UNIT_STAND_STATE_SUBMERGED        => 'Submerged'
            ),
            null,
/*idx:2*/   array(
                UNIT_VIS_FLAGS_UNK1        => 'UNK-1',
                UNIT_VIS_FLAGS_CREEP       => 'Creep',
                UNIT_VIS_FLAGS_UNTRACKABLE => 'Untrackable',
                UNIT_VIS_FLAGS_UNK4        => 'UNK-4',
                UNIT_VIS_FLAGS_UNK5        => 'UNK-5'
            ),
/*idx:3*/   array(
                UNIT_BYTE1_ANIM_TIER_GROUND    => 'ground animations',
                UNIT_BYTE1_ANIM_TIER_SWIM      => 'swimming animations',
                UNIT_BYTE1_ANIM_TIER_HOVER     => 'hovering animations',
                UNIT_BYTE1_ANIM_TIER_FLY       => 'flying animations',
                UNIT_BYTE1_ANIM_TIER_SUMBERGED => 'submerged animations'
            ),
            'bytesIdx' => ['StandState', null, 'VisFlags', 'AnimTier'],
            'valueUNK' => '[span class=q10]unhandled value [b class=q1]%d[/b] provided for UnitFieldBytes1 on offset [b class=q1]%d[/b][/span]',
            'idxUNK'   => '[span class=q10]unused offset [b class=q1]%d[/b] provided for UnitFieldBytes1[/span]'
        )
    ),
    'smartAI' => array(
        'eventUNK'      => '[span class=q10]Unknwon event #[b class=q1]%d[/b] in use.[/span]',
        'eventTT'       => '[b class=q1]EventType %d[/b][br][table][tr][td]PhaseMask[/td][td=header]0x%04X[/td][/tr][tr][td]Chance[/td][td=header]%d%%[/td][/tr][tr][td]Flags[/td][td=header]0x%04X[/td][/tr][tr][td]Param1[/td][td=header]%d[/td][/tr][tr][td]Param2[/td][td=header]%d[/td][/tr][tr][td]Param3[/td][td=header]%d[/td][/tr][tr][td]Param4[/td][td=header]%d[/td][/tr][tr][td]Param5[/td][td=header]%d[/td][/tr][/table]',
        'events'        => array(
            SmartEvent::EVENT_UPDATE_IC               => ['(%12$d)?:When in combat, ;(%11$s)?After %11$s:Instantly;', 'Repeat every %s'],
            SmartEvent::EVENT_UPDATE_OOC              => ['(%12$d)?:When out of combat, ;(%11$s)?After %11$s:Instantly;', 'Repeat every %s'],
            SmartEvent::EVENT_HEALTH_PCT              => ['At %11$s%% Health', 'Repeat every %s'],
            SmartEvent::EVENT_MANA_PCT                => ['At %11$s%% Mana', 'Repeat every %s'],
            SmartEvent::EVENT_AGGRO                   => ['On Aggro', ''],
            SmartEvent::EVENT_KILL                    => ['On killing (%3$d)?a player:(%4$d)?[npc=%4$d]:any creature;;', 'Cooldown: %s'],
            SmartEvent::EVENT_DEATH                   => ['On death', ''],
            SmartEvent::EVENT_EVADE                   => ['When evading', ''],
            SmartEvent::EVENT_SPELLHIT                => ['When hit by (%11$s)?%11$s :;(%1$d)?[spell=%1$d]:Spell;', 'Cooldown: %s'],
            SmartEvent::EVENT_RANGE                   => ['On #target# at %11$sm', 'Repeat every %s'],
/* 10*/     SmartEvent::EVENT_OOC_LOS                 => ['While out of combat,(%11$s)? %11$s:; (%5$d)?player:unit; enters line of sight within %2$dm', 'Cooldown: %s'],
            SmartEvent::EVENT_RESPAWN                 => ['On respawn(%11$s)? in %11$s:;(%12$d)? in [zone=%12$d]:;', ''],
            SmartEvent::EVENT_TARGET_HEALTH_PCT       => ['On #target# at %11$s%% health', 'Repeat every %s'],
            SmartEvent::EVENT_VICTIM_CASTING          => ['#target# is casting (%3$d)?[spell=%3$d]:any spell;', 'Repeat every %s'],
            SmartEvent::EVENT_FRIENDLY_HEALTH         => ['Friendly NPC within %2$dm is at %1$d health', 'Repeat every %s'],
            SmartEvent::EVENT_FRIENDLY_IS_CC          => ['Friendly NPC within %1$dm is crowd controlled', 'Repeat every %s'],
            SmartEvent::EVENT_FRIENDLY_MISSING_BUFF   => ['Friendly NPC within %2$dm is missing [spell=%1$d]', 'Repeat every %s'],
            SmartEvent::EVENT_SUMMONED_UNIT           => ['Just summoned (%1$d)?[npc=%1$d]:any creature;', 'Cooldown: %s'],
            SmartEvent::EVENT_TARGET_MANA_PCT         => ['On #target# at %11$s%% mana', 'Repeat every %s'],
            SmartEvent::EVENT_ACCEPTED_QUEST          => ['Giving (%1$d)?[quest=%1$d]:any quest;', 'Cooldown: %s'],
/* 20*/     SmartEvent::EVENT_REWARD_QUEST            => ['Rewarding (%1$d)?[quest=%1$d]:any quest;', 'Cooldown: %s'],
            SmartEvent::EVENT_REACHED_HOME            => ['Arriving at home coordinates', ''],
            SmartEvent::EVENT_RECEIVE_EMOTE           => ['Being targeted with [emote=%1$d]', 'Cooldown: %s'],
            SmartEvent::EVENT_HAS_AURA                => ['(%2$d)?Having %2$d stacks of:Missing aura; [spell=%1$d]', 'Repeat every %s'],
            SmartEvent::EVENT_TARGET_BUFFED           => ['#target# has (%2$d)?%2$d stacks of:aura; [spell=%1$d]', 'Repeat every %s'],
            SmartEvent::EVENT_RESET                   => ['On reset', ''],
            SmartEvent::EVENT_IC_LOS                  => ['While in combat,(%11$s)? %11$s:; (%5$d)?player:unit; enters line of sight within %2$dm', 'Cooldown: %s'],
            SmartEvent::EVENT_PASSENGER_BOARDED       => ['A passenger has boarded', 'Cooldown: %s'],
            SmartEvent::EVENT_PASSENGER_REMOVED       => ['A passenger got off', 'Cooldown: %s'],
            SmartEvent::EVENT_CHARMED                 => ['(%1$d)?On being charmed:On charm wearing off;', ''],
/* 30*/     SmartEvent::EVENT_CHARMED_TARGET          => ['When charming #target#', ''],
            SmartEvent::EVENT_SPELLHIT_TARGET         => ['When #target# gets hit by (%11$s)?%11$s :;(%1$d)?[spell=%1$d]:Spell;', 'Cooldown: %s'],
            SmartEvent::EVENT_DAMAGED                 => ['After taking %11$s points of damage', 'Repeat every %s'],
            SmartEvent::EVENT_DAMAGED_TARGET          => ['After #target# took %11$s points of damage', 'Repeat every %s'],
            SmartEvent::EVENT_MOVEMENTINFORM          => ['Ended (%1$d)?%11$s:movement; on point #[b]%2$d[/b]', ''],
            SmartEvent::EVENT_SUMMON_DESPAWNED        => ['Summoned npc(%1$d)? [npc=%1$d]:; despawned', 'Cooldown: %s'],
            SmartEvent::EVENT_CORPSE_REMOVED          => ['On corpse despawn', ''],
            SmartEvent::EVENT_AI_INIT                 => ['AI initialized', ''],
            SmartEvent::EVENT_DATA_SET                => ['Data field #[b]%1$d[/b] is set to [b]%2$d[/b]', 'Cooldown: %s'],
            SmartEvent::EVENT_WAYPOINT_START          => ['Start pathing from (%1$d)?waypoint #[b]%1$d[/b]:any waypoint;(%2$d)? on path #[b]%2$d[/b]:;', ''],
/* 40*/     SmartEvent::EVENT_WAYPOINT_REACHED        => ['Reaching (%1$d)?waypoint #[b]%1$d[/b]:any waypoint;(%2$d)? on path #[b]%2$d[/b]:;', ''],
            SmartEvent::EVENT_TRANSPORT_ADDPLAYER     => null,
            SmartEvent::EVENT_TRANSPORT_ADDCREATURE   => null,
            SmartEvent::EVENT_TRANSPORT_REMOVE_PLAYER => null,
            SmartEvent::EVENT_TRANSPORT_RELOCATE      => null,
            SmartEvent::EVENT_INSTANCE_PLAYER_ENTER   => null,
            SmartEvent::EVENT_AREATRIGGER_ONTRIGGER   => ['On activation', ''],
            SmartEvent::EVENT_QUEST_ACCEPTED          => null,
            SmartEvent::EVENT_QUEST_OBJ_COMPLETION    => null,
            SmartEvent::EVENT_QUEST_COMPLETION        => null,
/* 50*/     SmartEvent::EVENT_QUEST_REWARDED          => null,
            SmartEvent::EVENT_QUEST_FAIL              => null,
            SmartEvent::EVENT_TEXT_OVER               => ['(%2$d)?[npc=%2$d]:any creature; is done talking TextGroup #[b]%1$d[/b]', ''],
            SmartEvent::EVENT_RECEIVE_HEAL            => ['Received %11$s points of healing', 'Cooldown: %s'],
            SmartEvent::EVENT_JUST_SUMMONED           => ['On being summoned', ''],
            SmartEvent::EVENT_WAYPOINT_PAUSED         => ['Pausing path on (%1$d)?waypoint #[b]%1$d[/b]:any waypoint;(%2$d)? on path #[b]%2$d[/b]:;', ''],
            SmartEvent::EVENT_WAYPOINT_RESUMED        => ['Resuming path on (%1$d)?waypoint #[b]%1$d[/b]:any waypoint;(%2$d)? on path #[b]%2$d[/b]:;', ''],
            SmartEvent::EVENT_WAYPOINT_STOPPED        => ['Stopping path on (%1$d)?waypoint #[b]%1$d[/b]:any waypoint;(%2$d)? on path #[b]%2$d[/b]:;', ''],
            SmartEvent::EVENT_WAYPOINT_ENDED          => ['Ending current path on (%1$d)?waypoint #[b]%1$d[/b]:any waypoint;(%2$d)? on path #[b]%2$d[/b]:;', ''],
            SmartEvent::EVENT_TIMED_EVENT_TRIGGERED   => ['Timed event #[b]%1$d[/b] is triggered', ''],
/* 60*/     SmartEvent::EVENT_UPDATE                  => ['(%11$s)?After %11$s:Instantly;', 'Repeat every %s'],
            SmartEvent::EVENT_LINK                    => ['After Event %11$s', ''],
            SmartEvent::EVENT_GOSSIP_SELECT           => ['Selecting Gossip Option:[br](%11$s)?[span class=q1]%11$s[/span]:Menu #[b]%1$d[/b] - Option #[b]%2$d[/b];', ''],
            SmartEvent::EVENT_JUST_CREATED            => ['On being spawned for the first time', ''],
            SmartEvent::EVENT_GOSSIP_HELLO            => ['Opening Gossip', '(%1$d)?onGossipHello:;(%2$d)?onReportUse:;'],
            SmartEvent::EVENT_FOLLOW_COMPLETED        => ['Finished following', ''],
            SmartEvent::EVENT_EVENT_PHASE_CHANGE      => ['Event Phase changed and matches %11$s', ''],
            SmartEvent::EVENT_IS_BEHIND_TARGET        => ['Facing the backside of #target#', 'Cooldown: %s'],
            SmartEvent::EVENT_GAME_EVENT_START        => ['[event=%1$d] started', ''],
            SmartEvent::EVENT_GAME_EVENT_END          => ['[event=%1$d] ended', ''],
/* 70*/     SmartEvent::EVENT_GO_LOOT_STATE_CHANGED   => ['State changed to: %11$s', ''],
            SmartEvent::EVENT_GO_EVENT_INFORM         => ['Event #[b]%1$d[/b] defined in template was trigered', ''],
            SmartEvent::EVENT_ACTION_DONE             => ['Action #[b]%1$d[/b] requested by other script', ''],
            SmartEvent::EVENT_ON_SPELLCLICK           => ['SpellClick was triggered', ''],
            SmartEvent::EVENT_FRIENDLY_HEALTH_PCT     => ['Health of #target# is at %11$s%%', 'Repeat every %s'],
            SmartEvent::EVENT_DISTANCE_CREATURE       => ['[npc=%11$d](%1$d)? [small class=q0](GUID\u003A %1$d)[/small]:; is within %3$dm', 'Repeat every %s'],
            SmartEvent::EVENT_DISTANCE_GAMEOBJECT     => ['[object=%11$d](%1$d)? [small class=q0](GUID\u003A %1$d)[/small]:; is within %3$dm', 'Repeat every %s'],
            SmartEvent::EVENT_COUNTER_SET             => ['Counter #[b]%1$d[/b] is equal to [b]%2$d[/b]', 'Cooldown: %s'],
            SmartEvent::EVENT_SCENE_START             => null,
            SmartEvent::EVENT_SCENE_TRIGGER           => null,
/* 80*/     SmartEvent::EVENT_SCENE_CANCEL            => null,
            SmartEvent::EVENT_SCENE_COMPLETE          => null,
            SmartEvent::EVENT_SUMMONED_UNIT_DIES      => ['My summoned (%1$d)?[npc=%1$d]:NPC; died', 'Cooldown: %s'],
            SmartEvent::EVENT_ON_SPELL_CAST           => ['On [spell=%1$d] cast success', 'Cooldown: %s'],
            SmartEvent::EVENT_ON_SPELL_FAILED         => ['On [spell=%1$d] cast failed', 'Cooldown: %s'],
            SmartEvent::EVENT_ON_SPELL_START          => ['On [spell=%1$d] cast start', 'Cooldown: %s'],
            SmartEvent::EVENT_ON_DESPAWN              => ['On despawn', ''],
            SmartEvent::EVENT_SEND_EVENT_TRIGGER      => null,
            SmartEvent::EVENT_AREATRIGGER_EXIT        => null,
            SmartEvent::EVENT_ON_AURA_APPLIED         => ['On aura [spell=%1$d] applied', 'Cooldown: %s'],
            SmartEvent::EVENT_ON_AURA_REMOVED         => ['On aura [spell=%1$d] removed', 'Cooldown: %s']
        ),
        'eventFlags'    => array(
            SmartEvent::FLAG_NO_REPEAT     => 'No Repeat',
            SmartEvent::FLAG_DIFFICULTY_0  => '5N Dungeon / 10N Raid',
            SmartEvent::FLAG_DIFFICULTY_1  => '5H Dungeon / 25N Raid',
            SmartEvent::FLAG_DIFFICULTY_2  => '10H Raid',
            SmartEvent::FLAG_DIFFICULTY_3  => '25H Raid',
            SmartEvent::FLAG_DEBUG_ONLY    => null,         // only occurs in debug build; do not output
            SmartEvent::FLAG_NO_RESET      => 'No Reset',
            SmartEvent::FLAG_WHILE_CHARMED => 'While Charmed'
        ),
        'actionUNK'     => '[span class=q10]Unknown action #[b class=q1]%d[/b] in use.[/span]',
        'actionTT'      => '[b class=q1]ActionType %d[/b][br][table][tr][td]Param1[/td][td=header]%d[/td][/tr][tr][td]Param2[/td][td=header]%d[/td][/tr][tr][td]Param3[/td][td=header]%d[/td][/tr][tr][td]Param4[/td][td=header]%d[/td][/tr][tr][td]Param5[/td][td=header]%d[/td][/tr][tr][td]Param6[/td][td=header]%d[/td][/tr][/table]',
        'actions'       => array(                           // [body, footer]
            null,
            SmartAction::ACTION_TALK                               => ['(%3$d)?Say:#target# says; (%%11$d)?TextGroup:[span class=q10]unknown text[/span]; #[b]%1$d[/b] to (%3$d)?#target#:invoker;%11$s', 'Duration: %s'],
            SmartAction::ACTION_SET_FACTION                        => ['(%1$d)?Set faction of #target# to [faction=%11$d]:Reset faction of #target#;.', ''],
            SmartAction::ACTION_MORPH_TO_ENTRY_OR_MODEL            => ['(%11$d)?Reset apperance.:Take the appearance of;(%1$d)? [npc=%1$d].:;(%2$d)?[model npc=%2$d border=1 float=right][/model]:;', ''],
            SmartAction::ACTION_SOUND                              => ['Play sound to (%2$d)?invoking player:all players in sight;:[div][sound=%1$d][/div]', 'Played by environment.'],
            SmartAction::ACTION_PLAY_EMOTE                         => ['(%1$d)?Emote [emote=%1$d] to #target#.: End emote state.;', ''],
            SmartAction::ACTION_FAIL_QUEST                         => ['Fail [quest=%1$d] for #target#.', ''],
            SmartAction::ACTION_OFFER_QUEST                        => ['(%2$d)?Add [quest=%1$d] to #target#\'s log:Offer [quest=%1$d] to #target#;.', ''],
            SmartAction::ACTION_SET_REACT_STATE                    => ['#target# becomes %11$s.', ''],
            SmartAction::ACTION_ACTIVATE_GOBJECT                   => ['#target# becomes activated.', ''],
/* 10*/     SmartAction::ACTION_RANDOM_EMOTE                       => ['Emote %11$s to #target#.', ''],
            SmartAction::ACTION_CAST                               => ['Cast [spell=%1$d] at #target#.', '%1$s'],
            SmartAction::ACTION_SUMMON_CREATURE                    => ['Summon [npc=%1$d](%3$d)? for %11$s:;(%4$d)?, attacking invoker.:;', '%1$s'],
            SmartAction::ACTION_THREAT_SINGLE_PCT                  => ['Modify #target#\'s threat by %11$+d%%.', ''],
            SmartAction::ACTION_THREAT_ALL_PCT                     => ['Modify the threat of all opponents by %11$+d%%.', ''],
            SmartAction::ACTION_CALL_AREAEXPLOREDOREVENTHAPPENS    => ['Satisfy exploration event of [quest=%1$d] for #target#.', ''],
            SmartAction::ACTION_SET_INGAME_PHASE_ID                => null,
            SmartAction::ACTION_SET_EMOTE_STATE                    => ['(%1$d)?Continuously emote [emote=%1$d] to #target#.:End emote state;', ''],
            SmartAction::ACTION_SET_UNIT_FLAG                      => ['Set (%2$d)?UnitFlags2:UnitFlags; %11$s.', ''],
            SmartAction::ACTION_REMOVE_UNIT_FLAG                   => ['Unset (%2$d)?UnitFlags2:UnitFlags; %11$s.', ''],
/* 20*/     SmartAction::ACTION_AUTO_ATTACK                        => ['(%1$d)?Start:Stop; auto attacking #target#.', ''],
            SmartAction::ACTION_ALLOW_COMBAT_MOVEMENT              => ['(%1$d)?Enable:Disable; combat movement.', ''],
            SmartAction::ACTION_SET_EVENT_PHASE                    => ['Set Event Phase of #target# to [b]%1$d[/b].', ''],
            SmartAction::ACTION_INC_EVENT_PHASE                    => ['(%1$d)?Increment:Decrement; Event Phase of #target#.', ''],
            SmartAction::ACTION_EVADE                              => ['#target# evades to (%1$d)?last stored:spawn; position.', ''],
            SmartAction::ACTION_FLEE_FOR_ASSIST                    => ['Flee for assistance.', 'Use default flee emote'],
            SmartAction::ACTION_CALL_GROUPEVENTHAPPENS             => ['Satisfy exploration event of [quest=%1$d] for group of #target#.', ''],
            SmartAction::ACTION_COMBAT_STOP                        => ['End current combat.', ''],
            SmartAction::ACTION_REMOVEAURASFROMSPELL               => ['Remove(%2$d)? %2$d charges of:;(%1$d)? all auras: [spell=%1$d]\'s aura; from #target#.', 'Only own auras'],
            SmartAction::ACTION_FOLLOW                             => ['Follow #target#(%1$d)? at %1$dm distance:;(%3$d)? until reaching [npc=%3$d]:;.(%12$d)?Exploration event of [quest=%4$d] will be satisfied.:;(%13$d)? A kill of [npc=%4$d] will be credited.:;', '(%11$d)?Follow angle\u003A %7$.2f°:;'],
/* 30*/     SmartAction::ACTION_RANDOM_PHASE                       => ['Pick random Event Phase from %11$s.', ''],
            SmartAction::ACTION_RANDOM_PHASE_RANGE                 => ['Pick random Event Phase between %1$d and %2$d.', ''],
            SmartAction::ACTION_RESET_GOBJECT                      => ['Reset #target#.', ''],
            SmartAction::ACTION_CALL_KILLEDMONSTER                 => ['A kill of [npc=%1$d] is credited to (%11$s)?%11$s:#target#;.', ''],
            SmartAction::ACTION_SET_INST_DATA                      => ['Set instance (%3$d)?BossState:data field; #[b]%1$d[/b] to [b]%2$d[/b].', ''],
            SmartAction::ACTION_SET_INST_DATA64                    => ['Store GUID of #target# in instance data field #[b]%1$d[/b].', ''],
            SmartAction::ACTION_UPDATE_TEMPLATE                    => ['Transform to become [npc=%1$d].', 'Use level from [npc=%1$d]'],
            SmartAction::ACTION_DIE                                => ['Die…&nbsp;&nbsp;&nbsp;painfully.', ''],
            SmartAction::ACTION_SET_IN_COMBAT_WITH_ZONE            => ['Set in combat with units in zone.', ''],
            SmartAction::ACTION_CALL_FOR_HELP                      => ['Call for help within %1$dm.', 'Use default help emote'],
/* 40*/     SmartAction::ACTION_SET_SHEATH                         => ['Sheath %11$s weapons.', ''],
            SmartAction::ACTION_FORCE_DESPAWN                      => ['Despawn #target#(%1$d)? after %11$s:;(%2$d)? and then respawn after %12$s:;', ''],
            SmartAction::ACTION_SET_INVINCIBILITY_HP_LEVEL         => ['Become invincible below (%2$d)?%2$d%%:%1$d; HP.', ''],
            SmartAction::ACTION_MOUNT_TO_ENTRY_OR_MODEL            => ['(%11$d)?Dismount.:Mount ;(%1$d)?[npc=%1$d].:;(%2$d)?[model npc=%2$d border=1 float=right][/model]:;', ''],
            SmartAction::ACTION_SET_INGAME_PHASE_MASK              => ['Set visibility of #target# to phase %11$s.', ''],
            SmartAction::ACTION_SET_DATA                           => ['[b]%2$d[/b] is stored in data field #[b]%1$d[/b] of #target#.', ''],
            SmartAction::ACTION_ATTACK_STOP                        => ['Stop attacking.', ''],
            SmartAction::ACTION_SET_VISIBILITY                     => ['#target# becomes (%1$d)?visible:invisible;.', ''],
            SmartAction::ACTION_SET_ACTIVE                         => ['#target# becomes Grid (%1$d)?active:inactive;.', ''],
            SmartAction::ACTION_ATTACK_START                       => ['Start attacking #target#.', ''],
/* 50*/     SmartAction::ACTION_SUMMON_GO                          => ['Summon [object=%1$d](%2$d)? for %11$s:; at #target#.', 'Despawn not linked to summoner'],
            SmartAction::ACTION_KILL_UNIT                          => ['#target# dies!', ''],
            SmartAction::ACTION_ACTIVATE_TAXI                      => ['Fly from [span class=q1]%11$s[/span] to [span class=q1]%12$s[/span]', ''],
            SmartAction::ACTION_WP_START                           => ['(%1$d)?Run:Walk; on waypoint path #[b]%2$d[/b](%4$d)? and be bound to [quest=%4$d]:;.(%5$d)? Despawn after %11$s:;', 'Repeatable(%12$s)? [DEPRECATED] React %12$s on path:;'],
            SmartAction::ACTION_WP_PAUSE                           => ['Pause waypoint path for %11$s', ''],
            SmartAction::ACTION_WP_STOP                            => ['End waypoint path(%1$d)? and despawn after %11$s:.; (%2$d)?[quest=%2$d]:quest from start action; (%3$d)?fails:is completed;.', ''],
            SmartAction::ACTION_ADD_ITEM                           => ['Give %2$d [item=%1$d] to #target#.', ''],
            SmartAction::ACTION_REMOVE_ITEM                        => ['Remove %2$d [item=%1$d] from #target#.', ''],
            SmartAction::ACTION_INSTALL_AI_TEMPLATE                => ['Behave as a %11$s.', ''],
            SmartAction::ACTION_SET_RUN                            => ['(%1$d)?Enable:Disable; run speed.', ''],
/* 60*/     SmartAction::ACTION_SET_DISABLE_GRAVITY                => ['(%1$d)?Defy:Respect; gravity!', ''],
            SmartAction::ACTION_SET_SWIM                           => ['(%1$d)?Enable:Disable; swimming.', ''],
            SmartAction::ACTION_TELEPORT                           => ['#target# is teleported to [lightbox=map zone=%11$d(%12$s)? pins=%12$s:;]World Coordinates[/lightbox].', ''],
            SmartAction::ACTION_SET_COUNTER                        => ['(%3$d)?Set:Increase; Counter #[b]%1$d[/b] of #target# (%3$d)?to:by; [b]%2$d[/b].', ''],
            SmartAction::ACTION_STORE_TARGET_LIST                  => ['Store #target# as target in #[b]%1$d[/b].', ''],
            SmartAction::ACTION_WP_RESUME                          => ['Continue on waypoint path.', ''],
            SmartAction::ACTION_SET_ORIENTATION                    => ['Set orientation to (%11$s)?face %11$s:Home Position;.', ''],
            SmartAction::ACTION_CREATE_TIMED_EVENT                 => ['(%6$d)?%6$d%% chance to:; Trigger timed event #[b]%1$d[/b](%11$s)? after %11$s:;.', 'Repeat every %s'],
            SmartAction::ACTION_PLAYMOVIE                          => ['Play Movie #[b]%1$d[/b] to #target#.', ''],
            SmartAction::ACTION_MOVE_TO_POS                        => ['Move (%4$d)?within %4$dm of:to; Point #[b]%1$d[/b] at #target#(%2$d)? on a transport:;.', 'pathfinding disabled'],
/* 70*/     SmartAction::ACTION_ENABLE_TEMP_GOBJ                   => ['#target# is respawned for %11$s.', ''],
            SmartAction::ACTION_EQUIP                              => ['(%11$s)?Equip %11$s:Unequip non-standard items;(%1$d)? from equipment template #[b]%1$d[/b]:; on #target#.', 'Note: creature items do not necessarily have an item template'],
            SmartAction::ACTION_CLOSE_GOSSIP                       => ['Close Gossip Window.', ''],
            SmartAction::ACTION_TRIGGER_TIMED_EVENT                => ['Trigger previously defined timed event #[b]%1$d[/b].', ''],
            SmartAction::ACTION_REMOVE_TIMED_EVENT                 => ['Delete previously defined timed event #[b]%1$d[/b].', ''],
            SmartAction::ACTION_ADD_AURA                           => ['Apply aura from [spell=%1$d] on #target#.', ''],
            SmartAction::ACTION_OVERRIDE_SCRIPT_BASE_OBJECT        => ['Set #target# as base for further SmartAI events.', ''],
            SmartAction::ACTION_RESET_SCRIPT_BASE_OBJECT           => ['Reset base for SmartAI events.', ''],
            SmartAction::ACTION_CALL_SCRIPT_RESET                  => ['Reset current SmartAI.', ''],
            SmartAction::ACTION_SET_RANGED_MOVEMENT                => ['Set ranged attack distance to [b]%1$d[/b]m(%2$d)?, at %2$d°:;.', ''],
/* 80*/     SmartAction::ACTION_CALL_TIMED_ACTIONLIST              => ['Call Timed Actionlist [url=#sai-actionlist-%1$d onclick=TalTabClick(%1$d)]#%1$d[/url]. Updates %11$s.', ''],
            SmartAction::ACTION_SET_NPC_FLAG                       => ['Set #target#\'s npc flags to %11$s.', ''],
            SmartAction::ACTION_ADD_NPC_FLAG                       => ['Add %11$s npc flags to #target#.', ''],
            SmartAction::ACTION_REMOVE_NPC_FLAG                    => ['Remove %11$s npc flags from #target#.', ''],
            SmartAction::ACTION_SIMPLE_TALK                        => ['#target# says (%11$s)?TextGroup:[span class=q10]unknown text[/span]; #[b]%1$d[/b] %11$s', ''],
            SmartAction::ACTION_SELF_CAST                          => ['#target# casts [spell=%1$d] at #target#.(%4$d)? (max. %4$d |4target:targets;):;', '%1$s'],
            SmartAction::ACTION_CROSS_CAST                         => ['%11$s casts [spell=%1$d] at #target#.', '%1$s'],
            SmartAction::ACTION_CALL_RANDOM_TIMED_ACTIONLIST       => ['Call Timed Actionlist at random: %11$s', ''],
            SmartAction::ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST => ['Call Timed Actionlist at random from range: %11$s', ''],
            SmartAction::ACTION_RANDOM_MOVE                        => ['(%1$d)?Move #target# to a random point within %1$dm:#target# ends idle movement;.', ''],
/* 90*/     SmartAction::ACTION_SET_UNIT_FIELD_BYTES_1             => ['Set UnitFieldBytes1 %11$s for #target#.', ''],
            SmartAction::ACTION_REMOVE_UNIT_FIELD_BYTES_1          => ['Unset UnitFieldBytes1 %11$s for #target#.', ''],
            SmartAction::ACTION_INTERRUPT_SPELL                    => ['Interrupt (%2$d)?cast of [spell=%2$d]:current spell cast;.', '(%1$d)?Including instant spells.:;(%3$d)? Including delayed spells.:;'],
            SmartAction::ACTION_SEND_GO_CUSTOM_ANIM                => ['Set animation progress to [b]%1$d[/b].', ''],
            SmartAction::ACTION_SET_DYNAMIC_FLAG                   => ['Set Dynamic Flag to %11$s on #target#.', ''],
            SmartAction::ACTION_ADD_DYNAMIC_FLAG                   => ['Add Dynamic Flag %11$s to #target#.', ''],
            SmartAction::ACTION_REMOVE_DYNAMIC_FLAG                => ['Remove Dynamic Flag %11$s from #target#.', ''],
            SmartAction::ACTION_JUMP_TO_POS                        => ['Jump to fixed position — [b]X: %12$.2f,  Y: %13$.2f,  Z: %14$.2f, [i]v[/i][sub]xy[/sub]: %1$d [i]v[/i][sub]z[/sub]: %2$d[/b]', ''],
            SmartAction::ACTION_SEND_GOSSIP_MENU                   => ['Display Gossip entry #[b]%1$d[/b] / TextID #[b]%2$d[/b].', ''],
            SmartAction::ACTION_GO_SET_LOOT_STATE                  => ['Set loot state of #target# to %11$s.', ''],
/*100*/     SmartAction::ACTION_SEND_TARGET_TO_TARGET              => ['Send targets stored in #[b]%1$d[/b] to #target#.', ''],
            SmartAction::ACTION_SET_HOME_POS                       => ['Set Home Position to (%11$d)?current position.:fixed position — [b]X: %12$.2f,  Y: %13$.2f,  Z: %14$.2f[/b];', ''],
            SmartAction::ACTION_SET_HEALTH_REGEN                   => ['(%1$d)?Allow:Prevent; health regeneration for #target#.', ''],
            SmartAction::ACTION_SET_ROOT                           => ['(%1$d)?Prevent:Allow; movement for #target#.', ''],
            SmartAction::ACTION_SET_GO_FLAG                        => ['Set GameObject Flag to %11$s on #target#.', ''],
            SmartAction::ACTION_ADD_GO_FLAG                        => ['Add GameObject Flag %11$s to #target#.', ''],
            SmartAction::ACTION_REMOVE_GO_FLAG                     => ['Remove GameObject Flag %11$s from #target#.', ''],
            SmartAction::ACTION_SUMMON_CREATURE_GROUP              => ['Summon Creature Group #[b]%1$d[/b](%2$d)?, attacking invoker:;.[br](%11$s)?[span class=breadcrumb-arrow]&nbsp;[/span]%11$s:[span class=q0]<empty group>[/span];', ''],
            SmartAction::ACTION_SET_POWER                          => ['%11$s is set to [b]%2$d[/b] for #target#.', ''],
            SmartAction::ACTION_ADD_POWER                          => ['Add [b]%2$d[/b] %11$s to #target#.', ''],
/*110*/     SmartAction::ACTION_REMOVE_POWER                       => ['Remove [b]%2$d[/b] %11$s from #target#.', ''],
            SmartAction::ACTION_GAME_EVENT_STOP                    => ['Stop [event=%1$d].', ''],
            SmartAction::ACTION_GAME_EVENT_START                   => ['Start [event=%1$d].', ''],
            SmartAction::ACTION_START_CLOSEST_WAYPOINT             => ['#target# starts moving along a defined waypoint path. Enter path on the closest of these nodes: %11$s.', ''],
            SmartAction::ACTION_MOVE_OFFSET                        => ['Move to relative position — [b]X: %12$.2f,  Y: %13$.2f,  Z: %14$.2f[/b]', ''],
            SmartAction::ACTION_RANDOM_SOUND                       => ['Play a random sound to (%5$d)?invoking player:all players in sight;:%11$s', 'Played by environment.'],
            SmartAction::ACTION_SET_CORPSE_DELAY                   => ['Set corpse despawn delay for #target# to %11$s.', 'Apply Looted Corpse Decay Factor'],
            SmartAction::ACTION_DISABLE_EVADE                      => ['(%1$d)?Prevent:Allow; entering Evade Mode.', ''],
            SmartAction::ACTION_GO_SET_GO_STATE                    => ['Set gameobject state to %11$s.'. ''],
            SmartAction::ACTION_SET_CAN_FLY                        => ['(%1$d)?Enable:Disable; flight.', ''],
/*120*/     SmartAction::ACTION_REMOVE_AURAS_BY_TYPE               => ['Remove all Auras with [b]%11$s[/b] from #target#.', ''],
            SmartAction::ACTION_SET_SIGHT_DIST                     => ['Set sight range to %1$dm for #target#.', ''],
            SmartAction::ACTION_FLEE                               => ['#target# flees for assistance for %11$s.', ''],
            SmartAction::ACTION_ADD_THREAT                         => ['Modify threat level of #target# by %11$+d points.', ''],
            SmartAction::ACTION_LOAD_EQUIPMENT                     => ['(%2$d)?Unequip non-standard items:Equip %11$s; from equipment template #[b]%1$d[/b] on #target#.', 'Note: creature items do not necessarily have an item template'],
            SmartAction::ACTION_TRIGGER_RANDOM_TIMED_EVENT         => ['Trigger previously defined timed event in id range %11$s.', ''],
            SmartAction::ACTION_REMOVE_ALL_GAMEOBJECTS             => ['Remove all gameobjects owned by #target#.', ''],
            SmartAction::ACTION_PAUSE_MOVEMENT                     => ['Pause movement from slot #[b]%1$d[/b] for %11$s.', 'Forced'],
            SmartAction::ACTION_PLAY_ANIMKIT                       => null,
            SmartAction::ACTION_SCENE_PLAY                         => null,
/*130*/     SmartAction::ACTION_SCENE_CANCEL                       => null,
            SmartAction::ACTION_SPAWN_SPAWNGROUP                   => ['Spawn SpawnGroup [b]%11$s[/b](%12$s)? SpawnFlags\u003A %12$s:; %13$s', 'Cooldown: %s'],
            SmartAction::ACTION_DESPAWN_SPAWNGROUP                 => ['Despawn SpawnGroup [b]%11$s[/b](%12$s)? SpawnFlags\u003A %12$s:; %13$s', 'Cooldown: %s'],
            SmartAction::ACTION_RESPAWN_BY_SPAWNID                 => ['Respawn %11$s [small class=q0](GUID: %2$d)[/small]', ''],
            SmartAction::ACTION_INVOKER_CAST                       => ['Invoker casts [spell=%1$d] at #target#.(%4$d)? (max. %4$d |4target:targets;):;', '%1$s'],
            SmartAction::ACTION_PLAY_CINEMATIC                     => ['Play cinematic #[b]%1$d[/b] for #target#', ''],
            SmartAction::ACTION_SET_MOVEMENT_SPEED                 => ['Set speed of MotionType #[b]%1$d[/b] to [b]%11$.2f[/b]', ''],
            SmartAction::ACTION_PLAY_SPELL_VISUAL_KIT              => null,
            SmartAction::ACTION_OVERRIDE_LIGHT                     => ['(%3$d)?Change skybox in [zone=%1$d] to #[b]%3$d[/b]:Reset skybox in  [zone=%1$d];.', 'Transition: %s'],
            SmartAction::ACTION_OVERRIDE_WEATHER                   => ['Change weather in [zone=%1$d] to %11$s at %3$d%% intensity.', ''],
/*140*/     SmartAction::ACTION_SET_AI_ANIM_KIT                    => null,
            SmartAction::ACTION_SET_HOVER                          => ['(%1$d)?Enable:Disable; hovering.', ''],
            SmartAction::ACTION_SET_HEALTH_PCT                     => ['Set health percentage of #target# to %1$d%%.', ''],
            SmartAction::ACTION_CREATE_CONVERSATION                => null,
            SmartAction::ACTION_SET_IMMUNE_PC                      => ['(%1$d)?Enable:Disable; #target# immunity to players.', ''],
            SmartAction::ACTION_SET_IMMUNE_NPC                     => ['(%1$d)?Enable:Disable; #target# immunity to NPCs.', ''],
            SmartAction::ACTION_SET_UNINTERACTIBLE                 => ['(%1$d)?Prevent:Allow; interaction with #target#.', ''],
            SmartAction::ACTION_ACTIVATE_GAMEOBJECT                => ['Activate Gameobject (Method: %1$d)', ''],
            SmartAction::ACTION_ADD_TO_STORED_TARGET_LIST          => ['Add #target# as target to list #%1$d.', ''],
            SmartAction::ACTION_BECOME_PERSONAL_CLONE_FOR_PLAYER   => null,
/*150*/     SmartAction::ACTION_TRIGGER_GAME_EVENT                 => null,
            SmartAction::ACTION_DO_ACTION                          => null
        ),
        'targetUNK'     => '[span class=q10]unknown target #[b class=q1]%d[/b][/span]',
        'targetTT'      => '[b class=q1]TargetType %d[/b][br][table][tr][td]Param1[/td][td=header]%d[/td][/tr][tr][td]Param2[/td][td=header]%d[/td][/tr][tr][td]Param3[/td][td=header]%d[/td][/tr][tr][td]Param4[/td][td=header]%d[/td][/tr][tr][td]X[/td][td=header]%17$.2f[/td][/tr][tr][td]Y[/td][td=header]%18$.2f[/td][/tr][tr][td]Z[/td][td=header]%19$.2f[/td][/tr][tr][td]O[/td][td=header]%20$.2f[/td][/tr][/table]',
        'targets'       => array(
            SmartTarget::TARGET_NONE                   => '[span class=q0]<None>[/span]',
            SmartTarget::TARGET_SELF                   => 'self',
            SmartTarget::TARGET_VICTIM                 => 'Opponent',
            SmartTarget::TARGET_HOSTILE_SECOND_AGGRO   => '2nd (%2$d)?player:unit;(%1$d)? within %1$dm:; in threat list(%11$s)? using %11$s:;',
            SmartTarget::TARGET_HOSTILE_LAST_AGGRO     => 'last (%2$d)?player:unit;(%1$d)? within %1$dm:; in threat list(%11$s)? using %11$s:;',
            SmartTarget::TARGET_HOSTILE_RANDOM         => 'random (%2$d)?player:unit;(%1$d)? within %1$dm:;(%11$s)? using %11$s:;',
            SmartTarget::TARGET_HOSTILE_RANDOM_NOT_TOP => 'random non-tank (%2$d)?player:unit;(%1$d)? within %1$dm:;(%11$s)? using %11$s:;',
            SmartTarget::TARGET_ACTION_INVOKER         => 'Invoker',
            SmartTarget::TARGET_POSITION               => 'world coordinates',
            SmartTarget::TARGET_CREATURE_RANGE         => '(%1$d)?instance of [npc=%1$d]:any creature; within %11$sm(%4$d)? (max. %4$d |4target:targets;):;',
/*10*/      SmartTarget::TARGET_CREATURE_GUID          => '(%11$d)?[npc=%11$d]:NPC; [small class=q0](GUID: %1$d)[/small]',
            SmartTarget::TARGET_CREATURE_DISTANCE      => '(%1$d)?instance of [npc=%1$d]:any creature;(%2$d)? within %2$dm:;(%3$d)? (max. %3$d |4target:targets;):;',
            SmartTarget::TARGET_STORED                 => 'previously stored targets',
            SmartTarget::TARGET_GAMEOBJECT_RANGE       => '(%1$d)?instance of [object=%1$d]:any object; within %11$sm(%4$d)? (max. %4$d |4target:targets;):;',
            SmartTarget::TARGET_GAMEOBJECT_GUID        => '(%11$d)?[object=%11$d]:gameobject; [small class=q0](GUID: %1$d)[/small]',
            SmartTarget::TARGET_GAMEOBJECT_DISTANCE    => '(%1$d)?instance of [object=%1$d]:any object;(%2$d)? within %2$dm:;(%3$d)? (max. %3$d |4target:targets;):;',
            SmartTarget::TARGET_INVOKER_PARTY          => 'Invokers party',
            SmartTarget::TARGET_PLAYER_RANGE           => 'all players within %11$sm',
            SmartTarget::TARGET_PLAYER_DISTANCE        => 'all players within %1$dm',
            SmartTarget::TARGET_CLOSEST_CREATURE       => 'closest (%3$d)?dead:alive; (%1$d)?[npc=%1$d]:creature; within (%2$d)?%2$d:100;m',
/*20*/      SmartTarget::TARGET_CLOSEST_GAMEOBJECT     => 'closest (%1$d)?[object=%1$d]:gameobject; within (%2$d)?%2$d:100;m',
            SmartTarget::TARGET_CLOSEST_PLAYER         => 'closest player within %1$dm',
            SmartTarget::TARGET_ACTION_INVOKER_VEHICLE => 'Invokers vehicle',
            SmartTarget::TARGET_OWNER_OR_SUMMONER      => 'owner or summoner',
            SmartTarget::TARGET_THREAT_LIST            => 'all units(%1$d)? within %1$dm:; engaged in combat with me',
            SmartTarget::TARGET_CLOSEST_ENEMY          => 'closest attackable (%2$d)?player:unit; within %1$dm',
            SmartTarget::TARGET_CLOSEST_FRIENDLY       => 'closest friendly (%2$d)?player:unit; within %1$dm',
            SmartTarget::TARGET_LOOT_RECIPIENTS        => 'all players eligible for loot',
            SmartTarget::TARGET_FARTHEST               => 'furthest engaged (%2$d)?player:unit; within %1$dm(%3$d)? and line of sight:;',
            SmartTarget::TARGET_VEHICLE_PASSENGER      => 'vehicle accessory in (%1$d)?seat %11$s:all seats;',
/*30*/      SmartTarget::TARGET_CLOSEST_UNSPAWNED_GO   => 'closest unspawned (%1$d)?[object=%1$d]:, gameobject; within %11$sm'
        ),
        'castFlags'     => array(
            SmartAI::CAST_FLAG_INTERRUPT_PREV => 'Interrupt current cast',
            SmartAI::CAST_FLAG_TRIGGERED      => 'Triggered',
            SmartAI::CAST_FLAG_AURA_MISSING   => 'Aura missing',
            SmartAI::CAST_FLAG_COMBAT_MOVE    => 'Combat movement'
        ),
        'spawnFlags'    => array(
            SmartAI::SPAWN_FLAG_IGNORE_RESPAWN => 'Override and reset respawn timer',
            SmartAI::SPAWN_FLAG_FORCE_SPAWN    => 'Force spawn if already in world',
            SmartAI::SPAWN_FLAG_NOSAVE_RESPAWN => 'Remove respawn time on despawn'
        ),
        'GOStates'       => ['active', 'ready', 'destroyed'],
        'summonTypes'    => [null, 'Despawn timed or when corpse disappears', 'Despawn timed or when dying', 'Despawn timed', 'Despawn timed out of combat', 'Despawn when dying', 'Despawn timed after death', 'Despawn when corpse disappears', 'Despawn manually'],
        'aiTpl'          => ['basic AI', 'spell caster', 'turret', 'passive creature', 'cage for creature', 'caged creature'],
        'reactStates'    => ['passive', 'defensive', 'aggressive', 'assisting'],
        'sheaths'        => ['all', 'melee', 'ranged'],
        'saiUpdate'      => ['out of combat', 'in combat', 'always'],
        'lootStates'     => ['Not ready', 'Ready', 'Activated', 'Just Deactivated'],
        'weatherStates'  => ['Fine', 'Fog', 'Drizzle', 'Light Rain', 'Medium Rain', 'Heavy Rain', 'Light Snow', 'Medium Snow', 'Heavy Snow', 22 => 'Light Sandstorm', 41=> 'Medium Sandstorm', 42 => 'Heavy Sandstorm', 86 => 'Thunders', 90 => 'Black Rain', 106 => 'Black Snow'],
        'hostilityModes' => ['hostile', 'non-hostile', ''/*any*/],
        'motionTypes'    => ['IdleMotion', 'RandomMotion', 'WaypointMotion', null, 'ConfusedMotion', 'ChaseMotion', 'HomeMotion', 'FlightMotion', 'PointMotion', 'FleeingMotion', 'DistractMotion', 'AssistanceMotion', 'AssistanceDistractMotion', 'TimedFleeingMotion', 'FollowMotion', 'RotateMotion', 'EffectMotion', 'SplineChainMotion', 'FormationMotion'],

        'GOStateUNK'       => '[span class=q10]unknown gameobject state #[b class=q1]%d[/b][/span]',
        'summonTypeUNK'    => '[span class=q10]unknown SummonType #[b class=q1]%d[/b][/span]',
        'aiTplUNK'         => '[span class=q10]unknown AI template #[b class=q1]%d[/b][/span]',
        'reactStateUNK'    => '[span class=q10]unknown ReactState #[b class=q1]%d[/b][/span]',
        'sheathUNK'        => '[span class=q10]unknown sheath #[b class=q1]%d[/b][/span]',
        'saiUpdateUNK'     => '[span class=q10]unknown update condition #[b class=q1]%d[/b][/span]',
        'lootStateUNK'     => '[span class=q10]unknown loot state #[b class=q1]%d[/b][/span]',
        'weatherStateUNK'  => '[span class=q10]unknown weather state #[b class=q1]%d[/b][/span]',
        'powerTypeUNK'     => '[span class=q10]unknown resource #[b class=q1]%d[/b][/span]',
        'hostilityModeUNK' => '[span class=q10]unknown HostilityMode #[b class=q1]%d[/b][/span]',
        'motionTypeUNK'    => '[span class=q10]unknown MotionType #[b class=q1]%d[/b][/span]',
        'entityUNK'        => '[b class=q10]unknown entity[/b]',

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
        'signIn'        => "Log In",
        'user'          => "Username",
        'pass'          => "Password",
        'rememberMe'    => "Stay logged in",
        'forgot'        => "Forgot",
        'forgotUser'    => "Username",
        'forgotPass'    => "Password",
        'accCreate'     => 'Don\'t have an account? <a href="?account=signup">Create one now!</a>',

        // recovery
        'newPass'       => "New Password:",
        'confNewPass'   => "Confirm new password:",
        'passResetHint' => 'If you don\'t know your password, visit the <a href="?account=forgot-password">password reset page</a> to reset it.',
     // 'tokenExpires'  => "This token expires in %s.",     // previously appended to all emails, now it's part of the mail template

        // creation
        'passConfirm'   => "Confirm password:",

        // dashboard
        'ipAddress'     => "IP address: ",
        'lastIP'        => "last used IP: ",
     // 'myAccount'     => "My Account",
     // 'editAccount'   => "Simply use the forms below to update your account information.",
     // 'viewPubDesc'   => 'View your Public Description in your <a href="?user=%s">Profile  Page</a>',

        // bans
        'accBanned'     => "This account was closed",
        'bannedBy'      => "Banned by: ",
        'reason'        => "Reason: ",
        'ends'          => "Ends on: ",
        'permanent'     => "The ban is permanent",
        'noReason'      => "No reason was given.",

        // form-text
        'emailInvalid'  => "That email address is not valid.", // message_emailnotvalid
        'userNotFound'  => "The username you entered does not exists.",
        'wrongPass'     => "That password is not vaild.",
     // 'accInactive'   => "That account has not yet been confirmed active.",
        'errNameLength' => "Your username must be at least 4 characters long.", // message_usernamemin
        'errNameChars'  => "Your username can only contain letters and numbers.", // message_usernamenotvalid
        'errPassLength' => "Your password must be at least 6 characters long.", // message_passwordmin
        'passMismatch'  => "The passwords you entered do not match.",
        'nameInUse'     => "This username is already in use.",
        'mailInUse'     => "That email is already registered to an account.",
        'passCheckFail' => "Passwords do not match.", // message_passwordsdonotmatch
        'newPassDiff'   => "Your new password must be different than your previous one.", // message_newpassdifferent
        'newMailDiff'   => "Your new email address must be different than your previous one.", // message_newemaildifferent

        // premium avatar manager
        'uploadAvatar'  => "Upload new Avatar",
        'goToManager'   => "Go to Avatar Manager",
        'manageAvatars' => "Manage Avatars",
        'avatarSlots'   => 'Using <b>%1$d / %2$d</b> avatar slots',
        'manageBorders' => "Manage Premium Borders",
        'selectAvatar'  => "Please select the avatar to upload.",
        'errTooSmall'   => "Your avatar must be at last %dpx in size.",
        'cropAvatar'    => "You may crop your avatar.",
        'avatarSubmit'  => "Avatar Submission",
        'reminder'      => "Reminder",
        'avatarCoC'     => "Using imagery violating out terms of service may result in revocation of your premium privileges.",

        // settings
        'settings'      => "Account Settings",
        'settingsNote'  => "Simply use the forms below to update your account information.",
        'tabGeneral'    => "General",
        'tabPersonal'   => "Personal",
        'tabCommunity'  => "Community",
        'tabPremium'    => "Premium",
        'preferences'   => "Preferences",
        'modelviewer'   => "Model Viewer",
        'mvNote'        => "Default character model:",
        'lists'         => "Lists",
        'listsNote'     => "Show IDs in supported lists",
        'announcements' => "Announcements",
        'annNote'       => "Removes data related to announcements you have closed so that they may be viewed again.",
        'purge'         => "Purge",
        'curPass'       => "Current password:",
        'globalLogout'  => "Log me out of all other browsers/devices",
        'curEmail'      => "Current email address:",
        'newEmail'      => "New email address:",
        'userPage'      => "User Page",
        'publicDesc'    => "Public Description",
        'publicDescNote'=> 'Tell us more about yourself and your WoW characters. Whatever you type here will appear on your <a href="?user=%s">user page</a>.',
        'forums'        => "Forums",
        'signature'     => "Signature",
        'signatureNote' => "Your signature will appear beneath all of your posts in the forums.",
        'usernameNote'  => "Usernames can only be changed once every %s and must be between 4-16 characters. No special characters are permitted.",
        'curName'       => "Current Username:",
        'newName'       => "New Username:",
        'accDelete'     => "Delete Account",
        'accDeleteNote' => "If you'd like to completely delete your account and all its personal information, visit our <a href=\"?account=delete\" style=\"color:inherit; text-decoration:underline\">account deletion page</a>.",
        'avatar'        => "Avatar",
        'avatarNote'    => "Your avatar will appear next to all of your posts in the forums.",
        'avWowIcon'     => "Icon from World of Warcraft",
        'avWowIconNote' => '<span class="q0">e.g. INV_Axe_54</span><br />Tip: To find the name of an icon, simply double-click the big icon while<br />browsing an <a href="?item=22632" target="_blank">item</a> or <a href="?spell=29516" target="_blank">spell</a> page. Then copy and paste it above.',
        'avIconName'    => "Icon name:",
        'none'          => "None",
        'preview'       => "Preview",
        'custom'        => "Custom",
        'premiumStatus' => "Premium Status",
        'status'        => "Status",
        'active'        => "Active",
        'inactive'      => "Inactive",
        'activeCD'      => "You must wait until %s to change your username again.",
        'updateMessage' => array(
            'general'    => "Updated your preferences.",
            'community'  => "Your public description and forum signature have been updated successfully.",
            'personal'   => "A confirmation email was sent to %s.",
            'username'   => 'Username changed from %1$s to %2$s.',
            'avNotFound' => "Icon not found.",
            'avSuccess'  => "Your avatar has been updated successfully.",
            'avNoChange' => "No changes were made.",
            'av1stUser'  => "Congratulations for picking one that is unique! /cheer",
            'avNthUser'  => "FYI, your icon is also used by %d other user(s)."
        ),
        'inputbox' => array(
            'head' => array(
                'success'     => "Success",
                'error'       => "Oops!",
                'register'    => "Registration - Step %s of 2",
                'recoverUser' => "Username Request",
                'recoverPass' => "Password Reset: Step %s of 2",
                'resendMail'  => "Re-Send Verification Email",
                'signin'      => "Log in to your Account"
            ),
            'message' => array(
                'accActivated'  => 'Your account has been activated.<br />Proceed to <a href="?account=signin&key=%s">sign in</a>',
                'resendMail'    => "If you registered but did not receive a verification email, enter your email address below and submit the form. (Please be sure to check your spam or trash folders to make sure the email didn't accidentally get put in the wrong place!)",
                'mailChangeOk'  => "Your email address has been changed successfully.",
                'mailRevertOk'  => "Your email change request has been cancelled/reverted.",
                'passChangeOk'  => "Your password has been changed successfully.",
                'deleteAccSent' => "An email has been sent to %s with confirmation link attached.",
                'deleteOk'      => "Your account has been successfully removed. We hope to see you again soon!<br /><br /> You may now close this window.",
                'deleteCancel'  => "Account deletion was canceled.",
                'createAccSent' => 'An email was sent to <b>%s</b>. Simply follow the instructions to create your account.<br /><br />If you don\'t receive the verification email, <a href="?account=resend">click here</a> to send another one.</div>',
                'recovUserSent' => "An email was sent to <b>%s</b>. Simply follow the instructions to recover your username.",
                'recovPassSent' => "An email was sent to <b>%s</b>. Simply follow the instructions to reset your password."
            ),
            'error' => array(
                'mailTokenUsed'  => 'Either that email change key has already been used, or it\'s not a valid key. Visit your <a href="?account#personal">Account Settings page</a> to try again.',
                'passTokenUsed'  => 'Either that password change key has already been used, or it\'s not a valid key. Visit your <a href="?account#personal">Account Settings page</a> to try again.',
                'purgeTokenUsed' => 'Either that account delete key has already been used, or it\'s not a valid key. Visit your <a href="?account#personal">Account Settings page</a> to try again.',
                'passTokenLost'  => "No token was provided. If you received a reset password link in an email, please copy and paste the entire URL (including the token at the end) into your browser's location bar.",
                'isRecovering'   => "This account is already recovering. Follow the instructions in your email or wait %s for the token to expire.",
                'loginExceeded'  => "The maximum number of logins from this IP has been exceeded. Please try again in %s.",
                'signupExceeded' => "The maximum number of signups from this IP has been exceeded. Please try again in %s.",
             // 'emailNotFound'  => "The email address you entered is not associated with any account.<br /><br />If you forgot the email you registered your account with email CFG_CONTACT_EMAIL for assistance.",
                'emailNotFound'  => "That email address wasn't found in our system."
            )
        )
    ),
    'user' => array(
        'notFound'      => "User \"%s\" not found!",
        'removed'       => "(Removed)",
        'joinDate'      => "Joined: ",
        'lastLogin'     => "Last visit: ",
        'userGroups'    => "Role: ",
        'consecVisits'  => "Consecutive visits: ",
        'publicDesc'    => "Public Description",
        'profileTitle'  => "%s's Profile",
        'contributions' => "Contributions",
        'uploads'       => "Data uploads: ",
        'comments'      => "Comments: ",
        'screenshots'   => "Screenshots: ",
        'videos'        => "Videos: ",
        'posts'         => "Forum posts: "
    ),
    'emote' => array(
        'id'            => "Emote ID: ",
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
        'id'            => "Enchantment ID: ",
        'notFound'      => "This enchantment doesn't exist.",
        'details'       => "Details",
        'activation'    => "Activation",
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
        'id'            => "Object ID: ",
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
            GO_FLAG_IN_USE           => 'In use',
            GO_FLAG_LOCKED           => 'Locked',
            GO_FLAG_INTERACT_COND    => 'Cannot interact',
            GO_FLAG_TRANSPORT        => 'Transport',
            GO_FLAG_NOT_SELECTABLE   => 'Not selectable',
            GO_FLAG_AI_OBSTACLE      => 'Triggered',
            GO_FLAG_FREEZE_ANIMATION => 'Freeze Animation',
            GO_FLAG_DAMAGED          => 'Siege damaged',
            GO_FLAG_DESTROYED        => 'Siege destroyed'
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
        'id'            => "NPC ID: ",
        'notFound'      => "This NPC doesn't exist.",
        'classification'=> "Classification: %s",
        'petFamily'     => "Pet familiy: ",
        'react'         => "React: %s",
        'worth'         => "Worth: %s",
        'unkPosition'   => "The location of this NPC is unknown.",
        'difficultyPH'  => 'This NPC is a placeholder for a different mode of <a href="?npc=%1$d">%2$s</a>.',
        'seat'          => "Seat",
        'accessory'     => "Accessories",
        'accessoryFor'  => "This NPC is an accessory for vehicle",
        'quotes'        => "Quotes&nbsp;(%d)",
        'gainsDesc'     => "After killing this NPC you will gain: ",
        'repWith'       => "reputation with",
        'stopsAt'       => "stops at %s",
        'vehicle'       => "Vehicle",
        'stats'         => "Stats",
        'melee'         => "Melee: ",
        'ranged'        => "Ranged: ",
        'armor'         => "Armor: ",
        'resistances'   => "Resistances: ",
        'foundIn'       => "This NPC can be found in",
        'tameable'      => "Tameable (%s)",
        'waypoint'      => "Waypoint",
        'wait'          => "Wait",
        'respawnIn'     => "Respawn in: %s",
        'despawnAfter'  => "Spawned by Script<br />Despawn after: %s",
        'rank'          => [0 => "Normal", 1 => "Elite", 4 => "Rare", 2 => "Rare Elite", 3 => "Boss"],
        'textRanges'    => [null, "sent to area", "sent to zone", "sent to map", "sent to world"],
        'textTypes'     => [null, "yells", "says", "whispers"],
        'mechanicimmune'=> 'Not affected by mechanic: %s',
        '_extraFlags'   => 'Extra Flags: ',
        'versions'      => 'Difficulty Versions: ',
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
        ),
        'extraFlags'    => array(
            CREATURE_FLAG_EXTRA_INSTANCE_BIND                   => 'Binds attacker to instance on death',
            CREATURE_FLAG_EXTRA_CIVILIAN                        => "[tooltip name=civilian]- does not aggro\n- death costs Honor[/tooltip][span class=tip tooltip=civilian]Civilian[/span]",
            CREATURE_FLAG_EXTRA_NO_PARRY                        => 'Cannot use [spell=3127]',
            CREATURE_FLAG_EXTRA_NO_PARRY_HASTEN                 => 'Does not gain Parry Haste',
            CREATURE_FLAG_EXTRA_NO_BLOCK                        => 'Cannot use [spell=107]',
            CREATURE_FLAG_EXTRA_NO_CRUSHING_BLOWS               => 'Cannot deal Crushing Blows',
            CREATURE_FLAG_EXTRA_NO_XP                           => 'Rewards no experience',
            CREATURE_FLAG_EXTRA_TRIGGER                         => 'Trigger Creature',
            CREATURE_FLAG_EXTRA_NO_TAUNT                        => 'Immune to Taunt',
         // CREATURE_FLAG_EXTRA_NO_MOVE_FLAGS_UPDATE            => '', // ??
            CREATURE_FLAG_EXTRA_GHOST_VISIBILITY                => '[tooltip name=spirit]Only visible to dead players[/tooltip][span class=tip tooltip=spirit]Spirit[/span]',
            CREATURE_FLAG_EXTRA_USE_OFFHAND_ATTACK              => 'Uses [spell=674]',
            CREATURE_FLAG_EXTRA_NO_SELL_VENDOR                  => 'Vendor does not buy from player',
            CREATURE_FLAG_EXTRA_IGNORE_COMBAT                   => 'Does not enter combat',
            CREATURE_FLAG_EXTRA_WORLDEVENT                      => 'Related to World Event',
            CREATURE_FLAG_EXTRA_GUARD                           => "[tooltip name=guard]- engages PvP attackers\n- ignores enemy stealth, invisibility and Feign Death[/tooltip][span class=tip tooltip=guard]Guard[/span]",
            CREATURE_FLAG_EXTRA_IGNORE_FEIGN_DEATH              => 'Ignores [spell=5384]',
            CREATURE_FLAG_EXTRA_NO_CRIT                         => 'Cannot deal critical hits',
            CREATURE_FLAG_EXTRA_NO_SKILL_GAINS                  => 'Attacker does not gain weapon skill',
            CREATURE_FLAG_EXTRA_OBEYS_TAUNT_DIMINISHING_RETURNS => 'Taunt has diminishing returns',
            CREATURE_FLAG_EXTRA_ALL_DIMINISH                    => 'Is subject to diminishing returns',
            CREATURE_FLAG_EXTRA_NO_PLAYER_DAMAGE_REQ            => 'Attacking players are always eligible for loot',
         // CREATURE_FLAG_EXTRA_DUNGEON_BOSS                    => '', // set during runtime
            CREATURE_FLAG_EXTRA_IGNORE_PATHFINDING              => 'Ignores pathfinding',
            CREATURE_FLAG_EXTRA_IMMUNITY_KNOCKBACK              => 'Immune to knockback'
        )
    ),
    'event' => array(
        'id'            => "World Event ID: ",
        'notFound'      => "This world event doesn't exist.",
        'start'         => "Start: ",
        'end'           => "End: ",
        'interval'      => "Interval: ",
        'inProgress'    => "Event is currently in progress",
        'category'      => ["Uncategorized", "Holidays", "Recurring", "Player vs. Player"]
    ),
    'achievement' => array(
        'id'            => "Achievement ID: ",
        'notFound'      => "This achievement doesn't exist.",
        'criteria'      => "Criteria",
        'points'        => "Points",
        'series'        => "Series",
        'criteriaType'  => "Criterium Type ID:",
        'itemReward'    => "You will receive",
        'titleReward'   => 'You shall be granted the title "<a href="?title=%d">%s</a>"',
        'slain'         => "slain",
        'reqNumCrt'     => 'Requires %1$d out of %2$d',
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
        'id'            => "Class ID: ",
        'notFound'      => "This class doesn't exist."
    ),
    'race' => array(
        'id'            => "Race ID: ",
        'notFound'      => "This race doesn't exist.",
        'racialLeader'  => "Racial leader: ",
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
        'floorN'        => "Level %d"
    ),
    'privileges' => array(
        'main'          => "Here on our Site you can generate <a href=\"?reputation\">reputation</a>. The main way to generate it is to get your comments upvotes.<br /><br />So, reputation is a rough measure of how much you contributed to the community.<br /><br />As you amass reputation you earn the community's trust and you will be granted with additional privileges. You can find a full list below.",
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
        'id'            => "Zone ID: ",
        'notFound'      => "This zone doesn't exist.",
        'attunement'    => ["Attunement: ", "Heroic attunement: "],
        'key'           => ["Key: ", "Heroic key: "],
        'location'      => "Location: ",
        'faction'       => "Faction: ",
        'factions'      => "Factions: ",
        'raidFaction'   => "Raid faction: ",
        'reputationHub' => "Reputation Hub: ",
        'boss'          => "Final boss: ",
        'reqLevels'     => "Required levels: [tooltip=instancereqlevel_tip]%d[/tooltip], [tooltip=lfgreqlevel_tip]%d[/tooltip]",
        'zonePartOf'    => "This zone is part of [zone=%s].",
        'autoRez'       => "Automatic resurrection",
        'city'          => "City",
        'territory'     => "Territory: ",
        'instanceType'  => "Instance type: ",
        'hcAvailable'   => "Heroic mode available&nbsp;(%d)",
        'numPlayers'    => 'Number of players: %1$s',
        'numPlayersVs'  => 'Number of players: %1$dv%1$d',
        'noMap'         => "There is no map available for this zone.",
        'fishingSkill'  => "25 &ndash; 100% chance to catch a listed fish.",
        'instanceTypes' => ["Zone",     "Transit", "Dungeon",   "Raid",      "Battleground", "Dungeon",  "Arena", "Raid", "Raid"],
        'territories'   => ["Alliance", "Horde",   "Contested", "Sanctuary", "PvP",          "World PvP"],
        'cat'           => array(
            "Eastern Kingdoms",         "Kalimdor",                 "Dungeons",                 "Raids",                    "Unused",                   null,
            "Battlegrounds",            null,                       "Outland",                  "Arenas",                   "Northrend"
        )
    ),
    'quest' => array(
        'id'            => "Quest ID: ",
        'notFound'      => "This quest doesn't exist.",
        '_transfer'     => 'This quest will be converted to <a href="?quest=%d" class="q1">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        'questLevel'    => "Level %s",
        'requirements'  => "Requirements",
        'reqMoney'      => "Required money: %s",            // REQUIRED_MONEY
        'money'         => "Money",
        'additionalReq' => "Additional requirements to obtain this quest",
        'reqRepWith'    => 'Your reputation with <a href="?faction=%d">%s</a> must be %s %s',
        'reqRepMin'     => "at least",
        'reqRepMax'     => "lower than",
        'progress'      => "Progress",
        'provided'      => "(Provided)",
        'providedItem'  => "Provided item",
        'completion'    => "Completion",
        'description'   => "Description",
        'playerSlain'   => "Players slain&nbsp;(%d)",
        'profession'    => "Profession: ",
        'timer'         => "Timer: ",
        'loremaster'    => "Loremaster: ",
        'suggestedPl'   => "Suggested players: %d",
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
        'unavailable'   => "This quest was marked obsolete and cannot be obtained or completed.",
        'experience'    => "experience",
        'expConvert'    => "(or %s if completed at level %d)",
        'expConvert2'   => "%s if completed at level %d",
        'rewardChoices' => "You will be able to choose one of these rewards:",             // REWARD_CHOICES
        'rewardItems'   => "You will receive:",                                            // REWARD_ITEMS_ONLY
        'rewardAlso'    => "You will also receive:",                                       // REWARD_ITEMS
        'rewardSpell'   => "You will learn:",                                              // REWARD_SPELL
        'rewardAura'    => "The following spell will be cast on you:",                     // REWARD_AURA
        'rewardTradeSkill'=>"You will learn how to create:",                               // REWARD_TRADESKILL_SPELL
        'rewardTitle'   => 'You shall be granted the title: "<a href="?title=%d">%s</a>"', // REWARD_TITLE
        'bonusTalents'  => "%d talent |4point:points;",                                    // partly LEVEL_UP_CHAR_POINTS
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
        'id'            => "Title ID: ",
        'notFound'      => "This title doesn't exist.",
        '_transfer'     => 'This title will be converted to <a href="?title=%d" class="q1">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        'cat'           => array(
            "General",      "Player vs. Player",    "Reputation",       "Dungeons & Raids",     "Quests",       "Professions",      "World Events"
        )
    ),
    'skill' => array(
        'id'            => "Skill ID: ",
        'notFound'      => "This skill doesn't exist.",
        'cat'           => array(
            -6 => "Companions",         -5 => "Mounts",             -4 => "Racial Traits",      5 => "Attributes",          6 => "Weapon Skills",       7 => "Class Skills",        8 => "Armor Proficiencies",
             9 => "Secondary Skills",   10 => "Languages",          11 => "Professions"
        )
    ),
    'currency' => array(
        'id'            => "Currency ID: ",
        'notFound'      => "This currency doesn't exist.",
        'cap'           => "Total cap: ",
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
        'id'            => "Mail ID: ",
        'notFound'      => "This mail doesn't exist.",
        'attachment'    => "Attachment",
        'mailDelivery'  => 'You will receive <a href="?mail=%d">this letter</a>%s%s',
        'mailBy'        => ' by <a href="?npc=%d">%s</a>',
        'mailIn'        => " after %s",
        'delay'         => "Delay: %s",
        'sender'        => "Sender: %s",
        'untitled'      => "Untitled Mail #%d"
    ),
    'pet'      => array(
        'id'            => "Pet family ID: ",
        'notFound'      => "This pet family doesn't exist.",
        'exotic'        => "Exotic",
        'cat'           => ["Ferocity", "Tenacity", "Cunning"],
        'food'          => ["Meat", "Fish", "Cheese", "Bread", "Fungus", "Fruit", "Raw Meat", "Raw Fish"] // ItemPetFood.dbc
    ),
    'faction' => array(
        'id'            => "Faction ID: ",
        'notFound'      => "This faction doesn't exist.",
        'spillover'     => "Reputation Spillover",
        'spilloverDesc' => "Gaining reputation with this faction also yields a proportional gain with the factions listed below.",
        'maxStanding'   => "Max. Standing",
        'quartermaster' => "Quartermaster: ",
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
        'id'            => "Item Set ID: ",
        'notFound'      => "This item set doesn't exist.",
        '_desc'         => "<b>%s</b> is the <b>%s</b>. It contains %s pieces.",
        '_descTagless'  => "<b>%s</b> is an item set that contains %s pieces.",
        '_setBonuses'   => "Set Bonuses",
        '_conveyBonus'  => "Wearing more pieces of this set will convey bonuses to your character.",
        '_pieces'       => "%d pieces: ",
        '_unavailable'  => "This item set is not available to players.",
        '_tag'          => "Tag: ",
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
        'id'            => "Spell ID: ",
        'notFound'      => "This spell doesn't exist.",
        '_spellDetails' => "Spell Details",
        '_cost'         => "Cost",
        '_range'        => "Range",
        '_castTime'     => "Cast time",
        '_cooldown'     => "Cooldown",
        '_distUnit'     => " yards",
        '_forms'        => "Forms",
        '_aura'         => "Aura",
        '_effect'       => "Effect",
        '_none'         => "None",
        '_gcd'          => "GCD",
        '_globCD'       => "Global Cooldown",
        '_gcdCategory'  => "GCD category",
        '_value'        => "Value",
        '_radius'       => "Radius: ",
        '_interval'     => "Interval: ",
        '_inSlot'       => "in slot: ",
        '_collapseAll'  => "Collapse All",
        '_expandAll'    => "Expand All",
        '_transfer'     => 'This spell will be converted to <a href="?spell=%d" class="q%d icontiny tinyspecial" style="background-image: url(STATIC_URL/images/wow/icons/tiny/%s.gif)">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        '_affected'     => "Affected Spells: ",
        '_seeMore'      => "See more",
        '_rankRange'    => "Rank:&nbsp;%d&nbsp;-&nbsp;%d",
        '_showXmore'    => "Show %d More",

        'normal'        => "Normal",
        'special'       => "Special",

        'currentArea'   => '&lt;current area&gt;',
        'discovered'    => "Learned via discovery",
        'ppm'           => "(%.1f procs per minute)",
        'procChance'    => "Proc chance: %.4g%%",
        'starter'       => "Starter spell",
        'trainingCost'  => "Training cost: ",
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
        'pointsPerCP'   => ", plus %s per combo point",
        'stackGroup'    => "Stack Group",
        'linkedWith'    => "Linked with",
        'apMod'         => " (AP mod: %.3g)",
        'spMod'         => " (SP mod: %.3g)",
        'instantPhys'   => "Instant",                       // SPELL_CAST_TIME_INSTANT_NO_MANA
        'castTime' => array(
            "Instant cast",                                 // SPELL_CAST_TIME_INSTANT
            "%.3g sec cast",                                // SPELL_CAST_TIME_SEC
            "%.3g min cast"                                 // SPELL_CAST_TIME_MIN
        ),
        'cooldown' => array(
            "Instant cooldown",                             // SPELL_RECAST_TIME_INSTANT not used?
            "%.3g sec cooldown",                            // SPELL_RECAST_TIME_SEC
            "%.3g min cooldown",                            // SPELL_RECAST_TIME_MIN
            "%.3g hour cooldown",                           // SPELL_RECAST_TIME_HOURS - not in 3.3.5 but we display cooldowns the client hides anyways
            "%.3g day cooldown"                             // SPELL_RECAST_TIME_DAYS - not in 3.3.5 but we display cooldowns the client hides anyways
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
            // conventional - HEALTH, MANA, RAGE, FOCUS, ENERGY, HAPPINESS, RUNES, RUNIC_POWER
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
        'weaponSubClass' => array(                          // ItemSubClass.dbc/4; ordered by content first, then alphabeticaly
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
        'combatRatingMask' => array(
            0xE0 => "Hit Chance",                   0x700 => "Critical Hit Chance",         0x1C000 => "Resilience"
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
        'summonControl' => ["Uncontrolled", "Guardian", "Pet", "Possessed", "Possessed Vehicle", "Uncontrolled Vehicle"],
        'summonSlot'    => ["Pet", "Fire Totem", "Earth Totem", "Water Totem", "Air Totem", "Non-combat Pet", "Quest"],
        'unkEffect'     => 'Unknown Effect (%1$d)',
        'effects'       => array(
/*0-5    */ "None",                     "Instakill",                "School Damage",            "Dummy",                    "Portal Teleport",          "Teleport Units",
/*6+     */ "Apply Aura",               "Environmental Damage",     "Drain Power",              "Drain Health",             "Heal",                     "Bind",
/*12+    */ "Portal",                   "Ritual Base",              "Ritual Specialize",        "Ritual Activate Portal",   "Complete Quest",           "Weapon Damage - No School",
/*18+    */ "Resurrect with % Health",  "Add Extra Attacks",        "Can Dodge",                "Can Evade",                "Can Parry",                "Can Block",
/*24+    */ "Create Item",              "Can Use Weapon",           "Know Defense Skill",       "Persistent Area Aura",     "Summon",                   "Leap",
/*30+    */ "Give Power",               "Weapon Damage - %",        "Trigger Missile",          "Open Lock",                "Transform Item",           "Apply Area Aura - Party",
/*36+    */ "Learn Spell",              "Know Spell Defense",       "Dispel",                   "Learn Language",           "Dual Wield",               "Jump to Target",
/*42+    */ "Jump Behind Target",       "Teleport Target to Caster","Learn Skill Step",         "Give Honor",               "Spawn",                    "Trade Skill",
/*48+    */ "Stealth",                  "Detect Stealthed",         "Summon Object",            "Force Critical Hit",       "Guarantee Hit",            "Enchant Item Permanent",
/*54+    */ "Enchant Item Temporary",   "Tame Creature",            "Summon Pet",               "Learn Spell - Pet",        "Weapon Damage - Flat",     "Open Item & Fast Loot",
/*60+    */ "Proficiency",              "Send Script Event",        "Burn Power",               "Modify Threat - Flat",     "Trigger Spell",            "Apply Area Aura - Raid",
/*66+    */ "Create Mana Gem",          "Heal to Full",             "Interrupt Cast",           "Distract",                 "Distract Move",            "Pickpocket",
/*72+    */ "Far Sight",                "Forget Talents",           "Apply Glyph",              "Heal Mechanical",          "Summon Object - Temporary","Script Effect",
/*78+    */ "Attack",                   "Abort All Pending Attacks","Add Combo Points",         "Create House",             "Bind Sight",               "Duel",
/*84+    */ "Stuck",                    "Summon Player",            "Activate Object",          "Siege Damage",             "Repair Building",          "Siege Building Action",
/*90+    */ "Kill Credit",              "Threat All",               "Enchant Held Item",        "Force Deselect",           "Self Resurrect",           "Skinning",
/*96+    */ "Charge",                   "Cast Button",              "Knock Back",               "Disenchant",               "Inebriate",                "Feed Pet",
/*102+   */ "Dismiss Pet",              "Give Reputation",          "Summon Object (Trap)",     "Summon Object (Battle S.)","Summon Object (#3)",       "Summon Object (#4)",
/*108+   */ "Dispel Mechanic",          "Summon Dead Pet",          "Destroy All Totems",       "Durability Damage - Flat", "Summon Demon",             "Resurrect with Flat Health",
/*114+   */ "Taunt",                    "Durability Damage - %",    "Skin Player Corpse (PvP)", "AoE Resurrect with % Health","Learn Skill",            "Apply Area Aura - Pet",
/*120+   */ "Teleport to Graveyard",    "Normalized Weapon Damage", "",                         "Take Flight Path",         "Pull Towards",             "Modify Threat - %",
/*126+   */ "Spell Steal ",             "Prospect",                 "Apply Area Aura - Friend", "Apply Area Aura - Enemy",  "Redirect Done Threat %",   "Play Sound",
/*132+   */ "Play Music",               "Unlearn Specialization",   "Kill Credit 2",            "Call Pet",                 "Heal for % of Total Health","Give % of Total Power",
/*138+   */ "Leap Back",                "Abandon Quest",            "Force Cast",               "Force Spell Cast with Value","Trigger Spell with Value","Apply Area Aura - Pet Owner",
/*144+   */ "Knockback to Dest.",       "Pull Towards Dest.",       "Activate Rune",            "Fail Quest",               "Trigger Missile with Value","Charge to Dest",
/*150+   */ "Start Quest",              "Trigger Spell 2",          "Summon - Refer-A-Friend",  "Create Tamed Pet",         "Discover Flight Path",     "Dual Wield 2H Weapons",
/*156+   */ "Add Socket to Item",       "Create Tradeskill Item",   "Milling",                  "Rename Pet",               "Force Cast 2",             "Change Talent Spec. Count",
/*162-167*/ "Activate Talent Spec.",    "",                         "Remove Aura"
        ),
        'unkAura'       => 'Unknown Aura (%1$d)',
        'auras'         => array(
/*0-   */   "None",                                 "Bind Sight",                           "Possess",                              "Periodic Damage - Flat",               "Dummy",
/*5+   */   "Confuse",                              "Charm",                                "Fear",                                 "Periodic Heal",                        "Mod Attack Speed",
            "Mod Threat",                           "Taunt",                                "Stun",                                 "Mod Damage Done - Flat",               "Mod Damage Taken - Flat",
            "Damage Shield",                        "Stealth",                              "Mod Stealth Detection Level",          "Invisibility",                         "Mod Invisibility Detection Level",
            "Regenerate Health - %",                "Regenerate Power - %",                 "Mod Resistance - Flat",                "Periodically Trigger Spell",           "Periodically Give Power",
/*25+  */   "Pacify",                               "Root",                                 "Silence",                              "Reflect Spells",                       "Mod Stat - Flat",
            "Mod Skill - Temporary",                "Increase Run Speed %",                 "Mod Mounted Speed %",                  "Decrease Run Speed %",                 "Mod Maximum Health - Flat",
            "Mod Maximum Power - Flat",             "Shapeshift",                           "Spell Effect Immunity",                "Spell Aura Immunity",                  "Spell School Immunity",
            "Damage Immunity",                      "Dispel Type Immunity",                 "Proc Trigger Spell",                   "Proc Trigger Damage",                  "Track Creatures",
            "Track Resources",                      "Ignore All Gear",                      "Mod Parry %",                          "Periodic Trigger Spell from Client",   "Mod Dodge %",
/*50+  */   "Mod Critical Healing Amount %",        "Mod Block %",                          "Mod Physical Crit Chance",             "Periodically Drain Health",            "Mod Physical Hit Chance",
            "Mod Spell Hit Chance",                 "Transform",                            "Mod Spell Crit Chance",                "Increase Swim Speed %",                "Mod Damage Done Versus Creature",
            "Pacify & Silence",                     "Mod Size %",                           "Periodically Transfer Health",         "Periodic Transfer Power",              "Periodic Drain Power",
            "Mod Spell Haste % (not stacking)",     "Feign Death",                          "Disarm",                               "Stalked",                              "Mod Absorb School Damage",
            "Extra Attacks",                        "Mod Spell School Crit Chance",         "Mod Spell School Power Cost - %",      "Mod Spell School Power Cost - Flat",   "Reflect Spells School From School",
/*75+  */   "Force Language",                       "Far Sight",                            "Mechanic Immunity",                    "Mounted",                              "Mod Damage Done - %",
            "Mod Stat - %",                         "Split Damage - %",                     "Underwater Breathing",                 "Mod Base Resistance - Flat",           "Mod Health Regeneration - Flat",
            "Mod Power Regeneration - Flat",        "Create Item on Death",                 "Mod Damage Taken - %",                 "Mod Health Regeneration - %",          "Periodic Damage - %",
            "Mod Resist Chance",                    "Mod Aggro Range",                      "Prevent Fleeing",                      "Unattackable",                         "Interrupt Power Decay",
            "Ghost",                                "Spell Magnet",                         "Absorb Damage - Mana Shield",          "Mod Skill Value",                      "Mod Attack Power - Flat",
/*100+ */   "Always Show Debuffs",                  "Mod Resistance - %",                   "Mod Melee Attack Power vs Creature",   "Mod Total Threat - Temporary",         "Water Walking",
            "Feather Fall",                         "Levitate / Hover",                     "Add Modifier - Flat",                  "Add Modifier - %",                     "Proc Spell on Target",
            "Mod Power Regeneration - %",           "Intercept % of Attacks Against Target","Override Class Script",                "Mod Ranged Damage Taken - Flat",       "Mod Ranged Damage Taken - %",
            "Mod Healing Taken - Flat",             "Allow % of Health Regen During Combat","Mod Mechanic Resistance",              "Mod Healing Taken - %",                "Share Pet Tracking",
            "Untrackable",                          "Beast Lore",                           "Mod Offhand Damage Done %",            "Mod Target Resistance - Flat",         "Mod Ranged Attack Power - Flat",
/*125+ */   "Mod Melee Damage Taken - Flat",        "Mod Melee Damage Taken - %",           "Mod Attacker Ranged Attack Power",     "Possess Pet",                          "Increase Run Speed % - Stacking",
            "Incerase Mounted Speed % - Stacking",  "Mod Ranged Attack Power vs Creature",  "Mod Maximum Power - %",                "Mod Maximum Health - %",               "Allow % of Mana Regen During Combat",
            "Mod Healing Done - Flat",              "Mod Healing Done - %",                 "Mod Stat - %",                         "Mod Melee Haste %",                    "Force Reputation",
            "Mod Ranged Haste %",                   "Mod Ranged Ammo Haste %",              "Mod Base Resistance - %",              "Mod Resistance - Flat (not stacking)", "Safe Fall",
            "Increase Pet Talent Points",           "Allow Exotic Pets Taming",             "Mechanic Immunity Mask",               "Retain Combo Points",                  "Reduce Pushback Time %",
/*150+ */   "Mod Shield Block Value - %",           "Track Stealthed",                      "Mod Player Aggro Range",               "Split Damage - Flat",                  "Mod Stealth Level",
            "Mod Underwater Breathing %",           "Mod All Reputation Gained by %",       "Done Pet Damage Multiplier",           "Mod Shield Block Value - Flat",        "No PvP Credit",
            "Mod AoE Avoidance",                    "Mod Health Regen During Combat",       "Mana Burn",                            "Mod Melee Critical Damage %",          "",
            "Mod Attacker Melee Attack Power",      "Mod Melee Attack Power - %",           "Mod Ranged Attack Power - %",          "Mod Damage Done vs Creature",          "Mod Crit Chance vs Creature",
            "Change Object Visibility for Player",  "Mod Run Speed (not stacking)",         "Mod Mounted Speed (not stacking)",     "",                                     "Mod Spell Power by % of Stat",
/*175+ */   "Mod Healing Power by % of Stat",       "Spirit of Redemption",                 "AoE Charm",                            "Mod Debuff Resistance - %",            "Mod Attacker Spell Crit Chance",
            "Mod Spell Power vs Creature",          "",                                     "Mod Resistance by % of Stat",          "Mod Threat % of Critical Hits",        "Mod Attacker Melee Hit Chance",
            "Mod Attacker Ranged Hit Chance",       "Mod Attacker Spell Hit Chance",        "Mod Attacker Melee Crit Chance",       "Mod Attacker Ranged Crit Chance",      "Mod Rating",
            "Mod Reputation Gained %",              "Limit Movement Speed",                 "Mod Attack Speed %",                   "Mod Haste % (gain)",                   "Mod Target School Absorb %",
            "Mod Target School Absorb for Ability", "Mod Cooldowns",                        "Mod Attacker Crit Chance",             "",                                     "Mod Spell Hit Chance",
/*200+ */   "Mod Kill Experience Gained %",         "Can Fly",                              "Ignore Combat Result",                 "Mod Attacker Melee Crit Damage %",     "Mod Attacker Ranged Crit Damage %",
            "Mod Attacker Spell Crit Damage %",     "Mod Vehicle Flight Speed %",           "Mod Mounted Flight Speed %",           "Mod Flight Speed %",                   "Mod Mounted Flight Speed % (always)",
            "Mod Vehicle Speed % (always)",         "Mod Flight Speed % (not stacking)",    "Mod Ranged Attack Power by % of Stat", "Mod Rage Generated from Damage Dealt", "Tamed Pet Passive",
            "Arena Preparation",                    "Mod Spell Haste %",                    "Killing Spree",                        "Mod Ranged Haste %",                   "Mod Mana Regeneration by % of Stat",
            "Mod Combat Rating by % of Stat",       "Ignore Threat",                        "",                                     "Raid Proc from Charge",                "",
/*225+ */   "Raid Proc from Charge with Value",     "Periodic Dummy",                       "Periodically Trigger Spell with Value","Detect Stealth",                       "Mod AoE Damage Taken %",
            "Mod Maximum Health - Flat (no stacking)","Proc Trigger Spell with Value",      "Mod Mechanic Duration %",              "Change other Humanoid Display",        "Mod Mechanic Duration % (not stacking)",
            "Mod Dispel Resistance %",              "Control Vehicle",                      "Mod Spell Power by % of Attack Power", "Mod Healing Power by % of Attack Power","Mod Size % (not stacking)",
            "Mod Expertise",                        "Force Move Forward",                   "Mod Spell & Healing Power by % of Int","Faction Override",                     "Comprehend Language",
            "Mod Aura Duration by Dispel Type",   "Mod Aura Duration by Dispel Type (not stacking)", "Clone Caster",                "Mod Combat Result Chance",             "Convert Rune",
/*250+ */   "Mod Maximum Health - Flat (stacking)", "Mod Enemy Dodge Chance",               "Mod Haste % (loss)",                   "Mod Critical Block Chance",            "Disarm Offhand",
            "Mod Mechanic Damage Taken %",          "No Reagent Cost",                      "Mod Target Resistance by Spell Class", "Mod Spell Visual",                     "Mod Periodic Healing Taken %",
            "Screen Effect",                        "Phase",                                "Ability Ignore Aurastate",             "Allow Only Ability",                   "",
            "",                                     "",                                     "Cancel Aura Buffer at % of Caster Health","Mod Attack Power by % of Stat",     "Ignore Target Resistance",
            "Ignore Target Resistance for Ability", "Mod Damage Taken % from Caster",       "Ignore Swing Timer Reset",             "X-Ray",                                "Ability Consume No Ammo",
/*275+ */   "Mod Ability Ignore Shapeshift",        "Mod Mechanic Damage Done %",           "Mod Max Affected Targets",             "Disarm Ranged Weapon",                 "Spawn Effect",
            "Mod Armor Penetration %",              "Mod Honor Gain %",                     "Mod Base Health %",                    "Mod Healing Taken % from Caster",      "Linked Aura",
            "Mod Attack Power by School Resistance","Allow Periodic Ability to Crit",       "Mod Spell Deflect Chance",             "Ignore Hit Direction",                 "",
            "Mod Crit Chance",                      "Mod Quest Experience Gained %",        "Open Stable",                          "Override Spells",                      "Prevent Power Regeneration",
            "",                                     "Set Vehicle Id",                       "Spirit Burst",                         "Strangulate",                          "",
/*300+ */   "Share Damage %",                       "Mod Absorb School Healing",            "",                                     "Mod Damage Done vs Aurastate - %",     "Fake Inebriate",
            "Mod Minimum Speed %",                  "",                                     "Heal Absorb Test",                     "Mod Critical Strike Chance for Caster","",
            "Mod Pet AoE Damage Avoidance",         "",                                     "",                                     "",                                     "Prevent Ressurection",
/* -316*/   "Underwater Walking",                   "Periodic Haste"
        ),
        'attributes0' => array(
            SPELL_ATTR0_PROC_FAILURE_BURNS_CHARGE     => 'Proc Failure Burns Charge', // 1120
            SPELL_ATTR0_REQ_AMMO                      => 'Requires a ranged weapon', // 27632
            SPELL_ATTR0_ON_NEXT_SWING                 => 'On next swing (players)', // 6807
            SPELL_ATTR0_IS_REPLENISHMENT              => 'Do Not Log Immune Misses', // only 57669 Replenishment (tested with 57669, 5405 against aura 62692)
            SPELL_ATTR0_ABILITY                       => 'Is Ability',  // 27576
            SPELL_ATTR0_TRADESPELL                    => 'Tradeskill recipe', // 2479
            SPELL_ATTR0_PASSIVE                       => 'Passive spell', // 12296
            SPELL_ATTR0_HIDDEN_CLIENTSIDE             => 'Aura is hidden', // 12296
            SPELL_ATTR0_HIDE_IN_COMBAT_LOG            => 'Does not appear in log', // 45471 - "cast time is hidden" is demonstrably false
            SPELL_ATTR0_TARGET_MAINHAND_ITEM          => 'Held Item Only', // 37360
            SPELL_ATTR0_ON_NEXT_SWING_2               => 'On next swing (npcs)', // 6807
            SPELL_ATTR0_WEARER_CASTS_PROC_TRIGGER     => 'Wearer Casts Proc Trigger', // 47193
            SPELL_ATTR0_DAYTIME_ONLY                  => 'Can only be used during daytime', // < unused >
            SPELL_ATTR0_NIGHT_ONLY                    => 'Can only be used during nighttime', // < unused >
            SPELL_ATTR0_INDOORS_ONLY                  => 'Can only be used indoors', // < unused >
            SPELL_ATTR0_OUTDOORS_ONLY                 => 'Can only be used outdoors', // 55293
            SPELL_ATTR0_NOT_SHAPESHIFT                => 'Cannot be used while shapeshifted',  // 27576
            SPELL_ATTR0_ONLY_STEALTHED                => 'Must be in stealth', // 8724
            SPELL_ATTR0_DONT_AFFECT_SHEATH_STATE      => 'Do Not Sheath',  // 27576
            SPELL_ATTR0_LEVEL_DAMAGE_CALCULATION      => 'Spell damage depends on caster level', // 13901
            SPELL_ATTR0_STOP_ATTACK_TARGET            => 'Stops auto-attack', // 55293
            SPELL_ATTR0_IMPOSSIBLE_DODGE_PARRY_BLOCK  => 'Cannot be dodged, parried or blocked', // 57755
            SPELL_ATTR0_CAST_TRACK_TARGET             => 'Track Target in Cast (Player Only)', // 27632
            SPELL_ATTR0_CASTABLE_WHILE_DEAD           => 'Can be used while dead', // 27285
            SPELL_ATTR0_CASTABLE_WHILE_MOUNTED        => 'Can be used while mounted', // 2457
            SPELL_ATTR0_DISABLED_WHILE_ACTIVE         => 'Starts cooldown after aura fades', // 53756
            SPELL_ATTR0_NEGATIVE_1                    => 'Aura is Debuff', // 31117
            SPELL_ATTR0_CASTABLE_WHILE_SITTING        => 'Can be used while sitting', // 2457
            SPELL_ATTR0_CANT_USED_IN_COMBAT           => 'Cannot be used in combat', // 100
            SPELL_ATTR0_UNAFFECTED_BY_INVULNERABILITY => 'Unaffected by invulnerability', // 2457
            SPELL_ATTR0_HEARTBEAT_RESIST_CHECK        => 'Heartbeat Resist', // 5782
            SPELL_ATTR0_CANT_CANCEL                   => 'Aura cannot be cancelled' // 48018
        ),
        'attributes1' => array(
            SPELL_ATTR1_DISMISS_PET                     => 'Dismiss Pet First', // 1098
            SPELL_ATTR1_DRAIN_ALL_POWER                 => 'Uses all Power', // 17233
            SPELL_ATTR1_CHANNELED_1                     => 'Channeled 1', // 689 genFilter 66
            SPELL_ATTR1_CANT_BE_REDIRECTED              => 'Cannot be redirected', // 5246 - [WH] Cannot be reflected
            SPELL_ATTR1_NO_SKILL_INCREASE               => 'No Skill Increase', // 46924
            SPELL_ATTR1_NOT_BREAK_STEALTH               => 'Does not break stealth', // 5500
            SPELL_ATTR1_CHANNELED_2                     => 'Channeled 2', // 1949
            SPELL_ATTR1_CANT_BE_REFLECTED               => 'Cannot be reflected', // 5246 - [WH] "All spell effects are harmful" and cr: 69
            SPELL_ATTR1_CANT_TARGET_IN_COMBAT           => 'The target cannot be in combat', // 38605
            SPELL_ATTR1_MELEE_COMBAT_START              => 'Initiates Combat (Enabled Auto-Attack)', // 1329
            SPELL_ATTR1_NO_THREAT                       => 'Generates no threat', // 2457
            SPELL_ATTR1_DONT_REFRESH_DURATION_ON_RECAST => 'Aura Unique', // 34697
            SPELL_ATTR1_IS_PICKPOCKET                   => 'Pickpocket spell', // 921
            SPELL_ATTR1_FARSIGHT                        => 'Toggle Far Sight', // 126
            SPELL_ATTR1_CHANNEL_TRACK_TARGET            => 'Track Target in Channel', // 689
            SPELL_ATTR1_DISPEL_AURAS_ON_IMMUNITY        => 'Remove auras on immunity', // 18499
            SPELL_ATTR1_UNAFFECTED_BY_SCHOOL_IMMUNE     => 'Unaffected by school immunity', // 12292
            SPELL_ATTR1_UNAUTOCASTABLE_BY_PET           => 'No AutoCast (AI)', // 12975,
            SPELL_ATTR1_PREVENTS_ANIM                   => 'Prevents Anim', // 22570
            SPELL_ATTR1_CANT_TARGET_SELF                => 'Exclude Caster', // 50720
            SPELL_ATTR1_FINISHING_MOVE_DAMAGE           => 'Requires combo points on target (Damage)', // 22570
            SPELL_ATTR1_THREAT_ONLY_ON_MISS             => 'Threat only on Miss', // 921
            SPELL_ATTR1_FINISHING_MOVE_DURATION         => 'Requires combo points on target (Duration)', // 22570
            SPELL_ATTR1_IGNORE_OWNERS_DEATH             => 'Ignore Owner\'s Death', // 45145
            SPELL_ATTR1_IS_FISHING                      => 'Requires fishing pole', // 62734
            SPELL_ATTR1_AURA_STAYS_AFTER_COMBAT         => 'Aura Stays After Combat', // 61112
            SPELL_ATTR1_REQUIRE_ALL_TARGETS             => 'Require All Targets', // 1120
            SPELL_ATTR1_DISCOUNT_POWER_ON_MISS          => 'Discount Power On Miss', // 1329
            SPELL_ATTR1_DONT_DISPLAY_IN_AURA_BAR        => 'No Aura Icon', // 2457
            SPELL_ATTR1_CHANNEL_DISPLAY_SPELL_NAME      => 'Name in Channel Bar', // 62734
            SPELL_ATTR1_ENABLE_AT_DODGE                 => 'Combo on Dodge', // 7384 - Combo on Block (Mainline: Dispel All Stacks)
            SPELL_ATTR1_CAST_WHEN_LEARNED               => 'Cast When Learned' // 2457
        ),
        'attributes2' => array(
            SPELL_ATTR2_CAN_TARGET_DEAD                               => 'Allow Dead Target', // 2457
            SPELL_ATTR2_NO_SHAPESHIFT_UI                              => 'No shapeshift UI', // 9736
            SPELL_ATTR2_CAN_TARGET_NOT_IN_LOS                         => 'Ignore Line of Sight', // 20647
            SPELL_ATTR2_ALLOW_LOW_LEVEL_BUFF                          => 'Allow Low Level Buff', // 755
            SPELL_ATTR2_DISPLAY_IN_STANCE_BAR                         => 'Use Shapeshift Bar', // 465
            SPELL_ATTR2_AUTOREPEAT_FLAG                               => 'Auto Repeat', // 75
            SPELL_ATTR2_CANT_TARGET_TAPPED                            => 'Requires untapped target', // 710
            SPELL_ATTR2_DO_NOT_REPORT_SPELL_FAILURE                   => 'Do Not Report Spell Failure', // 26654
            SPELL_ATTR2_INCLUDE_IN_ADVANCED_COMBAT_LOG                => '', // < unused > - 'Include in Advanced Combat Log' for modern client
            SPELL_ATTR2_ALWAYS_CAST_AS_UNIT                           => 'Always Cast As Unit', // 42454
            SPELL_ATTR2_SPECIAL_TAMING_FLAG                           => 'Special Taming Flag', // 1515
            SPELL_ATTR2_HEALTH_FUNNEL                                 => 'Health Funnel', // 'No Target Per-Second Costs' makes no sense in 335
            SPELL_ATTR2_CHAIN_FROM_CASTER                             => 'Chain From Caster', // 6807
            SPELL_ATTR2_PRESERVE_ENCHANT_IN_ARENA                     => 'Target must be own item', // 24168  (modern client naming seems to be more correct (close enough in either case))
            SPELL_ATTR2_ALLOW_WHILE_INVISIBLE                         => 'Allow While Invisible', // 2479
            SPELL_ATTR2_DO_NOT_CONSUME_IF_GAINED_DURING_CAST          => 'Do Not Consume if Gained During Cast', // < unused/157228 >
            SPELL_ATTR2_TAME_BEAST                                    => 'No Active Pets', // 1515
            SPELL_ATTR2_NOT_RESET_AUTO_ACTIONS                        => 'Do Not Reset Combat Timers', // 1464
            SPELL_ATTR2_REQ_DEAD_PET                                  => 'Requires Dead Pet', // 982 - No Jump While Cast Pending
            SPELL_ATTR2_NOT_NEED_SHAPESHIFT                           => 'Does not require shapeshift', // 5176
            SPELL_ATTR2_INITIATE_COMBAT_POST_CAST_ENABLES_AUTO_ATTACK => 'Initiate Combat Post-Cast (Enables Auto-Attack)', // 1329
            SPELL_ATTR2_FAIL_ON_ALL_TARGETS_IMMUNE                    => 'Fail on all targets immune', // 642
            SPELL_ATTR2_NO_INITIAL_THREAT                             => 'No initial Threat', // 26654
            SPELL_ATTR2_IS_ARCANE_CONCENTRATION                       => 'Proc Cooldown On Failure', // 12574
            SPELL_ATTR2_ITEM_CAST_WITH_OWNER_SKILL                    => 'Item Cast With Owner Skill', // 11353
            SPELL_ATTR2_DONT_BLOCK_MANA_REGEN                         => 'Don\'t Block Mana Regen',// 18220
            SPELL_ATTR2_UNAFFECTED_BY_AURA_SCHOOL_IMMUNE              => 'No School immunities', // 1161
            SPELL_ATTR2_IGNORE_WEAPONSKILL                            => 'Ignore Weaponskill', // 62734
            SPELL_ATTR2_NOT_AN_ACTION                                 => 'Not an Action', // 33280
            SPELL_ATTR2_CANT_CRIT                                     => 'Cannot crit', // 26654
            SPELL_ATTR2_ACTIVE_THREAT                                 => 'Active Threat', // 5857
            SPELL_ATTR2_FOOD_BUFF                                     => 'Food/Drink buff' // 44101
        ),
        'attributes3' => array(
            SPELL_ATTR3_PVP_ENABLING                          => 'PvP Enabling', // 27285
            SPELL_ATTR3_IGNORE_PROC_SUBCLASS_MASK             => 'No Proc Equip Requirement', // 2565
            SPELL_ATTR3_NO_CASTING_BAR_TEXT                   => 'No Casting Bar Text', // 47542
            SPELL_ATTR3_COMPLETELY_BLOCKED                    => 'Completely Blocked', // 1715
            SPELL_ATTR3_IGNORE_RESURRECTION_TIMER             => 'No Res Timer', // 20742
            SPELL_ATTR3_NO_DURABILTIY_LOSS                    => 'No Durability Loss', // 66588
            SPELL_ATTR3_NO_AVOIDANCE                          => 'No Avoidance', // 52090
            SPELL_ATTR3_STACK_FOR_DIFF_CASTERS                => 'DoT Stacking Rule', // 980
            SPELL_ATTR3_ONLY_TARGET_PLAYERS                   => 'Can only target players', // 26561
            SPELL_ATTR3_NOT_A_PROC                            => 'Not a Proc',  // 27576
            SPELL_ATTR3_MAIN_HAND                             => 'Requires main hand weapon', // 27576
            SPELL_ATTR3_BATTLEGROUND                          => 'Can only be used in a battleground', // 23035
            SPELL_ATTR3_ONLY_TARGET_GHOSTS                    => 'Only On Ghosts', // 22012
            SPELL_ATTR3_DONT_DISPLAY_CHANNEL_BAR              => 'Hide Channel Bar', // 24323
            SPELL_ATTR3_IS_HONORLESS_TARGET                   => 'Is Honorless Target', // renamed in modern client
            SPELL_ATTR3_NORMAL_RANGED_ATTACK                  => 'Normal Ranged Attack', // 75
            SPELL_ATTR3_CANT_TRIGGER_PROC                     => 'Suppress Caster Procs',  // 1329
            SPELL_ATTR3_NO_INITIAL_AGGRO                      => 'Does not engage target', // 1464
            SPELL_ATTR3_IGNORE_HIT_RESULT                     => 'Cannot miss', // 64380
            SPELL_ATTR3_DISABLE_PROC                          => 'Disable Proc', // 'Instant Target Procs', // 47261
            SPELL_ATTR3_DEATH_PERSISTENT                      => 'Persists through death', // 2457
            SPELL_ATTR3_ONLY_PROC_OUTDOORS                    => 'Only Proc Outdoors', // < unused/116684 >
            SPELL_ATTR3_REQ_WAND                              => 'Requires a wand', // < unused > - reqwand
            SPELL_ATTR3_NO_DAMAGE_HISTORY                     => 'No Damage History', // 30839
            SPELL_ATTR3_REQ_OFFHAND                           => 'Requires an off-hand weapon', // 27576
            SPELL_ATTR3_TREAT_AS_PERIODIC                     => 'Treat As Periodic', // 5857
            SPELL_ATTR3_CAN_PROC_FROM_PROCS                   => 'Can Proc From Procs', // 1719
            SPELL_ATTR3_DRAIN_SOUL                            => 'Only Proc on Caster', // 1120
            SPELL_ATTR3_IGNORE_CASTER_AND_TARGET_RESTRICTIONS => 'Ignore Caster and Target restrictions', // changed attribute
            SPELL_ATTR3_NO_DONE_BONUS                         => 'Ignore Caster Modifiers', // 12723
            SPELL_ATTR3_DONT_DISPLAY_RANGE                    => 'Do Not Display Range', // 20647
            SPELL_ATTR3_NOT_ON_AOE_IMMUNE                     => 'Not on AOE Immune' // 71718
        ),
        'attributes4' => array(
            SPELL_ATTR4_IGNORE_RESISTANCES        => 'No Cast Log', // 5374
            SPELL_ATTR4_PROC_ONLY_ON_CASTER       => 'Class Trigger Only On Target', // 58423
            SPELL_ATTR4_FADES_WHILE_LOGGED_OUT    => 'Continues while logged out', // 48018
            SPELL_ATTR4_NO_HELPFUL_THREAT         => 'No Helpful Threat', // 32645
            SPELL_ATTR4_NO_HARMFUL_THREAT         => 'No Harmful Threat', // 48743
            SPELL_ATTR4_ALLOW_CLIENT_TARGETING    => 'Allow Client Targeting', // 53510
            SPELL_ATTR4_NOT_STEALABLE             => 'Cannot be Spellstolen', // 53756
            SPELL_ATTR4_CAN_CAST_WHILE_CASTING    => 'Allow Cast While Casting', // 53742
            SPELL_ATTR4_FIXED_DAMAGE              => 'Ignore Damage Taken Modifiers', // 31117
            SPELL_ATTR4_TRIGGER_ACTIVATE          => 'Combat Feedback When Usable',
            SPELL_ATTR4_SPELL_VS_EXTEND_COST      => 'Weapon Speed Cost Scaling', // 5938 only
            SPELL_ATTR4_NO_PARTIAL_IMMUNITY       => 'No Partial Immunity', // 1161
            SPELL_ATTR4_AURA_IS_BUFF              => 'Aura Is Buff', // 70243
            SPELL_ATTR4_DO_NOT_LOG_CASTER         => 'Do Not Log Caster', // 26062
            SPELL_ATTR4_DAMAGE_DOESNT_BREAK_AURAS => 'Reactive Damage Proc', // 26367
            SPELL_ATTR4_NOT_IN_SPELLBOOK          => 'Not In Spellbook', // 61154
            SPELL_ATTR4_NOT_USABLE_IN_ARENA       => 'Not usable in arena', // 126
            SPELL_ATTR4_USABLE_IN_ARENA           => 'Usable in arena', // 67017
            SPELL_ATTR4_AREA_TARGET_CHAIN         => 'Bouncy Chain Missiles', // 31935
            SPELL_ATTR4_ALLOW_PROC_WHILE_SITTING  => 'Allow Proc While Sitting', // 20230
            SPELL_ATTR4_NOT_CHECK_SELFCAST_POWER  => 'Aura Never Bounces', // 980
            SPELL_ATTR4_DONT_REMOVE_IN_ARENA      => 'Allow Entering Arena', // 2457
            SPELL_ATTR4_PROC_SUPPRESS_SWING_ANIM  => 'Proc Suppress Swing Anim', // 42058
            SPELL_ATTR4_CANT_TRIGGER_ITEM_SPELLS  => 'Suppress Weapon Procs', // 22570
            SPELL_ATTR4_AUTO_RANGED_COMBAT        => 'Auto Ranged Combat', // 75
            SPELL_ATTR4_IS_PET_SCALING            => 'Owner Power Scaling', // 34902
            SPELL_ATTR4_CAST_ONLY_IN_OUTLAND      => 'Flying areas only', // 60025 - WH "Allow Equip While Casting"
            SPELL_ATTR4_FORCE_DISPLAY_CASTBAR     => 'Force Display Castbar', // 48871
            SPELL_ATTR4_IGNORE_COMBAT_TIMER       => 'Ignore Combat Timer', // 19434
            SPELL_ATTR4_AURA_BOUNCE_FAILS_SPELL   => 'Aura Bounce Fails Spell', // 676
            SPELL_ATTR4_OBSOLETE                  => '', // 228 - "Obsoloete" flag is used and it is DEFENITELY not an obsolete marker
            SPELL_ATTR4_USE_FACING_FROM_SPELL     => 'Use Facing From Spell' // 228
        ),
        'attributes5' => array(
            SPELL_ATTR5_CAN_CHANNEL_WHEN_MOVING                        => 'Allow Actions During Channel', // 66588
            SPELL_ATTR5_NO_REAGENT_WHILE_PREP                          => 'No Reagent Cost With Aura', // 6201
            SPELL_ATTR5_REMOVE_ON_ARENA_ENTER                          => 'Remove Entering Arena', // 1490
            SPELL_ATTR5_USABLE_WHILE_STUNNED                           => 'Can be used while stunned', // 49575
            SPELL_ATTR5_TRIGGERS_CHANNELING                            => 'Triggers Channeling', // 8344
            SPELL_ATTR5_SINGLE_TARGET_SPELL                            => 'The aura can only affect one target', // 50720 > js
            SPELL_ATTR5_IGNORE_AREA_EFFECT_PVP_CHECK                   => 'Ignore Area Effect PvP Check', // 63803
            SPELL_ATTR5_NOT_ON_PLAYER                                  => 'Not On Player', // 38605
            SPELL_ATTR5_CANT_TARGET_PLAYER_CONTROLLED                  => 'Not On Player Controlled NPC', // 64373
            SPELL_ATTR5_START_PERIODIC_AT_APPLY                        => 'Starts ticking at aura application', // 46924
            SPELL_ATTR5_HIDE_DURATION                                  => 'Do Not Display Duration', // 33280
            SPELL_ATTR5_ALLOW_TARGET_OF_TARGET_AS_TARGET               => 'Implied Targeting', // 3411
            SPELL_ATTR5_MELEE_CHAIN_TARGETING                          => 'Melee Chain Targeting', // 50581
            SPELL_ATTR5_HASTE_AFFECT_DURATION                          => 'Spell Haste Affects Periodic', // 689
            SPELL_ATTR5_NOT_USABLE_WHILE_CHARMED                       => 'Not Available While Charmed', // 34186
            SPELL_ATTR5_TREAT_AS_AREA_EFFECT                           => 'Treat as Area Effect', // 1680
            SPELL_ATTR5_AURA_AFFECTS_NOT_JUST_REQ_EQUIPPED_ITEM        => 'Aura Affects Not Just Req. Equipped Item', // 20197 and its ranks only
            SPELL_ATTR5_USABLE_WHILE_FEARED                            => 'Usable while feared', // 50720
            SPELL_ATTR5_USABLE_WHILE_CONFUSED                          => 'Usable while confused', // 50720
            SPELL_ATTR5_DONT_TURN_DURING_CAST                          => 'AI Doesn\'t Face target', // 34172
            SPELL_ATTR5_DO_NOT_ATTEMPT_A_PET_RESUMMON_WHEN_DISMOUNTING => 'Do Not Attempt a Pet Resummon When Dismounting', // 31700
            SPELL_ATTR5_IGNORE_TARGET_REQUIREMENTS                     => 'Ignore Target Requirements', // 48743
            SPELL_ATTR5_NOT_ON_TRIVIAL                                 => 'Not On Trivial', // 34861
            SPELL_ATTR5_NO_PARTIAL_RESISTS                             => 'No Partial Resists', // 12654
            SPELL_ATTR5_IGNORE_CASTER_REQUIREMENTS                     => 'Ignore Caster Requirements', // 46394
            SPELL_ATTR5_ALWAYS_LINE_OF_SIGHT                           => 'Always Line of Sight', // 59838
            SPELL_ATTR5_SKIP_CHECKCAST_LOS_CHECK                       => 'Always AOE Line of Sight', // 27285
            SPELL_ATTR5_DONT_SHOW_AURA_IF_SELF_CAST                    => 'No Caster Aura Icon', // 47542
            SPELL_ATTR5_DONT_SHOW_AURA_IF_NOT_SELF_CAST                => 'No Target Aura Icon', // 46846
            SPELL_ATTR5_AURA_UNIQUE_PER_CASTER                         => 'Aura Unique Per Caster', // 71350
            SPELL_ATTR5_ALWAYS_SHOW_GROUND_TEXTURE                     => 'Always Show Ground Texture', // 53400
            SPELL_ATTR5_ADD_MELEE_HIT_RATING                           => 'Add Melee Hit Rating' // 1161
        ),
        'attributes6' => array(
            SPELL_ATTR6_DONT_DISPLAY_COOLDOWN                           => 'No Cooldown On Tooltip', // 72426
            SPELL_ATTR6_ONLY_IN_ARENA                                   => 'Only usable in arena', // < unused > onlyarena
            SPELL_ATTR6_IGNORE_CASTER_AURAS                             => 'Ignore Caster Auras', // 42454
            SPELL_ATTR6_ASSIST_IGNORE_IMMUNE_FLAG                       => 'Can Assist Immune PC', // 66588
            SPELL_ATTR6_IGNORE_FOR_MOD_TIME_RATE                        => 'Ignore For Mod Time Rate', // 54107
            SPELL_ATTR6_DONT_CONSUME_PROC_CHARGES                       => 'Do Not Consume Resources', // 1464
            SPELL_ATTR6_USE_SPELL_CAST_EVENT                            => 'Send SpellCast event', // 60970
            SPELL_ATTR6_AURA_IS_WEAPON_PROC                             => 'Aura Is Weapon Proc', // 45482
            SPELL_ATTR6_CANT_TARGET_CROWD_CONTROLLED                    => 'Do Not Chain To Crowd-Controlled Targets', // 31935
            SPELL_ATTR6_ALLOW_ON_CHARMED_TARGETS                        => 'Allow On Charmed Targets', // 53510
            SPELL_ATTR6_CAN_TARGET_POSSESSED_FRIENDS                    => 'No Aura Log', // 7384
            SPELL_ATTR6_NOT_IN_RAID_INSTANCE                            => 'Cannot be used in a raid', // notinraid
            SPELL_ATTR6_CASTABLE_WHILE_ON_VEHICLE                       => 'Allow While Riding Vehicle', // 2457
            SPELL_ATTR6_CAN_TARGET_INVISIBLE                            => 'Ignore Phase Shift', // 71350
            SPELL_ATTR6_AI_PRIMARY_RANGED_ATTACK                        => 'AI Primary Ranged Attack', // 50403
            SPELL_ATTR6_NO_PUSHBACK                                     => 'No Pushback', // 67892
            SPELL_ATTR6_NO_JUMP_PATHING                                 => 'No Jump Pathing', // 59790
            SPELL_ATTR6_ALLOW_EQUIP_WHILE_CASTING                       => 'Allow Equip While Casting', // 55293
            SPELL_ATTR6_CAST_BY_CHARMER                                 => 'Originate From Controller', // 48677
            SPELL_ATTR6_DELAY_COMBAT_TIMER_DURING_CAST                  => 'Delay Combat Timer During Cast', // 50782
            SPELL_ATTR6_ONLY_VISIBLE_TO_CASTER                          => 'Aura Icon Only Visible For Caster (Limit 10)', // 58371
            SPELL_ATTR6_CLIENT_UI_TARGET_EFFECTS                        => '', // 1715 - [WH] Show Mechanic as Combat Text (looks like a modern client feature...?)
            SPELL_ATTR6_ABSORB_CANNOT_BE_IGNORE                         => 'Absorb Cannot Be Ignore', // only 72054
            SPELL_ATTR6_TAPS_IMMEDIATELY                                => 'Taps immediately', // 1161
            SPELL_ATTR6_CAN_TARGET_UNTARGETABLE                         => 'Can Target Untargetable', // 62705
            SPELL_ATTR6_NOT_RESET_SWING_IF_INSTANT                      => 'Doesn\'t Reset Swing Timer if Instant', // 879
            SPELL_ATTR6_VEHICLE_IMMUNITY_CATEGORY                       => 'Vehicle Immunity Category', // 6673
            SPELL_ATTR6_LIMIT_PCT_HEALING_MODS                          => 'Ignore Healing Modifiers', // 53652
            SPELL_ATTR6_DO_NOT_AUTO_SELECT_TARGET_WITH_INITIATES_COMBAT => 'Do Not Auto Select Target with Initiates Combat', // 66017 (death grip and derivates)
            SPELL_ATTR6_LIMIT_PCT_DAMAGE_MODS                           => 'Ignore Caster Damage Modifiers', // 31117
            SPELL_ATTR6_DISABLE_TIED_EFFECT_POINTS                      => 'Disable Tied Effect Points', // 29801
            SPELL_ATTR6_IGNORE_CATEGORY_COOLDOWN_MODS                   => 'No Category Cooldown Mods' // 2894 only
        ),
        'attributes7' => array(
            SPELL_ATTR7_ALLOW_SPELL_REFLECTION                        => '', // 66843 - Allow Spell Reflection - this makes zero sense on the flagged spells
            SPELL_ATTR7_IGNORE_DURATION_MODS                          => 'No Target Duration Mod', // < unused/43095 >
            SPELL_ATTR7_DISABLE_AURA_WHILE_DEAD                       => 'Paladin aura', // 465
            SPELL_ATTR7_IS_CHEAT_SPELL                                => 'Debug Spell', // 43574
            SPELL_ATTR7_TREAT_AS_RAID_BUFF                            => 'Treat as Raid Buff', // 47883
            SPELL_ATTR7_SUMMON_PLAYER_TOTEM                           => 'Totem', // 2894
            SPELL_ATTR7_NO_PUSHBACK_ON_DAMAGE                         => 'Don\'t Cause Spell Pushback', // 66670
            SPELL_ATTR7_PREPARE_FOR_VEHICLE_CONTROL_END               => 'Prepare for Vehicle Control End', // 66218 only
            SPELL_ATTR7_HORDE_ONLY                                    => 'Horde Specific Spell', // 2825
            SPELL_ATTR7_ALLIANCE_ONLY                                 => 'Alliance Specific Spell', // 32182
            SPELL_ATTR7_DISPEL_CHARGES                                => 'Dispel Removes Charges', // 974
            SPELL_ATTR7_INTERRUPT_ONLY_NONPLAYER                      => 'Can Cause Interrupt', // 22570
            SPELL_ATTR7_CAN_CAUSE_SILENCE                             => 'Can Cause Silence', // < unused/17253 >
            SPELL_ATTR7_NO_UI_NOT_INTERRUPTIBLE                       => 'No UI Not Interruptible', // < unused/79111 >
            SPELL_ATTR7_RECAST_ON_RESUMMON                            => 'Recast On Resummon', // 52150 only
            SPELL_ATTR7_RESET_SWING_TIMER_AT_SPELL_START              => 'Reset Swing Timer at spell start', // 879
            SPELL_ATTR7_CAN_RESTORE_SECONDARY_POWER                   => 'Can Restore Inactive Power', // 68285
            SPELL_ATTR7_DO_NOT_LOG_PVP_KILL                           => 'Do Not Log PvP Kill', // 27965 only
            SPELL_ATTR7_HAS_CHARGE_EFFECT                             => 'Attack on Charge to Unit', // 100
            SPELL_ATTR7_ZONE_TELEPORT                                 => 'Report Spell failure to unit target', // 64030
            SPELL_ATTR7_NO_CLIENT_FAIL_WHILE_STUNNED_FLEEING_CONFUSED => 'No Client Fail While Stunned, Fleeing, Confused', // 642
            SPELL_ATTR7_RETAIN_COOLDOWN_THROUGH_LOAD                  => 'Retain Cooldown Through Load', // < unused/187611 >
            SPELL_ATTR7_IGNORE_COLD_WEATHER_FLYING                    => 'Ignores Cold Weather Flying Requirement', // 64761
            SPELL_ATTR7_CANT_DODGE                                    => 'No Attack Dodge',  // 27576
            SPELL_ATTR7_CANT_PARRY                                    => 'No Attack Parry',  // 27576
            SPELL_ATTR7_CANT_MISS                                     => 'No Attack Miss',  // 27576
            SPELL_ATTR7_TREAT_AS_NPC_AOE                              => 'Treat as NPC AoE', // 72454
            SPELL_ATTR7_BYPASS_NO_RESURRECT_AURA                      => 'Bypass No Resurrect Aura', // < unused/72423 >
            SPELL_ATTR7_CONSOLIDATED_RAID_BUFF                        => 'Consolidate in raid buff frame', // '[WH] Do Not Count For PvP Scoreboard', // 6673
            SPELL_ATTR7_REFLECTION_ONLY_DEFENDS                       => 'Reflection Only Defends', // 71237 only
            SPELL_ATTR7_CAN_PROC_FROM_SUPPRESSED_TARGET_PROCS         => 'Can Proc From Suppressed Target Procs', // 974
            SPELL_ATTR7_CLIENT_INDICATOR                              => 'Always Cast Log' // 70769 only
        )
    ),
    'item' => array(
        'id'            => "Item ID: ",
        'notFound'      => "This item doesn't exist.",
        'armor'         => "%s Armor",                      // ARMOR_TEMPLATE
        'block'         => "%s Block",                      // SHIELD_BLOCK_TEMPLATE
        'charges'       => "%d |4Charge:Charges;",          // ITEM_SPELL_CHARGES
        'locked'        => "Locked",                        // LOCKED
        'ratingString'  => '<!--rtg%%%1$d-->%2$s&nbsp;@&nbsp;L<!--lvl-->%3$d',
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
        'worth'         => "Worth: ",
        'consumable'    => "Consumable",
        'nonConsumable' => "Non-consumable",
        'accountWide'   => "Account-wide",
        'millable'      => "Millable",                      // ITEM_MILLABLE
        'noEquipCD'     => "No equip cooldown",
        'prospectable'  => "Prospectable",                  // ITEM_PROSPECTABLE
        'disenchantable'=> "Disenchantable",                // ITEM_DISENCHANT_ANY_SKILL
        'cantDisenchant'=> "Cannot be disenchanted",        // ITEM_DISENCHANT_NOT_DISENCHANTABLE
        'repairCost'    => "Repair cost: ",                 // REPAIR_COST
        'tool'          => "Tool: ",
        'cost'          => "Cost",                          // COSTS_LABEL
        'content'       => "Content",
        '_transfer'     => 'This item will be converted to <a href="?item=%d" class="q%d icontiny tinyspecial" style="background-image: url(STATIC_URL/images/wow/icons/tiny/%s.gif)">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        '_unavailable'  => "This item is not available to players.",
        '_rndEnchants'  => "Random Enchantments",
        '_chance'       => "(%s%% chance)",
        'slot'          => "Slot: ",
        '_quality'      => "Quality: ",                     // QUALITY
        'usableBy'      => "Usable by: ",
        'buyout'        => "Buyout price",                  // BUYOUT_PRICE
        'each'          => "each",
        'tabOther'      => "Other",
        'reqMinLevel'   => "Requires Level %d",             // ITEM_MIN_LEVEL
        'reqLevelRange' => "Requires level %d to %d (%s)",  // ITEM_LEVEL_RANGE_CURRENT
        'unique'        => ["Unique",          "Unique (%d)", "Unique: %s (%d)"         ],   // ITEM_UNIQUE, ITEM_UNIQUE_MULTIPLE, ITEM_LIMIT_CATEGORY
        'uniqueEquipped'=> ["Unique-Equipped", null,          "Unique-Equipped: %s (%d)"],   // ITEM_UNIQUE_EQUIPPABLE, null, ITEM_LIMIT_CATEGORY_MULTIPLE
        'speed'         => "Speed",                         // SPEED
        'dps'           => "(%.1f damage per second)",      // DPS_TEMPLATE
        'vendorLoc'     => "Vendor Locations",
        'purchasedIn'   => "This item can be purchased in",
        'fishingLoc'    => "Fishing Locations",
        'fishedIn'      => "This item can be fished in",
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
        'gems'          => "Gems: ",
        'socketBonus'   => "Socket Bonus: %s",              // ITEM_SOCKET_BONUS
        'socket'        => array(                           // EMPTY_SOCKET_*
            "Meta Socket",          "Red Socket",       "Yellow Socket",        "Blue Socket",          -1 => "Prismatic Socket"
        ),
        'gemColors'     => array(                           // *_GEM
            "meta",                 "red",              "yellow",               "blue"
        ),
        'gemRequires'   => "Requires ",                     // ENCHANT_CONDITION_REQUIRES
        'gemConditions' => array(                           // ENCHANT_CONDITION_* in GlobalStrings.lua
            ENCHANT_CONDITION_LESS_VALUE   => "less than %d %s |4gem:gems;",
            ENCHANT_CONDITION_MORE_COMPARE => "more %s gems than %s gems",
            ENCHANT_CONDITION_MORE_VALUE   => "at least %d %s |4gem:gems;"
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
             2 => array("Weapons", []),                     // filled with self::$spell['weaponSubClass'] on load
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
            "%c%d Mana",
            "%c%d Health",
            null,
            "%c%d Agility",
            "%c%d Strength",
            "%c%d Intellect",
            "%c%d Spirit",
            "%c%d Stamina",
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
            "Improves your resilience rating by %d.",
            "Improves haste rating by %d.",
            "Increases your expertise rating by %d.",
            "Increases attack power by %d.",
            "Increases ranged attack power by %d.",
            "Increases attack power by %d in Cat, Bear, Dire Bear, and Moonkin forms only.",
            "Increases healing done by magical spells and effects by up to %d.",
            "Increases damage done by magical spells and effects by up to %d.",
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
