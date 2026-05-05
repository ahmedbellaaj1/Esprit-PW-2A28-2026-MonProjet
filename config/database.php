<?php
class config {
    private static $pdo = null;
    
    /**
     * Obtenir la connexion PDO à la base de données
     * @return PDO
     */
    public static function getConnexion() {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=projetwebnova;charset=utf8',
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (Exception $e) {
                die('Erreur de connexion à la base de données: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
?>