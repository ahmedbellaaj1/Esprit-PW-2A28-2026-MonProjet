<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../Model/Produit.php';
require_once __DIR__ . '/../Model/Commande.php';



function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function requireAuth(): void
{
    if (empty($_SESSION['user'])) {
        setFlash('error', 'Vous devez être connecté.');
        redirect('/projetwebnova/View/front-office/produits.php');
    }
}

function requireAdmin(): void
{
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        setFlash('error', 'Accès réservé aux administrateurs.');
        redirect('/projetwebnova/View/back-office/commandes.php');
    }
}



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/projetwebnova/View/front-office/produits.php');
}

$action        = $_POST['action'] ?? '';
$commandeModel = new Commande();
$produitModel  = new Produit();

try {

    if ($action === 'create_commande') {
        requireAuth();

        $id_produit        = (int) ($_POST['id_produit']        ?? 0);
        $quantite          = (int) ($_POST['quantite']          ?? 1);
        $adresse_livraison = trim($_POST['adresse_livraison']   ?? '');
        $id_utilisateur    = (int) $_SESSION['user']['id'];

        if ($id_produit <= 0) {
            setFlash('error', 'Produit invalide.');
            redirect('/projetwebnova/View/front-office/produits.php');
        }

        if ($quantite < 1) {
            setFlash('error', 'La quantite doit etre au moins 1.');
            redirect('/projetwebnova/View/front-office/produits.php');
        }

        if ($adresse_livraison === '') {
            setFlash('error', 'L adresse de livraison est obligatoire.');
            redirect('/projetwebnova/View/front-office/produits.php');
        }

        $produit = $produitModel->findById($id_produit);

        if ($produit === null) {
            setFlash('error', 'Produit introuvable.');
            redirect('/projetwebnova/View/front-office/produits.php');
        }

        if ($produit['statut'] === 'rupture') {
            setFlash('error', 'Ce produit est en rupture de stock.');
            redirect('/projetwebnova/View/front-office/produits.php');
        }

        $prix_total = round((float) $produit['prix'] * $quantite, 2);

        $commandeModel->create([
            'id_produit'        => $id_produit,
            'id_utilisateur'    => $id_utilisateur,
            'quantite'          => $quantite,
            'prix_total'        => $prix_total,
            'adresse_livraison' => $adresse_livraison,
            'statut'            => 'en_attente',
        ]);

        setFlash('success', 'Commande passee avec succes ! Nous vous contacterons bientot.');
        redirect('/projetwebnova/View/front-office/produits.php');
    }

    // ── UPDATE STATUT COMMANDE ────────────────────────────────────────────────
    if ($action === 'update_statut_commande') {
        requireAdmin();

        $id_commande    = (int) ($_POST['id_commande'] ?? 0);
        $statut         = trim($_POST['statut']        ?? '');
        $statutsValides = ['en_attente', 'confirmee', 'expediee', 'livree', 'annulee'];

        if ($id_commande <= 0 || !in_array($statut, $statutsValides, true)) {
            setFlash('error', 'Donnees invalides.');
            redirect('/projetwebnova/View/back-office/commandes.php');
        }

        $commandeModel->updateStatut($id_commande, $statut);

        setFlash('success', 'Statut de la commande mis a jour.');
        redirect('/projetwebnova/View/back-office/commandes.php');
    }

    // ── DELETE COMMANDE ───────────────────────────────────────────────────────
    if ($action === 'delete_commande') {
        requireAdmin();

        $id_commande = (int) ($_POST['id_commande'] ?? 0);

        if ($id_commande <= 0) {
            setFlash('error', 'ID commande invalide.');
            redirect('/projetwebnova/View/back-office/commandes.php');
        }

        $commandeModel->delete($id_commande);

        setFlash('success', 'Commande supprimee.');
        redirect('/projetwebnova/View/back-office/commandes.php');
    }

    setFlash('error', 'Action non reconnue.');
    redirect('/projetwebnova/View/back-office/commandes.php');

} catch (Throwable $e) {
    setFlash('error', 'Erreur serveur: ' . $e->getMessage());
    redirect('/projetwebnova/View/back-office/commandes.php');
}