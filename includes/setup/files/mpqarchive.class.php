<?php

namespace Aowow;


/**
 * Describes the functions exported by StormLib.dll for static analysis.
 */
interface StormLib
{
    /**
     * Opens an MPQ archive.
     *
     * @param \FFI\CData $file Archive file name.
     * @param int $priority Archive priority.
     * @param int $flags Open flags.
     * @param \FFI\CData $handle Pointer receiving the archive handle.
     * @return int Non-zero on success.
     */
    public function SFileOpenArchive(\FFI\CData $file, int $priority, int $flags, \FFI\CData $handle) : int;

    /**
     * Closes an MPQ archive.
     *
     * @param \FFI\CData $handle Archive handle.
     * @return int Non-zero on success.
     */
    public function SFileCloseArchive(\FFI\CData $handle) : int;

    /**
     * Adds a patch archive to the opened MPQ archive.
     *
     * @param \FFI\CData $archive Primary archive handle.
     * @param \FFI\CData $patchFile Patch archive file name.
     * @param string $patchPrefix Prefix for patch file names.
     * @param int $flags Reserved flags.
     * @return int Non-zero on success.
     */
    public function SFileOpenPatchArchive(\FFI\CData $archive, \FFI\CData $patchFile, string $patchPrefix, int $flags) : int;

    /**
     * Opens a file from an MPQ archive for reading. The returned file handle must be closed with SFileCloseFile().
     *
     * @param \FFI\CData $archive Archive handle.
     * @param string $fileName Name of the file to open.
     * @param int $scope File search scope.
     * @param \FFI\CData $handle Pointer receiving the file handle.
     * @return int Non-zero on success.
     */
    public function SFileOpenFileEx(\FFI\CData $archive, string $fileName, int $scope, \FFI\CData $handle) : int;

    /**
     * Retrieves information about an open MPQ archive or file.
     *
     * @param \FFI\CData $handle Archive or file handle.
     * @param int $infoClass Information class to retrieve.
     * @param \FFI\CData $info Buffer receiving the information.
     * @param int $infoSize Size of the information buffer in bytes.
     * @param \FFI\CData $sizeNeeded Pointer receiving the required buffer size.
     * @return int Non-zero on success.
     */
    public function SFileGetFileInfo(\FFI\CData $handle, int $infoClass, \FFI\CData $info, int $infoSize, \FFI\CData $sizeNeeded) : int;

    /**
     * Retrieves the size of an open file.
     *
     * @param \FFI\CData $handle File handle.
     * @param \FFI\CData $sizeHigh Pointer receiving the high 32 bits of the file size.
     * @return int Low 32 bits of the file size.
     */
    public function SFileGetFileSize(\FFI\CData $handle, \FFI\CData $sizeHigh) : int;

    /**
     * Retrieves the size of an open file.
     *
     * @param \FFI\CData $handle File handle.
     * @param \FFI\CData $fileName Buffer receiving the filename.
     * @return int Low 32 bits of the file size.
     */
    public function SFileGetFileName(\FFI\CData $handle, \FFI\CData $fileName) : int;

    /**
     * Starts searching for files in an MPQ archive.
     *
     * @param \FFI\CData $archive Archive handle.
     * @param string $mask File mask.
     * @param \FFI\CData $findData Buffer receiving the first match.
     * @param \FFI\CData|null $listFile Optional list file name.
     * @return \FFI\CData Search handle, or a null handle when no match exists.
     */
    public function SFileFindFirstFile(\FFI\CData $archive, string $mask, \FFI\CData $findData, ?\FFI\CData $listFile) : \FFI\CData;

    /**
     * Advances an MPQ file search.
     *
     * @param \FFI\CData $findHandle Search handle.
     * @param \FFI\CData $findData Buffer receiving the next match.
     * @return int Non-zero when another match is available.
     */
    public function SFileFindNextFile(\FFI\CData $findHandle, \FFI\CData $findData) : int;

