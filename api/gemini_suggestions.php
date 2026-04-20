<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Method not allowed'], 405);
}

$data = readJsonInput();
$wqi = isset($data['wqi']) ? (float)$data['wqi'] : 0.0;
$status = trim((string)($data['status'] ?? ''));
$city = trim((string)($data['city'] ?? ''));
$state = trim((string)($data['state'] ?? ''));
$notes = trim((string)($data['notes'] ?? ''));

$apiKey = getenv('GEMINI_API_KEY') ?: '';
if ($apiKey === '') {
    jsonResponse([
        'ok' => false,
        'message' => 'GEMINI_API_KEY is not configured on server.',
    ], 500);
}

$model = getenv('GEMINI_MODEL') ?: 'gemini-2.5-flash';
$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent';

$prompt = "You are a water quality advisory assistant.\n" .
    "Return concise recommendations in 4 bullet points.\n" .
    "Input:\n" .
    "- WQI: {$wqi}\n" .
    "- Status: {$status}\n" .
    "- City: {$city}\n" .
    "- State: {$state}\n" .
    "- Notes: {$notes}\n" .
    "Focus on: drinking safety, treatment steps, monitoring frequency, and community action.";

$body = [
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt],
            ],
        ],
    ],
    'generationConfig' => [
        'temperature' => 0.4,
        'maxOutputTokens' => 300,
    ],
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'x-goog-api-key: ' . $apiKey,
    ],
    CURLOPT_POSTFIELDS => json_encode($body),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 25,
]);

$response = curl_exec($ch);
if ($response === false) {
    $err = curl_error($ch);
    curl_close($ch);
    jsonResponse(['ok' => false, 'message' => 'Gemini request failed: ' . $err], 502);
}

$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$decoded = json_decode($response, true);
if ($httpCode >= 400) {
    $msg = $decoded['error']['message'] ?? 'Gemini API error';
    jsonResponse(['ok' => false, 'message' => $msg], $httpCode);
}

$text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
if ($text === '') {
    jsonResponse(['ok' => false, 'message' => 'No suggestion text returned from Gemini.'], 502);
}

jsonResponse([
    'ok' => true,
    'suggestion' => $text,
    'model' => $model,
]);
