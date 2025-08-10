<?php
// Fichier : admin_dashboard.php (Le nouveau Hub)
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Correction : utiliser 'profile_image' au lieu de 'profile_pic'
$stmt = $pdo->prepare("SELECT username, profile_image FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
?>


<head>
    <link rel="stylesheet" href="admin_dsh.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: url('img/ATCMARP.png') no-repeat center center fixed;
            background-size: cover;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="admin_dashboard.php"><img src="logo.png" alt="Logo"></a>
        <div class="nav-links">
            <a href="profile.php">
                <img src="<?= htmlspecialchars($admin['profile_image']) ?>" class="avatar-xs" style="margin-right: 5px;">
                <?= htmlspecialchars($admin['username']) ?>
            </a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </div>
    <div class="container">
        <div class="header">
            <h1>Centre de Contrôle</h1>
        </div>
        <div class="admin-hub">
            <a href="manage_tickets.php" class="hub-card">
                <i class="fa-solid fa-ticket-alt hub-icon"></i>
                <h3>Gérer les Tickets</h3>
                <p>Voir, répondre et fermer les tickets.</p>
            </a>
            <a href="manage_users.php" class="hub-card">
                <i class="fa-solid fa-users-cog hub-icon"></i>
                <h3>Gérer les Utilisateurs</h3>
                <p>Consulter la liste des inscrits.</p>
            </a>
            <a href="post_media.php" class="hub-card">
                <i class="fa-solid fa-pen-to-square hub-icon"></i>
                <h3>Poster un Média</h3>
                <p>Créer de nouvelles publications.</p>
            </a>
            <a href="media.php" class="hub-card">
                <i class="fa-solid fa-photo-film hub-icon"></i>
                <h3>Voir les Médias</h3>
                <p>Consulter la galerie des posts.</p>
            </a>
        </div>
    </div>
</body>
</html>