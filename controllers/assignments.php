<?php
/**
 * SimpleEdu - Assignments Controller
 * Features: create, edit, delete, close, submit, grade, auto-zero
 */

$db = Database::getInstance();
$prefix = $db->getPrefix();
$action = $GLOBALS['action'] ?? 'index';
$param = $GLOBALS['param'] ?? null;
$userId = Session::userId();
$role = Session::userRole();

// Set timezone from admin settings
$tz = app_setting('timezone', 'Asia/Jakarta');
if ($tz) date_default_timezone_set($tz);

switch ($action) {
    case 'index':
        // Auto-close expired assignments that don't allow late submissions
        $expiredAssignments = $db->fetchAll(
            "SELECT a.id, a.subject_id, a.title FROM {$prefix}assignments a 
             JOIN {$prefix}subjects sub ON a.subject_id = sub.id
             WHERE a.deadline < NOW() AND a.allow_late = 0 AND a.status != 'closed'
             AND (sub.teacher_id = ? OR ? = 'admin')",
            [$userId, $role]
        );
        foreach ($expiredAssignments as $exp) {
            $db->update('assignments', ['status' => 'closed'], 'id = ?', [$exp['id']]);
            // Auto assign 0 to students who didn't submit
            autoAssignZero($db, $prefix, $exp['id']);
        }

        if ($role === 'siswa') {
            $assignments = $db->fetchAll(
                "SELECT a.*, sub.name as subject_name, sub.color,
                 (SELECT id FROM {$prefix}submissions WHERE assignment_id = a.id AND student_id = ? LIMIT 1) as submission_id,
                 (SELECT status FROM {$prefix}submissions WHERE assignment_id = a.id AND student_id = ? ORDER BY submitted_at DESC LIMIT 1) as submission_status
                 FROM {$prefix}assignments a
                 JOIN {$prefix}subjects sub ON a.subject_id = sub.id
                 JOIN {$prefix}class_members cm ON cm.class_id = sub.class_id
                 WHERE cm.user_id = ? AND cm.role = 'student'
                 ORDER BY a.deadline ASC",
                [$userId, $userId, $userId]
            );
        } else {
            $assignments = $db->fetchAll(
                "SELECT DISTINCT a.*, sub.name as subject_name, sub.color, c.name as class_name,
                 (SELECT COUNT(*) FROM {$prefix}submissions WHERE assignment_id = a.id) as submission_count,
                 (SELECT COUNT(*) FROM {$prefix}submissions WHERE assignment_id = a.id AND status = 'submitted') as pending_count
                 FROM {$prefix}assignments a
                 JOIN {$prefix}subjects sub ON a.subject_id = sub.id
                 JOIN {$prefix}classes c ON sub.class_id = c.id
                 WHERE sub.teacher_id = ?
                 ORDER BY a.created_at DESC",
                [$userId]
            );
        }
        render_with_layout('assignments/index', ['assignments' => $assignments, 'pageTitle' => 'Tugas']);
        break;

    case 'create':
        Auth::requireRole(['admin', 'guru', 'wali_kelas']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate deadline must be in the future
            $deadline = $_POST['deadline'] ?? '';
            if (strtotime($deadline) <= time()) {
                Session::flash('error', 'Deadline harus di atas waktu saat ini!');
                Router::redirect('assignments/create');
            }

            $assignId = $db->insert('assignments', [
                'subject_id' => $_POST['subject_id'],
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description'] ?? ''),
                'type' => $_POST['type'] ?? 'both',
                'max_score' => (int)($_POST['max_score'] ?? 100),
                'deadline' => $deadline,
                'allow_late' => isset($_POST['allow_late']) ? 1 : 0,
                'allow_revision' => isset($_POST['allow_revision']) ? 1 : 0,
                'status' => 'open',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Notify students
            $subject = $db->fetch("SELECT s.name, s.class_id FROM {$prefix}subjects s WHERE s.id = ?", [$_POST['subject_id']]);
            if ($subject) {
                $students = $db->fetchAll(
                    "SELECT user_id FROM {$prefix}class_members WHERE class_id = ? AND role = 'student'",
                    [$subject['class_id']]
                );
                foreach ($students as $student) {
                    $db->insert('notifications', [
                        'user_id' => $student['user_id'],
                        'title' => 'Tugas Baru',
                        'message' => 'Tugas baru "' . truncate(trim($_POST['title']), 40) . '" pada mapel ' . $subject['name'],
                        'type' => 'info',
                        'link' => url('assignments/view/' . $assignId),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            Session::flash('success', 'Tugas berhasil dibuat!');
            Router::redirect('assignments/view/' . $assignId);
        }
        
        if ($role === 'admin') {
            $subjects = $db->fetchAll(
                "SELECT s.*, c.name as class_name FROM {$prefix}subjects s JOIN {$prefix}classes c ON s.class_id = c.id ORDER BY c.grade, c.name, s.name"
            );
        } else {
            $subjects = $db->fetchAll(
                "SELECT DISTINCT s.*, c.name as class_name FROM {$prefix}subjects s 
                 JOIN {$prefix}classes c ON s.class_id = c.id 
                 WHERE s.teacher_id = ?
                 ORDER BY c.grade, c.name, s.name",
                [$userId]
            );
        }
        render_with_layout('assignments/create', ['subjects' => $subjects, 'pageTitle' => 'Buat Tugas']);
        break;

    case 'edit':
        Auth::requireRole(['admin', 'guru', 'wali_kelas']);
        if (!$param) Router::redirect('assignments');
        $assignment = $db->fetch("SELECT a.*, sub.teacher_id FROM {$prefix}assignments a JOIN {$prefix}subjects sub ON a.subject_id = sub.id WHERE a.id = ?", [$param]);
        if (!$assignment) Router::redirect('assignments');
        if ($role !== 'admin' && (int)$assignment['teacher_id'] !== (int)$userId) {
            Session::flash('error', 'Anda tidak memiliki akses.');
            Router::redirect('assignments');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deadline = $_POST['deadline'] ?? $assignment['deadline'];
            // Validate deadline if changed
            if ($deadline !== $assignment['deadline'] && strtotime($deadline) <= time()) {
                Session::flash('error', 'Deadline harus di atas waktu saat ini!');
                Router::redirect('assignments/edit/' . $param);
            }

            $db->update('assignments', [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description'] ?? ''),
                'deadline' => $deadline,
                'allow_late' => isset($_POST['allow_late']) ? 1 : 0,
                'allow_revision' => isset($_POST['allow_revision']) ? 1 : 0,
            ], 'id = ?', [$param]);

            Session::flash('success', 'Tugas berhasil diperbarui!');
            Router::redirect('assignments/view/' . $param);
        }

        if ($role === 'admin') {
            $subjects = $db->fetchAll("SELECT s.*, c.name as class_name FROM {$prefix}subjects s JOIN {$prefix}classes c ON s.class_id = c.id ORDER BY c.grade, s.name");
        } else {
            $subjects = $db->fetchAll("SELECT DISTINCT s.*, c.name as class_name FROM {$prefix}subjects s JOIN {$prefix}classes c ON s.class_id = c.id WHERE s.teacher_id = ? ORDER BY c.grade, s.name", [$userId]);
        }
        render_with_layout('assignments/edit', compact('assignment', 'subjects') + ['pageTitle' => 'Edit Tugas']);
        break;

    case 'close':
        // Close assignment: no more submissions, auto assign 0 to non-submitters
        Auth::requireRole(['admin', 'guru', 'wali_kelas']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $assignment = $db->fetch("SELECT a.*, sub.teacher_id FROM {$prefix}assignments a JOIN {$prefix}subjects sub ON a.subject_id = sub.id WHERE a.id = ?", [$param]);
            if (!$assignment) Router::redirect('assignments');
            if ($role !== 'admin' && (int)$assignment['teacher_id'] !== (int)$userId) Router::redirect('assignments');

            $db->update('assignments', ['status' => 'closed'], 'id = ?', [$param]);
            
            // Auto assign 0 to students who didn't submit
            autoAssignZero($db, $prefix, $param);

            Session::flash('success', 'Tugas ditutup. Siswa yang belum mengumpulkan mendapat nilai 0.');
        }
        Router::redirect('assignments/view/' . $param);
        break;

    case 'reopen':
        // Reopen a closed assignment
        Auth::requireRole(['admin', 'guru', 'wali_kelas']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $assignment = $db->fetch("SELECT a.*, sub.teacher_id FROM {$prefix}assignments a JOIN {$prefix}subjects sub ON a.subject_id = sub.id WHERE a.id = ?", [$param]);
            if (!$assignment) Router::redirect('assignments');
            if ($role !== 'admin' && (int)$assignment['teacher_id'] !== (int)$userId) Router::redirect('assignments');

            $db->update('assignments', ['status' => 'open'], 'id = ?', [$param]);
            Session::flash('success', 'Tugas dibuka kembali.');
        }
        Router::redirect('assignments/view/' . $param);
        break;

    case 'delete':
        Auth::requireRole(['admin', 'guru', 'wali_kelas']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $assignment = $db->fetch("SELECT a.*, sub.teacher_id FROM {$prefix}assignments a JOIN {$prefix}subjects sub ON a.subject_id = sub.id WHERE a.id = ?", [$param]);
            if (!$assignment) Router::redirect('assignments');
            if ($role !== 'admin' && (int)$assignment['teacher_id'] !== (int)$userId) Router::redirect('assignments');

            // Delete related submissions and grades
            $db->delete('submissions', 'assignment_id = ?', [$param]);
            $db->delete('assignments', 'id = ?', [$param]);
            Session::flash('success', 'Tugas berhasil dihapus.');
        }
        Router::redirect('assignments');
        break;

    case 'view':
        if (!$param) Router::redirect('assignments');
        $assignment = $db->fetch("SELECT a.*, sub.name as subject_name, sub.color, c.name as class_name, sub.teacher_id, sub.class_id FROM {$prefix}assignments a JOIN {$prefix}subjects sub ON a.subject_id = sub.id JOIN {$prefix}classes c ON sub.class_id = c.id WHERE a.id = ?", [$param]);
        if (!$assignment) Router::redirect('assignments');

        // Check if assignment is closed (expired + no late allowed)
        $isClosed = ($assignment['status'] ?? '') === 'closed';
        $isExpired = strtotime($assignment['deadline']) < time();
        if (!$isClosed && $isExpired && !$assignment['allow_late']) {
            $db->update('assignments', ['status' => 'closed'], 'id = ?', [$param]);
            autoAssignZero($db, $prefix, $param);
            $isClosed = true;
            $assignment['status'] = 'closed';
        }

        if ($role === 'siswa') {
            $mySubmission = $db->fetch("SELECT * FROM {$prefix}submissions WHERE assignment_id = ? AND student_id = ? ORDER BY submitted_at DESC LIMIT 1", [$param, $userId]);
            render_with_layout('assignments/view-student', compact('assignment', 'mySubmission', 'isClosed') + ['pageTitle' => $assignment['title']]);
        } else {
            // Get ALL students in the class (for status list)
            $allStudents = $db->fetchAll(
                "SELECT u.id, u.full_name, u.nis, u.avatar FROM {$prefix}class_members cm 
                 JOIN {$prefix}users u ON cm.user_id = u.id 
                 WHERE cm.class_id = ? AND cm.role = 'student' 
                 ORDER BY u.full_name",
                [$assignment['class_id']]
            );

            // Get submissions
            $submissions = $db->fetchAll(
                "SELECT s.*, u.full_name, u.avatar, u.nis FROM {$prefix}submissions s JOIN {$prefix}users u ON s.student_id = u.id WHERE s.assignment_id = ? ORDER BY s.submitted_at DESC",
                [$param]
            );
            $submittedStudentIds = array_column($submissions, 'student_id');

            render_with_layout('assignments/view-teacher', compact('assignment', 'submissions', 'allStudents', 'submittedStudentIds', 'isClosed') + ['pageTitle' => $assignment['title']]);
        }
        break;

    case 'submit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $assignment = $db->fetch("SELECT * FROM {$prefix}assignments WHERE id = ?", [$param]);
            if (!$assignment) Router::redirect('assignments');

            // Check if closed
            if (($assignment['status'] ?? '') === 'closed') {
                Session::flash('error', 'Tugas sudah ditutup, tidak dapat mengumpulkan.');
                Router::redirect('assignments/view/' . $param);
            }

            // Check if expired and late not allowed
            if (strtotime($assignment['deadline']) < time() && !$assignment['allow_late']) {
                Session::flash('error', 'Deadline sudah lewat dan tugas tidak menerima pengumpulan terlambat.');
                Router::redirect('assignments/view/' . $param);
            }

            $data = [
                'assignment_id' => $param,
                'student_id' => $userId,
                'content' => trim($_POST['content'] ?? ''),
                'status' => 'submitted',
                'submitted_at' => date('Y-m-d H:i:s')
            ];

            // Check if late
            if (strtotime($assignment['deadline']) < time()) {
                $data['status'] = 'late';
            }

            // Handle multiple file uploads
            $filePaths = [];
            $fileNames = [];
            if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
                $allowedExts = ['pdf','doc','docx','jpg','jpeg','png','ppt','pptx','xls','xlsx','zip','rar'];
                for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
                    if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmpFile = [
                            'name' => $_FILES['files']['name'][$i],
                            'type' => $_FILES['files']['type'][$i],
                            'tmp_name' => $_FILES['files']['tmp_name'][$i],
                            'error' => $_FILES['files']['error'][$i],
                            'size' => $_FILES['files']['size'][$i]
                        ];
                        $path = upload_file($tmpFile, 'assignments', $allowedExts);
                        if ($path) {
                            $filePaths[] = $path;
                            $fileNames[] = $_FILES['files']['name'][$i];
                        }
                    }
                }
            }
            if (empty($filePaths) && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $filePath = upload_file($_FILES['file'], 'assignments', ['pdf','doc','docx','jpg','jpeg','png','ppt','pptx','xls','xlsx','zip']);
                if ($filePath) {
                    $filePaths[] = $filePath;
                    $fileNames[] = $_FILES['file']['name'];
                }
            }

            if (!empty($filePaths)) {
                $data['file_path'] = implode('|', $filePaths);
                $data['file_name'] = implode('|', $fileNames);
            }

            $existing = $db->fetch("SELECT id, revision_count FROM {$prefix}submissions WHERE assignment_id = ? AND student_id = ?", [$param, $userId]);
            if ($existing) {
                $data['revision_count'] = $existing['revision_count'] + 1;
                $db->update('submissions', $data, 'id = ?', [$existing['id']]);
            } else {
                $db->insert('submissions', $data);
                Gamification::awardXP($userId, 10, 'Submit tugas: ' . $assignment['title']);
            }

            // Notify teacher
            $subject = $db->fetch("SELECT s.teacher_id, s.name FROM {$prefix}subjects s WHERE s.id = ?", [$assignment['subject_id']]);
            if ($subject && (int)$subject['teacher_id'] !== (int)$userId) {
                $studentName = $db->fetch("SELECT full_name FROM {$prefix}users WHERE id = ?", [$userId])['full_name'] ?? 'Siswa';
                $db->insert('notifications', [
                    'user_id' => $subject['teacher_id'],
                    'title' => 'Tugas Dikumpulkan',
                    'message' => $studentName . ' mengumpulkan tugas "' . truncate($assignment['title'], 35) . '"',
                    'type' => 'info',
                    'link' => url('assignments/view/' . $param),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            Session::flash('success', 'Tugas berhasil dikumpulkan!');
        }
        Router::redirect('assignments/view/' . $param);
        break;

    case 'delete-file':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $submission = $db->fetch("SELECT s.*, a.deadline, a.status as assign_status FROM {$prefix}submissions s JOIN {$prefix}assignments a ON s.assignment_id = a.id WHERE s.id = ? AND s.student_id = ?", [$param, $userId]);
            if ($submission && ($submission['assign_status'] ?? '') !== 'closed' && strtotime($submission['deadline']) > time()) {
                $fileIndex = (int)($_POST['file_index'] ?? -1);
                $paths = $submission['file_path'] ? explode('|', $submission['file_path']) : [];
                $names = $submission['file_name'] ? explode('|', $submission['file_name']) : [];
                
                if ($fileIndex >= 0 && $fileIndex < count($paths)) {
                    array_splice($paths, $fileIndex, 1);
                    array_splice($names, $fileIndex, 1);
                    $db->update('submissions', [
                        'file_path' => !empty($paths) ? implode('|', $paths) : null,
                        'file_name' => !empty($names) ? implode('|', $names) : null
                    ], 'id = ?', [$param]);
                    Session::flash('success', 'File berhasil dihapus.');
                }
                Router::redirect('assignments/view/' . $submission['assignment_id']);
            }
        }
        Router::redirect('assignments');
        break;

    case 'grade':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            Auth::requireRole(['admin', 'guru', 'wali_kelas']);
            $submissionId = $_POST['submission_id'];
            $score = (float)$_POST['score'];
            $feedback = trim($_POST['feedback'] ?? '');

            $db->update('submissions', [
                'score' => $score,
                'feedback' => $feedback,
                'graded_by' => $userId,
                'graded_at' => date('Y-m-d H:i:s'),
                'status' => 'graded'
            ], 'id = ?', [$submissionId]);

            $sub = $db->fetch("SELECT s.*, a.subject_id, a.title as assign_title FROM {$prefix}submissions s JOIN {$prefix}assignments a ON s.assignment_id = a.id WHERE s.id = ?", [$submissionId]);
            if ($sub) {
                // Upsert grade
                $existingGrade = $db->fetch("SELECT id FROM {$prefix}grades WHERE student_id = ? AND subject_id = ? AND type = 'tugas' AND title = ?", [$sub['student_id'], $sub['subject_id'], $sub['assign_title']]);
                if ($existingGrade) {
                    $db->update('grades', ['score' => $score], 'id = ?', [$existingGrade['id']]);
                } else {
                    $db->insert('grades', [
                        'student_id' => $sub['student_id'],
                        'subject_id' => $sub['subject_id'],
                        'type' => 'tugas',
                        'title' => $sub['assign_title'],
                        'score' => $score,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }

                if ($score >= 80) {
                    Gamification::awardXP($sub['student_id'], 15, 'Nilai tugas bagus: ' . $score);
                }
            }

            Session::flash('success', 'Nilai berhasil disimpan!');
        }
        Router::redirect('assignments/view/' . $param);
        break;

    default:
        Router::redirect('assignments');
        break;
}

/**
 * Auto assign score 0 to students who didn't submit
 */
function autoAssignZero($db, $prefix, $assignmentId) {
    $assignment = $db->fetch("SELECT a.*, sub.class_id, sub.name as subject_name FROM {$prefix}assignments a JOIN {$prefix}subjects sub ON a.subject_id = sub.id WHERE a.id = ?", [$assignmentId]);
    if (!$assignment) return;

    // Get all students in the class
    $students = $db->fetchAll(
        "SELECT cm.user_id FROM {$prefix}class_members cm WHERE cm.class_id = ? AND cm.role = 'student'",
        [$assignment['class_id']]
    );

    foreach ($students as $student) {
        $sid = $student['user_id'];
        // Check if already submitted
        $submitted = $db->fetch("SELECT id FROM {$prefix}submissions WHERE assignment_id = ? AND student_id = ?", [$assignmentId, $sid]);
        if (!$submitted) {
            // Insert submission with score 0
            $db->insert('submissions', [
                'assignment_id' => $assignmentId,
                'student_id' => $sid,
                'content' => null,
                'score' => 0,
                'feedback' => 'Tidak mengumpulkan tugas',
                'status' => 'graded',
                'graded_at' => date('Y-m-d H:i:s'),
                'submitted_at' => date('Y-m-d H:i:s')
            ]);
            // Insert grade
            $existingGrade = $db->fetch("SELECT id FROM {$prefix}grades WHERE student_id = ? AND subject_id = ? AND type = 'tugas' AND title = ?", [$sid, $assignment['subject_id'], $assignment['title']]);
            if (!$existingGrade) {
                $db->insert('grades', [
                    'student_id' => $sid,
                    'subject_id' => $assignment['subject_id'],
                    'type' => 'tugas',
                    'title' => $assignment['title'],
                    'score' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }
}
