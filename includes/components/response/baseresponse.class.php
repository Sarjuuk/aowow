<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


trait TrRecoveryHelper
{
    const MODE_INFO       = 0;
    const MODE_FORM_PASS  = 1;
    const MODE_FORM_EMAIL = 2;

    private function startRecovery(int $newStatus, string $mailTemplate, string $email) : string
    {
        if (!$newStatus <= ACC_STATUS_NEW && $newStatus > ACC_STATUS_CHANGE_PASS)
            return Lang::main('intError');

        // check if already processing
        if ($_ = DB::Aowow()->selectCell('SELECT `statusTimer` - UNIX_TIMESTAMP() FROM ?_account WHERE `email` = ? AND `status` > ?d AND `statusTimer` > UNIX_TIMESTAMP()', $email, ACC_STATUS_NEW))
            return Lang::account('inputbox', 'error', 'isRecovering', [DateTime::formatTimeElapsed($_ * 1000)]);

        // create new token and write to db
        $token = Util::createHash();
        if (!DB::Aowow()->query('UPDATE ?_account SET `token` = ?, `status` = ?d, `statusTimer` =  UNIX_TIMESTAMP() + ?d WHERE `email` = ?', $token, $newStatus, Cfg::get('ACC_RECOVERY_DECAY'), $email))
            return Lang::main('intError');

        // send recovery mail
        if (!Util::sendMail($email, $mailTemplate, [$token], Cfg::get('ACC_RECOVERY_DECAY')))
            return Lang::main('intError2', ['send mail']);

        return '';
    }
}

trait TrGetNext
{
    private function getNext(bool $forHeader = false) : string
    {
        $next = '';
        if (!empty($this->_get['next']))
            $next = $this->_get['next'];
        else if (isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], '?'))
            $next = explode('?', $_SERVER['HTTP_REFERER'])[1];
        else if ($forHeader)
            return '.';

        return ($forHeader ? '?' : '').$next;
    }
}


Interface ICache
{
    public function saveCache(string|Template\PageTemplate $toCache) : void;
    public function loadCache(bool|string|Template\PageTemplate &$fromCache) : bool;
    public function setOnCacheLoaded(callable $callback, mixed $params = null) : void;
    public function getCacheKeyComponents() : array;
    public function applyOnCacheLoaded(mixed &$data) : mixed;
}

trait TrCache
{
    private const STORE_METHOD_OBJECT = 0;
    private const STORE_METHOD_STRING = 1;

    private  int        $_cacheType    = CACHE_TYPE_NONE;
    private  int        $skipCache     = 0x0;
    private ?int        $decay         = null;
    private  string     $cacheDir      = 'cache/template/';
    private  bool       $cacheInited   = false;
    private ?\Memcached $memcached     = null;
    private  array      $onCacheLoaded = [null, null];      // post-load updater

    public  static array $cacheStats = [];                  // load info for page footer

    // visible properties or given strings are cached
    public function saveCache(string|object $toCache) : void
    {
        $this->initCache();

        if ($this->_cacheType == CACHE_TYPE_NONE)
            return;

        if (!Cfg::get('CACHE_MODE') /* || Cfg::get('DEBUG') */)
            return;

        if (!$this->decay)
            return;

        $cKey   = $this->formatCacheKey();
        $method = is_object($toCache) ? self::STORE_METHOD_OBJECT : self::STORE_METHOD_STRING;

        if ($method == self::STORE_METHOD_OBJECT)
            $toCache = serialize($toCache);
        else
            $toCache = (string)$toCache;

        if (is_callable($this->onCacheLoaded[0]))
            $postCache = serialize($this->onCacheLoaded);

        if (Cfg::get('CACHE_MODE') & CACHE_MODE_MEMCACHED)
        {
            // on &refresh also clear related
            if ($this->skipCache & CACHE_MODE_MEMCACHED)
                $this->deleteCache(CACHE_MODE_MEMCACHED);

            $data = array(
                'timestamp' => time(),
                'lifetime'  => $this->decay,
                'revision'  => AOWOW_REVISION,
                'method'    => $method,
                'postCache' => $postCache ?? null,
                'data'      => $toCache
            );

            $this->memcached()?->set($cKey[2], $data);
        }

        if (Cfg::get('CACHE_MODE') & CACHE_MODE_FILECACHE)
        {
            $data  = time()." ".$this->decay." ".AOWOW_REVISION." ".$method."\n";
            $data .= ($postCache ?? '')."\n";
            $data .= gzcompress($toCache, 9);

            // on &refresh also clear related
            if ($this->skipCache & CACHE_MODE_FILECACHE)
                $this->deleteCache(CACHE_MODE_FILECACHE);

            if (Util::writeDir($this->cacheDir . implode(DIRECTORY_SEPARATOR, array_slice($cKey, 0, 2))))
                file_put_contents($this->cacheDir . implode(DIRECTORY_SEPARATOR, $cKey), $data);
        }
    }

