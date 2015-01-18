<?php

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


class DBC
{
    private $_formats = array(
        'talent'                  => 'niiiiiiiixxxxixxixxixii',
        'talenttab'               => 'nsxssxxsxsxxxxxxxxiiiiis',
        'gtchancetomeleecrit'     => 'f',
        'gtchancetomeleecritbase' => 'f',
        'gtchancetospellcrit'     => 'f',
        'gtchancetospellcritbase' => 'f',
        'gtoctregenhp'            => 'f',
        'gtregenmpperspt'         => 'f',
        'gtregenhpperspt'         => 'f',
        'spellicon'               => 'ns',
        'itemdisplayinfo'         => 'nssxxsxxxxxxxxxxxxxxxxxxx',
        'holidays'                => 'nxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxixxxxxxxxxxiisxix',
        'chrclasses'              => 'nxixsxssxxsxsxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxsxixi',
        'worldmaparea'            => 'niisffffxix',         // 4.x - niisffffxixxxx
        'worldmapoverlay'         => 'niixxxxxsiiiixxxx',   // 4.x - niixxxsiiiixxxx
    );
    private $_fields = array(
        'talent'                  => 'Id,tabId,row,column,rank1,rank2,rank3,rank4,rank5,reqTalent,reqRank,talentSpell,petCategory1,petCategory2',
        'talenttab'               => 'Id,nameEN,nameFR,nameDE,nameES,nameRU,iconId,raceMask,classMask,creatureFamilyMask,tabNumber,textureFile',
        'gtchancetomeleecrit'     => 'chance',
        'gtchancetomeleecritbase' => 'chance',
        'gtchancetospellcrit'     => 'chance',
        'gtchancetospellcritbase' => 'chance',
        'gtoctregenhp'            => 'ratio',
        'gtregenmpperspt'         => 'ratio',
        'gtregenhpperspt'         => 'ratio',
        'spellicon'               => 'Id,iconPath',
        'itemdisplayinfo'         => 'Id,leftModelName,rightModelName,inventoryIcon1',
        'holidays'                => 'Id,looping,nameId,descriptionId,textureString,scheduleType',
        'chrclasses'              => 'Id,powerType,nameMaleEN,nameMaleFR,nameMaleDE,nameMaleES,nameMaleRU,nameINT,flags,addon',
        'worldmaparea'            => 'Id,mapId,areaId,nameINT,left,right,top,bottom,defaultDungeonMapId',
        'worldmapoverlay'         => 'Id,worldMapAreaId,areaTableId,textureString,w,h,x,y',
    );

    private $isGameTable = false;

    public $result = [];
    public $fields = [];
    public $format = '';
    public $file   = '';

    public function __construct($file)
    {
        $file = strtolower($file);
        if (empty($this->_fields[$file]) || empty($this->_formats[$file]))
        {
            FileGen::status('no structure known for '.$file.'.dbc, aborting.', MSG_LVL_ERROR);
            return;
        }

        $this->fields    = explode(',', $this->_fields[$file]);
        $this->format    = $this->_formats[$file];
        $this->file      = $file;

        // gameTable-DBCs don't have an index and are accessed through value order
        // allas, you cannot do this with mysql, so we add a 'virtual' index
        $this->isGameTable = $this->format == 'f' && substr($file, 0, 2) == 'gt';
    }

