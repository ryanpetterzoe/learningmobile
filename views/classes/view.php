<?php $isTeacher = Auth::isAdmin() || Auth::isGuru(); ?>

<div class="page-header">
    <div>
        <h1><?= e($class['name']) ?></h1>
        <p><?= e($class['major'] ?? '') ?> • <?= e($class['academic_year']) ?> • Wali Kelas: <?= e($class['homeroom_teacher'] ?? '-') ?></p>
    </div>
    <a href="<?= url('classes') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<!-- Tab Navigation -->
<div class="tab-nav" style="margin-bottom: 20px;">
    <button class="tab-btn active" data-tab="subjects">📚 Mata Pelajaran</button>
    <button class="tab-btn" data-tab="members">👥 Anggota (<?= count($members) ?>)</button>
</div>

<!-- Subjects Tab -->
<div class="tab-content active" id="tab-subjects">
    <?php if ($isTeacher): ?>
        <div style="margin-bottom: 20px;">
            <button class="btn btn-primary" onclick="openModal('addSubjectModal')"><i class="fas fa-plus"></i> Tambah Mata Pelajaran</button>
        </div>
    <?php endif; ?>

    <?php if (empty($subjects)): ?>
        <div class="card"><div class="empty-state"><i class="fas fa-book"></i><h3>Belum Ada Mata Pelajaran</h3></div></div>
    <?php else: ?>
        <div class="subjects-grid">
            <?php foreach ($subjects as $sub): ?>
                <a href="<?= url('subject/view/' . $sub['id']) ?>" class="subject-card">
                    <div class="subject-color" style="background: <?= e($sub['color']) ?>;"></div>
                    <div class="subject-info">
                        <h4><?= e($sub['name']) ?></h4>
                        <p><i class="fas fa-user"></i> <?= e($sub['teacher_name']) ?></p>
                    </div>
                    <i class="fas fa-chevron-right" style="color: var(--text-muted);"></i>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Members Tab -->
<div class="tab-content" id="tab-members">
    <?php if ($isTeacher): ?>
        <div style="margin-bottom: 20px;">
            <button class="btn btn-primary" onclick="openModal('addMemberModal')"><i class="fas fa-user-plus"></i> Tambah Siswa</button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="table-container">
            <table>
                <thead><tr><th>Nama</th><th>NIS</th><th>Role</th><th>Bergabung</th><?php if($isTeacher): ?><th>Aksi</th><?php endif; ?></tr></thead>
                <tbody>
                    <?php foreach ($members as $m): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <?php if ($m['avatar']): ?>
                                        <img src="<?= upload_url($m['avatar']) ?>" class="user-cell-avatar">
                                    <?php else: ?>
                                        <div class="user-cell-avatar placeholder"><?= strtoupper(substr($m['full_name'], 0, 1)) ?></div>
                                    <?php endif; ?>
                                    <div>
                                        <span class="user-cell-name"><?= e($m['full_name']) ?></span>
                                        <span class="user-cell-email"><?= e($m['email']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($m['nis'] ?? '-') ?></td>
                            <td><span class="badge badge-primary"><?= ucfirst($m['member_role']) ?></span></td>
                            <td><span style="font-size:12px;color:var(--text-muted);"><?= format_date($m['joined_at']) ?></span></td>
                            <?php if($isTeacher): ?>
                                <td>
                                    <form method="POST" action="<?= url('classes/remove-member/' . $class['id']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" data-confirm="Hapus anggota ini dari kelas?"><i class="fas fa-times"></i></button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Subject Modal -->
<div class="modal-overlay" id="addSubjectModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Tambah Mata Pelajaran</h3>
            <button class="modal-close" onclick="closeModal('addSubjectModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="<?= url('classes/add-subject/' . $class['id']) ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Mata Pelajaran</label>
                    <input type="text" name="name" class="form-control" placeholder="Pemrograman Web" required>
                </div>
                <div class="form-group">
                    <label>Guru Pengajar</label>
                    <select name="teacher_id" class="form-control" required>
                        <option value="">-- Pilih Guru --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px;">
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <input type="text" name="description" class="form-control" placeholder="Opsional">
                    </div>
                    <div class="form-group">
                        <label>Warna</label>
                        <input type="color" name="color" value="#3B49DF" style="width:50px;height:38px;border:none;border-radius:8px;cursor:pointer;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addSubjectModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Tambah</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal-overlay" id="addMemberModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Tambah Siswa ke Kelas</h3>
            <button class="modal-close" onclick="closeModal('addMemberModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="<?= url('classes/add-member/' . $class['id']) ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label>Pilih Siswa (bisa pilih beberapa)</label>
                    <select name="student_ids[]" class="form-control" multiple style="min-height: 200px;">
                        <?php 
                        $memberIds = array_column($members, 'id');
                        foreach ($allStudents as $s): 
                            if (in_array($s['id'], $memberIds)) continue;
                        ?>
                            <option value="<?= $s['id'] ?>"><?= e($s['full_name']) ?> <?= $s['nis'] ? '(' . e($s['nis']) . ')' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--text-muted); font-size: 11px;">Hold Ctrl/Cmd untuk pilih lebih dari satu</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addMemberModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Tambah Siswa</button>
            </div>
        </form>
    </div>
</div>

<style>
.tab-nav { display: flex; gap: 4px; border-bottom: 2px solid var(--border); padding-bottom: 0; }
.tab-btn {
    padding: 10px 18px; border: none; background: none; font-size: 13px; font-weight: 600;
    color: var(--text-secondary); cursor: pointer; border-bottom: 2px solid transparent;
    margin-bottom: -2px; transition: var(--transition); font-family: inherit;
}
.tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
.tab-btn:hover { color: var(--primary); }
.tab-content { display: none; animation: fadeIn 0.3s ease; }
.tab-content.active { display: block; }

.subjects-grid { display: flex; flex-direction: column; gap: 10px; }
.subject-card {
    display: flex; align-items: center; gap: 16px; padding: 16px 20px;
    background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md);
    text-decoration: none; transition: var(--transition);
}
.subject-card:hover { border-color: var(--primary); transform: translateX(4px); box-shadow: var(--shadow-sm); }
.subject-color { width: 8px; height: 40px; border-radius: 4px; }
.subject-info { flex: 1; }
.subject-info h4 { font-size: 14px; color: var(--text-primary); margin-bottom: 4px; }
.subject-info p { font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; }

.user-cell { display: flex; align-items: center; gap: 10px; }
.user-cell-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }
.user-cell-avatar.placeholder { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
.user-cell-name { display: block; font-size: 13px; font-weight: 600; color: var(--text-primary); }
.user-cell-email { display: block; font-size: 11px; color: var(--text-muted); }
</style>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});
</script>
