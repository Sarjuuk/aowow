<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

/*
    this is just a skeleton for now
    at some point i'll need to (at least rudamentary) parse
    back and forth between markup and html
*/

class Markup
{
    private $text      = '';
    private $jsGlobals = [];

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function parseGlobalsFromText(&$jsg = [])
    {
        if (preg_match_all('/(?<!\\\\)\[(npc|object|item|itemset|quest|spell|zone|faction|pet|achievement|statistic|title|event|class|race|skill|currency)=(-?\d+)[^\]]*\]/i', $this->text, $matches, PREG_SET_ORDER))
        {
            foreach ($matches as $match)
            {
                if ($match[1] == 'statistic')
                    $match[1] = 'achievement';

                if ($type = array_search($match[1], Util::$typeStrings))
                    $this->jsGlobals[$type][$match[2]] = $match[2];
            }
        }

        Util::mergeJsGlobals($jsg, $this->jsGlobals);

        return $this->jsGlobals;
    }

    public function fromHtml()
    {
    }

    public function toHtml()
    {
    }
}

?>
