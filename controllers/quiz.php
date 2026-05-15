<?php
/**
 * SimpleEdu - Quiz/CBT Controller
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
            $quizzes = $db->fetchAll(
                "SELECT q.*, sub.name as subject_name, sub.color, c.name as class_name,
                 (SELECT COUNT(*) FROM {$prefix}quiz_questions WHERE quiz_id = q.id) as question_count,
                 (SELECT id FROM {$prefix}quiz_attempts WHERE quiz_id = q.id AND student_id = ? AND status = 'completed' LIMIT 1) as completed
                 FROM {$prefix}quizzes q
                 JOIN {$prefix}subjects sub ON q.subject_id = sub.id
                 JOIN {$prefix}classes c ON sub.class_id = c.id
                 JOIN {$prefix}class_members cm ON cm.class_id = c.id
                 WHERE cm.user_id = ? AND cm.role = 'student' AND q.status = 'active'
                 ORDER BY q.created_at DESC",
                [$userId, $userId]
            );
        } elseif ($role === 'admin') {
            $quizzes = $db->fetchAll(
                "SELECT q.*, sub.name as subject_name, sub.color, c.name as class_name,
                 (SELECT COUNT(*) FROM {$prefix}quiz_questions WHERE quiz_id = q.id) as question_count,
                 (SELECT COUNT(*) FROM {$prefix}quiz_attempts WHERE quiz_id = q.id AND status = 'completed') as attempt_count
                 FROM {$prefix}quizzes q
                 JOIN {$prefix}subjects sub ON q.subject_id = sub.id
                 JOIN {$prefix}classes c ON sub.class_id = c.id
                 ORDER BY q.created_at DESC"
            );
        } else {
            // Guru/Wali Kelas: show quizzes for their assigned subjects
            $quizzes = $db->fetchAll(
                "SELECT DISTINCT q.*, sub.name as subject_name, sub.color, c.name as class_name,
                 (SELECT COUNT(*) FROM {$prefix}quiz_questions WHERE quiz_id = q.id) as question_count,
                 (SELECT COUNT(*) FROM {$prefix}quiz_attempts WHERE quiz_id = q.id AND status = 'completed') as attempt_count
                 FROM {$prefix}quizzes q
                 JOIN {$prefix}subjects sub ON q.subject_id = sub.id
                 JOIN {$prefix}classes c ON sub.class_id = c.id
                 WHERE sub.teacher_id = ?
                 ORDER BY q.created_at DESC",
                [$userId]
            );
        }
        render_with_layout('quiz/index', compact('quizzes') + ['pageTitle' => 'Quiz & CBT']);
        break;

    case 'create':
        Auth::requireRole(['admin', 'guru', 'wali_kelas']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $quizStatus = $_POST['status'] ?? 'draft';
            $quizId = $db->insert('quizzes', [
                'subject_id' => $_POST['subject_id'],
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description'] ?? ''),
                'duration_minutes' => (int)($_POST['duration'] ?? 60),
                'shuffle_questions' => isset($_POST['shuffle_questions']) ? 1 : 0,
                'shuffle_options' => isset($_POST['shuffle_options']) ? 1 : 0,
                'passing_score' => (int)($_POST['passing_score'] ?? 70),
                'status' => $quizStatus,
                'start_time' => $_POST['start_time'] ?: null,
                'end_time' => $_POST['end_time'] ?: null,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Notify students if quiz is immediately active
            if ($quizStatus === 'active') {
                $subject = $db->fetch("SELECT s.name, s.class_id FROM {$prefix}subjects s WHERE s.id = ?", [$_POST['subject_id']]);
                if ($subject) {
                    $students = $db->fetchAll(
                        "SELECT user_id FROM {$prefix}class_members WHERE class_id = ? AND role = 'student'",
                        [$subject['class_id']]
                    );
                    foreach ($students as $student) {
                        $db->insert('notifications', [
                            'user_id' => $student['user_id'],
                            'title' => 'Quiz Baru',
                            'message' => 'Quiz baru "' . truncate(trim($_POST['title']), 40) . '" pada mapel ' . $subject['name'],
                            'type' => 'info',
                            'link' => url('quiz'),
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }

            Session::flash('success', 'Quiz berhasil dibuat! Tambahkan soal.');
            Router::redirect('quiz/questions/' . $quizId);
        }
        // Get subjects assigned to this teacher
        if ($role === 'admin') {
            $subjects = $db->fetchAll("SELECT s.*, c.name as class_name FROM {$prefix}subjects s JOIN {$prefix}classes c ON s.class_id = c.id ORDER BY c.grade, s.name");
        } else {
            $subjects = $db->fetchAll(
                "SELECT DISTINCT s.*, c.name as class_name FROM {$prefix}subjects s 
                 JOIN {$prefix}classes c ON s.class_id = c.id 
                 WHERE s.teacher_id = ?
                 ORDER BY c.grade, s.name",
                [$userId]
            );
        }
        render_with_layout('quiz/create', compact('subjects') + ['pageTitle' => 'Buat Quiz']);
        break;

    case 'questions':
        Auth::requireRole(['admin', 'guru', 'wali_kelas']);
        if (!$param) Router::redirect('quiz');
        $quiz = $db->fetch("SELECT * FROM {$prefix}quizzes WHERE id = ?", [$param]);
        if (!$quiz) Router::redirect('quiz');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = $_POST['type'] ?? 'multiple_choice';
            $options = null;
            $correct = null;

            if ($type === 'multiple_choice') {
                $opts = $_POST['options'] ?? [];
                $options = json_encode(array_values(array_filter($opts)));
                $correct = $_POST['correct_answer'] ?? '0';
            } elseif ($type === 'true_false') {
                $options = json_encode(['Benar', 'Salah']);
                $correct = $_POST['correct_tf'] ?? '0';
            }

            $data = [
                'quiz_id' => $param,
                'question' => trim($_POST['question']),
                'type' => $type,
                'options' => $options,
                'correct_answer' => $correct,
                'points' => (int)($_POST['points'] ?? 1)
            ];

            // Handle attachment upload (photo/video)
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $path = upload_file($_FILES['attachment'], 'quiz', ['jpg','jpeg','png','gif','webp','mp4','webm']);
                if ($path) $data['attachment'] = $path;
            }

            $db->insert('quiz_questions', $data);
            Session::flash('success', 'Soal ditambahkan!');
            Router::redirect('quiz/questions/' . $param);
        }

        $questions = $db->fetchAll("SELECT * FROM {$prefix}quiz_questions WHERE quiz_id = ? ORDER BY order_num, id", [$param]);
        render_with_layout('quiz/questions', compact('quiz', 'questions') + ['pageTitle' => 'Kelola Soal']);
        break;

    case 'start':
        if (!$param) Router::redirect('quiz');
        $quiz = $db->fetch("SELECT * FROM {$prefix}quizzes WHERE id = ? AND status = 'active'", [$param]);
        if (!$quiz) { Session::flash('error', 'Quiz tidak tersedia.'); Router::redirect('quiz'); }

        // Check if already completed
        $existing = $db->fetch("SELECT * FROM {$prefix}quiz_attempts WHERE quiz_id = ? AND student_id = ? AND status = 'completed'", [$param, $userId]);
        if ($existing && $quiz['max_attempts'] <= 1) {
            Session::flash('error', 'Anda sudah mengerjakan quiz ini.');
            Router::redirect('quiz');
        }

        // Check or create attempt
        $attempt = $db->fetch("SELECT * FROM {$prefix}quiz_attempts WHERE quiz_id = ? AND student_id = ? AND status = 'in_progress'", [$param, $userId]);
        if (!$attempt) {
            $attemptId = $db->insert('quiz_attempts', [
                'quiz_id' => $param,
                'student_id' => $userId,
                'started_at' => date('Y-m-d H:i:s'),
                'status' => 'in_progress'
            ]);
            $attempt = $db->fetch("SELECT * FROM {$prefix}quiz_attempts WHERE id = ?", [$attemptId]);
        }

        // Get questions
        $questions = $db->fetchAll("SELECT * FROM {$prefix}quiz_questions WHERE quiz_id = ?", [$param]);
        if ($quiz['shuffle_questions']) shuffle($questions);

        // Shuffle options if needed
        if ($quiz['shuffle_options']) {
            foreach ($questions as &$q) {
                if ($q['type'] === 'multiple_choice' && $q['options']) {
                    $opts = json_decode($q['options'], true);
                    $correctIdx = (int)$q['correct_answer'];
                    $correctText = $opts[$correctIdx] ?? '';
                    shuffle($opts);
                    $q['options'] = json_encode($opts);
                    $q['correct_answer'] = (string)array_search($correctText, $opts);
                }
            }
        }

        render('quiz/take', compact('quiz', 'attempt', 'questions'));
        break;

    case 'submit-quiz':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $attempt = $db->fetch("SELECT * FROM {$prefix}quiz_attempts WHERE id = ? AND student_id = ?", [$param, $userId]);
            if (!$attempt || $attempt['status'] === 'completed') Router::redirect('quiz');

            $quiz = $db->fetch("SELECT * FROM {$prefix}quizzes WHERE id = ?", [$attempt['quiz_id']]);
            $questions = $db->fetchAll("SELECT * FROM {$prefix}quiz_questions WHERE quiz_id = ?", [$attempt['quiz_id']]);
            
            $answers = $_POST['answers'] ?? [];
            $totalPoints = 0;
            $earnedPoints = 0;

            foreach ($questions as $q) {
                $totalPoints += $q['points'];
                $userAnswer = $answers[$q['id']] ?? '';
                if ($q['type'] !== 'essay' && $userAnswer === $q['correct_answer']) {
                    $earnedPoints += $q['points'];
                }
            }

            $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;

            $db->update('quiz_attempts', [
                'answers' => json_encode($answers),
                'score' => $score,
                'finished_at' => date('Y-m-d H:i:s'),
                'status' => 'completed'
            ], 'id = ?', [$param]);

            // Add grade
            $db->insert('grades', [
                'student_id' => $userId,
                'subject_id' => $quiz['subject_id'],
                'type' => 'quiz',
                'title' => $quiz['title'],
                'score' => $score,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Award XP
            Gamification::awardXP($userId, 15, 'Menyelesaikan quiz: ' . $quiz['title']);
            if ($score >= 90) {
                Gamification::awardXP($userId, 20, 'Nilai quiz excellent: ' . $score);
            }

            Session::flash('success', "Quiz selesai! Skor Anda: {$score}%");
            Router::redirect('quiz');
        }
        break;

    case 'results':
        Auth::requireRole(['admin', 'guru', 'wali_kelas']);
        if (!$param) Router::redirect('quiz');
        $quiz = $db->fetch(
            "SELECT q.*, sub.name as subject_name, c.name as class_name 
             FROM {$prefix}quizzes q 
             JOIN {$prefix}subjects sub ON q.subject_id = sub.id 
             JOIN {$prefix}classes c ON sub.class_id = c.id 
             WHERE q.id = ?", [$param]
        );
        if (!$quiz) Router::redirect('quiz');

        $attempts = $db->fetchAll(
            "SELECT qa.*, u.full_name, u.nis, u.avatar
             FROM {$prefix}quiz_attempts qa
             JOIN {$prefix}users u ON qa.student_id = u.id
             WHERE qa.quiz_id = ? AND qa.status = 'completed'
             ORDER BY qa.score DESC, qa.finished_at ASC",
            [$param]
        );

        // Stats
        $stats = [
            'total' => count($attempts),
            'avg_score' => $attempts ? round(array_sum(array_column($attempts, 'score')) / count($attempts), 1) : 0,
            'max_score' => $attempts ? max(array_column($attempts, 'score')) : 0,
            'min_score' => $attempts ? min(array_column($attempts, 'score')) : 0,
            'passed' => count(array_filter($attempts, fn($a) => $a['score'] >= $quiz['passing_score'])),
        ];

        render_with_layout('quiz/results', compact('quiz', 'attempts', 'stats') + ['pageTitle' => 'Hasil Quiz: ' . $quiz['title']]);
        break;

    case 'review-attempt':
        Auth::requireRole(['admin', 'guru', 'wali_kelas']);
        if (!$param) Router::redirect('quiz');
        
        $attempt = $db->fetch(
            "SELECT qa.*, u.full_name, u.nis, u.avatar
             FROM {$prefix}quiz_attempts qa
             JOIN {$prefix}users u ON qa.student_id = u.id
             WHERE qa.id = ?", [$param]
        );
        if (!$attempt) Router::redirect('quiz');

        $quiz = $db->fetch("SELECT q.*, sub.name as subject_name FROM {$prefix}quizzes q JOIN {$prefix}subjects sub ON q.subject_id = sub.id WHERE q.id = ?", [$attempt['quiz_id']]);
        $questions = $db->fetchAll("SELECT * FROM {$prefix}quiz_questions WHERE quiz_id = ? ORDER BY order_num, id", [$attempt['quiz_id']]);
        $studentAnswers = json_decode($attempt['answers'] ?? '{}', true) ?: [];

        // Handle essay grading POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $essayScores = $_POST['essay_score'] ?? [];
            $totalPoints = 0;
            $earnedPoints = 0;

            foreach ($questions as $q) {
                $totalPoints += $q['points'];
                $userAnswer = $studentAnswers[$q['id']] ?? '';
                if ($q['type'] === 'essay') {
                    // Use teacher's score for essay
                    $givenScore = isset($essayScores[$q['id']]) ? (float)$essayScores[$q['id']] : 0;
                    $earnedPoints += min($givenScore, $q['points']);
                } else {
                    if ($userAnswer === $q['correct_answer']) {
                        $earnedPoints += $q['points'];
                    }
                }
            }

            $newScore = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
            $db->update('quiz_attempts', ['score' => $newScore], 'id = ?', [$param]);

            // Update grade record too
            $db->query(
                "UPDATE {$prefix}grades SET score = ? WHERE student_id = ? AND subject_id = ? AND type = 'quiz' AND title = ?",
                [$newScore, $attempt['student_id'], $quiz['subject_id'], $quiz['title']]
            );

            Session::flash('success', "Nilai essay diperbarui! Skor baru: {$newScore}%");
            Router::redirect('quiz/review-attempt/' . $param);
        }

        render_with_layout('quiz/review-attempt', [
            'attempt' => $attempt,
            'quiz' => $quiz,
            'questions' => $questions,
            'studentAnswers' => $studentAnswers,
            'pageTitle' => 'Review Jawaban: ' . $attempt['full_name']
        ]);
        break;

    case 'delete-question':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param) {
            $q = $db->fetch("SELECT quiz_id FROM {$prefix}quiz_questions WHERE id = ?", [$param]);
            $db->delete('quiz_questions', 'id = ?', [$param]);
            Session::flash('success', 'Soal dihapus.');
            Router::redirect('quiz/questions/' . $q['quiz_id']);
        }
        break;

    default:
        Router::redirect('quiz');
        break;
}
