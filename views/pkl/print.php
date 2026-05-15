<?php
/**
 * PKL Journal Print View - Standalone (no layout wrapper)
 */
$bulanIndo = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tglCetak = $printData['tanggal'] ? date('Y-m-d', strtotime($printData['tanggal'])) : date('Y-m-d');
$tglFormatted = (int)date('d', strtotime($tglCetak)) . ' ' . $bulanIndo[(int)date('m', strtotime($tglCetak))] . ' ' . date('Y', strtotime($tglCetak));

$appName = app_setting('app_name', 'SimpleEdu');
$schoolName = app_setting('school_name', '');
$schoolAddress = app_setting('school_address', '');
$schoolContact = app_setting('school_contact', '');
$appLogo = app_setting('app_logo');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Jurnal PKL - <?= e($printData['student_name']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 12pt; color: #000; padding: 20mm 25mm; line-height: 1.6; }
        
        .header { text-align: center; margin-bottom: 24px; border-bottom: 3px double #000; padding-bottom: 16px; }
        .header-content { display: flex; align-items: center; justify-content: center; gap: 16px; }
        .header-logo { width: 70px; height: 70px; object-fit: contain; }
        .header-text { text-align: center; }
        .header h1 { font-size: 16pt; font-weight: bold; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 1px; }
        .header h2 { font-size: 14pt; font-weight: bold; margin-bottom: 4px; }
        .header p { font-size: 10pt; color: #333; }
        .header .address { font-size: 9pt; color: #444; margin-top: 2px; }

        .info-section { margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .info-table td { padding: 4px 8px; font-size: 11pt; vertical-align: top; }
        .info-table td:first-child { width: 160px; font-weight: bold; }

        .journal-title { font-size: 13pt; font-weight: bold; text-align: center; margin: 24px 0 16px; text-transform: uppercase; }

        .journal-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .journal-table th, .journal-table td { border: 1px solid #000; padding: 6px 10px; font-size: 10pt; vertical-align: top; }
        .journal-table th { background: #f0f0f0; font-weight: bold; text-align: center; font-size: 10pt; }
        .journal-table td.no { text-align: center; width: 30px; }
        .journal-table td.date { white-space: nowrap; width: 90px; text-align: center; }

        .signature-section { margin-top: 40px; page-break-inside: avoid; }
        .signature-row { display: flex; justify-content: space-between; }
        .signature-box { width: 45%; text-align: center; }
        .signature-box .place-date { margin-bottom: 8px; font-size: 11pt; }
        .signature-box .title { font-size: 11pt; margin-bottom: 60px; }
        .signature-box .name { font-size: 11pt; font-weight: bold; border-bottom: 1px solid #000; display: inline-block; padding: 0 10px; }

        .no-print { margin-bottom: 20px; text-align: center; }
        .no-print button { padding: 10px 24px; font-size: 14px; font-weight: 600; background: #3B49DF; color: #fff; border: none; border-radius: 8px; cursor: pointer; margin: 0 8px; }
        .no-print button:hover { background: #2c3ab0; }
        .no-print .btn-back { background: #6b7280; }
        .no-print .btn-back:hover { background: #4b5563; }

        @media print {
            body { padding: 10mm 15mm; }
            .no-print { display: none !important; }
        }

        @page { margin: 15mm; }
    </style>
</head>
<body>
    <!-- Print Controls -->
    <div class="no-print">
        <button onclick="window.print()"><i class="fas fa-print"></i> Cetak / Print</button>
        <button class="btn-back" onclick="history.back()">Kembali</button>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <?php if ($appLogo): ?>
                <img src="<?= upload_url($appLogo) ?>" alt="Logo" class="header-logo">
            <?php endif; ?>
            <div class="header-text">
                <?php if ($schoolName): ?>
                    <h2><?= e($schoolName) ?></h2>
                <?php endif; ?>
                <?php if ($schoolAddress): ?>
                    <p class="address"><?= e($schoolAddress) ?></p>
                <?php endif; ?>
                <?php if ($schoolContact): ?>
                    <p class="address"><?= e($schoolContact) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <h1 style="font-size:14pt;text-align:center;margin:16px 0 20px;text-transform:uppercase;letter-spacing:1px;">Laporan Jurnal Praktik Kerja Lapangan</h1>

    <!-- Student & Company Info -->
    <div class="info-section">
        <table class="info-table">
            <tr><td>Nama Siswa</td><td>: <?= e($printData['student_name']) ?></td></tr>
            <?php if (!empty($student['nis'])): ?>
                <tr><td>NIS</td><td>: <?= e($student['nis']) ?></td></tr>
            <?php endif; ?>
            <tr><td>Perusahaan / Instansi</td><td>: <?= e($pkl['company_name']) ?></td></tr>
            <tr><td>Alamat Perusahaan</td><td>: <?= e($pkl['company_address'] ?: '-') ?></td></tr>
            <tr><td>Pembimbing Perusahaan</td><td>: <?= e($printData['pembimbing']) ?></td></tr>
            <tr><td>Periode PKL</td><td>: <?= format_date($pkl['start_date']) ?> s/d <?= format_date($pkl['end_date']) ?></td></tr>
        </table>
    </div>

    <!-- Journal Table -->
    <div class="journal-title">Jurnal Kegiatan Harian</div>

    <table class="journal-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Kegiatan / Uraian Pekerjaan</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($journals)): ?>
                <tr><td colspan="4" style="text-align:center;padding:20px;color:#666;">Belum ada data jurnal.</td></tr>
            <?php else: ?>
                <?php foreach ($journals as $idx => $j): ?>
                    <tr>
                        <td class="no"><?= $idx + 1 ?></td>
                        <td class="date"><?= date('d/m/Y', strtotime($j['date'])) ?></td>
                        <td><?= e($j['activity']) ?></td>
                        <td><?= e($j['notes'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-row">
            <div class="signature-box">
                &nbsp;
            </div>
            <div class="signature-box">
                <div class="place-date"><?= e($printData['kabupaten']) ?>, <?= $tglFormatted ?></div>
                <div class="title">Pembimbing Perusahaan</div>
                <div class="name"><?= e($printData['pembimbing']) ?></div>
            </div>
        </div>
    </div>
</body>
</html>
