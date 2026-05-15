<?php
/**
 * SimpleEdu - Profile Controller
 */

$db = Database::getInstance();
$prefix = $db->getPrefix();
$userId = Session::userId();
$user = Session::user();
$action = $GLOBALS['action'] ?? 'index';
$param = $GLOBALS['param'] ?? null;

switch ($action) {
    case 'view':
        // Public profile view
        if (!$param) Router::redirect('forum');
        $profileUser = $db->fetch("SELECT id, full_name, email, avatar, role, bio, level, xp_points, created_at FROM {$prefix}users WHERE id = ?", [$param]);
        if (!$profileUser) {
            Session::flash('error', 'Pengguna tidak ditemukan.');
            Router::redirect('forum');
        }

        // Get class name from class_members
        $profileUser['class_name'] = '';
        $cls = $db->fetch("SELECT c.name FROM {$prefix}class_members cm JOIN {$prefix}classes c ON cm.class_id = c.id WHERE cm.user_id = ? AND cm.role = 'student' LIMIT 1", [$param]);
        if ($cls) $profileUser['class_name'] = $cls['name'];

        // Get competency name (table may not exist)
        $profileUser['competency_name'] = '';
        try {
            $compId = $db->fetch("SELECT competency_id FROM {$prefix}users WHERE id = ?", [$param]);
            if ($compId && !empty($compId['competency_id'])) {
                $comp = $db->fetch("SELECT name FROM {$prefix}competencies WHERE id = ?", [$compId['competency_id']]);
                if ($comp) $profileUser['competency_name'] = $comp['name'];
            }
        } catch (Exception $e) {
            // Columns or table don't exist yet
        }

        // Stats
        $postCount = $db->count('forum_posts', 'author_id = ?', [$param]);
        $commentCount = $db->count('forum_replies', 'author_id = ?', [$param]);

        // Get user's forum posts
        $userPosts = $db->fetchAll(
            "SELECT fp.*, fc.name as category_name,
             (SELECT COUNT(*) FROM {$prefix}forum_replies WHERE post_id = fp.id) as reply_count,
             (SELECT COUNT(*) FROM {$prefix}forum_likes WHERE post_id = fp.id) as like_count
             FROM {$prefix}forum_posts fp
             JOIN {$prefix}forum_categories fc ON fp.category_id = fc.id
             WHERE fp.author_id = ?
             ORDER BY fp.created_at DESC LIMIT 20", [$param]
        );

        render_with_layout('profile/view', [
            'profileUser' => $profileUser,
            'postCount' => $postCount,
            'commentCount' => $commentCount,
            'userPosts' => $userPosts,
            'pageTitle' => $profileUser['full_name'] . ' - Profil'
        ]);
        break;

    default:
        // Self-profile edit (existing functionality)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'full_name' => trim($_POST['full_name'] ?? $user['full_name']),
                'phone' => trim($_POST['phone'] ?? ''),
                'bio' => trim($_POST['bio'] ?? ''),
            ];

            // Handle birth_date update
            if (isset($_POST['birth_date'])) {
                $data['birth_date'] = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;
            }

            // Handle competency_id update
            if (isset($_POST['competency_id'])) {
                $data['competency_id'] = !empty($_POST['competency_id']) ? (int)$_POST['competency_id'] : null;
            }

            // Handle avatar upload
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatarPath = upload_file($_FILES['avatar'], 'avatars', ['jpg','jpeg','png','webp','gif']);
                if ($avatarPath) {
                    $data['avatar'] = $avatarPath;
                    Session::set('user_avatar', $avatarPath);
                }
            }

            // Password change
            if (!empty($_POST['new_password'])) {
                if (!password_verify($_POST['current_password'] ?? '', $user['password'])) {
                    Session::flash('error', 'Password lama salah!');
                    Router::redirect('profile');
                }
                if ($_POST['new_password'] !== $_POST['confirm_password']) {
                    Session::flash('error', 'Konfirmasi password tidak cocok!');
                    Router::redirect('profile');
                }
                $data['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            }

            $db->update('users', $data, 'id = ?', [$userId]);
            Session::set('user_name', $data['full_name']);
            Session::flash('success', 'Profil berhasil diperbarui!');
            Router::redirect('profile');
        }

        // Get competencies for dropdown (table may not exist on older installs)
        $competencies = [];
        try {
            $competencies = $db->fetchAll("SELECT * FROM {$prefix}competencies ORDER BY name");
        } catch (Exception $e) {
            // Table doesn't exist yet, skip
        }

        // Get user badges and stats
        $badges = Gamification::getUserBadges($userId);
        $xpHistory = Gamification::getXPHistory($userId, 10);
        $xpForNext = xp_for_level($user['level'] + 1);
        $xpProgress = $xpForNext > 0 ? min(100, ($user['xp_points'] / $xpForNext) * 100) : 100;

        render_with_layout('profile/index', compact('user', 'badges', 'xpHistory', 'xpProgress', 'xpForNext', 'competencies') + ['pageTitle' => 'Profile Saya']);
        break;
}
