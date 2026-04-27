<?php
declare(strict_types=1);

final class RecetteIngredient
{
    private ?int $idRecette = null;
    private ?int $idIngredient = null;
    private float $quantite = 1.0;
    private string $unite = 'piece';

    public function getIdRecette(): ?int
    {
        return $this->idRecette;
    }

    public function setIdRecette(?int $idRecette): self
    {
        $this->idRecette = $idRecette;
        return $this;
    }

    public function getIdIngredient(): ?int
    {
        return $this->idIngredient;
    }

    public function setIdIngredient(?int $idIngredient): self
    {
        $this->idIngredient = $idIngredient;
        return $this;
    }

    public function getQuantite(): float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getUnite(): string
    {
        return $this->unite;
    }

    public function setUnite(string $unite): self
    {
        $this->unite = $unite;
        return $this;
    }
}