    public function loadCache(mixed &$fromCache) : bool
    {
        $this->initCache();

        if ($this->_cacheType == CACHE_TYPE_NONE)
            return false;

        if (!Cfg::get('CACHE_MODE') /* || Cfg::get('DEBUG') */)
            return false;

        $cKey = $this->formatCacheKey();
        $rev = $method = $data = $postCache = null;

        if ((Cfg::get('CACHE_MODE') & CACHE_MODE_MEMCACHED) && !($this->skipCache & CACHE_MODE_MEMCACHED))
        {
            if ($cache = $this->memcached()?->get($cKey[2]))
            {
                $method    = $cache['method'];
                $data      = $cache['data'];
                $postCache = $cache['postCache'];

                if (($cache['timestamp'] + $cache['lifetime']) > time() && $cache['revision'] == AOWOW_REVISION)
                    self::$cacheStats = [CACHE_MODE_MEMCACHED, $cache['timestamp'], $cache['lifetime']];
            }
        }

        if (!$data && (Cfg::get('CACHE_MODE') & CACHE_MODE_FILECACHE) && !($this->skipCache & CACHE_MODE_FILECACHE))
        {
            $file = $this->cacheDir . implode(DIRECTORY_SEPARATOR, $cKey);
            if (!file_exists($file))
                return false;

            $content = file_get_contents($file);
            if (!$content)
                return false;

            [$head, $postCache, $data] = explode("\n", $content, 3);
            if (substr_count($head, ' ') != 3)
                return false;

            [$time, $lifetime, $rev, $method] = explode(' ', $head);

            if (($time + $lifetime) < time() || $rev != AOWOW_REVISION)
                return false;

            self::$cacheStats = [CACHE_MODE_FILECACHE, $time, $lifetime];
            $data = gzuncompress($data);
        }

        if (!$data)
            return false;

        if ($postCache)
            $this->onCacheLoaded = unserialize($postCache);

        $fromCache = false;
        if ($method == self::STORE_METHOD_OBJECT)
            $fromCache = unserialize($data);
        else if ($method == self::STORE_METHOD_STRING)
            $fromCache = $data;

        return $fromCache !== false;
    }

    public function deleteCache(int $modeMask = 0x3) : void
    {
        $this->initCache();

        // type+typeId/catg+mode; 3+10+1
        $cKey    = $this->formatCacheKey();
        $cKey[2] = substr($cKey[2], 0, 13);

        if ($modeMask & CACHE_MODE_MEMCACHED)
            foreach ($this->memcached()?->getAllKeys() ?? [] as $k)
                if (strpos($k, $cKey[2]) === 0)
                    $this->memcached()?->delete($k);

        if ($modeMask & CACHE_MODE_FILECACHE)
            foreach (glob(implode(DIRECTORY_SEPARATOR, $cKey).'*') as $file)
                unlink($file);
    }

    private function memcached() : ?\Memcached
    {
        if (!class_exists('\Memcached'))
        {
            trigger_error('Memcached is enabled by us but not in php!', E_USER_ERROR);
            return null;
        }

        if (!$this->memcached && (Cfg::get('CACHE_MODE') & CACHE_MODE_MEMCACHED))
        {
            $this->memcached = new \Memcached();
            $this->memcached->addServer('localhost', 11211);
        }

        return $this->memcached;
    }

    private function initCache() : void
    {
        // php's missing trait property conflict resolution is going to be the end of me
        // also allow reevaluation even if inited. It may have changed in generate(), because of an error.
        if (isset($this->cacheType))
            $this->_cacheType = $this->cacheType;

        if ($this->cacheInited)
            return;

        // force refresh
        if (isset($_GET['refresh']) && User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_DEV))
        {
            if ($_GET['refresh'] == 'filecache')
                $this->skipCache = CACHE_MODE_FILECACHE;
            else if ($_GET['refresh'] == 'memcached')
                $this->skipCache = CACHE_MODE_MEMCACHED;
            else if ($_GET['refresh'] == '')
                $this->skipCache = CACHE_MODE_FILECACHE | CACHE_MODE_MEMCACHED;
        }

