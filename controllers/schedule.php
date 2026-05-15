<?php
/**
 * SimpleEdu - Schedule Controller
 */
$db = Database::getInstance();
$prefix = $db->getPrefix();
$userId = Session::userId();
$role = Session::userRole();

$schedules = [];
if ($role === 'siswa') {
    $schedules = $db->fetchAll(
        "SELECT sc.*, sub.name as subject_name, sub.color, u.full_name as teacher_name, c.name as class_name
         FROM {$prefix}schedules sc
         JOIN {$prefix}subjects sub ON sc.subject_id = sub.id
         JOIN {$prefix}users u ON sub.teacher_id = u.id
         JOIN {$prefix}classes c ON sc.class_id = c.id
         JOIN {$prefix}class_members cm ON cm.class_id = c.id
         WHERE cm.user_id = ? AND cm.role = 'student'
         ORDER BY sc.day_of_week, sc.start_time",
        [$userId]
    );
} elseif ($role === 'guru' || $role === 'wali_kelas') {
    $schedules = $db->fetchAll(
        "SELECT sc.*, sub.name as subject_name, sub.color, c.name as class_name
         FROM {$prefix}schedules sc
         JOIN {$prefix}subjects sub ON sc.subject_id = sub.id
         JOIN {$prefix}classes c ON sc.class_id = c.id
         WHERE sub.teacher_id = ?
         ORDER BY sc.day_of_week, sc.start_time",
        [$userId]
    );
}

// Group by day
$byDay = [];
foreach ($schedules as $s) {
    $byDay[$s['day_of_week']][] = $s;
}

render_with_layout('schedule/index', compact('byDay') + ['pageTitle' => 'Jadwal Pelajaran']);
