<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    // Vérifie si le compte est déjà validé
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $message = "Ce compte est déjà validé. <a href='login.php'>Connectez-vous</a>";
    } else {
        // Vérifie si le compte est en attente
        $stmt = $pdo->prepare("SELECT * FROM pending_users WHERE email = ?");
        $stmt->execute([$email]);
        $pendingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pendingUser) {
            // Génère un nouveau token et met à jour
            $token = bin2hex(random_bytes(32));
            $created_at = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE pending_users SET token = ?, created_at = ? WHERE email = ?");
            $stmt->execute([$token, $created_at, $email]);
            // Envoi du mail
            require_once 'LOGIN.PHP';
            sendConfirmationEmail($email, $token, "http://localhost/ATC_v1/TICKET%20SYS/");
            $message = "Un nouveau mail de vérification a été envoyé à votre adresse.";
        } else {
            $message = "Aucun compte en attente trouvé pour cet email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Renvoyer le mail de vérification</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #020220; font-family: Arial, sans-serif; }
        .container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 400px; margin: 80px auto; }
        h2 { text-align: center; }
        form { display: flex; flex-direction: column; }
        label, input, button { margin-bottom: 1rem; }
        button { background: #007bff; color: white; border: none; border-radius: 4px; padding: 0.75rem; cursor: pointer; }
        button:hover { background: #0056b3; }
        .message { text-align: center; color: green; }
        .error { text-align: center; color: red; }
    </style>
</head>
<body>
<div class="container">
    <h2>Renvoyer le mail de vérification</h2>
    <?php 
    if (isset($message)) {
        echo '<p class="message">' . $message . '</p>';
        echo '<div style="text-align:center;margin-top:1rem;"><a href="login.php"><button style="background:#007bff;color:white;border:none;padding:0.75rem 1.5rem;border-radius:4px;cursor:pointer;">Retour à la connexion</button></a></div>';
    } else {
    ?>
    <form method="POST">
        <label for="email">Email :</label>
        <input type="email" name="email" id="email" required>
        <button type="submit">Renvoyer</button>
    </form>
    <div style="text-align:center;margin-top:1rem;"><a href="login.php">Retour à la connexion</a></div>
    <?php } ?>
</div>
</body>
</html>