        $this->decay ??= Cfg::get('CACHE_DECAY');

        $cacheDir = Cfg::get('CACHE_DIR');
        if ($cacheDir && Util::writeDir($cacheDir))
            $this->cacheDir = mb_substr($cacheDir, -1) != '/' ? $cacheDir.'/' : $cacheDir;

        $this->cacheInited = true;
    }

    // https://stackoverflow.com/questions/466521
    private function formatCacheKey() : array
    {
        [$dbType, $dbTypeIdOrCat, $staffMask, $miscInfo] = $this->getCacheKeyComponents();

        $fileKey = '';
        // DBType: 3
        $fileKey .= str_pad(dechex($dbType & 0xFFF), 3, 0, STR_PAD_LEFT);
        // DBTypeId: 6 / category: (2+4+4)
        $fileKey .= str_pad(dechex($dbTypeIdOrCat & 0xFFFFFFFFFF), 2+4+4, 0, STR_PAD_LEFT);
        // cacheType: 1
        $fileKey .= str_pad(dechex($this->_cacheType & 0xF), 1, 0, STR_PAD_LEFT);
        // localeId: 2,
        $fileKey .= str_pad(dechex(Lang::getLocale()->value & 0xFF), 2, 0, STR_PAD_LEFT);
        // staff mask: 4
        $fileKey .= str_pad(dechex($staffMask & 0xFFFFFFFF), 4, 0, STR_PAD_LEFT);
        // optional: miscInfo
        if ($miscInfo)
            $fileKey .= '-'.$miscInfo;

        // topDir, 2ndDir, file
        return array(
            str_pad(dechex($dbType & 0xFF), 2, 0, STR_PAD_LEFT),
            str_pad(dechex(($dbTypeIdOrCat) & 0xFF), 2, 0, STR_PAD_LEFT),
            $fileKey
        );
    }

    public function setOnCacheLoaded(callable $callback, mixed $params = null) : void
    {
        $this->onCacheLoaded = [$callback, $params];
    }

    public function applyOnCacheLoaded(mixed &$data) : mixed
    {
        if (is_callable($this->onCacheLoaded[0]))
            return $this->onCacheLoaded[0]($data, $this->onCacheLoaded[1]);

        return $data;
    }

    public function setCacheDecay(int $seconds) : void
    {
        if ($seconds < 0)
            return;

        $this->decay = $seconds;
    }

    abstract public function getCacheKeyComponents() : array;
}

trait TrSearch
{
    private string $query      = '';                        // sanitized search string
    private int    $searchMask = 0;                         // what to search for
    private Search $searchObj;

    public function getCacheKeyComponents() : array
    {
        $misc = $this->query .                              // can be empty for upgrade search
                serialize($this->_get['wt'] ?? null) .      // extra &_GET not expected for normal and opensearch
                serialize($this->_get['wtv'] ?? null) .
                serialize($this->_get['type'] ?? null) .
                serialize($this->_get['slots'] ?? null);

        return array(
            -1,                                             // DBType
            $this->searchMask,                              // DBTypeId/category
            User::$groups,                                  // staff mask
            md5($misc)                                      // misc
        );
    }
}

Interface IProfilerList
{
    public function getRegions() : void;
}

trait TrProfiler
{
    protected int    $realmId     = 0;
    protected string $battlegroup = '';                     // not implemented, since no pserver supports it

    public string $region = '';
    public string $realm  = '';

    private function getSubjectFromUrl(string $pageParam) : void
    {
        if (!$pageParam)
            return;

        // cat[0] is always region
        // cat[1] is realm or bGroup (must be realm if cat[2] is set)
        // cat[2] is arena-team, guild or character
        $cat = explode('.', mb_strtolower($pageParam), 3);

        $cat = array_map('urldecode', $cat);

        if (array_search($cat[0], Util::$regions) === false)
            return;

        $this->region = $cat[0];

        // if ($cat[1] == Profiler::urlize(Cfg::get('BATTLEGROUP')))
            // $this->battlegroup = Cfg::get('BATTLEGROUP');
        if (isset($cat[1]))
        {
            foreach (Profiler::getRealms() as $rId => $r)
            {
                if (Profiler::urlize($r['name'], true) == $cat[1])
                {
                    $this->realm   = $r['name'];
                    $this->realmId = $rId;
                    if (isset($cat[2]) && mb_strlen($cat[2]) >= 2)
                        $this->subjectName = mb_strtolower($cat[2]); // cannot reconstruct original name from urlized form; match against special name field

                    break;
                }
            }
        }
    }

