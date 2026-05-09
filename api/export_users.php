<?php
session_start();
include "../config/db.php";
require_once __DIR__ . '/../controller/AdminController.php';
require_once __DIR__ . '/../assets/libs/fpdf186/fpdf.php';

try {
    // MVC: Fetch data via Controller
    $data = AdminController::getDashboardData($pdo);
    $users = $data['users'];

    // Créer le PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Header
    $pdf->SetFillColor(15, 118, 110); 
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 20, 'Liste des Preferences Utilisateurs - Greenbite', 0, 1, 'C', true);
    $pdf->Ln(10);

    // Table Header
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(241, 245, 249);
    
    $pdf->Cell(10, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(60, 10, 'Email', 1, 0, 'L', true);
    $pdf->Cell(50, 10, utf8_decode('Préférence'), 1, 0, 'L', true);
    $pdf->Cell(20, 10, utf8_decode('Âge'), 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Poids', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Calories', 1, 1, 'C', true);

    // Table Body
    $pdf->SetFont('Arial', '', 9);
    foreach ($users as $u) {
        $pdf->Cell(10, 8, $u->getId(), 1, 0, 'C');
        $pdf->Cell(60, 8, utf8_decode($u->getEmail()), 1, 0, 'L');
        $pdf->Cell(50, 8, utf8_decode($u->getPreferences() ?? 'Aucune'), 1, 0, 'L');
        $pdf->Cell(20, 8, ($u->getAge() ?? '-'), 1, 0, 'C');
        $pdf->Cell(20, 8, ($u->getPoids() ?? '-') . ' kg', 1, 0, 'C');
        $pdf->Cell(30, 8, ($u->getCalories() ?? '-') . ' kcal', 1, 1, 'C');
    }

    // Output
    $pdf->Output("D", "utilisateurs_greenbite.pdf");
    exit;

} catch (Exception $e) {
    die("Erreur PDF: " . $e->getMessage());
}
