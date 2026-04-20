<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$db = getDb();
$queries = [
    "ALTER TABLE water_data ADD COLUMN IF NOT EXISTS bio_chemical_oxygen_demand FLOAT NOT NULL DEFAULT 0",
    "ALTER TABLE water_data ADD COLUMN IF NOT EXISTS faecal_streptococci FLOAT NOT NULL DEFAULT 0",
    "ALTER TABLE water_data ADD COLUMN IF NOT EXISTS nitrate FLOAT NOT NULL DEFAULT 0",
    "ALTER TABLE water_data ADD COLUMN IF NOT EXISTS faecal_coliform FLOAT NOT NULL DEFAULT 0",
    "ALTER TABLE water_data ADD COLUMN IF NOT EXISTS total_coliform FLOAT NOT NULL DEFAULT 0",
    "ALTER TABLE water_data ADD COLUMN IF NOT EXISTS conductivity FLOAT NOT NULL DEFAULT 0",
    "ALTER TABLE dataset_data ADD COLUMN IF NOT EXISTS bio_chemical_oxygen_demand FLOAT NOT NULL DEFAULT 0",
    "ALTER TABLE dataset_data ADD COLUMN IF NOT EXISTS faecal_streptococci FLOAT NOT NULL DEFAULT 0",
    "ALTER TABLE dataset_data ADD COLUMN IF NOT EXISTS nitrate FLOAT NOT NULL DEFAULT 0",
    "ALTER TABLE dataset_data ADD COLUMN IF NOT EXISTS faecal_coliform FLOAT NOT NULL DEFAULT 0",
    "ALTER TABLE dataset_data ADD COLUMN IF NOT EXISTS total_coliform FLOAT NOT NULL DEFAULT 0",
    "ALTER TABLE dataset_data ADD COLUMN IF NOT EXISTS conductivity FLOAT NOT NULL DEFAULT 0",
    "UPDATE water_data SET conductivity = tds WHERE conductivity = 0",
    "UPDATE water_data SET bio_chemical_oxygen_demand = turbidity WHERE bio_chemical_oxygen_demand = 0",
    "UPDATE dataset_data SET conductivity = tds WHERE conductivity = 0",
    "UPDATE dataset_data SET bio_chemical_oxygen_demand = turbidity WHERE bio_chemical_oxygen_demand = 0",
];

foreach ($queries as $sql) {
    $db->query($sql);
}

echo 'Migration complete.';
