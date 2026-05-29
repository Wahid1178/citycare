<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Masyarakat']);

$userId = $_SESSION['user']['id'];

$total = $laporanCollection->countDocuments(['user_id' => $userId]);
$menunggu = $laporanCollection->countDocuments(['user_id' => $userId, 'status_humas' => 'Menunggu Validasi']);
$diproses = $laporanCollection->countDocuments(['user_id' => $userId, 'status_lapangan' => 'Sedang Dikerjakan']);
$selesai = $laporanCollection->countDocuments(['user_id' => $userId, 'status_final' => 'Selesai Final']);

$laporanTerbaru = $laporanCollection->find(
    ['user_id' => $userId],
    ['sort' => ['created_at' => -1], 'limit' => 5]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="public-hero">
    <h1>Halo, <?= safe($_SESSION['user']['nama']) ?> 👋</h1>
    <p>
        Laporkan masalah fasilitas umum di sekitar Anda seperti jalan rusak,
        banjir, longsor, lampu jalan mati, sampah menumpuk, pohon tumbang,
        dan berbagai gangguan lingkungan lainnya.
    </p>
    <a href="/masyarakat/tambah_laporan.php" class="btn green">+ Buat Laporan Sekarang</a>
</div>

<div class="quick-action">
    <div class="quick-card">
        <h3>Laporan Saya</h3>
        <p>Pantau semua laporan yang sudah Anda kirimkan.</p>
        <a href="/masyarakat/laporan_saya.php" class="btn dark">Lihat Laporan</a>
    </div>

    <div class="quick-card">
        <h3>Progress Real-Time</h3>
        <p>Lihat perkembangan laporan dari humas sampai pegawai lapangan.</p>
        <a href="/masyarakat/laporan_saya.php" class="btn orange">Cek Progress</a>
    </div>

    <div class="quick-card">
        <h3>Notifikasi</h3>
        <p>Dapatkan update status terbaru dari laporan Anda.</p>
        <a href="/masyarakat/notifikasi.php" class="btn gray">Buka Notifikasi</a>
    </div>
</div>

<div class="grid-4">
    <div class="card stat">
        <h3>Total Laporan</h3>
        <p><?= $total ?></p>
    </div>

    <div class="card stat">
        <h3>Menunggu Validasi</h3>
        <p><?= $menunggu ?></p>
    </div>

    <div class="card stat">
        <h3>Sedang Diproses</h3>
        <p><?= $diproses ?></p>
    </div>

    <div class="card stat">
        <h3>Selesai</h3>
        <p><?= $selesai ?></p>
    </div>
</div>

<div class="card">
    <h2>Progress Laporan Terbaru</h2>

    <table>
        <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Status Humas</th>
            <th>Status Lapangan</th>
            <th>Progress</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($laporanTerbaru as $laporan): ?>
        <tr>
            <td><?= safe($laporan['judul']) ?></td>
            <td><?= safe($laporan['kategori']) ?></td>
            <td><?= safe($laporan['status_final']) ?></td>
            
            <td>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= (int)$laporan['persentase_progress'] ?>%;"></div>
                </div>
                <small><?= (int)$laporan['persentase_progress'] ?>%</small>
            </td>
            <td>
                <a class="btn gray" href="/masyarakat/detail_laporan.php?id=<?= $laporan['_id'] ?>">Detail</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>