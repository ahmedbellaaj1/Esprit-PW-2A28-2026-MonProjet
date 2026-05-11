<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/Product.php';
require_once __DIR__ . '/../config/database.php';

final class ProductController
{
    private PDO $pdo;
    // Nutriscore E (mauvais pour la santé) est intentionnellement exclu pour GreenBite
    private const ALLOWED_NUTRISCORES = ['A', 'B', 'C', 'D'];
    private const ALLOWED_STATUS = ['actif', 'inactif', 'attente'];

    public function __construct()
    {
        $this->pdo = Database::connection();
    }


    public function list(array $filters = []): array
    {
        $q = trim((string) ($filters['q'] ?? ''));
        $categorie = trim((string) ($filters['categorie'] ?? ''));
        $nutriscore = trim((string) ($filters['nutriscore'] ?? ''));
        $prixMin = trim((string) ($filters['prix_min'] ?? ''));
        $prixMax = trim((string) ($filters['prix_max'] ?? ''));
        $sort = trim((string) ($filters['sort'] ?? 'recent'));
        $status = trim((string) ($filters['status'] ?? ''));

        $where = [];
        $params = [];

        if ($q !== '') {
            $search = '%' . $q . '%';
            $where[] = "(nom LIKE ? OR marque LIKE ? OR code_barre LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        if ($categorie !== '') {
            $where[] = 'categorie = ?';
            $params[] = $categorie;
        }
        if ($nutriscore !== '') {
            $where[] = 'nutriscore = ?';
            $params[] = $nutriscore;
        }
        if ($prixMin !== '' && is_numeric($prixMin)) {
            $where[] = 'prix >= ?';
            $params[] = (float) $prixMin;
        }
        if ($prixMax !== '' && is_numeric($prixMax)) {
            $where[] = 'prix <= ?';
            $params[] = (float) $prixMax;
        }
        if ($status !== '') {
            $where[] = 'statut = ?';
            $params[] = $status;
        }

        $sql = 'SELECT * FROM produits';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // Tri
        $sql .= match ($sort) {
            'prix_asc' => ' ORDER BY prix ASC',
            'prix_desc' => ' ORDER BY prix DESC',
            'nom' => ' ORDER BY nom ASC',
            'recent' => ' ORDER BY date_ajout DESC',
            default => ' ORDER BY id_produit DESC',
        };

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM produits WHERE id_produit = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function categories(): array
    {
        return [
            'Produits laitiers',
            'Boissons',
            'Cereales & Pains',
            'Epicerie',
            'Snacks & Biscuits',
            'Conserves',
            'Fruits & Legumes',
            'Viandes & Poissons',
            'Produits surgelés',
            'Chocolat & Bonbons',
            'Cafe & The',
            'Miel & Confitures',
            'Huiles & Condiments',
            'Produits bio',
            'Petit déjeuner',
            'Sauces & Assaisonnements',
            'Produits fermentés',
            'Noix & Graines',
            'Sucres & Miel',
            'Oeufs & Produits frais'
        ];
    }

    public function countAll(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM produits')->fetchColumn();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM produits WHERE statut = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function latest(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare('SELECT id_produit, nom, marque, prix, statut, nutriscore FROM produits ORDER BY date_ajout DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function decreaseQuantity(int $productId, int $quantity): bool
    {
        if ($productId <= 0 || $quantity <= 0) {
            return false;
        }
        
        $stmt = $this->pdo->prepare('UPDATE produits SET quantite_disponible = quantite_disponible - ? WHERE id_produit = ? AND quantite_disponible >= ?');
        $result = $stmt->execute([$quantity, $productId, $quantity]);
        
        return $result && $stmt->rowCount() > 0;
    }

    private function create(Product $product): void
    {
        $data = $product->toArray();
        $sql = 'INSERT INTO produits (nom, marque, code_barre, categorie, prix, calories, proteines, glucides, lipides, nutriscore, image, quantite_disponible, statut, date_ajout)
                VALUES (:nom, :marque, :code_barre, :categorie, :prix, :calories, :proteines, :glucides, :lipides, :nutriscore, :image, :quantite_disponible, :statut, NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $data['nom'],
            ':marque' => $data['marque'],
            ':code_barre' => $data['code_barre'],
            ':categorie' => $data['categorie'],
            ':prix' => $data['prix'],
            ':calories' => $data['calories'],
            ':proteines' => $data['proteines'],
            ':glucides' => $data['glucides'],
            ':lipides' => $data['lipides'],
            ':nutriscore' => $data['nutriscore'],
            ':image' => $data['image'],
            ':quantite_disponible' => $data['quantite_disponible'],
            ':statut' => $data['statut'],
        ]);
    }

    private function update(int $id, Product $product): void
    {
        $data = $product->toArray();
        $sql = 'UPDATE produits SET nom = :nom, marque = :marque, code_barre = :code_barre, categorie = :categorie,
                prix = :prix, calories = :calories, proteines = :proteines, glucides = :glucides,
                lipides = :lipides, nutriscore = :nutriscore, image = :image, quantite_disponible = :quantite_disponible, statut = :statut
                WHERE id_produit = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $data['nom'],
            ':marque' => $data['marque'],
            ':code_barre' => $data['code_barre'],
            ':categorie' => $data['categorie'],
            ':prix' => $data['prix'],
            ':calories' => $data['calories'],
            ':proteines' => $data['proteines'],
            ':glucides' => $data['glucides'],
            ':lipides' => $data['lipides'],
            ':nutriscore' => $data['nutriscore'],
            ':image' => $data['image'],
            ':quantite_disponible' => $data['quantite_disponible'],
            ':statut' => $data['statut'],
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        if ($id > 0) {
            $stmt = $this->pdo->prepare('DELETE FROM produits WHERE id_produit = :id');
            $stmt->execute(['id' => $id]);
        }
    }



    public function save(array $input, ?int $id = null): array
    {
        $product = new Product();
        $product->setNom(trim((string) ($input['nom'] ?? '')));
        $product->setMarque(trim((string) ($input['marque'] ?? '')));
        $product->setCodeBarre(trim((string) ($input['code_barre'] ?? '')));
        $product->setCategorie(trim((string) ($input['categorie'] ?? '')));
        $product->setPrix((float) ($input['prix'] ?? 0));
        $product->setCalories((float) ($input['calories'] ?? 0));
        $product->setProteines((float) ($input['proteines'] ?? 0));
        $product->setGlucides((float) ($input['glucides'] ?? 0));
        $product->setLipides((float) ($input['lipides'] ?? 0));
        
        // Calculer automatiquement le nutriscore basé sur les valeurs nutritionnelles
        $nutriscore = $this->calculateNutriscore(
            $product->getCalories(),
            $product->getProteines(),
            $product->getGlucides(),
            $product->getLipides()
        );
        $product->setNutriscore($nutriscore);
        
        $product->setImage(trim((string) ($input['image'] ?? '')));
        $product->setQuantiteDisponible((int) ($input['quantite_disponible'] ?? 0));
        $product->setStatut(trim((string) ($input['statut'] ?? 'actif')));

        $errors = $this->validate($product);
        if ($errors) {
            return [
                'ok' => false,
                'errors' => $errors,
                'error' => 'Veuillez corriger les champs invalides.',
                'data' => $product->toArray(),
            ];
        }

        if ($id && $id > 0) {
            $this->update($id, $product);
        } else {
            $this->create($product);
        }

        return ['ok' => true, 'data' => $product->toArray()];
    }

    public function metrics(): array
    {
        return [
            'total' => $this->countAll(),
            'active' => $this->countByStatus('actif'),
        ];
    }


    private function validate(Product $product): array
    {
        $errors = [];

        // Validation du nom - 2 à 150 caractères, combinaison de lettres et chiffres mais pas uniquement chiffres
        if ($product->getNom() === '' || mb_strlen($product->getNom()) < 2 || mb_strlen($product->getNom()) > 150) {
            $errors['nom'] = 'Le nom doit contenir 2 a 150 caracteres.';
        } elseif (preg_match('/^[0-9]+$/', $product->getNom())) {
            $errors['nom'] = 'Le nom ne peut pas etre uniquement des chiffres.';
        }

        // Validation de la marque - 2 à 120 caractères, combinaison de lettres et chiffres mais pas uniquement chiffres
        if ($product->getMarque() === '' || mb_strlen($product->getMarque()) < 2 || mb_strlen($product->getMarque()) > 120) {
            $errors['marque'] = 'La marque doit contenir 2 a 120 caracteres.';
        } elseif (preg_match('/^[0-9]+$/', $product->getMarque())) {
            $errors['marque'] = 'La marque ne peut pas etre uniquement des chiffres.';
        }

        // Validation du code barre - chiffres uniquement, 8 à 20 chiffres
        if ($product->getCodeBarre() !== '' && !preg_match('/^[0-9]{8,20}$/', $product->getCodeBarre())) {
            $errors['code_barre'] = 'Le code barre doit contenir 8 a 20 chiffres uniquement.';
        }

        if (mb_strlen($product->getCategorie()) > 120) {
            $errors['categorie'] = 'La categorie ne doit pas depasser 120 caracteres.';
        }

        if ($product->getPrix() < 0 || $product->getPrix() > 100000) {
            $errors['prix'] = 'Le prix doit etre compris entre 0 et 100000.';
        }

        // Validation stricte des champs nutritionnels - TOUS doivent être remplis, chiffres uniquement (avec décimales)
        foreach (['calories', 'proteines', 'glucides', 'lipides'] as $field) {
            $getter = 'get' . ucfirst($field);
            $value = $product->$getter();
            
            $fieldLabel = [
                'calories' => 'les calories',
                'proteines' => 'les protéines',
                'glucides' => 'les glucides',
                'lipides' => 'les lipides'
            ][$field];
            
            // Vérifier que le champ est rempli et positif
            if ($value <= 0) {
                $errors[$field] = 'Vous devez renseigner ' . $fieldLabel . ' (valeur > 0).';
            } elseif ($value > 5000) {
                $errors[$field] = 'La valeur doit etre comprise entre 0 et 5000.';
            }
        }

        if (!in_array($product->getNutriscore(), self::ALLOWED_NUTRISCORES, true)) {
            $errors['nutriscore'] = 'Le nutriscore est invalide.';
        }

        if ($product->getImage() === '' || !preg_match('/^https?:\/\/.+\..+$/i', $product->getImage())) {
            $errors['image'] = 'Vous devez renseigner une URL valide pour l\'image (commencant par http:// ou https://).';
        }

        if ($product->getQuantiteDisponible() < 0) {
            $errors['quantite_disponible'] = 'La quantité disponible ne peut pas etre négative.';
        }

        if (!in_array($product->getStatut(), self::ALLOWED_STATUS, true)) {
            $errors['statut'] = 'Le statut est invalide.';
        }

        return $errors;
    }

    /**
     * Calcule automatiquement le nutriscore basé sur les valeurs nutritionnelles
     * La moyenne est faite entre calories, glucides, protéines et lipides
     * 
     * Échelle logique (plus la moyenne est basse, mieux c'est):
     * A: moyenne < 250  (très bon)
     * B: 250-500        (bon)
     * C: 500-750        (moyen)
     * D: 750-1000       (moins bon)
     */
    private function calculateNutriscore(float $calories, float $proteines, float $glucides, float $lipides): string
    {
        // Calculer la moyenne des 4 valeurs nutritionnelles
        $moyenne = ($calories + $glucides + $proteines + $lipides) / 4;

        // Attribuer la note basée sur des intervalles logiques
        if ($moyenne < 250) {
            return 'A';
        } elseif ($moyenne < 500) {
            return 'B';
        } elseif ($moyenne < 750) {
            return 'C';
        } elseif ($moyenne < 1000) {
            return 'D';
        } else {
            return 'E';
        }
    }
}
