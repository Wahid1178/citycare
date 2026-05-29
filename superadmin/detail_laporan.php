<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Super Admin']);

$id = new MongoDB\BSON\ObjectId($_GET['id']);

$laporan = $laporanCollection->findOne([
    '_id' => $id
]);

if (!$laporan) {
    die("Laporan tidak ditemukan.");
}

$progressList = $progressCollection->find(
    ['laporan_id' => (string)$id],
    ['sort' => ['created_at' => -1]]
);

$riwayat = $activityCollection->find(
    ['laporan_id' => (string)$id],
    ['sort' => ['created_at' => -1]]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Detail Laporan Super Admin</h1>
    <p>
        Super Admin dapat melihat seluruh alur laporan dari masyarakat,
        humas, pegawai lapangan, kepala bidang, hingga verifikasi akhir.
    </p>
</div>

<div class="grid-2">
    <div class="card">
        <h2><?= safe($laporan['judul']) ?></h2>

        <div class="timeline">
            <?php foreach ([
                'Menunggu Validasi',
                'Valid',
                'Sedang Dikerjakan',
                'Selesai Dikerjakan',
                'Selesai Final'
            ] as $step): ?>
                <div class="timeline-item 
                    <?=
                        $laporan['status_humas'] == $step ||
                        $laporan['status_lapangan'] == $step ||
                        $laporan['status_final'] == $step
                        ? 'timeline-active'
                        : ''
                    ?>
                ">
                    <?= safe($step) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <p><b>Pelapor:</b><br><?= safe($laporan['nama_pelapor']) ?></p>
        <p><b>Kategori:</b><br><?= safe($laporan['kategori']) ?></p>
        <p><b>Wilayah:</b><br><?= safe($laporan['wilayah']) ?></p>
        <p><b>Alamat Lokasi:</b><br><?= safe($laporan['alamat_lokasi']) ?></p>
        <p><b>Jumlah Titik:</b><br><?= safe($laporan['jumlah_titik']) ?></p>
        <p><b>Dampak:</b><br><?= safe($laporan['dampak']) ?></p>
        <p><b>Prioritas:</b><br><?= safe($laporan['prioritas']) ?></p>

        <p><b>Status Humas:</b><br><?= safe($laporan['status_humas']) ?></p>
        <p><b>Status Lapangan:</b><br><?= safe($laporan['status_lapangan']) ?></p>
        <p><b>Status Final:</b><br><?= safe($laporan['status_final']) ?></p>

        <p><b>Humas:</b><br><?= safe($laporan['humas_nama'] ?: '-') ?></p>
        <p><b>Pegawai Lapangan:</b><br><?= safe($laporan['pegawai_nama'] ?: '-') ?></p>
        <p><b>Estimasi Biaya:</b><br><?= rupiah($laporan['estimasi_biaya']) ?></p>

        <p><b>Progress:</b></p>
        <div class="progress-bar">
            <div 
                class="progress-fill" 
                style="width: <?= (int)$laporan['persentase_progress'] ?>%;"
            ></div>
        </div>
        <small><?= (int)$laporan['persentase_progress'] ?>%</small>

        <p style="margin-top:20px;"><b>Deskripsi:</b><br><?= nl2br(safe($laporan['deskripsi'])) ?></p>
    </div>

    <div class="card">
        <h2>Dokumentasi Laporan</h2>

        <?php if (!empty($laporan['foto_laporan'])): ?>
            <p><b>Foto Awal dari Masyarakat:</b></p>
            <img 
                src="/uploads/<?= safe($laporan['foto_laporan']) ?>"
                style="width:100%;max-height:320px;object-fit:cover;border-radius:18px;"
            >
        <?php else: ?>
            <p>Belum ada foto awal.</p>
        <?php endif; ?>

        <?php if (!empty($laporan['foto_selesai'])): ?>
            <p style="margin-top:20px;"><b>Foto Hasil Pekerjaan:</b></p>
            <img 
                src="/uploads/<?= safe($laporan['foto_selesai']) ?>"
                style="width:100%;max-height:320px;object-fit:cover;border-radius:18px;"
            >
        <?php endif; ?>

        <?php if (!empty($laporan['bukti_masyarakat'])): ?>
            <p style="margin-top:20px;"><b>Bukti Verifikasi Masyarakat:</b></p>
            <img 
                src="/uploads/<?= safe($laporan['bukti_masyarakat']) ?>"
                style="width:100%;max-height:320px;object-fit:cover;border-radius:18px;"
            >
        <?php endif; ?>

        <div class="info" style="margin-top:20px;">
            <b>Rating Masyarakat:</b><br>
            <?= (int)($laporan['rating'] ?? 0) ?>/5<br><br>

            <b>Ulasan:</b><br>
            <?= safe($laporan['ulasan'] ?? '-') ?>
        </div>
    </div>
</div>

<div class="card">
    <h2>Riwayat Progress Pegawai</h2>

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
            <td><?= safe($progress['created_at']) ?></td>
            <td><?= safe($progress['pegawai_nama']) ?></td>
            <td><?= safe($progress['persentase']) ?>%</td>
            <td><?= safe($progress['keterangan']) ?></td>
            <td>
                <?php if (!empty($progress['foto_progress'])): ?>
                    <a href="/uploads/<?= safe($progress['foto_progress']) ?>" target="_blank" class="btn gray">
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
    <h2>Riwayat Aktivitas Laporan</h2>

    <table>
        <tr>
            <th>Waktu</th>
            <th>User</th>
            <th>Role</th>
            <th>Aktivitas</th>
        </tr>

        <?php foreach ($riwayat as $item): ?>
        <tr>
            <td><?= safe($item['created_at']) ?></td>
            <td><?= safe($item['user_nama']) ?></td>
            <td><?= safe($item['role']) ?></td>
            <td><?= safe($item['aktivitas']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<a href="/admin/laporan.php" class="btn gray">Kembali</a>

<?php include __DIR__ . '/../partials/footer.php'; ?>