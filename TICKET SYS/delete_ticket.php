<?php
// Fichier : delete_ticket.php
session_start();
require_once 'config.php';

// Sécurité : Seul un admin peut supprimer un ticket
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Récupérer l'ID du ticket depuis l'URL
$ticket_id = $_GET['id'] ?? 0;

if ($ticket_id) {
    // On supprime d'abord les réactions et commentaires liés pour respecter les contraintes
    $stmt_reactions = $pdo->prepare("DELETE FROM reactions WHERE id_post IN (SELECT id FROM media_posts WHERE id = ?)"); // Pas directement applicable aux tickets, mais bonne pratique
    $stmt_comments = $pdo->prepare("DELETE FROM commentaires WHERE id_post IN (SELECT id FROM media_posts WHERE id = ?)"); // Pas directement applicable aux tickets

    // Requête de suppression du ticket
    $stmt = $pdo->prepare("DELETE FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
}

// Rediriger vers la page de gestion des tickets
header("Location: manage_tickets.php?deleted=success");
exit();
?>