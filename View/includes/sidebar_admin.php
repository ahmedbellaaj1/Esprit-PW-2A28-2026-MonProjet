<?php
/**
 * Sidebar partagée — back-office Green-Bite.
 * Variable attendue : $activePage (string) — ex: 'dashboard', 'products', 'orders', 'reviews', 'recettes', 'users'
 */
$activePage = $activePage ?? '';

function sidebarLink(string $page, string $href, string $icon, string $label, string $activePage): string
{
    $cls = $activePage === $page ? 'sidebar-link active' : 'sidebar-link';
    return "<a class=\"{$cls}\" href=\"{$href}\"><span class=\"icon\">{$icon}</span> {$label}</a>";
}
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico" alt="GreenBite Logo" class="sidebar-logo-img">
        <span>Green<span>Bite</span></span>
    </div>
    <div class="sidebar-role">Administration</div>
    <nav class="sidebar-nav">
        <?= sidebarLink('dashboard',  '/Green-Bite/View/back-office/dashboard.php',           '📊', "Vue d'ensemble", $activePage) ?>
        <?= sidebarLink('products',   '/Green-Bite/View/back-office/products.php',             '🛒', 'Produits',       $activePage) ?>
        <?= sidebarLink('orders',     '/Green-Bite/View/back-office/orders.php',               '📦', 'Commandes',      $activePage) ?>
        <?= sidebarLink('reviews',    '/Green-Bite/View/back-office/reviews_moderation.php',   '⭐', 'Avis clients',   $activePage) ?>
        <?= sidebarLink('recettes',   '/Green-Bite/View/back-office/recettes.php',             '🍽️', 'Recettes',      $activePage) ?>
        <?= sidebarLink('dons',           '/Green-Bite/View/back-office/dons.php',                 '🎁', 'Dons alimentaires', $activePage) ?>
        <?= sidebarLink('evenements',     '/Green-Bite/View/back-office/evenements.php',            '📅', 'Événements',         $activePage) ?>
        <?= sidebarLink('participants',   '/Green-Bite/View/back-office/participants.php',          '👥', 'Participants',        $activePage) ?>
        <?= sidebarLink('organisateurs',  '/Green-Bite/View/back-office/organisateurs_ev.php',      '🏢', 'Organisateurs',       $activePage) ?>
<<<<<<< HEAD
        <?= sidebarLink('preferences',  '/Green-Bite/View/back-office/preferences.php',          '🥗', 'Préférences & Allergies', $activePage) ?>
=======
>>>>>>> e2c825fb4e8f094eb8c5d8bde41073ee13565fcd
        <?= sidebarLink('users',          '/Green-Bite/View/back-office/users.php',                '👤', 'Utilisateurs',        $activePage) ?>
        <?= sidebarLink('front',      '/Green-Bite/View/front-office/index.php',               '🌐', 'Front Office',   $activePage) ?>
    </nav>
    <div class="sidebar-bottom">
        <a class="sidebar-link" href="#"><span class="icon">⚙️</span> Paramètres</a>
    </div>
</aside>
