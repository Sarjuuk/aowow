<?php

// Render the real guide editor without a database or application bootstrap.
// --browser emits standalone DOM and preview checks for a browser.
namespace Aowow {
    class Cfg {
        public static function get(string $key) : string|int { return in_array($key, ['STATIC_URL', 'HOST_URL'], true) ? '' : 0; }
        public static function applyToString(string $text) : string { return $text; }
    }
    class User {
        public static bool $staff = false;
        public static int $groups = 0;
        public static function isInGroup(int $group) : bool { return self::$staff; }
        public static function getUserGlobal() : array { return []; }
        public static function getFavorites() : array { return []; }
        public static function canWriteGuide() : bool { return true; }
    }
    class DB {
        public static array $logs = [];
        public static function Aowow() : self { return new self; }
        public function selectRow(string $query, mixed ...$args) : array { return []; }
        public function selectAssoc(string $query, mixed ...$args) : array { return self::$logs; }
    }
    class GuideList {
        public static array $fields = [];
        public int $id = 123;
        public bool $error = false;
        public function __construct(array $conditions) {}
        public function getField(string $field) : mixed { return self::$fields[$field]; }
        public function canBeViewed() : bool { return true; }
        public function userCanView() : bool { return false; }
        public function canBeReported() : bool { return true; }
    }
}

namespace {
    use Aowow\GuideEditResponse;
    use Aowow\Template\PageTemplate;
    use Aowow\User;

    define('AOWOW_REVISION', 54);
    require __DIR__.'/../includes/defines.php';
    require __DIR__.'/../includes/locale.class.php';
    require __DIR__.'/../includes/type.class.php';
    require __DIR__.'/../includes/utilities.php';
    require __DIR__.'/../includes/components/csrf.class.php';
    require __DIR__.'/../localization/lang.class.php';
    require __DIR__.'/../includes/game/uitext.class.php';
    require __DIR__.'/../localization/datetime.class.php';
    require __DIR__.'/../includes/components/jsexpression.class.php';
    require __DIR__.'/../includes/components/frontend/tabs.class.php';
    require __DIR__.'/../includes/components/guidemgr.class.php';
    // The locale bundle includes SmartAI constant keys even for unrelated pages.
    foreach (['SmartAI', 'SmartEvent', 'SmartAction', 'SmartTarget'] as $class)
        require __DIR__.'/../includes/components/SmartAI/'.$class.'.class.php';
    require __DIR__.'/../includes/components/response/baseresponse.class.php';
    require __DIR__.'/../includes/components/response/templateresponse.class.php';
    require __DIR__.'/../includes/components/pagetemplate.class.php';
    require __DIR__.'/../endpoints/guide/edit.php';
    require __DIR__.'/../endpoints/guide/changelog.php';
    require __DIR__.'/../endpoints/guide/guide.php';

    // Keep real response generation and localized HTML; omit unrelated metadata builds.
    class ChangelogFixture extends Aowow\GuideChangelogResponse {
        protected function buildBasicMetadata(string $genDesc = '', string $icon = '', bool $useArticle = true) : void {}
    }
    class GuideFixture extends Aowow\GuideBaseResponse {
        protected function buildBasicMetadata(string $genDesc = '', string $icon = '', bool $useArticle = true) : void {}
    }

    set_error_handler(function (int $level, string $message) : never { throw new ErrorException($message, 0, $level); });
    $checks = 0;
    function check(bool $condition, string $message) : void {
        global $checks;
        ++$checks;
        if (!$condition) throw new RuntimeException($message);
    }

