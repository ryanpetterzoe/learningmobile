<div class="page-header">
    <div>
        <h1><i class="fas fa-users-cog"></i> Kelola Pengguna</h1>
        <p>Total <?= $total ?> pengguna terdaftar</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('createUserModal')"><i class="fas fa-user-plus"></i> Tambah User</button>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px; padding: 16px;">
    <div class="filters-bar">
        <div class="filter-tabs">
            <a href="<?= url('admin/users?filter=all') ?>" class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">Semua</a>
            <a href="<?= url('admin/users?filter=pending') ?>" class="filter-tab <?= $filter === 'pending' ? 'active' : '' ?>">
                Pending
                <?php 
                $pendingCount = Database::getInstance()->count('users', 'status = "pending"');
                if ($pendingCount > 0): ?>
                    <span class="tab-badge"><?= $pendingCount ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= url('admin/users?filter=active') ?>" class="filter-tab <?= $filter === 'active' ? 'active' : '' ?>">Aktif</a>
            <a href="<?= url('admin/users?filter=siswa') ?>" class="filter-tab <?= $filter === 'siswa' ? 'active' : '' ?>">Siswa</a>
            <a href="<?= url('admin/users?filter=guru') ?>" class="filter-tab <?= $filter === 'guru' ? 'active' : '' ?>">Guru</a>
            <a href="<?= url('admin/users?filter=suspended') ?>" class="filter-tab <?= $filter === 'suspended' ? 'active' : '' ?>">Suspended</a>
        </div>
        <form method="GET" action="<?= url('admin/users') ?>" class="search-filter">
            <input type="hidden" name="route" value="admin/users">
            <input type="hidden" name="filter" value="<?= e($filter) ?>">
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari nama atau email..." class="form-control" style="width: 250px; padding: 8px 14px; font-size: 13px;">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Pengguna</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>NIS/NIP</th>
                    <th>Terdaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="6" style="text-align:center; padding: 40px; color: var(--text-muted);">Tidak ada data pengguna.</td></tr>
                <?php endif; ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <?php if ($u['avatar']): ?>
                                    <img src="<?= upload_url($u['avatar']) ?>" class="user-cell-avatar">
                                <?php else: ?>
                                    <div class="user-cell-avatar placeholder"><?= strtoupper(substr($u['full_name'], 0, 1)) ?></div>
                                <?php endif; ?>
                                <div>
                                    <span class="user-cell-name"><?= e($u['full_name']) ?></span>
                                    <span class="user-cell-email"><?= e($u['email']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <form method="POST" action="<?= url('admin/user-role/' . $u['id']) ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <select name="role" onchange="this.form.submit()" class="role-select">
                                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="guru" <?= $u['role'] === 'guru' ? 'selected' : '' ?>>Guru</option>
                                    <option value="wali_kelas" <?= $u['role'] === 'wali_kelas' ? 'selected' : '' ?>>Wali Kelas</option>
                                    <option value="siswa" <?= $u['role'] === 'siswa' ? 'selected' : '' ?>>Siswa</option>
                                    <option value="orang_tua" <?= $u['role'] === 'orang_tua' ? 'selected' : '' ?>>Orang Tua</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <?php if ($u['status'] === 'active'): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php elseif ($u['status'] === 'pending'): ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Suspended</span>
                            <?php endif; ?>
                        </td>
                        <td><span style="font-size: 12px; color: var(--text-muted);"><?= e($u['nis'] ?: $u['nip'] ?: '-') ?></span></td>
                        <td><span style="font-size: 12px; color: var(--text-muted);"><?= time_ago($u['created_at']) ?></span></td>
                        <td>
                            <div class="action-btns">
                                <?php if ($u['status'] === 'pending'): ?>
                                    <form method="POST" action="<?= url('admin/user-approve/' . $u['id']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve"><i class="fas fa-check"></i></button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($u['status'] === 'active' && $u['id'] != Session::userId()): ?>
                                    <form method="POST" action="<?= url('admin/user-suspend/' . $u['id']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-warning" title="Suspend" data-confirm="Nonaktifkan pengguna ini?"><i class="fas fa-ban"></i></button>
                                    </form>
                                <?php elseif ($u['status'] === 'suspended'): ?>
                                    <form method="POST" action="<?= url('admin/user-approve/' . $u['id']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-success" title="Aktifkan Kembali"><i class="fas fa-check"></i></button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($u['id'] != Session::userId()): ?>
                                    <form method="POST" action="<?= url('admin/user-delete/' . $u['id']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus" data-confirm="Yakin hapus pengguna ini? Data tidak dapat dikembalikan."><i class="fas fa-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= url("admin/users?filter={$filter}&search={$search}&page={$i}") ?>" 
                   class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.filters-bar { display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap; }
.filter-tabs { display: flex; gap: 4px; flex-wrap: wrap; }
.filter-tab {
    padding: 7px 14px; border-radius: 20px; font-size: 12px; font-weight: 500;
    color: var(--text-secondary); text-decoration: none; transition: var(--transition);
    display: flex; align-items: center; gap: 6px;
}
.filter-tab:hover { background: var(--bg-hover); color: var(--text-primary); }
.filter-tab.active { background: var(--primary-bg); color: var(--primary); font-weight: 600; }
.tab-badge { background: var(--danger); color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 10px; }
.search-filter { display: flex; gap: 8px; align-items: center; }

.user-cell { display: flex; align-items: center; gap: 12px; }
.user-cell-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
.user-cell-avatar.placeholder {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700;
}
.user-cell-name { display: block; font-size: 13px; font-weight: 600; color: var(--text-primary); }
.user-cell-email { display: block; font-size: 11px; color: var(--text-muted); }

.role-select {
    padding: 4px 8px; border: 1px solid var(--border); border-radius: 6px;
    font-size: 11px; background: var(--bg-card); color: var(--text-primary);
    cursor: pointer; outline: none;
}

.action-btns { display: flex; gap: 4px; }

.pagination { display: flex; justify-content: center; gap: 4px; padding: 16px; }
.page-link {
    width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
    border-radius: 8px; font-size: 12px; font-weight: 500; color: var(--text-secondary);
    text-decoration: none; transition: var(--transition);
}
.page-link:hover { background: var(--bg-hover); }
.page-link.active { background: var(--primary); color: #fff; }

@media (max-width: 768px) {
    .filters-bar { flex-direction: column; align-items: stretch; }
    .filter-tabs { overflow-x: auto; }
    .search-filter { width: 100%; }
    .search-filter input { flex: 1; }
}
</style>

<!-- Create User Modal -->
<div class="modal-overlay" id="createUserModal">
    <div class="modal" style="max-width: 550px;">
        <div class="modal-header">
            <h3>Tambah Pengguna Baru</h3>
            <button class="modal-close" onclick="closeModal('createUserModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="<?= url('admin/user-create') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Lengkap <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="full_name" class="form-control" placeholder="Nama lengkap" required>
                </div>
                <div class="form-group">
                    <label>Email <span style="color:var(--danger);">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="email@sekolah.sch.id" required>
                </div>
                <div class="form-group">
                    <label>Password <span style="color:var(--danger);">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>
                <div class="form-group">
                    <label>Role <span style="color:var(--danger);">*</span></label>
                    <select name="role" class="form-control" required>
                        <option value="siswa">Siswa</option>
                        <option value="guru">Guru</option>
                        <option value="wali_kelas">Wali Kelas</option>
                        <option value="admin">Admin</option>
                        <option value="orang_tua">Orang Tua</option>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label>NIS (Siswa)</label>
                        <input type="text" name="nis" class="form-control" placeholder="Opsional">
                    </div>
                    <div class="form-group">
                        <label>NIP (Guru)</label>
                        <input type="text" name="nip" class="form-control" placeholder="Opsional">
                    </div>
                </div>
                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx">
                </div>
                <div class="alert alert-info" style="margin-bottom: 0;">
                    <i class="fas fa-info-circle"></i> User yang ditambahkan admin langsung berstatus <strong>Aktif</strong> tanpa perlu verifikasi.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createUserModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Tambah User</button>
            </div>
        </form>
    </div>
</div>
