<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/Order.php';
require_once __DIR__ . '/../config/database.php';

final class OrderController
{
    private PDO $pdo;
    private const ALLOWED_STATUS = ['en-cours', 'en-preparation', 'confirmee', 'livree', 'annulee'];
    private const ALLOWED_DELIVERY_MODES = ['standard', 'express'];

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    // ========== Database Operations ==========

    public function list(array $filters = []): array
    {
        $q = trim((string) ($filters['q'] ?? ''));
        $idUser = trim((string) ($filters['id_user'] ?? ''));
        $filterStatus = trim((string) ($filters['status'] ?? ''));
        $filterDateFrom = trim((string) ($filters['date_from'] ?? ''));
        $filterDateTo = trim((string) ($filters['date_to'] ?? ''));
        
        $where = [];
        $params = [];
        
        // Recherche par texte ou ID commande
        if ($q !== '') {
            if (ctype_digit($q)) {
                // Recherche par ID commande
                $id = (int) $q;
                $where[] = 'c.id_commande = ?';
                $params[] = $id;
            } else {
                // Recherche par nom ou marque de produit
                $search = '%' . $q . '%';
                $where[] = '(p.nom LIKE ? OR p.marque LIKE ?)';
                $params[] = $search;
                $params[] = $search;
            }
        }
        
        // Filtre par ID utilisateur
        if ($idUser !== '' && ctype_digit($idUser)) {
            $where[] = 'c.id_utilisateur = ?';
            $params[] = (int) $idUser;
        }
        
        // Filtre par statut
        if ($filterStatus !== '') {
            $where[] = 'c.statut = ?';
            $params[] = $filterStatus;
        }
        
        // Filtre par date (depuis)
        if ($filterDateFrom !== '') {
            $where[] = 'DATE(c.date_commande) >= ?';
            $params[] = $filterDateFrom;
        }
        
        // Filtre par date (jusqu'à)
        if ($filterDateTo !== '') {
            $where[] = 'DATE(c.date_commande) <= ?';
            $params[] = $filterDateTo;
        }
        
        $sql = 'SELECT c.*, p.nom as produit_nom, p.marque as produit_marque FROM commandes c LEFT JOIN produits p ON c.id_produit = p.id_produit';
        
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        
        $sql .= ' ORDER BY c.date_commande DESC';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT c.*, p.nom as produit_nom, p.marque as produit_marque FROM commandes c LEFT JOIN produits p ON c.id_produit = p.id_produit WHERE c.id_commande = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function countAll(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM commandes')->fetchColumn();
    }

    public function countPending(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM commandes WHERE statut IN ('en-cours', 'en-preparation')")->fetchColumn();
    }

    public function latest(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare('SELECT c.id_commande, c.id_produit, c.id_utilisateur, c.quantite, c.prix_total, c.statut, c.mode_livraison, c.date_livraison_souhaitee, c.date_commande, p.nom as produit_nom, p.marque as produit_marque FROM commandes c LEFT JOIN produits p ON c.id_produit = p.id_produit ORDER BY c.date_commande DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function create(Order $order): void
    {
        $data = $order->toArray();
        $sql = 'INSERT INTO commandes (id_produit, id_utilisateur, quantite, prix_total, date_commande, statut, adresse_livraison, mode_livraison, date_livraison_souhaitee, methode_paiement, numero_carte, nom_titulaire, date_expiration, cvv)
                VALUES (:id_produit, :id_utilisateur, :quantite, :prix_total, :date_commande, :statut, :adresse_livraison, :mode_livraison, :date_livraison_souhaitee, :methode_paiement, :numero_carte, :nom_titulaire, :date_expiration, :cvv)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_produit' => $data['id_produit'],
            ':id_utilisateur' => $data['id_utilisateur'],
            ':quantite' => $data['quantite'],
            ':prix_total' => $data['prix_total'],
            ':date_commande' => $data['date_commande'],
            ':statut' => $data['statut'],
            ':adresse_livraison' => $data['adresse_livraison'],
            ':mode_livraison' => $data['mode_livraison'],
            ':date_livraison_souhaitee' => $data['date_livraison_souhaitee'],
            ':methode_paiement' => $data['methode_paiement'],
            ':numero_carte' => $data['numero_carte'],
            ':nom_titulaire' => $data['nom_titulaire'],
            ':date_expiration' => $data['date_expiration'],
            ':cvv' => $data['cvv'],
        ]);
    }

    private function createNow(Order $order): void
    {
        $data = $order->toArray();
        $sql = 'INSERT INTO commandes (id_produit, id_utilisateur, quantite, prix_total, date_commande, statut, adresse_livraison, mode_livraison, date_livraison_souhaitee, methode_paiement, numero_carte, nom_titulaire, date_expiration, cvv)
                VALUES (:id_produit, :id_utilisateur, :quantite, :prix_total, NOW(), :statut, :adresse_livraison, :mode_livraison, :date_livraison_souhaitee, :methode_paiement, :numero_carte, :nom_titulaire, :date_expiration, :cvv)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_produit' => $data['id_produit'],
            ':id_utilisateur' => $data['id_utilisateur'],
            ':quantite' => $data['quantite'],
            ':prix_total' => $data['prix_total'],
            ':statut' => $data['statut'],
            ':adresse_livraison' => $data['adresse_livraison'],
            ':mode_livraison' => $data['mode_livraison'],
            ':date_livraison_souhaitee' => $data['date_livraison_souhaitee'],
            ':methode_paiement' => $data['methode_paiement'],
            ':numero_carte' => $data['numero_carte'],
            ':nom_titulaire' => $data['nom_titulaire'],
            ':date_expiration' => $data['date_expiration'],
            ':cvv' => $data['cvv'],
        ]);
    }

    private function update(int $id, Order $order): void
    {
        $data = $order->toArray();
        $sql = 'UPDATE commandes SET id_produit = :id_produit, id_utilisateur = :id_utilisateur, quantite = :quantite,
                prix_total = :prix_total, date_commande = :date_commande, statut = :statut,
                adresse_livraison = :adresse_livraison, mode_livraison = :mode_livraison,
                date_livraison_souhaitee = :date_livraison_souhaitee, methode_paiement = :methode_paiement,
                numero_carte = :numero_carte, nom_titulaire = :nom_titulaire, date_expiration = :date_expiration,
                cvv = :cvv WHERE id_commande = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_produit' => $data['id_produit'],
            ':id_utilisateur' => $data['id_utilisateur'],
            ':quantite' => $data['quantite'],
            ':prix_total' => $data['prix_total'],
            ':date_commande' => $data['date_commande'],
            ':statut' => $data['statut'],
            ':adresse_livraison' => $data['adresse_livraison'],
            ':mode_livraison' => $data['mode_livraison'],
            ':date_livraison_souhaitee' => $data['date_livraison_souhaitee'],
            ':methode_paiement' => $data['methode_paiement'],
            ':numero_carte' => $data['numero_carte'],
            ':nom_titulaire' => $data['nom_titulaire'],
            ':date_expiration' => $data['date_expiration'],
            ':cvv' => $data['cvv'],
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        if ($id > 0) {
            $stmt = $this->pdo->prepare('DELETE FROM commandes WHERE id_commande = :id');
            $stmt->execute(['id' => $id]);
        }
    }


    public function save(array $input, ?int $id = null): array
    {
        // Create Order object from input
        $order = new Order();
        $order->setIdProduit((int) ($input['id_produit'] ?? 0));
        $order->setIdUtilisateur((int) ($input['id_utilisateur'] ?? 0));
        $order->setQuantite((int) ($input['quantite'] ?? 0));
        $order->setPrixTotal((float) ($input['prix_total'] ?? 0));
        $order->setDateCommande(trim((string) ($input['date_commande'] ?? date('Y-m-d H:i:s'))));
        $order->setStatut(trim((string) ($input['statut'] ?? 'en-cours')));
        $order->setAdresseLivraison(trim((string) ($input['adresse_livraison'] ?? '')));
        $order->setModeLivraison(trim((string) ($input['mode_livraison'] ?? 'standard')));
        $order->setDateLivraisonSouhaitee($this->normalizeDeliveryDate($input['date_livraison_souhaitee'] ?? null));
        
        // Set payment method and details
        $methodePaiement = trim((string) ($input['methode_paiement'] ?? 'cash'));
        $order->setMethodePaiement($methodePaiement);
        
        if ($methodePaiement === 'carte') {
            $order->setNumeroCarte(trim((string) ($input['numero_carte'] ?? '')));
            $order->setNomTitulaire(trim((string) ($input['nom_titulaire'] ?? '')));
            $order->setDateExpiration(trim((string) ($input['date_expiration'] ?? '')));
            $order->setCvv(trim((string) ($input['cvv'] ?? '')));
        }

        // Validate
        $errors = $this->validateForBackOffice($order);
        if ($errors) {
            return [
                'ok' => false,
                'errors' => $errors,
                'error' => 'Veuillez corriger les champs invalides.',
                'data' => $order->toArray(),
            ];
        }

        // Save to database
        if ($id && $id > 0) {
            $this->update($id, $order);
        } else {
            $this->createNow($order);
        }

        return ['ok' => true, 'data' => $order->toArray()];
    }

    public function createFromFront(array $input): array
    {
        $idProduit = (int) ($input['id_produit'] ?? 0);
        $idUtilisateur = (int) ($input['id_utilisateur'] ?? 0);
        $quantite = (int) ($input['quantite'] ?? 0);
        $prixUnitaire = (float) ($input['prix_unitaire'] ?? 0);
        $adresse = trim((string) ($input['adresse_livraison'] ?? ''));
        $modeLivraison = trim((string) ($input['mode_livraison'] ?? 'standard'));
        $dateLivraisonSouhaitee = $this->normalizeDeliveryDate($input['date_livraison_souhaitee'] ?? null);
        $methodePaiement = trim((string) ($input['methode_paiement'] ?? 'cash'));

        $errors = [];

        if ($idProduit <= 0) {
            $errors['id_produit'] = 'Produit invalide.';
        }
        if ($idUtilisateur <= 0) {
            $errors['id_utilisateur'] = 'ID utilisateur invalide.';
        }

        if ($quantite < 1 || $quantite > 1000) {
            $errors['quantite'] = 'Quantite invalide (1 a 1000).';
        }

        if ($prixUnitaire <= 0 || $prixUnitaire > 100000) {
            $errors['prix_unitaire'] = 'Prix unitaire invalide.';
        }

        if (!in_array($modeLivraison, self::ALLOWED_DELIVERY_MODES, true)) {
            $errors['mode_livraison'] = 'Mode de livraison invalide.';
        }

        if (mb_strlen($adresse) < 10 || mb_strlen($adresse) > 255) {
            $errors['adresse_livraison'] = 'Adresse invalide (10 a 255 caracteres).';
        }

        if ($dateLivraisonSouhaitee === null) {
            $errors['date_livraison_souhaitee'] = 'Date de livraison souhaitée invalide.';
        } elseif ($dateLivraisonSouhaitee < date('Y-m-d')) {
            $errors['date_livraison_souhaitee'] = 'La date de livraison souhaitée ne peut pas etre passee.';
        }

        // Validation du paiement
        if (!in_array($methodePaiement, ['cash', 'carte'], true)) {
            $errors['methode_paiement'] = 'Methode de paiement invalide.';
        } elseif ($methodePaiement === 'carte') {
            // Valider les informations de carte
            $numeroCarte = trim((string) ($input['numero_carte'] ?? ''));
            $nomTitulaire = trim((string) ($input['nom_titulaire'] ?? ''));
            $dateExpiration = trim((string) ($input['date_expiration'] ?? ''));
            $cvv = trim((string) ($input['cvv'] ?? ''));

            if (!preg_match('/^\d{13,19}$/', $numeroCarte)) {
                $errors['numero_carte'] = 'Numero de carte invalide (13-19 chiffres).';
            }
            if (mb_strlen($nomTitulaire) < 3 || mb_strlen($nomTitulaire) > 100) {
                $errors['nom_titulaire'] = 'Nom du titulaire invalide (3-100 caracteres).';
            }
            if (!preg_match('/^\d{2}\/\d{2}$/', $dateExpiration)) {
                $errors['date_expiration'] = 'Format: MM/YY';
            }
            if (!preg_match('/^\d{3,4}$/', $cvv)) {
                $errors['cvv'] = 'CVV invalide (3-4 chiffres).';
            }
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors, 'error' => 'Veuillez corriger les champs invalides.'];
        }

        $order = new Order();
        $order->setIdProduit($idProduit);
        $order->setIdUtilisateur($idUtilisateur);
        $order->setQuantite($quantite);
        $order->setPrixTotal($prixUnitaire * $quantite);
        $order->setStatut('en-cours');
        $order->setAdresseLivraison($adresse);
        $order->setModeLivraison($modeLivraison);
        $order->setDateLivraisonSouhaitee($dateLivraisonSouhaitee);
        $order->setMethodePaiement($methodePaiement);
        
        if ($methodePaiement === 'carte') {
            $order->setNumeroCarte(trim((string) ($input['numero_carte'] ?? '')));
            $order->setNomTitulaire(trim((string) ($input['nom_titulaire'] ?? '')));
            $order->setDateExpiration(trim((string) ($input['date_expiration'] ?? '')));
            $order->setCvv(trim((string) ($input['cvv'] ?? '')));
        }
        
        $this->createNow($order);

        return ['ok' => true];
    }

    public function metrics(): array
    {
        return [
            'total' => $this->countAll(),
            'pending' => $this->countPending(),
        ];
    }

    // ========== Validation ==========

    private function validateForBackOffice(Order $order): array
    {
        $errors = [];

        if ($order->getIdProduit() <= 0) {
            $errors['id_produit'] = 'ID produit invalide.';
        }
        if ($order->getIdUtilisateur() <= 0) {
            $errors['id_utilisateur'] = 'ID utilisateur invalide.';
        }

        if ($order->getQuantite() < 1 || $order->getQuantite() > 1000) {
            $errors['quantite'] = 'La quantite doit etre comprise entre 1 et 1000.';
        }

        if ($order->getPrixTotal() < 0 || $order->getPrixTotal() > 1000000) {
            $errors['prix_total'] = 'Le prix total doit etre compris entre 0 et 1000000.';
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $order->getDateCommande());
        if (!$date || $date->format('Y-m-d H:i:s') !== $order->getDateCommande()) {
            $errors['date_commande'] = 'Format attendu: YYYY-MM-DD HH:MM:SS.';
        }

        if (!in_array($order->getStatut(), self::ALLOWED_STATUS, true)) {
            $errors['statut'] = 'Le statut de commande est invalide.';
        }

        if (mb_strlen($order->getAdresseLivraison()) < 10 || mb_strlen($order->getAdresseLivraison()) > 255) {
            $errors['adresse_livraison'] = 'L\'adresse doit contenir entre 10 et 255 caracteres.';
        }

        if (!in_array($order->getModeLivraison(), self::ALLOWED_DELIVERY_MODES, true)) {
            $errors['mode_livraison'] = 'Le mode de livraison est invalide.';
        }

        if ($this->normalizeDeliveryDate($order->getDateLivraisonSouhaitee()) === null) {
            $errors['date_livraison_souhaitee'] = 'La date de livraison souhaitée est invalide.';
        }

        return $errors;
    }

    private function normalizeDeliveryDate(mixed $value): ?string
    {
        $date = trim((string) $value);
        if ($date === '') {
            return null;
        }

        $parsed = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            return null;
        }

        return $date;
    }
}
