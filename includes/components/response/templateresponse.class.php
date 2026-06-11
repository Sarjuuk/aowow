<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


trait TrDetailPage
{
    // template vars
    public ?InfoboxMarkup $infobox       = null;
    public ?InfoboxMarkup $contributions = null;
    public ?array         $series        = null;
    public ?string        $transfer      = null;            // faction transfer equivalent data
    public ?Markup        $smartAI       = null;
    public ?array         $map           = null;
    public  array         $headIcons     = [];

    public function getCacheKeyComponents() : array
    {
        return array(
            $this->type,                                    // DBType
            $this->typeId,                                  // DBTypeId/category
            User::$groups,                                  // staff mask
            ''                                              // misc (here unused)
        );
    }

    /**
     * for display on map on detail page
     * (for historic reasons )
     * @param DBTypeEntry|DBTypeContainer $set limited to:
     * * `CreatureEntry` `CreatureSet`
     * * `GameobjectEntry` `GameobjectSet`
     * * `SoundEntry` `SoundSet`
     * * `AreatriggerEntry` `AreatriggerSet`
     * @param bool $skipWPs do not display waypoints
     * @param bool $skipAdmin neither display admin info nor attach spawnposfix menu
     * @param bool $hasLabel use entities name as tooltip if it would otherwise be empty
     * @param bool $hasLink attach link to entity to pip
     * @return array [zoneId => [floor => ['coords' => [[x, y, opts], ...], count => n]]]
     */
    private static function createFullSpawns(DBTypeEntry|DBTypeContainer $set, bool $skipWPs = false, bool $skipAdmin = false, bool $hasLabel = false, bool $hasLink = false) : array
    {
        // works for both Sets and Entities
        if ($set::$dbType != Type::NPC && $set::$dbType != Type::OBJECT && $set::$dbType != Type::SOUND && $set::$dbType != Type::AREATRIGGER)
            return [];

        if ($set instanceof DBTypeEntry)
        {
            $tmp = $set;
            ($set = Type::newContainer($set::$dbType, null))->import($tmp);
            unset($tmp);
        }

        $result   = [];
        $wpSum    = [];                                     // subtract from pip total later to get spawn count
        $wpIdx    = 0;                                      // map pip color
        $worldPos = [];
        $spawns   = DB::Aowow()->selectAssoc(
           'SELECT CASE WHEN z.`type` = %i THEN 1
                        WHEN z.`type` = %i THEN 2
                        WHEN z.`type` = %i THEN 2
                        ELSE 0
                   END AS "mapType",
                   IF (s.`pathId`, -s.`pathId`, s.`typeId`) AS "creatureOrPath",
                   s.*
            FROM   ::spawns s
            JOIN   ::zones z ON s.areaId = z.id
            WHERE s.`type` = %i AND s.`typeId` IN %in AND s.`posX` > 0 AND s.`posY` > 0',
            MAP_TYPE_DUNGEON_HC, MAP_TYPE_MMODE_RAID, MAP_TYPE_MMODE_RAID_HC,
            $set::$dbType, $set->getFoundIDs()
        ) ?: [];

        if (!$skipAdmin && User::isInGroup(U_GROUP_MODERATOR))
            if ($guids = array_column(array_filter($spawns, fn($x) => $x['guid'] > 0 || $x['type'] != Type::NPC), 'guid'))
                $worldPos = WorldPosition::getForGUID($set::$dbType, ...$guids);

        // check, if we can attach waypoints to creature
        // we will get a nice clusterfuck of dots if we do this for more GUIDs, than we have colors though
        $wPoints = [];
        $wpSum   = [];
        if (!$skipWPs && $spawns && count($spawns) < 6 && $set::$dbType == Type::NPC)
            $wPoints = DB::Aowow()->selectAssoc('SELECT cw.`creatureOrPath` AS ARRAY_KEY, cw.`floor` AS ARRAY_KEY2, cw.* FROM ::creature_waypoints cw WHERE cw.`creatureOrPath` IN %in AND cw.`floor` IN %in', array_column($spawns, 'creatureOrPath'), array_column($spawns, 'floor'));

        foreach ($spawns as $s)
        {
            $isAccessory = $s['guid'] < 0 && $s['type'] == Type::NPC;

            foreach ($wPoints[$s['creatureOrPath']][$s['floor']] ?? [] as $i => $p)
            {
                $label = [Lang::npc('waypoint').Lang::main('colon').$p['point']];

                if ($p['wait'])
                    $label[] = Lang::npc('wait').Lang::main('colon').DateTime::formatTimeElapsedFloat($p['wait']);

                $opts = array(                              // \0 doesn't get printed and tricks Util::toJSON() into handling this as a string .. i feel slightly dirty now
                    'label' => "\0$<br /><span class=\"q0\">".implode('<br />', $label).'</span>',
                    'type'  => $wpIdx
                );

                // connective line
                if ($i > 0 && $wPoints[$i - 1]['areaId'] == $p['areaId'])
                    $opts['lines'] = [[$wPoints[$i - 1]['posX'], $wPoints[$i - 1]['posY']]];

                $result[$p['areaId']][$p['floor']]['coords'][] = [$p['posX'], $p['posY'], $opts];
                $wpSum[$p['areaId']][$p['floor']] = ($wpSum[$p['areaId']][$p['floor']] ?? 0) + 1;
            }
            $wpIdx++;

            $opts   = $menu = $tt = $info = [];
            $footer = '';

            if ($s['respawn'] > 0)
                $info[1] = '<span class="q0">'.Lang::npc('respawnIn', [Lang::formatTime($s['respawn'] * 1000, 'game', 'timeAbbrev', true)]).'</span>';
            else if ($s['respawn'] < 0)
            {
                $info[1] = '<span class="q0">'.Lang::npc('despawnAfter', [Lang::formatTime(-$s['respawn'] * 1000, 'game', 'timeAbbrev', true)]).'</span>';
                $opts['type'] = 4;                          // make pip purple
            }

            if (!$skipAdmin && User::isInGroup(U_GROUP_STAFF))
            {
                if ($isAccessory)
                    $info[0] = 'Vehicle Accessory';
                else if ($s['guid'] > 0 && ($s['type'] == Type::NPC || $s['type'] == Type::OBJECT))
                    $info[0] = 'GUID'.Lang::main('colon').$s['guid'];

                if ($s['phaseMask'] > 1 && ($s['phaseMask'] & 0xFFFF) != 0xFFFF)
                    $info[2] = Lang::game('phases').Lang::main('colon').Util::asHex($s['phaseMask']);

                if ($s['spawnMask'] == 15)
                    $info[3] = Lang::game('mode').Lang::game('modes', 0, -1);
                else if ($s['spawnMask'])
                    if ($_ = array_intersect_key(Lang::game('modes', $s['mapType']), Util::mask2bits($s['spawnMask'])))
                        $info[4] = Lang::game('mode').implode(', ', $_);

                if ($s['ScriptName'])
                    $info[5] = 'ScriptName'.Lang::main('colon').$s['ScriptName'];
                if ($s['StringId'])
                    $info[6] = 'StringId'.Lang::main('colon').$s['StringId'];

                // teleporter endpoint
                if ($s['type'] == Type::AREATRIGGER && $s['guid'] < 0)
                {
                    $opts['type'] = 4;
                    $info[7] = 'Teleport Destination';
                }
                /* can no longer access $this .. also mostly useless, so away it goes
                 *
                 * [$num, $word] = Util::O2Deg($this->orientation);
                 * $info[7] = 'Orientation'.Lang::main('colon').$num.'° ('.$word.')';
                 */

                // guid < 0 are vehicle accessories. those are moved by moving the vehicle
                if (User::isInGroup(U_GROUP_MODERATOR) && $worldPos && !$isAccessory && isset($worldPos[$s['guid']]))
                    $menu = Util::buildPosFixMenu($worldPos[$s['guid']]['mapId'], $worldPos[$s['guid']]['posX'], $worldPos[$s['guid']]['posY'], $s['type'], $s['guid'], $s['areaId'], $s['floor']);

                if ($menu)
                    $footer = '<br /><span class="q2">Click to move pin</span>';
            }

            /* recognized opts
             * url:     string - makes pin clickable
             * tooltip: array  - title => [info: <arr>lines, footer: <string>line]
             * label:   string - single line tooltip (skipped if 'tooltip' is set)
             * menu:    array  - menu definiton (conflicts with url)
             * type:    int    - colors the pip [default, green, red, blue, purple]
             * lines:   array  - [[destX, destY]] - draws line from point to dest
             */

            if ($info)
                $tt['info'] = $info;

            if ($footer)
                $tt['footer'] = $footer;

            /** @var CreatureEntry|GameobjectEntry|SoundEntry|AreatriggerEntry $entry */
            if ($entry = $set->getEntry($s['typeId']))
            {
                if ($tt)
                    $opts['tooltip'] = [(string)$entry->name => $tt];
                else if ($hasLabel)
                    $opts['label'] = $entry->name;
            }

            if ($hasLink)
                $opts['url'] = '?' . Type::getFileString($set::$dbType) . '=' . $s['typeId'];

            if ($menu)
                $opts['menu'] = $menu;

            $result[$s['areaId']] [$s['floor']] ['coords'] [] = [$s['posX'], $s['posY'], $opts];
        }

        foreach ($result as $a => &$areas)
            foreach ($areas as $f => &$floor)
                $floor['count'] = count($floor['coords']) - ($wpSum[$a][$f] ?? 0);

        uasort($result, self::sortBySpawnCount(...));

        return $result;
    }

