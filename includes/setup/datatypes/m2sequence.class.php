<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2Sequence                                            // Mo3: Animation
{
    public readonly UInt16   $id;                           // Animation id in AnimationData.dbc
    public readonly UInt16   $subId;                        // Sub-animation id: Which number in a row of animations this one is.
    public readonly UInt32   $duration;                     // The length of this animation sequence in milliseconds.
    public readonly Double   $moveSpeed;                    // This is the speed the character moves with in this animation.
    public readonly UInt32   $flags;
    public readonly UInt16   $frequency;                    // This is used to determine how often the animation is played. For all animations of the same type, this adds up to 0x7FFF (32767).
    // uint16 _padding
    public readonly M2Range  $replay;                       // May both be 0 to not repeat. Client will pick a random number of repetitions within bounds if given.
    public readonly UInt32   $blendTime;                    // The client blends (lerp) animation states between animations where the end and start values differ. This specifies how long that blending takes. Values: 0, 50, 100, 150, 200, 250, 300, 350, 500.
    public readonly M2Bounds $bounds;
    public readonly UInt16   $variationNext;                // id of the following animation of this AnimationID, points to an Index or is -1 if none.
    public readonly UInt16   $aliasNext;                    // id in the list of animations. Used to find actual animation if this sequence is an alias (flags & 0x40)

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->id            = $byteBuffer->readUInt16();
        $this->subId         = $byteBuffer->readUInt16();
        $this->duration      = $byteBuffer->readUInt32();
        $this->moveSpeed     = $byteBuffer->readFloat();
        $this->flags         = $byteBuffer->readUInt32();
        $this->frequency     = $byteBuffer->readUInt16();
        $byteBuffer->ffwd(2);                               // _padding
        $this->replay        = new M2Range($byteBuffer);
        $this->blendTime     = $byteBuffer->readUInt32();
        $this->bounds        = new M2Bounds($byteBuffer);
        $this->variationNext = $byteBuffer->readUInt16();
        $this->aliasNext     = $byteBuffer->readUInt16();
    }

    public function pack() : string
    {
        $data  = '';

        $data .= $this->id->pack();                         // id
        $data .= $this->subId->pack();                      // subId
        $data .= $this->flags->pack();                      // flags (maybe AnimationData.dbc/flags ?)
        $data .= $this->duration->pack();                   // length
        $data .= pack(PACK_U16, 0);                         // WH uint16 frequency (pretty sure)
        $data .= pack(PACK_U16, 0);                         // WH uint16 blendIn  (pretty sure)
        $data .= pack(PACK_U16, 0);                         // WH uint16 blendOut (pretty sure)
        $data .= pack(PACK_FLOAT.'3', 0, 0, 0);             // WH C3Vector of unknown origin/purpose (bounds min?)
        $data .= pack(PACK_FLOAT.'3', 0, 0, 0);             // WH C3Vector of unknown origin/purpose (bounds max?)
        $data .= $this->variationNext->pack();              // next
        $data .= $this->aliasNext->pack();                  // index (not entirely sure)

        if ($name = DB::Aowow()->selectCell('SELECT `name` FROM dbc_animationdata WHERE `id` = ?d', $this->id->unpack()))
        {
            $data .= pack(PACK_U8, 1);                      // available
            $data .= pack(PACK_U16, strlen($name)) . $name;
        }
        else
        {
            $data .= pack(PACK_U8, 0);                      // available - maybe other conditions?
            trigger_error('M2Sequence::pack - id not in AnimationData.dbc');
        }

        return $data;
    }
}
