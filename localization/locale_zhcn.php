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
            ["无", "none"],         ["Slot", "slot"],       ["等级", "level"],     ["来源", "source"]
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
        'dateFmtLong'   => "Y/m/d H:i",  //Y/m/d \a\\t H:i

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
            'note'    => "注意: 你的截图显示在网站前需要审核。这需要最多72小时。"
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
            "研磨",                       "采矿",                        "勘探",                   "偷窃",                 "回收",                     "剥皮",
            "游戏内商城"
        ),
        'languages'     => array(
             1 => "兽人语",                  2 => "达纳苏斯语",              3 => "牛头人语",                 6 => "矮人语",                7 => "通用语",                  8 => "恶魔语",
             9 => "泰坦语",                  10 => "萨拉斯语",             11 => "龙语",               12 => "卡利姆多语",                13 => "侏儒语",                14 => "巨魔语",
            33 => "亡灵语",            35 => "德莱尼语",                36 => "僵尸语",                 37 => "侏儒二进制",         38 => "地精二进制"
        ),
        'gl'            => [null, "主要", "次要"],                                                                                                                               // MAJOR_GLYPH, MINOR_GLYPH
        'si'            => [1 => "联盟", -1 => "仅限联盟", 2 => "部落", -2 => "仅限部落", 3 => "双方"],
        'resistances'   => [null, '神圣抗性', '火焰抗性', '自然抗性', '冰霜抗性', '暗影抗性', '奥术抗性'],                         // RESISTANCE?_NAME
        'dt'            => [null, "魔法", "诅咒", "疾病", "中毒", "潜行", "隐形", null, null, "激怒"],                                                          // SpellDispalType.dbc
        'sc'            => ["物理", "神圣", "火焰", "自然", "冰霜", "暗影", "奥术"],                                                                                     // STRING_SCHOOL_*
        'cl'            => [null, "战士", "圣骑士", "猎人", "潜行者", "牧师", "死亡骑士", "萨满祭司", "法师", "术士", null, "德鲁伊"],                                   // ChrClasses.dbc
        'ra'            => [-2 => "部落", -1 => "联盟", "双方", "人类", "兽人", "矮人", "暗夜精灵", "亡灵", "牛头人", "侏儒", "巨魔", null, "血精灵", "德莱尼"],     // ChrRaces.dbc
        'rep'           => ["仇恨", "敌对", "冷淡", "中立", "友善", "尊敬", "崇敬", "崇拜"],                                                              // FACTION_STANDING_LABEL*
        'st'            => array(                           // SpellShapeshiftForm.dbc // with minor deviations on 27, 28  形态=form
            "Default",                      "猎豹形态",                     "生命之树形态",                 "旅行形态",                  "水生形态",                 "熊形态",
            "小动物",                      "食尸鬼",                        "巨熊形态",               "斯蒂文的食尸鬼",                "萨隆亚的骷髅",           "Darkmoon - Test of Strength",
            "BLB Player",                   "暗影之舞",                  "Creature - Bear",              "Creature - Cat",               "幽魂之狼",                    "战斗姿态",
            "防御姿态",             "狂暴姿态",             "测试",                         "僵尸",                       "恶魔变身",                null,
            null,                           "亡灵",                       "狂乱",                       "史诗飞行形态",            "暗影形态",                  "飞行形态",
            "潜行",                      "枭兽形态",                 "救赎之魂"
        ),
        'me'            => array(                           // SpellMechanic.dbc .. not quite
            null,                           "被魅惑",                      "迷惑",                  "被缴械",                     "被吸引",                   "逃跑",
            "笨拙",                      "被定身",                       "平静",                     "沉默",                     "沉睡",                       "诱捕",
            "昏迷",                      "冻结",                       "瘫痪",                "流血",                     "治疗",                      "被变形",
            "被放逐",                     "被防护",                     "被禁锢",                     "骑乘",                      "被诱惑",                      "转向",
            "惊骇",                    "无敌",                 "被打断",                  "眩晕",                        "被发现",                    "无敌",
            "被闷棍",                       "激怒"
        ),
        'ct'            => array(                           // CreatureType.dbc
            "未分类",                "野兽",                        "龙类",                    "恶魔",                        "元素生物",                    "巨人",
            "亡灵",                       "人型生物",                     "小动物",                      "机械",                   "未指定",                "图腾",
            "非战斗宠物",               "气体云雾"
        ),
        'fa'            => array(                           // CreatureFamily.dbc
             1 => "狼",                    2 => "豹",                     3 => "蜘蛛",                  4 => "熊",                    5 => "野猪",                    6 => "鳄鱼",
             7 => "食腐鸟",            8 => "螃蟹",                    9 => "猩猩",                11 => "迅猛龙",                 12 => "陆行鸟",            20 => "蝎子",
            21 => "海龟",                 24 => "蝙蝠",                    25 => "土狼",                  26 => "猛禽",           27 => "风蛇",           30 => "龙鹰",
            31 => "掠食者",                32 => "迁跃捕猎者",           33 => "孢子蝠",               34 => "虚空鳐",             35 => "蛇",                37 => "蛾子",
            38 => "奇美拉",               39 => "魔暴龙",              41 => "异种虫",               42 => "蠕虫",                   43 => "犀牛",                  44 => "巨蜂",
            45 => "熔岩犬",             46 => "灵魂兽"
        ),
        'pvpRank'       => array(                           // PVP_RANK_\d_\d(_FEMALE)?
            null,                                                           "下士 / 斥候",                                              "下士 / 步兵",
            "中士 / 中士",                                          "军士长 / 高阶军士",                            "士官长 / 一等军士长",
            "骑士 / 石头守卫",                                         "骑士中尉 / 血卫士",                              "骑士队长 / 军团士兵",
            "护卫骑士 / 百夫长",                                  "少校 / 勇士",                              "司令 / 中将",
            "统帅 / 将军",                                            "元帅 / 督军",                                      "大元帅 / 高阶督军"
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
        'recoverUser'   => "用户名需求",
        'recoverPass'   => "密码重置：步骤 %s / 2",
        'newPass'       => "新密码",

        // creation
        'register'      => "注册 - 步骤 %s / 2",
        'passConfirm'   => "确认密码",

        // dashboard
        'ipAddress'     => "IP地址",
        'lastIP'        => "上次使用IP地址",
        'myAccount'     => "我的账号",
        'editAccount'   => "只需使用以下表格就能更新你的帐户信息",
        'viewPubDesc'   => '在你的<a href="?user=%s">简介页面</a>查看你公共描述',

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
        'accActivated'  => '你的帐户已被激活。<br>继续<a href="?account=signin&token=%s">登录</a>',
        'userNotFound'  => "输入的用户名不存在。",
        'wrongPass'     => "密码无效。",
        // 'accInactive'   => "That account has not yet been confirmed active.",
        'loginExceeded' => "这个IP最大登录次数已超过。请在%s后再次尝试。",
        'signupExceeded'=> "这个IP最大注册次数已超过。请在%s后再次尝试。",
        'errNameLength' => "你的用户名必须至少4个字符长度。", // message_usernamemin
        'errNameChars'  => "你的用户名只能包含字母和数字。", // message_usernamenotvalid
        'errPassLength' => "你的密码必须至少6个字符长度。", // message_passwordmin
        'passMismatch'  => "你输入的密码不匹配。",
        'nameInUse'     => "用户名已被占用。",
        'mailInUse'     => "该电子邮件已注册到一个帐户。",
        'isRecovering'  => "此帐户已恢复。按照电子邮件中的说明或等待%s后令牌过期。",
        'passCheckFail' => "密码不匹配。", // message_passwordsdonotmatch
        'newPassDiff'   => "你的新密码必须与以前的密码不同。" // message_newpassdifferent
    ),
    'user' => array(
        'notFound'      => "用户 \"%s\" 未找到",
        'removed'       => "(已移除)",
        'joinDate'      => "加入",
        'lastLogin'     => "上次访问",
        'userGroups'    => "角色", //Role
        'consecVisits'  => "Consecutive visits",
        'publicDesc'    => "公共描述", //Public Description
        'profileTitle'  => "%s的简介",
        'contributions' => "Contributions",
        'uploads'       => "Data uploads",
        'comments'      => "评论",
        'screenshots'   => "截图",
        'videos'        => "视频",
        'posts'         => "Forum posts"
    ),
    'mail' => array(
        'tokenExpires'  => "此令牌将在%s过期。",
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
        'cat'           => [0 => "其他", 9 => "书籍", 3 => "容器", -5 => "宝箱", 25 => "钓鱼水池", -3 => "草药", -4 => "矿脉", -2 => "任务", -6 => "工具"],
        'type'          => [              9 => "书籍",  3 => "容器",  -5 => "宝箱",  25 => "",              -3 => "草药",  -4 => "矿脉",  -2 => "任务", -6 => ""],
        'unkPosition'   => "这个对象的位置未知。",
        'npcLootPH'     => '这个<b>%s</b>包含战利品，与<a href="?npc=%d">%s</a>的作战后，在他/她/它死亡后刷新。',
        'key'           => "Key",
        'focus'         => "Spell Focus",
        'focusDesc'     => "Spells requiring this Focus can be cast near this Object",
        'trap'          => "Trap",
        'triggeredBy'   => "触发由",  //Triggered by
        'capturePoint'  => "Capture Point",
        'foundIn'       => "这个对象能在以下地区找到：",
        'restock'       => "Restocks every %s."
    ),
    'npc' => array(
        'notFound'      => "这个NPC不存在。",
        'classification'=> "分类",   //Classification
        'petFamily'     => "宠物家族",
        'react'         => "反应",  //React
        'worth'         => "价值",  //Worth
        'unkPosition'   => "这个NPC的位置未知。",
        'difficultyPH'  => "这个NPC是不同模式下的占位符，是",
        'seat'          => "Seat",
        'accessory'     => "附件",
        'accessoryFor'  => "这个NPC是载具的附件",
        'quotes'        => "引用",  //Quotes
        'gainsDesc'     => "杀死这个NPC后你将得到",
        'repWith'       => "点声望点数在", //reputation with
        'stopsAt'       => "在%s停止",
        'vehicle'       => "载具",  //Vehicle
        'stats'         => "状态",  //Stats
        'melee'         => "近战", //Melee
        'ranged'        => "远程", //Ranged
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
            "未分类",            "野兽",                   "龙类",               "恶魔",                   "元素",               "巨人",                   "亡灵",                   "人型生物",
            "小动物",                 "机械",              "未指定",            "图腾",                   "非战斗宠物",          "气体云雾"
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
        'outOf'         => "/", //out of
        'criteriaType'  => "Criterium Type-Id:",
        'itemReward'    => "你将得到",
        'titleReward'   => '你将被授予头衔"<a href="?title=%d">%s</a>"',
        'slain'         => "杀死", //slain
        'reqNumCrt'     => "要求",
        'rfAvailable'   => "Available on realm: ",
        '_transfer'     => '这个成就将被转换到<a href="?achievement=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a>，如果你转移到<span class="icon-%s">%s</span>。',
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
        'Instances'     => "副本",  //Instances
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
        'raidFaction'   => "团队副本阵营", //Raid faction
        'boss'          => "最终首领",
        'reqLevels'     => "要求等级： [tooltip=instancereqlevel_tip]%d[/tooltip], [tooltip=lfgreqlevel_tip]%d[/tooltip]",
        'zonePartOf'    => "这个区域是[zone=%s]的一部分。",
        'autoRez'       => "自动复活",
        'city'          => "城市",
        'territory'     => "领地",
        'instanceType'  => "副本类型",
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
        'provided'      => "提供的", //Provided
        'providedItem'  => "提供的物品",
        'completion'    => "完成",
        'description'   => "描述",
        'playerSlain'   => "玩家被杀",  //Players slain
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
        'reqQDesc'      => "要完成这个任务，你必须完成所有这些任务",
        'reqOneQ'       => "需要其中之一",
        'reqOneQDesc'   => "要完成此任务，必须完成下列任务之一",
        'opensQ'        => "打开任务",
        'opensQDesc'    => "完成此任务需要接取到这个任务",
        'closesQ'       => "关闭任务",
        'closesQDesc'   => "完成此任务后，您将无法接取到这些任务",
        'enablesQ'      => "可用",
        'enablesQDesc'  => "当这个任务是活跃的，这些任务也可用",
        'enabledByQ'    => "启用自",
        'enabledByQDesc'=> "只有当这些任务中的一个活跃时，这个任务才可用",
        'gainsDesc'     => "完成这个任务后，你将获得",
        'theTitle'      => '头衔 "%s"',                                        // partly REWARD_TITLE
        'mailDelivery'  => "你会收到这封信%s%s",
        'mailBy'        => '由<a href="?npc=%d">%s</a>所写',
        'mailIn'        => " after %s",
        'unavailable'   => "这项任务已被标记为过时，无法获得或完成。",
        'experience'    => "经验",
        'expConvert'    => "（或%s如果在等级%d完成）",
        'expConvert2'   => "%s如果在等级%d完成",
        'chooseItems'   => "你可以从这些奖励品中选择一件",       // REWARD_CHOICES
        'receiveItems'  => "你将得到",                                      // REWARD_ITEMS_ONLY
        'receiveAlso'   => "你还将得到",                                 // REWARD_ITEMS
        'spellCast'     => "该法术将被施放在你身上",               // REWARD_AURA
        'spellLearn'    => "你将学会",                                        // REWARD_SPELL
        'bonusTalents'  => "%d天赋|4点数:点数;",                             // partly LEVEL_UP_CHAR_POINTS
        'spellDisplayed'=> ' (<a href="?spell=%d">%s</a> is displayed)',
        'attachment'    => "附件",
        'questInfo'     => array(
             0 => "普通",              1 => "组队",              21 => "传记",               41 => "PvP",                62 => "团队副本",               81 => "地下城",            82 => "世界事件",
            83 => "传说",          84 => "护送",             85 => "英雄",             88 => "团队副本(10)",          89 => "团队副本(25)"
        ),
        'cat'           => array(
            0 => array( "东部王国",
                  36 => "奥特兰克山脉",              45 => "阿拉希高地",                3 => "荒芜之地",                       25 => "黑石山",              4 => "诅咒之地",
                  46 => "燃烧平原",               279 => "达拉然巨坑",                 41 => "逆风小径",                2257 => "矿道地铁",                    1 => "丹莫罗",
                  10 => "暮色森林",                      139 => "东瘟疫之地",            12 => "艾尔文森林",                3430 => "永歌森林",               3433 => "幽魂之地",
                 267 => "希尔斯布莱德丘陵",          1537 => "铁炉堡",                    4080 => "奎尔丹纳斯岛",             38 => "洛克莫丹",                     44 => "赤脊山",
                  51 => "灼热峡谷",                3487 => "银月城",               130 => "银松森林",            1519 => "暴风城",                 33 => "荆棘谷",
                   8 => "悲伤沼泽",               47 => "辛特兰",              4298 => "东瘟疫之地：血色领地",            85 => "提瑞斯法林地",              1497 => "幽暗城",
                  28 => "西瘟疫之地",            40 => "西部荒野",                       11 => "湿地"
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
                3959 => "黑暗神殿",                 2677 => "黑翼之巢",               3923 => "格鲁尔的巢穴",                 3606 => "Hyjal Summit",                 4812 => "Icecrown Citadel",
                3457 => "Karazhan",                     3836 => "Magtheridon's Lair",           2717 => "Molten Core",                  3456 => "Naxxramas",                    2159 => "Onyxia's Lair",
                3429 => "Ruins of Ahn'Qiraj",           3607 => "Serpentshrine Cavern",         4075 => "Sunwell Plateau",              3428 => "Temple of Ahn'Qiraj",          3842 => "The Eye",
                4500 => "The Eye of Eternity",          4493 => "The Obsidian Sanctum",         4722 => "Trial of the Crusader",        4273 => "Ulduar",                       4603 => "Vault of Archavon",
                3805 => "Zul'Aman",                     1977 => "Zul'Gurub"
            ),
            4 => array( "职业",
                -372 => "死亡骑士",                 -263 => "德鲁伊",                        -261 => "猎人",                       -161 => "法师",                         -141 => "圣骑士",
                -262 => "牧师",                       -162 => "潜行者",                         -82 => "萨满祭司",                        -61 => "术士",                       -81 => "战士"
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
        '_transfer'     => '这个头衔将被转换到<a href="?title=%d" class="q1">%s</a>，如果你转移到<span class="icon-%s">%s</span>。',
        'cat'           => array(
            "一般",      "PvP",    "声望",       "地下城与团队",     "任务",       "专业",      "世界事件"
        )
    ),
    'skill' => array(
        'notFound'      => "这个技能不存在。",
        'cat'           => array(
            -6 => "小伙伴",         -5 => "坐骑",             -4 => "种族特长",      5 => "属性",          6 => "武器技能",       7 => "职业技能",        8 => "护甲精通",
             9 => "辅助专业",   10 => "语言",          11 => "专业"
        )
    ),
    'currency' => array(
        'notFound'      => "这个货币不存在。",
        'cap'           => "总共上限", //Total cap
        'cat'           => array(
            1 => "其它", 2 => "PvP", 4 => "经典旧世", 21 => "巫妖王之怒", 22 => "地下城与团队", 23 => "燃烧的远征", 41 => "测试", 3 => "未使用"
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
            null,              "法术",            "用户界面", "脚步",   "武器碰撞", null,      "武器格挡", null,            null,         "拿起/放下",
            "NPC攻击",      null,                "错误",         "自然",      "对象",         null,      "死亡",          "NPC问候", null,         "护甲",
            "Footstep Splash", "水(角色)", "水",          "交易技能", "环境气氛",   "装饰物", "法术失败",   "NPC循环",     "区域音乐", "表情",
            "叙事音乐", "叙事",         50 => "区域气氛", 52 => "发射器", 53 => "载具", 1000 => "我的播放列表"
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
        'spillover'     => "声望额外效果",
        'spilloverDesc' => "获得这个阵营的声望也将按比例获得下列阵营的声望。", //Gaining reputation with this faction also yields a proportional gain with the factions listed below.
        'maxStanding'   => "最大关系",
        'quartermaster' => "军需官",
        'customRewRate' => "自定义奖励率",
        '_transfer'     => '这个阵营的声望将被转换到<a href="?faction=%d" class="q1">%s</a>，如果你转移到<span class="icon-%s">%s</span>。',
        'cat'           => array(
            1118 => ["经典旧世", 469 => "联盟", 169 => "热砂港", 67 => "部落", 891 => "联盟部队", 892 => "部落部队"],
            980  => ["燃烧的远征", 936 => "沙塔斯城"],
            1097 => ["巫妖王之怒", 1052 => "部落先遣军", 1117 => "索拉查盆地", 1037 => "联盟先遣军"],
            0    => "其他"
        )
    ),
    'itemset' => array(
        'notFound'      => "这个物品套装不存在。",
        '_desc'         => "<b>%s</b>是<b>%s</b>。它包含%s件。",
        '_descTagless'  => "<b>%s</b>是物品套装包含%s件。",
        '_setBonuses'   => "套装奖励",
        '_conveyBonus'  => "穿更多这个套装的部分将会提供给你角色奖励。", //Wearing more pieces of this set will convey bonuses to your character.
        '_pieces'       => "件",  //pieces
        '_unavailable'  => "这个物品套装对玩家不可用。",
        '_tag'          => "标签",
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
            null,               "布甲",                "皮甲",              "锁甲",                     "板甲",                    "匕首",                   "戒指",
            "拳套",      "单手斧",       "单手锤",      "单手剑",         "饰品",                  "Amulet"
        )
    ),
    'spell' => array(
        'notFound'      => "这个法术不存在。",
        '_spellDetails' => "法术细节", //Spell Details
        '_cost'         => "花费", //Cost
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
        '_interval'     => "Interval", //Interval
        '_inSlot'       => "in slot",
        '_collapseAll'  => "折叠全部",
        '_expandAll'    => "展开全部",
        '_transfer'     => '这个法术将被转换到<a href="?spell=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a>，如果你转移到<span class="icon-%s">%s</span>。',
        'discovered'    => "Learned via discovery",
        'ppm'           => "%s每分钟触发几率",
        'procChance'    => "触发几率",
        'starter'       => "Starter spell",
        'trainingCost'  => "训练成本",
        'remaining'     => "持续%s",                  // SPELL_TIME_REMAINING_*  //remaining
        'untilCanceled' => "直到主动取消",               // SPELL_DURATION_UNTIL_CANCELLED
        'castIn'        => "%s秒施法时间",                   // SPELL_CAST_TIME_SEC
        'instantPhys'   => "瞬发",                       // SPELL_CAST_TIME_INSTANT_NO_MANA
        'instantMagic'  => "瞬发",                  // SPELL_CAST_TIME_INSTANT
        'channeled'     => "需引导",                     // SPELL_CAST_CHANNELED
        'range'         => "%s码范围",                   // SPELL_RANGE / SPELL_RANGE_DUAL
        'meleeRange'    => "近战范围",                   // MELEE_RANGE
        'unlimRange'    => "无限范围",               // SPELL_RANGE_UNLIMITED
        'reagents'      => "材料",                      // SPELL_REAGENTS
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
        'powerRunes'    => ["冰霜", "邪恶", "鲜血", "死亡"], // RUNE_COST_* / COMBAT_TEXT_RUNE_*
        'powerTypes'    => array(
            // conventional - HEALTH, MANA, RAGE, FOCUS, ENERGY, HAPPINESS, RUNES, RUNIC_POWER / *_COST / *COST_PER_TIME
              -2 => "生命值",              0 => "法力值",                1 => "怒气",                2 => "集中",               3 => "能量",              4 => "快乐值",
               5 => "符文",               6 => "符文能量",
            // powerDisplay - PowerDisplay.dbc -> GlobalStrings.lua POWER_TYPE_*
              -1 => "Ammo",              -41 => "Pyrite",            -61 => "Steam Pressure",   -101 => "Heat",             -121 => "Ooze",             -141 => "Blood Power",
            -142 => "Wrath"
        ),
        'relItems'      => array(
            'base'    => "<small>Show %s related to <b>%s</b></small>",
            'link'    => "或",
            'recipes' => '<a href="?items=9.%s">制作物品</a>',
            'crafted' => '<a href="?items&filter=cr=86;crs=%s;crv=0">手工制作物品</a>'
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
                "宠物技能",               782 => "食尸鬼",             270 => "通用",               653 => "蝙蝠",                       210 => "熊",                  655 => "猛禽",          211 => "野猪",
                213 => "食腐鸟",      209 => "豹",               780 => "奇美拉",              787 => "熔岩犬",                214 => "螃蟹",                  212 => "鳄鱼",             781 => "魔暴龙",
                763 => "龙鹰",        215 => "猩猩",           654 => "土狼",                 775 => "蛾子",                      764 => "虚空鳐",            217 => "迅猛龙",                767 => "掠食者",
                786 => "犀牛",             236 => "蝎子",           768 => "蛇",               783 => "异种虫",                  203 => "蜘蛛",                788 => "灵魂兽",          765 => "孢子蝠",
                218 => "陆行鸟",       251 => "海龟",            766 => "迁跃捕猎者",          785 => "巨蜂",                      656 => "风蛇",          208 => "狼",                  784 => "蠕虫",
                761 => "恶魔卫士",          189 => "地狱猎犬",         188 => "小鬼",                   205 => "魅魔",                  204 => "虚空行者"
            ),
             -7 => ["宠物天赋", 410 => "狡诈", 411 => "狂野", 409 => "坚韧"],
             11 => array(
                "专业",
                171 => "炼金术",
                164 => ["锻造", 9788 => "防具锻造", 9787 => "武器锻造", 17041 => "大师级铸斧", 17040 => "大师级铸锤", 17039 => "大师级铸剑"],
                333 => "附魔",
                202 => ["工程学", 20219 => "侏儒工程学", 20222 => "地精工程学"],
                182 => "草药学",
                773 => "铭文",
                755 => "珠宝加工",
                165 => ["制皮", 10656 => "龙鳞制皮", 10658 => "元素制皮", 10660 => "部族制皮"],
                186 => "采矿",
                393 => "剥皮",
                197 => ["裁缝", 26798 => "月布裁缝", 26801 => "暗纹裁缝", 26797 => "魔焰裁缝"],
            ),
              9 => ["辅助专业", 185 => "烹饪", 129 => "急救", 356 => "钓鱼", 762 => "骑术"],
             -8 => "NPC能力",
             -9 => "GM能力",
              0 => "未分类"
        ),
        'armorSubClass' => array(                           // ItemSubClass.dbc/2
            "杂项",                        "布甲",                          "皮甲",                        "锁甲",                           "板甲",
            null,                                   "盾牌",                              "圣契",                              "神像",                                "图腾",
            "魔印"
        ),
        'weaponSubClass' => array(                          // ItemSubClass.dbc/4; ordered by content firts, then alphabeticaly
            15 => "匕首",                        13 => "拳套",                    0 => "单手斧",                 4 => "单手杖",                7 => "单手剑",
             6 => "长柄武器",                       10 => "法杖",                          1 => "双手斧",                 5 => "双手锤",                8 => "双手剑",
             2 => "弓",                           18 => "弩",                       3 => "枪",                           16 => "投掷",                         19 => "魔杖",
            20 => "鱼竿",                  14 => "杂项"
        ),
        'subClassMasks' => array(
            0x02A5F3 => "近战武器",             0x0060 => "盾牌",                     0x04000C => "远程武器",            0xA091 => "单手近战武器"
        ),
        'traitShort'    => array(
            'atkpwr'    => "AP",                    'rgdatkpwr' => "RAP",                   'splpwr'    => "SP",                    'arcsplpwr' => "ArcP",                  'firsplpwr' => "FireP",
            'frosplpwr' => "FroP",                  'holsplpwr' => "HolP",                  'natsplpwr' => "NatP",                  'shasplpwr' => "ShaP",                  'splheal'   => "Heal",
            'str'       => "Str",                   'agi'       => "Agi",                   'sta'       => "Sta",                   'int'       => "Int",                   'spi'       => "Spi"
        ),
        'spellModOp'    => array(
            "伤害",                               "持续时间",                             "Thread",                               "效果1",                             "可使用次数",
            "范围",                                "半径",                               "Critical Hit Chance",                  "所有效果",                          "Casting Time loss",
            "Casting Time",                         "冷却时间",                             "效果2",                             "无视护甲",                         "花费",
            "Critical Damage Bonus",                "Chance to Fail",                       "Jump Targets",                         "Proc Chance",                          "Intervall",
            "Multiplier (Damage)",                  "公共冷却时间",                      "Damage over Time",                     "效果3",                             "Multiplier (Bonus)",
            null,                                   "Procs per Minute",                     "Multiplier (Value)",                   "Chance to Resist Dispel",              "Critical Damage Bonus2",
            "Refund Cost on Fail"
        ),
        'combatRating'  => array(
            "武器技能",                         "防御",                        "躲闪",                                "招架",                                "格挡",
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
/*0-5    */ '无',                     '杀死',                '类型伤害',            'Dummy',                    '传送门',          '传送单位',
/*6+     */ '应用光环',               '环境伤害',     'Power Drain',              'Health Leech',             'Heal',                     'Bind',
/*12+    */ 'Portal',                   'Ritual Base',              'Ritual Specialize',        'Ritual Activate Portal',   '任务完成',           'Weapon Damage NoSchool',
/*18+    */ '复活',                'Add Extra Attacks',        '躲闪',                    '闪避',                    '招架',                    '格挡',
/*24+    */ '创建物品',              '可以使用武器',           '防御',                  'Persistent Area Aura',     '召唤',                   'Leap',
/*30+    */ 'Energize',                 '武器伤害百分比',    'Trigger Missile',          'Open Lock',                'Summon Change Item',       'Apply Area Aura Party',
/*36+    */ '学习法术',              '法术防御',            '驱散',                   'Language',                 'Dual Wield',               'Jump',
/*42+    */ 'Jump Dest',                'Teleport Units Face Caster','Skill Step',              'Add Honor',                'Spawn',                    'Trade Skill',
/*48+    */ 'Stealth',                  'Detect',                   'Trans Door',               'Force Critical Hit',       'Guarantee Hit',            'Enchant Item Permanent',
/*54+    */ 'Enchant Item Temporary',   'Tame Creature',            'Summon Pet',               'Learn Pet Spell',          'Weapon Damage Flat',       'Create Random Item',
/*60+    */ 'Proficiency',              'Send Event',               'Power Burn',               'Threat',                   'Trigger Spell',            'Apply Area Aura Raid',
/*66+    */ 'Create Mana Gem',          'Heal Max Health',          'Interrupt Cast',           'Distract',                 'Pull',                     '偷窃',
/*72+    */ 'Add Farsight',             'Untrain Talents',          'Apply Glyph',              'Heal Mechanical',          'Summon Object Wild',       'Script Effect',
/*78+    */ 'Attack',                   'Sanctuary',                'Add Combo Points',         'Create House',             'Bind Sight',               'Duel',
/*84+    */ 'Stuck',                    'Summon Player',            'Activate Object',          'WMO Damage',               'WMO Repair',               'WMO Change',
/*90+    */ 'Kill Credit',              'Threat All',               'Enchant Held Item',        'Force Deselect',           'Self Resurrect',           'Skinning',
/*96+    */ '可使用次数',                   'Cast Button',              'Knock Back',               'Disenchant',               'Inebriate',                'Feed Pet',
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
        'armor'         => "%s点护甲",                      // ARMOR_TEMPLATE
        'block'         => "%s格挡",                      // SHIELD_BLOCK_TEMPLATE
        'charges'       => "%d |4次:次;",          // ITEM_SPELL_CHARGES
        'locked'        => "Locked",                        // LOCKED
        'ratingString'  => "%s&nbsp;@&nbsp;L%s",
        'heroic'        => "英雄",                        // ITEM_HEROIC
        'startQuest'    => "该物品将触发一个任务",      // ITEM_STARTS_QUEST
        'bagSlotString' => "%d格%s",                    // CONTAINER_SLOTS
        'fap'           => "在猎豹、熊、巨熊和枭兽形态下的攻击强度",
        'durability'    => "耐久度 %d / %d",            // DURABILITY_TEMPLATE
        'realTime'      => "real time",
        'conjured'      => "魔法制造物品",                 // ITEM_CONJURED
        'sellPrice'     => "卖价",                    // SELL_PRICE
        'itemLevel'     => "物品等级%d",                 // ITEM_LEVEL
        'randEnchant'   => "&lt;随机附魔&gt",     // ITEM_RANDOM_ENCHANT
        'readClick'     => "&lt;右键点击阅读&gt",    // ITEM_READABLE
        'openClick'     => "&lt;右键点击打开&gt",    // ITEM_OPENABLE
        'setBonus'      => "(%d) 套装：%s",                  // ITEM_SET_BONUS_GRAY
        'setName'       => "%s (%d/%d)",                    // ITEM_SET_NAME
        'partyLoot'     => "Party loot",
        'smartLoot'     => "Smart loot",
        'indestructible'=> "不能被摧毁",
        'deprecated'    => "过时的",
        'useInShape'    => "可在变形形态下使用", //shapeshifted=变形
        'useInArena'    => "可在竞技场状态下使用",
        'refundable'    => "可退还的",
        'noNeedRoll'    => "Cannot roll Need",
        'atKeyring'     => "可以放在钥匙链",
        'worth'         => "Worth",
        'consumable'    => "消耗品",
        'nonConsumable' => "非消耗品",
        'accountWide'   => "Account-wide",
        'millable'      => "可研磨",                      // ITEM_MILLABLE
        'noEquipCD'     => "没有装备的冷却时间",
        'prospectable'  => "可选矿",                  // ITEM_PROSPECTABLE
        'disenchantable'=> "可分解",                // ITEM_DISENCHANT_ANY_SKILL
        'cantDisenchant'=> "无法分解",        // ITEM_DISENCHANT_NOT_DISENCHANTABLE
        'repairCost'    => "修理费用",                   // REPAIR_COST
        'tool'          => "工具",
        'cost'          => "花费",                          // COSTS_LABEL  //Cost
        'content'       => "内容",
        '_transfer'     => '这个物品将被转换到<a href="?item=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a>，如果你转移到<span class="icon-%s">%s</span>。',
        '_unavailable'  => "这个物品对玩家不可用。",
        '_rndEnchants'  => "随机附魔",  //Random Enchantments
        '_chance'       => "（%s%%几率）",
        'slot'          => "Slot",
        '_quality'      => "质量",                       // QUALITY
        'usableBy'      => "Usable by",
        'buyout'        => "一口价",                  // BUYOUT_PRICE
        'each'          => "每个", //each
        'tabOther'      => "其他",
        'reqMinLevel'   => "需要等级 %d",             // ITEM_MIN_LEVEL
        'reqLevelRange' => "需要等级 %d 到 %d (%s)",  // ITEM_LEVEL_RANGE_CURRENT
        'unique'        => ["唯一",          "唯一(%d)", "唯一：%s (%d)"         ],   // ITEM_UNIQUE, ITEM_UNIQUE_MULTIPLE, ITEM_LIMIT_CATEGORY
        'uniqueEquipped'=> ["装备唯一", null,          "装备唯一：%s (%d)"],   // ITEM_UNIQUE_EQUIPPABLE, null, ITEM_LIMIT_CATEGORY_MULTIPLE
        'speed'         => "速度",                         // SPEED
        'dps'           => "（每秒伤害%.1f）",      // DPS_TEMPLATE
        'damage'        => array(                           // *DAMAGE_TEMPLATE*
                        //  basic,                          basic /w school,                add basic,                  add basic /w school
            'single'    => ["%d 伤害",                    "%d %s 伤害",                 "+ %d 伤害",              "+%d %s 伤害"             ],
            'range'     => ["%d - %d 伤害",               "%d - %d %s 伤害",            "+ %d - %d 伤害",         "+%d - %d %s 伤害"        ],
            'ammo'      => ["增加 %g 伤害每秒",    "增加 %g %s 伤害每秒", "+ %g 伤害每秒",   "+ %g %s 伤害每秒" ]
        ),
        'gems'          => "宝石",
        'socketBonus'   => "镶孔奖励",                  // ITEM_SOCKET_BONUS
        'socket'        => array(                           // EMPTY_SOCKET_*
            "多彩插槽",          "红色插槽",       "黄色插槽",        "蓝色插槽",          -1 => "棱彩插槽"
        ),
        'gemColors'     => array(                           // *_GEM
            "多彩",                 "红色",              "黄色",               "蓝色"
        ),
        'gemConditions' => array(                           // ENCHANT_CONDITION_* in GlobalStrings.lua
            2 => "少于%d%s|4颗:颗;宝石",
            3 => "%s宝石数量多于%s宝石",  //more %s gems than %s gems
            5 => "至少%d%s|4颗:颗;宝石"
        ),
        'reqRating'     => array(                           // ITEM_REQ_ARENA_RATING*
            "需要个人竞技场等级达到%d",   //Requires personal and team arena rating of 
            "需要3v3或5v5的个人竞技场等级达到%d|n",
            "需要5v5的个人竞技场等级达到%d|n"
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
        'elixirType'    => [null, "战斗", "守护"],
        'cat'           => array(                           // ordered by content first, then alphabeticaly; item menu from locale_enus.js
             2 => "武器",                                // self::$spell['weaponSubClass']
             4 => array("护甲", array(
                 1 => "布甲",                 2 => "皮甲",           3 => "锁甲",              4 => "板甲",             6 => "盾牌",                 7 => "圣契",
                 8 => "圣像",                       9 => "图腾",                 10 => "魔印",                 -6 => "斗篷",                 -5 => "副手",        -8 => "衬衫",
                -7 => "战袍",                    -3 => "项链",                -2 => "戒指",                  -4 => "饰品",                0 => "杂项（护甲）",
            )),
             1 => array("容器", array(
                 0 => "背包",                        3 => "附魔材料包",         4 => "工程学材料包",        5 => "宝石袋",                2 => "草药包",               8 => "铭文包",
                 7 => "制皮材料包",         6 => "矿石袋",             1 => "灵魂袋"
            )),
             0 => array("消耗品", array(
                -3 => "物品强化（临时）",                               6 => "物品强化（永久）",                           2 => ["药剂", [1 => "战斗药剂", 2 => "守护药剂"]],
                 1 => "药水",                     4 => "卷轴",                 7 => "绷带",                0 => "消耗品",             3 => "合剂",                  5 => "食物和饮料",
                 8 => "其他（消耗品）"
            )),
            16 => array("雕文", array(
                 1 => "战士雕文",              2 => "圣骑士雕文",          3 => "猎人雕文",           4 => "潜行者雕文",            5 => "牧师雕文",           6 => "死亡骑士雕文",
                 7 => "萨满祭司雕文",               8 => "法师雕文",             9 => "术士雕文",         11 => "德鲁伊雕文"
            )),
             7 => array("杂货", array(
                14 => "护甲附魔",          5 => "布甲",                   3 => "装置",                10 => "元素",              12 => "附魔",              2 => "爆炸物",
                 9 => "草药",                       4 => "珠宝加工",           6 => "皮甲",                13 => "原料",               8 => "肉类",                    7 => "金属和矿石",
                 1 => "零件",                      15 => "武器附魔",    11 => "其他（杂货）"
             )),
             6 => ["弹药", [                  2 => "箭",                  3 => "子弹"     ]],
            11 => ["箭袋",     [                  2 => "箭袋",                 3 => "弹药袋"]],
             9 => array("配方", array(
                 0 => "书籍",                       6 => "炼金术配方",         4 => "锻造设计图",     5 => "烹饪配方",         8 => "附魔公式",     3 => "工程学图纸",
                 7 => "急救书籍",             9 => "钓鱼书籍",          11 => "铭文", 10 => "珠宝加工设计图",   1 => "制皮",12 => "采矿",
                 2 => "裁缝"
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
