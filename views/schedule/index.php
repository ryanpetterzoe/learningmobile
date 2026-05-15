<div class="page-header">
    <div><h1><i class="fas fa-clock"></i> Jadwal Pelajaran</h1><p>Jadwal mingguan Anda</p></div>
</div>

<?php if (empty($byDay)): ?>
    <div class="card"><div class="empty-state"><i class="fas fa-calendar"></i><h3>Belum Ada Jadwal</h3><p>Jadwal belum diatur oleh admin/guru.</p></div></div>
<?php else: ?>
    <div class="schedule-week">
        <?php for ($d = 1; $d <= 6; $d++): ?>
            <div class="day-column">
                <div class="day-header <?= date('N') == $d ? 'today' : '' ?>"><?= day_name($d) ?></div>
                <div class="day-slots">
                    <?php if (isset($byDay[$d])): ?>
                        <?php foreach ($byDay[$d] as $s): ?>
                            <div class="slot-item" style="border-left: 4px solid <?= e($s['color'] ?? '#3B49DF') ?>;">
                                <span class="slot-time"><?= date('H:i', strtotime($s['start_time'])) ?> - <?= date('H:i', strtotime($s['end_time'])) ?></span>
                                <h4><?= e($s['subject_name']) ?></h4>
                                <span class="slot-meta"><?= e($s['class_name'] ?? '') ?> <?= $s['room'] ? '• ' . e($s['room']) : '' ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="slot-empty">-</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<style>
.schedule-week { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; }
.day-column { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; }
.day-header { padding: 12px; text-align: center; font-size: 13px; font-weight: 700; background: var(--bg-hover); color: var(--text-secondary); }
.day-header.today { background: var(--primary); color: #fff; }
.day-slots { padding: 10px; display: flex; flex-direction: column; gap: 8px; min-height: 200px; }
.slot-item { padding: 10px; background: var(--bg-hover); border-radius: var(--radius-sm); }
.slot-time { font-size: 11px; color: var(--primary); font-weight: 600; }
.slot-item h4 { font-size: 12px; color: var(--text-primary); margin: 3px 0; }
.slot-meta { font-size: 10px; color: var(--text-muted); }
.slot-empty { text-align: center; color: var(--text-muted); padding: 20px; font-size: 12px; }
@media (max-width: 768px) { .schedule-week { grid-template-columns: 1fr 1fr; } }
@media (max-width: 480px) { .schedule-week { grid-template-columns: 1fr; } }
</style>
