<?php
// Fichier : manage_users.php (Version avec contrôle total)
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Correction du nom de la colonne pour correspondre aux autres fichiers : profile_pic
$stmt = $pdo->query("SELECT id, username, email, role, profile_image FROM utilisateurs ORDER BY id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$admin_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Gestion des Utilisateurs</h1>
        <a href="admin_dashboard.php" class="btn btn-primary">&larr; Retour au Hub</a>
    </div>

    <!-- Affichage des messages de succès/erreur -->
    <?php if(isset($_GET['success'])): ?><p class="message">Action effectuée avec succès !</p><?php endif; ?>
    <?php if(isset($_GET['error'])): ?><p class="error">Une erreur est survenue ou l'action est interdite.</p><?php endif; ?>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Avatar</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th style="width: 280px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Avatar" class="avatar-sm"></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <span class="status-badge status-<?= strtolower($user['role']) ?>">
                                <?= htmlspecialchars($user['role']) ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <?php if ($user['id'] != $admin_id): // On ne peut pas modifier son propre compte ?>
                                <!-- Bouton pour changer le rôle -->
                                <?php if ($user['role'] === 'utilisateur'): ?>
                                    <a href="process_user_action.php?action=toggle_role&id=<?= $user['id'] ?>" class="btn-table-action btn-promote"><i class="fa-solid fa-user-shield"></i> Promouvoir</a>
                                <?php else: ?>
                                    <a href="process_user_action.php?action=toggle_role&id=<?= $user['id'] ?>" class="btn-table-action btn-demote"><i class="fa-solid fa-user-xmark"></i> Rétrograder</a>
                                <?php endif; ?>

                                <!-- Bouton pour réinitialiser le mdp -->
                                <button onclick="openPasswordModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')" class="btn-table-action btn-reset"><i class="fa-solid fa-key"></i> Mdp</button>
                                
                                <!-- Bouton pour supprimer -->
                                <a href="process_user_action.php?action=delete&id=<?= $user['id'] ?>" class="btn-table-action btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ? Cette action est définitive.');"><i class="fa-solid fa-trash"></i></a>
                            <?php else: ?>
                                <small>Votre compte (actions désactivées)</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Fenêtre Modale pour le mot de passe (cachée par défaut) -->
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
    function openPasswordModal(userId, username) {
        document.getElementById('modalUserId').value = userId;
        document.getElementById('modalTitle').innerText = `Réinitialiser le mot de passe pour "${username}"`;
        document.getElementById('passwordModal').style.display = 'flex';
    }

    function closePasswordModal() {
        document.getElementById('passwordModal').style.display = 'none';
    }

    // Fermer la modale si on clique en dehors
    window.onclick = function(event) {
        const modal = document.getElementById('passwordModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
</body>
</html>