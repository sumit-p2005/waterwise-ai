<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Method not allowed'], 405);
}

$data = readJsonInput();
$sample = normalizeSample($data);
$k = isset($data['k']) ? (int)$data['k'] : 5;

$db = getDb();
$pred = knnPredictWqi($db, $sample, $k);
$class = classifyWqi((float)$pred['predicted_wqi']);

jsonResponse([
    'ok' => true,
    'predicted_wqi' => $pred['predicted_wqi'],
    'status' => $class,
    'neighbors' => array_slice($pred['neighbors'], 0, 5),
    'dataset_count' => $pred['count'],
    'note' => 'KNN uses dataset terms: Temperature, DO, pH, BOD, Faecal Streptococci, Nitrate, Faecal Coliform, Total Coliform, Conductivity.',
]);
