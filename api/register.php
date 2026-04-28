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
    $user_id = $pdo->lastInsertId();
    $_SESSION['user_id'] = $user_id;

    // --- SAVE PREFERENCES ---
    // On split les préférences ou on crée un tableau avec un élément vide si aucune préférence n'est donnée
    // pour s'assurer que le poids, l'âge et les calories sont bien enregistrés au moins une fois dans cette table.
    $prefList = !empty($preferences) ? array_map('trim', explode(',', $preferences)) : [null];
    
    $stmtPref = $pdo->prepare("INSERT INTO preferences_alimentaires (id_user, type_preference, poids, age, calories) VALUES (?, ?, ?, ?, ?)");
    foreach ($prefList as $p) {
        // On évite d'insérer des chaînes vides, on préfère NULL
        $pVal = ($p === '') ? null : $p;
        $stmtPref->execute([$user_id, $pVal, $poids, $age, $calories]);
    }

    // --- SAVE ALLERGIES ---
    if (!empty($allergies)) {
        $allList = array_map('trim', explode(',', $allergies));
        $stmtAll = $pdo->prepare("INSERT INTO allergies (id_user, nom_allergie) VALUES (?, ?)");
        foreach ($allList as $a) {
            if ($a !== '') {
                $stmtAll->execute([$user_id, $a]);
            }
        }
    }

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