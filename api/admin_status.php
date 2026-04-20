<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$db = getDb();
$dataset = $db->query('SELECT COUNT(*) total, AVG(wqi) avg_wqi, MIN(wqi) min_wqi, MAX(wqi) max_wqi FROM dataset_data')->fetch_assoc();
$water = $db->query('SELECT COUNT(*) total, AVG(wqi) avg_wqi FROM water_data')->fetch_assoc();

$csvPath = __DIR__ . '/../assets/data/Results_MADE.csv';
$csvInfo = [
  'exists' => file_exists($csvPath),
  'path' => realpath($csvPath) ?: $csvPath,
  'size_kb' => file_exists($csvPath) ? round(filesize($csvPath) / 1024, 2) : 0,
];

jsonResponse([
  'ok' => true,
  'dataset' => $dataset,
  'water_data' => $water,
  'csv' => $csvInfo,
]);
