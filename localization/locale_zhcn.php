<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');



// comments in CAPS point to items in \Interface\FrameXML\GlobalStrings.lua - lowercase sources are contextual



$lang = array(
    // page variables
    'timeUnits' => array(
        'sg'            => ["年",  "月",  "周",  "天",  "小时",  "分钟",  "秒",  "毫秒"],
        'pl'            => ["年", "月", "周", "天", "小时", "分钟", "秒", "毫秒"],
        'ab'            => ["年",    "月",     "周",    "天",  "小时",    "分钟",     "秒",     "毫秒"]
    ),
    'main' => array(
        'name'          => "名字",
        'link'          => "链接",
        'signIn'        => "登录 / 注册",
        'jsError'       => "请确认你启用了javascript。",
        'language'      => "语言",
        'feedback'      => "反馈",
        'numSQL'        => "数据库查询次数",
        'timeSQL'       => "数据库查询时间",
        'noJScript'     => '<b>本站点基于JavaScript。</b><br />请在你的浏览器里<a href="https://www.google.com/support/adsense/bin/answer.py?answer=12654" target="_blank">启用JavaScript</a>。',
        'userProfiles'  => "我的简介",
        'pageNotFound'  => "%s不存在。",
        'gender'        => "性别",
        'sex'           => [null, "男性", "女性"],
        'players'       => "玩家",
        'quickFacts'    => "相关信息",
        'screenshots'   => "截图",
        'videos'        => "视频",
        'side'          => "阵营", //Side
        'related'       => "相关",
        'contribute'    => "贡献",
        // 'replyingTo'    => "The answer to a comment from",
        'submit'        => "提交",
        'cancel'        => "取消",
        'rewards'       => "奖励",
        'gains'         => "获得",  //Gains
        'login'         => "登录",
        'forum'         => "论坛",
        'n_a'           => "n/a",
        'siteRep'       => "站点声望",
        'yourRepHistory'=> "您的声望历史",
        'aboutUs'       => "关于我们 & 联系我们",
        'and'           => "和",
        'or'            => "或",
        'back'          => "返回",
        'reputationTip' => "声望点数",
        'byUserTimeAgo' => '由<a href="'.HOST_URL.'/?user=%s">%1$s</a>%s之前',
        'help'          => "帮助",

        // filter
        'extSearch'     => "扩展搜索",
        'addFilter'     => "添加一个过滤器",
        'match'         => "匹配",
        'allFilter'     => "所有过滤器",
        'oneFilter'     => "至少一个",
        'applyFilter'   => "应用过滤",
        'resetForm'     => "清除表单",
        'refineSearch'  => '提示: 通过浏览 <a href="javascript:;" id="fi_subcat">子类别</a>搜索。',
        'clear'         => "清除",
        'exactMatch'    => "精确匹配",
        '_reqLevel'     => "要求等级",

        // infobox
        'unavailable'   => "对玩家不可用",      // alternative wording found: "No longer available to players" ... aw screw it <_<
        'disabled'      => "禁用",
        'disabledHint'  => "不能达到或完成",
        'serverside'    => "服务器端",
        'serversideHint'=> "这些信息不存在于客户端，但是已通过嗅探和/或猜测获得。",

        // red buttons
        'links'         => "链接",
        'compare'       => "比较",
        'view3D'        => "在3D里查看",
        'findUpgrades'  => "查找升级……",

        // misc Tools
        'errPageTitle'  => "页面未找到",
        'nfPageTitle'   => "错误",
        'subscribe'     => "订阅",
        'mostComments'  => ["昨天", "%d天前"],
        'utilities'     => array(
            "最新添加",                     "最新文章",                      "最新评论",                      "最新截图",                   null,
            "未评级评论",                     11 => "最新视频",                  12 => "最多评论",                  13 => "缺失截图"
        ),

        // article & infobox
        'englishOnly'   => "该页面仅以<b>英语</b>提供。",

        // calculators
        'preset'        => "预设",
        'addWeight'     => "添加另一个权重",
        'createWS'      => "创建一个权重比例",
        'jcGemsOnly'    => "包含<span%s>JC-only</span>宝石",
        'cappedHint'    => 'Tip: <a href="javascript:;" onclick="fi_presetDetails();">Remove</a> weights for capped statistics such as Hit rating.',
        'groupBy'       => "按组",
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
        'viewCharacter' => "查看角色",
        '_cpHead'       => "Character Profiler",
        '_cpHint'       => "The <b>Character Profiler</b> lets you edit your character, find gear upgrades, check your gearscore and more!",
        '_cpHelp'       => "To get started, just follow the steps below. If you'd like more information, check out our extensive <a href=\"?help=profiler\">help page</a>.",
        '_cpFooter'     => "If you want a more refined search try out our <a href=\"?profiles\">advanced search</a> options. You can also create a <a href=\"?profile&amp;new\">new custom profile</a>.",

        // search
        'search'        => "搜索",
        'searchButton'  => "搜索",
        'foundResult'   => "搜索结果关于",
        'noResult'      => "没有搜索结果关于",
        'tryAgain'      => "请尝试不同的关键词或检查你的拼写。",
        'ignoredTerms'  => "以下词语在搜索中已被忽略：%s",

        // formating
        'colon'         => ': ',
        'dateFmtShort'  => "Y/m/d",
        'dateFmtLong'   => "Y/m/d \a\\t H:i",

        // error
        'intError'      => "发生内部错误。",
        'intError2'     => "发生内部错误。(%s)",
        'genericError'  => "发生错误，请刷新页面再试一次。如果错误持续存在，请联系<a href=\"#contact\">反馈</a>。", # LANG.genericerror
        'bannedRating'  => "你评级评论的权力已被冻结。", # LANG.tooltip_banned_rating
        'tooManyVotes'  => "你已经达到每日投票上限。请明天再来！", # LANG.tooltip_too_many_votes

        'moreTitles'    => array(
            'reputation'    => "网站声望",
            'whats-new'     => "新内容",
            'searchbox'     => "搜索框",
            'tooltips'      => "工具提示",
            'faq'           => "常见问答",
            'aboutus'       => "什么是AoWoW？",
            'searchplugins' => "搜索插件",
            'privileges'    => "特权",
            'top-users'     => "高级用户",
            'help'          => array(
                'commenting-and-you' => "评论和你",               'modelviewer'       => "模型查看器",              'screenshots-tips-tricks' => "截图：提示和技巧",
                'stat-weighting'     => "Stat Weighting",                   'talent-calculator' => "天赋模拟器",         'item-comparison'         => "物品比较工具",
                'profiler'           => "Profiler",                         'markup-guide'      => "标记指南"
            )
        )
    ),
    'screenshot' => array(
        'submission'    => "截图提交",
        'selectAll'     => "选择全部",
        'cropHint'      => "您可以裁剪您的截图并输入标题。",
        'displayOn'     => "显示在：[br]%s - [%s=%d]",
        'caption'       => "标题",
        'charLimit'     => "可选，最多200个字符",
        'thanks'        => array(
            'contrib' => "非常感谢你的贡献！",
            'goBack'  => '<a href="?%s=%d">点击这里</a>返回上一页。',
            'note'    => "注意: 你的截图在显示在网站前需要审核。这需要最多72小时。"
        ),
        'error'         => array(
            'unkFormat'   => "未知图像格式。",
            'tooSmall'    => "你的截图太小了。 (&lt; ".CFG_SCREENSHOT_MIN_SIZE."x".CFG_SCREENSHOT_MIN_SIZE.").",
            'selectSS'    => "请选择截图上传。",
            'notAllowed'  => "你不允许上传截图！",
        )
    ),
    'game' => array(
        'achievement'   => "成就",
        'achievements'  => "成就",
        'class'         => "职业",
        'classes'       => "职业",
        'currency'      => "货币",
        'currencies'    => "货币",
        'difficulty'    => "难度",
        'dispelType'    => "驱散类型",
        'duration'      => "持续时间",
        'emote'         => "表情",
        'emotes'        => "表情",
        'enchantment'   => "附魔",
        'enchantments'  => "附魔",
        'object'        => "对象",
        'objects'       => "对象",
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
        'cooldown'      => "%s冷却时间",
        'icon'          => "图标",
        'icons'         => "图标",
        'item'          => "物品",
        'items'         => "物品",
        'itemset'       => "套装",
        'itemsets'      => "套装",
        'mechanic'      => "机制",  //mechanic
        'mechAbbr'      => "机制",  //Mech.
        'meetingStone'  => "集合石",
        'npc'           => "NPC",
        'npcs'          => "NPC",
        'pet'           => "猎人宠物",
        'pets'          => "猎人宠物",
        'profile'       => "简介",
        'profiles'      => "简介",
        'quest'         => "任务",
        'quests'        => "任务",
        'requires'      => "需要%s",
        'requires2'     => "需要",
        'reqLevel'      => "需要等级%s",
        'reqSkillLevel' => "需要技能等级",
        'level'         => "等级",
        'school'        => "类型",  //School
        'skill'         => "技能",
        'skills'        => "技能",
        'sound'         => "声音",
        'sounds'        => "声音",
        'spell'         => "法术",
        'spells'        => "法术",
        'type'          => "类型",
        'valueDelim'    => "到",
        'zone'          => "区域",
        'zones'         => "区域",

        'pvp'           => "PvP",                           // PVP
        'honorPoints'   => "荣誉点数",                  // HONOR_POINTS
        'arenaPoints'   => "竞技场点数",                  // ARENA_POINTS
        'heroClass'     => "英雄职业",
        'resource'      => "资源",
        'resources'     => "资源",
        'role'          => "角色",                          // ROLE
        'roles'         => "职责",                         // LFG_TOOLTIP_ROLES
        'specs'         => "专精",  //Specs
        '_roles'        => ["治疗者", "近距离伤害输出者", "远距离伤害输出者", "坦克"],

        'phases'        => "阶段",
        'mode'          => "模式",
        'modes'         => [-1 => "任何", "普通 / 普通 10人", "英雄 / 普通 25人", "英雄 10人", "英雄 25人"],
        'expansions'    => ["经典旧世", "燃烧的远征", "巫妖王之怒"],
        'stats'         => ["力量", "敏捷", "耐力", "智力", "精神"],
        'sources'       => array(
            "未知",                      "制造",                      "掉落",                         "PvP",                          "任务",                        "出售",
            "训练师",                      "探索",                    "赎回",                   "天赋",                       "Starter",                      "事件",
            "成就",                  null,                           "黑市",                 "Disenchanted",                 "钓鱼",                       "采集",
            "研磨",                       "采矿",                        "勘探",                   "Pickpocketed",                 "回收",                     "剥皮",
            "游戏内商城"
        ),
        'languages'     => array(
             1 => "兽人语",                  2 => "达纳苏斯语",              3 => "牛头人语",                 6 => "Dwarvish",                7 => "通用语",                  8 => "Demonic",
             9 => "泰坦语",                  10 => "萨拉斯语",             11 => "龙语",               12 => "Kalimag",                13 => "侏儒语",                14 => "巨魔语",
            33 => "亡灵语",            35 => "德莱尼语",                36 => "Zombie",                 37 => "Gnomish Binary",         38 => "Goblin Binary"
        ),
        'gl'            => [null, "主要", "次要"],                                                                                                                               // MAJOR_GLYPH, MINOR_GLYPH
        'si'            => [1 => "联盟", -1 => "仅限联盟", 2 => "部落", -2 => "仅限部落", 3 => "双方"],
        'resistances'   => [null, 'Holy Resistance', 'Fire Resistance', 'Nature Resistance', 'Frost Resistance', 'Shadow Resistance', 'Arcane Resistance'],                         // RESISTANCE?_NAME
        'dt'            => [null, "Magic", "Curse", "Disease", "Poison", "Stealth", "Invisibility", null, null, "Enrage"],                                                          // SpellDispalType.dbc
        'sc'            => ["Physical", "Holy", "Fire", "Nature", "Frost", "Shadow", "Arcane"],                                                                                     // STRING_SCHOOL_*
        'cl'            => [null, "战士", "圣骑士", "猎人", "潜行者", "牧师", "死亡骑士", "萨满祭司", "法师", "术士", null, "德鲁伊"],                                   // ChrClasses.dbc
        'ra'            => [-2 => "部落", -1 => "联盟", "双方", "人类", "兽人", "矮人", "暗夜精灵", "亡灵", "牛头人", "侏儒", "巨魔", null, "血精灵", "德莱尼"],     // ChrRaces.dbc
        'rep'           => ["仇恨", "敌对", "敌意", "中立", "友善", "尊敬", "崇敬", "崇拜"],                                                              // FACTION_STANDING_LABEL*
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
        'pvpRank'       => array(                           // PVP_RANK_\d_\d(_FEMALE)?
            null,                                                           "Private / Scout",                                              "Corporal / Grunt",
            "Sergeant / Sergeant",                                          "Master Sergeant / Senior Sergeant",                            "Sergeant Major / First Sergeant",
            "Knight / Stone Guard",                                         "Knight-Lieutenant / Blood Guard",                              "Knight-Captain / Legionnare",
            "Knight-Champion / Centurion",                                  "Lieutenant Commander / Champion",                              "Commander / Lieutenant General",
            "Marshal / General",                                            "Field Marshal / Warlord",                                      "Grand Marshal / High Warlord"
        ),
    ),
    'account' => array(
        'title'         => "Aowow账号",
        'email'         => "电子邮箱地址",
        'continue'      => "继续",
        'groups'        => array(
            -1 => "无",                   "Tester",                       "Administrator",                "Editor",                       "Moderator",                    "Bureaucrat",
            "Developer",                    "VIP",                          "Blogger",                      "Premium",                      "Localizer",                    "Sales agent",
            "Screenshot manager",           "Video manager",                "API partner",                  "Pending"
        ),
        // signIn
        'doSignIn'      => "登录你的AoWoW账号",
        'signIn'        => "登录",
        'user'          => "用户名",
        'pass'          => "密码",
        'rememberMe'    => "保持登录",
        'forgot'        => "忘记",
        'forgotUser'    => "用户名",
        'forgotPass'    => "密码",
        'accCreate'     => '没有账号？<a href="?account=signup">现在创建一个！</a>',

        // recovery
        'recoverUser'   => "Username Request",
        'recoverPass'   => "密码重置：步骤 %s / 2",
        'newPass'       => "新密码",

        // creation
        'register'      => "注册 - 步骤 %s / 2",
        'passConfirm'   => "确认密码",

        // dashboard
        'ipAddress'     => "IP地址",
        'lastIP'        => "last used IP",
        'myAccount'     => "我的账号",
        'editAccount'   => "Simply use the forms below to update your account information",
        'viewPubDesc'   => 'View your Public Description in your <a href="?user=%s">Profile  Page</a>',

        // bans
        'accBanned'     => "这个账号已被关闭",
        'bannedBy'      => "冻结操作者",
        'ends'          => "结束于",
        'permanent'     => "永久冻结",
        'reason'        => "理由",
        'noReason'      => "没有理由提供。",

        // form-text
        'emailInvalid'  => "该电子邮件地址无效。", // message_emailnotvalid
        'emailNotFound' => "你输入的电子邮件地址与任何帐户不关联。<br><br>If you forgot the email you registered your account with email ".CFG_CONTACT_EMAIL." for assistance.",
        'createAccSent' => "电子邮件发送到<b>%s</b>。只需按照说明创建你的帐户。",
        'recovUserSent' => "电子邮件发送到<b>%s</b>。只需按照说明恢复你的用户名。",
        'recovPassSent' => "电子邮件发送到<b>%s</b>。只需按照说明重置你的密码。",
        'accActivated'  => '你的帐户已被激活。<br>Proceed to <a href="?account=signin&token=%s">sign in</a>',
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
        'notFound'      => "用户 \"%s\" 未找到",
        'removed'       => "(已移除)",
        'joinDate'      => "加入",
        'lastLogin'     => "上次访问",
        'userGroups'    => "Role",
        'consecVisits'  => "Consecutive visits",
        'publicDesc'    => "Public Description",
        'profileTitle'  => "%s's Profile",
        'contributions' => "Contributions",
        'uploads'       => "Data uploads",
        'comments'      => "评论",
        'screenshots'   => "截图",
        'videos'        => "视频",
        'posts'         => "Forum posts"
    ),
    'mail' => array(
        'tokenExpires'  => "This token expires in %s.",
        'accConfirm'    => ["Account Confirmation", "Welcome to ".CFG_NAME_SHORT."!\r\n\r\nClick the Link below to activate your account.\r\n\r\n".HOST_URL."?account=signup&token=%s\r\n\r\nIf you did not request this mail simply ignore it."],
        'recoverUser'   => ["User Recovery",        "Follow this link to log in.\r\n\r\n".HOST_URL."?account=signin&token=%s\r\n\r\nIf you did not request this mail simply ignore it."],
        'resetPass'     => ["Password Reset",       "Follow this link to reset your password.\r\n\r\n".HOST_URL."?account=forgotpassword&token=%s\r\n\r\nIf you did not request this mail simply ignore it."]
    ),
    'emote' => array(
        'notFound'      => "这个表情不存在。",
        'self'          => "对你自己",
        'target'        => "对别人并且选择了目标",
        'noTarget'      => "对别人并且不选择目标",
        'isAnimated'    => "使用动画",  //Uses an animation
        'aliases'       => "别名",  //Aliases
        'noText'        => "这个表情没有文字。",
    ),
    'enchantment' => array(
        'details'       => "细节",
        'activation'    => "激活",
        'notFound'      => "这个附魔不存在。",
        'types'         => array(
            1 => "触发法术",              3 => "装备法术",             7 => "使用法术",               8 => "棱形插槽",
            5 => "统计",              2 => "武器伤害",           6 => "DPS",                     4 => "防御"
        )
    ),
    'gameObject' => array(
        'notFound'      => "这个对象不存在。",
        'cat'           => [0 => "Other", 9 => "Books", 3 => "Containers", -5 => "Chests", 25 => "Fishing Pools", -3 => "Herbs", -4 => "Mineral Veins", -2 => "Quest", -6 => "Tools"],
        'type'          => [              9 => "Book",  3 => "Container",  -5 => "Chest",  25 => "",              -3 => "Herb",  -4 => "Mineral Vein",  -2 => "Quest", -6 => ""],
        'unkPosition'   => "这个对象的位置未知。",
        'npcLootPH'     => '这个<b>%s</b>包含战利品由于与<a href="?npc=%d">%s</a>的作战。在他/她/它死亡后刷新。',
        'key'           => "Key",
        'focus'         => "Spell Focus",
        'focusDesc'     => "Spells requiring this Focus can be cast near this Object",
        'trap'          => "Trap",
        'triggeredBy'   => "触发由",  //Triggered by
        'capturePoint'  => "Capture Point",
        'foundIn'       => "这个对象可被找到在",
        'restock'       => "Restocks every %s."
    ),
    'npc' => array(
        'notFound'      => "这个NPC不存在。",
        'classification'=> "分类",   //Classification
        'petFamily'     => "宠物家族",
        'react'         => "反应",  //React
        'worth'         => "Worth",
        'unkPosition'   => "这个NPC的位置未知。",
        'difficultyPH'  => "这个NPC是不同模式下的占位符，是",
        'seat'          => "Seat",
        'accessory'     => "Accessories",
        'accessoryFor'  => "This NPC is an accessory for vehicle",
        'quotes'        => "引用",  //Quotes
        'gainsDesc'     => "杀死这个NPC后你将得到",
        'repWith'       => "点声望点数", //reputation with
        'stopsAt'       => "在%s停止",
        'vehicle'       => "载具",  //Vehicle
        'stats'         => "状态",  //Stats
        'melee'         => "Melee",
        'ranged'        => "Ranged",
        'armor'         => "护甲",
        'foundIn'       => "这个NPC能在以下地区找到：",
        'tameable'      => "可驯服的(%s)",
        'waypoint'      => "路径点",  //Waypoint
        'wait'          => "等待",
        'respawnIn'     => "重生",  //Respawn in
        'rank'          => [0 => "普通", 1 => "稀有", 4 => "精英", 2 => "稀有精英", 3 => "首领"],
        'textRanges'    => [null, "发送到地区", "发送到区域", "发送到地图", "发送到世界"],
        'textTypes'     => [null, "喊道", "说", "悄悄地说"],
        'modes'         => array(
            1 => ["普通", "英雄"],
            2 => ["10人普通", "25人普通", "10人英雄", "25人英雄"]
        ),
        'cat'           => array(
            "未分类",            "野兽",                   "龙类",               "恶魔",                   "元素",               "巨人",                   "亡灵",                   "人型",
            "小动物",                 "机械",              "Not specified",            "图腾",                   "非战斗宠物",          "气体云雾"
        ),
    ),
    'event' => array(
        'notFound'      => "这个世界事件不存在。",
        'start'         => "开始",
        'end'           => "结束",
        'interval'      => "间隔",   //Interval
        'inProgress'    => "事件正在进行中",
        'category'      => ["未分类", "节日", "循环", "PvP"]
    ),
    'achievement' => array(
        'notFound'      => "这个成就不存在。",
        'criteria'      => "达成条件",  //Criteria
        'points'        => "点数",
        'series'        => "系列",  //Series
        'outOf'         => "out of",
        'criteriaType'  => "Criterium Type-Id:",
        'itemReward'    => "你将得到",
        'titleReward'   => '你将被授予头衔"<a href="?title=%d">%s</a>"',
        'slain'         => "杀死", //slain
        'reqNumCrt'     => "要求",
        'rfAvailable'   => "Available on realm: ",
        '_transfer'     => 'This achievement will be converted to <a href="?achievement=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
    ),
    'chrClass' => array(
        'notFound'      => "这个职业不存在。"
    ),
    'race' => array(
        'notFound'      => "这个种族不存在。",
        'racialLeader'  => "种族领袖",
        'startZone'     => "起始区域",  //Starting zone
    ),
    'maps' => array(
        'maps'          => "地图",
        'linkToThisMap' => "链接到这个地图",
        'clear'         => "清除",
        'EasternKingdoms' => "东部王国",
        'Kalimdor'      => "卡利姆多",
        'Outland'       => "外域",
        'Northrend'     => "诺森德",
        'Instances'     => "地下城和团队副本",  //Instances
        'Dungeons'      => "地下城",
        'Raids'         => "团队副本",
        'More'          => "更多",
        'Battlegrounds' => "战场",
        'Miscellaneous' => "其他",
        'Azeroth'       => "艾泽拉斯",
        'CosmicMap'     => "宇宙地图",
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
        'notFound'      => "这个区域不存在。",
        'attunement'    => ["Attunement", "Heroic attunement"],
        'key'           => ["钥匙", "英雄钥匙"],
        'location'      => "位置",
        'raidFaction'   => "Raid faction",
        'boss'          => "最终首领",
        'reqLevels'     => "要求等级： [tooltip=instancereqlevel_tip]%d[/tooltip], [tooltip=lfgreqlevel_tip]%d[/tooltip]",
        'zonePartOf'    => "这个区域是[zone=%s]的一部分。",
        'autoRez'       => "自动复活",
        'city'          => "城市",
        'territory'     => "Territory",
        'instanceType'  => "Instance类型",
        'hcAvailable'   => "英雄模式可用&nbsp;(%d)",
        'numPlayers'    => "玩家人数",
        'noMap'         => "这个区域没有可用地图。",
        'instanceTypes' => ["区域",     "运送", "地下城",   "团队副本",      "战场", "地下城",  "竞技场", "团队副本", "团队副本"],
        'territories'   => ["联盟", "部落",   "争夺中", "安全区域", "PvP",          "世界PvP"],
        'cat'           => array(
            "东部王国",         "卡利姆多",                 "地下城",                 "团队副本",                    "未使用",                   null,
            "战场",            null,                       "外域",                  "竞技场",                   "诺森德"
        )
    ),
    'quest' => array(
        'notFound'      => "这个任务不存在。",
        '_transfer'     => '这个任务将被转换到<a href="?quest=%d" class="q1">%s</a>，如果你转移到<span class="icon-%s">%s</span>。',
        'questLevel'    => "等级%s",
        'requirements'  => "要求", //Requirements
        'reqMoney'      => "要求金钱",                // REQUIRED_MONEY
        'money'         => "金钱",
        'additionalReq' => "获得这个任务的额外要求",
        'reqRepWith'    => '你<a href="?faction=%d">%s</a>的声望需要%s %s',
        'reqRepMin'     => "至少",
        'reqRepMax'     => "低于",
        'progress'      => "进行", //Progress
        'provided'      => "Provided",
        'providedItem'  => "Provided item",
        'completion'    => "完成",
        'description'   => "描述",
        'playerSlain'   => "Players slain",  //Players slain
        'profession'    => "专业",
        'timer'         => "Timer",
        'loremaster'    => "博学者",  //Loremaster
        'suggestedPl'   => "建议玩家数", //Suggested players
        'keepsPvpFlag'  => "保持你的PvP标记",
        'daily'         => "每日",
        'weekly'        => "每周",
        'monthly'       => "每月",
        'sharable'      => "可共享的", //Sharable
        'notSharable'   => "不可共享的",
        'repeatable'    => "可重复的",
        'reqQ'          => "要求",
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
        'gainsDesc'     => "完成这个任务后，你将获得",
        'theTitle'      => 'the title "%s"',                                        // partly REWARD_TITLE
        'mailDelivery'  => "你会收到这封信%s%s",
        'mailBy'        => '由<a href="?npc=%d">%s</a>',
        'mailIn'        => " after %s",
        'unavailable'   => "这项任务已被标记为过时，无法获得或完成。",
        'experience'    => "经验",
        'expConvert'    => "（或%s如果在等级%d完成）",
        'expConvert2'   => "%s如果在等级%d完成",
        'chooseItems'   => "你将能够选择其中的一个奖励",       // REWARD_CHOICES
        'receiveItems'  => "你将得到",                                      // REWARD_ITEMS_ONLY
        'receiveAlso'   => "你也将得到",                                 // REWARD_ITEMS
        'spellCast'     => "下面的法术将会被施放在你身上",               // REWARD_AURA
        'spellLearn'    => "你将学会",                                        // REWARD_SPELL
        'bonusTalents'  => "%d天赋|4点数:点数;",                             // partly LEVEL_UP_CHAR_POINTS
        'spellDisplayed'=> ' (<a href="?spell=%d">%s</a> is displayed)',
        'attachment'    => "附件",
        'questInfo'     => array(
             0 => "Normal",              1 => "Group",              21 => "Life",               41 => "PvP",                62 => "团队副本",               81 => "地下城",            82 => "世界事件",
            83 => "传说",          84 => "护送",             85 => "英雄",             88 => "团队副本(10)",          89 => "团队副本(25)"
        ),
        'cat'           => array(
            0 => array( "东部王国",
                  36 => "奥特兰克山脉",              45 => "阿拉希高地",                3 => "荒芜之地",                       25 => "黑石山",              4 => "诅咒之地",
                  46 => "燃烧平原",               279 => "达拉然巨坑",                 41 => "逆风小径",                2257 => "矿道地铁",                    1 => "丹莫罗",
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
                -372 => "Death Knight",                 -263 => "Druid",                        -261 => "Hunter",                       -161 => "Mage",                         -141 => "圣骑士",
                -262 => "Priest",                       -162 => "Rogue",                         -82 => "Shaman",                        -61 => "Warlock",                       -81 => "战士"
                    ),
            5 => array( "Professions",
                -181 => "Alchemy",                      -121 => "Blacksmithing",                -304 => "Cooking",                      -201 => "Engineering",                  -324 => "First Aid",
                -101 => "Fishing",                       -24 => "Herbalism",                    -371 => "Inscription",                  -373 => "Jewelcrafting",                -182 => "Leatherworking",
                -264 => "Tailoring"
            ),
            6 => array( "战场",
                 -25 => "全部",                          2597 => "奥特兰克山谷",               3358 => "阿拉希盆地",                 3820 => "风暴之眼",             4710 => "征服之岛",
                4384 => "远古海滩",       3277 => "战歌峡谷"
            ),
            9 => array( "Seasonal",
                -370 => "美酒节",                    -1002 => "儿童周",              -364 => "暗月马戏团",                -41 => "悼念日",             -1003 => "万圣节",
               -1005 => "收获节",             -376 => "情人节",           -366 => "春节",               -369 => "仲夏火焰节",                   -1006 => "除夕夜",
                -375 => "感恩节",             -374 => "复活节",                 -1001 => "冬幕节"
            ),
            7 => array( "杂项",
                -365 => "安其拉战争",         -241 => "锦标赛",           -1010 => "地下城查找器",                 -1 => "史诗",                         -344 => "传说",
                -367 => "声望",                   -368 => "天灾入侵"
            ),
           -2 => "未分类"
        )
    ),
    'icon'  => array(
        'notFound'      => "这个图标不存在。"
    ),
    'title' => array(
        'notFound'      => "这个头衔不存在。",
        '_transfer'     => 'This title will be converted to <a href="?title=%d" class="q1">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        'cat'           => array(
            "General",      "PvP",    "声望",       "Dungeons & Raids",     "任务",       "专业",      "世界事件"
        )
    ),
    'skill' => array(
        'notFound'      => "这个技能不存在。",
        'cat'           => array(
            -6 => "Companions",         -5 => "Mounts",             -4 => "Racial Traits",      5 => "Attributes",          6 => "Weapon Skills",       7 => "Class Skills",        8 => "Armor Proficiencies",
             9 => "Secondary Skills",   10 => "Languages",          11 => "Professions"
        )
    ),
    'currency' => array(
        'notFound'      => "这个货币不存在。",
        'cap'           => "Total cap",
        'cat'           => array(
            1 => "Miscellaneous", 2 => "Player vs. Player", 4 => "Classic", 21 => "Wrath of the Lich King", 22 => "Dungeon and Raid", 23 => "Burning Crusade", 41 => "Test", 3 => "Unused"
        )
    ),
    'sound' => array(
        'notFound'      => "这个音频不存在。",
        'foundIn'       => "这个声音可以找到在",
        'goToPlaylist'  => "转到我的播放列表",
        'music'         => "音乐",
        'intro'         => "介绍音乐",
        'ambience'      => "背景音乐",
        'cat'           => array(
            null,              "法术",            "用户界面", "脚步",   "Weapons Impacts", null,      "Weapons Misses", null,            null,         "Pick Up/Put Down",
            "NPC Combat",      null,                "错误",         "Nature",      "Objects",         null,      "死亡",          "NPC Greetings", null,         "Armor",
            "Footstep Splash", "Water (Character)", "Water",          "Tradeskills", "Misc Ambience",   "Doodads", "Spell Fizzle",   "NPC Loops",     "Zone Music", "Emotes",
            "Narration Music", "Narration",         50 => "Zone Ambience", 52 => "Emitters", 53 => "Vehicles", 1000 => "我的播放列表"
        )
    ),
    'pet'      => array(
        'notFound'      => "这个宠物家族不存在。",
        'exotic'        => "异域的",
        'cat'           => ["狂野", "坚韧", "狡诈"],
        'food'          => ["肉", "鱼", "奶酪", "面包", "蘑菇", "水果", "生肉", "生鱼"] // ItemPetFood.dbc
    ),
    'faction' => array(
        'notFound'      => "这个阵营不存在。",
        'spillover'     => "声望溢出",
        'spilloverDesc' => "获得这个阵营的声望也将按比例获得下列阵营的声望。", //Gaining reputation with this faction also yields a proportional gain with the factions listed below.
        'maxStanding'   => "Max. Standing",
        'quartermaster' => "Quartermaster",
        'customRewRate' => "Custom Reward Rate",
        '_transfer'     => 'The reputation with this faction will be converted to <a href="?faction=%d" class="q1">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        'cat'           => array(
            1118 => ["Classic", 469 => "联盟", 169 => "Steamwheedle Cartel", 67 => "部落", 891 => "Alliance Forces", 892 => "Horde Forces"],
            980  => ["The Burning Crusade", 936 => "Shattrath City"],
            1097 => ["Wrath of the Lich King", 1052 => "Horde Expedition", 1117 => "Sholazar Basin", 1037 => "Alliance Vanguard"],
            0    => "Other"
        )
    ),
    'itemset' => array(
        'notFound'      => "这个物品套装不存在。",
        '_desc'         => "<b>%s</b>是<b>%s</b>。它包含%s件。",
        '_descTagless'  => "<b>%s</b> is an item set that contains %s pieces.",
        '_setBonuses'   => "套装奖励",
        '_conveyBonus'  => "穿更多这个套装的部分将会提供给你角色奖励。", //Wearing more pieces of this set will convey bonuses to your character.
        '_pieces'       => "件",  //pieces
        '_unavailable'  => "这个物品套装对玩家不可用。",
        '_tag'          => "Tag",
        'summary'       => "摘要",
        'notes'         => array(
            null,                                   "地下城套装1",                        "地下城套装2",                        "T1团队副本套装",
            "T2团队副本套装",                      "T3团队副本套装",                      "60级PVP稀有套装",                "60级PVP稀有套装（旧）",
            "60级PVP史诗套装",                "安其拉废墟套装",               "安其拉神庙套装",              "祖尔格拉布套装",
            "T4团队副本套装",                      "T5团队副本套装",                      "地下城套装3",                        "阿拉希盆地套装",
            "70级PVP稀有套装",                "竞技场第1赛季套装",                   "T6团队副本套装",                      "竞技场第2赛季套装",
            "竞技场第3赛季套装",                   "70级PVP稀有套装2",              "竞技场第4赛季套装",                   "T7团队副本套装",
            "竞技场第5赛季套装",                   "T8团队副本套装",                      "竞技场第6赛季套装",                   "T9团队副本套装",
            "竞技场第7赛季套装",                   "T10团队副本套装",                     "竞技场第8赛季套装"
        ),
        'types'         => array(
            null,               "Cloth",                "Leather",              "Mail",                     "Plate",                    "Dagger",                   "Ring",
            "Fist Weapon",      "One-Handed Axe",       "One-Handed Mace",      "One-Handed Sword",         "Trinket",                  "Amulet"
        )
    ),
    'spell' => array(
        'notFound'      => "这个法术不存在。",
        '_spellDetails' => "法术细节", //Spell Details
        '_cost'         => "成本", //Cost
        '_range'        => "范围",
        '_castTime'     => "施法时间",
        '_cooldown'     => "冷却时间",
        '_distUnit'     => "码",
        '_forms'        => "Forms",
        '_aura'         => "光环",
        '_effect'       => "效果",
        '_none'         => "无",
        '_gcd'          => "GCD",
        '_globCD'       => "公共冷却时间",
        '_gcdCategory'  => "GCD类别",
        '_value'        => "值",
        '_radius'       => "半径",
        '_interval'     => "Interval",
        '_inSlot'       => "in slot",
        '_collapseAll'  => "折叠全部",
        '_expandAll'    => "展开全部",
        '_transfer'     => 'This spell will be converted to <a href="?spell=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        'discovered'    => "Learned via discovery",
        'ppm'           => "%s每分钟触发几率",
        'procChance'    => "触发几率",
        'starter'       => "Starter spell",
        'trainingCost'  => "训练成本",
        'remaining'     => "持续%s",                  // SPELL_TIME_REMAINING_*  //remaining
        'untilCanceled' => "直到被取消",               // SPELL_DURATION_UNTIL_CANCELLED
        'castIn'        => "%s秒施法",                   // SPELL_CAST_TIME_SEC
        'instantPhys'   => "瞬发",                       // SPELL_CAST_TIME_INSTANT_NO_MANA
        'instantMagic'  => "瞬发",                  // SPELL_CAST_TIME_INSTANT
        'channeled'     => "Channeled",                     // SPELL_CAST_CHANNELED
        'range'         => "%s码",                   // SPELL_RANGE / SPELL_RANGE_DUAL
        'meleeRange'    => "近战范围",                   // MELEE_RANGE
        'unlimRange'    => "无限范围",               // SPELL_RANGE_UNLIMITED
        'reagents'      => "Reagents",                      // SPELL_REAGENTS
        'tools'         => "工具",                         // SPELL_TOTEMS
        'home'          => "&lt;旅店&gt;", //Inn
        'pctCostOf'     => "的基础%s",  //of base 
        'costPerSec'    => "，加%s每秒",             // see 'powerTypes'
        'costPerLevel'  => "，加%s每级",           // not used?
        'stackGroup'    => "Stack Group",
        'linkedWith'    => "Linked with",
        '_scaling'      => "缩放比例",
        'scaling'       => array(
            'directSP' => "+%.2f%% of spell power to direct component",         'directAP' => "+%.2f%% of attack power to direct component",
            'dotSP'    => "+%.2f%% of spell power per tick",                    'dotAP'    => "+%.2f%% of attack power per tick"
        ),
        'powerRunes'    => ["冰霜", "邪恶", "鲜血", "Death"], // RUNE_COST_* / COMBAT_TEXT_RUNE_*
        'powerTypes'    => array(
            // conventional - HEALTH, MANA, RAGE, FOCUS, ENERGY, HAPPINESS, RUNES, RUNIC_POWER / *_COST / *COST_PER_TIME
              -2 => "生命值",              0 => "法力值",                1 => "怒气值",                2 => "集中值",               3 => "能量值",              4 => "Happiness",
               5 => "符文值",               6 => "符文能量值",
            // powerDisplay - PowerDisplay.dbc -> GlobalStrings.lua POWER_TYPE_*
              -1 => "Ammo",              -41 => "Pyrite",            -61 => "Steam Pressure",   -101 => "Heat",             -121 => "Ooze",             -141 => "Blood Power",
            -142 => "Wrath"
        ),
        'relItems'      => array(
            'base'    => "<small>Show %s related to <b>%s</b></small>",
            'link'    => "或",
            'recipes' => '<a href="?items=9.%s">recipe items</a>',
            'crafted' => '<a href="?items&filter=cr=86;crs=%s;crv=0">crafted items</a>'
        ),
        'cat'           => array(                           // as per menu in locale_enus.js
              7 => "职业技能",                          // classList
            -13 => "雕文",                                // classList
            -11 => array("精通", 8 => "护甲", 6 => "武器", 10 => "语言"),
             -4 => "种族特长",
             -2 => "天赋",                               // classList
             -6 => "小伙伴", //Companions
             -5 => "坐骑",
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
            0x02A5F3 => "近战武器",             0x0060 => "Shield",                     0x04000C => "Ranged Weapon",            0xA091 => "单手近战武器"
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
        'unkEffect'     => '未知效果',  //Unknown Effect
        'effects'       => array(
/*0-5    */ '无',                     'Instakill',                'School Damage',            'Dummy',                    'Portal Teleport',          'Teleport Units',
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
        'unkAura'       => '未知光环',  //Unknown Aura
        'auras'         => array(
/*0-   */   '无',                                 'Bind Sight',                           'Mod Possess',                          'Periodic Damage',                      'Dummy',
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
        'notFound'      => "这个物品不存在。",
        'armor'         => "%s护甲",                      // ARMOR_TEMPLATE
        'block'         => "%s Block",                      // SHIELD_BLOCK_TEMPLATE
        'charges'       => "%d |4Charge:Charges;",          // ITEM_SPELL_CHARGES
        'locked'        => "Locked",                        // LOCKED
        'ratingString'  => "%s&nbsp;@&nbsp;L%s",
        'heroic'        => "英雄",                        // ITEM_HEROIC
        'startQuest'    => "This Item Begins a Quest",      // ITEM_STARTS_QUEST
        'bagSlotString' => "%d Slot %s",                    // CONTAINER_SLOTS
        'fap'           => "Feral攻击强度",
        'durability'    => "耐久度 %d / %d",            // DURABILITY_TEMPLATE
        'realTime'      => "real time",
        'conjured'      => "Conjured Item",                 // ITEM_CONJURED
        'sellPrice'     => "出售价格",                    // SELL_PRICE
        'itemLevel'     => "物品等级%d",                 // ITEM_LEVEL
        'randEnchant'   => "&lt;随机附魔&gt",     // ITEM_RANDOM_ENCHANT
        'readClick'     => "&lt;右击以读取&gt",    // ITEM_READABLE
        'openClick'     => "&lt;右击以打开&gt",    // ITEM_OPENABLE
        'setBonus'      => "(%d)件：%s",                  // ITEM_SET_BONUS_GRAY
        'setName'       => "%s (%d/%d)",                    // ITEM_SET_NAME
        'partyLoot'     => "Party loot",
        'smartLoot'     => "Smart loot",
        'indestructible'=> "不能被摧毁",
        'deprecated'    => "过时的",
        'useInShape'    => "Usable when shapeshifted",
        'useInArena'    => "Usable in arenas",
        'refundable'    => "可退还的",
        'noNeedRoll'    => "Cannot roll Need",
        'atKeyring'     => "Can be placed in the keyring",
        'worth'         => "Worth",
        'consumable'    => "消耗品",
        'nonConsumable' => "Non-consumable",
        'accountWide'   => "Account-wide",
        'millable'      => "Millable",                      // ITEM_MILLABLE
        'noEquipCD'     => "No equip cooldown",
        'prospectable'  => "Prospectable",                  // ITEM_PROSPECTABLE
        'disenchantable'=> "分解",                // ITEM_DISENCHANT_ANY_SKILL
        'cantDisenchant'=> "不能分解",        // ITEM_DISENCHANT_NOT_DISENCHANTABLE
        'repairCost'    => "修理费用",                   // REPAIR_COST
        'tool'          => "Tool",
        'cost'          => "成本",                          // COSTS_LABEL  //Cost
        'content'       => "内容",
        '_transfer'     => 'This item will be converted to <a href="?item=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a> if you transfer to <span class="icon-%s">%s</span>.',
        '_unavailable'  => "这个物品对玩家不可用。",
        '_rndEnchants'  => "随机附魔",  //Random Enchantments
        '_chance'       => "（%s%%几率）",
        'slot'          => "Slot",
        '_quality'      => "Quality",                       // QUALITY
        'usableBy'      => "Usable by",
        'buyout'        => "Buyout price",                  // BUYOUT_PRICE
        'each'          => "each",
        'tabOther'      => "Other",
        'reqMinLevel'   => "需要等级 %d",             // ITEM_MIN_LEVEL
        'reqLevelRange' => "需要等级 %d 到 %d (%s)",  // ITEM_LEVEL_RANGE_CURRENT
        'unique'        => ["唯一",          "唯一(%d)", "唯一：%s (%d)"         ],   // ITEM_UNIQUE, ITEM_UNIQUE_MULTIPLE, ITEM_LIMIT_CATEGORY
        'uniqueEquipped'=> ["装备唯一", null,          "装备唯一：%s (%d)"],   // ITEM_UNIQUE_EQUIPPABLE, null, ITEM_LIMIT_CATEGORY_MULTIPLE
        'speed'         => "速度",                         // SPEED
        'dps'           => "(%.1f伤害每秒)",      // DPS_TEMPLATE
        'damage'        => array(                           // *DAMAGE_TEMPLATE*
                        //  basic,                          basic /w school,                add basic,                  add basic /w school
            'single'    => ["%d 伤害",                    "%d %s 伤害",                 "+ %d 伤害",              "+%d %s 伤害"             ],
            'range'     => ["%d - %d 伤害",               "%d - %d %s 伤害",            "+ %d - %d 伤害",         "+%d - %d %s 伤害"        ],
            'ammo'      => ["Adds %g damage per second",    "Adds %g %s damage per second", "+ %g damage per second",   "+ %g %s damage per second" ]
        ),
        'gems'          => "宝石",
        'socketBonus'   => "插槽奖励",                  // ITEM_SOCKET_BONUS
        'socket'        => array(                           // EMPTY_SOCKET_*
            "多彩插槽",          "红色插槽",       "黄色插槽",        "蓝色插槽",          -1 => "棱彩插槽"
        ),
        'gemColors'     => array(                           // *_GEM
            "多彩",                 "红色",              "黄色",               "蓝色"
        ),
        'gemConditions' => array(                           // ENCHANT_CONDITION_* in GlobalStrings.lua
            2 => "less than %d %s |4gem:gems;",
            3 => "more %s gems than %s gems",
            5 => "at least %d %s |4gem:gems;"
        ),
        'reqRating'     => array(                           // ITEM_REQ_ARENA_RATING*
            "需要个人和竞技场队伍等级达到%d",   //Requires personal and team arena rating of 
            "需要个人和竞技场队伍等级达到%d|n在3v3或5v5 brackets",
            "需要个人和竞技场队伍等级达到%d|n在5v5 brackets"
        ),
        'quality'       => array(                           // ITEM_QUALITY?_DESC
            "粗糙",                   "普通",               "优秀",                   "精良",
            "史诗",                   "传说",               "神器",                   "传家宝"
        ),
        'trigger'       => array(                           // ITEM_SPELL_TRIGGER_*
            "使用： ",                 "装备： ",             "击中时可能： ",              "",                             "",
            "",                     ""
        ),
        'bonding'       => array(                           // ITEM_BIND_*
            "账号绑定",                                     "拾取后绑定",                                                "装备后绑定",
            "使用后绑定",                                    "任务物品",                                                 "任务物品"
        ),
        "bagFamily"     => array(                           // ItemSubClass.dbc/1
            "容器",                   "箭袋",               "弹药袋",                  "灵魂袋",                          "制皮材料包",
            "铭文包",                  "草药袋",              "附魔材料袋",                "工程学材料袋",                       null, /*Key*/
            "宝石袋",                  "矿石袋"
        ),
        'inventoryType' => array(                           // INVTYPE_*
            null,                   "头部",               "颈部",                   "肩部",                           "衬衣",
            "胸部",                   "腰部",               "腿部",                   "脚",                            "手腕",
            "手",                    "手指",               "饰品",                   "单手",                           "副手", /*Shield*/
            "远程",                   "背部",               "双手",                   "背包",                           "战袍",
            null, /*Robe*/          "主手",               "副手",                   "副手物品",                         "弹药",
            "投掷",                   null, /*Ranged2*/   "箭袋",                   "圣物"
        ),
        'armorSubClass' => array(                           // ItemSubClass.dbc/2
            "其它",                   "布甲",               "皮甲",                   "锁甲",                           "板甲",
            null,                   "盾牌",               "圣契",                   "神像",                           "图腾",
            "魔印"
        ),
        'weaponSubClass'=> array(                           // ItemSubClass.dbc/4
            "斧",                    "斧",                "弓",                    "枪械",                           "锤",
            "锤",                    "长柄武器",             "剑",                    "剑",                            null,
            "法杖",                   null,               null,                   "拳套",                           "其它",
            "匕首",                   "投掷武器",             null,                   "弩",                            "魔杖",
            "鱼竿"
        ),
        'projectileSubClass' => array(                      // ItemSubClass.dbc/6
            null,                   null,               "箭",                   "子弹",                         null
        ),
        'elixirType'    => [null, "Battle", "Guardian"],
        'cat'           => array(                           // ordered by content first, then alphabeticaly; item menu from locale_enus.js
             2 => "武器",                                // self::$spell['weaponSubClass']
             4 => array("护甲", array(
                 1 => "布甲",                 2 => "皮甲",           3 => "锁甲",              4 => "板甲",             6 => "盾牌",                 7 => "圣契",
                 8 => "圣像",                       9 => "图腾",                 10 => "魔印",                 -6 => "斗篷",                 -5 => "副手",        -8 => "衬衫",
                -7 => "战袍",                    -3 => "项链",                -2 => "戒指",                  -4 => "饰品",                0 => "Miscellaneous (Armor)",
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
            16 => array("雕文", array(
                 1 => "战士雕文",              2 => "圣骑士雕文",          3 => "猎人雕文",           4 => "潜行者雕文",            5 => "牧师雕文",           6 => "死亡骑士雕文",
                 7 => "萨满祭司雕文",               8 => "法师雕文",             9 => "术士雕文",         11 => "德鲁伊雕文"
            )),
             7 => array("贸易货物", array(
                14 => "护甲附魔",          5 => "Cloth",                   3 => "Devices",                10 => "Elemental",              12 => "Enchanting",              2 => "Explosives",
                 9 => "Herbs",                       4 => "Jewelcrafting",           6 => "Leather",                13 => "Materials",               8 => "Meat",                    7 => "Metal & Stone",
                 1 => "Parts",                      15 => "Weapon Enchantments",    11 => "Other (Trade Goods)"
             )),
             6 => ["弹药", [                  2 => "Arrows",                  3 => "Bullets"     ]],
            11 => ["Quivers",     [                  2 => "Quivers",                 3 => "Ammo Pouches"]],
             9 => array("配方", array(
                 0 => "Books",                       6 => "Alchemy Recipes",         4 => "Blacksmithing Plans",     5 => "Cooking Recipes",         8 => "Enchanting Formulae",     3 => "Engineering Schematics",
                 7 => "First Aid Books",             9 => "Fishing Books",          11 => "Inscription Techniques", 10 => "Jewelcrafting Designs",   1 => "Leatherworking Patterns",12 => "Mining Guides",
                 2 => "Tailoring Patterns"
            )),
             3 => array("宝石", array(
                 6 => "多彩宝石",                   0 => "红色宝石",                1 => "蓝色宝石",               2 => "黄色宝石",             3 => "紫色宝石",             4 => "绿色宝石",
                 5 => "橙色",                 8 => "棱彩宝石",          7 => "Simple宝石"
            )),
            15 => array("杂项", array(
                -2 => "护甲兑换",                3 => "节日",                 0 => "垃圾",                    1 => "施法材料",                5 => "坐骑",                 -7 => "飞行坐骑",
                 2 => "小宠物",                  4 => "其他"
            )),
            10 => "货币",
            12 => "任务",
            13 => "钥匙",
        ),
        'statType'      => array(                           // ITEM_MOD_*
            "法力值",
            "生命值",
            null,
            "敏捷",
            "力量",
            "智力",
            "精神",
            "耐力",
            null, null, null, null,
            "防御等级提高%d。",
            "使你的躲闪等级提高%d。",
            "使你的招架等级提高%d。",
            "使你的盾牌格挡等级提高%d。",
            "近战命中等级提高%d。",
            "远程命中等级提高%d。",
            "法术命中等级提高%d。",
            "近战爆击等级提高%d。",
            "远程爆击等级提高%d。",
            "法术爆击等级提高%d。",
            "近战命中躲闪等级提高%d。",
            "远程命中躲闪等级提高%d。",
            "法术命中躲闪等级提高%d。",
            "近战爆击躲闪等级提高%d。",
            "远程爆击躲闪等级提高%d。",
            "法术爆击躲闪等级提高%d。",
            "近战急速等级提高%d。",
            "远程急速等级提高%d。",
            "法术急速等级提高%d。",
            "命中等级提高%d。",
            "爆击等级提高%d。",
            "命中躲闪等级提高%d。",
            "爆击躲闪等级提高%d。",
            "韧性等级提高%d。",
            "急速等级提高%d。",
            "使你的精准等级提高%d。",
            "攻击强度提高%d点。",
            "远程攻击强度提高%d点。",
            "在猎豹、熊、巨熊和枭兽形态下的攻击强度提高%d点。",
            "法术和魔法效果的伤害量提高最多%d点。",
            "法术和魔法效果的治疗量提高最多%d点。",
            "每5秒回复%d点法力值。",
            "使你的护甲穿透等级提高%d。",
            "法术强度提高%d点。",
            "每5秒恢复%d点生命值。",
            "法术穿透提高%d。",
            "使你的盾牌格挡值提高%d。",
            "Unknown Bonus #%d (%d)",
        )
    )
);

?>
