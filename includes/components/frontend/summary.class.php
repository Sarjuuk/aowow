<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Summary implements \JsonSerializable
{
    private  string $id          = '';                      // HTMLNode.id
    private ?string $parent      = '';                      // HTMLNode.id; if set $id is created and attached here instead of searched for
    private  string $template    = '';                      //
    private ?int    $editable    = null;                    // js:bool; defaults to TRUE
    private ?int    $draggable   = null;                    // js:bool; defaults to $editable
    private ?int    $searchable  = null;                    // js:bool; defaults to $editable && $draggable
    private ?int    $weightable  = null;                    // js:bool; defaults to $editable
    private ?int    $textable    = null;                    // js:bool; defaults to FALSE
    private ?int    $enhanceable = null;                    // js:bool; defaults to $editable
    private ?int    $level       = null;                    // js:int;  defaults to 80
    private  array  $groups      = [];                      // js:array; defaults to GET-params
    private ?array  $weights     = null;                    // js:array; defaults to GET-params

    public function __construct(array $opts)
    {
        foreach ($opts as $k => $v)
        {
            if (property_exists($this, $k))
                $this->$k = $v;
            else
                trigger_error(self::class.'::__construct - unrecognized option: ' . $k);
        }

        if (!$this->template)
            trigger_error(self::class.'::__construct - initialized without template', E_USER_WARNING);
        if (!$this->id)
            trigger_error(self::class.'::__construct - initialized without HTMLNode#id to reference', E_USER_WARNING);
    }

    public function &iterate() : \Generator
    {
        reset($this->groups);

        foreach ($this->groups as $idx => &$group)
            yield $idx => $group;
    }

    public function addGroup(array $group) : void
    {
        $this->groups[] = $group;
    }

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
        return "new Summary(".Util::toJSON($this).");\n";
    }
}

?>
