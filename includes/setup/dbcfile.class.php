<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class DBCFile
{
    private const /* string */ MAGIC       = 'WDBC';
    private const /* int    */ HEADER_SIZE = 16;

    private $handle = null;

    public readonly int $nCols;
    public readonly int $nRows;
    public readonly int $recordSize;
    public readonly int $stringSize;

    private readonly int $stringOffset;

    public string $error = '';

    public function __construct(string $file)
    {
        if (!file_exists($file))
        {
            $this->error = 'file '.$file.' not found';
            return;
        }

        $filesize = filesize($file);
        if ($filesize < strlen(self::MAGIC) + self::HEADER_SIZE)
        {
            $this->error = 'file '.$file.' too small for a dbc';
            return;
        }

        if (!$this->handle = fopen($file, 'rb'))
        {
            $this->error = 'failed to open file '.$file;
            return;
        }

        if ($this->read(4) != self::MAGIC)
        {
            $this->error = 'file '.$file.' has incorrect magic bytes';
            fclose($this->handle);
            return;
        }

        [, $this->nRows, $this->nCols, $this->recordSize, $this->stringSize] = unpack('V4', fread($this->handle, 16));
        $this->stringOffset = strlen(self::MAGIC) + self::HEADER_SIZE + $this->recordSize * $this->nRows;

        if ($this->stringOffset + $this->stringSize != $filesize)
        {
            $this->error = 'file '.$file.' has unexpected size - expected: '.($this->stringOffset + $this->stringSize).' has: '.$filesize;
            fclose($this->handle);
            return;
        }
    }

    public function readRecord(string $colFmt = "V*") : array
    {
        if ($this->error || !is_resource($this->handle))
            return [];

        return unpack($colFmt, fread($this->handle, $this->recordSize));
    }

    private function read(int $bytes) : ?string
    {
        if ($this->error || !is_resource($this->handle))
            return null;

        return fread($this->handle, $bytes);
    }

    public function readByte() : ?int
    {
        $x = $this->read(1);
        if (is_null($x))
            return null;
        return intVal(unpack('C', $x)[1]);
    }

    public function readInt() : ?int
    {
        $x = $this->read(4);
        if (is_null($x))
            return null;
        return intVal(unpack('l', $x)[1]);
    }

    public function readUInt() : ?int
    {
        $x = $this->read(4);
        if (is_null($x))
            return null;
        return intVal(unpack('V', $x)[1]);
    }

    public function readFloat() : ?float
    {
        $x = $this->read(4);
        if (is_null($x))
            return null;
        return floatVal(unpack('f', $x)[1]);
    }

    public function readString() : ?string
    {
        $x = $this->readUInt();
        if (is_null($x))
            return null;

        return $this->getStringFromOffset($x);
    }

    public function ffwd(int $bytes) : int
    {
        if (!is_resource($this->handle))
            return 0;

        if (ftell($this->handle) + $bytes < 0)
        {
            fseek($this->handle, 0);
            return 0;
        }

        if (ftell($this->handle) + $bytes > $this->stringSize + $this->stringOffset)
        {
            fseek($this->handle, $this->stringSize + $this->stringOffset);
            return $this->stringSize + $this->stringOffset;
        }

        fseek($this->handle, $bytes, SEEK_CUR);
        return ftell($this->handle);
    }

    public function getStringFromOffset(int $offset) : ?string
    {
        $curPos = ftell($this->handle);

        fseek($this->handle, $this->stringOffset + $offset);

        // apparently it is more efficient to read more than one byte at once..?
        $str = '';
        while (($pos = strpos($str, "\0")) === false)
            $str .= fread($this->handle, 255);

        $str = substr($str, 0, $pos);

        fseek($this->handle, $curPos);

        return $pos ? $str : null;
    }

    public function __destruct()
    {
        if (is_resource($this->handle))
            fclose($this->handle);
    }
}

?>
