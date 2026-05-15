<?php
/**
 * SimpleEdu - Portfolio Controller
 */

$db = Database::getInstance();
$prefix = $db->getPrefix();
$action = $GLOBALS['action'] ?? 'index';
$param = $GLOBALS['param'] ?? null;
$userId = Session::userId();

switch ($action) {
    case 'index':
        $portfolios = $db->fetchAll("SELECT * FROM {$prefix}portfolio WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
        render_with_layout('portfolio/index', compact('portfolios') + ['pageTitle' => 'Portofolio']);
        break;

    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id' => $userId,
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description'] ?? ''),
                'category' => trim($_POST['category'] ?? ''),
                'link_url' => trim($_POST['link_url'] ?? ''),
                'created_at' => date('Y-m-d H:i:s')
            ];
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $path = upload_file($_FILES['file'], 'portfolio');
                if ($path) $data['file_path'] = $path;
            }
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $path = upload_file($_FILES['thumbnail'], 'portfolio', ['jpg','jpeg','png','webp']);
                if ($path) $data['thumbnail'] = $path;
            }
            $db->insert('portfolio', $data);
            Gamification::awardXP($userId, 10, 'Upload portofolio baru');
            Session::flash('success', 'Portofolio berhasil ditambahkan!');
        }
        Router::redirect('portfolio');
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $db->delete('portfolio', 'id = ? AND user_id = ?', [$param, $userId]);
            Session::flash('success', 'Portofolio dihapus.');
        }
        Router::redirect('portfolio');
        break;

    default:
        Router::redirect('portfolio');
}
