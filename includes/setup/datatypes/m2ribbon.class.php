<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2Ribbon                                              // Mo3: RibbonEmitter
{
    public readonly UInt32   $ribbonId;                     // Always (as I have seen): -1.
    public readonly UInt32   $boneIndex;                    // A bone to attach to.
    public readonly C3Vector $position;                     // And a position, relative to that bone.
    public readonly M2Array  $textureIndices;               // into textures
    public readonly M2Array  $materialIndices;              // into materials
    public readonly M2Track  $colorTrack;                   // An RGB multiple for the material.[1]
    public readonly M2Track  $alphaTrack;                   // And an alpha value in a short, where: 0 - transparent, 0x7FFF - opaque.
    public readonly M2Track  $heightAboveTrack;             // Above and Below – These fields define the width of a ribbon in units based on their offset from the origin.[1]
    public readonly M2Track  $heightBelowTrack;             // do not set to same!
    public readonly Double   $edgesPerSecond;               // this defines how smooth the ribbon is. A low value may produce a lot of edges. - The number of quads generated.[1]
    public readonly Double   $edgeLifetime;                 // the length aka Lifespan. in seconds - Time in seconds that the quads stay around after being generated.[1]
    public readonly Double   $gravity;                      // use arcsin(val) to get the emission angle in degree - Can be positive or negative. Will cause the ribbon to sink or rise in the z axis over time.[1]
    public readonly Uint16   $textureRows;                  // tiles in texture
    public readonly Uint16   $textureCols;                  // Texture Rows and Cols – Allows an animating texture similar to BlizParticle. Set the number of rows and columns equal to the texture.[1]
    public readonly M2Track  $texSlotTrack;                 // Pick the index number of rows and columns, and animate this number to get a cycle.[1]
    public readonly M2Track  $visibilityTrack;
    public readonly int16    $priorityPlane;
    public readonly Int8     $RibbonColorIndex;
    public readonly Int8     $textureTransformLookupIndex;  // Index into m2data.header.textureTransformCombos. Applied only if m2data.header.global_flags.flag_unk_0x20000 flag is set

    private BinaryFile $ref;

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->ribbonId                    = $byteBuffer->readUInt32();
        $this->boneIndex                   = $byteBuffer->readUInt32();
        $this->position                    = new C3Vector($byteBuffer);
        $this->textureIndices              = new M2Array($byteBuffer);
        $this->materialIndices             = new M2Array($byteBuffer);
        $this->colorTrack                  = new M2Track($byteBuffer, C3Vector::class);
        $this->alphaTrack                  = new M2Track($byteBuffer, Fixed16::class);
        $this->heightAboveTrack            = new M2Track($byteBuffer, Double::class);
        $this->heightBelowTrack            = new M2Track($byteBuffer, Double::class);
        $this->edgesPerSecond              = $byteBuffer->readFloat();
        $this->edgeLifetime                = $byteBuffer->readFloat();
        $this->gravity                     = $byteBuffer->readFloat();
        $this->textureRows                 = $byteBuffer->readUInt16();
        $this->textureCols                 = $byteBuffer->readUInt16();
        $this->texSlotTrack                = new M2Track($byteBuffer, UInt16::class);
        $this->visibilityTrack             = new M2Track($byteBuffer, Char::class);
        $this->priorityPlane               = $byteBuffer->readInt16();
        $this->RibbonColorIndex            = $byteBuffer->readInt8();
        $this->textureTransformLookupIndex = $byteBuffer->readInt8();

        $this->ref = $byteBuffer;
    }

    public function pack() : string
    {
        $data  = [];

        $data .= $this->ribbonId->pack();                   // id
        $data .= $this->boneIndex->pack();                  // boneId
        $data .= $this->position->pack();                   // position
        $data .= $this->edgesPerSecond->pack();             // resolution
        $data .= $this->edgeLifetime->pack();               // length
        $data .= $this->gravity->pack();                    // emissionAngle
        $data .= $this->textureRows->pack();                // s1 - unsure, but the only int16 that make sense
        $data .= $this->textureCols->pack();                // s2 - unsure, but the only int16 that make sense

        $cPos = $this->ref->tell();
        $this->ref->seek($this->textureIndices->offset);

        $data .= pack(PACK_I32, $this->textureIndices->size); // count
        for ($i = 0; $i < $this->textureIndices->size; $i++)  // textureIds - unsure, maybe materialIndices..?
            $data .= $this->ref->readUInt32()->pack();

        $this->ref->seek($cPos);

        $data .= $this->colorTrack->pack();                 // color
        $data .= $this->alphaTrack->pack();                 // alpha
        $data .= $this->heightAboveTrack->pack();           // above
        $data .= $this->heightBelowTrack->pack();           // below

        return $data;
    }
}
