<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$db = getDb();
$rows = $db->query('SELECT id, temperature, do_level, ph, bio_chemical_oxygen_demand, faecal_streptococci, nitrate, faecal_coliform, total_coliform, conductivity, wqi, latitude, longitude, created_at FROM water_data ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="water_data_export.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['id', 'temperature', 'do_level', 'ph', 'bio_chemical_oxygen_demand', 'faecal_streptococci', 'nitrate', 'faecal_coliform', 'total_coliform', 'conductivity', 'wqi', 'latitude', 'longitude', 'created_at']);
foreach ($rows as $row) {
    fputcsv($out, $row);
}
fclose($out);
exit;
