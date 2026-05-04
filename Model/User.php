<?php

declare(strict_types=1);

final class User
{
    private ?int $id;
    private string $nom;
    private string $prenom;
    private string $email;
    private string $motDePasse;
    private ?string $photo;
    private string $role;
    private string $statut;
    private ?string $dateInscription;

    public function __construct(
        ?int $id = null,
        string $nom = '',
        string $prenom = '',
        string $email = '',
        string $motDePasse = '',
        ?string $photo = null,
        string $role = 'user',
        string $statut = 'actif',
        ?string $dateInscription = null
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->motDePasse = $motDePasse;
        $this->photo = $photo;
        $this->role = $role;
        $this->statut = $statut;
        $this->dateInscription = $dateInscription;
    }

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) ($row['nom'] ?? ''),
            (string) ($row['prenom'] ?? ''),
            (string) ($row['email'] ?? ''),
            (string) ($row['mot_de_passe'] ?? ''),
            isset($row['photo']) ? (string) $row['photo'] : null,
            (string) ($row['role'] ?? 'user'),
            (string) ($row['statut'] ?? 'actif'),
            isset($row['date_inscription']) ? (string) $row['date_inscription'] : null
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
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

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): self
    {
        $this->motDePasse = $motDePasse;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateInscription(): ?string
    {
        return $this->dateInscription;
    }

    public function setDateInscription(?string $dateInscription): self
    {
        $this->dateInscription = $dateInscription;
        return $this;
    }
}
