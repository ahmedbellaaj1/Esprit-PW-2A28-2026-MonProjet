<?php
/**
 * Green-Bite Front-Office - Recherche Avancée d'Événements
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../../Controller/EvenementController.php';
require_once __DIR__ . '/../../../Controller/OrganisateurController.php';

$eventController      = new EvenementController();
$organisateurController = new OrganisateurController();
$organisateurs        = $organisateurController->listOrganisateurs();
$allLieus             = $eventController->getAllLieus();

$filters = [];
$events  = null;

if (!empty($_GET)) {
    $filters = [
        'keyword'        => trim($_GET['keyword'] ?? ''),
        'type'           => trim($_GET['type'] ?? ''),
        'lieu'           => trim($_GET['lieu'] ?? ''),
        'organisateur_id'=> $_GET['organisateur_id'] ?? '',
        'date_debut'     => trim($_GET['date_debut'] ?? ''),
        'date_fin'       => trim($_GET['date_fin'] ?? ''),
        'statut'         => trim($_GET['statut'] ?? ''),
        'tri'            => trim($_GET['tri'] ?? 'date_asc'),
    ];
    $events = $eventController->searchAdvanced($filters);
    if (!is_array($events)) $events = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche Avancée - Événements GreenBite</title>
    <meta name="description" content="Recherche avancée parmi les événements GreenBite : filtres par type, lieu, date, organisateur.">
    <link rel="icon" type="image/x-icon" href="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="/Green-Bite/View/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .ev-ra-hero { background: linear-gradient(135deg, #0f766e, #14b8a6); padding: 2.5rem 2rem; text-align: center; color: white; }
        .ev-ra-hero h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .ev-ra-main { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; display: grid; grid-template-columns: 300px 1fr; gap: 2rem; }
        .ev-ra-form-card { background: white; border-radius: 20px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.07); height: fit-content; }
        .ev-ra-form-card h3 { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 1.25rem; border-left: 4px solid #14b8a6; padding-left: 0.75rem; }
        .ev-ra-field { margin-bottom: 1rem; }
        .ev-ra-field label { display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.35rem; }
        .ev-ra-field input, .ev-ra-field select {
            width: 100%; padding: 0.625rem 0.875rem; border: 2px solid #e2e8f0;
            border-radius: 10px; font-size: 0.9rem; font-family: 'Inter', sans-serif; transition: border-color 0.2s;
        }
        .ev-ra-field input:focus, .ev-ra-field select:focus { outline: none; border-color: #14b8a6; }
        .ev-ra-btn { width: 100%; background: #0f766e; color: white; padding: 0.75rem; border: none; border-radius: 9999px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.3s; font-family: 'Inter', sans-serif; }
        .ev-ra-btn:hover { background: #0c5f58; }
        .ev-ra-btn-reset { display: block; text-align: center; margin-top: 0.5rem; color: #64748b; font-size: 0.85rem; text-decoration: none; }
        .ev-ra-results h2 { font-size: 1.4rem; font-weight: 700; color: #0f172a; margin-bottom: 1.5rem; }
        .ev-ra-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem; }
        .ev-ra-card { background: white; border-radius: 18px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.07); transition: all 0.3s; cursor: pointer; }
        .ev-ra-card:hover { transform: translateY(-6px); box-shadow: 0 16px 25px rgba(15,118,110,0.15); }
        .ev-ra-card-img { height: 120px; background: linear-gradient(135deg, #d1fae5, #a7f3d0); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; }
        .ev-ra-card-body { padding: 1rem; }
        .ev-ra-card-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin-bottom: 0.4rem; }
        .ev-ra-card-meta { font-size: 0.8rem; color: #64748b; margin: 0.2rem 0; }
        .ev-ra-tag { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; margin: 0.4rem 0; }
        .ev-ra-tag-Atelier { background: #dcfce7; color: #166534; }
        .ev-ra-tag-Conférence { background: #dbeafe; color: #1e40af; }
        .ev-ra-tag-Festival { background: #fef3c7; color: #92400e; }
        .ev-ra-tag-Autre { background: #f3e8ff; color: #6b21a5; }
        .ev-ra-btn-detail { display: inline-block; margin-top: 0.5rem; padding: 0.4rem 0.9rem; background: #0f766e; color: white; text-decoration: none; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
        .ev-ra-empty { text-align: center; padding: 3rem; background: white; border-radius: 20px; }
        .ev-ra-placeholder { text-align: center; padding: 3rem; background: white; border-radius: 20px; }
        @media (max-width: 900px) { .ev-ra-main { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="ev-ra-hero">
    <h1>🔍 Recherche Avancée d'Événements</h1>
    <p>Utilisez les filtres pour trouver l'événement idéal</p>
</div>

<div class="ev-ra-main">
    <!-- Formulaire de filtres -->
    <div>
        <form method="GET" class="ev-ra-form-card">
            <h3>🔧 Filtres</h3>
            <div class="ev-ra-field">
                <label for="keyword">🔍 Mot-clé</label>
                <input type="text" id="keyword" name="keyword" placeholder="Titre, description, lieu..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
            </div>
            <div class="ev-ra-field">
                <label for="type">🏷️ Type</label>
                <select id="type" name="type">
                    <option value="">Tous les types</option>
                    <?php foreach(['Atelier','Conférence','Festival','Autre'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($filters['type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ev-ra-field">
                <label for="lieu">📍 Lieu</label>
                <select id="lieu" name="lieu">
                    <option value="">Tous les lieux</option>
                    <?php foreach($allLieus as $l): ?>
                        <option value="<?= htmlspecialchars($l['lieu']) ?>" <?= ($filters['lieu'] ?? '') === $l['lieu'] ? 'selected' : '' ?>><?= htmlspecialchars($l['lieu']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ev-ra-field">
                <label for="organisateur_id">👥 Organisateur</label>
                <select id="organisateur_id" name="organisateur_id">
                    <option value="">Tous les organisateurs</option>
                    <?php foreach($organisateurs as $o): ?>
                        <option value="<?= $o['id'] ?>" <?= ($filters['organisateur_id'] ?? '') == $o['id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ev-ra-field">
                <label for="date_debut">📅 Date début</label>
                <input type="date" id="date_debut" name="date_debut" value="<?= $filters['date_debut'] ?? '' ?>">
            </div>
            <div class="ev-ra-field">
                <label for="date_fin">📅 Date fin</label>
                <input type="date" id="date_fin" name="date_fin" value="<?= $filters['date_fin'] ?? '' ?>">
            </div>
            <div class="ev-ra-field">
                <label for="statut">📊 Statut</label>
                <select id="statut" name="statut">
                    <option value="">Tous</option>
                    <option value="upcoming" <?= ($filters['statut'] ?? '') === 'upcoming' ? 'selected' : '' ?>>📅 À venir</option>
                    <option value="past"     <?= ($filters['statut'] ?? '') === 'past'     ? 'selected' : '' ?>>✅ Passés</option>
                    <option value="today"    <?= ($filters['statut'] ?? '') === 'today'    ? 'selected' : '' ?>>🔴 Aujourd'hui</option>
                </select>
            </div>
            <div class="ev-ra-field">
                <label for="tri">🔀 Trier par</label>
                <select id="tri" name="tri">
                    <option value="date_asc"         <?= ($filters['tri'] ?? '') === 'date_asc'         ? 'selected' : '' ?>>Date (croissant)</option>
                    <option value="date_desc"        <?= ($filters['tri'] ?? '') === 'date_desc'        ? 'selected' : '' ?>>Date (décroissant)</option>
                    <option value="titre_asc"        <?= ($filters['tri'] ?? '') === 'titre_asc'        ? 'selected' : '' ?>>Titre A→Z</option>
                    <option value="organisateur_asc" <?= ($filters['tri'] ?? '') === 'organisateur_asc' ? 'selected' : '' ?>>Organisateur</option>
                </select>
            </div>
            <button type="submit" class="ev-ra-btn">🔍 Rechercher</button>
            <a href="/Green-Bite/View/front-office/evenements/recherche-avancee.php" class="ev-ra-btn-reset">↩ Réinitialiser les filtres</a>
        </form>
    </div>

    <!-- Résultats -->
    <div class="ev-ra-results">
        <?php if ($events === null): ?>
            <div class="ev-ra-placeholder">
                <div style="font-size:4rem;margin-bottom:1rem;">🔍</div>
                <h3>Utilisez les filtres pour rechercher des événements</h3>
                <p style="color:#64748b;margin-top:0.5rem;">Remplissez les critères à gauche et cliquez sur "Rechercher".</p>
                <a href="/Green-Bite/View/front-office/evenements/listEvenements.php" style="color:#0f766e;display:inline-block;margin-top:1rem;">📅 Voir tous les événements</a>
            </div>
        <?php elseif (empty($events)): ?>
            <div class="ev-ra-empty">
                <div style="font-size:4rem;margin-bottom:1rem;">📭</div>
                <h3>Aucun événement trouvé</h3>
                <p style="color:#64748b;">Essayez d'autres critères de recherche.</p>
            </div>
        <?php else: ?>
            <h2>📅 Résultats (<?= count($events) ?> événement(s) trouvé(s))</h2>
            <div class="ev-ra-grid">
                <?php foreach($events as $event):
                    $typeIcon = match($event['type']) { 'Atelier'=>'🧑‍🍳','Conférence'=>'🎤','Festival'=>'🎉', default=>'📌' };
                    $formattedDate = !empty($event['date_event']) ? date('d/m/Y', strtotime($event['date_event'])) : '';
                ?>
                    <div class="ev-ra-card" onclick="window.location.href='showEvenement.php?id=<?= (int)$event['id'] ?>'">
                        <div class="ev-ra-card-img"><?= $typeIcon ?></div>
                        <div class="ev-ra-card-body">
                            <div class="ev-ra-card-title"><?= htmlspecialchars($event['titre']) ?></div>
                            <div class="ev-ra-card-meta">📍 <?= htmlspecialchars($event['lieu']) ?></div>
                            <div class="ev-ra-card-meta">📆 <?= $formattedDate ?></div>
                            <?php if (!empty($event['organisateur_nom'])): ?>
                                <div class="ev-ra-card-meta">👤 <?= htmlspecialchars($event['organisateur_nom']) ?></div>
                            <?php endif; ?>
                            <div><span class="ev-ra-tag ev-ra-tag-<?= $event['type'] ?>"><?= $event['type'] ?></span></div>
                            <a href="showEvenement.php?id=<?= (int)$event['id'] ?>" class="ev-ra-btn-detail">Voir détail →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../../includes/chatbot_widget.php'; ?>
</body>
</html>
