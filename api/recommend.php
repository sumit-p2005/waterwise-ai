<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

$wqi = isset($_GET['wqi']) ? (float)$_GET['wqi'] : (float)((readJsonInput()['wqi'] ?? 0));
$rec = recommendationFromWqi($wqi);
jsonResponse(['ok' => true, 'wqi' => $wqi, 'recommendation' => $rec]);
