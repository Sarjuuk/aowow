<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class BinaryFile
{
    private /*res*/ $handle = null;
    private string  $data   = '';
    private int     $pos    = 0;

    protected int $filesize     = 0;

    public string $error = '';

    public function __construct(string $file, private bool $inRAM = true)
    {
        if (!file_exists($file))
        {
            $this->error = 'file '.$file.' not found';
            return;
        }

        if (!$this->handle = fopen($file, 'rb'))
        {
            $this->error = 'failed to open file '.$file;
            return;
        }

        $this->filesize = filesize($file);

        if ($inRAM)
            $this->data = file_get_contents($file);
    }

    public function __destruct()
    {
        $this->close();
    }


    /**********************/
    /* direct file access */
    /**********************/

    public function read(int $bytes) : ?string
    {
        if ($this->error || !is_resource($this->handle) || $bytes < 0)
            return null;

        $start = $this->pos;
        $this->pos += $bytes;

        if ($this->inRAM)
            return substr($this->data, $start, $bytes);
        else
            return fread($this->handle, $bytes);
    }

    public function readOffset(int $bytes, int $offset, bool $jumpBack = true) : ?string
    {
        if ($this->error || !is_resource($this->handle))
            return null;

        if ($jumpBack)
            $curPos = $this->inRAM ? $this->pos : ftell($this->handle);

        $this->seek($offset);

        $str = $this->read($bytes);

        if ($jumpBack)
            $this->seek($curPos);

        return $str;
    }

    public function seek(int $pos) : int
    {
        if (!is_resource($this->handle))
            return 0;

        if ($pos < 0)
            $pos = 0;
        if ($pos > $this->filesize)
            $pos = $this->filesize;

        $this->pos = $pos;

        if (!$this->inRAM)
            fseek($this->handle, $pos, SEEK_SET);

        return $pos;
    }

    public function ffwd(int $bytes) : int
    {
        if (!is_resource($this->handle))
            return 0;

        $curPos = $this->inRAM ? $this->pos : ftell($this->handle);

        if ($curPos + $bytes < 0)
            $bytes -= $curPos;
        if ($curPos + $bytes > $this->filesize)
            $bytes -= $this->filesize;

        $this->pos += $bytes;

        if ($this->inRAM)
            return $this->pos;

        fseek($this->handle, $bytes, SEEK_CUR);
        return ftell($this->handle);
    }

    public function close() : void
    {
        if (is_resource($this->handle))
            fclose($this->handle);
    }

    public function tell() : int
    {
        if (!is_resource($this->handle))
            return 0;

        return $this->inRAM ? $this->pos : ftell($this->handle);
    }

    /******************/
    /* read Primitive */
    /******************/

    public function readInt8() : ?Int8
    {
        if (!is_resource($this->handle))
            return null;
        return new Int8($this);
    }

    public function readInt16() : ?Int16
    {
        if (!is_resource($this->handle))
            return null;
        return new Int16($this);
    }

    public function readInt32() : ?Int32
    {
        if (!is_resource($this->handle))
            return null;
        return new Int32($this);
    }

    public function readUInt8() : ?UInt8
    {
        if (!is_resource($this->handle))
            return null;
        return new UInt8($this);
    }

    public function readUInt16() : ?UInt16
    {
        if (!is_resource($this->handle))
            return null;
        return new UInt16($this);
    }

    public function readUInt32() : ?UInt32
    {
        if (!is_resource($this->handle))
            return null;
        return new UInt32($this);
    }

    public function readFloat() : ?Double
    {
        if (!is_resource($this->handle))
            return null;
        return new Double($this);
    }

    public function readChar() : ?Char
    {
        if (!is_resource($this->handle))
            return null;
        return new Char($this);
    }

    public function readBool() : ?Boolean
    {
        if (!is_resource($this->handle))
            return null;
        return new Boolean($this);
    }
}

?>
