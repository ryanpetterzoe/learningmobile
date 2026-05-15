<div class="page-header">
    <div><h1><i class="fas fa-chart-line"></i> Rekap Nilai</h1><p>Lihat nilai siswa per mata pelajaran</p></div>
</div>

<?php if (empty($subjects)): ?>
    <div class="card"><div class="empty-state"><i class="fas fa-book"></i><h3>Tidak Ada Mata Pelajaran</h3></div></div>
<?php else: ?>
    <div class="card">
        <div class="table-container">
            <table>
                <thead><tr><th>Mata Pelajaran</th><th>Kelas</th><th>Jumlah Nilai</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach ($subjects as $s): 
                        $gradeCount = Database::getInstance()->count('grades', 'subject_id = ?', [$s['id']]);
                    ?>
                        <tr>
                            <td style="font-weight:600;"><?= e($s['name']) ?></td>
                            <td><?= e($s['class_name']) ?></td>
                            <td><?= $gradeCount ?> entri</td>
                            <td><a href="<?= url('subject/view/' . $s['id']) ?>" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i> Lihat</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
