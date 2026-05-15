<?php $role = Session::userRole(); ?>

<div class="page-header">
    <div>
        <h1>Selamat Datang, <?= e(Session::get('user_name')) ?>! 👋</h1>
        <p><?= date('l, d F Y') ?> • <?= ucfirst($role) ?></p>
    </div>
    <?php if ($role === 'admin'): ?>
        <a href="<?= url('admin/users') ?>" class="btn btn-primary"><i class="fas fa-users-cog"></i> Kelola Pengguna</a>
    <?php endif; ?>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <?php if ($role === 'siswa'): ?>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-chalkboard"></i></div>
            <div class="stat-info">
                <h3><?= $stats['classes'] ?? 0 ?></h3>
                <p>Kelas Diikuti</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <h3><?= $stats['assignments_done'] ?? 0 ?></h3>
                <p>Tugas Selesai</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow"><i class="fas fa-brain"></i></div>
            <div class="stat-info">
                <h3><?= $stats['quiz_done'] ?? 0 ?></h3>
                <p>Quiz Selesai</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-star"></i></div>
            <div class="stat-info">
                <h3><?= number_format($stats['xp'] ?? 0) ?></h3>
                <p>XP Points</p>
            </div>
        </div>

    <?php elseif ($role === 'guru' || $role === 'wali_kelas'): ?>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-book"></i></div>
            <div class="stat-info">
                <h3><?= $stats['subjects'] ?? 0 ?></h3>
                <p>Mata Pelajaran</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h3><?= $stats['students'] ?? 0 ?></h3>
                <p>Total Siswa</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3><?= $stats['pending_grading'] ?? 0 ?></h3>
                <p>Perlu Dinilai</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-question-circle"></i></div>
            <div class="stat-info">
                <h3><?= $stats['quizzes'] ?? 0 ?></h3>
                <p>Quiz Dibuat</p>
            </div>
        </div>

    <?php elseif ($role === 'admin'): ?>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h3><?= $stats['total_users'] ?? 0 ?></h3>
                <p>Total Pengguna</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow"><i class="fas fa-user-clock"></i></div>
            <div class="stat-info">
                <h3><?= $stats['pending_users'] ?? 0 ?></h3>
                <p>Menunggu Aktivasi</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="stat-info">
                <h3><?= $stats['total_classes'] ?? 0 ?></h3>
                <p>Total Kelas</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-book"></i></div>
            <div class="stat-info">
                <h3><?= $stats['total_subjects'] ?? 0 ?></h3>
                <p>Mata Pelajaran</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Main Content Grid -->
