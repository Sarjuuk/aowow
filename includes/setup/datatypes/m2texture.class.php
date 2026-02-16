<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2Texture                                             // Mo3: Material
{
    public readonly UInt32 $type;
    public readonly UInt32 $flags;
    public readonly string $filename;                       // for non-hardcoded textures (type != 0), this still points to a zero-byte-only string.

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->type  = $byteBuffer->readUInt32();
        $this->flags = $byteBuffer->readUInt32();

        $nameOffset = new M2Array($byteBuffer);
        $this->filename = trim($byteBuffer->readOffset($nameOffset->size, $nameOffset->offset));
    }

    public function pack() : string
    {
        $data  = '';

        $data .= $this->type->pack();                       // type
        $data .= $this->flags->pack();                      // flags

        // filename - note! in future this will just be a uint32 FileDataID
        $data .= pack(PACK_U16, strlen($this->filename));
        $data .= $this->filename;

        return $data;
    }
}
