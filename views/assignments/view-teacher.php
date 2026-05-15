<div class="page-header">
    <div>
        <h1><?= e($assignment['title']) ?></h1>
        <p><span class="badge badge-primary"><?= e($assignment['subject_name']) ?></span> • <?= e($assignment['class_name']) ?> • Deadline: <?= format_datetime($assignment['deadline']) ?></p>
    </div>
    <a href="<?= url('assignments') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom: 25px;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-paper-plane"></i></div>
        <div class="stat-info"><h3><?= count($submissions) ?></h3><p>Total Pengumpulan</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-clock"></i></div>
        <div class="stat-info"><h3><?= count(array_filter($submissions, fn($s) => $s['status'] === 'submitted')) ?></h3><p>Belum Dinilai</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check"></i></div>
        <div class="stat-info"><h3><?= count(array_filter($submissions, fn($s) => $s['status'] === 'graded')) ?></h3><p>Sudah Dinilai</p></div>
    </div>
</div>

<!-- Submissions List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Pengumpulan Siswa</h3>
    </div>
    
    <?php if (empty($submissions)): ?>
        <div class="empty-state"><i class="fas fa-inbox"></i><h3>Belum Ada Pengumpulan</h3></div>
    <?php else: ?>
        <div class="submissions-list">
            <?php foreach ($submissions as $sub): ?>
                <div class="submission-row" id="sub-<?= $sub['id'] ?>">
                    <div class="sub-student">
                        <?php if ($sub['avatar']): ?>
                            <img src="<?= upload_url($sub['avatar']) ?>" class="sub-avatar">
                        <?php else: ?>
                            <div class="sub-avatar placeholder"><?= strtoupper(substr($sub['full_name'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <div>
                            <span class="sub-name"><?= e($sub['full_name']) ?></span>
                            <span class="sub-meta"><?= e($sub['nis'] ?? '') ?> • <?= format_datetime($sub['submitted_at']) ?></span>
                        </div>
                    </div>
                    <div class="sub-content-preview">
                        <?php if ($sub['content']): ?>
                            <p><?= e(truncate($sub['content'], 80)) ?></p>
                        <?php endif; ?>
                        <?php if ($sub['file_path']): ?>
                            <a href="<?= upload_url($sub['file_path']) ?>" target="_blank" class="file-link"><i class="fas fa-file"></i> <?= e($sub['file_name'] ?? 'File') ?></a>
                        <?php endif; ?>
                    </div>
                    <div class="sub-grade-area">
                        <?php if ($sub['status'] === 'graded'): ?>
                            <div class="graded-badge">
                                <span class="score-display"><?= $sub['score'] ?>/<?= $assignment['max_score'] ?></span>
                                <span class="badge badge-success">Dinilai</span>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="<?= url('assignments/grade/' . $assignment['id']) ?>" class="grade-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                                <div class="grade-inputs">
                                    <input type="number" name="score" min="0" max="<?= $assignment['max_score'] ?>" placeholder="Nilai" class="form-control" style="width: 70px; padding: 6px 8px; font-size: 13px;" required>
                                    <input type="text" name="feedback" placeholder="Komentar..." class="form-control" style="flex: 1; padding: 6px 10px; font-size: 12px;">
                                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.submissions-list { display: flex; flex-direction: column; }
.submission-row {
    display: grid;
    grid-template-columns: 200px 1fr auto;
    align-items: center;
    gap: 16px;
    padding: 14px 0;
    border-bottom: 1px solid var(--border-light);
}
.submission-row:last-child { border-bottom: none; }
.sub-student { display: flex; align-items: center; gap: 10px; }
.sub-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
.sub-avatar.placeholder { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; }
.sub-name { display: block; font-size: 13px; font-weight: 600; color: var(--text-primary); }
.sub-meta { display: block; font-size: 11px; color: var(--text-muted); }
.sub-content-preview p { font-size: 12px; color: var(--text-secondary); }
.file-link { font-size: 12px; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 5px; }
.grade-inputs { display: flex; align-items: center; gap: 6px; }
.graded-badge { text-align: right; }
.score-display { font-size: 16px; font-weight: 700; color: var(--primary); display: block; }

@media (max-width: 768px) {
    .submission-row { grid-template-columns: 1fr; gap: 10px; }
}
</style>
