<div class="page-header">
    <div><h1><i class="fas fa-chart-line"></i> Nilai Saya</h1><p>Rekap nilai dari semua mata pelajaran</p></div>
</div>

<?php if (empty($grades)): ?>
    <div class="card"><div class="empty-state"><i class="fas fa-chart-bar"></i><h3>Belum Ada Nilai</h3></div></div>
<?php else: ?>
    <?php foreach ($bySubject as $subName => $subGrades): 
        $avg = count($subGrades) > 0 ? round(array_sum(array_column($subGrades, 'score')) / count($subGrades), 1) : 0;
    ?>
        <div class="card" style="margin-bottom: 16px;">
            <div class="card-header">
                <h3 class="card-title"><?= e($subName) ?></h3>
                <span class="badge badge-<?= $avg >= 75 ? 'success' : ($avg >= 60 ? 'warning' : 'danger') ?>">Rata-rata: <?= $avg ?></span>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>Jenis</th><th>Judul</th><th>Nilai</th><th>Tanggal</th></tr></thead>
                    <tbody>
                        <?php foreach ($subGrades as $g): ?>
                            <tr>
                                <td><span class="badge badge-primary"><?= ucfirst($g['type']) ?></span></td>
                                <td><?= e($g['title'] ?? '-') ?></td>
                                <td><strong style="color:<?= $g['score'] >= 75 ? 'var(--success)' : ($g['score'] >= 60 ? 'var(--warning)' : 'var(--danger)') ?>;"><?= $g['score'] ?></strong></td>
                                <td style="font-size:12px;color:var(--text-muted);"><?= format_date($g['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
