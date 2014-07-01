<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


trait DetailPage
{
    protected $hasComContent = true;
    protected $category      = null;                        // not used on detail pages

    private   $subject       = null;                        // so it will not get cached

    protected function generateCacheKey()
    {
        //     mode,         type,        typeId,        employee-flag,                             localeId,        category, filter
        $key = [$this->mode, $this->type, $this->typeId, intVal(User::isInGroup(U_GROUP_EMPLOYEE)), User::$localeId, '-1',     '-1'];

        // item special: can modify tooltips
        if (isset($this->enhancedTT))
            $key[] = md5(serialize($this->enhancedTT));

        return implode('_', $key);
    }
}


trait ListPage
{
    protected $category  = null;
    protected $typeId    = 0;
    protected $filter    = [];

    protected function generateCacheKey()
    {
        //     mode,         type,        typeId, employee-flag,                             localeId,
        $key = [$this->mode, $this->type, '-1',   intVal(User::isInGroup(U_GROUP_EMPLOYEE)), User::$localeId];

        //category
        $key[] = $this->category ? implode('.', $this->category) : '-1';

        // filter
        $key[] = $this->filter ? md5(serialize($this->filter)) : '-1';

        return implode('_', $key);
    }
}


class GenericPage
{
    protected $tpl              = '';
    protected $restrictedGroups = U_GROUP_NONE;
    protected $mode             = CACHETYPE_NONE;

    protected $jsGlobals        = [];
    protected $lvData           = [];
    protected $title            = [CFG_NAME];               // for title-Element
    protected $name             = '';                       // for h1-Element
    protected $tabId            = 0;
    protected $js               = [];
    protected $css              = [];

    // private vars don't get cached
    private   $time             = 0;
    private   $isCached         = false;
    private   $cacheDir         = 'cache/template/';
    private   $jsgBuffer        = [];
    private   $gLocale          = [];
    private   $gPageInfo        = [];
    private   $gUser            = [];
    private   $community        = ['co' => [], 'sc' => [], 'vi' => []];

    public function __construct()
    {
        $this->time = microtime(true);

        // restricted access
        if ($this->restrictedGroups && !User::isInGroup($this->restrictedGroups))
            $this->error();

        if (CFG_MAINTENANCE && !User::isInGroup(U_GROUP_EMPLOYEE))
            $this->maintenance();
        else if (CFG_MAINTENANCE && User::isInGroup(U_GROUP_EMPLOYEE))
            Util::addNote(U_GROUP_EMPLOYEE, 'Maintenance mode enabled!');

        // display modes
        if (isset($_GET['power']) && method_exists($this, 'generateTooltip'))
            $this->mode = CACHETYPE_TOOLTIP;
        else if (isset($_GET['xml']) && method_exists($this, 'generateXML'))
            $this->mode = CACHETYPE_XML;
        else
        {
            $this->gUser   = User::getUserGlobals();
            $this->gLocale = array(
                'id'   => User::$localeId,
                'name' => User::$localeString
            );

            if (!$this->isValidPage() || !$this->tpl)
                $this->error();
        }
    }

    /**********/
    /* Checks */
    /**********/

