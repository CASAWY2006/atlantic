<?php
session_start();
require_once 'config.php';

// URL de base du site (modifiable facilement ici)
$base_url = "http://localhost/ATC_V1/TICKET%20SYS/"; // <-- modifier selon le domaine / chemin

// PHPMailer - Remplacement de la fonction d'envoi mail
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/src/Exception.php';
require_once __DIR__ . '/src/PHPMailer.php';
require_once __DIR__ . '/src/SMTP.php';

function sendConfirmationEmail($email, $token, $base_url) {
    $mail = new PHPMailer(true);
    try {
        // Configurer le serveur SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'atlanticmarocrp@gmail.com';
        $mail->Password = 'sdlq ondn vdod zzmh'; // Mot de passe d'application Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Expéditeur et destinataire
        $mail->setFrom('atlanticmarocrp@gmail.com', 'Atlantic RP');
        $mail->addAddress($email);

        // Contenu du mail
        $mail->isHTML(true);
        $mail->Subject = "Confirmez votre inscription";
        $confirmation_link = $base_url . "confirm.php?token=" . urlencode($token);
        // Correction du chemin si le bouton ne fonctionne pas
        if (!file_exists(__DIR__ . '/confirm.php')) {
            // Si confirm.php est dans TICKET SYS, mais l'URL n'est pas bonne, on corrige
            $confirmation_link = "http://localhost/ATC_v1/TICKET%20SYS/confirm.php?token=" . urlencode($token);
        }
        $mail->Body = "Bonjour,<br><br>Merci de confirmer votre inscription en cliquant sur le bouton ci-dessous :<br><br>"
            . "<a href='$confirmation_link' style='display:inline-block;padding:12px 24px;background:#007bff;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;'>Confirmer mon compte</a>"
            . "<br><br>Cordialement,<br>L'équipe";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Si utilisateur déjà connecté, rediriger selon rôle
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['user_role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php'));
    exit();
}

// Inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($username) && !empty($password)) {
        // Vérifier que email et username ne sont pas déjà dans utilisateurs ou pending_users
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        $existsUser = $stmt->fetch();

        $stmt = $pdo->prepare("SELECT id FROM pending_users WHERE email = ? OR name = ?");
        $stmt->execute([$email, $username]);
        $existsPending = $stmt->fetch();

        if ($existsUser || $existsPending) {
            $error = "Cet email ou nom d'utilisateur est déjà utilisé ou en attente de validation.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));

            try {
                $created_at = date('Y-m-d H:i:s');
                $stmt = $pdo->prepare("INSERT INTO pending_users (name, email, password_hash, token, created_at) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $token, $created_at]);

                // Envoi mail confirmation
                $base_url = "http://localhost/TICKET_SYS/";  // Modifie l'URL ici selon ton serveur
                // Multilingue
                $success_fr = "Compte créé avec succès ! Un email de confirmation vous a été envoyé.";
                $success_en = "Account created successfully! A confirmation email has been sent.";
                $success_ar = "تم إنشاء الحساب بنجاح! تم إرسال بريد إلكتروني للتأكيد.";
                // Ici, tu peux choisir la langue selon la préférence utilisateur ou la langue du navigateur
                $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
                if ($lang === 'ar') {
                    $message = $success_ar;
                } elseif ($lang === 'en') {
                    $message = $success_en;
                } else {
                    $message = $success_fr;
                }

                if (!sendConfirmationEmail($email, $token, $base_url)) {
                    $error = "Erreur lors de l'envoi de l'email de confirmation.";
                }
            } catch (PDOException $e) {
                $error = "Une erreur est survenue lors de l'inscription.";
            }
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
} // <-- fermeture du bloc d'inscription
// Connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? OR username = ?");
    $stmt->execute([$identifier, $identifier]);
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
        // Vérifie si le compte est en attente de validation
        $stmt = $pdo->prepare("SELECT * FROM pending_users WHERE email = ? OR name = ?");
        $stmt->execute([$identifier, $identifier]);
        $pending = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pending) {
            // Propose de renvoyer le mail de confirmation
            if (isset($_POST['resend_confirmation'])) {
                if (sendConfirmationEmail($pending['email'], $pending['token'], $base_url)) {
                    $message = "Un nouvel email de confirmation a été envoyé à votre adresse.";
                } else {
                    $error = "Erreur lors de l'envoi du nouvel email de confirmation.";
                }
            } else {
                $error = "Votre compte n'est pas encore validé. <form method='POST'><input type='hidden' name='identifier' value='" . htmlspecialchars($identifier) . "'><button type='submit' name='resend_confirmation'>Renvoyer l'email de confirmation</button></form>";
            }
        } else {
            $error = "Identifiant ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Connexion / Inscription</title>
    <style>
        /* Ton CSS ici (inchangé) */
        body {
            margin: 0; padding: 0; min-height: 100vh;
            background-color: #020220;
            background-image: url('../IMG/ATCMARP.PNG');
            background-position: center -240px;
            background-repeat: no-repeat;
            background-size: 100% auto;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
        }
        .container { background-color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 400px; }
        h2 { text-align: center; color: #333; }
        form { display: flex; flex-direction: column; }
        .input-group { margin-bottom: 1rem; }
        label { margin-bottom: 0.5rem; color: #555; }
        input { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { padding: 0.75rem; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background-color: #0056b3; }
        .toggle-link { text-align: center; margin-top: 1rem; }
        .toggle-link a { color: #007bff; text-decoration: none; cursor: pointer; }
        .message { text-align: center; color: green; margin-bottom: 1rem; }
        .error { text-align: center; color: red; margin-bottom: 1rem; }
        #signup-form { display: none; }
    </style>
</head>
<body>
<div class="container">
    <div id="login-form">
        <h2>Connexion</h2>
        <?php 
        if (isset($error)) { 
            echo "<p class='error'>$error</p>"; 
            // Si le compte est en attente de validation, proposer le lien vers la nouvelle page
            if (strpos($error, "en attente de validation") !== false || strpos($error, "n'est pas encore validé") !== false) {
                echo '<div style="text-align:center;margin-bottom:10px;">';
                echo '<a href="resend_verification.php">Renvoyer le mail de vérification</a>';
                echo '</div>';
            }
        }
        ?>
        <?php if (isset($message)) { echo "<p class='message'>$message</p>"; } ?>
        <form action="login.php" method="POST">
            <div class="input-group">
                <label for="login-identifier">Email ou Nom d'utilisateur</label>
                <input type="text" id="login-identifier" name="identifier" required />
            </div>
            <div class="input-group">
                <label for="login-password">Mot de passe</label>
                <input type="password" id="login-password" name="password" required />
            </div>
            <button type="submit" name="login">Se connecter</button>
        </form>
        <p class="toggle-link">Pas de compte ? <a onclick="toggleForms()">Créez-en un</a></p>
    </div>

    <div id="signup-form">
        <h2>Inscription</h2>
        <form action="login.php" method="POST">
            <div class="input-group">
                <label for="signup-username">Nom d'utilisateur</label>
                <input type="text" id="signup-username" name="username" required />
            </div>
            <div class="input-group">
                <label for="signup-email">Email</label>
                <input type="email" id="signup-email" name="email" required />
            </div>
            <div class="input-group">
                <label for="signup-password">Mot de passe</label>
                <input type="password" id="signup-password" name="password" required />
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