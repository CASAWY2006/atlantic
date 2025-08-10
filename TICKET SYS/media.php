<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'] ?? 'user';

define('POSTS_PER_PAGE', 10);

// Si requête AJAX pour charger plus de posts (via GET offset)
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $offset = intval($_GET['offset'] ?? 0);

    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.profile_image
        FROM media_posts p
        JOIN utilisateurs u ON p.id_admin = u.id
        ORDER BY p.date_creation DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', POSTS_PER_PAGE, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($posts as $post):
?>
    <article class="post-card" data-postid="<?= $post['id'] ?>">
        <header class="post-header">
            <img src="<?= htmlspecialchars($post['profile_image']) . '?v=' . time() ?>" alt="Avatar" class="avatar" />

            <div class="username"><?= htmlspecialchars($post['username']) ?></div>
            <time datetime="<?= htmlspecialchars($post['date_creation']) ?>" class="date"><?= date('d/m/Y H:i', strtotime($post['date_creation'])) ?></time>
        </header>
        <section class="post-content">
            <?php if ($post['titre']): ?><h3><?= htmlspecialchars($post['titre']) ?></h3><?php endif; ?>
            <?php if ($post['text_content']): ?><p><?= nl2br(htmlspecialchars($post['text_content'])) ?></p><?php endif; ?>
            <?php if ($post['image_path']): ?><img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Image du post" /><?php endif; ?>
            <?php if ($post['video_embed']): ?><div class="video-wrapper"><?= $post['video_embed'] ?></div><?php endif; ?>
            <?php if ($post['link_url']): ?><p><a href="<?= htmlspecialchars($post['link_url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($post['link_url']) ?></a></p><?php endif; ?>
        </section>
        <section class="reactions" data-postid="<?= $post['id'] ?>">
            <button class="btn-show-emoji">😊 Réagir</button>
            <div class="emoji-menu"></div>
        </section>
        <section class="comments" data-postid="<?= $post['id'] ?>"></section>
        <form class="comment-form" data-postid="<?= $post['id'] ?>">
            <textarea placeholder="Écrire un commentaire..." required></textarea>
            <button type="submit">Envoyer</button>
        </form>
    </article>
<?php
    endforeach;
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Fil Média</title>
    <style>
        body {
            background: url('img/ATCMARP.png') no-repeat center center fixed;
            background-size: cover;
        }
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    margin: 0; padding: 0; color: #222;
}
.navbar {
    background: #1877f2; color: #fff;
    padding: 10px 20px;
    display: flex; justify-content: space-between; align-items: center;
}
.navbar a { color: white; text-decoration: none; font-weight: 600; }
.navbar a:hover { text-decoration: underline; }
.container {
    max-width: 700px;
    margin: 20px auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    padding: 20px;
}
.post-card {
    border-bottom: 1px solid #ddd;
    padding: 15px 0;
}
.post-header {
    display: flex; align-items: center; gap: 12px;
}
.post-header img.avatar {
    width: 45px; height: 45px;
    border-radius: 50%;
    object-fit: cover;
}
.username {
    font-weight: 700; font-size: 1.1rem;
}
.date {
    margin-left: auto;
    font-size: 0.85rem;
    color: #666;
}
.post-content {
    margin-top: 12px;
    font-size: 1rem; line-height: 1.5;
}
.post-content img,
.post-content video,
.post-content iframe {
    width: 100%;
    margin-top: 10px;
    border-radius: 10px;
}
.reactions {
    margin-top: 10px;
    position: relative;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}
