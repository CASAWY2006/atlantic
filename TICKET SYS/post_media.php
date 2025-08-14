<?php
// Fichier : post_media.php (Version Finale - Pro & Animée)
session_start();
require_once 'config.php';

// Sécurité : accès réservé à l'administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$id_admin = $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // --- Récupération des données ---
        $titre = trim($_POST['titre'] ?? '');
        $text_content = trim($_POST['text_content'] ?? '');
        $image_path = null;
        $video_path = null;

        // --- Validation du contenu ---
        if (empty($titre) && empty($text_content) && empty($_FILES['media']['tmp_name'])) {
            throw new Exception("Un post doit contenir au moins un titre, du texte ou un média.");
        }

        // --- Détection automatique du premier lien dans le texte ---
        preg_match('/(https?:\/\/[^\s"\'<>]+)/', $text_content, $matches);
        $link_url = $matches[0] ?? null;

        // --- Traitement du téléversement de Média (Image OU Vidéo) ---
        if (isset($_FILES['media']) && $_FILES['media']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            // Sécurité : Vérifier la taille du fichier (ex: 50MB max)
            $max_size = 50 * 1024 * 1024; // 50 MB
            if ($_FILES['media']['size'] > $max_size) {
                throw new Exception("Le fichier est trop volumineux (Max 50MB).");
            }

            $file_type = mime_content_type($_FILES['media']['tmp_name']);
            $file_extension = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
            $file_name = 'media-' . uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;

            // Gérer les IMAGES
            if (str_starts_with($file_type, 'image/')) {
                if (move_uploaded_file($_FILES['media']['tmp_name'], $target_file)) {
                    $image_path = $target_file;
                } else { throw new Exception("Erreur lors de l'enregistrement de l'image."); }
            
            // Gérer les VIDÉOS
            } elseif (str_starts_with($file_type, 'video/')) {
                // Pour que cela fonctionne, votre serveur doit autoriser l'upload de fichiers volumineux.
                // Modifiez `upload_max_filesize` et `post_max_size` dans votre fichier php.ini si nécessaire.
                if (move_uploaded_file($_FILES['media']['tmp_name'], $target_file)) {
                    // On stocke le chemin de la vidéo dans la colonne `video_embed`
                    $video_path = $target_file;
                } else { throw new Exception("Erreur lors de l'enregistrement de la vidéo."); }
            
            } else {
                throw new Exception("Format de fichier non supporté. Utilisez des images (jpg, png) ou des vidéos (mp4, webm).");
            }
        }
        
        // --- Insertion en base de données ---
        // Note : On utilise la colonne 'video_embed' pour stocker le chemin du FICHIER vidéo.
        $stmt = $pdo->prepare(
            "INSERT INTO media_posts (id_admin, titre, text_content, image_path, video_embed, link_url) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$id_admin, $titre, $text_content, $image_path, $video_path, $link_url]);
        
        $message = "Publication créée avec succès !";

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../IMG/AYV RE.png">
    <title>Créer une Publication</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        :root {
            --background-image: url('https://i.imgur.com/your-background-image.jpg'); /* Mettez l'URL de votre fond d'écran ici */
            --container-bg: rgba(28, 28, 35, 0.85);
            --input-bg: rgba(10, 10, 15, 0.7);
            --border-color: rgba(255, 255, 255, 0.15);
            --text-color: #e0e0e0;
            --text-muted: #8b949e;
            --publish-bg: #2ecc71;
            --publish-hover: #28b463;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes menuIn { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }

        body {
            margin: 0; padding: 0; min-height: 100vh;
            background-color: #020220;
            background-image: url('../IMG/ATCMARP.PNG');
            background-position: center -240px;
            background-repeat: no-repeat;
            background-size: 100% auto;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
        }

        /* --- MENU DE NAVIGATION --- */
        .menu-toggle {
            position: fixed; top: 20px; right: 20px;
            background: var(--container-bg); border: 1px solid var(--border-color);
            color: var(--text-color); width: 50px; height: 50px;
            border-radius: 50%; font-size: 1.2rem; cursor: pointer;
            z-index: 1001; transition: transform 0.3s ease;
        }
        .menu-toggle:hover { transform: scale(1.1); }
        
        .main-menu {
            position: fixed; top: 0; right: 0;
            width: 300px; height: 100%;
            background: var(--container-bg);
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
        }
        .main-menu a:hover { background-color: rgba(255,255,255,0.1); color: white; }
        .main-menu a i { margin-right: 15px; width: 20px; }


        .composer-container {
            width: 100%; max-width: 650px;
            background-color: var(--container-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 12px; padding: 1.5rem 2rem;
            animation: fadeIn 0.6s ease-out forwards;
        }

        .composer-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 1rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); }
        .composer-header h1 { font-size: 1.5rem; margin: 0; }
        
        .message, .error { padding: 1rem; margin-bottom: 1rem; border-radius: 6px; border: 1px solid; }
        /* styles pour .message et .error ... */

        .input-group { margin-bottom: 1.5rem; }
        .input-group input[type="text"], .input-group textarea {
            width: 100%; background-color: var(--input-bg);
            border: 1px solid var(--border-color); border-radius: 8px;
            padding: 0.8rem 1rem; color: var(--text-color); font-size: 1rem;
            box-sizing: border-box; transition: all 0.2s ease;
        }
        .input-group input[type="text"]::placeholder, .input-group textarea::placeholder { color: var(--text-muted); }
        .input-group input[type="text"]:focus, .input-group textarea:focus {
            outline: none; border-color: var(--publish-bg);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.3);
        }
        
        .composer-toolbar { display: flex; align-items: center; gap: 1rem; margin-top: 1rem; }
        .toolbar-label { font-weight: 500; color: var(--text-muted); }
        .toolbar-btn { background: none; border: 1px solid var(--border-color); color: var(--text-muted); font-size: 1rem; cursor: pointer; padding: 0.5rem 1rem; border-radius: 8px; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease; }
        .toolbar-btn:hover { border-color: var(--text-color); color: var(--text-color); }
        .media-upload-input { display: none; }

        /* --- Prévisualisation Média --- */
        #media-preview-container { display: none; position: relative; margin-top: 1rem; }
        #media-preview-container img, #media-preview-container video { max-width: 100%; border-radius: 8px; }
        #remove-media-btn {
            position: absolute; top: 0.5rem; right: 0.5rem;
            background-color: rgba(0,0,0,0.7); color: white; border: none;
            border-radius: 50%; width: 30px; height: 30px; font-size: 1rem; cursor: pointer;
            display: flex; align-items: center; justify-content: center; transition: transform 0.2s ease;
        }
        #remove-media-btn:hover { transform: scale(1.1); }
        
        .btn-publish {
            width: 100%; background-color: var(--publish-bg); color: white;
            border: none; padding: 1rem; font-size: 1.1rem; font-weight: 600;
            border-radius: 8px; cursor: pointer; margin-top: 2rem;
            transition: background-color 0.2s, transform 0.2s;
        }
        .btn-publish:hover { background-color: var(--publish-hover); transform: translateY(-2px); }

        /* --- Responsive pour Téléphone --- */
        @media (max-width: 768px) {
            body { padding: 1rem; align-items: flex-start; }
            .composer-container { padding: 1rem 1.5rem; }
            .composer-header h1 { font-size: 1.2rem; }
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

    <div class="composer-container">
        <div class="composer-header">
            <h1>Créer une Publication</h1>
        </div>
        
        <?php if ($message): ?><p class="message"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($message) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?></p><?php endif; ?>

        <form action="post_media.php" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <input type="text" id="titre" name="titre" placeholder="Titre de votre publication (optionnel)">
            </div>
            <div class="input-group">
                <textarea id="text_content" name="text_content" rows="6" placeholder="Exprimez-vous..."></textarea>
            </div>

            <div id="media-preview-container">
                <img id="image-preview" src="#" alt="Aperçu de l'image">
                <video id="video-preview" controls muted loop playsinline></video>
                <button type="button" id="remove-media-btn" title="Retirer le média">&times;</button>
            </div>

            <div class="composer-toolbar">
                <label for="media-upload" class="toolbar-btn">
                    <i class="fa-solid fa-photo-film"></i>
                    <span>Ajouter Photo/Vidéo</span>
                </label>
                <input type="file" id="media-upload" name="media" class="media-upload-input" accept="image/*,video/mp4,video/webm">
            </div>

            <button type="submit" class="btn-publish"><i class="fa-solid fa-paper-plane"></i> Publier</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- GESTION DU MENU ---
            const menuToggle = document.getElementById('menu-toggle');
            const mainMenu = document.getElementById('main-menu');
            const menuIcon = menuToggle.querySelector('i');

            menuToggle.addEventListener('click', () => {
                mainMenu.classList.toggle('active');
                if (mainMenu.classList.contains('active')) {
                    menuIcon.classList.replace('fa-bars', 'fa-times');
                } else {
                    menuIcon.classList.replace('fa-times', 'fa-bars');
                }
            });

            // --- GESTION DE LA PRÉVISUALISATION MÉDIA ---
            const mediaUpload = document.getElementById('media-upload');
            const previewContainer = document.getElementById('media-preview-container');
            const imagePreview = document.getElementById('image-preview');
            const videoPreview = document.getElementById('video-preview');
            const removeMediaBtn = document.getElementById('remove-media-btn');

            mediaUpload.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;

                const fileURL = URL.createObjectURL(file);

                // Cacher les deux au cas où avant d'afficher le bon
                imagePreview.style.display = 'none';
                videoPreview.style.display = 'none';

                if (file.type.startsWith('image/')) {
                    imagePreview.src = fileURL;
                    imagePreview.style.display = 'block';
                } else if (file.type.startsWith('video/')) {
                    videoPreview.src = fileURL;
                    videoPreview.style.display = 'block';
                    videoPreview.play();
                }

                previewContainer.style.display = 'block';
            });

            removeMediaBtn.addEventListener('click', () => {
                mediaUpload.value = ''; // Indispensable pour pouvoir re-sélectionner le même fichier
                previewContainer.style.display = 'none';
                imagePreview.src = '#';
                videoPreview.src = '#';
            });
        });
    </script>
</body>
</html>