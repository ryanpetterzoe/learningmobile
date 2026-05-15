<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($quiz['title']) ?> - CBT</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f4ff; min-height: 100vh; }
        
        .cbt-header {
            background: linear-gradient(135deg, #3B49DF, #6366f1);
            color: #fff; padding: 15px 25px; display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
        }
        .cbt-title { font-size: 16px; font-weight: 700; }
        .cbt-timer { font-size: 20px; font-weight: 800; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 10px; }
        .cbt-timer.urgent { background: #ef4444; animation: pulse 1s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

        .cbt-body { max-width: 800px; margin: 30px auto; padding: 0 20px; }
        
        .question-card {
            background: #fff; border-radius: 16px; padding: 30px; margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;
        }
        .q-number { font-size: 12px; color: #3B49DF; font-weight: 700; margin-bottom: 8px; }
        .q-text { font-size: 15px; color: #1e293b; line-height: 1.7; margin-bottom: 20px; }
        
        .option-list { display: flex; flex-direction: column; gap: 10px; }
        .option-label {
            display: flex; align-items: center; gap: 12px; padding: 14px 16px;
            border: 2px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: all 0.2s;
            font-size: 14px; color: #374151;
        }
        .option-label:hover { border-color: #3B49DF; background: rgba(59,73,223,0.04); }
        .option-label input:checked + .option-letter { background: #3B49DF; color: #fff; border-color: #3B49DF; }
        .option-label:has(input:checked) { border-color: #3B49DF; background: rgba(59,73,223,0.06); }
        .option-label input { display: none; }
        .option-letter {
            width: 28px; height: 28px; border-radius: 50%; border: 2px solid #e2e8f0;
            display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;
            flex-shrink: 0; transition: all 0.2s;
        }

        .essay-textarea { width: 100%; padding: 14px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 14px; font-family: inherit; resize: vertical; min-height: 120px; outline: none; }
        .essay-textarea:focus { border-color: #3B49DF; }

        .cbt-footer {
            max-width: 800px; margin: 0 auto 40px; padding: 0 20px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .btn-submit {
            padding: 14px 30px; background: linear-gradient(135deg, #10b981, #059669); color: #fff;
            border: none; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer;
            transition: all 0.3s; font-family: inherit;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(16,185,129,0.4); }
        
        .question-nav {
            display: flex; flex-wrap: wrap; gap: 6px; max-width: 800px; margin: 0 auto 20px; padding: 0 20px;
        }
        .q-nav-btn {
            width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600;
            cursor: pointer; transition: all 0.2s; color: #64748b;
        }
        .q-nav-btn.answered { background: #3B49DF; color: #fff; border-color: #3B49DF; }
        .q-nav-btn:hover { border-color: #3B49DF; }

        /* Anti-cheat styles */
        .cbt-warning { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 9999; align-items: center; justify-content: center; color: #fff; text-align: center; }
        .cbt-warning.show { display: flex; }
        .cbt-warning h2 { font-size: 24px; margin-bottom: 10px; }
        .cbt-warning p { opacity: 0.8; }
    </style>
</head>
<body>
    <!-- Anti-cheat warning -->
    <div class="cbt-warning" id="antiCheat">
        <div>
            <h2>⚠️ Peringatan!</h2>
            <p>Jangan meninggalkan halaman ujian.<br>Klik di sini untuk melanjutkan.</p>
        </div>
    </div>

    <div class="cbt-header">
        <div>
            <span class="cbt-title"><?= e($quiz['title']) ?></span>
        </div>
        <div class="cbt-timer" id="timer">--:--</div>
    </div>

    <!-- Question Navigation -->
    <div class="question-nav" id="qNav">
        <?php foreach ($questions as $idx => $q): ?>
            <button type="button" class="q-nav-btn" data-q="<?= $idx ?>" onclick="scrollToQ(<?= $idx ?>)"><?= $idx + 1 ?></button>
        <?php endforeach; ?>
    </div>

    <form method="POST" action="<?= url('quiz/submit-quiz/' . $attempt['id']) ?>" id="quizForm">
        <?= csrf_field() ?>
        <div class="cbt-body">
            <?php $letters = ['A','B','C','D','E']; ?>
            <?php foreach ($questions as $idx => $q): ?>
                <div class="question-card" id="question-<?= $idx ?>">
                    <div class="q-number">Soal <?= $idx + 1 ?> dari <?= count($questions) ?> • <?= $q['points'] ?> poin</div>
                    <div class="q-text"><?= nl2br(e($q['question'])) ?></div>

                    <?php if ($q['type'] === 'multiple_choice' || $q['type'] === 'true_false'): ?>
                        <div class="option-list">
                            <?php $opts = json_decode($q['options'], true) ?: []; ?>
                            <?php foreach ($opts as $oi => $opt): ?>
                                <label class="option-label">
                                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $oi ?>" onchange="markAnswered(<?= $idx ?>)">
                                    <span class="option-letter"><?= $letters[$oi] ?? ($oi+1) ?></span>
                                    <span><?= e($opt) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($q['type'] === 'essay'): ?>
                        <textarea class="essay-textarea" name="answers[<?= $q['id'] ?>]" placeholder="Tulis jawaban Anda..." onkeyup="markAnswered(<?= $idx ?>)"></textarea>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cbt-footer">
            <span style="font-size: 13px; color: #64748b;">
                <span id="answeredCount">0</span>/<?= count($questions) ?> soal dijawab
            </span>
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Kumpulkan Jawaban
            </button>
        </div>
    </form>

    <script>
        // === State ===
        let isSubmitting = false;

        // === Timer ===
        const duration = <?= $quiz['duration_minutes'] ?> * 60;
        const startTime = new Date('<?= $attempt['started_at'] ?>').getTime();
        const endTime = startTime + (duration * 1000);

        function updateTimer() {
            const now = Date.now();
            const remaining = Math.max(0, Math.floor((endTime - now) / 1000));
            const mins = Math.floor(remaining / 60);
            const secs = remaining % 60;
            const timerEl = document.getElementById('timer');
            timerEl.textContent = `${mins.toString().padStart(2,'0')}:${secs.toString().padStart(2,'0')}`;
            
            if (remaining <= 300) timerEl.classList.add('urgent');
            if (remaining <= 0) {
                isSubmitting = true;
                document.getElementById('quizForm').submit();
            }
        }
        updateTimer();
        setInterval(updateTimer, 1000);

        // === Mark answered ===
        function markAnswered(idx) {
            document.querySelectorAll('.q-nav-btn')[idx].classList.add('answered');
            updateCount();
            saveToLocal();
        }

        function updateCount() {
            const answered = document.querySelectorAll('.q-nav-btn.answered').length;
            document.getElementById('answeredCount').textContent = answered;
        }

        function scrollToQ(idx) {
            document.getElementById('question-' + idx).scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // === Auto-save to localStorage ===
        const storageKey = 'quiz_<?= $attempt['id'] ?>';

        function saveToLocal() {
            const formData = new FormData(document.getElementById('quizForm'));
            const answers = {};
            for (const [key, value] of formData.entries()) {
                if (key.startsWith('answers[')) {
                    answers[key] = value;
                }
            }
            localStorage.setItem(storageKey, JSON.stringify(answers));
        }

        function loadFromLocal() {
            const saved = localStorage.getItem(storageKey);
            if (!saved) return;
            try {
                const answers = JSON.parse(saved);
                for (const [key, value] of Object.entries(answers)) {
                    const input = document.querySelector(`[name="${key}"][value="${value}"]`);
                    if (input) { input.checked = true; }
                    const textarea = document.querySelector(`textarea[name="${key}"]`);
                    if (textarea) { textarea.value = value; }
                }
                // Update nav buttons
                document.querySelectorAll('.question-card').forEach((card, idx) => {
                    const hasAnswer = card.querySelector('input:checked') || 
                                     (card.querySelector('textarea') && card.querySelector('textarea').value.trim());
                    if (hasAnswer) {
                        document.querySelectorAll('.q-nav-btn')[idx].classList.add('answered');
                    }
                });
                updateCount();
            } catch(e) {}
        }
        loadFromLocal();

        // === Form submit handler ===
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            if (!isSubmitting) {
                const answered = document.querySelectorAll('.q-nav-btn.answered').length;
                const total = <?= count($questions) ?>;
                const msg = answered < total 
                    ? `Anda baru menjawab ${answered} dari ${total} soal.\nSoal yang tidak dijawab akan dianggap salah.\n\nYakin ingin mengumpulkan jawaban?`
                    : 'Yakin ingin mengumpulkan jawaban? Anda tidak bisa mengubahnya lagi.';
                if (!confirm(msg)) {
                    e.preventDefault();
                    return false;
                }
            }
            isSubmitting = true;
            localStorage.removeItem(storageKey);
        });

        // === Anti-cheat: detect tab/window switch ===
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                document.getElementById('antiCheat').classList.add('show');
            }
        });
        document.getElementById('antiCheat').addEventListener('click', function() {
            this.classList.remove('show');
        });

        // === Anti-cheat: prevent right-click ===
        document.addEventListener('contextmenu', e => e.preventDefault());

        // === Prevent accidental leave (but allow form submit) ===
        // Delay registration to prevent immediate trigger on page load
        setTimeout(function() {
            window.addEventListener('beforeunload', function(e) {
                if (isSubmitting) return; // Allow normal form submission
                e.preventDefault();
                e.returnValue = 'Anda sedang mengerjakan ujian. Jawaban yang belum disimpan bisa hilang.';
            });
        }, 2000); // Wait 2 seconds after page loads
    </script>
</body>
</html>
