<?php
/**
 * SimpleEdu - Main Layout (Mobile-First Redesign)
 * 
 * Modern mobile app-style layout with bottom navigation
 */
$currentUser = Session::user();

// If user not found (reinstall, session expired), redirect to login
if (!$currentUser) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$currentRoute = trim($_GET['route'] ?? '', '/');
$routeParts = explode('/', $currentRoute);
$activePage = $routeParts[0] ?? 'dashboard';

// Get unread notifications count
$db = Database::getInstance();
$prefix = $db->getPrefix();
$notifCount = $db->count('notifications', 'user_id = ? AND is_read = 0', [Session::userId()]);
$notifications = $db->fetchAll(
    "SELECT * FROM {$prefix}notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20",
    [Session::userId()]
);
$appLogo = app_setting('app_logo');
$appName = app_setting('app_name', 'SimpleEdu');
$schoolName = app_setting('school_name', 'Sekolah');
$userTheme = Session::get('user_theme', $currentUser['theme'] ?? 'light');
?>
<!DOCTYPE html>
<html lang="id" data-theme="<?= e($userTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title><?= e($pageTitle ?? 'Dashboard') ?> - <?= e(APP_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <?php if (isset($extraCss)): ?>
        <?php foreach ((array)$extraCss as $css): ?>
            <link rel="stylesheet" href="<?= asset('css/' . $css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- App Container -->
    <div class="app-container">
        <!-- Top Header - Mobile Style -->
        <header class="app-header">
            <div class="header-top">
                <div class="header-location">
                    <button class="btn-icon" id="menuToggle" aria-label="Menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="location-info">
                        <small>Lokasi Anda</small>
                        <h3><?= e($schoolName) ?> <i class="fas fa-chevron-down"></i></h3>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="btn-icon" id="themeToggle" aria-label="Toggle Theme">
                        <i class="fas fa-<?= $userTheme === 'dark' ? 'sun' : 'moon' ?>"></i>
                    </button>
                    <div class="notif-wrapper">
                        <button class="btn-icon" id="notifBtn" onclick="toggleNotifPanel()" aria-label="Notifikasi">
                            <i class="fas fa-bell"></i>
                            <?php if ($notifCount > 0): ?>
                                <span class="notif-badge"><?= $notifCount ?></span>
                            <?php endif; ?>
                        </button>
                        <!-- Notification Dropdown -->
                        <div class="notif-dropdown" id="notifDropdown">
                            <div class="notif-header">
                                <strong>Notifikasi</strong>
                                <?php if ($notifCount > 0): ?>
                                    <form method="POST" action="<?= url('api/mark-read') ?>" style="display:inline;">
                                        <button type="submit" class="notif-mark-read">Tandai dibaca</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="notif-list">
                                <?php if (empty($notifications)): ?>
                                    <p class="notif-empty">Tidak ada notifikasi</p>
                                <?php else: ?>
                                    <?php foreach ($notifications as $n): ?>
                                        <?php 
                                        $unreadClass = empty($n['is_read']) ? 'notif-unread' : '';
                                        $iconClass = 'info-circle'; $iconColor = '#3B49DF';
                                        if ($n['type'] === 'success') { $iconClass = 'check-circle'; $iconColor = '#10b981'; }
                                        elseif ($n['type'] === 'warning') { $iconClass = 'exclamation-triangle'; $iconColor = '#f59e0b'; }
                                        elseif ($n['type'] === 'error') { $iconClass = 'exclamation-circle'; $iconColor = '#ef4444'; }
                                        ?>
                                        <a href="<?= url('api/mark-read-single/' . $n['id']) ?>" class="notif-item <?= $unreadClass ?>">
                                            <div class="notif-icon" style="color:<?= $iconColor ?>"><i class="fas fa-<?= $iconClass ?>"></i></div>
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
                    <a href="<?= url('profile') ?>" class="avatar-btn">
                        <?php if (!empty($currentUser['avatar'])): ?>
                            <img src="<?= upload_url($currentUser['avatar']) ?>" alt="Avatar">
                        <?php else: ?>
                            <div class="avatar-placeholder"><?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </header>

        <!-- Slide-out Menu (Side Panel) -->
        <div class="side-panel-overlay" id="sidePanelOverlay"></div>
        <aside class="side-panel" id="sidePanel">
            <div class="side-panel-header">
                <div class="side-panel-user">
                    <?php if (!empty($currentUser['avatar'])): ?>
                        <img src="<?= upload_url($currentUser['avatar']) ?>" alt="Avatar" class="side-panel-avatar">
                    <?php else: ?>
                        <div class="avatar-placeholder lg"><?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <div class="side-panel-user-info">
                        <h4><?= e($currentUser['full_name'] ?? '') ?></h4>
                        <span class="user-role-badge"><?= ucfirst(e($currentUser['role'] ?? 'user')) ?></span>
                        <span class="user-xp">Level <?= $currentUser['level'] ?? 1 ?> &bull; <?= number_format($currentUser['xp_points'] ?? 0) ?> XP</span>
                    </div>
                </div>
                <button class="btn-icon" id="sidePanelClose"><i class="fas fa-times"></i></button>
            </div>
            <nav class="side-panel-nav">
                <?php if (Auth::isAdmin()): ?>
                <div class="nav-group">
                    <span class="nav-group-title">Admin</span>
                    <a href="<?= url('admin/users') ?>" class="nav-item"><i class="fas fa-users-cog"></i> Kelola Pengguna</a>
                    <a href="<?= url('admin/settings') ?>" class="nav-item"><i class="fas fa-school"></i> Identitas Sekolah</a>
                    <a href="<?= url('admin/subjects') ?>" class="nav-item"><i class="fas fa-book"></i> Mata Pelajaran</a>
                    <a href="<?= url('admin/schedules') ?>" class="nav-item"><i class="fas fa-calendar-alt"></i> Kelola Jadwal</a>
                    <a href="<?= url('admin/classes') ?>" class="nav-item"><i class="fas fa-school"></i> Kelola Kelas</a>
                    <a href="<?= url('admin/competencies') ?>" class="nav-item"><i class="fas fa-graduation-cap"></i> Kompetensi</a>
                </div>
                <?php endif; ?>
                <div class="nav-group">
                    <span class="nav-group-title">Akademik</span>
                    <a href="<?= url('classes') ?>" class="nav-item"><i class="fas fa-chalkboard-teacher"></i> Kelas</a>
                    <a href="<?= url('assignments') ?>" class="nav-item"><i class="fas fa-tasks"></i> Tugas</a>
                    <a href="<?= url('quiz') ?>" class="nav-item"><i class="fas fa-question-circle"></i> Quiz & CBT</a>
                    <a href="<?= url('grades') ?>" class="nav-item"><i class="fas fa-chart-line"></i> Nilai</a>
                    <a href="<?= url('attendance') ?>" class="nav-item"><i class="fas fa-calendar-check"></i> Kehadiran</a>
                    <a href="<?= url('schedule') ?>" class="nav-item"><i class="fas fa-clock"></i> Jadwal</a>
                </div>
                <div class="nav-group">
                    <span class="nav-group-title">SMK & Komunitas</span>
                    <a href="<?= url('pkl') ?>" class="nav-item"><i class="fas fa-building"></i> PKL / Magang</a>
                    <a href="<?= url('portfolio') ?>" class="nav-item"><i class="fas fa-folder-open"></i> Portofolio</a>
                    <a href="<?= url('certificates') ?>" class="nav-item"><i class="fas fa-certificate"></i> Sertifikat</a>
                    <a href="<?= url('forum') ?>" class="nav-item"><i class="fas fa-comments"></i> Forum</a>
                    <a href="<?= url('announcements') ?>" class="nav-item"><i class="fas fa-bullhorn"></i> Pengumuman</a>
                </div>
                <div class="nav-group">
                    <span class="nav-group-title">Akun</span>
                    <a href="<?= url('profile') ?>" class="nav-item"><i class="fas fa-user"></i> Profil Saya</a>
                    <a href="<?= url('settings') ?>" class="nav-item"><i class="fas fa-cog"></i> Pengaturan</a>
                    <a href="<?= url('logout') ?>" class="nav-item text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="app-main">
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
        </main>

        <!-- Bottom Navigation -->
        <nav class="bottom-nav">
            <a href="<?= url('dashboard') ?>" class="bottom-nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                <span>Beranda</span>
            </a>
            <a href="<?= url('classes') ?>" class="bottom-nav-item <?= $activePage === 'classes' ? 'active' : '' ?>">
                <i class="fas fa-book-open"></i>
                <span>Kelas</span>
            </a>
            <a href="<?= url('assignments') ?>" class="bottom-nav-item <?= $activePage === 'assignments' ? 'active' : '' ?>">
                <i class="fas fa-tasks"></i>
                <span>Tugas</span>
            </a>
            <a href="<?= url('forum') ?>" class="bottom-nav-item <?= $activePage === 'forum' ? 'active' : '' ?>">
                <i class="fas fa-comments"></i>
                <span>Forum</span>
            </a>
            <a href="<?= url('profile') ?>" class="bottom-nav-item <?= $activePage === 'profile' ? 'active' : '' ?>">
                <i class="fas fa-user"></i>
                <span>Profil</span>
            </a>
        </nav>
    </div>

    <script src="<?= asset('js/app.js') ?>"></script>
    <?php if (isset($extraJs)): ?>
        <?php foreach ((array)$extraJs as $js): ?>
            <script src="<?= asset('js/' . $js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
