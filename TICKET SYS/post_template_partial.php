
                    <article class="post-card" data-postid="<?= $post['id'] ?>">
                        <header class="post-header">
                            <img src="<?= htmlspecialchars($post['profile_image'] ?: 'img/profil.png') . '?v=' . time() ?>" alt="Avatar" class="avatar" />
                            <div class="post-header-info">
                                <div class="username"><?= htmlspecialchars($post['username']) ?></div>
                                <time datetime="<?= htmlspecialchars($post['date_creation']) ?>" class="date"><?= date('d/m/Y à H:i', strtotime($post['date_creation'])) ?></time>
                            </div>
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <button class="btn-delete-post" title="Supprimer ce post"><i class="fa-solid fa-trash-can"></i></button>
                            <?php endif; ?>
                        </header>
                        <section class="post-content">
                            <?php if (!empty($post['titre'])): ?><h3><?= htmlspecialchars($post['titre']) ?></h3><?php endif; ?>
                            <?php if (!empty($post['text_content'])): ?><p><?= nl2br(htmlspecialchars($post['text_content'])) ?></p><?php endif; ?>
                            <?php if (!empty($post['image_path'])): ?><img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Image du post" /><?php endif; ?>
                            <?php if (!empty($post['video_embed']) && !str_starts_with($post['video_embed'],'<')): ?><video src="<?= htmlspecialchars($post['video_embed']) ?>" controls muted loop playsinline></video><?php endif; ?>
                            <?php if (!empty($post['video_embed']) && str_starts_with($post['video_embed'],'<')): ?><div class="video-wrapper"><?= $post['video_embed'] ?></div><?php endif; ?>
                            <?php if (!empty($post['link_url'])): ?><p><a href="<?= htmlspecialchars($post['link_url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($post['link_url']) ?></a></p><?php endif; ?>
                        </section>
                        <div class="reactions-display" data-postid="<?= $post['id'] ?>"></div>
                        <div class="post-actions">
                            <div class="action-btn-wrapper"><button class="action-btn btn-react">😊 Réagir</button><div class="emoji-menu"></div></div>
                            <button class="action-btn btn-toggle-comments">💬 Commenter</button>
                        </div>
                        <div class="comments-wrapper"><div class="comments-list"></div><form class="comment-form" data-postid="<?= $post['id'] ?>"><textarea name="commentaire" placeholder="Écrire un commentaire..." rows="1" required></textarea><button type="submit">Envoyer</button></form></div>
                    </article>
                