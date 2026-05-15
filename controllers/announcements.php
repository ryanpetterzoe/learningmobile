<?php
/**
 * SimpleEdu - Announcements (Public View)
 */
$db = Database::getInstance();
$prefix = $db->getPrefix();
$role = Session::userRole();

$announcements = $db->fetchAll(
    "SELECT a.*, u.full_name as author_name, u.avatar as author_avatar FROM {$prefix}announcements a 
     JOIN {$prefix}users u ON a.author_id = u.id 
     WHERE a.target = 'all' OR a.target_role = ? 
     ORDER BY a.is_pinned DESC, a.created_at DESC",
    [$role]
);

render_with_layout('announcements/index', compact('announcements') + ['pageTitle' => 'Pengumuman']);
