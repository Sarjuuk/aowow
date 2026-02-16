<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2Material                                            // Mo3: RenderFlags
{
    public readonly UInt16 $flags;
    public readonly UInt16 $bleningMode;

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->flags       = $byteBuffer->readUInt16();
        $this->bleningMode = $byteBuffer->readUInt16();
    }

    public function pack() : string
    {
        $data  = '';

        $data .= $this->flags->pack();                      // flags
        $data .= $this->bleningMode->pack();                // blend

        return $data;
    }
}
