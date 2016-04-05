<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


trait DetailPage
{
    protected $hasComContent = true;
    protected $category      = null;                        // not used on detail pages
    protected $lvTabs        = [];                          // most pages have this

    protected $ssError       = null;
    protected $coError       = null;
    protected $viError       = null;

    protected $subject       = null;                        // so it will not get cached

    protected function generateCacheKey($withStaff = true)
    {
        $staff = intVal($withStaff && User::isInGroup(U_GROUP_EMPLOYEE));

        //     mode,         type,        typeId,        employee-flag, localeId,        category, filter
        $key = [$this->mode, $this->type, $this->typeId, $staff,        User::$localeId, '-1',     '-1'];

        // item special: can modify tooltips
        if (isset($this->enhancedTT))
            $key[] = md5(serialize($this->enhancedTT));

        return implode('_', $key);
    }


    protected function applyCCErrors()
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


trait ListPage
{
    protected $category  = null;
    protected $filter    = [];
    protected $lvTabs    = [];                              // most pages have this

    private   $filterObj = null;

    protected function generateCacheKey($withStaff = true)
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


class GenericPage
{
    protected $tpl          = '';
    protected $reqUGroup    = U_GROUP_NONE;
    protected $reqAuth      = false;
    protected $mode         = CACHE_TYPE_NONE;

    protected $jsGlobals    = [];
    protected $lvData       = [];
    protected $title        = [CFG_NAME];                   // for title-Element
    protected $name         = '';                           // for h1-Element
    protected $tabId        = null;
    protected $gDataKey     = false;                        // adds the dataKey to the user vars
    protected $js           = [];
    protected $css          = [];

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

    private   $lvTemplates  = array(
        'achievement'       => ['template' => 'achievement',       'id' => 'achievements',    'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_achievements'  ],
        'calendar'          => ['template' => 'holidaycal',        'id' => 'calendar',        'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_calendar'      ],
        'class'             => ['template' => 'classs',            'id' => 'classes',         'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_classes'       ],
        'commentpreview'    => ['template' => 'commentpreview',    'id' => 'comments',        'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_comments'      ],
        'creature'          => ['template' => 'npc',               'id' => 'npcs',            'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_npcs'          ],
        'currency'          => ['template' => 'currency',          'id' => 'currencies',      'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_currencies'    ],
        'emote'             => ['template' => 'emote',             'id' => 'emotes',          'parent' => 'lv-generic', 'data' => []                                      ],
        'enchantment'       => ['template' => 'enchantment',       'id' => 'enchantments',    'parent' => 'lv-generic', 'data' => []                                      ],
        'event'             => ['template' => 'holiday',           'id' => 'holidays',        'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_holidays'      ],
        'faction'           => ['template' => 'faction',           'id' => 'factions',        'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_factions'      ],
        'genericmodel'      => ['template' => 'genericmodel',      'id' => 'same-model-as',   'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_samemodelas'   ],
        'item'              => ['template' => 'item',              'id' => 'items',           'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_items'         ],
        'itemset'           => ['template' => 'itemset',           'id' => 'itemsets',        'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_itemsets'      ],
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
        'spell'             => ['template' => 'spell',             'id' => 'spells',          'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_spells'        ],
        'title'             => ['template' => 'title',             'id' => 'titles',          'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_titles'        ],
        'video'             => ['template' => 'video',             'id' => 'videos',          'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_videos'        ],
        'zone'              => ['template' => 'zone',              'id' => 'zones',           'parent' => 'lv-generic', 'data' => [], 'name' => '$LANG.tab_zones'         ]
    );

