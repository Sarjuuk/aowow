<?php

namespace Aowow {
    // Instrument the CSPRNG call without replacing its real entropy, and inject
    // an entropy failure to ensure production code never falls back to mt_rand.
    class TokenFixture {
        public static int $draws = 0;
        public static bool $fail = false;
    }
    function random_int(int $min, int $max) : int {
        ++TokenFixture::$draws;
        if ($min !== 0 || $max !== 61) throw new \RuntimeException('Unexpected token alphabet bounds');
        if (TokenFixture::$fail) throw new \Random\RandomException('Fixture entropy failure');
        return \random_int($min, $max);
    }
}

namespace {
    use Aowow\TokenFixture;
    use Aowow\Util;

    define('AOWOW_REVISION', 56);
    require __DIR__.'/../includes/utilities.php';
    set_error_handler(function (int $level, string $message) : never { throw new ErrorException($message, 0, $level); });
    $checks = 0;
    function check(bool $condition, string $message) : void {
        global $checks;
        ++$checks;
        if (!$condition) throw new RuntimeException($message);
    }

    foreach ([-1, 0, 1, 8, 12, 16, 40, 128] as $length) {
        TokenFixture::$draws = 0;
        $token = Util::createHash($length);
        check(strlen($token) === max(0, $length), 'Requested token lengths stay compatible');
        check($token === '' || preg_match('/^[a-zA-Z0-9]+$/D', $token) === 1, 'Existing token alphabet stays compatible');
        check(TokenFixture::$draws === max(0, $length), 'Every character uses the bounded CSPRNG');
    }
    $accountToken = Util::createHash();
    check(preg_match('/^[a-zA-Z0-9]{40}$/D', $accountToken) === 1, 'Default tokens satisfy account confirmation validators');
    check(preg_match('/^[a-zA-Z0-9]{16}$/D', Util::createHash(16)) === 1, 'Upload tokens retain their URL/file format');
    mt_srand(12345);
    $first = Util::createHash();
    mt_srand(12345);
    check(Util::createHash() !== $first, 'Repeating the mt_rand seed cannot reproduce security tokens');
    TokenFixture::$fail = true;
    try { Util::createHash(); check(false, 'Entropy failure must not produce a token'); }
    catch (Random\RandomException) { check(true, 'Entropy failures propagate without insecure fallback'); }
    echo 'PASS: '.$checks.' token security and compatibility checks'.PHP_EOL;
}
