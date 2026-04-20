<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Method not allowed'], 405);
}

ob_start();
try {
    require __DIR__ . '/../import_dataset.php';
    $output = trim((string)ob_get_clean());
    jsonResponse([
        'ok' => true,
        'message' => $output !== '' ? $output : 'Dataset import completed.',
    ]);
} catch (Throwable $e) {
    ob_end_clean();
    jsonResponse([
        'ok' => false,
        'message' => $e->getMessage(),
    ], 500);
}
