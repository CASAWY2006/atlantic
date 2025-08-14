<?php
// Fichier : process_interaction.php (Version Finale - Corrigée pour VOTRE Base de Données)
session_start();
require_once 'config.php';

// Sécurité de base
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé.']);
    exit();
}

header('Content-Type: application/json');
$user_id = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? ($data['action'] ?? null);


// --- Action pour ajouter ou modifier une réaction (CORRIGÉ) ---
if ($action === 'add_reaction') {
    $post_id = intval($data['post_id'] ?? 0);
    $emoji = trim($data['emoji'] ?? '');

    if (empty($post_id) || empty($emoji)) {
        echo json_encode(['success' => false, 'error' => 'Données invalides.']);
        exit();
    }
    
    try {
        // Utilise VOS noms de colonnes : `id_post` et `id_utilisateur`
        $stmt = $pdo->prepare(
            "INSERT INTO reactions (id_post, id_utilisateur, emoji) 
             VALUES (?, ?, ?) 
             ON DUPLICATE KEY UPDATE emoji = ?"
        );
        $stmt->execute([$post_id, $user_id, $emoji, $emoji]);
        
        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Erreur de base de données.']);
    }
    exit();
}


// --- Action pour récupérer les réactions (CORRIGÉ) ---
if ($action === 'get_reactions') {
    $post_id = intval($_GET['post_id'] ?? 0);
    if (!$post_id) {
        echo json_encode(['success' => true, 'reactions' => []]);
        exit();
    }
    // Utilise VOS noms de colonnes : `id_post`
    $stmt = $pdo->prepare("SELECT emoji, COUNT(id) AS count FROM reactions WHERE id_post = ? GROUP BY emoji");
    $stmt->execute([$post_id]);
    $reactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'reactions' => $reactions]);
    exit();
}


// --- Action pour ajouter un commentaire (CORRIGÉ) ---
if ($action === 'add_comment') {
    $post_id = intval($data['post_id'] ?? 0);
    $commentaire = trim($data['commentaire'] ?? '');

    if (empty($post_id) || empty($commentaire)) {
        echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
        exit();
    }
    // Utilise VOS noms de table/colonnes : `commentaires`, `id_post`, `id_utilisateur`
    $stmt = $pdo->prepare("INSERT INTO commentaires (id_post, id_utilisateur, commentaire, date_creation) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$post_id, $user_id, $commentaire]);
    echo json_encode(['success' => true]);
    exit();
}


// --- Action pour récupérer les commentaires (CORRIGÉ) ---
if ($action === 'get_comments') {
    $post_id = intval($_GET['post_id'] ?? 0);
    if (!$post_id) {
        echo json_encode(['success' => true, 'comments' => []]);
        exit();
    }
    // Utilise VOS noms de table/colonnes
    $stmt = $pdo->prepare("
        SELECT c.commentaire, u.username, u.profile_image
        FROM commentaires c
        JOIN utilisateurs u ON c.id_utilisateur = u.id
        WHERE c.id_post = ?
        ORDER BY c.date_creation ASC
    ");
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'comments' => $comments]);
    exit();
}


// --- Action pour supprimer un post (CORRIGÉ) ---
if ($action === 'delete_post') {
    if ($_SESSION['user_role'] !== 'admin') {
        echo json_encode(['success' => false, 'error' => 'Action non autorisée.']);
        exit();
    }
    $post_id = intval($data['post_id'] ?? 0);
    if (!$post_id) {
        echo json_encode(['success' => false, 'error' => 'ID de post invalide.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT image_path, video_embed FROM media_posts WHERE id = ?");
        $stmt->execute([$post_id]);
        if ($files = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($files['image_path']) && file_exists($files['image_path'])) unlink($files['image_path']);
            if (!empty($files['video_embed']) && !str_starts_with($files['video_embed'], '<') && file_exists($files['video_embed'])) unlink($files['video_embed']);
        }
        
        $pdo->beginTransaction();
        // Utilise VOS noms de table/colonnes
        $pdo->prepare("DELETE FROM commentaires WHERE id_post = ?")->execute([$post_id]);
        $pdo->prepare("DELETE FROM reactions WHERE id_post = ?")->execute([$post_id]);
        $pdo->prepare("DELETE FROM media_posts WHERE id = ?")->execute([$post_id]);
        $pdo->commit();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Erreur de base de données.']);
    }
    exit();
}

// --- Si aucune action ne correspond ---
echo json_encode(['success' => false, 'error' => 'Action inconnue.']);
exit();
?>