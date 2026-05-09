<?php
session_start();
include "../config/db.php";
require_once __DIR__ . '/../controller/UserController.php';
require_once __DIR__ . '/../assets/libs/fpdf186/fpdf.php';

try {
    // MVC: Call Controller
    $user = UserController::register($pdo, $_POST);

    // 📄 CREATION PDF (Part of the "Business" requirement in this specific script)
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);

    $pdf->Cell(0,10,'Greenbite Profil',0,1,'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,10,"Nom: " . $user->getNom(),0,1);
    $pdf->Cell(0,10,"Email: " . $user->getEmail(),0,1);
    $pdf->Cell(0,10,"Preferences: " . $user->getPreferences(),0,1);
    $pdf->Cell(0,10,"Allergies: " . $user->getAllergies(),0,1);
    $pdf->Cell(0,10,"Poids: " . $user->getPoids() . " kg",0,1);
    $pdf->Cell(0,10,"Age: " . $user->getAge() . " ans",0,1);
    $pdf->Cell(0,10,"Calories: " . $user->getCalories() . " kcal",0,1);

    // 📥 DOWNLOAD PDF DIRECT
    $pdf->Output("D", "profil_greenbite.pdf");
    exit;

} catch (Exception $e) {
    die("Erreur: " . $e->getMessage());
}