    public function __construct($pageCall/*, $pageParam */)
    {
        $this->time = microtime(true);

        if (CFG_CACHE_DIR && Util::checkOrCreateDirectory(CFG_CACHE_DIR))
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

        // display modes
        if (isset($_GET['power']) && method_exists($this, 'generateTooltip'))
            $this->mode = CACHE_TYPE_TOOLTIP;
        else if (isset($_GET['xml']) && method_exists($this, 'generateXML'))
            $this->mode = CACHE_TYPE_XML;
        else
        {
            $this->gUser   = User::getUserGlobals();
            $this->pageTemplate['pageName'] = strtolower($pageCall);

            if (!$this->isValidPage())
                $this->error();
        }

        // requires authed user
        if ($this->reqAuth && !User::$id)
            $this->forwardToSignIn($_SERVER['QUERY_STRING']);

        // restricted access
        if ($this->reqUGroup && !User::isInGroup($this->reqUGroup))
        {
            if (User::$id)
                $this->error();
            else
                $this->forwardToSignIn($_SERVER['QUERY_STRING']);
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

    private function isSaneInclude($path, $file)            // "template_exists"
    {
        if (preg_match('/[^\w\-]/i', str_replace('admin/', '', $file)))
            return false;

        if (!is_file($path.$file.'.tpl.php'))
            return false;

        return true;
    }

    private function isValidPage()                          // has a valid combination of categories
    {
        if (!isset($this->category) || empty($this->validCats))
            return true;

        switch (count($this->category))
        {
            case 0: // no params works always
                return true;
            case 1: // null is valid               || value in a 1-dim-array                         || key for a n-dim-array
                return $this->category[0] === null || in_array($this->category[0], $this->validCats) || !empty($this->validCats[$this->category[0]]);
            case 2: // first param has to be a key. otherwise invalid
                if (!isset($this->validCats[$this->category[0]]))
                    return false;

                // check if the sub-array is n-imensional
                if (count($this->validCats[$this->category[0]]) == count($this->validCats[$this->category[0]], COUNT_RECURSIVE))
                    return in_array($this->category[1], $this->validCats[$this->category[0]]); // second param is value in second level array
                else
                    return isset($this->validCats[$this->category[0]][$this->category[1]]);    // check if params is key of another array
            case 3: // 3 params MUST point to a specific value
                return isset($this->validCats[$this->category[0]][$this->category[1]]) && in_array($this->category[2], $this->validCats[$this->category[0]][$this->category[1]]);
        }

        return false;
    }

    /****************/
    /* Prepare Page */
    /****************/

    private function prepareContent()                       // get from cache ?: run generators
    {
        if (!$this->loadCache())
        {
            $this->addArticle();

            $this->generatePath();
            $this->generateTitle();
            $this->generateContent();

            $this->applyGlobals();

            $this->saveCache();
        }

        if (isset($this->type) && isset($this->typeId))
            $this->gPageInfo = array(                       // varies slightly for special pages like maps, user-dashboard or profiler
                'type'   => $this->type,
                'typeId' => $this->typeId,
                'name'   => $this->name
            );

        if (!empty($this->path))
            $this->pageTemplate['breadcrumb'] = $this->path;

        if (!empty($this->filter))
            $this->pageTemplate['filter'] = empty($this->filter['query']) ? 0 : 1;

        if (method_exists($this, 'postCache'))              // e.g. update dates for events and such
            $this->postCache();

        if (!empty($this->hasComContent))                   // get comments, screenshots, videos
        {
            $this->community = CommunityContent::getAll($this->type, $this->typeId, $jsGlobals);
            $this->extendGlobalData($jsGlobals);            // as comments are not cached, those globals cant be either
            $this->applyGlobals();
        }

        $this->time = microtime(true) - $this->time;
        Util::arraySumByKey($this->mysql, DB::Aowow()->getStatistics(), DB::World()->getStatistics());
    }

    public function addJS($name, $unshift = false)
    {
        if (is_array($name))
        {
            foreach ($name as $n)
                $this->addJS($n, $unshift);
        }
        else if (!in_array($name, $this->js))
        {
            if ($unshift)
                array_unshift($this->js, $name);
            else
                $this->js[] = $name;
        }
    }

    public function addCSS($struct, $unshift = false)
    {
        if (is_array($struct) && empty($struct['path']) && empty($struct['string']))
        {
            foreach ($struct as $s)
                $this->addCSS($s, $unshift);
        }
        else if (!in_array($struct, $this->css))
        {
            if ($unshift)
                array_unshift($this->css, $struct);
            else
                $this->css[] = $struct;
        }
    }

    private function addArticle()                           // get article & static infobox (run before processing jsGlobals)
    {
        if (empty($this->type) || !isset($this->typeId))
            return;

        $article = DB::Aowow()->selectRow(
            'SELECT article, quickInfo, locale FROM ?_articles WHERE type = ?d AND typeId = ?d AND locale = ?d UNION ALL '.
            'SELECT article, quickInfo, locale FROM ?_articles WHERE type = ?d AND typeId = ?d AND locale = 0  ORDER BY locale DESC LIMIT 1',
            $this->type, $this->typeId, User::$localeId,
            $this->type, $this->typeId
        );

        if ($article)
        {
            foreach ($article as $text)
                (new Markup($text))->parseGlobalsFromText($this->jsgBuffer);

            $replace = array(
                '<script'    => '<scr"+"ipt',
                'script>'    => 'scr"+"ipt>',
                'HOST_URL'   => HOST_URL,
                'STATIC_URL' => STATIC_URL
            );

            $this->article = array(
                'text'   => strtr($article['article'], $replace),
                'params' => []
            );

            if (empty($this->infobox) && !empty($article['quickInfo']))
                $this->infobox = $article['quickInfo'];

            if ($article['locale'] != User::$localeId)
                $this->article['params'] = ['prepend' => Util::jsEscape('<div class="notice-box"><span class="icon-bubble">'.Lang::main('englishOnly').'</span></div>')];
        }
    }

    private function addAnnouncements()                     // get announcements and notes for user
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
                'style'  => 'padding-left: 40px; background-image: url('.STATIC_URL.'/images/announcements/warn-small.png); background-size: 15px 15px; background-position: 12px center; border: dashed 2px #C03030;',
                'text'   => '[span id=inputbox-error]'.implode("[br]", $_).'[/span]',
            );
        }

