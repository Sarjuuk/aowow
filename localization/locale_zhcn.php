<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$lang = array(
    // page variables
    'timeUnits' => array(
        'sg'            => ["年",  "月",  "周",  "日",  "时",  "分",  "秒",  "毫秒"],
        'pl'            => ["years", "months", "weeks", "days", "hours", "minutes", "seconds", "milliseconds"],
        'ab'            => ["年",    "月",     "周",    "日",  "小时",    "分钟",     "秒",     "毫秒"]
    ),
    'main' => array(
        'name'          => "名字",
        'link'          => "链接",
        'signIn'        => "登陆 / 注册",
        'jsError'       => "请确认你已经启用了javascript脚本。",
        'language'      => "语言",
        'feedback'      => "反馈",
        'numSQL'        => "数据库查询数",
        'timeSQL'       => "数据库查询时间",
        'noJScript'     => '<b>本网站需要使用javascript脚本</b><br />请 <a href="https://www.google.com/support/adsense/bin/answer.py?answer=12654" target="_blank">启用JavaScript脚本</a>。',
        'userProfiles'  => "My Profiles",
        'pageNotFound'  => "This %s doesn't exist.",
        'gender'        => "性别",
        'sex'           => [null, "男", "女"],
        'players'       => "Players",
        'quickFacts'    => "相关信息",
        'screenshots'   => "截图",
        'videos'        => "视频",
        'side'          => "Side",
        'related'       => "相关",
        'contribute'    => "贡献",
        // 'replyingTo'    => "The answer to a comment from",
        'submit'        => "发送",
        'cancel'        => "取消",
        'rewards'       => "奖励",
        'gains'         => "获得",
        'login'         => "登陆",
        'forum'         => "论坛",
        'n_a'           => "n/a",
        'siteRep'       => "Reputation",
        'aboutUs'       => "关于我们& 联系我们",
        'and'           => " 和 ",
        'or'            => " 或 ",
        'back'          => "返回",
        'reputationTip' => "Reputation points",
        'byUserTimeAgo' => 'By <a href="'.HOST_URL.'/?user=%s">%1$s</a> %s ago',

        // filter
        'extSearch'     => "扩展搜索",
        'addFilter'     => "添加另一个过滤器",
        'match'         => "比较",
        'allFilter'     => "所有过滤器",
        'oneFilter'     => "至少一个过滤器",
        'applyFilter'   => "应用过滤器",
        'resetForm'     => "清空表单",
        'refineSearch'  => '提示：浏览<a href="javascript:;" id="fi_subcat">二级目录</a>以精炼你的搜索。',  //Tip: Refine your search by browsing a subcategory
        'clear'         => "清除",
        'exactMatch'    => "精确匹配",
        '_reqLevel'     => "Required level",

        // infobox
        'unavailable'   => "Not available to players",      // alternative wording found: "No longer available to players" ... aw screw it <_<
        'disabled'      => "Disabled",
        'disabledHint'  => "Cannot be attained or completed",
        'serverside'    => "Serverside",
        'serversideHint'=> "These informations are not in the Client and have been provided by sniffing and/or guessing.",

        // red buttons
        'links'         => "链接",
        'compare'       => "比较",
       // 'view3D'        => "View in 3D",
        'findUpgrades'  => "查找升级……",

        // misc Tools
        'errPageTitle'  => "页面没有找到",
        'nfPageTitle'   => "错误",
        'subscribe'     => "订阅",
        'mostComments'  => ["Yesterday", "Past %d Days"],
        'utilities'     => array(
            "最近添加",                     "最新文章",                      "最新评论",                      "最新截图",                   null,
            "未评级的评论",                     11 => "Latest Videos",                  12 => "Most Comments",                  13 => "Missing Screenshots"
        ),

        // article & infobox
        'englishOnly'   => "本页面仅在 <b>English</b>下可用。",

        // calculators
        'preset'        => "预设",
        'addWeight'     => "添加另一个权重",
        'createWS'      => "Create a weight scale",
        'jcGemsOnly'    => "Include <span%s>JC-only</span> gems",
        'cappedHint'    => '提示：<a href="javascript:;" onclick="fi_presetDetails();">Remove</a> weights for capped statistics such as Hit rating.',
        'groupBy'       => "Group By",
        'gb'            => array(
            ["None", "none"],         ["Slot", "slot"],       ["Level", "level"],     ["Source", "source"]
        ),
        'compareTool'   => "物品比较工具",
        'talentCalc'    => "天赋模拟器",
        'petCalc'       => "猎人宠物模拟器",
        'chooseClass'   => "选择一个职业",
        'chooseFamily'  => "选择一个宠物家族",

        // profiler
        'realm'         => "服务器",
        'region'        => "区域",
        'viewCharacter' => "View Character",
        '_cpHead'       => "Character Profiler",
        '_cpHint'       => "The <b>Character Profiler</b> lets you edit your character, find gear upgrades, check your gearscore and more!",
        '_cpHelp'       => "To get started, just follow the steps below. If you'd like more information, check out our extensive <a href=\"?help=profiler\">help page</a>.",
        '_cpFooter'     => "If you want a more refined search try out our <a href=\"?profiles\">advanced search</a> options. You can also create a <a href=\"?profile&amp;new\">new custom profile</a>.",

        // help
        'help'          => "帮助",
        'helpTopics'    => array(
            "Commenting and You",                   "Model Viewer",                         "Screenshots: Tips & Tricks",          "Stat Weighting",
            "Talent Calculator",                    "Item Comparison",                      "Profiler",                            "Markup Guide"
        ),

        // search
        'search'        => "搜索",
        'searchButton'  => "搜索",
        'foundResult'   => "搜索到以下结果：",
        'noResult'      => "没有搜索到相关结果：",
        'tryAgain'      => "请尝试其他关键词或检查你的内容。",
        'ignoredTerms'  => "以下内容在搜索中被忽略： %s",

        // formating
        'colon'         => ': ',
        'dateFmtShort'  => "Y/m/d",
        'dateFmtLong'   => "Y/m/d \a\\t H:i",

        // error
        'intError'      => "发生内部错误。",
        'intError2'     => "发生内部错误。(%s)",
        'genericError'  => "An error has occurred; refresh the page and try again. If the error persists email <a href=\"#contact\">feedback</a>", # LANG.genericerror
        'bannedRating'  => "You have been banned from rating comments.", # LANG.tooltip_banned_rating
        'tooManyVotes'  => "You have reached the daily voting cap. Come back tomorrow!", # LANG.tooltip_too_many_votes
    ),
    'screenshot' => array(
        'submission'    => "Screenshot Submission",
        'selectAll'     => "选择全部",
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
        'achievement'   => "成就",
        'achievements'  => "成就",
        'class'         => "职业",
        'classes'       => "职业",
        'currency'      => "货币",
        'currencies'    => "货币",
        'difficulty'    => "Difficulty",
        'dispelType'    => "Dispel type",
        'duration'      => "持续时间",
        'emote'         => "表情",
        'emotes'        => "表情",
        'enchantment'   => "附魔",
        'enchantments'  => "附魔",
        'object'        => "物件",
        'objects'       => "物件",
        'glyphType'     => "雕文类型",
        'race'          => "种族",
        'races'         => "种族",
        'title'         => "头衔",
        'titles'        => "头衔",
        'eventShort'    => "事件",
        'event'         => "世界事件",
        'events'        => "世界事件",
        'faction'       => "阵营",
        'factions'      => "阵营",
        'cooldown'      => "%s 冷却",
        'item'          => "物品",
        'items'         => "物品",
        'itemset'       => "职业套装",
        'itemsets'      => "职业套装",
        'mechanic'      => "Mechanic",
        'mechAbbr'      => "Mech.",
        'meetingStone'  => "集合石",
        'npc'           => "生物",
        'npcs'          => "生物",
        'pet'           => "宠物",
        'pets'          => "猎人宠物",
        'profile'       => "简介",
        'profiles'      => "简介",
        'quest'         => "任务",
        'quests'        => "任务",
        'requires'      => "需要 %s",
        'requires2'     => "需要",
        'reqLevel'      => "需要等级 %s",
        'reqLevelHlm'   => "需要等级 %s",
        'reqSkillLevel' => "需要技能等级",
        'level'         => "等级",
        'school'        => "School",
        'skill'         => "技能",
        'skills'        => "技能",
        'spell'         => "法术",
        'spells'        => "法术",
        'type'          => "类型",
        'valueDelim'    => " 到 ",
        'zone'          => "区域",
        'zones'         => "区域",

        'pvp'           => "PvP",
        'honorPoints'   => "荣誉点数",
        'arenaPoints'   => "竞技场点数",
        'heroClass'     => "英雄职业",
        'resource'      => "Resource",
        'resources'     => "Resources",
        'role'          => "Role",
        'roles'         => "Roles",
        'specs'         => "Specs",
        '_roles'        => ["治疗", "Melee 伤害输出", "Ranged 伤害输出", "坦克"],

        'phases'        => "阶段",
        'mode'          => "模式",
        'modes'         => [-1 => "任何", "普通 / 普通 10", "英雄 / 普通 25", "英雄 10", "英雄 25"],
        'expansions'    => ["经典旧世", "燃烧的远征", "巫妖王之怒"],
        'stats'         => ["力量", "敏捷", "耐力", "智力", "精神"],
        'sources'       => array(
            "未知",                      "Crafted",                      "Drop",                         "PvP",                          "任务",                        "Vendor",
            "训练师",                      "Discovery",                    "Redemption",                   "Talent",                       "Starter",                      "事件",
            "成就",                  null,                           "Black Market",                 "Disenchanted",                 "Fished",                       "Gathered",
            "Milled",                       "Mined",                        "Prospected",                   "Pickpocketed",                 "Salvaged",                     "Skinned",
            "游戏商店"
        ),
        'languages'     => array(
             1 => "Orcish",                  2 => "Darnassian",              3 => "Taurahe",                 6 => "Dwarvish",                7 => "Common",                  8 => "Demonic",
             9 => "Titan",                  10 => "Thalassian",             11 => "Draconic",               12 => "Kalimag",                13 => "Gnomish",                14 => "Troll",
            33 => "Gutterspeak",            35 => "德莱尼语",                36 => "Zombie",                 37 => "Gnomish Binary",         38 => "Goblin Binary"
        ),
        'gl'            => [null, "主要的", "次要的"],
        'si'            => [1 => "联盟", -1 => "仅限联盟", 2 => "部落", -2 => "仅限部落", 3 => "双方"],
        'resistances'   => [null, '神圣抗性', '火焰抗性', '自然抗性', '冰霜抗性', '暗影抗性', '奥术抗性'],
        'dt'            => [null, "Magic", "Curse", "Disease", "Poison", "Stealth", "Invisibility", null, null, "Enrage"],
        'sc'            => ["Physical", "Holy", "Fire", "Nature", "Frost", "暗影", "Arcane"],
        'cl'            => [null, "战士", "圣骑士", "猎人", "潜行者", "牧师", "死亡骑士", "萨满祭司", "法师", "术士", null, "德鲁伊"],
        'ra'            => [-2 => "部落", -1 => "联盟", "双方", "人类", "兽人", "矮人", "暗夜精灵", "亡灵", "牛头人", "侏儒", "巨魔", null, "血精灵", "德莱尼"],
        'rep'           => ["仇恨", "敌对", "冷淡", "中立", "友好", "尊敬", "崇敬", "崇拜"],
        'st'            => array(
            "Default",                      "Cat Form",                     "Tree of Life",                 "Travel Form",                  "Aquatic Form",                 "Bear From",
            null,                           null,                           "Dire Bear Form",               null,                           null,                           null,
            null,                           "Shadowdance",                  null,                           null,                           "Ghostwolf",                    "Battle Stance",
            "Defensive Stance",             "Berserker Stance",             null,                           null,                           "Metamorphosis",                null,
            null,                           null,                           null,                           "Swift Flight Form",            "Shadow Form",                  "Flight Form",
            "Stealth",                      "Moonkin Form",                 "Spirit of Redemption"
        ),
        'me'            => array(
            null,                           "Charmed",                      "Disoriented",                  "Disarmed",                     "Distracted",                   "Fleeing",
            "Gripped",                      "Rooted",                       "Pacified",                     "Silenced",                     "Asleep",                       "Ensnared",
            "Stunned",                      "Frozen",                       "Incapacitated",                "Bleeding",                     "Healing",                      "Polymorphed",
            "Banished",                     "Shielded",                     "Shackled",                     "Mounted",                      "Seduced",                      "Turned",
            "Horrified",                    "Invulnerable",                 "Interrupted",                  "Dazed",                        "Discovery",                    "Invulnerable",
            "Sapped",                       "Enraged"
        ),
        'ct'            => array(
            "Uncategorized",                "Beast",                        "Dragonkin",                    "Demon",                        "Elemental",                    "Giant",
            "Undead",                       "Humanoid",                     "Critter",                      "Mechanical",                   "Not specified",                "Totem",
            "Non-combat Pet",               "Gas Cloud"
        ),
        'fa'            => array(
             1 => "Wolf",                    2 => "Cat",                     3 => "Spider",                  4 => "Bear",                    5 => "Boar",                    6 => "Crocolisk",
             7 => "Carrion Bird",            8 => "Crab",                    9 => "Gorilla",                11 => "Raptor",                 12 => "Tallstrider",            20 => "Scorpid",
            21 => "Turtle",                 24 => "Bat",                    25 => "Hyena",                  26 => "Bird of Prey",           27 => "Wind Serpent",           30 => "Dragonhawk",
            31 => "Ravager",                32 => "Warp Stalker",           33 => "Sporebat",               34 => "Nether Ray",             35 => "Serpent",                37 => "Moth",
            38 => "Chimaera",               39 => "Devilsaur",              41 => "Silithid",               42 => "Worm",                   43 => "Rhino",                  44 => "Wasp",
            45 => "Core Hound",             46 => "Spirit Beast"
        ),
        'pvpRank'       => array(
            null,                                                           "Private / Scout",                                              "Corporal / Grunt",
            "Sergeant / Sergeant",                                          "Master Sergeant / Senior Sergeant",                            "Sergeant Major / First Sergeant",
            "Knight / Stone Guard",                                         "Knight-Lieutenant / Blood Guard",                              "Knight-Captain / Legionnare",
            "Knight-Champion / Centurion",                                  "Lieutenant Commander / Champion",                              "Commander / Lieutenant General",
            "Marshal / General",                                            "Field Marshal / Warlord",                                      "Grand Marshal / High Warlord"
        ),
    ),
    'account' => array(
        'title'         => "Aowow Account",
        'email'         => "邮箱地址",
        'continue'      => "继续",
        'groups'        => array(
            -1 => "None",                   "Tester",                       "Administrator",                "Editor",                       "Moderator",                    "Bureaucrat",
            "Developer",                    "VIP",                          "Blogger",                      "Premium",                      "Localizer",                    "Sales agent",
            "Screenshot manager",           "Video manager"
        ),
        // signIn
        'doSignIn'      => "登陆你的AoWoW Account",
        'signIn'        => "登陆",
        'user'          => "用户名",
        'pass'          => "密码",
        'rememberMe'    => "保持登陆",
        'forgot'        => "忘记",
        'forgotUser'    => "用户名",
        'forgotPass'    => "密码",
        'accCreate'     => '没有账号？ <a href="?account=signup">现在注册！</a>',

        // recovery
        'recoverUser'   => "Username Request",
        'recoverPass'   => "Password Reset: Step %s of 2",
        'newPass'       => "New Password",

        // creation
        'register'      => "注册 - 第 %s 步共 2步",
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
        'accInactive'   => "That account has not yet been confirmed active.",
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
        'notFound'      => "这个表情是不存在的。",
        'self'          => "对你自己施放",
        'target'        => "To others with a target",
        'noTarget'      => "To others without a target",
        'isAnimated'    => "Uses an animation",
        'aliases'       => "别名",
        'noText'        => "这个表情没有文字。",
    ),
    'enchantment' => array(
        'details'       => "细节",
        'activation'    => "Activation",
        'notFound'      => "这个附魔是不存在的。",
        'types'         => array(
            1 => "触发法术",              3 => "装备法术",             7 => "使用法术",               8 => "Prismatic Socket",
            5 => "Statistics",              2 => "Weapon Damage",           6 => "DPS",                     4 => "Defense"
        )
    ),
    'gameObject' => array(
        'notFound'      => "这个物件不存在。",
        'cat'           => [0 => "其他", 9 => "书籍", 3 => "容器", -5 => "箱子", 25 => "渔点", -3 => "植物", -4 => "矿脉", -2 => "任务", -6 => "工具"],
        'type'          => [              9 => "书籍",  3 => "容器",  -5 => "箱子",  25 => "",              -3 => "植物",  -4 => "矿脉",  -2 => "任务", -6 => ""],
        'unkPosition'   => "这个物件的位置是未知的。",
        'npcLootPH'     => '<b>%s</b> 作为<a href="?npc=%d">%s</a>的战斗战利品之一。在他死后刷新。',
        'key'           => "Key",
        'focus'         => "Spell Focus",
        'focusDesc'     => "Spells requiring this Focus can be cast near this Object",
        'trap'          => "Trap",
        'triggeredBy'   => "Triggered by",
        'capturePoint'  => "Capture Point",
        'foundIn'       => "这个物件可找到于",
        'restock'       => "Restocks every %s."
    ),
    'npc' => array(
        'notFound'      => "This NPC doesn't exist.",
        'classification'=> "Classification",
        'petFamily'     => "宠物家族",
        'react'         => "React",
        'worth'         => "Worth",
        'unkPosition'   => "这个NPC的位置未知。",
        'difficultyPH'  => "这个NPC是一个不同的模式的占位符，请看",
        'seat'          => "Seat",
        'accessory'     => "Accessories",
        'accessoryFor'  => "This NPC is an accessory for vehicle",
        'quotes'        => "引用",
        'gainsDesc'     => "杀死这个NPC后，你将获得",
        'repWith'       => "声望",
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
        'respawnIn'     => "刷新",
        'rank'          => [0 => "Normal", 1 => "Elite", 4 => "Rare", 2 => "Rare Elite", 3 => "Boss"],
        'textRanges'    => [null, "sent to area", "sent to zone", "sent to map", "sent to world"],
        'textTypes'     => [null, "大喊", "说", "悄悄地说"],
        'modes'         => array(
            1 => ["普通", "英雄"],
            2 => ["10人普通", "25人普通", "10人英雄", "25人英雄"]
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
        'points'        => "点数",
        'series'        => "系列",
        'outOf'         => "out of",
        'criteriaType'  => "Criterium Type-Id:",
        'itemReward'    => "你将得到",
        'titleReward'   => '你将被授予头衔 "<a href="?title=%d">%s</a>"',
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
        'racialLeader'  => "种族领袖",
        'startZone'     => "起始区域",
    ),
    'maps' => array(
        'maps'          => "地图",
        'linkToThisMap' => "链接到这张地图",
        'clear'         => "清除",
        'EasternKingdoms' => "东部王国",
        'Kalimdor'      => "卡利姆多",
        'Outland'       => "外域",
        'Northrend'     => "诺森德",
        'Instances'     => "副本",
        'Dungeons'      => "地下城",
        'Raids'         => "团队副本",
        'More'          => "更多 ",
        'Battlegrounds' => "战场",
        'Miscellaneous' => "其他",
        'Azeroth'       => "艾泽拉斯",
        'CosmicMap'     => "宇宙地图",
    ),
    'zone' => array(
        'notFound'      => "This zone doesn't exist.",
        'attunement'    => ["Attunement", "Heroic attunement"],
        'key'           => ["Key", "Heroic key"],
        'location'      => "位置",
        'raidFaction'   => "Raid faction",
        'boss'          => "守关首领",
        'reqLevels'     => "需要等级: [tooltip=instancereqlevel_tip]%d[/tooltip], [tooltip=lfgreqlevel_tip]%d[/tooltip]",
        'zonePartOf'    => "这个区域是 [zone=%s] 一部分。",
        'autoRez'       => "自动复活",
        'city'          => "城市",
        'territory'     => "领土",
        'instanceType'  => "副本类型",
        'hcAvailable'   => "英雄模式可用 &nbsp;(%d)",
        'numPlayers'    => "玩家人数",
        'noMap'         => "There is no map available for this zone.",
        'instanceTypes' => ["Zone",     "Transit", "地下城",   "团队副本",      "战场", "地下城",  "竞技场", "团队副本", "团队副本"],
        'territories'   => ["联盟", "部落",   "争夺中", "Sanctuary", "PvP",          "World PvP"],
        'cat'           => array(
            "东部王国",         "卡利姆多",                 "地下城",                 "团队副本",                    "未使用",                   null,
            "战场",            null,                       "外域",                  "竞技场",                   "诺森德"
        )
    ),
    'quest' => array(
        'notFound'      => "This quest doesn't exist.",
        '_transfer'     => 'This quest will be converted to <a href="?quest=%d" class="q1">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        'questLevel'    => "任务等级 %s",
        'requirements'  => "Requirements",
        'reqMoney'      => "Required money",
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
        'theTitle'      => 'the title "%s"',
        'mailDelivery'  => "You will receive this letter%s%s",
        'mailBy'        => ' by <a href="?npc=%d">%s</a>',
        'mailIn'        => " after %s",
        'unavailable'   => "This quest was marked obsolete and cannot be obtained or completed.",
        'experience'    => "experience",
        'expConvert'    => "(or %s if completed at level %d)",
        'expConvert2'   => "%s if completed at level %d",
        'chooseItems'   => "You will be able to choose one of these rewards",
        'receiveItems'  => "You will receive",
        'receiveAlso'   => "You will also receive",
        'spellCast'     => "The following spell will be cast on you",
        'spellLearn'    => "你将学会",
        'bonusTalents'  => "天赋点数",
        'spellDisplayed'=> ' (<a href="?spell=%d">%s</a> is displayed)',
		'attachment'    => "Attachment",
        'questInfo'     => array(
             0 => "Normal",              1 => "Group",              21 => "Life",               41 => "PvP",                62 => "Raid",               81 => "Dungeon",            82 => "World Event",
            83 => "Legendary",          84 => "Escort",             85 => "Heroic",             88 => "Raid (10)",          89 => "Raid (25)"
        ),
        'cat'           => array(
            0 => array( "东部王国",
                  36 => "Alterac Mountains",              45 => "Arathi Highlands",                3 => "Badlands",                       25 => "Blackrock Mountain",              4 => "Blasted Lands",
                  46 => "Burning Steppes",               279 => "Dalaran Crater",                 41 => "Deadwind Pass",                2257 => "Deeprun Tram",                    1 => "Dun Morogh",
                  10 => "Duskwood",                      139 => "Eastern Plaguelands",            12 => "Elwynn Forest",                3430 => "Eversong Woods",               3433 => "Ghostlands",
                 267 => "Hillsbrad Foothills",          1537 => "Ironforge",                    4080 => "Isle of Quel'Danas",             38 => "Loch Modan",                     44 => "Redridge Mountains",
                  51 => "Searing Gorge",                3487 => "Silvermoon City",               130 => "Silverpine Forest",            1519 => "Stormwind City",                 33 => "Stranglethorn Vale",
                   8 => "Swamp of Sorrows",               47 => "The Hinterlands",              4298 => "The Scarlet Enclave",            85 => "Tirisfal Glades",              1497 => "Undercity",
                  28 => "Western Plaguelands",            40 => "Westfall",                       11 => "Wetlands"
            ),
            1 => array( "卡利姆多",
                  331 => "Ashenvale",                     16 => "Azshara",                      3524 => "Azuremyst Isle",               3525 => "Bloodmyst Isle",                148 => "Darkshore",
                 1657 => "Darnassus",                    405 => "Desolace",                       14 => "Durotar",                        15 => "Dustwallow Marsh",              361 => "Felwood",
                  357 => "Feralas",                      493 => "Moonglade",                     215 => "Mulgore",                      1637 => "Orgrimmar",                    1377 => "Silithus",
                  406 => "Stonetalon Mountains",         440 => "Tanaris",                       141 => "Teldrassil",                     17 => "The Barrens",                  3557 => "The Exodar",
                  457 => "The Veiled Sea",               400 => "Thousand Needles",             1638 => "Thunder Bluff",                 490 => "Un'Goro Crater",                618 => "Winterspring"
             ),
            8 => array( "外域",
                3522 => "Blade's Edge Mountains",       3483 => "Hellfire Peninsula",           3518 => "Nagrand",                      3523 => "Netherstorm",                  3520 => "Shadowmoon Valley",
                 703 => "Shattrath City",               3679 => "Skettis",                      3519 => "Terokkar Forest",              3521 => "Zangarmarsh"
            ),
           10 => array( "诺森德",
                3537 => "Borean Tundra",                2817 => "Crystalsong Forest",           4395 => "Dalaran",                        65 => "Dragonblight",                  394 => "Grizzly Hills",
                 495 => "Howling Fjord",                4742 => "Hrothgar's Landing",            210 => "Icecrown",                     3711 => "Sholazar Basin",                 67 => "The Storm Peaks",
                4197 => "Wintergrasp",                    66 => "Zul'Drak"
            ),
            2 => array( "地下城",
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
            3 => array( "团队副本",
                3959 => "Black Temple",                 2677 => "Blackwing Lair",               3923 => "Gruul's Lair",                 3606 => "Hyjal Summit",                 4812 => "Icecrown Citadel",
                3457 => "卡拉赞",                     3836 => "Magtheridon's Lair",           2717 => "Molten Core",                  3456 => "纳克萨玛斯",                    2159 => "Onyxia's Lair",
                3429 => "Ruins of Ahn'Qiraj",           3607 => "Serpentshrine Cavern",         4075 => "Sunwell Plateau",              3428 => "Temple of Ahn'Qiraj",          3842 => "The Eye",
                4500 => "The Eye of Eternity",          4493 => "The Obsidian Sanctum",         4722 => "Trial of the Crusader",        4273 => "奥杜尔",                       4603 => "阿尔卡冯的宝库",
                3805 => "祖阿曼",                     1977 => "祖尔格拉布"
            ),
            4 => array( "职业",
                -372 => "死亡骑士",                 -263 => "德鲁伊",                        -261 => "猎人",                       -161 => "法师",                         -141 => "圣骑士",
                -262 => "牧师",                       -162 => "潜行者",                         -82 => "萨满祭司",                        -61 => "术士",                       -81 => "战士"
                    ),
            5 => array( "专业",
                -181 => "炼金术",                      -121 => "Blacksmithing",                -304 => "Cooking",                      -201 => "Engineering",                  -324 => "First Aid",
                -101 => "Fishing",                       -24 => "Herbalism",                    -371 => "Inscription",                  -373 => "Jewelcrafting",                -182 => "Leatherworking",
                -264 => "Tailoring"
            ),
            6 => array( "战场",
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
    'pet'      => array(
        'notFound'      => "This pet family doesn't exist.",
        'exotic'        => "Exotic",
        'cat'           => ["Ferocity", "Tenacity", "Cunning"]
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
            "Arena Season 7 Set",                   "Tier 10 Raid Set",                     "第8赛季"
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
		'stackGroup'    => "Stack Group",
		'linkedWith'    => "Linked with",
        '_scaling'      => "Scaling",
        'scaling'       => array(
            'directSP' => "+%.2f%% of spell power to direct component",         'directAP' => "+%.2f%% of attack power to direct component",
            'dotSP'    => "+%.2f%% of spell power per tick",                    'dotAP'    => "+%.2f%% of attack power per tick"
        ),
        'powerRunes'    => ["Frost", "Unholy", "Blood", "Death"],
        'powerTypes'    => array(
            // conventional
              -2 => "Health",              0 => "Mana",                1 => "Rage",                2 => "Focus",               3 => "Energy",              4 => "Happiness",
               5 => "Rune",                6 => "Runic Power",
            // powerDisplay
              -1 => "Ammo",              -41 => "Pyrite",            -61 => "Steam Pressure",   -101 => "Heat",             -121 => "Ooze",             -141 => "Blood Power",
            -142 => "Wrath"
        ),
        'relItems'      => array(
            'base'    => "<small>Show %s related to <b>%s</b></small>",
            'link'    => " or ",
            'recipes' => '<a href="?items=9.%s">recipe items</a>',
            'crafted' => '<a href="?items&filter=cr=86;crs=%s;crv=0">crafted items</a>'
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
            0x02A5F3 => "Melee Weapon",             0x0060 => "Shield",                     0x04000C => "Ranged Weapon",            0xA091 => "One-Handed Melee Weapon"
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
        'notFound'      => "This item doesn't exist.",
        'armor'         => "%s Armor",
        'block'         => "%s Block",
        'charges'       => "Charges",
        'locked'        => "Locked",
        'ratingString'  => "%s&nbsp;@&nbsp;L%s",
        'heroic'        => "英雄",
        'unique'        => "Unique",
        'uniqueEquipped'=> "Unique-Equipped",
        'startQuest'    => "This Item Begins a Quest",
        'bagSlotString' => "%d Slot %s",
        'dps'           => "每秒伤害",
        'dps2'          => "每秒伤害",
        'addsDps'       => "Adds",
        'fap'           => "Feral Attack Power",
        'durability'    => "Durability",
        'realTime'      => "real time",
        'conjured'      => "Conjured Item",
        'damagePhys'    => "%s 伤害",
        'damageMagic'   => "%s %s 伤害",
        'speed'         => "速度",
        'sellPrice'     => "Sell Price",
        'itemLevel'     => "物品等级",
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
        '_transfer'     => 'This item will be converted to <a href="?item=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        '_unavailable'  => "This item is not available to players.",
        '_rndEnchants'  => "Random Enchantments",
        '_chance'       => "(%s%% chance)",
        'slot'          => "Slot",
        '_quality'      => "Quality",
        'usableBy'      => "Usable by",
        'buyout'        => "Buyout price",
        'each'          => "each",
        'tabOther'      => "Other",
        'gems'          => "Gems",
        'socketBonus'   => "Socket Bonus",
        'socket'        => array(
            "Meta Socket",          "Red Socket",       "Yellow Socket",        "Blue Socket",          -1 => "Prismatic Socket"
        ),
        'gemColors'     => array(
            "meta",                 "red",              "yellow",               "blue"
        ),
        'gemConditions' => array(                           // ENCHANT_CONDITION_* in GlobalStrings.lua
            2 => ["less than %d %s gem", "less than %d %s gems"],
            3 => "more %s gems than %s gems",
            5 => ["at least %d %s gem", "at least %d %s gems"]
        ),
        'reqRating'     => array(                           // ITEM_REQ_ARENA_RATING*
            "Requires personal and team arena rating of %d",
            "Requires personal and team arena rating of %d<br>in 3v3 or 5v5 brackets",
            "Requires personal and team arena rating of %d<br>in 5v5 brackets"
        ),
        'quality'       => array(
            "Poor",                 "Common",           "Uncommon",             "Rare",
            "Epic",                 "Legendary",        "Artifact",             "Heirloom"
        ),
        'trigger'       => array(
            "使用：",                "装备：",          "Chance on hit: ",      "",                             "",
            "",                     ""
        ),
        'bonding'       => array(
            "账号绑定",                         "拾取后绑定",                                 "装备后绑定",
            "使用后绑定",                          "任务物品",                                           "任务物品"
        ),
        "bagFamily"     => array(
            "Bag",                  "Quiver",           "Ammo Pouch",           "Soul Bag",                     "Leatherworking Bag",
            "Inscription Bag",      "Herb Bag",         "Enchanting Bag",       "Engineering Bag",              null, /*Key*/
            "Gem Bag",              "Mining Bag"
        ),
        'inventoryType' => array(
            null,                   "头部",             "Neck",                 "Shoulder",                     "Shirt",
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
        'cat'           => array(                           // ordered by content first, then alphabeticaly
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
    )
);

?>
