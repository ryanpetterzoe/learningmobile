<div class="page-header">
    <div>
        <h1><i class="fas fa-plus-circle"></i> Buat Tugas Baru</h1>
        <p>Buat tugas untuk siswa Anda</p>
    </div>
    <a href="<?= url('assignments') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card" style="max-width: 700px;">
    <form method="POST" action="<?= url('assignments/create') ?>">
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label>Mata Pelajaran & Kelas</label>
            <select name="subject_id" class="form-control" required>
                <option value="">-- Pilih --</option>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> - <?= e($s['class_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Judul Tugas</label>
            <input type="text" name="title" class="form-control" placeholder="Contoh: Membuat Website Landing Page" required>
        </div>

        <div class="form-group">
            <label>Deskripsi / Instruksi</label>
            <textarea name="description" class="form-control" rows="5" placeholder="Tuliskan instruksi tugas dengan detail..."></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Tipe Pengumpulan</label>
                <select name="type" class="form-control">
                    <option value="both">File & Teks</option>
                    <option value="file">File Only</option>
                    <option value="text">Teks Only</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nilai Maksimal</label>
                <input type="number" name="max_score" class="form-control" value="100" min="1" max="100">
            </div>
        </div>

        <div class="form-group">
            <label>Deadline</label>
            <input type="datetime-local" name="deadline" class="form-control" required>
        </div>

        <div style="display: flex; gap: 20px; margin: 15px 0;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                <input type="checkbox" name="allow_late" style="accent-color: var(--primary);">
                Izinkan submit terlambat
            </label>
            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                <input type="checkbox" name="allow_revision" checked style="accent-color: var(--primary);">
                Izinkan revisi
            </label>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 10px;"><i class="fas fa-paper-plane"></i> Publikasikan Tugas</button>
    </form>
</div>
