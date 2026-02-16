<?php

namespace Aowow;

use JsonSerializable;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
 * meta info files for the modelviewer all have the same structure,
 * but depending on the subject different fields are filled
 */

abstract class MetaJSON implements JsonSerializable
{
    public string $Model             = '';                  // originally FileDataId; an int of 0
    public ?array $Textures          = null;
    public ?array $Textures2         = null;
    public ?array $TextureFiles      = null;
    public ?array $ModelFiles        = null;
    public ?array $Item              = null;
    public ?array $Creature          = null;
    public ?array $Character         = null;
    public ?array $ItemEffects       = null;
    public ?array $Equipment         = null;
    public ?array $ComponentTextures = null;
    public ?array $ComponentModels   = null;
    public ?array $StateKit          = null;
    public ?array $StateKits         = null;
    public ?array $Decor             = null;
    public int    $Scale             = 1;

    protected string $path;

    public function __construct(protected int $displayId)
    {
    }

    public function jsonSerialize() : array
    {
        $result = [];

        $properties = (new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property)
            $result[$property->getName()] = $this->{$property->getName()};

        return $result;
    }

    public function write() : bool
    {
        $file = 'static/modelviewer/meta/'.$this->path.'/'.$this->displayId.'.json';
        $data = Util::toJSON($this);

        return Util::writeFile($file, $data);
    }

    abstract function appendData(mixed $data) : void;
}

?>
