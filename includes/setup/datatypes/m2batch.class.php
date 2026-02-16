<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2Batch                                               // Mo3: TextureUnit
{
    public readonly UInt8  $flags;                          // Usually 16 for static textures, and 0 for animated textures. &0x1: materials invert something; &0x2: transform &0x4: projected texture; &0x10: something batch compatible; &0x20: projected texture?; &0x40: possibly don't multiply transparency by texture weight transparency to get final transparency value(?)
    public readonly Int8   $priorityPlane;
    public readonly UInt16 $shaderId;                       // See below.
    public readonly UInt16 $skinSectionIndex;               // A duplicate entry of a submesh from the list above.
    public readonly UInt16 $geosetIndex;                    // See below. New name: flags2. 0x2 - projected. 0x8 - EDGF chunk in m2 is mandatory and data from is applied to this mesh
    public readonly UInt16 $colorIndex;                     // A Color out of the Colors-Block or -1 if none.
    public readonly UInt16 $materialIndex;                  // The renderflags used on this texture-unit.
    public readonly UInt16 $materialLayer;                  // Capped at 7 (see CM2Scene::BeginDraw)
    public readonly UInt16 $textureCount;                   // 1 to 4. See below. Also seems to be the number of textures to load, starting at the texture lookup in the next field (0x10).
    public readonly UInt16 $textureComboIndex;              // Index into Texture lookup table
    public readonly UInt16 $textureCoordComboIndex;         // Index into the texture mapping lookup table.
    public readonly UInt16 $textureWeightComboIndex;        // Index into transparency lookup table.
    public readonly UInt16 $textureTransformComboIndex;     // Index into uvanimation lookup table.

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->flags                      = $byteBuffer->readUInt8();
        $this->priorityPlane              = $byteBuffer->readInt8();
        $this->shaderId                   = $byteBuffer->readUInt16();
        $this->skinSectionIndex           = $byteBuffer->readUInt16();
        $this->geosetIndex                = $byteBuffer->readUInt16();
        $this->colorIndex                 = $byteBuffer->readUInt16();
        $this->materialIndex              = $byteBuffer->readUInt16();
        $this->materialLayer              = $byteBuffer->readUInt16();
        $this->textureCount               = $byteBuffer->readUInt16();
        $this->textureComboIndex          = $byteBuffer->readUInt16();
        $this->textureCoordComboIndex     = $byteBuffer->readUInt16();
        $this->textureWeightComboIndex    = $byteBuffer->readUInt16();
        $this->textureTransformComboIndex = $byteBuffer->readUInt16();
    }

    public function pack() : string
    {
        $data  = '';

        $data .= $this->flags->pack();                      // flags
        $data .= $this->priorityPlane->pack();              // priorityPlane
        $data .= $this->shaderId->pack();                   // shaderId
        $data .= $this->skinSectionIndex->pack();           // meshIndex
        $data .= $this->geosetIndex->pack();                // geosetIndex
        $data .= $this->colorIndex->pack();                 // colorIndex
        $data .= $this->materialIndex->pack();              // renderFlagIndex
        $data .= $this->materialLayer->pack();              // materialLayer
        $data .= $this->textureCount->pack();               // opcount
        $data .= $this->textureComboIndex->pack();          // materialIndex
        $data .= $this->textureCoordComboIndex->pack();     // texUnitIndex
        $data .= $this->textureWeightComboIndex->pack();    // alphaIndex
        $data .= $this->textureTransformComboIndex->pack(); // textureAnimIndex (unsure)

        return $data;
    }
}
