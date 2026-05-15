<?php $role = Session::userRole(); ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-question-circle"></i> Quiz & CBT</h1>
        <p><?= Auth::isGuru() ? 'Kelola quiz dan ujian online' : 'Daftar quiz yang tersedia' ?></p>
    </div>
    <?php if (Auth::isGuru() || Auth::isAdmin()): ?>
        <a href="<?= url('quiz/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Quiz</a>
    <?php endif; ?>
</div>

<?php if (empty($quizzes)): ?>
    <div class="card"><div class="empty-state"><i class="fas fa-brain"></i><h3>Belum Ada Quiz</h3><p>Tidak ada quiz tersedia saat ini.</p></div></div>
<?php else: ?>
    <div class="quiz-grid">
        <?php foreach ($quizzes as $q): ?>
            <div class="quiz-card">
                <div class="quiz-card-top" style="background: <?= e($q['color'] ?? '#3B49DF') ?>;">
                    <div class="quiz-badge"><?= $q['question_count'] ?> Soal</div>
                    <h3><?= e($q['title']) ?></h3>
                    <p><?= e($q['subject_name']) ?> • <?= e($q['class_name']) ?></p>
                </div>
                <div class="quiz-card-body">
                    <div class="quiz-info-row">
                        <span><i class="fas fa-clock"></i> <?= $q['duration_minutes'] ?> menit</span>
                        <span><i class="fas fa-trophy"></i> Min <?= $q['passing_score'] ?>%</span>
                    </div>
                    <?php if ($q['start_time']): ?>
                        <div class="quiz-time-info">
                            <span><?= format_datetime($q['start_time']) ?> - <?= format_datetime($q['end_time']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="quiz-card-footer">
                    <?php if ($role === 'siswa'): ?>
                        <?php if (!empty($q['completed'])): ?>
                            <span class="badge badge-success">Selesai</span>
                        <?php else: ?>
                            <a href="<?= url('quiz/start/' . $q['id']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-play"></i> Mulai</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div style="display:flex;gap:4px;align-items:center;flex-wrap:wrap;">
                            <span class="badge <?= $q['status'] === 'active' ? 'badge-success' : ($q['status'] === 'closed' ? 'badge-danger' : 'badge-warning') ?>"><?= ucfirst($q['status']) ?></span>
                            <a href="<?= url('quiz/edit/' . $q['id']) ?>" class="btn btn-sm btn-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?= url('quiz/questions/' . $q['id']) ?>" class="btn btn-sm btn-secondary" title="Soal"><i class="fas fa-list"></i></a>
                            <?php if ($q['status'] === 'active'): ?>
                                <a href="<?= url('quiz/results/' . $q['id']) ?>" class="btn btn-sm btn-primary" title="Hasil"><i class="fas fa-chart-bar"></i></a>
                            <?php endif; ?>
                            <form method="POST" action="<?= url('quiz/delete/' . $q['id']) ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus" data-confirm="Yakin hapus quiz ini beserta semua soal dan hasilnya?"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                        <span style="font-size: 11px; color: var(--text-muted);"><?= $q['attempt_count'] ?? 0 ?> peserta</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
.quiz-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
.quiz-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; transition: var(--transition); }
.quiz-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
.quiz-card-top { padding: 20px; color: #fff; }
.quiz-badge { display: inline-block; background: rgba(255,255,255,0.2); padding: 3px 10px; border-radius: 12px; font-size: 11px; margin-bottom: 8px; }
.quiz-card-top h3 { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
.quiz-card-top p { font-size: 12px; opacity: 0.85; }
.quiz-card-body { padding: 14px 20px; }
.quiz-info-row { display: flex; justify-content: space-between; }
.quiz-info-row span { font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 5px; }
.quiz-time-info { margin-top: 8px; font-size: 11px; color: var(--text-muted); }
.quiz-card-footer { padding: 12px 20px; border-top: 1px solid var(--border-light); display: flex; align-items: center; justify-content: space-between; }
</style>
