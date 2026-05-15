<?php $role = Session::userRole(); ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-tasks"></i> Tugas</h1>
        <p><?= Auth::isGuru() ? 'Kelola tugas yang Anda buat' : 'Daftar tugas yang harus dikerjakan' ?></p>
    </div>
    <?php if (Auth::isGuru() || Auth::isAdmin()): ?>
        <a href="<?= url('assignments/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Tugas</a>
    <?php endif; ?>
</div>

<?php if (empty($assignments)): ?>
    <div class="card"><div class="empty-state"><i class="fas fa-clipboard-check"></i><h3>Belum Ada Tugas</h3><p>Tidak ada tugas saat ini.</p></div></div>
<?php else: ?>
    <div class="assignments-list">
        <?php foreach ($assignments as $a): 
            $isPast = strtotime($a['deadline']) < time();
            $isUrgent = !$isPast && (strtotime($a['deadline']) - time()) < 86400;
        ?>
            <a href="<?= url('assignments/view/' . $a['id']) ?>" class="assignment-card <?= $isPast ? 'past' : '' ?>">
                <div class="assign-color" style="background: <?= e($a['color'] ?? '#3B49DF') ?>;"></div>
                <div class="assign-main">
                    <div class="assign-header">
                        <h3><?= e($a['title']) ?></h3>
                        <?php if ($role === 'siswa'): ?>
                            <?php if (isset($a['submission_status'])): ?>
                                <?php if ($a['submission_status'] === 'graded'): ?>
                                    <span class="badge badge-success">Dinilai</span>
                                <?php else: ?>
                                    <span class="badge badge-primary">Dikumpulkan</span>
                                <?php endif; ?>
                            <?php elseif ($isPast): ?>
                                <span class="badge badge-danger">Terlewat</span>
                            <?php elseif ($isUrgent): ?>
                                <span class="badge badge-warning">Segera!</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge badge-primary"><?= $a['pending_count'] ?? 0 ?> belum dinilai</span>
                        <?php endif; ?>
                    </div>
                    <div class="assign-meta">
                        <span><i class="fas fa-book"></i> <?= e($a['subject_name']) ?></span>
                        <?php if (isset($a['class_name'])): ?>
                            <span><i class="fas fa-users"></i> <?= e($a['class_name']) ?></span>
                        <?php endif; ?>
                        <span class="<?= $isPast ? 'text-danger' : ($isUrgent ? 'text-warning' : '') ?>">
                            <i class="fas fa-clock"></i> 
                            <?= format_datetime($a['deadline']) ?>
                            <?php if (!$isPast): ?>
                                <span class="countdown-inline" data-countdown="<?= $a['deadline'] ?>"></span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                <i class="fas fa-chevron-right" style="color: var(--text-muted);"></i>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
.assignments-list { display: flex; flex-direction: column; gap: 10px; }
.assignment-card {
    display: flex; align-items: center; gap: 16px; padding: 18px 20px;
    background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md);
    text-decoration: none; transition: var(--transition);
}
.assignment-card:hover { border-color: var(--primary); transform: translateX(4px); box-shadow: var(--shadow-sm); }
.assignment-card.past { opacity: 0.6; }
.assign-color { width: 6px; height: 50px; border-radius: 3px; flex-shrink: 0; }
.assign-main { flex: 1; }
.assign-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.assign-header h3 { font-size: 15px; color: var(--text-primary); }
.assign-meta { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.assign-meta span { font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 5px; }
.text-danger { color: var(--danger) !important; }
.text-warning { color: var(--warning) !important; }
.countdown-inline { font-weight: 600; margin-left: 4px; }
</style>
