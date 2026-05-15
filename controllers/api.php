<?php
/**
 * SimpleEdu - Simple API Controller
 */

$db = Database::getInstance();
$prefix = $db->getPrefix();
$action = $GLOBALS['action'] ?? '';
$param = $GLOBALS['param'] ?? null;

switch ($action) {
    case 'class-students':
        if ($param) {
            $students = $db->fetchAll(
                "SELECT u.id, u.full_name, u.nis FROM {$prefix}class_members cm 
                 JOIN {$prefix}users u ON cm.user_id = u.id 
                 WHERE cm.class_id = ? AND cm.role = 'student' ORDER BY u.full_name",
                [$param]
            );
            json_response($students);
        }
        json_response([]);
        break;

    case 'theme':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            // Just store in session for now
            if (isset($input['theme'])) {
                Session::set('user_theme', $input['theme']);
            }
            json_response(['ok' => true]);
        }
        break;

    case 'notifications':
        $notifs = $db->fetchAll(
            "SELECT * FROM {$prefix}notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20",
            [Session::userId()]
        );
        json_response($notifs);
        break;

    case 'mark-read':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db->query("UPDATE {$prefix}notifications SET is_read = 1 WHERE user_id = ?", [Session::userId()]);
        }
        Router::back();
        break;

    case 'mark-read-single':
        // Mark a single notification as read and redirect to its link
        if ($param) {
            $notif = $db->fetch("SELECT * FROM {$prefix}notifications WHERE id = ? AND user_id = ?", [$param, Session::userId()]);
            if ($notif) {
                $db->update('notifications', ['is_read' => 1], 'id = ?', [$param]);
                $redirectTo = !empty($notif['link']) ? $notif['link'] : url('dashboard');
                header('Location: ' . $redirectTo);
                exit;
            }
        }
        Router::redirect('dashboard');
        break;

    default:
        json_response(['error' => 'Not found'], 404);
}
