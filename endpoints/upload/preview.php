<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/** Private read-only images bypass template/shared caches and release the session before streaming. */
class UploadPreviewResponse extends TextResponse
{
    protected string $contentType = MIME_TYPE_TEXT;
    protected bool $requiresLogin = true;
    protected array $expectedGET = [
        'kind' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^(pending|screenshot|avatar)$/D']],
        'key' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[a-zA-Z0-9]{16}$/D']],
        'id' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 1]]
    ];

    public function __construct(string $rawParam = '')
    {
        // Denied images carry the same private cache policy as successful ones.
        header('Cache-Control: private, no-store, max-age=0');
        header('Pragma: no-cache');
        header('Vary: Cookie');
        header('X-Content-Type-Options: nosniff');
        header('Cross-Origin-Resource-Policy: same-origin');
        parent::__construct($rawParam);
        if (!in_array($_SERVER['REQUEST_METHOD'] ?? '', ['GET', 'HEAD'], true))
            $this->generate404();
    }

    protected function generate() : void
    {
        $path = PrivateUpload::resolve($this->_get['kind'] ?: '', $this->_get['key'] ?: null, $this->_get['id'] ?: null);
        if (!$path)
            $this->generate404();

        $file = fopen($path, 'rb');
        if (!$file)
            $this->generate404();

        // These files are produced by the JPEG writer; verify bytes before declaring a browser image type.
        $signature = fread($file, 3);
        if ($signature !== "\xff\xd8\xff")
        {
            fclose($file);
            $this->generate404();
        }
        rewind($file);
        $this->result = $file;
    }

    protected function display() : void
    {
        header(MIME_TYPE_JPEG);
        session_write_close();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'HEAD')
            fpassthru($this->result);
        fclose($this->result);
    }
}
