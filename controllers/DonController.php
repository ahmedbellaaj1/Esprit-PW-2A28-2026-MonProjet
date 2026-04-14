<?php
// controllers/DonController.php

require_once __DIR__ . '/../models/Don.php';
require_once __DIR__ . '/../models/Partenaire.php';

class DonController {

    private Don        $donModel;
    private Partenaire $partModel;

    public function __construct() {
        $this->donModel  = new Don();
        $this->partModel = new Partenaire();
    }

    /* ============================================================
       Méthode principale : dispatch selon method + action
       ============================================================ */
    public function handle(): void {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        match (true) {
            $method === 'GET'    && $action === 'stats'    => $this->stats(),
            $method === 'GET'    && $action === 'one'      => $this->getOne(),
            $method === 'GET'                              => $this->index(),
            $method === 'POST'                             => $this->store(),
            $method === 'PUT'    && $action === 'statut'   => $this->updateStatut(),
            $method === 'PUT'                              => $this->update(),
            $method === 'DELETE'                           => $this->destroy(),
            default => $this->jsonError('Méthode non autorisée', 405)
        };
    }

    /* ============================================================
       GET /api/dons.php          → liste des dons
       GET /api/dons.php?action=stats → statistiques
       GET /api/dons.php?action=one&id=X → un seul don
       ============================================================ */
    private function index(): void {
        $statut = $_GET['statut'] ?? '';
        $search = $_GET['search'] ?? '';
        $dons   = $this->donModel->getAll($statut, $search);
        echo json_encode(['success' => true, 'data' => $dons]);
    }

    private function stats(): void {
        $stats = $this->donModel->getStats();
        $total = array_sum($stats);
        echo json_encode([
            'success' => true,
            'data'    => array_merge($stats, ['total' => $total])
        ]);
    }

    private function getOne(): void {
        $id  = (int)($_GET['id'] ?? 0);
        $don = $this->donModel->getById($id);
        if (!$don) { $this->jsonError('Don introuvable', 404); return; }
        echo json_encode(['success' => true, 'data' => $don]);
    }

    /* ============================================================
       POST /api/dons.php
       Body JSON : { statut, id_user, id_partenaire, produits:[...] }
       ============================================================ */
    private function store(): void {
        $data = $this->getBody();

        // Validation
        if (empty($data['produits']) || !is_array($data['produits'])) {
            $this->jsonError('Le champ produits (tableau) est requis.', 400); return;
        }
        foreach ($data['produits'] as $p) {
            if (empty($p['nom_produit']) || empty($p['quantite']) || empty($p['date_peremption'])) {
                $this->jsonError('Chaque produit doit avoir : nom_produit, quantite, date_peremption.', 400);
                return;
            }
        }

        $id = $this->donModel->create($data);
        if ($id === false) { $this->jsonError('Erreur lors de la création.', 500); return; }

        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Don créé.', 'id_don' => $id]);
    }

    /* ============================================================
       PUT /api/dons.php?id=X
       Body JSON : { statut, id_partenaire, produits:[...] }
       ============================================================ */
    private function update(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $data = $this->getBody();

        if (!$id) { $this->jsonError('id requis.', 400); return; }
        if (empty($data['produits'])) { $this->jsonError('produits requis.', 400); return; }

        $ok = $this->donModel->update($id, $data);
        if (!$ok) { $this->jsonError('Erreur lors de la mise à jour.', 500); return; }
        echo json_encode(['success' => true, 'message' => 'Don modifié.']);
    }

    /* ============================================================
       PUT /api/dons.php?action=statut&id=X
       Body JSON : { statut, id_partenaire? }
       ============================================================ */
    private function updateStatut(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $data = $this->getBody();
        if (!$id || empty($data['statut'])) { $this->jsonError('id et statut requis.', 400); return; }

        $ok = $this->donModel->updateStatut($id, $data['statut'], $data['id_partenaire'] ?? null);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Statut mis à jour.' : 'Erreur.']);
    }

    /* ============================================================
       DELETE /api/dons.php?id=X
       ============================================================ */
    private function destroy(): void {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { $this->jsonError('id requis.', 400); return; }

        $ok = $this->donModel->delete($id);
        if (!$ok) {
            $this->jsonError('Impossible : don déjà réservé/récupéré ou introuvable.', 403);
            return;
        }
        echo json_encode(['success' => true, 'message' => 'Don supprimé.']);
    }

    /* ── Helpers ─────────────────────────────────────────────── */
    private function getBody(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function jsonError(string $msg, int $code = 400): void {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $msg]);
    }
}
