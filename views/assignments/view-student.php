<?php $isPast = strtotime($assignment['deadline']) < time(); ?>

<div class="page-header">
    <div>
        <h1><?= e($assignment['title']) ?></h1>
        <p><span class="badge badge-primary"><?= e($assignment['subject_name']) ?></span> &bull; <?= e($assignment['class_name']) ?></p>
    </div>
    <a href="<?= url('assignments') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="grid-2">
    <!-- Left: Assignment Details -->
    <div>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3 class="card-title">Detail Tugas</h3>
                <?php if (!$isPast): ?>
                    <div class="deadline-badge">
                        <i class="fas fa-clock"></i>
                        <span data-countdown="<?= $assignment['deadline'] ?>"></span>
                    </div>
                <?php else: ?>
                    <span class="badge badge-danger">Deadline Terlewat</span>
                <?php endif; ?>
            </div>
            <div class="assignment-desc">
                <?= nl2br(e($assignment['description'])) ?>
            </div>
            <div class="assignment-meta-grid">
                <div class="meta-item">
                    <span class="meta-label">Deadline</span>
                    <span class="meta-value"><?= format_datetime($assignment['deadline']) ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Nilai Maks</span>
                    <span class="meta-value"><?= $assignment['max_score'] ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Tipe</span>
                    <span class="meta-value"><?= ucfirst($assignment['type']) ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Revisi</span>
                    <span class="meta-value"><?= $assignment['allow_revision'] ? 'Diizinkan' : 'Tidak' ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Submission -->
    <div>
        <!-- Submission Status -->
        <?php if ($mySubmission): ?>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <h3 class="card-title">Pengumpulan Saya</h3>
                    <?php if ($mySubmission['status'] === 'graded'): ?>
                        <span class="badge badge-success">Dinilai</span>
                    <?php else: ?>
                        <span class="badge badge-primary">Dikumpulkan</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($mySubmission['content']): ?>
                    <div class="submission-content"><?= nl2br(e($mySubmission['content'])) ?></div>
                <?php endif; ?>

                <?php
                // Support multiple files (pipe-separated)
                $submittedFiles = [];
                if ($mySubmission['file_path']) {
                    $paths = explode('|', $mySubmission['file_path']);
                    $names = $mySubmission['file_name'] ? explode('|', $mySubmission['file_name']) : $paths;
                    for ($i = 0; $i < count($paths); $i++) {
                        $submittedFiles[] = ['path' => $paths[$i], 'name' => $names[$i] ?? basename($paths[$i])];
                    }
                }
                ?>

                <?php if (!empty($submittedFiles)): ?>
                    <div class="submission-files">
                        <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:8px;display:block;">File yang dikumpulkan:</label>
                        <?php foreach ($submittedFiles as $idx => $sf): ?>
                            <div class="submission-file-item">
                                <i class="fas fa-file"></i>
                                <a href="<?= upload_url($sf['path']) ?>" target="_blank"><?= e($sf['name']) ?></a>
                                <?php if (!$isPast && $mySubmission['status'] !== 'graded'): ?>
                                    <form method="POST" action="<?= url('assignments/delete-file/' . $mySubmission['id']) ?>" style="display:inline;margin-left:auto;" onsubmit="return confirm('Hapus file ini?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="file_index" value="<?= $idx ?>">
                                        <button type="submit" class="file-delete-btn" title="Hapus file"><i class="fas fa-times"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="submission-meta">
                    <span>Dikumpulkan: <?= format_datetime($mySubmission['submitted_at']) ?></span>
                    <?php if ($mySubmission['revision_count'] > 0): ?>
                        <span>Revisi ke-<?= $mySubmission['revision_count'] ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($mySubmission['status'] === 'graded'): ?>
                    <div class="grade-result">
                        <div class="grade-score-big"><?= $mySubmission['score'] ?><small>/<?= $assignment['max_score'] ?></small></div>
                        <?php if ($mySubmission['feedback']): ?>
                            <div class="grade-feedback">
                                <strong>Feedback:</strong>
                                <p><?= nl2br(e($mySubmission['feedback'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Submit Form -->
        <?php
        $canSubmit = false;
        if (!$isPast || $assignment['allow_late']) {
            if (!$mySubmission || $assignment['allow_revision']) {
                $canSubmit = true;
            }
        }
        // Allow editing before deadline even if already submitted
        if (!$isPast && $mySubmission && $mySubmission['status'] !== 'graded') {
            $canSubmit = true;
        }
        ?>
        <?php if ($canSubmit): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= $mySubmission ? 'Sunting / Revisi Tugas' : 'Kumpulkan Tugas' ?></h3>
                </div>
                <form method="POST" action="<?= url('assignments/submit/' . $assignment['id']) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="form-group">
                        <label>Catatan / Jawaban Teks</label>
                        <textarea name="content" class="form-control" rows="4" placeholder="Tulis catatan, jawaban, atau keterangan tambahan..."><?= e($mySubmission['content'] ?? '') ?></textarea>
                        <small style="color:var(--text-muted);font-size:11px;">Opsional. Bisa diisi catatan untuk guru atau jawaban teks.</small>
                    </div>

                    <?php if ($assignment['type'] !== 'text'): ?>
                        <div class="form-group">
                            <label>Upload File <small style="color:var(--text-muted);">(bisa pilih lebih dari satu)</small></label>
                            <div class="drop-zone" id="dropZone">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 32px; color: var(--primary); margin-bottom: 10px;"></i>
                                <p>Drag & drop file di sini atau <strong>klik untuk pilih</strong></p>
                                <small>PDF, DOC, DOCX, JPG, PNG, PPT, XLS, ZIP (Max 50MB per file)</small>
                                <input type="file" name="files[]" id="fileInput" style="display: none;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.ppt,.pptx,.xls,.xlsx,.zip,.rar" multiple>
                            </div>
                            <div id="filePreview" class="file-preview-list"></div>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> <?= $mySubmission ? 'Kirim Revisi' : 'Kumpulkan' ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.assignment-desc { font-size: 14px; color: var(--text-secondary); line-height: 1.7; margin-bottom: 20px; white-space: pre-wrap; }
.assignment-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.meta-item { background: var(--bg-hover); padding: 12px; border-radius: var(--radius-sm); }
.meta-label { display: block; font-size: 11px; color: var(--text-muted); margin-bottom: 4px; }
.meta-value { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.deadline-badge { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: var(--warning); }

.submission-content { font-size: 13px; color: var(--text-secondary); padding: 12px; background: var(--bg-hover); border-radius: var(--radius-sm); margin-bottom: 12px; line-height: 1.6; }
.submission-files { margin-bottom: 12px; }
.submission-file-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: var(--bg-hover); border-radius: var(--radius-sm); margin-bottom: 6px; }
.submission-file-item i { color: var(--primary); }
.submission-file-item a { font-size: 13px; color: var(--primary); text-decoration: none; font-weight: 500; flex: 1; word-break: break-all; }
.submission-file-item a:hover { text-decoration: underline; }
.file-delete-btn { background: none; border: none; color: #ef4444; cursor: pointer; font-size: 14px; padding: 4px 8px; border-radius: 4px; transition: background 0.2s; }
.file-delete-btn:hover { background: #fef2f2; }
.submission-meta { display: flex; gap: 16px; font-size: 11px; color: var(--text-muted); margin-bottom: 16px; }

.grade-result { background: var(--bg-hover); padding: 16px; border-radius: var(--radius-md); margin-top: 12px; }
.grade-score-big { font-size: 32px; font-weight: 800; color: var(--primary); }
.grade-score-big small { font-size: 16px; color: var(--text-muted); }
.grade-feedback { margin-top: 10px; font-size: 13px; }
.grade-feedback p { color: var(--text-secondary); margin-top: 4px; }

.drop-zone {
    border: 2px dashed var(--border);
    border-radius: var(--radius-md);
    padding: 30px;
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
}
.drop-zone:hover, .drop-zone.drag-over {
    border-color: var(--primary);
    background: var(--primary-bg);
}
.drop-zone p { font-size: 13px; color: var(--text-secondary); margin-bottom: 6px; }
.drop-zone small { font-size: 11px; color: var(--text-muted); }

/* File preview list */
.file-preview-list { margin-top: 10px; }
.file-preview-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: var(--bg-hover); border-radius: 8px; margin-bottom: 6px; font-size: 13px; }
.file-preview-item .file-icon { color: var(--primary); font-size: 16px; }
.file-preview-item .file-name { flex: 1; color: var(--text-primary); font-weight: 500; word-break: break-all; }
.file-preview-item .file-size { color: var(--text-muted); font-size: 11px; }
.file-preview-item .file-remove { background: none; border: none; color: #ef4444; cursor: pointer; font-size: 14px; padding: 2px 6px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dropZone = document.getElementById('dropZone');
    var fileInput = document.getElementById('fileInput');
    var filePreview = document.getElementById('filePreview');
    
    if (!dropZone || !fileInput) return;

    // Click to select
    dropZone.addEventListener('click', function() {
        fileInput.click();
    });

    // Drag and drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    dropZone.addEventListener('dragleave', function() {
        dropZone.classList.remove('drag-over');
    });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            showFileNames();
        }
    });

    // File selected
    fileInput.addEventListener('change', function() {
        showFileNames();
    });

    function showFileNames() {
        var files = fileInput.files;
        if (!files.length) {
            filePreview.innerHTML = '';
            return;
        }
        var html = '';
        for (var i = 0; i < files.length; i++) {
            var f = files[i];
            var size = f.size < 1048576 ? (f.size / 1024).toFixed(1) + ' KB' : (f.size / 1048576).toFixed(1) + ' MB';
            var ext = f.name.split('.').pop().toLowerCase();
            var icon = 'fa-file';
            if (['pdf'].includes(ext)) icon = 'fa-file-pdf';
            else if (['doc','docx'].includes(ext)) icon = 'fa-file-word';
            else if (['jpg','jpeg','png','gif'].includes(ext)) icon = 'fa-file-image';
            else if (['ppt','pptx'].includes(ext)) icon = 'fa-file-powerpoint';
            else if (['xls','xlsx'].includes(ext)) icon = 'fa-file-excel';
            else if (['zip','rar'].includes(ext)) icon = 'fa-file-archive';
            
            html += '<div class="file-preview-item">';
            html += '<i class="fas ' + icon + ' file-icon"></i>';
            html += '<span class="file-name">' + escapeHtml(f.name) + '</span>';
            html += '<span class="file-size">' + size + '</span>';
            html += '</div>';
        }
        filePreview.innerHTML = html;
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }
});
</script>
