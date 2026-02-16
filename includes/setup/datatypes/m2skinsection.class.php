<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2SkinSection                                         // Mo3: Mesh
{
    public readonly UInt16   $skinSectionId;                // Mesh part ID, see below.
    public readonly UInt16   $level;                        // (level << 16) is added (|ed) to startTriangle and alike to avoid having to increase those fields to uint32s.
    public readonly UInt16   $vertexStart;                  // Starting vertex number.
    public readonly UInt16   $vertexCount;                  // Number of vertices.
    public readonly UInt16   $indexStart;                   // Starting triangle index (that's 3* the number of triangles drawn so far).
    public readonly UInt16   $indexCount;                   // Number of triangle indices.
    public readonly UInt16   $boneCount;                    // Number of elements in the bone lookup table. Max seems to be 256 in Wrath. Shall be ≠ 0.
    public readonly UInt16   $boneComboIndex;               // Starting index in the bone lookup table.
    public readonly UInt16   $boneInfluences;               // Highest number of bones referenced by a vertex of this submesh. 3.3.5a and suspectedly all other client revisions. -- Skarn
    public readonly UInt16   $centerBoneIndex;
    public readonly C3Vector $centerPosition;               // Average position of all the vertices in the sub mesh.
    public readonly C3Vector $sortCenterPosition;           // The center of the box when an axis aligned box is built around the vertices in the submesh.
    public readonly Double   $sortRadius;                   // Distance of the vertex farthest from CenterBoundingBox.

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->skinSectionId      = $byteBuffer->readUInt16();
        $this->level              = $byteBuffer->readUInt16();
        $this->vertexStart        = $byteBuffer->readUInt16();
        $this->vertexCount        = $byteBuffer->readUInt16();
        $this->indexStart         = $byteBuffer->readUInt16();
        $this->indexCount         = $byteBuffer->readUInt16();
        $this->boneCount          = $byteBuffer->readUInt16();
        $this->boneComboIndex     = $byteBuffer->readUInt16();
        $this->boneInfluences     = $byteBuffer->readUInt16();
        $this->centerBoneIndex    = $byteBuffer->readUInt16();
        $this->centerPosition     = new C3Vector($byteBuffer);
        $this->sortCenterPosition = new C3Vector($byteBuffer);
        $this->sortRadius         = $byteBuffer->readFloat();
    }

    public function pack() : string
    {
        $data  = '';

        $data .= $this->skinSectionId->pack();              // id
        $data .= $this->level->pack();                      // indexWrap
        $data .= $this->vertexStart->pack();                // vertexStart
        $data .= $this->vertexCount->pack();                // vertexCount
        $data .= $this->indexStart->pack();                 // indexStart
        $data .= $this->indexCount->pack();                 // indexCount
        $data .= pack(PACK_U16, 0);                         // WH uint16 of unknown origin/purpose (centerBoneIndex?)
        $data .= $this->centerPosition->pack();             // centerOfMass
        $data .= $this->sortCenterPosition->pack();         // centerBounds
        $data .= $this->sortRadius->pack();                 // radius

        return $data;
    }
}
