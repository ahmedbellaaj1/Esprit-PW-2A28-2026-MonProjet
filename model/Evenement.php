<?php
class Evenement {
    private $id;
    private $titre;
    private $description;
    private $date_event;
    private $lieu;
    private $type;

    public function __construct($titre = '', $description = '', $date_event = '', $lieu = '', $type = '') {
        $this->titre = $titre;
        $this->description = $description;
        $this->date_event = $date_event;
        $this->lieu = $lieu;
        $this->type = $type;
    }

    // Setters avec validation PHP
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
        $this->description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function setDate($date) {
        $date = trim($date);
        if (empty($date)) {
            throw new Exception("La date est obligatoire");
        }
        $dateObj = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
            throw new Exception("Format de date invalide. Utilisez le format AAAA-MM-JJ");
        }
        if ($dateObj < new DateTime()) {
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

    // Getters
    public function getId() { return $this->id; }
    public function getTitre() { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getDate() { return $this->date_event; }
    public function getLieu() { return $this->lieu; }
    public function getType() { return $this->type; }

    // Validation complète
    public function isValid() {
        try {
            $this->setTitre($this->titre);
            $this->setDescription($this->description);
            $this->setDate($this->date_event);
            $this->setLieu($this->lieu);
            $this->setType($this->type);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getErrors() {
        $errors = [];
        try { $this->setTitre($this->titre); } catch (Exception $e) { $errors['titre'] = $e->getMessage(); }
        try { $this->setDescription($this->description); } catch (Exception $e) { $errors['description'] = $e->getMessage(); }
        try { $this->setDate($this->date_event); } catch (Exception $e) { $errors['date'] = $e->getMessage(); }
        try { $this->setLieu($this->lieu); } catch (Exception $e) { $errors['lieu'] = $e->getMessage(); }
        try { $this->setType($this->type); } catch (Exception $e) { $errors['type'] = $e->getMessage(); }
        return $errors;
    }

    public function getFormattedDate() {
        $date = new DateTime($this->date_event);
        return $date->format('d/m/Y');
    }

    public function getTypeLabel() {
        $types = [
            'Atelier' => '🧑‍🍳 Atelier',
            'Conférence' => '🎤 Conférence',
            'Festival' => '🎉 Festival',
            'Autre' => '📌 Autre'
        ];
        return $types[$this->type] ?? $this->type;
    }

    public function getTypeClass() {
        $classes = [
            'Atelier' => 'type-Atelier',
            'Conférence' => 'type-Conférence',
            'Festival' => 'type-Festival',
            'Autre' => 'type-Autre'
        ];
        return $classes[$this->type] ?? 'type-Autre';
    }
}
?>