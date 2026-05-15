<div class="page-header">
    <div>
        <h1><i class="fas fa-folder-plus"></i> Kelola Kategori Forum</h1>
        <p>Tambah atau hapus kategori diskusi</p>
    </div>
    <a href="<?= url('forum') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="grid-2">
    <!-- Existing Categories -->
    <div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Daftar Kategori</h3></div>
            <?php if (empty($categories)): ?>
                <div class="empty-state" style="padding:30px;"><i class="fas fa-folder-open"></i><p>Belum ada kategori.</p></div>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <div class="question-row" style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border-light);">
                        <span style="font-size:20px;"><?= $cat['icon'] ?></span>
                        <div style="flex:1;">
                            <span style="font-size:13px;font-weight:600;color:var(--text-primary);"><?= e($cat['name']) ?></span>
                            <span style="display:block;font-size:11px;color:var(--text-muted);"><?= e($cat['description'] ?? '') ?> • <?= $cat['post_count'] ?> diskusi</span>
                        </div>
                        <?php if ($cat['post_count'] == 0): ?>
                            <form method="POST" action="<?= url('forum/manage-categories') ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="cat_action" value="delete">
                                <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger" data-confirm="Hapus kategori ini?"><i class="fas fa-trash"></i></button>
                            </form>
                        <?php else: ?>
                            <span class="badge badge-warning" style="font-size:10px;">Tidak bisa dihapus</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Category Form -->
    <div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Tambah Kategori Baru</h3></div>
            <form method="POST" action="<?= url('forum/manage-categories') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="cat_action" value="create">
                <div class="form-group">
                    <label>Nama Kategori</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Matematika, Jaringan Komputer" required>
                </div>
                <div class="form-group">
                    <label>Emoji Icon</label>
                    <input type="text" name="icon" class="form-control" placeholder="Contoh: 💡 atau 📐" value="💬" style="max-width:100px;">
                    <small style="color:var(--text-muted);font-size:11px;">Gunakan satu emoji sebagai ikon</small>
                </div>
                <div class="form-group">
                    <label>Deskripsi (opsional)</label>
                    <input type="text" name="description" class="form-control" placeholder="Deskripsi singkat kategori...">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Kategori</button>
            </form>
        </div>
    </div>
</div>
