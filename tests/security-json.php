<?php

// Run with PHP >= 8.4: php tests/security-json.php
// --fixtures emits scripts for the independent JavaScript execution checks.
namespace Aowow {
    class Cfg {
        public static int $debug = 0;
        public static function get(string $key) : mixed { return $key === 'DEBUG' ? self::$debug : 0; }
    }
    class User {
        public static int $id = 0;
        public static function isInGroup(int $group) : bool { return false; }
    }
    class Lang {
        public static function getLocale() : Locale { return Locale::EN; }
    }
    class DB {
        public const string OR = '%or';
        public static array $rows = [];
        public static function Aowow() : self { return new self; }
        public function selectAssoc(string $query, mixed ...$args) : array {
            if (str_contains($query, '::screenshots')) return self::$rows['screenshots'];
            if (str_contains($query, '::videos')) return self::$rows['videos'];
            foreach ($args as $arg)
                if (is_array($arg))
                    foreach ($arg as $condition)
                        if (is_array($condition) && ($condition[0] ?? null) === 'c.`replyTo` = %i')
                            return self::$rows[$condition[1] ? 'replies' : 'comments'];
            throw new \RuntimeException('Unexpected fixture query.');
        }
        public function selectCol(string $query, mixed ...$args) : array { return array_column($this->selectAssoc($query, ...$args), 'id'); }
        public function selectCell(string $query, mixed ...$args) : int { return 1; }
    }
}

namespace {
    use Aowow\Cfg;
    use Aowow\DB;
    use Aowow\JsExpression;
    use Aowow\Listview;
    use Aowow\Markup;
    use Aowow\Tabs;
    use Aowow\Tooltip;
    use Aowow\Util;

    define('AOWOW_REVISION', 53);
    require __DIR__.'/../includes/defines.php';
    require __DIR__.'/../includes/locale.class.php';
    require __DIR__.'/../includes/type.class.php';
    require __DIR__.'/../includes/utilities.php';
    require __DIR__.'/../includes/components/csrf.class.php';
    require __DIR__.'/../includes/components/jsexpression.class.php';
    require __DIR__.'/../includes/components/frontend/listview.class.php';
    require __DIR__.'/../includes/components/frontend/tabs.class.php';
    require __DIR__.'/../includes/components/frontend/markup.class.php';
    require __DIR__.'/../includes/components/frontend/tooltip.class.php';
    require __DIR__.'/../includes/components/frontend/summary.class.php';
    require __DIR__.'/../includes/components/frontend/announcement.class.php';
    require __DIR__.'/../includes/components/communitycontent.class.php';
    require __DIR__.'/../includes/components/report.class.php';
    require __DIR__.'/../includes/components/response/baseresponse.class.php';
    require __DIR__.'/../includes/components/response/templateresponse.class.php';
    require __DIR__.'/../includes/components/pagetemplate.class.php';

    $checks = 0;
    function check(bool $condition, string $message) : void {
        global $checks;
        ++$checks;
        if (!$condition) throw new RuntimeException($message);
    }
    function expectEncodingFailure(callable $encode, string $message) : void {
        $warnings = [];
        set_error_handler(function (int $level, string $warning) use (&$warnings) : bool { $warnings[] = $warning; return true; });
        try { $result = $encode(); } finally { restore_error_handler(); }
        check($result === '' && count($warnings) === 1, $message);
    }
    set_error_handler(function (int $level, string $message) : never { throw new ErrorException($message, 0, $level); });

    $payload = '$(globalThis.aowowSecurityMarker=1)';
    $htmlPayload = '</script><script>globalThis.aowowSecurityMarker=2</script><!--';
    $strings = [$payload, '$LANG.tab_items', '$function(){globalThis.aowowSecurityMarker=3}', '$', '$"quotes"', "quote ' \" slash \\ newline\n", 'Български 中文', $htmlPayload];
    $fixtures = [];
    foreach ([0, JSON_UNESCAPED_UNICODE, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE, JSON_AOWOW_POWER] as $flags) {
        foreach ($strings as $text) {
            $data = ['body' => $text, 'nested' => [['caption' => $text]], $text => $text];
            foreach (['toJSON', 'toJavaScript'] as $method) {
                $encoded = Util::$method($data, $flags);
                check(json_decode($encoded, true, 512, JSON_THROW_ON_ERROR) === $data, "$method must preserve strings and keys");
                check(!str_contains($encoded, '<') && !str_contains($encoded, '>'), "$method must protect script boundaries");
            }
        }
    }
    check(Util::toJSON(['count' => '12']) === '{"count":12}', 'Default numeric conversion remains available');
    check(Util::toJSON(['user' => '123'], JSON_UNESCAPED_UNICODE) === '{"user":"123"}', 'Community numeric usernames stay strings');

