<?php
/**
 * SimpleEdu - Classes Controller
 */

$db = Database::getInstance();
$prefix = $db->getPrefix();
$action = $GLOBALS['action'] ?? 'index';
$param = $GLOBALS['param'] ?? null;
$userId = Session::userId();
$role = Session::userRole();

switch ($action) {
    case 'index':
        if ($role === 'siswa' || $role === 'orang_tua') {
            $classes = $db->fetchAll(
                "SELECT c.*, u.full_name as homeroom_teacher,
                 (SELECT COUNT(*) FROM {$prefix}class_members WHERE class_id = c.id AND role = 'student') as student_count
                 FROM {$prefix}class_members cm
                 JOIN {$prefix}classes c ON cm.class_id = c.id
                 LEFT JOIN {$prefix}users u ON c.homeroom_teacher_id = u.id
                 WHERE cm.user_id = ?
                 ORDER BY c.grade, c.name",
                [$userId]
            );
        } elseif ($role === 'guru' || $role === 'wali_kelas') {
            $classes = $db->fetchAll(
                "SELECT DISTINCT c.*, u.full_name as homeroom_teacher,
                 (SELECT COUNT(*) FROM {$prefix}class_members WHERE class_id = c.id AND role = 'student') as student_count
                 FROM {$prefix}classes c
                 LEFT JOIN {$prefix}users u ON c.homeroom_teacher_id = u.id
                 LEFT JOIN {$prefix}subjects s ON s.class_id = c.id
                 WHERE c.homeroom_teacher_id = ? OR s.teacher_id = ?
                 ORDER BY c.grade, c.name",
                [$userId, $userId]
            );
        } else {
            $classes = $db->fetchAll(
                "SELECT c.*, u.full_name as homeroom_teacher,
                 (SELECT COUNT(*) FROM {$prefix}class_members WHERE class_id = c.id AND role = 'student') as student_count
                 FROM {$prefix}classes c
                 LEFT JOIN {$prefix}users u ON c.homeroom_teacher_id = u.id
                 ORDER BY c.grade, c.name"
            );
        }

        render_with_layout('classes/index', ['classes' => $classes, 'pageTitle' => 'Kelas Saya']);
        break;

    case 'view':
        if (!$param) Router::redirect('classes');
        
        $class = $db->fetch("SELECT c.*, u.full_name as homeroom_teacher FROM {$prefix}classes c LEFT JOIN {$prefix}users u ON c.homeroom_teacher_id = u.id WHERE c.id = ?", [$param]);
        if (!$class) { Router::redirect('classes'); }

        $subjects = $db->fetchAll(
            "SELECT s.*, u.full_name as teacher_name FROM {$prefix}subjects s 
             JOIN {$prefix}users u ON s.teacher_id = u.id 
             WHERE s.class_id = ? ORDER BY s.name",
            [$param]
        );

        $members = $db->fetchAll(
            "SELECT u.*, cm.role as member_role, cm.joined_at FROM {$prefix}class_members cm 
             JOIN {$prefix}users u ON cm.user_id = u.id 
             WHERE cm.class_id = ? ORDER BY cm.role, u.full_name",
            [$param]
        );

        $teachers = $db->fetchAll("SELECT id, full_name FROM {$prefix}users WHERE role IN ('guru','wali_kelas') AND status = 'active' ORDER BY full_name");
        $allStudents = $db->fetchAll("SELECT id, full_name, nis FROM {$prefix}users WHERE role = 'siswa' AND status = 'active' ORDER BY full_name");

        render_with_layout('classes/view', compact('class', 'subjects', 'members', 'teachers', 'allStudents') + ['pageTitle' => $class['name']]);
        break;

    case 'add-subject':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            Auth::requireRole(['admin', 'guru', 'wali_kelas']);
            $db->insert('subjects', [
                'class_id' => $param,
                'teacher_id' => $_POST['teacher_id'],
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description'] ?? ''),
                'color' => $_POST['color'] ?? '#3B49DF',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            Session::flash('success', 'Mata pelajaran berhasil ditambahkan!');
        }
        Router::redirect('classes/view/' . $param);
        break;

    case 'add-member':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            Auth::requireRole(['admin', 'wali_kelas']);
            $studentIds = $_POST['student_ids'] ?? [];
            foreach ($studentIds as $sid) {
                $existing = $db->fetch("SELECT id FROM {$prefix}class_members WHERE class_id = ? AND user_id = ?", [$param, $sid]);
                if (!$existing) {
                    $db->insert('class_members', [
                        'class_id' => $param,
                        'user_id' => $sid,
                        'role' => 'student',
                        'joined_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            Session::flash('success', 'Siswa berhasil ditambahkan ke kelas!');
        }
        Router::redirect('classes/view/' . $param);
        break;

    case 'remove-member':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            Auth::requireRole(['admin', 'wali_kelas']);
            $memberId = $_POST['user_id'] ?? 0;
            $db->delete('class_members', 'class_id = ? AND user_id = ?', [$param, $memberId]);
            Session::flash('success', 'Anggota dihapus dari kelas.');
        }
        Router::redirect('classes/view/' . $param);
        break;

    default:
        Router::redirect('classes');
        break;
}
