<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$db = getDb();
$csvPath = __DIR__ . '/assets/data/Results_MADE.csv';

if (!file_exists($csvPath)) {
    exit('CSV file not found: ' . $csvPath);
}

$fp = fopen($csvPath, 'r');
$headers = fgetcsv($fp);
if (!$headers) {
    exit('Invalid CSV header');
}

$headerMap = array_flip($headers);
$required = [
    'Temperature',
    'Dissolved Oxygen',
    'pH',
    'Bio-Chemical Oxygen Demand (mg/L)',
    'Faecal Streptococci (MPN/ 100 mL)',
    'Nitrate (mg/ L)',
    'Faecal Coliform (MPN/ 100 mL)',
    'Total Coliform (MPN/ 100 mL)',
    'Conductivity (mho/ Cm)',
    'WQI',
];

foreach ($required as $col) {
    if (!isset($headerMap[$col])) {
        exit('Missing column: ' . $col);
    }
}

$db->query('TRUNCATE TABLE dataset_data');
$stmt = $db->prepare('INSERT INTO dataset_data (ph, tds, do_level, turbidity, temperature, bio_chemical_oxygen_demand, faecal_streptococci, nitrate, faecal_coliform, total_coliform, conductivity, wqi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

$count = 0;
while (($row = fgetcsv($fp)) !== false) {
    $temperature = (float)$row[$headerMap['Temperature']];
    $doLevel = (float)$row[$headerMap['Dissolved Oxygen']];
    $ph = (float)$row[$headerMap['pH']];
    $bod = (float)$row[$headerMap['Bio-Chemical Oxygen Demand (mg/L)']];
    $fs = (float)$row[$headerMap['Faecal Streptococci (MPN/ 100 mL)']];
    $nitrate = (float)$row[$headerMap['Nitrate (mg/ L)']];
    $fc = (float)$row[$headerMap['Faecal Coliform (MPN/ 100 mL)']];
    $tc = (float)$row[$headerMap['Total Coliform (MPN/ 100 mL)']];
    $conductivity = (float)$row[$headerMap['Conductivity (mho/ Cm)']];
    $wqi = (float)$row[$headerMap['WQI']];

    $tdsProxy = $conductivity;
    $turbidityProxy = $bod;

    $stmt->bind_param('dddddddddddd', $ph, $tdsProxy, $doLevel, $turbidityProxy, $temperature, $bod, $fs, $nitrate, $fc, $tc, $conductivity, $wqi);
    $stmt->execute();
    $count++;
}

fclose($fp);
echo "Imported {$count} rows into dataset_data with full parameter mapping.";
