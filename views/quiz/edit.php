<div class="page-header">
    <div><h1><i class="fas fa-edit"></i> Edit Quiz</h1></div>
    <a href="<?= url('quiz') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card">
    <form method="POST" action="<?= url('quiz/edit/' . $quiz['id']) ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Judul Quiz <span style="color:var(--danger);">*</span></label>
            <input type="text" name="title" class="form-control" value="<?= e($quiz['title']) ?>" required>
        </div>
        <div class="form-group">
            <label>Mata Pelajaran</label>
            <select name="subject_id" class="form-control" disabled>
                <?php foreach ($subjects as $sub): ?>
                    <option value="<?= $sub['id'] ?>" <?= $sub['id'] == $quiz['subject_id'] ? 'selected' : '' ?>><?= e($sub['name']) ?> (<?= e($sub['class_name']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <small style="color:var(--text-muted);font-size:11px;">Mata pelajaran tidak bisa diubah setelah dibuat</small>
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" class="form-control" rows="3"><?= e($quiz['description'] ?? '') ?></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
                <label>Durasi (menit)</label>
                <input type="number" name="duration" class="form-control" value="<?= $quiz['duration_minutes'] ?>" min="1">
            </div>
            <div class="form-group">
                <label>Passing Score (%)</label>
                <input type="number" name="passing_score" class="form-control" value="<?= $quiz['passing_score'] ?>" min="0" max="100">
            </div>
        </div>
        <div class="form-group">
            <label>Status <span style="color:var(--danger);">*</span></label>
            <select name="status" class="form-control" required>
                <option value="draft" <?= $quiz['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="active" <?= $quiz['status'] === 'active' ? 'selected' : '' ?>>Aktif</option>
                <option value="closed" <?= $quiz['status'] === 'closed' ? 'selected' : '' ?>>Ditutup</option>
            </select>
            <small style="color:var(--text-muted);font-size:11px;">Ubah ke "Aktif" agar siswa bisa mengerjakan. "Ditutup" = siswa yang belum mengerjakan otomatis nilai 0.</small>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
                <label>Waktu Mulai (opsional)</label>
                <input type="datetime-local" name="start_time" class="form-control" value="<?= $quiz['start_time'] ? date('Y-m-d\TH:i', strtotime($quiz['start_time'])) : '' ?>">
            </div>
            <div class="form-group">
                <label>Waktu Berakhir (opsional)</label>
                <input type="datetime-local" name="end_time" class="form-control" value="<?= $quiz['end_time'] ? date('Y-m-d\TH:i', strtotime($quiz['end_time'])) : '' ?>">
            </div>
        </div>
        <div class="form-group" style="display:flex;gap:20px;flex-wrap:wrap;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" name="shuffle_questions" value="1" <?= $quiz['shuffle_questions'] ? 'checked' : '' ?>>
                Acak urutan soal
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" name="shuffle_options" value="1" <?= $quiz['shuffle_options'] ? 'checked' : '' ?>>
                Acak pilihan jawaban
            </label>
        </div>
        <div style="display:flex;gap:10px;margin-top:10px;">
            <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;"><i class="fas fa-save"></i> Simpan Perubahan</button>
            <a href="<?= url('quiz/questions/' . $quiz['id']) ?>" class="btn btn-secondary"><i class="fas fa-list"></i> Kelola Soal</a>
        </div>
    </form>
</div>

<div style="margin-top:16px;">
    <form method="POST" action="<?= url('quiz/delete/' . $quiz['id']) ?>" style="display:inline;">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;" data-confirm="Yakin hapus quiz ini? Semua soal dan hasil akan hilang."><i class="fas fa-trash"></i> Hapus Quiz</button>
    </form>
</div>

<div style="height:80px;"></div>
