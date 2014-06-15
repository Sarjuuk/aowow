<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


trait DetailPage
{
    protected $hasComContent = true;

    function generateCacheKey($cacheType, $params = '-1')
    {
        $key = [$cacheType, $this->type, '-1', $params];

        if ($this->isLocalized)
            $key[] = User::$localeId;

        return implode('_', $key);
    }
}


trait ListPage
{
    protected $category  = null;
    protected $validCats = [];

    function generateCacheKey($cacheType)
    {
        $key = [$cacheType, $this->type, $this->typeId, '-1'];

        if ($this->isLocalized)
            $key[] = User::$localeId;

        return implode('_', $key);
    }
}


class GenericPage
{
    protected $tpl              = '';
    protected $restrictedGroups = U_GROUP_NONE;

    protected $jsGlobals        = [];
    protected $jsgBuffer        = [];
    protected $isCachable       = true;
    protected $isLocalized      = false;
    protected $hasCacheFile     = false;
    protected $lvData           = [];
    protected $title            = [CFG_NAME];               // for title-Element
    protected $name             = '';                       // for h1-Element
    protected $tabId            = 0;
    protected $community        = ['co' => [], 'sc' => [], 'vi' => []];

    private   $js               = [];
    private   $css              = [];

    private   $gLocale          = [];
    protected $gPageInfo        = [];
    private   $gUser            = [];

	protected function generatePath() {}
	protected function generateTitle() {}
	protected function generateContent() {}

    public function __construct()
    {
        // restricted access
        if ($this->restrictedGroups && !User::isInGroup($this->restrictedGroups))
            $this->error();

        if (CFG_MAINTENANCE && !User::isInGroup(U_GROUP_EMPLOYEE))
            $this->maintenance();
        else if (CFG_MAINTENANCE && User::isInGroup(U_GROUP_EMPLOYEE))
            Util::addNote(U_GROUP_EMPLOYEE, 'Maintenance mode enabled!');

        if (isset($this->validCats) && !Util::isValidPage($this->validCats, $this->category))
            $this->error();

        $this->gUser   = User::getUserGlobals();
        $this->gLocale = array(
            'id'   => User::$localeId,
            'name' => User::$localeString
        );

        if (!$this->tpl)
            return;

        if (!$this->loadCache())
        {
            // run generators
            $this->addArticle();

            $this->generatePath();
            $this->generateTitle();
            $this->generateContent();

            $this->applyGlobals();

            $this->saveCache();
        }

        if (!empty($this->hasComContent))
            $this->community = CommunityContent::getAll($this->type, $this->typeId);

        $this->mysql = DB::Aowow()->getStatistics();

        $this->display();
    }

	public function display($override = '')
	{
        if (!$override && !$this->isSaneInclude('template/pages/', $this->tpl))
            die(User::isInGroup(U_GROUP_STAFF) ? 'Error: nonexistant template requestst: template/pages/'.$this->tpl.'.tpl.php' : null);

        $this->addAnnouncements();

        include('template/pages/'.($override ? $override : $this->tpl).'.tpl.php');
	}

	public function gBrick($file, array $localVars = [])
	{
        foreach ($localVars as $n => $v)
            $$n = $v;

        if (!$this->isSaneInclude('template/globals/', $file))
            echo !User::isInGroup(U_GROUP_STAFF) ? "\n\nError: nonexistant template requestst: template/globals/".$file.".tpl.php\n\n" : null;
        else
            include('template/globals/'.$file.'.tpl.php');
	}

	public function brick($file, array $localVars = [])
	{
        foreach ($localVars as $n => $v)
            $$n = $v;

        if (!$this->isSaneInclude('template/bricks/', $file))
            echo User::isInGroup(U_GROUP_STAFF) ? "\n\nError: nonexistant template requestst: template/bricks/".$file.".tpl.php\n\n" : null;
        else
            include('template/bricks/'.$file.'.tpl.php');
	}

	public function lvBrick($file, array $localVars = [])
	{
        foreach ($localVars as $n => $v)
            $$n = $v;

        if (!$this->isSaneInclude('template/listviews/', $file))
            echo User::isInGroup(U_GROUP_STAFF) ? "\n\nError: nonexistant template requestst: template/listviews/".$file.".tpl.php\n\n" : null;
        else
            include('template/listviews/'.$file.'.tpl.php');
	}

    private function isSaneInclude($path, $file)
    {
        if (preg_match('/[^\w\-]/i', $file))
            return false;

        if (!is_file(CWD.$path.$file.'.tpl.php'))
            return false;

        return true;
    }

