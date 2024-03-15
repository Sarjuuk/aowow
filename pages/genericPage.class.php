<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


trait TrDetailPage
{
    protected $category   = null;                           // not used on detail pages
    protected $lvTabs     = [];                             // most pages have this

    protected $ssError    = null;
    protected $coError    = null;
    protected $viError    = null;

    protected $subject    = null;                           // so it will not get cached

    // template vars
    protected $extraText  = '';
    protected $infobox    = [];
    protected $transfer   = [];                             // faction transfer equivalent data
    protected $redButtons = [];                             // see template/redButtons.tpl.php
    protected $smartAI    = null;
    protected $map        = null;
    protected $article    = [];
    protected $headIcons  = [];
    protected $expansion  = EXP_CLASSIC;


    protected $contribute = CONTRIBUTE_ANY;

    protected function generateCacheKey(bool $withStaff = true) : string
    {
        $staff = intVal($withStaff && User::isInGroup(U_GROUP_EMPLOYEE));

        //     mode,         type,        typeId,        employee-flag, localeId,        category, filter
        $key = [$this->mode, $this->type, $this->typeId, $staff,        User::$localeId, '-1',     '-1'];

        // item special: can modify tooltips
        if (isset($this->enhancedTT))
            $key[] = md5(serialize($this->enhancedTT));

        return implode('_', $key);
    }

    protected function applyCCErrors() : void
    {
        if (!empty($_SESSION['error']['co']))
            $this->coError = $_SESSION['error']['co'];

        if (!empty($_SESSION['error']['ss']))
            $this->ssError = $_SESSION['error']['ss'];

        if (!empty($_SESSION['error']['vi']))
            $this->viError = $_SESSION['error']['vi'];

        unset($_SESSION['error']);
    }
}


trait TrListPage
{
    protected $category   = null;
    protected $subCat     = '';
    protected $filter     = [];
    protected $lvTabs     = [];                             // most pages have this
    protected $redButtons = [];                             // see template/redButtons.tpl.php

    private   $filterObj = null;

    protected function generateCacheKey(bool $withStaff = true) : string
    {
        $staff = intVal($withStaff && User::isInGroup(U_GROUP_EMPLOYEE));

        //     mode,         type,        typeId, employee-flag, localeId,
        $key = [$this->mode, $this->type, '-1',   $staff,        User::$localeId];

        //category
        $key[] = $this->category ? implode('.', $this->category) : '-1';

        // filter
        $key[] = $this->filterObj ? md5(serialize($this->filterObj)) : '-1';

        return implode('_', $key);
    }
}


trait TrProfiler
{
    protected $region      = '';
    protected $realm       = '';
    protected $realmId     = 0;
    protected $battlegroup = '';                            // not implemented, since no pserver supports it
    protected $subjectName = '';
    protected $subjectGUID = 0;
    protected $sumSubjects = 0;

    protected $doResync    = null;

    protected function generateCacheKey(bool $withStaff = true) : string
    {
        $staff = intVal($withStaff && User::isInGroup(U_GROUP_EMPLOYEE));

        //     mode,         type,        typeId,                         employee-flag, localeId,        category, filter
        $key = [$this->mode, $this->type, $this->subject->getField('id'), $staff,        User::$localeId, '-1',     '-1'];

        return implode('_', $key);
    }

    protected function getSubjectFromUrl(string $pageParam) : void
    {
        if (!$pageParam)
            return;

        // cat[0] is always region
        // cat[1] is realm or bGroup (must be realm if cat[2] is set)
        // cat[2] is arena-team, guild or player
        $cat = explode('.', $pageParam, 3);

        $cat = array_map('urldecode', $cat);

        if (array_search($cat[0], Util::$regions) === false)
            return;

        $this->region = $cat[0];

        // if ($cat[1] == Profiler::urlize(CFG_BATTLEGROUP))
            // $this->battlegroup = CFG_BATTLEGROUP;
        if (isset($cat[1]))
        {
            foreach (Profiler::getRealms() as $rId => $r)
            {
                if (Profiler::urlize($r['name'], true) == $cat[1])
                {
                    $this->realm   = $r['name'];
                    $this->realmId = $rId;
                    if (isset($cat[2]) && mb_strlen($cat[2]) >= 2)
                        $this->subjectName = $cat[2];       // cannot reconstruct original name from urlized form; match against special name field

                    break;
                }
            }
        }
    }

    protected function initialSync() : void
    {
        $this->prepareContent();

        $this->contribute = CONTRIBUTE_NONE;
        $this->notFound   = array(
            'title' => sprintf(Lang::profiler('firstUseTitle'), $this->subjectName, $this->realm),
            'msg'   => ''
        );

        if (isset($this->tabId))
            $this->pageTemplate['activeTab'] = $this->tabId;

        $this->sumSQLStats();

        $this->display('text-page-generic');
        exit();
    }

    protected function generatePath() : void
    {
        if ($this->region)
        {
            $this->path[] = $this->region;

            if ($this->realm)
                $this->path[] = Profiler::urlize($this->realm, true);
            // else
                // $this->path[] = Profiler::urlize(CFG_BATTLEGROUP);
        }
    }
}