    // Empty surrounding bricks isolate this page, while real PageTemplate methods
    // and GuideEditResponse properties exercise the vulnerable rendering boundary.
    $root = dirname(__DIR__);
    Aowow\Lang::load(Aowow\Locale::EN);
    $scratch = sys_get_temp_dir().'/aowow-guide-test-'.bin2hex(random_bytes(8));
    mkdir($scratch.'/template/bricks', 0700, true);
    $bricks = ['header', 'announcement', 'pageTemplate', 'markup', 'footer', 'infobox', 'headIcons', 'redButtons', 'mapper', 'lvTabs', 'contribute'];
    foreach ($bricks as $brick)
        file_put_contents($scratch.'/template/bricks/'.$brick.'.tpl.php', '');
    $cwd = getcwd();
    $fixtures = [];
    try {
        chdir($scratch);
        $payload = '" autofocus onfocus="parent.aowowSecurityMarker=1"><b id="aowow-injected">text</b>';
        $textareaPayload = '</textarea><script>parent.aowowSecurityMarker=2</script><b id="aowow-injected">text</b>';
        $samples = [
            ['title' => $payload, 'name' => $payload, 'description' => $textareaPayload, 'body' => $textareaPayload],
            array_fill_keys(['title', 'name', 'description', 'body'], 'Български 中文 "quotes" \' & < >'),
            ['title' => '&lt;literal&gt;', 'name' => '&#34;literal&#34;', 'description' => '&amp; &lt;b&gt;', 'body' => "[h2]Markup[/h2]\n[quote]&lt;b&gt; &amp;[/quote]"],
            array_fill_keys(['title', 'name', 'description', 'body'], '0'),
            array_fill_keys(['title', 'name', 'description', 'body'], ''),
            ['title' => 'Newlines', 'name' => 'newlines', 'description' => "\nFirst\nSecond", 'body' => "\n[h2]First[/h2]\nSecond"]
        ];
        foreach (['author', 'staff', 'failed-save'] as $scenario) {
            User::$staff = $scenario === 'staff';
            foreach ($samples as $sample) {
                $response = (new ReflectionClass(GuideEditResponse::class))->newInstanceWithoutConstructor();
                $response->typeId = 123;
                $response->editTitle = $sample['title'];
                $response->editName = $sample['name'];
                $response->editDescription = $sample['description'];
                $response->editText = $sample['body'];
                $response->error = $scenario === 'failed-save' ? 'Save failed' : '';
                $template = (new ReflectionClass(PageTemplate::class))->newInstanceWithoutConstructor();
                foreach (['context' => $response, 'user' => User::class, 'gStaticUrl' => '', 'locale' => Aowow\Locale::EN] as $property => $value)
                    (new ReflectionProperty(PageTemplate::class, $property))->setValue($template, $value);
                $render = (function (string $path) : string {
                    ob_start();
                    try { include $path; return ob_get_contents(); }
                    finally { ob_end_clean(); }
                })->bindTo($template, PageTemplate::class);
                $html = $render($root.'/template/pages/guide-edit.tpl.php');
                check(!str_contains($html, $payload), 'Attribute payload must not become HTML');
                check(!str_contains($html, $textareaPayload), 'Textarea payload must not become HTML');
                check($response->editTitle === $sample['title'] && $response->editName === $sample['name'] && $response->editDescription === $sample['description'] && $response->editText === $sample['body'], 'Rendering must preserve raw response data');
                check(str_contains($html, 'Save failed') === ($scenario === 'failed-save'), 'Error redisplay remains visible');
                check(str_contains($html, ' required></textarea>') === !User::$staff, 'Author/staff changelog requirements remain intact');
                $fixtures[] = ['html' => $html, 'values' => $sample, 'scenario' => $scenario, 'kind' => 'editor'];
            }
        }
        foreach ($samples as $sample) {
            Aowow\GuideList::$fields = [
                'title' => $sample['title'], 'name' => $sample['name'], 'description' => $sample['description'],
                'category' => 2, 'author' => $sample['name'], 'date' => time() - 60,
                'status' => Aowow\GuideMgr::STATUS_APPROVED, 'rev' => 1,
                'cuFlags' => GUIDE_CU_NO_QUICKFACTS | GUIDE_CU_NO_RATING
            ];
            Aowow\DB::$logs = [
                ['name' => $sample['name'], 'date' => time() - 60, 'status' => 0, 'msg' => $sample['body'], 'rev' => 1],
                ['name' => $sample['name'], 'date' => time() - 60, 'status' => 0, 'msg' => '', 'rev' => 1]
            ];
            foreach (['changelog' => ChangelogFixture::class, 'guide' => GuideFixture::class] as $kind => $class) {
                $response = (new ReflectionClass($class))->newInstanceWithoutConstructor();
                (new ReflectionProperty($class, '_get'))->setValue($response, ['id' => 123, 'rev' => null]);
                (new ReflectionMethod($class, 'generate'))->invoke($response);
                $expectedTitle = $sample['title'] ?: $sample['name'];
                if ($kind === 'guide') {
                    check($response->gPageInfo['name'] === $sample['name'], 'Guide globals retain the raw name');
                    $ogTitle = array_values(array_filter($response->metaTags, fn($tag) => ($tag['property'] ?? '') === 'og:title'))[0];
                    check($ogTitle['content'] === $sample['name'], 'Guide metadata retains the raw name');
                    check($response->ldIntangible['name'] === $sample['name'], 'Guide structured data retains the raw name');
                }
                else
                    check($response->title[0] === 'Changelog For "'.$expectedTitle.'"', 'Changelog document title must not double-escape entities');
                $template = (new ReflectionClass(PageTemplate::class))->newInstanceWithoutConstructor();
                (new ReflectionProperty(PageTemplate::class, 'context'))->setValue($template, $response);
                $render = (function (string $path) : string {
                    ob_start();
                    try { include $path; return ob_get_contents(); }
                    finally { ob_end_clean(); }
                })->bindTo($template, PageTemplate::class);
                $html = $render($root.'/template/pages/'.($kind === 'guide' ? 'detail-page-generic' : 'text-page-generic').'.tpl.php');
                check(!str_contains($html, $payload) && !str_contains($html, $textareaPayload), 'Adjacent guide outputs must not emit payload HTML');
                $fixtures[] = ['html' => $html, 'values' => $sample, 'kind' => $kind, 'title' => $expectedTitle];
            }
        }
    }
    finally {
        chdir($cwd);
        foreach ($bricks as $brick)
            unlink($scratch.'/template/bricks/'.$brick.'.tpl.php');
        rmdir($scratch.'/template/bricks');
        rmdir($scratch.'/template');
        rmdir($scratch);
    }

