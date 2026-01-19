<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class Primitive
{
    public const /* int */    SIZE     = 1;
    public const /* string */ PACK_FMT = 'x';

    protected string $data;

    public function __construct(BinaryFile|string $data)
    {
        if (is_string($data))
            $this->data = $data;
        else
            $this->data = $data->read(static::SIZE);
    }

    public function pack() : string
    {
        return $this->data;
    }

    public function unpack() : mixed
    {
        return current(unpack(static::PACK_FMT, $this->data));
    }

    public function __debugInfo() : array
    {
        return [$this->unpack()];
    }
}

class Char extends Primitive
{
    public const /* int */    SIZE     = 1;
    public const /* string */ PACK_FMT = 'C';

    public function unpack() : string
    {
        return chr(parent::unpack());
    }
}

class Boolean extends Primitive
{
    public const /* int */    SIZE     = 1;
    public const /* string */ PACK_FMT = 'C';

    public function unpack() : string
    {
        return !!(parent::unpack());
    }
}

class UInt8 extends Primitive
{
    public const /* int */    SIZE     = 1;
    public const /* string */ PACK_FMT = 'C';
}

class Int8 extends Primitive
{
    public const /* int */    SIZE     = 1;
    public const /* string */ PACK_FMT = 'c';
}

class UInt16 extends Primitive
{
    public const /* int */    SIZE     = 2;
    public const /* string */ PACK_FMT = 'v';
}

class Int16 extends Primitive
{
    public const /* int */    SIZE     = 2;
    public const /* string */ PACK_FMT = 's';
}

class UInt32 extends Primitive
{
    public const /* int */    SIZE     = 4;
    public const /* string */ PACK_FMT = 'V';
}

class Int32 extends Primitive
{
    public const /* int */    SIZE     = 4;
    public const /* string */ PACK_FMT = 'l';
}

class Double extends Primitive
{
    public const /* int */    SIZE     = 4;
    public const /* string */ PACK_FMT = 'f';
}

?>
