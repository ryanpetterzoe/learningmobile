<div class="page-header">
    <div><h1><i class="fas fa-folder-open"></i> Portofolio Saya</h1><p>Showcase karya dan proyek terbaik Anda</p></div>
    <button class="btn btn-primary" onclick="openModal('addPortfolioModal')"><i class="fas fa-plus"></i> Tambah Karya</button>
</div>

<?php if (empty($portfolios)): ?>
    <div class="card"><div class="empty-state"><i class="fas fa-palette"></i><h3>Belum Ada Portofolio</h3><p>Mulai upload karya terbaik Anda!</p></div></div>
<?php else: ?>
    <div class="portfolio-grid">
        <?php foreach ($portfolios as $p): ?>
            <div class="portfolio-card">
                <?php if ($p['thumbnail']): ?>
                    <div class="portfolio-thumb"><img src="<?= upload_url($p['thumbnail']) ?>" alt=""></div>
                <?php else: ?>
                    <div class="portfolio-thumb no-img"><i class="fas fa-image"></i></div>
                <?php endif; ?>
                <div class="portfolio-body">
                    <h4><?= e($p['title']) ?></h4>
                    <?php if ($p['category']): ?><span class="badge badge-primary"><?= e($p['category']) ?></span><?php endif; ?>
                    <p><?= e(truncate($p['description'], 80)) ?></p>
                    <div class="portfolio-actions">
                        <?php if ($p['file_path']): ?><a href="<?= upload_url($p['file_path']) ?>" target="_blank" class="btn btn-sm btn-secondary"><i class="fas fa-download"></i></a><?php endif; ?>
                        <?php if ($p['link_url']): ?><a href="<?= e($p['link_url']) ?>" target="_blank" class="btn btn-sm btn-secondary"><i class="fas fa-external-link-alt"></i></a><?php endif; ?>
                        <form method="POST" action="<?= url('portfolio/delete/' . $p['id']) ?>" style="display:inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger" data-confirm="Hapus?"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Add Modal -->
<div class="modal-overlay" id="addPortfolioModal">
    <div class="modal">
        <div class="modal-header"><h3>Tambah Portofolio</h3><button class="modal-close" onclick="closeModal('addPortfolioModal')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="<?= url('portfolio/create') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group"><label>Judul Karya</label><input type="text" name="title" class="form-control" required></div>
                <div class="form-group"><label>Kategori</label><input type="text" name="category" class="form-control" placeholder="Web, Desain, Mobile, dll"></div>
                <div class="form-group"><label>Deskripsi</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                <div class="form-group"><label>Link URL (opsional)</label><input type="url" name="link_url" class="form-control" placeholder="https://..."></div>
                <div class="form-group"><label>File Karya</label><input type="file" name="file" class="form-control"></div>
                <div class="form-group"><label>Thumbnail (gambar)</label><input type="file" name="thumbnail" class="form-control" accept="image/*"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addPortfolioModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
            </div>
        </form>
    </div>
</div>

<style>
.portfolio-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.portfolio-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; transition: var(--transition); }
.portfolio-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
.portfolio-thumb { height: 160px; overflow: hidden; }
.portfolio-thumb img { width: 100%; height: 100%; object-fit: cover; }
.portfolio-thumb.no-img { background: var(--bg-hover); display: flex; align-items: center; justify-content: center; }
.portfolio-thumb.no-img i { font-size: 32px; color: var(--text-muted); }
.portfolio-body { padding: 16px; }
.portfolio-body h4 { font-size: 14px; color: var(--text-primary); margin-bottom: 6px; }
.portfolio-body p { font-size: 12px; color: var(--text-muted); margin-top: 6px; }
.portfolio-actions { display: flex; gap: 6px; margin-top: 12px; }
</style>
