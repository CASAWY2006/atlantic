<?php
// Fichier : post_media.php (Nouvelle Version Intelligente)
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
    // On récupère toutes les données possibles du formulaire
    $titre = trim($_POST['titre']);
    $text_content = trim($_POST['text_content']);
    $video_embed = trim($_POST['video_embed']);
    $link_url = trim($_POST['link_url']);
    $image_path = null; // Par défaut, il n'y a pas d'image

    try {
        // Un post doit avoir au moins un titre ou un contenu texte pour être valide
        if (empty($titre) && empty($text_content)) {
            throw new Exception("Un titre ou un texte est requis pour créer un post.");
        }

        // Traitement de l'image si elle est envoyée
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir = 'uploads/';
            // On vérifie que le type de fichier est une image (sécurité de base)
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($_FILES['image']['type'], $allowed_types)) {
                throw new Exception("Format d'image non valide. Utilisez JPG, PNG, GIF ou WEBP.");
            }
            $file_name = uniqid('post-') . '-' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = $target_file; // On enregistre le chemin de l'image
            } else {
                throw new Exception("Erreur lors de l'upload de l'image.");
            }
        }
        
        // Nouvelle requête INSERT adaptée à la nouvelle structure de la table
        $stmt = $pdo->prepare(
            "INSERT INTO media_posts (id_admin, titre, text_content, image_path, video_embed, link_url) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$id_admin, $titre, $text_content, $image_path, $video_embed, $link_url]);
        
        $message = "Média posté avec succès !";

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
    <title>Créer une Publication</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
    <div class="container" style="max-width: 800px;">
        <div class="header">
            <h1>Créer une Publication</h1>
            <a href="admin_dashboard.php" class="btn btn-primary">&larr; Retour</a>
        </div>
        
        <?php if ($message) echo "<p class='message'><i class='fa-solid fa-check-circle'></i> $message</p>"; ?>
        <?php if ($error) echo "<p class='error'><i class='fa-solid fa-exclamation-triangle'></i> $error</p>"; ?>

        <div class="form-container">
            <form action="post_media.php" method="POST" enctype="multipart/form-data">
                
                <div class="input-group">
                    <label for="titre">Titre (optionnel si vous mettez beaucoup de texte)</label>
                    <input type="text" id="titre" name="titre" placeholder="Un titre accrocheur pour votre post...">
                </div>

                <div class="input-group">
                    <label for="text_content">Texte</label>
                    <textarea id="text_content" name="text_content" rows="8" placeholder="Écrivez votre message ici..."></textarea>
                </div>
                
                <hr style="margin: 2rem 0;">
                
                <p style="text-align:center; color: var(--gray-color); margin-bottom: 1.5rem;">Ajoutez des éléments à votre publication (optionnel)</p>
                
                <div class="input-group">
                    <label for="image"><i class="fa-solid fa-image"></i> Ajouter une image</label>
                    <input type="file" id="image" name="image" class="input-file-styled">
                </div>

                <div class="input-group">
                    <label for="video_embed"><i class="fa-solid fa-video"></i> Ajouter une vidéo (code d'intégration)</label>
                    <textarea id="video_embed" name="video_embed" rows="3" placeholder="Collez le code <iframe ...> de YouTube, Vimeo, etc."></textarea>
                </div>
                
                <div class="input-group">
                    <label for="link_url"><i class="fa-solid fa-link"></i> Ajouter un lien externe</label>
                    <input type="text" id="link_url" name="link_url" placeholder="https://exemple.com">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.2rem;"><i class="fa-solid fa-paper-plane"></i> Publier</button>
            </form>
        </div>
    </div>
</body>
</html>