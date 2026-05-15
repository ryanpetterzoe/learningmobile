<div class="page-header">
    <div>
        <h1><i class="fas fa-book"></i> Kelola Mata Pelajaran</h1>
        <p>Total <?= count($allSubjects) ?> mata pelajaran terdaftar</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('createSubjectModal')"><i class="fas fa-plus"></i> Tambah Mata Pelajaran</button>
</div>

<?php if (empty($allSubjects)): ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h3>Belum Ada Mata Pelajaran</h3>
            <p>Klik tombol "Tambah Mata Pelajaran" untuk memulai.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Guru Pengajar</th>
                        <th>Warna</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allSubjects as $sub): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 6px; height: 30px; border-radius: 3px; background: <?= e($sub['color'] ?? '#3B49DF') ?>;"></div>
                                    <div>
                                        <span style="font-weight: 600; font-size: 13px; color: var(--text-primary);"><?= e($sub['name']) ?></span>
                                        <?php if ($sub['description']): ?>
                                            <span style="display: block; font-size: 11px; color: var(--text-muted);"><?= e(truncate($sub['description'], 50)) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-primary"><?= e($sub['grade']) ?></span>
                                <span style="font-size: 12px; color: var(--text-secondary);"><?= e($sub['class_name']) ?></span>
                            </td>
                            <td style="font-size: 13px; color: var(--text-secondary);">
                                <i class="fas fa-user" style="color: var(--text-muted); margin-right: 4px;"></i>
                                <?= e($sub['teacher_name']) ?>
                            </td>
                            <td>
                                <div style="width: 24px; height: 24px; border-radius: 6px; background: <?= e($sub['color'] ?? '#3B49DF') ?>;"></div>
                            </td>
                            <td>
                                <form method="POST" action="<?= url('admin/subject-delete/' . $sub['id']) ?>" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger" data-confirm="Hapus mata pelajaran ini? Semua materi, tugas, dan quiz terkait akan ikut terhapus."><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Create Subject Modal -->
<div class="modal-overlay" id="createSubjectModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Tambah Mata Pelajaran</h3>
            <button class="modal-close" onclick="closeModal('createSubjectModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="<?= url('admin/subject-create') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Mata Pelajaran <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Pemrograman Web, Basis Data" required>
                </div>
                <div class="form-group">
                    <label>Kelas <span style="color:var(--danger);">*</span></label>
                    <select name="class_id" class="form-control" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['grade'] . ' - ' . $c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Guru Pengajar <span style="color:var(--danger);">*</span></label>
                    <select name="teacher_id" class="form-control" required>
                        <option value="">-- Pilih Guru --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deskripsi (opsional)</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Deskripsi singkat mata pelajaran..."></textarea>
                </div>
                <div class="form-group">
                    <label>Warna</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="color" name="color" value="#3B49DF" style="width: 50px; height: 38px; border: none; border-radius: 8px; cursor: pointer;">
                        <span style="font-size: 12px; color: var(--text-muted);">Pilih warna untuk identitas mata pelajaran</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createSubjectModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Mata Pelajaran</button>
            </div>
        </form>
    </div>
</div>
