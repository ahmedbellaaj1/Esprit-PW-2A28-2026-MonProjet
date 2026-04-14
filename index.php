<?php
// Tu peux ajouter ici des vérifications (session, login, etc.)
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Greenbite Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../view/style.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
<div class="dashboard-layout">

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-logo">🌿 Greenbite</div>
    <p class="sidebar-role">Amen Allah Bani</p>
    <nav class="sidebar-nav">
        <a class="sidebar-link active" href="../greenbite/index.php">Dashboard</a>
        <a class="sidebar-link" href="../view/backoffice.php">Back Office</a>
    </nav>
</div>

<!-- CONTENU -->
<div class="dashboard-main">
    <header class="dashboard-header">
        <div class="header-title">Tableau de bord</div>
        <div class="header-right">
            <div class="header-badge">Préférences</div>
            <div class="admin-avatar">A</div>
        </div>
    </header>
    <main class="page-content">
        <div class="page-header">
            <h1>Tableau de bord</h1>
            <p>Affichez et mettez à jour les préférences et allergies du profil.</p>
        </div>

        <!-- CARD -->
        <div class="card">

        <h3><i class="fa-solid fa-leaf"></i> Préférences & Allergies</h3>

        <!-- Préférences -->
        <div>
            <p class="label">Préférences Alimentaires</p>
            <div id="preferences-container" class="tags"></div>
        </div>

        <!-- Allergies -->
        <div>
            <p class="label">Allergies</p>
            <div id="allergies-container" class="tags"></div>
        </div>

        <br>

        <button class="primary-btn" onclick="saveProfile()">
            Enregistrer
        </button>

    </div>

    </main>
</div>
</div>

<!-- SCRIPT JS -->
<script>
const userId = 1;

// 🔹 Charger données depuis backend
function loadProfile() {
    fetch("../rappel/get_profile.php?id_user=" + userId)
    .then(res => res.json())
    .then(data => {

        let prefHTML = "";
        data.preferences.forEach(p => {
            prefHTML += `<span class="tag tag-green">${p}</span>`;
        });
        document.getElementById("preferences-container").innerHTML = prefHTML;

        let allHTML = "";
        data.allergies.forEach(a => {
            allHTML += `<span class="tag tag-red">${a}</span>`;
        });
        document.getElementById("allergies-container").innerHTML = allHTML;

    })
    .catch(err => console.error(err));
}

// 🔹 Sauvegarder dans DB
function saveProfile() {

    const preferences = ["Vegan", "Sans gluten", "Sans lactose"];
    const allergies = ["Lactose", "Arachides"];

    fetch("../rappel/save_profile.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            id_user: userId,
            preferences: preferences,
            allergies: allergies
        })
    })
    .then(res => res.text())
    .then(data => {
        alert("✅ Données enregistrées !");
        loadProfile();
    })
    .catch(err => console.error(err));
}

// Charger au démarrage
window.onload = loadProfile;
</script>

</body>
</html>