<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

$db = getDb();
$mode = $_GET['mode'] ?? 'all';

if ($mode === 'dataset_trends') {
    $rows = $db->query('SELECT id, temperature, ph, conductivity, bio_chemical_oxygen_demand, do_level, wqi FROM dataset_data ORDER BY id ASC')->fetch_all(MYSQLI_ASSOC);
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'data' => $rows]);
    exit;
}

if ($mode === 'dataset_summary') {
    $stats = $db->query('SELECT COUNT(*) AS total, AVG(wqi) AS avg_wqi, MIN(wqi) AS min_wqi, MAX(wqi) AS max_wqi, AVG(ph) AS avg_ph, AVG(conductivity) AS avg_tds FROM dataset_data')->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'stats' => $stats]);
    exit;
}

if ($mode === 'markers') {
    $rows = $db->query('SELECT id, city, state, temperature, do_level, ph, bio_chemical_oxygen_demand, faecal_streptococci, nitrate, faecal_coliform, total_coliform, conductivity, wqi, latitude, longitude, created_at FROM water_data WHERE latitude IS NOT NULL AND longitude IS NOT NULL ORDER BY created_at DESC LIMIT 500')->fetch_all(MYSQLI_ASSOC);
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'data' => $rows]);
    exit;
}

if ($mode === 'trends') {
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;

    $query = 'SELECT created_at, wqi, ph, conductivity AS tds, latitude, longitude FROM water_data WHERE 1=1';
    $params = [];
    $types = '';

    if ($dateFrom) {
        $query .= ' AND DATE(created_at) >= ?';
        $params[] = $dateFrom;
        $types .= 's';
    }
    if ($dateTo) {
        $query .= ' AND DATE(created_at) <= ?';
        $params[] = $dateTo;
        $types .= 's';
    }

    $query .= ' ORDER BY created_at ASC LIMIT 500';
    $stmt = $db->prepare($query);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'data' => $rows]);
    exit;
}

if ($mode === 'activity') {
    $rows = $db->query('SELECT id, wqi, latitude, longitude, created_at FROM water_data ORDER BY created_at DESC LIMIT 15')->fetch_all(MYSQLI_ASSOC);
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'data' => $rows]);
    exit;
}

if ($mode === 'dashboard') {
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;

    $conditions = ' WHERE 1=1 ';
    $params = [];
    $types = '';
    if ($dateFrom) {
        $conditions .= ' AND DATE(created_at) >= ?';
        $params[] = $dateFrom;
        $types .= 's';
    }
    if ($dateTo) {
        $conditions .= ' AND DATE(created_at) <= ?';
        $params[] = $dateTo;
        $types .= 's';
    }

    $statsSql = 'SELECT COUNT(*) AS total, AVG(wqi) AS avg_wqi, MIN(wqi) AS min_wqi, MAX(wqi) AS max_wqi, SUM(CASE WHEN wqi <= 100 THEN 1 ELSE 0 END) AS safe_count, SUM(CASE WHEN wqi > 200 THEN 1 ELSE 0 END) AS unsafe_count FROM water_data' . $conditions;
    $statsStmt = $db->prepare($statsSql);
    if ($types !== '') {
        $statsStmt->bind_param($types, ...$params);
    }
    $statsStmt->execute();
    $stats = $statsStmt->get_result()->fetch_assoc();
    $total = max((int)($stats['total'] ?? 0), 1);
    $stats['safe_pct'] = round((((int)$stats['safe_count']) / $total) * 100, 2);

    $leadSql = 'SELECT ROUND(latitude, 2) AS lat_key, ROUND(longitude, 2) AS lon_key, COUNT(*) as points, AVG(wqi) AS avg_wqi FROM water_data WHERE latitude IS NOT NULL AND longitude IS NOT NULL GROUP BY lat_key, lon_key HAVING points >= 1 ORDER BY avg_wqi ASC, points DESC LIMIT 10';
    $leaderboard = $db->query($leadSql)->fetch_all(MYSQLI_ASSOC);

    $recent = $db->query('SELECT id, wqi, ph, conductivity AS tds, created_at, latitude, longitude FROM water_data ORDER BY created_at DESC LIMIT 20')->fetch_all(MYSQLI_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true,
        'stats' => $stats,
        'leaderboard' => $leaderboard,
        'recent' => $recent,
    ]);
    exit;
}

$latest = $db->query('SELECT id, temperature, do_level, ph, bio_chemical_oxygen_demand, conductivity, wqi, latitude, longitude, created_at FROM water_data ORDER BY created_at DESC LIMIT 20')->fetch_all(MYSQLI_ASSOC);
$stats = $db->query('SELECT COUNT(*) AS total, AVG(wqi) AS avg_wqi, MIN(wqi) AS min_wqi, MAX(wqi) AS max_wqi FROM water_data')->fetch_assoc();

header('Content-Type: application/json');
echo json_encode([
    'ok' => true,
    'latest' => $latest,
    'stats' => $stats,
]);