    public function writeToDB()
    {
        if (!$this->result)
            return false;

        $n     = 0;
        $pKey  = $this->fields[0];
        $query = 'CREATE TABLE `dbc_'.$this->file.'` (';

        if ($this->isGameTable)
        {
            $query .= '`idx` BIGINT(20) NOT NULL, ';
            $pKey   = 'idx';
        }

        foreach (str_split($this->format) as $idx => $f)
        {
            if ($f == 'f')
                $query .= '`'.$this->fields[$n].'` FLOAT NOT NULL, ';
            else if ($f == 's' || $f == 'b')
                $query .= '`'.$this->fields[$n].'` TEXT NOT NULL, ';
            else if ($f == 'i' || $f == 'n')
                $query .= '`'.$this->fields[$n].'` BIGINT(20) NOT NULL, ';

            if ($f == 'n')
                $pKey = $this->fields[$n];

            if ($f != 'x')
                $n++;
        }
        $query .= 'PRIMARY KEY (`'.$pKey.'`)) COLLATE=\'utf8_general_ci\' ENGINE=MyISAM';

        DB::Aowow()->query('DROP TABLE IF EXISTS ?#', 'dbc_'.$this->file);
        DB::Aowow()->query($query);

        // make inserts more manageable
        $offset = 0;
        $limit  = 1000;
        $fields = $this->fields;

        if ($this->isGameTable)
            array_unshift($fields, 'idx');

        while (($offset * $limit) < count($this->result))
            DB::Aowow()->query('INSERT INTO ?# (?#) VALUES (?a)', 'dbc_'.$this->file, $fields, array_slice($this->result, $offset++ * $limit, $limit));

        return true;
    }

    public function readFiltered(Closure $filterFunc = null, $localized = false, $safeIf = true)
    {
        $result = $this->readArbitrary($localized, $safeIf);

        if (is_object($filterFunc))
            foreach ($result as $key => &$val)
                if (!$filterFunc($val, $key))
                    unset($result[$key]);

        return $result;
    }

    public function readArbitrary($localized = false, $safeIf = true)
    {
        // try DB first
        if (!$this->result)
            $this->readFromDB();

        // try file second
        if (!$this->result)
            if ($this->readFromFile($localized) && $safeIf)
                $this->writeToDB();

        return $this->getIndexed();
    }

    public function readFromDB()
    {
        if (!DB::Aowow()->selectCell('SHOW TABLES LIKE ?', 'dbc_'.$this->file))
            return [];

        $key = strstr($this->format, 'n') ? $this->fields[strpos($this->format, 'n')] : '';

        $this->result = DB::Aowow()->select('SELECT '.($key ? 'tbl.`'.$key.'` AS ARRAY_KEY, ' : '').'tbl.* FROM ?# tbl', 'dbc_'.$this->file);

        return $this->result;
    }

    public function readFromFile($localized = false)
    {
        if (!$this->file)
            return [];

        $foundMask = 0x0;
        foreach (FileGen::$expectedPaths as $locStr => $locId)
        {
            if ($foundMask & (1 << $locId))
                continue;

            $fullpath = FileGen::$srcDir.($locStr ? $locStr.'/' : '').'DBFilesClient/'.$this->file.'.dbc';
            if (!FileGen::fileExists($fullpath))
                continue;

            FileGen::status(' - reading '.($localized ? 'and merging ' : '').'data from '.$fullpath);

            if (!$this->read($fullpath, $localized))
                FileGen::status(' - DBC::read() returned with error', MSG_LVL_ERROR);
            else
                $foundMask |= (1 << $locId);

            if (!$localized)                                // one match is enough
                break;
        }

        return $this->getIndexed();
    }

