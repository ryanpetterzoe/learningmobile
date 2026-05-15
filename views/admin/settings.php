<div class="page-header">
    <div>
        <h1><i class="fas fa-cog"></i> Pengaturan Sistem</h1>
        <p>Konfigurasi identitas dan tampilan aplikasi</p>
    </div>
</div>

<form method="POST" action="<?= url('admin/settings') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    
    <div class="grid-2">
        <!-- App Identity -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Identitas Aplikasi</h3>
            </div>
            <div class="form-group">
                <label>Nama Aplikasi</label>
                <input type="text" name="app_name" class="form-control" value="<?= e($settings['app_name'] ?? 'SimpleEdu') ?>">
            </div>
            <div class="form-group">
                <label>Nama Sekolah</label>
                <input type="text" name="school_name" class="form-control" value="<?= e($settings['school_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Slogan Aplikasi</label>
                <input type="text" name="app_slogan" class="form-control" placeholder="Contoh: Belajar Lebih Mudah & Menyenangkan" value="<?= e($settings['app_slogan'] ?? '') ?>">
                <small style="color:var(--text-muted);font-size:11px;">Ditampilkan di halaman login dan branding</small>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="app_desc" class="form-control" rows="3"><?= e($settings['app_desc'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Logo Aplikasi</label>
                <?php if (!empty($settings['app_logo'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?= upload_url($settings['app_logo']) ?>" style="max-height: 60px; border-radius: 10px;">
                        <p style="font-size:11px;color:var(--text-muted);margin-top:4px;">Logo saat ini (digunakan di sidebar, halaman login, dan splash screen)</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="app_logo" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label>Alamat Sekolah</label>
                <textarea name="school_address" class="form-control" rows="2" placeholder="Contoh: Jl. Pendidikan No. 1, Kec. Batang, Kab. Batang, Jawa Tengah 51215"><?= e($settings['school_address'] ?? '') ?></textarea>
                <small style="color:var(--text-muted);font-size:11px;">Ditampilkan di cetak laporan/surat resmi</small>
            </div>
            <div class="form-group">
                <label>Kontak Sekolah</label>
                <input type="text" name="school_contact" class="form-control" placeholder="Contoh: Telp. (0285) 391234 | Email: info@smk.sch.id" value="<?= e($settings['school_contact'] ?? '') ?>">
                <small style="color:var(--text-muted);font-size:11px;">Nomor telepon, email, atau website sekolah</small>
            </div>
            <div class="form-group">
                <label>Copyright Footer</label>
                <input type="text" name="app_copyright" class="form-control" placeholder="Contoh: &copy; 2025 SMK Nusantara. All rights reserved." value="<?= e($settings['app_copyright'] ?? '') ?>">
                <small style="color:var(--text-muted);font-size:11px;">Ditampilkan di bagian bawah semua halaman</small>
            </div>
        </div>

        <!-- Appearance -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tampilan</h3>
            </div>
            <div class="form-group">
                <label>Warna Utama</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="color" name="primary_color" value="<?= e($settings['primary_color'] ?? '#3B49DF') ?>" style="width: 50px; height: 40px; border: none; border-radius: 8px; cursor: pointer;">
                    <input type="text" class="form-control" value="<?= e($settings['primary_color'] ?? '#3B49DF') ?>" style="width: 120px;" readonly>
                </div>
            </div>
            <div class="form-group">
                <label>Theme Default</label>
                <select name="theme" class="form-control">
                    <option value="light" <?= ($settings['theme'] ?? 'light') === 'light' ? 'selected' : '' ?>>Light Mode</option>
                    <option value="dark" <?= ($settings['theme'] ?? '') === 'dark' ? 'selected' : '' ?>>Dark Mode</option>
                </select>
            </div>

            <div style="margin-top: 30px; padding: 16px; background: var(--bg-hover); border-radius: var(--radius-md);">
                <h4 style="font-size: 13px; margin-bottom: 8px; color: var(--text-secondary);">Info Sistem</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">
                    <strong>Versi:</strong> <?= e($settings['version'] ?? '1.0.0') ?>
                </p>
                <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">
                    <strong>Installed:</strong> <?= e($settings['installed_at'] ?? '-') ?>
                </p>
                <p style="font-size: 12px; color: var(--text-muted);">
                    <strong>PHP:</strong> <?= PHP_VERSION ?>
                </p>
            </div>
        </div>
    </div>

    <div style="margin-top: 20px; text-align: right;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Pengaturan</button>
    </div>
</form>
