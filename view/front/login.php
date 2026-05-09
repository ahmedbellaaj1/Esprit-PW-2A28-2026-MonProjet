<?php
// view/front/login.php - Page de connexion temporaire
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Simulation de connexion pour les tests
    if ($email === 'ahmed@email.com' && $password === 'password123') {
        $_SESSION['user'] = [
            'id' => 1,
            'nom' => 'Ben Ali',
            'prenom' => 'Ahmed',
            'email' => 'ahmed@email.com',
            'role' => 'user'
        ];
        header('Location: listEvenements.php');
        exit();
    } elseif ($email === 'admin@greenbite.com' && $password === 'admin123') {
        $_SESSION['user'] = [
            'id' => 8,
            'nom' => 'Admin',
            'prenom' => 'GreenBite',
            'email' => 'admin@greenbite.com',
            'role' => 'admin'
        ];
        header('Location: listEvenements.php');
        exit();
    } else {
        $error = "Email ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - GreenBite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #e6f7f5 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            font-size: 1.8rem;
            color: #0f172a;
        }
        .login-header span {
            color: #0f766e;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #0f172a;
        }
        input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
        }
        input:focus {
            outline: none;
            border-color: #14b8a6;
        }
        .btn-login {
            width: 100%;
            background: #0f766e;
            color: white;
            padding: 0.875rem;
            border: none;
            border-radius: 9999px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-login:hover {
            background: #0c5f58;
        }
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.75rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .info-test {
            background: #f1f5f9;
            padding: 1rem;
            border-radius: 12px;
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Green<span>Bite</span></h1>
            <p>Connectez-vous à votre compte</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="text" name="email" placeholder="exemple@email.com">
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" placeholder="••••••••">
            </div>
            <button type="submit" class="btn-login">Se connecter</button>
        </form>
        
        <div class="info-test">
            <strong>📝 Comptes de test :</strong>
            <p>📧 ahmed@email.com / password123 (utilisateur)</p>
            <p>📧 admin@greenbite.com / admin123 (admin)</p>
        </div>
    </div>
</body>
</html>