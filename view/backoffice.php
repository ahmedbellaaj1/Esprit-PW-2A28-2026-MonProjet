<?php
session_start();
include '../config/db.php';

// Récupérer utilisateurs
$stmt = $pdo->query("SELECT id, nom, email, age, poids, calories FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer produits
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
$stmtCheck->execute(['produits']);
$productsTable = ((int)$stmtCheck->fetchColumn() > 0) ? 'produits' : 'Produits';

$stmtP = $pdo->query("SELECT id, nom, categorie, prix, calories FROM $productsTable ORDER BY id DESC");
$allProducts = $stmtP->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Back Office - Greenbite</title>

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* TABLE USERS */
.users-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.users-table th, .users-table td {
    padding: 10px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}

.users-table th {
    background: #0f766e;
    color: white;
}

.badge {
    padding: 4px 8px;
    border-radius: 5px;
    font-size: 12px;
}

.badge-green { background: #16a34a; color: white; }
.badge-blue { background: #2563eb; color: white; }
.badge-orange { background: #f59e0b; color: white; }

/* FORMULAIRE PRODUIT */
.product-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 15px;
}

.product-form input, .product-form select, .product-form textarea {
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    width: 100%;
}

.product-form textarea {
    resize: vertical;
    min-height: 80px;
}

.btn-submit {
    background: #0f766e;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 10px;
}

.btn-submit:hover {
    background: #0d9488;
}

#msg-produit {
    margin-top: 10px;
    font-weight: bold;
    font-size: 14px;
}

/* STATS CARDS */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}

.stat-info .stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    display: block;
}

.stat-info .stat-label {
    font-size: 14px;
    color: #64748b;
}

