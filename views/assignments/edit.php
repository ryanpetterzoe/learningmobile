<div class="page-header">
    <div><h1><i class="fas fa-edit"></i> Edit Tugas</h1></div>
    <a href="<?= url('assignments/view/' . $assignment['id']) ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card">
    <form method="POST" action="<?= url('assignments/edit/' . $assignment['id']) ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Judul Tugas <span style="color:var(--danger);">*</span></label>
            <input type="text" name="title" class="form-control" value="<?= e($assignment['title']) ?>" required>
        </div>
        <div class="form-group">
            <label>Instruksi / Deskripsi</label>
            <textarea name="description" class="form-control" rows="5"><?= e($assignment['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Deadline <span style="color:var(--danger);">*</span></label>
            <input type="datetime-local" name="deadline" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($assignment['deadline'])) ?>" min="<?= date('Y-m-d\TH:i') ?>" required>
            <small style="color:var(--text-muted);font-size:11px;">Harus di atas waktu saat ini</small>
        </div>
        <div class="form-group" style="display:flex;gap:20px;flex-wrap:wrap;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" name="allow_late" value="1" <?= $assignment['allow_late'] ? 'checked' : '' ?>>
                Izinkan pengumpulan terlambat
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" name="allow_revision" value="1" <?= $assignment['allow_revision'] ? 'checked' : '' ?>>
                Izinkan revisi
            </label>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fas fa-save"></i> Simpan Perubahan</button>
    </form>
</div>

<div style="height:80px;"></div>
