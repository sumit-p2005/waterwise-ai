<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/fpdf.php';

$sample = normalizeSample($_GET);

$wqi = calculateWqiFromSample($sample);
$prediction = knnPredictWqi(getDb(), $sample, 5);
$predWqi = (float)$prediction['predicted_wqi'];
$rec = recommendationFromWqi($predWqi);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(0, 8, 'WaterWise AI - Water Quality Report', 0, 1);
$pdf->Cell(0, 8, 'Generated: ' . date('Y-m-d H:i:s'), 0, 1);
$pdf->Cell(0, 8, '--------------------------------------------', 0, 1);
$pdf->Cell(0, 8, 'Temperature: ' . $sample['temperature'], 0, 1);
$pdf->Cell(0, 8, 'DO: ' . $sample['do_level'], 0, 1);
$pdf->Cell(0, 8, 'pH: ' . $sample['ph'], 0, 1);
$pdf->Cell(0, 8, 'BOD: ' . $sample['bio_chemical_oxygen_demand'], 0, 1);
$pdf->Cell(0, 8, 'Faecal Streptococci: ' . $sample['faecal_streptococci'], 0, 1);
$pdf->Cell(0, 8, 'Nitrate: ' . $sample['nitrate'], 0, 1);
$pdf->Cell(0, 8, 'Faecal Coliform: ' . $sample['faecal_coliform'], 0, 1);
$pdf->Cell(0, 8, 'Total Coliform: ' . $sample['total_coliform'], 0, 1);
$pdf->Cell(0, 8, 'Conductivity: ' . $sample['conductivity'], 0, 1);
$pdf->Cell(0, 8, '--------------------------------------------', 0, 1);
$pdf->Cell(0, 8, 'Calculated WQI: ' . $wqi, 0, 1);
$pdf->Cell(0, 8, 'Predicted WQI (KNN): ' . $predWqi, 0, 1);
$pdf->Cell(0, 8, 'Recommendation: ' . $rec['status'] . ' - ' . $rec['message'], 0, 1);

$pdf->Output('I', 'waterwise_report.pdf');