.bg-users { background: #3b82f6; }
.bg-products { background: #10b981; }
.bg-cats { background: #f59e0b; }
.bg-calories { background: #ef4444; }
</style>

</head>

<body>

<div class="dashboard-layout">

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">Green<span>Bite</span></div>
    <div class="sidebar-role">Administration</div>

    <nav class="sidebar-nav">
        <a class="sidebar-link" href="../greenbite/index.php">📊 Dashboard</a>
        <a class="sidebar-link active" href="backoffice.php">⚙️ Admin</a>
    </nav>

    <div class="sidebar-bottom">
        <a class="sidebar-link" href="../index.php">🚪 Retour</a>
    </div>
</aside>

<!-- MAIN -->
<div class="dashboard-main">

<header class="dashboard-header">
    <div class="header-title">Back Office Greenbite</div>
</header>

<main class="page-content">

<h1>Back Office</h1>

<!-- 📊 STATISTIQUES -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-users"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="stat-users">-</span>
            <span class="stat-label">Utilisateurs</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-products"><i class="fas fa-utensils"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="stat-products">-</span>
            <span class="stat-label">Produits</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-cats"><i class="fas fa-list"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="stat-categories">-</span>
            <span class="stat-label">Catégories</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-calories"><i class="fas fa-fire"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="stat-calories">-</span>
            <span class="stat-label">Calories Moy.</span>
        </div>
    </div>
</div>

<!-- 📊 USERS TABLE -->
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px;">
        <h3 style="margin: 0;"><i class="fa-solid fa-users"></i> Utilisateurs</h3>
        <div style="display:flex; gap:10px;">
            <input type="text" id="search-users" placeholder="Rechercher (Nom, Email)..." style="padding: 8px; border: 1px solid #e2e8f0; border-radius: 8px; width: 250px;" onkeyup="searchUsers()">
            <a href="../api/export_users.php" class="btn-submit" style="margin-top:0; display:inline-flex; align-items:center; gap:8px; text-decoration:none; padding:8px 15px;">
                <i class="fas fa-file-pdf"></i> Exporter
            </a>
        </div>
    </div>

    <table class="users-table" id="usersTable">
        <thead>
            <tr>
                <th onclick="sortTable(0)" style="cursor:pointer;">ID <i class="fa-solid fa-sort"></i></th>
                <th onclick="sortTable(1)" style="cursor:pointer;">Nom <i class="fa-solid fa-sort"></i></th>
                <th onclick="sortTable(2)" style="cursor:pointer;">Email <i class="fa-solid fa-sort"></i></th>
                <th onclick="sortTable(3)" style="cursor:pointer;">Âge <i class="fa-solid fa-sort"></i></th>
                <th onclick="sortTable(4)" style="cursor:pointer;">Poids <i class="fa-solid fa-sort"></i></th>
                <th onclick="sortTable(5)" style="cursor:pointer;">Calories <i class="fa-solid fa-sort"></i></th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['nom']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge badge-blue"><?= $u['age'] ?></span></td>
                <td><span class="badge badge-green"><?= $u['poids'] ?> kg</span></td>
                <td><span class="badge badge-orange"><?= $u['calories'] ?> kcal</span></td>
                <td>
                    <button onclick="deleteUser(<?= $u['id'] ?>)" style="background:none; border:none; color:#ef4444; cursor:pointer;" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- 📦 AJOUTER UN PRODUIT -->
<div class="card">
    <h3><i class="fa-solid fa-plus-circle"></i> Ajouter un Produit</h3>
    
    <form id="add-product-form" class="product-form" onsubmit="submitProduct(event)">
        <input type="text" id="prod-nom" name="nom" placeholder="Nom du produit (ex: Salade César)" required>
        
        <select id="prod-cat" name="categorie" required>
            <option value="">-- Choisir une catégorie --</option>
            <option value="Salades">Salades</option>
            <option value="Fast Food">Fast Food</option>
            <option value="Produits Laitiers">Produits Laitiers</option>
            <option value="Boulangerie">Boulangerie</option>
            <option value="Boissons">Boissons</option>
            <option value="Plats préparés">Plats préparés</option>
        </select>
        
        <div style="display: flex; gap: 10px;">
            <input type="number" id="prod-cal" name="calories" placeholder="Calories (kcal)" min="0" style="flex: 1;">
            <input type="number" id="prod-prix" name="prix" placeholder="Prix (DT)" step="0.01" min="0" style="flex: 1;">
        </div>
        
        <textarea id="prod-desc" name="description" placeholder="Description du produit..."></textarea>
        
        <button type="submit" class="btn-submit">Enregistrer le produit</button>
    </form>
    
    <div id="msg-produit"></div>
</div>

<!-- 📦 LISTE DES PRODUITS -->
<div class="card">
    <h3><i class="fa-solid fa-box"></i> Liste des Produits</h3>
    <table class="users-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Prix</th>
                <th>Calories</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allProducts as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['nom']) ?></td>
                <td><span class="badge badge-blue"><?= $p['categorie'] ?></span></td>
                <td><?= number_format($p['prix'], 2) ?> DT</td>
                <td><?= $p['calories'] ?> kcal</td>
                <td>
                    <button onclick="deleteProduct(<?= $p['id'] ?>)" style="background:none; border:none; color:#ef4444; cursor:pointer;">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ⚙️ OPTIONS -->
<div class="card">
    <h3><i class="fa-solid fa-list"></i> Préférences</h3>

    <input type="text" id="new-pref" placeholder="Nouvelle préférence">
    <button onclick="addOption('preference')">Ajouter</button>

    <ul id="prefs-list"></ul>
</div>

<div class="card">
    <h3><i class="fa-solid fa-exclamation-triangle"></i> Allergies</h3>

    <input type="text" id="new-allergy" placeholder="Nouvelle allergie">
    <button onclick="addOption('allergy')">Ajouter</button>

    <ul id="allergies-list"></ul>
</div>

</main>
</div>
</div>

<!-- JS -->
<script>

// LOAD STATS
function loadStats() {
    fetch("../api/get_stats.php")
    .then(res => res.json())
    .then(data => {
        document.getElementById('stat-users').textContent = data.total_users;
        document.getElementById('stat-products').textContent = data.total_products;
        document.getElementById('stat-categories').textContent = data.total_categories;
        document.getElementById('stat-calories').textContent = data.avg_calories + " kcal";
    })
    .catch(err => console.error("Erreur stats:", err));
}

// LOAD OPTIONS
function loadOptions() {
    loadStats(); // Charger aussi les stats ici
    fetch("../rappel/get_options.php")
    .then(res => res.json())
    .then(data => {
        renderList('prefs-list', data.preferences, 'preference');
        renderList('allergies-list', data.allergies, 'allergy');
    });
}

// RENDER LIST
function renderList(id, data, type) {
    const el = document.getElementById(id);

    el.innerHTML = data.map(o => `
        <li>
            ${o.name}
            <button onclick="editOption(${o.id}, '${o.name}', '${type}')">✏️</button>
            <button onclick="deleteOption(${o.id}, '${type}')">❌</button>
        </li>
    `).join('');
}

// ADD
function addOption(type) {
    const input = type === 'preference' ? 'new-pref' : 'new-allergy';
    const name = document.getElementById(input).value;

    fetch("../rappel/add_option.php", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({type, name})
    })
    .then(res => res.json())
    .then(() => loadOptions());
}

// DELETE
function deleteOption(id, type) {
    fetch("../rappel/delete_option.php", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({id, type})
    })
    .then(() => loadOptions());
}

// EDIT
function editOption(id, name, type) {
    const newName = prompt("Modifier:", name);

    fetch("../rappel/update_option.php", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({id, type, name: newName})
    })
    .then(() => loadOptions());
}

