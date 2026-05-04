<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/Review.php';

/**
 * Contrôleur pour les avis et notations
 */
class ReviewController
{
    private Review $reviewModel;

    public function __construct()
    {
        $this->reviewModel = new Review();
    }

    /**
     * Ajouter un nouvel avis (POST)
     */
    public function addReview(): array
    {
        $response = [
            'success' => false,
            'message' => ''
        ];

        // Vérifier la méthode HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $response['message'] = 'Méthode non autorisée';
            return $response;
        }

        // Récupérer les données
        $data = json_decode(file_get_contents('php://input'), true);

        // Validation
        $id_produit = filter_var($data['id_produit'] ?? null, FILTER_VALIDATE_INT);
        $id_utilisateur = filter_var($data['id_utilisateur'] ?? null, FILTER_VALIDATE_INT);
        $note = filter_var($data['note'] ?? null, FILTER_VALIDATE_INT);
        $titre = trim($data['titre'] ?? '');
        $texte = trim($data['texte'] ?? '');

        if (!$id_produit || !$id_utilisateur) {
            $response['message'] = 'ID produit ou utilisateur invalide';
            return $response;
        }

        if (!$note || $note < 1 || $note > 5) {
            $response['message'] = 'Note doit être entre 1 et 5';
            return $response;
        }

        if (strlen($titre) < 5 || strlen($titre) > 150) {
            $response['message'] = 'Le titre doit faire entre 5 et 150 caractères';
            return $response;
        }

        if (strlen($texte) < 10 || strlen($texte) > 1000) {
            $response['message'] = 'Le texte doit faire entre 10 et 1000 caractères';
            return $response;
        }

        // Ajouter l'avis
        if ($this->reviewModel->addReview($id_produit, $id_utilisateur, $note, $titre, $texte)) {
            $response['success'] = true;
            $response['message'] = 'Avis ajouté avec succès! En attente de modération.';
            http_response_code(201);
        } else {
            $response['message'] = 'Erreur lors de l\'ajout de l\'avis';
            http_response_code(500);
        }

        return $response;
    }

    /**
     * Récupérer les avis d'un produit (GET)
     */
    public function getProductReviews(int $id_produit): array
    {
        $page = (int)($_GET['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $reviews = $this->reviewModel->getProductReviews($id_produit, $limit, $offset);
        $stats = $this->reviewModel->getProductRatingStats($id_produit);
        $distribution = $this->reviewModel->getRatingDistribution($id_produit);

        return [
            'success' => true,
            'avis' => $reviews,
            'stats' => $stats,
            'distribution' => $distribution,
            'page' => $page,
            'total' => $stats['nombre_avis']
        ];
    }

    /**
     * Afficher tous les avis (admin)
     */
    public function listAllReviews(): array
    {
        $statut = $_GET['statut'] ?? null;
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $reviews = $this->reviewModel->getAllReviews($statut, $limit, $offset);

        return [
            'success' => true,
            'avis' => $reviews,
            'page' => $page,
            'statut' => $statut
        ];
    }

    /**
     * Approuver un avis (admin)
     */
    public function approveReview(int $id_avis): array
    {
        if ($this->reviewModel->approveReview($id_avis)) {
            return [
                'success' => true,
                'message' => 'Avis approuvé'
            ];
        }
        return [
            'success' => false,
            'message' => 'Erreur lors de l\'approbation'
        ];
    }

    /**
     * Rejeter un avis (admin)
     */
    public function rejectReview(int $id_avis): array
    {
        if ($this->reviewModel->rejectReview($id_avis)) {
            return [
                'success' => true,
                'message' => 'Avis rejeté'
            ];
        }
        return [
            'success' => false,
            'message' => 'Erreur lors du rejet'
        ];
    }

    /**
     * Supprimer un avis (admin)
     */
    public function deleteReview(int $id_avis): array
    {
        if ($this->reviewModel->deleteReview($id_avis)) {
            return [
                'success' => true,
                'message' => 'Avis supprimé'
            ];
        }
        return [
            'success' => false,
            'message' => 'Erreur lors de la suppression'
        ];
    }

    /**
     * Compter les avis en attente
     */
    public function getPendingCount(): array
    {
        return [
            'count' => $this->reviewModel->countPendingReviews()
        ];
    }
}
