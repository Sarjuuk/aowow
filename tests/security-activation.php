<?php

// Share the guarded disposable SQL/session bootstrap with the recovery suite.
$argv[] = '--activation';
require __DIR__.'/security-password-recovery.php';
