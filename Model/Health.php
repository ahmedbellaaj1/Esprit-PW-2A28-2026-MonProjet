<?php

declare(strict_types=1);

final class Health
{
    private int $idUser;
    private ?string $preferenceAlimentaire;
    private ?string $allergies;
    private ?float $poids;
    private ?int $age;
    private ?float $taille;
    private ?string $sexe;

    public function __construct(
        int $idUser,
        ?string $preferenceAlimentaire = null,
        ?string $allergies = null,
        ?float $poids = null,
        ?int $age = null,
        ?float $taille = null,
        ?string $sexe = null
    ) {
        $this->idUser = $idUser;
        $this->preferenceAlimentaire = $preferenceAlimentaire;
        $this->allergies = $allergies;
        $this->poids = $poids;
        $this->age = $age;
        $this->taille = $taille;
        $this->sexe = $sexe;
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int) ($row['id_user'] ?? 0),
            isset($row['preference_alimentaire']) ? (string) $row['preference_alimentaire'] : null,
            isset($row['allergies']) ? (string) $row['allergies'] : null,
            isset($row['poids']) ? (float) $row['poids'] : null,
            isset($row['age']) ? (int) $row['age'] : null,
            isset($row['taille']) ? (float) $row['taille'] : null,
            isset($row['sexe']) ? (string) $row['sexe'] : null
        );
    }

    public function getIdUser(): int
    {
        return $this->idUser;
    }

    public function getPreferenceAlimentaire(): ?string
    {
        return $this->preferenceAlimentaire;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function getTaille(): ?float
    {
        return $this->taille;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }
}
