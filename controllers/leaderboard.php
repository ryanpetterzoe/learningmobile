<?php
/**
 * SimpleEdu - Leaderboard Controller
 */
$ranking = Gamification::getRanking(50);
render_with_layout('gamification/leaderboard', ['ranking' => $ranking, 'pageTitle' => 'Ranking']);
