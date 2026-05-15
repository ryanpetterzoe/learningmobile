<?php $role = Session::userRole(); $userName = Session::get('user_name'); ?>

<!-- Welcome Section -->
<div class="welcome-section">
    <div class="welcome-text">
        <h2>Halo, <?= e($userName) ?>! <span class="wave">👋</span></h2>
        <p><?= date('l, d M Y') ?></p>
    </div>
</div>

<!-- Quick Menu Grid - App Style Icons -->
<div class="menu-grid">
    <a href="<?= url('schedule') ?>" class="menu-item">
        <div class="menu-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <span>Jadwal</span>
    </a>
    <a href="<?= url('assignments') ?>" class="menu-item">
        <div class="menu-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
            <i class="fas fa-tasks"></i>
        </div>
        <span>Tugas</span>
    </a>
    <a href="<?= url('quiz') ?>" class="menu-item">
        <div class="menu-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
            <i class="fas fa-brain"></i>
        </div>
        <span>Quiz</span>
    </a>
    <a href="<?= url('attendance') ?>" class="menu-item">
        <div class="menu-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
            <i class="fas fa-calendar-check"></i>
        </div>
        <span>Kehadiran</span>
    </a>
    <a href="<?= url('grades') ?>" class="menu-item">
        <div class="menu-icon" style="background: linear-gradient(135deg, #fa709a, #fee140);">
            <i class="fas fa-chart-line"></i>
        </div>
        <span>Nilai</span>
    </a>
    <a href="<?= url('leaderboard') ?>" class="menu-item">
        <div class="menu-icon" style="background: linear-gradient(135deg, #a18cd1, #fbc2eb);">
            <i class="fas fa-trophy"></i>
        </div>
        <span>Ranking</span>
    </a>
    <a href="<?= url('announcements') ?>" class="menu-item">
        <div class="menu-icon" style="background: linear-gradient(135deg, #fccb90, #d57eeb);">
            <i class="fas fa-bullhorn"></i>
        </div>
        <span>Info</span>
    </a>
    <?php if ($role === 'siswa'): ?>
    <a href="<?= url('badges') ?>" class="menu-item">
        <div class="menu-icon" style="background: linear-gradient(135deg, #ff9a9e, #fecfef);">
            <i class="fas fa-medal"></i>
        </div>
        <span>Badge</span>
    </a>
    <?php elseif (Auth::isAdmin()): ?>
    <a href="<?= url('admin/users') ?>" class="menu-item">
        <div class="menu-icon" style="background: linear-gradient(135deg, #ff9a9e, #fecfef);">
            <i class="fas fa-users-cog"></i>
        </div>
        <span>Admin</span>
    </a>
    <?php else: ?>
    <a href="<?= url('classes') ?>" class="menu-item">
        <div class="menu-icon" style="background: linear-gradient(135deg, #ff9a9e, #fecfef);">
            <i class="fas fa-chalkboard"></i>
        </div>
        <span>Kelas</span>
    </a>
    <?php endif; ?>
</div>

<!-- XP & Level Card -->
<?php if ($role === 'siswa'): ?>
<div class="xp-card">
    <div class="xp-card-content">
        <div class="xp-info">
            <div class="xp-level">
                <i class="fas fa-star"></i>
                <span>Level <?= $stats['level'] ?? ($currentUser['level'] ?? 1) ?></span>
            </div>
            <div class="xp-points"><?= number_format($stats['xp'] ?? 0) ?> XP</div>
        </div>
        <div class="xp-progress">
            <div class="xp-bar">
                <div class="xp-fill" style="width: <?= min(100, ($stats['xp'] ?? 0) % 100) ?>%"></div>
            </div>
            <small>Selangkah lagi ke level berikutnya!</small>
        </div>
    </div>
    <div class="xp-card-decoration">
        <i class="fas fa-rocket"></i>
    </div>
</div>
<?php endif; ?>

<!-- Stats Section -->
<div class="section-header">
    <h3>Ringkasan</h3>
    <a href="<?= url($role === 'admin' ? 'admin/users' : 'grades') ?>" class="see-all">Lihat semua</a>
</div>

