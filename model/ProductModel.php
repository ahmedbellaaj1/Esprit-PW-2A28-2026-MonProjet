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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function add($pdo, $data) {
        $tableName = self::getTableName($pdo);
        $stmt = $pdo->prepare("INSERT INTO $tableName (nom, categorie, description, calories, prix) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['nom'],
            $data['categorie'],
            $data['description'],
            $data['calories'],
            $data['prix']
        ]);
    }

    public static function delete($pdo, $id) {
        $tableName = self::getTableName($pdo);
        $stmt = $pdo->prepare("DELETE FROM $tableName WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
