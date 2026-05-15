<div class="page-header">
    <div><h1><i class="fas fa-certificate"></i> Sertifikat Kompetensi</h1><p>Upload dan kelola sertifikat Anda</p></div>
    <button class="btn btn-primary" onclick="openModal('addCertModal')"><i class="fas fa-plus"></i> Tambah Sertifikat</button>
</div>

<?php if (empty($certificates)): ?>
    <div class="card"><div class="empty-state"><i class="fas fa-award"></i><h3>Belum Ada Sertifikat</h3><p>Upload sertifikat kompetensi Anda.</p></div></div>
<?php else: ?>
    <div class="cert-grid">
        <?php foreach ($certificates as $c): ?>
            <div class="cert-card">
                <div class="cert-icon"><i class="fas fa-award"></i></div>
                <div class="cert-body">
                    <h4><?= e($c['title']) ?></h4>
                    <p><?= e($c['issuer']) ?></p>
                    <small><?= format_date($c['issued_date']) ?></small>
                </div>
                <div class="cert-actions">
                    <?php if ($c['file_path']): ?>
                        <a href="<?= upload_url($c['file_path']) ?>" target="_blank" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a>
                    <?php endif; ?>
                    <form method="POST" action="<?= url('certificates/delete/' . $c['id']) ?>" style="display:inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-danger" data-confirm="Hapus?"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="modal-overlay" id="addCertModal">
    <div class="modal">
        <div class="modal-header"><h3>Tambah Sertifikat</h3><button class="modal-close" onclick="closeModal('addCertModal')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="<?= url('certificates/add') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group"><label>Nama Sertifikat</label><input type="text" name="title" class="form-control" required></div>
                <div class="form-group"><label>Penerbit</label><input type="text" name="issuer" class="form-control" placeholder="LSP, BNSP, dll"></div>
                <div class="form-group"><label>Tanggal Terbit</label><input type="date" name="issued_date" class="form-control"></div>
                <div class="form-group"><label>Deskripsi</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="form-group"><label>File Sertifikat</label><input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addCertModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
            </div>
        </form>
    </div>
</div>

<style>
.cert-grid { display: flex; flex-direction: column; gap: 12px; }
.cert-card { display: flex; align-items: center; gap: 16px; padding: 16px 20px; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); transition: var(--transition); }
.cert-card:hover { border-color: var(--primary); }
.cert-icon { width: 44px; height: 44px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; }
.cert-body { flex: 1; }
.cert-body h4 { font-size: 14px; color: var(--text-primary); }
.cert-body p { font-size: 12px; color: var(--text-secondary); }
.cert-body small { font-size: 11px; color: var(--text-muted); }
.cert-actions { display: flex; gap: 6px; }
</style>