	public function addJS($name, $unshift = false)
	{
		if (!in_array($name, $this->js))
		{
			if ($unshift)
				array_unshift($this->js, $name);
			else
				$this->js[] = $name;
		}
	}

	public function addCSS($name, $unshift = false)
	{
		if (!in_array($name, $this->css))
		{
			if ($unshift)
				array_unshift($this->css, $name);
			else
				$this->css[] = $name;
		}
	}

    private function addArticle()                           // fetch article & static infobox
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
            {
                if (preg_match_all('/\[(npc|object|item|itemset|quest|spell|zone|faction|pet|achievement|title|holiday|class|race|skill|currency)=(\d+)[^\]]*\]/i', $text, $matches, PREG_SET_ORDER))
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

            $this->article = array(
                'text'   => $article['article'],
                'params' => []
            );
            if (empty($this->infobox) && !empty($article['quickInfo']))
                $this->infobox = $article['quickInfo'];

            if ($article['locale'] != User::$localeId)
                $this->article['params'] = ['prepend' => Util::jsEscape('<div class="notice-box"><span class="icon-bubble">'.Lang::$main['englishOnly'].'</span></div>')];
        }
    }

    private function addAnnouncements()
    {
        // display occured notices
        if ($_ = Util::getNotes(false))
        {
            if (!isset($this->announcements))
                $this->announcements = [];

            $this->announcements[] = array(
                'id'     => 0,
                'mode'   => 1,
                'status' => 1,
                'name'   => 'internal error',
                'style'  => 'padding-left: 40px; background-image: url(static/images/announcements/warn-small.png); background-size: 15px 15px; background-position: 12px center; border: dashed 2px #C03030;',
                'text'   => '[span id=inputbox-error]'.implode("<br>", $_).'[/span]',
            );
        }

        // fetch announcements
        if (preg_match('/^([a-z\-]+)=?.*$/i', $_SERVER['QUERY_STRING'], $match))
        {
            if (!isset($this->announcements))
                $this->announcements = [];

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

    public function extendGlobalIds($type, $data)
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

    public function extendGlobalData($data, $extra = null)
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

    private function initJSGlobal($type)
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
            case TYPE_WORLDEVENT:  $jsg[TYPE_WORLDEVENT]  = ['holiday',     [], []]; break;
            case TYPE_CLASS:       $jsg[TYPE_CLASS]       = ['class',       [], []]; break;
            case TYPE_RACE:        $jsg[TYPE_RACE]        = ['race',        [], []]; break;
            case TYPE_SKILL:       $jsg[TYPE_SKILL]       = ['skill',       [], []]; break;
            case TYPE_CURRENCY:    $jsg[TYPE_CURRENCY]    = ['currency',    [], []]; break;
        }
    }

    private function applyGlobals()
    {
        foreach ($this->jsgBuffer as $type => $ids)
        {
            foreach ($ids as $k => $id)                     // filter already generated data, maybe we can save a lookup or two
                if (isset($this->jsGlobals[$type][1][$id]))
                    unset($ids[$k]);

            if (!$ids)
                continue;

            $this->initJSGlobal($type);
            $cnd = array(['id', array_unique($ids, SORT_NUMERIC)], CFG_SQL_LIMIT_NONE);

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

    public function notFound($subject)
    {
        $this->subject = $subject;
        $this->mysql   = DB::Aowow()->getStatistics();

        $this->display('text-page-generic');
        exit();
    }

    public function error()
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

    public function maintenance()
    {
        $this->display('maintenance');
        exit();
    }

    // creates the cache file
    public function saveCache(/*$key, $data, $filter = null*/)
    {
        // if (CFG_DEBUG)
            return;

        $file = $this->cache_dir.'data/'.$key;

        $cacheData = time()." ".AOWOW_REVISION."\n";
        $cacheData .= serialize(str_replace(["\n", "\t"], ['\n', '\t'], $data));

        if ($filter)
            $cacheData .= "\n".serialize($filter);

        file_put_contents($file, $cacheData);
    }

    // loads and evaluates the cache file
    public function loadCache(/*$key, &$data, &$filter = null*/)
    {
        // if (CFG_DEBUG)
            return false;

        $cache = @file_get_contents($this->cache_dir.'data/'.$key);
        if (!$cache)
            return false;

        $cache = explode("\n", $cache);

        @list($time, $rev) = explode(' ', $cache[0]);
        $expireTime = $time + CFG_CACHE_DECAY;
        if ($expireTime <= time() || $rev < AOWOW_REVISION)
            return false;

        $data = str_replace(['\n', '\t'], ["\n", "\t"], unserialize($cache[1]));
        if (isset($cache[2]))
            $filter = unserialize($cache[2]);

        return true;
    }
}

?>