        // fetch announcements
        if ($this->pageTemplate['pageName'])
        {
            $ann = DB::Aowow()->Select('SELECT ABS(id) AS ARRAY_KEY, a.* FROM ?_announcements a WHERE status = 1 AND (page = ? OR page = "*") AND (groupMask = 0 OR groupMask & ?d)', $this->pageTemplate['pageName'], User::$groups);
            foreach ($ann as $k => $v)
            {
                if ($t = Util::localizedString($v, 'text'))
                {
                    $replace = array(
                        'HOST_URL'   => HOST_URL,
                        'STATIC_URL' => STATIC_URL
                    );

                    $_ = array(
                        'parent' => 'announcement-'.$k,
                        'id'     => $v['id'],
                        'mode'   => $v['mode'],
                        'status' => $v['status'],
                        'name'   => $v['name'],
                        'text'   => strtr($t, $replace)
                    );

                    if ($v['style'])                        // may be empty
                        $_['style'] = strtr($v['style'], $replace);

                    $this->announcements[$k] = $_;
                }
            }
        }
    }

    protected function getCategoryFromUrl($str)
    {
        $arr    = explode('.', $str);
        $params = [];

        foreach ($arr as $v)
            if (is_numeric($v))
                $params[] = (int)$v;

        $this->category = $params;
    }

    protected function forwardToSignIn($next = '')
    {
        $next = $next ? '&next='.$next : '';
        header('Location: ?account=signin'.$next, true, 302);
    }

    /*******************/
    /* Special Display */
    /*******************/

    public function notFound($title, $msg = '')             // unknown entry
    {
        array_unshift($this->title, Lang::main('nfPageTitle'));

        $this->notFound      = array(
            'title' =>          isset($this->typeId) ? Util::ucFirst($title).' #'.$this->typeId    : $title,
            'msg'   => !$msg && isset($this->typeId) ? sprintf(Lang::main('pageNotFound'), $title) : $msg
        );
        $this->hasComContent = false;
        Util::arraySumByKey($this->mysql, DB::Aowow()->getStatistics(), DB::World()->getStatistics());

        if (isset($this->tabId))
            $this->pageTemplate['activeTab'] = $this->tabId;

        $this->display('text-page-generic');
        exit();
    }

    public function error()                                 // unknown page
    {
        $this->path    = null;
        $this->tabId   = null;
        $this->type    = -99;                               // get error-article
        $this->typeId  = 0;
        $this->title[] = Lang::main('errPageTitle');
        $this->name    = Lang::main('errPageTitle');

        $this->addArticle();

        Util::arraySumByKey($this->mysql, DB::Aowow()->getStatistics(), DB::World()->getStatistics());

        $this->display('text-page-generic');
        exit();
    }

    public function maintenance()                           // display brb gnomes
    {
        $this->display('maintenance');
        exit();
    }

    /*******************/
    /* General Display */
    /*******************/

