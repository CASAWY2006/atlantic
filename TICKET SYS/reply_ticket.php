<?php
// Fichier : reply_ticket.php (Version améliorée)
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
$ticket_id = $_GET['id'] ?? 0;
if (!$ticket_id) { header('Location: manage_tickets.php'); exit(); }

// Logique pour poster une réponse/changer le statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reply'])) {
    $reponse = trim($_POST['reponse']);
    $statut = $_POST['statut'];
    $stmt = $pdo->prepare("UPDATE tickets SET reponse_admin = ?, statut = ? WHERE id = ?");
    $stmt->execute([$reponse, $statut, $ticket_id]);
    $success_message = "Réponse envoyée et statut mis à jour.";
}

// Récupérer les infos complètes du ticket
$stmt = $pdo->prepare("SELECT t.*, u.username, u.profile_image FROM tickets t JOIN utilisateurs u ON t.id_utilisateur = u.id WHERE t.id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$ticket) { header('Location: manage_tickets.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Répondre au Ticket #<?= $ticket['id'] ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Ticket #<?= $ticket['id'] ?></h1>
        <a href="manage_tickets.php" class="btn btn-primary">&larr; Retour</a>
    </div>

    <?php if(isset($success_message)) { echo "<p class='message'>$success_message</p>"; } ?>

    <div class="ticket-reply-grid">
        <div class="ticket-details">
            <h3>Détails du Ticket</h3>
            <p><strong>Utilisateur :</strong> <?= htmlspecialchars($ticket['username']) ?></p>
            <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($ticket['date_creation'])) ?></p>
            <p><strong>Statut :</strong> <span class="status-badge status-<?= str_replace(' ', '-', strtolower($ticket['statut'])) ?>"><?= htmlspecialchars($ticket['statut']) ?></span></p>
            <hr>
            <h4>Message de l'utilisateur :</h4>
            <div class="user-message-box">
                <?= nl2br(htmlspecialchars($ticket['message'])) ?>
            </div>
        </div>

        <div class="form-container">
            <h3>Votre Réponse</h3>
            <form action="reply_ticket.php?id=<?= $ticket['id'] ?>" method="POST">
                <div class="input-group">
                    <label for="reponse">Message de réponse :</label>
                    <textarea name="reponse" id="reponse" rows="10"><?= htmlspecialchars($ticket['reponse_admin'] ?? '') ?></textarea>
                </div>
                <div class="input-group">
                    <label for="statut">Mettre à jour le statut :</label>
                    <select name="statut" id="statut">
                        <!-- NOUVEAU MENU DE STATUT COMPLET -->
                        <option value="Ouvert" <?= $ticket['statut'] == 'Ouvert' ? 'selected' : '' ?>>Ouvert</option>
                        <option value="En attente" <?= $ticket['statut'] == 'En attente' ? 'selected' : '' ?>>En attente (de la réponse client)</option>
                        <option value="Fermé" <?= $ticket['statut'] == 'Fermé' ? 'selected' : '' ?>>Fermé (résolu)</option>
                    </select>
                </div>
                <button type="submit" name="submit_reply" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Envoyer la réponse</button>
            </form>
        </div>
    </div>
    
    <!-- NOUVELLE SECTION ZONE DE DANGER -->
    <div class="danger-zone">
        <h4>Zone de Danger</h4>
        <p>Cette action est irréversible et supprimera toutes les données liées à ce ticket.</p>
        <a href="delete_ticket.php?id=<?= $ticket['id'] ?>" class="btn btn-danger-outline"
           onclick="return confirm('Êtes-vous absolument certain de vouloir supprimer ce ticket ?');">
           <i class="fa-solid fa-trash-alt"></i> Supprimer Définitivement le Ticket
        </a>
    </div>
</div>
</body>
</html>