    /**
     * Closes an MPQ file search.
     *
     * @param \FFI\CData $findHandle Search handle.
     * @return int Non-zero on success.
     */
    public function SFileFindClose(\FFI\CData $findHandle) : int;

    /**
     * Reads data from an open file.
     *
     * @param \FFI\CData $handle File handle.
     * @param \FFI\CData $buffer Buffer receiving the file data.
     * @param int $size Number of bytes to read.
     * @param \FFI\CData $read Pointer receiving the number of bytes read.
     * @param \FFI\CData|null $overlapped Optional Windows overlapped I/O structure.
     * @return int Non-zero on success.
     */
    public function SFileReadFile(\FFI\CData $handle, \FFI\CData $buffer, int $size, \FFI\CData $read, ?\FFI\CData $overlapped) : int;

    /**
     * Closes an open MPQ file and releases its in-memory data. The file handle is invalid after this call.
     *
     * @param \FFI\CData $handle File handle.
     * @return int Non-zero on success.
     */
    public function SFileCloseFile(\FFI\CData $handle) : int;
}

final class MPQArchive
{
    private const CDEF = <<<C
        typedef unsigned char BYTE;
        typedef unsigned long DWORD;
        typedef unsigned long long ULONGLONG;
        typedef long LONG;
        typedef unsigned short WCHAR;
        typedef unsigned long LCID;
        typedef void * HANDLE;
        typedef void * LPOVERLAPPED;
        typedef unsigned char BOOL8;

        BOOL8 SFileOpenArchive(const WCHAR * szMpqName, DWORD dwPriority, DWORD dwFlags, HANDLE * phMpq);
        BOOL8 SFileCloseArchive(HANDLE hMpq);
        BOOL8 SFileOpenPatchArchive(HANDLE hMpq, const WCHAR * szPatchMpqName, const char * szPatchPathPrefix, DWORD dwFlags);
        BOOL8 SFileOpenFileEx(HANDLE hMpq, const char * szFileName, DWORD dwSearchScope, HANDLE * phFile);
        DWORD SFileGetFileSize(HANDLE hFile, DWORD * pdwFileSizeHigh);
        BOOL8 SFileGetFileName(HANDLE hFile, char * szFileName);
        typedef struct _SFILE_FIND_DATA
        {
            char cFileName[260];
            char * szPlainName;
            DWORD dwHashIndex;
            DWORD dwBlockIndex;
            DWORD dwFileSize;
            DWORD dwFileFlags;
            DWORD dwCompSize;
            DWORD dwFileTimeLo;
            DWORD dwFileTimeHi;
            LCID lcLocale;
        } SFILE_FIND_DATA;
        HANDLE SFileFindFirstFile(HANDLE hMpq, const char * szMask, SFILE_FIND_DATA * lpFindFileData, const WCHAR * szListFile);
        BOOL8 SFileFindNextFile(HANDLE hFind, SFILE_FIND_DATA * lpFindFileData);
        BOOL8 SFileFindClose(HANDLE hFind);
        BOOL8 SFileGetFileInfo(HANDLE hMpqOrFile, int InfoClass, void * pvFileInfo, DWORD cbFileInfo, DWORD * pcbLengthNeeded);
        BOOL8 SFileReadFile(HANDLE hFile, void * lpBuffer, DWORD dwToRead, DWORD * pdwRead, LPOVERLAPPED lpOverlapped);
        BOOL8 SFileCloseFile(HANDLE hFile);
    C;

    // SearchScope
    private const /* int */ SFILE_OPEN_FROM_MPQ   = 0x00000000;   // The file is open from the MPQ. This is the default value. hMpq must be valid if SFILE_OPEN_FROM_MPQ is specified.
    private const /* int */ SFILE_OPEN_LOCAL_FILE = 0xFFFFFFFF;   // Opens a local file instead. The file is open using CreateFileEx with GENERIC_READ access and FILE_SHARE_READ mode.

