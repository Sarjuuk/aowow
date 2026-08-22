<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class BinaryFile
{
    private /*res*/ $handle     = null;
    private int     $pos        = 0;
    private int     $ptchOffset = 0;                        // in case of ptch - copy, the actual file starts at 0x44

    protected int $filesize = 0;

    public string $error = '';

    public function __construct(private string $data, private bool $inRAM = true)
    {
        $this->filesize = strlen($data);

        // predict replacement patch files
        // ref: http://www.zezula.net/en/mpq/patchfiles.html
        if ($this->read(4) == "PTCH")
        {
            $this->ffwd(60);                                // skip through TPatchHeader
            if ($this->read(4) != "COPY")
            {
                $this->error = 'file is an incremental patch file and cannot be used.';
                $this->close();
                return;
            }

            $this->filesize -= 68;

            if ($this->inRAM)
                $this->data = substr($this->data, 68);
            else
                $this->ptchOffset = 68;
        }

        $this->seek(0);                                     // reset position
    }

    public function __destruct()
    {
        $this->close();
    }

    public static function open(string $file) : ?static
    {
        if (!file_exists($file))
            return null;

        if (!$data = file_get_contents($file))
            return null;

        // ugh .. cant set $handle?

        return new self($data);
    }


    /**********************/
    /* direct file access */
    /**********************/

    public function read(int $bytes) : ?string
    {
        if (!$this->canRead() || $bytes < 0)
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
        if (!$this->canRead())
            return null;

        $curPos = $jumpBack ? $this->pos : null;            // no need to ftell, >pos is always updated

        $this->seek($offset);

        $str = $this->read($bytes);

        if ($curPos !== null)
            $this->seek($curPos);

        return $str;
    }

    public function seek(int $pos) : int
    {
        if (!$this->canRead())
            return 0;

        if ($pos < 0)
            $pos = 0;
        if ($pos > $this->filesize)
            $pos = $this->filesize;

        $this->pos = $pos;

        if (!$this->inRAM)
            fseek($this->handle, $pos + $this->ptchOffset, SEEK_SET);

        return $pos;
    }

    public function ffwd(int $bytes) : int
    {
        if (!$this->canRead())
            return 0;

        $curPos = $this->pos;                               // no need to ftell, >pos is always updated

        if ($curPos + $bytes < 0)
            $bytes -= $curPos;
        if ($curPos + $bytes > $this->filesize)
            $bytes -= $this->filesize;

        $this->pos += $bytes;

        if (!$this->inRAM)
            fseek($this->handle, $bytes, SEEK_CUR);

        return $this->pos;;
    }

    public function close() : void
    {
        if (is_resource($this->handle))
            fclose($this->handle);
    }

    public function tell() : int
    {
        if (!$this->canRead())
            return 0;

        // >pos is always updated, no need to ftell()
        return $this->pos;
    }


    /******************/
    /* read Primitive */
    /******************/

    public function readInt8() : ?Int8
    {
        if (!$this->canRead())
            return null;
        return new Int8($this);
    }

    public function readInt16() : ?Int16
    {
        if (!$this->canRead())
            return null;
        return new Int16($this);
    }

    public function readInt32() : ?Int32
    {
        if (!$this->canRead())
            return null;
        return new Int32($this);
    }

    public function readUInt8() : ?UInt8
    {
        if (!$this->canRead())
            return null;
        return new UInt8($this);
    }

    public function readUInt16() : ?UInt16
    {
        if (!$this->canRead())
            return null;
        return new UInt16($this);
    }

    public function readUInt32() : ?UInt32
    {
        if (!$this->canRead())
            return null;
        return new UInt32($this);
    }

    public function readFloat() : ?Double
    {
        if (!$this->canRead())
            return null;
        return new Double($this);
    }

    public function readChar() : ?Char
    {
        if (!$this->canRead())
            return null;
        return new Char($this);
    }

    public function readBool() : ?Boolean
    {
        if (!$this->canRead())
            return null;
        return new Boolean($this);
    }

    /******************/
    /* misc internals */
    /******************/

    private function canRead() : bool
    {
        if ($this->error)
            return false;
        if ($this->inRAM && !$this->data)
            return false;
        if (!$this->inRAM && !is_resource($this->handle))
            return false;
        return true;
    }
}

?>
