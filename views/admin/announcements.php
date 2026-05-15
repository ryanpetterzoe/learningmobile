<div class="page-header">
    <div>
        <h1><i class="fas fa-bullhorn"></i> Kelola Pengumuman</h1>
        <p>Buat dan kelola pengumuman sekolah</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('createAnnModal')"><i class="fas fa-plus"></i> Buat Pengumuman</button>
</div>

<!-- Announcements List -->
<div class="card">
    <?php if (empty($announcements)): ?>
        <div class="empty-state">
            <i class="fas fa-bullhorn"></i>
            <h3>Belum Ada Pengumuman</h3>
            <p>Buat pengumuman pertama untuk dilihat seluruh warga sekolah.</p>
        </div>
    <?php else: ?>
        <?php foreach ($announcements as $ann): ?>
            <div class="ann-row">
                <div class="ann-content">
                    <div class="ann-meta">
                        <?php if ($ann['is_pinned']): ?>
                            <span class="badge badge-warning"><i class="fas fa-thumbtack"></i> Pinned</span>
                        <?php endif; ?>
                        <span class="badge badge-primary"><?= ucfirst($ann['target']) ?></span>
                        <span style="font-size: 11px; color: var(--text-muted);"><?= format_datetime($ann['created_at']) ?></span>
                    </div>
                    <h4><?= e($ann['title']) ?></h4>
                    <p><?= e(truncate(strip_tags($ann['content']), 150)) ?></p>
                    <span style="font-size: 11px; color: var(--text-muted);">Oleh: <?= e($ann['author_name']) ?></span>
                </div>
                <form method="POST" action="<?= url('admin/announcement-delete/' . $ann['id']) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" data-confirm="Hapus pengumuman ini?"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Create Announcement Modal -->
<div class="modal-overlay" id="createAnnModal">
    <div class="modal" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Buat Pengumuman</h3>
            <button class="modal-close" onclick="closeModal('createAnnModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="<?= url('admin/announcements') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label>Judul Pengumuman</label>
                    <input type="text" name="title" class="form-control" placeholder="Judul pengumuman..." required>
                </div>
                <div class="form-group">
                    <label>Isi Pengumuman</label>
                    <textarea name="content" class="form-control" rows="5" placeholder="Tulis isi pengumuman..." required></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Target</label>
                        <select name="target" class="form-control">
                            <option value="all">Semua</option>
                            <option value="role">Role Tertentu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Target Role (opsional)</label>
                        <select name="target_role" class="form-control">
                            <option value="">-- Semua --</option>
                            <option value="siswa">Siswa</option>
                            <option value="guru">Guru</option>
                            <option value="orang_tua">Orang Tua</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_pinned" style="accent-color: var(--primary);">
                        <span>Pin pengumuman ini (tampil di atas)</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createAnnModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Publikasikan</button>
            </div>
        </form>
    </div>
</div>

<style>
.ann-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid var(--border-light);
}
.ann-row:last-child { border-bottom: none; }
.ann-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.ann-content h4 { font-size: 14px; color: var(--text-primary); margin-bottom: 4px; }
.ann-content p { font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 4px; }
</style>