    private function followBreadcrumbPath() : void
    {
        if ($this->region)
        {
            $this->breadcrumb[] = $this->region;

            if ($this->realm)
                $this->breadcrumb[] = Profiler::urlize($this->realm, true);
            // else
                // $this->breadcrumb[] = Profiler::urlize(Cfg::get('BATTLEGROUP'));
        }
    }
}

trait TrProfilerDetail
{
    use TrProfiler { TrProfiler::getSubjectFromUrl as _getSubjectFromUrl; }

    protected string $subjectName = '';

    public  int   $typeId   = 0;
    public ?array $doResync = null;

    private function getSubjectFromUrl(string $pageParam) : void
    {
        if (!$pageParam)
            return;

        if (Util::checkNumeric($pageParam, NUM_CAST_INT))
            $this->typeId = $pageParam;
        else
            $this->_getSubjectFromUrl($pageParam);
    }

    private function handleIncompleteData(int $type, int $guid) : void
    {
        // queue full fetch
        if ($newId = Profiler::scheduleResync($type, $this->realmId, $guid))
        {
            $this->template = 'text-page-generic';
            $this->doResync = [Type::getFileString($type), $newId];
            $this->inputbox = ['inputbox-status', ['head' => Lang::profiler('firstUseTitle', [Util::ucFirst($this->subjectName), $this->realm])]];

            return;
        }

        // todo: base info should have been created in __construct .. why are we here..?
        $this->forward('?'.Type::getFileString($type).'s='.$this->region.'.'.Profiler::urlize($this->realm, true).'&filter=na='.Util::ucFirst($this->subjectName).';ex=on');
    }
}

trait TrProfilerList
{
    use TrProfiler;

    public array $regions = [];

    public function getRegions() : void
    {
        $usedRegions = array_column(Profiler::getRealms(), 'region');
        foreach (Util::$regions as $idx => $id)
            if (in_array($id, $usedRegions))
                $this->regions[$id] = Lang::profiler('regions', $id);
    }
}


abstract class BaseResponse
{
    protected const PATTERN_TEXT_LINE = '/[\p{Cc}\p{Cf}\p{Co}\p{Cs}\p{Cn}]/iu';
    protected const PATTERN_TEXT_BLOB = '/[\x00-\x09\x0B-\x1F\p{Cf}\p{Co}\p{Cs}\p{Cn}]/iu';

    protected static array $sql = [];                       // debug: sql stats container

    protected array $expectedPOST   = [];                   // fill with variables you that are going to be used; eg:
    protected array $expectedGET    = [];                   // 'id' => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkIdList']
    protected array $expectedCOOKIE = [];

    protected array $_post   = [];                          // the filtered variable result
    protected array $_get    = [];
    protected array $_cookie = [];

    protected int   $requiredUserGroup = U_GROUP_NONE;      // by default accessible to everone
    protected bool  $requiresLogin     = false;             // normal users and guests are both U_GROUP_NONE, soooo.....
    protected mixed $result            = null;

    public function __construct()
    {
        $this->initRequestData();

        if (!User::isInGroup($this->requiredUserGroup))
            $this->onUserGroupMismatch();

        if ($this->requiresLogin && !User::isLoggedIn())
            $this->onUserGroupMismatch();
    }

    public function process() : void
    {
        $fromCache = false;

        if ($this instanceof ICache)
            $fromCache = $this->loadCache($this->result);

        if (!$this->result)
            $this->generate();

        $this->display();

        if ($this instanceof ICache && !$fromCache)
            $this->saveCache($this->result);
    }