    private function isSaneInclude($path, $file)            // "template_exists"
    {
        if (preg_match('/[^\w\-]/i', $file))
            return false;

        if (!is_file(CWD.$path.$file.'.tpl.php'))
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
        else
            $this->isCached = true;

        if (isset($this->type) && isset($this->typeId))
            $this->gPageInfo = array(                       // varies slightly for special pages like maps, user-dashboard or profiler
                'type'   => $this->type,
                'typeId' => $this->typeId,
                'name'   => $this->name
            );

        if (method_exists($this, 'postCache'))              // e.g. update dates for events and such
            $this->postCache();

        if (!empty($this->hasComContent))                   // get comments, screenshots, videos
            $this->community = CommunityContent::getAll($this->type, $this->typeId);

        $this->time  = microtime(true) - $this->time;
        $this->mysql = DB::Aowow()->getStatistics();
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
        if (empty($this->type) && !isset($this->typeId))
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
            {
                if (preg_match_all('/\[(npc|object|item|itemset|quest|spell|zone|faction|pet|achievement|title|event|class|race|skill|currency)=(\d+)[^\]]*\]/i', $text, $matches, PREG_SET_ORDER))
                {
                    foreach ($matches as $match)
                    {
                        if ($type = array_search($match[1], Util::$typeStrings))
                        {
                            if (!isset($this->jsgBuffer[$type]))
                                $this->jsgBuffer[$type] = [];

                            $this->jsgBuffer[$type][] = $match[2];
                        }
                    }
                }
            }

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
                $this->article['params'] = ['prepend' => Util::jsEscape('<div class="notice-box"><span class="icon-bubble">'.Lang::$main['englishOnly'].'</span></div>')];
        }
    }

    private function addAnnouncements()                     // get announcements and notes for user
    {
        if (!isset($this->announcements))
            $this->announcements = [];

        // display occured notices
        if ($_ = Util::getNotes(false))
        {
            $this->announcements[] = array(
                'id'     => 0,
                'mode'   => 1,
                'status' => 1,
                'name'   => 'internal error',
                'style'  => 'padding-left: 40px; background-image: url(static/images/announcements/warn-small.png); background-size: 15px 15px; background-position: 12px center; border: dashed 2px #C03030;',
                'text'   => '[span id=inputbox-error]'.implode("[br]", $_).'[/span]',
            );
        }

        // fetch announcements
        if (preg_match('/^([a-z\-]+)=?.*$/i', $_SERVER['QUERY_STRING'], $match))
        {
            $ann = DB::Aowow()->Select('SELECT * FROM ?_announcements WHERE status = 1 AND (page = ? OR page = "*") AND (groupMask = 0 OR groupMask & ?d)', $match[1], User::$groups);
            foreach ($ann as $k => $v)
            {
                if ($t = Util::localizedString($v, 'text'))
                {
                    $ann[$k]['text'] = $t;
                    $this->announcements[] = $ann[$k];
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

    /*******************/
    /* Special Display */
    /*******************/

    public function notFound($typeStr)                      // unknown ID
    {
        $this->typeStr = $typeStr;
        $this->mysql   = DB::Aowow()->getStatistics();

        $this->display('text-page-generic');
        exit();
    }

    public function error()                                 // unknown page
    {
        $this->type    = -99;                               // get error-article
        $this->typeId  = 0;
        $this->title[] = Lang::$main['errPageTitle'];
        $this->name    = Lang::$main['errPageTitle'];

        $this->addArticle();

        $this->mysql   = DB::Aowow()->getStatistics();

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
        if ($override)
        {
            $this->addAnnouncements();

            include('template/pages/'.$override.'.tpl.php');
            die();
        }
        else
        {
            $this->prepareContent();

            if (!$this->isSaneInclude('template/pages/', $this->tpl))
                die(User::isInGroup(U_GROUP_EMPLOYEE) ? 'Error: nonexistant template requested: template/pages/'.$this->tpl.'.tpl.php' : null);

            $this->addAnnouncements();

            include('template/pages/'.$this->tpl.'.tpl.php');
            die();
        }
    }

    public function gBrick($file, array $localVars = [])    // load jsGlobal
    {
        foreach ($localVars as $n => $v)
            $$n = $v;

        if (!$this->isSaneInclude('template/globals/', $file))
            echo !User::isInGroup(U_GROUP_EMPLOYEE) ? "\n\nError: nonexistant template requested: template/globals/".$file.".tpl.php\n\n" : null;
        else
            include('template/globals/'.$file.'.tpl.php');
    }

    public function brick($file, array $localVars = [])     // load brick
    {
        foreach ($localVars as $n => $v)
            $$n = $v;

        if (!$this->isSaneInclude('template/bricks/', $file))
            echo User::isInGroup(U_GROUP_EMPLOYEE) ? "\n\nError: nonexistant template requested: template/bricks/".$file.".tpl.php\n\n" : null;
        else
            include('template/bricks/'.$file.'.tpl.php');
    }

    public function lvBrick($file, array $localVars = [])   // load listview
    {
        foreach ($localVars as $n => $v)
            $$n = $v;

        if (!$this->isSaneInclude('template/listviews/', $file))
            echo User::isInGroup(U_GROUP_EMPLOYEE) ? "\n\nError: nonexistant template requested: template/listviews/".$file.".tpl.php\n\n" : null;
        else
            include('template/listviews/'.$file.'.tpl.php');
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
        {                                                // [brickFile,  [data], [extra]]
            case TYPE_NPC:         $jsg[TYPE_NPC]         = ['creature',    [], []]; break;
            case TYPE_OBJECT:      $jsg[TYPE_OBJECT]      = ['object',      [], []]; break;
            case TYPE_ITEM:        $jsg[TYPE_ITEM]        = ['item',        [], []]; break;
            case TYPE_QUEST:       $jsg[TYPE_QUEST]       = ['quest',       [], []]; break;
            case TYPE_SPELL:       $jsg[TYPE_SPELL]       = ['spell',       [], []]; break;
            case TYPE_ZONE:        $jsg[TYPE_ZONE]        = ['zone',        [], []]; break;
            case TYPE_FACTION:     $jsg[TYPE_FACTION]     = ['faction',     [], []]; break;
            case TYPE_PET:         $jsg[TYPE_PET]         = ['pet',         [], []]; break;
            case TYPE_ACHIEVEMENT: $jsg[TYPE_ACHIEVEMENT] = ['achievement', [], []]; break;
            case TYPE_TITLE:       $jsg[TYPE_TITLE]       = ['title',       [], []]; break;
            case TYPE_WORLDEVENT:  $jsg[TYPE_WORLDEVENT]  = ['event',       [], []]; break;
            case TYPE_CLASS:       $jsg[TYPE_CLASS]       = ['class',       [], []]; break;
            case TYPE_RACE:        $jsg[TYPE_RACE]        = ['race',        [], []]; break;
            case TYPE_SKILL:       $jsg[TYPE_SKILL]       = ['skill',       [], []]; break;
            case TYPE_CURRENCY:    $jsg[TYPE_CURRENCY]    = ['currency',    [], []]; break;
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

            // todo (med): properly distinguish holidayId and eventId
            $cnd = [CFG_SQL_LIMIT_NONE];
            if ($type == TYPE_WORLDEVENT)
            {
                $hIds = array_filter($ids, function($v) { return $v > 0; });
                $eIds = array_filter($ids, function($v) { return $v < 0; });

                if ($hIds)
                    $cnd[] = ['holidayId', array_unique($hIds, SORT_NUMERIC)];

                if ($eIds)
                    $cnd[] = ['id', array_unique($eIds, SORT_NUMERIC)];

                if ($eIds && $hIds)
                    $cnd[] = 'OR';
            }
            else
                $cnd [] = ['id', array_unique($ids, SORT_NUMERIC)];

            switch ($type)
            {
                case TYPE_NPC:         $obj = new CreatureList($cnd);    break;
                case TYPE_OBJECT:      $obj = new GameobjectList($cnd);  break;
                case TYPE_ITEM:        $obj = new ItemList($cnd);        break;
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
                default: continue;
            }

            $this->extendGlobalData($obj->getJSGlobals(GLOBALINFO_SELF));
        }
    }

    /*********/
    /* Cache */
    /*********/

    public function saveCache($saveString = null)           // visible properties or given strings are cached
    {
        if ($this->mode == CACHETYPE_NONE)
            return false;

        if (CFG_DEBUG)
            return;

        $file = CWD.$this->cacheDir.$this->generateCacheKey();
        $data = time()." ".AOWOW_REVISION." ".($saveString ? '1' : '0')."\n";
        if (!$saveString)
        {
            $cache = [];
            foreach ($this as $key => $val)
            {
                try
                {
                    // public, protected and an undocumented flag added to properties created on the fly..?
                    if ((new ReflectionProperty($this, $key))->getModifiers() & 0x1300)
                        $cache[$key] = $val;
                }
                catch (ReflectionException $e) { }          // shut up!
            }

            $data .= serialize($cache);
        }
        else
            $data .= (string)$saveString;

        file_put_contents($file, $data);
    }

    public function loadCache(&$saveString = null)
    {
        if ($this->mode == CACHETYPE_NONE)
            return false;

        if (CFG_DEBUG)
            return false;

        $file = CWD.$this->cacheDir.$this->generateCacheKey();
        if (!file_exists($file))
            return false;

        $cache = file_get_contents($file);
        if (!$cache)
            return false;

        $cache = explode("\n", $cache, 2);

        @list($time, $rev, $type) = explode(' ', $cache[0]);
        if ($time + CFG_CACHE_DECAY <= time() || $rev < AOWOW_REVISION)
            return false;

        if ($type == '0')
        {
            $data = unserialize($cache[1]);
            foreach ($data as $k => $v)
                $this->$k = $v;

            return true;
        }
        else if ($type == '1')
        {
            $saveString = $cache[1];
            return true;
        }

        return false;;
    }
}

?>
