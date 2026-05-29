<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Kepala Bidang']);

$totalLaporan = $laporanCollection->countDocuments();

$totalValid = $laporanCollection->countDocuments([
    'status_humas' => 'Valid'
]);

$totalDiproses = $laporanCollection->countDocuments([
    'status_lapangan' => 'Sedang Dikerjakan'
]);

$totalSelesaiLapangan = $laporanCollection->countDocuments([
    'status_lapangan' => 'Selesai Dikerjakan'
]);

$totalFinal = $laporanCollection->countDocuments([
    'status_final' => 'Selesai Final'
]);

$laporanTerbaru = $laporanCollection->find(
    [],
    [
        'sort' => ['updated_at' => -1],
        'limit' => 6
    ]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="hero-dashboard">
    <h1>Dashboard Kepala Bidang</h1>
    <p>
        Pantau perkembangan laporan masyarakat,
        kinerja Humas,
        dan progress pekerjaan Pegawai Lapangan.
    </p>
</div>

<div class="grid-4">
    <div class="card stat">
        <h3>Total Laporan</h3>
        <p><?= $totalLaporan ?></p>
    </div>

    <div class="card stat">
        <h3>Laporan Valid</h3>
        <p><?= $totalValid ?></p>
    </div>

    <div class="card stat">
        <h3>Sedang Dikerjakan</h3>
        <p><?= $totalDiproses ?></p>
    </div>

    <div class="card stat">
        <h3>Selesai Final</h3>
        <p><?= $totalFinal ?></p>
    </div>
</div>

<div class="card">
    <a href="/kepala_bidang/monitoring.php" class="btn dark">Monitoring Laporan</a>
    <a href="/kepala_bidang/performa_pegawai.php" class="btn green">Performa Pegawai</a>
    <a href="/kepala_bidang/rekap.php" class="btn orange">Rekap Kepala Bidang</a>
</div>

<div class="card">
    <h2>Laporan Terbaru</h2>

    <table>
        <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Wilayah</th>
            <th>Status Humas</th>
            <th>Status Lapangan</th>
            <th>Final</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($laporanTerbaru as $laporan): ?>
        <tr>
            <td><?= safe($laporan['judul']) ?></td>
            <td><?= safe($laporan['kategori']) ?></td>
            <td><?= safe($laporan['wilayah']) ?></td>
            <td><?= safe($laporan['status_humas']) ?></td>
            <td><?= safe($laporan['status_lapangan']) ?></td>
            <td><?= safe($laporan['status_final']) ?></td>
            <td>
                <a class="btn gray" href="/kepala_bidang/detail_laporan.php?id=<?= $laporan['_id'] ?>">
                    Detail
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>