<div class="page-header">
    <div><h1><i class="fas fa-calendar-check"></i> Kehadiran Saya</h1></div>
</div>

<div class="stats-grid" style="margin-bottom: 25px;">
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-check"></i></div><div class="stat-info"><h3><?= $stats['hadir'] ?></h3><p>Hadir</p></div></div>
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-envelope"></i></div><div class="stat-info"><h3><?= $stats['izin'] ?></h3><p>Izin</p></div></div>
    <div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-medkit"></i></div><div class="stat-info"><h3><?= $stats['sakit'] ?></h3><p>Sakit</p></div></div>
    <div class="stat-card"><div class="stat-icon red"><i class="fas fa-times"></i></div><div class="stat-info"><h3><?= $stats['alpha'] ?></h3><p>Alpha</p></div></div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Riwayat Kehadiran</h3></div>
    <div class="table-container">
        <table>
            <thead><tr><th>Tanggal</th><th>Mata Pelajaran</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($attendance as $a): ?>
                    <tr>
                        <td><?= format_date($a['date']) ?></td>
                        <td><?= e($a['subject_name'] ?? '-') ?></td>
                        <td>
                            <?php
                            $colors = ['hadir'=>'success','izin'=>'primary','sakit'=>'warning','alpha'=>'danger'];
                            ?>
                            <span class="badge badge-<?= $colors[$a['status']] ?? 'primary' ?>"><?= ucfirst($a['status']) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
