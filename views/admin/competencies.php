<div class="page-header">
    <div>
        <h1><i class="fas fa-graduation-cap"></i> Kelola Kompetensi Keahlian</h1>
        <p>Kelola data kompetensi keahlian untuk profil siswa SMK</p>
    </div>
</div>

<div class="admin-layout" style="display:grid;grid-template-columns:1fr 350px;gap:20px;">
    <!-- Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Kompetensi Keahlian</h3>
        </div>
        <?php if (empty($competencies)): ?>
            <div style="padding:30px;text-align:center;">
                <i class="fas fa-graduation-cap" style="font-size:40px;color:var(--text-muted);margin-bottom:12px;"></i>
                <p style="color:var(--text-muted);font-size:13px;">Belum ada data kompetensi keahlian.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kompetensi</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Siswa</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($competencies as $i => $comp): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= e($comp['name']) ?></strong></td>
                                <td style="font-size:12px;color:var(--text-muted);"><?= e($comp['description'] ?? '-') ?></td>
                                <td>
                                    <span class="badge badge-primary"><?= $comp['user_count'] ?> siswa</span>
                                </td>
                                <td>
                                    <form method="POST" action="<?= url('admin/competencies') ?>" style="display:inline;" onsubmit="return confirm('Hapus kompetensi ini?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="comp_action" value="delete">
                                        <input type="hidden" name="comp_id" value="<?= $comp['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Form -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Kompetensi</h3>
            </div>
            <form method="POST" action="<?= url('admin/competencies') ?>" style="padding:16px;">
                <?= csrf_field() ?>
                <input type="hidden" name="comp_action" value="create">
                <div class="form-group">
                    <label>Nama Kompetensi <span style="color:red;">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi (Opsional)</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi singkat kompetensi keahlian..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fas fa-plus"></i> Tambah Kompetensi
                </button>
            </form>
        </div>
    </div>
</div>

<style>
@media (max-width: 900px) {
    .admin-layout { grid-template-columns: 1fr !important; }
}
</style>
