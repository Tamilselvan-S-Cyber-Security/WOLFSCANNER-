<?php

declare(strict_types=1);

$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
$target = $base . '/../debugger';

header('Location: ' . $target);
exit;