    private static function sortBySpawnCount(array $a, array $b) : int
    {
        $aCount = current($a)['count'];
        $bCount = current($b)['count'];

        return $bCount <=> $aCount;                         // sort descending
    }
}


trait TrListPage
{
    public  string $subCat          = '';
    public ?Filter $filter          = null;
    public ?string $fiMenuExtension = null;

    public function getCacheKeyComponents() : array
    {
        // max. 3 catgs
        // catg max 32767 - largest in use should be 11.197.26801 (Spells: Professions > Tailoring > Spellfire Tailoring)
        if ($this->category)
        {
            $catg = 0x0;
            for ($i = 0; $i < 3; $i++)
            {
                $catg <<= 4 * 4;
                if (!isset($this->category[$i]))
                    continue;

                if ($this->category[$i])
                    $catg |= ($this->category[$i] << 1) & 0xFFFF;
                else
                    $catg |= 1;
            }
        }

        if ($get = $this->filter?->buildGETParam())
            $misc = md5($get);

        return array(
            $this->type,                                    // DBType
            $catg ?? -1,                                    // DBTypeId/category
            User::$groups,                                  // staff mask
            $misc ?? ''                                     // misc (here filter)
        );
    }
}


trait TrGuideEditor
{
    public int    $typeId          = 0;

