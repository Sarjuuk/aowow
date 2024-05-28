<?php
/*
    imagecreatefromblp - a PHP function which loads images from BLP files
    This file is a part of AoWoW project.
    Copyright (C) 2010  Mix <ru-mangos.ru>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
    // Usage example:
    //   $img = imagecreatefromblp("fileName.blp");
    //   imagejpeg($img);
    //   imagedestroy($img);

    if (!defined('AOWOW_REVISION'))
        die('illegal access');

    if (!CLI)
        die('not in cli mode');


    function imagecreatefromblp($fileName, $imgId = 0)
    {
        if (!CLISetup::fileExists($fileName))
        {
            CLI::write('file '.$fileName.' could not be found', CLI::LOG_ERROR);
            return;
        }

        $file = fopen($fileName, 'rb');

        if (!$file)
        {
            CLI::write('could not open file '.$fileName, CLI::LOG_ERROR);
            return;
        }

        $fileSize = fileSize($fileName);
        if ($fileSize < 16)
        {
            CLI::write('file '.$fileName.' is too small for a BLP file', CLI::LOG_ERROR);
            return;
        }

        $data = fread($file, $fileSize);
        fclose($file);

        // predict replacement patch files
        // ref: http://www.zezula.net/en/mpq/patchfiles.html
        if (substr($data, 0x0, 4) == "PTCH")
        {
            // strip patch header
            if (substr($data, 0x40, 4) == "COPY")
                $data = substr($data, 0x44);
            else
            {
                CLI::write('file '.$fileName.' is an incremental patch file and cannot be used by this script.', CLI::LOG_ERROR);
                return;
            }
        }

        if (substr($data, 0, 4) != "BLP2")
        {
            CLI::write('file '.$fileName.' has incorrect/unsupported magic bytes', CLI::LOG_ERROR);
            return;
        }

        $header = unpack("Vformat/Ctype/CalphaBits/CalphaType/Cmips/Vwidth/Vheight", substr($data, 4, 16));
        $header['mipsOffs'] = unpack("V16", substr($data, 20, 64));
        $header['mipsSize'] = unpack("V16", substr($data, 84, 64));

        $debugStr = ' header = '.print_r($header, true);

        if ($header['format'] != 1)
        {
            CLI::write('file '.$fileName.' has unsupported format'.$debugStr, CLI::LOG_ERROR);
            return;
        }

        $offs = $header['mipsOffs'][$imgId + 1];
        $size = $header['mipsSize'][$imgId + 1];

        while ($imgId > 0)
        {
            $header['width']  /= 2;
            $header['height'] /= 2;
            $imgId--;
        }

        if ($size == 0)
        {
            CLI::write('file '.$fileName.' contains zeroes in a mips table'.$debugStr, CLI::LOG_ERROR);
            return;
        }
        if ($offs + $size > $fileSize)
        {
            CLI::write('file '.$fileName.' is corrupted/incomplete'.$debugStr, CLI::LOG_ERROR);
            return;
        }

        if ($header['type'] == 1)
            $img = icfb1($header['width'], $header['height'], substr($data, 148, 1024), substr($data, $offs, $size));
        else if ($header['type'] == 2)
            $img = icfb2($header['width'], $header['height'], substr($data, $offs, $size), $header['alphaBits'], $header['alphaType']);
        else if ($header['type'] == 3)
            $img = icfb3($header['width'], $header['height'], substr($data, $offs, $size));
        else
        {
            CLI::write('file '.$fileName.' has unsupported type'.$debugStr, CLI::LOG_ERROR);
            return;
        }

        return $img;
    }

    // uncompressed
    function icfb1($width, $height, $palette, $data)
    {
        $img = imagecreatetruecolor($width, $height);
        imagesavealpha($img, true);
        imagealphablending($img, false);

        $t = unpack("V256", $palette);
        $i = unpack("C*", $data);

        for ($y = 0; $y < $height; $y++)
        {
            for ($x = 0; $x < $width; $x++)
            {
                $c = $t[$i[$x + $y * $width+ 1 ] + 1];
                $c = imagecolorallocatealpha($img, ($c >> 16) & 255, ($c >> 8) & 255, $c & 255, (($c >> 24) & 255) >> 1);
                imagesetpixel($img, $x, $y, $c);
                imagecolordeallocate($img, $c);
            }
        }

        return $img;
    }

    // DXTC
    function icfb2($width, $height, $data, $alphaBits, $alphaType)
    {
        if (!in_array($alphaBits * 10 + $alphaType, [0, 10, 41, 81, 87, 88]))
        {
            CLI::write('unsupported compression type', CLI::LOG_ERROR);
            return;
        }

        $img = imagecreatetruecolor($width, $height);
        imagesavealpha($img, true);
        imagealphablending($img, false);

        $offset = 0;
        for ($offy = 0; $offy < $height; $offy += 4)
        {
            for ($offx = 0; $offx < $width; $offx += 4)
            {
                $alpha = [];
                if ($alphaBits > 1)
                {
                    if ($alphaType <= 1)
                    {
                        $a  = unpack("V2", substr($data, $offset, 8));
                        $a1 = $a[1];
                        $a2 = $a[2];

                        for ($i = 0; $i < 8; $i++, $a1 >>= 4)
                            $alpha[$i] = (($a1 & 15) << 4) | ($a1 & 15);

                        for ($i = 8; $i < 16; $i++, $a2 >>= 4)
                            $alpha[$i] = (($a2 & 15) << 4) | ($a2 & 15);
                    }
                    else
                    {
                        $c = unpack("C2", substr($data, $offset, 2));
                        $t = [$c[1], $c[2]];

                        if ($t[0] <= $t[1])
                        {
                            $t[2] = (4 * $t[0] +     $t[1]) / 5;
                            $t[3] = (3 * $t[0] + 2 * $t[1]) / 5;
                            $t[4] = (2 * $t[0] + 3 * $t[1]) / 5;
                            $t[5] = (    $t[0] + 4 * $t[1]) / 5;
                            $t[6] = 0;
                            $t[7] = 255;
                        }
                        else
                        {
                            $t[2] = (6 * $t[0] +     $t[1]) / 7;
                            $t[3] = (5 * $t[0] + 2 * $t[1]) / 7;
                            $t[4] = (4 * $t[0] + 3 * $t[1]) / 7;
                            $t[5] = (3 * $t[0] + 4 * $t[1]) / 7;
                            $t[6] = (2 * $t[0] + 5 * $t[1]) / 7;
                            $t[7] = (    $t[0] + 6 * $t[1]) / 7;
                        }

                        $a  = unpack("C6", substr($data, $offset + 2, 6));
                        $a1 = $a[1] | ($a[2] << 8) | ($a[3] << 16);
                        $a2 = $a[4] | ($a[5] << 8) | ($a[6] << 16);

                        for ($i = 0; $i < 8; $i++, $a1 >>= 3)
                            $alpha[$i] = $t[$a1 & 7];

                        for ($i = 8; $i < 16; $i++, $a2 >>= 3)
                            $alpha[$i] = $t[$a2 & 7];
                    }

                    $offset += 8;
                }

                $c0 = unpack("v", substr($data, $offset, 2))[1];

                $t = [];
                $t[0] = array(
                    'r' => (($c0 >> 8) & 0xF8) | (($c0 >> 13) & 7),
                    'g' => (($c0 >> 3) & 0xFC) | (($c0 >>  9) & 3),
                    'b' => (($c0 << 3) & 0xF8) | (($c0 >>  2) & 7),
                    'a' => 0
                );

                $c1 = unpack("v", substr($data, $offset + 2, 2))[1];

                $t[1] = array(
                    'r' => (($c1 >> 8) & 0xF8) | (($c1 >> 13) & 7),
                    'g' => (($c1 >> 3) & 0xFC) | (($c1 >>  9) & 3),
                    'b' => (($c1 << 3) & 0xF8) | (($c1 >>  2) & 7),
                    'a' => 0
                );

                if (($c0 <= $c1) && ($alphaBits <= 1))
                {
                    $t[2] = array(
                        'r' => ($t[0]['r'] + $t[1]['r']) / 2,
                        'g' => ($t[0]['g'] + $t[1]['g']) / 2,
                        'b' => ($t[0]['b'] + $t[1]['b']) / 2,
                        'a' => 0
                    );

                    if ($alphaBits == 1)
                        $t[3] = ['r' => 0, 'g' => 0, 'b' => 0, 'a' => 255];
                    else
                        $t[3] = ['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0];
                }
                else
                {
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

                if ($alphaBits > 1)
                {
                    $i = unpack("V", substr($data, $offset + 4, 4))[1];

                    for ($y = 0; $y < 4; $y++)
                    {
                        for ($x = 0; $x < 4; $x++, $i >>= 2)
                        {
                            $color = imagecolorallocatealpha($img, $t[$i & 3]['r'], $t[$i & 3]['g'], $t[$i & 3]['b'], (255 - $alpha[$x + $y * 4]) / 2);
                            imagesetpixel($img, $offx + $x, $offy + $y, $color);
                            imagecolordeallocate($img, $color);
                        }
                    }
                }
                else
                {
                    $c = [];
                    for ($i = 0; $i < 4; $i++)
                        $c[$i] = imagecolorallocatealpha($img, $t[$i]['r'], $t[$i]['g'], $t[$i]['b'], $t[$i]['a'] / 2);

                    $i = unpack("V", substr($data, $offset + 4, 4))[1];
                    for ($y = 0; $y < 4; $y++)
                        for ($x = 0; $x < 4; $x++, $i >>= 2)
                            imagesetpixel($img, $offx + $x, $offy + $y, $c[$i & 3]);

                    for ($i = 0; $i < 4; $i++)
                        imagecolordeallocate($img, $c[$i]);
                }

                $offset += 8;
            }
        }

        return $img;
    }

    // plain
    function icfb3($width, $height, $data)
    {
        $img = imagecreatetruecolor($width, $height);
        $i = unpack("V*", $data);

        for ($y = 0; $y < $height; $y++)
        {
            for ($x = 0; $x < $width; $x++)
            {
                $c = $i[$x + $y * $width + 1];
                $c = imagecolorallocate($img, ($c >> 16) & 255, ($c >> 8) & 255, $c & 255);
                imagesetpixel($img, $x, $y, $c);
                imagecolordeallocate($img, $c);
            }
        }

        return $img;
    }
?>
