<div class="page-header">
    <div>
        <h1><i class="fas fa-clock"></i> Kelola Jadwal Pelajaran</h1>
        <p>Atur jadwal kelas per hari</p>
    </div>
</div>

<!-- Class Selector -->
<div class="card" style="margin-bottom: 20px; padding: 16px;">
    <form method="GET" action="<?= url('admin/schedules') ?>" class="filters-bar">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <label style="font-size: 13px; font-weight: 600; color: var(--text-primary);">Pilih Kelas:</label>
            <select name="class_id" class="form-control" style="width: 250px; padding: 8px 14px; font-size: 13px;" onchange="this.form.submit()">
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $selectedClass == $c['id'] ? 'selected' : '' ?>><?= e($c['grade'] . ' - ' . $c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<?php if ($selectedClass): ?>
    <!-- Add Schedule Button -->
    <div style="margin-bottom: 16px;">
        <button class="btn btn-primary" onclick="openModal('addScheduleModal')"><i class="fas fa-plus"></i> Tambah Jadwal</button>
    </div>

    <!-- Schedule Table by Day -->
    <?php
    $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
    $byDay = [];
    foreach ($schedules as $s) {
        $byDay[$s['day_of_week']][] = $s;
    }
    ?>

    <?php if (empty($schedules)): ?>
        <div class="card">
            <div class="empty-state">
                <i class="fas fa-calendar-alt"></i>
                <h3>Belum Ada Jadwal</h3>
                <p>Klik tombol "Tambah Jadwal" untuk menambahkan jadwal pelajaran.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="schedule-days">
            <?php foreach ($days as $dayNum => $dayName): ?>
                <?php if (isset($byDay[$dayNum])): ?>
                    <div class="card" style="margin-bottom: 16px;">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-calendar-day"></i> <?= $dayName ?></h3>
                            <span class="badge badge-primary"><?= count($byDay[$dayNum]) ?> jadwal</span>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Guru</th>
                                        <th>Ruangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($byDay[$dayNum] as $sc): ?>
                                        <tr>
                                            <td>
                                                <span style="font-weight: 600; color: var(--primary);">
                                                    <?= date('H:i', strtotime($sc['start_time'])) ?> - <?= date('H:i', strtotime($sc['end_time'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <div style="width: 6px; height: 24px; border-radius: 3px; background: <?= e($sc['color'] ?? '#3B49DF') ?>;"></div>
                                                    <span style="font-weight: 600; font-size: 13px;"><?= e($sc['subject_name']) ?></span>
                                                </div>
                                            </td>
                                            <td style="font-size: 13px; color: var(--text-secondary);"><?= e($sc['teacher_name']) ?></td>
                                            <td style="font-size: 13px; color: var(--text-muted);"><?= e($sc['room'] ?? '-') ?></td>
                                            <td>
                                                <form method="POST" action="<?= url('admin/schedule-delete/' . $sc['id']) ?>" style="display:inline;">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-danger" data-confirm="Hapus jadwal ini?"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Add Schedule Modal -->
    <div class="modal-overlay" id="addScheduleModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Tambah Jadwal Pelajaran</h3>
                <button class="modal-close" onclick="closeModal('addScheduleModal')"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="<?= url('admin/schedule-create') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="class_id" value="<?= e($selectedClass) ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Mata Pelajaran <span style="color:var(--danger);">*</span></label>
                        <select name="subject_id" class="form-control" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php foreach ($subjects as $sub): ?>
                                <?php if ($sub['class_id'] == $selectedClass): ?>
                                    <option value="<?= $sub['id'] ?>"><?= e($sub['name']) ?> (<?= e($sub['teacher_name']) ?>)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php
                            // Also show all subjects if none match this class
                            $classSubjects = array_filter($subjects, fn($s) => $s['class_id'] == $selectedClass);
                            if (empty($classSubjects)):
                            ?>
                                <option disabled>-- Belum ada mata pelajaran untuk kelas ini --</option>
                                <option disabled>-- Tambahkan mata pelajaran dulu di menu Mata Pelajaran --</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Hari <span style="color:var(--danger);">*</span></label>
                        <select name="day_of_week" class="form-control" required>
                            <option value="1">Senin</option>
                            <option value="2">Selasa</option>
                            <option value="3">Rabu</option>
                            <option value="4">Kamis</option>
                            <option value="5">Jumat</option>
                            <option value="6">Sabtu</option>
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-group">
                            <label>Jam Mulai <span style="color:var(--danger);">*</span></label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Jam Selesai <span style="color:var(--danger);">*</span></label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Ruangan / Lab</label>
                        <input type="text" name="room" class="form-control" placeholder="Contoh: Lab Komputer 1, Ruang 201">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addScheduleModal')">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Jadwal</button>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-hand-pointer"></i>
            <h3>Pilih Kelas</h3>
            <p>Pilih kelas di atas untuk melihat dan mengatur jadwal pelajaran.</p>
        </div>
    </div>
<?php endif; ?>

<style>
.filters-bar { display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap; }
</style>
