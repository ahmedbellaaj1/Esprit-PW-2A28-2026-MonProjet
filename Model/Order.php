<?php

declare(strict_types=1);

final class Order
{
    private ?int $idCommande = null;
    private int $idProduit = 0;
    private int $idUtilisateur = 0;
    private int $quantite = 0;
    private float $prixTotal = 0.0;
    private string $dateCommande = '';
    private string $statut = 'en-cours';
    private string $adresseLivraison = '';
    private string $modeLivraison = 'standard';
    private ?string $dateLivraisonSouhaitee = null;
    private string $methodePaiement = 'cash';
    private ?string $numeroCarte = null;
    private ?string $nomTitulaire = null;
    private ?string $dateExpiration = null;
    private ?string $cvv = null;

    // Getters
    public function getIdCommande(): ?int
    {
        return $this->idCommande;
    }

    public function getIdProduit(): int
    {
        return $this->idProduit;
    }

    public function getIdUtilisateur(): int
    {
        return $this->idUtilisateur;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function getPrixTotal(): float
    {
        return $this->prixTotal;
    }

    public function getDateCommande(): string
    {
        return $this->dateCommande;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getAdresseLivraison(): string
    {
        return $this->adresseLivraison;
    }

    public function getModeLivraison(): string
    {
        return $this->modeLivraison;
    }

    public function getDateLivraisonSouhaitee(): ?string
    {
        return $this->dateLivraisonSouhaitee;
    }

    public function getMethodePaiement(): string
    {
        return $this->methodePaiement;
    }

    public function getNumeroCarte(): ?string
    {
        return $this->numeroCarte;
    }

    public function getNomTitulaire(): ?string
    {
        return $this->nomTitulaire;
    }

    public function getDateExpiration(): ?string
    {
        return $this->dateExpiration;
    }

    public function getCvv(): ?string
    {
        return $this->cvv;
    }

    // Setters
    public function setIdCommande(?int $id): self
    {
        $this->idCommande = $id;
        return $this;
    }

    public function setIdProduit(int $id): self
    {
        $this->idProduit = $id;
        return $this;
    }

    public function setIdUtilisateur(int $id): self
    {
        $this->idUtilisateur = $id;
        return $this;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function setPrixTotal(float $prix): self
    {
        $this->prixTotal = $prix;
        return $this;
    }

    public function setDateCommande(string $date): self
    {
        $this->dateCommande = $date;
        return $this;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function setAdresseLivraison(string $adresse): self
    {
        $this->adresseLivraison = $adresse;
        return $this;
    }

    public function setModeLivraison(string $mode): self
    {
        $this->modeLivraison = $mode;
        return $this;
    }

    public function setDateLivraisonSouhaitee(?string $date): self
    {
        $this->dateLivraisonSouhaitee = $date;
        return $this;
    }

    public function setMethodePaiement(string $methode): self
    {
        $this->methodePaiement = $methode;
        return $this;
    }

    public function setNumeroCarte(?string $numero): self
    {
        $this->numeroCarte = $numero;
        return $this;
    }

    public function setNomTitulaire(?string $nom): self
    {
        $this->nomTitulaire = $nom;
        return $this;
    }

    public function setDateExpiration(?string $date): self
    {
        $this->dateExpiration = $date;
        return $this;
    }

    public function setCvv(?string $cvv): self
    {
        $this->cvv = $cvv;
        return $this;
    }

    // Logique métier
    public function isConfirmed(): bool
    {
        return $this->statut === 'confirmee';
    }

    public function isPending(): bool
    {
        return in_array($this->statut, ['en-cours', 'en-preparation'], true);
    }

    public function isCancelled(): bool
    {
        return $this->statut === 'annulee';
    }

    public function isDelivered(): bool
    {
        return $this->statut === 'livree';
    }

    public function getPrixUnitaire(): float
    {
        return $this->quantite > 0 ? $this->prixTotal / $this->quantite : 0.0;
    }

    public function toArray(): array
    {
        return [
            'id_commande' => $this->idCommande,
            'id_produit' => $this->idProduit,
            'id_utilisateur' => $this->idUtilisateur,
            'quantite' => $this->quantite,
            'prix_total' => $this->prixTotal,
            'date_commande' => $this->dateCommande,
            'statut' => $this->statut,
            'adresse_livraison' => $this->adresseLivraison,
            'mode_livraison' => $this->modeLivraison,
            'date_livraison_souhaitee' => $this->dateLivraisonSouhaitee,
            'methode_paiement' => $this->methodePaiement,
            'numero_carte' => $this->numeroCarte,
            'nom_titulaire' => $this->nomTitulaire,
            'date_expiration' => $this->dateExpiration,
            'cvv' => $this->cvv,
        ];
    }

    public static function fromArray(array $data): self
    {
        $order = new self();
        $order->setIdCommande($data['id_commande'] ?? null);
        $order->setIdProduit((int) ($data['id_produit'] ?? 0));
        $order->setIdUtilisateur((int) ($data['id_utilisateur'] ?? 0));
        $order->setQuantite((int) ($data['quantite'] ?? 0));
        $order->setPrixTotal((float) ($data['prix_total'] ?? 0));
        $order->setDateCommande($data['date_commande'] ?? '');
        $order->setStatut($data['statut'] ?? 'en-cours');
        $order->setAdresseLivraison($data['adresse_livraison'] ?? '');
        $order->setModeLivraison($data['mode_livraison'] ?? 'standard');
        $order->setDateLivraisonSouhaitee($data['date_livraison_souhaitee'] ?? null);
        $order->setMethodePaiement($data['methode_paiement'] ?? 'cash');
        $order->setNumeroCarte($data['numero_carte'] ?? null);
        $order->setNomTitulaire($data['nom_titulaire'] ?? null);
        $order->setDateExpiration($data['date_expiration'] ?? null);
        $order->setCvv($data['cvv'] ?? null);
        return $order;
    }
}
