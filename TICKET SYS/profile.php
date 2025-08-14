<?php
// Fichier : profile.php (Version Finale - Pro & Autonome)
session_start();
require_once 'config.php';

// Sécurité : si pas connecté, redirection
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// === Logique de mise à jour du profil (IDENTIQUE À VOTRE CODE) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Mise à jour informations générales ---
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        
        // Validation basique
        if (empty($username)) {
            $error = "Le nom d'utilisateur ne peut pas être vide.";
        } else {
            // Vérifier si username déjà pris (par un autre utilisateur)
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE username = ? AND id != ?");
            $stmt->execute([$username, $user_id]);
            if ($stmt->fetch()) {
                $error = "Ce nom d'utilisateur est déjà utilisé.";
            } else {
                $image_path = $_POST['current_image']; // Garde l'ancienne image par défaut

                // Upload de la nouvelle image si elle existe
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

                    // Contrôles de sécurité
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (in_array(mime_content_type($_FILES['profile_image']['tmp_name']), $allowed_types)) {
                        $file_name = 'profile-' . $user_id . '-' . time() . '.' . pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                        $target_file = $upload_dir . $file_name;

                        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                            $image_path = $target_file;
                        } else {
                            $error = "Erreur lors de l'enregistrement de l'image.";
                        }
                    } else {
                        $error = "Format d'image non valide.";
                    }
                }

                if (empty($error)) {
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET username = ?, profile_image = ? WHERE id = ?");
                    if ($stmt->execute([$username, $image_path, $user_id])) {
                         $message = "Profil mis à jour avec succès !";
                         // Mettre à jour la session pour que le changement soit visible partout
                         $_SESSION['user_name'] = $username; 
                    } else {
                         $error = "Une erreur est survenue lors de la mise à jour.";
                    }
                }
            }
        }
    }

    // --- Mise à jour du mot de passe ---
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
             $error = "Tous les champs de mot de passe sont requis.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Les nouveaux mots de passe ne correspondent pas.";
        } else {
            $stmt = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = ?");
            $stmt->execute([$user_id]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user_data && password_verify($current_password, $user_data['mot_de_passe'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
                $message = "Mot de passe changé avec succès !";
            } else {
                $error = "Le mot de passe actuel est incorrect.";
            }
        }
    }
}

// Récupérer les infos à jour de l'utilisateur pour l'affichage
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirection si l'utilisateur n'existe plus
if (!$user) {
    session_destroy(); header('Location: login.php'); exit();
}

