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

    // ==================== SETTERS AVEC VALIDATION PHP ====================
    
    public function setId($id) {
        $this->id = (int)$id;
        return $this;
    }

    public function setTitre($titre) {
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
        if (!preg_match('/^[a-zA-Z0-9\s\-\'àâäéèêëîïôöùûüçÀÂÄÉÈÊËÎÏÔÖÙÛÜÇ]+$/', $titre)) {
            throw new Exception("Le titre contient des caractères non autorisés");
        }
        $this->titre = htmlspecialchars($titre, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function setDescription($description) {
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
        $this->description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function setDate($date) {
        $date = trim($date);
        if (empty($date)) {
            throw new Exception("La date est obligatoire");
        }
        
        // Vérifier le format YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new Exception("Format de date invalide. Utilisez le format AAAA-MM-JJ");
        }
        
        $dateObj = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
            throw new Exception("Date invalide");
        }
        
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        if ($dateObj < $today) {
            throw new Exception("La date ne peut pas être dans le passé");
        }
        
        $this->date_event = $date;
        return $this;
    }

    public function setLieu($lieu) {
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
        $this->lieu = htmlspecialchars($lieu, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function setType($type) {
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
        $organisateur_id = (int)$organisateur_id;
        if ($organisateur_id <= 0) {
            throw new Exception("L'organisateur est obligatoire");
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
        $this->organisateur_nom = $nom;
        return $this;
    }

    public function setOrganisateurEmail($email) {
        $this->organisateur_email = $email;
        return $this;
    }

    public function setOrganisateurTelephone($telephone) {
        $this->organisateur_telephone = $telephone;
        return $this;
    }

    // ==================== GETTERS ====================
    
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

    // ==================== MÉTHODES UTILITAIRES ====================
    
    /**
     * Validation complète de l'événement
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
     * Récupérer tous les messages d'erreur
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
     * Formater la date en français
     */
    public function getFormattedDate() {
        if (empty($this->date_event)) {
            return '';
        }
        $date = new DateTime($this->date_event);
        return $date->format('d/m/Y');
    }

    /**
     * Formater la date avec le jour de la semaine
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
     * Obtenir la classe CSS du type
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

    /**
     * Vérifier si l'événement est à venir
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
     * Obtenir le statut de l'événement
     */
    public function getStatus() {
        if ($this->isUpcoming()) {
            return ['label' => 'À venir', 'class' => 'status-upcoming', 'icon' => '📅'];
        } else {
            return ['label' => 'Passé', 'class' => 'status-past', 'icon' => '✅'];
        }
    }

    /**
     * Convertir l'objet en tableau pour la base de données
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
}
?>