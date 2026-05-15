<?php
/**
 * SimpleEdu - Attendance Controller
 */
$db = Database::getInstance();
$prefix = $db->getPrefix();
$userId = Session::userId();
$role = Session::userRole();
$action = $GLOBALS['action'] ?? 'index';
$param = $GLOBALS['param'] ?? null;

switch ($action) {
    case 'index':
        if ($role === 'siswa') {
            $attendance = $db->fetchAll(
                "SELECT a.*, sub.name as subject_name FROM {$prefix}attendance a 
                 LEFT JOIN {$prefix}subjects sub ON a.subject_id = sub.id 
                 WHERE a.student_id = ? ORDER BY a.date DESC LIMIT 50",
                [$userId]
            );
            $stats = [
                'hadir' => $db->count('attendance', 'student_id = ? AND status = "hadir"', [$userId]),
                'izin' => $db->count('attendance', 'student_id = ? AND status = "izin"', [$userId]),
                'sakit' => $db->count('attendance', 'student_id = ? AND status = "sakit"', [$userId]),
                'alpha' => $db->count('attendance', 'student_id = ? AND status = "alpha"', [$userId]),
            ];
            render_with_layout('attendance/student', compact('attendance', 'stats') + ['pageTitle' => 'Kehadiran']);
        } else {
            // Teacher: select class to record attendance
            $classes = $db->fetchAll(
                "SELECT DISTINCT c.* FROM {$prefix}classes c JOIN {$prefix}subjects s ON s.class_id = c.id WHERE s.teacher_id = ?",
                [$userId]
            );

            // Pre-load students for each class (avoid AJAX issues)
            $classStudents = [];
            foreach ($classes as $c) {
                $students = $db->fetchAll(
                    "SELECT u.id, u.full_name, u.nis FROM {$prefix}class_members cm 
                     JOIN {$prefix}users u ON cm.user_id = u.id 
                     WHERE cm.class_id = ? AND cm.role = 'student' ORDER BY u.full_name",
                    [$c['id']]
                );
                $classStudents[$c['id']] = $students;
            }

            render_with_layout('attendance/teacher', compact('classes', 'classStudents') + ['pageTitle' => 'Kehadiran']);
        }
        break;

    case 'record':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            Auth::requireRole(['admin', 'guru', 'wali_kelas']);
            $date = $_POST['date'] ?? date('Y-m-d');
            $statuses = $_POST['status'] ?? [];
            
            foreach ($statuses as $studentId => $status) {
                $existing = $db->fetch("SELECT id FROM {$prefix}attendance WHERE class_id = ? AND student_id = ? AND date = ?", [$param, $studentId, $date]);
                if ($existing) {
                    $db->update('attendance', ['status' => $status, 'recorded_by' => $userId], 'id = ?', [$existing['id']]);
                } else {
                    $db->insert('attendance', [
                        'class_id' => $param,
                        'student_id' => $studentId,
                        'date' => $date,
                        'status' => $status,
                        'recorded_by' => $userId,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            Session::flash('success', 'Absensi berhasil disimpan!');
        }
        Router::redirect('attendance');
        break;

    default:
        Router::redirect('attendance');
}
