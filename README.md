# SimpleEdu LMS

**Platform Learning Management System (LMS) berbasis web** untuk sekolah menengah kejuruan (SMK) dengan desain mobile-first modern.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white)
![Mobile First](https://img.shields.io/badge/Mobile-First-10b981?style=flat-square)

---

## Fitur Utama

### Akademik
- **Kelas & Mata Pelajaran** — Kelola kelas, mapel, dan jadwal
- **Tugas (Assignments)** — Buat, kumpulkan, dan nilai tugas siswa
- **Quiz & CBT** — Ujian berbasis komputer dengan timer dan penilaian otomatis
- **Kehadiran (Attendance)** — Rekap dan kelola absensi siswa
- **Nilai (Grades)** — Sistem penilaian lengkap per mapel
- **Jadwal (Schedule)** — Jadwal pelajaran harian

### SMK / Vokasi
- **PKL / Magang** — Jurnal harian, cetak surat, dan manajemen magang
- **Portofolio** — Upload dan kelola karya siswa
- **Sertifikat** — Sertifikat digital untuk pencapaian
- **Kompetensi Keahlian** — Kelola jurusan dan kompetensi

### Komunitas
- **Forum Diskusi** — Forum dengan kategori, post, dan nested replies
- **Pengumuman** — Broadcast informasi ke seluruh pengguna

### Gamification
- **XP & Level** — Sistem poin pengalaman dan leveling
- **Badge** — Lencana pencapaian
- **Leaderboard** — Ranking siswa berdasarkan XP

### UI/UX
- **Mobile-First Design** — Dioptimalkan untuk smartphone
- **Dark & Light Mode** — Tema gelap/terang yang bisa di-switch
- **Bottom Navigation** — Navigasi utama di bawah layar (mobile style)
- **Menu Grid** — Akses cepat fitur dengan ikon berwarna-warni
- **Responsive** — Bekerja di semua ukuran layar

---

## Screenshot

| Light Mode | Dark Mode |
|:---:|:---:|
| Menu grid + bottom nav | Tema gelap otomatis |

---

## Teknologi

| Komponen | Teknologi |
|----------|-----------|
| Backend | PHP (Native, tanpa framework) |
| Frontend | HTML5, CSS3 (Custom), JavaScript (Vanilla) |
| Database | MySQL / MariaDB |
| Font | Plus Jakarta Sans (Google Fonts) |
| Icons | Font Awesome 6 |
| Architecture | MVC-like (Controllers, Views, Core) |

---

## Instalasi

### Prasyarat
- PHP >= 7.4
- MySQL / MariaDB >= 5.7
- Web Server (Apache/Nginx)

### Langkah Instalasi

1. **Clone repository**
   ```bash
   git clone https://github.com/ryanpetterzoe/learningmobile.git
   cd learningmobile
   ```

2. **Setup database**
   - Buat database baru di MySQL
   - Import schema dari `install/schema.sql`
   ```bash
   mysql -u root -p nama_database < install/schema.sql
   ```

3. **Konfigurasi**
   - Akses `/install` melalui browser untuk setup awal
   - Atau konfigurasi manual di file config

4. **Jalankan**
   - Arahkan web server ke folder project
   - Akses melalui browser

### Migrasi Database
Jika upgrade dari versi sebelumnya, jalankan file migrasi secara berurutan:
```bash
mysql -u root -p nama_database < install/migrate_v2.sql
mysql -u root -p nama_database < install/migrate_v3_nested_replies.sql
mysql -u root -p nama_database < install/migrate_v4_biodata.sql
```

---

## Struktur Folder

```
learningmobile/
├── assets/
│   ├── css/          # Stylesheet (mobile-first)
│   ├── img/          # Gambar dan aset
│   └── js/           # JavaScript (vanilla)
├── controllers/      # Logic per fitur
├── core/             # Framework core (Auth, DB, Router, dll)
├── install/          # Schema dan migrasi database
├── views/
│   ├── admin/        # Halaman admin panel
│   ├── assignments/  # Halaman tugas
│   ├── attendance/   # Halaman kehadiran
│   ├── auth/         # Login & register
│   ├── classes/      # Halaman kelas
│   ├── dashboard/    # Halaman utama
│   ├── forum/        # Forum diskusi
│   ├── gamification/ # Badge & leaderboard
│   ├── grades/       # Halaman nilai
│   ├── layouts/      # Template layout
│   ├── pkl/          # PKL / magang
│   └── ...
└── index.php         # Entry point
```

---

## Role Pengguna

| Role | Akses |
|------|-------|
| **Admin** | Full access — kelola user, mapel, kelas, jadwal, settings |
| **Guru** | Buat tugas, quiz, kelola nilai, kehadiran |
| **Wali Kelas** | Sama seperti guru + akses kelola kelas |
| **Siswa** | Kerjakan tugas, quiz, lihat nilai, forum, PKL |

---

## Dark Mode

Aplikasi mendukung tema gelap dan terang. Pengguna dapat switch tema melalui:
- Tombol toggle di header (ikon bulan/matahari)
- Preferensi disimpan di `localStorage` dan di server

---

## Kontribusi

1. Fork repository ini
2. Buat branch fitur (`git checkout -b fitur-baru`)
3. Commit perubahan (`git commit -m 'Tambah fitur baru'`)
4. Push ke branch (`git push origin fitur-baru`)
5. Buat Pull Request

---

## Lisensi

Proyek ini untuk keperluan pembelajaran dan pengembangan internal.

---

## Kontak

Dibuat dengan ❤️ untuk pendidikan Indonesia.
