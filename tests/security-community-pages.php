<?php
namespace Aowow {
    class Lang {
        public static function main(string $key,array $args=[]) : string {
            return match($key){'commentsPage'=>sprintf('Comments page %d of %d',...$args),'comments'=>'Comments','previousComments'=>'Previous comments','nextComments'=>'Next comments',default=>$key};
        }
    }
}
namespace {
    define('AOWOW_REVISION',67);
    $root=dirname(__DIR__); require $root.'/includes/defines.php';require $root.'/includes/utilities.php';
    require $root.'/includes/components/pagetemplate.class.php';require $root.'/includes/components/communitycontent.class.php';
    $checks=0;$fixtures=[];
    foreach([1,2,3] as $page) {
        $_GET=['item'=>'1','coPage'=>$page,'locale'=>'fr','probe'=>'"><script>'];
        $template=(new ReflectionClass(Aowow\Template\PageTemplate::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty($template,'context'))->setValue($template,null);
        foreach(['lvTabs'=>null,'charactersLvData'=>null,'profilesLvData'=>null,'contribute'=>CONTRIBUTE_CO,'gemScores'=>null,'community'=>['coPages'=>3,'coPage'=>$page]] as $key=>$value)$template->$key=$value;
        ob_start();(function()use($root){include $root.'/template/bricks/lvTabs.tpl.php';})->call($template);$html=ob_get_clean();
        foreach([str_contains($html,"Comments page $page of 3"),str_contains($html,'Previous comments')===($page>1),str_contains($html,'Next comments')===($page<3),!str_contains($html,'"><script>') && str_contains($html,'&amp;locale=fr')] as $ok) {
            $checks++;if(!$ok)throw new RuntimeException('Bounded comment navigation/escaping');
        }
        preg_match('~<script[^>]*>(.*?)</script>~s',$html,$match);$fixtures[]=$match[1];
    }
    if(in_array('--fixtures',$argv,true))echo json_encode($fixtures,JSON_THROW_ON_ERROR);
    else echo "PASS: $checks rendered comment navigation checks\n";
}
