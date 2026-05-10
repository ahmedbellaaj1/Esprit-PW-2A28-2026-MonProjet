<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../../Controller/ReviewController.php';

$controller  = new ReviewController();
$statut      = $_GET['statut'] ?? '';
$page        = max(1, (int)($_GET['page'] ?? 1));
$limit       = 15;

// POST actions (form fallback without JS)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action']  ?? '';
    $id_avis = (int)($_POST['id_avis'] ?? 0);
    if ($id_avis > 0) {
        match($action) {
            'approve' => $controller->approveReview($id_avis),
            'reject'  => $controller->rejectReview($id_avis),
            'delete'  => $controller->deleteReview($id_avis),
            default   => null,
        };
    }
    $qs = http_build_query(array_filter(['statut' => $statut, 'page' => $page]));
    header('Location: reviews_moderation.php' . ($qs ? '?' . $qs : ''));
    exit;
}

$result       = $controller->listAllReviews();
$pendingCount = $controller->getPendingCount()['count'];
$reviews      = $result['avis'] ?? [];

// Stats rapides
$allResult      = (new ReviewController())->listAllReviews();
$totalAll       = count($allResult['avis']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modération des Avis – Admin GreenBite</title>
    <link rel="icon" type="image/x-icon" href="../assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="../style.css">
    <style>
        /* ─── Filters bar ─── */
        .filter-bar {
            display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: 1.5rem;
        }
        .filter-pill {
            padding: .45rem 1rem; border-radius: 999px;
            border: 1.5px solid #e2e8f0; background: white;
            color: #64748b; font-size: .85rem; font-weight: 600;
            text-decoration: none; transition: all .2s; cursor: pointer;
        }
        .filter-pill:hover { border-color: #16a34a; color: #16a34a; }
        .filter-pill.active { background: #16a34a; color: white; border-color: #16a34a; }

        /* ─── Review cards ─── */
        .reviews-grid { display: grid; gap: 1rem; }

        .review-card {
            background: white; border-radius: 14px;
            box-shadow: 0 1px 8px rgba(0,0,0,.06);
            border: 1.5px solid #f1f5f9;
            overflow: hidden; transition: box-shadow .2s;
        }
        .review-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); }

        .rc-top {
            display: flex; justify-content: space-between;
            align-items: flex-start; gap: 1rem;
            padding: 1.1rem 1.25rem .75rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .rc-meta { flex: 1; min-width: 0; }
        .rc-product {
            font-size: .78rem; color: #16a34a; font-weight: 700;
            text-transform: uppercase; letter-spacing: .5px; margin-bottom: .2rem;
        }
        .rc-title { font-weight: 700; color: #0f172a; font-size: .97rem; margin-bottom: .2rem; }
        .rc-stars { color: #f59e0b; font-size: 1rem; letter-spacing: 1px; }
        .rc-user  { font-size: .78rem; color: #94a3b8; margin-top: .3rem; }

        .rc-status {
            padding: .35rem .9rem; border-radius: 999px;
            font-size: .75rem; font-weight: 700; white-space: nowrap;
            flex-shrink: 0;
        }
        .s-pending  { background: #fef3c7; color: #92400e; }
        .s-approuve { background: #dcfce7; color: #166534; }
        .s-rejet    { background: #fee2e2; color: #991b1b; }

        .rc-body {
            padding: .75rem 1.25rem;
            font-size: .88rem; color: #475569; line-height: 1.6;
        }

        .rc-footer {
            display: flex; justify-content: space-between; align-items: center;
            padding: .7rem 1.25rem;
            background: #f8fafc; border-top: 1px solid #f1f5f9;
        }
        .rc-date { font-size: .78rem; color: #94a3b8; }
        .rc-actions { display: flex; gap: .5rem; }

        .btn-act {
            padding: .4rem .9rem; border-radius: 8px;
            border: none; cursor: pointer;
            font-size: .8rem; font-weight: 700; transition: all .2s;
        }
        .btn-approve { background: #dcfce7; color: #166534; }
        .btn-approve:hover { background: #16a34a; color: white; }
        .btn-reject  { background: #fef3c7; color: #92400e; }
        .btn-reject:hover  { background: #f59e0b; color: white; }
        .btn-delete  { background: #fee2e2; color: #991b1b; }
        .btn-delete:hover  { background: #ef4444; color: white; }
        .btn-act:disabled { opacity: .5; cursor: not-allowed; }

        /* ─── Stats row ─── */
        .stats-row {
            display: grid; grid-template-columns: repeat(3,1fr);
            gap: 1rem; margin-bottom: 1.5rem;
        }
        .stat-box {
            background: white; border-radius: 12px;
            padding: 1.1rem 1.25rem;
            box-shadow: 0 1px 6px rgba(0,0,0,.05);
            border: 1px solid #f1f5f9;
        }
        .stat-box .sv { font-size: 1.8rem; font-weight: 800; color: #0f172a; }
        .stat-box .sl { font-size: .8rem; color: #64748b; font-weight: 600; margin-top: .2rem; }

        /* ─── Toast ─── */
        .toast {
            position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 999;
            padding: .85rem 1.4rem; border-radius: 12px;
            font-weight: 600; font-size: .9rem;
            box-shadow: 0 8px 24px rgba(0,0,0,.15);
            transform: translateY(100px); opacity: 0;
            transition: all .35s ease; pointer-events: none;
        }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast-ok  { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }
        .toast-err { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        .empty-state { text-align: center; padding: 4rem 2rem; color: #94a3b8; }
        .empty-state .ei { font-size: 3rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="../assets/659943731_2229435644263567_1175829106494475277_n.ico" alt="GreenBite Logo" class="sidebar-logo-img">
            <span>Green<span>Bite</span></span>
        </div>
        <div class="sidebar-role">Administration</div>
        <nav class="sidebar-nav">
            <a class="sidebar-link" href="dashboard.php"><span class="icon">📊</span> Vue d'ensemble</a>
            <a class="sidebar-link" href="products.php"><span class="icon">🛒</span> Produits</a>
            <a class="sidebar-link" href="orders.php"><span class="icon">📦</span> Commandes</a>
            <a class="sidebar-link active" href="reviews_moderation.php">
                <span class="icon">⭐</span> Avis clients
                <?php if ($pendingCount > 0): ?>
                    <span style="margin-left:auto;background:#ef4444;color:white;font-size:.7rem;padding:.15rem .5rem;border-radius:999px;"><?= $pendingCount ?></span>
                <?php endif; ?>
            </a>
            <a class="sidebar-link" href="../front-office/index.php"><span class="icon">🌐</span> Front Office</a>
        </nav>
        <div class="sidebar-bottom">
            <a class="sidebar-link" href="#"><span class="icon">⚙️</span> Paramètres</a>
        </div>
    </aside>

    <div class="dashboard-main">
        <header class="dashboard-header">
            <div class="header-title">Modération des Avis Clients</div>
            <div class="header-right">
                <span class="header-badge">🟢 En ligne</span>
                <div class="admin-avatar">AB</div>
            </div>
        </header>

        <div class="page-content">
            <div class="page-header">
                <h1>⭐ Avis & Notations</h1>
                <p>Gérez les avis clients : approuvez, rejetez ou supprimez les commentaires.</p>
            </div>

            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-box">
                    <div class="sv"><?= $pendingCount ?></div>
                    <div class="sl">⏳ En attente de modération</div>
                </div>
                <div class="stat-box">
                    <div class="sv"><?= $totalAll ?></div>
                    <div class="sl">📋 Total des avis chargés</div>
                </div>
                <div class="stat-box">
                    <div class="sv"><?= count($reviews) ?></div>
                    <div class="sl">👁 Avis affichés (filtre actuel)</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-bar">
                <a href="reviews_moderation.php" class="filter-pill <?= $statut === '' ? 'active' : '' ?>">Tous</a>
                <a href="?statut=en-attente" class="filter-pill <?= $statut === 'en-attente' ? 'active' : '' ?>">
                    ⏳ En attente <?php if ($pendingCount > 0): ?><span>(<?= $pendingCount ?>)</span><?php endif; ?>
                </a>
                <a href="?statut=approuve" class="filter-pill <?= $statut === 'approuve' ? 'active' : '' ?>">✅ Approuvés</a>
                <a href="?statut=rejet"    class="filter-pill <?= $statut === 'rejet'    ? 'active' : '' ?>">❌ Rejetés</a>
            </div>

            <!-- Reviews list -->
            <?php if (empty($reviews)): ?>
                <div class="empty-state">
                    <div class="ei">💬</div>
                    <p style="font-size:1.1rem;font-weight:600;color:#475569;">Aucun avis à afficher</p>
                    <p style="margin-top:.5rem;">Aucun avis ne correspond aux filtres sélectionnés.</p>
                </div>
            <?php else: ?>
                <div class="reviews-grid">
                    <?php foreach ($reviews as $avis): ?>
                        <?php
                        $note  = (int)$avis['note'];
                        $stars = str_repeat('★', $note) . str_repeat('☆', 5 - $note);
                        $sClass = match($avis['statut']) {
                            'approuve' => 's-approuve',
                            'rejet'    => 's-rejet',
                            default    => 's-pending',
                        };
                        $sLabel = match($avis['statut']) {
                            'approuve' => '✅ Approuvé',
                            'rejet'    => '❌ Rejeté',
                            default    => '⏳ En attente',
                        };
                        ?>
                        <div class="review-card" id="card-<?= (int)$avis['id_avis'] ?>">
                            <div class="rc-top">
                                <div class="rc-meta">
                                    <div class="rc-product">📦 <?= h((string)($avis['produit_nom'] ?? 'Produit supprimé')) ?></div>
                                    <div class="rc-title"><?= h($avis['titre']) ?></div>
                                    <div class="rc-stars"><?= $stars ?> <span style="color:#94a3b8;font-size:.8rem;">(<?= $note ?>/5)</span></div>
                                    <div class="rc-user">👤 Utilisateur #<?= (int)$avis['id_utilisateur'] ?></div>
                                </div>
                                <span class="rc-status <?= $sClass ?>" id="status-<?= (int)$avis['id_avis'] ?>"><?= $sLabel ?></span>
                            </div>
                            <div class="rc-body"><?= h($avis['texte']) ?></div>
                            <div class="rc-footer">
                                <span class="rc-date">🕐 <?= (new DateTime($avis['date_avis']))->format('d/m/Y à H:i') ?></span>
                                <div class="rc-actions">
                                    <?php if ($avis['statut'] !== 'approuve'): ?>
                                        <button class="btn-act btn-approve"
                                            onclick="act(<?= (int)$avis['id_avis'] ?>, 'approve', this)">✅ Approuver</button>
                                    <?php endif; ?>
                                    <?php if ($avis['statut'] !== 'rejet'): ?>
                                        <button class="btn-act btn-reject"
                                            onclick="act(<?= (int)$avis['id_avis'] ?>, 'reject', this)">❌ Rejeter</button>
                                    <?php endif; ?>
                                    <button class="btn-act btn-delete"
                                        onclick="if(confirm('Supprimer définitivement cet avis ?')) act(<?= (int)$avis['id_avis'] ?>, 'delete', this)">🗑 Supprimer</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
async function act(id, action, btn) {
    // Disable all buttons in this card
    const card = document.getElementById('card-' + id);
    card.querySelectorAll('.btn-act').forEach(b => b.disabled = true);

    try {
        const res  = await fetch('../../api/reviews/manage.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_avis: id, action })
        });
        const data = await res.json();

        if (data.ok) {
            if (action === 'delete') {
                card.style.transition = 'opacity .4s, transform .4s';
                card.style.opacity  = '0';
                card.style.transform = 'translateX(40px)';
                setTimeout(() => card.remove(), 400);
                showToast('Avis supprimé.', 'ok');
            } else {
                const statusEl = document.getElementById('status-' + id);
                if (action === 'approve') {
                    statusEl.textContent  = '✅ Approuvé';
                    statusEl.className    = 'rc-status s-approuve';
                } else {
                    statusEl.textContent  = '❌ Rejeté';
                    statusEl.className    = 'rc-status s-rejet';
                }
                // Re-enable remaining buttons
                card.querySelectorAll('.btn-act').forEach(b => b.disabled = false);
                // Hide the button that no longer applies
                btn.remove();
                showToast(action === 'approve' ? 'Avis approuvé ✅' : 'Avis rejeté ❌', 'ok');
            }
        } else {
            card.querySelectorAll('.btn-act').forEach(b => b.disabled = false);
            showToast('Erreur : ' + (data.message || 'Inconnue'), 'err');
        }
    } catch(e) {
        card.querySelectorAll('.btn-act').forEach(b => b.disabled = false);
        showToast('Erreur réseau : ' + e.message, 'err');
    }
}

function showToast(msg, type) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className   = 'toast toast-' + (type === 'ok' ? 'ok' : 'err');
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
}
</script>
</body>
</html>
