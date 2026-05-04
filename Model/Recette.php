<?php
declare(strict_types=1);

final class Recette
{
    private ?int $idRecette = null;
    private string $nom = '';
    private float $calories = 0.0;
    private string $description = '';
    private ?string $dateCreation = null;
    private ?string $dureePrep = null;
    private ?int $idUser = null;

    public function getIdRecette(): ?int
    {
        return $this->idRecette;
    }

    public function setIdRecette(?int $idRecette): self
    {
        $this->idRecette = $idRecette;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getCalories(): float
    {
        return $this->calories;
    }

    public function setCalories(float $calories): self
    {
        $this->calories = $calories;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateCreation(): ?string
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?string $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDureePrep(): ?string
    {
        return $this->dureePrep;
    }

    public function setDureePrep(?string $dureePrep): self
    {
        $this->dureePrep = $dureePrep;
        return $this;
    }

    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    public function setIdUser(?int $idUser): self
    {
        $this->idUser = $idUser;
        return $this;
    }
}
