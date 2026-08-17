<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class BLP2File extends BinaryFile
{
    private const /* string */ MAGIC       = 'BLP2';
    private const /* int    */ HEADER_SIZE = 148 + 1024;

    // colorEncoding
    private const /* int    */ COLOR_JPEG    = 0;
    private const /* int    */ COLOR_PALETTE = 1;
    private const /* int    */ COLOR_DXT     = 2;
    private const /* int    */ COLOR_ARGB    = 3;           // wowdev.wiki/BLP says this is cata+, but no
    private const /* int    */ COLOR_ARGB2   = 4;           // same like 3, not expected in WotLK

    // pixelFormat
    private const /* int    */ PIXEL_DXT1        = 0;       // COLOR_DXT
    private const /* int    */ PIXEL_DXT3        = 1;       // COLOR_DXT
    private const /* int    */ PIXEL_ARGB8888    = 2;       // COLOR_PALETTE
    private const /* int    */ PIXEL_ARGB1555    = 3;
    private const /* int    */ PIXEL_ARGB4444    = 4;       // COLOR_PALETTE
    private const /* int    */ PIXEL_RGB565      = 5;
    private const /* int    */ PIXEL_A8          = 6;
    private const /* int    */ PIXEL_DXT5        = 7;       // COLOR_DXT
    private const /* int    */ PIXEL_UNSPECIFIED = 8;       // COLOR_PALETTE
    private const /* int    */ PIXEL_ARGB2565    = 9;
    private const /* int    */ PIXEL_BC5         = 11;      // DXGI_FORMAT_BC5_UNORM

    // mipFlags
    private const /* int    */ MIPS_NONE      = 0x0;
    private const /* int    */ MIPS_GENERATED = 0x1;
    private const /* int    */ MIPS_HANDMADE  = 0x2;        // not handled differently, a mipmap is a mipmap

    // header
    private readonly int   $colorEncoding;
    private readonly int   $alphaBitDepth;
    private readonly int   $pixelFormat;                    // aka alphaType
    private readonly int   $mipFlags;
    private readonly int   $width;
    private readonly int   $height;
    /** @var int[] $mipOffsets - length: 16 */
    private readonly array $mipOffsets;
    /** @var int[] $mipSizes - length: 16 */
    private readonly array $mipSizes;

    // extended header
    /** @var null|int[] $palette - length: 256 */
    private ?array  $palette   = null;
    private ?string $jpgHeader = null;

    public function __construct(string $file, bool $inRAM = true)
    {
        parent::__construct($file, $inRAM);

        // doesn't work with
        if ($this->filesize < strlen(self::MAGIC) + self::HEADER_SIZE)
        {
            $this->error = 'file '.$file.' is too small for a BLP file.';
            $this->close();
            return;
        }

        if ($this->read(4) != self::MAGIC)
        {
            $this->error = 'file '.$file.' has incorrect magic bytes.';
            $this->close();
            return;
        }

        if ($this->readUInt32()->unpack() !== 1)
        {
            $this->error = 'file '.$file.' has incorrect version.';
            $this->close();
            return;
        }

        [, $this->colorEncoding, $this->alphaBitDepth, $this->pixelFormat, $this->mipFlags] = unpack(UInt8::PACK_FMT.'4', $this->read(4));

        if ($this->colorEncoding > self::COLOR_ARGB)
        {
            $this->error = 'file '.$file.' has unhandled encoding: '.$this->colorEncoding;
            $this->close();
            return;
        }

        if (!in_array($this->alphaBitDepth, [0, 1, 4, 8]))
        {
            $this->error = 'file '.$file.' has unhandled alpha depth: '.$this->alphaBitDepth;
            $this->close();
            return;
        }

        [, $this->width, $this->height] = unpack(UInt32::PACK_FMT.'2', $this->read(2*4));

        $this->mipOffsets = array_values(unpack(Uint32::PACK_FMT.'16', $this->read(16*4)));
        $this->mipSizes   = array_values(unpack(Uint32::PACK_FMT.'16', $this->read(16*4)));

        if ($this->colorEncoding == self::COLOR_JPEG)
        {
            $len = $this->readUInt32()->unpack();
            $this->jpgHeader = substr($this->read(1020), 0, $len);
        }
        else if ($this->colorEncoding == self::COLOR_PALETTE)
            $this->palette = array_values(unpack(UInt32::PACK_FMT.'256', $this->read(1024)));
        else
            $this->ffwd(1024);
    }

    public function exportGD(int $mipLevel = 0) : ?\GdImage
    {
        if ($this->error || $mipLevel < 0 || $mipLevel > 15)
            return null;

        // BLP has no mipmaps
        if ($mipLevel && !$this->mipFlags)
            $mipLevel = 0;

        $offset = $this->mipOffsets[$mipLevel];
        $size   = $this->mipSizes[$mipLevel];
        $width  = $this->width  / (2 ** $mipLevel);
        $height = $this->height / (2 ** $mipLevel);

        if (!$size)
        {
            $this->error = 'requested mip level not set.';
            return null;
        }

        if ($offset + $size > $this->filesize)
        {
            $this->error = 'file is corrupted/incomplete';
            return null;
        }

        return match ($this->colorEncoding)
        {
            self::COLOR_JPEG    => $this->unpackJpegImg($offset, $size),
            self::COLOR_PALETTE => $this->unpackPaletteImg($offset, $size, $width, $height),
            self::COLOR_DXT     => $this->unpackDxtImg($offset, $size, $width, $height),
            self::COLOR_ARGB    => $this->unpackRawImg($offset, $size, $width, $height)
        };
    }

    private function unpackJpegImg(int $offset, int $size) : ?\GdImage
    {
        // ! TESTING !
        // just blindly combine header + data block and call it a jpeg?
        // from Kanma: "R and B are inverted in the JPEG file"

        $imgString = $this->jpgHeader . $this->readOffset($size, $offset, false);

        return imagecreatefromstring($imgString) ?: null;
    }

    private function unpackDxtImg(int $offset, int $size, int $width, int $height) : ?\GdImage
    {
        if (!in_array($this->alphaBitDepth * 10 + $this->pixelFormat, [0, 10, 41, 81, 87, 88]))
        {
            $this->error = 'unsupported compression type';
            return null;
        }

        if (!($img = imagecreatetruecolor($width, $height)))
            return null;

        imagesavealpha($img, true);
        imagealphablending($img, false);

        $data = $this->readOffset($size, $offset, false);
        $dataOffset = 0;

        for ($offy = 0; $offy < $height; $offy += 4)
        {
            for ($offx = 0; $offx < $width; $offx += 4)
            {
                $alpha = [];
                if ($this->alphaBitDepth > 1)
                {
                    if ($this->pixelFormat <= 1)
                    {
                        // DXT3: 4-bit alpha values are packed into 8 bytes
                        [, $a1, $a2] = unpack(Uint32::PACK_FMT.'2', substr($data, $dataOffset, 8));

                        for ($i = 0; $i < 8; $i++, $a1 >>= 4)
                            $alpha[$i] = (($a1 & 0xF) << 4) | ($a1 & 0xF);

                        for ($i = 8; $i < 16; $i++, $a2 >>= 4)
                            $alpha[$i] = (($a2 & 0xF) << 4) | ($a2 & 0xF);
                    }
                    else
                    {
                        // DXT5: 8-byte alpha block
                        [, $a0, $a1] = unpack(Uint8::PACK_FMT.'2', substr($data, $dataOffset, 2));

                        if ($a0 <= $a1)
                        {
                            $t = array(
                                $a0,
                                $a1,
                                (4 * $a0 + 1 * $a1) / 5,
                                (3 * $a0 + 2 * $a1) / 5,
                                (2 * $a0 + 3 * $a1) / 5,
                                (1 * $a0 + 4 * $a1) / 5,
                                0,
                                255
                            );
                        }
                        else
                        {
                            $t = array(
                                $a0,
                                $a1,
                                (6 * $a0 + 1 * $a1) / 7,
                                (5 * $a0 + 2 * $a1) / 7,
                                (4 * $a0 + 3 * $a1) / 7,
                                (3 * $a0 + 4 * $a1) / 7,
                                (2 * $a0 + 5 * $a1) / 7,
                                (1 * $a0 + 6 * $a1) / 7
                            );
                        }

                        $a  = unpack(Uint8::PACK_FMT.'6', substr($data, $dataOffset + 2, 6));
                        $a1 = $a[1] | ($a[2] << 8) | ($a[3] << 16);
                        $a2 = $a[4] | ($a[5] << 8) | ($a[6] << 16);

                        for ($i = 0; $i < 8; $i++, $a1 >>= 3)
                            $alpha[$i] = $t[$a1 & 7];

                        for ($i = 8; $i < 16; $i++, $a2 >>= 3)
                            $alpha[$i] = $t[$a2 & 7];
                    }

                    $dataOffset += 8;
                }

                $c0 = unpack(UInt16::PACK_FMT, substr($data, $dataOffset, 2))[1];

                $t = [];
                $t[0] = array(
                    'r' => (($c0 >> 8) & 0xF8) | (($c0 >> 13) & 7),
                    'g' => (($c0 >> 3) & 0xFC) | (($c0 >>  9) & 3),
                    'b' => (($c0 << 3) & 0xF8) | (($c0 >>  2) & 7),
                    'a' => 0
                );

                $c1 = unpack(UInt16::PACK_FMT, substr($data, $dataOffset + 2, 2))[1];

                $t[1] = array(
                    'r' => (($c1 >> 8) & 0xF8) | (($c1 >> 13) & 7),
                    'g' => (($c1 >> 3) & 0xFC) | (($c1 >>  9) & 3),
                    'b' => (($c1 << 3) & 0xF8) | (($c1 >>  2) & 7),
                    'a' => 0
                );

                if (($c0 <= $c1) && ($this->alphaBitDepth <= 1))
                {
                    // DXT1 / DXT1A
                    $t[2] = array(
                        'r' => ($t[0]['r'] + $t[1]['r']) / 2,
                        'g' => ($t[0]['g'] + $t[1]['g']) / 2,
                        'b' => ($t[0]['b'] + $t[1]['b']) / 2,
                        'a' => 0
                    );

                    if ($this->alphaBitDepth == 1)
                        $t[3] = ['r' => 0, 'g' => 0, 'b' => 0, 'a' => 255];
                    else
                        $t[3] = ['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0];
                }
                else
                {
                    // DXT3 / DXT5 interpolation colors
                    $t[2] = array(
                        'r' => (2 * $t[0]['r'] + $t[1]['r']) / 3,
                        'g' => (2 * $t[0]['g'] + $t[1]['g']) / 3,
                        'b' => (2 * $t[0]['b'] + $t[1]['b']) / 3,
                        'a' => 0
                    );
                    $t[3] = array(
                        'r' => ($t[0]['r'] + 2 * $t[1]['r']) / 3,
                        'g' => ($t[0]['g'] + 2 * $t[1]['g']) / 3,
                        'b' => ($t[0]['b'] + 2 * $t[1]['b']) / 3,
                        'a' => 0
                    );
                }

                if ($this->alphaBitDepth > 1)
                {
                    // per-pixel alpha means the color/alpha pair can still repeat across the 16 pixels of this
                    // block: cache allocations by (paletteIdx, alphaIdx) instead of re-allocating every pixel
                    $colors = [];
                    $indices = unpack(Uint32::PACK_FMT, substr($data, $dataOffset + 4, 4))[1];

                    for ($y = 0; $y < 4; $y++)
                    {
                        for ($x = 0; $x < 4; $x++, $indices >>= 2)
                        {
                            $pIdx = $indices & 3;
                            $aVal = $alpha[$x + $y * 4];

                            $colors[$pIdx][$aVal] ??= imagecolorallocatealpha($img, $t[$pIdx]['r'], $t[$pIdx]['g'], $t[$pIdx]['b'], (255 - $aVal) / 2);
                            imagesetpixel($img, $offx + $x, $offy + $y, $colors[$pIdx][$aVal]);
                        }
                    }
                }
                else
                {
                    $colors = [];
                    for ($i = 0; $i < 4; $i++)
                        $colors[$i] = imagecolorallocatealpha($img, $t[$i]['r'], $t[$i]['g'], $t[$i]['b'], $t[$i]['a'] / 2);

                    $indices = unpack(UInt32::PACK_FMT, substr($data, $dataOffset + 4, 4))[1];

                    for ($y = 0; $y < 4; $y++)
                        for ($x = 0; $x < 4; $x++, $indices >>= 2)
                            imagesetpixel($img, $offx + $x, $offy + $y, $colors[$indices & 3]);
                }

                $dataOffset += 8;
            }
        }

        return $img;
    }

    private function unpackPaletteImg(int $offset, int $size, int $width, int $height) : ?\GdImage
    {
        if (!($img = imagecreatetruecolor($width, $height)))
            return null;

        imagesavealpha($img, true);
        imagealphablending($img, false);

        // depending on >alphaBitDepth $size contains an amount of alpha data, while the pixel data MUST always be width x height uint8s
        $data  = array_values(unpack(UInt8::PACK_FMT.'*', $this->readOffset($width * $height, $offset, false)));
        $alpha = match($this->alphaBitDepth)
        {
            1 => self::unpack1BitAlpha(unpack(UInt8::PACK_FMT.'*', $this->read($size - ($width * $height)))),
            4 => self::unpack4BitAlpha(unpack(UInt8::PACK_FMT.'*', $this->read($size - ($width * $height)))),
            8 => array_map(fn($x) : int => $x / 2, array_values(unpack(UInt8::PACK_FMT.'*', $this->read($size - ($width * $height))))),
            default => []
        };

        // try to cache color/alpha combos
        $colors = [];

        $i = 0;
        for ($y = 0; $y < $height; $y++)
        {
            for ($x = 0; $x < $width; $x++)
            {
                $a = $alpha[$i] ?? 127;
                $c = $this->palette[$data[$i]];
                $colors[$c & 0xFFFFFF][$a] ??= imagecolorallocatealpha($img, ($c >> 16) & 0xFF, ($c >> 8) & 0xFF, $c & 0xFF, 127 - $a);
                imagesetpixel($img, $x, $y, $colors[$c & 0xFFFFFF][$a]);
                $i++;
            }
        }

        return $img;
    }

    private function unpackRawImg(int $offset, int $size, int $width, int $height) : ?\GdImage
    {
        if (!($img = imagecreatetruecolor($width, $height)))
            return null;

        imagesavealpha($img, true);
        imagealphablending($img, false);

        $data = array_values(unpack(Uint32::PACK_FMT.'*', $this->readOffset($size, $offset, false)));

        // terrain/water textures tend to reuse a small set of colors: cache allocations instead of
        // re-allocating (and immediately discarding) one per pixel
        $colors = [];
        for ($y = 0; $y < $height; $y++)
        {
            for ($x = 0; $x < $width; $x++)
            {
                $c = $data[$x + $y * $width] & 0xFFFFFF;
                $colors[$c] ??= imagecolorallocatealpha($img, ($c >> 16) & 0xFF, ($c >> 8) & 0xFF, $c & 0xFF, (($c >> 24) & 0xFF) / 2);
                imagesetpixel($img, $x, $y, $colors[$c]);
            }
        }

        return $img;
    }

    private static function unpack1BitAlpha(array $alphaBytes) : array
    {
        $res = [];
        foreach ($alphaBytes as $byte)
            for ($i = 0; $i < 8; $i++)
                $res[] = (1 << $i) & $byte ? 127 : 0;       // imagecolorallocatealpha expects max 127 for full transparency

        return $res;
    }

    private static function unpack4BitAlpha(array $alphaBytes) : array
    {
        $res = [];
        foreach ($alphaBytes as $byte)
            for ($i = 0; $i < 8; $i += 4)
                $res[] = intval(((($byte >> $i) & 0xF) | (($byte >> $i) & 0xF) << 4) / 2); // /2 because imagecolorallocatealpha expects max 127 for full transparency

        return $res;
    }
}

?>
