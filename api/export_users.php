<?php
session_start();
include "../config/db.php";
require_once __DIR__ . '/fpdf186/fpdf.php';

try {
    // Récupérer tous les utilisateurs
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Créer le PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Header
    $pdf->SetFillColor(15, 118, 110); // Couleur Greenbite #0f766e
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 20, 'Liste des Utilisateurs - Greenbite', 0, 1, 'C', true);
    $pdf->Ln(10);

    // Table Header
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(241, 245, 249);
    
    $pdf->Cell(10, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Nom', 1, 0, 'L', true);
    $pdf->Cell(65, 10, 'Email', 1, 0, 'L', true);
    $pdf->Cell(20, 10, 'Age', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Poids', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Calories', 1, 1, 'C', true);

    // Table Body
    $pdf->SetFont('Arial', '', 9);
    foreach ($users as $u) {
        $pdf->Cell(10, 8, $u['id'], 1, 0, 'C');
        $pdf->Cell(45, 8, utf8_decode($u['nom']), 1, 0, 'L');
        $pdf->Cell(65, 8, utf8_decode($u['email']), 1, 0, 'L');
        $pdf->Cell(20, 8, $u['age'], 1, 0, 'C');
        $pdf->Cell(20, 8, $u['poids'] . ' kg', 1, 0, 'C');
        $pdf->Cell(30, 8, $u['calories'] . ' kcal', 1, 1, 'C');
    }

    // Output
    $pdf->Output("D", "utilisateurs_greenbite.pdf");
    exit;

} catch (Exception $e) {
    die("Erreur PDF: " . $e->getMessage());
}
