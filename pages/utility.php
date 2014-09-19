<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId  8: Utilities g_initPath()
//  tabId  1: Tools     g_initHeader()
class UtilityPage extends GenericPage
{
    protected $tpl           = 'list-page-generic';
    protected $path          = [1, 8];
    protected $tabId         = 1;
    protected $mode          = CACHE_TYPE_NONE;
    protected $validPages    = array(
        'latest-additions',       'latest-articles',       'latest-comments',       'latest-screenshots',  'random',
        'unrated-comments', 11 => 'latest-videos',   12 => 'most-comments',   13 => 'missing-screenshots'
    );
    private $page            = '';
    private $rss             = false;

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageCall, $pageParam);

        $this->page = $pageCall;
        $this->rss  = isset($_GET['rss']);
        $this->name = Lang::$main['utilities'][array_search($pageCall, $this->validPages)];

        if ($this->page == 'most-comments')
        {
            if ($this->category && in_array($this->category[0], [7, 30]))
                $this->name .= Lang::$main['colon'] . sprintf(Lang::$main['mostComments'][1], $this->category[0]);
            else
                $this->name .= Lang::$main['colon'] . Lang::$main['mostComments'][0];
        }
    }

    public function display($override = '')
    {
        if ($this->rss)                                     // this should not be cached
        {
            header('Content-Type: application/rss+xml; charset=ISO-8859-1');
            die($this->generateRSS());
        }
        else
            return parent::display($override);
    }

    protected function generateContent()
    {
        /****************/
        /* Main Content */
        /****************/

        if (in_array(array_search($this->page, $this->validPages), [0, 1, 2, 3, 11, 12]))
            $this->h1Links = '<small><a href="?'.$this->page.($this->category ? '='.$this->category[0] : null).'&rss" class="icon-rss">'.Lang::$main['subscribe'].'</a></small>';

        switch ($this->page)
        {
            case 'random':
                $type   = array_rand(array_filter(Util::$typeStrings));
                $typeId = (new Util::$typeClasses[$type](null))->getRandomId();

                header('Location: ?'.Util::$typeStrings[$type].'='.$typeId, true, 302);
                die();
            case 'latest-comments':
                $this->lvTabs[] = array(
                    'file'   => 'commentpreview',
                    'data'   => CommunityContent::getCommentPreviews(),
                    'params' => []
                );
                break;
            case 'latest-screenshots':
                $this->lvTabs[] = array(
                    'file'   => 'screenshot',
                    'data'   => [],
                    'params' => []
                );
                break;
            case 'latest-videos':
                $this->lvTabs[] = array(
                    'file'   => 'video',
                    'data'   => [],
                    'params' => []
                );
                break;
            case 'latest-articles':
                $this->lvTabs = [];
                break;
            case 'latest-additions':
                $extraText = '';
                break;
            case 'unrated-comments':
                $this->lvTabs[] = array(
                    'file'   => 'commentpreview',
                    'data'   => [],
                    'params' => []
                );
                break;
            case 'missing-screenshots':
                // limit to 200 entries each (it generates faster, consumes less memory and should be enough options)
                $cnd = [[['cuFlags', CUSTOM_HAS_SCREENSHOT, '&'], 0], 200];
                if (!User::isInGroup(U_GROUP_EMPLOYEE))
                    $cnd[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

                foreach (Util::$typeClasses as $classStr)
                {
                    if (!$classStr)
                        continue;

                    $typeObj = new $classStr($cnd);
                    if (!$typeObj->error)
                    {
                        $this->extendGlobalData($typeObj->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED | GLOBALINFO_REWARDS));
                        $this->lvTabs[] = array(
                            'file'   => $typeObj::$brickFile,
                            'data'   => $typeObj->getListviewData(),
                            'params' => []
                        );
                    }
                }
                break;
            case 'most-comments':
                if ($this->category && !in_array($this->category[0], [1, 7, 30]))
                    header('Location: ?most-comments=1'.($this->rss ? '&rss' : null), true, 302);

                $this->lvTabs[] = array(
                    'file'   => 'commentpreview',
                    'data'   => [],
                    'params' => []
                );
                break;
        }
    }

    protected function generateRSS()
    {
        $this->generateContent();

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
            "<rss version=\"2.0\">\n<channel>\n".
            "<title>".CFG_NAME_SHORT.' - '.$this->name."</title>\n".
            "<link>".HOST_URL.'?'.$this->page . ($this->category ? '='.$this->category[0] : null)."</link>\n".
            "<description>".CFG_NAME."</description>\n".
            "<language>".implode('-', str_split(User::$localeString, 2))."</language>\n".
            "<ttl>".CFG_TTL_RSS."</ttl>\n".
            "<lastBuildDate>".date(DATE_RSS)."</lastBuildDate>\n";

        foreach ($this->lvTabs[0]['data'] as $row)
        {
            $xml .= "<item>\n".
                "<title><![CDATA[".$row['subject']."]]></title>\n".
                "<link>".HOST_URL.'?go-to-comment&amp;id='.$row['id']."</link>\n".
                "<description><![CDATA[".$row['preview']." ".sprintf(Lang::$timeUnits['ago'], Util::formatTime($row['elapsed'] * 100, true))."]]></description>\n". // todo (low): preview should be html-formated
                "<pubDate>".date(DATE_RSS, time() - $row['elapsed'])."</pubDate>\n".
                "<guid>".HOST_URL.'?go-to-comment&amp;id='.$row['id']."</guid>\n".
                "<domain />\n".
                "</item>\n";
        }

        $xml .= "</channel>\n</rss>";

        return $xml;
    }

    protected function generateTitle()
    {
        if ($this->page == 'most-comments')
        {
            if ($this->category && in_array($this->category[0], [7, 30]))
                array_unshift($this->title, sprintf(Lang::$main['mostComments'][1], $this->category[0]));
            else
                array_unshift($this->title, Lang::$main['mostComments'][0]);
        }

        array_unshift($this->title, Lang::$main['utilities'][array_search($this->page, $this->validPages)]);
    }

    protected function generatePath()
    {
        $this->path[] = array_search($this->page, $this->validPages);

        if ($this->page == 'most-comments')
        {
            if ($this->category && in_array($this->category[0], [7, 30]))
                $this->path[] = $this->category[0];
            else
                $this->path[] = 1;
        }
    }
}

?>
