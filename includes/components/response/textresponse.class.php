<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


trait TrTooltip
{
    private array $enhancedTT = [];

    public function getCacheKeyComponents() : array
    {
        $key = array(
            $this->type,                                    // DBType
            $this->typeId,                                  // DBTypeId/category
            User::$groups,                                  // staff mask
            ''                                              // misc (here tooltip)
        );

        if ($this->enhancedTT)
            $key[3] = md5(serialize($this->enhancedTT));

        return $key;
    }
}


trait TrRss
{
    private array $feedData = [];

    protected function generateRSS(string $title, string $link) : string
    {
        $root = new SimpleXML('<rss />');
        $root->addAttribute('version', '2.0');

        $channel = $root->addChild('channel');

        $channel->addChild('title',         Cfg::get('NAME_SHORT').' - '.$title);
        $channel->addChild('link',          Cfg::get('HOST_URL').'/?'.$link);
        $channel->addChild('description',   Cfg::get('NAME'));
        $channel->addChild('language',      implode('-', str_split(Lang::getLocale()->json(), 2)));
        $channel->addChild('ttl',           Cfg::get('TTL_RSS'));
        $channel->addChild('lastBuildDate', date(DATE_RSS));

        foreach ($this->feedData as $row)
        {
            $item = $channel->addChild('item');

            foreach ($row as $key => [$isCData, $attrib, $text])
            {
                if ($isCData && $text)
                    $child = $item->addChild($key)->addCData($text);
                else
                    $child = $item->addChild($key, $text);

                foreach ($attrib as $k => $v)
                    $child->addAttribute($k, $v);
            }
        }

        // pretty print for debug
        if (Cfg::get('DEBUG') >= LOG_LEVEL_INFO)
        {
            $dom = new \DOMDocument('1.0');
            $dom->formatOutput = true;
            $dom->loadXML($root->asXML());
            return $dom->saveXML();
        }

        return $root->asXML();
    }
}

trait TrCommunityHelper
{
    private function handleCaption(?string $caption) : string
    {
        if (!$caption)
            return '';

        // trim excessive whitespaces
        $caption = trim(preg_replace('/\s{2,}/', ' ', $caption));

        // shorten to fit db
        return substr($caption, 0, 200);
    }
}

class TextResponse extends BaseResponse
{
    protected  string $contentType = MIME_TYPE_JAVASCRIPT;
    protected ?string $redirectTo  = null;
    protected  array  $params      = [];

    /// generation stats
    protected static float $time = 0.0;

    public function __construct(string $rawParam = '')
    {
        self::$time   = microtime(true);
        $this->params = explode('.', $rawParam);
        // todo - validate params?

        parent::__construct();

        if (Cfg::get('MAINTENANCE') && !User::isInGroup(U_GROUP_EMPLOYEE))
            $this->generate404();
    }

    // by default ajax has nothing to say
    protected function onUserGroupMismatch() : never
    {
        trigger_error('TextResponse::onUserGroupMismatch - loggedIn: '.($this->requiresLogin ? 'yes' : 'no').'; expected: '.Util::asHex($this->requiredUserGroup).'; is: '.Util::asHex(User::$groups), E_USER_WARNING);

        $this->generate403();
    }

    public function generate404(?string $out = null) : never
    {
        header('HTTP/1.0 404 Not Found', true, 404);
        header($this->contentType);
        exit($out);
    }

    public function generate403(?string $out = null) : never
    {
        header('HTTP/1.0 403 Forbidden', true, 403);
        header($this->contentType);
        exit($out);
    }

    protected function display() : void
    {
        if ($this->redirectTo)
            $this->forward($this->redirectTo);

        $out = ($this instanceof ICache) ? $this->applyOnCacheLoaded($this->result) : $this->result;

        $this->sendNoCacheHeader();
        header($this->contentType);

        // NOTE - this may fuck up some javascripts that say they expect ajax, but use the whole string anyway
        // so it's limited to tooltips
        if (Cfg::get('DEBUG') && User::isInGroup(U_GROUP_STAFF) && $this->result instanceof Tooltip)
        {
            $this->sumSQLStats();

            echo "/*\n";
            echo " * generated in ".DateTime::formatTimeElapsedFloat((microtime(true) - self::$time) * 1000)."\n";
            echo " * " . parent::$sql['count'] . " SQL queries in " . DateTime::formatTimeElapsedFloat(parent::$sql['time'] * 1000) . "\n";
            if ($this instanceof ICache && static::$cacheStats)
            {
                [$mode, $set, $lifetime] = static::$cacheStats;
                echo " * stored in " . ($mode == CACHE_MODE_MEMCACHED ? 'Memcached' : 'filecache') . ":\n";
                echo " *  + ".date('c', $set) . ' - ' . DateTime::formatTimeElapsedFloat((time() - $set) * 1000) . " ago\n";
                echo " *  - ".date('c', $set + $lifetime) . ' - in '.DateTime::formatTimeElapsedFloat(($set + $lifetime - time()) * 1000) . "\n";
            }
            echo " */\n\n";
        }

        echo $out;
    }

    protected function generate() : void {}
}

?>
