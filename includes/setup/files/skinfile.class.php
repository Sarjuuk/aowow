<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SkinFile extends BinaryFile
{
    private const /* string */ MAGIC = 'SKIN';

    public readonly M2Array $vertices;
    public readonly M2Array $indices;
    public readonly M2Array $bones;
    public readonly M2Array $submeshes;
    public readonly M2Array $batches;
    public readonly Uint32  $boneCountMax;

    public function __construct(string $file)
    {
        parent::__construct($file);

        if ($this->read(4) != self::MAGIC)
        {
            $this->error = 'file '.$file.' has incorrect magic bytes';
            $this->close();
            return;
        }

        $this->vertices     = new M2Array($this);
        $this->indices      = new M2Array($this);
        $this->bones        = new M2Array($this);
        $this->submeshes    = new M2Array($this);
        $this->batches      = new M2Array($this);
        $this->boneCountMax = $this->readUInt32();
    }

    public function getIndices() : array
    {
        $data = [];

        $this->seek($this->indices->offset);

        for ($i = 0; $i < $this->indices->size; $i++)
            $data[] = $this->readUInt16()->pack();

        return $data;
    }

    public function getMeshes() : array
    {
        $data = [];

        $this->seek($this->submeshes->offset);

        for ($i = 0; $i < $this->submeshes->size; $i++)
            $data[] = (new M2SkinSection($this))->pack();

        return $data;
    }

    public function getTexUnits() : array
    {
        $data = [];

        $this->seek($this->batches->offset);

        for ($i = 0; $i < $this->batches->size; $i++)
            $data[] = (new M2Batch($this))->pack();

        return $data;
    }
}

?>
