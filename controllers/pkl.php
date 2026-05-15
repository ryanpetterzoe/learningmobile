<?php
/**
 * SimpleEdu - PKL (Praktek Kerja Lapangan) Controller
 */

$db = Database::getInstance();
$prefix = $db->getPrefix();
$action = $GLOBALS['action'] ?? 'index';
$param = $GLOBALS['param'] ?? null;
$userId = Session::userId();
$role = Session::userRole();

switch ($action) {
    case 'index':
        if ($role === 'siswa') {
            $pkl = $db->fetch("SELECT * FROM {$prefix}pkl WHERE student_id = ? ORDER BY created_at DESC LIMIT 1", [$userId]);
            $journals = [];
            if ($pkl) {
                $journals = $db->fetchAll("SELECT * FROM {$prefix}pkl_journals WHERE pkl_id = ? ORDER BY date DESC", [$pkl['id']]);
            }
            render_with_layout('pkl/student', compact('pkl', 'journals') + ['pageTitle' => 'PKL / Magang']);
        } else {
            $pklList = $db->fetchAll(
                "SELECT p.*, u.full_name, u.avatar, u.nis FROM {$prefix}pkl p JOIN {$prefix}users u ON p.student_id = u.id ORDER BY p.created_at DESC"
            );
            render_with_layout('pkl/manage', compact('pklList') + ['pageTitle' => 'Kelola PKL']);
        }
        break;

    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db->insert('pkl', [
                'student_id' => $userId,
                'company_name' => trim($_POST['company_name']),
                'company_address' => trim($_POST['company_address'] ?? ''),
                'supervisor_name' => trim($_POST['supervisor_name'] ?? ''),
                'supervisor_phone' => trim($_POST['supervisor_phone'] ?? ''),
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            Session::flash('success', 'Pendaftaran PKL berhasil! Menunggu persetujuan.');
        }
        Router::redirect('pkl');
        break;

    case 'journal':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $data = [
                'pkl_id' => $param,
                'date' => $_POST['date'],
                'activity' => trim($_POST['activity']),
                'notes' => trim($_POST['notes'] ?? ''),
                'created_at' => date('Y-m-d H:i:s')
            ];
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $path = upload_file($_FILES['photo'], 'pkl', ['jpg','jpeg','png','webp']);
                if ($path) $data['photo'] = $path;
            }
            $db->insert('pkl_journals', $data);
            Gamification::awardXP($userId, 5, 'Mengisi jurnal PKL');
            Session::flash('success', 'Jurnal PKL berhasil ditambahkan!');
        }
        Router::redirect('pkl');
        break;

    case 'journals':
        // Guru/Admin: view student's journal entries
        Auth::requireRole(['admin', 'guru', 'wali_kelas']);
        if (!$param) Router::redirect('pkl');

        $pkl = $db->fetch(
            "SELECT p.*, u.full_name, u.avatar, u.nis FROM {$prefix}pkl p JOIN {$prefix}users u ON p.student_id = u.id WHERE p.id = ?",
            [$param]
        );
        if (!$pkl) { Session::flash('error', 'Data PKL tidak ditemukan.'); Router::redirect('pkl'); }

        $journals = $db->fetchAll("SELECT * FROM {$prefix}pkl_journals WHERE pkl_id = ? ORDER BY date ASC", [$param]);

        // Handle verify journal
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['verify_journal_id'])) {
            $db->update('pkl_journals', ['verified' => 1], 'id = ?', [$_POST['verify_journal_id']]);
            Session::flash('success', 'Jurnal diverifikasi.');
            Router::redirect('pkl/journals/' . $param);
        }

        render_with_layout('pkl/journals', compact('pkl', 'journals') + ['pageTitle' => 'Jurnal PKL - ' . $pkl['full_name']]);
        break;

    case 'print':
        // Student: print journal report
        if (!$param) Router::redirect('pkl');

        $pkl = $db->fetch("SELECT * FROM {$prefix}pkl WHERE id = ?", [$param]);
        if (!$pkl) Router::redirect('pkl');

        // Only the student themselves or guru/admin can print
        if ($role === 'siswa' && (int)$pkl['student_id'] !== (int)$userId) {
            Router::redirect('pkl');
        }

        $student = $db->fetch("SELECT full_name, nis FROM {$prefix}users WHERE id = ?", [$pkl['student_id']]);
        $journals = $db->fetchAll("SELECT * FROM {$prefix}pkl_journals WHERE pkl_id = ? ORDER BY date ASC", [$param]);

        // Get form data from query params (passed from form)
        $printData = [
            'kabupaten' => $_GET['kabupaten'] ?? '',
            'tanggal' => $_GET['tanggal'] ?? date('Y-m-d'),
            'pembimbing' => $_GET['pembimbing'] ?? $pkl['supervisor_name'],
            'student_name' => $student['full_name'] ?? '',
        ];

        // If no kabupaten provided, show form first
        if (empty($printData['kabupaten'])) {
            render_with_layout('pkl/print-form', compact('pkl', 'student', 'journals') + ['pageTitle' => 'Cetak Laporan PKL']);
        } else {
            // Render print layout (no main layout - standalone page)
            require BASE_PATH . '/views/pkl/print.php';
            exit;
        }
        break;

    case 'approve':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            Auth::requireRole(['admin', 'guru', 'wali_kelas']);
            $db->update('pkl', ['status' => 'active'], 'id = ?', [$param]);
            Session::flash('success', 'PKL disetujui.');
        }
        Router::redirect('pkl');
        break;

    default:
        Router::redirect('pkl');
}
