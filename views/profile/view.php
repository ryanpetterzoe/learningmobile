<div class="profile-public-page">
    <!-- Cover & Avatar -->
    <div class="profile-cover-area">
        <div class="profile-cover-bg"></div>
        <div class="profile-main-info">
            <div class="profile-avatar-large">
                <?php if ($profileUser['avatar']): ?>
                    <img src="<?= upload_url($profileUser['avatar']) ?>" alt="Avatar">
                <?php else: ?>
                    <div class="avatar-large-placeholder"><?= strtoupper(substr($profileUser['full_name'], 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            <div class="profile-name-area">
                <h1><?= e($profileUser['full_name']) ?></h1>
                <span class="profile-role-badge"><?= ucfirst(e($profileUser['role'])) ?></span>
                <?php if ($profileUser['class_name']): ?>
                    <span class="profile-class-badge"><i class="fas fa-school"></i> <?= e($profileUser['class_name']) ?></span>
                <?php endif; ?>
                <?php if (!empty($profileUser['competency_name'])): ?>
                    <span class="profile-class-badge"><i class="fas fa-graduation-cap"></i> <?= e($profileUser['competency_name']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bio -->
    <?php if (!empty($profileUser['bio'])): ?>
        <div class="card profile-bio-card">
            <p><?= nl2br(e($profileUser['bio'])) ?></p>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="profile-stats-row">
        <div class="profile-stat-item">
            <span class="stat-number"><?= $postCount ?></span>
            <span class="stat-label">Postingan</span>
        </div>
        <div class="profile-stat-item">
            <span class="stat-number"><?= $commentCount ?></span>
            <span class="stat-label">Komentar</span>
        </div>
        <div class="profile-stat-item">
            <span class="stat-number"><?= number_format($profileUser['xp_points']) ?></span>
            <span class="stat-label">XP</span>
        </div>
        <div class="profile-stat-item">
            <span class="stat-number"><?= $profileUser['level'] ?></span>
            <span class="stat-label">Level</span>
        </div>
    </div>

    <!-- User's Posts -->
    <div class="profile-posts-section">
        <h3><i class="fas fa-comments"></i> Postingan oleh <?= e(explode(' ', $profileUser['full_name'])[0]) ?></h3>
        <?php if (empty($userPosts)): ?>
            <div class="card" style="padding:30px;text-align:center;">
                <p style="color:var(--text-muted);font-size:13px;">Belum ada postingan.</p>
            </div>
        <?php else: ?>
            <?php foreach ($userPosts as $post): ?>
                <div class="card profile-post-card">
                    <a href="<?= url('forum/post/' . $post['id']) ?>" class="profile-post-link">
                        <h4><?= e($post['title']) ?></h4>
                        <p><?= e(truncate($post['content'], 150)) ?></p>
                        <div class="profile-post-meta">
                            <span class="feed-tag"><?= e($post['category_name']) ?></span>
                            <span><i class="fas fa-heart"></i> <?= $post['like_count'] ?></span>
                            <span><i class="fas fa-comment"></i> <?= $post['reply_count'] ?></span>
                            <span><i class="fas fa-clock"></i> <?= time_ago($post['created_at']) ?></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.profile-public-page { max-width: 700px; margin: 0 auto; }
.profile-cover-area { position: relative; margin-bottom: 20px; }
.profile-cover-bg {
    height: 160px;
    background: linear-gradient(135deg, var(--primary), #6366f1, #a855f7);
    border-radius: 16px 16px 0 0;
}
.profile-main-info {
    display: flex;
    align-items: flex-end;
    gap: 16px;
    padding: 0 24px;
    margin-top: -40px;
    position: relative;
}
.profile-avatar-large img, .avatar-large-placeholder {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--bg-card);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.avatar-large-placeholder {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: 700;
}
.profile-name-area { padding-bottom: 12px; }
.profile-name-area h1 { font-size: 22px; font-weight: 800; color: var(--text-primary); margin: 0; line-height: 1.3; }
.profile-role-badge {
    display: inline-block;
    padding: 2px 10px;
    background: var(--primary-bg);
    color: var(--primary);
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    margin-right: 6px;
}
.profile-class-badge {
    display: inline-block;
    padding: 2px 10px;
    background: #fef3c7;
    color: #d97706;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    margin-right: 6px;
}
.profile-bio-card { padding: 16px 20px; margin-bottom: 16px; }
.profile-bio-card p { font-size: 14px; color: var(--text-secondary); line-height: 1.7; margin: 0; }

.profile-stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
.profile-stat-item {
    background: var(--bg-card);
    border: 1px solid var(--border-light);
    border-radius: 12px;
    padding: 16px;
    text-align: center;
}
.profile-stat-item .stat-number { display: block; font-size: 22px; font-weight: 800; color: var(--primary); }
.profile-stat-item .stat-label { display: block; font-size: 11px; color: var(--text-muted); margin-top: 2px; }

.profile-posts-section h3 {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.profile-post-card { padding: 16px; margin-bottom: 10px; }
.profile-post-link { text-decoration: none; display: block; }
.profile-post-link h4 { font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px; }
.profile-post-link p { font-size: 13px; color: var(--text-secondary); margin-bottom: 8px; line-height: 1.5; }
.profile-post-meta { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.profile-post-meta span { font-size: 11px; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }
.profile-post-link:hover h4 { color: var(--primary); }

@media (max-width: 600px) {
    .profile-stats-row { grid-template-columns: repeat(2, 1fr); }
    .profile-main-info { flex-direction: column; align-items: center; text-align: center; }
}
</style>
