<?php

declare(strict_types=1);

$makeConfig = require dirname(__DIR__, 2) . '/.php-cs-fixer.php';

return $makeConfig(__DIR__);
