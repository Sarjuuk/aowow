<?php

// Uses the exact disposable-database guard and shipped-schema bootstrap from the recovery suite.
$argv[] = '--policy';
require __DIR__.'/security-password-recovery.php';
