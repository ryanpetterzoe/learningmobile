<div class="page-header">
    <div>
        <h1><i class="fas fa-chalkboard-teacher"></i> Kelas Saya</h1>
        <p>Daftar kelas yang Anda ikuti atau kelola</p>
    </div>
</div>

<?php if (empty($classes)): ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-school"></i>
            <h3>Belum Ada Kelas</h3>
            <p>Anda belum terdaftar di kelas manapun. Hubungi admin atau wali kelas.</p>
        </div>
    </div>
<?php else: ?>
    <div class="classes-grid">
        <?php 
        $colors = ['#3B49DF', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#84cc16'];
        $i = 0;
        foreach ($classes as $cls): 
            $color = $colors[$i % count($colors)];
            $i++;
        ?>
            <a href="<?= url('classes/view/' . $cls['id']) ?>" class="class-card-link">
                <div class="class-card">
                    <div class="class-card-header" style="background: linear-gradient(135deg, <?= $color ?>, <?= $color ?>cc);">
                        <div>
                            <h3><?= e($cls['name']) ?></h3>
                            <span class="class-subtitle"><?= e($cls['major'] ?? '') ?></span>
                        </div>
                        <span class="class-grade-badge"><?= e($cls['grade']) ?></span>
                    </div>
                    <div class="class-card-body">
                        <div class="class-meta">
                            <span><i class="fas fa-user-tie"></i> <?= e($cls['homeroom_teacher'] ?? '-') ?></span>
                            <span><i class="fas fa-users"></i> <?= $cls['student_count'] ?> siswa</span>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
.classes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}
.class-card-link { text-decoration: none; }
.class-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: var(--transition);
}
.class-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
.class-card-header { padding: 24px; color: #fff; display: flex; justify-content: space-between; align-items: flex-start; }
.class-card-header h3 { font-size: 18px; font-weight: 700; }
.class-subtitle { font-size: 12px; opacity: 0.85; }
.class-grade-badge { background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
.class-card-body { padding: 16px 20px; }
.class-meta { display: flex; flex-direction: column; gap: 6px; }
.class-meta span { font-size: 13px; color: var(--text-secondary); display: flex; align-items: center; gap: 8px; }
.class-meta i { width: 16px; color: var(--text-muted); }
</style>
