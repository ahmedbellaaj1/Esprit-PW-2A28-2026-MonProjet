<?php
declare(strict_types=1);

final class Ingredient
{
    private ?int $idIngredient = null;
    private string $nom = '';
    private string $bio = 'non';
    private string $local = 'non';
    private string $saisonnier = 'non';

    public function getIdIngredient(): ?int
    {
        return $this->idIngredient;
    }

    public function setIdIngredient(?int $idIngredient): self
    {
        $this->idIngredient = $idIngredient;
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

    public function getBio(): string
    {
        return $this->bio;
    }

    public function setBio(string $bio): self
    {
        $this->bio = $bio;
        return $this;
    }

    public function getLocal(): string
    {
        return $this->local;
    }

    public function setLocal(string $local): self
    {
        $this->local = $local;
        return $this;
    }

    public function getSaisonnier(): string
    {
        return $this->saisonnier;
    }

    public function setSaisonnier(string $saisonnier): self
    {
        $this->saisonnier = $saisonnier;
        return $this;
    }
}