    public int    $editCategory    = 0;
    public int    $editClassId     = 0;
    public int    $editSpecId      = 0;
    public int    $editRev         = 0;
    public int    $editStatus      = GuideMgr::STATUS_DRAFT;
    public string $editStatusColor = GuideMgr::STATUS_COLORS[GuideMgr::STATUS_DRAFT];
    public string $editTitle       = '';
    public string $editName        = '';
    public string $editDescription = '';
    public string $editText        = '';
    public string $error           = '';
    public Locale $editLocale      = Locale::EN;
    public bool   $isDraft         = false;
}

class TemplateResponse extends BaseResponse
{
    final protected const /* int */ TAB_DATABASE  = 0;
    final protected const /* int */ TAB_TOOLS     = 1;
    final protected const /* int */ TAB_MORE      = 2;
    final protected const /* int */ TAB_COMMUNITY = 3;
    final protected const /* int */ TAB_STAFF     = 4;
    final protected const /* int */ TAB_GUIDES    = 6;

    private array  $jsgBuffer     = [];                     // throw any db type references in here to be processed later
    private array  $header        = [];
    private string $fullParams    = '';                     // effectively articleUrl

    protected  string $template    = '';
    protected  array  $breadcrumb  = [];
    protected ?int    $activeTab   = null;                  // [Database, Tools, More, Community, Staff, null, Guides] ?? none
    protected  string $pageName    = '';
    protected  array  $category    = [];
    protected  array  $validCats   = [];
    protected ?string $articleUrl  = null;
    protected  bool   $filterError = false;                 // retroactively apply error notice to fixed filter result

