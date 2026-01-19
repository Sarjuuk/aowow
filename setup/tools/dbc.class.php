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
    private const /* string */ INI_FILE_PATH   = 'setup/tools/dbc/%s.ini';
    private const /* int    */ MAX_INSERT_ROWS = 500;

    public  const /* string */ DEFAULT_WOW_BUILD = '12340';

    private bool   $isGameTable = false;
    private bool   $isLocalized = false;
    private bool   $isTempTable = true;
    private string $tableName   = '';
    private array  $dataBuffer  = [];
    private array  $fileRefs    = [];
    private array  $format      = [];
    private string $recordFmt   = '';
    private array  $macro       = array(
        'LOC'   => 'sxsssxsxsxxxxxxxx',                     // pre 4.x locale block (in use)
        'X_LOC' => 'xxxxxxxxxxxxxxxxx'                      // pre 4.x locale block (unused)
    );
    private array  $unpackFmt   = array(                    // Supported format characters:
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

    private static array $structs = [];

    public bool $error = true;

    public function __construct(public string $file, array $opts = [], string $wowBuild = self::DEFAULT_WOW_BUILD)
    {
        self::loadStructs($wowBuild);

        $this->file = strtolower($this->file);
        if (empty(self::$structs[$this->file]))
        {
            CLI::write('no structure known for '.$this->file.'.dbc, build '.$wowBuild, CLI::LOG_ERROR);
            return;
        }

        foreach (self::$structs[$this->file] as $name => $type)
        {
            // resolove locale macro
            if (isset($this->macro[$type]))
            {
                $this->isLocalized = true;
                for ($i = 0; $i < strlen($this->macro[$type]); $i++)
                {
                    $this->format[$name.'_loc'.$i] = $this->macro[$type][$i];
                    $this->recordFmt .= '/'.$this->unpackFmt[$this->macro[$type][$i]].$name.'_loc'.$i;
                }
            }
            else if (!isset($this->unpackFmt[$type]))
            {
                CLI::write('unknown format parameter '.CLI::bold($type).' at for field '.CLI::bold($name).' in format string', CLI::LOG_ERROR);
                return;
            }
            else
            {
                $this->format[$name] = $type;
                $this->recordFmt .= '/'.$this->unpackFmt[$type];
                if ($type !== 'x' && $type !== 'X')
                    $this->recordFmt .= $name;

                if ($type === 'S')
                    $this->isLocalized = true;
            }
        }

        // Optimizing unpack string: 'x/x/x/x/x/x' => 'x6'
        $this->recordFmt = preg_replace_callback('/x(\/x)+/i', fn($m) => 'x'.((strlen($m[0]) + 1) / 2), substr($this->recordFmt, 1));

        if (is_bool($opts['temporary']))
            $this->isTempTable = $opts['temporary'];

        if (!empty($opts['tableName']))
            $this->tableName = $opts['tableName'];
        else
            $this->tableName = 'dbc_'.$this->file;

        // gameTable-DBCs don't have an index and are accessed through value order
        // allas, you cannot do this with mysql, so we add a 'virtual' index
        $this->isGameTable = array_values($this->format) == ['f'] && substr($this->file, 0, 2) == 'gt';

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

                $dbcFile = new DBCFile($fullPath);
                if ($dbcFile->error)
                {
                    CLI::write($dbcFile->error, CLI::LOG_ERROR);
                    unset($dbcFile);
                    continue;
                }

                if ($dbcFile->nCols != count($this->format))
                {
                    CLI::write('incorrect format specified for file '.$this->file.' - expected fields: '.count($this->format).' read fields: '.$dbcFile->nCols, CLI::LOG_ERROR);
                    unset($dbcFile);
                    continue;
                }

                $recSize = 0;
                foreach ($this->format as $ch)
                    $recSize += ($ch == 'X' || $ch == 'b') ? 1 : 4;

                if ($recSize != $dbcFile->recordSize)
                {
                    CLI::write('format string size ('.$recSize.') for file '.$this->file.' does not match actual size ('.$dbcFile->recordSize.')', CLI::LOG_ERROR);
                    unset($dbcFile);
                    continue;
                }

                $this->fileRefs[$loc->value] = $dbcFile;
                $foundMask |= (1 << $loc->value);
            }
        }

        if (!$this->fileRefs)
        {
            CLI::write('no suitable files found for '.$this->file.'.dbc, aborting.', CLI::LOG_ERROR);
            return;
        }

        // check if DBCs are identical

        $tests = ['nRows' => null, 'nCols' => null, 'recordSize' => null];
        foreach ($this->fileRefs as $fileRef)
        {
            foreach ($tests as $field => $val)
            {
                if ($val === null)
                    $tests[$field] = $fileRef->{$field};
                else if ($val != $fileRef->{$field})
                {
                    CLI::write('some DBCs have different '.$field.': '.CLI::bold($val).' <> '.CLI::bold($fileRef->{$field}).' respectively. cannot merge!', CLI::LOG_ERROR);
                    return;
                }
            }
        }

        $this->error = false;
    }

    public function readFile()
    {
        if (!$this->file || $this->error)
            return [];

        $this->createTable();

        if ($this->isLocalized)
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
        unset($this->fileRefs, $this->dataBuffer);
    }

    private function createTable()
    {
        if ($this->error)
            return;

        $pKey    = '';
        $query   = 'CREATE '.($this->isTempTable ? 'TEMPORARY' : '').' TABLE `'.$this->tableName.'` (';
        $indizes = [];

        if ($this->isGameTable)
        {
            $query .= '`idx` INT SIGNED NOT NULL, ';
            $pKey   = 'idx';
        }

        foreach ($this->format as $name => $type)
        {
            $query .= match($type)
            {
                'f'           => '`'.$name.'` FLOAT NOT NULL, ',
                's'           => '`'.$name.'` TEXT NULL, ',
                'b'           => '`'.$name.'` TINYINT UNSIGNED NOT NULL, ',
                'i', 'I', 'n' => '`'.$name.'` INT SIGNED NOT NULL, ',
                'u', 'U'      => '`'.$name.'` INT SIGNED NOT NULL, ',
                'S'           => (function ($n) {
                                     $buf = '';
                                     for ($l = 0; $l < strlen($this->macro['LOC']); $l++)
                                         if ($this->macro['LOC'][$l] == 's')
                                             $buf .= '`'.$n.'_loc'.$l.'` TEXT NULL, ';
                                     return $buf;
                                 })($name),
                default       => ''                         // 'x', 'X', 'd'
            };

            if ($this->isGameTable)
                continue;

            if ($type == 'I' || $type == 'U')
                $indizes[] = $name;
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

        DB::Aowow()->qry('DROP TABLE IF EXISTS %n', $this->tableName);
        DB::Aowow()->qry($query);
    }

    private function writeToDB()
    {
        if (!$this->dataBuffer || $this->error)
            return;

        DB::Aowow()->qry('INSERT INTO %n %m', $this->tableName, $this->dataBuffer);

        $this->dataBuffer = [];
    }

    private function read()
    {
        $nRows = 1;                                         // set to actual value once we have a file handle
        for ($i = 0; $i < $nRows; $i++)
        {
            // add 'virtual' enumerator for gt*-dbcs
            if ($this->isGameTable)
                $this->dataBuffer['idx'][$i] = $i;

            foreach ($this->fileRefs as $locId => $dbcFile)
            {
                $nRows = $dbcFile->nRows;

                // note that the file pointer is already on the first record as the DBCFile reads its own header
                $row = $dbcFile->readRecord($this->recordFmt);

                foreach ($row as $name => $value)
                {
                    $type = $this->format[$name];

                    // handle locale fields for post 3.3.5a DBCs
                    if ($type === 'S')
                    {
                        for ($k = 0; $k < strlen($this->macro['LOC']); $k++)
                            if ($this->macro['LOC'][$k] === 's')
                                $this->dataBuffer[$name.'_loc'.$k][$i] ??= null;

                        $this->dataBuffer[$name.'_loc'.$locId][$i] ??= $dbcFile->getStringFromOffset($value);
                    }
                    if (empty($this->dataBuffer[$name][$i]))
                    {
                        if ($type == 's')
                            $this->dataBuffer[$name][$i] ??= $dbcFile->getStringFromOffset($value);
                        else
                            $this->dataBuffer[$name][$i] = $value;
                    }
                }

                if (!$this->isLocalized)                    // one match is enough
                    break;
            }

            if (count(current($this->dataBuffer)) >= self::MAX_INSERT_ROWS)
                $this->writeToDB();
        }

        $this->writeToDB();

        $this->endClean();

        return true;
    }
}

?>
