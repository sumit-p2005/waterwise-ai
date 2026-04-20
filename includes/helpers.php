<?php
declare(strict_types=1);

function clamp(float $value, float $min, float $max): float
{
    return max($min, min($max, $value));
}

function normalizeSample(array $input): array
{
    return [
        'temperature' => (float)($input['temperature'] ?? 0),
        'do_level' => (float)($input['do_level'] ?? $input['dissolved_oxygen'] ?? 0),
        'ph' => (float)($input['ph'] ?? 0),
        'bio_chemical_oxygen_demand' => (float)($input['bio_chemical_oxygen_demand'] ?? $input['turbidity'] ?? 0),
        'faecal_streptococci' => (float)($input['faecal_streptococci'] ?? 0),
        'nitrate' => (float)($input['nitrate'] ?? 0),
        'faecal_coliform' => (float)($input['faecal_coliform'] ?? 0),
        'total_coliform' => (float)($input['total_coliform'] ?? 0),
        'conductivity' => (float)($input['conductivity'] ?? $input['tds'] ?? 0),
    ];
}

function calculateWqiFromSample(array $sample): float
{
    $s = normalizeSample($sample);

    $qTemperature = clamp((abs($s['temperature'] - 25.0) / 15.0) * 100.0, 0, 300);
    $qDo = clamp(((14.6 - $s['do_level']) / 14.6) * 100.0, 0, 300);
    $qPh = clamp((abs($s['ph'] - 7.0) / 1.5) * 100.0, 0, 300);
    $qBod = clamp(($s['bio_chemical_oxygen_demand'] / 6.0) * 100.0, 0, 300);
    $qFs = clamp(($s['faecal_streptococci'] / 500.0) * 100.0, 0, 300);
    $qNitrate = clamp(($s['nitrate'] / 45.0) * 100.0, 0, 300);
    $qFc = clamp(($s['faecal_coliform'] / 500.0) * 100.0, 0, 300);
    $qTc = clamp(($s['total_coliform'] / 1000.0) * 100.0, 0, 300);
    $qConductivity = clamp(($s['conductivity'] / 1500.0) * 100.0, 0, 300);

    $weights = [
        'temperature' => 0.06,
        'do' => 0.20,
        'ph' => 0.12,
        'bod' => 0.12,
        'fs' => 0.10,
        'nitrate' => 0.10,
        'fc' => 0.12,
        'tc' => 0.10,
        'conductivity' => 0.08,
    ];

    $wqi = (
        $weights['temperature'] * $qTemperature +
        $weights['do'] * $qDo +
        $weights['ph'] * $qPh +
        $weights['bod'] * $qBod +
        $weights['fs'] * $qFs +
        $weights['nitrate'] * $qNitrate +
        $weights['fc'] * $qFc +
        $weights['tc'] * $qTc +
        $weights['conductivity'] * $qConductivity
    );

    return round($wqi, 2);
}

function calculateWqi(float $ph, float $tds, float $doLevel, float $turbidity, float $temperature): float
{
    return calculateWqiFromSample([
        'ph' => $ph,
        'conductivity' => $tds,
        'do_level' => $doLevel,
        'bio_chemical_oxygen_demand' => $turbidity,
        'temperature' => $temperature,
    ]);
}

function classifyWqi(float $wqi): array
{
    if ($wqi <= 50) {
        return ['label' => 'Excellent', 'color' => 'emerald', 'message' => 'Safe to drink with standard handling.'];
    }
    if ($wqi <= 100) {
        return ['label' => 'Good', 'color' => 'sky', 'message' => 'Minor filtration is recommended.'];
    }
    if ($wqi <= 200) {
        return ['label' => 'Poor', 'color' => 'amber', 'message' => 'Use RO/boiling before consumption.'];
    }
    return ['label' => 'Unsafe', 'color' => 'rose', 'message' => 'Avoid consumption and treat immediately.'];
}

function recommendationFromWqi(float $wqi): array
{
    $class = classifyWqi($wqi);
    $tips = [
        'Excellent' => ['Use clean storage containers.', 'Test monthly to maintain quality.', 'Ideal for domestic consumption.'],
        'Good' => ['Use activated carbon or UV filtration.', 'Flush tanks weekly.', 'Retest within 7-14 days.'],
        'Poor' => ['Use RO + boiling for drinking.', 'Investigate contamination source.', 'Increase sampling frequency.'],
        'Unsafe' => ['Do not drink this water.', 'Deploy emergency treatment immediately.', 'Notify local authority and test source points.'],
    ];

    return [
        'status' => $class['label'],
        'message' => $class['message'],
        'tips' => $tips[$class['label']] ?? [],
    ];
}

function readJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return $_POST;
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $_POST;
}

function knnPredictWqi(mysqli $db, array $input, int $k = 5): array
{
    $k = max(1, min(20, $k));
    $sample = normalizeSample($input);

    $rows = $db->query('SELECT temperature, do_level, ph, bio_chemical_oxygen_demand, faecal_streptococci, nitrate, faecal_coliform, total_coliform, conductivity, wqi FROM dataset_data')->fetch_all(MYSQLI_ASSOC);

    if (!$rows) {
        return ['predicted_wqi' => 0.0, 'neighbors' => [], 'count' => 0];
    }

    $distances = [];
    foreach ($rows as $row) {
        $d = sqrt(
            (($sample['temperature'] - (float)$row['temperature']) ** 2) +
            (($sample['do_level'] - (float)$row['do_level']) ** 2) +
            (($sample['ph'] - (float)$row['ph']) ** 2) +
            (($sample['bio_chemical_oxygen_demand'] - (float)$row['bio_chemical_oxygen_demand']) ** 2) +
            (($sample['faecal_streptococci'] - (float)$row['faecal_streptococci']) ** 2) +
            (($sample['nitrate'] - (float)$row['nitrate']) ** 2) +
            (($sample['faecal_coliform'] - (float)$row['faecal_coliform']) ** 2) +
            (($sample['total_coliform'] - (float)$row['total_coliform']) ** 2) +
            (($sample['conductivity'] - (float)$row['conductivity']) ** 2)
        );

        $row['distance'] = $d;
        $distances[] = $row;
    }

    usort($distances, static fn(array $a, array $b): int => $a['distance'] <=> $b['distance']);
    $neighbors = array_slice($distances, 0, $k);

    $sum = 0.0;
    foreach ($neighbors as $n) {
        $sum += (float)$n['wqi'];
    }

    return [
        'predicted_wqi' => round($sum / max(count($neighbors), 1), 2),
        'neighbors' => $neighbors,
        'count' => count($rows),
    ];
}

function jsonResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}
