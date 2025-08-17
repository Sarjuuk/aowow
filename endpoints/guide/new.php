<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuideNewResponse extends TemplateResponse
{
    use TrGuideEditor;

    protected  bool   $requiresLogin = true;

    protected ?string $articleUrl    = 'new';

    protected  string $template      = 'guide-edit';
    protected  string $pageName      = 'guide=new';
    protected ?int    $activeTab     = parent::TAB_GUIDES;
    protected  array  $breadcrumb    = [6];

    protected  array  $scripts       = array(
        [SC_JS_FILE,    'js/article-description.js'],
        [SC_JS_FILE,    'js/article-editing.js'],
        [SC_JS_FILE,    'js/guide-editing.js'],
        [SC_JS_FILE,    'js/fileuploader.js'],
        [SC_JS_FILE,    'js/toolbar.js'],
        [SC_JS_FILE,    'js/AdjacentPreview.js'],
        [SC_CSS_FILE,   'css/article-editing.css'],
        [SC_CSS_FILE,   'css/fileuploader.css'],
        [SC_CSS_FILE,   'css/guide-edit.css'],
        [SC_CSS_FILE,   'css/AdjacentPreview.css'],
        [SC_CSS_STRING, <<<CSS

                #upload-result input[type=text] { padding: 0px 2px; font-size: 12px;        }
                #upload-result > span           { display: block; height: 22px;             }
                #upload-result                  { display: inline-block; text-align: right; }
                #upload-progress                { display: inline-block; margin-right: 8px; }

        CSS]
    );

    public function __construct(string $param)
    {
        parent::__construct($param);

        if (!User::canWriteGuide())
            $this->generateError();
    }

    protected function generate() : void
    {
        $this->h1 = Lang::guide('newTitle');

        array_unshift($this->title, $this->h1, Lang::game('guides'));

        Lang::sort('guide', 'category');

        // update required template vars
        $this->editLocale = Lang::getLocale();

        parent::generate();
    }
}

?>
