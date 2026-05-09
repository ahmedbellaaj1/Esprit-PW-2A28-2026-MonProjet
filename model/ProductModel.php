<?php
class ProductModel {
    private static function getTableName($pdo) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
        $stmtCheck->execute(['produits']);
        return ((int)$stmtCheck->fetchColumn() > 0) ? 'produits' : 'Produits';
    }

    public static function getAll($pdo) {
        $tableName = self::getTableName($pdo);
        $stmt = $pdo->query("SELECT * FROM $tableName ORDER BY id DESC");
        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $p = new Product();
            $p->setNom($row['nom']);
            $p->setCategorie($row['categorie']);
            $p->setDescription($row['description']);
            $p->setCalories($row['calories']);
            $p->setPrix($row['prix']);
            $p->setId($row['id']);
            $products[] = $p;
        }
        return $products;
    }

    public static function add($pdo, Product $product) {
        $tableName = self::getTableName($pdo);
        $stmt = $pdo->prepare("INSERT INTO $tableName (nom, categorie, description, calories, prix) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $product->getNom(),
            $product->getCategorie(),
            $product->getDescription(),
            $product->getCalories(),
            $product->getPrix()
        ]);
    }

    public static function delete($pdo, $id) {
        $tableName = self::getTableName($pdo);
        $stmt = $pdo->prepare("DELETE FROM $tableName WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
