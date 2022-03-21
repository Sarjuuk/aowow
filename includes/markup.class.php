<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
    this is just a skeleton for now
    at some point i'll need to (at least rudamentary) parse
    back and forth between markup and html
*/

class Markup
{
    private $text      = '';
    private $jsGlobals = [];

    private static $dbTagPattern = '/(?<!\\\\)\[(npc|object|item|itemset|quest|spell|zone|faction|pet|achievement|statistic|title|event|class|race|skill|currency|emote|enchantment|money|sound|icondb)=(-?\d+)[^\]]*\]/i';

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function parseGlobalsFromText(&$jsg = [])
    {
        if (preg_match_all(self::$dbTagPattern, $this->text, $matches, PREG_SET_ORDER))
        {
            foreach ($matches as $match)
            {
                if ($match[1] == 'statistic')
                    $match[1] = 'achievement';
                else if ($match[1] == 'icondb')
                    $match[1] = 'icon';

                if ($match[1] == 'money')
                {
                    if (stripos($match[0], 'items'))
                    {
                        if (preg_match('/items=([0-9,]+)/i', $match[0], $submatch))
                        {
                            $sm = explode(',', $submatch[1]);
                            for ($i = 0; $i < count($sm); $i+=2)
                                $this->jsGlobals[Type::ITEM][$sm[$i]] = $sm[$i];
                        }
                    }

                    if (stripos($match[0], 'currency'))
                    {
                        if (preg_match('/currency=([0-9,]+)/i', $match[0], $submatch))
                        {
                            $sm = explode(',', $submatch[1]);
                            for ($i = 0; $i < count($sm); $i+=2)
                                $this->jsGlobals[Type::CURRENCY][$sm[$i]] = $sm[$i];
                        }
                    }
                }
                else if ($type = Type::getIndexFrom(Type::IDX_FILE_STR, $match[1]))
                    $this->jsGlobals[$type][$match[2]] = $match[2];
            }
        }

        Util::mergeJsGlobals($jsg, $this->jsGlobals);

        return $this->jsGlobals;
    }

    public function stripTags($globals = [])
    {
        // since this is an article the db-tags should already be parsed
        $text = preg_replace_callback(self::$dbTagPattern, function ($match) use ($globals) {
            if ($match[1] == 'statistic')
                $match[1] = 'achievement';
            else if ($match[1] == 'icondb')
                $match[1] = 'icon';
            else if ($match[1] == 'money')
            {
                $moneys = [];
                if (stripos($match[0], 'items'))
                {
                    if (preg_match('/items=([0-9,]+)/i', $match[0], $submatch))
                    {
                        $sm = explode(',', $submatch[1]);
                        for ($i = 0; $i < count($sm); $i += 2)
                        {
                            if (!empty($globals[Type::ITEM][1][$sm[$i]]))
                                $moneys[] = $globals[Type::ITEM][1][$sm[$i]]['name'];
                            else
                                $moneys[] = Util::ucFirst(Lang::game('item')).' #'.$sm[$i];
                        }
                    }
                }

                if (stripos($match[0], 'currency'))
                {
                    if (preg_match('/currency=([0-9,]+)/i', $match[0], $submatch))
                    {
                        $sm = explode(',', $submatch[1]);
                        for ($i = 0; $i < count($sm); $i += 2)
                        {
                            if (!empty($globals[Type::CURRENCY][1][$sm[$i]]))
                                $moneys[] = $globals[Type::CURRENCY][1][$sm[$i]]['name'];
                            else
                                $moneys[] = Util::ucFirst(Lang::game('curency')).' #'.$sm[$i];
                        }
                    }
                }

                return Lang::concat($moneys);
            }
            if ($type = Type::getIndexFrom(Type::IDX_FILE_STR, $match[1]))
            {
                if (!empty($globals[$type][1][$match[2]]))
                    return $globals[$type][1][$match[2]]['name'];
                else
                    return Util::ucFirst(Lang::game($match[1])).' #'.$match[2];
            }

            trigger_error('Markup::stripTags() - encountered unhandled db-tag: '.var_export($match));
            return '';
        }, $this->text);

        $text     = str_replace('[br]', "\n",  $text);
        $stripped = '';

        $inTag = false;
        for ($i = 0; $i < strlen($text); $i++)
        {
            if ($text[$i] == '[')
                $inTag = true;
            if (!$inTag)
                $stripped .= $text[$i];
            if ($text[$i] == ']')
                $inTag = false;
        }

        return $stripped;
    }

    public function fromHtml()
    {
    }

    public function toHtml()
    {
    }
}

?>
