<?php
/**
 * SimpleEdu - Grades Controller
 */
$db = Database::getInstance();
$prefix = $db->getPrefix();
$userId = Session::userId();
$role = Session::userRole();

if ($role === 'siswa') {
    // Get grades from grades table
    $grades = $db->fetchAll(
        "SELECT g.*, sub.name as subject_name, sub.color FROM {$prefix}grades g 
         JOIN {$prefix}subjects sub ON g.subject_id = sub.id 
         WHERE g.student_id = ? ORDER BY g.created_at DESC",
        [$userId]
    );

    // Also check for closed/expired assignments that don't have a grade yet (auto-zero)
    $closedAssignments = $db->fetchAll(
        "SELECT a.id, a.title, a.subject_id, sub.name as subject_name, sub.color, a.deadline
         FROM {$prefix}assignments a
         JOIN {$prefix}subjects sub ON a.subject_id = sub.id
         JOIN {$prefix}class_members cm ON cm.class_id = sub.class_id
         WHERE cm.user_id = ? AND cm.role = 'student'
         AND (a.status = 'closed' OR (a.deadline < NOW() AND a.allow_late = 0))
         AND NOT EXISTS (
             SELECT 1 FROM {$prefix}grades g2 
             WHERE g2.student_id = ? AND g2.subject_id = a.subject_id AND g2.type = 'tugas' AND g2.title = a.title
         )
         AND NOT EXISTS (
             SELECT 1 FROM {$prefix}submissions s2
             WHERE s2.assignment_id = a.id AND s2.student_id = ?
         )",
        [$userId, $userId, $userId]
    );

    // Insert auto-zero grades for closed assignments without submission
    foreach ($closedAssignments as $ca) {
        $db->insert('grades', [
            'student_id' => $userId,
            'subject_id' => $ca['subject_id'],
            'type' => 'tugas',
            'title' => $ca['title'],
            'score' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        // Add to grades array for display
        $grades[] = [
            'subject_name' => $ca['subject_name'],
            'color' => $ca['color'],
            'type' => 'tugas',
            'title' => $ca['title'],
            'score' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    
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
