<?php
// Independent loopback PHP workers; never boots kernel/runtime configuration or a real DB/account.
define('AOWOW_REVISION',68);require __DIR__.'/../includes/defines.php';
$checks=0;$workers=[];$logs=[];
function check(bool $ok,string $label):void {global $checks;$checks++;if(!$ok)throw new RuntimeException($label);}
function start(mixed $policy,bool $present=true):array {
    global $workers,$logs;
    $socket=stream_socket_server('tcp://127.0.0.1:0',$errno,$error);$address=stream_socket_get_name($socket,false);fclose($socket);
    $log=tempnam(sys_get_temp_dir(),'aowow-admin-');$logs[]=$log;
    $env=getenv();unset($env['AOWOW_TEST_ADMIN_POLICY']);if($present)$env['AOWOW_TEST_ADMIN_POLICY']=json_encode($policy,JSON_THROW_ON_ERROR);
    $env['AOWOW_TEST_ADMIN_ORIGIN']='http://'.$address;$env['AOWOW_ADMIN_SECRET_SENTINEL']='PRIVATE-FIXTURE-SENTINEL';
    $process=proc_open([PHP_BINARY,'-S',$address,__DIR__.'/security-admin-http.php'],[0=>['pipe','r'],1=>['file',$log,'a'],2=>['file',$log,'a']],$pipes,dirname(__DIR__),$env);fclose($pipes[0]);$workers[]=$process;
    $deadline=microtime(true)+5;
    do{$socket=@stream_socket_client('tcp://'.$address,$errno,$error,0.1);if(!$socket)usleep(10000);}while(!$socket && microtime(true)<$deadline);
    if(!$socket)throw new RuntimeException('HTTP fixture did not start');fclose($socket);
    return [$address,''];
}
function request(array &$server,string $query,string $method='GET',array $body=[],array $headers=[]):array {
    [$address,$cookie]=$server;
    $headers[]='Cookie: '.$cookie;$headers[]='Content-Type: application/x-www-form-urlencoded';
    $ctx=stream_context_create(['http'=>['method'=>$method,'header'=>implode("\r\n",$headers),'content'=>http_build_query($body),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>5]]);
    $text=file_get_contents('http://'.$address.'/db/?'.$query,false,$ctx);$response=http_get_last_response_headers();
    preg_match('/^HTTP\/\S+ (\d+)/',$response[0],$m);$location=null;
    foreach($response as $header) {
        if(preg_match('/^Set-Cookie: ([^;]+)/i',$header,$match))$server[1]=$match[1];
        if(str_starts_with($header,'Location: '))$location=substr($header,10);
    }
    return ['status'=>(int)$m[1],'text'=>$text,'json'=>json_decode($text,true),'location'=>$location,'headers'=>$response];
}
try {
    foreach([[null,false],[[],true],[['198.51.100.99'],true],[['*'],true],[['127.0.0.1','bad'],true]] as [$policy,$present]) {
        $server=start($policy,$present);$initial=request($server,'fixture=state')['json'];$token=$initial['token'];
        foreach(['phpinfo','siteconfig'] as $page)foreach(['GET','HEAD'] as $method) {
            $r=request($server,'admin='.$page,$method,headers:['X-Forwarded-For: 127.0.0.1','Forwarded: for=127.0.0.1']);
            check($r['status']===403 && !str_contains($r['text'],'PRIVATE-FIXTURE-SENTINEL'),'operator denial on sensitive view before disclosure');
            check(str_contains(implode("\n",$r['headers']),'Cache-Control: no-store'),'denied sensitive view is uncacheable');
        }
        foreach(['add','remove','update'] as $action) {
            $r=request($server,'admin=siteconfig&action='.$action.'&key=FIXTURE&val=value','POST',['csrfToken'=>$token],['Origin: http://'.$server[0],'X-Forwarded-For: 127.0.0.1']);
            check($r['status']===403,'operator denial covers each configuration mutation');
        }
        $after=request($server,'fixture=state')['json'];
        check($after['writes']===0 && $after['configReads']===0 && $after['phpinfoCalls']===0,'denied requests never reach config/diagnostic work');
    }
    $server=start(['127.0.0.1']);$initial=request($server,'fixture=state')['json'];$token=$initial['token'];
    foreach([U_GROUP_ADMIN,U_GROUP_DEV] as $groups) {
        $identity=request($server,'fixture=identity&user=7&groups='.$groups)['json'];$token=$identity['token'];
        $r=request($server,'admin=phpinfo');check($r['status']===200 && ($r['json']['title']??'')==='PHP Information' && count($r['json']['tabs']??[])>0,'allowed logged-in role retains actual native diagnostic rendering');
        check(str_contains(implode("\n",$r['headers']),'Cache-Control: no-store'),'authorized diagnostics are uncacheable');
        $r=request($server,'admin=siteconfig');check($r['status']===200 && ($r['json']['title']??'')==='Site Configuration','allowed role retains configuration view');
        foreach(['add','remove','update'] as $action) {
            $before=request($server,'fixture=state')['json']['writes'];
            $r=request($server,'admin=siteconfig&action='.$action.'&key=FIXTURE&val=value','POST',['csrfToken'=>$token],['Origin: http://'.$server[0]]);
            check($r['status']===200 && request($server,'fixture=state')['json']['writes']===$before+1,'protected operator configuration mutation retains existing behavior');
        }
    }
    foreach([[7,0],[0,U_GROUP_ADMIN]] as [$user,$groups]) {
        $identity=request($server,'fixture=identity&user='.$user.'&groups='.$groups)['json'];$token=$identity['token'];
        foreach(['phpinfo','siteconfig','siteconfig&action=add&key=FIXTURE&val=value','siteconfig&action=remove&key=FIXTURE','siteconfig&action=update&key=FIXTURE&val=value'] as $page) {
            $before=request($server,'fixture=state')['json'];$mutate=str_contains($page,'action=');
            $r=request($server,'admin='.$page,$mutate?'POST':'GET',$mutate?['csrfToken'=>$token]:[]);
            check($r['status']===403,'operator IP does not bypass roles or login');
            $after=request($server,'fixture=state')['json'];
            check($after['writes']===$before['writes'] && $after['phpinfoCalls']===$before['phpinfoCalls'] && $after['configReads']===$before['configReads'],'role/login denial performs no privileged work');
        }
    }
    $identity=request($server,'fixture=identity&user=7&groups='.U_GROUP_ADMIN)['json'];$token=$identity['token'];
    foreach(['add','remove','update'] as $action) {
        $query='admin=siteconfig&action='.$action.'&key=FIXTURE&val=value';
        check(request($server,$query)['status']===405,'operator cannot mutate via GET');
        check(request($server,$query,'POST')['status']===403,'operator still needs a CSRF token');
        check(request($server,$query,'POST',['csrfToken'=>$token],['Origin: https://evil.example'])['status']===403,'operator off-origin mutations remain denied');
    }
    // Verified Origin allows the handler to independently reject an untrusted Referer without masking the return-target test.
    foreach([null,'https://evil.example/path','//evil.example','http://'.$server[0].'/elsewhere','http://'.$server[0].'/db/?item=1&locale=fr'] as $referer) {
        $headers=['Origin: http://'.$server[0]];if($referer!==null)$headers[]='Referer: '.$referer;
        $expected=str_contains($referer??'','/db/')?'/db/?item=1&locale=fr':'.';
        $before=request($server,'fixture=state')['json']['localeSaves'];
        $r=request($server,'locale=2','POST',['csrfToken'=>$token],$headers);
        check($r['status']===302 && $r['location']===$expected,'actual locale return target remains within the configured app');
        check(request($server,'fixture=state')['json']['localeSaves']===$before+1,'locale preference still persists');
        $r=request($server,'admin=announcements&id=1&status=1','POST',['csrfToken'=>$token],$headers);
        check($r['status']===302 && $r['location']===($expected==='.'?'?admin=announcements':$expected),'actual announcement return target uses safe admin fallback');
    }
    echo "PASS: $checks real HTTP redirect/operator/role/CSRF checks\n";
}
finally {foreach($workers as $process){proc_terminate($process);proc_close($process);}foreach($logs as $log)unlink($log);}
