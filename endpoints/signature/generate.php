<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

// introductory blog post
// https://web.archive.org/web/20210419162936/https://www.wowhead.com/news/new-feature-preview-forum-signatures-175630
// looks like it was .. at best .. live for half a year

// only example seen. looks like archive.org had a hickup when parsing the markup js
// https://web.archive.org/web/20110924014309/http://www.wowhead.com/signature=generate&id='+b.unnamed+'.png

// no clue where generated images are stored.
// static/uploads/signatures/ indicates users can upload their own backgrounds
// unclear when updated. With every char sync?

// generating and also viewing
class SignatureGenerateResponse extends TextResponse
{
    protected string $contentType = MIME_TYPE_PNG;
    protected array  $expectedGET = array(
        'id' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkProfileId']]
    );

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        parent::generate();

        if (!$this->assertGET('id'))
            $this->generate404();

        // find file in storage

        // build image
    }

    public function generate404(?string $out = null) : never
    {
        // "Signature Unavailable"
        $out = /*data:image/png;base64,*/'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';
        parent::generate404(base64_decode($out));
    }

    protected static function checkProfileId(string $sigId) : ?int
    {
        if (preg_match('/^(\d+)\.png$/i', $sigId, $m))
            return $m[0];

        return null;
    }
}

?>
