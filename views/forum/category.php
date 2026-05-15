<div class="page-header">
    <div>
        <h1><?= $category['icon'] ?> <?= e($category['name']) ?></h1>
        <p><?= e($category['description'] ?? '') ?></p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="<?= url('forum') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        <a href="<?= url('forum/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Diskusi</a>
    </div>
</div>

<div class="card">
    <?php if (empty($posts)): ?>
        <div class="empty-state"><i class="fas fa-comments"></i><h3>Belum Ada Diskusi</h3><p>Jadilah yang pertama memulai diskusi di kategori ini!</p></div>
    <?php else: ?>
        <div class="posts-list">
            <?php foreach ($posts as $post): ?>
                <a href="<?= url('forum/post/' . $post['id']) ?>" class="post-row">
                    <div class="post-author-avatar">
                        <?php if ($post['avatar']): ?>
                            <img src="<?= upload_url($post['avatar']) ?>" alt="">
                        <?php else: ?>
                            <div class="avatar-sm"><?= strtoupper(substr($post['full_name'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="post-main">
                        <div class="post-title-row">
                            <?php if ($post['is_pinned']): ?><span>📌</span><?php endif; ?>
                            <h4><?= e($post['title']) ?></h4>
                        </div>
                        <div class="post-meta-row">
                            <span class="post-author"><?= e($post['full_name']) ?></span>
                            <span class="post-time"><?= time_ago($post['created_at']) ?></span>
                        </div>
                    </div>
                    <div class="post-stats">
                        <span><i class="fas fa-comment"></i> <?= $post['reply_count'] ?></span>
                        <span><i class="fas fa-eye"></i> <?= $post['views'] ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.posts-list { display: flex; flex-direction: column; }
.post-row { display: flex; align-items: center; gap: 14px; padding: 14px 0; border-bottom: 1px solid var(--border-light); text-decoration: none; transition: var(--transition); }
.post-row:last-child { border-bottom: none; }
.post-row:hover { background: var(--bg-hover); padding-left: 8px; border-radius: var(--radius-sm); }
.post-author-avatar img, .avatar-sm { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }
.avatar-sm { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; }
.post-main { flex: 1; }
.post-title-row { display: flex; align-items: center; gap: 6px; margin-bottom: 4px; }
.post-title-row h4 { font-size: 14px; color: var(--text-primary); }
.post-meta-row { display: flex; align-items: center; gap: 8px; }
.post-author { font-size: 12px; color: var(--text-secondary); font-weight: 500; }
.post-time { font-size: 11px; color: var(--text-muted); }
.post-stats { display: flex; gap: 12px; }
.post-stats span { font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }
</style>
