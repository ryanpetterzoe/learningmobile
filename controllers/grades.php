<?php
/**
 * SimpleEdu - Grades Controller
 */
$db = Database::getInstance();
$prefix = $db->getPrefix();
$userId = Session::userId();
$role = Session::userRole();

if ($role === 'siswa') {
    $grades = $db->fetchAll(
        "SELECT g.*, sub.name as subject_name, sub.color FROM {$prefix}grades g 
         JOIN {$prefix}subjects sub ON g.subject_id = sub.id 
         WHERE g.student_id = ? ORDER BY g.created_at DESC",
        [$userId]
    );
    
    // Group by subject
    $bySubject = [];
    foreach ($grades as $g) {
        $bySubject[$g['subject_name']][] = $g;
    }

    render_with_layout('grades/student', compact('grades', 'bySubject') + ['pageTitle' => 'Nilai Saya']);
} else {
    // Teacher view
    $subjects = $db->fetchAll("SELECT s.*, c.name as class_name FROM {$prefix}subjects s JOIN {$prefix}classes c ON s.class_id = c.id WHERE s.teacher_id = ?", [$userId]);
    render_with_layout('grades/teacher', compact('subjects') + ['pageTitle' => 'Nilai Siswa']);
}
