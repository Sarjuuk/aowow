<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2Color                                               // Mo3: Color
{
    public readonly M2Track $color;                         // vertex colors in rgb order
    public readonly M2Track $alpha;                         // 0 - transparent, 0x7FFF - opaque. Normaly NonInterp

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->color = new M2Track($byteBuffer, C3Vector::class);
        $this->alpha = new M2Track($byteBuffer, Fixed16::class);
    }

    public function pack() : string
    {
        $data  = '';

        $data .= $this->color->pack();                      // rgb
        $data .= $this->alpha->pack();                      // alpha

        return $data;
    }
}
