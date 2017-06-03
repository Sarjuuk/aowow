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
  //   $img = imagecreatefromblp("filename.blp");
  //   imagejpeg($img);
  //   imagedestroy($img);
  function imagecreatefromblp($filename, $imgid = 0)
  {
    $f = fopen($filename, "rb") or die("Cannot open file " . $filename . "\n");

    $filesize = filesize($filename);
    if ($filesize < 16)
      die("File " . $filename . " is too small for a BLP file\n");

    $data = fread($f, $filesize);
    fclose($f);

    if (substr($data, 0, 4) != "BLP2")
      die("File " . $filename . " has incorrect/unsupported magic bytes\n");

    $header = unpack("Vformat/Ctype/CalphaBits/CalphaType/Cmips/Vwidth/Vheight", substr($data, 4, 16));
    $header["mipsOffs"] = unpack("V16", substr($data, 20, 64));
    $header["mipsSize"] = unpack("V16", substr($data, 84, 64));

    $debugstr = "\nheader = " . print_r($header, true) . "\n";

    if ($header["format"] != 1)
      die("File " . $filename . " has unsupported format" . $debugstr);

    //print_r($debugstr);

    $offs = $header["mipsOffs"][$imgid+1];
    $size = $header["mipsSize"][$imgid+1];
    while ($imgid > 0)
    {
      $header["width"] /= 2;
      $header["height"] /= 2;
      $imgid--;
    }
    if ($size == 0)
      die("File " . $filename . " contains zeroes in a mips table" . $debugstr);
    if ($offs + $size > $filesize)
      die("File " . $filename . " is corrupted/incomplete" . $debugstr);

    if ($header["type"] == 1)
      $img = icfb1($header["width"], $header["height"], substr($data, 148, 1024), substr($data, $offs, $size));
    elseif ($header["type"] == 2)
      $img = icfb2($header["width"], $header["height"], substr($data, $offs, $size), $header["alphaBits"], $header["alphaType"]);
    elseif ($header["type"] == 3)
      $img = icfb3($header["width"], $header["height"], substr($data, $offs, $size));
    else
      die("File " . $filename . " has unsupported type" . $debugstr);

    if ($img)
      return $img;
    else 
      die($debugstr);
  }

  function icfb1($width, $height, $palette, $data)
  {
    $img = imagecreatetruecolor($width, $height);
    imagesavealpha($img, true);
    imagealphablending($img, false);
    $t = unpack("V256", $palette);
    $i = unpack("C*", $data);
    for ($y = 0; $y < $height; $y++)
      for ($x = 0; $x < $width; $x++)
      {
        $c = $t[$i[$x+$y*$width+1]+1];
        $c = imagecolorallocatealpha($img, ($c>>16)&255, ($c>>8)&255, $c&255, (($c>>24)&255)>>1);
        imagesetpixel($img, $x, $y, $c);
        imagecolordeallocate($img, $c);
      }
    return $img;
  }

  function icfb2($width, $height, $data, $alphaBits, $alphaType)
  {
    if (!in_array($alphaBits*10+$alphaType, array(0, 10, 41, 81, 87, 88)))
    {
      echo "Unsupported compression type";
      return NULL;
    }
    $img = imagecreatetruecolor($width, $height);
    imagesavealpha($img, true);
    imagealphablending($img, false);
    $offset = 0;
    for ($offy = 0; $offy < $height; $offy += 4)
      for ($offx = 0; $offx < $width; $offx += 4)
      {
        $alpha = array();
        if ($alphaBits > 1)
        {
          if ($alphaType <= 1)
          {
            $a = unpack("V2", substr($data, $offset, 8)); $a1=$a[1]; $a2=$a[2];
            for ($i=0; $i<8; $i++, $a1 >>= 4) $alpha[$i] = (($a1&15)<<4)|($a1&15);
            for ($i=8; $i<16; $i++, $a2 >>= 4) $alpha[$i] = (($a2&15)<<4)|($a2&15);
          }
          else
          {
            $c = unpack("C2", substr($data, $offset, 2));
            $t = array(0=>$c[1], 1=>$c[2]);
            if ($t[0] <= $t[1])
            {
              $t[2] = (4*$t[0] + $t[1])/5;
              $t[3] = (3*$t[0] + 2*$t[1])/5;
              $t[4] = (2*$t[0] + 3*$t[1])/5;
              $t[5] = ($t[0] + 4*$t[1])/5;
              $t[6] = 0;
              $t[7] = 255;
            }
            else
            {
              $t[2] = (6*$t[0] + $t[1])/7;
              $t[3] = (5*$t[0] + 2*$t[1])/7;
              $t[4] = (4*$t[0] + 3*$t[1])/7;
              $t[5] = (3*$t[0] + 4*$t[1])/7;
              $t[6] = (2*$t[0] + 5*$t[1])/7;
              $t[7] = ($t[0] + 6*$t[1])/7;
            }
            $a = unpack("C6", substr($data, $offset+2, 6));
            $a1 = $a[1] | ($a[2]<<8) | ($a[3]<<16);
            $a2 = $a[4] | ($a[5]<<8) | ($a[6]<<16);
            for ($i=0; $i<8; $i++, $a1 >>= 3) $alpha[$i] = $t[$a1&7];
            for ($i=8; $i<16; $i++, $a2 >>= 3) $alpha[$i] = $t[$a2&7];
          }
          $offset += 8;
        }

        $c0 = unpack("v", substr($data, $offset, 2)); $c0=$c0[1];
        $t = array();
        $t[0] = array("r"=>(($c0>>8)&0xF8)|(($c0>>13)&7), "g"=>(($c0>>3)&0xFC)|(($c0>>9)&3), "b"=>(($c0<<3)&0xF8)|(($c0>>2)&7), "a"=>0);
        $c1 = unpack("v", substr($data, $offset+2, 2)); $c1=$c1[1];
        $t[1] = array("r"=>(($c1>>8)&0xF8)|(($c1>>13)&7), "g"=>(($c1>>3)&0xFC)|(($c1>>9)&3), "b"=>(($c1<<3)&0xF8)|(($c1>>2)&7), "a"=>0);
        if (($c0 <= $c1) && ($alphaBits <= 1))
        {
          $t[2] = array("r"=>($t[0]["r"]+$t[1]["r"])/2, "g"=>($t[0]["g"]+$t[1]["g"])/2, "b"=>($t[0]["b"]+$t[1]["b"])/2, "a"=>0);
          if ($alphaBits == 1)
            $t[3] = array("r"=>0, "g"=>0, "b"=>0, "a"=>255);
          else
            $t[3] = array("r"=>0, "g"=>0, "b"=>0, "a"=>0);
        }
        else
        {
          $t[2] = array("r"=>(2*$t[0]["r"]+$t[1]["r"])/3, "g"=>(2*$t[0]["g"]+$t[1]["g"])/3, "b"=>(2*$t[0]["b"]+$t[1]["b"])/3, "a"=>0);
          $t[3] = array("r"=>($t[0]["r"]+2*$t[1]["r"])/3, "g"=>($t[0]["g"]+2*$t[1]["g"])/3, "b"=>($t[0]["b"]+2*$t[1]["b"])/3, "a"=>0);
        }

        if ($alphaBits > 1)
        {
          $i = unpack("V", substr($data, $offset+4, 4)); $i=$i[1];
          for ($y=0; $y<4; $y++)
            for ($x=0; $x<4; $x++, $i >>= 2)
            {
              $color = imagecolorallocatealpha($img, $t[$i&3]["r"], $t[$i&3]["g"], $t[$i&3]["b"], (255-$alpha[$x+$y*4])/2);
              imagesetpixel($img, $offx+$x, $offy+$y, $color);
              imagecolordeallocate($img, $color);
            }
        }
        else
        {
          $c = array();
          for ($i=0; $i<4; $i++)
            $c[$i] = imagecolorallocatealpha($img, $t[$i]["r"], $t[$i]["g"], $t[$i]["b"], $t[$i]["a"]/2);
          $i = unpack("V", substr($data, $offset+4, 4)); $i=$i[1];
          for ($y=0; $y<4; $y++)
            for ($x=0; $x<4; $x++, $i >>= 2)
              imagesetpixel($img, $offx+$x, $offy+$y, $c[$i&3]);
          for ($i=0; $i<4; $i++)
            imagecolordeallocate($img, $c[$i]);
        }
            
        $offset += 8;
      }
    return $img;
  }

  function icfb3($width, $height, $data)
  {
    $img = imagecreatetruecolor($width, $height);
    $i = unpack("V*", $data);
    for ($y = 0; $y < $height; $y++)
      for ($x = 0; $x < $width; $x++)
      {
        $c = $i[$x+$y*$width+1];
        $c = imagecolorallocate($img, ($c>>16)&255, ($c>>8)&255, $c&255);
        imagesetpixel($img, $x, $y, $c);
        imagecolordeallocate($img, $c);
      }
    return $img;
  }
?>