    if (!in_array('--browser', $argv, true)) {
        echo 'PASS: '.$checks.' guide editor PHP checks'.PHP_EOL;
        exit;
    }

    // Only frontend infrastructure is stubbed; the actual article preview reads
    // textarea.value and hands the unmodified markup to the renderer fixture.
    $support = <<<'JS'
window.$ = function() { return {
    ready() {}, change() {}, closest() { return this; }, attr() { return this; },
    val() { return '0'; }, get() { return document.getElementById('category'); }
}; };
window.$WH = {
    ge: id => document.getElementById(id), in_array: (list, value) => list.indexOf(value),
    AdjacentPreview: { init() {} }
};
window.g_enhanceTextarea = function() {};
window.Markup = { toHtml(text) { window.previewInput = text; return '<b>Preview fixture</b>'; } };
window.setTimeout = function(callback, delay) { if (delay === 250) callback(); return 0; };
JS;
    $preview = file_get_contents($root.'/static/js/article-editing.js');
    echo '<!doctype html><meta charset="utf-8"><title>Guide editor security checks</title><pre id="result">RUNNING</pre>';
    echo '<script>window.aowowSecurityMarker=0;</script>';
    foreach ($fixtures as $idx => $fixture) {
        $page = '<!doctype html><meta charset="utf-8"><script>'.$support."\n".$preview.'</script>'.$fixture['html'];
        echo '<iframe id="case-'.$idx.'" srcdoc="'.Aowow\Util::htmlEscape($page).'" hidden></iframe>';
    }
    echo '<script>const fixtures = '.json_encode(array_map(fn($f) => array_diff_key($f, ['html' => true]), $fixtures), JSON_HEX_TAG | JSON_THROW_ON_ERROR).';';
    echo <<<'JS'
window.addEventListener('load', function() {
    let checks = 0;
    const check = (condition, message) => { ++checks; if (!condition) throw new Error(message); };
    try {
        fixtures.forEach((fixture, index) => {
            const values = fixture.values;
            const frame = document.getElementById('case-' + index);
            const doc = frame.contentDocument;
            check(!doc.getElementById('aowow-injected'), `case ${index}: no injected node`);
            if (fixture.kind !== 'editor') {
                const heading = doc.querySelector(fixture.kind === 'guide' ? 'h1' : 'h1 a');
                let expectedHeading = fixture.kind === 'guide' ? values.name : fixture.title;
                // Existing generic templates omit falsey headings (including "0").
                if (fixture.kind === 'guide' && (expectedHeading === '' || expectedHeading === '0')) expectedHeading = '';
                check((heading?.textContent ?? '') === expectedHeading, `case ${index}: guide heading remains text`);
                if (fixture.kind === 'changelog') {
                    check(doc.querySelectorAll('li').length === 3, `case ${index}: changelog boundaries`);
                    check(doc.querySelectorAll('li a').length === 2, `case ${index}: author links preserved`);
                    for (const author of doc.querySelectorAll('li a'))
                        check(author.textContent === values.name, `case ${index}: author remains text`);
                    if (values.body)
                        check(doc.querySelector('li').textContent.includes(values.body), `case ${index}: changelog remains text`);
                }
                return;
            }
            for (const [field, id] of [['title', 'title'], ['name', 'name'], ['description', 'description'], ['body', 'editBox']]) {
                const el = doc.getElementById(id);
                check(el.value === values[field], `case ${index}: ${field} round trip`);
                check(!el.hasAttribute('autofocus') && !el.hasAttribute('onfocus'), `case ${index}: no injected attributes`);
            }
            check(doc.querySelector('.guide-form-guide-link a').textContent === values.title, `case ${index}: heading remains text`);
            check(doc.querySelectorAll('form').length === 1 && doc.querySelectorAll('textarea').length === 3, `case ${index}: editor boundaries`);
            check(frame.contentWindow.previewInput === values.body, `case ${index}: preview receives original markup`);
            check(doc.getElementById('livePreview').textContent === 'Preview fixture', `case ${index}: preview still runs`);
        });
        check(window.aowowSecurityMarker === 0, 'Injected script must not execute');
        document.getElementById('result').textContent = `PASS: ${checks} guide editor browser checks`;
    } catch (error) {
        document.getElementById('result').textContent = 'FAIL: ' + error.message;
    }
});
</script>
JS;
}
