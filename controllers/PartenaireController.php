<?php
// controllers/PartenaireController.php

require_once __DIR__ . '/../models/Partenaire.php';

class PartenaireController {

    private Partenaire $model;

    public function __construct() {
        $this->model = new Partenaire();
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

    /* ── GET /api/partenaires.php ─────────────────────────── */
    private function index(): void {
        $type   = $_GET['type']   ?? '';
        $search = $_GET['search'] ?? '';
        $parts  = $this->model->getAll($type, $search);

        // Ajouter le nombre de dons liés à chaque partenaire
        foreach ($parts as &$p) {
            $p['nb_dons'] = $this->model->countDons((int)$p['id_partenaire']);
        }
        echo json_encode(['success' => true, 'data' => $parts]);
    }

    /* ── POST /api/partenaires.php ───────────────────────── */
    private function store(): void {
        $data = $this->getBody();
        if (empty($data['nom']) || empty($data['type'])) {
            $this->jsonError('nom et type sont requis.', 400); return;
        }
        $id = $this->model->create($data);
        if ($id === false) { $this->jsonError('Erreur lors de la création.', 500); return; }

        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Partenaire ajouté.', 'id_partenaire' => $id]);
    }

    /* ── PUT /api/partenaires.php?id=X ──────────────────── */
    private function update(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $data = $this->getBody();
        if (!$id || empty($data['nom'])) { $this->jsonError('id et nom requis.', 400); return; }

        $ok = $this->model->update($id, $data);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Partenaire modifié.' : 'Erreur.']);
    }

    /* ── DELETE /api/partenaires.php?id=X ───────────────── */
    private function destroy(): void {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { $this->jsonError('id requis.', 400); return; }

        $result = $this->model->delete($id);
        if (is_string($result)) { $this->jsonError($result, 409); return; }
        echo json_encode(['success' => true, 'message' => 'Partenaire supprimé.']);
    }

    /* ── Helpers ─────────────────────────────────────────── */
    private function getBody(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function jsonError(string $msg, int $code = 400): void {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $msg]);
    }
}
