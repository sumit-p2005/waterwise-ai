<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Method not allowed'], 405);
}

$data = readJsonInput();
$sample = normalizeSample($data);
$latitude = isset($data['latitude']) ? (float)$data['latitude'] : null;
$longitude = isset($data['longitude']) ? (float)$data['longitude'] : null;
$city = isset($data['city']) ? trim((string)$data['city']) : null;
$state = isset($data['state']) ? trim((string)$data['state']) : null;

$wqi = calculateWqiFromSample($sample);
$class = classifyWqi($wqi);

$db = getDb();
$stmt = $db->prepare('INSERT INTO water_data (city, state, ph, tds, do_level, turbidity, temperature, bio_chemical_oxygen_demand, faecal_streptococci, nitrate, faecal_coliform, total_coliform, conductivity, wqi, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

$ph = $sample['ph'];
$tdsProxy = $sample['conductivity'];
$doLevel = $sample['do_level'];
$turbidityProxy = $sample['bio_chemical_oxygen_demand'];
$temperature = $sample['temperature'];
$bod = $sample['bio_chemical_oxygen_demand'];
$fs = $sample['faecal_streptococci'];
$nitrate = $sample['nitrate'];
$fc = $sample['faecal_coliform'];
$tc = $sample['total_coliform'];
$conductivity = $sample['conductivity'];

$stmt->bind_param('ssdddddddddddddd', $city, $state, $ph, $tdsProxy, $doLevel, $turbidityProxy, $temperature, $bod, $fs, $nitrate, $fc, $tc, $conductivity, $wqi, $latitude, $longitude);
$stmt->execute();

jsonResponse([
    'ok' => true,
    'id' => $stmt->insert_id,
    'wqi' => $wqi,
    'status' => $class,
]);
