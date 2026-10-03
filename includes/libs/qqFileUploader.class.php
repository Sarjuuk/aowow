<?php

/****************************************
Example of how to use this uploader class...
You can uncomment the following lines (minus the require) to use these as your defaults.

// list of valid extensions, ex. array("jpeg", "xml", "bmp")
$allowedExtensions = array();
// max file size in bytes
$sizeLimit = 10 * 1024 * 1024;

require('valums-file-uploader/server/php.php');
$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);

// Call handleUpload() with the name of the folder, relative to PHP's getcwd()
$result = $uploader->handleUpload('uploads/');

// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);

/******************************************/



/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr
{
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save(string $path, int $sizeLimit = 10485760) : bool
    {
        $input    = fopen("php://input", "r");
        if (!$input)
            return false;
        try { return self::saveStream($input, $path, $this->getSize(), $sizeLimit); }
        finally { fclose($input); }
    }

    /** Bound actual bytes and exclusively own staging; failures remove only the file created by this request. */
    public static function saveStream($input, string $path, int $expectedSize, int $sizeLimit) : bool
    {
        if ($expectedSize <= 0 || $expectedSize > $sizeLimit)
            return false;
        $target = fopen($path, 'xb');
        if (!$target)
            return false;
        $complete = false;
        try
        {
            $size = stream_copy_to_stream($input, $target, $sizeLimit + 1);
            $complete = $size === $expectedSize && $size <= $sizeLimit && fflush($target);
            return $complete;
        }
        finally
        {
            fclose($target);
            if (!$complete && is_file($path)) unlink($path);
        }
    }

    function getName() : string
    {
        return is_string($_GET['qqfile'] ?? null) ? $_GET['qqfile'] : '';
    }

    function getSize(): int
    {
        return filter_var($_SERVER['CONTENT_LENGTH'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) ?: 0;
    }
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm
{
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save(string $path, int $sizeLimit = 10485760) : bool
    {
        $source = $_FILES['qqfile']['tmp_name'] ?? null;
        if (($_FILES['qqfile']['error'] ?? null) !== UPLOAD_ERR_OK || !is_string($source) || !is_uploaded_file($source))
            return false;
        $input = fopen($source, 'rb');
        if (!$input)
            return false;
        try { return qqUploadedFileXhr::saveStream($input, $path, $this->getSize(), $sizeLimit); }
        finally { fclose($input); }
    }

    function getName() : string
    {
        return is_string($_FILES['qqfile']['name'] ?? null) ? $_FILES['qqfile']['name'] : '';
    }

    function getSize() : int
    {
        return filter_var($_FILES['qqfile']['size'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) ?: 0;
    }
}

class qqFileUploader
{
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;
    private ?string $settingsError;

    public function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760)
    {
        $this->allowedExtensions = array_map("strtolower", $allowedExtensions);
        $this->sizeLimit = $sizeLimit;

        $this->settingsError = $this->checkServerSettings();

        // Multipart XHR also carries a query filename; the parsed uploaded file takes precedence.
        if (isset($_FILES['qqfile']) && is_array($_FILES['qqfile']))
            $this->file = new qqUploadedFileForm();
        else if (isset($_GET['qqfile']) && is_string($_GET['qqfile']) &&
            !str_starts_with(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'multipart/form-data'))
            $this->file = new qqUploadedFileXhr();
        else
            $this->file = null;
    }

    public function getName() : string
    {
        return $this->file?->getName() ?? '';
    }

    private function checkServerSettings() : ?string
    {
        $postSize   = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));

        if (($postSize !== 0 && $postSize <= $this->sizeLimit) || $uploadSize < $this->sizeLimit)
        {
            trigger_error('qqFileUploader - upload settings below supported limit', E_USER_WARNING);
            return 'Server error. Uploads are temporarily unavailable.';
        }
        return null;
    }

    private function toBytes(string $str) : int
    {
        return ini_parse_quantity($str);
    }

    /**
     * Returns array('success' => true, 'newFilename' => 'myDoc123.doc') or array('error' => 'error message')
     */
    function handleUpload(string $uploadDirectory, string $newName = '', bool $replaceOldFile = FALSE) : array
    {
        if ($this->settingsError)
            return ['error' => $this->settingsError];

        if (!is_writable($uploadDirectory))
            return ['error' => "Server error. Upload directory isn't writable."];

        if (!$this->file)
            return ['error' => 'No files were uploaded.'];

        if ($this->file instanceof qqUploadedFileForm && ($_FILES['qqfile']['error'] ?? null) !== UPLOAD_ERR_OK)
            return ['error' => in_array($_FILES['qqfile']['error'] ?? null, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) ? 'File is too large' : 'Could not receive uploaded file.'];

        $size = $this->file->getSize();

        if ($size <= 0)
            return ['error' => 'File is empty'];

        if ($size > $this->sizeLimit)
            return ['error' => 'File is too large'];

        $pathinfo = pathinfo($this->getName());
        $filename = $newName ?: $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'] ?? '';

        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions))
        {
            $these = implode(', ', $this->allowedExtensions);
            return ['error' => 'File has an invalid extension, it should be one of '. $these . '.'];
        }

        // don't overwrite previous files that were uploaded
        if (!$replaceOldFile)
            while (file_exists($uploadDirectory . $filename . '.' . $ext))
                $filename .= rand(10, 99);

        // save() always uses exclusive creation, including legacy callers requesting replacement.
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext, $this->sizeLimit))
            return ['success' => true, 'newFilename' => $filename . '.' . $ext];
        else
            return ['error' => 'Could not save uploaded file. The upload was cancelled, or server error encountered'];
    }
}
