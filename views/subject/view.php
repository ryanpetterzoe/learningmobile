<div class="page-header">
    <div>
        <h1><?= e($subject['name']) ?></h1>
        <p><?= e($subject['class_name']) ?> • Guru: <?= e($subject['teacher_name']) ?></p>
    </div>
    <a href="<?= url('classes/view/' . $subject['class_id']) ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<!-- Tabs -->
<div class="tab-nav" style="margin-bottom: 20px;">
    <button class="tab-btn active" data-tab="materials">📚 Materi (<?= count($materials) ?>)</button>
    <button class="tab-btn" data-tab="tasks">📝 Tugas (<?= count($assignments) ?>)</button>
    <button class="tab-btn" data-tab="quizzes">🧠 Quiz (<?= count($quizzes) ?>)</button>
</div>

<!-- Materials Tab -->
<div class="tab-content active" id="tab-materials">
    <?php if ($isTeacher): ?>
        <button class="btn btn-primary" onclick="openModal('addMaterialModal')" style="margin-bottom: 16px;"><i class="fas fa-plus"></i> Tambah Materi</button>
    <?php endif; ?>

    <?php if (empty($materials)): ?>
        <div class="card"><div class="empty-state" style="padding:30px;"><i class="fas fa-book-open"></i><p>Belum ada materi.</p></div></div>
    <?php else: ?>
        <?php foreach ($materials as $m): ?>
            <div class="material-card">
                <div class="mat-icon">
                    <?php if ($m['type'] === 'video'): ?><i class="fas fa-play-circle" style="color:#ef4444;"></i>
                    <?php elseif ($m['type'] === 'file'): ?><i class="fas fa-file-alt" style="color:#f59e0b;"></i>
                    <?php else: ?><i class="fas fa-book" style="color:var(--primary);"></i><?php endif; ?>
                </div>
                <div class="mat-content">
                    <h4><?= e($m['title']) ?></h4>
                    <?php if ($m['content']): ?><p><?= e(truncate($m['content'], 120)) ?></p><?php endif; ?>
                    <?php if ($m['video_url']): ?><a href="<?= e($m['video_url']) ?>" target="_blank" class="mat-link"><i class="fas fa-external-link-alt"></i> Tonton Video</a><?php endif; ?>
                    <?php if ($m['file_path']): ?><a href="<?= upload_url($m['file_path']) ?>" target="_blank" class="mat-link"><i class="fas fa-download"></i> Download File</a><?php endif; ?>
                </div>
                <?php if ($isTeacher): ?>
                    <form method="POST" action="<?= url('subject/delete-material/' . $m['id']) ?>" style="display:inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-danger" data-confirm="Hapus materi ini?"><i class="fas fa-trash"></i></button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Tasks Tab -->
<div class="tab-content" id="tab-tasks">
    <?php if (empty($assignments)): ?>
        <div class="card"><div class="empty-state" style="padding:30px;"><p>Belum ada tugas.</p></div></div>
    <?php else: ?>
        <?php foreach ($assignments as $a): ?>
            <a href="<?= url('assignments/view/' . $a['id']) ?>" class="material-card" style="text-decoration:none;">
                <div class="mat-icon"><i class="fas fa-clipboard-list" style="color:var(--primary);"></i></div>
                <div class="mat-content">
                    <h4><?= e($a['title']) ?></h4>
                    <p>Deadline: <?= format_datetime($a['deadline']) ?></p>
                </div>
                <i class="fas fa-chevron-right" style="color:var(--text-muted);"></i>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Quizzes Tab -->
<div class="tab-content" id="tab-quizzes">
    <?php if (empty($quizzes)): ?>
        <div class="card"><div class="empty-state" style="padding:30px;"><p>Belum ada quiz.</p></div></div>
    <?php else: ?>
        <?php foreach ($quizzes as $q): ?>
            <div class="material-card">
                <div class="mat-icon"><i class="fas fa-brain" style="color:#8b5cf6;"></i></div>
                <div class="mat-content">
                    <h4><?= e($q['title']) ?></h4>
                    <p><?= $q['duration_minutes'] ?> menit • <?= ucfirst($q['status']) ?></p>
                </div>
                <?php if ($isTeacher): ?>
                    <a href="<?= url('quiz/questions/' . $q['id']) ?>" class="btn btn-sm btn-secondary"><i class="fas fa-cog"></i></a>
                <?php else: ?>
                    <a href="<?= url('quiz/start/' . $q['id']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-play"></i></a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add Material Modal -->
<div class="modal-overlay" id="addMaterialModal">
    <div class="modal" style="max-width:600px;">
        <div class="modal-header"><h3>Tambah Materi</h3><button class="modal-close" onclick="closeModal('addMaterialModal')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="<?= url('subject/add-material/' . $subject['id']) ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group"><label>Judul</label><input type="text" name="title" class="form-control" required></div>
                <div class="form-group"><label>Tipe</label>
                    <select name="type" class="form-control"><option value="text">Teks/Artikel</option><option value="video">Video</option><option value="file">File Download</option></select>
                </div>
                <div class="form-group"><label>Konten</label><textarea name="content" class="form-control" rows="5" placeholder="Tulis materi atau deskripsi..."></textarea></div>
                <div class="form-group"><label>URL Video (YouTube, dll)</label><input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/..."></div>
                <div class="form-group"><label>Upload File</label><input type="file" name="file" class="form-control"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addMaterialModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah</button>
            </div>
        </form>
    </div>
</div>

<style>
.tab-nav { display: flex; gap: 4px; border-bottom: 2px solid var(--border); }
.tab-btn { padding: 10px 16px; border: none; background: none; font-size: 13px; font-weight: 600; color: var(--text-secondary); cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: var(--transition); font-family: inherit; }
.tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
.tab-content { display: none; animation: fadeIn 0.3s ease; }
.tab-content.active { display: block; }

.material-card { display: flex; align-items: center; gap: 14px; padding: 16px; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); margin-bottom: 10px; transition: var(--transition); }
.material-card:hover { border-color: var(--primary); transform: translateX(3px); }
.mat-icon { width: 40px; height: 40px; background: var(--bg-hover); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.mat-content { flex: 1; }
.mat-content h4 { font-size: 14px; color: var(--text-primary); margin-bottom: 4px; }
.mat-content p { font-size: 12px; color: var(--text-muted); }
.mat-link { font-size: 12px; color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px; }
</style>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});
</script>
