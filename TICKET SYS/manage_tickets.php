<?php
// Fichier : manage_tickets.php (Version complète et corrigée)
session_start();
require_once 'config.php';

// Sécurité : Seul l'admin peut accéder
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Cette requête est correcte, elle récupère TOUS les tickets
$stmt = $pdo->prepare("
    SELECT tickets.*, utilisateurs.username 
    FROM tickets 
    JOIN utilisateurs ON tickets.id_utilisateur = utilisateurs.id 
    ORDER BY tickets.date_creation DESC
");
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// On récupère aussi les infos de l'admin pour le bandeau
$admin_stmt = $pdo->prepare("SELECT username, profile_pic FROM utilisateurs WHERE id = ?");
$admin_stmt->execute([$_SESSION['user_id']]);
$admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" type="image/png" href="../IMG/AYV RE.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Tickets</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

<!-- Bandeau de navigation cohérent -->
<div class="navbar">
    <a href="admin_dashboard.php"><img src="logo.png" alt="Logo"></a>
    <div class="nav-links">
        <a href="profile.php">
            <img src="<?= htmlspecialchars($admin['profile_pic']) ?>" class="avatar-xs" style="margin-right: 5px;">
            <?= htmlspecialchars($admin['username']) ?>
        </a>
        <a href="logout.php">Déconnexion</a>
    </div>
</div>

<div class="container">
    <div class="header">
        <h1>Gestion des Tickets</h1>
        <a href="admin_dashboard.php" class="btn btn-primary"><i class="fa-solid fa-arrow-left"></i> Retour au Hub</a>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Créé par</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <!-- Dans manage_tickets.php, remplacez la section <tbody> -->
<tbody>
    <?php if (empty($tickets)): ?>
        <tr>
            <td colspan="6" style="text-align: center;">Aucun ticket à afficher pour le moment.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($tickets as $ticket): ?>
            <tr>
                <td>#<?= $ticket['id'] ?></td>
                <td><?= htmlspecialchars($ticket['titre']) ?></td>
                <td><?= htmlspecialchars($ticket['username']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($ticket['date_creation'])) ?></td>
                <td>
                    <span class="status-badge status-<?= str_replace(' ', '-', strtolower($ticket['statut'])) ?>">
                        <?= htmlspecialchars($ticket['statut']) ?>
                    </span>
                </td>
                <td class="actions-cell">
                    <a href="reply_ticket.php?id=<?= $ticket['id'] ?>" class="btn-table-action">
                        <i class="fa-solid fa-eye"></i> Voir
                    </a>
                    <!-- NOUVEAU BOUTON SUPPRIMER AVEC CONFIRMATION -->
                    <a href="delete_ticket.php?id=<?= $ticket['id'] ?>" class="btn-table-action btn-danger" 
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer définitivement ce ticket ? Cette action est irréversible.');">
                        <i class="fa-solid fa-trash"></i> Supprimer
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
        </table>
    </div>
</div>
</body>
</html>