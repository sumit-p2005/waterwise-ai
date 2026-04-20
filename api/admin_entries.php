<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$db = getDb();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $search = trim((string)($_GET['search'] ?? ''));
    $risk = trim((string)($_GET['risk'] ?? 'all'));

    $query = 'SELECT id, temperature, do_level, ph, bio_chemical_oxygen_demand, conductivity, wqi, latitude, longitude, created_at FROM water_data WHERE 1=1';
    $params = [];
    $types = '';

    if ($search !== '') {
        $query .= ' AND (CAST(id AS CHAR) LIKE ? OR CAST(latitude AS CHAR) LIKE ? OR CAST(longitude AS CHAR) LIKE ?)';
        $s = '%' . $search . '%';
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
        $types .= 'sss';
    }

    if ($risk === 'safe') {
        $query .= ' AND wqi <= 100';
    } elseif ($risk === 'unsafe') {
        $query .= ' AND wqi > 200';
    }

    $query .= ' ORDER BY created_at DESC LIMIT 300';
    $stmt = $db->prepare($query);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    jsonResponse(['ok' => true, 'data' => $rows]);
}

if ($method === 'PUT') {
    $data = readJsonInput();
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(['ok' => false, 'message' => 'Invalid id'], 422);
    }

    // Preserve full parameter set from DB for hidden fields removed from UI.
    $oldStmt = $db->prepare('SELECT faecal_streptococci, nitrate, faecal_coliform, total_coliform FROM water_data WHERE id=? LIMIT 1');
    $oldStmt->bind_param('i', $id);
    $oldStmt->execute();
    $existing = $oldStmt->get_result()->fetch_assoc();
    if (!$existing) {
        jsonResponse(['ok' => false, 'message' => 'Record not found'], 404);
    }

    $merged = [
        'temperature' => $data['temperature'] ?? 0,
        'do_level' => $data['do_level'] ?? 0,
        'ph' => $data['ph'] ?? 0,
        'bio_chemical_oxygen_demand' => $data['bio_chemical_oxygen_demand'] ?? 0,
        'conductivity' => $data['conductivity'] ?? 0,
        'faecal_streptococci' => $existing['faecal_streptococci'] ?? 0,
        'nitrate' => $existing['nitrate'] ?? 0,
        'faecal_coliform' => $existing['faecal_coliform'] ?? 0,
        'total_coliform' => $existing['total_coliform'] ?? 0,
    ];

    $sample = normalizeSample($merged);
    $wqi = calculateWqiFromSample($sample);
    $lat = isset($data['latitude']) && $data['latitude'] !== '' ? (float)$data['latitude'] : null;
    $lon = isset($data['longitude']) && $data['longitude'] !== '' ? (float)$data['longitude'] : null;

    $ph = $sample['ph'];
    $tdsProxy = $sample['conductivity'];
    $do = $sample['do_level'];
    $turbidityProxy = $sample['bio_chemical_oxygen_demand'];
    $temp = $sample['temperature'];
    $bod = $sample['bio_chemical_oxygen_demand'];
    $fs = $sample['faecal_streptococci'];
    $nitrate = $sample['nitrate'];
    $fc = $sample['faecal_coliform'];
    $tc = $sample['total_coliform'];
    $conductivity = $sample['conductivity'];

    $stmt = $db->prepare('UPDATE water_data SET ph=?, tds=?, do_level=?, turbidity=?, temperature=?, bio_chemical_oxygen_demand=?, faecal_streptococci=?, nitrate=?, faecal_coliform=?, total_coliform=?, conductivity=?, wqi=?, latitude=?, longitude=? WHERE id=?');
    $stmt->bind_param('ddddddddddddddi', $ph, $tdsProxy, $do, $turbidityProxy, $temp, $bod, $fs, $nitrate, $fc, $tc, $conductivity, $wqi, $lat, $lon, $id);
    $stmt->execute();

    jsonResponse(['ok' => true, 'message' => 'Entry updated', 'wqi' => $wqi]);
}

if ($method === 'DELETE') {
    $data = readJsonInput();
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(['ok' => false, 'message' => 'Invalid id'], 422);
    }

    $stmt = $db->prepare('DELETE FROM water_data WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    jsonResponse(['ok' => true, 'message' => 'Entry deleted']);
}

jsonResponse(['ok' => false, 'message' => 'Method not allowed'], 405);
