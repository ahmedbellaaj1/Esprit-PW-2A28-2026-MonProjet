<?php
session_start();
include "../config/db.php";
require_once __DIR__ . '/fpdf186/fpdf.php';

// 🔵 Récupération des données
$nom = $_POST['nom'] ?? '';
$email = $_POST['email'] ?? '';
$preferences = $_POST['preferences'] ?? '';
$allergies = $_POST['allergies'] ?? '';
$poids = $_POST['poids'] ?? 0;
$age = $_POST['age'] ?? 0;
$calories = $_POST['calories'] ?? 0;

// 🔴 Vérification simple
if (!$nom || !$email) {
    die("Nom et email obligatoires");
}

try {

    // 🟢 INSERT INTO DATABASE (PDO version)
    $sql = "INSERT INTO users (nom, email, preferences, allergies, poids, age, calories)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $email, $preferences, $allergies, $poids, $age, $calories]);

    // 🔑 Set session so the user is logged in
    $_SESSION['user_id'] = $pdo->lastInsertId();

    // 📄 CREATION PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);

    $pdf->Cell(0,10,'Greenbite Profil',0,1,'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,10,"Nom: $nom",0,1);
    $pdf->Cell(0,10,"Email: $email",0,1);
    $pdf->Cell(0,10,"Preferences: $preferences",0,1);
    $pdf->Cell(0,10,"Allergies: $allergies",0,1);
    $pdf->Cell(0,10,"Poids: $poids kg",0,1);
    $pdf->Cell(0,10,"Age: $age ans",0,1);
    $pdf->Cell(0,10,"Calories: $calories kcal",0,1);

    // 📥 DOWNLOAD PDF DIRECT
    $pdf->Output("D", "profil_greenbite.pdf");
    exit;

} catch (Exception $e) {
    echo "Erreur serveur: " . $e->getMessage();
}
?>