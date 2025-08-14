<?php
// Fichier : manage_users.php (Version Finale - Pro & Responsive)
session_start();
require_once 'config.php';

// Sécurité : Vérifie si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Récupère tous les utilisateurs pour les afficher
$stmt = $pdo->query("SELECT id, username, email, role, profile_image FROM utilisateurs ORDER BY id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$admin_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../IMG/AYV RE.png">
    <title>Gestion des Utilisateurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        :root {
            --background-image: url('../IMG/ATCMARP.PNG');
            --container-bg: rgba(28, 28, 35, 0.85);
            --card-bg: rgba(40, 40, 50, 0.7);
            --input-bg: rgba(10, 10, 15, 0.7);
            --border-color: rgba(255, 255, 255, 0.15);
            --text-color: #e0e0e0;
            --text-muted: #a0a0b0;
            --primary-color: #2ecc71; /* Vert */
            --promote-color: #3498db; /* Bleu */
            --demote-color: #f39c12; /* Orange */
            --danger-color: #e74c3c; /* Rouge */
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", sans-serif;
            background-image: var(--background-image);
            background-size: cover; background-position: center; background-attachment: fixed;
            color: var(--text-color); margin: 0; padding: 2rem;
            min-height: 100vh; box-sizing: border-box;
        }

        /* --- MENU DE NAVIGATION (IDENTIQUE AUX AUTRES PAGES) --- */
        .menu-toggle { position: fixed; top: 20px; right: 20px; background: var(--container-bg); border: 1px solid var(--border-color); color: var(--text-color); width: 50px; height: 50px; border-radius: 50%; font-size: 1.2rem; cursor: pointer; z-index: 1001; transition: all 0.3s ease; }
        .menu-toggle:hover { transform: scale(1.1); background-color: rgba(40,40,50,0.9); }
        .main-menu { position: fixed; top: 0; right: 0; width: 300px; height: 100%; background: rgba(30, 30, 40, 0.9); backdrop-filter: blur(10px); border-left: 1px solid var(--border-color); z-index: 1000; display: flex; flex-direction: column; padding: 80px 20px 20px; transform: translateX(100%); transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
        .main-menu.active { transform: translateX(0); }
        .main-menu a { color: var(--text-color); text-decoration: none; font-size: 1.2rem; padding: 1rem; border-radius: 8px; margin-bottom: 0.5rem; transition: background-color 0.2s, color 0.2s; display: flex; align-items: center; }
        .main-menu a:hover { background-color: rgba(255,255,255,0.1); color: white; }
        .main-menu a i { margin-right: 15px; width: 25px; text-align: center; }

        /* --- CONTENEUR PRINCIPAL --- */
        .page-container {
            width: 100%; max-width: 1100px;
            background: var(--container-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 12px; padding: 2rem;
            animation: fadeIn 0.6s ease-out forwards;
        }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .page-header h1 { font-size: 2rem; margin: 0; }
        .page-header .btn-back { background: var(--card-bg); color: var(--text-color); padding: 0.6rem 1rem; border-radius: 8px; text-decoration: none; transition: background-color 0.2s; }
        .page-header .btn-back:hover { background: rgba(50,50,60,0.9); }

        .message, .error { padding: 1rem; margin-bottom: 1rem; border-radius: 6px; border: 1px solid; }
        .message { color: var(--primary-color); background-color: rgba(46, 204, 113, 0.1); border-color: rgba(46, 204, 113, 0.3); }
        .error { color: var(--danger-color); background-color: rgba(231, 76, 60, 0.1); border-color: rgba(231, 76, 60, 0.3); }

        /* --- TABLEAU RESPONSIVE --- */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; }
        thead { border-bottom: 2px solid var(--border-color); }
        tbody tr { border-bottom: 1px solid var(--border-color); }
        tbody tr:last-child { border-bottom: none; }
        .avatar-sm { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        
        .role-badge { padding: 0.3rem 0.6rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .role-admin { background-color: rgba(231, 76, 60, 0.2); color: var(--danger-color); }
        .role-user { background-color: rgba(52, 152, 219, 0.2); color: var(--promote-color); }

        .actions-cell { display: flex; gap: 0.5rem; align-items: center; }
        .btn-action { display: inline-flex; justify-content: center; align-items: center; text-decoration: none; border: none; width: 38px; height: 38px; font-size: 1rem; border-radius: 8px; cursor: pointer; transition: all 0.2s ease; }
        .btn-action:hover { transform: translateY(-2px); }
        .btn-action.promote { background-color: var(--promote-color); color: white; }
        .btn-action.demote { background-color: var(--demote-color); color: white; }
        .btn-action.reset { background-color: var(--card-bg); color: var(--text-color); border: 1px solid var(--border-color); }
        .btn-action.delete { background-color: var(--danger-color); color: white; }

        /* --- FENÊTRE MODALE --- */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(5px); display: none; justify-content: center; align-items: center; z-index: 2000; }
        .modal-overlay.active { display: flex; }
        .modal-content { background: var(--container-bg); padding: 2rem; border-radius: 12px; width: 90%; max-width: 450px; animation: fadeIn 0.3s; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .modal-close-btn { background: none; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer; }
        .input-group { margin-bottom: 1.5rem; }
        .input-group label { display: block; margin-bottom: 0.5rem; }
        .input-group input { width: 100%; box-sizing: border-box; /* ... styles from previous pages ... */ }
        .modal-footer { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem; }
        .btn { padding: 0.6rem 1.2rem; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; }
        .btn-secondary { background: var(--card-bg); color: var(--text-color); }
        .btn-primary { background: var(--primary-color); color: white; }


        /* --- TRANSFORMATION EN CARTES POUR MOBILE --- */
        @media (max-width: 768px) {
            body { padding: 1rem; align-items: flex-start; }
            .page-container { margin-top: 4rem; padding: 1rem; }
            .page-header h1 { font-size: 1.5rem; }
            .main-menu { width: 100%; }

            thead { display: none; }
            tbody, tr, td { display: block; width: 100%; box-sizing: border-box; }
            tr { background: var(--card-bg); margin-bottom: 1rem; border-radius: 10px; padding: 1rem; border: 1px solid var(--border-color); }
            td { display: flex; justify-content: space-between; align-items: center; padding: 0.8rem 0; border-bottom: 1px solid var(--border-color); }
            td:last-child { border-bottom: none; }
            td::before { content: attr(data-label); font-weight: 600; color: var(--text-muted); }
            .avatar-cell { display: none; } /* On peut cacher l'avatar simple pour l'intégrer autrement */
            .user-card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 1rem; }
            .actions-cell { justify-content: center; padding-top: 1rem; }
        }
    </style>
</head>
<body>
    
    <!-- Menu Hamburger -->
    <button class="menu-toggle" id="menu-toggle" aria-label="Ouvrir le menu"><i class="fa-solid fa-bars"></i></button>
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

    <div class="page-container">
        <div class="page-header">
            <h1>Gestion Utilisateurs</h1>
            <a href="admin_dashboard.php" class="btn-back">&larr; Retour</a>
        </div>

        <?php if(isset($_GET['success'])): ?><p class="message">Action effectuée avec succès !</p><?php endif; ?>
        <?php if(isset($_GET['error'])): ?><p class="error">Une erreur est survenue : <?= htmlspecialchars($_GET['error']) ?></p><?php endif; ?>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <!-- Pour l'affichage mobile, on combine Avatar et Username -->
                            <td data-label="Utilisateur">
                                <div class="user-card-header">
                                    <img src="<?= htmlspecialchars(!empty($user['profile_image']) ? $user['profile_image'] : 'img/profil.png') ?>" alt="Avatar" class="avatar-sm">
                                    <span><?= htmlspecialchars($user['username']) ?></span>
                                </div>
                            </td>
                            <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
                            <td data-label="Rôle">
                                <span class="role-badge role-<?= strtolower(htmlspecialchars($user['role'])) === 'admin' ? 'admin' : 'user' ?>">
                                    <?= htmlspecialchars($user['role']) ?>
                                </span>
                            </td>
                            <td data-label="Actions">
                                <div class="actions-cell">
                                    <?php if ($user['id'] != $admin_id): ?>
                                        <?php if ($user['role'] === 'user'): ?>
                                            <a href="process_user_action.php?action=toggle_role&id=<?= $user['id'] ?>" class="btn-action promote" title="Promouvoir en Admin"><i class="fa-solid fa-user-shield"></i></a>
                                        <?php else: ?>
                                            <a href="process_user_action.php?action=toggle_role&id=<?= $user['id'] ?>" class="btn-action demote" title="Rétrograder en Utilisateur"><i class="fa-solid fa-user-xmark"></i></a>
                                        <?php endif; ?>

                                        <button onclick="openPasswordModal(<?= $user['id'] ?>, '<?= htmlspecialchars(addslashes($user['username'])) ?>')" class="btn-action reset" title="Réinitialiser le mot de passe"><i class="fa-solid fa-key"></i></button>
                                        
                                        <a href="process_user_action.php?action=delete&id=<?= $user['id'] ?>" class="btn-action delete" title="Supprimer l'utilisateur" onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ? Cette action est définitive.');"><i class="fa-solid fa-trash"></i></a>
                                    <?php else: ?>
                                        <small>Votre compte</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fenêtre Modale -->
    <div id="passwordModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Réinitialiser le mot de passe</h3>
                <button onclick="closePasswordModal()" class="modal-close-btn">&times;</button>
            </div>
            <form action="process_user_action.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="user_id" id="modalUserId">
                    <div class="input-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" name="new_password" id="new_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closePasswordModal()" class="btn btn-secondary">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- GESTION DU MENU (SÉCURISÉ) ---
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

    // --- GESTION DE LA MODALE ---
    const modal = document.getElementById('passwordModal');

    function openPasswordModal(userId, username) {
        if (modal) {
            modal.querySelector('#modalUserId').value = userId;
            modal.querySelector('#modalTitle').innerText = `Nouveau mot de passe pour "${username}"`;
            modal.classList.add('active');
        }
    }

    function closePasswordModal() {
        if (modal) {
            modal.classList.remove('active');
        }
    }

    // Fermer la modale si on clique en dehors
    window.addEventListener('click', (event) => {
        if (event.target == modal) {
            closePasswordModal();
        }
    });
    </script>
</body>
</html>