<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2Vertex                                              // Mo3: Vertex
{
    public readonly C3Vector $pos;
    public readonly array    $boneWeights;                  // 4 x uint8
    public readonly array    $boneIndices;                  // 4 x uint8
    public readonly C3Vector $normal;
    public readonly C2Vector $texCoordsA;                   // 2 x C2Vector - two textures, depending on shader used
    public readonly C2Vector $texCoordsB;                   //

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->pos         = new C3Vector($byteBuffer);
        $this->boneWeights = str_split($byteBuffer->read(4));
        $this->boneIndices = str_split($byteBuffer->read(4));
        $this->normal      = new C3Vector($byteBuffer);
        $this->texCoordsA  = new C2Vector($byteBuffer);
        $this->texCoordsB  = new C2Vector($byteBuffer);
    }

    public function pack() : string
    {
        $data  = '';

        $data .= $this->pos->pack();
        $data .= $this->normal->pack();
        $data .= $this->texCoordsA->pack();
        $data .= $this->texCoordsB->pack();
        $data .= implode('', $this->boneWeights);
        $data .= implode('', $this->boneIndices);

        return $data;
    }

    public function __debugInfo() : array
    {
        return array(
            'pos'         => $this->pos,
            'boneWeights' => array_map(fn($x) => unpack('C', $x)[1], $this->boneWeights),
            'boneIndices' => array_map(fn($x) => unpack('C', $x)[1], $this->boneIndices),
            'normal'      => $this->normal,
            'texCoordsA'  => $this->texCoordsA,
            'texCoordsB'  => $this->texCoordsB
        );
    }
}
