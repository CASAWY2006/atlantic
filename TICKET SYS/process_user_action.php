<?php
// Fichier : process_user_action.php
session_start();
require_once 'config.php';

// Sécurité : Seul un admin peut effectuer ces actions
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    exit("Accès non autorisé.");
}

$admin_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = $_POST['user_id'] ?? $_GET['id'] ?? 0;

// Vérification de sécurité cruciale : l'admin ne peut pas se modifier lui-même
if ($user_id == $admin_id) {
    header("Location: manage_users.php?error=self_action");
    exit("Vous не pouvez pas modifier votre propre compte.");
}

if ($user_id > 0) {
    switch ($action) {
        case 'toggle_role':
            // Inverser le rôle de l'utilisateur
            $stmt = $pdo->prepare("SELECT role FROM utilisateurs WHERE id = ?");
            $stmt->execute([$user_id]);
            $current_role = $stmt->fetchColumn();
            
            $new_role = ($current_role === 'admin') ? 'utilisateur' : 'admin';
            
            $update_stmt = $pdo->prepare("UPDATE utilisateurs SET role = ? WHERE id = ?");
            $update_stmt->execute([$new_role, $user_id]);
            header("Location: manage_users.php?success=role_changed");
            break;

        case 'reset_password':
            // Réinitialiser le mot de passe depuis le formulaire modal
            $new_password = $_POST['new_password'];
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
                $update_stmt->execute([$hashed_password, $user_id]);
                header("Location: manage_users.php?success=password_reset");
            } else {
                header("Location: manage_users.php?error=empty_password");
            }
            break;

        case 'delete':
            // Supprimer définitivement l'utilisateur
            $delete_stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
            $delete_stmt->execute([$user_id]);
            header("Location: manage_users.php?success=user_deleted");
            break;
    }
}
exit();