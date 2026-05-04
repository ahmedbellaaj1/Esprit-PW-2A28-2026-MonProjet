<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/stripe.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../Controller/OrderController.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = $_GET['action'] ?? '';

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        
        switch ($action) {
            case 'create_payment_intent':
                handleCreatePaymentIntent($input);
                break;
            case 'confirm_payment':
                handleConfirmPayment($input);
                break;
            default:
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Action inconnue']);
        }
    } else {
        // GET - Récupérer les détails de paiement
        switch ($action) {
            case 'check_config':
                handleCheckConfig();
                break;
            default:
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Action inconnue']);
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}

/**
 * Créer un Payment Intent avec l'API Stripe via cURL
 */
function handleCreatePaymentIntent(array $input): void
{
    // Valider les paramètres
    $amount = filter_var($input['amount'] ?? null, FILTER_VALIDATE_FLOAT);
    $email = filter_var($input['email'] ?? null, FILTER_VALIDATE_EMAIL);
    $description = trim((string) ($input['description'] ?? ''));

    if (!$amount || !$email) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Montant et email requis'
        ]);
        return;
    }

    if ($amount <= 0) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Montant invalide'
        ]);
        return;
    }

    $secretKey = getStripeSecretKey();
    if (str_contains($secretKey, 'YOUR_')) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Clé Stripe non configurée. Veuillez configurer config/stripe.php'
        ]);
        return;
    }

    // Préparer la requête
    $amountInCents = intval($amount * 100);
    $postData = [
        'amount' => $amountInCents,
        'currency' => 'eur',
        'payment_method_types[]' => 'card',
        'receipt_email' => $email,
        'description' => $description,
        'metadata[email]' => $email
    ];

    // Faire la requête à Stripe
    $response = makeStripeRequest('POST', 'https://api.stripe.com/v1/payment_intents', $postData, $secretKey);

    if (!$response || !isset($response['id'])) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => $response['error']['message'] ?? 'Erreur Stripe'
        ]);
        return;
    }

    echo json_encode([
        'ok' => true,
        'client_secret' => $response['client_secret'],
        'payment_intent_id' => $response['id'],
        'amount' => $amount
    ]);
}

/**
 * Confirmer le paiement et créer la commande
 */
function handleConfirmPayment(array $input): void
{
    $paymentIntentId = trim((string) ($input['payment_intent_id'] ?? ''));
    $orderId = filter_var($input['order_id'] ?? null, FILTER_VALIDATE_INT);

    if (!$paymentIntentId || !$orderId) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'ID de paiement et ID de commande requis'
        ]);
        return;
    }

    $secretKey = getStripeSecretKey();

    // Récupérer les détails du paiement depuis Stripe
    $response = makeStripeRequest('GET', "https://api.stripe.com/v1/payment_intents/$paymentIntentId", [], $secretKey);

    if (!$response) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Impossible de vérifier le paiement'
        ]);
        return;
    }

    // Vérifier le statut du paiement
    $status = $response['status'] ?? '';
    
    if ($status === 'succeeded') {
        // Paiement réussi
        try {
            $pdo = getPdo();
            
            // Mettre à jour la commande avec le statut "confirmée"
            $stmt = $pdo->prepare('UPDATE commandes SET statut = ?, methode_paiement = ? WHERE id_commande = ?');
            $stmt->execute(['confirmee', 'stripe', $orderId]);
            
            echo json_encode([
                'ok' => true,
                'message' => 'Paiement confirmé avec succès',
                'status' => 'succeeded'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'error' => 'Erreur lors de la mise à jour de la commande'
            ]);
        }
    } elseif ($status === 'requires_action') {
        // L'utilisateur doit authentifier le paiement
        echo json_encode([
            'ok' => false,
            'status' => 'requires_action',
            'message' => 'Authentification requise'
        ]);
    } else {
        // Paiement échoué
        echo json_encode([
            'ok' => false,
            'status' => $status,
            'message' => 'Le paiement a échoué'
        ]);
    }
}

/**
 * Vérifier la configuration Stripe
 */
function handleCheckConfig(): void
{
    $isConfigured = validateStripeKeys();
    $publicKey = getStripePublicKey();
    
    echo json_encode([
        'ok' => true,
        'configured' => $isConfigured,
        'mode' => STRIPE_MODE,
        'public_key' => $isConfigured ? $publicKey : null,
        'help_text' => $isConfigured ? null : 'Veuillez configurer les clés Stripe dans config/stripe.php'
    ]);
}

/**
 * Faire une requête cURL à l'API Stripe
 */
function makeStripeRequest(string $method, string $url, array $data, string $secretKey): ?array
{
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $secretKey . ':');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if ($method === 'POST' && !empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return null;
    }

    $decoded = json_decode($response, true);
    
    if ($httpCode >= 400) {
        return $decoded;
    }

    return $decoded;
}
