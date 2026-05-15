<div class="page-header">
    <div><h1><i class="fas fa-plus-circle"></i> Buat Quiz Baru</h1></div>
    <a href="<?= url('quiz') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>
<div class="card" style="max-width: 650px;">
    <form method="POST" action="<?= url('quiz/create') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Mata Pelajaran</label>
            <select name="subject_id" class="form-control" required>
                <option value="">-- Pilih --</option>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> - <?= e($s['class_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Judul Quiz</label><input type="text" name="title" class="form-control" required></div>
        <div class="form-group"><label>Deskripsi</label><textarea name="description" class="form-control" rows="3"></textarea></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
            <div class="form-group"><label>Durasi (menit)</label><input type="number" name="duration" class="form-control" value="60"></div>
            <div class="form-group"><label>Nilai Minimum Lulus (%)</label><input type="number" name="passing_score" class="form-control" value="70"></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
            <div class="form-group"><label>Waktu Mulai (opsional)</label><input type="datetime-local" name="start_time" class="form-control"></div>
            <div class="form-group"><label>Waktu Selesai (opsional)</label><input type="datetime-local" name="end_time" class="form-control"></div>
        </div>
        <div class="form-group"><label>Status</label>
            <select name="status" class="form-control">
                <option value="draft">Draft</option>
                <option value="active">Aktif (langsung bisa dikerjakan)</option>
            </select>
        </div>
        <div style="display:flex;gap:20px;margin:10px 0;">
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;"><input type="checkbox" name="shuffle_questions" checked style="accent-color:var(--primary);"> Acak urutan soal</label>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;"><input type="checkbox" name="shuffle_options" checked style="accent-color:var(--primary);"> Acak pilihan jawaban</label>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:10px;"><i class="fas fa-save"></i> Buat Quiz</button>
    </form>
</div>
