<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Method not allowed'], 405);
}

$data = readJsonInput();
$message = trim((string)($data['message'] ?? ''));

if ($message === '') {
    jsonResponse(['ok' => false, 'reply' => 'Please send a message.'], 422);
}

preg_match_all('/-?\d+(?:\.\d+)?/', $message, $matches);
$nums = array_map('floatval', $matches[0] ?? []);
$db = getDb();

if (count($nums) >= 9) {
    [$temperature, $doLevel, $ph, $bod, $fs, $nitrate, $fc, $tc, $conductivity] = array_slice($nums, 0, 9);
    $sample = normalizeSample([
        'temperature' => $temperature,
        'do_level' => $doLevel,
        'ph' => $ph,
        'bio_chemical_oxygen_demand' => $bod,
        'faecal_streptococci' => $fs,
        'nitrate' => $nitrate,
        'faecal_coliform' => $fc,
        'total_coliform' => $tc,
        'conductivity' => $conductivity,
    ]);

    $pred = knnPredictWqi($db, $sample, 5);
    $wqi = (float)$pred['predicted_wqi'];
    $rec = recommendationFromWqi($wqi);

    jsonResponse([
        'ok' => true,
        'mode' => 'prediction',
        'reply' => "Predicted WQI: {$wqi}. Category: {$rec['status']}.",
        'predicted_wqi' => $wqi,
        'category' => $rec['status'],
        'explanation' => $rec['message'],
        'tips' => $rec['tips'],
    ]);
}

$lower = strtolower($message);
if (str_contains($lower, 'predict')) {
    jsonResponse([
        'ok' => true,
        'mode' => 'assistant',
        'reply' => 'Provide 9 values: Temperature, DO, pH, BOD, Faecal Streptococci, Nitrate, Faecal Coliform, Total Coliform, Conductivity.',
        'explanation' => 'I will run KNN on the local dataset and return predicted WQI.',
    ]);
}

$row = $db->query('SELECT AVG(wqi) AS avg_wqi, MIN(wqi) AS min_wqi, MAX(wqi) AS max_wqi, COUNT(*) AS total FROM dataset_data')->fetch_assoc();
jsonResponse([
    'ok' => true,
    'mode' => 'dataset',
    'reply' => 'Local dataset summary ready.',
    'category' => 'Knowledge',
    'explanation' => 'Records: ' . (int)$row['total'] . ', Avg WQI: ' . round((float)$row['avg_wqi'], 2) . ', Min: ' . round((float)$row['min_wqi'], 2) . ', Max: ' . round((float)$row['max_wqi'], 2) . '.',
]);
