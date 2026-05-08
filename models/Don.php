<?php
// models/Don.php — Logique HTTP/API pour les dons

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/DonController.php';

class Don {

    private DonController $controller;

    public function __construct() {
        $this->controller = new DonController();
    }

    public function handle(): void {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        match (true) {
            $method === 'GET'    && $action === 'stats'         => $this->stats(),
            $method === 'GET'    && $action === 'one'           => $this->getOne(),
            $method === 'GET'                                   => $this->index(),
            $method === 'POST'   && $action === 'verify-image'  => $this->verifyImageUpload(),
            $method === 'POST'                                  => $this->store(),
            $method === 'PUT'    && $action === 'statut'        => $this->updateStatut(),
            $method === 'PUT'                                   => $this->update(),
            $method === 'DELETE'                                => $this->destroy(),
            default => $this->jsonError('Méthode non autorisée', 405)
        };
    }

    private function index(): void {
        $statut = $_GET['statut'] ?? '';
        $search = $_GET['search'] ?? '';
        $dons   = $this->controller->getAll($statut, $search);
        echo json_encode(['success' => true, 'data' => $dons]);
    }

    private function stats(): void {
        $stats = $this->controller->getStats();
        $total = array_sum($stats);
        echo json_encode([
            'success' => true,
            'data'    => array_merge($stats, ['total' => $total])
        ]);
    }

    private function getOne(): void {
        $id  = (int)($_GET['id'] ?? 0);
        $don = $this->controller->getById($id);
        if (!$don) { $this->jsonError('Don introuvable', 404); return; }
        echo json_encode(['success' => true, 'data' => $don]);
    }

    private function store(): void {
        $data = $this->getBody();

        if (empty($data['produits']) || !is_array($data['produits'])) {
            $this->jsonError('Le champ produits (tableau) est requis.', 400); return;
        }
        foreach ($data['produits'] as $p) {
            if (empty($p['nom_produit']) || empty($p['quantite']) || empty($p['date_peremption'])) {
                $this->jsonError('Chaque produit doit avoir : nom_produit, quantite, date_peremption.', 400);
                return;
            }
            // Le nom du produit ne doit contenir que des lettres (espaces, tirets et apostrophes autorisés)
            if (!preg_match('/^[\p{L}\s\-\'\'.]+$/u', trim($p['nom_produit']))) {
                $this->jsonError('Le nom du produit ne doit contenir que des caractères alphabétiques.', 400);
                return;
            }
            // Vérifier que la date de péremption n'est pas avant la date actuelle
            $date_peremption = new DateTime($p['date_peremption']);
            $date_publication = new DateTime();
            if ($date_peremption < $date_publication) {
                $this->jsonError('La date de péremption doit être après la date de publication.', 400);
                return;
            }
        }

        $id = $this->controller->create($data);
        if ($id === false) { $this->jsonError('Erreur lors de la création.', 500); return; }

        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Don créé.', 'id_don' => $id]);
    }

    private function update(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $data = $this->getBody();

        if (!$id) { $this->jsonError('id requis.', 400); return; }
        if (empty($data['produits'])) { $this->jsonError('produits requis.', 400); return; }

        // Vérifier que la date de péremption n'est pas avant la date actuelle
        foreach ($data['produits'] as $p) {
            if (empty($p['date_peremption'])) {
                $this->jsonError('date_peremption requise pour chaque produit.', 400);
                return;
            }
            // Le nom du produit ne doit contenir que des lettres (espaces, tirets et apostrophes autorisés)
            if (!empty($p['nom_produit']) && !preg_match('/^[\p{L}\s\-\'\'.]+$/u', trim($p['nom_produit']))) {
                $this->jsonError('Le nom du produit ne doit contenir que des caractères alphabétiques.', 400);
                return;
            }
            $date_peremption = new DateTime($p['date_peremption']);
            $date_publication = new DateTime();
            if ($date_peremption < $date_publication) {
                $this->jsonError('La date de péremption doit être après la date de publication.', 400);
                return;
            }
        }

        $ok = $this->controller->update($id, $data);
        if (!$ok) { $this->jsonError('Erreur lors de la mise à jour.', 500); return; }
        echo json_encode(['success' => true, 'message' => 'Don modifié.']);
    }

    private function updateStatut(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $data = $this->getBody();
        if (!$id || empty($data['statut'])) { $this->jsonError('id et statut requis.', 400); return; }

        $ok = $this->controller->updateStatut($id, $data['statut'], $data['id_partenaire'] ?? null);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Statut mis à jour.' : 'Erreur.']);
    }

    private function destroy(): void {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { $this->jsonError('id requis.', 400); return; }

        $ok = $this->controller->delete($id);
        if (!$ok) {
            $this->jsonError('Impossible : don déjà réservé/récupéré ou introuvable.', 403);
            return;
        }
        echo json_encode(['success' => true, 'message' => 'Don supprimé.']);
    }

    private function verifyImageUpload(): void {
        if (empty($_FILES['image'])) {
            $this->jsonError('Aucun fichier reçu.', 400);
            return;
        }

        $file     = $_FILES['image'];
        $allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowed, true)) {
            $this->jsonError('Format non supporté. Utilisez JPEG, PNG ou WebP.', 415);
            return;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $this->jsonError('Image trop lourde (max 5 Mo).', 413);
            return;
        }

        $imageData = file_get_contents($file['tmp_name']);
        $result    = $this->controller->verifyImage($imageData, $mimeType);

        $message = $result['is_food']
            ? '✅ Produit alimentaire confirmé : ' . $result['label']
            : '❌ Produit non alimentaire : ' . $result['label'];

        echo json_encode([
            'success'  => true,
            'is_food'  => $result['is_food'],
            'label'    => $result['label'],
            'message'  => $message
        ]);
    }

    private function getBody(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function jsonError(string $msg, int $code = 400): void {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $msg]);
    }
}

// Point d'entrée : exécution de l'API
(new Don())->handle();