class GenericPage
{
    use TrRequestData;

    protected $tpl          = '';
    protected $reqUGroup    = U_GROUP_NONE;
    protected $reqAuth      = false;
    protected $mode         = CACHE_TYPE_NONE;
 // protected $contribute;                                  // defined in __construct()

    protected $wowheadLink  = 'https://wowhead.com/';

    protected $jsGlobals    = [];
    protected $lvData       = [];
    protected $title        = [CFG_NAME];                   // for title-Element
    protected $name         = '';                           // for h1-Element
    protected $tabId        = null;
    protected $gDataKey     = false;                        // adds the dataKey to the user vars
    protected $scripts      = array(
        [SC_JS_FILE,  'js/jquery-3.7.0.min.js',  SC_FLAG_NO_TIMESTAMP                        ],
        [SC_JS_FILE,  'js/basic.js'                                                          ],
        [SC_JS_FILE,  'widgets/power.js',        SC_FLAG_NO_TIMESTAMP | SC_FLAG_APPEND_LOCALE],
        [SC_JS_FILE,  'js/locale_%s.js',         SC_FLAG_LOCALIZED                           ],
        [SC_JS_FILE,  'js/global.js'                                                         ],
        [SC_JS_FILE,  'js/locale.js'                                                         ],
        [SC_JS_FILE,  'js/Markup.js'                                                         ],
        [SC_CSS_FILE, 'css/basic.css'                                                        ],
        [SC_CSS_FILE, 'css/global.css'                                                       ],
        [SC_CSS_FILE, 'css/aowow.css'                                                        ],
        [SC_CSS_FILE, 'css/locale_%s.css',       SC_FLAG_LOCALIZED                           ]
    );

    // private vars don't get cached
    private   $time         = 0;
    private   $cacheDir     = 'cache/template/';
    private   $jsgBuffer    = [];
    private   $gPageInfo    = [];
    private   $gUser        = [];
    private   $pageTemplate = [];
    private   $community    = ['co' => [], 'sc' => [], 'vi' => []];

    private   $cacheLoaded  = [];
    private   $skipCache    = 0x0;
    private   $memcached    = null;
    private   $mysql        = ['time' => 0, 'count' => 0];

    private   $js           = [];
    private   $css          = [];
    private   $headerLogo   = '';
    private   $fullParams   = '';

    private   $lvTemplates  = array(
        'achievement'       => ['template' => 'achievement',       'id' => 'achievements',    'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_achievements'  ],
        'areatrigger'       => ['template' => 'areatrigger',       'id' => 'areatrigger',     'parent' => 'lv-generic', 'data' => [],                                     ],
        'calendar'          => ['template' => 'holidaycal',        'id' => 'calendar',        'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_calendar'      ],
        'class'             => ['template' => 'classs',            'id' => 'classes',         'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_classes'       ],
        'commentpreview'    => ['template' => 'commentpreview',    'id' => 'comments',        'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_comments'      ],
        'npc'               => ['template' => 'npc',               'id' => 'npcs',            'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_npcs'          ],
        'currency'          => ['template' => 'currency',          'id' => 'currencies',      'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_currencies'    ],
        'emote'             => ['template' => 'emote',             'id' => 'emotes',          'parent' => 'lv-generic', 'data' => []                                      ],
        'enchantment'       => ['template' => 'enchantment',       'id' => 'enchantments',    'parent' => 'lv-generic', 'data' => []                                      ],
        'event'             => ['template' => 'holiday',           'id' => 'holidays',        'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_holidays'      ],
        'faction'           => ['template' => 'faction',           'id' => 'factions',        'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_factions'      ],
        'genericmodel'      => ['template' => 'genericmodel',      'id' => 'same-model-as',   'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_samemodelas'   ],
        'icongallery'       => ['template' => 'icongallery',       'id' => 'icons',           'parent' => 'lv-generic', 'data' => []                                      ],
        'item'              => ['template' => 'item',              'id' => 'items',           'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_items'         ],
        'itemset'           => ['template' => 'itemset',           'id' => 'itemsets',        'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_itemsets'      ],
        'mail'              => ['template' => 'mail',              'id' => 'mails',           'parent' => 'lv-generic', 'data' => []                                      ],
        'model'             => ['template' => 'model',             'id' => 'gallery',         'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_gallery'       ],
        'object'            => ['template' => 'object',            'id' => 'objects',         'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_objects'       ],
        'pet'               => ['template' => 'pet',               'id' => 'hunter-pets',     'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_pets'          ],
        'profile'           => ['template' => 'profile',           'id' => 'profiles',        'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_profiles'      ],
        'quest'             => ['template' => 'quest',             'id' => 'quests',          'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_quests'        ],
        'race'              => ['template' => 'race',              'id' => 'races',           'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_races'         ],
        'replypreview'      => ['template' => 'replypreview',      'id' => 'comment-replies', 'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_commentreplies'],
        'reputationhistory' => ['template' => 'reputationhistory', 'id' => 'reputation',      'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_reputation'    ],
        'screenshot'        => ['template' => 'screenshot',        'id' => 'screenshots',     'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_screenshots'   ],
        'skill'             => ['template' => 'skill',             'id' => 'skills',          'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_skills'        ],
        'sound'             => ['template' => 'sound',             'id' => 'sounds',          'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.types[19][2]'      ],
        'spell'             => ['template' => 'spell',             'id' => 'spells',          'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_spells'        ],
        'title'             => ['template' => 'title',             'id' => 'titles',          'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_titles'        ],
        'topusers'          => ['template' => 'topusers',          'id' => 'topusers',        'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.topusers'          ],
        'video'             => ['template' => 'video',             'id' => 'videos',          'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_videos'        ],
        'zone'              => ['template' => 'zone',              'id' => 'zones',           'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_zones'         ],
        'guide'             => ['template' => 'guide',             'id' => 'guides',          'parent' => 'lv-generic', 'data' => [],                                     ]
    );

