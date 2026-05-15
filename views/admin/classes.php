<div class="page-header">
    <div>
        <h1><i class="fas fa-chalkboard-teacher"></i> Kelola Kelas</h1>
        <p><?= count($classes) ?> kelas terdaftar</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('createClassModal')"><i class="fas fa-plus"></i> Tambah Kelas</button>
</div>

<!-- Classes Grid -->
<div class="classes-grid">
    <?php if (empty($classes)): ?>
        <div class="empty-state" style="grid-column: 1/-1;">
            <i class="fas fa-school"></i>
            <h3>Belum Ada Kelas</h3>
            <p>Buat kelas pertama untuk memulai.</p>
        </div>
    <?php endif; ?>
    <?php foreach ($classes as $cls): ?>
        <div class="class-card">
            <div class="class-card-header" style="background: linear-gradient(135deg, #3B49DF, #6366f1);">
                <h3><?= e($cls['name']) ?></h3>
                <span class="class-grade"><?= e($cls['grade']) ?></span>
            </div>
            <div class="class-card-body">
                <?php if ($cls['major']): ?>
                    <p class="class-major"><i class="fas fa-graduation-cap"></i> <?= e($cls['major']) ?></p>
                <?php endif; ?>
                <p class="class-info"><i class="fas fa-user-tie"></i> <?= e($cls['homeroom_teacher'] ?? 'Belum ditentukan') ?></p>
                <p class="class-info"><i class="fas fa-users"></i> <?= $cls['student_count'] ?> siswa</p>
                <p class="class-info"><i class="fas fa-calendar"></i> <?= e($cls['academic_year']) ?></p>
            </div>
            <div class="class-card-footer">
                <a href="<?= url('classes/view/' . $cls['id']) ?>" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i> Lihat</a>
                <form method="POST" action="<?= url('admin/class-delete/' . $cls['id']) ?>" style="display:inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" data-confirm="Hapus kelas ini? Semua data terkait akan hilang."><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Create Class Modal -->
<div class="modal-overlay" id="createClassModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Tambah Kelas Baru</h3>
            <button class="modal-close" onclick="closeModal('createClassModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="<?= url('admin/class-create') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Kelas</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: X RPL 1" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Tingkat</label>
                        <select name="grade" class="form-control" required>
                            <option value="X">X (Sepuluh)</option>
                            <option value="XI">XI (Sebelas)</option>
                            <option value="XII">XII (Dua Belas)</option>
                            <option value="XIII">XIII (Tiga Belas)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tahun Ajaran</label>
                        <input type="text" name="academic_year" class="form-control" value="<?= date('Y') . '/' . (date('Y')+1) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Jurusan</label>
                    <input type="text" name="major" class="form-control" placeholder="RPL / TKJ / DKV / dll">
                </div>
                <div class="form-group">
                    <label>Wali Kelas</label>
                    <select name="homeroom_teacher_id" class="form-control">
                        <option value="">-- Pilih Wali Kelas --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deskripsi (opsional)</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi singkat kelas..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createClassModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Kelas</button>
            </div>
        </form>
    </div>
</div>

<style>
.classes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.class-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: var(--transition);
}

.class-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.class-card-header {
    padding: 20px;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.class-card-header h3 { font-size: 16px; font-weight: 700; }
.class-grade { font-size: 12px; background: rgba(255,255,255,0.2); padding: 3px 10px; border-radius: 12px; }

.class-card-body { padding: 16px 20px; }
.class-major { font-size: 13px; font-weight: 600; color: var(--primary); margin-bottom: 10px; }
.class-info { font-size: 12px; color: var(--text-secondary); margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
.class-info i { width: 16px; color: var(--text-muted); }

.class-card-footer {
    padding: 12px 20px;
    border-top: 1px solid var(--border-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>
