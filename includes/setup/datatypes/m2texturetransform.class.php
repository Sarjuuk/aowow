<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2TextureTransform                                    // Mo3: TextureAnimation
{
    public readonly M2Track $translation;
    public readonly M2Track $rotation;                      // rotation center is texture center (0.5, 0.5)
    public readonly M2Track $scaling;

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->translation = new M2Track($byteBuffer, C3Vector::class);
        $this->rotation    = new M2Track($byteBuffer, C4Quaternion::class);
        $this->scaling     = new M2Track($byteBuffer, C3Vector::class);
    }

    public function pack() : string
    {
        $data  = '';
                                                            // TODO: confirm with AirElemental.m2 - has 12 TexAnims, all with translation but neither rotation nor scaling
        $data .= pack(PACK_I32, 1);                         // unsure how multiple translations could be possible? Maybe set 0 if translation is empty and content is skipped?
        for ($i = 0; $i < 1; $i++)
            $data .= $this->translation->pack();

        $data .= pack(PACK_I32, 1);                         // unsure how multiple rotations could be possible? Maybe set 0 if rotation is empty and content is skipped?
        for ($i = 0; $i < 1; $i++)                          // $this->rotation->sequenceTimes->size ? 1 : 0
            $data .= $this->rotation->pack();

        $data .= pack(PACK_I32, 1);                         // unsure how multiple scalings could be possible? Maybe set 0 if scaling is empty and content is skipped?
        for ($i = 0; $i < 1; $i++)
            $data .= $this->scaling->pack();

        return $data;
    }
}