// Définir une image par défaut si aucune n'est définie
$user['profile_image'] = !empty($user['profile_image']) ? $user['profile_image'] : 'img/profil.png';
$dashboard_url = ($user['role'] === 'admin') ? 'admin_dashboard.php' : 'user_dashboard.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../IMG/AYV RE.png">
    <title>Mon Profil</title>
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
            --primary-color: #2ecc71; /* Vert, comme sur les autres pages */
            --danger-color: #e74c3c;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", sans-serif;
            background-image: var(--background-image);
            background-size: cover; background-position: center; background-attachment: fixed;
            color: var(--text-color); margin: 0; padding: 2rem;
            display: flex; justify-content: center; align-items: flex-start;
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
            width: 100%; max-width: 900px;
            animation: fadeIn 0.6s ease-out forwards;
        }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .page-header h1 { font-size: 2rem; margin: 0; }
        .page-header .btn-back { background: var(--card-bg); color: var(--text-color); padding: 0.6rem 1rem; border-radius: 8px; text-decoration: none; transition: background-color 0.2s; }
        .page-header .btn-back:hover { background: var(--container-bg); }

        .message, .error { padding: 1rem; margin-bottom: 1rem; border-radius: 6px; border: 1px solid; }
        .message { color: var(--primary-color); background-color: rgba(46, 204, 113, 0.1); border-color: rgba(46, 204, 113, 0.3); }
        .error { color: var(--danger-color); background-color: rgba(231, 76, 60, 0.1); border-color: rgba(231, 76, 60, 0.3); }

        /* --- GRILLE DES FORMULAIRES --- */
        .profile-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        .form-card { background: var(--container-bg); backdrop-filter: blur(10px); padding: 2rem; border-radius: 12px; border: 1px solid var(--border-color); }
        .form-card h2 { margin-top: 0; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1.5rem; }
        
        .profile-pic-container { text-align: center; margin-bottom: 1.5rem; }
        .avatar-label { cursor: pointer; display: inline-block; position: relative; }
        .avatar-label .avatar-lg { width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 3px solid var(--border-color); transition: border-color 0.3s; }
        .avatar-label .edit-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 50%; background: rgba(0,0,0,0.5); color: white; display: flex; justify-content: center; align-items: center; font-size: 1.5rem; opacity: 0; transition: opacity 0.3s; }
        .avatar-label:hover .edit-overlay { opacity: 1; }
        .avatar-label:hover .avatar-lg { border-color: var(--primary-color); }
        #profile_image { display: none; }

        .input-group { margin-bottom: 1.5rem; }
        .input-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .input-group input { width: 100%; background-color: var(--input-bg); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.8rem 1rem; color: var(--text-color); font-size: 1rem; box-sizing: border-box; transition: all 0.2s ease; }
        .input-group input:disabled { background: rgba(0,0,0,0.2); color: var(--text-muted); cursor: not-allowed; }
        .input-group input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.3); }

        .btn { width: 100%; padding: 0.8rem; border: none; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.2s ease; }
        .btn-primary { background: var(--primary-color); color: white; }
        .btn-primary:hover { background: #27ae60; }
        .btn-danger { background: var(--danger-color); color: white; }
        .btn-danger:hover { background: #c0392b; }

        @media (max-width: 800px) {
            body { padding: 1rem; align-items: flex-start; }
            .profile-grid { grid-template-columns: 1fr; }
            .page-container { margin-top: 4rem; }
            .main-menu { width: 100%; }
        }
    </style>
</head>
<body>

    <!-- Menu Hamburger -->
    <button class="menu-toggle" id="menu-toggle" aria-label="Ouvrir le menu"><i class="fa-solid fa-bars"></i></button>
    <nav class="main-menu" id="main-menu">
        <a href="<?= htmlspecialchars($dashboard_url) ?>"><i class="fa-solid fa-chart-line"></i> Tableau de bord</a>
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
            <h1>Mon Profil</h1>
        </div>

        <?php if ($message): ?><p class="message"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($message) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?></p><?php endif; ?>

        <div class="profile-grid">
            <!-- CARTE 1: INFORMATIONS GÉNÉRALES -->
            <div class="form-card">
                <h2>Informations</h2>
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="current_image" value="<?= htmlspecialchars($user['profile_image']) ?>">
                    
                    <div class="profile-pic-container">
                        <label for="profile_image" class="avatar-label">
                            <img src="<?= htmlspecialchars($user['profile_image']) ?>?v=<?= time() ?>" alt="Avatar" class="avatar-lg" id="avatar-preview">
                            <div class="edit-overlay"><i class="fa-solid fa-camera"></i></div>
                        </label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*">
                    </div>
                    
                    <div class="input-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="email">Adresse Email</label>
                        <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">Mettre à jour le profil</button>
                </form>
            </div>

            <!-- CARTE 2: SÉCURITÉ -->
            <div class="form-card">
                <h2>Sécurité</h2>
                <form action="profile.php" method="POST">
                    <div class="input-group">
                        <label for="current_password">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="input-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="input-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-danger">Changer le mot de passe</button>
                </form>
            </div>
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

        // --- GESTION DE LA PRÉVISUALISATION DE L'AVATAR ---
        const imageInput = document.getElementById('profile_image');
        const preview = document.getElementById('avatar-preview');
        if (imageInput && preview) {
            imageInput.addEventListener('change', function(event) {
                if (event.target.files && event.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    }
                    reader.readAsDataURL(event.target.files[0]);
                }
            });
        }
    });
    </script>
</body>
</html>