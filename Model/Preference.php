<?php

declare(strict_types=1);

final class Preference
{
    private ?int $id;
    private string $nom;
    private ?string $description;

    public function __construct(?int $id, string $nom, ?string $description = null)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
    }

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) ($row['nom'] ?? ''),
            isset($row['description']) ? (string) $row['description'] : null
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getDescription(): ?string { return $this->description; }
}
