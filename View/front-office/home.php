<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
$user = getCurrentUser();
$isGuest = !isLoggedIn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenBite — Bien manger, bien vivre</title>
    <meta name="description" content="GreenBite – Produits sains, recettes équilibrées, dons alimentaires et événements communautaires.">
    <link rel="icon" type="image/x-icon" href="/Green-Bite/View/assets/659943731_2229435644263567_1175829106494475277_n.ico">
    <link rel="stylesheet" href="/Green-Bite/View/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;background:#f8fafc;color:#0f172a;overflow-x:hidden}

        /* ── HERO ── */
        .gb-hero{
            min-height:92vh;
            background:#0f172a;
            display:flex;align-items:center;justify-content:center;
            position:relative;overflow:hidden;padding:6rem 2rem 4rem;
        }
        /* Decorative grid */
        .gb-hero::before{
            content:'';position:absolute;inset:0;
            background-image:linear-gradient(rgba(16,185,129,.06) 1px,transparent 1px),
                             linear-gradient(90deg,rgba(16,185,129,.06) 1px,transparent 1px);
            background-size:48px 48px;
        }
        /* Glow blobs */
        .blob{position:absolute;border-radius:50%;filter:blur(90px);pointer-events:none}
        .blob-1{width:520px;height:520px;background:rgba(16,185,129,.18);top:-120px;right:-100px;animation:blobFloat 9s ease-in-out infinite}
        .blob-2{width:400px;height:400px;background:rgba(245,158,11,.1);bottom:-80px;left:-80px;animation:blobFloat 12s ease-in-out infinite reverse}
        .blob-3{width:300px;height:300px;background:rgba(99,102,241,.1);top:40%;left:50%;animation:blobFloat 7s ease-in-out infinite 2s}
        @keyframes blobFloat{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(20px,-20px) scale(1.06)}}

        .gb-hero-content{position:relative;text-align:center;max-width:820px;z-index:2}
        .gb-badge{
            display:inline-flex;align-items:center;gap:.5rem;
            background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);
            color:#34d399;padding:.45rem 1.2rem;border-radius:9999px;
            font-size:.82rem;font-weight:600;letter-spacing:.5px;
            margin-bottom:2rem;animation:fadeDown .6s ease both;
        }
        .gb-hero h1{
            font-size:clamp(2.6rem,6vw,4.8rem);font-weight:900;
            color:#f8fafc;line-height:1.08;margin-bottom:1.5rem;
            animation:fadeUp .7s ease .1s both;
        }
        .gb-hero h1 .accent{
            background:linear-gradient(135deg,#10b981,#34d399);
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
        }
        .gb-hero h1 .accent2{
            background:linear-gradient(135deg,#f59e0b,#fbbf24);
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
        }
        .gb-hero p{
            font-size:1.15rem;color:rgba(248,250,252,.65);
            max-width:580px;margin:0 auto 2.8rem;line-height:1.75;
            animation:fadeUp .7s ease .2s both;
        }
        .gb-cta{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;animation:fadeUp .7s ease .3s both}
        .btn-primary{
            display:inline-flex;align-items:center;gap:.6rem;
            background:linear-gradient(135deg,#10b981,#059669);color:#fff;
            padding:.95rem 2rem;border-radius:9999px;font-weight:700;font-size:1rem;
            text-decoration:none;transition:all .3s;
            box-shadow:0 8px 28px rgba(16,185,129,.35);
        }
        .btn-primary:hover{transform:translateY(-3px);box-shadow:0 14px 38px rgba(16,185,129,.5)}
        .btn-ghost{
            display:inline-flex;align-items:center;gap:.6rem;
            background:rgba(248,250,252,.06);backdrop-filter:blur(8px);
            border:1.5px solid rgba(248,250,252,.18);color:#f8fafc;
            padding:.95rem 2rem;border-radius:9999px;font-weight:600;font-size:1rem;
            text-decoration:none;transition:all .3s;
        }
        .btn-ghost:hover{background:rgba(248,250,252,.12);transform:translateY(-3px)}
        .scroll-hint{
            position:absolute;bottom:2rem;left:50%;transform:translateX(-50%);
            color:rgba(248,250,252,.3);font-size:.75rem;text-align:center;
            animation:bounce 2.5s infinite;z-index:2;
        }
        @keyframes bounce{0%,100%{transform:translateX(-50%) translateY(0)}55%{transform:translateX(-50%) translateY(8px)}}

        /* ── STATS BAR ── */
        .gb-stats{background:#fff;border-bottom:1px solid #e2e8f0;padding:2.5rem 2rem}
        .gb-stats-inner{
            max-width:1100px;margin:0 auto;
            display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;text-align:center;
        }
        .stat-val{font-size:2.4rem;font-weight:900;line-height:1;margin-bottom:.3rem}
        .stat-val.green{color:#10b981}.stat-val.amber{color:#f59e0b}
        .stat-val.indigo{color:#6366f1}.stat-val.rose{color:#f43f5e}
        .stat-lbl{font-size:.85rem;color:#64748b;font-weight:500}

        /* ── SECTION ── */
        .gb-section{padding:5rem 2rem;max-width:1200px;margin:0 auto}
        .sec-tag{
            display:inline-block;background:#ecfdf5;color:#059669;
            padding:.3rem .9rem;border-radius:9999px;font-size:.75rem;
            font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:.9rem;
        }
        .sec-title{font-size:2.1rem;font-weight:800;color:#0f172a;margin-bottom:.7rem}
        .sec-sub{color:#64748b;font-size:.98rem;max-width:520px;line-height:1.7}
        .sec-head{text-align:center;margin-bottom:3.5rem}
        .sec-head .sec-sub{margin:0 auto}

        /* ── CARDS GRID ── */
        .cards-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:1.25rem}
        .gb-card{
            background:#fff;border-radius:20px;padding:1.8rem;
            border:1px solid #f1f5f9;box-shadow:0 2px 10px rgba(0,0,0,.04);
            transition:all .3s ease;text-decoration:none;color:inherit;display:block;
            position:relative;overflow:hidden;
        }
        .gb-card::after{
            content:'';position:absolute;inset:0;
            border-radius:20px;opacity:0;transition:opacity .3s;
            box-shadow:0 0 0 2px #10b981;
        }
        .gb-card:hover{transform:translateY(-6px);box-shadow:0 20px 48px rgba(0,0,0,.1)}
        .gb-card:hover::after{opacity:1}
        .card-icon{
            width:56px;height:56px;border-radius:14px;
            display:flex;align-items:center;justify-content:center;
            font-size:1.6rem;margin-bottom:1.2rem;
        }
        .gb-card h3{font-size:1.08rem;font-weight:700;margin-bottom:.5rem;color:#0f172a}
        .gb-card p{font-size:.88rem;color:#64748b;line-height:1.6;margin-bottom:1.1rem}
        .card-arrow{
            display:inline-flex;align-items:center;gap:.35rem;
            font-size:.84rem;font-weight:600;color:#10b981;
        }
        .card-arrow i{transition:transform .2s}
        .gb-card:hover .card-arrow i{transform:translateX(4px)}

        /* ── CTA BANNER ── */
        .gb-cta-banner{
            background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);
            border-radius:24px;padding:4rem 2rem;text-align:center;
            margin:0 2rem 4rem;position:relative;overflow:hidden;
        }
        .gb-cta-banner::before{
            content:'';position:absolute;inset:0;
            background:radial-gradient(ellipse at 70% 50%,rgba(16,185,129,.15) 0%,transparent 70%);
        }
        .gb-cta-banner h2{font-size:2rem;font-weight:800;color:#f8fafc;margin-bottom:.75rem;position:relative}
        .gb-cta-banner p{color:rgba(248,250,252,.6);margin-bottom:1.8rem;position:relative}

        /* ── FOOTER ── */
        .gb-footer{background:#0f172a;color:rgba(248,250,252,.45);text-align:center;padding:2rem;font-size:.85rem}
        .gb-footer span{color:#10b981;font-weight:600}

        /* ── GUEST BANNER ── */
        .guest-banner{
            background:linear-gradient(135deg,#fffbeb,#fef3c7);
            border:1px solid #fde68a;border-radius:16px;padding:2rem;
            text-align:center;margin-bottom:3rem;
        }
        .guest-banner h3{color:#92400e;font-size:1.2rem;font-weight:700;margin-bottom:.5rem}
        .guest-banner p{color:#78350f;font-size:.9rem;margin-bottom:1.2rem}

        /* ── ANIMATIONS ── */
        @keyframes fadeDown{from{opacity:0;transform:translateY(-16px)}to{opacity:1;transform:translateY(0)}}
        @keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
        .reveal{opacity:0;transform:translateY(28px);transition:opacity .6s ease,transform .6s ease}
        .reveal.visible{opacity:1;transform:translateY(0)}

        @media(max-width:768px){
            .gb-hero{padding:5rem 1.5rem 3rem}
            .gb-cta-banner{margin:0 1rem 3rem;border-radius:16px}
        }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<!-- HERO -->
<section class="gb-hero">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
    <div class="gb-hero-content">
        <?php if ($isGuest): ?>
            <div class="gb-badge"><i class="fas fa-leaf"></i> Bienvenue sur GreenBite 🌿</div>
        <?php else: ?>
            <div class="gb-badge"><i class="fas fa-leaf"></i> Bienvenue, <?= htmlspecialchars($user['prenom'] ?? 'Utilisateur') ?> 👋</div>
        <?php endif; ?>
        <h1>Mangez <span class="accent">mieux</span>,<br>vivez <span class="accent2">mieux.</span></h1>
        <p>Découvrez des produits sains, des recettes équilibrées, participez à des événements communautaires et contribuez à la solidarité alimentaire.</p>
        <div class="gb-cta">
            <?php if ($isGuest): ?>
                <a href="/Green-Bite/View/auth.php" class="btn-primary"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
                <a href="/Green-Bite/View/auth.php" class="btn-ghost"><i class="fas fa-user-plus"></i> Créer un compte</a>
            <?php else: ?>
                <a href="/Green-Bite/View/front-office/index.php" class="btn-primary"><i class="fas fa-shopping-basket"></i> Explorer les produits</a>
                <a href="/Green-Bite/View/front-office/recettes.php" class="btn-ghost"><i class="fas fa-utensils"></i> Voir les recettes</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="scroll-hint"><span>Défiler</span><br><i class="fas fa-chevron-down" style="margin-top:.4rem;display:block"></i></div>
</section>

<!-- STATS -->
<section class="gb-stats reveal">
    <div class="gb-stats-inner">
        <div><div class="stat-val green" data-target="500">0</div><div class="stat-lbl">🛒 Produits disponibles</div></div>
        <div><div class="stat-val amber" data-target="120">0</div><div class="stat-lbl">🍽️ Recettes publiées</div></div>
        <div><div class="stat-val indigo" data-target="80">0</div><div class="stat-lbl">🎁 Dons effectués</div></div>
        <div><div class="stat-val rose" data-target="35">0</div><div class="stat-lbl">📅 Événements organisés</div></div>
    </div>
</section>

<!-- FEATURES -->
<section class="gb-section">
    <div class="sec-head reveal">
        <span class="sec-tag">Notre plateforme</span>
        <h2 class="sec-title">Tout ce dont vous avez besoin</h2>
        <p class="sec-sub">Une expérience complète autour de l'alimentation saine et de la solidarité communautaire.</p>
    </div>

    <div class="cards-grid">
        <?php
        $authLink = '/Green-Bite/View/auth.php';
        $cards = [
            ['icon'=>'🛒','bg'=>'#ecfdf5','href'=>'/Green-Bite/View/front-office/index.php','title'=>'Produits & Nutrition','desc'=>'Explorez notre catalogue avec le Nutriscore, les calories et les avis clients.','lbl'=>'Voir les produits','delay'=>'.05s'],
            ['icon'=>'🍽️','bg'=>'#fefce8','href'=>'/Green-Bite/View/front-office/recettes.php','title'=>'Recettes Saines','desc'=>'Découvrez des recettes équilibrées partagées par notre communauté.','lbl'=>'Voir les recettes','delay'=>'.1s'],
            ['icon'=>'🎁','bg'=>'#f5f3ff','href'=>'/Green-Bite/View/front-office/dons.php','title'=>'Dons Alimentaires','desc'=>'Publiez ou réservez des dons alimentaires disponibles près de chez vous.','lbl'=>'Voir les dons','delay'=>'.15s'],
            ['icon'=>'📅','bg'=>'#fdf2f8','href'=>'/Green-Bite/View/front-office/evenements/listEvenements.php','title'=>'Événements','desc'=>'Rejoignez des ateliers culinaires et marchés bio organisés par la communauté.','lbl'=>'Voir les événements','delay'=>'.2s'],
            ['icon'=>'🛍️','bg'=>'#eff6ff','href'=>'/Green-Bite/View/front-office/cart.php','title'=>'Mon Panier','desc'=>'Consultez votre panier et passez commande facilement.','lbl'=>'Voir mon panier','delay'=>'.25s'],
            ['icon'=>'👤','bg'=>'#f8fafc','href'=>'/Green-Bite/View/front-office/profile.php','title'=>'Mon Profil','desc'=>'Gérez vos informations et consultez votre historique de commandes.','lbl'=>'Voir mon profil','delay'=>'.3s'],
        ];
        foreach ($cards as $c):
            $href = $isGuest ? $authLink : $c['href'];
        ?>
        <a href="<?= $href ?>" class="gb-card reveal" style="transition-delay:<?= $c['delay'] ?>">
            <div class="card-icon" style="background:<?= $c['bg'] ?>"><?= $c['icon'] ?></div>
            <h3><?= $c['title'] ?></h3>
            <p><?= $c['desc'] ?></p>
            <span class="card-arrow"><?= $isGuest ? 'Se connecter' : $c['lbl'] ?> <i class="fas fa-arrow-right"></i></span>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- GUEST BANNER or CTA -->
<?php if ($isGuest): ?>
<div style="max-width:1200px;margin:0 auto;padding:0 2rem 4rem">
    <div class="guest-banner reveal">
        <h3>🔒 Accès réservé aux membres</h3>
        <p>Créez un compte gratuit pour accéder aux produits, recettes, dons et événements de la communauté GreenBite.</p>
        <a href="/Green-Bite/View/auth.php" class="btn-primary" style="display:inline-flex"><i class="fas fa-user-plus"></i> Rejoindre GreenBite</a>
    </div>
</div>
<?php else: ?>
<div class="gb-cta-banner reveal">
    <h2>🌿 Prêt à bien manger ?</h2>
    <p>Commencez par explorer notre catalogue soigneusement sélectionné.</p>
    <a href="/Green-Bite/View/front-office/index.php" class="btn-primary" style="display:inline-flex"><i class="fas fa-leaf"></i> Découvrir les produits</a>
</div>
<?php endif; ?>

<footer class="gb-footer">
    <p>© 2026 <span>GreenBite</span> — Manger sain, vivre mieux. 🌱</p>
</footer>

<?php if (!$isGuest) require_once __DIR__ . '/../includes/chatbot_widget.php'; ?>

<script>
// Scroll reveal
const obs = new IntersectionObserver(entries => entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); }), {threshold:.1});
document.querySelectorAll('.reveal').forEach(el => obs.observe(el));

// Counters
const statsObs = new IntersectionObserver(entries => {
    if (!entries[0].isIntersecting) return;
    document.querySelectorAll('[data-target]').forEach(el => {
        const target = +el.dataset.target, dur = 1800, step = target/(dur/16);
        let cur = 0;
        const t = setInterval(() => { cur = Math.min(cur+step, target); el.textContent = Math.floor(cur)+'+'; if(cur>=target) clearInterval(t); }, 16);
    });
    statsObs.disconnect();
}, {threshold:.3});
const s = document.querySelector('.gb-stats');
if(s) statsObs.observe(s);
</script>
</body>
</html>
