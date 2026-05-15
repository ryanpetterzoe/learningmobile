<div class="page-header">
    <div>
        <h1><i class="fas fa-chart-bar"></i> Hasil Quiz: <?= e($quiz['title']) ?></h1>
        <p><?= e($quiz['subject_name']) ?> • <?= e($quiz['class_name']) ?></p>
    </div>
    <a href="<?= url('quiz') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<!-- Stats Cards -->
<div class="stats-row" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:12px; margin-bottom:20px;">
    <div class="card" style="padding:16px; text-align:center;">
        <p style="font-size:24px; font-weight:800; color:var(--primary);"><?= $stats['total'] ?></p>
        <p style="font-size:11px; color:var(--text-muted);">Total Peserta</p>
    </div>
    <div class="card" style="padding:16px; text-align:center;">
        <p style="font-size:24px; font-weight:800; color:#10b981;"><?= $stats['avg_score'] ?>%</p>
        <p style="font-size:11px; color:var(--text-muted);">Rata-rata</p>
    </div>
    <div class="card" style="padding:16px; text-align:center;">
        <p style="font-size:24px; font-weight:800; color:#f59e0b;"><?= $stats['max_score'] ?>%</p>
        <p style="font-size:11px; color:var(--text-muted);">Nilai Tertinggi</p>
    </div>
    <div class="card" style="padding:16px; text-align:center;">
        <p style="font-size:24px; font-weight:800; color:#ef4444;"><?= $stats['min_score'] ?>%</p>
        <p style="font-size:11px; color:var(--text-muted);">Nilai Terendah</p>
    </div>
    <div class="card" style="padding:16px; text-align:center;">
        <p style="font-size:24px; font-weight:800; color:#10b981;"><?= $stats['passed'] ?>/<?= $stats['total'] ?></p>
        <p style="font-size:11px; color:var(--text-muted);">Lulus (>= <?= $quiz['passing_score'] ?>%)</p>
    </div>
</div>

<!-- Results Table -->
<div class="card">
    <div class="card-header"><h3 class="card-title">Daftar Nilai Peserta</h3></div>
    <?php if (empty($attempts)): ?>
        <div class="empty-state"><i class="fas fa-clipboard-list"></i><h3>Belum Ada Peserta</h3><p>Belum ada siswa yang mengerjakan quiz ini.</p></div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Siswa</th>
                        <th>NIS</th>
                        <th>Nilai</th>
                        <th>Status</th>
                        <th>Waktu Mulai</th>
                        <th>Waktu Selesai</th>
                        <th>Durasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attempts as $idx => $a): ?>
                        <?php
                            $duration = '-';
                            if ($a['started_at'] && $a['finished_at']) {
                                $diff = strtotime($a['finished_at']) - strtotime($a['started_at']);
                                $mins = floor($diff / 60);
                                $secs = $diff % 60;
                                $duration = $mins . 'm ' . $secs . 's';
                            }
                        ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <?php if ($a['avatar']): ?>
                                        <img src="<?= upload_url($a['avatar']) ?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;"><?= strtoupper(substr($a['full_name'], 0, 1)) ?></div>
                                    <?php endif; ?>
                                    <span style="font-size:13px;font-weight:600;"><?= e($a['full_name']) ?></span>
                                </div>
                            </td>
                            <td style="font-size:12px;color:var(--text-muted);"><?= e($a['nis'] ?? '-') ?></td>
                            <td>
                                <span style="font-size:15px;font-weight:700;color:<?= $a['score'] >= $quiz['passing_score'] ? '#10b981' : '#ef4444' ?>;">
                                    <?= number_format($a['score'], 1) ?>%
                                </span>
                            </td>
                            <td>
                                <?php if ($a['score'] >= $quiz['passing_score']): ?>
                                    <span class="badge badge-success">Lulus</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Tidak Lulus</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px;color:var(--text-muted);"><?= format_datetime($a['started_at']) ?></td>
                            <td style="font-size:12px;color:var(--text-muted);"><?= format_datetime($a['finished_at']) ?></td>
                            <td style="font-size:12px;color:var(--text-muted);"><?= $duration ?></td>
                            <td>
                                <a href="<?= url('quiz/review-attempt/' . $a['id']) ?>" class="btn btn-sm btn-primary" title="Review Jawaban"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
