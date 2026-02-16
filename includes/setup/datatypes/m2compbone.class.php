<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2CompBone                                            // Mo3: Bone
{
    public readonly Int32    $keyBoneId;                    // Back-reference to the key bone lookup table. -1 if this is no key bone.
    public readonly Uint32   $flags;
    public readonly int16    $parentBoneId;                 // Parent bone ID or -1 if there is none.
    public readonly UInt16   $subMeshId;                    // Mesh part ID OR uDistToParent?
    public readonly UInt16   $uDistToFurthDesc;             // struct { uint16_t uDistToFurthDesc;
    public readonly UInt16   $uZRatioOfChain;               //          uint16_t uZRatioOfChain; } CompressData; // No model has ever had this part of the union used.ᵘ
    public readonly M2Track  $translation;                  // C3Vector
    public readonly M2Track  $rotation;                     // M2CompQuat - compressed values, default is (32767,32767,32767,65535) == (0,0,0,1) == identity
    public readonly M2Track  $scale;                        // C3Vector
    public readonly C3Vector $pivot;                        // The pivot point of that bone.

    private int $boneNameCRC;

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->keyBoneId        = $byteBuffer->readInt32();
        $this->flags            = $byteBuffer->readUInt32();
        $this->parentBoneId     = $byteBuffer->readInt16();
        $this->subMeshId        = $byteBuffer->readUInt16();
        $this->uDistToFurthDesc = $byteBuffer->readUInt16();
        $this->uZRatioOfChain   = $byteBuffer->readUInt16();
        $this->translation      = new M2Track($byteBuffer, C3Vector::class);
        $this->rotation         = new M2Track($byteBuffer, M2CompQuat::class);
        $this->scale            = new M2Track($byteBuffer, C3Vector::class);
        $this->pivot            = new C3Vector($byteBuffer);

        $this->boneNameCRC = $this->uZRatioOfChain->unpack() << 16 | $this->uDistToFurthDesc->unpack();
    }

    public function pack() : string
    {
        $data  = '';

        $data .= $this->keyBoneId->pack();                  // keyId
        $data .= $this->flags->pack();                      // flags
        $data .= $this->parentBoneId->pack();               // parent
        $data .= $this->subMeshId->pack();                  // mesh
        $data .= pack(PACK_U32, $this->boneNameCRC);        // id
        $data .= $this->pivot->pack();                      // pivot

        // translation
        $data .= pack(PACK_I32, 1);                         // unsure how multiple translations could be possible?
        for ($i = 0; $i < 1; $i++)                          // var count = r.getInt32();
            $data .= $this->translation->pack();            // for (var i = 0; i < count; ++i) data[i] = new ModelViewer.Wow.AnimatedVec3(r);

        // rotation
        $data .= pack(PACK_I32, 1);                         // unsure how multiple rotations could be possible?
        for ($i = 0; $i < 1; $i++)
            $data .= $this->rotation->pack();

        // scale
        $data .= pack(PACK_I32, 1);                         // unsure how multiple scales could be possible?
        for ($i = 0; $i < 1; $i++)
            $data .= $this->scale->pack();

        return $data;
    }
}
