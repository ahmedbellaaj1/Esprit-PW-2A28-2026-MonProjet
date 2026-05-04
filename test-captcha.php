<?php
// Test file pour vérifier les endpoints CAPTCHA
session_start();

// Générer un code test
$_SESSION['captcha_code'] = 'ABC123';

echo "CAPTCHA Code stored in session: " . $_SESSION['captcha_code'] . "\n";
echo "Testing CAPTCHA endpoints...\n\n";

// Test 1: Test captcha.php
echo "1. Testing captcha.php (SVG generation):\n";
ob_start();
include 'Controller/captcha.php';
$output = ob_get_clean();
$isValidSVG = strpos($output, '<svg') !== false;
echo "   - SVG Generated: " . ($isValidSVG ? "YES ✓" : "NO ✗") . "\n";
echo "   - Output size: " . strlen($output) . " bytes\n";
if (!$isValidSVG) {
    echo "   - First 200 chars: " . substr($output, 0, 200) . "\n";
}

// Regenérer le code pour le test audio
$_SESSION['captcha_code'] = 'ABC123';

// Test 2: Test captcha-sound.php
echo "\n2. Testing captcha-sound.php (WAV generation):\n";
ob_start();
include 'Controller/captcha-sound.php';
$output = ob_get_clean();
$isValidWAV = strpos($output, 'RIFF') === 0;
echo "   - WAV Generated: " . ($isValidWAV ? "YES ✓" : "NO ✗") . "\n";
echo "   - Output size: " . strlen($output) . " bytes\n";
if (!$isValidWAV) {
    echo "   - First 20 chars: " . bin2hex(substr($output, 0, 20)) . "\n";
}

echo "\nTest completed!\n";
?>
