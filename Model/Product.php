<?php

declare(strict_types=1);

final class Product
{
    private ?int $idProduit = null;
    private string $nom = '';
    private string $marque = '';
    private string $codeBarre = '';
    private string $categorie = '';
    private float $prix = 0.0;
    private float $calories = 0.0;
    private float $proteines = 0.0;
    private float $glucides = 0.0;
    private float $lipides = 0.0;
    private string $nutriscore = 'C';
    private string $image = '';
    private string $statut = 'actif';
    private ?string $dateAjout = null;

    // Getters
    public function getIdProduit(): ?int
    {
        return $this->idProduit;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getMarque(): string
    {
        return $this->marque;
    }

    public function getCodeBarre(): string
    {
        return $this->codeBarre;
    }

    public function getCategorie(): string
    {
        return $this->categorie;
    }

    public function getPrix(): float
    {
        return $this->prix;
    }

    public function getCalories(): float
    {
        return $this->calories;
    }

    public function getProteines(): float
    {
        return $this->proteines;
    }

    public function getGlucides(): float
    {
        return $this->glucides;
    }

    public function getLipides(): float
    {
        return $this->lipides;
    }

    public function getNutriscore(): string
    {
        return $this->nutriscore;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getDateAjout(): ?string
    {
        return $this->dateAjout;
    }

    // Setters
    public function setIdProduit(?int $id): self
    {
        $this->idProduit = $id;
        return $this;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function setMarque(string $marque): self
    {
        $this->marque = $marque;
        return $this;
    }

    public function setCodeBarre(string $codeBarre): self
    {
        $this->codeBarre = $codeBarre;
        return $this;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function setCalories(float $calories): self
    {
        $this->calories = $calories;
        return $this;
    }

    public function setProteines(float $proteines): self
    {
        $this->proteines = $proteines;
        return $this;
    }

    public function setGlucides(float $glucides): self
    {
        $this->glucides = $glucides;
        return $this;
    }

    public function setLipides(float $lipides): self
    {
        $this->lipides = $lipides;
        return $this;
    }

    public function setNutriscore(string $nutriscore): self
    {
        $this->nutriscore = strtoupper($nutriscore);
        return $this;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function setDateAjout(?string $dateAjout): self
    {
        $this->dateAjout = $dateAjout;
        return $this;
    }

    // Logique métier
    public function isActive(): bool
    {
        return $this->statut === 'actif';
    }

    public function isPending(): bool
    {
        return $this->statut === 'attente';
    }

    public function isInactive(): bool
    {
        return $this->statut === 'inactif';
    }

    public function getTotalNutritionalValue(): float
    {
        return $this->calories + $this->proteines + $this->glucides + $this->lipides;
    }

    public function toArray(): array
    {
        return [
            'id_produit' => $this->idProduit,
            'nom' => $this->nom,
            'marque' => $this->marque,
            'code_barre' => $this->codeBarre,
            'categorie' => $this->categorie,
            'prix' => $this->prix,
            'calories' => $this->calories,
            'proteines' => $this->proteines,
            'glucides' => $this->glucides,
            'lipides' => $this->lipides,
            'nutriscore' => $this->nutriscore,
            'image' => $this->image,
            'statut' => $this->statut,
            'date_ajout' => $this->dateAjout,
        ];
    }

    public static function fromArray(array $data): self
    {
        $product = new self();
        $product->setIdProduit($data['id_produit'] ?? null);
        $product->setNom($data['nom'] ?? '');
        $product->setMarque($data['marque'] ?? '');
        $product->setCodeBarre($data['code_barre'] ?? '');
        $product->setCategorie($data['categorie'] ?? '');
        $product->setPrix((float) ($data['prix'] ?? 0));
        $product->setCalories((float) ($data['calories'] ?? 0));
        $product->setProteines((float) ($data['proteines'] ?? 0));
        $product->setGlucides((float) ($data['glucides'] ?? 0));
        $product->setLipides((float) ($data['lipides'] ?? 0));
        $product->setNutriscore($data['nutriscore'] ?? 'C');
        $product->setImage($data['image'] ?? '');
        $product->setStatut($data['statut'] ?? 'actif');
        $product->setDateAjout($data['date_ajout'] ?? null);
        return $product;
    }
}
