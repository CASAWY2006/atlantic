<?php
// Fichier : reply_ticket.php
session_start();
require_once 'config.php';

// Protection : doit être admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Récupérer l'ID du ticket depuis l'URL
$ticket_id = $_GET['id'] ?? 0;
if (!$ticket_id) {
    header('Location: admin_dashboard.php');
    exit();
}

// Logique pour envoyer une réponse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reply'])) {
    $reponse = trim($_POST['reponse']);
    $statut = $_POST['statut'];
    
    $stmt = $pdo->prepare("UPDATE tickets SET reponse_admin = ?, statut = ? WHERE id = ?");
    $stmt->execute([$reponse, $statut, $ticket_id]);
    $success_message = "Réponse envoyée et statut mis à jour.";
}

// Récupérer les informations complètes du ticket
$stmt = $pdo->prepare("
    SELECT tickets.*, utilisateurs.email 
    FROM tickets 
    JOIN utilisateurs ON tickets.id_utilisateur = utilisateurs.id 
    WHERE tickets.id = ?
");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    // Si le ticket n'existe pas, on redirige
    header('Location: admin_dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Répondre au Ticket #<?= $ticket['id'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        a { color: #007bff; }
        .ticket-info { border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px; }
        .user-message { background-color: #f9f9f9; padding: 15px; border-radius: 5px; }
        textarea, select { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <p><a href="admin_dashboard.php">&larr; Retour au tableau de bord</a></p>
    <h1>Ticket #<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['titre']) ?></h1>
    
    <div class="ticket-info">
        <p><strong>De :</strong> <?= htmlspecialchars($ticket['email']) ?></p>
        <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($ticket['date_creation'])) ?></p>
        <p><strong>Statut actuel :</strong> <?= $ticket['statut'] ?></p>
        <div class="user-message">
            <strong>Message de l'utilisateur :</strong><br>
            <?= nl2br(htmlspecialchars($ticket['message'])) ?>
        </div>
    </div>

    <h2>Répondre au ticket</h2>
    <?php if(isset($success_message)) { echo "<p style='color:green;'>$success_message</p>"; } ?>
    <form action="reply_ticket.php?id=<?= $ticket['id'] ?>" method="POST">
        <label for="reponse">Votre réponse :</label>
        <textarea name="reponse" id="reponse" rows="8"><?= htmlspecialchars($ticket['reponse_admin'] ?? '') ?></textarea>
        
        <label for="statut">Changer le statut :</label>
        <select name="statut" id="statut">
            <option value="Ouvert" <?= $ticket['statut'] == 'Ouvert' ? 'selected' : '' ?>>Ouvert</option>
            <option value="Fermé" <?= $ticket['statut'] == 'Fermé' ? 'selected' : '' ?>>Fermé</option>
        </select>
        
        <button type="submit" name="submit_reply">Envoyer la réponse</button>
    </form>
</div>

</body>
</html>