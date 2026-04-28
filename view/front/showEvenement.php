<?php
require_once "../../controller/EvenementController.php";

$controller = new EvenementController();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validation PHP
if ($id <= 0) {
    header('Location: listEvenements.php');
    exit();
}

$event = $controller->getEvenementById($id);

if (!$event) {
    header('Location: listEvenements.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['titre']) ?> - GreenBite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #e6f7f5 100%);
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(90deg, #0f766e 0%, #14b8a6 100%);
            padding: 0 2rem;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 20px rgba(15,118,110,0.25);
        }
        .navbar-logo { font-size: 1.6rem; font-weight: 700; color: white; text-decoration: none; }
        .navbar-logo span { color: #ccfbf1; }
        .nav-btn {
            background: rgba(255,255,255,0.15);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 9999px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .nav-btn:hover { background: rgba(255,255,255,0.25); }
        .show-container {
            max-width: 900px;
            margin: 2rem auto;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .show-header {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            padding: 2rem;
            color: white;
        }
        .show-header h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        .show-content { padding: 2rem; }
        .show-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .info-card {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
        }
        .info-card .icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .info-card .label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; }
        .info-card .value { font-size: 1rem; font-weight: 600; color: #0f172a; margin-top: 0.25rem; }
        .show-description h3 { font-size: 1.2rem; margin-bottom: 0.75rem; color: #0f172a; }
        .show-description p { line-height: 1.6; color: #334155; }
        .back-link {
            display: inline-block;
            margin-top: 2rem;
            color: #0f766e;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover { text-decoration: underline; }
        .type-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        .type-Atelier { background: #dcfce7; color: #166534; }
        .type-Conférence { background