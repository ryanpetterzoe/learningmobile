<?php
/**
 * SimpleEdu - Subject Controller (Materials, Content within a subject)
 */

$db = Database::getInstance();
$prefix = $db->getPrefix();
$action = $GLOBALS['action'] ?? 'index';
$param = $GLOBALS['param'] ?? null;
$userId = Session::userId();

switch ($action) {
    case 'view':
        if (!$param) Router::redirect('classes');
        $subject = $db->fetch(
            "SELECT s.*, c.name as class_name, u.full_name as teacher_name 
             FROM {$prefix}subjects s 
             JOIN {$prefix}classes c ON s.class_id = c.id 
             JOIN {$prefix}users u ON s.teacher_id = u.id 
             WHERE s.id = ?",
            [$param]
        );
        if (!$subject) Router::redirect('classes');

        $materials = $db->fetchAll("SELECT * FROM {$prefix}materials WHERE subject_id = ? ORDER BY order_num, created_at", [$param]);
        $assignments = $db->fetchAll("SELECT * FROM {$prefix}assignments WHERE subject_id = ? ORDER BY deadline DESC", [$param]);
        $quizzes = $db->fetchAll("SELECT * FROM {$prefix}quizzes WHERE subject_id = ? ORDER BY created_at DESC", [$param]);

        $isTeacher = ($subject['teacher_id'] == $userId) || Auth::isAdmin();

        render_with_layout('subject/view', compact('subject', 'materials', 'assignments', 'quizzes', 'isTeacher') + ['pageTitle' => $subject['name']]);
        break;

    case 'add-material':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $data = [
                'subject_id' => $param,
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content'] ?? ''),
                'type' => $_POST['type'] ?? 'text',
                'video_url' => trim($_POST['video_url'] ?? ''),
                'created_at' => date('Y-m-d H:i:s')
            ];
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $path = upload_file($_FILES['file'], 'materials');
                if ($path) $data['file_path'] = $path;
            }
            $db->insert('materials', $data);
            Session::flash('success', 'Materi berhasil ditambahkan!');
        }
        Router::redirect('subject/view/' . $param);
        break;

    case 'delete-material':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $mat = $db->fetch("SELECT subject_id FROM {$prefix}materials WHERE id = ?", [$param]);
            $db->delete('materials', 'id = ?', [$param]);
            Session::flash('success', 'Materi dihapus.');
            Router::redirect('subject/view/' . $mat['subject_id']);
        }
        break;

    default:
        Router::redirect('classes');
}
