<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class C4Quaternion implements IDataType
{
    private readonly string $x;
    private readonly string $y;
    private readonly string $z;
    private readonly string $w;

    public function __construct(BinaryFile $byteBuffer)
    {
        [$this->x, $this->y, $this->z, $this->w] = str_split($byteBuffer->read(16), 4);
    }

    public function pack() : string
    {
        return $this->x . $this->y . $this->z . $this->w;
    }

    public function unpack() : array
    {
        return array(
            'x' => current(unpack('f', $this->x)),
            'y' => current(unpack('f', $this->y)),
            'z' => current(unpack('f', $this->z)),
            'w' => current(unpack('f', $this->w))
        );
    }

    public function __debugInfo() : array
    {
        return $this->unpack();
    }
}

class C3Vector implements IDataType
{
    private readonly string $x;
    private readonly string $y;
    private readonly string $z;

    public function __construct(BinaryFile $byteBuffer)
    {
        [$this->x, $this->y, $this->z] = str_split($byteBuffer->read(12), 4);
    }

    public function pack() : string
    {
        return $this->x . $this->y . $this->z;
    }

    public function unpack() : array
    {
        return array(
            'x' => current(unpack('f', $this->x)),
            'y' => current(unpack('f', $this->y)),
            'z' => current(unpack('f', $this->z))
        );
    }

    public function __debugInfo() : array
    {
        return $this->unpack();
    }
}

class C2Vector implements IDataType
{
    private readonly string $x;
    private readonly string $y;

    public function __construct(BinaryFile $byteBuffer)
    {
        [$this->x, $this->y] = str_split($byteBuffer->read(8), 4);
    }

    public function pack() : string
    {
        return $this->x . $this->y;
    }

    public function unpack() : array
    {
        return array(
            'x' => current(unpack('f', $this->x)),
            'y' => current(unpack('f', $this->y))
        );
    }

    public function __debugInfo() : array
    {
        return $this->unpack();
    }
}

class CRange implements IDataType
{
    private readonly string $min;
    private readonly string $max;

    public function __construct(BinaryFile $byteBuffer)
    {
        [$this->min, $this->max] = str_split($byteBuffer->read(8), 4);
    }

    public function pack() : string
    {
        return $this->min . $this->max;
    }

    public function unpack() : array
    {
        return array(
            'min' => current(unpack('f', $this->min)),
            'max' => current(unpack('f', $this->max))
        );
    }

    public function __debugInfo() : array
    {
        return $this->unpack();
    }
};

class Fixed16 implements IDataType
{
    private readonly string $data;

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->data = $byteBuffer->read(2);
    }

    public function pack() : string
    {
        return pack('f', current(unpack('s', $this->data)) / 0x7FFF);
    }

    public function unpack() : int
    {
        return current(unpack('s', $this->data));
    }

    public function __debugInfo() : array
    {
        return [$this->unpack()];
    }
}

?>
