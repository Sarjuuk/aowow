<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class DBCFile extends BinaryFile
{
    private const /* string */ MAGIC       = 'WDBC';
    private const /* int    */ HEADER_SIZE = 16;

    private readonly int $stringSize;
    private readonly int $stringOffset;

    public readonly int $nCols;
    public readonly int $nRows;
    public readonly int $recordSize;

    public function __construct(string $file)
    {
        parent::__construct($file);

        if ($this->filesize < strlen(self::MAGIC) + self::HEADER_SIZE)
        {
            $this->error = 'file '.$file.' too small for a dbc';
            $this->close();
            return;
        }

        if ($this->read(4) != self::MAGIC)
        {
            $this->error = 'file '.$file.' has incorrect magic bytes';
            $this->close();
            return;
        }

        [, $this->nRows, $this->nCols, $this->recordSize, $this->stringSize] = unpack(UInt32::PACK_FMT.'4', $this->read(self::HEADER_SIZE));
        $this->stringOffset = strlen(self::MAGIC) + self::HEADER_SIZE + $this->recordSize * $this->nRows;

        if ($this->stringOffset + $this->stringSize != $this->filesize)
        {
            $this->error = 'file '.$file.' has unexpected size - expected: '.($this->stringOffset + $this->stringSize).' has: '.$this->filesize;
            $this->close();
            return;
        }
    }

    public function readRecord(string $colFmt = "V*") : array
    {
        return unpack($colFmt, $this->read($this->recordSize));
    }

    public function readString() : ?string
    {
        $x = $this->readUInt32();
        if (is_null($x))
            return null;

        return $this->getStringFromBlock($x->unpack());
    }

    public function getStringFromBlock(int $offset) : ?string
    {
        $curPos = $this->tell();

        $this->seek($this->stringOffset + $offset);

        // apparently it is more efficient to read more than one byte at once..?
        $str = '';
        while (($pos = strpos($str, "\0")) === false)
            $str .= $this->read(255);

        $str = substr($str, 0, $pos);

        $this->seek($curPos);

        return $pos ? $str : null;
    }
}

?>
