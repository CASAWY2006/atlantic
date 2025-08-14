<?php
// Fichier : admin_dashboard.php (Version Professionnelle et Autonome)
session_start();
require_once 'config.php';

// Sécurité : Vérifie si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Récupère les informations de l'administrateur pour l'affichage
$stmt = $pdo->prepare("SELECT username, profile_image FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Au cas où l'utilisateur serait supprimé de la BDD mais sa session existerait toujours
if (!$admin) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../IMG/AYV RE.png">
    <title>Tableau de Bord Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        :root {
            --background-image: url('../IMG/ATCMARP.PNG');
            --container-bg: rgba(28, 28, 35, 0.85);
            --card-bg: rgba(40, 40, 50, 0.7);
            --card-hover-bg: rgba(50, 50, 60, 0.9);
            --border-color: rgba(255, 255, 255, 0.15);
            --text-color: #e0e0e0;
            --text-muted: #a0a0b0;
            --icon-color: #2ecc71;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", sans-serif;
            background-image: var(--background-image);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: var(--text-color);
            margin: 0;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
        }

        /* --- MENU DE NAVIGATION --- */
        .menu-toggle {
            position: fixed; top: 20px; right: 20px;
            background: var(--container-bg); border: 1px solid var(--border-color);
            color: var(--text-color); width: 50px; height: 50px;
            border-radius: 50%; font-size: 1.2rem; cursor: pointer;
            z-index: 1001; transition: all 0.3s ease;
        }
        .menu-toggle:hover { transform: scale(1.1); background-color: rgba(40,40,50,0.9); }
        
        .main-menu {
            position: fixed; top: 0; right: 0;
            width: 300px; height: 100%;
            background: rgba(30, 30, 40, 0.9);
            backdrop-filter: blur(10px);
            border-left: 1px solid var(--border-color);
            z-index: 1000;
            display: flex; flex-direction: column;
            padding: 80px 20px 20px;
            transform: translateX(100%);
            transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .main-menu.active { transform: translateX(0); }
        .main-menu a {
            color: var(--text-color); text-decoration: none; font-size: 1.2rem;
            padding: 1rem; border-radius: 8px; margin-bottom: 0.5rem;
            transition: background-color 0.2s, color 0.2s;
            display: flex; align-items: center;
        }
        .main-menu a:hover { background-color: rgba(255,255,255,0.1); color: white; }
        .main-menu a i { margin-right: 15px; width: 25px; text-align: center; }

        /* --- CONTENEUR PRINCIPAL DU DASHBOARD --- */
        .dashboard-container {
            width: 100%; max-width: 900px;
            background-color: var(--container-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            animation: fadeIn 0.6s ease-out forwards;
        }

        .dashboard-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 2rem;
        }
        .dashboard-header .avatar {
            width: 60px; height: 60px;
            border-radius: 50%; object-fit: cover;
            border: 2px solid var(--icon-color);
        }
        .dashboard-header h1 {
            font-size: 1.8rem; margin: 0;
        }
        .dashboard-header h1 span {
            font-size: 1rem; font-weight: 400;
            color: var(--text-muted); display: block;
        }
        
        /* --- GRILLE DES CARTES --- */
        .admin-hub {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .hub-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1.5rem;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .hub-card:hover {
            transform: translateY(-5px);
            background: var(--card-hover-bg);
            border-color: var(--icon-color);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .hub-icon {
            font-size: 2.5rem;
            color: var(--icon-color);
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }
        .hub-card:hover .hub-icon {
            transform: scale(1.1);
        }
        .hub-card h3 {
            font-size: 1.2rem;
            margin: 0 0 0.5rem 0;
        }
        .hub-card p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.4;
        }

        /* --- RESPONSIVE POUR TÉLÉPHONE --- */
        @media (max-width: 768px) {
            body { padding: 1rem; }
            .dashboard-container { padding: 1.5rem; }
            .dashboard-header { flex-direction: column; text-align: center; }
            .admin-hub { grid-template-columns: 1fr; }
            .main-menu { width: 100%; }
        }
    </style>
</head>
<body>

    <!-- Menu Hamburger -->
    <button class="menu-toggle" id="menu-toggle" aria-label="Ouvrir le menu">
        <i class="fa-solid fa-bars"></i>
    </button>
    <nav class="main-menu" id="main-menu">
        <a href="admin_dashboard.php"><i class="fa-solid fa-house"></i> Accueil</a>
        <a href="manage_tickets.php"><i class="fa-solid fa-ticket-alt"></i> Gérer les Tickets</a>
        <a href="manage_users.php"><i class="fa-solid fa-users-cog"></i> Gérer les Utilisateurs</a>
        <a href="post_media.php"><i class="fa-solid fa-pen-to-square"></i> Créer une Publication</a>
        <a href="media.php"><i class="fa-solid fa-photo-film"></i> Consulter le Fil</a>
        <a href="profile.php"><i class="fa-solid fa-user"></i> Mon Profil</a>
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a> 
        <a href="../"><i class="fa-solid fa-house"></i> ATC</a>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <img src="<?= htmlspecialchars($admin['profile_image']) ?>" alt="Avatar" class="avatar">
            <h1>
                <span>Bienvenue,</span>
                <?= htmlspecialchars($admin['username']) ?>
            </h1>
        </div>
        <div class="admin-hub">
            <a href="manage_tickets.php" class="hub-card">
                <i class="fa-solid fa-ticket-alt hub-icon"></i>
                <h3>Gérer les Tickets</h3>
                <p>Voir, répondre et fermer les tickets des utilisateurs.</p>
            </a>
            <a href="manage_users.php" class="hub-card">
                <i class="fa-solid fa-users-cog hub-icon"></i>
                <h3>Gérer les Utilisateurs</h3>
                <p>Consulter et gérer la liste des membres inscrits.</p>
            </a>
            <a href="post_media.php" class="hub-card">
                <i class="fa-solid fa-pen-to-square hub-icon"></i>
                <h3>Créer une Publication</h3>
                <p>Rédiger et poster de nouvelles annonces ou médias.</p>
            </a>
            <a href="media.php" class="hub-card">
                <i class="fa-solid fa-photo-film hub-icon"></i>
                <h3>Consulter le Fil</h3>
                <p>Voir la galerie de toutes les publications.</p>
            </a>
        </div>
    </div>

    <script>
        // Le JavaScript pour le menu, autonome et sécurisé
        document.addEventListener('DOMContentLoaded', () => {
            const menuToggle = document.getElementById('menu-toggle');
            const mainMenu = document.getElementById('main-menu');

            if (menuToggle && mainMenu) {
                const menuIcon = menuToggle.querySelector('i');
                menuToggle.addEventListener('click', () => {
                    const isActive = mainMenu.classList.toggle('active');
                    menuIcon.className = isActive ? 'fa-solid fa-times' : 'fa-solid fa-bars';
                });
            }
        });
    </script>
</body>
</html>