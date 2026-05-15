<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f4ff; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .container { text-align: center; padding: 40px; }
        h1 { font-size: 120px; color: #3B49DF; font-weight: 800; }
        h2 { font-size: 24px; color: #1e293b; margin: 10px 0; }
        p { color: #64748b; margin-bottom: 30px; }
        a { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #3B49DF, #6366f1); color: #fff; text-decoration: none; border-radius: 12px; font-weight: 600; transition: all 0.3s; }
        a:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(59,73,223,0.4); }
    </style>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <h2>Halaman Tidak Ditemukan</h2>
        <p>Maaf, halaman yang Anda cari tidak tersedia.</p>
        <a href="<?= BASE_URL ?>/dashboard">Kembali ke Dashboard</a>
    </div>
</body>
</html>
