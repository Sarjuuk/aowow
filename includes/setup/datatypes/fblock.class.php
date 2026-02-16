<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// Fake AnimationBlock
class FBlock                                                // Mo3: SAnimated children
{
    public readonly M2Array $timestamps;
    public readonly M2Array $keys;

    private BinaryFile $ref;

    public function __construct(BinaryFile $byteBuffer, private readonly string $dataType)
    {
        $this->timestamps = new M2Array($byteBuffer);
        $this->keys       = new M2Array($byteBuffer);

        $this->ref = $byteBuffer;
    }

    public function pack() : string
    {
        $data  = '';
        $cPos  = $this->ref->tell();

        $this->ref->seek($this->timestamps->offset);

        $data .= pack(PACK_I32, $this->timestamps->size);
        for ($i = 0; $i < $this->timestamps->size; $i++)
            $data .= $this->ref->readUInt32()->pack();

        $this->ref->seek($this->keys->offset);

        $data .= pack(PACK_I32, $this->keys->size);
        for ($i = 0; $i < $this->keys->size; $i++)
            $data .= (new $this->dataType($this->ref))->pack(); // data[]

        $this->ref->seek($cPos);

        return $data;
    }
}
