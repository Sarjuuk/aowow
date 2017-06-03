<?php
/*
    dbc2array - PHP functions for loading DBC file into array
    This file is a part of AoWoW project.
    Copyright (C) 2009-2010  Mix <ru-mangos.ru>

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

  /// Convert DBC file content into a 2-dimentional array
  // $filename - name of the file
  // $format - format string, that contains 1 character for each field
  // Supported format characters:
  //   x   - not used/unknown, 4 bytes
  //   X   - not used/unknown, 1 byte
  //   s   - char*
  //   f   - float, 4 bytes (rounded to 2 digits after comma)
  //   i   - unsigned int, 4 bytes
  //   b   - unsigned char, 1 byte
  //   d   - sorted by this field, not included in array
  //   n   - same, but field included in array
  //
  // Usage example:
  //   $spellinfo = dbc2array("ItemSubClass.dbc","iiiiiiiiiissssssssssssssssissssssssssssssssi");
  //   print_r($spellinfo);
  function dbc2array($filename, $format)
  {
    $f = fopen($filename, "rb") or die("Cannot open file " . $filename . "\n");

    $filesize = filesize($filename);
    if ($filesize < 20)
      die("File " . $filename . " is too small for a DBC file\n");

    if (fread($f, 4) != "WDBC")
      die("File " . $filename . " has incorrect magic bytes\n");

    $header = unpack("VrecordCount/VfieldCount/VrecordSize/VstringSize", fread($f, 16));

    // Different debug checks to be sure, that file was opened correctly
    $debugstr = "\n(recordCount=" . $header["recordCount"] . " " .
                "fieldCount=" . $header["fieldCount"] . " " .
                "recordSize=" . $header["recordSize"] . " " .
                "stringSize=" . $header["stringSize"] . ")\n";

    if ($header["recordCount"] * $header["recordSize"] + $header["stringSize"] + 20 != $filesize)
      die("File " . $filename . " has incorrect size" . $debugstr);
    if ($header["fieldCount"] != strlen($format))
	 {
	   echo '<br> DBC Field :'.$header["fieldCount"].'<br> My field :'.strlen($format).'<br>';
      die("Incorrect format string specified for file " . $filename . $debugstr);
	  }

    $unpack_fmt = array('x'=>"x/x/x/x", 'X'=>"x", 's'=>"V", 'f'=>"f", 'i'=>"V", 'b'=>"C", 'd'=>"x4", 'n'=>"V");
    $unpackstr = "";

    // Check that record size also matches
    $recsize = 0;
    for ($i=0; $i<strlen($format); $i++)
    {
      $ch = $format[$i];
      if ($ch == 'X' || $ch == 'b') $recsize += 1; else $recsize += 4;
      if (!isset($unpack_fmt[$ch]))
        die("Unknown format parameter '" . $ch . "' in format string\n");
      $unpackstr = $unpackstr . "/" . $unpack_fmt[$ch];
      if ($ch != 'X' && $ch != 'x') $unpackstr = $unpackstr .'f'.$i;
    }
    $unpackstr = substr($unpackstr, 1);

    // Optimizing unpack string: "x/x/x/x/x/x" => "x6"
    while (ereg("(x/)+x", $unpackstr, $r))
      $unpackstr = substr_replace($unpackstr, 'x'.((strlen($r[0])+1)/2), strpos($unpackstr, $r[0]), strlen($r[0]));

    //echo "Unpack string for " . $filename . ": " . $unpackstr . "\n";

    // The last debug check (most of the code in this function is for debug checks)
    /* if ($recsize != $header["recordSize"])
	{
	  echo '<br> DBC Size :'.$recsize.'<br> My Size :'.$header["recordSize"].'<br>'; 
      die("Format string size (".$recsize.") for file " . $filename .
          " does not match actual size (".$header["recordSize"].")" . $debugstr);
	} */	  

    // Cache the data to make it faster
    $data = fread($f, $header["recordCount"] * $header["recordSize"]);
    $strings = fread($f, $header["stringSize"]);
    fclose($f);

    // And, finally, extract the records
    $result = array();
    $cache = array();
    $rcount = $header["recordCount"];
    $rsize = $header["recordSize"];
    $fcount = strlen($format);
    for ($i=0; $i<$rcount; $i++)
    {
      $result[$i] = array();
      $record = unpack($unpackstr, substr($data, $i*$rsize, $rsize));
      for ($j=0; $j<$fcount; $j++)
        if (isset($record['f'.$j]))
        {
          $value = $record['f'.$j];
          if ($format[$j] == 's')
          {
            if (isset($cache[$value]))
              $value = $cache[$value];
            else
            {
              $s = substr($strings, $value);
              $s = substr($s, 0, strpos($s, "\000"));
              $cache[$value] = $s;
              $value = $s;
            }
          }
          else if ($format[$j] == 'f')
            $value = round($value, 2);
          array_push($result[$i], $value);
        }
    }

    return $result;
  }
?>
