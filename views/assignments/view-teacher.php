<?php
$isClosed = ($assignment['status'] ?? '') === 'closed';
$isExpired = strtotime($assignment['deadline']) < time();
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
                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-lock-open"></i> Buka Kembali</button>
            </form>
        <?php endif; ?>
        <form method="POST" action="<?= url('assignments/delete/' . $assignment['id']) ?>" style="display:inline;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-danger" data-confirm="Yakin hapus tugas ini? Semua data pengumpulan akan hilang."><i class="fas fa-trash"></i> Hapus</button>
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
        <div class="stat-chip-info"><strong><?= count($allStudents) - count(array_filter($submissions, fn($s) => $s['score'] !== null && (float)$s['score'] > 0)) ?></strong><span>Belum / Nilai 0</span></div>
    </div>
</div>

<!-- Student List with Status -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list"></i> Daftar Siswa & Status</h3>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th>Nilai</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Build lookup
                $submissionMap = [];
                foreach ($submissions as $sub) {
                    $submissionMap[$sub['student_id']] = $sub;
                }
                foreach ($allStudents as $student): 
                    $sub = $submissionMap[$student['id']] ?? null;
                    $hasSubmitted = $sub !== null;
                    $isGraded = $hasSubmitted && $sub['status'] === 'graded';
                    $isLate = $hasSubmitted && $sub['status'] === 'late';
                    $score = $hasSubmitted ? $sub['score'] : null;
                ?>
                <tr>
                    <td>
                        <div class="user-cell">
                            <?php if ($student['avatar']): ?>
                                <img src="<?= upload_url($student['avatar']) ?>" class="user-cell-avatar">
                            <?php else: ?>
                                <div class="user-cell-avatar placeholder"><?= strtoupper(substr($student['full_name'], 0, 1)) ?></div>
                            <?php endif; ?>
                            <div>
                                <span class="user-cell-name"><?= e($student['full_name']) ?></span>
                                <span class="user-cell-email"><?= e($student['nis'] ?? '') ?></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if (!$hasSubmitted): ?>
                            <span class="badge badge-danger">Belum Mengumpulkan</span>
                        <?php elseif ($isGraded): ?>
                            <span class="badge badge-success">Dinilai</span>
                        <?php elseif ($isLate): ?>
                            <span class="badge badge-warning">Terlambat</span>
                        <?php else: ?>
                            <span class="badge badge-primary">Dikumpulkan</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="font-size:11px;color:var(--text-muted);">
                            <?= $hasSubmitted ? format_datetime($sub['submitted_at']) : '-' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($score !== null): ?>
                            <strong style="color: <?= (float)$score >= 70 ? 'var(--success)' : ((float)$score > 0 ? 'var(--warning)' : 'var(--danger)') ?>;"><?= $score ?></strong>
                        <?php else: ?>
                            <span style="color:var(--text-muted);">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($hasSubmitted): ?>
                            <form method="POST" action="<?= url('assignments/grade/' . $assignment['id']) ?>" class="grade-inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                                <div style="display:flex;gap:4px;align-items:center;">
                                    <input type="number" name="score" min="0" max="<?= $assignment['max_score'] ?>" value="<?= $score !== null ? $score : '' ?>" placeholder="Nilai" class="form-control" style="width:60px;padding:5px 6px;font-size:12px;" required>
                                    <input type="text" name="feedback" value="<?= e($sub['feedback'] ?? '') ?>" placeholder="Feedback" class="form-control" style="width:100px;padding:5px 6px;font-size:11px;">
                                    <button type="submit" class="btn btn-sm btn-success" style="padding:4px 8px;"><i class="fas fa-save"></i></button>
                                </div>
                            </form>
                        <?php else: ?>
                            <span style="font-size:11px;color:var(--text-muted);">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!empty($submissions)): ?>
<!-- Detailed Submissions with Files -->
<div class="card" style="margin-top:16px;">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-file-alt"></i> Detail Pengumpulan</h3></div>
    <?php foreach ($submissions as $sub): ?>
        <?php if ($sub['content'] || $sub['file_path']): ?>
        <div style="padding:12px 0;border-bottom:1px solid var(--border-light);">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                <strong style="font-size:13px;"><?= e($sub['full_name']) ?></strong>
                <?php if ($sub['status'] === 'late'): ?><span class="badge badge-warning">Terlambat</span><?php endif; ?>
            </div>
            <?php if ($sub['content']): ?>
                <p style="font-size:12px;color:var(--text-secondary);margin-bottom:6px;"><?= nl2br(e(truncate($sub['content'], 200))) ?></p>
            <?php endif; ?>
            <?php if ($sub['file_path']): 
                $files = explode('|', $sub['file_path']);
                $names = $sub['file_name'] ? explode('|', $sub['file_name']) : $files;
            ?>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <?php foreach ($files as $i => $fp): ?>
                        <a href="<?= upload_url($fp) ?>" target="_blank" style="font-size:11px;color:var(--primary);text-decoration:none;background:var(--primary-bg);padding:3px 8px;border-radius:6px;">
                            <i class="fas fa-file"></i> <?= e($names[$i] ?? 'File') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div style="height:80px;"></div>

<style>
.user-cell { display: flex; align-items: center; gap: 10px; }
.user-cell-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }
.user-cell-avatar.placeholder { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
.user-cell-name { display: block; font-size: 12px; font-weight: 600; color: var(--text-primary); }
.user-cell-email { display: block; font-size: 10px; color: var(--text-muted); }
.grade-inline-form { margin: 0; }
</style>
