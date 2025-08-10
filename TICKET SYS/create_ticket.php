<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'utilisateur') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!$title || !$description) {
        // Tu peux faire une redirection avec message d'erreur ou afficher un message
        die('Tous les champs sont requis.');
    }

    // Insérer le ticket en base
    $stmt = $pdo->prepare("INSERT INTO tickets (id_utilisateur, title, description, date_creation) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user_id, $title, $description]);

    // Redirection vers le dashboard
    header('Location: user_dashboard.php');
    exit();
}

// Accès direct interdit
header('Location: user_dashboard.php');
exit();
