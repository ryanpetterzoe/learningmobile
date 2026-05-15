<?php
/**
 * SimpleEdu - Certificates Controller
 */

$db = Database::getInstance();
$prefix = $db->getPrefix();
$action = $GLOBALS['action'] ?? 'index';
$param = $GLOBALS['param'] ?? null;
$userId = Session::userId();

switch ($action) {
    case 'index':
        $certificates = $db->fetchAll("SELECT * FROM {$prefix}certificates WHERE user_id = ? ORDER BY issued_date DESC", [$userId]);
        render_with_layout('certificates/index', compact('certificates') + ['pageTitle' => 'Sertifikat']);
        break;

    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id' => $userId,
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description'] ?? ''),
                'issuer' => trim($_POST['issuer'] ?? ''),
                'issued_date' => $_POST['issued_date'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $path = upload_file($_FILES['file'], 'certificates', ['pdf','jpg','jpeg','png']);
                if ($path) $data['file_path'] = $path;
            }
            $db->insert('certificates', $data);
            Session::flash('success', 'Sertifikat berhasil ditambahkan!');
        }
        Router::redirect('certificates');
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $db->delete('certificates', 'id = ? AND user_id = ?', [$param, $userId]);
            Session::flash('success', 'Sertifikat dihapus.');
        }
        Router::redirect('certificates');
        break;

    default:
        Router::redirect('certificates');
}
