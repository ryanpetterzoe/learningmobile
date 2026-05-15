<div class="page-header">
    <div>
        <h1><i class="fas fa-edit"></i> Edit Diskusi</h1>
        <p>Ubah postingan Anda</p>
    </div>
    <a href="<?= url('forum/post/' . $post['id']) ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card" style="max-width: 700px;">
    <form method="POST" action="<?= url('forum/edit/' . $post['id']) ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Judul Diskusi</label>
            <input type="text" name="title" class="form-control" value="<?= e($post['title']) ?>" required>
        </div>
        <div class="form-group">
            <label>Isi Diskusi</label>
            <textarea name="content" class="form-control" rows="8" required><?= e($post['content']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
    </form>
</div>
