<div class="page-header">
    <div>
        <h1><i class="fas fa-list-ol"></i> Kelola Soal: <?= e($quiz['title']) ?></h1>
        <p><?= count($questions) ?> soal terdaftar • Status: <span class="badge badge-<?= $quiz['status'] === 'active' ? 'success' : 'warning' ?>"><?= ucfirst($quiz['status']) ?></span></p>
    </div>
    <a href="<?= url('quiz') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="grid-2">
    <!-- Left: Question List -->
    <div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Daftar Soal</h3></div>
            <?php if (empty($questions)): ?>
                <div class="empty-state" style="padding:30px;"><i class="fas fa-clipboard-list"></i><p>Belum ada soal. Tambahkan di form sebelah.</p></div>
            <?php else: ?>
                <?php foreach ($questions as $idx => $q): ?>
                    <div class="question-row">
                        <span class="q-num"><?= $idx + 1 ?></span>
                        <div class="q-content">
                            <p><?= e(truncate($q['question'], 100)) ?></p>
                            <span class="badge badge-primary"><?= ucfirst(str_replace('_', ' ', $q['type'])) ?></span>
                            <span style="font-size:11px;color:var(--text-muted);"><?= $q['points'] ?> poin</span>
                        </div>
                        <form method="POST" action="<?= url('quiz/delete-question/' . $q['id']) ?>" style="display:inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger" data-confirm="Hapus soal ini?"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: Add Question Form -->
    <div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Tambah Soal</h3></div>
            <form method="POST" action="<?= url('quiz/questions/' . $quiz['id']) ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Tipe Soal</label>
                    <select name="type" id="qType" class="form-control" onchange="toggleOptions()">
                        <option value="multiple_choice">Pilihan Ganda</option>
                        <option value="true_false">Benar/Salah</option>
                        <option value="essay">Essay</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pertanyaan</label>
                    <textarea name="question" class="form-control" rows="3" required></textarea>
                </div>
                <div id="mcOptions">
                    <div class="form-group"><label>Pilihan Jawaban</label>
                        <div class="options-list">
                            <div class="option-item"><input type="radio" name="correct_answer" value="0" checked><input type="text" name="options[]" class="form-control" placeholder="Opsi A" required></div>
                            <div class="option-item"><input type="radio" name="correct_answer" value="1"><input type="text" name="options[]" class="form-control" placeholder="Opsi B" required></div>
                            <div class="option-item"><input type="radio" name="correct_answer" value="2"><input type="text" name="options[]" class="form-control" placeholder="Opsi C"></div>
                            <div class="option-item"><input type="radio" name="correct_answer" value="3"><input type="text" name="options[]" class="form-control" placeholder="Opsi D"></div>
                            <div class="option-item"><input type="radio" name="correct_answer" value="4"><input type="text" name="options[]" class="form-control" placeholder="Opsi E (opsional)"></div>
                        </div>
                        <small style="color:var(--text-muted);">Pilih radio button untuk jawaban benar</small>
                    </div>
                </div>
                <div id="tfOptions" style="display:none;">
                    <div class="form-group"><label>Jawaban Benar</label>
                        <select name="correct_tf" class="form-control"><option value="0">Benar</option><option value="1">Salah</option></select>
                    </div>
                </div>
                <div class="form-group"><label>Poin</label><input type="number" name="points" class="form-control" value="1" min="1"></div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Soal</button>
            </form>
        </div>
    </div>
</div>

<style>
.question-row { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border-light); }
.question-row:last-child { border-bottom: none; }
.q-num { width: 28px; height: 28px; background: var(--primary-bg); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }
.q-content { flex: 1; }
.q-content p { font-size: 13px; color: var(--text-primary); margin-bottom: 4px; }
.options-list { display: flex; flex-direction: column; gap: 8px; }
.option-item { display: flex; align-items: center; gap: 8px; }
.option-item input[type="radio"] { accent-color: var(--primary); }
.option-item .form-control { padding: 8px 12px; font-size: 13px; }
</style>

<script>
function toggleOptions() {
    const type = document.getElementById('qType').value;
    document.getElementById('mcOptions').style.display = type === 'multiple_choice' ? 'block' : 'none';
    document.getElementById('tfOptions').style.display = type === 'true_false' ? 'block' : 'none';
    // Remove required from hidden options
    document.querySelectorAll('#mcOptions input[type="text"]').forEach(i => i.required = type === 'multiple_choice');
}
</script>
