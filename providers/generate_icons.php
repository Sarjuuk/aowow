<pre>
<?php
/*
    generate_icons.php - code for extracting icons for AoWoW
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
  set_time_limit(0);
  require("config.php");

  if (!isset($config["mpq"]))
    die("Path to extracted MPQ files is not configured");
  if (!isset($config["icons"]))
    die("Path where to extract icons is not configured");

  $mpqdir = $config["mpq"];
  $outimgdir = $config["icons"];

  $dbcdir = $mpqdir . "DBFilesClient/";
  if (@stat($dbcdir) == NULL)
    $dbcdir = $mpqdir . "dbfilesclient/";

  $largedir  = $outimgdir . "large/";  // 56x56 JPG
  $mediumdir = $outimgdir . "medium/"; // 36x36 JPG
  $smalldir  = $outimgdir . "small/";  // 18x18 JPG
  $tinydir   = $outimgdir . "tiny/";   // 15x15 GIF

  @mkdir($outimgdir);
  @mkdir($largedir);
  @mkdir($mediumdir);
  @mkdir($smalldir);
  @mkdir($tinydir);

  require("dbc2array.php");
  require("imagecreatefromblp.php");

  function dbc2array_($filename, $format)
  {
    global $dbcdir;
    if (@stat($dbcdir . $filename) == NULL) $filename = strtolower($filename);
    return dbc2array($dbcdir . $filename, $format);
  }

  function status($message)
  {
    echo $message;
    @ob_flush();
    flush();
    @ob_end_flush();
  }

  function resave($outfilename, $img, $width, $height)
  {
    $imgnew = imagecreatetruecolor($width, $height);
    imagecopyresampled($imgnew, $img, 0,0, 0,0, $width,$height, imagesx($img),imagesy($img));
    if (substr($outfilename, -4) == ".jpg")
      imagejpeg($imgnew, $outfilename);
    else if (substr($outfilename, -4) == ".gif")
      imagegif($imgnew, $outfilename);
    else die("Unsupported file fromat: " . substr($outfilename, -4));
    imagedestroy($imgnew);
  }

  function process($dbcfile, $dbcfmt)
  {
    global $mpqdir, $largedir, $mediumdir, $smalldir, $tinydir;

    status("Reading icons list from $dbcfile...");
    $dbc = dbc2array_($dbcfile, $dbcfmt);
    $count = count($dbc);
    status($count . " icons found\n");

    $current = 0;
    $status = array();
    $lastfile = array();
    foreach ($dbc as $row)
    {
      $srcfilename = strtolower(str_replace("\\", "/", $row[1]));
      if (strpos($srcfilename, "/") === FALSE)
        $srcfilename = "interface/icons/" . $srcfilename;
      $src = $mpqdir . $srcfilename . ".blp";
      $stat_src = @stat($src);
      if ($row[1] == "")
        echo " ";
      else if ($stat_src == NULL || $stat_src['size'] == 0)
      {
        $msg = "Not found";
        $status[$msg] = isset($status[$msg]) ? $status[$msg]+1 : 1;
        $lastfile[$msg][] = $src;
        echo "-";
      }
      else
      {
        $dstfilename = strtolower(substr(strrchr($srcfilename,"/"),1));
        $stat_dst = @stat($largedir . $dstfilename . ".jpg");
        if ($stat_dst != NULL && $stat_dst['mtime'] >= $stat_src['mtime'])
        {
          $msg = "Already up-to-date";
          $status[$msg] = isset($status[$msg]) ? $status[$msg]+1 : 1;
          //$lastfile[$msg][] = $src;
          $lastfile[$msg][0] = "...";
          $lastfile[$msg][1] = $src;
          echo ".";
        }
        else
        {
          $img = imagecreatefromblp($src);

          resave($largedir . $dstfilename . ".jpg", $img, 56, 56);
          resave($mediumdir . $dstfilename . ".jpg", $img, 36, 36);
          resave($smalldir . $dstfilename . ".jpg", $img, 18, 18);
          resave($tinydir . $dstfilename . ".gif", $img, 15, 15);

          echo "+";
        }
      }

      $current++;
      if ($current % 60 == 0)
        status(" " . $current . "/" . $count . " (" . round(100*$current/$count) . "%)\n");
    }
    if ($current % 60 != 0)
      status(" " . $current . "/" . $count . " (100%)\n");

    echo "Done\n";
    if (count($status) > 0)
    {
      echo "Status:\n";
      foreach ($status as $s => $row)
      {
        echo "  " . $s . ": " . $row . "\n";
//        foreach ($lastfile[$s] as $file)
//          echo "    $file\n";
      }
    }
  }

  process("ItemDisplayInfo.dbc", "nxxxxsxxxxxxxxxxxxxxxxxxx");
  process("SpellIcon.dbc", "ns");
?>
</pre>
