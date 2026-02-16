<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2TextureWeight                                       // Mo3: Alpha
{
    public readonly M2Track $weight;

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->weight = new M2Track($byteBuffer, Fixed16::class);
    }

    public function pack() : string
    {
        return $this->weight->pack();
    }
}