    protected  array  $dataLoader = [];                     // ?data=x.y.z as javascript
    protected  array  $scripts    = array(
        [SC_JS_FILE,  'js/jquery-3.7.0.min.js', SC_FLAG_NO_TIMESTAMP                        ],
        [SC_JS_FILE,  'js/basic.js'                                                         ],
        [SC_JS_FILE,  'widgets/power.js',       SC_FLAG_NO_TIMESTAMP | SC_FLAG_APPEND_LOCALE],
        [SC_JS_FILE,  'js/locale_%s.js',        SC_FLAG_LOCALIZED                           ],
        [SC_JS_FILE,  'js/global.js'                                                        ],
        [SC_CSS_FILE, 'css/basic.css'                                                       ],
        [SC_CSS_FILE, 'css/global.css'                                                      ],
        [SC_CSS_FILE, 'css/aowow.css'                                                       ],
        [SC_CSS_FILE, 'css/locale_%s.css',      SC_FLAG_LOCALIZED                           ]
    );

    // debug: stats
    protected static float $time       = 0.0;
 // protected static array $sql        = [];
 // protected static array $cacheStats = [];
    public           array $pageStats  = [];                // static properties carry the values, this is just for the PageTemplate to reference

    // send to template
    public  array  $title       = [];                       // head title components
    public  string $h1          = '';                       // body title
    public  string $h1Link      = '';                       //
    public ?string $headerLogo  = null;                     // url to non-standard logo for events etc.
    public  string $search      = '';                       // prefilled search bar
    public  string $wowheadLink = 'https://wowhead.com/';
    public  int    $contribute  = CONTRIBUTE_NONE;
    public ?array  $inputbox    = null;
    public ?string $rss         = null;                     // link rel=alternate for rss auto-discovery
    public ?string $tabsTitle   = null;
    public ?Markup $extraText   = null;
    public ?string $extraHTML   = null;
    public  array  $redButtons  = [];                       // see template/redButtons.tpl.php

    // send to template, but it is js stuff
    public  array  $gPageInfo    = [];
    public  bool   $gDataKey     = false;                   // send g_DataKey to template or don't (stored in $_SESSION)
    public ?Markup $article      = null;
    public ?Tabs   $lvTabs       = null;
    public  array  $pageTemplate = [];                      // js PageTemplate object
    public  array  $jsGlobals    = [];                      // ready to be used in template

    public function __construct(string $rawParam = '')
    {
        $this->title[] = Cfg::get('NAME');
        self::$time = microtime(true);

        parent::__construct();

        $this->fullParams = $this->pageName;
        if ($this->category)
            $this->fullParams .= '='.implode('.', $this->category);
        else if (in_array(__NAMESPACE__.'\TrDetailPage', class_uses($this)) && ($id = intVal($rawParam)))
            $this->fullParams .= '='.$id;

        // prep js+css includes
        $parentVars = get_class_vars(__CLASS__);
        if ($parentVars['scripts'] != $this->scripts)       // additions set in child class
            $this->scripts = array_merge($parentVars['scripts'], $this->scripts);

        if (User::isInGroup(U_GROUP_STAFF | U_GROUP_SCREENSHOT | U_GROUP_VIDEO))
            array_push($this->scripts, [SC_CSS_FILE, 'css/staff.css'], [SC_JS_FILE,  'js/staff.js']);

        // get alt header logo
        if ($ahl = DB::Aowow()->selectCell('SELECT `altHeaderLogo` FROM ::home_featuredbox WHERE %i BETWEEN `startDate` AND `endDate` ORDER BY `id` DESC', time()))
            $this->headerLogo = Util::defStatic($ahl);

        if ($this->pageName)
        {
            $this->wowheadLink = sprintf(WOWHEAD_LINK, Lang::getLocale()->domain(), $this->fullParams, '');
            $this->pageTemplate['pageName'] = $this->pageName;
        }

        if (!is_null($this->activeTab))
            $this->pageTemplate['activeTab'] = $this->activeTab;

        if (!$this->isValidPage())
            $this->onInvalidCategory();

        if (Cfg::get('MAINTENANCE') && !User::isInGroup(U_GROUP_EMPLOYEE))
            $this->generateMaintenance();
        else if (Cfg::get('MAINTENANCE') && User::isInGroup(U_GROUP_EMPLOYEE))
            Util::addNote('Maintenance mode enabled!');
    }