    public function __construct(string $pageCall = '', string $pageParam = '')
    {
        $this->time = microtime(true);

        $this->initRequestData();

        if (!isset($this->contribute))
            $this->contribute = CONTRIBUTE_NONE;

        $this->fullParams = $pageCall;
        if ($pageParam)
            $this->fullParams .= '='.$pageParam;

        if (CFG_CACHE_DIR && Util::writeDir(CFG_CACHE_DIR))
            $this->cacheDir = mb_substr(CFG_CACHE_DIR, -1) != '/' ? CFG_CACHE_DIR.'/' : CFG_CACHE_DIR;

        // force page refresh
        if (isset($_GET['refresh']) && User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_DEV))
        {
            if ($_GET['refresh'] == 'filecache')
                $this->skipCache = CACHE_MODE_FILECACHE;
            else if ($_GET['refresh'] == 'memcached')
                $this->skipCache = CACHE_MODE_MEMCACHED;
            else if ($_GET['refresh'] == '')
                $this->skipCache = CACHE_MODE_FILECACHE | CACHE_MODE_MEMCACHED;
        }

        // prep js+css includes
        $parentVars = get_class_vars(__CLASS__);
        if ($parentVars['scripts'] != $this->scripts)       // additions set in child class
            $this->scripts = array_merge($parentVars['scripts'], $this->scripts);

        $this->addScript(...$this->scripts);

        if (User::isInGroup(U_GROUP_STAFF | U_GROUP_SCREENSHOT | U_GROUP_VIDEO))
            $this->addScript([SC_CSS_FILE, 'css/staff.css'], [SC_JS_FILE,  'js/staff.js']);

        // display modes
        if (isset($_GET['power']) && method_exists($this, 'generateTooltip'))
            $this->mode = CACHE_TYPE_TOOLTIP;
        else if (isset($_GET['xml']) && method_exists($this, 'generateXML'))
            $this->mode = CACHE_TYPE_XML;
        else
        {
            // get alt header logo
            if ($ahl = DB::Aowow()->selectCell('SELECT altHeaderLogo FROM ?_home_featuredbox WHERE ?d BETWEEN startDate AND endDate ORDER BY id DESC', time()))
                $this->headerLogo = Util::defStatic($ahl);

            $this->gUser      = User::getUserGlobals();
            $this->gFavorites = User::getFavorites();
            $this->pageTemplate['pageName'] = strtolower($pageCall);

            $this->wowheadLink = sprintf(WOWHEAD_LINK, Util::$subDomains[User::$localeId], $pageCall, $pageParam);

            if (!$this->isValidPage())
                $this->error();
        }

        // requires authed user
        if ($this->reqAuth && !User::$id)
            $this->forwardToSignIn($_SERVER['QUERY_STRING'] ?? '');

        // restricted access
        if ($this->reqUGroup && !User::isInGroup($this->reqUGroup))
        {
            if (User::$id)
                $this->error();
            else
                $this->forwardToSignIn($_SERVER['QUERY_STRING'] ?? '');
        }

        if (CFG_MAINTENANCE && !User::isInGroup(U_GROUP_EMPLOYEE))
            $this->maintenance();
        else if (CFG_MAINTENANCE && User::isInGroup(U_GROUP_EMPLOYEE))
            Util::addNote(U_GROUP_EMPLOYEE, 'Maintenance mode enabled!');

