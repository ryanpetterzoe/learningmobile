<div class="page-header">
    <div>
        <h1><i class="fas fa-book-open"></i> Jurnal PKL</h1>
        <p><?= e($pkl['full_name']) ?> - <?= e($pkl['company_name']) ?></p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="<?= url('pkl/print/' . $pkl['id']) ?>" class="btn btn-primary"><i class="fas fa-print"></i> Cetak Laporan</a>
        <a href="<?= url('pkl') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

<!-- PKL Info Card -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><h3 class="card-title">Informasi PKL</h3></div>
    <div class="pkl-detail-grid">
        <div class="pkl-detail-item">
            <span class="pkl-detail-label">Siswa</span>
            <span class="pkl-detail-value"><?= e($pkl['full_name']) ?><?php if ($pkl['nis']): ?> (<?= e($pkl['nis']) ?>)<?php endif; ?></span>
        </div>
        <div class="pkl-detail-item">
            <span class="pkl-detail-label">Perusahaan</span>
            <span class="pkl-detail-value"><?= e($pkl['company_name']) ?></span>
        </div>
        <div class="pkl-detail-item">
            <span class="pkl-detail-label">Alamat</span>
            <span class="pkl-detail-value"><?= e($pkl['company_address'] ?: '-') ?></span>
        </div>
        <div class="pkl-detail-item">
            <span class="pkl-detail-label">Pembimbing</span>
            <span class="pkl-detail-value"><?= e($pkl['supervisor_name'] ?: '-') ?></span>
        </div>
        <div class="pkl-detail-item">
            <span class="pkl-detail-label">Periode</span>
            <span class="pkl-detail-value"><?= format_date($pkl['start_date']) ?> s/d <?= format_date($pkl['end_date']) ?></span>
        </div>
        <div class="pkl-detail-item">
            <span class="pkl-detail-label">Total Jurnal</span>
            <span class="pkl-detail-value"><strong><?= count($journals) ?></strong> entri</span>
        </div>
    </div>
</div>

<!-- Journals -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Jurnal (<?= count($journals) ?> entri)</h3>
    </div>
    <?php if (empty($journals)): ?>
        <div style="padding:40px;text-align:center;">
            <i class="fas fa-clipboard-list" style="font-size:40px;color:var(--text-muted);margin-bottom:12px;"></i>
            <p style="color:var(--text-muted);font-size:13px;">Siswa belum mengisi jurnal PKL.</p>
        </div>
    <?php else: ?>
        <div class="journal-table-wrap">
            <table class="journal-table">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th style="width:100px;">Tanggal</th>
                        <th>Kegiatan</th>
                        <th style="width:100px;">Catatan</th>
                        <th style="width:60px;">Foto</th>
                        <th style="width:80px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($journals as $idx => $j): ?>
                        <tr>
                            <td style="text-align:center;font-weight:600;color:var(--text-muted);"><?= $idx + 1 ?></td>
                            <td><span class="journal-date-badge"><?= date('d M Y', strtotime($j['date'])) ?></span></td>
                            <td style="font-size:13px;color:var(--text-primary);line-height:1.5;"><?= e($j['activity']) ?></td>
                            <td style="font-size:12px;color:var(--text-muted);"><?= e($j['notes'] ?: '-') ?></td>
                            <td style="text-align:center;">
                                <?php if ($j['photo']): ?>
                                    <a href="<?= upload_url($j['photo']) ?>" target="_blank" title="Lihat Foto">
                                        <img src="<?= upload_url($j['photo']) ?>" style="width:36px;height:36px;border-radius:6px;object-fit:cover;">
                                    </a>
                                <?php else: ?>
                                    <span style="color:var(--text-muted);font-size:11px;">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if ($j['verified']): ?>
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Verified</span>
                                <?php else: ?>
                                    <form method="POST" action="<?= url('pkl/journals/' . $pkl['id']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="verify_journal_id" value="<?= $j['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success" title="Verifikasi"><i class="fas fa-check"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.pkl-detail-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 12px; }
.pkl-detail-item { padding: 10px 14px; background: var(--bg-hover); border-radius: 8px; }
.pkl-detail-label { display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; text-transform: uppercase; }
.pkl-detail-value { font-size: 13px; color: var(--text-primary); }
.journal-table-wrap { overflow-x: auto; }
.journal-table { width: 100%; border-collapse: collapse; }
.journal-table th, .journal-table td { padding: 12px 14px; text-align: left; border-bottom: 1px solid var(--border-light); font-size: 13px; }
.journal-table th { background: var(--bg-hover); font-weight: 600; color: var(--text-secondary); font-size: 12px; }
.journal-table tr:hover { background: var(--bg-hover); }
.journal-date-badge { font-size: 11px; font-weight: 600; color: var(--primary); background: var(--primary-bg); padding: 3px 8px; border-radius: 6px; white-space: nowrap; }
</style>
