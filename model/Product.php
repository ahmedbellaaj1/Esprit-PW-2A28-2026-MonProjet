<?php

class Product implements JsonSerializable {
    private $id;
    private $nom;
    private $categorie;
    private $description;
    private $calories;
    private $prix;
    
    // AI Status Properties (optional)
    public $forbidden = false;
    public $recommended = false;
    public $allowed = true;
    public $check = false;

    public function __construct($nom = null, $categorie = null, $description = null, $calories = 0, $prix = 0.0) {
        $this->nom = $nom;
        $this->categorie = $categorie;
        $this->description = $description;
        $this->calories = $calories;
        $this->prix = $prix;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getCategorie() { return $this->categorie; }
    public function getDescription() { return $this->description; }
    public function getCalories() { return $this->calories; }
    public function getPrix() { return $this->prix; }

    // Setters
    public function setId($id) { $this->id = (int)$id; }
    public function setNom($nom) { $this->nom = trim((string)$nom); }
    public function setCategorie($categorie) { $this->categorie = trim((string)$categorie); }
    public function setDescription($description) { $this->description = trim((string)$description); }
    public function setCalories($calories) { $this->calories = (int)$calories; }
    public function setPrix($prix) { $this->prix = (float)$prix; }
    
    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'categorie' => $this->categorie,
            'description' => $this->description,
            'calories' => $this->calories,
            'prix' => $this->prix,
            'forbidden' => $this->forbidden,
            'recommended' => $this->recommended,
            'allowed' => $this->allowed,
            'check' => $this->check
        ];
    }
}