    private const /* int */ STREAM_FLAG_READ_ONLY   = 0x00000100; // This flag causes the file to be open read-only. This flag is automatically set for partial and encrypted MPQs, and also for all MPQs that are not open from BASE_PROVIDER_FILE.
    private const /* int */ STREAM_FLAG_WRITE_SHARE = 0x00000200; // This flag causes the writable MPQ being open for write share. Use with caution. If two applications write to an open MPQ simultaneously, the MPQ data get corrupted.
    private const /* int */ STREAM_FLAG_USE_BITMAP  = 0x00000400; // This flag tells the file stream handler to respect a file bitmap. File bitmap is used by MPQs whose file blocks are downloaded on demand by the game.

    /** index into SFileInfoClass */
    private const /* int */ INFO_FLAGS = 53;

    /** max length of filenames in archive */
    private const /* int */ MAX_PATH = 260;

    // SFileInfoFlags
    public const /* int */ FILE_IMPLODE       = 0x00000100; // File is compressed using PKWARE Data compression library
    public const /* int */ FILE_COMPRESS      = 0x00000200; // File is compressed using combination of compression methods
    public const /* int */ FILE_ENCRYPTED     = 0x00010000; // The file is encrypted
    public const /* int */ FILE_FIX_KEY       = 0x00020000; // The decryption key for the file is altered according to the position of the file in the archive
    public const /* int */ FILE_PATCH_FILE    = 0x00100000; // The file contains incremental patch for an existing file in base MPQ
    public const /* int */ FILE_SINGLE_UNIT   = 0x01000000; // Instead of being divided to 0x1000-bytes blocks, the file is stored as single unit
    public const /* int */ FILE_DELETE_MARKER = 0x02000000; // File is a deletion marker, indicating that the file no longer exists. This is used to allow patch archives to delete files present in lower-priority archives in the search chain. The file usually has length of 0 or 1 byte and its name is a hash
    public const /* int */ FILE_SECTOR_CRC    = 0x04000000; // File has checksums for each sector (explained in the File Data section). Ignored if file is not compressed or imploded.
    public const /* int */ FILE_EXISTS        = 0x80000000; // Set if file exists, reset when the file was deleted

    /** @var \FFI&StormLib $ffi */
    private \FFI $ffi;
    private \FFI\CData $archive;

    private function __construct(\FFI $ffi, \FFI\CData $archive)
    {
        $this->ffi = $ffi;
        $this->archive = $archive;
    }

    private static function toWideString(\FFI $ffi, string $value): \FFI\CData
    {
        $encoded = mb_convert_encoding($value . "\0", 'UTF-16LE', 'UTF-8');
        $wide = $ffi->new('WCHAR[' . (strlen($encoded) / 2) . ']');

        foreach (unpack('v*', $encoded) as $index => $character)
            $wide[$index - 1] = $character;

        return $wide;
    }

    public static function open(string $archivePath, string $libraryPath): self
    {
        if (!extension_loaded('ffi'))
            throw new \RuntimeException('StormLib requires the PHP FFI extension.');

        if (!($archivePath = realpath($archivePath)))
            throw new \RuntimeException('MPQ archive not found: ' . $archivePath);

        if (!($libraryPath = realpath($libraryPath)))
            throw new \RuntimeException('StormLib library not found: ' . $libraryPath);

        try
        {
            /** @var \FFI&StormLib $ffi */
            $ffi = \FFI::cdef(self::CDEF, $libraryPath);
        }
        catch (\FFI\Exception $exception)
        {
            throw new \RuntimeException('Unable to load StormLib library: ' . $libraryPath, 0, $exception);
        }

        $archive = $ffi->new('HANDLE');
        $wideArchivePath = self::toWideString($ffi, $archivePath);

        if (!$ffi->SFileOpenArchive($wideArchivePath, 0, self::STREAM_FLAG_READ_ONLY, \FFI::addr($archive)))
            throw new \RuntimeException('Unable to open MPQ archive: ' . $archivePath);

        return new self($ffi, $archive);
    }

