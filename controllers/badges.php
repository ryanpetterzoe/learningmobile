<?php
/**
 * SimpleEdu - Badges Controller
 */
$db = Database::getInstance();
$prefix = $db->getPrefix();
$userId = Session::userId();

$allBadges = $db->fetchAll("SELECT * FROM {$prefix}badges ORDER BY id");
$userBadges = Gamification::getUserBadges($userId);
$earnedIds = array_column($userBadges, 'id');
$xpHistory = Gamification::getXPHistory($userId, 30);

render_with_layout('gamification/badges', compact('allBadges', 'userBadges', 'earnedIds', 'xpHistory') + ['pageTitle' => 'Badge & XP']);
