<?php
/**
 * SimpleEdu - Gamification System
 */
class Gamification {
    
    public static function awardXP($userId, $points, $reason) {
        $db = Database::getInstance();
        
        // Log XP
        $db->insert('xp_log', [
            'user_id' => $userId,
            'points' => $points,
            'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Update user XP
        $db->query(
            "UPDATE {$db->getPrefix()}users SET xp_points = xp_points + ? WHERE id = ?",
            [$points, $userId]
        );

        // Check and update level
        $user = $db->fetch("SELECT xp_points FROM {$db->getPrefix()}users WHERE id = ?", [$userId]);
        $newLevel = calculate_level($user['xp_points']);
        $db->update('users', ['level' => $newLevel], 'id = ?', [$userId]);

        // Check badges
        self::checkBadges($userId);
    }

    public static function checkBadges($userId) {
        $db = Database::getInstance();
        $prefix = $db->getPrefix();

        $badges = $db->fetchAll("SELECT * FROM {$prefix}badges");
        
        foreach ($badges as $badge) {
            // Skip if already earned
            $earned = $db->fetch(
                "SELECT id FROM {$prefix}user_badges WHERE user_id = ? AND badge_id = ?",
                [$userId, $badge['id']]
            );
            if ($earned) continue;

            $qualified = false;
            switch ($badge['criteria_type']) {
                case 'login':
                    $qualified = true; // First login
                    break;
                case 'posts':
                    $count = $db->count('forum_posts', 'author_id = ?', [$userId]);
                    $qualified = $count >= $badge['criteria_value'];
                    break;
                case 'replies':
                    $count = $db->count('forum_replies', 'author_id = ?', [$userId]);
                    $qualified = $count >= $badge['criteria_value'];
                    break;
                case 'portfolio':
                    $count = $db->count('portfolio', 'user_id = ?', [$userId]);
                    $qualified = $count >= $badge['criteria_value'];
                    break;
                case 'on_time':
                    $count = $db->fetch(
                        "SELECT COUNT(*) as cnt FROM {$prefix}submissions s 
                         JOIN {$prefix}assignments a ON s.assignment_id = a.id 
                         WHERE s.student_id = ? AND s.submitted_at <= a.deadline",
                        [$userId]
                    );
                    $qualified = ($count['cnt'] ?? 0) >= $badge['criteria_value'];
                    break;
            }

            if ($qualified) {
                $db->insert('user_badges', [
                    'user_id' => $userId,
                    'badge_id' => $badge['id'],
                    'earned_at' => date('Y-m-d H:i:s')
                ]);

                // Award badge XP
                if ($badge['xp_reward'] > 0) {
                    $db->query(
                        "UPDATE {$prefix}users SET xp_points = xp_points + ? WHERE id = ?",
                        [$badge['xp_reward'], $userId]
                    );
                }

                // Notify user
                $db->insert('notifications', [
                    'user_id' => $userId,
                    'title' => 'Badge Baru! ' . $badge['icon'],
                    'message' => 'Selamat! Kamu mendapatkan badge "' . $badge['name'] . '"',
                    'type' => 'achievement',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    public static function getRanking($limit = 10) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT id, full_name, avatar, xp_points, level, role FROM {$db->getPrefix()}users 
             WHERE role = 'siswa' AND status = 'active' 
             ORDER BY xp_points DESC LIMIT ?",
            [$limit]
        );
    }

    public static function getUserBadges($userId) {
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        return $db->fetchAll(
            "SELECT b.*, ub.earned_at FROM {$prefix}user_badges ub 
             JOIN {$prefix}badges b ON ub.badge_id = b.id 
             WHERE ub.user_id = ? ORDER BY ub.earned_at DESC",
            [$userId]
        );
    }

    public static function getXPHistory($userId, $limit = 20) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM {$db->getPrefix()}xp_log WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
}
