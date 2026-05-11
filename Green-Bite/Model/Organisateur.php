<?php
class Organisateur {
    private $id;
    private $nom;
    private $email;
    private $telephone;
    private $adresse;
    private $site_web;
    private $created_at;
    private $updated_at;
    
    // Données supplémentaires (non persistées)
    private $event_count;

    public function __construct($nom = '', $email = '', $telephone = '', $adresse = '', $site_web = '') {
        $this->nom = $nom;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->adresse = $adresse;
        $this->site_web = $site_web;
        $this->event_count = 0;
    }

    // ==================== SETTERS AVEC VALIDATION PHP UNIQUEMENT ====================
    
    public function setId($id) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            throw new Exception("ID d'organisateur invalide");
        }
        $this->id = $id;
        return $this;
    }

    public function setNom($nom) {
        // Nettoyage et validation PHP
        $nom = trim($nom);
        
        if (empty($nom)) {
            throw new Exception("Le nom de l'organisateur est obligatoire");
        }
        
        if (strlen($nom) < 2) {
            throw new Exception("Le nom doit contenir au moins 2 caractères");
        }
        
        if (strlen($nom) > 100) {
            throw new Exception("Le nom ne peut pas dépasser 100 caractères");
        }
        
        // Validation des caractères autorisés
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\.\']+$/', $nom)) {
            throw new Exception("Le nom ne peut contenir que des lettres, espaces, tirets, points et apostrophes");
        }
        
        // Protection contre les injections XSS
        $this->nom = htmlspecialchars($nom, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function setEmail($email) {
        // Nettoyage et validation PHP
        $email = trim($email);
        
        if (empty($email)) {
            throw new Exception("L'email est obligatoire");
        }
        
        if (strlen($email) > 150) {
            throw new Exception("L'email ne peut pas dépasser 150 caractères");
        }
        
        // Validation du format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Format d'email invalide. Exemple: nom@domaine.com");
        }
        
        // Validation supplémentaire avec regex
        if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
            throw new Exception("Format d'email invalide. Vérifiez la présence du @ et du domaine");
        }
        
        // Vérification des caractères interdits dans l'email
        if (preg_match('/[<>\(\)\[\]\\\;,]/', $email)) {
            throw new Exception("L'email contient des caractères non autorisés");
        }
        
        $this->email = strtolower($email);
        return $this;
    }

    public function setTelephone($telephone) {
        // Nettoyage et validation PHP
        $telephone = trim($telephone);
        
        if (empty($telephone)) {
            throw new Exception("Le numéro de téléphone est obligatoire");
        }
        
        if (strlen($telephone) < 8) {
            throw new Exception("Le numéro de téléphone doit contenir au moins 8 chiffres");
        }
        
        if (strlen($telephone) > 20) {
            throw new Exception("Le numéro de téléphone ne peut pas dépasser 20 caractères");
        }
        
        // Validation du format téléphone (chiffres, espaces, tirets, +)
        if (!preg_match('/^[0-9+\-\s]{8,20}$/', $telephone)) {
            throw new Exception("Format de téléphone invalide. Exemples: 71 123 456, 71234567, +21671234567");
        }
        
        // Vérification qu'il y a au moins 8 chiffres
        $digitsOnly = preg_replace('/[^0-9]/', '', $telephone);
        if (strlen($digitsOnly) < 8) {
            throw new Exception("Le numéro de téléphone doit contenir au moins 8 chiffres");
        }
        
        $this->telephone = htmlspecialchars($telephone, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function setAdresse($adresse) {
        // Nettoyage et validation PHP
        $adresse = trim($adresse);
        
        if (!empty($adresse)) {
            if (strlen($adresse) < 5) {
                throw new Exception("L'adresse doit contenir au moins 5 caractères");
            }
            if (strlen($adresse) > 500) {
                throw new Exception("L'adresse ne peut pas dépasser 500 caractères");
            }
            // Protection contre les injections XSS
            $this->adresse = htmlspecialchars($adresse, ENT_QUOTES, 'UTF-8');
        } else {
            $this->adresse = null;
        }
        return $this;
    }

    public function setSiteWeb($site_web) {
        // Nettoyage et validation PHP
        $site_web = trim($site_web);
        
        if (!empty($site_web)) {
            if (strlen($site_web) > 200) {
                throw new Exception("L'URL du site web ne peut pas dépasser 200 caractères");
            }
            
            // Ajouter http:// si absent
            if (!preg_match('/^https?:\/\//', $site_web)) {
                $site_web = 'https://' . $site_web;
            }
            
            // Validation du format URL
            if (!filter_var($site_web, FILTER_VALIDATE_URL)) {
                throw new Exception("Format d'URL invalide. Exemple: https://exemple.com");
            }
            
            // Vérification supplémentaire du domaine
            $parsedUrl = parse_url($site_web);
            if (!isset($parsedUrl['host']) || empty($parsedUrl['host'])) {
                throw new Exception("L'URL doit contenir un nom de domaine valide");
            }
            
            $this->site_web = htmlspecialchars($site_web, ENT_QUOTES, 'UTF-8');
        } else {
            $this->site_web = null;
        }
        return $this;
    }

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
        return $this;
    }

    public function setUpdatedAt($updated_at) {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function setEventCount($count) {
        $count = filter_var($count, FILTER_VALIDATE_INT);
        $this->event_count = ($count === false) ? 0 : $count;
        return $this;
    }

    // ==================== GETTERS AVEC SÉCURISATION ====================
    
    public function getId() { 
        return $this->id; 
    }
    
    public function getNom() { 
        return $this->nom; 
    }
    
    public function getEmail() { 
        return $this->email; 
    }
    
    public function getTelephone() { 
        return $this->telephone; 
    }
    
    public function getAdresse() { 
        return $this->adresse; 
    }
    
    public function getSiteWeb() { 
        return $this->site_web; 
    }
    
    public function getCreatedAt() { 
        return $this->created_at; 
    }
    
    public function getUpdatedAt() { 
        return $this->updated_at; 
    }
    
    public function getEventCount() { 
        return $this->event_count; 
    }

    // ==================== MÉTHODES DE VALIDATION ====================
    
    /**
     * Validation complète de l'organisateur (tous les champs)
     * @return bool
     */
    public function isValid() {
        try {
            $this->setNom($this->nom);
            $this->setEmail($this->email);
            $this->setTelephone($this->telephone);
            $this->setAdresse($this->adresse);
            $this->setSiteWeb($this->site_web);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Récupérer tous les messages d'erreur sous forme de tableau
     * @return array
     */
    public function getErrors() {
        $errors = [];
        try { $this->setNom($this->nom); } catch (Exception $e) { $errors['nom'] = $e->getMessage(); }
        try { $this->setEmail($this->email); } catch (Exception $e) { $errors['email'] = $e->getMessage(); }
        try { $this->setTelephone($this->telephone); } catch (Exception $e) { $errors['telephone'] = $e->getMessage(); }
        try { $this->setAdresse($this->adresse); } catch (Exception $e) { $errors['adresse'] = $e->getMessage(); }
        try { $this->setSiteWeb($this->site_web); } catch (Exception $e) { $errors['site_web'] = $e->getMessage(); }
        return $errors;
    }

    /**
     * Récupérer les erreurs sous forme de chaîne de caractères
     * @return string
     */
    public function getErrorsString() {
        $errors = $this->getErrors();
        if (empty($errors)) {
            return '';
        }
        return implode(', ', $errors);
    }

    /**
     * Vérifier si un champ spécifique est valide
     * @param string $field
     * @return bool
     */
    public function isFieldValid($field) {
        try {
            switch ($field) {
                case 'nom': $this->setNom($this->nom); break;
                case 'email': $this->setEmail($this->email); break;
                case 'telephone': $this->setTelephone($this->telephone); break;
                case 'adresse': $this->setAdresse($this->adresse); break;
                case 'site_web': $this->setSiteWeb($this->site_web); break;
                default: return false;
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Obtenir l'erreur d'un champ spécifique
     * @param string $field
     * @return string|null
     */
    public function getFieldError($field) {
        try {
            switch ($field) {
                case 'nom': $this->setNom($this->nom); break;
                case 'email': $this->setEmail($this->email); break;
                case 'telephone': $this->setTelephone($this->telephone); break;
                case 'adresse': $this->setAdresse($this->adresse); break;
                case 'site_web': $this->setSiteWeb($this->site_web); break;
                default: return null;
            }
            return null;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Vérifier si l'objet a des erreurs de validation
     * @return bool
     */
    public function hasErrors() {
        return !empty($this->getErrors());
    }

    // ==================== MÉTHODES DE FORMATAGE ====================
    
    /**
     * Obtenir le numéro de téléphone formaté
     * @return string
     */
    public function getFormattedTelephone() {
        if (empty($this->telephone)) {
            return '';
        }
        // Supprime tous les caractères non numériques
        $digits = preg_replace('/[^0-9]/', '', $this->telephone);
        
        // Formater selon la longueur
        $length = strlen($digits);
        if ($length == 8) {
            return substr($digits, 0, 2) . ' ' . substr($digits, 2, 3) . ' ' . substr($digits, 5, 3);
        } elseif ($length == 10 && substr($digits, 0, 2) == '21') {
            return '+216 ' . substr($digits, 2, 2) . ' ' . substr($digits, 4, 3) . ' ' . substr($digits, 7, 3);
        }
        return $this->telephone;
    }

    /**
     * Obtenir l'icône du statut (basé sur le nombre d'événements)
     * @return string
     */
    public function getStatusIcon() {
        if ($this->event_count > 5) {
            return '🏆'; // Très actif
        } elseif ($this->event_count > 0) {
            return '✅'; // Actif
        } else {
            return '⏳'; // Inactif
        }
    }

    /**
     * Obtenir le label du statut
     * @return string
     */
    public function getStatusLabel() {
        if ($this->event_count > 5) {
            return 'Très actif';
        } elseif ($this->event_count > 0) {
            return 'Actif';
        } else {
            return 'Aucun événement';
        }
    }

    /**
     * Obtenir la classe CSS du statut
     * @return string
     */
    public function getStatusClass() {
        if ($this->event_count > 5) {
            return 'status-very-active';
        } elseif ($this->event_count > 0) {
            return 'status-active';
        } else {
            return 'status-inactive';
        }
    }

    /**
     * Obtenir l'email masqué (protection anti-spam)
     * @return string
     */
    public function getMaskedEmail() {
        if (empty($this->email)) {
            return '';
        }
        $parts = explode('@', $this->email);
        $username = $parts[0];
        $domain = $parts[1] ?? '';
        
        if (strlen($username) <= 3) {
            $maskedUsername = substr($username, 0, 1) . str_repeat('*', strlen($username) - 1);
        } else {
            $maskedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 3) . substr($username, -1);
        }
        
        return $maskedUsername . '@' . $domain;
    }

    // ==================== MÉTHODES DE CONVERSION ====================
    
    /**
     * Convertir l'objet en tableau pour la base de données
     * @return array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'site_web' => $this->site_web
        ];
    }

    /**
     * Créer un objet Organisateur à partir d'un tableau (résultat PDO)
     * @param array $data
     * @return Organisateur
     */
    public static function fromArray($data) {
        $organisateur = new Organisateur(
            $data['nom'] ?? '',
            $data['email'] ?? '',
            $data['telephone'] ?? '',
            $data['adresse'] ?? '',
            $data['site_web'] ?? ''
        );
        
        if (isset($data['id'])) {
            $organisateur->setId($data['id']);
        }
        if (isset($data['created_at'])) {
            $organisateur->setCreatedAt($data['created_at']);
        }
        if (isset($data['updated_at'])) {
            $organisateur->setUpdatedAt($data['updated_at']);
        }
        if (isset($data['event_count'])) {
            $organisateur->setEventCount($data['event_count']);
        }
        
        return $organisateur;
    }

    /**
     * Créer un tableau d'erreurs pour l'affichage dans les formulaires
     * @return array
     */
    public function getValidationErrors() {
        return $this->getErrors();
    }
}
?>