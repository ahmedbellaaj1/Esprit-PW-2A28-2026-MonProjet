<?php
class Organisateur {
    private $id;
    private $nom;
    private $email;
    private $telephone;
    private $adresse;
    private $site_web;

    public function __construct($nom = '', $email = '', $telephone = '', $adresse = '', $site_web = '') {
        $this->nom = $nom;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->adresse = $adresse;
        $this->site_web = $site_web;
    }

    // Setters avec validation PHP
    public function setId($id) {
        $this->id = (int)$id;
        return $this;
    }

    public function setNom($nom) {
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
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $nom)) {
            throw new Exception("Le nom ne peut contenir que des lettres, espaces et tirets");
        }
        $this->nom = htmlspecialchars($nom, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function setEmail($email) {
        $email = trim($email);
        if (empty($email)) {
            throw new Exception("L'email est obligatoire");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Format d'email invalide");
        }
        if (strlen($email) > 150) {
            throw new Exception("L'email ne peut pas dépasser 150 caractères");
        }
        $this->email = strtolower($email);
        return $this;
    }

    public function setTelephone($telephone) {
        $telephone = trim($telephone);
        if (empty($telephone)) {
            throw new Exception("Le numéro de téléphone est obligatoire");
        }
        if (!preg_match('/^[0-9+\-\s]{8,20}$/', $telephone)) {
            throw new Exception("Format de téléphone invalide (ex: 71 123 456)");
        }
        $this->telephone = htmlspecialchars($telephone, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function setAdresse($adresse) {
        $adresse = trim($adresse);
        if (!empty($adresse) && strlen($adresse) < 5) {
            throw new Exception("L'adresse doit contenir au moins 5 caractères");
        }
        $this->adresse = htmlspecialchars($adresse, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function setSiteWeb($site_web) {
        $site_web = trim($site_web);
        if (!empty($site_web)) {
            if (!filter_var($site_web, FILTER_VALIDATE_URL)) {
                throw new Exception("Format d'URL invalide (ex: https://exemple.com)");
            }
        }
        $this->site_web = htmlspecialchars($site_web, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getEmail() { return $this->email; }
    public function getTelephone() { return $this->telephone; }
    public function getAdresse() { return $this->adresse; }
    public function getSiteWeb() { return $this->site_web; }

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

    public function getErrors() {
        $errors = [];
        try { $this->setNom($this->nom); } catch (Exception $e) { $errors['nom'] = $e->getMessage(); }
        try { $this->setEmail($this->email); } catch (Exception $e) { $errors['email'] = $e->getMessage(); }
        try { $this->setTelephone($this->telephone); } catch (Exception $e) { $errors['telephone'] = $e->getMessage(); }
        try { $this->setAdresse($this->adresse); } catch (Exception $e) { $errors['adresse'] = $e->getMessage(); }
        try { $this->setSiteWeb($this->site_web); } catch (Exception $e) { $errors['site_web'] = $e->getMessage(); }
        return $errors;
    }
}
?>