    public function close() : void
    {
        if (!isset($this->archive))
            return;

        $this->ffi->SFileCloseArchive($this->archive);
        unset($this->archive);
    }

    public function __destruct()
    {
        $this->close();
    }

    public function applyPatch(string $patchPath, string $patchPrefix = '', int $flags = 0) : void
    {
        if (!($patchPath = realpath($patchPath)))
            throw new \RuntimeException('MPQ patch archive not found: ' . $patchPath);

        $widePatchPath = self::toWideString($this->ffi, $patchPath);
        if (!$this->ffi->SFileOpenPatchArchive($this->archive, $widePatchPath, $patchPrefix, $flags))
            throw new \RuntimeException('Unable to apply MPQ patch archive: ' . $patchPath);
    }

    public function hasFile(string $fileName, int $excludeFlags = self::FILE_DELETE_MARKER) : bool
    {
        $file = $this->ffi->new('HANDLE');

        if (!$this->ffi->SFileOpenFileEx($this->archive, $fileName, self::SFILE_OPEN_FROM_MPQ, \FFI::addr($file)))
            return false;

        try
        {
            $fileFlags  = $this->ffi->new('DWORD');
            $sizeNeeded = $this->ffi->new('DWORD');
            if (!$this->ffi->SFileGetFileInfo($file, self::INFO_FLAGS, \FFI::addr($fileFlags), 4, \FFI::addr($sizeNeeded)))
                return false;

            return ($fileFlags->cdata & self::FILE_EXISTS) !== 0 && ($fileFlags->cdata & $excludeFlags) === 0;
        }
        finally
        {
            $this->ffi->SFileCloseFile($file);
        }
    }

    public function readFile(string $fileName, ?string $outDir = null) : string
    {
        $file = $this->ffi->new('HANDLE');

        if (!$this->ffi->SFileOpenFileEx($this->archive, $fileName, self::SFILE_OPEN_FROM_MPQ, \FFI::addr($file)))
            throw new \RuntimeException('Unable to open file in MPQ: ' . $fileName);

        try
        {
            $sizeHigh = $this->ffi->new('DWORD');
            $sizeLow  = $this->ffi->SFileGetFileSize($file, \FFI::addr($sizeHigh));
            if ($sizeHigh->cdata !== 0)
                throw new \RuntimeException('MPQ file is larger than 4 GiB: ' . $fileName);
            if ($sizeLow === 0)
                return '';

            $buffer = $this->ffi->new('char[' . $sizeLow . ']');
            $read   = $this->ffi->new('DWORD');
            if ($sizeLow > 0 && !$this->ffi->SFileReadFile($file, $buffer, $sizeLow, \FFI::addr($read), null))
                throw new \RuntimeException('Unable to read file from MPQ: ' . $fileName);

            if ($outDir !== null)
            {
                $pName = $this->ffi->new('char[' . self::MAX_PATH . ']');
                $this->ffi->SFileGetFileName($file, $pName);

                $name = \FFI::string($pName);

                Util::writeFile(CLI::nicePath($name, $outDir), \FFI::string($buffer, $read->cdata));

                return '';
            }
            else
                return \FFI::string($buffer, $read->cdata);
        }
        finally
        {
            $this->ffi->SFileCloseFile($file);
        }
    }

    public function search(string $searchPattern, int $excludeFlags = self::FILE_DELETE_MARKER) : array
    {
        $result = [];

        $findData   = $this->ffi->new('SFILE_FIND_DATA');
        $findHandle = $this->ffi->SFileFindFirstFile($this->archive, $searchPattern, \FFI::addr($findData), null);

        if (!$findHandle)
            return [];

        try
        {
            do
            {
                if (($findData->dwFileFlags & $excludeFlags) === 0)
                    $result[] = \FFI::string($findData->cFileName);
            }
            while ($this->ffi->SFileFindNextFile($findHandle, \FFI::addr($findData)));
        }
        finally
        {
            $this->ffi->SFileFindClose($findHandle);
        }

        return $result;
    }
}
