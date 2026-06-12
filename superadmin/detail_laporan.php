<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Super Admin']);

if (empty($_GET['id'])) {
    die("ID laporan tidak ditemukan.");
}

try {
    $id = new MongoDB\BSON\ObjectId($_GET['id']);
} catch (Exception $e) {
    die("ID laporan tidak valid.");
}

$laporan = $laporanCollection->findOne([
    '_id' => $id
]);

if (!$laporan) {
    die("Laporan tidak ditemukan.");
}

$progressList = $progressCollection->find(
    [
        'laporan_id' => (string)$id
    ],
    [
        'sort' => [
            'created_at' => -1
        ]
    ]
);

$activityList = $activityCollection->find(
    [
        'laporan_id' => (string)$id
    ],
    [
        'sort' => [
            'created_at' => -1
        ]
    ]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Detail Laporan</h1>
    <p>
        Halaman ini digunakan Super Admin untuk melihat seluruh informasi laporan,
        progress pegawai, verifikasi Humas, verifikasi masyarakat, dan rating pelayanan.
    </p>
</div>

<div class="grid-2">

    <div class="card">
        <h2>Informasi Laporan</h2>

        <p><b>Judul:</b><br><?= safe($laporan['judul'] ?? '-') ?></p>
        <p><b>Pelapor:</b><br><?= safe($laporan['nama_pelapor'] ?? '-') ?></p>
        <p><b>User ID:</b><br><?= safe($laporan['user_id'] ?? '-') ?></p>
        <p><b>Kategori:</b><br><?= safe($laporan['kategori'] ?? '-') ?></p>
        <p><b>Wilayah:</b><br><?= safe($laporan['wilayah'] ?? '-') ?></p>
        <p><b>Alamat:</b><br><?= safe($laporan['alamat_lokasi'] ?? '-') ?></p>
        <p><b>Jumlah Titik:</b><br><?= safe($laporan['jumlah_titik'] ?? '-') ?></p>
        <p><b>Dampak:</b><br><?= safe($laporan['dampak'] ?? '-') ?></p>
        <p><b>Prioritas:</b><br><?= safe($laporan['prioritas'] ?? '-') ?></p>

        <p><b>Deskripsi:</b><br><?= nl2br(safe($laporan['deskripsi'] ?? '-')) ?></p>

        <?php if (!empty($laporan['foto_laporan'])): ?>
            <p><b>Foto Laporan Awal:</b></p>
            <img
                src="/uploads/<?= safe($laporan['foto_laporan']) ?>"
                style="width:100%;border-radius:18px;max-height:340px;object-fit:cover;"
            >
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Status & Penugasan</h2>

        <p><b>Status Umum:</b><br><?= safe($laporan['status'] ?? '-') ?></p>
        <p><b>Status Humas:</b><br><?= safe($laporan['status_humas'] ?? '-') ?></p>
        <p><b>Status Lapangan:</b><br><?= safe($laporan['status_lapangan'] ?? '-') ?></p>
        <p><b>Status Final:</b><br><?= safe($laporan['status_final'] ?? '-') ?></p>

        <p><b>Progress:</b></p>
        <div class="progress-bar">
            <div
                class="progress-fill"
                style="width: <?= (int)($laporan['persentase_progress'] ?? 0) ?>%;"
            ></div>
        </div>
        <small><?= (int)($laporan['persentase_progress'] ?? 0) ?>%</small>

        <hr style="margin:20px 0;">

        <p><b>Humas:</b><br><?= safe($laporan['humas_nama'] ?? '-') ?></p>
        <p><b>Pegawai:</b><br><?= safe($laporan['pegawai_nama'] ?? '-') ?></p>
        <p><b>Estimasi Biaya:</b><br>Rp <?= number_format((int)($laporan['estimasi_biaya'] ?? 0), 0, ',', '.') ?></p>

        <p><b>Catatan Humas:</b><br><?= nl2br(safe($laporan['catatan_humas'] ?? '-')) ?></p>
        <p><b>Catatan Verifikasi Humas:</b><br><?= nl2br(safe($laporan['catatan_verifikasi_humas'] ?? '-')) ?></p>
        <p><b>Catatan Pegawai:</b><br><?= nl2br(safe($laporan['catatan_pegawai'] ?? '-')) ?></p>
        <p><b>Catatan Masyarakat:</b><br><?= nl2br(safe($laporan['catatan_masyarakat'] ?? '-')) ?></p>

        <?php if (($laporan['verifikasi_humas'] ?? null) === true): ?>
            <div style="padding:14px;border-radius:14px;background:#e8f5e9;color:#1b5e20;margin-top:12px;">
                ✅ Hasil pekerjaan telah disetujui Humas.
            </div>
        <?php elseif (($laporan['verifikasi_humas'] ?? null) === false): ?>
            <div style="padding:14px;border-radius:14px;background:#ffebee;color:#b71c1c;margin-top:12px;">
                ❌ Hasil pekerjaan ditolak Humas dan perlu perbaikan ulang.
            </div>
        <?php else: ?>
            <div style="padding:14px;border-radius:14px;background:#fff3cd;color:#664d03;margin-top:12px;">
                ⏳ Hasil pekerjaan belum diverifikasi Humas.
            </div>
        <?php endif; ?>

        <?php if (($laporan['verifikasi_masyarakat'] ?? false) === true): ?>
            <div style="padding:14px;border-radius:14px;background:#e8f5e9;color:#1b5e20;margin-top:12px;">
                ✅ Laporan sudah diverifikasi masyarakat.
            </div>
        <?php elseif (($laporan['status_final'] ?? '') == 'Menunggu Verifikasi Masyarakat'): ?>
            <div style="padding:14px;border-radius:14px;background:#fff3cd;color:#664d03;margin-top:12px;">
                ⏳ Menunggu verifikasi dari masyarakat.
            </div>
        <?php endif; ?>
    </div>

</div>

<div class="grid-2">

    <div class="card">
        <h2>Foto Hasil & Bukti</h2>

        <?php if (!empty($laporan['foto_selesai'])): ?>
            <p><b>Foto Hasil Pekerjaan Pegawai:</b></p>
            <img
                src="/uploads/<?= safe($laporan['foto_selesai']) ?>"
                style="width:100%;border-radius:18px;max-height:340px;object-fit:cover;"
            >
        <?php else: ?>
            <p>Belum ada foto hasil pekerjaan pegawai.</p>
        <?php endif; ?>

        <?php if (!empty($laporan['bukti_masyarakat'])): ?>
            <p style="margin-top:20px;"><b>Bukti Verifikasi Masyarakat:</b></p>
            <img
                src="/uploads/<?= safe($laporan['bukti_masyarakat']) ?>"
                style="width:100%;border-radius:18px;max-height:340px;object-fit:cover;"
            >
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Rating Masyarakat</h2>

        <?php if (!empty($laporan['sudah_rating'])): ?>
            <p><b>Rating Pegawai:</b><br>⭐ <?= number_format((float)($laporan['rating_pegawai'] ?? 0), 1) ?> / 5</p>
            <p><b>Ulasan:</b><br><?= nl2br(safe($laporan['ulasan_pegawai'] ?? '-')) ?></p>
            <p><b>Tanggal Rating:</b><br><?= safe($laporan['tanggal_rating'] ?? '-') ?></p>
        <?php else: ?>
            <p>Belum ada rating dari masyarakat.</p>
        <?php endif; ?>

        <hr style="margin:20px 0;">

        <p><b>Dibuat Pada:</b><br><?= safe($laporan['created_at'] ?? '-') ?></p>
        <p><b>Diperbarui Pada:</b><br><?= safe($laporan['updated_at'] ?? '-') ?></p>
        <p><b>Tanggal Verifikasi Humas:</b><br><?= safe($laporan['tanggal_verifikasi_humas'] ?? '-') ?></p>
        <p><b>Tanggal Verifikasi Masyarakat:</b><br><?= safe($laporan['tanggal_verifikasi_masyarakat'] ?? '-') ?></p>
    </div>

</div>

<div class="card">
    <h2>Riwayat Progress Lapangan</h2>

    <table>
        <tr>
            <th>Waktu</th>
            <th>Pegawai</th>
            <th>Progress</th>
            <th>Keterangan</th>
            <th>Foto</th>
        </tr>

        <?php foreach ($progressList as $progress): ?>
            <tr>
                <td><?= safe($progress['created_at'] ?? '-') ?></td>
                <td><?= safe($progress['pegawai_nama'] ?? '-') ?></td>
                <td><?= safe($progress['persentase'] ?? 0) ?>%</td>
                <td><?= safe($progress['keterangan'] ?? '-') ?></td>
                <td>
                    <?php if (!empty($progress['foto_progress'])): ?>
                        <a
                            href="/uploads/<?= safe($progress['foto_progress']) ?>"
                            target="_blank"
                            class="btn gray"
                        >
                            Lihat Foto
                        </a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card">
    <h2>Riwayat Aktivitas</h2>

    <table>
        <tr>
            <th>Waktu</th>
            <th>Nama User</th>
            <th>Role</th>
            <th>Aktivitas</th>
        </tr>

        <?php foreach ($activityList as $activity): ?>
            <tr>
                <td><?= safe($activity['created_at'] ?? '-') ?></td>
                <td><?= safe($activity['user_nama'] ?? '-') ?></td>
                <td><?= safe($activity['role'] ?? '-') ?></td>
                <td><?= safe($activity['aktivitas'] ?? '-') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<a href="/superadmin/laporan.php" class="btn gray">Kembali</a>

<?php include __DIR__ . '/../partials/footer.php'; ?>