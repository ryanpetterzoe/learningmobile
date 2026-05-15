<div class="page-header">
    <div>
        <h1><i class="fas fa-print"></i> Cetak Laporan Jurnal PKL</h1>
        <p>Lengkapi data berikut sebelum mencetak</p>
    </div>
    <a href="<?= url('pkl') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card" style="max-width:600px;">
    <div class="card-header"><h3 class="card-title">Data Cetak Laporan</h3></div>
    
    <form method="GET" action="<?= url('pkl/print/' . $pkl['id']) ?>">
        <input type="hidden" name="route" value="pkl/print/<?= $pkl['id'] ?>">

        <div class="form-group">
            <label>Nama Siswa</label>
            <input type="text" class="form-control" value="<?= e($student['full_name']) ?>" disabled style="background:var(--bg-hover);">
            <small style="color:var(--text-muted);font-size:11px;">Otomatis dari data akun</small>
        </div>

        <div class="form-group">
            <label>Kabupaten / Kota <span style="color:#ef4444;">*</span></label>
            <input type="text" name="kabupaten" class="form-control" placeholder="Contoh: Batang" required>
            <small style="color:var(--text-muted);font-size:11px;">Akan ditampilkan pada format: <em>Batang, 15 Maret 2026</em></small>
        </div>

        <div class="form-group">
            <label>Tanggal Cetak <span style="color:#ef4444;">*</span></label>
            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="form-group">
            <label>Nama Pembimbing Perusahaan <span style="color:#ef4444;">*</span></label>
            <input type="text" name="pembimbing" class="form-control" value="<?= e($pkl['supervisor_name'] ?? '') ?>" placeholder="Nama lengkap pembimbing" required>
            <small style="color:var(--text-muted);font-size:11px;">Akan ditampilkan di bagian tanda tangan</small>
        </div>

        <div style="background:var(--bg-hover);border-radius:10px;padding:16px;margin-bottom:20px;">
            <h4 style="font-size:13px;color:var(--text-secondary);margin-bottom:8px;"><i class="fas fa-info-circle"></i> Preview Format</h4>
            <p style="font-size:13px;color:var(--text-muted);line-height:1.6;margin:0;" id="previewFormat">
                <span id="previewKab">______</span>, <span id="previewTgl">__ ______ ____</span><br>
                Pembimbing Perusahaan<br><br>
                <strong id="previewPembimbing">______________________</strong>
            </p>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-print"></i> Cetak Laporan</button>
            <a href="<?= url('pkl') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<div class="card" style="max-width:600px;margin-top:20px;">
    <div class="card-header"><h3 class="card-title">Ringkasan Jurnal</h3></div>
    <p style="font-size:13px;color:var(--text-secondary);padding:0 0 8px;">
        <strong><?= count($journals) ?></strong> entri jurnal akan dicetak
        <?php if (!empty($journals)): ?>
            (<?= format_date($journals[count($journals)-1]['date'] ?? '') ?> s/d <?= format_date($journals[0]['date'] ?? '') ?>)
        <?php endif; ?>
    </p>
    <?php if (count($journals) === 0): ?>
        <div style="padding:16px;background:#fef3c7;border-radius:8px;">
            <p style="font-size:12px;color:#d97706;margin:0;"><i class="fas fa-exclamation-triangle"></i> Anda belum memiliki jurnal. Isi jurnal terlebih dahulu sebelum mencetak.</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Live preview
var kabInput = document.querySelector('input[name="kabupaten"]');
var tglInput = document.querySelector('input[name="tanggal"]');
var pembInput = document.querySelector('input[name="pembimbing"]');

function updatePreview() {
    var kab = kabInput.value || '______';
    document.getElementById('previewKab').textContent = kab;
    
    var tgl = tglInput.value;
    if (tgl) {
        var d = new Date(tgl);
        var bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        document.getElementById('previewTgl').textContent = d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
    }
    
    document.getElementById('previewPembimbing').textContent = pembInput.value || '______________________';
}

kabInput.addEventListener('input', updatePreview);
tglInput.addEventListener('change', updatePreview);
pembInput.addEventListener('input', updatePreview);
updatePreview();
</script>
