<div class="page-header">
    <div>
        <h1><i class="fas fa-pen"></i> Buat Diskusi Baru</h1>
        <p>Mulai diskusi atau ajukan pertanyaan</p>
    </div>
    <a href="<?= url('forum') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card" style="max-width: 700px;">
    <form method="POST" action="<?= url('forum/create') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Kategori</label>
            <select name="category_id" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= $cat['icon'] ?> <?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Mapel Terkait (Opsional)</label>
            <select name="subject_id" class="form-control">
                <option value="">-- Tidak ada mapel terkait --</option>
                <?php foreach ($allSubjects as $subj): ?>
                    <option value="<?= $subj['id'] ?>"><?= e($subj['name']) ?> (<?= e($subj['class_name'] ?? '') ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Judul Diskusi</label>
            <input type="text" name="title" class="form-control" placeholder="Tulis judul yang jelas dan ringkas..." required>
        </div>
        <div class="form-group">
            <label>Isi Diskusi</label>
            <textarea name="content" class="form-control" rows="6" placeholder="Jelaskan detail pertanyaan atau topik diskusi Anda..." required></textarea>
        </div>

        <!-- Media Upload (Facebook-style) -->
        <div class="form-group">
            <label>Lampiran (Opsional)</label>
            <div class="media-upload-zone" id="mediaZone">
                <div class="media-upload-placeholder" id="mediaPlaceholder">
                    <i class="fas fa-photo-video" style="font-size:28px;color:var(--primary);margin-bottom:8px;"></i>
                    <p style="font-size:13px;color:var(--text-secondary);margin-bottom:4px;">Tambahkan foto, video, atau file</p>
                    <small style="color:var(--text-muted);font-size:11px;">JPG, PNG, GIF, WEBP, MP4, WEBM, PDF, DOC, ZIP (Max 50MB)</small>
                </div>
                <input type="file" name="attachments[]" id="mediaInput" accept="image/*,video/*,.pdf,.doc,.docx,.zip,.rar" multiple style="display:none;">
                <div id="mediaPreview" style="display:none;"></div>
            </div>
            <div class="media-actions">
                <button type="button" class="media-btn" onclick="document.getElementById('mediaInput').click();">
                    <i class="fas fa-image" style="color:#10b981;"></i> Foto
                </button>
                <button type="button" class="media-btn" onclick="document.getElementById('mediaInput').click();">
                    <i class="fas fa-video" style="color:#ef4444;"></i> Video
                </button>
                <button type="button" class="media-btn" onclick="document.getElementById('mediaInput').click();">
                    <i class="fas fa-paperclip" style="color:#3b82f6;"></i> File
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;"><i class="fas fa-paper-plane"></i> Publikasikan</button>
    </form>
</div>

<style>
.media-upload-zone {
    border: 2px dashed var(--border);
    border-radius: 12px;
    padding: 24px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--bg-hover);
    position: relative;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.media-upload-zone:hover, .media-upload-zone.drag-over {
    border-color: var(--primary);
    background: var(--primary-bg);
}
.media-upload-zone.has-file {
    border-style: solid;
    border-color: var(--primary);
    padding: 12px;
}
.media-actions {
    display: flex;
    gap: 8px;
    margin-top: 10px;
}
.media-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border: 1px solid var(--border);
    border-radius: 20px;
    background: var(--bg-card);
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}
.media-btn:hover {
    background: var(--bg-hover);
    border-color: var(--primary);
}
#mediaPreview img, #mediaPreview video {
    max-width: 100%;
    max-height: 300px;
    border-radius: 8px;
    object-fit: contain;
}
.media-preview-file {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    background: var(--bg-card);
    border-radius: 8px;
    border: 1px solid var(--border-light);
}
.media-preview-file i { font-size: 24px; color: var(--primary); }
.media-preview-file .file-info { flex: 1; }
.media-preview-file .file-name { font-size: 13px; font-weight: 600; color: var(--text-primary); display: block; word-break: break-all; }
.media-preview-file .file-size { font-size: 11px; color: var(--text-muted); }
.media-remove-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(0,0,0,0.6);
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    z-index: 2;
}
.media-remove-btn:hover { background: #ef4444; }
.multi-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 8px; width: 100%; }
.multi-preview-item { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 8px; padding: 8px; min-height: 80px; overflow: hidden; }
.multi-preview-item img { width: 100%; height: 80px; object-fit: cover; border-radius: 6px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var zone = document.getElementById('mediaZone');
    var input = document.getElementById('mediaInput');
    var preview = document.getElementById('mediaPreview');
    var placeholder = document.getElementById('mediaPlaceholder');

    zone.addEventListener('click', function(e) {
        if (e.target.closest('.media-remove-btn')) return;
        input.click();
    });

    zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', function() { zone.classList.remove('drag-over'); });
    zone.addEventListener('drop', function(e) {
        e.preventDefault();
        zone.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            showPreview();
        }
    });

    input.addEventListener('change', showPreview);

    function showPreview() {
        var files = input.files;
        if (!files || files.length === 0) return;

        placeholder.style.display = 'none';
        preview.style.display = 'block';
        zone.classList.add('has-file');

        var html = '<button type="button" class="media-remove-btn" onclick="clearMedia()"><i class="fas fa-times"></i></button>';
        html += '<div class="multi-preview-grid">';

        for (var i = 0; i < files.length; i++) {
            (function(file, index) {
                if (file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var item = document.getElementById('preview-item-' + index);
                        if (item) item.innerHTML = '<img src="' + e.target.result + '" alt="preview">';
                    };
                    reader.readAsDataURL(file);
                }
            })(files[i], i);

            if (files[i].type.startsWith('image/')) {
                html += '<div class="multi-preview-item" id="preview-item-' + i + '"><i class="fas fa-spinner fa-spin"></i></div>';
            } else if (files[i].type.startsWith('video/')) {
                html += '<div class="multi-preview-item"><i class="fas fa-video" style="font-size:24px;color:var(--primary);"></i><span style="font-size:11px;">' + escapeHtml(files[i].name) + '</span></div>';
            } else {
                var ext = files[i].name.split('.').pop().toLowerCase();
                var icon = 'fa-file';
                if (ext === 'pdf') icon = 'fa-file-pdf';
                else if (['doc','docx'].includes(ext)) icon = 'fa-file-word';
                else if (['zip','rar'].includes(ext)) icon = 'fa-file-archive';
                html += '<div class="multi-preview-item"><i class="fas ' + icon + '" style="font-size:24px;color:var(--primary);"></i><span style="font-size:11px;">' + escapeHtml(files[i].name) + '</span></div>';
            }
        }
        html += '</div>';
        html += '<p style="font-size:11px;color:var(--text-muted);margin-top:8px;text-align:center;">' + files.length + ' file dipilih</p>';
        preview.innerHTML = html;
    }

    window.clearMedia = function() {
        input.value = '';
        preview.style.display = 'none';
        preview.innerHTML = '';
        placeholder.style.display = 'flex';
        zone.classList.remove('has-file');
    };

    function escapeHtml(t) { var d = document.createElement('div'); d.appendChild(document.createTextNode(t)); return d.innerHTML; }
});
</script>