// 📦 SUBMIT PRODUIT
function submitProduct(e) {
    e.preventDefault();
    const form = document.getElementById('add-product-form');
    const msgDiv = document.getElementById('msg-produit');
    
    const formData = new FormData(form);
    
    msgDiv.innerHTML = '<span style="color:#64748b;">Ajout en cours...</span>';
    
    fetch("../api/add_product.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            msgDiv.innerHTML = `<span style="color:red;">Erreur: ${data.message}</span>`;
        } else {
            msgDiv.innerHTML = `<span style="color:green;">${data.message}</span>`;
            form.reset(); // Vider le formulaire
            // Effacer le message après 3s
            setTimeout(() => { msgDiv.innerHTML = ''; }, 3000);
        }
    })
    .catch(err => {
        console.error(err);
        msgDiv.innerHTML = '<span style="color:red;">Erreur de connexion au serveur.</span>';
    });
}

// 🔍 RECHERCHE UTILISATEURS
function searchUsers() {
    let input = document.getElementById("search-users");
    let filter = input.value.toLowerCase();
    let table = document.getElementById("usersTable");
    let tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        let tdNom = tr[i].getElementsByTagName("td")[1];
        let tdEmail = tr[i].getElementsByTagName("td")[2];
        if (tdNom || tdEmail) {
            let txtValueNom = tdNom.textContent || tdNom.innerText;
            let txtValueEmail = tdEmail.textContent || tdEmail.innerText;
            if (txtValueNom.toLowerCase().indexOf(filter) > -1 || txtValueEmail.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

// ↕️ TRI UTILISATEURS
function sortTable(n) {
    let table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    table = document.getElementById("usersTable");
    switching = true;
    dir = "asc";

    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i + 1].getElementsByTagName("TD")[n];
            
            let valX = x.innerText.toLowerCase();
            let valY = y.innerText.toLowerCase();
            
            // Pour les colonnes ID, Age, Poids, Calories (nombres)
            if (n === 0 || n === 3 || n === 4 || n === 5) {
                valX = parseFloat(valX) || 0;
                valY = parseFloat(valY) || 0;
            }

            if (dir == "asc") {
                if (valX > valY) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir == "desc") {
                if (valX < valY) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++;
        } else {
            if (switchcount == 0 && dir == "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
}

// 🗑️ SUPPRIMER UTILISATEUR
function deleteUser(id) {
    if (confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?")) {
        fetch("../api/delete_user.php", {
            method: "POST",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({id})
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Recharger pour voir le changement
            } else {
                alert("Erreur: " + data.message);
            }
        });
    }
}

// 🗑️ SUPPRIMER PRODUIT
function deleteProduct(id) {
    if (confirm("Supprimer ce produit ?")) {
        fetch("../api/delete_product.php", {
            method: "POST",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({id})
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message);
        });
    }
}

window.onload = loadOptions;

</script>

</body>
</html>