.reaction-pill {
    background: #f0f2f5;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.9rem;
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s ease;
}
.reaction-pill:hover {
    background-color: #cce4ff;
}
.btn-show-emoji {
    background: #f0f2f5;
    border: none;
    padding: 6px 12px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 600;
    user-select: none;
}
.btn-show-emoji:hover {
    background-color: #cce4ff;
}
.emoji-menu {
    display: none;
    position: absolute;
    background: white;
    border: 1px solid #ccc;
    padding: 5px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    z-index: 100;
    max-width: 280px;
    max-height: 150px;
    overflow-y: auto;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    top: 36px;
    left: 0;
}
.emoji-choice {
    font-size: 22px;
    cursor: pointer;
    transition: transform 0.15s ease;
}
.emoji-choice:hover {
    transform: scale(1.3);
}
.comments {
    margin-top: 15px;
    border-top: 1px solid #ddd;
    padding-top: 12px;
}
.comment {
    display: flex; gap: 10px;
    margin-bottom: 10px;
}
.comment img.avatar-sm {
    width: 30px; height: 30px;
    border-radius: 50%;
    object-fit: cover;
}
.comment-content {
    background: #f9f9f9;
    border-radius: 12px;
    padding: 8px 12px;
    flex-grow: 1;
}
.comment-content strong {
    font-weight: 700;
    font-size: 0.9rem;
    color: #1877f2;
}
.comment-content p {
    margin: 4px 0 0;
    font-size: 0.9rem;
}
.comment-form {
    margin-top: 10px;
    display: flex;
    gap: 10px;
}
.comment-form textarea {
    flex-grow: 1;
    padding: 8px 12px;
    border-radius: 20px;
    border: 1px solid #ccc;
    resize: none;
    font-size: 0.9rem;
    min-height: 38px;
}
.comment-form button {
    background: #1877f2;
    border: none;
    color: white;
    padding: 0 16px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.95rem;
    transition: background-color 0.3s ease;
}
.comment-form button:hover {
    background-color: #0f4db7;
}
.load-more-container {
    text-align: center;
    margin: 30px 0 10px;
}
.load-more-btn {
    background: #1877f2;
    color: white;
    padding: 8px 20px;
    font-weight: 600;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
.load-more-btn:hover {
    background: #0f4db7;
}
@media (max-width: 768px) {
    .container { width: 95%; padding: 15px; }
}
</style>
</head>
<body>

<div class="navbar">
    <a href="<?= ($role === 'admin') ? 'admin_dashboard.php' : 'user_dashboard.php' ?>">Accueil</a>
    <div>
        <a href="profile.php">Mon Profil</a> | 
        <a href="logout.php">Déconnexion</a>
    </div>
</div>

<main class="container" id="posts-container">
    <h1>Fil d'actualité</h1>

    <?php
    // Charge initial posts PHP côté serveur pour SEO + fallback
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.profile_image

        FROM media_posts p
        JOIN utilisateurs u ON p.id_admin = u.id
        ORDER BY p.date_creation DESC
        LIMIT :limit OFFSET 0
    ");
    $stmt->bindValue(':limit', POSTS_PER_PAGE, PDO::PARAM_INT);
    $stmt->execute();
    $initialPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$initialPosts): ?>
        <p>Aucun post disponible.</p>
    <?php else:
        foreach ($initialPosts as $post): ?>
            <article class="post-card" data-postid="<?= $post['id'] ?>">
                <header class="post-header">
                    <img src="<?= htmlspecialchars($post['profile_image']) ?>" alt="Avatar" class="avatar" />
                    <div class="username"><?= htmlspecialchars($post['username']) ?></div>
                    <time datetime="<?= htmlspecialchars($post['date_creation']) ?>" class="date"><?= date('d/m/Y H:i', strtotime($post['date_creation'])) ?></time>
                </header>
                <section class="post-content">
                    <?php if ($post['titre']): ?><h3><?= htmlspecialchars($post['titre']) ?></h3><?php endif; ?>
                    <?php if ($post['text_content']): ?><p><?= nl2br(htmlspecialchars($post['text_content'])) ?></p><?php endif; ?>
                    <?php if ($post['image_path']): ?><img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Image du post" /><?php endif; ?>
                    <?php if ($post['video_embed']): ?><div class="video-wrapper"><?= $post['video_embed'] ?></div><?php endif; ?>
                    <?php if ($post['link_url']): ?><p><a href="<?= htmlspecialchars($post['link_url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($post['link_url']) ?></a></p><?php endif; ?>
                </section>
                <section class="reactions" data-postid="<?= $post['id'] ?>">
                    <button class="btn-show-emoji">😊 Réagir</button>
                    <div class="emoji-menu"></div>
                </section>
                <section class="comments" data-postid="<?= $post['id'] ?>"></section>
                <form class="comment-form" data-postid="<?= $post['id'] ?>">
                    <textarea placeholder="Écrire un commentaire..." required></textarea>
                    <button type="submit">Envoyer</button>
                </form>
            </article>
        <?php endforeach; ?>
        <?php if (count($initialPosts) === POSTS_PER_PAGE): ?>
            <div class="load-more-container">
                <button class="load-more-btn" id="loadMoreBtn">Charger plus</button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<script>
// ==== Variables & fonctions ====

const emojiList = [
  '👍','❤️','😂','😮','😢','🔥','👏','🤔','😍','😡','😎','🙌','🤩','🥳','🤷','🤦','💯','✔️','✌️','🙏','😴','🥺','🤫','😇','💥','💬'
];

// Récupère les éléments de post
const postsContainer = document.getElementById('posts-container');

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Affiche menu emoji pour un post donné
function toggleEmojiMenu(btn) {
    const reactionsSection = btn.closest('.reactions');
    const menu = reactionsSection.querySelector('.emoji-menu');
    if(menu.style.display === 'flex'){
        menu.style.display = 'none';
        return;
    }
    // Cacher tous les menus ouverts
    document.querySelectorAll('.emoji-menu').forEach(m => m.style.display = 'none');

    menu.innerHTML = '';
    emojiList.forEach(e => {
        const span = document.createElement('span');
        span.className = 'emoji-choice';
        span.textContent = e;
        span.onclick = () => sendReaction(reactionsSection.dataset.postid, e);
        menu.appendChild(span);
    });
    menu.style.display = 'flex';
}

