<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/ChatBot.php';

/**
 * Contrôleur ChatBot IA
 * Gère les interactions du chat et les recommandations
 */
class ChatBotController
{
    private ChatBot $chatbot;

    public function __construct()
    {
        $this->chatbot = new ChatBot();
    }

    /**
     * Traiter un nouveau message
     */
    public function handleMessage(): array
    {
        $response = [
            'success' => false,
            'message' => '',
            'recommendations' => [],
            'bot_response' => ''
        ];

        // Récupérer les données
        $data = json_decode(file_get_contents('php://input'), true);

        $id_conversation = filter_var($data['id_conversation'] ?? null, FILTER_VALIDATE_INT);
        $id_utilisateur = filter_var($data['id_utilisateur'] ?? null, FILTER_VALIDATE_INT);
        $message_text = trim($data['message'] ?? '');

        // Validation
        if (!$id_conversation && !$id_utilisateur) {
            http_response_code(400);
            $response['message'] = 'ID conversation ou utilisateur requis';
            return $response;
        }

        if (strlen($message_text) < 2) {
            $response['message'] = 'Message trop court';
            return $response;
        }

        if (strlen($message_text) > 2000) {
            $response['message'] = 'Message trop long';
            return $response;
        }

        // Créer conversation si nécessaire
        if (!$id_conversation) {
            $id_conversation = $this->chatbot->createConversation($id_utilisateur);
            if (!$id_conversation) {
                http_response_code(500);
                $response['message'] = 'Erreur création conversation';
                return $response;
            }
        }

        // Ajouter le message utilisateur
        $id_message = $this->chatbot->addMessage($id_conversation, 'utilisateur', $message_text);

        if (!$id_message) {
            http_response_code(500);
            $response['message'] = 'Erreur enregistrement message';
            return $response;
        }

        // Analyser le message
        $analysis = $this->chatbot->analyzeMessage($message_text);

        // Générer la réponse du bot
        $bot_response = $this->chatbot->generateResponse($message_text, $analysis);

        // Ajouter la réponse du bot
        $id_bot_message = $this->chatbot->addMessage($id_conversation, 'bot', $bot_response);

        // Obtenir les recommandations
        $recommendations = $this->chatbot->getRecommendations($message_text, $id_message, 5);

        // Succès
        $response['success'] = true;
        $response['message'] = 'Message traité';
        $response['id_conversation'] = $id_conversation;
        $response['id_message'] = $id_message;
        $response['bot_response'] = $bot_response;
        $response['recommendations'] = $recommendations;
        $response['analysis'] = [
            'intent' => $analysis['intent'],
            'confiance' => $analysis['confiance'] ?? 0.5,
            'has_search_keywords' => $analysis['has_search_keywords'] ?? false,
            'has_diet_criteria' => $analysis['has_diet_criteria'] ?? false,
            'has_budget_criteria' => $analysis['has_budget_criteria'] ?? false,
            'search_keywords' => $analysis['search_keywords'] ?? [],
            'diet_type' => $analysis['diet_type'] ?? null,
            'found_products_count' => count($recommendations)
        ];

        http_response_code(200);
        return $response;
    }

    /**
     * Récupérer l'historique de la conversation
     */
    public function getConversation(int $id_conversation): array
    {
        try {
            $messages = $this->chatbot->getConversationHistory($id_conversation);
            return [
                'success' => true,
                'messages' => $messages,
                'message_count' => count($messages)
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération',
                'messages' => []
            ];
        }
    }

    /**
     * Ajouter un produit au panier depuis une recommandation
     */
    public function addRecommendationToCart(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id_recommendation = filter_var($data['id_recommendation'] ?? null, FILTER_VALIDATE_INT);

        if (!$id_recommendation) {
            return ['success' => false, 'message' => 'ID recommandation invalide'];
        }

        $success = $this->chatbot->trackAddToCart($id_recommendation);

        return [
            'success' => $success,
            'message' => $success ? 'Produit ajouté au panier' : 'Erreur lors de l\'ajout'
        ];
    }

    /**
     * Créer une nouvelle conversation
     */
    public function startNewConversation(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id_utilisateur = filter_var($data['id_utilisateur'] ?? null, FILTER_VALIDATE_INT);

        if (!$id_utilisateur) {
            return ['success' => false, 'message' => 'ID utilisateur requis'];
        }

        $id_conversation = $this->chatbot->createConversation($id_utilisateur);

        return [
            'success' => $id_conversation > 0,
            'id_conversation' => $id_conversation,
            'message' => 'Nouvelle conversation créée'
        ];
    }

    /**
     * Obtenir les suggestions rapides
     */
    public function getQuickSuggestions(): array
    {
        return [
            'success' => true,
            'suggestions' => [
                [
                    'text' => '🥗 Produits bio',
                    'query' => 'Je cherche des produits bio'
                ],
                [
                    'text' => '🌱 Vegan',
                    'query' => 'J\'aimerais des produits vegan'
                ],
                [
                    'text' => '💪 Haute protéine',
                    'query' => 'Recommande-moi des produits riches en protéines'
                ],
                [
                    'text' => '🥜 Sans allergie',
                    'query' => 'Je suis allergique aux cacahuètes'
                ],
                [
                    'text' => '💰 Petit budget',
                    'query' => 'Montre-moi les meilleurs produits pas chers'
                ],
                [
                    'text' => '❓ Aide',
                    'query' => 'Comment utiliser le chat?'
                ]
            ]
        ];
    }
}