    $plainValues = [[], (object)[], [2 => 'sparse', '007' => 'key'], (object)['0' => '123', 'text' => $payload], ['float' => 1.0, 'bool' => true, 'null' => null], Aowow\Locale::EN];
    foreach ([JSON_UNESCAPED_UNICODE, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT, JSON_PRESERVE_ZERO_FRACTION] as $flags)
        foreach ($plainValues as $data)
            check(Util::toJavaScript($data, $flags) === json_encode($data, $flags | JSON_HEX_TAG | JSON_THROW_ON_ERROR), 'Plain JavaScript values must match native JSON encoding');
    $publicObject = new class implements JsonSerializable {
        public string $label = '$LANG.tab_items';
        public function jsonSerialize() : mixed { return $this; }
    };
    check(Util::toJavaScript($publicObject) === Util::toJSON($publicObject), 'Self-returning JsonSerializable public properties match JSON');
    $sharedObject = (object)['text' => $payload];
    check(Util::toJavaScript([$sharedObject, $sharedObject]) === Util::toJSON([$sharedObject, $sharedObject]), 'Repeated objects are not recursive');
    Cfg::$debug = 1;
    check(Util::toJavaScript(['nested' => [$payload]]) === Util::toJSON(['nested' => [$payload]]), 'Debug formatting matches native JSON');
    Cfg::$debug = 0;

    $cycle = (object)[]; $cycle->self = $cycle;
    expectEncodingFailure(fn() => Util::toJavaScript($cycle), 'Object recursion must fail closed');
    $arrayCycle = []; $arrayCycle[] = &$arrayCycle;
    expectEncodingFailure(fn() => Util::toJavaScript($arrayCycle), 'Array recursion must fail closed');
    expectEncodingFailure(fn() => Util::toJavaScript(["\xff"]), 'Invalid UTF-8 must fail closed');
    expectEncodingFailure(fn() => Util::toJSON(new JsExpression('LANG.tab_items')), 'Expressions cannot pass through the JSON data API');
    foreach (['</script>', '<!--', '-->'] as $delimiter) {
        try { new JsExpression($delimiter); check(false, 'Unsafe raw expression accepted'); }
        catch (InvalidArgumentException) { check(true, 'Raw script delimiters rejected'); }
    }
    try { JsExpression::call('alert(1)', 'text'); check(false, 'Unsafe function reference accepted'); }
    catch (InvalidArgumentException) { check(true, 'Function references are bounded'); }
    $unique = array_unique([new JsExpression('Listview.extraCols.date'), new JsExpression('Listview.extraCols.date')]);
    check(count($unique) === 1 && reset($unique) instanceof JsExpression, 'Column deduplication preserves explicit expression types');

    $mixed = ['label' => new JsExpression('LANG.tab_items'), 'callback' => new JsExpression('function(){ return 7; }'), 'data' => $payload, 'talents' => JsExpression::literal('0000123'), 'guild' => JsExpression::literal($htmlPayload)];
    $fixtures['mixed'] = 'globalThis.mixed = '.Util::toJavaScript($mixed).';';
    check(str_contains($fixtures['mixed'], '"label":LANG.tab_items'), 'Trusted locale references remain executable');
    check(str_contains($fixtures['mixed'], '"data":"$'), 'Ordinary dollar strings stay quoted beside code');
    check(str_contains($fixtures['mixed'], '"talents":"0000123"'), 'Talent strings retain leading zeros');
    $fixtures['call'] = 'globalThis.callResult = '.Util::toJavaScript(JsExpression::call('$WH.sprintf', new JsExpression('LANG.tab_items'), $htmlPayload, $payload)).';';

    $lv = new Listview(['data' => [['name' => $payload, 'title' => $htmlPayload]], 'extraCols' => [new JsExpression('Listview.extraCols.date')], 'onAfterCreate' => new JsExpression('Listview.funcBox.addModeIndicator')], 'item');
    $fixtures['listview'] = (string)$lv;
    check(str_contains($fixtures['listview'], '"name":LANG.tab_items'), 'Listview default names use the trusted representation');
    check(str_contains($fixtures['listview'], '"onAfterCreate":Listview.funcBox.addModeIndicator'), 'Callbacks remain function references');
    $fixtures['user-label'] = (string)new Listview(['name' => $payload, 'note' => $htmlPayload, 'data' => []], 'guide');
    $tabs = new Tabs(['parent' => new JsExpression("$"."WH.ge('tabs-generic')")]);
    $tabs->addListviewTab($lv);
    $tabs->addDataTab('safe-tab', $payload, '<b>content</b>');
    $fixtures['tabs'] = (string)$tabs;
    check(str_contains($fixtures['tabs'], '"tabs":myTabs'), 'Tabbed listviews keep the Tabs instance reference');
    check(str_contains($fixtures['tabs'], '.add("$'), 'Data tab titles no longer promote dollar strings to code');

