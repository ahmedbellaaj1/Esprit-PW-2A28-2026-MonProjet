<?php

declare(strict_types=1);

final class Favoris
{
    private ?int $idFavoris = null;
    private ?int $idRecette = null;
    private ?int $idUser = null;

    public function __construct(
        ?int $idFavoris = null,
        ?int $idRecette = null,
        ?int $idUser = null
    ) {
        $this->idFavoris = $idFavoris;
        $this->idRecette = $idRecette;
        $this->idUser = $idUser;
    }

    /**
     * Crée une instance Favoris à partir d'un tableau (résultat de requête SQL)
     */
    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id_favoris']) ? (int) $row['id_favoris'] : null,
            isset($row['id_recette']) ? (int) $row['id_recette'] : null,
            isset($row['id_user']) ? (int) $row['id_user'] : null
        );
    }

    public function getIdFavoris(): ?int
    {
        return $this->idFavoris;
    }

    public function setIdFavoris(?int $idFavoris): self
    {
        $this->idFavoris = $idFavoris;
        return $this;
    }

    public function getIdRecette(): ?int
    {
        return $this->idRecette;
    }

    public function setIdRecette(?int $idRecette): self
    {
        $this->idRecette = $idRecette;
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
