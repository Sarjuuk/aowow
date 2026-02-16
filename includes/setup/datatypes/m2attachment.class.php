<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2Attachment                                          // Mo3: Attachment
{
    public readonly UInt32   $id;                           // Referenced in the lookup-block below.
    public readonly UInt16   $bone;                         // attachment base
    public readonly UInt16   $unknown;                      // see BogBeast.m2 in vanilla for a model having values here
    public readonly C3Vector $position;                     // relative to bone; Often this value is the same as bone's pivot point
    public readonly M2Track  $animateAttached;              // whether or not the attached model is animated when this model is. only a bool is used. default is true.

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->id              = $byteBuffer->readUInt32();
        $this->bone            = $byteBuffer->readUInt16();
        $this->unknown         = $byteBuffer->readUInt16();
        $this->position        = new C3Vector($byteBuffer);
        $this->animateAttached = new M2Track($byteBuffer, Char::class);
    }

    public function pack() : string
    {
        $data  = '';

        $data .= $this->id->pack();                         // id
        $data .= $this->bone->pack();                       // bone
        $data .= $this->position->pack();                   // position

        return $data;
    }
}
