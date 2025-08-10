<?php
$token = $_GET['token'] ?? '';

if (!$token) {
    die("Token manquant");
}

// Chercher dans pending_users
$stmt = $pdo->prepare("SELECT * FROM pending_users WHERE token = ? AND verified = 0");
$stmt->execute([$token]);
$user = $stmt->fetch();

if ($user) {
    // Copier vers users
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$user['name'], $user['email'], $user['password_hash']]);

    // Marquer vérifié
    $stmt = $pdo->prepare("UPDATE pending_users SET verified = 1 WHERE id = ?");
    $stmt->execute([$user['id']]);

    echo "Votre compte est validé, vous pouvez maintenant vous connecter.";
} else {
    echo "Lien invalide ou compte déjà vérifié.";
}
?>
