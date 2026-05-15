<div class="page-header">
    <div><h1><i class="fas fa-calendar-check"></i> Absensi Kelas</h1><p>Pilih kelas untuk merekam kehadiran</p></div>
</div>

<?php if (empty($classes)): ?>
    <div class="card"><div class="empty-state"><i class="fas fa-users"></i><h3>Tidak Ada Kelas</h3><p style="color:var(--text-muted);">Anda belum memiliki kelas yang di-assign.</p></div></div>
<?php else: ?>
    <div class="classes-grid">
        <?php foreach ($classes as $c): ?>
            <div class="card class-card" style="cursor:pointer;" onclick="showAttendance(<?= $c['id'] ?>, '<?= e($c['grade'] . ' - ' . $c['name']) ?>')">
                <h3 style="font-size:16px;"><?= e($c['name']) ?></h3>
                <p style="font-size:13px;color:var(--text-muted);"><?= e($c['grade']) ?> &bull; <?= e($c['major'] ?? $c['name']) ?></p>
                <small style="color:var(--primary);font-size:11px;"><?= count($classStudents[$c['id']] ?? []) ?> siswa</small>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="attendanceForm" style="margin-top: 25px; display: none;">
        <div class="card">
            <div class="card-header"><h3 class="card-title" id="attendanceTitle">Absensi</h3></div>
            <form method="POST" id="absForm">
                <?= csrf_field() ?>
                <div class="form-group" style="max-width:200px;margin-bottom:16px;">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div id="studentList"></div>
                <button type="submit" class="btn btn-primary" style="margin-top:15px;"><i class="fas fa-save"></i> Simpan Absensi</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- Embed student data as JSON (no AJAX needed) -->
<script>
var classStudentsData = <?= json_encode($classStudents ?? [], JSON_HEX_TAG | JSON_HEX_AMP) ?>;

function showAttendance(classId, className) {
    // Highlight active card
    document.querySelectorAll('.class-card').forEach(function(c) { c.classList.remove('active'); });
    event.currentTarget.classList.add('active');

    // Show form
    document.getElementById('attendanceForm').style.display = 'block';
    document.getElementById('attendanceTitle').textContent = 'Absensi: ' + className;
    document.getElementById('absForm').action = '<?= url("attendance/record/") ?>' + classId;

    // Render students from pre-loaded data
    var students = classStudentsData[classId] || [];
    if (students.length === 0) {
        document.getElementById('studentList').innerHTML = '<p style="color:var(--text-muted);font-size:13px;padding:16px 0;"><i class="fas fa-info-circle"></i> Tidak ada siswa di kelas ini. Tambahkan siswa terlebih dahulu dari menu Admin.</p>';
        return;
    }

    var html = '<div class="table-responsive"><table><thead><tr><th style="width:40%">Siswa</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpha</th></tr></thead><tbody>';
    students.forEach(function(s, idx) {
        html += '<tr style="animation: fadeIn 0.2s ease ' + (idx * 0.03) + 's forwards; opacity:0;">';
        html += '<td><strong>' + escapeHtml(s.full_name) + '</strong>';
        if (s.nis) html += ' <small style="color:var(--text-muted);">(' + escapeHtml(s.nis) + ')</small>';
        html += '</td>';
        ['hadir','izin','sakit','alpha'].forEach(function(st) {
            var checked = (st === 'hadir') ? ' checked' : '';
            html += '<td style="text-align:center;"><input type="radio" name="status[' + s.id + ']" value="' + st + '"' + checked + '></td>';
        });
        html += '</tr>';
    });
    html += '</tbody></table></div>';
    html += '<p style="margin-top:10px;font-size:12px;color:var(--text-muted);">Total: ' + students.length + ' siswa</p>';
    document.getElementById('studentList').innerHTML = html;

    // Scroll to form
    document.getElementById('attendanceForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
</script>

<style>
.classes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
.class-card { transition: all 0.2s; border: 2px solid transparent; }
.class-card:hover { border-color: var(--primary); transform: translateY(-2px); }
.class-card.active { border-color: var(--primary); background: var(--primary-bg); }
.table-responsive { overflow-x: auto; }
#studentList table { width: 100%; border-collapse: collapse; }
#studentList th, #studentList td { padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--border-light); font-size: 13px; }
#studentList th { font-weight: 600; color: var(--text-secondary); background: var(--bg-hover); }
#studentList td { color: var(--text-primary); }
#studentList input[type="radio"] { accent-color: var(--primary); width: 16px; height: 16px; cursor: pointer; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>
