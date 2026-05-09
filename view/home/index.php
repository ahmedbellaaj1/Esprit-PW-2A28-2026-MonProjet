<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Greenbite - Accueil (Profil)</title>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .home-container {
            display: flex;
            justify-content: center;
            align-items: stretch;
            min-height: calc(100vh - 68px);
            padding: 3rem;
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
            gap: 40px;
        }

        /* 📢 Pub Section */
        .promo-section {
            flex: 1;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            color: white;
            justify-content: center;
        }

        .promo-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideIn 0.6s ease-out forwards;
            opacity: 0;
        }

        .promo-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .promo-card:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes slideIn {
            from {
                transform: translateX(-30px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .promo-card i {
            font-size: 30px;
            margin-bottom: 15px;
            display: block;
            color: #f0fdf4;
        }

        .promo-card h3 {
            font-size: 20px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .promo-card p {
            font-size: 14px;
            line-height: 1.6;
            opacity: 0.9;
        }

        .form-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 900px) {
            .home-container {
                flex-direction: column;
                align-items: center;
                padding: 20px;
            }

            .promo-section {
                order: 2;
            }
        }

        .form-card h2 {
            color: #0f766e;
            text-align: center;
            margin-bottom: 24px;
            font-size: 24px;
        }

        .input-group {
            margin-bottom: 16px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #475569;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #0f766e;
        }

        .submit-btn {
            width: 100%;
            margin-top: 10px;
            padding: 14px;
            font-size: 16px;
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <a class="navbar-logo" href="index.php?page=home">Greenbite</a>

        <div class="navbar-links">
            <a class="active" href="index.php?page=home">Accueil</a>
            <a href="index.php?page=dashboard">Dashboard</a>
            <a href="index.php?page=admin">Back Office</a>
        </div>

        <div class="navbar-right">
            <div class="nav-avatar"><i class="fas fa-user"></i></div>
        </div>
    </nav>

    <!-- SECTION ACCUEIL -->
    <div class="home-container">

        <!-- 📢 PROMOTIONS & PUB -->
        <div class="promo-section">
            <div class="promo-card">
                <i class="fas fa-heartbeat"></i>
                <h3>Prenez soin de vous</h3>
                <p>Rejoignez <b>Greenbite</b> pour suivre votre santé au quotidien. Une alimentation équilibrée est la
                    clé d'une vie longue et dynamique.</p>
            </div>

            <div class="promo-card">
                <i class="fas fa-robot"></i>
                <h3>IA Nutritionnelle</h3>
                <p>Notre intelligence artificielle analyse vos allergies et préférences pour vous suggérer les meilleurs
                    produits en temps réel.</p>
            </div>

            <div class="promo-card">
                <i class="fas fa-file-pdf"></i>
                <h3>Profil Exportable</h3>
                <p>Générez votre fiche santé en PDF en un clic pour l'avoir toujours avec vous ou la partager avec votre
                    nutritionniste.</p>
            </div>
        </div>

        <!-- FORMULAIRE -->
        <div class="form-card">
            <h2>🌿 La connexion au profil Greenbite</h2>

            <form id="form">
                <div class="input-group">
                    <label for="nom">Nom complet</label>
                    <input type="text" id="nom" class="form-input" placeholder="Ex: Jean Dupont">
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" class="form-input" placeholder="Ex: jean.dupont@email.com">
                </div>

                <div class="input-group">
                    <label for="preferences">Préférences alimentaires</label>
                    <input type="text" id="preferences" class="form-input" placeholder="Ex: Végétarien, Sans gluten...">
                </div>

                <div class="input-group">
                    <label for="allergies">Allergies</label>
                    <input type="text" id="allergies" class="form-input" placeholder="Ex: Arachides, Lactose...">
                </div>

                <div style="display: flex; gap: 16px;">
                    <div class="input-group" style="flex: 1;">
                        <label for="poids">Poids ideal (kg)</label>
                        <input type="number" id="poids" class="form-input" placeholder="Ex: 70">
                    </div>

                    <div class="input-group" style="flex: 1;">
                        <label for="age">Âge</label>
                        <input type="number" id="age" class="form-input" placeholder="Ex: 25">
                    </div>
                </div>

                <div class="input-group">
                    <label for="calories">Calories visées / jour</label>
                    <input type="number" id="calories" class="form-input" placeholder="Ex: 2000">
                </div>

                <!-- 🔒 Politique de Confidentialité -->
                <div class="input-group" style="display: flex; align-items: flex-start; gap: 10px; margin-top: 10px;">
                    <input type="checkbox" id="privacy" style="width: 20px; height: 20px; cursor: pointer; accent-color: #0f766e; margin-top: 3px;">
                    <label for="privacy" style="font-size: 13px; color: #64748b; line-height: 1.5; cursor: pointer;">
                        J'accepte que les données saisies dans ce formulaire puissent être traitées conformément à la Politique de Confidentialité.*
                    </label>
                </div>

                <div style="display: flex; gap: 16px; margin-top: 20px;">
                    <button type="submit" class="primary-btn submit-btn" style="flex: 2;">
                        <i class="fas fa-save"></i> Enregistrer & PDF
                    </button>
                    <button type="button" onclick="searchProfile()" class="primary-btn submit-btn"
                        style="flex: 1; background: #64748b;">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // 🔍 Charger un profil existant pour modification
        async function searchProfile() {
            const email = prompt("Veuillez saisir votre email pour modifier votre profil :");
            if (!email) return;

            try {
                const response = await fetch(`api/get_user.php?email=${encodeURIComponent(email)}`);
                const user = await response.json();

                if (user.error) {
                    alert("❌ " + user.message);
                } else {
                    document.getElementById("nom").value = user.nom;
                    document.getElementById("email").value = user.email;
                    document.getElementById("preferences").value = user.preferences;
                    document.getElementById("allergies").value = user.allergies;
                    document.getElementById("poids").value = user.poids;
                    document.getElementById("age").value = user.age;
                    document.getElementById("calories").value = user.calories;
                    alert("✅ Profil chargé ! Vous pouvez maintenant le modifier.");
                }
            } catch (err) {
                console.error(err);
                alert("❌ Erreur lors de la récupération du profil");
            }
        }

        document.getElementById("form").addEventListener("submit", async (e) => {
            e.preventDefault();

            const data = new URLSearchParams();
            data.append("nom", document.getElementById("nom").value);
            data.append("email", document.getElementById("email").value);
            data.append("preferences", document.getElementById("preferences").value);
            data.append("allergies", document.getElementById("allergies").value);
            data.append("poids", document.getElementById("poids").value);
            data.append("age", document.getElementById("age").value);
            data.append("calories", document.getElementById("calories").value);

            try {
                const form = document.createElement("form");
                form.method = "POST";
                form.action = "http://localhost/PROJET%202A28/api/register.php";
                form.target = "_blank"; // Download PDF in new tab so current page can redirect smoothly

                for (let pair of data.entries()) {
                    let input = document.createElement("input");
                    input.type = "hidden";
                    input.name = pair[0];
                    input.value = pair[1];
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();

                // Redirect to dashboard after starting the PDF download
                setTimeout(() => {
                    window.location.href = "index.php?page=dashboard";
                }, 1500);

            } catch (err) {
                console.error(err);
                alert("❌ Erreur serveur");
            }
        });
    </script>

</body>

</html>