    public function display($override = '')                 // load given template string or GenericPage::$tpl
    {
        // Heisenbug: IE11 and FF32 will sometimes (under unknown circumstances) cache 302 redirects and stop
        // re-requesting them from the server but load them from local cache, thus breaking menu features.
        Util::sendNoCacheHeader();

        if (isset($this->tabId))
            $this->pageTemplate['activeTab'] = $this->tabId;

        if ($override)
        {
            $this->addAnnouncements();

            include('template/pages/'.$override.'.tpl.php');
            die();
        }
        else if ($this->tpl)
        {
            $this->prepareContent();

            if (!$this->isSaneInclude('template/pages/', $this->tpl))
                die(User::isInGroup(U_GROUP_EMPLOYEE) ? 'Error: nonexistant template requested: template/pages/'.$this->tpl.'.tpl.php' : null);

            $this->addAnnouncements();

            include('template/pages/'.$this->tpl.'.tpl.php');
            die();
        }
        else
            $this->error();
    }

    public function writeGlobalVars()                       // load jsGlobal
    {
        $buff = '';

        foreach ($this->jsGlobals as $type => $struct)
        {
            $buff .= "            var _ = ".$struct[0].';';

            foreach ($struct[1] as $key => $data)
            {
                foreach ($data as $k => $v)
                {
                    // localizes expected fields
                    if (in_array($k, ['name', 'namefemale']))
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

    public function brick($file, array $localVars = [])     // load brick
    {
        foreach ($localVars as $n => $v)
            $$n = $v;

        if (!$this->isSaneInclude('template/bricks/', $file))
            trigger_error("Nonexistant template requested: template/bricks/".$file.".tpl.php");
        else
            include('template/bricks/'.$file.'.tpl.php');
    }

    public function lvBrick($file)                          // load listview addIns
    {
        if (!$this->isSaneInclude('template/listviews/', $file))
            trigger_error('Nonexistant Listview addin requested: template/listviews/'.$file.'.tpl.php');
        else
            include('template/listviews/'.$file.'.tpl.php');
    }

    public function localizedBrick($file, $loc = LOCALE_EN) // load brick with more text then vars
    {
        if (!$this->isSaneInclude('template/localized/', $file.'_'.$loc))
        {
            if ($loc == LOCALE_EN || !$this->isSaneInclude('template/localized/', $file.'_'.LOCALE_EN))
                trigger_error("Nonexistant template requested: template/localized/".$file.'_'.$loc.".tpl.php");
            else
                include('template/localized/'.$file.'_'.LOCALE_EN.'.tpl.php');
        }
        else
            include('template/localized/'.$file.'_'.$loc.'.tpl.php');
    }

    /**********************/
    /* Prepare js-Globals */
    /**********************/

    public function extendGlobalIds($type, $data)           // add typeIds <int|array[int]> that should be displayed as jsGlobal on the page
    {
        if (!$type || !$data)
            return false;

        if (!isset($this->jsgBuffer[$type]))
            $this->jsgBuffer[$type] = [];

        if (is_array($data))
        {
            foreach ($data as $id)
                $this->jsgBuffer[$type][] = (int)$id;
        }
        else if (is_numeric($data))
            $this->jsgBuffer[$type][] = (int)$data;
    }

    public function extendGlobalData($data, $extra = null)  // add jsGlobals or typeIds (can be mixed in one array: TYPE => [mixeddata]) to display on the page
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

    private function initJSGlobal($type)                    // init store for type
    {
        $jsg = &$this->jsGlobals;                           // shortcut

        if (isset($jsg[$type]))
            return;

        switch ($type)
        {                                                // [varName,            [data], [extra]]
            case TYPE_NPC:         $jsg[TYPE_NPC]         = ['g_npcs',               [], []]; break;
            case TYPE_OBJECT:      $jsg[TYPE_OBJECT]      = ['g_objects',            [], []]; break;
            case TYPE_ITEM:        $jsg[TYPE_ITEM]        = ['g_items',              [], []]; break;
            case TYPE_ITEMSET:     $jsg[TYPE_ITEMSET]     = ['g_itemsets',           [], []]; break;
            case TYPE_QUEST:       $jsg[TYPE_QUEST]       = ['g_quests',             [], []]; break;
            case TYPE_SPELL:       $jsg[TYPE_SPELL]       = ['g_spells',             [], []]; break;
            case TYPE_ZONE:        $jsg[TYPE_ZONE]        = ['g_gatheredzones',      [], []]; break;
            case TYPE_FACTION:     $jsg[TYPE_FACTION]     = ['g_factions',           [], []]; break;
            case TYPE_PET:         $jsg[TYPE_PET]         = ['g_pets',               [], []]; break;
            case TYPE_ACHIEVEMENT: $jsg[TYPE_ACHIEVEMENT] = ['g_achievements',       [], []]; break;
            case TYPE_TITLE:       $jsg[TYPE_TITLE]       = ['g_titles',             [], []]; break;
            case TYPE_WORLDEVENT:  $jsg[TYPE_WORLDEVENT]  = ['g_holidays',           [], []]; break;
            case TYPE_CLASS:       $jsg[TYPE_CLASS]       = ['g_classes',            [], []]; break;
            case TYPE_RACE:        $jsg[TYPE_RACE]        = ['g_races',              [], []]; break;
            case TYPE_SKILL:       $jsg[TYPE_SKILL]       = ['g_skills',             [], []]; break;
            case TYPE_CURRENCY:    $jsg[TYPE_CURRENCY]    = ['g_gatheredcurrencies', [], []]; break;
            // well, this is awkward
            case TYPE_USER:        $jsg[TYPE_USER]        = ['g_users',              [], []]; break;
            case TYPE_EMOTE:       $jsg[TYPE_EMOTE]       = ['g_emotes',             [], []]; break;
            case TYPE_ENCHANTMENT: $jsg[TYPE_ENCHANTMENT] = ['g_enchantments',       [], []]; break;
        }
    }

    private function applyGlobals()                         // lookup jsGlobals from collected typeIds
    {
        foreach ($this->jsgBuffer as $type => $ids)
        {
            foreach ($ids as $k => $id)                     // filter already generated data, maybe we can save a lookup or two
                if (isset($this->jsGlobals[$type][1][$id]))
                    unset($ids[$k]);

            if (!$ids)
                continue;

            $this->initJSGlobal($type);

            $cnd = [CFG_SQL_LIMIT_NONE, ['id', array_unique($ids, SORT_NUMERIC)]];

            switch ($type)
            {
                case TYPE_NPC:         $obj = new CreatureList($cnd);    break;
                case TYPE_OBJECT:      $obj = new GameobjectList($cnd);  break;
                case TYPE_ITEM:        $obj = new ItemList($cnd);        break;
                case TYPE_ITEMSET:     $obj = new ItemsetList($cnd);     break;
                case TYPE_QUEST:       $obj = new QuestList($cnd);       break;
                case TYPE_SPELL:       $obj = new SpellList($cnd);       break;
                case TYPE_ZONE:        $obj = new ZoneList($cnd);        break;
                case TYPE_FACTION:     $obj = new FactionList($cnd);     break;
                case TYPE_PET:         $obj = new PetList($cnd);         break;
                case TYPE_ACHIEVEMENT: $obj = new AchievementList($cnd); break;
                case TYPE_TITLE:       $obj = new TitleList($cnd);       break;
                case TYPE_WORLDEVENT:  $obj = new WorldEventList($cnd);  break;
                case TYPE_CLASS:       $obj = new CharClassList($cnd);   break;
                case TYPE_RACE:        $obj = new CharRaceList($cnd);    break;
                case TYPE_SKILL:       $obj = new SkillList($cnd);       break;
                case TYPE_CURRENCY:    $obj = new CurrencyList($cnd);    break;
                // "um, eh":, he ums and ehs.
                case TYPE_USER:        $obj = new UserList($cnd);        break;
                case TYPE_EMOTE:       $obj = new EmoteList($cnd);       break;
                case TYPE_ENCHANTMENT: $obj = new EnchantmentList($cnd); break;
                default: continue;
            }

            $this->extendGlobalData($obj->getJSGlobals(GLOBALINFO_SELF));

            // delete processed ids
            $this->jsgBuffer[$type] = [];
        }
    }

    /*********/
    /* Cache */
    /*********/

    public function saveCache($saveString = null)           // visible properties or given strings are cached
    {
        if ($this->mode == CACHE_TYPE_NONE)
            return false;

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
                    // public, protected and an undocumented flag added to properties created on the fly..?
                    if ((new ReflectionProperty($this, $key))->getModifiers() & 0x1300)
                        if (!in_array($key, $noCache))
                            $cache[$key] = $val;
                }
                catch (ReflectionException $e) { }          // shut up!
            }
        }
        else
            $cache = (string)$saveString;

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

    public function loadCache(&$saveString = null)
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

                if ($cache['timestamp'] + CFG_CACHE_DECAY <= time() || $cache['revision'] < AOWOW_REVISION)
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

            list($time, $rev, $type) = explode(' ', $cache[0]);

            if ($time + CFG_CACHE_DECAY <= time() || $rev < AOWOW_REVISION)
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

    private function memcached()
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
