<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


define('PACK_U8',    'C');
define('PACK_U16',   'v');
define('PACK_U32',   'V');
define('PACK_I8',    'c');
define('PACK_I16',   's');
define('PACK_I32',   'l');
define('PACK_FLOAT', 'f');
define('PACK_CHAR',  'C');
define('PACK_BOOL',  'C');
define('PACK_RAW',   'a');

interface IDataType
{
    public function pack();
    public function unpack();
    public function __debugInfo();
}

class CAxisAlignedBox
{
    public readonly C3Vector $min;
    public readonly C3Vector $max;

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->min = new C3Vector($byteBuffer);
        $this->max = new C3Vector($byteBuffer);
    }
}

class M2CompQuat // extends C4Quaternion,  but not really
{
    public readonly float $x;
    public readonly float $y;
    public readonly float $z;
    public readonly float $w;

    public function __construct(BinaryFile $byteBuffer)
    {
        foreach (unpack('sx/sy/sz/sw', $byteBuffer->read(8)) as $k => $v)
            $this->$k = $this->convert($v);
    }

    private function convert(int $x) : float
    {
        return ($x < 0 ? $x + 32768 : $x - 32767) / 32767;
    }

    public function pack() : string
    {
        return pack('f4', $this->x, $this->y, $this->z, $this->w);
    }
}

class M2Array
{
    public readonly int $size;
    public readonly int $offset;                            // pointer to T, relative to begin of m2 data block (i.e. MD21 chunk content or begin of file)

    public function __construct(BinaryFile $byteBuffer)
    {
        [$this->size, $this->offset] = array_values(unpack('V2', $byteBuffer->read(8)));
    }
};

class M2Range
{
    public readonly int $min;
    public readonly int $max;

    public function __construct(BinaryFile $byteBuffer)
    {
        [$this->min, $this->max] = array_values(unpack('V2', $byteBuffer->read(8)));
    }
};

class M2Bounds
{
    public readonly CAxisAlignedBox $extend;
    public readonly float $radius;

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->extend = new CAxisAlignedBox($byteBuffer);
        $this->radius = unpack('f', $byteBuffer->read(4))[1];
    }
};

class M2Box
{
    public readonly C3Vector $min;
    public readonly C3Vector $max;

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->min = new C3Vector($byteBuffer);
        $this->max = new C3Vector($byteBuffer);
    }
};