    /*
        Convert DBC file content into a 2-dimentional array
        $filename - name of the file
        $format   - format string, that contains 1 character for each field
        Supported format characters:
          x   - not used/unknown, 4 bytes
          X   - not used/unknown, 1 byte
          s   - char*
          f   - float, 4 bytes (rounded to 4 digits after comma)
          i   - unsigned int, 4 bytes
          b   - unsigned char, 1 byte
          d   - sorted by this field, not included in array
          n   - same, but field included in array
    */
    private function read($filename, $mergeStrings = false)
    {
        $file = fopen($filename, 'rb');

        if (!$file)
        {
            FileGen::status('cannot open file '.$filename, MSG_LVL_ERROR);
            return false;
        }

        $filesize = filesize($filename);
        if ($filesize < 20)
        {
            FileGen::status('file '.$filename.' is too small for a DBC file', MSG_LVL_ERROR);
            return false;
        }

        if (fread($file, 4) != 'WDBC')
        {
            FileGen::status('file '.$filename.' has incorrect magic bytes', MSG_LVL_ERROR);
            return false;
        }

        $header = unpack('VrecordCount/VfieldCount/VrecordSize/VstringSize', fread($file, 16));

        // Different debug checks to be sure, that file was opened correctly
        $debugStr = '(recordCount='.$header['recordCount'].
                    ' fieldCount=' .$header['fieldCount'] .
                    ' recordSize=' .$header['recordSize'] .
                    ' stringSize=' .$header['stringSize'] .')';

        if ($header['recordCount'] * $header['recordSize'] + $header['stringSize'] + 20 != $filesize)
        {
            FileGen::status('file '.$filename.' has incorrect size '.$filesize.': '.$debugstr, MSG_LVL_ERROR);
            return false;
        }

        if ($header['fieldCount'] != strlen($this->format))
        {
            FileGen::status('incorrect format string ('.$this->format.') specified for file '.$filename.' fieldCount='.$header['fieldCount'], MSG_LVL_ERROR);
            return false;
        }

        $unpackStr = '';
        $unpackFmt = array(
            'x' => 'x/x/x/x',
            'X' => 'x',
            's' => 'V',
            'f' => 'f',
            'i' => 'V',
            'b' => 'C',
            'd' => 'x4',
            'n' => 'V'
        );

        // Check that record size also matches
        $recSize = 0;
        for ($i = 0; $i < strlen($this->format); $i++)
        {
            $ch = $this->format[$i];
            if ($ch == 'X' || $ch == 'b')
                $recSize += 1;
            else
                $recSize += 4;

            if (!isset($unpackFmt[$ch]))
            {
                FileGen::status('unknown format parameter \''.$ch.'\' in format string', MSG_LVL_ERROR);
                return false;
            }

            $unpackStr .= '/'.$unpackFmt[$ch];

            if ($ch != 'X' && $ch != 'x')
                $unpackStr .= 'f'.$i;
        }

        $unpackStr = substr($unpackStr, 1);

        // Optimizing unpack string: 'x/x/x/x/x/x' => 'x6'
        while (preg_match('/(x\/)+x/', $unpackStr, $r))
            $unpackStr = substr_replace($unpackStr, 'x'.((strlen($r[0]) + 1) / 2), strpos($unpackStr, $r[0]), strlen($r[0]));

        // The last debug check (most of the code in this function is for debug checks)
        if ($recSize != $header['recordSize'])
        {
            FileGen::status('format string size ('.$recSize.') for file '.$filename.' does not match actual size ('.$header['recordSize'].') '.$debugstr, MSG_LVL_ERROR);
            return false;
        }

        // Cache the data to make it faster
        $data    = fread($file, $header['recordCount'] * $header['recordSize']);
        $strings = fread($file, $header['stringSize']);
        fclose($file);

        // And, finally, extract the records
        $cache  = [];
        $rSize  = $header['recordSize'];
        $rCount = $header['recordCount'];
        $fCount = strlen($this->format);

        for ($i = 0; $i < $rCount; $i++)
        {
            $row = [];
            $idx = $i;
            $record = unpack($unpackStr, substr($data, $i * $rSize, $rSize));

            // add 'virtual' enumerator for gt*-dbcs
            if ($this->isGameTable)
                $row[] = $i;

            for ($j = 0; $j < $fCount; $j++)
            {
                if (!isset($record['f'.$j]))
                    continue;

                $value = $record['f'.$j];
                if ($this->format[$j] == 's')
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
                else if ($this->format[$j] == 'f')
                    $value = round($value, 8);

                $row[] = $value;

                if ($this->format[$j] == 'n')
                    $idx = $value;
            }

            if (!$mergeStrings || empty($this->result[$idx]))
                $this->result[$idx] = $row;
            else
            {
                $n = 0;
                for ($j = 0; $j < $fCount; $j++)
                {
                    if ($this->format[$j] == 's')
                        if (!$this->result[$idx][$n] && $row[$n])
                            $this->result[$idx][$n] = $row[$n];

                    if ($this->format[$j] != 'x')
                        $n++;
                }
            }
        }

        return !empty($this->result);
    }

    private function getIndexed()
    {
        $result = $this->result;
        $fields = $this->fields;
        if ($this->isGameTable)
            array_unshift($fields, 'idx');

        foreach ($result as &$row)
            $row = array_combine($fields, $row);

        return $result;
    }
}

?>
