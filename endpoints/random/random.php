<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/* note:
 * WH sends a whole page (empty maincontents)
 * probably so the whole shebang of tracking providers can insert themselves
 * also $WH.localStorage.set('showRandomWidget', 'true'); creates a more accessible random button in the topbar on the target page via
 *
    if ($WH.localStorage.get("showRandomWidget") == "true") {
        $WH.localStorage.remove("showRandomWidget");
        var a = $WH.ce("a");
        a.className = "topbar-random fa fa-random";
        a.href = "/random";
        $WH.Tooltip.simple(a, LANG.anotherrandompage_tip, "q2");
        X.append(a);
    }
 *
 * in PageTemplate.initTopBar
*/
class RandomBaseResponse extends TextResponse
{
    // protected string $template = 'text-page-generic';
    // protected string $pageName = 'random';

    public function generate() : void
    {
     // $this->h1 = 'Random Page';
     // array_unshift($this->title, $this->h1);

        $type    = array_rand(Type::getClassesFor(Type::FLAG_RANDOM_SEARCHABLE));
        $typeId  = (Type::newList($type))?->getRandomId();

        $this->redirectTo = '?'.Type::getFileString($type).'='.$typeId;

     // $this->extraHTML = <<<JS
     //     <script type="text/javascript">//<![CDATA[
     //         \$WH.localStorage.set('showRandomWidget', 'true');
     //         location = "?{$type}={$typeId}";
     // //]]></script>
     // JS;
    }
}

?>
