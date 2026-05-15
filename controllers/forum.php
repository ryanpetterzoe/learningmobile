<?php
/**
 * SimpleEdu - Forum Controller
 * FORUM PUBLIK - Semua orang bisa lihat semua diskusi (tidak ada filter kelas)
 * Supports nested replies (like Facebook comments)
 */

$db = Database::getInstance();
$prefix = $db->getPrefix();
$action = $GLOBALS['action'] ?? 'index';
$param = $GLOBALS['param'] ?? null;
$userId = Session::userId();
$userRole = Session::userRole();

switch ($action) {
    case 'index':
        $categories = $db->fetchAll(
            "SELECT fc.*, 
             (SELECT COUNT(*) FROM {$prefix}forum_posts WHERE category_id = fc.id) as post_count
             FROM {$prefix}forum_categories fc ORDER BY fc.order_num"
        );

        // Get all subjects for filter
        $allSubjects = $db->fetchAll("SELECT s.id, s.name FROM {$prefix}subjects s ORDER BY s.name");

        // Filters
        $filterCategory = $_GET['category_id'] ?? '';
        $filterSubject = $_GET['subject_id'] ?? '';
        $filterSort = $_GET['sort'] ?? 'terbaru';
        $filterSearch = trim($_GET['search'] ?? '');

        $where = "1=1";
        $params = [];

        if ($filterCategory) {
            $where .= " AND fp.category_id = ?";
            $params[] = (int)$filterCategory;
        }
        if ($filterSubject) {
            $where .= " AND fp.subject_id = ?";
            $params[] = (int)$filterSubject;
        }
        if ($filterSearch) {
            $where .= " AND (fp.title LIKE ? OR fp.content LIKE ?)";
            $params[] = "%{$filterSearch}%";
            $params[] = "%{$filterSearch}%";
        }

        // Sorting
        switch ($filterSort) {
            case 'terlama':
                $orderBy = "fp.is_pinned DESC, fp.created_at ASC";
                break;
            case 'populer':
                $orderBy = "fp.is_pinned DESC, (SELECT COUNT(*) FROM {$prefix}forum_likes WHERE post_id = fp.id) DESC, fp.views DESC";
                break;
            default: // terbaru
                $orderBy = "fp.is_pinned DESC, fp.updated_at DESC";
                break;
        }

        $recentPosts = $db->fetchAll(
            "SELECT fp.*, u.full_name, u.avatar, u.role as author_role, fc.name as category_name,
             (SELECT c2.name FROM {$prefix}classes c2 WHERE c2.id = u.class_id LIMIT 1) as author_class_name,
             (SELECT c3.name FROM {$prefix}class_members cm2 JOIN {$prefix}classes c3 ON cm2.class_id = c3.id WHERE cm2.user_id = u.id AND cm2.role = 'student' LIMIT 1) as author_class_fallback,
             (SELECT s.name FROM {$prefix}subjects s WHERE s.id = fp.subject_id LIMIT 1) as subject_name,
             (SELECT COUNT(*) FROM {$prefix}forum_replies WHERE post_id = fp.id) as reply_count,
             (SELECT COUNT(*) FROM {$prefix}forum_likes WHERE post_id = fp.id) as like_count
             FROM {$prefix}forum_posts fp 
             JOIN {$prefix}users u ON fp.author_id = u.id 
             JOIN {$prefix}forum_categories fc ON fp.category_id = fc.id
             WHERE {$where}
             ORDER BY {$orderBy} LIMIT 50",
            $params
        );

        // Get user's liked post IDs for showing active state
        $userLikedPosts = [];
        if ($userId) {
            $likedRows = $db->fetchAll("SELECT post_id FROM {$prefix}forum_likes WHERE user_id = ? AND post_id IS NOT NULL", [$userId]);
            $userLikedPosts = array_column($likedRows, 'post_id');
        }

        render_with_layout('forum/index', [
            'categories' => $categories,
            'allSubjects' => $allSubjects,
            'recentPosts' => $recentPosts,
            'role' => $userRole,
            'filterCategory' => $filterCategory,
            'filterSubject' => $filterSubject,
            'filterSort' => $filterSort,
            'filterSearch' => $filterSearch,
            'userLikedPosts' => $userLikedPosts,
            'pageTitle' => 'Forum Diskusi'
        ]);
        break;

    case 'category':
        if (!$param) Router::redirect('forum');
        $category = $db->fetch("SELECT * FROM {$prefix}forum_categories WHERE id = ?", [$param]);
        if (!$category) Router::redirect('forum');

        $posts = $db->fetchAll(
            "SELECT fp.*, u.full_name, u.avatar, u.role as author_role,
             (SELECT COUNT(*) FROM {$prefix}forum_replies WHERE post_id = fp.id) as reply_count
             FROM {$prefix}forum_posts fp JOIN {$prefix}users u ON fp.author_id = u.id
             WHERE fp.category_id = ?
             ORDER BY fp.is_pinned DESC, fp.updated_at DESC", [$param]
        );
        render_with_layout('forum/category', ['category' => $category, 'posts' => $posts, 'pageTitle' => $category['name']]);
        break;

    case 'post':
        if (!$param) Router::redirect('forum');
        $post = $db->fetch(
            "SELECT fp.*, u.full_name, u.avatar, u.role as author_role, u.level, fc.name as category_name,
             (SELECT c2.name FROM {$prefix}classes c2 WHERE c2.id = u.class_id LIMIT 1) as author_class_name,
             (SELECT c3.name FROM {$prefix}class_members cm2 JOIN {$prefix}classes c3 ON cm2.class_id = c3.id WHERE cm2.user_id = u.id AND cm2.role = 'student' LIMIT 1) as author_class_fallback,
             (SELECT s.name FROM {$prefix}subjects s WHERE s.id = fp.subject_id LIMIT 1) as subject_name
             FROM {$prefix}forum_posts fp 
             JOIN {$prefix}users u ON fp.author_id = u.id
             JOIN {$prefix}forum_categories fc ON fp.category_id = fc.id
             WHERE fp.id = ?", [$param]
        );
        if (!$post) Router::redirect('forum');

        $db->query("UPDATE {$prefix}forum_posts SET views = views + 1 WHERE id = ?", [$param]);
        $likeCount = $db->count('forum_likes', 'post_id = ? AND reply_id IS NULL', [$param]);
        $userLiked = $db->fetch("SELECT id FROM {$prefix}forum_likes WHERE post_id = ? AND reply_id IS NULL AND user_id = ?", [$param, $userId]) ? true : false;

        // Get all replies with like counts and user like status
        $replies = $db->fetchAll(
            "SELECT fr.*, u.full_name, u.avatar, u.role as author_role, u.level,
             (SELECT COUNT(*) FROM {$prefix}forum_likes WHERE reply_id = fr.id) as like_count,
             (SELECT COUNT(*) FROM {$prefix}forum_likes WHERE reply_id = fr.id AND user_id = ?) as user_liked
             FROM {$prefix}forum_replies fr JOIN {$prefix}users u ON fr.author_id = u.id
             WHERE fr.post_id = ? ORDER BY fr.created_at ASC", [$userId, $param]
        );

        // Organize replies into tree (parent + children)
        $parentReplies = [];
        $childReplies = [];
        foreach ($replies as $r) {
            $parentId = $r['parent_reply_id'] ?? null;
            if (!$parentId) {
                $parentReplies[] = $r;
            } else {
                $childReplies[$parentId][] = $r;
            }
        }

        render_with_layout('forum/post', [
            'post' => $post,
            'parentReplies' => $parentReplies,
            'childReplies' => $childReplies,
            'totalReplyCount' => count($replies),
            'likeCount' => $likeCount,
            'userLiked' => $userLiked,
            'userId' => $userId,
            'userRole' => $userRole,
            'pageTitle' => $post['title']
        ]);
        break;

    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) { Session::flash('error', 'Token tidak valid.'); Router::redirect('forum/create'); }

            $data = [
                'category_id' => (int)$_POST['category_id'],
                'author_id' => $userId,
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            if (!empty($_POST['subject_id'])) {
                $data['subject_id'] = (int)$_POST['subject_id'];
            }
            if (isset($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
                $uploadedPaths = [];
                $allowedExts = ['jpg','jpeg','png','gif','webp','mp4','webm','pdf','doc','docx','zip','rar'];
                $fileCount = count($_FILES['attachments']['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['attachments']['name'][$i],
                            'type' => $_FILES['attachments']['type'][$i],
                            'tmp_name' => $_FILES['attachments']['tmp_name'][$i],
                            'error' => $_FILES['attachments']['error'][$i],
                            'size' => $_FILES['attachments']['size'][$i],
                        ];
                        $path = upload_file($file, 'forum', $allowedExts);
                        if ($path) $uploadedPaths[] = $path;
                    }
                }
                if (!empty($uploadedPaths)) {
                    $data['attachment'] = implode('|', $uploadedPaths);
                }
            }
            $postId = $db->insert('forum_posts', $data);
            Gamification::awardXP($userId, 5, 'Membuat post di forum');
            Session::flash('success', 'Post berhasil dibuat!');
            Router::redirect('forum/post/' . $postId);
        }

        $categories = $db->fetchAll("SELECT * FROM {$prefix}forum_categories ORDER BY order_num");
        $allSubjects = $db->fetchAll("SELECT s.id, s.name, c.name as class_name FROM {$prefix}subjects s JOIN {$prefix}classes c ON s.class_id = c.id ORDER BY c.grade, s.name");
        render_with_layout('forum/create', ['categories' => $categories, 'allSubjects' => $allSubjects, 'role' => $userRole, 'pageTitle' => 'Buat Diskusi Baru']);
        break;

    case 'reply':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            if (!verify_csrf()) { Router::redirect('forum/post/' . $param); }
            $content = trim($_POST['content'] ?? '');
            if (empty($content)) { Router::redirect('forum/post/' . $param); }

            $data = [
                'post_id' => (int)$param,
                'author_id' => $userId,
                'content' => $content,
                'parent_reply_id' => !empty($_POST['parent_reply_id']) ? (int)$_POST['parent_reply_id'] : null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $path = upload_file($_FILES['attachment'], 'forum', ['jpg','jpeg','png','gif','webp','mp4','webm','pdf','doc','docx','zip']);
                if ($path) $data['attachment'] = $path;
            }
            $db->insert('forum_replies', $data);
            $db->query("UPDATE {$prefix}forum_posts SET updated_at = NOW() WHERE id = ?", [$param]);
            Gamification::awardXP($userId, 3, 'Membalas diskusi di forum');

            // Determine who to notify
            $post = $db->fetch("SELECT author_id, title FROM {$prefix}forum_posts WHERE id = ?", [$param]);
            $replierName = $db->fetch("SELECT full_name FROM {$prefix}users WHERE id = ?", [$userId])['full_name'] ?? 'Seseorang';

            if (!empty($_POST['parent_reply_id'])) {
                // This is a sub-reply: notify the parent reply author
                $parentReply = $db->fetch("SELECT author_id FROM {$prefix}forum_replies WHERE id = ?", [$_POST['parent_reply_id']]);
                if ($parentReply && (int)$parentReply['author_id'] !== (int)$userId) {
                    $db->insert('notifications', [
                        'user_id' => $parentReply['author_id'],
                        'title' => 'Balasan Komentar',
                        'message' => $replierName . ' membalas komentar Anda di "' . truncate($post['title'] ?? '', 35) . '"',
                        'type' => 'info',
                        'link' => url('forum/post/' . $param),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            } else {
                // Top-level reply: notify post author
                if ($post && (int)$post['author_id'] !== (int)$userId) {
                    $db->insert('notifications', [
                        'user_id' => $post['author_id'],
                        'title' => 'Komentar Baru',
                        'message' => $replierName . ' mengomentari postingan "' . truncate($post['title'], 40) . '"',
                        'type' => 'info',
                        'link' => url('forum/post/' . $param),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            Session::flash('success', 'Balasan terkirim!');
        }
        Router::redirect('forum/post/' . $param);
        break;

    case 'like':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $existing = $db->fetch("SELECT id FROM {$prefix}forum_likes WHERE post_id = ? AND reply_id IS NULL AND user_id = ?", [$param, $userId]);
            if ($existing) {
                $db->delete('forum_likes', 'id = ?', [$existing['id']]);
                $liked = false;
            } else {
                $db->insert('forum_likes', ['post_id' => (int)$param, 'reply_id' => null, 'user_id' => $userId, 'created_at' => date('Y-m-d H:i:s')]);
                $liked = true;

                // Notify post author about the like
                $post = $db->fetch("SELECT author_id, title FROM {$prefix}forum_posts WHERE id = ?", [$param]);
                if ($post && (int)$post['author_id'] !== (int)$userId) {
                    $likerName = $db->fetch("SELECT full_name FROM {$prefix}users WHERE id = ?", [$userId])['full_name'] ?? 'Seseorang';
                    $db->insert('notifications', [
                        'user_id' => $post['author_id'],
                        'title' => 'Post Disukai',
                        'message' => $likerName . ' menyukai postingan "' . truncate($post['title'], 40) . '"',
                        'type' => 'info',
                        'link' => url('forum/post/' . $param),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            // If AJAX request, return JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                $likeCount = $db->count('forum_likes', 'post_id = ? AND reply_id IS NULL', [$param]);
                json_response(['liked' => $liked, 'count' => $likeCount]);
            }

            // Otherwise redirect back
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            if (strpos($referer, 'forum/post/') !== false) {
                Router::redirect('forum/post/' . $param);
            }
            Router::redirect('forum');
        }
        Router::redirect('forum/post/' . $param);
        break;

    case 'like-reply':
        // Like/unlike a reply (comment)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $reply = $db->fetch("SELECT * FROM {$prefix}forum_replies WHERE id = ?", [$param]);
            if (!$reply) Router::redirect('forum');

            $existing = $db->fetch("SELECT id FROM {$prefix}forum_likes WHERE reply_id = ? AND user_id = ?", [$param, $userId]);
            if ($existing) {
                $db->delete('forum_likes', 'id = ?', [$existing['id']]);
            } else {
                $db->insert('forum_likes', ['post_id' => null, 'reply_id' => (int)$param, 'user_id' => $userId, 'created_at' => date('Y-m-d H:i:s')]);

                // Notify reply author
                if ((int)$reply['author_id'] !== (int)$userId) {
                    $likerName = $db->fetch("SELECT full_name FROM {$prefix}users WHERE id = ?", [$userId])['full_name'] ?? 'Seseorang';
                    $postTitle = $db->fetch("SELECT title FROM {$prefix}forum_posts WHERE id = ?", [$reply['post_id']])['title'] ?? '';
                    $db->insert('notifications', [
                        'user_id' => $reply['author_id'],
                        'title' => 'Komentar Disukai',
                        'message' => $likerName . ' menyukai komentar Anda di "' . truncate($postTitle, 35) . '"',
                        'type' => 'info',
                        'link' => url('forum/post/' . $reply['post_id']),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            Router::redirect('forum/post/' . $reply['post_id']);
        }
        Router::redirect('forum');
        break;

    case 'edit-reply':
        if (!$param) Router::redirect('forum');
        $reply = $db->fetch("SELECT * FROM {$prefix}forum_replies WHERE id = ?", [$param]);
        if (!$reply || (int)$reply['author_id'] !== (int)$userId) { Router::redirect('forum'); }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) { Router::redirect('forum/post/' . $reply['post_id']); }
            $newContent = trim($_POST['content'] ?? '');
            if (!empty($newContent)) {
                $db->update('forum_replies', ['content' => $newContent, 'edited_at' => date('Y-m-d H:i:s')], 'id = ?', [$param]);
                Session::flash('success', 'Komentar diperbarui!');
            }
        }
        Router::redirect('forum/post/' . $reply['post_id']);
        break;

    case 'delete-reply':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $reply = $db->fetch("SELECT * FROM {$prefix}forum_replies WHERE id = ?", [$param]);
            if ($reply && ((int)$reply['author_id'] === (int)$userId || in_array($userRole, ['admin','wali_kelas']))) {
                $postId = $reply['post_id'];
                // Also delete child replies (sub-comments)
                $db->delete('forum_replies', 'parent_reply_id = ?', [$param]);
                // Delete likes on this reply
                $db->delete('forum_likes', 'reply_id = ?', [$param]);
                // Delete the reply itself
                $db->delete('forum_replies', 'id = ?', [$param]);
                Session::flash('success', 'Komentar dihapus.');
                Router::redirect('forum/post/' . $postId);
            }
        }
        Router::redirect('forum');
        break;

    case 'edit':
        if (!$param) Router::redirect('forum');
        $post = $db->fetch("SELECT * FROM {$prefix}forum_posts WHERE id = ?", [$param]);
        if (!$post || (int)$post['author_id'] !== (int)$userId) { Session::flash('error', 'Hanya penulis yang bisa edit.'); Router::redirect('forum'); }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) { Router::redirect('forum/edit/' . $param); }
            $db->update('forum_posts', ['title' => trim($_POST['title']), 'content' => trim($_POST['content']), 'edited_at' => date('Y-m-d H:i:s'), 'edited_by' => $userId], 'id = ?', [$param]);
            Session::flash('success', 'Post diperbarui!');
            Router::redirect('forum/post/' . $param);
        }
        $categories = $db->fetchAll("SELECT * FROM {$prefix}forum_categories ORDER BY order_num");
        render_with_layout('forum/edit', ['post' => $post, 'categories' => $categories, 'pageTitle' => 'Edit Diskusi']);
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $post = $db->fetch("SELECT * FROM {$prefix}forum_posts WHERE id = ?", [$param]);
            if ($post && ((int)$post['author_id'] === (int)$userId || in_array($userRole, ['admin','wali_kelas']))) {
                $db->delete('forum_replies', 'post_id = ?', [$param]);
                $db->delete('forum_likes', 'post_id = ?', [$param]);
                $db->delete('forum_posts', 'id = ?', [$param]);
                Session::flash('success', 'Post dihapus.');
            }
        }
        Router::redirect('forum');
        break;

    case 'manage-categories':
        Auth::requireRole(['admin', 'guru', 'wali_kelas']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) { Router::redirect('forum/manage-categories'); }
            $catAction = $_POST['cat_action'] ?? '';
            if ($catAction === 'create' && !empty(trim($_POST['name'] ?? ''))) {
                $maxOrder = $db->fetch("SELECT MAX(order_num) as mx FROM {$prefix}forum_categories")['mx'] ?? 0;
                $db->insert('forum_categories', ['name' => trim($_POST['name']), 'icon' => trim($_POST['icon'] ?? '') ?: '💬', 'description' => trim($_POST['description'] ?? ''), 'order_num' => (int)$maxOrder + 1]);
                Session::flash('success', 'Kategori ditambahkan!');
            } elseif ($catAction === 'delete' && !empty($_POST['cat_id'])) {
                $cnt = $db->count('forum_posts', 'category_id = ?', [$_POST['cat_id']]);
                if ($cnt == 0) { $db->delete('forum_categories', 'id = ?', [$_POST['cat_id']]); Session::flash('success', 'Kategori dihapus.'); }
                else { Session::flash('error', 'Kategori masih punya post.'); }
            }
            Router::redirect('forum/manage-categories');
        }
        $categories = $db->fetchAll("SELECT fc.*, (SELECT COUNT(*) FROM {$prefix}forum_posts WHERE category_id = fc.id) as post_count FROM {$prefix}forum_categories fc ORDER BY fc.order_num");
        render_with_layout('forum/manage-categories', ['categories' => $categories, 'pageTitle' => 'Kelola Kategori Forum']);
        break;

    default:
        Router::redirect('forum');
}