    private function initRequestData() : void
    {
        // php bug? If INPUT_X is empty, filter_input_array returns null/fails
        // only really relevant for INPUT_POST
        // manuall set everything null in this case

        if ($this->expectedPOST)
        {
            if ($_POST)
                $this->_post = filter_input_array(INPUT_POST, $this->expectedPOST);
            else
                $this->_post = array_fill_keys(array_keys($this->expectedPOST), null);
        }

        if ($this->expectedGET)
        {
            if ($_GET)
                $this->_get = filter_input_array(INPUT_GET, $this->expectedGET);
            else
                $this->_get = array_fill_keys(array_keys($this->expectedGET), null);
        }

        if ($this->expectedCOOKIE)
        {
            if ($_COOKIE)
                $this->_cookie = filter_input_array(INPUT_COOKIE, $this->expectedCOOKIE);
            else
                $this->_cookie = array_fill_keys(array_keys($this->expectedCOOKIE), null);
        }
    }

    protected function forward(string $url = '') : never
    {
        $this->sendNoCacheHeader();
        header('Location: '.($url ?: '.'), true, 302);
        exit;
    }

    protected function forwardToSignIn(string $next = '') : never
    {
        $this->forward('?account=signin'.($next ? '&next='.$next : ''));
    }

    protected function sumSQLStats() : void
    {
        Util::arraySumByKey(self::$sql, DB::Aowow()->getStatistics(), DB::World()->getStatistics());
        foreach (Profiler::getRealms() as $rId => $_)
            Util::arraySumByKey(self::$sql, DB::Characters($rId)->getStatistics());
    }

    protected function sendNoCacheHeader()
    {
        header('Expires: Sat, 01 Jan 2000 01:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
    }


    /****************************/
    /* required Parameter tests */
    /****************************/

    protected function assertPOST(string ...$keys) : bool
    {
        foreach ($keys as $k)            // not sent by browser       || empty text field sent   || validation failed
            if (!isset($this->_post[$k]) || $this->_post[$k] === null || $this->_post[$k] === '' || $this->_post[$k] === false)
                return false;

        return true;
    }

    protected function assertGET(string ...$keys) : bool
    {
        foreach ($keys as $k)
            if (!isset($this->_get[$k]) || $this->_get[$k] === null || $this->_get[$k] === '' || $this->_get[$k] === false)
                return false;

        return true;
    }

    protected function assertCOOKIE(string ...$keys) : bool
    {
        foreach ($keys as $k)
            if (!isset($this->_cookie[$k]) || $this->_cookie[$k] === null || $this->_cookie[$k] === '' || $this->_cookie[$k] === false)
                return false;

        return true;
    }


    /*******************************/
    /* Parameter validation checks */
    /*******************************/

    protected static function checkRememberMe(string $val) : bool
    {
        return $val === 'yes';
    }

    protected static function checkCheckbox(string $val) : bool
    {
        return $val === 'on';
    }

    protected static function checkEmptySet(string $val) : bool
    {
        return $val === '';                                 // parameter is set and expected to be empty
    }

    protected static function checkIdList(string $val) : array
    {
        if (preg_match('/^-?\d+(,-?\d+)*$/', $val))
            return array_map('intVal', explode(',', $val));

        return [];
    }

    protected static function checkIntArray(string $val) : array
    {
        if (preg_match('/^-?\d+(:-?\d+)*$/', $val))
            return array_map('intVal', explode(':', $val));

        return [];
    }

    protected static function checkIdListUnsigned(string $val) : array
    {
        if (preg_match('/^\d+(,\d+)*$/', $val))
            return array_map('intVal', explode(',', $val));

        return [];
    }

    protected static function checkTextLine(string $val) : string
    {
        // remove invalid characters
        $val = mb_convert_encoding(trim($val), 'utf-8', 'utf-8');
        // trim non-printable chars
        return preg_replace(self::PATTERN_TEXT_LINE, '', $val);
    }

    protected static function checkTextBlob(string $val) : string
    {
        $val = mb_convert_encoding(trim($val), 'utf-8', 'utf-8');
        // trim non-printable chars + excessive whitespaces (pattern includes \r)
        $str = preg_replace(self::PATTERN_TEXT_BLOB, '', $val);
        return preg_replace('/ +/', ' ', trim($str));
    }

    protected static function checkLocale(string $localeId) : ?Locale
    {
        if (Util::checkNumeric($localeId, NUM_CAST_INT))
            return Locale::tryFrom($localeId);
        return null;
    }


    /********************/
    /* child implements */
    /********************/

    // calc response
    abstract protected function generate() : void;

    // send response
    abstract protected function display() : void;

    // handling differs by medium
    abstract protected function onUserGroupMismatch() : never;
}

?>
