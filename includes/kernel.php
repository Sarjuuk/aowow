<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


ini_set('serialize_precision', 4);

require 'includes/defines.php';
require 'config/config.php';

$e = !!$AoWoWconf['debug'] ? (E_ALL & ~(E_DEPRECATED|E_USER_DEPRECATED|E_STRICT)) : 0;
error_reporting($e);

require 'includes/Smarty-2.6.26/libs/Smarty.class.php';     // Libraray: http://www.smarty.net/
require 'includes/DbSimple/Generic.php';                    // Libraray: http://en.dklab.ru/lib/DbSimple
require 'includes/utilities.php';
require 'includes/class.user.php';
require 'includes/class.database.php';

// autoload any List-Classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'List'))
        require 'includes/class.'.strtr($class, ['List' => '']).'.php';
});

// debug: measure execution times
Util::execTime(!!$AoWoWconf['debug']);

// Setup Smarty
class Smarty_AoWoW extends Smarty
{
    var $config = [];

    public function __construct($config)
    {
        $cwd = str_replace("\\", "/", getcwd());

        $this->Smarty();
        $this->config           = $config;
        $this->template_dir     = $cwd.'/template/';
        $this->compile_dir      = $cwd.'/cache/template/';
        $this->config_dir       = $cwd.'/configs/';
        $this->cache_dir        = $cwd.'/cache/';
        $this->debugging        = $config['debug'];
        $this->left_delimiter   = '{';
        $this->right_delimiter  = '}';
        $this->caching          = false;                    // Total Cache, this site does not work
        $this->assign('app_name', $config['page']['name']);
        $this->assign('AOWOW_REVISION', AOWOW_REVISION);
        $this->_tpl_vars['page'] = array(
            'reqJS'      => [],                             // <[string]> path to required JSFile
            'reqCSS'     => [],                             // <[string,string]> path to required CSSFile, IE condition
            'title'      => null,                           // [string] page title
            'tab'        => null,                           // [int] # of tab to highlight in the menu
            'type'       => null,                           // [int] numCode for spell, npx, object, ect
            'typeId'     => null,                           // [int] entry to display
            'path'       => '[]',                           // [string] (js:array) path to preselect in the menu
            'gStaticUrl' => substr('http://'.$_SERVER['SERVER_NAME'].strtr($_SERVER['SCRIPT_NAME'], ['index.php' => '']), 0, -1)
        );
    }

    // using Smarty::assign would overwrite every pair and result in undefined indizes
    public function updatePageVars($pageVars)
    {
        if (!is_array($pageVars))
            return;

        foreach ($pageVars as $var => $val)
            $this->_tpl_vars['page'][$var] = $val;
    }

    public function display($tpl)
    {
        // since it's the same for every page, except index..
        if ($this->_tpl_vars['query'][0])
        {
            $ann = DB::Aowow()->Select('SELECT * FROM ?_announcements WHERE flags & 0x10 AND (page = ?s OR page = "*")', $this->_tpl_vars['query'][0]);
            foreach ($ann as $k => $v)
                $ann[$k]['text'] = Util::localizedString($v, 'text');

            $this->_tpl_vars['announcements'] = $ann;
        }

        parent::display($tpl);
    }

    // creates the actual cache file
    public function saveCache($key, $data)
    {
        if ($this->debugging)
            return;

        $file = $this->cache_dir.'data/'.$key;

        $cache_data = time()." ".AOWOW_REVISION."\n";
        $cache_data .= serialize($data);

        file_put_contents($file, $cache_data);
    }

    // loads and evaluates the cache file
    public function loadCache($key, &$data)
    {
        if ($this->debugging)
            return false;

        $cache = @file_get_contents($this->cache_dir.'data/'.$key);
        if (!$cache)
            return false;

        $cache = explode("\n", $cache);

        @list($time, $rev) = explode(' ', $cache[0]);
        $expireTime = $time + $this->config['page']['cacheTimer'];
        if ($expireTime <= time() || $rev < AOWOW_REVISION)
            return false;

        $data = unserialize($cache[1]);

        return true;
    }
}

// Setup DB-Wrapper
if (!empty($AoWoWconf['aowow']['db']))
    DB::load(DB_AOWOW, $AoWoWconf['aowow']);
else
    die('no database credentials given for: aowow');

if (!empty($AoWoWconf['world']['db']))
    DB::load(DB_WORLD, $AoWoWconf['world']);

if (!empty($AoWoWconf['auth']['db']))
    DB::load(DB_AUTH, $AoWoWconf['auth']);

foreach ($AoWoWconf['characters'] as $realm => $charDBInfo)
    if (!empty($charDBInfo))
        DB::load(DB_CHARACTERS + $realm, $charDBInfo);

// create Template-Object
$smarty = new Smarty_AoWoW($AoWoWconf);

// Setup Session
if (isset($_COOKIE[COOKIE_AUTH]))
{
    $offset = intval($_COOKIE[COOKIE_AUTH][1]);

    if ($id = hexdec(substr($_COOKIE[COOKIE_AUTH], 2, $offset)))
    {
        User::init($id);

        switch (User::Auth())
        {
            case AUTH_OK:
            case AUTH_BANNED:
                User::writeCookie();
                break;
            default:
                User::destroy();
        }
    }
    else
        User::init(0);
}
else
    User::init(0);

User::setLocale();

// assign lang/locale, userdata, characters and custom profiles
User::assignUserToTemplate($smarty, true);

// parse page-parameters .. sanitize before use!
@list($str, $trash) = explode('&', $_SERVER['QUERY_STRING'], 2);
@list($pageCall, $pageParam) = explode('=', $str, 2);
$smarty->assign('query', [$pageCall, $pageParam]);

// init global vars for smarty
$pageData = array(
    'page'          => NULL,
    'gAchievements' => NULL,
    'gCurrencies'   => NULL,
    'gItems'        => NULL,
    'gSpells'       => NULL,
    'gTitles'       => NULL,
);

?>
