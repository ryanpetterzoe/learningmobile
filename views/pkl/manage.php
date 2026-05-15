<div class="page-header">
    <div><h1><i class="fas fa-building"></i> Kelola PKL</h1><p>Monitor dan kelola data PKL siswa</p></div>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead><tr><th>Siswa</th><th>Perusahaan</th><th>Periode</th><th>Jurnal</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php if (empty($pklList)): ?>
                    <tr><td colspan="6" class="empty-state" style="padding:30px;text-align:center;color:var(--text-muted);">Belum ada data PKL.</td></tr>
                <?php endif; ?>
                <?php foreach ($pklList as $p): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <?php if ($p['avatar']): ?>
                                    <img src="<?= upload_url($p['avatar']) ?>" style="width:30px;height:30px;border-radius:50%;object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;"><?= strtoupper(substr($p['full_name'],0,1)) ?></div>
                                <?php endif; ?>
                                <div>
                                    <span style="font-size:13px;font-weight:600;display:block;"><?= e($p['full_name']) ?></span>
                                    <?php if ($p['nis']): ?><small style="color:var(--text-muted);"><?= e($p['nis']) ?></small><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:13px;">
                            <strong><?= e($p['company_name']) ?></strong>
                            <?php if ($p['supervisor_name']): ?>
                                <br><small style="color:var(--text-muted);">Pembimbing: <?= e($p['supervisor_name']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);"><?= format_date($p['start_date']) ?> - <?= format_date($p['end_date']) ?></td>
                        <td>
                            <a href="<?= url('pkl/journals/' . $p['id']) ?>" class="btn btn-sm btn-secondary" title="Lihat Jurnal">
                                <i class="fas fa-book-open"></i> Jurnal
                            </a>
                        </td>
                        <td><span class="badge badge-<?= $p['status']==='active'?'success':($p['status']==='pending'?'warning':'primary') ?>"><?= ucfirst($p['status']) ?></span></td>
                        <td>
                            <?php if ($p['status'] === 'pending'): ?>
                                <form method="POST" action="<?= url('pkl/approve/' . $p['id']) ?>" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-success" title="Setujui"><i class="fas fa-check"></i> Setujui</button>
                                </form>
                            <?php else: ?>
                                <a href="<?= url('pkl/print/' . $p['id']) ?>" class="btn btn-sm btn-primary" title="Cetak Laporan"><i class="fas fa-print"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
