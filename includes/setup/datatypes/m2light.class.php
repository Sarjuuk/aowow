<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2Light                                               // Mo3: Light
{
    public readonly UInt16   $type;                         // Types are listed below.
    public readonly Int16    $bone;                         // -1 if not attached to a bone
    public readonly C3Vector $position;                     // relative to bone, if given
    public readonly M2Track  $ambientColor;
    public readonly M2Track  $ambientIntensity;             // defaults to 1.0
    public readonly M2Track  $diffuseColor;
    public readonly M2Track  $diffuseIntensity;             // defaults to 1.0
    public readonly M2Track  $attenuationStart;
    public readonly M2Track  $attenuationEnd;
    public readonly M2Track  $visibility;                   // enabled?

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->type             = $byteBuffer->readUInt16();
        $this->bone             = $byteBuffer->readInt16();
        $this->position         = new C3Vector($byteBuffer);
        $this->ambientColor     = new M2Track($byteBuffer, C3Vector::class);
        $this->ambientIntensity = new M2Track($byteBuffer, Double::class);
        $this->diffuseColor     = new M2Track($byteBuffer, C3Vector::class);
        $this->diffuseIntensity = new M2Track($byteBuffer, Double::class);
        $this->attenuationStart = new M2Track($byteBuffer, Double::class);
        $this->attenuationEnd   = new M2Track($byteBuffer, Double::class);
        $this->visibility       = new M2Track($byteBuffer, UInt8::class);
    }

    public function pack() : string
    {
        $data  = '';

        $data .= $this->type->pack();
        $data .= $this->bone->pack();
        $data .= $this->position->pack();
        $data .= $this->ambientColor->pack();
        $data .= $this->ambientIntensity->pack();
        $data .= $this->diffuseColor->pack();
        $data .= $this->diffuseIntensity->pack();
        $data .= $this->attenuationStart->pack();
        $data .= $this->attenuationEnd->pack();
        $data .= $this->visibility->pack();

        return $data;
    }
}
