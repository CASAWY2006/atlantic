<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? ($data['action'] ?? '');

if ($action === 'add_reaction') {
    $post_id = intval($data['post_id'] ?? 0);
    $emoji = trim($data['emoji'] ?? '');

    if (!$post_id || !$emoji) {
        echo json_encode(['error' => 'Paramètres manquants']);
        exit();
    }

    // Vérifier si déjà réagi, si oui update, sinon insert
    $stmt = $pdo->prepare("SELECT id FROM reactions WHERE id_post = ? AND id_utilisateur = ?");
    $stmt->execute([$post_id, $user_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE reactions SET emoji = ? WHERE id_post = ? AND id_utilisateur = ?");
        $stmt->execute([$emoji, $post_id, $user_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO reactions (id_post, id_utilisateur, emoji) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $user_id, $emoji]);
    }
    echo json_encode(['success' => true]);
    exit();
}

if ($action === 'get_reactions') {
    $post_id = intval($_GET['post_id'] ?? 0);
    if (!$post_id) {
        echo json_encode(['reactions' => []]);
        exit();
    }
    $stmt = $pdo->prepare("SELECT emoji, COUNT(id) AS count FROM reactions WHERE id_post = ? GROUP BY emoji");
    $stmt->execute([$post_id]);
    $reactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['reactions' => $reactions]);
    exit();
}

if ($action === 'add_comment') {
    $post_id = intval($data['post_id'] ?? 0);
    $commentaire = trim($data['commentaire'] ?? '');

    if (!$post_id || !$commentaire) {
        echo json_encode(['error' => 'Paramètres manquants']);
        exit();
    }
    $stmt = $pdo->prepare("INSERT INTO commentaires (id_post, id_utilisateur, commentaire, date_creation) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$post_id, $user_id, $commentaire]);
    echo json_encode(['success' => true]);
    exit();
}

if ($action === 'get_comments') {
    $post_id = intval($_GET['post_id'] ?? 0);
    if (!$post_id) {
        echo json_encode(['comments' => []]);
        exit();
    }
    $stmt = $pdo->prepare("
        SELECT c.commentaire, u.username, u.profile_image
        FROM commentaires c
        JOIN utilisateurs u ON c.id_utilisateur = u.id
        WHERE c.id_post = ?
        ORDER BY c.date_creation ASC
    ");
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['comments' => $comments]);
    exit();
}

echo json_encode(['error' => 'Action inconnue']);