    // by default goto login page
    protected function onUserGroupMismatch() : never
    {
        if (User::isLoggedIn())
            $this->generateError();

        $this->forwardToSignIn($_SERVER['QUERY_STRING'] ?? '');
    }

    // by default show error page
    protected function onInvalidCategory() : never
    {
        $this->generateError();
    }

    // just pass through
    protected function addScript(array ...$scriptDefs) : void
    {
        if (!$this->result)
            $this->scripts = array_merge($this->scripts, $scriptDefs);
        else
            foreach ($scriptDefs as $s)
                $this->result->addScript(...$s);
    }

    protected function addDataLoader(string ...$dataFiles) : void
    {
        if (!$this->result)
            $this->dataLoader = array_merge($this->dataLoader, $dataFiles);
        else
            $this->result->addDataLoader(...$dataFiles);
    }

    public static function pageStatsHook(Template\PageTemplate &$pt, array &$stats) : void
    {
        if (User::isInGroup(U_GROUP_EMPLOYEE))
        {
            $stats['time']  = DateTime::formatTimeElapsed((microtime(true) - self::$time) * 1000);
            $stats['sql']   = ['count' => parent::$sql['count'], 'time' => DateTime::formatTimeElapsed(parent::$sql['time'] * 1000)];
            $stats['cache'] = !empty(static::$cacheStats) ? [static::$cacheStats[0], (new DateTime())->formatDate(static::$cacheStats[1])] : null;
        }
        else
            $stats = [];
    }

    protected function getCategoryFromUrl(string $pageParam) : void
    {
        $arr = explode('.', $pageParam);
        foreach ($arr as $v)
        {
            if (!is_numeric($v))
                break;

            $this->category[] = (int)$v;
        }
    }

