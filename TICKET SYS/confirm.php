<?php
session_start();
require_once 'config.php';

if (!isset($_GET['token'])) {
    die("Lien de confirmation invalide.");
}

$token = $_GET['token'];


$stmt = $pdo->prepare("SELECT * FROM pending_users WHERE token = ?");
$stmt->execute([$token]);
$pendingUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pendingUser) {
    die("Ce lien de confirmation est invalide ou a déjà été utilisé.");
}

// Vérifie l'expiration du token (10 min)
$created_at = strtotime($pendingUser['created_at']);
$now = time();
if ($now - $created_at > 600) {
    // Supprime le compte en attente
    $stmt = $pdo->prepare("DELETE FROM pending_users WHERE id = ?");
    $stmt->execute([$pendingUser['id']]);
    echo "<h2>Le lien de confirmation a expiré.</h2>";
    echo '<div style="text-align:center;margin-top:1rem;"><a href="resend_verification.php">Renvoyer le mail de vérification</a></div>';
    exit();
}

$stmt = $pdo->prepare("INSERT INTO utilisateurs (username, email, mot_de_passe, role, profile_image) VALUES (?, ?, ?, 'utilisateur', 'img/profil.png')");
$stmt->execute([
    $pendingUser['name'],
    $pendingUser['email'],
    $pendingUser['password_hash']
]);

$stmt = $pdo->prepare("DELETE FROM pending_users WHERE id = ?");
$stmt->execute([$pendingUser['id']]);

echo "<h2>Votre compte a été confirmé avec succès !</h2>";
echo "<p><a href='login.php'>Cliquez ici pour vous connecter</a></p>";
?>
