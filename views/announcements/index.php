<div class="page-header">
    <div><h1><i class="fas fa-bullhorn"></i> Pengumuman</h1><p>Informasi dan pengumuman terbaru dari sekolah</p></div>
    <?php if (Auth::isAdmin()): ?>
        <a href="<?= url('admin/announcements') ?>" class="btn btn-primary"><i class="fas fa-cog"></i> Kelola</a>
    <?php endif; ?>
</div>

<?php if (empty($announcements)): ?>
    <div class="card"><div class="empty-state"><i class="fas fa-bullhorn"></i><h3>Belum Ada Pengumuman</h3></div></div>
<?php else: ?>
    <?php foreach ($announcements as $ann): ?>
        <div class="card" style="margin-bottom: 16px;">
            <div style="display:flex;align-items:flex-start;gap:12px;">
                <?php if ($ann['author_avatar']): ?>
                    <img src="<?= upload_url($ann['author_avatar']) ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                    <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;"><?= strtoupper(substr($ann['author_name'],0,1)) ?></div>
                <?php endif; ?>
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                        <span style="font-weight:600;font-size:13px;color:var(--text-primary);"><?= e($ann['author_name']) ?></span>
                        <span style="font-size:11px;color:var(--text-muted);"><?= time_ago($ann['created_at']) ?></span>
                        <?php if ($ann['is_pinned']): ?><span class="badge badge-warning">📌 Pinned</span><?php endif; ?>
                    </div>
                    <h3 style="font-size:16px;margin-bottom:8px;color:var(--text-primary);"><?= e($ann['title']) ?></h3>
                    <p style="font-size:14px;color:var(--text-secondary);line-height:1.7;"><?= nl2br(e($ann['content'])) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