<div class="grid-2">
    <!-- Left Column -->
    <div>
        <!-- Today's Schedule -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-day"></i> Jadwal Hari Ini</h3>
                <a href="<?= url('schedule') ?>" class="btn btn-sm btn-secondary">Lihat Semua</a>
            </div>
            <?php if (!empty($schedule)): ?>
                <div class="schedule-list">
                    <?php foreach ($schedule as $item): ?>
                        <div class="schedule-item">
                            <div class="schedule-time">
                                <span class="time-start"><?= date('H:i', strtotime($item['start_time'])) ?></span>
                                <span class="time-end"><?= date('H:i', strtotime($item['end_time'])) ?></span>
                            </div>
                            <div class="schedule-info">
                                <h4><?= e($item['subject_name']) ?></h4>
                                <p>
                                    <?php if (isset($item['teacher_name'])): ?>
                                        <i class="fas fa-user"></i> <?= e($item['teacher_name']) ?>
                                    <?php endif; ?>
                                    <?php if (isset($item['class_name'])): ?>
                                        <i class="fas fa-door-open"></i> <?= e($item['class_name']) ?>
                                    <?php endif; ?>
                                    <?php if ($item['room']): ?>
                                        • <i class="fas fa-map-marker-alt"></i> <?= e($item['room']) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state" style="padding: 30px;">
                    <i class="fas fa-coffee"></i>
                    <p>Tidak ada jadwal hari ini</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pending Assignments (Student) -->
        <?php if ($role === 'siswa' && !empty($pending_assignments)): ?>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tasks"></i> Tugas Belum Dikerjakan</h3>
                    <a href="<?= url('assignments') ?>" class="btn btn-sm btn-secondary">Semua</a>
                </div>
                <div class="assignment-list">
                    <?php foreach ($pending_assignments as $task): ?>
                        <div class="assignment-item">
                            <div class="assignment-info">
                                <h4><?= e($task['title']) ?></h4>
                                <p><span class="badge badge-primary"><?= e($task['subject_name']) ?></span></p>
                            </div>
                            <div class="assignment-deadline">
                                <span class="countdown" data-countdown="<?= $task['deadline'] ?>"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recent Users (Admin) -->
        <?php if ($role === 'admin' && !empty($recent_users)): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-plus"></i> Pendaftar Terbaru</h3>
                    <a href="<?= url('admin/users') ?>" class="btn btn-sm btn-secondary">Kelola</a>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr><th>Nama</th><th>Role</th><th>Status</th><th>Tanggal</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_users as $u): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div class="avatar-placeholder" style="width:30px;height:30px;font-size:11px;border-radius:50%;background:linear-gradient(135deg,#3B49DF,#6366f1);color:#fff;display:flex;align-items:center;justify-content:center;">
                                                <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                                            </div>
                                            <?= e($u['full_name']) ?>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-primary"><?= ucfirst($u['role']) ?></span></td>
                                    <td>
                                        <?php if ($u['status'] === 'active'): ?>
                                            <span class="badge badge-success">Aktif</span>
                                        <?php elseif ($u['status'] === 'pending'): ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Suspended</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= time_ago($u['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column -->
    <div>
        <!-- Attendance (Student) -->
        <?php if ($role === 'siswa'): ?>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie"></i> Kehadiran</h3>
                </div>
                <div style="text-align: center; padding: 20px;">
                    <div class="attendance-circle">
                        <svg width="120" height="120" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" stroke="#e2e8f0" stroke-width="10" fill="none"/>
                            <circle cx="60" cy="60" r="50" stroke="#3B49DF" stroke-width="10" fill="none"
                                stroke-dasharray="<?= ($attendance_pct ?? 100) * 3.14 ?> 314"
                                stroke-linecap="round" transform="rotate(-90 60 60)"
                                style="transition: stroke-dasharray 1s ease;"/>
                        </svg>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                            <span style="font-size: 28px; font-weight: 800; color: var(--text-primary);"><?= $attendance_pct ?? 100 ?>%</span>
                        </div>
                    </div>
                    <p style="margin-top: 10px; color: var(--text-muted); font-size: 13px;">Persentase Kehadiran</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Latest Grades (Student) -->
        <?php if ($role === 'siswa' && !empty($latest_grades)): ?>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Nilai Terbaru</h3>
                </div>
                <?php foreach ($latest_grades as $grade): ?>
                    <div class="grade-item">
                        <div class="grade-info">
                            <h4><?= e($grade['title'] ?? $grade['type']) ?></h4>
                            <p><?= e($grade['subject_name']) ?></p>
                        </div>
                        <div class="grade-score <?= $grade['score'] >= 75 ? 'good' : ($grade['score'] >= 60 ? 'mid' : 'low') ?>">
                            <?= $grade['score'] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Announcements -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bullhorn"></i> Pengumuman</h3>
                <a href="<?= url('announcements') ?>" class="btn btn-sm btn-secondary">Semua</a>
            </div>
            <?php if (!empty($announcements)): ?>
                <?php foreach ($announcements as $ann): ?>
                    <div class="announcement-item">
                        <div class="ann-header">
                            <span class="ann-author"><?= e($ann['author_name']) ?></span>
                            <span class="ann-time"><?= time_ago($ann['created_at']) ?></span>
                        </div>
                        <h4><?= e($ann['title']) ?></h4>
                        <p><?= e(truncate(strip_tags($ann['content']), 100)) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state" style="padding: 20px;">
                    <p style="color: var(--text-muted);">Belum ada pengumuman</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.schedule-list { display: flex; flex-direction: column; gap: 10px; }
.schedule-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px;
    border-radius: var(--radius-sm);
    background: var(--bg-hover);
    transition: var(--transition);
}
.schedule-item:hover { transform: translateX(4px); }
.schedule-time {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 55px;
}
.time-start { font-size: 14px; font-weight: 700; color: var(--primary); }
.time-end { font-size: 11px; color: var(--text-muted); }
.schedule-info h4 { font-size: 14px; color: var(--text-primary); margin-bottom: 3px; }
.schedule-info p { font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; }

.assignment-list { display: flex; flex-direction: column; gap: 8px; }
.assignment-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-light);
    transition: var(--transition);
}
.assignment-item:hover { border-color: var(--primary); background: var(--primary-bg); }
.assignment-info h4 { font-size: 13px; color: var(--text-primary); margin-bottom: 4px; }
.assignment-deadline { font-size: 12px; font-weight: 600; }

.attendance-circle { position: relative; display: inline-block; }

.grade-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-light);
}
.grade-item:last-child { border-bottom: none; }
.grade-info h4 { font-size: 13px; color: var(--text-primary); }
.grade-info p { font-size: 11px; color: var(--text-muted); }
.grade-score {
    width: 40px; height: 40px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700;
}
.grade-score.good { background: rgba(16,185,129,0.1); color: #10b981; }
.grade-score.mid { background: rgba(245,158,11,0.1); color: #f59e0b; }
.grade-score.low { background: rgba(239,68,68,0.1); color: #ef4444; }

.announcement-item {
    padding: 12px 0;
    border-bottom: 1px solid var(--border-light);
}
.announcement-item:last-child { border-bottom: none; }
.ann-header { display: flex; justify-content: space-between; margin-bottom: 6px; }
.ann-author { font-size: 12px; font-weight: 600; color: var(--primary); }
.ann-time { font-size: 11px; color: var(--text-muted); }
.announcement-item h4 { font-size: 14px; color: var(--text-primary); margin-bottom: 4px; }
.announcement-item p { font-size: 12px; color: var(--text-secondary); line-height: 1.5; }

@media (max-width: 768px) {
    .grid-2 { grid-template-columns: 1fr; }
}
</style>
