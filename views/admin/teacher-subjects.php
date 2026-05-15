<div class="page-header">
    <div>
        <h1><i class="fas fa-chalkboard-teacher"></i> Assign Mata Pelajaran</h1>
        <p>Atur mata pelajaran untuk: <strong><?= e($teacher['full_name']) ?></strong> (<?= e($teacher['role'] === 'wali_kelas' ? 'Wali Kelas' : 'Guru') ?>)</p>
    </div>
    <a href="<?= url('admin/users?filter=guru') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card" style="max-width: 800px;">
    <form method="POST" action="<?= url('admin/teacher-subjects/' . $teacher['id']) ?>">
        <?= csrf_field() ?>
        
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-book"></i> Pilih Mata Pelajaran yang Diampu</h3>
        </div>

        <?php if (empty($allSubjects)): ?>
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h3>Belum Ada Mata Pelajaran</h3>
                <p>Tambahkan mata pelajaran terlebih dahulu di menu <a href="<?= url('admin/subjects') ?>">Kelola Mata Pelajaran</a>.</p>
            </div>
        <?php else: ?>
            <div class="subject-checklist">
                <?php 
                $currentClass = '';
                foreach ($allSubjects as $sub): 
                    $classLabel = $sub['grade'] . ' - ' . $sub['class_name'];
                    if ($classLabel !== $currentClass):
                        if ($currentClass !== '') echo '</div>';
                        $currentClass = $classLabel;
                ?>
                    <div class="class-group-header">
                        <i class="fas fa-school"></i> <?= e($classLabel) ?>
                    </div>
                    <div class="class-group-items">
                <?php endif; ?>
                    <label class="subject-check-item">
                        <input type="checkbox" name="subjects[]" value="<?= $sub['id'] ?>" 
                               <?= in_array($sub['id'], $assignedIds) ? 'checked' : '' ?>>
                        <span class="subject-color" style="background: <?= e($sub['color'] ?? '#3B49DF') ?>;"></span>
                        <span class="subject-name"><?= e($sub['name']) ?></span>
                    </label>
                <?php endforeach; ?>
                <?php if ($currentClass !== '') echo '</div>'; ?>
            </div>

            <div style="padding: 20px; border-top: 1px solid var(--border);">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        <?php endif; ?>
    </form>
</div>

<style>
.subject-checklist { padding: 20px; }
.class-group-header {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
    padding: 10px 0 6px;
    margin-top: 10px;
    border-bottom: 1px solid var(--border-light);
    display: flex;
    align-items: center;
    gap: 8px;
}
.class-group-header:first-child { margin-top: 0; }
.class-group-items { padding: 8px 0 8px 10px; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 8px; }
.subject-check-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: var(--transition);
    font-size: 13px;
}
.subject-check-item:hover { border-color: var(--primary); background: var(--primary-bg); }
.subject-check-item input[type="checkbox"] { accent-color: var(--primary); width: 16px; height: 16px; }
.subject-check-item input[type="checkbox"]:checked ~ .subject-name { font-weight: 600; color: var(--primary); }
.subject-color { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.subject-name { color: var(--text-secondary); }
</style>
