<div class="page-header">
    <div>
        <h1><i class="fas fa-comments"></i> Forum Diskusi</h1>
        <p>Tempat berdiskusi, bertanya, dan berbagi ilmu</p>
    </div>
    <div style="display:flex;gap:8px;">
        <?php if (in_array($role, ['guru', 'wali_kelas', 'admin'])): ?>
            <a href="<?= url('forum/manage-categories') ?>" class="btn btn-secondary"><i class="fas fa-folder-plus"></i> Kategori</a>
        <?php endif; ?>
        <a href="<?= url('forum/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Diskusi</a>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card" style="margin-bottom: 20px; padding: 16px;">
    <form method="GET" action="<?= url('forum') ?>" class="forum-filters">
        <input type="hidden" name="route" value="forum">
        
        <!-- Search -->
        <div class="filter-row" style="margin-bottom:12px;">
            <div class="search-filter-wrap">
                <i class="fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Cari diskusi..." value="<?= e($filterSearch) ?>" style="padding-left:36px;">
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
        </div>

        <!-- Filters Row -->
        <div class="filter-row">
            <div class="filter-item">
                <label><i class="fas fa-folder"></i> Kategori</label>
                <select name="category_id" class="form-control">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $filterCategory == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['icon'] . ' ' . $cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-item">
                <label><i class="fas fa-book"></i> Mapel Terkait</label>
                <select name="subject_id" class="form-control">
                    <option value="">Semua Mapel</option>
                    <?php foreach ($allSubjects as $subj): ?>
                        <option value="<?= $subj['id'] ?>" <?= $filterSubject == $subj['id'] ? 'selected' : '' ?>>
                            <?= e($subj['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-item">
                <label><i class="fas fa-sort"></i> Urutkan</label>
                <select name="sort" class="form-control">
                    <option value="terbaru" <?= $filterSort === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
                    <option value="terlama" <?= $filterSort === 'terlama' ? 'selected' : '' ?>>Terlama</option>
                    <option value="populer" <?= $filterSort === 'populer' ? 'selected' : '' ?>>Populer</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <?php if ($filterCategory || $filterSubject || $filterSort !== 'terbaru' || $filterSearch): ?>
                    <a href="<?= url('forum') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i> Reset</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<div class="forum-layout">
    <!-- Main Feed -->
    <div class="forum-feed">
        <!-- Create Post Shortcut -->
        <div class="card create-post-card">
            <a href="<?= url('forum/create') ?>" class="create-post-trigger">
                <div class="create-avatar">
                    <?php $cu = Session::user(); ?>
                    <?php if ($cu['avatar']): ?>
                        <img src="<?= upload_url($cu['avatar']) ?>" alt="">
                    <?php else: ?>
                        <div class="avatar-sm"><?= strtoupper(substr($cu['full_name'], 0, 1)) ?></div>
                    <?php endif; ?>
                </div>
                <span class="create-placeholder">Apa yang ingin Anda diskusikan, <?= e(explode(' ', $cu['full_name'])[0]) ?>?</span>
            </a>
        </div>

        <?php if ($filterSearch && empty($recentPosts)): ?>
            <div class="card" style="padding:40px;text-align:center;">
                <i class="fas fa-search" style="font-size:48px;color:var(--text-muted);margin-bottom:16px;"></i>
                <h3 style="color:var(--text-primary);margin-bottom:8px;">Tidak Ditemukan</h3>
                <p style="color:var(--text-muted);font-size:13px;">Tidak ada diskusi yang cocok dengan pencarian "<?= e($filterSearch) ?>"</p>
            </div>
        <?php elseif (empty($recentPosts)): ?>
            <div class="card" style="padding:40px;text-align:center;">
                <i class="fas fa-comments" style="font-size:48px;color:var(--text-muted);margin-bottom:16px;"></i>
                <h3 style="color:var(--text-primary);margin-bottom:8px;">Belum Ada Diskusi</h3>
                <p style="color:var(--text-muted);font-size:13px;">Jadilah yang pertama memulai diskusi!</p>
            </div>
        <?php else: ?>
            <?php foreach ($recentPosts as $post): ?>
                <div class="card feed-post">
                    <div class="feed-post-header">
                        <div class="feed-author-avatar">
                            <?php if ($post['avatar']): ?>
                                <img src="<?= upload_url($post['avatar']) ?>" alt="">
                            <?php else: ?>
                                <div class="avatar-sm"><?= strtoupper(substr($post['full_name'], 0, 1)) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="feed-author-info">
                            <span class="feed-author-name"><a href="<?= url('profile/view/' . $post['author_id']) ?>" style="color:inherit;text-decoration:none;font-weight:700;"><?= e($post['full_name']) ?></a></span>
                            <div class="feed-meta">
                                <?php 
                                $className = $post['author_class_name'] ?? $post['author_class_fallback'] ?? '';
                                if ($className): ?>
                                    <span style="font-size:11px;color:var(--text-muted);">Anggota kelas <?= e($className) ?></span> &bull;
                                <?php endif; ?>
                                <span><?= time_ago($post['created_at']) ?></span>
                                <span class="feed-tag"><?= e($post['category_name']) ?></span>
                                <?php if (!empty($post['subject_name'])): ?>
                                    <span class="feed-tag" style="background:#fef3c7;color:#d97706;"><?= e($post['subject_name']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($post['is_pinned']): ?>
                            <span style="font-size:14px;" title="Pinned">&#128204;</span>
                        <?php endif; ?>
                    </div>
                    <a href="<?= url('forum/post/' . $post['id']) ?>" class="feed-post-body">
                        <h3 class="feed-title"><?= e($post['title']) ?></h3>
                        <p class="feed-excerpt"><?= e(truncate($post['content'], 200)) ?></p>
                    </a>
                    <?php if (!empty($post['attachment'])): ?>
                        <?php
                        $attachments = explode('|', $post['attachment']);
                        $mediaCount = count($attachments);
                        $gridClass = 'media-grid media-grid-' . min($mediaCount, 4);
                        ?>
                        <div class="feed-media-grid <?= $gridClass ?>">
                            <?php foreach (array_slice($attachments, 0, 4) as $idx => $att): ?>
                                <?php
                                $ext = strtolower(pathinfo($att, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                $isVideo = in_array($ext, ['mp4','webm']);
                                ?>
                                <a href="<?= url('forum/post/' . $post['id']) ?>" class="media-grid-item">
                                    <?php if ($isImage): ?>
                                        <img src="<?= upload_url($att) ?>" alt="media">
                                    <?php elseif ($isVideo): ?>
                                        <div class="media-grid-video">
                                            <video src="<?= upload_url($att) ?>" preload="metadata"></video>
                                            <div class="media-play-overlay"><i class="fas fa-play"></i></div>
                                        </div>
                                    <?php else: ?>
                                        <div class="media-grid-file">
                                            <i class="fas fa-file"></i>
                                            <span><?= e(basename($att)) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($idx === 3 && $mediaCount > 4): ?>
                                        <div class="media-grid-overlay">+<?= $mediaCount - 4 ?></div>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="feed-post-footer">
                        <form id="like-form-<?= $post['id'] ?>" method="POST" action="<?= url('forum/like/' . $post['id']) ?>" style="display:none;">
                            <?= csrf_field() ?>
                        </form>
                        <button type="button" class="like-btn-preview <?= in_array($post['id'], $userLikedPosts) ? 'liked' : '' ?>" data-post-id="<?= $post['id'] ?>">
                            <i class="fas fa-heart"></i> <span class="like-count"><?= $post['like_count'] ?></span>
                        </button>
                        <a href="<?= url('forum/post/' . $post['id']) ?>"><i class="fas fa-comment"></i> <?= $post['reply_count'] ?> Komentar</a>
                        <span><i class="fas fa-eye"></i> <?= $post['views'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Sidebar: Categories -->
    <div class="forum-sidebar">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Kategori</h3></div>
            <div class="cat-list">
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= url('forum?category_id=' . $cat['id']) ?>" class="cat-list-item <?= $filterCategory == $cat['id'] ? 'active' : '' ?>">
                        <span class="cat-list-icon"><?= $cat['icon'] ?></span>
                        <span class="cat-list-name"><?= e($cat['name']) ?></span>
                        <span class="cat-list-count"><?= $cat['post_count'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.forum-layout { display: grid; grid-template-columns: 1fr 280px; gap: 20px; }
@media (max-width: 900px) { .forum-layout { grid-template-columns: 1fr; } .forum-sidebar { order: -1; } }

.forum-feed { display: flex; flex-direction: column; gap: 16px; }

/* Search bar */
.search-filter-wrap { position: relative; flex: 1; }
.search-filter-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 13px; }
.search-filter-wrap input { padding-left: 36px !important; }

/* Create post shortcut */
.create-post-card { padding: 16px; }
.create-post-trigger { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.create-avatar img, .create-avatar .avatar-sm { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
.create-avatar .avatar-sm { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; }
.create-placeholder {
    flex: 1; padding: 10px 16px; background: var(--bg-hover); border-radius: 20px;
    font-size: 13px; color: var(--text-muted); transition: var(--transition);
}
.create-post-trigger:hover .create-placeholder { background: var(--border); color: var(--text-secondary); }

/* Feed post card */
.feed-post { padding: 0; overflow: hidden; }
.feed-post-header { display: flex; align-items: center; gap: 12px; padding: 16px 16px 0; }
.feed-author-avatar img, .feed-author-avatar .avatar-sm { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
.feed-author-avatar .avatar-sm { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; }
.feed-author-info { flex: 1; }
.feed-author-name { font-size: 14px; font-weight: 700; color: var(--text-primary); display: block; }
.feed-meta { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.feed-meta span { font-size: 11px; color: var(--text-muted); }
.feed-tag { background: var(--primary-bg); color: var(--primary); padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; }

.feed-post-body { display: block; padding: 12px 16px; text-decoration: none; transition: background 0.2s; }
.feed-post-body:hover { background: var(--bg-hover); }
.feed-title { font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px; line-height: 1.4; }
.feed-excerpt { font-size: 13px; color: var(--text-secondary); line-height: 1.6; }

.feed-post-footer {
    display: flex; justify-content: space-around; padding: 10px 16px;
    border-top: 1px solid var(--border-light);
}
.feed-post-footer span, .feed-post-footer a, .feed-post-footer button {
    font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 5px;
    cursor: pointer; padding: 6px 12px; border-radius: 6px; transition: var(--transition);
    text-decoration: none; background: none; border: none; font-family: inherit;
}
.feed-post-footer span:hover, .feed-post-footer a:hover, .feed-post-footer button:hover { background: var(--bg-hover); color: var(--primary); }

/* Like button in preview */
.like-btn-preview { cursor: pointer; }
.like-btn-preview.liked { color: #ef4444 !important; }
.like-btn-preview.liked:hover { background: #fef2f2 !important; color: #ef4444 !important; }

/* Sidebar categories */
.cat-list { display: flex; flex-direction: column; }
.cat-list-item { display: flex; align-items: center; gap: 10px; padding: 10px 16px; text-decoration: none; transition: background 0.2s; border-bottom: 1px solid var(--border-light); }
.cat-list-item:last-child { border-bottom: none; }
.cat-list-item:hover { background: var(--bg-hover); }
.cat-list-item.active { background: var(--primary-bg); }
.cat-list-icon { font-size: 16px; }
.cat-list-name { flex: 1; font-size: 13px; color: var(--text-primary); font-weight: 500; }
.cat-list-count { font-size: 11px; color: var(--text-muted); background: var(--bg-hover); padding: 2px 8px; border-radius: 10px; }

/* Filter */
.forum-filters .filter-row { display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; }
.forum-filters .filter-item { display: flex; flex-direction: column; gap: 4px; min-width: 160px; }
.forum-filters .filter-item label { font-size: 11px; font-weight: 600; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }
.forum-filters .filter-item select { padding: 8px 12px; font-size: 13px; }
.forum-filters .filter-actions { display: flex; gap: 6px; align-items: flex-end; padding-bottom: 2px; }

@media (max-width: 600px) {
    .forum-filters .filter-row { flex-direction: column; align-items: stretch; }
    .forum-filters .filter-item { min-width: 100%; }
    .forum-filters .filter-actions { justify-content: flex-start; }
}

/* Media Grid (Facebook-style) */
.feed-media-grid { display: grid; gap: 3px; padding: 0 16px 12px; border-radius: 0; overflow: hidden; }
.feed-media-grid.media-grid-1 { grid-template-columns: 1fr; }
.feed-media-grid.media-grid-2 { grid-template-columns: 1fr 1fr; }
.feed-media-grid.media-grid-3 { grid-template-columns: 2fr 1fr; grid-template-rows: 1fr 1fr; }
.feed-media-grid.media-grid-3 .media-grid-item:first-child { grid-row: 1 / 3; }
.feed-media-grid.media-grid-4 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
.media-grid-item { position: relative; overflow: hidden; border-radius: 8px; background: var(--bg-hover); display: flex; align-items: center; justify-content: center; min-height: 120px; max-height: 300px; text-decoration: none; }
.media-grid-item img { width: 100%; height: 100%; object-fit: cover; min-height: 120px; max-height: 300px; }
.media-grid-video { position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
.media-grid-video video { width: 100%; height: 100%; object-fit: cover; min-height: 120px; max-height: 300px; }
.media-play-overlay { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 44px; height: 44px; background: rgba(0,0,0,0.6); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 16px; }
.media-grid-file { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 16px; text-align: center; }
.media-grid-file i { font-size: 28px; color: var(--primary); }
.media-grid-file span { font-size: 11px; color: var(--text-secondary); word-break: break-all; }
.media-grid-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 28px; font-weight: 700; }
</style>

<script>
// Like from preview - uses hidden form submission (no AJAX to avoid HTTP/HTTPS issues)
document.querySelectorAll('.like-btn-preview').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        var postId = this.dataset.postId;
        // Find and submit the hidden form for this post
        var form = document.getElementById('like-form-' + postId);
        if (form) {
            form.submit();
        }
    });
});
</script>
