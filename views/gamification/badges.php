<div class="page-header">
    <div><h1><i class="fas fa-medal"></i> Badge & XP</h1><p>Kumpulkan badge dan dapatkan XP dari aktivitas belajar</p></div>
</div>

<div class="grid-2">
    <!-- Badges -->
    <div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Semua Badge</h3></div>
            <div class="badges-grid">
                <?php foreach ($allBadges as $badge): 
                    $earned = in_array($badge['id'], $earnedIds);
                ?>
                    <div class="badge-card <?= $earned ? 'earned' : 'locked' ?>">
                        <span class="badge-icon"><?= $badge['icon'] ?></span>
                        <h4><?= e($badge['name']) ?></h4>
                        <p><?= e($badge['description']) ?></p>
                        <span class="badge-xp">+<?= $badge['xp_reward'] ?> XP</span>
                        <?php if ($earned): ?><span class="earned-check"><i class="fas fa-check-circle"></i></span><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- XP History -->
    <div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Riwayat XP</h3></div>
            <?php if (empty($xpHistory)): ?>
                <div class="empty-state" style="padding:20px;"><p style="color:var(--text-muted);">Belum ada aktivitas XP</p></div>
            <?php else: ?>
                <div class="xp-timeline">
                    <?php foreach ($xpHistory as $xp): ?>
                        <div class="xp-item">
                            <span class="xp-points-badge">+<?= $xp['points'] ?></span>
                            <div class="xp-detail">
                                <p><?= e($xp['reason']) ?></p>
                                <small><?= time_ago($xp['created_at']) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.badges-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
.badge-card { text-align: center; padding: 16px 10px; border-radius: var(--radius-md); border: 1px solid var(--border); position: relative; transition: var(--transition); }
.badge-card.earned { background: var(--primary-bg); border-color: var(--primary); }
.badge-card.locked { opacity: 0.5; filter: grayscale(1); }
.badge-card:hover { transform: translateY(-2px); }
.badge-icon { font-size: 32px; display: block; margin-bottom: 8px; }
.badge-card h4 { font-size: 12px; color: var(--text-primary); margin-bottom: 4px; }
.badge-card p { font-size: 10px; color: var(--text-muted); }
.badge-xp { display: inline-block; margin-top: 6px; font-size: 10px; font-weight: 700; color: var(--primary); background: var(--primary-bg); padding: 2px 8px; border-radius: 10px; }
.earned-check { position: absolute; top: 8px; right: 8px; color: var(--success); font-size: 14px; }

.xp-timeline { display: flex; flex-direction: column; gap: 8px; }
.xp-item { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: var(--radius-sm); transition: var(--transition); }
.xp-item:hover { background: var(--bg-hover); }
.xp-points-badge { background: var(--success); color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 700; min-width: 50px; text-align: center; }
.xp-detail p { font-size: 13px; color: var(--text-primary); }
.xp-detail small { font-size: 11px; color: var(--text-muted); }
</style>
