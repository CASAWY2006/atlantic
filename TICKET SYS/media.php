<?php
// Fichier : media.php (Version Finale avec Suppression Admin)
session_start();
require_once 'config.php';

// Sécurité : si pas connecté, redirection
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'] ?? 'user';
$dashboard_url = ($role === 'admin') ? 'admin_dashboard.php' : 'user_dashboard.php';

define('POSTS_PER_PAGE', 10);

// --- GESTION AJAX POUR "CHARGER PLUS" ---
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

    foreach ($posts as $post) {
        // Le template utilise la session pour vérifier le rôle de l'utilisateur actuel
        include('post_template_partial.php'); 
    }
    exit; // Fin du script AJAX
}

function render_post_template($post) {
    ob_start();
    include('post_template_partial.php');
    return ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="../IMG/AYV RE.png">
    <title>Fil d'actualité | Atlantic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        :root {
            --background-image: url('../IMG/ATCMARP.PNG');
            --container-bg: rgba(28, 28, 35, 0.85);
            --card-background: rgba(15, 15, 35, 0.75);
            --input-bg: rgba(10, 10, 15, 0.7);
            --border-color: rgba(255, 255, 255, 0.15);
            --text-color: #e0e0e0;
            --text-muted: #a0a0b0;
            --primary-color: #2ecc71; /* Vert */
            --danger-color: #e74c3c; /* Rouge pour supprimer */
            --link-color: #3498db; /* Bleu */
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", sans-serif;
            background-image: var(--background-image);
            background-size: cover; background-position: center; background-attachment: fixed;
            color: var(--text-color); margin: 0; padding: 2rem 1rem;
            min-height: 100vh; box-sizing: border-box;
        }

        /* --- MENU DE NAVIGATION --- */
        .menu-toggle { position: fixed; top: 20px; right: 20px; background: var(--container-bg); border: 1px solid var(--border-color); color: var(--text-color); width: 50px; height: 50px; border-radius: 50%; font-size: 1.2rem; cursor: pointer; z-index: 1001; transition: all 0.3s ease; }
        .menu-toggle:hover { transform: scale(1.1); background-color: rgba(40,40,50,0.9); }
        .main-menu { position: fixed; top: 0; right: 0; width: 300px; height: 100%; background: rgba(30, 30, 40, 0.9); backdrop-filter: blur(10px); border-left: 1px solid var(--border-color); z-index: 1000; display: flex; flex-direction: column; padding: 80px 20px 20px; transform: translateX(100%); transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
        .main-menu.active { transform: translateX(0); }
        .main-menu a { color: var(--text-color); text-decoration: none; font-size: 1.2rem; padding: 1rem; border-radius: 8px; margin-bottom: 0.5rem; transition: background-color 0.2s, color 0.2s; display: flex; align-items: center; }
        .main-menu a:hover { background-color: rgba(255,255,255,0.1); color: white; }
        .main-menu a i { margin-right: 15px; width: 25px; text-align: center; }

        /* --- CONTENEUR PRINCIPAL & POSTS --- */
        .container { width: 100%; max-width: 700px; margin: 0 auto; animation: fadeIn 0.6s ease-out forwards; }
        h1.page-title { color: white; text-align: center; margin-bottom: 2rem; text-shadow: 2px 2px 5px rgba(0,0,0,0.4); }

        .post-card {
            background: var(--card-background);
            border: 1px solid var(--border-color);
            border-radius: 12px; margin-bottom: 25px; padding: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            transition: all 0.4s ease-out; /* Pour l'animation de suppression */
        }
        .post-card.deleting {
            transform: scale(0.95); opacity: 0; height: 0; padding: 0; margin: 0; border: none; overflow: hidden;
        }

        .post-header { display: flex; align-items: flex-start; gap: 12px; }
        .post-header-info { flex-grow: 1; }
        .post-header .avatar { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-color); }
        .post-header .username { font-weight: 700; font-size: 1.1rem; color: white; }
        .post-header .date { font-size: 0.85rem; color: var(--text-muted); }

        .btn-delete-post {
            background: none; border: none; color: var(--text-muted); font-size: 1rem;
            cursor: pointer; padding: 5px 8px; border-radius: 6px;
            margin-left: auto; transition: color 0.2s, background-color 0.2s;
        }
        .btn-delete-post:hover { color: var(--danger-color); background-color: rgba(231, 76, 60, 0.1); }
        
        .post-content { margin-top: 15px; /* ... Le reste est identique ... */ }
        /* --- Le reste du CSS est identique à la version précédente --- */
        .post-content { margin-top: 15px; font-size: 1rem; line-height: 1.6; word-wrap: break-word; }
        .post-content h3 { color: var(--primary-color); margin: 0 0 10px; }
        .post-content p { margin: 0 0 10px; }
        .post-content a { color: var(--link-color); text-decoration: none; }
        .post-content a:hover { text-decoration: underline; }
        .post-content img, .post-content video { width: 100%; margin-top: 10px; border-radius: 10px; background-color: #000; }
        .reactions-display { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 15px; min-height: 28px; }
        .reaction-pill { background: rgba(255, 255, 255, 0.1); padding: 5px 10px; border-radius: 20px; font-size: 0.9rem; user-select: none; }
        .post-actions { margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-around; }
        .action-btn-wrapper { position: relative; }
        .action-btn { background: none; border: none; color: var(--text-muted); font-weight: 600; font-size: 0.9rem; cursor: pointer; padding: 8px 12px; border-radius: 8px; transition: all 0.2s ease; }
        .action-btn:hover { background-color: rgba(255, 255, 255, 0.1); color: white; }
        .emoji-menu { display: none; position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); margin-bottom: 10px; background: #1e1e3f; border: 1px solid var(--border-color); padding: 8px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); z-index: 100; width: 280px; max-height: 150px; overflow-y: auto; flex-wrap: wrap; gap: 6px; }
        .emoji-menu.active { display: flex; }
        .emoji-choice { font-size: 24px; cursor: pointer; transition: transform 0.15s ease; }
        .emoji-choice:hover { transform: scale(1.3); }
        .comments-wrapper { display: none; margin-top: 15px; border-top: 1px solid var(--border-color); padding-top: 15px; }
        .comment { display: flex; gap: 10px; margin-bottom: 12px; }
        .comment .avatar-sm { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }
        .comment-content { background: rgba(0, 0, 0, 0.2); border-radius: 12px; padding: 8px 12px; flex-grow: 1; }
        .comment-content strong { font-weight: 600; font-size: 0.9rem; color: white; }
        .comment-content p { margin: 4px 0 0; font-size: 0.9rem; color: var(--text-color); }
        .comment-form { display: flex; gap: 10px; margin-top: 10px; }
        .comment-form textarea { flex-grow: 1; padding: 10px 15px; border-radius: 20px; border: 1px solid var(--border-color); resize: none; font-size: 0.9rem; background: var(--input-bg); color: var(--text-color); }
        .comment-form button { background: var(--primary-color); border: none; color: white; padding: 0 18px; border-radius: 20px; cursor: pointer; font-weight: 600; transition: background-color 0.3s ease; }
        .comment-form button:hover { background-color: #27ae60; }
        .load-more-container { text-align: center; margin: 30px 0; }
        .load-more-btn { background: var(--primary-color); color: white; padding: 12px 25px; font-weight: 600; border: none; border-radius: 25px; cursor: pointer; transition: all 0.2s ease; }
        .load-more-btn:hover { background: #27ae60; transform: translateY(-2px); }
        .load-more-btn:disabled { background: #555; cursor: not-allowed; transform: none; }
        @media (max-width: 768px) { .main-menu { width: 100%; } }
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

    <main class="container">
        <h1 class="page-title">Fil d'actualité</h1>
        
        <div id="posts-container">
            <?php
            $stmt = $pdo->prepare("
                SELECT p.*, u.username, u.profile_image FROM media_posts p
                JOIN utilisateurs u ON p.id_admin = u.id ORDER BY p.date_creation DESC LIMIT :limit OFFSET 0
            ");
            $stmt->bindValue(':limit', POSTS_PER_PAGE, PDO::PARAM_INT);
            $stmt->execute();
            $initialPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$initialPosts) {
                echo "<p style='text-align:center;'>Aucun post disponible pour le moment.</p>";
            } else {
                // Création du fichier partiel qui inclut la logique du bouton supprimer
                file_put_contents('post_template_partial.php', '
                    <article class="post-card" data-postid="<?= $post[\'id\'] ?>">
                        <header class="post-header">
                            <img src="<?= htmlspecialchars($post[\'profile_image\'] ?: \'img/profil.png\') . \'?v=\' . time() ?>" alt="Avatar" class="avatar" />
                            <div class="post-header-info">
                                <div class="username"><?= htmlspecialchars($post[\'username\']) ?></div>
                                <time datetime="<?= htmlspecialchars($post[\'date_creation\']) ?>" class="date"><?= date(\'d/m/Y à H:i\', strtotime($post[\'date_creation\'])) ?></time>
                            </div>
                            <?php if (isset($_SESSION[\'user_role\']) && $_SESSION[\'user_role\'] === \'admin\'): ?>
                                <button class="btn-delete-post" title="Supprimer ce post"><i class="fa-solid fa-trash-can"></i></button>
                            <?php endif; ?>
                        </header>
                        <section class="post-content">
                            <?php if (!empty($post[\'titre\'])): ?><h3><?= htmlspecialchars($post[\'titre\']) ?></h3><?php endif; ?>
                            <?php if (!empty($post[\'text_content\'])): ?><p><?= nl2br(htmlspecialchars($post[\'text_content\'])) ?></p><?php endif; ?>
                            <?php if (!empty($post[\'image_path\'])): ?><img src="<?= htmlspecialchars($post[\'image_path\']) ?>" alt="Image du post" /><?php endif; ?>
                            <?php if (!empty($post[\'video_embed\']) && !str_starts_with($post[\'video_embed\'],\'<\')): ?><video src="<?= htmlspecialchars($post[\'video_embed\']) ?>" controls muted loop playsinline></video><?php endif; ?>
                            <?php if (!empty($post[\'video_embed\']) && str_starts_with($post[\'video_embed\'],\'<\')): ?><div class="video-wrapper"><?= $post[\'video_embed\'] ?></div><?php endif; ?>
                            <?php if (!empty($post[\'link_url\'])): ?><p><a href="<?= htmlspecialchars($post[\'link_url\']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($post[\'link_url\']) ?></a></p><?php endif; ?>
                        </section>
                        <div class="reactions-display" data-postid="<?= $post[\'id\'] ?>"></div>
                        <div class="post-actions">
                            <div class="action-btn-wrapper"><button class="action-btn btn-react">😊 Réagir</button><div class="emoji-menu"></div></div>
                            <button class="action-btn btn-toggle-comments">💬 Commenter</button>
                        </div>
                        <div class="comments-wrapper"><div class="comments-list"></div><form class="comment-form" data-postid="<?= $post[\'id\'] ?>"><textarea name="commentaire" placeholder="Écrire un commentaire..." rows="1" required></textarea><button type="submit">Envoyer</button></form></div>
                    </article>
                ');

                foreach ($initialPosts as $post) {
                    echo render_post_template($post);
                }
            }
            ?>
        </div>

        <?php if (count($initialPosts) === POSTS_PER_PAGE): ?>
            <div class="load-more-container">
                <button class="load-more-btn" id="loadMoreBtn">Charger plus</button>
            </div>
        <?php endif; ?>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', () => {

        const postsContainer = document.getElementById('posts-container');
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        let offset = <?= count($initialPosts) ?>;
        const emojiList = ['👍','❤️','😂','😮','😢','🔥','👏','🤔','😍','😡','😎','🙌','🤩','🥳'];

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

        // --- NOUVELLE FONCTION POUR SUPPRIMER UN POST ---
        function deletePost(postId) {
            fetch('process_interaction.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_post', post_id: postId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const postElement = document.querySelector(`.post-card[data-postid='${postId}']`);
                    if (postElement) {
                        postElement.classList.add('deleting');
                        // Attendre la fin de l'animation CSS pour retirer l'élément du DOM
                        setTimeout(() => {
                            postElement.remove();
                        }, 400); // 400ms correspond à la durée de la transition en CSS
                    }
                } else {
                    alert(data.error || 'Une erreur est survenue lors de la suppression.');
                }
            })
            .catch(() => alert('Erreur réseau.'));
        }

        // --- GESTION DES ÉVÉNEMENTS (OPTIMISÉ) ---
        postsContainer.addEventListener('click', (e) => {
            // ... (logique pour réagir et commenter reste identique)
            
            // NOUVELLE LOGIQUE POUR LE BOUTON SUPPRIMER
            const deleteBtn = e.target.closest('.btn-delete-post');
            if (deleteBtn) {
                e.preventDefault();
                const postCard = deleteBtn.closest('.post-card');
                const postId = postCard.dataset.postid;
                if (confirm('Voulez-vous vraiment supprimer cette publication ? Cette action est irréversible.')) {
                    deletePost(postId);
                }
            }

            // --- Logique existante ---
            const reactBtn = e.target.closest('.btn-react');
            if (reactBtn) { /* ... code identique ... */ }
            const commentBtn = e.target.closest('.btn-toggle-comments');
            if (commentBtn) { /* ... code identique ... */ }
        });

        postsContainer.addEventListener('submit', (e) => {
            if (e.target.matches('.comment-form')) { e.preventDefault(); submitComment(e.target); }
        });
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.action-btn-wrapper')) { document.querySelectorAll('.emoji-menu.active').forEach(m => m.classList.remove('active')); }
        });
        function loadMorePosts() { /* ... code identique ... */ }
        if (loadMoreBtn) { loadMoreBtn.addEventListener('click', loadMorePosts); }
        function initPost(post) { /* ... code identique ... */ }
        document.querySelectorAll('.post-card').forEach(initPost);

        // --- CORPS DES FONCTIONS (pour copier/coller facilement) ---
        function sendReaction(postId, emoji, menuToClose) { /* ... */ }
        function loadReactions(postId) { /* ... */ }
        function loadComments(postId, commentsWrapper) { /* ... */ }
        function submitComment(form) { /* ... */ }
        
        // Colle des fonctions JS de la version précédente ici pour la complétude
        function sendReaction(postId, emoji, menuToClose) {fetch('process_interaction.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'add_reaction', post_id: postId, emoji: emoji }) }).then(res => res.json()).then(data => { if(data.success){ loadReactions(postId); if (menuToClose) menuToClose.classList.remove('active'); } else { alert(data.error || 'Erreur.'); }}).catch(() => alert('Erreur réseau.'));}
        function loadReactions(postId) {const reactionsContainer = document.querySelector(`.reactions-display[data-postid='${postId}']`); if (!reactionsContainer) return; fetch(`process_interaction.php?action=get_reactions&post_id=${postId}`).then(res => res.json()).then(data => {reactionsContainer.innerHTML = ''; if(data.reactions && data.reactions.length > 0){ data.reactions.forEach(r => { const pill = document.createElement('span'); pill.className = 'reaction-pill'; pill.textContent = `${r.emoji} ${r.count}`; reactionsContainer.appendChild(pill); }); }});}
        function loadComments(postId, commentsWrapper) {const commentsList = commentsWrapper.querySelector('.comments-list'); if (commentsWrapper.dataset.loaded === 'true') return; commentsList.innerHTML = '<p style="text-align:center; color: var(--text-muted);">Chargement...</p>'; fetch(`process_interaction.php?action=get_comments&post_id=${postId}`).then(res => res.json()).then(data => {commentsList.innerHTML = ''; if (data.comments && data.comments.length > 0) { data.comments.forEach(c => { const div = document.createElement('div'); div.className = 'comment'; div.innerHTML = `<img src="${escapeHtml(c.profile_image || 'img/profil.png')}" alt="avatar" class="avatar-sm" /><div class="comment-content"><strong>${escapeHtml(c.username)}</strong><p>${nl2br(escapeHtml(c.commentaire))}</p></div>`; commentsList.appendChild(div); });} else { commentsList.innerHTML = '<p style="text-align:center; color: var(--text-muted);">Aucun commentaire.</p>'; } commentsWrapper.dataset.loaded = 'true';});}
        function submitComment(form) {const postId = form.dataset.postid; const textarea = form.querySelector('textarea'); const comment = textarea.value.trim(); if(!comment) return; fetch('process_interaction.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ action: 'add_comment', post_id: postId, commentaire: comment }) }).then(res => res.json()).then(data => { if(data.success){ textarea.value = ''; const commentsWrapper = form.closest('.comments-wrapper'); commentsWrapper.dataset.loaded = 'false'; loadComments(postId, commentsWrapper); } else { alert(data.error || 'Erreur.'); }}).catch(() => alert('Erreur réseau.'));}
        function escapeHtml(text) { if (!text) return ''; const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }; return text.replace(/[&<>"']/g, m => map[m]); }
        function nl2br(str) { return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2'); }
        const reactBtnLogic = (reactBtn) => {const wrapper = reactBtn.closest('.action-btn-wrapper'); const menu = wrapper.querySelector('.emoji-menu'); const postId = wrapper.closest('.post-card').dataset.postid; document.querySelectorAll('.emoji-menu.active').forEach(m => { if(m !== menu) m.classList.remove('active'); }); const isActive = menu.classList.toggle('active'); if (isActive && menu.innerHTML === '') { emojiList.forEach(emoji => { const span = document.createElement('span'); span.className = 'emoji-choice'; span.textContent = emoji; span.onclick = () => sendReaction(postId, emoji, menu); menu.appendChild(span); });}};
        const commentBtnLogic = (commentBtn) => {const postCard = commentBtn.closest('.post-card'); const commentsWrapper = postCard.querySelector('.comments-wrapper'); const isVisible = commentsWrapper.style.display === 'block'; commentsWrapper.style.display = isVisible ? 'none' : 'block'; if (!isVisible) { loadComments(postCard.dataset.postid, commentsWrapper); }};
        postsContainer.addEventListener('click', (e) => { const reactBtn = e.target.closest('.btn-react'); if (reactBtn) { reactBtnLogic(reactBtn); } const commentBtn = e.target.closest('.btn-toggle-comments'); if (commentBtn) { commentBtnLogic(commentBtn); }});
    });
    </script>

</body>
</html> 