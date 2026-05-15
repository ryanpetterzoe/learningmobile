<div class="page-header">
    <div>
        <h1><?= e($post['title']) ?></h1>
        <p><span class="badge badge-primary"><?= e($post['category_name']) ?></span> &bull; <?= time_ago($post['created_at']) ?> &bull; <?= $post['views'] ?> views</p>
    </div>
    <a href="<?= url('forum') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<!-- Original Post -->
<div class="card" style="margin-bottom: 20px;">
    <div class="forum-post-content">
        <div class="post-author-info">
            <?php if ($post['avatar']): ?>
                <img src="<?= upload_url($post['avatar']) ?>" class="post-avatar">
            <?php else: ?>
                <div class="post-avatar placeholder"><?= strtoupper(substr($post['full_name'], 0, 1)) ?></div>
            <?php endif; ?>
            <div>
                <span class="author-name"><a href="<?= url('profile/view/' . $post['author_id']) ?>" style="color:inherit;text-decoration:none;"><?= e($post['full_name']) ?></a></span>
                <span class="author-meta">Level <?= $post['level'] ?> &bull; <?= ucfirst($post['author_role'] ?? 'user') ?></span>
            </div>
        </div>
        <div class="post-body"><?= nl2br(e($post['content'])) ?></div>
        <?php if (!empty($post['attachment'])): ?>
            <div class="post-attachment">
                <?php
                $attachments = explode('|', $post['attachment']);
                $mediaCount = count($attachments);
                $gridClass = 'media-grid media-grid-' . min($mediaCount, 4);
                ?>
                <?php if ($mediaCount === 1): ?>
                    <?php
                    $ext = strtolower(pathinfo($attachments[0], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])): ?>
                        <img src="<?= upload_url($attachments[0]) ?>" alt="attachment" style="max-width:100%;border-radius:12px;">
                    <?php elseif (in_array($ext, ['mp4','webm'])): ?>
                        <video src="<?= upload_url($attachments[0]) ?>" controls style="max-width:100%;max-height:400px;border-radius:12px;"></video>
                    <?php else: ?>
                        <a href="<?= upload_url($attachments[0]) ?>" target="_blank" class="post-file-link">
                            <i class="fas fa-file-download"></i> <?= e(basename($attachments[0])) ?>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="post-media-grid <?= $gridClass ?>">
                        <?php foreach (array_slice($attachments, 0, 4) as $idx => $att): ?>
                            <?php
                            $ext = strtolower(pathinfo($att, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                            $isVideo = in_array($ext, ['mp4','webm']);
                            ?>
                            <div class="media-grid-item">
                                <?php if ($isImage): ?>
                                    <img src="<?= upload_url($att) ?>" alt="media" style="cursor:pointer;" onclick="window.open('<?= upload_url($att) ?>','_blank')">
                                <?php elseif ($isVideo): ?>
                                    <div class="media-grid-video">
                                        <video src="<?= upload_url($att) ?>" controls></video>
                                    </div>
                                <?php else: ?>
                                    <div class="media-grid-file">
                                        <a href="<?= upload_url($att) ?>" target="_blank" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:4px;">
                                            <i class="fas fa-file"></i>
                                            <span><?= e(basename($att)) ?></span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <?php if ($idx === 3 && $mediaCount > 4): ?>
                                    <div class="media-grid-overlay">+<?= $mediaCount - 4 ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($mediaCount > 4): ?>
                        <div class="post-extra-attachments" style="margin-top:12px;">
                            <?php foreach (array_slice($attachments, 4) as $att): ?>
                                <?php $ext = strtolower(pathinfo($att, PATHINFO_EXTENSION)); ?>
                                <?php if (in_array($ext, ['jpg','jpeg','png','gif','webp'])): ?>
                                    <img src="<?= upload_url($att) ?>" alt="media" style="max-width:100%;border-radius:8px;margin-bottom:8px;">
                                <?php elseif (in_array($ext, ['mp4','webm'])): ?>
                                    <video src="<?= upload_url($att) ?>" controls style="max-width:100%;border-radius:8px;margin-bottom:8px;"></video>
                                <?php else: ?>
                                    <a href="<?= upload_url($att) ?>" target="_blank" class="post-file-link" style="margin-bottom:8px;">
                                        <i class="fas fa-file-download"></i> <?= e(basename($att)) ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="post-actions">
            <form method="POST" action="<?= url('forum/like/' . $post['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="post-action-btn <?= $userLiked ? 'liked' : '' ?>">
                    <i class="fas fa-heart"></i> <?= $likeCount ?> Suka
                </button>
            </form>
            <span class="post-action-btn"><i class="fas fa-comment"></i> <?= $totalReplyCount ?> Komentar</span>
        </div>
    </div>
</div>

<!-- Comments Section (Facebook style) -->
<div class="comments-section">
    <h3 class="comments-title"><i class="fas fa-comments"></i> <?= $totalReplyCount ?> Komentar</h3>

    <!-- Main Reply Form -->
    <?php if (!$post['is_locked']): ?>
        <?php $cu = Session::user(); ?>
        <div class="comment-form-main">
            <form method="POST" action="<?= url('forum/reply/' . $post['id']) ?>" class="comment-form" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="comment-form-row">
                    <div class="comment-avatar">
                        <?php if ($cu['avatar']): ?>
                            <img src="<?= upload_url($cu['avatar']) ?>" alt="">
                        <?php else: ?>
                            <div class="avatar-mini"><?= strtoupper(substr($cu['full_name'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="comment-input-group">
                        <div class="comment-input-wrap">
                            <textarea name="content" class="comment-input" placeholder="Tulis komentar..." rows="1" required></textarea>
                            <label class="comment-attach-btn" title="Lampirkan foto/video/file">
                                <i class="fas fa-image"></i>
                                <input type="file" name="attachment" accept="image/*,video/*,.pdf,.doc,.docx,.zip" style="display:none;" onchange="previewCommentMedia(this, 'main-media-preview')">
                            </label>
                            <button type="submit" class="comment-send"><i class="fas fa-paper-plane"></i></button>
                        </div>
                        <div id="main-media-preview" class="comment-media-preview"></div>
                    </div>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-warning"><i class="fas fa-lock"></i> Diskusi ini telah dikunci.</div>
    <?php endif; ?>

    <!-- Comments List -->
    <?php if (!empty($parentReplies)): ?>
        <div class="comments-list">
            <?php foreach ($parentReplies as $reply): ?>
                <div class="comment-item" id="comment-<?= $reply['id'] ?>">
                    <div class="comment-avatar">
                        <?php if ($reply['avatar']): ?>
                            <img src="<?= upload_url($reply['avatar']) ?>" alt="">
                        <?php else: ?>
                            <div class="avatar-mini"><?= strtoupper(substr($reply['full_name'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="comment-body-wrap">
                        <div class="comment-bubble">
                            <span class="comment-author"><?= e($reply['full_name']) ?></span>
                            <div class="comment-text"><?= nl2br(e($reply['content'])) ?></div>
                            <?php if (!empty($reply['attachment'])): ?>
                                <div class="comment-media">
                                    <?php
                                    $ext = strtolower(pathinfo($reply['attachment'], PATHINFO_EXTENSION));
                                    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])): ?>
                                        <img src="<?= upload_url($reply['attachment']) ?>" alt="media" class="comment-media-img">
                                    <?php elseif (in_array($ext, ['mp4','webm'])): ?>
                                        <video src="<?= upload_url($reply['attachment']) ?>" controls class="comment-media-video"></video>
                                    <?php else: ?>
                                        <a href="<?= upload_url($reply['attachment']) ?>" target="_blank" class="comment-file-link"><i class="fas fa-file"></i> <?= e(basename($reply['attachment'])) ?></a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- Comment actions: Like, Reply, Time, Edit, Delete -->
                        <div class="comment-actions">
                            <form method="POST" action="<?= url('forum/like-reply/' . $reply['id']) ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="comment-act-btn <?= $reply['user_liked'] ? 'liked' : '' ?>">
                                    Suka<?php if ($reply['like_count'] > 0): ?> (<?= $reply['like_count'] ?>)<?php endif; ?>
                                </button>
                            </form>
                            <?php if (!$post['is_locked']): ?>
                                <button type="button" class="comment-act-btn" onclick="toggleReplyForm(<?= $reply['id'] ?>)">Balas</button>
                            <?php endif; ?>
                            <span class="comment-time"><?= time_ago($reply['created_at']) ?></span>
                            <?php if (!empty($reply['edited_at'])): ?>
                                <span class="comment-edited" title="Diedit <?= $reply['edited_at'] ?>">diedit</span>
                            <?php endif; ?>
                            <?php if ((int)$reply['author_id'] === (int)$userId): ?>
                                <button type="button" class="comment-act-btn" onclick="toggleEditForm(<?= $reply['id'] ?>)">Sunting</button>
                                <form method="POST" action="<?= url('forum/delete-reply/' . $reply['id']) ?>" style="display:inline;" onsubmit="return confirm('Hapus komentar ini?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="comment-act-btn comment-delete">Hapus</button>
                                </form>
                            <?php elseif (in_array($userRole, ['admin','wali_kelas'])): ?>
                                <form method="POST" action="<?= url('forum/delete-reply/' . $reply['id']) ?>" style="display:inline;" onsubmit="return confirm('Hapus komentar ini?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="comment-act-btn comment-delete">Hapus</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <!-- Edit form (hidden) -->
                        <div class="comment-edit-form" id="edit-form-<?= $reply['id'] ?>" style="display:none;">
                            <form method="POST" action="<?= url('forum/edit-reply/' . $reply['id']) ?>">
                                <?= csrf_field() ?>
                                <textarea name="content" class="comment-input" rows="2"><?= e($reply['content']) ?></textarea>
                                <div style="display:flex;gap:6px;margin-top:6px;">
                                    <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditForm(<?= $reply['id'] ?>)">Batal</button>
                                </div>
                            </form>
                        </div>

                        <!-- Sub-reply form (hidden) -->
                        <div class="sub-reply-form" id="reply-form-<?= $reply['id'] ?>" style="display:none;">
                            <form method="POST" action="<?= url('forum/reply/' . $post['id']) ?>" class="comment-form" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <input type="hidden" name="parent_reply_id" value="<?= $reply['id'] ?>">
                                <div class="comment-form-row">
                                    <div class="comment-avatar comment-avatar-sm">
                                        <?php if ($cu['avatar']): ?>
                                            <img src="<?= upload_url($cu['avatar']) ?>" alt="">
                                        <?php else: ?>
                                            <div class="avatar-mini sm"><?= strtoupper(substr($cu['full_name'], 0, 1)) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-input-group">
                                        <div class="comment-input-wrap">
                                            <textarea name="content" class="comment-input" placeholder="Balas <?= e($reply['full_name']) ?>..." rows="1" required></textarea>
                                            <label class="comment-attach-btn" title="Lampirkan file">
                                                <i class="fas fa-image"></i>
                                                <input type="file" name="attachment" accept="image/*,video/*,.pdf,.doc,.docx,.zip" style="display:none;" onchange="previewCommentMedia(this, 'reply-preview-<?= $reply['id'] ?>')">
                                            </label>
                                            <button type="submit" class="comment-send"><i class="fas fa-paper-plane"></i></button>
                                        </div>
                                        <div id="reply-preview-<?= $reply['id'] ?>" class="comment-media-preview"></div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Sub-replies (children) -->
                        <?php if (!empty($childReplies[$reply['id']])): ?>
                            <div class="sub-comments">
                                <?php foreach ($childReplies[$reply['id']] as $child): ?>
                                    <div class="comment-item sub" id="comment-<?= $child['id'] ?>">
                                        <div class="comment-avatar comment-avatar-sm">
                                            <?php if ($child['avatar']): ?>
                                                <img src="<?= upload_url($child['avatar']) ?>" alt="">
                                            <?php else: ?>
                                                <div class="avatar-mini sm"><?= strtoupper(substr($child['full_name'], 0, 1)) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="comment-body-wrap">
                                            <div class="comment-bubble sub-bubble">
                                                <span class="comment-author"><?= e($child['full_name']) ?></span>
                                                <div class="comment-text"><?= nl2br(e($child['content'])) ?></div>
                                                <?php if (!empty($child['attachment'])): ?>
                                                    <div class="comment-media">
                                                        <?php
                                                        $cext = strtolower(pathinfo($child['attachment'], PATHINFO_EXTENSION));
                                                        if (in_array($cext, ['jpg','jpeg','png','gif','webp'])): ?>
                                                            <img src="<?= upload_url($child['attachment']) ?>" alt="media" class="comment-media-img">
                                                        <?php elseif (in_array($cext, ['mp4','webm'])): ?>
                                                            <video src="<?= upload_url($child['attachment']) ?>" controls class="comment-media-video"></video>
                                                        <?php else: ?>
                                                            <a href="<?= upload_url($child['attachment']) ?>" target="_blank" class="comment-file-link"><i class="fas fa-file"></i> <?= e(basename($child['attachment'])) ?></a>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="comment-actions">
                                                <form method="POST" action="<?= url('forum/like-reply/' . $child['id']) ?>" style="display:inline;">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="comment-act-btn <?= $child['user_liked'] ? 'liked' : '' ?>">
                                                        Suka<?php if ($child['like_count'] > 0): ?> (<?= $child['like_count'] ?>)<?php endif; ?>
                                                    </button>
                                                </form>
                                                <span class="comment-time"><?= time_ago($child['created_at']) ?></span>
                                                <?php if (!empty($child['edited_at'])): ?>
                                                    <span class="comment-edited">diedit</span>
                                                <?php endif; ?>
                                                <?php if ((int)$child['author_id'] === (int)$userId): ?>
                                                    <button type="button" class="comment-act-btn" onclick="toggleEditForm(<?= $child['id'] ?>)">Sunting</button>
                                                    <form method="POST" action="<?= url('forum/delete-reply/' . $child['id']) ?>" style="display:inline;" onsubmit="return confirm('Hapus balasan ini?')">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="comment-act-btn comment-delete">Hapus</button>
                                                    </form>
                                                <?php elseif (in_array($userRole, ['admin','wali_kelas'])): ?>
                                                    <form method="POST" action="<?= url('forum/delete-reply/' . $child['id']) ?>" style="display:inline;" onsubmit="return confirm('Hapus balasan ini?')">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="comment-act-btn comment-delete">Hapus</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                            <!-- Edit form for sub-reply -->
                                            <div class="comment-edit-form" id="edit-form-<?= $child['id'] ?>" style="display:none;">
                                                <form method="POST" action="<?= url('forum/edit-reply/' . $child['id']) ?>">
                                                    <?= csrf_field() ?>
                                                    <textarea name="content" class="comment-input" rows="2"><?= e($child['content']) ?></textarea>
                                                    <div style="display:flex;gap:6px;margin-top:6px;">
                                                        <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                                        <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditForm(<?= $child['id'] ?>)">Batal</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif (!$post['is_locked']): ?>
        <p style="text-align:center;color:var(--text-muted);padding:20px;font-size:13px;">Belum ada komentar. Jadilah yang pertama!</p>
    <?php endif; ?>
</div>

<style>
/* Post styles */
.post-author-info { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
.post-avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; }
.post-avatar.placeholder { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; }
.author-name { display: block; font-size: 14px; font-weight: 700; color: var(--text-primary); }
.author-meta { font-size: 11px; color: var(--text-muted); }
.post-body { font-size: 14px; color: var(--text-secondary); line-height: 1.8; padding: 16px 0; border-top: 1px solid var(--border-light); white-space: pre-wrap; }
.post-attachment { padding-bottom: 12px; }
.post-file-link { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: var(--bg-hover); border: 1px solid var(--border-light); border-radius: 10px; font-size: 13px; color: var(--primary); text-decoration: none; font-weight: 500; transition: all 0.2s; }
.post-file-link:hover { background: var(--primary-bg); border-color: var(--primary); }
/* Post media grid */
.post-media-grid { display: grid; gap: 3px; border-radius: 12px; overflow: hidden; }
.post-media-grid.media-grid-1 { grid-template-columns: 1fr; }
.post-media-grid.media-grid-2 { grid-template-columns: 1fr 1fr; }
.post-media-grid.media-grid-3 { grid-template-columns: 2fr 1fr; grid-template-rows: 1fr 1fr; }
.post-media-grid.media-grid-3 .media-grid-item:first-child { grid-row: 1 / 3; }
.post-media-grid.media-grid-4 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
.post-media-grid .media-grid-item { position: relative; overflow: hidden; background: var(--bg-hover); display: flex; align-items: center; justify-content: center; min-height: 150px; max-height: 350px; }
.post-media-grid .media-grid-item img { width: 100%; height: 100%; object-fit: cover; min-height: 150px; max-height: 350px; }
.post-media-grid .media-grid-video { position: relative; width: 100%; height: 100%; }
.post-media-grid .media-grid-video video { width: 100%; height: 100%; object-fit: cover; min-height: 150px; max-height: 350px; }
.post-media-grid .media-grid-file { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 16px; }
.post-media-grid .media-grid-file i { font-size: 28px; color: var(--primary); }
.post-media-grid .media-grid-file span { font-size: 11px; color: var(--text-secondary); word-break: break-all; }
.media-grid-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 28px; font-weight: 700; }
.post-actions { display: flex; gap: 16px; padding-top: 12px; border-top: 1px solid var(--border-light); }
.post-action-btn { font-size: 13px; color: var(--text-muted); text-decoration: none; display: flex; align-items: center; gap: 6px; cursor: pointer; transition: var(--transition); background: none; border: none; font-family: inherit; padding: 6px 10px; border-radius: 6px; }
.post-action-btn:hover { color: var(--primary); background: var(--bg-hover); }
.post-action-btn.liked { color: #ef4444; }

/* Comments title */
.comments-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }

/* Comments section wrapper */
.comments-section {
    margin-bottom: 30px;
    background: #ffffff;
    border: 1px solid var(--border-light);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    animation: fadeInUp 0.4s ease;
}
[data-theme="dark"] .comments-section { background: var(--bg-card); border-color: var(--border); }
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Comment form */
.comment-form-main { margin-bottom: 20px; }
.comment-form-row { display: flex; gap: 10px; align-items: flex-start; }
.comment-avatar img, .comment-avatar .avatar-mini { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
.comment-avatar-sm img, .comment-avatar-sm .avatar-mini { width: 28px; height: 28px; }
.avatar-mini { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; width: 36px; height: 36px; border-radius: 50%; }
.avatar-mini.sm { width: 28px; height: 28px; font-size: 11px; }
.comment-input-wrap { flex: 1; position: relative; display: flex; align-items: center; background: #f1f5f9; border-radius: 20px; padding: 4px 12px; border: 1px solid #e2e8f0; transition: border-color 0.2s, box-shadow 0.2s; }
[data-theme="dark"] .comment-input-wrap { background: var(--bg-hover); border-color: var(--border); }
.comment-input-wrap:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59,73,223,0.08); }
.comment-input { flex: 1; border: none; background: transparent; font-size: 13px; font-family: inherit; padding: 8px 4px; resize: none; outline: none; color: var(--text-primary); min-height: 20px; max-height: 120px; }
.comment-send { background: none; border: none; color: var(--primary); cursor: pointer; font-size: 14px; padding: 6px; border-radius: 50%; transition: background 0.2s; }
.comment-send:hover { background: var(--primary-bg); }

/* Comment items */
.comments-list { display: flex; flex-direction: column; gap: 4px; }
.comment-item { display: flex; gap: 10px; padding: 8px 0; animation: commentFadeIn 0.3s ease forwards; opacity: 0; }
.comment-item.sub { padding: 6px 0; }
.comment-item:nth-child(1) { animation-delay: 0.05s; }
.comment-item:nth-child(2) { animation-delay: 0.1s; }
.comment-item:nth-child(3) { animation-delay: 0.15s; }
.comment-item:nth-child(4) { animation-delay: 0.2s; }
.comment-item:nth-child(5) { animation-delay: 0.25s; }
.comment-item:nth-child(n+6) { animation-delay: 0.3s; }
@keyframes commentFadeIn {
    from { opacity: 0; transform: translateX(-8px); }
    to { opacity: 1; transform: translateX(0); }
}
.comment-body-wrap { flex: 1; min-width: 0; }
.comment-bubble { background: #ffffff; border: 1px solid var(--border-light); border-radius: 16px; padding: 10px 14px; display: inline-block; max-width: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.comment-bubble.sub-bubble { background: #f8fafc; border: 1px solid var(--border-light); }
[data-theme="dark"] .comment-bubble { background: var(--bg-card); border-color: var(--border); }
[data-theme="dark"] .comment-bubble.sub-bubble { background: var(--bg-hover); border-color: var(--border); }
.comment-author { font-size: 13px; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 2px; }
.comment-text { font-size: 13px; color: var(--text-secondary); line-height: 1.5; white-space: pre-wrap; word-break: break-word; }

/* Comment actions row */
.comment-actions { display: flex; align-items: center; gap: 4px; padding: 4px 8px; flex-wrap: wrap; }
.comment-act-btn { background: none; border: none; font-size: 12px; font-weight: 600; color: var(--text-muted); cursor: pointer; padding: 2px 6px; border-radius: 4px; font-family: inherit; transition: color 0.2s; }
.comment-act-btn:hover { color: var(--primary); }
.comment-act-btn.liked { color: #ef4444; font-weight: 700; }
.comment-act-btn.comment-delete:hover { color: #ef4444; }
.comment-time { font-size: 11px; color: var(--text-muted); }
.comment-edited { font-size: 10px; color: var(--text-muted); font-style: italic; }

/* Sub-replies */
.sub-comments { margin-top: 4px; padding-left: 0; }
.sub-reply-form { margin-top: 8px; }

/* Comment media */
.comment-media { margin-top: 8px; }
.comment-media-img { max-width: 100%; max-height: 250px; border-radius: 8px; object-fit: contain; cursor: pointer; }
.comment-media-video { max-width: 100%; max-height: 250px; border-radius: 8px; }
.comment-file-link { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: var(--bg-hover); border-radius: 6px; font-size: 12px; color: var(--primary); text-decoration: none; margin-top: 4px; }
.comment-file-link:hover { background: var(--primary-bg); }

/* Comment input group (textarea + preview) */
.comment-input-group { flex: 1; }

/* Attach button inside input */
.comment-attach-btn { cursor: pointer; color: var(--text-muted); font-size: 14px; padding: 6px; border-radius: 50%; transition: color 0.2s; display: flex; align-items: center; }
.comment-attach-btn:hover { color: #10b981; }

/* Comment media preview */
.comment-media-preview { margin-top: 6px; }
.comment-media-preview img { max-width: 120px; max-height: 80px; border-radius: 6px; object-fit: cover; }
.comment-media-preview video { max-width: 150px; max-height: 80px; border-radius: 6px; }
.comment-media-preview .cmp-file { font-size: 11px; color: var(--primary); display: flex; align-items: center; gap: 4px; }

/* Edit form */
.comment-edit-form { margin-top: 8px; }
.comment-edit-form .comment-input { width: 100%; padding: 10px 14px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; }

/* Responsive */
@media (max-width: 600px) {
    .comment-bubble { max-width: 100%; }
    .comment-actions { gap: 2px; }
    .comment-act-btn { font-size: 11px; padding: 2px 4px; }
}
</style>

<script>
function toggleReplyForm(replyId) {
    var form = document.getElementById('reply-form-' + replyId);
    if (form.style.display === 'none') {
        // Close all other reply forms
        document.querySelectorAll('.sub-reply-form').forEach(function(f) { f.style.display = 'none'; });
        form.style.display = 'block';
        form.querySelector('textarea').focus();
    } else {
        form.style.display = 'none';
    }
}

function toggleEditForm(replyId) {
    var form = document.getElementById('edit-form-' + replyId);
    if (form.style.display === 'none') {
        // Close all other edit forms
        document.querySelectorAll('.comment-edit-form').forEach(function(f) { f.style.display = 'none'; });
        form.style.display = 'block';
        form.querySelector('textarea').focus();
    } else {
        form.style.display = 'none';
    }
}

// Auto-resize textareas
document.querySelectorAll('.comment-input').forEach(function(textarea) {
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
});

// Preview media in comment forms
function previewCommentMedia(input, previewId) {
    var preview = document.getElementById(previewId);
    if (!preview) return;
    var file = input.files[0];
    if (!file) { preview.innerHTML = ''; return; }

    if (file.type.startsWith('image/')) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="preview">';
        };
        reader.readAsDataURL(file);
    } else if (file.type.startsWith('video/')) {
        var url = URL.createObjectURL(file);
        preview.innerHTML = '<video src="' + url + '" controls></video>';
    } else {
        preview.innerHTML = '<span class="cmp-file"><i class="fas fa-paperclip"></i> ' + file.name + '</span>';
    }
}
</script>
