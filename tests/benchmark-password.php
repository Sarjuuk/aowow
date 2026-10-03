<?php

// Bounded, sequential synthetic benchmark; no accounts, passwords, database, or mail are read.
namespace Aowow {
    class Cfg {
        public static function get(string $key) : int { return AUTH_MODE_SELF; }
    }
}

namespace {
    if (PHP_SAPI !== 'cli') die('CLI only');
    define('AOWOW_REVISION', 61);
    require __DIR__.'/../includes/defines.php';
    require __DIR__.'/../includes/utilities.php';
    require __DIR__.'/../includes/user.class.php';
    $samples = (int)($argv[1] ?? 3);
    if ($samples < 1 || $samples > 10) {
        fwrite(STDERR, "Usage: php tests/benchmark-password.php [samples: 1..10, default 3]\n");
        exit(1);
    }
    $median = function (array $values) : float {
        sort($values);
        $middle = intdiv(count($values), 2);
        return round(count($values) % 2 ? $values[$middle] : ($values[$middle - 1] + $values[$middle]) / 2, 2);
    };
    $results = [];
    foreach ([Aowow\User::BCRYPT_COST, 15] as $cost) {
        $hashTimes = $verifyTimes = [];
        for ($i = 0; $i < $samples; ++$i) {
            $start = hrtime(true);
            $hash = $cost === Aowow\User::BCRYPT_COST ? Aowow\User::hashCrypt('synthetic benchmark password') : password_hash('synthetic benchmark password', PASSWORD_BCRYPT, ['cost' => $cost]);
            $hashTimes[] = (hrtime(true) - $start) / 1e6;
            $start = hrtime(true);
            if (!Aowow\User::verifyCrypt('synthetic benchmark password', $hash)) throw new RuntimeException('Verification failed');
            $verifyTimes[] = (hrtime(true) - $start) / 1e6;
        }
        $results[] = ['cost' => $cost, 'hash_median_ms' => $median($hashTimes), 'verify_median_ms' => $median($verifyTimes)];
    }
    echo json_encode(['php' => PHP_VERSION, 'samples_per_cost' => $samples, 'sequential' => true, 'results' => $results], JSON_PRETTY_PRINT)."\n";
}
