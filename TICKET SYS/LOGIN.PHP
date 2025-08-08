<?php
// Fichier : login.php
session_start();
require_once 'config.php'; // Inclure la connexion à la BDD

// Si l'utilisateur est déjà connecté, on le redirige
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['user_role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php'));
    exit();
}

// Logique pour l'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (email, mot_de_passe, role) VALUES (?, ?, 'utilisateur')");
            $stmt->execute([$email, $hashed_password]);
            $message = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
        } catch (PDOException $e) {
            // Gérer l'erreur si l'email existe déjà
            if ($e->errorInfo[1] == 1062) {
                $error = "Cet email est déjà utilisé.";
            } else {
                $error = "Une erreur est survenue.";
            }
        }
    }
}

// Logique pour la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: user_dashboard.php');
        }
        exit();
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<!-- Le code HTML, CSS et JS reste le même que dans la réponse précédente -->
<!-- Copiez-collez ici le code <!DOCTYPE html>... de la réponse précédente -->
<!DOCTYPE html>
<html lang="fr">
<head>
<link rel="icon" type="image/png" href="../IMG/AYV RE.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion / Inscription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('../IMG/ATCMARP.PNG') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            position: relative;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.45);
            z-index: 0;
        }
        .container {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 2rem;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(31,38,135,0.12);
            width: 400px;
            position: relative;
            z-index: 1;
        }
        h2 {
            text-align: center;
            color: #b5c909ff;
            text-shadow: 0 0 8px #c5d302ff, 0 0 16px #8fc204ff;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        form { display: flex; flex-direction: column; }
        .input-group { margin-bottom: 1rem; }
        label {
            margin-bottom: 0.5rem;
            color: #b0ce04ff;
            text-shadow: 0 0 6px #9dc704ff;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #9cc505ff;
            border-radius: 4px;
            box-sizing: border-box;
            background: rgba(0,0,0,0.18);
            color: #99ce09ff;
            font-weight: 500;
            text-shadow: 0 0 6px #9bbd05ff;
        }
        button {
            padding: 0.75rem;
            background-color: #aabd01ff;
            color: #181818;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            box-shadow: 0 0 8px #99b308ff, 0 0 16px #9dc001ff;
            transition: background 0.2s, color 0.2s;
        }
        button:hover {
            background-color: #181818;
            color: #718505ff;
            box-shadow: 0 0 16px #6f8606ff;
        }
        .toggle-link { text-align: center; margin-top: 1rem; }
        .toggle-link a {
            color: #c4350aff;
            text-decoration: none;
            cursor: pointer;
            text-shadow: 0 0 6px #b64c0eff;
        }
        .toggle-link  {
            margin: 0.5rem 0;
            color: #ffffffff;
            text-shadow: 0 0 6px #ffffffff;
            
        }
        .message {
            text-align: center;
            color: #00ffe7;
            margin-bottom: 1rem;
            text-shadow: 0 0 6px #00ffe7;
        }
        .error {
            text-align: center;
            color: #ff0055;
            margin-bottom: 1rem;
            text-shadow: 0 0 6px #ff0055;
        }
        #signup-form { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Formulaire de Connexion -->
        <div id="login-form">
            <h2>Connexion</h2>
            <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
            <?php if (isset($message)) { echo "<p class='message'>$message</p>"; } ?>
            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="login-password">Mot de passe</label>
                    <input type="password" id="login-password" name="password" required>
                </div>
                <button type="submit" name="login">Se connecter</button>
            </form>
            <p class="toggle-link">Pas de compte ? <a onclick="toggleForms()">Créez-en un</a></p>
        </div>

        <!-- Formulaire d'Inscription -->
        <div id="signup-form">
            <h2>Inscription</h2>
            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="signup-email">Email</label>
                    <input type="email" id="signup-email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="signup-password">Mot de passe</label>
                    <input type="password" id="signup-password" name="password" required>
                </div>
                <button type="submit" name="signup">S'inscrire</button>
            </form>
            <p class="toggle-link">Déjà un compte ? <a onclick="toggleForms()">Connectez-vous</a></p>
        </div>
    </div>
    <script>
        function toggleForms() {
            const loginForm = document.getElementById('login-form');
            const signupForm = document.getElementById('signup-form');
            if (loginForm.style.display === 'none') {
                loginForm.style.display = 'block';
                signupForm.style.display = 'none';
            } else {
                loginForm.style.display = 'none';
                signupForm.style.display = 'block';
            }
        }
    </script>
</body>
</html>