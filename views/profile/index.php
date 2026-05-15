<div class="page-header">
    <div><h1><i class="fas fa-user"></i> Profile Saya</h1></div>
</div>

<div class="grid-2">
    <!-- Left: Profile Card -->
    <div>
        <div class="card profile-card">
            <div class="profile-header-bg"></div>
            <div class="profile-main">
                <div class="profile-avatar-wrapper">
                    <?php if ($user['avatar']): ?>
                        <img src="<?= upload_url($user['avatar']) ?>" class="profile-avatar-img">
                    <?php else: ?>
                        <div class="profile-avatar-placeholder"><?= strtoupper(substr($user['full_name'], 0, 2)) ?></div>
                    <?php endif; ?>
                </div>
                <h2><?= e($user['full_name']) ?></h2>
                <p class="profile-role"><span class="badge badge-primary"><?= ucfirst($user['role']) ?></span></p>
                <p class="profile-email"><i class="fas fa-envelope"></i> <?= e($user['email']) ?></p>
                <?php if ($user['phone']): ?><p class="profile-phone"><i class="fas fa-phone"></i> <?= e($user['phone']) ?></p><?php endif; ?>
                <?php if ($user['bio']): ?><p class="profile-bio"><?= e($user['bio']) ?></p><?php endif; ?>

                <!-- XP Progress -->
                <div class="xp-section">
                    <div class="xp-header">
                        <span class="xp-level">Level <?= $user['level'] ?> - <?= get_level_name($user['level']) ?></span>
                        <span class="xp-points"><?= number_format($user['xp_points']) ?> / <?= number_format($xpForNext) ?> XP</span>
                    </div>
                    <div class="progress-bar"><div class="progress-fill" style="width: <?= $xpProgress ?>%;"></div></div>
                </div>

                <!-- Badges -->
                <?php if (!empty($badges)): ?>
                    <div class="profile-badges">
                        <h4>Badge Saya</h4>
                        <div class="badges-row">
                            <?php foreach ($badges as $b): ?>
                                <span class="badge-item" title="<?= e($b['name']) ?>"><?= $b['icon'] ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: Edit Form -->
    <div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Edit Profil</h3></div>
            <form method="POST" action="<?= url('profile') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Foto Profil</label>
                    <input type="file" name="avatar" class="form-control" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" class="form-control" rows="3"><?= e($user['bio'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="form-control" value="<?= e($user['birth_date'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Kompetensi Keahlian</label>
                    <select name="competency_id" class="form-control">
                        <option value="">-- Pilih Kompetensi --</option>
                        <?php if (!empty($competencies)): ?>
                            <?php foreach ($competencies as $comp): ?>
                                <option value="<?= $comp['id'] ?>" <?= (($user['competency_id'] ?? '') == $comp['id']) ? 'selected' : '' ?>><?= e($comp['name']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <hr style="border-color: var(--border-light); margin: 20px 0;">
                <h4 style="font-size: 14px; margin-bottom: 15px;">Ubah Password</h4>
                <div class="form-group">
                    <label>Password Lama</label>
                    <input type="password" name="current_password" class="form-control">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group"><label>Password Baru</label><input type="password" name="new_password" class="form-control"></div>
                    <div class="form-group"><label>Konfirmasi</label><input type="password" name="confirm_password" class="form-control"></div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 10px;"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>

<style>
.profile-card { overflow: hidden; }
.profile-header-bg { height: 100px; background: linear-gradient(135deg, #3B49DF, #6366f1); margin: -20px -20px 0; }
.profile-main { text-align: center; padding: 0 20px 20px; margin-top: -40px; }
.profile-avatar-wrapper { display: inline-block; }
.profile-avatar-img, .profile-avatar-placeholder { width: 80px; height: 80px; border-radius: 50%; border: 4px solid var(--bg-card); object-fit: cover; }
.profile-avatar-placeholder { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; margin: 0 auto; }
.profile-main h2 { font-size: 18px; margin-top: 10px; color: var(--text-primary); }
.profile-role { margin: 6px 0; }
.profile-email, .profile-phone { font-size: 13px; color: var(--text-muted); margin: 4px 0; display: flex; align-items: center; justify-content: center; gap: 6px; }
.profile-bio { font-size: 13px; color: var(--text-secondary); margin-top: 10px; font-style: italic; }
.xp-section { margin-top: 20px; padding: 16px; background: var(--bg-hover); border-radius: var(--radius-md); }
.xp-header { display: flex; justify-content: space-between; margin-bottom: 8px; }
.xp-level { font-size: 12px; font-weight: 700; color: var(--primary); }
.xp-points { font-size: 11px; color: var(--text-muted); }
.profile-badges { margin-top: 16px; }
.profile-badges h4 { font-size: 13px; margin-bottom: 8px; color: var(--text-secondary); }
.badges-row { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }
.badge-item { font-size: 24px; cursor: default; }
</style>
