<?php

class User implements JsonSerializable {
    private $id;
    private $nom;
    private $email;
    private $preferences;
    private $allergies;
    private $poids;
    private $age;
    private $calories;

    public function __construct($nom = null, $email = null, $preferences = null, $allergies = null, $poids = 0, $age = 0, $calories = 0) {
        $this->nom = $nom;
        $this->email = $email;
        $this->preferences = $preferences;
        $this->allergies = $allergies;
        $this->poids = $poids;
        $this->age = $age;
        $this->calories = $calories;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getEmail() { return $this->email; }
    public function getPreferences() { return $this->preferences; }
    public function getAllergies() { return $this->allergies; }
    public function getPoids() { return $this->poids; }
    public function getAge() { return $this->age; }
    public function getCalories() { return $this->calories; }

    // Setters
    public function setId($id) { $this->id = (int)$id; }
    public function setNom($nom) { $this->nom = trim((string)$nom); }
    public function setEmail($email) { $this->email = trim((string)$email); }
    public function setPreferences($preferences) { $this->preferences = $preferences; }
    public function setAllergies($allergies) { $this->allergies = $allergies; }
    public function setPoids($poids) { $this->poids = (float)$poids; }
    public function setAge($age) { $this->age = (int)$age; }
    public function setCalories($calories) { $this->calories = (int)$calories; }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'email' => $this->email,
            'preferences' => $this->preferences,
            'allergies' => $this->allergies,
            'poids' => $this->poids,
            'age' => $this->age,
            'calories' => $this->calories
        ];
    }
}
