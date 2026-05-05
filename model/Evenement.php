<?php
class Evenement {
    private $id;
    private $titre;
    private $description;
    private $date_event;
    private $lieu;
    private $type;
    private $organisateur_id;
    private $created_at;
    private $updated_at;

    // Données de jointure (non persistées)
    private $organisateur_nom;
    private $organisateur_email;
    private $organisateur_telephone;

    public function __construct($titre = '', $description = '', $date_event = '', $lieu = '', $type = '', $organisateur_id = 0) {
        $this->titre = $titre;
        $this->description = $description;
        $this->date_event = $date_event;
        $this->lieu = $lieu;
        $this->type = $type;
        $this->organisateur_id = $organisateur_id;
    }

    // ==================== SETTERS AVEC VALIDATION PHP UNIQUEMENT ====================
    
    public function setId($id) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            throw new Exception("ID invalide");
        }
        $this->id = $id;
        return $this;
    }

    public function setTitre($titre) {
        // Nettoyage et validation PHP
        $titre = trim($titre);
        
        if (empty($titre)) {
            throw new Exception("Le titre est obligatoire");
        }
        
        if (strlen($titre) < 3) {
            throw new Exception("Le titre doit contenir au moins 3 caractères");
        }
        
        if (strlen($titre) > 100) {
            throw new Exception("Le titre ne peut pas dépasser 100 caractères");
        }
        
        // Validation des caractères autorisés (lettres, chiffres, espaces, tirets, apostrophes)
        if (!preg_match('/^[a-zA-Z0-9\s\-\'àâäéèêëîïôöùûüçÀÂÄÉÈÊËÎÏÔÖÙÛÜÇ]+$/', $titre)) {
            throw new Exception("Le titre contient des caractères non autorisés. Utilisez uniquement des lettres, chiffres, espaces, tirets et apostrophes");
        }
        
        // Protection contre les injections XSS
        $this->titre = htmlspecialchars($titre, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function setDescription($description) {
        // Nettoyage et validation PHP
        $description = trim($description);
        
        if (empty($description)) {
            throw new Exception("La description est obligatoire");
        }
        
        if (strlen($description) < 10) {
            throw new Exception("La description doit contenir au moins 10 caractères");
        }
        
        if (strlen($description) > 5000) {
            throw new Exception("La description ne peut pas dépasser 5000 caractères");
        }
        
        // Protection contre les injections XSS
        $this->description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function setDate($date) {
        // Nettoyage et validation PHP
        $date = trim($date);
        
        if (empty($date)) {
            throw new Exception("La date est obligatoire");
        }
        
        // Vérifier le format YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new Exception("Format de date invalide. Utilisez le format AAAA-MM-JJ (ex: 2025-12-31)");
        }
        
        // Vérifier que la date est valide (ex: pas 2025-02-30)
        $dateObj = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
            throw new Exception("Date invalide. Vérifiez que le jour et le mois sont corrects");
        }
        
        // Vérifier que la date n'est pas dans le passé
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        if ($dateObj < $today) {
            throw new Exception("La date ne peut pas être dans le passé. Choisissez une date à partir d'aujourd'hui");
        }
        
        // Vérifier que la date n'est pas trop loin (max +5 ans)
        $maxDate = (new DateTime())->modify('+5 years');
        if ($dateObj > $maxDate) {
            throw new Exception("La date ne peut pas dépasser 5 ans dans le futur");
        }
        
        $this->date_event = $date;
        return $this;
    }

    public function setLieu($lieu) {
        // Nettoyage et validation PHP
        $lieu = trim($lieu);
        
        if (empty($lieu)) {
            throw new Exception("Le lieu est obligatoire");
        }
        
        if (strlen($lieu) < 2) {
            throw new Exception("Le lieu doit contenir au moins 2 caractères");
        }
        
        if (strlen($lieu) > 100) {
            throw new Exception("Le lieu ne peut pas dépasser 100 caractères");
        }
        
        // Protection contre les injections XSS
        $this->lieu = htmlspecialchars($lieu, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function setType($type) {
        // Nettoyage et validation PHP
        $type = trim($type);
        
        $validTypes = ['Atelier', 'Conférence', 'Festival', 'Autre'];
        
        if (empty($type)) {
            throw new Exception("Le type est obligatoire");
        }
        
        if (!in_array($type, $validTypes)) {
            throw new Exception("Type d'événement invalide. Choisissez parmi: " . implode(', ', $validTypes));
        }
        
        $this->type = $type;
        return $this;
    }

    public function setOrganisateurId($organisateur_id) {
        // Validation PHP de l'ID
        $organisateur_id = filter_var($organisateur_id, FILTER_VALIDATE_INT);
        
        if ($organisateur_id === false || $organisateur_id <= 0) {
            throw new Exception("L'organisateur est obligatoire. Veuillez sélectionner un organisateur valide");
        }
        
        $this->organisateur_id = $organisateur_id;
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

    // Données de jointure
    public function setOrganisateurNom($nom) {
        $this->organisateur_nom = htmlspecialchars($nom, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function setOrganisateurEmail($email) {
        $this->organisateur_email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function setOrganisateurTelephone($telephone) {
        $this->organisateur_telephone = htmlspecialchars($telephone, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    // ==================== GETTERS AVEC SÉCURISATION ====================
    
    public function getId() { 
        return $this->id; 
    }
    
    public function getTitre() { 
        return $this->titre; 
    }
    
    public function getDescription() { 
        return $this->description; 
    }
    
    public function getDate() { 
        return $this->date_event; 
    }
    
    public function getLieu() { 
        return $this->lieu; 
    }
    
    public function getType() { 
        return $this->type; 
    }
    
    public function getOrganisateurId() { 
        return $this->organisateur_id; 
    }
    
    public function getCreatedAt() { 
        return $this->created_at; 
    }
    
    public function getUpdatedAt() { 
        return $this->updated_at; 
    }
    
    public function getOrganisateurNom() { 
        return $this->organisateur_nom; 
    }
    
    public function getOrganisateurEmail() { 
        return $this->organisateur_email; 
    }
    
    public function getOrganisateurTelephone() { 
        return $this->organisateur_telephone; 
    }

    // ==================== MÉTHODES DE VALIDATION ====================
    
    /**
     * Validation complète de l'événement (tous les champs)
     * @return bool
     */
    public function isValid() {
        try {
            $this->setTitre($this->titre);
            $this->setDescription($this->description);
            $this->setDate($this->date_event);
            $this->setLieu($this->lieu);
            $this->setType($this->type);
            $this->setOrganisateurId($this->organisateur_id);
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
        try { $this->setTitre($this->titre); } catch (Exception $e) { $errors['titre'] = $e->getMessage(); }
        try { $this->setDescription($this->description); } catch (Exception $e) { $errors['description'] = $e->getMessage(); }
        try { $this->setDate($this->date_event); } catch (Exception $e) { $errors['date'] = $e->getMessage(); }
        try { $this->setLieu($this->lieu); } catch (Exception $e) { $errors['lieu'] = $e->getMessage(); }
        try { $this->setType($this->type); } catch (Exception $e) { $errors['type'] = $e->getMessage(); }
        try { $this->setOrganisateurId($this->organisateur_id); } catch (Exception $e) { $errors['organisateur_id'] = $e->getMessage(); }
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
                case 'titre': $this->setTitre($this->titre); break;
                case 'description': $this->setDescription($this->description); break;
                case 'date': $this->setDate($this->date_event); break;
                case 'lieu': $this->setLieu($this->lieu); break;
                case 'type': $this->setType($this->type); break;
                case 'organisateur_id': $this->setOrganisateurId($this->organisateur_id); break;
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
                case 'titre': $this->setTitre($this->titre); break;
                case 'description': $this->setDescription($this->description); break;
                case 'date': $this->setDate($this->date_event); break;
                case 'lieu': $this->setLieu($this->lieu); break;
                case 'type': $this->setType($this->type); break;
                case 'organisateur_id': $this->setOrganisateurId($this->organisateur_id); break;
                default: return null;
            }
            return null;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // ==================== MÉTHODES DE FORMATAGE ====================
    
    /**
     * Formater la date en français (dd/mm/yyyy)
     * @return string
     */
    public function getFormattedDate() {
        if (empty($this->date_event)) {
            return '';
        }
        $date = new DateTime($this->date_event);
        return $date->format('d/m/Y');
    }

    /**
     * Formater la date avec le jour de la semaine (Lundi 15 décembre 2025)
     * @return string
     */
    public function getFormattedDateLong() {
        if (empty($this->date_event)) {
            return '';
        }
        $date = new DateTime($this->date_event);
        $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        
        return $jours[(int)$date->format('w')] . ' ' . $date->format('d') . ' ' . $mois[(int)$date->format('n')-1] . ' ' . $date->format('Y');
    }

    /**
     * Obtenir le label du type avec icône
     * @return string
     */
    public function getTypeLabel() {
        $types = [
            'Atelier' => '🧑‍🍳 Atelier',
            'Conférence' => '🎤 Conférence',
            'Festival' => '🎉 Festival',
            'Autre' => '📌 Autre'
        ];
        return $types[$this->type] ?? $this->type;
    }

    /**
     * Obtenir la classe CSS du type pour le styling
     * @return string
     */
    public function getTypeClass() {
        $classes = [
            'Atelier' => 'type-Atelier',
            'Conférence' => 'type-Conférence',
            'Festival' => 'type-Festival',
            'Autre' => 'type-Autre'
        ];
        return $classes[$this->type] ?? 'type-Autre';
    }

    /**
     * Obtenir l'icône du lieu
     * @return string
     */
    public function getLieuIcon() {
        $lieux = [
            'Tunis' => '📍',
            'Sfax' => '📍',
            'Sousse' => '📍',
            'Hammamet' => '🏖️',
            'Monastir' => '🏖️',
            'Nabeul' => '🏖️'
        ];
        return $lieux[$this->lieu] ?? '📍';
    }

    // ==================== MÉTHODES DE STATUT ====================
    
    /**
     * Vérifier si l'événement est à venir
     * @return bool
     */
    public function isUpcoming() {
        if (empty($this->date_event)) {
            return false;
        }
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $eventDate = new DateTime($this->date_event);
        return $eventDate >= $today;
    }

    /**
     * Vérifier si l'événement est passé
     * @return bool
     */
    public function isPast() {
        if (empty($this->date_event)) {
            return false;
        }
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $eventDate = new DateTime($this->date_event);
        return $eventDate < $today;
    }

    /**
     * Vérifier si l'événement est aujourd'hui
     * @return bool
     */
    public function isToday() {
        if (empty($this->date_event)) {
            return false;
        }
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $eventDate = new DateTime($this->date_event);
        return $eventDate == $today;
    }

    /**
     * Obtenir le statut de l'événement avec label, classe et icône
     * @return array
     */
    public function getStatus() {
        if ($this->isToday()) {
            return ['label' => 'Aujourd\'hui', 'class' => 'status-today', 'icon' => '🔴'];
        } elseif ($this->isUpcoming()) {
            return ['label' => 'À venir', 'class' => 'status-upcoming', 'icon' => '📅'];
        } else {
            return ['label' => 'Passé', 'class' => 'status-past', 'icon' => '✅'];
        }
    }

    /**
     * Obtenir le nombre de jours restants avant l'événement
     * @return int|null
     */
    public function getDaysRemaining() {
        if (empty($this->date_event) || $this->isPast()) {
            return null;
        }
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $eventDate = new DateTime($this->date_event);
        $diff = $today->diff($eventDate);
        return (int)$diff->days;
    }

    /**
     * Obtenir le libellé des jours restants
     * @return string
     */
    public function getDaysRemainingLabel() {
        $days = $this->getDaysRemaining();
        if ($days === null) {
            return '';
        }
        if ($days == 0) {
            return "Aujourd'hui !";
        } elseif ($days == 1) {
            return "Demain !";
        } else {
            return "Dans $days jours";
        }
    }

    // ==================== MÉTHODES DE CONVERSION ====================
    
    /**
     * Convertir l'objet en tableau pour la base de données
     * @return array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'description' => $this->description,
            'date_event' => $this->date_event,
            'lieu' => $this->lieu,
            'type' => $this->type,
            'organisateur_id' => $this->organisateur_id
        ];
    }

    /**
     * Créer un objet Evenement à partir d'un tableau (résultat PDO)
     * @param array $data
     * @return Evenement
     */
    public static function fromArray($data) {
        $event = new Evenement(
            $data['titre'] ?? '',
            $data['description'] ?? '',
            $data['date_event'] ?? '',
            $data['lieu'] ?? '',
            $data['type'] ?? '',
            $data['organisateur_id'] ?? 0
        );
        
        if (isset($data['id'])) {
            $event->setId($data['id']);
        }
        if (isset($data['created_at'])) {
            $event->setCreatedAt($data['created_at']);
        }
        if (isset($data['updated_at'])) {
            $event->setUpdatedAt($data['updated_at']);
        }
        if (isset($data['organisateur_nom'])) {
            $event->setOrganisateurNom($data['organisateur_nom']);
        }
        if (isset($data['organisateur_email'])) {
            $event->setOrganisateurEmail($data['organisateur_email']);
        }
        if (isset($data['organisateur_telephone'])) {
            $event->setOrganisateurTelephone($data['organisateur_telephone']);
        }
        
        return $event;
    }

    /**
     * Créer un tableau d'erreurs pour l'affichage dans les formulaires
     * @return array
     */
    public function getValidationErrors() {
        return $this->getErrors();
    }

    /**
     * Vérifier si l'objet a des erreurs de validation
     * @return bool
     */
    public function hasErrors() {
        return !empty($this->getErrors());
    }
}
?>