<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

function generateCaptchaCode(int $length = 6): string
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $code = '';
    
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    return $code;
}

function generateCaptchaSVG(string $code): string
{
    $width = 250;
    $height = 80;
    
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">';
    
    $svg .= '<rect width="' . $width . '" height="' . $height . '" fill="white" stroke="#cbd5e1" stroke-width="2"/>';
    
    for ($i = 0; $i < 8; $i++) {
        $x1 = random_int(0, $width);
        $y1 = random_int(0, $height);
        $x2 = random_int(0, $width);
        $y2 = random_int(0, $height);
        $color = sprintf('#%02X%02X%02X', random_int(180, 220), random_int(180, 220), random_int(180, 220));
        $svg .= '<line x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2 . '" y2="' . $y2 . '" stroke="' . $color . '" stroke-width="1" opacity="0.5"/>';
    }
    
    for ($i = 0; $i < 50; $i++) {
        $x = random_int(0, $width);
        $y = random_int(0, $height);
        $color = sprintf('#%02X%02X%02X', random_int(180, 220), random_int(180, 220), random_int(180, 220));
        $svg .= '<circle cx="' . $x . '" cy="' . $y . '" r="1" fill="' . $color . '" opacity="0.6"/>';
    }
    
    $textX = 15;
    $charWidth = ($width - 30) / strlen($code);
    
    for ($i = 0; $i < strlen($code); $i++) {
        $char = $code[$i];
        $x = $textX + ($i * $charWidth) + ($charWidth / 2);
        $y = $height / 2 + 15;
        $rotation = random_int(-15, 15);
        $color = sprintf('#%02X%02X%02X', random_int(0, 80), random_int(0, 80), random_int(0, 80));
        $offsetY = random_int(-8, 8);
        
        $svg .= '<text x="' . $x . '" y="' . ($y + $offsetY) . '" font-size="36" font-weight="bold" ';
        $svg .= 'fill="' . $color . '" text-anchor="middle" ';
        $svg .= 'transform="rotate(' . $rotation . ' ' . $x . ' ' . ($y + $offsetY) . ')" ';
        $svg .= 'font-family="Arial, sans-serif" letter-spacing="2">';
        $svg .= htmlspecialchars($char);
        $svg .= '</text>';
    }
    
    $svg .= '</svg>';
    
    return $svg;
}

$action = $_POST['action'] ?? '';

if ($action === 'refresh-captcha') {
    // Générer un nouveau code CAPTCHA
    $_SESSION['captcha_code'] = generateCaptchaCode();
    
    // Générer le SVG
    $svg = generateCaptchaSVG($_SESSION['captcha_code']);
    
    echo json_encode([
        'success' => true,
        'code' => $_SESSION['captcha_code'],
        'svg' => $svg,
        'dataAttr' => htmlspecialchars($_SESSION['captcha_code'])
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid action'
    ]);
}
