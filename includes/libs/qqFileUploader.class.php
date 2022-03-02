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
    function save(string $path) : bool
    {
        $input    = fopen("php://input", "r");
        $temp     = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);

        if ($realSize != $this->getSize())
            return false;

        $target = fopen($path, "w");
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);

        return true;
    }

    function getName() : string
    {
        return $_GET['qqfile'];
    }

    function getSize(): int
    {
        if (isset($_SERVER["CONTENT_LENGTH"]))
            return (int)$_SERVER["CONTENT_LENGTH"];

        throw new Exception('Getting content length is not supported.');
        return 0;
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
    function save(string $path) : bool
    {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path))
            return false;

        return true;
    }

    function getName() : string
    {
        return $_FILES['qqfile']['name'];
    }

    function getSize() : int
    {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader
{
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;

    public function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760)
    {
        $this->allowedExtensions = array_map("strtolower", $allowedExtensions);
        $this->sizeLimit = $sizeLimit;

        $this->checkServerSettings();

        if (isset($_GET['qqfile']))
            $this->file = new qqUploadedFileXhr();
        else if (isset($_FILES['qqfile']))
            $this->file = new qqUploadedFileForm();
        else
            $this->file = null;
    }

    public function getName() : string
    {
        return $this->file?->getName() ?? '';
    }

    private function checkServerSettings() : void
    {
        $postSize   = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));

        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit)
        {
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");
        }
    }

    private function toBytes(string $str) : int
    {
        $val  = trim($str);
        $last = strtolower(substr($str, -1, 1));
        switch ($last)
        {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }

        return $val;
    }

    /**
     * Returns array('success' => true, 'newFilename' => 'myDoc123.doc') or array('error' => 'error message')
     */
    function handleUpload(string $uploadDirectory, string $newName = '', bool $replaceOldFile = FALSE) : array
    {
        if (!is_writable($uploadDirectory))
            return ['error' => "Server error. Upload directory isn't writable."];

        if (!$this->file)
            return ['error' => 'No files were uploaded.'];

        $size = $this->file->getSize();

        if ($size == 0)
            return ['error' => 'File is empty'];

        if ($size > $this->sizeLimit)
            return ['error' => 'File is too large'];

        $pathinfo = pathinfo($this->getName());
        $filename = $newName ?: $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = @$pathinfo['extension'];        // hide notices if extension is empty

        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions))
        {
            $these = implode(', ', $this->allowedExtensions);
            return ['error' => 'File has an invalid extension, it should be one of '. $these . '.'];
        }

        // don't overwrite previous files that were uploaded
        if (!$replaceOldFile)
            while (file_exists($uploadDirectory . $filename . '.' . $ext))
                $filename .= rand(10, 99);

        if ($this->file->save($uploadDirectory . $filename . '.' . $ext))
            return ['success' => true, 'newFilename' => $filename . '.' . $ext];
        else
            return ['error' => 'Could not save uploaded file. The upload was cancelled, or server error encountered'];
    }
}
