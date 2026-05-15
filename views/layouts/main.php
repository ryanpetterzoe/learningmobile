<?php
/**
 * SimpleEdu - Main Layout (Rebuilt)
 * 
 * Notifications are loaded server-side (no fetch/AJAX).
 */
$currentUser = Session::user();
$currentRoute = trim($_GET['route'] ?? '', '/');
$routeParts = explode('/', $currentRoute);
$activePage = $routeParts[0] ?? 'dashboard';

// Get unread notifications count and recent notifications (server-side)
$db = Database::getInstance();
$prefix = $db->getPrefix();
$notifCount = $db->count('notifications', 'user_id = ? AND is_read = 0', [Session::userId()]);
$notifications = $db->fetchAll(
    "SELECT * FROM {$prefix}notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20",
    [Session::userId()]
);
$appLogo = app_setting('app_logo');
?>
<!DOCTYPE html>
<html lang="id" data-theme="<?= e(Session::get('user_theme', $currentUser['theme'] ?? 'light')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Dashboard') ?> - <?= e(APP_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <?php if (isset($extraCss)): ?>
        <?php foreach ((array)$extraCss as $css): ?>
            <link rel="stylesheet" href="<?= asset('css/' . $css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="app-brand">
                <?php if ($appLogo): ?>
                    <img src="<?= upload_url($appLogo) ?>" alt="Logo" class="brand-img">
                <?php else: ?>
                    <div class="brand-icon"><i class="fas fa-book-open"></i></div>
                <?php endif; ?>
                <div class="brand-text">
                    <h2><?= e(app_setting('app_name', 'SimpleEdu')) ?></h2>
                    <small><?= e(app_setting('school_name', '')) ?></small>
                </div>
            </div>
            <button class="sidebar-close" id="sidebarClose"><i class="fas fa-times"></i></button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <span class="nav-section-title">MENU UTAMA</span>
                <a href="<?= url('dashboard') ?>" class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i><span>Dashboard</span>
                </a>
                
                <?php if (Auth::isAdmin()): ?>
                    <a href="<?= url('admin/users') ?>" class="nav-link <?= ($activePage === 'admin' && ($GLOBALS['action'] ?? '') === 'users') ? 'active' : '' ?>">
                        <i class="fas fa-users-cog"></i><span>Admin Panel</span>
                    </a>
                    <a href="<?= url('admin/settings') ?>" class="nav-link <?= ($activePage === 'admin' && ($GLOBALS['action'] ?? '') === 'settings') ? 'active' : '' ?>">
                        <i class="fas fa-school"></i><span>Identitas Sekolah</span>
                    </a>
                    <a href="<?= url('admin/subjects') ?>" class="nav-link <?= ($activePage === 'admin' && ($GLOBALS['action'] ?? '') === 'subjects') ? 'active' : '' ?>">
                        <i class="fas fa-book"></i><span>Mata Pelajaran</span>
                    </a>
                    <a href="<?= url('admin/schedules') ?>" class="nav-link <?= ($activePage === 'admin' && ($GLOBALS['action'] ?? '') === 'schedules') ? 'active' : '' ?>">
                        <i class="fas fa-calendar-alt"></i><span>Kelola Jadwal</span>
                    </a>
                    <a href="<?= url('admin/classes') ?>" class="nav-link <?= ($activePage === 'admin' && ($GLOBALS['action'] ?? '') === 'classes') ? 'active' : '' ?>">
                        <i class="fas fa-school"></i><span>Kelola Kelas</span>
                    </a>
                    <a href="<?= url('admin/competencies') ?>" class="nav-link <?= ($activePage === 'admin' && ($GLOBALS['action'] ?? '') === 'competencies') ? 'active' : '' ?>">
                        <i class="fas fa-graduation-cap"></i><span>Kompetensi Keahlian</span>
                    </a>
                <?php endif; ?>

                <a href="<?= url('classes') ?>" class="nav-link <?= $activePage === 'classes' ? 'active' : '' ?>">
                    <i class="fas fa-chalkboard-teacher"></i><span>Kelas</span>
                </a>
                <a href="<?= url('assignments') ?>" class="nav-link <?= $activePage === 'assignments' ? 'active' : '' ?>">
                    <i class="fas fa-tasks"></i><span>Tugas</span>
                </a>
                <a href="<?= url('quiz') ?>" class="nav-link <?= $activePage === 'quiz' ? 'active' : '' ?>">
                    <i class="fas fa-question-circle"></i><span>Quiz & CBT</span>
                </a>
            </div>

            <div class="nav-section">
                <span class="nav-section-title">KOMUNITAS</span>
                <a href="<?= url('forum') ?>" class="nav-link <?= $activePage === 'forum' ? 'active' : '' ?>">
                    <i class="fas fa-comments"></i><span>Forum Diskusi</span>
                </a>
                <a href="<?= url('announcements') ?>" class="nav-link <?= $activePage === 'announcements' ? 'active' : '' ?>">
                    <i class="fas fa-bullhorn"></i><span>Pengumuman</span>
                </a>
            </div>

            <div class="nav-section">
                <span class="nav-section-title">AKADEMIK</span>
                <a href="<?= url('grades') ?>" class="nav-link <?= $activePage === 'grades' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i><span>Nilai</span>
                </a>
                <a href="<?= url('attendance') ?>" class="nav-link <?= $activePage === 'attendance' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-check"></i><span>Kehadiran</span>
                </a>
                <a href="<?= url('schedule') ?>" class="nav-link <?= $activePage === 'schedule' ? 'active' : '' ?>">
                    <i class="fas fa-clock"></i><span>Jadwal</span>
                </a>
            </div>

            <div class="nav-section">
                <span class="nav-section-title">SMK</span>
                <a href="<?= url('pkl') ?>" class="nav-link <?= $activePage === 'pkl' ? 'active' : '' ?>">
                    <i class="fas fa-building"></i><span>PKL / Magang</span>
                </a>
                <a href="<?= url('portfolio') ?>" class="nav-link <?= $activePage === 'portfolio' ? 'active' : '' ?>">
                    <i class="fas fa-folder-open"></i><span>Portofolio</span>
                </a>
                <a href="<?= url('certificates') ?>" class="nav-link <?= $activePage === 'certificates' ? 'active' : '' ?>">
                    <i class="fas fa-certificate"></i><span>Sertifikat</span>
                </a>
            </div>

            <div class="nav-section">
                <span class="nav-section-title">GAMIFICATION</span>
                <a href="<?= url('leaderboard') ?>" class="nav-link <?= $activePage === 'leaderboard' ? 'active' : '' ?>">
                    <i class="fas fa-trophy"></i><span>Ranking</span>
                </a>
                <a href="<?= url('badges') ?>" class="nav-link <?= $activePage === 'badges' ? 'active' : '' ?>">
                    <i class="fas fa-medal"></i><span>Badge & XP</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Cari..." id="globalSearch">
                </div>
            </div>

            <div class="header-right">
                <!-- Theme Toggle -->
                <button class="header-btn" id="themeToggle" title="Toggle Dark Mode">
                    <i class="fas fa-moon"></i>
                </button>

                <!-- Notifications (Server-Rendered) -->
                <div class="header-btn-wrapper" style="position:relative;">
                    <button class="header-btn" id="notifBtn" title="Notifikasi" onclick="toggleNotifPanel()">
                        <i class="fas fa-bell"></i>
                        <?php if ($notifCount > 0): ?>
                            <span class="badge-count"><?= $notifCount ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <strong>Notifikasi</strong>
                            <?php if ($notifCount > 0): ?>
                                <form method="POST" action="<?= url('api/mark-read') ?>" style="display:inline;">
                                    <button type="submit" style="font-size:11px;color:var(--primary);background:none;border:none;cursor:pointer;">Tandai semua dibaca</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <div class="notif-list">
                            <?php if (empty($notifications)): ?>
                                <p style="padding:20px;text-align:center;color:var(--text-muted);font-size:12px;">Tidak ada notifikasi.</p>
                            <?php else: ?>
                                <?php foreach ($notifications as $n): ?>
                                    <?php 
                                    $unreadClass = empty($n['is_read']) ? 'notif-unread' : '';
                                    $link = !empty($n['link']) ? $n['link'] : '#';
                                    // Smart icon based on notification title
                                    $iconClass = 'info-circle';
                                    $iconBg = 'var(--primary-bg)';
                                    $iconColor = 'var(--primary)';
                                    if ($n['type'] === 'success') { $iconClass = 'check-circle'; $iconBg = '#dcfce7'; $iconColor = '#16a34a'; }
                                    elseif ($n['type'] === 'warning') { $iconClass = 'exclamation-triangle'; $iconBg = '#fef3c7'; $iconColor = '#d97706'; }
                                    elseif ($n['type'] === 'error') { $iconClass = 'exclamation-circle'; $iconBg = '#fef2f2'; $iconColor = '#dc2626'; }
                                    // Context-specific icons
                                    if (strpos($n['title'], 'Disukai') !== false || strpos($n['title'], 'Like') !== false) { $iconClass = 'heart'; $iconBg = '#fef2f2'; $iconColor = '#ef4444'; }
                                    elseif (strpos($n['title'], 'Balasan') !== false) { $iconClass = 'reply'; $iconBg = '#e0f2fe'; $iconColor = '#0284c7'; }
                                    elseif (strpos($n['title'], 'Komentar') !== false) { $iconClass = 'comment'; $iconBg = '#e0f2fe'; $iconColor = '#0284c7'; }
                                    elseif (strpos($n['title'], 'Tugas') !== false) { $iconClass = 'tasks'; $iconBg = '#fef3c7'; $iconColor = '#d97706'; }
                                    elseif (strpos($n['title'], 'Quiz') !== false) { $iconClass = 'question-circle'; $iconBg = '#f3e8ff'; $iconColor = '#9333ea'; }
                                    elseif (strpos($n['title'], 'Pengumuman') !== false) { $iconClass = 'bullhorn'; $iconBg = '#dbeafe'; $iconColor = '#2563eb'; }
                                    elseif (strpos($n['title'], 'Badge') !== false) { $iconClass = 'medal'; $iconBg = '#fef3c7'; $iconColor = '#d97706'; }
                                    elseif (strpos($n['title'], 'Aktivasi') !== false) { $iconClass = 'check-circle'; $iconBg = '#dcfce7'; $iconColor = '#16a34a'; }
                                    ?>
                                    <a href="<?= url('api/mark-read-single/' . $n['id']) ?>" class="notif-item <?= $unreadClass ?>">
                                        <div class="notif-icon" style="background:<?= $iconBg ?>;color:<?= $iconColor ?>;"><i class="fas fa-<?= $iconClass ?>"></i></div>
                                        <div class="notif-content">
                                            <span class="notif-title"><?= e($n['title']) ?></span>
                                            <span class="notif-msg"><?= e($n['message'] ?? '') ?></span>
                                            <span class="notif-time"><?= time_ago($n['created_at']) ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- User Dropdown -->
                <div class="user-dropdown" id="userDropdown">
                    <button class="user-avatar-btn">
                        <?php if (!empty($currentUser['avatar'])): ?>
                            <img src="<?= upload_url($currentUser['avatar']) ?>" alt="Avatar">
                        <?php else: ?>
                            <div class="avatar-placeholder"><?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <div class="user-info-mini">
                            <span class="user-name-mini"><?= e($currentUser['full_name']) ?></span>
                            <span class="user-role-mini"><?= ucfirst(e($currentUser['role'])) ?></span>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu" id="userMenu">
                        <div class="dropdown-header">
                            <div class="user-xp-info">
                                <span class="xp-badge">Level <?= $currentUser['level'] ?> &bull; <?= number_format($currentUser['xp_points']) ?> XP</span>
                            </div>
                        </div>
                        <a href="<?= url('profile') ?>" class="dropdown-item"><i class="fas fa-user"></i> Profile Saya</a>
                        <a href="<?= url('settings') ?>" class="dropdown-item"><i class="fas fa-cog"></i> Pengaturan</a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= url('logout') ?>" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            <?php 
            $flash_success = Session::flash('success');
            $flash_error = Session::flash('error');
            ?>
            <?php if ($flash_success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= e($flash_success) ?></div>
            <?php endif; ?>
            <?php if ($flash_error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($flash_error) ?></div>
            <?php endif; ?>

            <?php 
            $contentView = BASE_PATH . '/views/' . $_content_view . '.php';
            if (file_exists($contentView)) {
                require $contentView;
            }
            ?>
        </div>

        <!-- Copyright Footer -->
        <?php $copyright = app_setting('app_copyright'); ?>
        <?php if ($copyright): ?>
        <footer class="app-footer">
            <p><?= e($copyright) ?></p>
        </footer>
        <?php endif; ?>
    </main>

    <!-- Overlay for mobile sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="<?= asset('js/app.js') ?>"></script>
    <script>
    // Simple toggle for notification dropdown (no fetch needed)
    function toggleNotifPanel() {
        var panel = document.getElementById('notifDropdown');
        panel.classList.toggle('show');
    }

    // Close notif dropdown when clicking outside
    document.addEventListener('click', function(e) {
        var wrapper = document.querySelector('.header-btn-wrapper');
        var panel = document.getElementById('notifDropdown');
        if (wrapper && !wrapper.contains(e.target)) {
            panel.classList.remove('show');
        }
    });
    </script>
    <style>
    .notif-dropdown {
        display: none; position: absolute; top: 100%; right: 0; width: 320px; max-height: 400px;
        background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md);
        box-shadow: var(--shadow-lg); z-index: 1000; overflow: hidden; margin-top: 8px;
    }
    .notif-dropdown.show { display: block; }
    .notif-header { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--border-light); }
    .notif-header strong { font-size: 13px; color: var(--text-primary); }
    .notif-list { max-height: 340px; overflow-y: auto; }
    .notif-item { display: flex; align-items: flex-start; gap: 10px; padding: 12px 16px; text-decoration: none; transition: background 0.2s; border-bottom: 1px solid var(--border-light); }
    .notif-item:hover { background: var(--bg-hover); }
    .notif-item.notif-unread { background: rgba(59,73,223,0.04); }
    .notif-icon { width: 28px; height: 28px; border-radius: 50%; background: var(--primary-bg); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
    .notif-content { flex: 1; }
    .notif-title { display: block; font-size: 12px; font-weight: 600; color: var(--text-primary); margin-bottom: 2px; }
    .notif-msg { display: block; font-size: 11px; color: var(--text-muted); }
    .notif-time { display: block; font-size: 10px; color: var(--text-muted); margin-top: 3px; }
    /* Footer */
    .app-footer { text-align: center; padding: 16px 20px; margin-top: 30px; border-top: 1px solid var(--border-light); }
    .app-footer p { font-size: 12px; color: var(--text-muted); margin: 0; }
    </style>
    <?php if (isset($extraJs)): ?>
        <?php foreach ((array)$extraJs as $js): ?>
            <script src="<?= asset('js/' . $js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
