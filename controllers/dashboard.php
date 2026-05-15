<?php
/**
 * SimpleEdu - Dashboard Controller
 */

$db = Database::getInstance();
$prefix = $db->getPrefix();
$userId = Session::userId();
$role = Session::userRole();

// Common data
$data = [
    'pageTitle' => 'Dashboard',
    'user' => Session::user(),
];

// Get today's schedule
$today = date('N'); // 1=Monday, 7=Sunday
if ($role === 'siswa') {
    // Get student's class
    $myClass = $db->fetch(
        "SELECT c.* FROM {$prefix}class_members cm 
         JOIN {$prefix}classes c ON cm.class_id = c.id 
         WHERE cm.user_id = ? AND cm.role = 'student' LIMIT 1",
        [$userId]
    );

    if ($myClass) {
        $data['schedule'] = $db->fetchAll(
            "SELECT s.*, sub.name as subject_name, u.full_name as teacher_name 
             FROM {$prefix}schedules s 
             JOIN {$prefix}subjects sub ON s.subject_id = sub.id 
             JOIN {$prefix}users u ON sub.teacher_id = u.id 
             WHERE s.class_id = ? AND s.day_of_week = ? 
             ORDER BY s.start_time",
            [$myClass['id'], $today]
        );

        // Pending assignments
        $data['pending_assignments'] = $db->fetchAll(
            "SELECT a.*, sub.name as subject_name FROM {$prefix}assignments a 
             JOIN {$prefix}subjects sub ON a.subject_id = sub.id 
             WHERE sub.class_id = ? AND a.deadline > NOW() 
             AND a.id NOT IN (SELECT assignment_id FROM {$prefix}submissions WHERE student_id = ?)
             ORDER BY a.deadline ASC LIMIT 5",
            [$myClass['id'], $userId]
        );

        // Attendance percentage
        $totalDays = $db->count('attendance', 'student_id = ? AND class_id = ?', [$userId, $myClass['id']]);
        $presentDays = $db->count('attendance', 'student_id = ? AND class_id = ? AND status = "hadir"', [$userId, $myClass['id']]);
        $data['attendance_pct'] = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 100;

        // Latest grades
        $data['latest_grades'] = $db->fetchAll(
            "SELECT g.*, sub.name as subject_name FROM {$prefix}grades g 
             JOIN {$prefix}subjects sub ON g.subject_id = sub.id 
             WHERE g.student_id = ? ORDER BY g.created_at DESC LIMIT 5",
            [$userId]
        );

        $data['my_class'] = $myClass;
    }

    // Stats
    $data['stats'] = [
        'classes' => $db->count('class_members', 'user_id = ? AND role = "student"', [$userId]),
        'assignments_done' => $db->count('submissions', 'student_id = ?', [$userId]),
        'quiz_done' => $db->count('quiz_attempts', 'student_id = ? AND status = "completed"', [$userId]),
        'xp' => Session::user()['xp_points'] ?? 0,
    ];

} elseif ($role === 'guru' || $role === 'wali_kelas') {
    // Teacher stats
    $data['stats'] = [
        'subjects' => $db->count('subjects', 'teacher_id = ?', [$userId]),
        'students' => $db->fetch("SELECT COUNT(DISTINCT cm.user_id) as cnt FROM {$prefix}class_members cm JOIN {$prefix}subjects s ON cm.class_id = s.class_id WHERE s.teacher_id = ? AND cm.role = 'student'", [$userId])['cnt'] ?? 0,
        'pending_grading' => $db->fetch("SELECT COUNT(*) as cnt FROM {$prefix}submissions sub JOIN {$prefix}assignments a ON sub.assignment_id = a.id JOIN {$prefix}subjects s ON a.subject_id = s.id WHERE s.teacher_id = ? AND sub.status = 'submitted'", [$userId])['cnt'] ?? 0,
        'quizzes' => $db->fetch("SELECT COUNT(*) as cnt FROM {$prefix}quizzes q JOIN {$prefix}subjects s ON q.subject_id = s.id WHERE s.teacher_id = ?", [$userId])['cnt'] ?? 0,
    ];

    // Schedule
    $data['schedule'] = $db->fetchAll(
        "SELECT sc.*, sub.name as subject_name, c.name as class_name 
         FROM {$prefix}schedules sc 
         JOIN {$prefix}subjects sub ON sc.subject_id = sub.id 
         JOIN {$prefix}classes c ON sc.class_id = c.id 
         WHERE sub.teacher_id = ? AND sc.day_of_week = ? 
         ORDER BY sc.start_time",
        [$userId, $today]
    );

} elseif ($role === 'admin') {
    // Admin stats
    $data['stats'] = [
        'total_users' => $db->count('users', '1=1'),
        'pending_users' => $db->count('users', 'status = "pending"'),
        'total_classes' => $db->count('classes', '1=1'),
        'total_subjects' => $db->count('subjects', '1=1'),
    ];

    // Recent registrations
    $data['recent_users'] = $db->fetchAll(
        "SELECT * FROM {$prefix}users ORDER BY created_at DESC LIMIT 5"
    );
}

// Announcements for all
$data['announcements'] = $db->fetchAll(
    "SELECT a.*, u.full_name as author_name FROM {$prefix}announcements a 
     JOIN {$prefix}users u ON a.author_id = u.id 
     WHERE a.target = 'all' OR a.target_role = ? 
     ORDER BY a.is_pinned DESC, a.created_at DESC LIMIT 5",
    [$role]
);

render_with_layout('dashboard/index', $data);
