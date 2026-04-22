<?php

declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

$page = $_GET['page'] ?? 'home';

$pageMap = [
    'home' => 'index.php',
    'calculator' => 'calculator.php',
    'map' => 'map.php',
    'dashboard' => 'dashboard.php',
    'admin' => 'admin.php',
    'report' => 'report.php',
];

if (!isset($pageMap[$page])) {
    http_response_code(404);
    echo 'Page not found';
    exit;
}

$target = $root . DIRECTORY_SEPARATOR . $pageMap[$page];

if (!is_file($target)) {
    http_response_code(500);
    echo 'Page file missing';
    exit;
}

require $target;