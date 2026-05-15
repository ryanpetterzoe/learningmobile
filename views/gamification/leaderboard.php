<div class="page-header">
    <div><h1><i class="fas fa-trophy"></i> Ranking Siswa</h1><p>Peringkat berdasarkan XP Points</p></div>
</div>

<div class="card">
    <!-- Top 3 -->
    <?php if (count($ranking) >= 3): ?>
        <div class="top-3">
            <div class="top-item second">
                <div class="top-rank">2</div>
                <?php if ($ranking[1]['avatar']): ?>
                    <img src="<?= upload_url($ranking[1]['avatar']) ?>" class="top-avatar">
                <?php else: ?>
                    <div class="top-avatar placeholder"><?= strtoupper(substr($ranking[1]['full_name'], 0, 1)) ?></div>
                <?php endif; ?>
                <h4><?= e($ranking[1]['full_name']) ?></h4>
                <span class="top-xp"><?= number_format($ranking[1]['xp_points']) ?> XP</span>
            </div>
            <div class="top-item first">
                <div class="top-crown">👑</div>
                <div class="top-rank">1</div>
                <?php if ($ranking[0]['avatar']): ?>
                    <img src="<?= upload_url($ranking[0]['avatar']) ?>" class="top-avatar big">
                <?php else: ?>
                    <div class="top-avatar big placeholder"><?= strtoupper(substr($ranking[0]['full_name'], 0, 1)) ?></div>
                <?php endif; ?>
                <h4><?= e($ranking[0]['full_name']) ?></h4>
                <span class="top-xp"><?= number_format($ranking[0]['xp_points']) ?> XP</span>
            </div>
            <div class="top-item third">
                <div class="top-rank">3</div>
                <?php if ($ranking[2]['avatar']): ?>
                    <img src="<?= upload_url($ranking[2]['avatar']) ?>" class="top-avatar">
                <?php else: ?>
                    <div class="top-avatar placeholder"><?= strtoupper(substr($ranking[2]['full_name'], 0, 1)) ?></div>
                <?php endif; ?>
                <h4><?= e($ranking[2]['full_name']) ?></h4>
                <span class="top-xp"><?= number_format($ranking[2]['xp_points']) ?> XP</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Full List -->
    <div class="table-container" style="margin-top: 20px;">
        <table>
            <thead><tr><th>#</th><th>Siswa</th><th>Level</th><th>XP Points</th></tr></thead>
            <tbody>
                <?php foreach ($ranking as $idx => $r): ?>
                    <tr class="<?= $r['id'] == Session::userId() ? 'highlight-row' : '' ?>">
                        <td><span class="rank-num"><?= $idx + 1 ?></span></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <?php if ($r['avatar']): ?>
                                    <img src="<?= upload_url($r['avatar']) ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;"><?= strtoupper(substr($r['full_name'], 0, 1)) ?></div>
                                <?php endif; ?>
                                <span style="font-weight:600;font-size:13px;"><?= e($r['full_name']) ?></span>
                            </div>
                        </td>
                        <td><span class="badge badge-primary">Lv.<?= $r['level'] ?></span></td>
                        <td><span style="font-weight:700;color:var(--primary);"><?= number_format($r['xp_points']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.top-3 { display: flex; align-items: flex-end; justify-content: center; gap: 20px; padding: 30px 20px; }
.top-item { text-align: center; }
.top-item.first { order: 2; }
.top-item.second { order: 1; }
.top-item.third { order: 3; }
.top-crown { font-size: 24px; margin-bottom: 5px; }
.top-rank { width: 24px; height: 24px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; margin: 0 auto 8px; }
.top-avatar { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary); margin: 0 auto; display: block; }
.top-avatar.big { width: 72px; height: 72px; }
.top-avatar.placeholder { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; }
.top-avatar.big.placeholder { font-size: 24px; }
.top-item h4 { font-size: 13px; margin-top: 8px; color: var(--text-primary); }
.top-xp { font-size: 12px; color: var(--primary); font-weight: 600; }
.rank-num { width: 24px; height: 24px; background: var(--bg-hover); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; }
.highlight-row td { background: var(--primary-bg) !important; }
</style>
