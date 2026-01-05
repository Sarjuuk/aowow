<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuideEditResponse extends TemplateResponse
{
    use TrGuideEditor;

    protected ?string $articleUrl = 'edit';

    protected  string $template   = 'guide-edit';
    protected  string $pageName   = 'guide=edit';
    protected ?int    $activeTab  = parent::TAB_GUIDES;
    protected  array  $breadcrumb = [6];

    protected  array  $scripts    = array(
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
    protected  array  $expectedPOST = array(
        'save'        => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkEmptySet']                         ], // saved for more editing
        'submit'      => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkEmptySet']                         ], // submitted for review
        'title'       => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkTextLine']                         ],
        'name'        => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkTextLine']                         ],
        'description' => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkDescription']                      ],
        'changelog'   => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkTextBlob']                         ],
        'body'        => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkTextBlob']                         ],
        'locale'      => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkLocale']                           ],
        'category'    => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_value' =>  1, 'max_value' => 9]                  ],
        'specId'      => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_value' => -1, 'max_value' => 2,  'default' => -1]],
        'classId'     => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_value' =>  1, 'max_value' => 11, 'default' =>  0]]
    );
    protected  array  $expectedGET = array(
        'id'  => ['filter' => FILTER_VALIDATE_INT],
        'rev' => ['filter' => FILTER_VALIDATE_INT]
    );

    public function __construct(string $param)
    {
        parent::__construct($param);

        if (!User::canWriteGuide())
            $this->generateError();

        if (!is_int($this->_get['id']))                     // edit existing guide
            return;

        $this->typeId = $this->_get['id'];                  // just to display sensible not-found msg
        $status = DB::Aowow()->selectCell('SELECT `status` FROM ::guides WHERE %if', !User::isInGroup(U_GROUP_STAFF), '`userId` = %i AND', User::$id, '%end `id` = %i AND `status` <> %i', $this->typeId, GuideMgr::STATUS_ARCHIVED);
        if (!$status && $this->typeId)
            $this->generateNotFound(Lang::game('guide'), Lang::guide('notFound'));
        else if (!$this->typeId)
            return;

        // just so we don't have to access GuideMgr from template
        $this->isDraft    = $status == GuideMgr::STATUS_DRAFT;
        $this->editStatus = $status;
        $this->editRev    = DB::Aowow()->selectCell('SELECT `rev` FROM ::articles WHERE `type` = %i AND `typeId` = %i ORDER BY `rev` DESC', Type::GUIDE, $this->typeId);
    }

    protected function generate() : void
    {
        if ($this->_post['save'] || $this->_post['submit'])
        {
            if (!$this->saveGuide())
                $this->error = Lang::main('intError');
            else if ($this->_get['id'] === 0)
                $this->forward('?guide=edit&id='.$this->typeId);
        }

        $guide = new GuideList(array(['id', $this->typeId]));

        $this->h1 = Lang::guide('editTitle');
        array_unshift($this->title, $this->h1.Lang::main('colon').$guide->getField('title'), Lang::game('guides'));

        Lang::sort('guide', 'category');

        // init required template vars
        $this->editCategory    = $this->_post['category']    ?? $guide->getField('category');
        $this->editTitle       = $this->_post['title']       ?? $guide->getField('title');
        $this->editName        = $this->_post['name']        ?? $guide->getField('name');
        $this->editDescription = $this->_post['description'] ?? $guide->getField('description');
        $this->editText        = $this->_post['body']        ?? $guide->getArticle();
        $this->editClassId     = $this->_post['classId']     ?? $guide->getField('classId');
        $this->editSpecId      = $this->_post['specId']      ?? $guide->getField('specId');
        $this->editLocale      = $this->_post['locale']      ?? Locale::tryFrom($guide->getField('locale'));
        $this->editStatus      = $this->editStatus           ?: $guide->getField('status');
        $this->editStatusColor = GuideMgr::STATUS_COLORS[$this->editStatus];

        $this->extendGlobalData($guide->getJSGlobals());

        parent::generate();
    }

    private function saveGuide() : bool
    {
        // test requiered fields set
        if (!$this->assertPOST('title', 'name', 'body', 'locale', 'category'))
        {
            trigger_error('GuideEditResponse::saveGuide - received malformed request', E_USER_ERROR);
            return false;
        }

        // test required fields context
        if (!$this->_post['locale']->validate())
            return false;

        // sanitize: spec / class
        if ($this->_post['category'] == 1)              // Classes
        {
            if ($this->_post['classId'] && !ChrClass::tryFrom($this->_post['classId']))
                $this->_post['classId'] = 0;

            if ($this->_post['specId'] > -1 && !$this->_post['classId'])
                $this->_post['specId'] = -1;
        }
        else
        {
            $this->_post['classId'] = 0;
            $this->_post['specId']  = -1;
        }

        $guideData = array(
            'category'    => $this->_post['category'],
            'classId'     => $this->_post['classId'],
            'specId'      => $this->_post['specId'],
            'title'       => $this->_post['title'],
            'name'        => $this->_post['name'],
            'description' => $this->_post['description'] ?: GuideMgr::createDescription($this->_post['body']),
            'locale'      => $this->_post['locale']->value,
            'roles'       => User::$groups,
            'status'      => $this->_post['submit'] ? GuideMgr::STATUS_REVIEW : GuideMgr::STATUS_DRAFT,
            'date'        => time()
        );

        // new guide > reload editor
        if ($this->_get['id'] === 0)
        {
            $guideData += ['userId' => User::$id];
            if (!($this->typeId = (int)DB::Aowow()->qry('INSERT INTO ::guides %v', $guideData)))
            {
                trigger_error('GuideEditResponse::saveGuide - failed to save guide to db', E_USER_ERROR);
                return false;
            }
        }
        // existing guide > :shrug:
        else if (DB::Aowow()->qry('UPDATE ::guides SET %a WHERE `id` = %i', $guideData, $this->typeId))
            DB::Aowow()->qry('INSERT INTO ::guides_changelog (`id`, `rev`, `date`, `userId`, `msg`) VALUES (%i, %i, %i, %i, %s)', $this->typeId, $this->editRev, time(), User::$id, $this->_post['changelog']);
        else
        {
            trigger_error('GuideEditResponse::saveGuide - failed to update guide in db', E_USER_ERROR);
            return false;
        }

        // insert Article
        $articleId = DB::Aowow()->qry(
           'INSERT INTO ::articles (`type`, `typeId`, `locale`, `rev`, `editAccess`, `article`) VALUES (%i, %i, %i, %i, %i, %s)',
            Type::GUIDE,
            $this->typeId,
            $this->_post['locale']->value,
            ++$this->editRev,
            User::$groups & U_GROUP_STAFF ? User::$groups : User::$groups | U_GROUP_BLOGGER,
            $this->_post['body']
        );

        if (!is_int($articleId))
        {
            if ($this->_get['id'] === 0)
                DB::Aowow()->qry('DELETE FROM ::guides WHERE `id` = %i', $this->typeId);

            trigger_error('GuideEditResponse::saveGuide - failed to save article to db', E_USER_ERROR);
            return false;
        }

        if ($this->_post['submit'] && $this->editStatus != GuideMgr::STATUS_REVIEW)
            DB::Aowow()->qry('INSERT INTO ::guides_changelog (`id`, `date`, `userId`, `status`) VALUES (%i, %i, %i, %i)', $this->typeId, time(), User::$id, GuideMgr::STATUS_REVIEW);

        $this->editStatus = $guideData['status'];

        return true;
    }

    protected static function checkDescription(string $str) : string
    {
        // run checkTextBlob and also replace \n => \s and \s+ => \s
        $str = preg_replace(parent::PATTERN_TEXT_BLOB, '', $str);

        $str = strtr($str, ["\n" => ' ', "\r" => ' ']);

        return preg_replace('/\s+/', ' ', trim($str));
    }
}

?>
