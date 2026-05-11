<?php
// Model/PartenaireApi.php — Point d'entrée REST JSON pour les partenaires
// Adapté depuis le projet Dons pour Green-Bite

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Controller/PartenaireController.php';

class PartenaireApi {

    private PartenaireController $controller;

    public function __construct() {
        $this->controller = new PartenaireController();
    }

    public function handle(): void {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

        $method = $_SERVER['REQUEST_METHOD'];

        match ($method) {
            'GET'    => $this->index(),
            'POST'   => $this->store(),
            'PUT'    => $this->update(),
            'DELETE' => $this->destroy(),
            default  => $this->jsonError('Méthode non autorisée', 405)
        };
    }

    private function index(): void {
        $action = $_GET['action'] ?? '';

        // Action spéciale pour les meilleurs partenaires
        if ($action === 'top') {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
            $topPartenaires = $this->controller->getTop($limit);
            echo json_encode(['success' => true, 'data' => $topPartenaires]);
            return;
        }

        $type   = $_GET['type']   ?? '';
        $search = $_GET['search'] ?? '';
        $parts  = $this->controller->getAll($type, $search);

        foreach ($parts as &$p) {
            $p['nb_dons'] = $this->controller->countDons((int)$p['id_partenaire']);
        }
        echo json_encode(['success' => true, 'data' => $parts]);
    }

    private function store(): void {
        $data = $this->getBody();

        foreach (['nom', 'type', 'adresse', 'telephone', 'email'] as $field) {
            if (empty($data[$field])) {
                $this->jsonError('Le champ ' . $field . ' est obligatoire.', 400); return;
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->jsonError("Format d'email invalide (ex: contact@example.com).", 400); return;
        }

        if (!$this->isValidPhone($data['telephone'])) {
            $this->jsonError('Le numéro doit contenir exactement 8 chiffres, avec indicatif pays optionnel (ex: +216 20123456).', 400); return;
        }

        $id = $this->controller->create($data);
        if ($id === false) { $this->jsonError('Erreur lors de la création.', 500); return; }

        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Partenaire ajouté.', 'id_partenaire' => $id]);
    }

    private function update(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $data = $this->getBody();
        if (!$id) { $this->jsonError('id requis.', 400); return; }

        foreach (['nom', 'type', 'adresse', 'telephone', 'email'] as $field) {
            if (empty($data[$field])) {
                $this->jsonError('Le champ ' . $field . ' est obligatoire.', 400); return;
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->jsonError("Format d'email invalide (ex: contact@example.com).", 400); return;
        }

        if (!$this->isValidPhone($data['telephone'])) {
            $this->jsonError('Le numéro doit contenir exactement 8 chiffres, avec indicatif pays optionnel (ex: +216 20123456).', 400); return;
        }

        $ok = $this->controller->update($id, $data);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Partenaire modifié.' : 'Erreur.']);
    }

    private function destroy(): void {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { $this->jsonError('id requis.', 400); return; }

        $result = $this->controller->delete($id);
        if (is_string($result)) { $this->jsonError($result, 409); return; }
        echo json_encode(['success' => true, 'message' => 'Partenaire supprimé.']);
    }

    private function getBody(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function jsonError(string $msg, int $code = 400): void {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $msg]);
    }

    private function isValidPhone(string $phone): bool {
        $stripped = preg_replace('/[\s\-\.]/', '', $phone);
        $digitsOnly = preg_replace('/^\+\d{1,3}/', '', $stripped);
        return preg_match('/^\d{8}$/', $digitsOnly) === 1;
    }
}

// Point d'entrée : exécution de l'API
(new PartenaireApi())->handle();
