<?php
// scratch/test_ai.php
require_once __DIR__ . '/../config/gemini.php';

function testAI($imagePath) {
    if (!file_exists($imagePath)) {
        echo "Fichier image non trouvé : $imagePath\n";
        return;
    }

    $imageData = file_get_contents($imagePath);
    $mimeType = mime_content_type($imagePath);
    $base64 = base64_encode($imageData);

    $payload = json_encode([
        'contents' => [[
            'parts' => [
                [
                    'text' => 'Analyse cette image. Est-ce qu\'elle montre un ou plusieurs produits alimentaires (nourriture, boissons, fruits, légumes, conserves, épicerie, etc.) ?' .
                              ' Réponds UNIQUEMENT en JSON valide avec ce format exact : {"is_food": true, "label": "description courte en français"}' .
                              ' Si ce n\'est PAS alimentaire, réponds : {"is_food": false, "label": "description courte en français"}'
                ],
                [
                    'inline_data' => [
                        'mime_type' => $mimeType,
                        'data'      => $base64
                    ]
                ]
            ]
        ]]
    ]);

    $url = GEMINI_API_URL . '?key=' . GEMINI_API_KEY;
    echo "URL: $url\n";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false // Pour éviter les problèmes de certificat sur Windows/XAMPP
    ]);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Code: $httpCode\n";
    if ($error) {
        echo "Curl Error: $error\n";
    }
    echo "Response: $response\n";
}

// Test avec logo.png
$image = 'views/assets/images/logo.png';
echo "Test avec : " . $image . "\n";
testAI($image);
