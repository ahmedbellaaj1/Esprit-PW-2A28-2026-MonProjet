<?php
class config {
    private static $pdo = null;
    private static $host = 'localhost';
    private static $dbname = 'projetwebnova';
    private static $username = 'root';
    private static $password = '';
    
    /**
     * Obtenir la connexion PDO à la base de données
     * @return PDO
     */
    public static function getConnexion() {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=' . self::$host . ';dbname=' . self::$dbname . ';charset=utf8',
                    self::$username,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                    ]
                );
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                self::handleError($e);
            } catch (Exception $e) {
                error_log("Database connection error: " . $e->getMessage());
                self::handleError($e);
            }
        }
        return self::$pdo;
    }
    
    /**
     * Fermer la connexion à la base de données
     */
    public static function closeConnexion() {
        self::$pdo = null;
    }
    
    /**
     * Tester la connexion à la base de données
     * @return bool
     */
    public static function testConnexion() {
        try {
            $pdo = self::getConnexion();
            $pdo->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtenir le nom de la base de données
     * @return string
     */
    public static function getDatabaseName() {
        return self::$dbname;
    }
    
    /**
     * Gérer les erreurs de connexion
     * @param Exception $e
     */
    private static function handleError($e) {
        if (strpos($e->getMessage(), 'Unknown database') !== false) {
            die('Erreur: La base de données "' . self::$dbname . '" n\'existe pas. Veuillez la créer d\'abord.');
        } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
            die('Erreur: Accès refusé à la base de données. Vérifiez vos identifiants (nom d\'utilisateur/mot de passe).');
        } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
            die('Erreur: Impossible de se connecter au serveur MySQL. Vérifiez que MySQL est démarré.');
        } else {
            die('Erreur de connexion à la base de données: ' . $e->getMessage());
        }
    }
    
    /**
     * Exécuter une requête SQL avec gestion d'erreurs
     * @param string $sql
     * @param array $params
     * @return PDOStatement|false
     */
    public static function executeQuery($sql, $params = []) {
        try {
            $pdo = self::getConnexion();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage() . " - SQL: " . $sql);
            return false;
        }
    }
    
    /**
     * Démarrer une transaction
     * @return bool
     */
    public static function beginTransaction() {
        try {
            $pdo = self::getConnexion();
            return $pdo->beginTransaction();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Valider une transaction
     * @return bool
     */
    public static function commit() {
        try {
            $pdo = self::getConnexion();
            return $pdo->commit();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Annuler une transaction
     * @return bool
     */
    public static function rollback() {
        try {
            $pdo = self::getConnexion();
            return $pdo->rollBack();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Échapper une chaîne pour une utilisation sécurisée (si nécessaire)
     * @param string $string
     * @return string
     */
    public static function escapeString($string) {
        $pdo = self::getConnexion();
        return substr($pdo->quote($string), 1, -1);
    }
    
    /**
     * Obtenir le dernier ID inséré
     * @return string
     */
    public static function lastInsertId() {
        $pdo = self::getConnexion();
        return $pdo->lastInsertId();
    }
}
?>