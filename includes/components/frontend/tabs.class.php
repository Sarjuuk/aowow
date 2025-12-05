<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Tabs implements \JsonSerializable, \Countable
{
    private array $__tabs   = [];

    private  string $parent      = '';                      // HTMLNode
    private ?int    $poundable   = null;                    // js:bool
    private ?int    $forceScroll = null;                    // js:bool
    private ?int    $noScroll    = null;                    // js:bool
    private ?string $trackable   = null;                    // String to track in Google Analytics .. often a DB Type

    private ?string $onLoad = null;                         // js::callable
    private ?string $onShow = null;                         // js::callable
    private ?string $onHide = null;                         // js::callable

    public function __construct(array $opts, public readonly string $__tabVar = 'myTabs', private bool $__forceTabs = false)
    {
        foreach ($opts as $k => $v)
        {
            if (property_exists($this, $k))
                $this->$k = $v;
            else
                trigger_error(self::class.'::__construct - unrecognized option: ' . $k);
        }
    }

    /**
     * @return \Generator<int, Listview> tabIndex => Listview
     */
    public function &iterate() : \Generator
    {
        reset($this->__tabs);

        foreach ($this->__tabs as $idx => &$tab)
            yield $idx => $tab;
    }

    public function addListviewTab(Listview $lv) : void
    {
        $this->__tabs[] = $lv;
    }

    public function addDataTab(string $id, string $name, string $data) : void
    {
        $this->__tabs[] = ['id' => $id, 'name' => $name, 'data' => $data];
        $this->__forceTabs = true;                          // otherwise a single DataTab could not be accessed
    }

    public function getDataContainer() : \Generator
    {
        foreach ($this->__tabs as $tab)
            if (is_array($tab))
                yield '<div class="text tabbed-contents" id="tab-'.$tab['id'].'" style="display:none;">'.$tab['data'].'</div>';
    }

    public function getFlush() : string
    {
        if ($this->isTabbed())
            return $this->__tabVar.".flush();";

        return '';
    }

    public function isTabbed() : bool
    {
        return count($this->__tabs) > 1 || $this->__forceTabs;
    }


    /***********************/
    /* enable deep cloning */
    /***********************/

    public function __clone()
    {
        foreach ($this->__tabs as $idx => $tab)
        {
            if (is_array($tab))
                continue;

            $this->__tabs[$idx] = clone $tab;
        }
    }


    /******************/
    /* make countable */
    /******************/

    public function count() : int
    {
        return count($this->__tabs);
    }


    /************************/
    /* make Tabs stringable */
    /************************/

    public function jsonSerialize() : array
    {
        $result = [];

        foreach ($this as $prop => $val)
            if ($val !== null && $prop[0] != '_')
                $result[$prop] = $val;

        return $result;
    }

    public function __toString() : string
    {
        $result = '';

        if ($this->isTabbed())
            $result .= "var ".$this->__tabVar." = new Tabs(".Util::toJSON($this).");\n";

        foreach ($this->__tabs as $tab)
        {
            if (is_array($tab))
            {
                $n = $tab['name'][0] == '$' ? substr($tab['name'], 1) : "'".$tab['name']."'";
                $result .= $this->__tabVar.".add(".$n.", { id: '".$tab['id']."' });\n";
            }
            else
            {
                if ($this->isTabbed())
                    $tab->setTabs($this->__tabVar);

                $result .= $tab;                            // Listview::__toString here
            }
        }

        return $result . "\n";
    }
}

?>
