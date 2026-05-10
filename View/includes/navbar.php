<?php
/**
 * Navbar unifiée — incluse par toutes les vues front-office.
 * Utilise les fonctions de Controller/bootstrap.php (isLoggedIn, getCurrentUser, etc.)
 */

$currentUser = getCurrentUser();
$isAdmin = $currentUser && ($currentUser['role'] ?? '') === 'admin';
$initials = '';
if ($currentUser) {
    $initials = strtoupper(
        mb_substr($currentUser['prenom'] ?? '', 0, 1) .
        mb_substr($currentUser['nom'] ?? '', 0, 1)
    );
    if ($initials === '') {
        $initials = '??';
    }
}
?>
<nav class="navbar">
    <a class="navbar-logo" href="/Green-Bite/View/front-office/index.php">
        <img src="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico" alt="GreenBite Logo" class="navbar-logo-img">
        <span class="navbar-logo-text">Green<span>Bite</span></span>
    </a>
    <ul class="navbar-links">
        <li><a href="/Green-Bite/View/front-office/index.php">Accueil</a></li>
        <li><a href="/Green-Bite/View/front-office/index.php">Produits</a></li>
        <li><a href="/Green-Bite/View/front-office/recettes.php">🍽️ Recettes</a></li>
        <?php if (isLoggedIn()): ?>
            <li><a href="/Green-Bite/View/front-office/cart.php">🛒 Panier</a></li>
            <li><a href="/Green-Bite/View/front-office/order-history.php">📋 Historique</a></li>
        <?php endif; ?>
    </ul>
    <div class="navbar-right">
        <?php if ($isAdmin): ?>
            <a class="primary-btn nav-quick-btn" href="/Green-Bite/View/back-office/dashboard.php">Dashboard Admin</a>
        <?php endif; ?>

        <?php if (isLoggedIn()): ?>
            <a href="/Green-Bite/View/front-office/cart.php" class="cart-icon" title="Voir le panier">
                🛒
                <span id="cartBadge" class="cart-badge" style="display:none;">0</span>
            </a>

            <!-- User dropdown -->
            <div class="nav-user-dropdown" style="position:relative;">
                <div class="nav-avatar" onclick="document.getElementById('userDropdown').classList.toggle('show')" style="cursor:pointer;" title="<?= h($currentUser['prenom'] . ' ' . $currentUser['nom']) ?>">
                    <?= h($initials) ?>
                </div>
                <div id="userDropdown" class="user-dropdown-menu" style="display:none;position:absolute;right:0;top:120%;background:white;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.15);min-width:200px;z-index:999;overflow:hidden;border:1px solid #e2e8f0;">
                    <div style="padding:1rem;border-bottom:1px solid #f1f5f9;background:#f8fafc;">
                        <div style="font-weight:700;color:#0f172a;font-size:.9rem;"><?= h($currentUser['prenom'] . ' ' . $currentUser['nom']) ?></div>
                        <div style="font-size:.78rem;color:#64748b;"><?= h($currentUser['email'] ?? '') ?></div>
                    </div>
                    <a href="/Green-Bite/View/front-office/profile.php" style="display:block;padding:.75rem 1rem;color:#374151;text-decoration:none;font-size:.88rem;transition:background .2s;" onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='transparent'">👤 Mon Profil</a>
                    <a href="/Green-Bite/View/front-office/order-history.php" style="display:block;padding:.75rem 1rem;color:#374151;text-decoration:none;font-size:.88rem;transition:background .2s;" onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='transparent'">📋 Mes Commandes</a>
                    <div style="border-top:1px solid #f1f5f9;">
                        <form method="POST" action="/Green-Bite/Controller/auth.php" style="margin:0;">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" style="width:100%;padding:.75rem 1rem;background:none;border:none;text-align:left;color:#dc2626;font-size:.88rem;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">🚪 Se déconnecter</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="/Green-Bite/View/auth.php" class="primary-btn nav-quick-btn">🔑 Se connecter</a>
        <?php endif; ?>
    </div>
</nav>

<script>
// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown && !e.target.closest('.nav-user-dropdown')) {
        dropdown.style.display = 'none';
        dropdown.classList.remove('show');
    }
});
// Toggle dropdown display
document.addEventListener('click', function(e) {
    if (e.target.closest('.nav-avatar')) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown) {
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        }
    }
});
</script>
