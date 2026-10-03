<?php
// Synthetic URLs only. No runtime configuration, accounts, database or network are loaded.
namespace Aowow {
    class Cfg { public static mixed $host = 'https://app.example/db'; public static function get(string $key) : mixed { return self::$host; } }
}
namespace {
    use Aowow\{Cfg,ReturnTarget,OperatorAccess};
    define('AOWOW_REVISION',68);
    require __DIR__.'/../includes/components/returntarget.class.php';
    require __DIR__.'/../includes/components/operatoraccess.class.php';
    $checks=0;$fixtures=[];
    function check(bool $ok,string $label):void { global $checks;$checks++;if(!$ok)throw new RuntimeException($label); }
    $cases=[
        ['https://app.example/db/?item=1&filter=na%3Dtwo%20words#comments','/db/?item=1&filter=na%3Dtwo%20words#comments'],
        ['HTTPS://APP.EXAMPLE:443/db?locale=fr','/db?locale=fr'],
        ['https://app.example/db/index.php?item=1','/db/index.php?item=1'],
        ['https://app.example/db/?url=https%3A%2F%2Fimages.example%2Fa.jpg','/db/?url=https%3A%2F%2Fimages.example%2Fa.jpg'],
        ['https://app.example/db/?x=//evil.example','/db/?x=//evil.example'],
        [null,'.'],['','.'],[false,'.'],[123,'.'],[[] ,'.'],
        ['https://evil.example/db/', '.'],['http://app.example/db/', '.'],['https://app.example:444/db/', '.'],
        ['https://app.example.evil/db/', '.'],['https://app.example@evil.example/db/', '.'],['https://evil@app.example/db/', '.'],
        ['https://app.example./db/', '.'],['https://%61pp.example/db/', '.'],['https://app.example:0/db/', '.'],['https://app.example:65536/db/', '.'],
        ['//evil.example/db/', '.'],['///evil.example/db/', '.'],['/db/?item=1', '.'],['?item=1', '.'],['javascript:alert(1)', '.'],['file:///db/', '.'],
        ['https://app.example/elsewhere/', '.'],['https://app.example/database/', '.'],['https://app.example', '.'],
        ['https://app.example//evil.example/', '.'],['https://app.example/db/../elsewhere/', '.'],['https://app.example/db/%2e%2e/elsewhere/', '.'],
        ['https://app.example/db/%252e%252e/elsewhere/', '.'],['https://app.example/db/%2f%2fevil.example', '.'],
        ['https://app.example/db/%5cevil.example', '.'],['https://app.example/db/\\evil.example', '.'],['https://app.example\\@evil.example/db/', '.'],
        ['https://app.example/db/?x=%0d%0aLocation%3Ahttps%3A%2F%2Fevil.example', '.'],["https://app.example/db/\r\nLocation: https://evil.example", '.'],
        ['https://app.example/db/?x=%00', '.'],['https://app.example/db/?x=%7f', '.'],['https://app.example/db/?x=%5c', '.'],
        ['https://app.example/db/?x=%GG', '.'],['https://app.example/db/?x=%', '.'],[' https://app.example/db/', '.'],
        ['https://app.example/db/?x='.str_repeat('x',4096), '.']
    ];
    foreach($cases as [$input,$expected]) {
        $output=ReturnTarget::local($input);check($output===$expected,'untrusted URL boundary');
        $fixtures[]=['base'=>Cfg::$host,'input'=>$input,'output'=>$output];
        $_SERVER=['HTTP_REFERER'=>$input,'HTTP_HOST'=>'evil.example'];check(ReturnTarget::fromReferer()===$expected,'trusted configuration controls return origin');
        if($expected==='.')check(ReturnTarget::local($input,'?admin=announcements')==='?admin=announcements','fixed admin fallback');
    }
    foreach(['https://app.example','https://app.example/'] as $base) {
        Cfg::$host=$base;check(ReturnTarget::local('https://app.example')==='/' && ReturnTarget::local('https://app.example/?item=1')==='/?item=1','root application preserves query route');
    }
    Cfg::$host='http://localhost:8080/db/';check(ReturnTarget::local('http://LOCALHOST:8080/db/?search=hello')==='/db/?search=hello','nondefault port preserved');
    Cfg::$host='http://[0:0:0:0:0:0:0:1]:8080/db';check(ReturnTarget::local('http://[::1]:8080/db/?item=1')==='/db/?item=1','equivalent IPv6 origins');
    foreach(['',null,[],123,'//app.example/db','ftp://app.example/db','https://app.example/db/../elsewhere'] as $base) {
        Cfg::$host=$base;check(ReturnTarget::local('https://app.example/db/?item=1')==='.' ,'invalid trusted origin fails closed');
    }

    check(!OperatorAccess::allowed(),'missing private operator policy denies');
    foreach(['192.0.2.10','2001:db8::10','::ffff:192.0.2.10'] as $peer) {
        check(OperatorAccess::matches($peer,[$peer]),'exact valid peer allowed');
        check(!OperatorAccess::matches($peer,['198.51.100.99']),'unlisted peer denied');
        foreach([[],null,false,123,'192.0.2.10',['*'],['0.0.0.0/0'],[$peer,'bad'],['127.0.0.1:8080'],['[::1]'],['fe80::1%eth0'],['named'=>$peer],array_fill(0,65,$peer)] as $policy)
            check(!OperatorAccess::matches($peer,$policy),'missing/malformed/broad policy fails closed');
    }
    check(OperatorAccess::matches('2001:0db8:0:0:0:0:0:10',['2001:db8::10']),'IPv6 canonical comparison');
    check(!OperatorAccess::matches('::ffff:192.0.2.10',['192.0.2.10']),'IPv4-mapped addresses require an explicit allowlist entry');
    foreach([null,'',[],false,123,'unknown','192.0.2.10:80',"192.0.2.10\n",'192.0.2.10, 198.51.100.99'] as $peer)
        check(!OperatorAccess::matches($peer,['192.0.2.10']),'invalid peer denied');
    $_SERVER=['REMOTE_ADDR'=>'198.51.100.99','HTTP_X_FORWARDED_FOR'=>'192.0.2.10','HTTP_FORWARDED'=>'for=192.0.2.10'];
    define('AOWOW_OPERATOR_IPS',['192.0.2.10']);
    putenv('REMOTE_ADDR=192.0.2.10');check(!OperatorAccess::allowed(),'forwarded headers/process environment cannot authorize another peer');putenv('REMOTE_ADDR');
    $_SERVER['REMOTE_ADDR']='192.0.2.10';check(OperatorAccess::allowed(),'configured SAPI peer allowed');
    $xml=simplexml_load_file(__DIR__.'/../crossdomain.xml',options:LIBXML_NONET);
    check($xml!==false && $xml->getName()==='cross-domain-policy' && count($xml->children())===1,'legacy policy contains no access grants');
    check((string)$xml->{'site-control'}['permitted-cross-domain-policies']==='none','legacy meta-policy denies other policy files');
    if(in_array('--fixtures',$argv,true))echo json_encode($fixtures,JSON_THROW_ON_ERROR);
    else echo "PASS: $checks return-target/operator/legacy policy checks\n";
}