<div class="stats-scroll">
    <?php if ($role === 'siswa'): ?>
        <div class="stat-chip">
            <div class="stat-chip-icon blue"><i class="fas fa-chalkboard"></i></div>
            <div class="stat-chip-info">
                <strong><?= $stats['classes'] ?? 0 ?></strong>
                <span>Kelas</span>
            </div>
        </div>
        <div class="stat-chip">
            <div class="stat-chip-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="stat-chip-info">
                <strong><?= $stats['assignments_done'] ?? 0 ?></strong>
                <span>Tugas Selesai</span>
            </div>
        </div>
        <div class="stat-chip">
            <div class="stat-chip-icon purple"><i class="fas fa-brain"></i></div>
            <div class="stat-chip-info">
                <strong><?= $stats['quiz_done'] ?? 0 ?></strong>
                <span>Quiz</span>
            </div>
        </div>
        <div class="stat-chip">
            <div class="stat-chip-icon orange"><i class="fas fa-fire"></i></div>
            <div class="stat-chip-info">
                <strong><?= number_format($stats['xp'] ?? 0) ?></strong>
                <span>XP Points</span>
            </div>
        </div>
    <?php elseif ($role === 'guru' || $role === 'wali_kelas'): ?>
        <div class="stat-chip">
            <div class="stat-chip-icon blue"><i class="fas fa-book"></i></div>
            <div class="stat-chip-info">
                <strong><?= $stats['subjects'] ?? 0 ?></strong>
                <span>Mapel</span>
            </div>
        </div>
        <div class="stat-chip">
            <div class="stat-chip-icon green"><i class="fas fa-users"></i></div>
            <div class="stat-chip-info">
                <strong><?= $stats['students'] ?? 0 ?></strong>
                <span>Siswa</span>
            </div>
        </div>
        <div class="stat-chip">
            <div class="stat-chip-icon orange"><i class="fas fa-clock"></i></div>
            <div class="stat-chip-info">
                <strong><?= $stats['pending_grading'] ?? 0 ?></strong>
                <span>Perlu Dinilai</span>
            </div>
        </div>
        <div class="stat-chip">
            <div class="stat-chip-icon purple"><i class="fas fa-question-circle"></i></div>
            <div class="stat-chip-info">
                <strong><?= $stats['quizzes'] ?? 0 ?></strong>
                <span>Quiz</span>
            </div>
        </div>
    <?php elseif ($role === 'admin'): ?>
        <div class="stat-chip">
            <div class="stat-chip-icon blue"><i class="fas fa-users"></i></div>
            <div class="stat-chip-info">
                <strong><?= $stats['total_users'] ?? 0 ?></strong>
                <span>Pengguna</span>
            </div>
        </div>
        <div class="stat-chip">
            <div class="stat-chip-icon orange"><i class="fas fa-user-clock"></i></div>
            <div class="stat-chip-info">
                <strong><?= $stats['pending_users'] ?? 0 ?></strong>
                <span>Pending</span>
            </div>
        </div>
        <div class="stat-chip">
            <div class="stat-chip-icon green"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="stat-chip-info">
                <strong><?= $stats['total_classes'] ?? 0 ?></strong>
                <span>Kelas</span>
            </div>
        </div>
        <div class="stat-chip">
            <div class="stat-chip-icon purple"><i class="fas fa-book"></i></div>
            <div class="stat-chip-info">
                <strong><?= $stats['total_subjects'] ?? 0 ?></strong>
                <span>Mapel</span>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Today's Schedule -->
<div class="section-header">
    <h3>Jadwal Hari Ini</h3>
    <a href="<?= url('schedule') ?>" class="see-all">Lihat semua</a>
</div>

<?php if (!empty($schedule)): ?>
    <div class="schedule-cards">
        <?php foreach ($schedule as $item): ?>
            <div class="schedule-card">
                <div class="schedule-card-time">
                    <span class="time-badge"><?= date('H:i', strtotime($item['start_time'])) ?></span>
                    <span class="time-separator">-</span>
                    <span class="time-badge end"><?= date('H:i', strtotime($item['end_time'])) ?></span>
                </div>
                <div class="schedule-card-info">
                    <h4><?= e($item['subject_name']) ?></h4>
                    <p>
                        <?php if (isset($item['teacher_name'])): ?>
                            <i class="fas fa-user"></i> <?= e($item['teacher_name']) ?>
                        <?php endif; ?>
                        <?php if (isset($item['class_name'])): ?>
                            &bull; <?= e($item['class_name']) ?>
                        <?php endif; ?>
                        <?php if (!empty($item['room'])): ?>
                            &bull; <i class="fas fa-map-marker-alt"></i> <?= e($item['room']) ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-card">
        <i class="fas fa-coffee"></i>
        <p>Tidak ada jadwal hari ini. Santai dulu! ☕</p>
    </div>
<?php endif; ?>

<!-- Pending Assignments (Student) -->
<?php if ($role === 'siswa' && !empty($pending_assignments)): ?>
<div class="section-header">
    <h3>Tugas Belum Selesai</h3>
    <a href="<?= url('assignments') ?>" class="see-all">Semua</a>
</div>
<div class="task-list">
    <?php foreach ($pending_assignments as $task): ?>
        <div class="task-item">
            <div class="task-icon"><i class="fas fa-file-alt"></i></div>
            <div class="task-info">
                <h4><?= e($task['title']) ?></h4>
                <span class="task-badge"><?= e($task['subject_name']) ?></span>
            </div>
            <div class="task-deadline">
                <span class="countdown" data-countdown="<?= $task['deadline'] ?>"></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Recent Users (Admin) -->
<?php if ($role === 'admin' && !empty($recent_users)): ?>
<div class="section-header">
    <h3>Pendaftar Terbaru</h3>
    <a href="<?= url('admin/users') ?>" class="see-all">Kelola</a>
</div>
<div class="user-list">
    <?php foreach ($recent_users as $u): ?>
        <div class="user-item">
            <div class="user-item-avatar">
                <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
            </div>
            <div class="user-item-info">
                <h4><?= e($u['full_name']) ?></h4>
                <span><?= ucfirst($u['role']) ?> &bull; <?= time_ago($u['created_at']) ?></span>
            </div>
            <span class="status-dot <?= $u['status'] === 'active' ? 'active' : ($u['status'] === 'pending' ? 'pending' : 'suspended') ?>"></span>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Announcements -->
<?php if (!empty($announcements)): ?>
<div class="section-header">
    <h3>Pengumuman</h3>
    <a href="<?= url('announcements') ?>" class="see-all">Semua</a>
</div>
<div class="announcement-cards">
    <?php foreach ($announcements as $ann): ?>
        <div class="announcement-card">
            <div class="announcement-card-header">
                <span class="ann-author"><i class="fas fa-user-circle"></i> <?= e($ann['author_name']) ?></span>
                <span class="ann-time"><?= time_ago($ann['created_at']) ?></span>
            </div>
            <h4><?= e($ann['title']) ?></h4>
            <p><?= e(truncate(strip_tags($ann['content']), 120)) ?></p>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Spacer for bottom nav -->
<div style="height: 80px;"></div>
