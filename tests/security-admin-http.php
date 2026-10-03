<?php
// Real responder/access/CSRF/handler code and native phpinfo; only identity, DB/configuration and surrounding UI are synthetic.
namespace Aowow {
    class Cfg {
        public const string PATTERN_CONF_KEY_FULL='/^[a-z0-9_\.\-]+$/i';
        public const int CAT_MISCELLANEOUS=0;
        public static array $categories=[1=>'Synthetic settings'];
        public static function get(string $key):mixed { return match($key){'HOST_URL'=>getenv('AOWOW_TEST_ADMIN_ORIGIN').'/db','LOCALES'=>0xFFFF,default=>0}; }
        public static function set(string $key,string $value):string {$_SESSION['writes']++;return '';}
        public static function add(string $key,string $value):string {$_SESSION['writes']++;return '';}
        public static function delete(string $key):string {$_SESSION['writes']++;return '';}
        public static function forCategory(int $category):array {$_SESSION['configReads']++;return [];}
    }
    class User {
        public static int $id=0;
        public static int $groups=0;
        public static \Aowow\Locale $preferedLoc;
        public static function isInGroup(int $mask):bool {return $mask===0 || (self::$groups & $mask)!==0;}
        public static function isLoggedIn():bool {return self::$id>0;}
        public static function save(bool $save):void {$_SESSION['localeSaves']++;$_SESSION['preferredLocale']=self::$preferedLoc->domain();}
    }
    class DB {
        public static function Aowow():self{return new self;}
        public function selectCell(string $sql,mixed ...$args):int {return 1;}
        public function qry(string $sql,mixed ...$args):int {$_SESSION['writes']++;return 1;}
    }
    class Lang {public static function __callStatic(string $method,array $args):string{return 'Fixture message';}}
    class Tabs {
        public array $tabs=[];
        public function __construct(array $options){}
        public function addDataTab(string $id,string $name,string $body):void {$this->tabs[]=$body;}
    }
    function phpinfo(int $flags):bool {$_SESSION['phpinfoCalls']++;return \phpinfo($flags);}
}
namespace {
    define('AOWOW_REVISION',68);define('CLI',false);
    require __DIR__.'/../includes/defines.php';require __DIR__.'/../includes/locale.class.php';require __DIR__.'/../includes/utilities.php';
    foreach(['errorlog','csrf','returntarget','operatoraccess','jsexpression'] as $class)require __DIR__.'/../includes/components/'.$class.'.class.php';
    Aowow\ErrorLog::configurePhp();
    if(PHP_SAPI!=='cli-server') {fwrite(STDERR,"Run php tests/security-admin-boundary.php.\n");exit(1);}
    if(($policy=getenv('AOWOW_TEST_ADMIN_POLICY'))!==false)define('AOWOW_OPERATOR_IPS',json_decode($policy,true,flags:JSON_THROW_ON_ERROR));
    require __DIR__.'/../includes/components/response/baseresponse.class.php';
    require __DIR__.'/../includes/components/response/textresponse.class.php';
}
namespace Aowow {
    // Rendering is a fixture; the actual BaseResponse constructor enforces operator/login/role/CSRF before handlers run.
    class TemplateResponse extends BaseResponse {
        public const int TAB_STAFF=4;
        public array $title=[];
        public string $h1='';
        public ?Tabs $lvTabs=null;
        protected function generate():void {}
        protected function addScript(array ...$scripts):void {}
        protected function display():void {header('Content-Type: application/json');echo json_encode(['title'=>$this->h1,'tabs'=>$this->lvTabs?->tabs],JSON_THROW_ON_ERROR);}
        protected function onUserGroupMismatch():never {http_response_code(403);header('Cache-Control: no-store');exit('Access denied.');}
    }
}
namespace {
    foreach(['locale/locale','admin/announcements','admin/phpinfo','admin/siteconfig','admin/siteconfig_add','admin/siteconfig_remove','admin/siteconfig_update'] as $endpoint)require __DIR__.'/../endpoints/'.$endpoint.'.php';
    session_start();
    $_SESSION+=['user'=>7,'groups'=>U_GROUP_ADMIN,'writes'=>0,'configReads'=>0,'phpinfoCalls'=>0,'localeSaves'=>0];
    Aowow\User::$id=$_SESSION['user'];Aowow\User::$groups=$_SESSION['groups'];
    if(isset($_GET['fixture'])) {
        if($_GET['fixture']==='identity') {$_SESSION['user']=(int)($_GET['user']??7);$_SESSION['groups']=(int)($_GET['groups']??U_GROUP_ADMIN);}
        header('Content-Type: application/json');echo json_encode(['token'=>Aowow\Csrf::token()]+$_SESSION,JSON_THROW_ON_ERROR);exit;
    }
    if(isset($_GET['locale']))$response=new Aowow\LocaleBaseResponse('locale');
    else $response=match($_GET['admin']??'') {
        'phpinfo'=>new Aowow\AdminPhpinfoResponse('phpinfo'),
        'announcements'=>new Aowow\AdminAnnouncementsResponse('announcements'),
        'siteconfig'=>match($_GET['action']??'') {
            'add'=>new Aowow\AdminSiteconfigActionAddResponse('siteconfig'),
            'remove'=>new Aowow\AdminSiteconfigActionRemoveResponse('siteconfig'),
            'update'=>new Aowow\AdminSiteconfigActionUpdateResponse('siteconfig'),
            default=>new Aowow\AdminSiteconfigResponse('siteconfig')
        }
    };
    $response->process();
}
