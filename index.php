<?php

namespace Aowow;

require 'includes/kernel.php';

if (CLI)
    die("this script must not be run from CLI.\nto setup aowow use 'php aowow'\n");


$pageCall  = 'home';                                        // default to Homepage unless specified otherwise
$pageParam = '';
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $query);
foreach ($query as $page => $param)
{
    // could be an array
    if (!is_string($param))
    {
        $pageCall = '';                                     // just .. fail
        break;
    }

    // fix page calls - pages like search use the page call directly and expect it as lower case
    if (preg_match('/[A-Z]/', $page))
    {
        $url  = explode('=', $_SERVER['REQUEST_URI'], 2);
        $page = Util::lower(array_shift($url)).($url ? '=' . $url[0] : '');
        header('Location: '.$page, true, 302);
        exit;
    }

    $pageCall  = preg_replace('/[^\w\-]/i', '', $page);
    $pageParam = $param ?? '';
    break;                                                  // only use first k/v-pair to determine page
}

[$classMod, $file] = match (true)
{
    // is search ajax
    isset($_GET['json'])                          => ['Json',     $pageCall . '_json'    ],
    isset($_GET['opensearch'])                    => ['Open',     $pageCall . '_open'    ],
    // is powered tooltip
    isset($_GET['power'])                         => ['Power',    $pageCall . '_power'   ],
    // is item data xml dump
    isset($_GET['xml'])                           => ['Xml',      $pageCall . '_xml'     ],
    // is community content feed
    isset($_GET['rss'])                           => ['Rss',      $pageCall . '_rss'     ],
    // is sounds playlist
    isset($_GET['playlist'])                      => ['Playlist', $pageCall . '_playlist'],
    // pageParam can be sub page
    (bool)preg_match('/^[a-z\-]+$/i', $pageParam) => [Util::ucFirst(strtr($pageParam, ['-' => ''])), Util::lower($pageParam)],
    // no pageParam or PageParam is param for BasePage
    default                                       => ['Base',     $pageCall              ]
};

// admin=X pages are mixed html and ajax on the same endpoint .. meh
if ($pageCall == 'admin' && isset($_GET['action']) && preg_match('/^[a-z]+$/', $_GET['action']))
{
    $classMod .= 'Action' . Util::ucFirst($_GET['action']);
    $file     .= '_' . Util::lower($_GET['action']);
}

try {
    $responder = new \StdClass;

    // 1. try specialized response
    if (file_exists('endpoints/'.$pageCall.'/'.$file.'.php'))
    {
        require_once 'endpoints/'.$pageCall.'/'.$file.'.php';

        $class     = __NAMESPACE__.'\\' . Util::ucFirst(strtr($pageCall, ['-' => ''])).$classMod.'Response';
        $responder = new $class($pageParam);
    }
    // 2. try generalized response
    else if (file_exists('endpoints/'.$pageCall.'/'.$pageCall.'.php'))
    {
        require_once 'endpoints/'.$pageCall.'/'.$pageCall.'.php';

        $class     = __NAMESPACE__.'\\' . Util::ucFirst(strtr($pageCall, ['-' => ''])).'BaseResponse';
        $responder = new $class($pageParam);
    }
    // 3. throw .. your hands in the air and give up
    if (!is_callable([$responder, 'process']))
        throw new \Exception('request handler '.$pageCall.'::'.$classMod.'('.$pageParam.') not found');

    $responder->process();
}
catch (\Exception $e)
{
    if (isset($_GET['json']) || isset($_GET['opensearch']) || isset($_GET['power']) || isset($_GET['xml']) || isset($_GET['rss']))
        (new TextResponse($pageParam))->generate404();
    else
        (new TemplateResponse($pageParam))->generateError($pageCall);
}

?>
