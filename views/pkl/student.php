<div class="page-header">
    <div><h1><i class="fas fa-building"></i> PKL / Magang</h1><p>Kelola praktik kerja lapangan Anda</p></div>
    <?php if ($pkl && $pkl['status'] === 'active'): ?>
        <a href="<?= url('pkl/print/' . $pkl['id']) ?>" class="btn btn-primary"><i class="fas fa-print"></i> Cetak Laporan</a>
    <?php endif; ?>
</div>

<?php if (!$pkl): ?>
    <!-- Register PKL -->
    <div class="card" style="max-width: 600px;">
        <div class="card-header"><h3 class="card-title">Daftar PKL</h3></div>
        <form method="POST" action="<?= url('pkl/register') ?>">
            <?= csrf_field() ?>
            <div class="form-group"><label>Nama Perusahaan/Instansi</label><input type="text" name="company_name" class="form-control" required></div>
            <div class="form-group"><label>Alamat</label><textarea name="company_address" class="form-control" rows="2"></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group"><label>Nama Pembimbing</label><input type="text" name="supervisor_name" class="form-control"></div>
                <div class="form-group"><label>Telp Pembimbing</label><input type="text" name="supervisor_phone" class="form-control"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group"><label>Tanggal Mulai</label><input type="date" name="start_date" class="form-control" required></div>
                <div class="form-group"><label>Tanggal Selesai</label><input type="date" name="end_date" class="form-control" required></div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Daftar PKL</button>
        </form>
    </div>
<?php else: ?>
    <!-- PKL Info -->
    <div class="grid-2">
        <div>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <h3 class="card-title">Informasi PKL</h3>
                    <span class="badge badge-<?= $pkl['status'] === 'active' ? 'success' : ($pkl['status'] === 'pending' ? 'warning' : 'primary') ?>"><?= ucfirst($pkl['status']) ?></span>
                </div>
                <div class="pkl-info-grid">
                    <div><strong>Perusahaan:</strong> <?= e($pkl['company_name']) ?></div>
                    <div><strong>Alamat:</strong> <?= e($pkl['company_address']) ?></div>
                    <div><strong>Pembimbing:</strong> <?= e($pkl['supervisor_name']) ?></div>
                    <div><strong>Periode:</strong> <?= format_date($pkl['start_date']) ?> - <?= format_date($pkl['end_date']) ?></div>
                </div>
            </div>

            <!-- Add Journal -->
            <?php if ($pkl['status'] === 'active'): ?>
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Tambah Jurnal</h3></div>
                    <form method="POST" action="<?= url('pkl/journal/' . $pkl['id']) ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="form-group"><label>Tanggal</label><input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                        <div class="form-group"><label>Kegiatan</label><textarea name="activity" class="form-control" rows="3" required placeholder="Jelaskan kegiatan hari ini..."></textarea></div>
                        <div class="form-group"><label>Catatan Tambahan</label><input type="text" name="notes" class="form-control" placeholder="Opsional"></div>
                        <div class="form-group"><label>Foto Kegiatan (opsional)</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Simpan Jurnal</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Journals List -->
        <div>
            <div class="card">
                <div class="card-header"><h3 class="card-title">Jurnal PKL (<?= count($journals) ?> entri)</h3></div>
                <?php if (empty($journals)): ?>
                    <div class="empty-state" style="padding:20px;"><p style="color:var(--text-muted);">Belum ada jurnal.</p></div>
                <?php else: ?>
                    <div class="journal-list">
                        <?php foreach ($journals as $j): ?>
                            <div class="journal-item">
                                <div class="journal-date"><?= format_date($j['date'], 'd M') ?></div>
                                <div class="journal-content">
                                    <p><?= e($j['activity']) ?></p>
                                    <?php if ($j['notes']): ?><small><?= e($j['notes']) ?></small><?php endif; ?>
                                    <?php if ($j['photo']): ?>
                                        <img src="<?= upload_url($j['photo']) ?>" class="journal-photo">
                                    <?php endif; ?>
                                </div>
                                <?php if ($j['verified']): ?><span class="badge badge-success">✓</span><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
.pkl-info-grid { display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: var(--text-secondary); }
.pkl-info-grid strong { color: var(--text-primary); }
.journal-list { display: flex; flex-direction: column; }
.journal-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border-light); }
.journal-item:last-child { border-bottom: none; }
.journal-date { font-size: 12px; font-weight: 700; color: var(--primary); min-width: 50px; text-align: center; background: var(--primary-bg); padding: 4px 8px; border-radius: 6px; }
.journal-content { flex: 1; }
.journal-content p { font-size: 13px; color: var(--text-primary); }
.journal-content small { font-size: 11px; color: var(--text-muted); }
.journal-photo { max-width: 100%; max-height: 150px; border-radius: 8px; margin-top: 8px; }
</style>
