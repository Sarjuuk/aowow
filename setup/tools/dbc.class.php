<?php

namespace Aowow;

/*
    DBC::read - PHP function for loading DBC file into array
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

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


class DBC
{
    private $isGameTable = false;
    private $localized   = false;
    private $tempTable   = true;
    private $tableName   = '';

    private $dataBuffer  = [];
    private $bufferSize  = 500;

    private static $structs = [];

    private $fileRefs    = [];
    private $curFile     = '';

    public $error  = true;
    public $fields = [];
    public $format = [];
    public $file   = '';

    private $macro = array(
        'LOC'   => 'sxsssxsxsxxxxxxxx',                     // pre 4.x locale block (in use)
        'X_LOC' => 'xxxxxxxxxxxxxxxxx'                      // pre 4.x locale block (unused)
    );

    private $unpackFmt = array(                             // Supported format characters:
        'x' => 'x/x/x/x',                                   // x - not used/unknown, 4 bytes
        'X' => 'x',                                         // X - not used/unknown, 1 byte
        's' => 'V',                                         // s - string block index, 4 bytes
        'S' => 'V',                                         // S - string block index, 4 bytes - localized; autofill
        'f' => 'f',                                         // f - float, 4 bytes (rounded to 4 digits after comma)
        'i' => 'l',                                         // i - signed int, 4 bytes
        'I' => 'l',                                         // I - signed int, 4 bytes, sql index
        'u' => 'V',                                         // u - unsigned int, 4 bytes
        'U' => 'V',                                         // U - unsigned int, 4 bytes, sql index
        'b' => 'C',                                         // b - unsigned char, 1 byte
        'd' => 'x4',                                        // d - ordered by this field, not included in array
        'n' => 'V'                                          // n - unsigned int, 4 bytes, sql primary key
    );

    public  const DEFAULT_WOW_BUILD = '12340';
    private const INI_FILE_PATH     = 'setup/tools/dbc/%s.ini';

    public function __construct($file, $opts = [], string $wowBuild = self::DEFAULT_WOW_BUILD)
    {
        self::loadStructs($wowBuild);

        $file = strtolower($file);
        if (empty(self::$structs[$file]))
        {
            CLI::write('no structure known for '.$file.'.dbc, build '.$wowBuild, CLI::LOG_ERROR);
            return;
        }

        foreach (self::$structs[$file] as $name => $type)
        {
            // resolove locale macro
            if (isset($this->macro[$type]))
            {
                $this->localized = true;
                for ($i = 0; $i < strlen($this->macro[$type]); $i++)
                    $this->format[$name.'_loc'.$i] = $this->macro[$type][$i];
            }
            else
            {
                $this->format[$name] = $type;
                if ($type == 'S')
                    $this->localized = true;
            }
        }

        $this->file = $file;

        if (is_bool($opts['temporary']))
            $this->tempTable = $opts['temporary'];

        if (!empty($opts['tableName']))
            $this->tableName = $opts['tableName'];
        else
            $this->tableName = 'dbc_'.$file;

        // gameTable-DBCs don't have an index and are accessed through value order
        // allas, you cannot do this with mysql, so we add a 'virtual' index
        $this->isGameTable = array_values($this->format) == ['f'] && substr($file, 0, 2) == 'gt';

        $foundMask = 0x0;
        foreach (Locale::cases() as $loc)
        {
            if (!in_array($loc, CLISetup::$locales))
                continue;

            if ($foundMask & (1 << $loc->value))
                continue;

            foreach ($loc->gameDirs() as $dir)
            {
                $fullPath = CLI::nicePath($this->file.'.dbc', CLISetup::$srcDir, $dir, 'DBFilesClient');
                if (!CLISetup::fileExists($fullPath))
                    continue;

                $this->curFile = $fullPath;
                if ($this->validateFile($loc))
                {
                    $foundMask |= (1 << $loc->value);
                    break;
                }
            }
        }

        if (!$this->fileRefs)
        {
            CLI::write('no suitable files found for '.$file.'.dbc, aborting.', CLI::LOG_ERROR);
            return;
        }

        // check if DBCs are identical
        $headers = array_column($this->fileRefs, 2);
        $x = array_unique(array_column($headers, 'recordCount'));
        if (count($x) != 1)
        {
            CLI::write('some DBCs have different record counts ('.implode(', ', $x).' respectively). cannot merge!', CLI::LOG_ERROR);
            return;
        }
        $x = array_unique(array_column($headers, 'fieldCount'));
        if (count($x) != 1)
        {
            CLI::write('some DBCs have differenct field counts ('.implode(', ', $x).' respectively). cannot merge!', CLI::LOG_ERROR);
            return;
        }
        $x = array_unique(array_column($headers, 'recordSize'));
        if (count($x) != 1)
        {
            CLI::write('some DBCs have differenct record sizes ('.implode(', ', $x).' respectively). cannot merge!', CLI::LOG_ERROR);
            return;
        }

        $this->error = false;
    }

    public function readFile()
    {
        if (!$this->file || $this->error)
            return [];

        $this->createTable();

        if ($this->localized)
            CLI::write(' - DBC: reading and merging '.$this->file.'.dbc for locales '.Lang::concat(array_keys($this->fileRefs), callback: fn($x) => CLI::bold(Locale::from($x)->name)));
        else
            CLI::write(' - DBC: reading '.$this->file.'.dbc');

        if (!$this->read())
        {
            CLI::write(' - DBC::read() returned with error', CLI::LOG_ERROR);
            return false;
        }

        return true;
    }

    public function getTableName() : string
    {
        return $this->tableName;
    }

    public static function getDefinitions() : array
    {
        if (empty(self::$structs))
            self::loadStructs();

        return array_keys(self::$structs);
    }

    private static function loadStructs(string $wowBuild = self::DEFAULT_WOW_BUILD) : void
    {
        $structFile = sprintf(self::INI_FILE_PATH, $wowBuild);

        if (!file_exists($structFile))
        {
            CLI::write('no structure file found for wow build '.$wowBuild, CLI::LOG_ERROR);
            return;
        }

        self::$structs = parse_ini_file($structFile, true);
    }

    private function endClean()
    {
        foreach ($this->fileRefs as &$ref)
            fclose($ref[0]);

        $this->dataBuffer = null;
    }

    private function readHeader(&$handle = null) : array
    {
        if (!is_resource($handle))
            $handle = fopen($this->curFile, 'rb');

        if (!$handle)
            return [];

        if (fread($handle, 4) != 'WDBC')
        {
            CLI::write('file '.$this->curFile.' has incorrect magic bytes', CLI::LOG_ERROR);
            fclose($handle);
            return [];
        }

        return unpack('VrecordCount/VfieldCount/VrecordSize/VstringSize', fread($handle, 16));
    }

    private function validateFile(Locale $loc) : bool
    {
        $filesize = filesize($this->curFile);
        if ($filesize < 20)
        {
            CLI::write('file '.$this->curFile.' is too small for a DBC file', CLI::LOG_ERROR);
            return false;
        }

        $header = $this->readHeader($handle);
        if (!$header)
        {
            CLI::write('cannot open file '.$this->curFile, CLI::LOG_ERROR);
            return false;
        }

        // Different debug checks to be sure, that file was opened correctly
        $debugStr = '(recordCount='.$header['recordCount'].
                    ' fieldCount=' .$header['fieldCount'] .
                    ' recordSize=' .$header['recordSize'] .
                    ' stringSize=' .$header['stringSize'] .')';

        if ($header['recordCount'] * $header['recordSize'] + $header['stringSize'] + 20 != $filesize)
        {
            CLI::write('file '.$this->curFile.' has incorrect size '.$filesize.': '.$debugStr, CLI::LOG_ERROR);
            fclose($handle);
            return false;
        }

        if ($header['fieldCount'] != count($this->format))
        {
            CLI::write('incorrect format ('.implode('', $this->format).') specified for file '.$this->curFile.' fieldCount='.$header['fieldCount'], CLI::LOG_ERROR);
            fclose($handle);
            return false;
        }

        $this->fileRefs[$loc->value] = [$handle, $this->curFile, $header];

        return true;
    }

    private function createTable()
    {
        if ($this->error)
            return;

        $pKey    = '';
        $query   = 'CREATE '.($this->tempTable ? 'TEMPORARY' : '').' TABLE `'.$this->tableName.'` (';
        $indizes = [];

        if ($this->isGameTable)
        {
            $query .= '`idx` INT SIGNED NOT NULL, ';
            $pKey   = 'idx';
        }

        foreach ($this->format as $name => $type)
        {
            switch ($type)
            {
                case 'f':
                    $query .= '`'.$name.'` FLOAT NOT NULL, ';
                    break;
                case 'S':
                    for ($l = 0; $l < strlen($this->macro['LOC']); $l++)
                        if ($this->macro['LOC'][$l] == 's')
                            $query .= '`'.$name.'_loc'.$l.'` TEXT NULL, ';

                    break;
                case 's':
                    $query .= '`'.$name.'` TEXT NULL, ';
                    break;
                case 'b':
                    $query .= '`'.$name.'` TINYINT UNSIGNED NOT NULL, ';
                    break;
                case 'I':
                    $indizes[] = $name;
                case 'i':
                case 'n':
                    $query .= '`'.$name.'` INT SIGNED NOT NULL, ';
                    break;
                case 'U':
                    $indizes[] = $name;
                case 'u':
                    $query .= '`'.$name.'` INT UNSIGNED NOT NULL, ';
                    break;
                default:                                    // 'x', 'X', 'd'
                    continue 2;
            }

            if ($type == 'n')
                $pKey = $name;
        }

        foreach ($indizes as $i)
            $query .= 'KEY `idx_'.$i.'` (`'.$i.'`), ';

        if ($pKey)
            $query .= 'PRIMARY KEY (`'.$pKey.'`) ';
        else
            $query = substr($query, 0, -2);

        $query .=  ') COLLATE=\'utf8mb4_unicode_ci\' ENGINE=InnoDB';

        DB::Aowow()->query('DROP TABLE IF EXISTS ?#', $this->tableName);
        DB::Aowow()->query($query);
    }

    private function writeToDB()
    {
        if (!$this->dataBuffer || $this->error)
            return;

        $cols = [];
        foreach ($this->format as $n => $type)
        {
            switch ($type)
            {
                case 'x':
                case 'X':
                case 'd':
                    continue 2;
                case 'S':
                    for ($l = 0; $l < strlen($this->macro['LOC']); $l++)
                        if ($this->macro['LOC'][$l] == 's')
                         $cols[] = $n.'_loc'.$l;
                    break;
                default:
                    $cols[] = $n;
            }
        }

        if ($this->isGameTable)
            array_unshift($cols, 'idx');

        DB::Aowow()->query('INSERT INTO ?# (?#) VALUES (?a)', $this->tableName, $cols, $this->dataBuffer);
        $this->dataBuffer = [];
    }

    private function read()
    {
        // Check that record size also matches
        $itr       = 0;
        $recSize   = 0;
        $unpackStr = '';
        foreach ($this->format as $ch)
        {
            if ($ch == 'X' || $ch == 'b')
                $recSize += 1;
            else
                $recSize += 4;

            if (!isset($this->unpackFmt[$ch]))
            {
                CLI::write('unknown format parameter \''.$ch.'\' in format string', CLI::LOG_ERROR);
                return false;
            }

            $unpackStr .= '/'.$this->unpackFmt[$ch];

            if ($ch != 'X' && $ch != 'x')
                $unpackStr .= 'f'.$itr;                     // output can't have numeric key as it gets interpreted as repeat factor here

            $itr++;
        }

        $unpackStr = substr($unpackStr, 1);

        // Optimizing unpack string: 'x/x/x/x/x/x' => 'x6'
        while (preg_match('/(x\/)+x/', $unpackStr, $r))
            $unpackStr = substr_replace($unpackStr, 'x'.((strlen($r[0]) + 1) / 2), strpos($unpackStr, $r[0]), strlen($r[0]));

        // we asserted all DBCs to be identical in structure. pick first header for checks
        $header = reset($this->fileRefs)[2];

        if ($recSize != $header['recordSize'])
        {
            CLI::write('format string size ('.$recSize.') for file '.$this->file.' does not match actual size ('.$header['recordSize'].')', CLI::LOG_ERROR);
            return false;
        }

        // And, finally, extract the records
        $strBlock = 4 + 16 + $header['recordSize'] * $header['recordCount'];

        for ($i = 0; $i < $header['recordCount']; $i++)
        {
            $row = [];
            $idx = $i;

            // add 'virtual' enumerator for gt*-dbcs
            if ($this->isGameTable)
                $row[-1] = $i;

            foreach ($this->fileRefs as $locId => [$handle, $fullPath, $header])
            {
                $rec = unpack($unpackStr, fread($handle, $header['recordSize']));

                $offset = 0;
                foreach (array_values($this->format) as $j => $type)
                {
                    if (!isset($rec['f'.$j]))
                        continue;

                    $outIdx = $j + $offset;

                    if (!empty($row[$outIdx]) && $type != 'S')
                        continue;

                    switch ($type)
                    {
                        case 'S':                           // localized String - autofill
                            $offset = substr_count($this->macro['LOC'], 's');

                            for ($k = 0; $k < strlen($this->macro['LOC']); $k++)
                            {
                                if ($this->macro['LOC'][$k] != 's')
                                    continue;

                                if (!isset($row[$j + $k]))  // prep locale fields
                                    $row[$j + $k] = null;
                            }

                            // provide outIdx for passthrough
                            $outIdx = $j + $locId;
                        case 's':
                            $curPos = ftell($handle);
                            fseek($handle, $strBlock + $rec['f'.$j]);

                            $str = $chr = '';
                            do
                            {
                                $str .= $chr;
                                $chr = fread($handle, 1);
                            }
                            while ($chr != "\000");

                            fseek($handle, $curPos);
                            $row[$outIdx] = $str;
                            break;
                        case 'f':
                            $row[$outIdx] = round($rec['f'.$j], 8);
                            break;
                        case 'n':                               // DO NOT BREAK!
                            $idx = $rec['f'.$j];
                        default:                                // nothing special .. 'i', 'u' and the likes
                            $row[$outIdx] = $rec['f'.$j];
                    }
                }

                if (!$this->localized)                          // one match is enough
                    break;
            }

            $this->dataBuffer[$idx] = array_values($row);

            if (count($this->dataBuffer) >= $this->bufferSize)
                $this->writeToDB();
        }

        $this->writeToDB();

        $this->endClean();

        return true;
    }
}

?>
