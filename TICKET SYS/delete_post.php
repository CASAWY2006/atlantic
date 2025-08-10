<?php
// Fichier : delete_post.php
session_start();
require_once 'config.php';

// Sécurité : Seul un admin peut supprimer un post
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Récupérer l'ID du post depuis l'URL
$post_id = $_GET['id'] ?? 0;

if ($post_id) {
    // Supprimer le post. Grâce aux contraintes "ON DELETE CASCADE" dans votre BDD,
    // les commentaires et réactions liés seront automatiquement supprimés.
    $stmt = $pdo->prepare("DELETE FROM media_posts WHERE id = ?");
    $stmt->execute([$post_id]);
}

// Rediriger vers la page des médias
header("Location: media.php?deleted=true");
exit();
?>