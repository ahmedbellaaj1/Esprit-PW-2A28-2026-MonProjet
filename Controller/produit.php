<?php
declare(strict_types=1);


session_start();
require_once __DIR__ . '/../Model/Produit.php';

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/projetwebnova/View/back-office/produits.php');
}

$action = $_POST['action'] ?? '';
$produitModel = new Produit();

try {

    if ($action === 'create_produit') {
        
        $nom = trim($_POST['nom'] ?? '');
        $prix = (float)($_POST['prix'] ?? 0);

        if (empty($nom) || $prix <= 0) {
            setFlash('error', 'Le nom et le prix sont obligatoires.');
            redirect('/projetwebnova/View/back-office/produits.php');
        }

        $produitModel->create([
            'nom'        => $nom,
            'marque'     => $_POST['marque'] ?? null,
            'code_barre' => $_POST['code_barre'] ?? null,
            'categorie'  => $_POST['categorie'] ?? null,
            'prix'       => $prix,
            'calories'   => !empty($_POST['calories']) ? (float)$_POST['calories'] : null,
            'proteines'  => !empty($_POST['proteines']) ? (float)$_POST['proteines'] : null,
            'glucides'   => !empty($_POST['glucides']) ? (float)$_POST['glucides'] : null,
            'lipides'    => !empty($_POST['lipides']) ? (float)$_POST['lipides'] : null,
            'nutriscore' => $_POST['nutriscore'] ?? 'C',
            'statut'     => $_POST['statut'] ?? 'disponible',
            'image'      => null
        ]);

        setFlash('success', '✅ Produit ajouté avec succès !');
        redirect('/projetwebnova/View/back-office/produits.php');
    }

    if ($action === 'update_produit') {
        $id = (int)($_POST['id_produit'] ?? 0);
        if ($id > 0) {
            $produitModel->update($id, [
                'nom'        => trim($_POST['nom'] ?? ''),
                'marque'     => $_POST['marque'] ?? null,
                'code_barre' => $_POST['code_barre'] ?? null,
                'categorie'  => $_POST['categorie'] ?? null,
                'prix'       => (float)($_POST['prix'] ?? 0),
                'calories'   => !empty($_POST['calories']) ? (float)$_POST['calories'] : null,
                'proteines'  => !empty($_POST['proteines']) ? (float)$_POST['proteines'] : null,
                'glucides'   => !empty($_POST['glucides']) ? (float)$_POST['glucides'] : null,
                'lipides'    => !empty($_POST['lipides']) ? (float)$_POST['lipides'] : null,
                'nutriscore' => $_POST['nutriscore'] ?? 'C',
                'statut'     => $_POST['statut'] ?? 'disponible'
            ]);
            setFlash('success', '✅ Produit modifié avec succès !');
        }
        redirect('/projetwebnova/View/back-office/produits.php');
    }

    if ($action === 'delete_produit') {
        $id = (int)($_POST['id_produit'] ?? 0);
        if ($id > 0) {
            $produitModel->delete($id);
            setFlash('success', '✅ Produit supprimé !');
        }
        redirect('/projetwebnova/View/back-office/produits.php');
    }

    setFlash('error', 'Action non reconnue.');
    redirect('/projetwebnova/View/back-office/produits.php');

} catch (Throwable $e) {
    // Debug important
    error_log("CRUD Erreur : " . $e->getMessage());
    setFlash('error', 'Erreur serveur : ' . $e->getMessage());
    redirect('/projetwebnova/View/back-office/produits.php');
}