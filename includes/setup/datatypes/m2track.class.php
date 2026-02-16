<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2TrackBase
{
    public readonly UInt16  $interpolationType;             // or trackType
    public readonly UInt16  $globalSequence;                // or loopIndex
    public readonly M2Array $sequenceTimes;

    protected BinaryFile $ref;

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->interpolationType = $byteBuffer->readUInt16();
        $this->globalSequence    = $byteBuffer->readUInt16();
        $this->sequenceTimes     = new M2Array($byteBuffer);

        $this->ref = $byteBuffer;
    }
}

class M2Track extends M2TrackBase
{
    public readonly M2Array $values;

    public function __construct(BinaryFile $byteBuffer, private readonly string $dataType)
    {
        parent::__construct($byteBuffer);

        $this->values = new M2Array($byteBuffer);
    }

    public function pack() : string
    {
        $data  = '';
        $cPos  = $this->ref->tell();

        $data .= $this->interpolationType->pack();          // type
        $data .= $this->globalSequence->pack();             // seq
        $data .= pack(PACK_BOOL, 1);                        // used - unknown when unused

        $num = 0;
        $buf = '';
        $this->ref->seek($this->sequenceTimes->offset);
        for ($i = 0; $i < $this->sequenceTimes->size; $i++)
        {
            $x = new M2Array($this->ref);
            $subPos = $this->ref->tell();
            $this->ref->seek($x->offset);
            $num += $x->size;
            for ($j = 0; $j < $x->size; $j++)
                $buf .= $this->ref->readInt32()->pack();
            $this->ref->seek($subPos);
        }
        $data .= pack(PACK_I32, $num);                      // numTimes
        $data .= $buf;                                      // times[]

        $num = 0;
        $buf = '';
        $this->ref->seek($this->values->offset);
        for ($i = 0; $i < $this->values->size; $i++)
        {
            $x = new M2Array($this->ref);
            $subPos = $this->ref->tell();
            $this->ref->seek($x->offset);
            $num += $x->size;
            for ($j = 0; $j < $x->size; $j++)
                $buf .= (new $this->dataType($this->ref))->pack();
            $this->ref->seek($subPos);
        }
        $data .= pack(PACK_I32, $num);                      // numData
        $data .= $buf;                                      // data[]

        $this->ref->seek($cPos);

        return $data;
    }
}
