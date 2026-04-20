<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Method not allowed'], 405);
}

$data = readJsonInput();
$id = (int)($data['id'] ?? 0);
$newWqi = (float)($data['wqi'] ?? -1);

if ($id <= 0 || $newWqi < 0) {
    jsonResponse(['ok' => false, 'message' => 'Invalid id or WQI'], 422);
}

$db = getDb();
$stmt = $db->prepare('UPDATE water_data SET wqi=? WHERE id=?');
$stmt->bind_param('di', $newWqi, $id);
$stmt->execute();

jsonResponse(['ok' => true, 'message' => 'WQI updated', 'id' => $id, 'wqi' => $newWqi]);