    $markup = new Markup($payload, ['mode' => Markup::MODE_COMMENT, 'allow' => Markup::CLASS_USER, 'prepend' => $payload, 'append' => $htmlPayload]);
    $fixtures['markup'] = (string)$markup;
    check(str_contains($fixtures['markup'], '"mode":Markup.MODE_COMMENT'), 'Markup mode constants still execute');
    check(str_contains($fixtures['markup'], '"prepend":"$'), 'Markup text options stay quoted');
    $fixtures['summary'] = (string)new Aowow\Summary(['id' => 'summary', 'template' => 'item', 'groups' => [['name' => $payload]]]);
    $fixtures['tooltip'] = (string)new Tooltip('$WowheadPower.registerProfile(%s, %d, %s);', 'profile', ['name' => $payload, 'tooltip' => $htmlPayload, 'icon' => JsExpression::call('$WH.g_getProfileIcon', 1, 2, 0, 80, '123')]);
    check(str_contains($fixtures['tooltip'], '"icon": $WH.g_getProfileIcon'), 'Tooltip icon expressions retain their unlocalized key');
    check(!str_contains($fixtures['tooltip'], '"icon_enus"'), 'Tooltip icon key is not translated');
    $fixtures['profiler'] = 'globalThis.profile = '.Util::toJavaScript(['guild' => JsExpression::literal('123'), 'talents' => JsExpression::literal('000123'), 'achievements' => [1 => new JsExpression('new Date(1000)')], 'health' => [0.05, 'functionOf', new JsExpression('(x) => g_statistics.combo[x.classs][x.level][5]')]]).';';
    $fixtures['globals'] = 'globalThis.locales = '.Util::toJavaScript([['id' => new JsExpression('LOCALE_ENUS'), 'name' => 'enus', 'description' => 'English']]).';';

    $row = ['id' => 1, 'userId' => 7, 'user' => '123', 'body' => $payload, 'roles' => 0, 'date' => 1, 'editDate' => 1, 'rating' => 0, 'userRating' => 0, 'userReported' => 0, 'responseBody' => '', 'editCount' => 0, 'flags' => 0];
    DB::$rows = ['comments' => [$row], 'replies' => [array_replace($row, ['id' => 2, 'body' => $htmlPayload])], 'screenshots' => [['id' => 3, 'user' => '123', 'date' => 1, 'caption' => $payload, 'sticky' => 0]], 'videos' => [['id' => 4, 'user' => '123', 'date' => 1, 'caption' => $htmlPayload, 'sticky' => 0]]];
    // Exercise the real community reader and response serializer with a synthetic DB boundary.
    $response = new class extends Aowow\TemplateResponse {
        public int $type;
        public int $typeId;
        public function __construct() { $this->type = Aowow\Type::ITEM; $this->typeId = 1; $this->contribute = CONTRIBUTE_CO | CONTRIBUTE_SS | CONTRIBUTE_VI; $this->result = new stdClass; }
        public function extendGlobalData(array $data, ?array $extra = null) : void {}
        public function community() : array { return $this->result->community; }
    };
    (new ReflectionMethod(Aowow\TemplateResponse::class, 'addCommunityContent'))->invoke($response);
    $community = $response->community();
    $decoded = json_decode($community['co'], true, 512, JSON_THROW_ON_ERROR);
    check($decoded[0]['body'] === $payload && $decoded[0]['replies'][0]['body'] === $htmlPayload, 'Real comment/reply readers and serializer preserve attack strings');
    check($decoded[0]['user'] === '123', 'Real community path preserves numeric usernames');
    $page = new class($community) {
        public int $contribute = CONTRIBUTE_CO | CONTRIBUTE_SS | CONTRIBUTE_VI;
        public array $gPageInfo = [];
        public array $pageTemplate = [];
        public function __construct(public array $community) {}
        public function render() : string { ob_start(); include __DIR__.'/../template/bricks/pageTemplate.tpl.php'; return ob_get_clean(); }
    };
    $html = $page->render();
    check(substr_count(strtolower($html), '<script') === 1 && substr_count(strtolower($html), '</script>') === 1, 'Rendered community HTML retains one script boundary');
    preg_match('~<script[^>]*>(.*?)</script>~s', $html, $script);
    $fixtures['community'] = $script[1];

    // The actual PageTemplate helper also carries guide labels, user icons, weights and map configuration.
    $template = (new ReflectionClass(Aowow\Template\PageTemplate::class))->newInstanceWithoutConstructor();
    $encoded = (new ReflectionMethod($template, 'json'))->invoke($template, ['title' => $payload, 'map' => ['name' => $htmlPayload], 'icon' => new JsExpression('LANG.tab_items')]);
    check(str_contains($encoded, '"title":"$') && !str_contains($encoded, '<'), 'PageTemplate mixed configuration keeps data safe');
    $fixtures['template'] = 'globalThis.templateData = '.$encoded.';';

    foreach ($fixtures as $name => $script)
        check(!preg_match('~</script|<!--~i', $script), "$name fixture cannot escape an HTML script");
    if (in_array('--fixtures', $argv, true))
        echo json_encode(['scripts' => $fixtures, 'payload' => $payload, 'htmlPayload' => $htmlPayload, 'checks' => $checks], JSON_THROW_ON_ERROR);
    else
        echo "PASS: $checks PHP security and compatibility checks.\n";
}
