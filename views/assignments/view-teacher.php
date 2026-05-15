<?php
$isClosed = ($assignment['status'] ?? '') === 'closed';
$isExpired = strtotime($assignment['deadline']) < time();
// Build submission map
$submissionMap = [];
foreach ($submissions as $sub) {
    $submissionMap[$sub['student_id']] = $sub;
}
?>
<div class="page-header" style="flex-wrap:wrap;gap:10px;">
    <div>
        <h1><?= e($assignment['title']) ?></h1>
        <p>
            <span class="badge badge-primary"><?= e($assignment['subject_name']) ?></span> 
            &bull; <?= e($assignment['class_name']) ?> 
            &bull; Deadline: <?= format_datetime($assignment['deadline']) ?>
            <?php if ($isClosed): ?>
                <span class="badge badge-danger" style="margin-left:6px;">DITUTUP</span>
            <?php elseif ($isExpired && $assignment['allow_late']): ?>
                <span class="badge badge-warning" style="margin-left:6px;">Expired (Terlambat OK)</span>
            <?php elseif ($isExpired): ?>
                <span class="badge badge-danger" style="margin-left:6px;">Expired</span>
            <?php else: ?>
                <span class="badge badge-success" style="margin-left:6px;">Aktif</span>
            <?php endif; ?>
        </p>
    </div>
    <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <a href="<?= url('assignments') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        <a href="<?= url('assignments/edit/' . $assignment['id']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>
        <?php if (!$isClosed): ?>
            <form method="POST" action="<?= url('assignments/close/' . $assignment['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-warning" data-confirm="Tutup tugas ini? Siswa yang belum mengumpulkan akan mendapat nilai 0."><i class="fas fa-lock"></i> Tutup</button>
            </form>
        <?php else: ?>
            <form method="POST" action="<?= url('assignments/reopen/' . $assignment['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-lock-open"></i> Buka</button>
            </form>
        <?php endif; ?>
        <form method="POST" action="<?= url('assignments/delete/' . $assignment['id']) ?>" style="display:inline;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-danger" data-confirm="Yakin hapus tugas ini?"><i class="fas fa-trash"></i> Hapus</button>
        </form>
    </div>
</div>

<?php if (!empty($assignment['description'])): ?>
<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> Instruksi</h3></div>
    <div style="font-size:13px;color:var(--text-secondary);line-height:1.6;"><?= nl2br(e($assignment['description'])) ?></div>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-scroll" style="margin-bottom:20px;">
    <div class="stat-chip">
        <div class="stat-chip-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-chip-info"><strong><?= count($allStudents) ?></strong><span>Total Siswa</span></div>
    </div>
    <div class="stat-chip">
        <div class="stat-chip-icon green"><i class="fas fa-paper-plane"></i></div>
        <div class="stat-chip-info"><strong><?= count($submissions) ?></strong><span>Mengumpulkan</span></div>
    </div>
    <div class="stat-chip">
        <div class="stat-chip-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-chip-info"><strong><?= count(array_filter($submissions, fn($s) => $s['status'] === 'submitted')) ?></strong><span>Belum Dinilai</span></div>
    </div>
    <div class="stat-chip">
        <div class="stat-chip-icon purple"><i class="fas fa-times-circle"></i></div>
        <div class="stat-chip-info"><strong><?= count($allStudents) - count($submissions) ?></strong><span>Belum Kirim</span></div>
    </div>
</div>

<!-- Student List - Vertical Cards -->
<div class="section-header">
    <h3><i class="fas fa-list"></i> Daftar Siswa & Status</h3>
</div>

<div class="student-submission-list">
    <?php foreach ($allStudents as $student): 
        $sub = $submissionMap[$student['id']] ?? null;
        $hasSubmitted = $sub !== null;
        $isGraded = $hasSubmitted && $sub['status'] === 'graded';
        $isLate = $hasSubmitted && $sub['status'] === 'late';
        $score = $hasSubmitted ? $sub['score'] : null;
    ?>
    <div class="student-card">
        <div class="student-card-header">
            <div class="student-card-user">
                <?php if (!empty($student['avatar'])): ?>
                    <img src="<?= upload_url($student['avatar']) ?>" class="student-card-avatar">
                <?php else: ?>
                    <div class="student-card-avatar placeholder"><?= strtoupper(substr($student['full_name'], 0, 1)) ?></div>
                <?php endif; ?>
                <div>
                    <strong><?= e($student['full_name']) ?></strong>
                    <small><?= e($student['nis'] ?? '') ?></small>
                </div>
            </div>
            <div>
                <?php if (!$hasSubmitted): ?>
                    <span class="badge badge-danger">Belum Kirim</span>
                <?php elseif ($isGraded): ?>
                    <span class="badge badge-success">Dinilai: <?= $score ?></span>
                <?php elseif ($isLate): ?>
                    <span class="badge badge-warning">Terlambat</span>
                <?php else: ?>
                    <span class="badge badge-primary">Dikumpulkan</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($hasSubmitted): ?>
            <div class="student-card-body">
                <p class="submission-time"><i class="fas fa-clock"></i> <?= format_datetime($sub['submitted_at']) ?></p>
                <?php if ($sub['content']): ?>
                    <p class="submission-text"><?= nl2br(e(truncate($sub['content'], 150))) ?></p>
                <?php endif; ?>
                <?php if ($sub['file_path']): 
                    $files = explode('|', $sub['file_path']);
                    $names = $sub['file_name'] ? explode('|', $sub['file_name']) : $files;
                ?>
                    <div class="submission-files">
                        <?php foreach ($files as $i => $fp): ?>
                            <a href="<?= upload_url($fp) ?>" target="_blank" class="submission-file-chip">
                                <i class="fas fa-file"></i> <?= e(truncate($names[$i] ?? 'File', 25)) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="student-card-grade">
                <form method="POST" action="<?= url('assignments/grade/' . $assignment['id']) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                    <div class="grade-row">
                        <div class="grade-input-wrap">
                            <label>Nilai</label>
                            <input type="number" name="score" min="0" max="<?= $assignment['max_score'] ?>" value="<?= $score !== null ? $score : '' ?>" placeholder="0-<?= $assignment['max_score'] ?>" class="form-control" required>
                        </div>
                        <div class="grade-input-wrap" style="flex:2;">
                            <label>Feedback</label>
                            <input type="text" name="feedback" value="<?= e($sub['feedback'] ?? '') ?>" placeholder="Komentar guru..." class="form-control">
                        </div>
                        <button type="submit" class="btn btn-sm btn-success" style="align-self:flex-end;"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="student-card-body">
                <p class="submission-text" style="color:var(--text-muted);font-style:italic;">Belum mengumpulkan tugas.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<div style="height:80px;"></div>

<style>
.student-submission-list { display: flex; flex-direction: column; gap: 12px; }
.student-card { background: var(--bg-card); border: 1px solid var(--border-light); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden; }
.student-card-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-bottom: 1px solid var(--border-light); gap: 8px; }
.student-card-user { display: flex; align-items: center; gap: 10px; }
.student-card-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
.student-card-avatar.placeholder { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; }
.student-card-user strong { display: block; font-size: 13px; color: var(--text-primary); }
.student-card-user small { font-size: 11px; color: var(--text-muted); }
.student-card-body { padding: 12px 16px; }
.submission-time { font-size: 11px; color: var(--text-muted); margin-bottom: 6px; display: flex; align-items: center; gap: 4px; }
.submission-text { font-size: 13px; color: var(--text-secondary); line-height: 1.5; }
.submission-files { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.submission-file-chip { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: var(--primary-bg); color: var(--primary); border-radius: var(--radius-full); font-size: 11px; font-weight: 500; text-decoration: none; transition: var(--transition); }
.submission-file-chip:hover { background: var(--primary); color: #fff; }
.student-card-grade { padding: 12px 16px; background: var(--bg-hover); border-top: 1px solid var(--border-light); }
.grade-row { display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap; }
.grade-input-wrap { flex: 1; min-width: 80px; }
.grade-input-wrap label { display: block; font-size: 10px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; text-transform: uppercase; }
.grade-input-wrap .form-control { padding: 8px 10px; font-size: 13px; }
@media (max-width: 480px) {
    .grade-row { flex-direction: column; align-items: stretch; }
    .grade-row .btn { width: 100%; justify-content: center; }
    .student-card-header { flex-direction: column; align-items: flex-start; }
}
</style>
