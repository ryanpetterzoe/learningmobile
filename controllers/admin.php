<?php
/**
 * SimpleEdu - Admin Panel Controller
 */

Auth::requireRole('admin');

$db = Database::getInstance();
$prefix = $db->getPrefix();
$action = $GLOBALS['action'] ?? 'index';
$param = $GLOBALS['param'] ?? null;

switch ($action) {
    case 'users':
        // Filter
        $filter = $_GET['filter'] ?? 'all';
        $search = $_GET['search'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = '1=1';
        $params = [];

        if ($filter === 'pending') { $where .= " AND status = 'pending'"; }
        elseif ($filter === 'active') { $where .= " AND status = 'active'"; }
        elseif ($filter === 'suspended') { $where .= " AND status = 'suspended'"; }
        elseif (in_array($filter, ['admin','guru','siswa','wali_kelas','orang_tua'])) {
            $where .= " AND role = ?";
            $params[] = $filter;
        }

        if ($search) {
            $where .= " AND (full_name LIKE ? OR email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $total = $db->fetch("SELECT COUNT(*) as cnt FROM {$prefix}users WHERE {$where}", $params)['cnt'];
        $totalPages = ceil($total / $perPage);
        
        $usersParams = array_merge($params, [$perPage, $offset]);
        $users = $db->fetchAll(
            "SELECT * FROM {$prefix}users WHERE {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            $usersParams
        );

        $data = compact('users', 'filter', 'search', 'page', 'totalPages', 'total');
        $data['pageTitle'] = 'Kelola Pengguna';
        render_with_layout('admin/users', $data);
        break;

    case 'user-approve':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $db->update('users', ['status' => 'active'], 'id = ?', [$param]);
            // Notify user
            $db->insert('notifications', [
                'user_id' => $param,
                'title' => 'Akun Diaktivasi',
                'message' => 'Selamat! Akun Anda telah diaktivasi oleh admin.',
                'type' => 'success',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            Session::flash('success', 'Pengguna berhasil diaktivasi!');
        }
        Router::redirect('admin/users?filter=pending');
        break;

    case 'user-suspend':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $db->update('users', ['status' => 'suspended'], 'id = ?', [$param]);
            Session::flash('success', 'Pengguna telah dinonaktifkan.');
        }
        Router::redirect('admin/users');
        break;

    case 'user-delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $db->delete('users', 'id = ? AND id != ?', [$param, Session::userId()]);
            Session::flash('success', 'Pengguna telah dihapus.');
        }
        Router::redirect('admin/users');
        break;

    case 'user-create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $fullName = trim($_POST['full_name'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'siswa';
            $nis = trim($_POST['nis'] ?? '');
            $nip = trim($_POST['nip'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            if (empty($email) || empty($fullName) || empty($password)) {
                Session::flash('error', 'Nama, email, dan password wajib diisi!');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Session::flash('error', 'Format email tidak valid!');
            } elseif (strlen($password) < 6) {
                Session::flash('error', 'Password minimal 6 karakter!');
            } else {
                $existing = $db->fetch("SELECT id FROM {$prefix}users WHERE email = ?", [$email]);
                if ($existing) {
                    Session::flash('error', 'Email sudah terdaftar!');
                } else {
                    $db->insert('users', [
                        'full_name' => $fullName,
                        'email' => $email,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'role' => in_array($role, ['admin','guru','siswa','wali_kelas','orang_tua']) ? $role : 'siswa',
                        'nis' => $nis ?: null,
                        'nip' => $nip ?: null,
                        'phone' => $phone ?: null,
                        'status' => 'active',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    Session::flash('success', 'Pengguna berhasil ditambahkan dan langsung aktif!');
                }
            }
        }
        Router::redirect('admin/users');
        break;

    case 'user-role':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $newRole = $_POST['role'] ?? '';
            if (in_array($newRole, ['admin','guru','siswa','wali_kelas','orang_tua'])) {
                $db->update('users', ['role' => $newRole], 'id = ?', [$param]);
                Session::flash('success', 'Role pengguna berhasil diubah.');
            }
        }
        Router::redirect('admin/users');
        break;

    case 'user-reset-password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $newPassword = $_POST['new_password'] ?? '';
            if (strlen($newPassword) < 6) {
                Session::flash('error', 'Password minimal 6 karakter!');
            } else {
                $db->update('users', [
                    'password' => password_hash($newPassword, PASSWORD_DEFAULT)
                ], 'id = ?', [$param]);
                Session::flash('success', 'Password berhasil direset!');
            }
        }
        Router::redirect('admin/users');
        break;

    case 'classes':
        $classes = $db->fetchAll(
            "SELECT c.*, u.full_name as homeroom_teacher,
             (SELECT COUNT(*) FROM {$prefix}class_members WHERE class_id = c.id AND role = 'student') as student_count
             FROM {$prefix}classes c 
             LEFT JOIN {$prefix}users u ON c.homeroom_teacher_id = u.id 
             ORDER BY c.grade, c.name"
        );
        $teachers = $db->fetchAll(
            "SELECT id, full_name FROM {$prefix}users WHERE role IN ('guru','wali_kelas') AND status = 'active' ORDER BY full_name"
        );

        $data = compact('classes', 'teachers');
        $data['pageTitle'] = 'Kelola Kelas';
        render_with_layout('admin/classes', $data);
        break;

    case 'class-create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $homeroomTeacherId = $_POST['homeroom_teacher_id'] ?: null;
            $classId = $db->insert('classes', [
                'name' => trim($_POST['name']),
                'grade' => trim($_POST['grade']),
                'major' => trim($_POST['major'] ?? ''),
                'academic_year' => trim($_POST['academic_year']),
                'homeroom_teacher_id' => $homeroomTeacherId,
                'description' => trim($_POST['description'] ?? ''),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            // Auto-add wali kelas as class member (teacher role)
            if ($classId && $homeroomTeacherId) {
                $existing = $db->fetch("SELECT id FROM {$prefix}class_members WHERE class_id = ? AND user_id = ?", [$classId, $homeroomTeacherId]);
                if (!$existing) {
                    $db->insert('class_members', [
                        'class_id' => $classId,
                        'user_id' => $homeroomTeacherId,
                        'role' => 'teacher',
                        'joined_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            Session::flash('success', 'Kelas berhasil ditambahkan!');
        }
        Router::redirect('admin/classes');
        break;

    case 'class-delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $db->delete('classes', 'id = ?', [$param]);
            Session::flash('success', 'Kelas berhasil dihapus.');
        }
        Router::redirect('admin/classes');
        break;

    case 'settings':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fields = ['app_name', 'school_name', 'app_desc', 'app_slogan', 'app_copyright', 'school_address', 'school_contact', 'primary_color', 'theme'];
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $db->setSetting($field, trim($_POST[$field]));
                }
            }
            // Logo upload
            if (isset($_FILES['app_logo']) && $_FILES['app_logo']['error'] === UPLOAD_ERR_OK) {
                $logoPath = upload_file($_FILES['app_logo'], 'system', ['png','jpg','jpeg','svg','webp']);
                if ($logoPath) {
                    $db->setSetting('app_logo', $logoPath);
                }
            }
            Session::flash('success', 'Pengaturan berhasil disimpan!');
            Router::redirect('admin/settings');
        }

        $settings = [];
        $allSettings = $db->fetchAll("SELECT setting_key, setting_value FROM {$prefix}settings");
        foreach ($allSettings as $s) {
            $settings[$s['setting_key']] = $s['setting_value'];
        }

        $data = compact('settings');
        $data['pageTitle'] = 'Pengaturan Sistem';
        render_with_layout('admin/settings', $data);
        break;

    case 'announcements':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $target = $_POST['target'] ?? 'all';
            $targetRole = $_POST['target_role'] ?? null;
            $announcementTitle = trim($_POST['title']);

            $db->insert('announcements', [
                'title' => $announcementTitle,
                'content' => trim($_POST['content']),
                'author_id' => Session::userId(),
                'target' => $target,
                'target_role' => $targetRole,
                'is_pinned' => isset($_POST['is_pinned']) ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Notify relevant users about the new announcement
            if ($target === 'all') {
                $targetUsers = $db->fetchAll("SELECT id FROM {$prefix}users WHERE status = 'active' AND id != ?", [Session::userId()]);
            } elseif ($target === 'role' && $targetRole) {
                $targetUsers = $db->fetchAll("SELECT id FROM {$prefix}users WHERE status = 'active' AND role = ? AND id != ?", [$targetRole, Session::userId()]);
            } else {
                $targetUsers = [];
            }

            foreach ($targetUsers as $tu) {
                $db->insert('notifications', [
                    'user_id' => $tu['id'],
                    'title' => 'Pengumuman Baru',
                    'message' => truncate($announcementTitle, 60),
                    'type' => 'info',
                    'link' => url('announcements'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            Session::flash('success', 'Pengumuman berhasil dibuat!');
            Router::redirect('admin/announcements');
        }

        $announcements = $db->fetchAll(
            "SELECT a.*, u.full_name as author_name FROM {$prefix}announcements a 
             JOIN {$prefix}users u ON a.author_id = u.id 
             ORDER BY a.is_pinned DESC, a.created_at DESC"
        );

        $data = compact('announcements');
        $data['pageTitle'] = 'Kelola Pengumuman';
        render_with_layout('admin/announcements', $data);
        break;

    case 'announcement-delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $db->delete('announcements', 'id = ?', [$param]);
            Session::flash('success', 'Pengumuman dihapus.');
        }
        Router::redirect('admin/announcements');
        break;

    case 'schedules':
        $classes = $db->fetchAll("SELECT id, name, grade FROM {$prefix}classes ORDER BY grade, name");
        $subjects = $db->fetchAll(
            "SELECT s.*, c.name as class_name, u.full_name as teacher_name 
             FROM {$prefix}subjects s 
             JOIN {$prefix}classes c ON s.class_id = c.id 
             JOIN {$prefix}users u ON s.teacher_id = u.id 
             ORDER BY c.name, s.name"
        );

        $selectedClass = $_GET['class_id'] ?? '';
        $schedules = [];
        if ($selectedClass) {
            $schedules = $db->fetchAll(
                "SELECT sc.*, sub.name as subject_name, sub.color, u.full_name as teacher_name
                 FROM {$prefix}schedules sc
                 JOIN {$prefix}subjects sub ON sc.subject_id = sub.id
                 JOIN {$prefix}users u ON sub.teacher_id = u.id
                 WHERE sc.class_id = ?
                 ORDER BY sc.day_of_week, sc.start_time",
                [$selectedClass]
            );
        }

        $data = compact('classes', 'subjects', 'schedules', 'selectedClass');
        $data['pageTitle'] = 'Kelola Jadwal';
        render_with_layout('admin/schedules', $data);
        break;

    case 'schedule-create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $classId = $_POST['class_id'] ?? '';
            $subjectId = $_POST['subject_id'] ?? '';
            $dayOfWeek = (int)($_POST['day_of_week'] ?? 1);
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            $room = trim($_POST['room'] ?? '');

            if ($classId && $subjectId && $startTime && $endTime) {
                $db->insert('schedules', [
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'room' => $room ?: null
                ]);
                Session::flash('success', 'Jadwal berhasil ditambahkan!');
            } else {
                Session::flash('error', 'Semua field wajib diisi!');
            }
            Router::redirect('admin/schedules?class_id=' . $classId);
        }
        Router::redirect('admin/schedules');
        break;

    case 'schedule-delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $schedule = $db->fetch("SELECT class_id FROM {$prefix}schedules WHERE id = ?", [$param]);
            $db->delete('schedules', 'id = ?', [$param]);
            Session::flash('success', 'Jadwal dihapus.');
            Router::redirect('admin/schedules?class_id=' . ($schedule['class_id'] ?? ''));
        }
        Router::redirect('admin/schedules');
        break;

    case 'subjects':
        $allSubjects = $db->fetchAll(
            "SELECT s.*, c.name as class_name, c.grade, u.full_name as teacher_name 
             FROM {$prefix}subjects s 
             JOIN {$prefix}classes c ON s.class_id = c.id 
             JOIN {$prefix}users u ON s.teacher_id = u.id 
             ORDER BY c.grade, c.name, s.name"
        );
        $classes = $db->fetchAll("SELECT id, name, grade FROM {$prefix}classes ORDER BY grade, name");
        $teachers = $db->fetchAll("SELECT id, full_name FROM {$prefix}users WHERE role IN ('guru','wali_kelas') AND status = 'active' ORDER BY full_name");

        $data = compact('allSubjects', 'classes', 'teachers');
        $data['pageTitle'] = 'Kelola Mata Pelajaran';
        render_with_layout('admin/subjects', $data);
        break;

    case 'subject-create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $classId = $_POST['class_id'] ?? '';
            $teacherId = $_POST['teacher_id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $color = $_POST['color'] ?? '#3B49DF';

            if ($classId && $teacherId && $name) {
                $db->insert('subjects', [
                    'class_id' => $classId,
                    'teacher_id' => $teacherId,
                    'name' => $name,
                    'description' => $description,
                    'color' => $color,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                Session::flash('success', 'Mata pelajaran berhasil ditambahkan!');
            } else {
                Session::flash('error', 'Kelas, guru, dan nama mata pelajaran wajib diisi!');
            }
        }
        Router::redirect('admin/subjects');
        break;

    case 'subject-delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $db->delete('subjects', 'id = ?', [$param]);
            Session::flash('success', 'Mata pelajaran dihapus.');
        }
        Router::redirect('admin/subjects');
        break;

    case 'teacher-subjects':
        // Show teacher subject assignment page
        if (!$param) Router::redirect('admin/users');
        $teacher = $db->fetch("SELECT id, full_name, role FROM {$prefix}users WHERE id = ? AND role IN ('guru','wali_kelas')", [$param]);
        if (!$teacher) { Session::flash('error', 'Guru tidak ditemukan.'); Router::redirect('admin/users'); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Save teacher subject assignments
            $selectedSubjects = $_POST['subjects'] ?? [];
            // Remove old assignments
            $db->delete('teacher_subjects', 'teacher_id = ?', [$param]);
            // Insert new assignments and update subjects.teacher_id
            foreach ($selectedSubjects as $subjectId) {
                $db->insert('teacher_subjects', [
                    'teacher_id' => $param,
                    'subject_id' => (int)$subjectId,
                    'assigned_at' => date('Y-m-d H:i:s')
                ]);
                // Update subjects.teacher_id
                $db->update('subjects', ['teacher_id' => $param], 'id = ?', [(int)$subjectId]);
                // Also add teacher to class_members for the subject's class
                $subject = $db->fetch("SELECT class_id FROM {$prefix}subjects WHERE id = ?", [(int)$subjectId]);
                if ($subject) {
                    $existingMember = $db->fetch("SELECT id FROM {$prefix}class_members WHERE class_id = ? AND user_id = ?", [$subject['class_id'], $param]);
                    if (!$existingMember) {
                        $db->insert('class_members', [
                            'class_id' => $subject['class_id'],
                            'user_id' => $param,
                            'role' => 'teacher',
                            'joined_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
            Session::flash('success', 'Mata pelajaran guru berhasil diperbarui!');
            Router::redirect('admin/teacher-subjects/' . $param);
        }

        // Get all subjects grouped by class
        $allSubjects = $db->fetchAll(
            "SELECT s.*, c.name as class_name, c.grade FROM {$prefix}subjects s 
             JOIN {$prefix}classes c ON s.class_id = c.id 
             ORDER BY c.grade, c.name, s.name"
        );
        // Get currently assigned subjects
        $assignedSubjects = $db->fetchAll(
            "SELECT subject_id FROM {$prefix}teacher_subjects WHERE teacher_id = ?", [$param]
        );
        $assignedIds = array_column($assignedSubjects, 'subject_id');

        $data = compact('teacher', 'allSubjects', 'assignedIds');
        $data['pageTitle'] = 'Assign Mata Pelajaran - ' . $teacher['full_name'];
        render_with_layout('admin/teacher-subjects', $data);
        break;

    case 'competencies':
        // CRUD for Kompetensi Keahlian
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $compAction = $_POST['comp_action'] ?? '';
            if ($compAction === 'create') {
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                if (!empty($name)) {
                    $db->insert('competencies', [
                        'name' => $name,
                        'description' => $description ?: null,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    Session::flash('success', 'Kompetensi keahlian berhasil ditambahkan!');
                } else {
                    Session::flash('error', 'Nama kompetensi wajib diisi!');
                }
            } elseif ($compAction === 'delete' && !empty($_POST['comp_id'])) {
                // Check if any user uses this competency
                $usageCount = $db->count('users', 'competency_id = ?', [$_POST['comp_id']]);
                if ($usageCount == 0) {
                    $db->delete('competencies', 'id = ?', [$_POST['comp_id']]);
                    Session::flash('success', 'Kompetensi keahlian dihapus.');
                } else {
                    Session::flash('error', 'Kompetensi masih digunakan oleh ' . $usageCount . ' pengguna.');
                }
            } elseif ($compAction === 'edit' && !empty($_POST['comp_id'])) {
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                if (!empty($name)) {
                    $db->update('competencies', [
                        'name' => $name,
                        'description' => $description ?: null
                    ], 'id = ?', [$_POST['comp_id']]);
                    Session::flash('success', 'Kompetensi keahlian diperbarui!');
                }
            }
            Router::redirect('admin/competencies');
        }

        $competencies = $db->fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM {$prefix}users WHERE competency_id = c.id) as user_count 
             FROM {$prefix}competencies c ORDER BY c.name"
        );
        $data = compact('competencies');
        $data['pageTitle'] = 'Kelola Kompetensi Keahlian';
        render_with_layout('admin/competencies', $data);
        break;

    default:
        Router::redirect('admin/users');
        break;
}