        // get errors from previous page from session and apply to template
        if (method_exists($this, 'applyCCErrors'))
            $this->applyCCErrors();
    }


    /**********/
    /* Checks */
    /**********/

    // "template_exists"
    private function isSaneInclude(string $path, string $file) : bool
    {
        if (preg_match('/[^\w\-]/i', str_replace('admin/', '', $file)))
            return false;

        if (!is_file($path.$file.'.tpl.php'))
            return false;

        return true;
    }

    // has a valid combination of categories
    private function isValidPage() : bool
    {
        if (!isset($this->category) || empty($this->validCats))
            return true;

        $c = $this->category;                               // shorthand

        switch (count($c))
        {
            case 0: // no params works always
                return true;
            case 1: // null is valid  || value in a 1-dim-array     || (key for a n-dim-array           && ( has more subcats                 || no further subCats ))
                $filtered = array_filter($this->validCats, function ($x) { return is_int($x); });
                return $c[0] === null || in_array($c[0], $filtered) || (!empty($this->validCats[$c[0]]) && (is_array($this->validCats[$c[0]]) || $this->validCats[$c[0]] === true));
            case 2: // first param has to be a key. otherwise invalid
                if (!isset($this->validCats[$c[0]]))
                    return false;

                // check if the sub-array is n-imensional
                if (is_array($this->validCats[$c[0]]) && count($this->validCats[$c[0]]) == count($this->validCats[$c[0]], COUNT_RECURSIVE))
                    return in_array($c[1], $this->validCats[$c[0]]); // second param is value in second level array
                else
                    return isset($this->validCats[$c[0]][$c[1]]);    // check if params is key of another array
            case 3: // 3 params MUST point to a specific value
                return isset($this->validCats[$c[0]][$c[1]]) && in_array($c[2], $this->validCats[$c[0]][$c[1]]);
        }

        return false;
    }


    /****************/
    /* Prepare Page */
    /****************/

    // get from cache ?: run generators
    protected function prepareContent() : void
    {
        if (!$this->loadCache())
        {
            $this->generateContent();
            $this->generatePath();
            $this->generateTitle();
            $this->addArticle();

            $this->applyGlobals();

            $this->saveCache();
        }

        if ($this instanceof GuidePage)
        {
            $this->gPageInfo = ['name' => $this->name];
            if (isset($this->author))
                $this->gPageInfo['author'] = $this->author;

        }
        else if (isset($this->type) && isset($this->typeId))
        {
            $this->gPageInfo = array(                       // varies slightly for special pages like maps, user-dashboard or profiler
                'type'   => $this->type,
                'typeId' => $this->typeId,
                'name'   => $this->name
            );
        }

        // only adds edit links to the staff menu: precursor to guides?
        if (!empty($this->articleUrl) && !($this instanceof GuidePage && $this->show == GuidePage::SHOW_GUIDE))
        {
            $this->gPageInfo = array(
                'articleUrl' => $this->fullParams,          // is actually be the url-param
                'editAccess' => isset($this->editAccess) ? $this->editAccess : (U_GROUP_ADMIN | U_GROUP_EDITOR | U_GROUP_BUREAU)
            );
        }

        if (!empty($this->path))
            $this->pageTemplate['breadcrumb'] = $this->path;

        if (!empty($this->filter))
            $this->pageTemplate['filter'] = empty($this->filter['query']) ? 0 : 1;

        if (method_exists($this, 'postCache'))              // e.g. update dates for events and such
            $this->postCache();

        // determine contribute tabs
        if (isset($this->subject) && !isset($this->contribute))
        {
            $x = get_class($this->subject);
            $this->contribute = $x::$contribute;
        }

        if ($this->contribute & CONTRIBUTE_CO)
            $this->community['co'] = CommunityContent::getComments($this->type, $this->typeId);

        if ($this->contribute & CONTRIBUTE_SS)
            $this->community['ss'] = CommunityContent::getScreenshots($this->type, $this->typeId);

        if ($this->contribute & CONTRIBUTE_VI)
            $this->community['vi'] = CommunityContent::getVideos($this->type, $this->typeId);

        // as comments are not cached, those globals cant be either
        if ($this->contribute)
        {
            $this->extendGlobalData(CommunityContent::getJSGlobals());
            $this->applyGlobals();
        }

        $this->time = microtime(true) - $this->time;
        $this->sumSQLStats();
    }

    public function addScript(array ...$structs) : void
    {
        array_walk($structs, function(&$x) { $x = array_pad($x, 3, 0); });

        foreach ($structs as [$type, $str, $flags])
        {
            if (empty($str))
            {
                trigger_error('GenericPage::addScript - content empty', E_USER_WARNING);
                continue;
            }

            $dynData = strpos($str, '?data=') === 0;
            $app = [];

            // insert locale string
            if ($flags & SC_FLAG_LOCALIZED)
                $str = sprintf($str, User::$localeString);

            if ($dynData)
            {
                $app[] = 'locale='.User::$localeId;
                $app[] = 't='.$_SESSION['dataKey'];
            }
            else if (($flags & SC_FLAG_APPEND_LOCALE) && User::$localeId)
                $app[] = 'lang='.Util::$subDomains[User::$localeId];

            // append anti-cache timestamp
            if (!($flags & SC_FLAG_NO_TIMESTAMP) && !$dynData)
                if ($type == SC_JS_FILE || $type == SC_CSS_FILE)
                    $app[] = filemtime('static/'.$str) ?: 0;

            if ($app)
                $str .= ($dynData ? '&' : '?').implode('&', $app);

            switch ($type)
            {
                case SC_JS_FILE:
                    $str = ($dynData ? HOST_URL : STATIC_URL).'/'.$str;
                case SC_JS_STRING:
                    if ($flags & SC_FLAG_PREFIX)
                        array_unshift($this->js, [$type, $str]);
                    else
                        $this->js[] = [$type, $str];
                    break;
                case SC_CSS_FILE:
                    $str = STATIC_URL.'/'.$str;
                case SC_CSS_STRING:
                    if ($flags & SC_FLAG_PREFIX)
                        array_unshift($this->css, [$type, $str]);
                    else
                        $this->css[] = [$type, $str];
                    break;
                default:
                    trigger_error('GenericPage::addScript - unknown script type #'.$type, E_USER_WARNING);
            }
        }
    }

    // get article & static infobox (run before processing jsGlobals)
    private function addArticle() :void
    {
        if (isset($this->article))
            return;

        $article = [];
        if (isset($this->guideRevision))
            $article = DB::Aowow()->selectRow('SELECT `article`, `quickInfo`, `locale`, `editAccess` FROM ?_articles WHERE `type` = ?d AND `typeId` = ?d AND `rev` = ?d',
                Type::GUIDE, $this->typeId, $this->guideRevision);
        else if (!empty($this->articleUrl))
            $article = DB::Aowow()->selectRow('SELECT `article`, `quickInfo`, `locale`, `editAccess` FROM ?_articles WHERE `url` = ? AND `locale` IN (?a) ORDER BY `locale`, `rev` DESC LIMIT 1',
                $this->articleUrl, [User::$localeId, LOCALE_EN]);
        else if (!empty($this->type) && isset($this->typeId))
            $article = DB::Aowow()->selectRow('SELECT `article`, `quickInfo`, `locale`, `editAccess` FROM ?_articles WHERE `type` = ?d AND `typeId` = ?d AND `locale` IN (?a) ORDER BY `locale`, `rev` DESC LIMIT 1',
                $this->type, $this->typeId, [User::$localeId, LOCALE_EN]);

        if ($article)
        {
            if ($article['article'])
                (new Markup($article['article']))->parseGlobalsFromText($this->jsgBuffer);
            if ($article['quickInfo'])
                (new Markup($article['quickInfo']))->parseGlobalsFromText($this->jsgBuffer);

            $this->article = array(
                'text'   => Util::jsEscape(Util::defStatic($article['article'])),
                'params' => []
            );

            if (!empty($this->type) && isset($this->typeId))
                $this->article['params']['dbpage'] = true;

            // convert U_GROUP_* to MARKUP.CLASS_* (as seen in js-object Markup)
            if ($article['editAccess'] & (U_GROUP_ADMIN | U_GROUP_VIP | U_GROUP_DEV))
                $this->article['params']['allow'] = '$Markup.CLASS_ADMIN';
            else if ($article['editAccess'] & U_GROUP_STAFF)
                $this->article['params']['allow'] = '$Markup.CLASS_STAFF';
            else if ($article['editAccess'] & U_GROUP_PREMIUM)
                $this->article['params']['allow'] = '$Markup.CLASS_PREMIUM';
            else if ($article['editAccess'] & U_GROUP_PENDING)
                $this->article['params']['allow'] = '$Markup.CLASS_PENDING';
            else
                $this->article['params']['allow'] = '$Markup.CLASS_USER';

            $this->editAccess = $article['editAccess'];

            if (empty($this->infobox) && !empty($article['quickInfo']))
                $this->infobox = $article['quickInfo'];

            if ($article['locale'] != User::$localeId)
                $this->article['params']['prepend'] = '<div class="notice-box"><span class="icon-bubble">'.Lang::main('langOnly', [Lang::lang($article['locale'])]).'</span></div>';

            if (method_exists($this, 'postArticle'))        // e.g. update variables in article
                $this->postArticle($this->article['text']);
        }
    }

    // get announcements and notes for user
    private function addAnnouncements(bool $pagespecific = true) : void
    {
        if (!isset($this->announcements))
            $this->announcements = [];

        // display occured notices
        if ($_ = Util::getNotes())
        {
            array_unshift($_, 'One or more errors occured, while generating this page.');

            $this->announcements[0] = array(
                'parent' => 'announcement-0',
                'id'     => 0,
                'mode'   => 1,
                'status' => 1,
                'name'   => 'internal error',
                'style'  => 'color: #ff3333; font-weight: bold; font-size: 14px; padding-left: 40px; background-image: url('.STATIC_URL.'/images/announcements/warn-small.png); background-size: 15px 15px; background-position: 12px center; border: dashed 2px #C03030;',
                'text'   => '[span]'.implode("[br]", $_).'[/span]'
            );
        }

        // fetch announcements
        if ($this->pageTemplate['pageName'])
        {
            $ann = DB::Aowow()->Select('SELECT ABS(id) AS ARRAY_KEY, a.* FROM ?_announcements a WHERE status = 1 AND (page = ? OR page = "*") AND (groupMask = 0 OR groupMask & ?d)', $pagespecific ? $this->pageTemplate['pageName'] : '', User::$groups);
            foreach ($ann as $k => $v)
            {
                if ($t = Util::localizedString($v, 'text'))
                {
                    $_ = array(
                        'parent' => 'announcement-'.$k,
                        'id'     => $v['id'],
                        'mode'   => $v['mode'],
                        'status' => $v['status'],
                        'name'   => $v['name'],
                        'text'   => Util::defStatic($t)
                    );

                    if ($v['style'])                        // may be empty
                        $_['style'] = Util::defStatic($v['style']);

                    $this->announcements[$k] = $_;
                }
            }
        }
    }

    protected function getCategoryFromUrl(string $urlParam) : void
    {
        $arr    = explode('.', $urlParam);
        $params = [];

        foreach ($arr as $v)
            if (is_numeric($v))
                $params[] = (int)$v;

        $this->category = $params;
    }

    protected function forwardToSignIn(string $next = '') : void
    {
        $next = $next ? '&next='.$next : '';
        header('Location: ?account=signin'.$next, true, 302);
    }

    protected function sumSQLStats() : void
    {
        Util::arraySumByKey($this->mysql, DB::Aowow()->getStatistics(), DB::World()->getStatistics());
    }


    /*******************/
    /* Special Display */
    /*******************/

    // unknown entry
    public function notFound(string $title = '', string $msg = '') : void
    {
        if ($this->mode == CACHE_TYPE_TOOLTIP && method_exists($this, 'generateTooltip'))
        {
            header(MIME_TYPE_JSON);
            echo $this->generateTooltip();
        }
        else if ($this->mode == CACHE_TYPE_XML && method_exists($this, 'generateXML'))
        {
            header(MIME_TYPE_XML);
            echo $this->generateXML();
        }
        else
        {
            header('HTTP/1.0 404 Not Found', true, 404);

            array_unshift($this->title, Lang::main('nfPageTitle'));

            $this->contribute = CONTRIBUTE_NONE;
            $this->notFound   = array(
                'title' =>          isset($this->typeId) ? Util::ucFirst($title).' #'.$this->typeId    : $title,
                'msg'   => !$msg && isset($this->typeId) ? sprintf(Lang::main('pageNotFound'), $title) : $msg
            );

            if (isset($this->tabId))
                $this->pageTemplate['activeTab'] = $this->tabId;

            $this->sumSQLStats();


            $this->display('list-page-generic');
        }

        exit();
    }

    // unknown page
    public function error() : void
    {
        $this->path       = null;
        $this->tabId      = null;
        $this->articleUrl = 'page-not-found';
        $this->title[]    = Lang::main('errPageTitle');
        $this->name       = Lang::main('errPageTitle');
        $this->lvTabs     = [];

        $this->addArticle();

        $this->sumSQLStats();

        header('HTTP/1.0 404 Not Found', true, 404);

        $this->display('list-page-generic');
        exit();
    }

    // display brb gnomes
    public function maintenance() : void
    {
        header('HTTP/1.0 503 Service Temporarily Unavailable', true, 503);
        header('Retry-After: '.(3 * HOUR));

        $this->display('maintenance');
        exit();
    }


    /*******************/
    /* General Display */
    /*******************/

    // load given template string or GenericPage::$tpl
    public function display(string $override = '') : void
    {
        // Heisenbug: IE11 and FF32 will sometimes (under unknown circumstances) cache 302 redirects and stop
        // re-requesting them from the server but load them from local cache, thus breaking menu features.
        Util::sendNoCacheHeader();

        if ($this->mode == CACHE_TYPE_TOOLTIP && method_exists($this, 'generateTooltip'))
            $this->displayExtra([$this, 'generateTooltip']);
        else if ($this->mode == CACHE_TYPE_XML && method_exists($this, 'generateXML'))
            $this->displayExtra([$this, 'generateXML'], MIME_TYPE_XML);
        else
        {
            if (isset($this->tabId))
                $this->pageTemplate['activeTab'] = $this->tabId;

            if ($override)
            {
                $this->addAnnouncements(false);

                include('template/pages/'.$override.'.tpl.php');
                die();
            }
            else if ($this->tpl)
            {
                $this->prepareContent();

                if (!$this->isSaneInclude('template/pages/', $this->tpl))
                {
                    trigger_error('Error: nonexistant template requested: template/pages/'.$this->tpl.'.tpl.php', E_USER_ERROR);
                    $this->error();
                }

                $this->addAnnouncements();

                if (isset($this->lvTabs))
                    array_walk($this->lvTabs, function (&$x) { $x = array_pad($x, 3, null); });

                include('template/pages/'.$this->tpl.'.tpl.php');
                die();
            }
            else
                $this->error();
        }
    }

    // generate and cache
    public function displayExtra(callable $generator, string $mime = MIME_TYPE_JSON) : void
    {
        $outString = '';
        if (!$this->loadCache($outString))
        {
            $outString = $generator();
            $this->saveCache($outString);
        }

        header($mime);

        if (method_exists($this, 'postCache') && ($pc = $this->postCache()))
            die(sprintf($outString, ...$pc));
        else
            die($outString);
    }

    // load jsGlobal
    public function writeGlobalVars() : string
    {
        $buff = '';

        if (!empty($this->guideRating))
            $buff .= sprintf(Util::$guideratingString, ...$this->guideRating);

        foreach ($this->jsGlobals as $type => $struct)
        {
            $buff .= "            var _ = ".$struct[0].';';

            foreach ($struct[1] as $key => $data)
            {
                foreach ($data as $k => $v)
                {
                    // localizes expected fields .. except for icons .. icons are special
                    if (in_array($k, ['name', 'namefemale']) && $struct[0] != Type::getJSGlobalString(Type::ICON))
                    {
                        $data[$k.'_'.User::$localeString] = $v;
                        unset($data[$k]);
                    }
                }

                $buff .= ' _['.(is_numeric($key) ? $key : "'".$key."'")."]=".Util::toJSON($data).';';
            }

            $buff .= "\n";

            if (!empty($this->typeId) && !empty($struct[2][$this->typeId]))
            {
                $x = $struct[2][$this->typeId];

                // spell
                if (!empty($x['tooltip']))                  // spell + item
                    $buff .= "\n            _[".$x['id'].'].tooltip_'.User::$localeString.' = '.Util::toJSON($x['tooltip']).";\n";
                if (!empty($x['buff']))                     // spell
                    $buff .= "            _[".$x['id'].'].buff_'.User::$localeString.' = '.Util::toJSON($x['buff']).";\n";
                if (!empty($x['spells']))                   // spell + item
                    $buff .= "            _[".$x['id'].'].spells_'.User::$localeString.' = '.Util::toJSON($x['spells']).";\n";
                if (!empty($x['buffspells']))               // spell
                    $buff .= "            _[".$x['id'].'].buffspells_'.User::$localeString.' = '.Util::toJSON($x['buffspells']).";\n";

                $buff .= "\n";
            }
        }

        return $buff;
    }

    // load brick
    public function brick(string $file, array $localVars = []) : void
    {
        foreach ($localVars as $n => $v)
            $$n = $v;

        if (!$this->isSaneInclude('template/bricks/', $file))
            trigger_error('Nonexistant template requested: template/bricks/'.$file.'.tpl.php', E_USER_ERROR);
        else
            include('template/bricks/'.$file.'.tpl.php');
    }

    // load listview addIns
    public function lvBrick(string $file) : void
    {
        if (!$this->isSaneInclude('template/listviews/', $file))
            trigger_error('Nonexistant Listview addin requested: template/listviews/'.$file.'.tpl.php', E_USER_ERROR);
        else
            include('template/listviews/'.$file.'.tpl.php');
    }

    // load brick with more text then vars
    public function localizedBrick(string $file, int $loc = LOCALE_EN) : void
    {
        if (!$this->isSaneInclude('template/localized/', $file.'_'.$loc))
        {
            if ($loc == LOCALE_EN || !$this->isSaneInclude('template/localized/', $file.'_'.LOCALE_EN))
                trigger_error('Nonexistant template requested: template/localized/'.$file.'_'.$loc.'.tpl.php', E_USER_ERROR);
            else
                include('template/localized/'.$file.'_'.LOCALE_EN.'.tpl.php');
        }
        else
            include('template/localized/'.$file.'_'.$loc.'.tpl.php');
    }


    /**********************/
    /* Prepare js-Globals */
    /**********************/

    // add typeIds <int|array[int]> that should be displayed as jsGlobal on the page
    public function extendGlobalIds(int $type, int ...$ids) : void
    {
        if (!$type || !$ids)
            return;

        if (!isset($this->jsgBuffer[$type]))
            $this->jsgBuffer[$type] = [];

        foreach ($ids as $id)
            $this->jsgBuffer[$type][] = $id;
    }

    // add jsGlobals or typeIds (can be mixed in one array: TYPE => [mixeddata]) to display on the page
    public function extendGlobalData(array $data, ?array $extra = null) : void
    {
        foreach ($data as $type => $globals)
        {
            if (!is_array($globals) || !$globals)
                continue;

            $this->initJSGlobal($type);

            // can be  id => data
            // or     idx => id
            // and may be mixed
            foreach ($globals as $k => $v)
            {
                if (is_array($v))
                    $this->jsGlobals[$type][1][$k] = $v;
                else if (is_numeric($v))
                    $this->extendGlobalIds($type, $v);
            }
        }

        if (is_array($extra) && $extra)
            $this->jsGlobals[$type][2] = $extra;
    }

    // init store for type
    private function initJSGlobal(int $type) : void
    {
        $jsg = &$this->jsGlobals;                           // shortcut

        if (isset($jsg[$type]))
            return;

        if ($tpl = Type::getJSGlobalTemplate($type))
            $jsg[$type] = $tpl;
    }

    // lookup jsGlobals from collected typeIds
    private function applyGlobals() : void
    {
        foreach ($this->jsgBuffer as $type => $ids)
        {
            foreach ($ids as $k => $id)                     // filter already generated data, maybe we can save a lookup or two
                if (isset($this->jsGlobals[$type][1][$id]))
                    unset($ids[$k]);

            if (!$ids)
                continue;

            $this->initJSGlobal($type);

            $obj = Type::newList($type, [CFG_SQL_LIMIT_NONE, ['id', array_unique($ids, SORT_NUMERIC)]]);
            if (!$obj)
                continue;

            $this->extendGlobalData($obj->getJSGlobals(GLOBALINFO_SELF));

            // delete processed ids
            $this->jsgBuffer[$type] = [];
        }
    }


    /*********/
    /* Cache */
    /*********/

    // visible properties or given strings are cached
    private function saveCache(string $saveString = '') : void
    {
        if ($this->mode == CACHE_TYPE_NONE)
            return;

        if (!CFG_CACHE_MODE || CFG_DEBUG)
            return;

        $noCache = ['coError', 'ssError', 'viError'];
        $cKey    = $this->generateCacheKey();
        $cache   = [];
        if (!$saveString)
        {
            foreach ($this as $key => $val)
            {
                try
                {
                    $rp = new ReflectionProperty($this, $key);
                    if ($rp && ($rp->isPublic() || $rp->isProtected()))
                        if (!in_array($key, $noCache))
                            $cache[$key] = $val;
                }
                catch (ReflectionException $e) { }          // shut up!
            }
        }
        else
            $cache = $saveString;

        if (CFG_CACHE_MODE & CACHE_MODE_MEMCACHED)
        {
            // on &refresh also clear related
            if ($this->skipCache == CACHE_MODE_MEMCACHED)
            {
                $oldMode = $this->mode;
                for ($i = 1; $i < 5; $i++)                  // page (1), tooltips (2), searches (3) and xml (4)
                {
                    $this->mode = $i;
                    for ($j = 0; $j < 2; $j++)              // staff / normal
                        $this->memcached()->delete($this->generateCacheKey($j));
                }

                $this->mode = $oldMode;
            }

            $data = array(
                'timestamp' => time(),
                'revision'  => AOWOW_REVISION,
                'isString'  => $saveString ? 1 : 0,
                'data'      => $cache
            );

            $this->memcached()->set($cKey, $data);
        }

        if (CFG_CACHE_MODE & CACHE_MODE_FILECACHE)
        {
            $data  = time()." ".AOWOW_REVISION." ".($saveString ? '1' : '0')."\n";
            $data .= gzcompress($saveString ? $cache : serialize($cache), 9);

            // on &refresh also clear related
            if ($this->skipCache == CACHE_MODE_FILECACHE)
            {
                $oldMode = $this->mode;
                for ($i = 1; $i < 5; $i++)                  // page (1), tooltips (2), searches (3) and xml (4)
                {
                    $this->mode = $i;
                    for ($j = 0; $j < 2; $j++)              // staff / normal
                    {
                        $key = $this->generateCacheKey($j);
                        if (file_exists($this->cacheDir.$key))
                            unlink($this->cacheDir.$key);
                    }
                }

                $this->mode = $oldMode;
            }

            file_put_contents($this->cacheDir.$cKey, $data);
        }
    }

    private function loadCache(string &$saveString = '') : bool
    {
        if ($this->mode == CACHE_TYPE_NONE)
            return false;

        if (!CFG_CACHE_MODE || CFG_DEBUG)
            return false;

        $cKey = $this->generateCacheKey();
        $rev = $type = $cache = $data = null;

        if ((CFG_CACHE_MODE & CACHE_MODE_MEMCACHED) && !($this->skipCache & CACHE_MODE_MEMCACHED))
        {
            if ($cache = $this->memcached()->get($cKey))
            {
                $type = $cache['isString'];
                $data = $cache['data'];

                if ($cache['timestamp'] + CFG_CACHE_DECAY <= time() || $cache['revision'] != AOWOW_REVISION)
                    $cache = null;
                else
                    $this->cacheLoaded = [CACHE_MODE_MEMCACHED, $cache['timestamp']];
            }
        }

        if (!$cache && (CFG_CACHE_MODE & CACHE_MODE_FILECACHE) && !($this->skipCache & CACHE_MODE_FILECACHE))
        {
            if (!file_exists($this->cacheDir.$cKey))
                return false;

            $cache = file_get_contents($this->cacheDir.$cKey);
            if (!$cache)
                return false;

            $cache = explode("\n", $cache, 2);
            $data  = $cache[1];
            if (substr_count($cache[0], ' ') < 2)
                return false;

            [$time, $rev, $type] = explode(' ', $cache[0]);

            if ($time + CFG_CACHE_DECAY <= time() || $rev != AOWOW_REVISION)
                $cache = null;
            else
            {
                $this->cacheLoaded = [CACHE_MODE_FILECACHE, $time];
                $data = gzuncompress($data);
            }
        }

        if (!$cache)
            return false;

        if ($type == '0')
        {
            if (is_string($data))
                $data = unserialize($data);

            foreach ($data as $k => $v)
                $this->$k = $v;

            return true;
        }
        else if ($type == '1')
        {
            $saveString = $data;
            return true;
        }

        return false;;
    }

    private function memcached() : Memcached
    {
        if (!$this->memcached && (CFG_CACHE_MODE & CACHE_MODE_MEMCACHED))
        {
            $this->memcached = new Memcached();
            $this->memcached->addServer('localhost', 11211);
        }

        return $this->memcached;
    }
}

?>