// Envoi réaction AJAX
function sendReaction(postId, emoji) {
    fetch('process_interaction.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add_reaction', post_id: postId, emoji: emoji })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            loadReactions(postId);
            // Cacher menu emoji
            document.querySelector(`.post-card[data-postid='${postId}'] .emoji-menu`).style.display = 'none';
        } else {
            alert(data.error || 'Erreur lors de l\'ajout de la réaction');
        }
    }).catch(() => alert('Erreur réseau'));
}

// Charger réactions pour un post
function loadReactions(postId) {
    fetch(`process_interaction.php?action=get_reactions&post_id=${postId}`)
    .then(res => res.json())
    .then(data => {
        if(!data.reactions) return;
        const reactionsSection = document.querySelector(`.reactions[data-postid='${postId}']`);
        if(!reactionsSection) return;
        // Supprimer anciennes réactions
        reactionsSection.querySelectorAll('.reaction-pill').forEach(e => e.remove());
        data.reactions.forEach(r => {
            const span = document.createElement('span');
            span.className = 'reaction-pill';
            span.textContent = `${r.emoji} ${r.count}`;
            reactionsSection.insertBefore(span, reactionsSection.querySelector('.btn-show-emoji'));
        });
    });
}

// Charger commentaires pour un post
function loadComments(postId) {
    fetch(`process_interaction.php?action=get_comments&post_id=${postId}`)
    .then(res => res.json())
    .then(data => {
        if(!data.comments) return;
        const commentsSection = document.querySelector(`.comments[data-postid='${postId}']`);
        if(!commentsSection) return;
        commentsSection.innerHTML = '';
        data.comments.forEach(c => {
            const div = document.createElement('div');
            div.className = 'comment';
            div.innerHTML = `
              <img src="${escapeHtml(c.profile_image)}" alt="avatar" class="avatar-sm" />

                <div class="comment-content">
                    <strong>${escapeHtml(c.username)}</strong>
                    <p>${escapeHtml(c.commentaire)}</p>
                </div>`;
            commentsSection.appendChild(div);
        });
    });
}

// Envoyer commentaire AJAX
function submitComment(e) {
    e.preventDefault();
    const form = e.target;
    const postId = form.dataset.postid;
    const textarea = form.querySelector('textarea');
    const comment = textarea.value.trim();
    if(!comment) return alert('Veuillez écrire un commentaire.');

    fetch('process_interaction.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ action: 'add_comment', post_id: postId, commentaire: comment })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            textarea.value = '';
            loadComments(postId);
        } else {
            alert(data.error || 'Erreur lors de l\'ajout du commentaire');
        }
    })
    .catch(() => alert('Erreur réseau'));
}

// Charger plus de posts
let offset = <?= count($initialPosts) ?>;
const POSTS_PER_PAGE = <?= POSTS_PER_PAGE ?>;
const loadMoreBtn = document.getElementById('loadMoreBtn');

function loadMorePosts(){
    loadMoreBtn.disabled = true;
    loadMoreBtn.textContent = 'Chargement...';

    fetch(`media.php?ajax=1&offset=${offset}`)
    .then(res => res.text())
    .then(html => {
        if(!html.trim()) {
            loadMoreBtn.textContent = 'Plus de posts';
            loadMoreBtn.disabled = true;
            return;
        }
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const newPosts = temp.querySelectorAll('.post-card');
        if(newPosts.length === 0){
            loadMoreBtn.textContent = 'Plus de posts';
            loadMoreBtn.disabled = true;
            return;
        }
        newPosts.forEach(post => postsContainer.appendChild(post));
        offset += newPosts.length;
        if(newPosts.length < POSTS_PER_PAGE){
            loadMoreBtn.textContent = 'Plus de posts';
            loadMoreBtn.disabled = true;
        } else {
            loadMoreBtn.textContent = 'Charger plus';
            loadMoreBtn.disabled = false;
        }
        initializePostEvents(newPosts);
    })
    .catch(() => {
        alert('Erreur de chargement des posts');
        loadMoreBtn.textContent = 'Charger plus';
        loadMoreBtn.disabled = false;
    });
}

if(loadMoreBtn){
    loadMoreBtn.addEventListener('click', loadMorePosts);
}

// Initialise événements sur les posts (réactions, commentaires)
function initializePostEvents(posts = null){
    const postElements = posts ? Array.from(posts) : Array.from(document.querySelectorAll('.post-card'));
    postElements.forEach(post => {
        const postId = post.dataset.postid;

        // Réactions bouton
        const btnShowEmoji = post.querySelector('.btn-show-emoji');
        btnShowEmoji.onclick = () => toggleEmojiMenu(btnShowEmoji);

        // Charger réactions & commentaires au chargement
        loadReactions(postId);
        loadComments(postId);

        // Formulaire commentaires
        const form = post.querySelector('.comment-form');
        form.addEventListener('submit', submitComment);
    });
}

initializePostEvents();

// Fermer menus emoji en cliquant ailleurs
document.addEventListener('click', e => {
    if(!e.target.closest('.reactions')){
        document.querySelectorAll('.emoji-menu').forEach(m => m.style.display = 'none');
    }
});
</script>

</body>
</html>
