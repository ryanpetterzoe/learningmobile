<div class="page-header">
    <div>
        <h1><i class="fas fa-clipboard-check"></i> Review Jawaban</h1>
        <p><?= e($quiz['title']) ?> • <?= e($attempt['full_name']) ?> <?php if($attempt['nis']): ?>(<?= e($attempt['nis']) ?>)<?php endif; ?></p>
    </div>
    <a href="<?= url('quiz/results/' . $quiz['id']) ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card" style="margin-bottom:16px;padding:16px;">
    <div style="display:flex;gap:20px;align-items:center;flex-wrap:wrap;">
        <div><strong>Skor saat ini:</strong> <span style="font-size:20px;font-weight:800;color:<?= $attempt['score'] >= $quiz['passing_score'] ? '#10b981' : '#ef4444' ?>;"><?= number_format($attempt['score'], 1) ?>%</span></div>
        <div><strong>Status:</strong> <?= $attempt['score'] >= $quiz['passing_score'] ? '<span class="badge badge-success">Lulus</span>' : '<span class="badge badge-danger">Tidak Lulus</span>' ?></div>
        <div><strong>Waktu:</strong> <?= format_datetime($attempt['started_at']) ?> - <?= format_datetime($attempt['finished_at']) ?></div>
    </div>
</div>

<?php
$hasEssay = false;
foreach ($questions as $q) { if ($q['type'] === 'essay') { $hasEssay = true; break; } }
?>

<?php if ($hasEssay): ?>
<form method="POST" action="<?= url('quiz/review-attempt/' . $attempt['id']) ?>">
    <?= csrf_field() ?>
<?php endif; ?>

<div style="display:flex;flex-direction:column;gap:16px;">
    <?php $letters = ['A','B','C','D','E']; ?>
    <?php foreach ($questions as $idx => $q): ?>
        <?php $userAnswer = $studentAnswers[$q['id']] ?? ''; ?>
        <div class="card" style="padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <span style="font-size:12px;font-weight:700;color:var(--primary);">Soal <?= $idx + 1 ?> • <?= $q['points'] ?> poin • <?= ucfirst(str_replace('_', ' ', $q['type'])) ?></span>
                <?php if ($q['type'] !== 'essay'): ?>
                    <?php if ($userAnswer === $q['correct_answer']): ?>
                        <span class="badge badge-success"><i class="fas fa-check"></i> Benar</span>
                    <?php else: ?>
                        <span class="badge badge-danger"><i class="fas fa-times"></i> Salah</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <p style="font-size:14px;color:var(--text-primary);margin-bottom:12px;line-height:1.6;"><?= nl2br(e($q['question'])) ?></p>

            <?php if ($q['type'] === 'multiple_choice' || $q['type'] === 'true_false'): ?>
                <?php $opts = json_decode($q['options'], true) ?: []; ?>
                <div style="display:flex;flex-direction:column;gap:6px;">
                    <?php foreach ($opts as $oi => $opt): ?>
                        <?php
                        $isCorrect = ((string)$oi === (string)$q['correct_answer']);
                        $isSelected = ((string)$oi === (string)$userAnswer);
                        $bg = '';
                        if ($isCorrect) $bg = 'background:#d1fae5;border-color:#10b981;';
                        elseif ($isSelected && !$isCorrect) $bg = 'background:#fee2e2;border-color:#ef4444;';
                        ?>
                        <div style="padding:8px 12px;border:2px solid var(--border);border-radius:8px;font-size:13px;<?= $bg ?>">
                            <strong><?= $letters[$oi] ?? ($oi+1) ?>.</strong> <?= e($opt) ?>
                            <?php if ($isCorrect): ?> <i class="fas fa-check" style="color:#10b981;margin-left:6px;"></i><?php endif; ?>
                            <?php if ($isSelected && !$isCorrect): ?> <i class="fas fa-times" style="color:#ef4444;margin-left:6px;"></i><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($q['type'] === 'essay'): ?>
                <div style="background:var(--bg-hover);padding:12px 16px;border-radius:8px;margin-bottom:12px;">
                    <label style="font-size:11px;font-weight:700;color:var(--text-muted);display:block;margin-bottom:4px;">Jawaban Siswa:</label>
                    <p style="font-size:13px;color:var(--text-secondary);white-space:pre-wrap;"><?= e($userAnswer ?: '(tidak dijawab)') ?></p>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-size:12px;font-weight:600;">Nilai Essay (0 - <?= $q['points'] ?>):</label>
                    <input type="number" name="essay_score[<?= $q['id'] ?>]" class="form-control" style="max-width:120px;" min="0" max="<?= $q['points'] ?>" step="0.5" value="0" required>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($hasEssay): ?>
    <div style="margin-top:20px;display:flex;gap:12px;align-items:center;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Nilai Essay</button>
        <span style="font-size:12px;color:var(--text-muted);">Nilai akan dihitung ulang berdasarkan skor essay yang diberikan.</span>
    </div>
</form>
<?php endif; ?>