    // functionally this should be in PageTemplate but inaccessible there
    protected function fmtStaffTip(?string $text, string $tip) : string
    {
        if (!$text || !User::isInGroup(U_GROUP_EMPLOYEE))
            return $text ?? '';
        else
            return sprintf(Util::$dfnString, $tip, $text);
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
                {
                    // localize name fields .. except for icons .. icons are special
                    if ($type != Type::ICON)
                    {
                        foreach (['name', 'namefemale'] as $n)
                        {
                            if (!isset($v[$n]))
                                continue;

                            $v[$n . '_'.Lang::getLocale()->json()] = $v[$n];
                            unset($v[$n]);
                        }
                    }

                    $this->jsGlobals[$type][1][$k] = $v;
                }
                else if (is_numeric($v))
                    $this->extendGlobalIds($type, $v);
            }
        }

        if ($extra)
        {
            $namedExtra = [];
            foreach ($extra as $typeId => $extraData)
                foreach ($extraData as $k => $v)
                    $namedExtra[$typeId][$k.'_'.Lang::getLocale()->json()] = $v;

            $this->jsGlobals[$type][2] = $namedExtra;
        }
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

            $obj = Type::newContainer($type, [['id', array_unique($ids, SORT_NUMERIC)]]);
            if (!$obj)
                continue;

            $this->extendGlobalData($obj->getJSGlobals(GLOBALINFO_SELF));

            // delete processed ids
            $this->jsgBuffer[$type] = [];
        }
    }


    /************************/
    /* Generic Page Content */
    /************************/

    // get announcements and notes for user
    private function addAnnouncements(bool $onlyGenerics = false) : void
    {
        $announcements = [];

        // display occured notices
        $notes = $_SESSION['notes'] ?? [];
        unset($_SESSION['notes']);

        $notes[] = [...Util::getNotes(), 'One or more issues occured during page generation'];

        foreach ($notes as $i => [$messages, $logLevel, $head])
        {
            if (!$messages)
                continue;

            array_unshift($messages, $head);

            $colors = array(   // [border, text]
                LOG_LEVEL_ERROR => ['C50F1F', 'E51223'],
                LOG_LEVEL_WARN  => ['C19C00', 'E5B700'],
                LOG_LEVEL_INFO  => ['3A96DD', '42ADFF']
            );

            $str = ['name_loc' . Lang::getLocale()->value => '[span]'.implode("[br]", $messages).'[/span]'];
            $text = new LocString($str, formatter: Util::defStatic(...));
            $style = 'color: #'.($colors[$logLevel][1] ?? 'fff').'; font-weight: bold; font-size: 14px; padding-left: 40px; background-image: url('.Cfg::get('STATIC_URL').'/images/announcements/warn-small.png); background-size: 15px 15px; background-position: 12px center; border: dashed 2px #'.($colors[$logLevel][0] ?? 'fff').';';

            $announcements[] = new Announcement(-$i, 'internal error', $text, style: $style);
        }

        // fetch announcements
        $fromDB = DB::Aowow()->selectAssoc(
           'SELECT `id`, `mode`, `status`, `name`, `style`, `text_loc0`, `text_loc2`, `text_loc3`, `text_loc4`, `text_loc6`, `text_loc8`
            FROM   ::announcements
            WHERE  (`page` = "*" %if', !$onlyGenerics && $this->pageName, 'OR `page` = %s', $this->pageName, '%end) AND
                   `status` IN (%i) AND (`groupMask` = 0 OR `groupMask` & %i)',
            User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU) ? [Announcement::STATUS_ENABLED, Announcement::STATUS_DISABLED] : [Announcement::STATUS_ENABLED],
            User::$groups
        );

        foreach ($fromDB as $a)
            if (($ann = new Announcement($a['id'], $a['name'], new LocString($a, 'text', Util::defStatic(...)), $a['mode'], $a['status'], Util::defStatic($a['style'])))->status != Announcement::STATUS_DELETED)
                $announcements[] = $ann;

        $this->result->announcements = $announcements;
    }

    // get article & static infobox (run before processing jsGlobals)
    private function addArticle() : void
    {
        if ($this->article)
            return;

        $article = [];
        if (isset($this->guideRevision))
            $article = DB::Aowow()->selectRow('SELECT `article`, `locale`, `editAccess` FROM ::articles WHERE `type` = %i AND `typeId` = %i AND `rev` = %i',
                Type::GUIDE, $this->typeId, $this->guideRevision);
        if (!$article && !empty($this->gPageInfo['articleUrl']))
            $article = DB::Aowow()->selectRow('SELECT `article`, `locale`, `editAccess` FROM ::articles WHERE `url` = %s AND `locale` IN %in ORDER BY `locale` DESC, `rev` DESC LIMIT 1',
                $this->gPageInfo['articleUrl'], [Lang::getLocale()->value, Locale::EN->value]);
        if (!$article && !empty($this->type) && isset($this->typeId))
            $article = DB::Aowow()->selectRow('SELECT `article`, `locale`, `editAccess` FROM ::articles WHERE `type` = %i AND `typeId` = %i AND `locale` IN %in ORDER BY `locale` DESC, `rev` DESC LIMIT 1',
                $this->type, $this->typeId, [Lang::getLocale()->value, Locale::EN->value]);

        if (!$article)
            return;

        $text = Util::defStatic($article['article']);
        $opts = [];

        // convert U_GROUP_* to MARKUP.CLASS_* (as seen in js-object Markup)
        if ($article['editAccess'] & (U_GROUP_ADMIN | U_GROUP_VIP | U_GROUP_DEV))
            $opts['allow'] = Markup::CLASS_ADMIN;
        else if ($article['editAccess'] & U_GROUP_STAFF)
            $opts['allow'] = Markup::CLASS_STAFF;
        else if ($article['editAccess'] & U_GROUP_PREMIUM)
            $opts['allow'] = Markup::CLASS_PREMIUM;
        else if ($article['editAccess'] & U_GROUP_PENDING)
            $opts['allow'] = Markup::CLASS_PENDING;
        else
            $opts['allow'] = Markup::CLASS_USER;

        if (!empty($this->type) && isset($this->typeId))
            $opts['dbpage'] = 1;

        if ($article['locale'] != Lang::getLocale()->value)
            $opts['prepend'] = '<div class="notice-box"><span class="icon-bubble">'.Lang::main('langOnly', [Lang::lang($article['locale'])]).'</span></div>';

        $this->article = new Markup($text, $opts);

        if ($jsg = $this->article->getJSGlobals())
            $this->extendGlobalData($jsg);

        $this->gPageInfo['editAccess'] = $article['editAccess'];
    }

    private function addCommunityContent() : void
    {
        $community = array(
            'coError' => $_SESSION['error']['co'] ?? null,
            'ssError' => $_SESSION['error']['ss'] ?? null,
            'viError' => $_SESSION['error']['vi'] ?? null
        );

        // we cannot blanket NUMERIC_CHECK the data as usernames of deleted users are their id which does not support String.lower()

        if ($this->contribute & CONTRIBUTE_CO)
            $community['co'] = Util::toJSON(CommunityContent::getComments($this->type, $this->typeId), JSON_UNESCAPED_UNICODE);

        if ($this->contribute & CONTRIBUTE_SS)
            $community['ss'] = Util::toJSON(CommunityContent::getScreenshots($this->type, $this->typeId), JSON_UNESCAPED_UNICODE);

        if ($this->contribute & CONTRIBUTE_VI)
            $community['vi'] = Util::toJSON(CommunityContent::getVideos($this->type, $this->typeId), JSON_UNESCAPED_UNICODE);

        unset($_SESSION['error']);

        // as comments are not cached, those globals cant be either
        $this->extendGlobalData(CommunityContent::getJSGlobals());

        $this->result->community = $community;
        $this->applyGlobals();
    }


    /**************/
    /* Generators */
    /**************/

    protected function generate() : void
    {
        $this->result = new Template\PageTemplate($this->template, $this);

        foreach ($this->scripts as $s)
            $this->result->addScript(...$s);

        $this->result->addDataLoader(...$this->dataLoader);

        // static::class so pageStatsHook defined here, can access cacheStats defined in the implementation
        $this->result->registerDisplayHook('pageStats', [static::class, 'pageStatsHook']);

        // only adds edit links to the staff menu: precursor to guides?
        if (!($this instanceof GuideBaseResponse))
            $this->gPageInfo += array(
                'articleUrl' => $this->articleUrl ?? $this->fullParams,          // is actually be the url-param
                'editAccess' => (U_GROUP_ADMIN | U_GROUP_EDITOR | U_GROUP_BUREAU)
            );

        if ($this->breadcrumb)
            $this->pageTemplate['breadcrumb'] = $this->breadcrumb;

        if (isset($this->filter))
            $this->pageTemplate['filter'] = $this->filter->query ? 1 : 0;

        $this->addArticle();

        $this->applyGlobals();
    }

    // we admit this page exists and an error occured on it
    public function generateError(?string $altPageName = null) : never
    {
        $this->result = new Template\PageTemplate('text-page-generic', $this);

        // only use own script defs
        foreach (get_class_vars(self::class)['scripts'] as $s)
            $this->result->addScript(...$s);

        if (User::isInGroup(U_GROUP_STAFF | U_GROUP_SCREENSHOT | U_GROUP_VIDEO))
        {
            $this->result->addScript(SC_CSS_FILE, 'css/staff.css');
            $this->result->addScript(SC_JS_FILE, 'js/staff.js');
        }

        $this->result->registerDisplayHook('pageStats', [self::class, 'pageStatsHook']);

        $this->title[]    = Lang::main('errPageTitle');
        $this->h1         = Lang::main('errPageTitle');
        $this->articleUrl = 'page-not-found';
        $this->gPageInfo += array(
            'articleUrl' => 'page-not-found',
            'editAccess' => (U_GROUP_ADMIN | U_GROUP_EDITOR | U_GROUP_BUREAU)
        );

        $this->pageTemplate['pageName'] ??= $altPageName ?? 'page-not-found';

        $this->addArticle();

        $this->sumSQLStats();

        $this->header[] = ['HTTP/1.0 404 Not Found', true, 404];

        $this->display(true);
        exit;
    }

    // we do not have this page
    public function generateNotFound(string $title = '', string $msg = '') : never
    {
        $this->result = new Template\PageTemplate('text-page-generic', $this);

        // only use own script defs
        foreach (get_class_vars(self::class)['scripts'] as $s)
            $this->result->addScript(...$s);

        if (User::isInGroup(U_GROUP_STAFF | U_GROUP_SCREENSHOT | U_GROUP_VIDEO))
        {
            $this->result->addScript(SC_CSS_FILE, 'css/staff.css');
            $this->result->addScript(SC_JS_FILE, 'js/staff.js');
        }

        $this->result->registerDisplayHook('pageStats', [self::class, 'pageStatsHook']);

        array_unshift($this->title, Lang::main('nfPageTitle'));

        $this->inputbox = ['inputbox-status', array(
            'head'  =>          isset($this->typeId) ? Util::ucWords($title).' #'.$this->typeId : $title,
            'error' => !$msg && isset($this->typeId) ? Lang::main('pageNotFound', [$title])     : $msg
        )];

        $this->contribute = CONTRIBUTE_NONE;

        if (!empty($this->breadcrumb))
            $this->pageTemplate['breadcrumb'] = $this->breadcrumb;

        $this->sumSQLStats();

        $this->header[] = ['HTTP/1.0 404 Not Found', true, 404];

        $this->display(true);
        exit;
    }

    // display brb gnomes
    public function generateMaintenance() : never
    {
        $this->result = new Template\PageTemplate('maintenance', $this);

        $this->header[] = ['HTTP/1.0 503 Service Temporarily Unavailable', true, 503];
        $this->header[] = ['Retry-After: '.(3 * HOUR)];

        $this->display(true);
        exit;
    }

    protected function display(bool $withError = false) : void
    {
        $this->title  = Util::htmlEscape($this->title);
        $this->search = Util::htmlEscape($this->search);
        // can't escape >h1 here, because CharTitles legitimately add HTML

        $this->addAnnouncements($withError);
        if (!$withError)
            $this->addCommunityContent();

        // force jsGlobals from Announcements/CommunityContent into PageTemplate
        // as this may be loaded from cache, it will be unlinked from its response
        if ($ptJSG = $this->result->jsGlobals)
        {
            foreach ($this->jsGlobals as $type => [, $data, ])
            {
                if (!isset($ptJSG[$type]) || $type == Type::USER)
                    $ptJSG[$type] = $this->jsGlobals[$type];
                else
                {
                    $masterJSG = [$type => &$ptJSG[$type][1]];
                    Util::mergeJsGlobals($masterJSG, [$type => $data]);
                }

                unset($masterJSG);
            }

            $this->result->jsGlobals = $ptJSG;
        }
        else if ($this->jsGlobals)
            $this->result->jsGlobals = $this->jsGlobals;

        if ($this instanceof ICache)
            $this->applyOnCacheLoaded($this->result);

        if ($this->result && $this->filterError)
            $this->result->setListviewError();

        $this->sumSQLStats();

        // Heisenbug: IE11 and FF32 will sometimes (under unknown circumstances) cache 302 redirects and stop
        // re-requesting them from the server but load them from local cache, thus breaking menu features.
        $this->sendNoCacheHeader();
        foreach ($this->header as $h)
            header(...$h);

        $this->result?->render();
    }


    /**********/
    /* Checks */
    /**********/

    // has a valid combination of categories
    private function isValidPage() : bool
    {
        if (!$this->category)
            return true;

        $c = $this->category;                               // shorthand

        switch (count($c))
        {
            case 1: // null is valid  || value in a 1-dim-array     || (key for a n-dim-array           && ( has more subcats                 || no further subCats ))
                $filtered = array_filter($this->validCats, fn ($x) => is_int($x));
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
}

?>
