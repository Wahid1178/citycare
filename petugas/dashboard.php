<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
cekRole(['Petugas']);

$total = $laporanCollection->countDocuments();
$menunggu = $laporanCollection->countDocuments(['status' => 'Menunggu']);
$diproses = $laporanCollection->countDocuments(['status' => 'Diproses']);
$selesai = $laporanCollection->countDocuments(['status' => 'Selesai']);

$laporanBaru = $laporanCollection->find([], ['sort' => ['created_at' => -1], 'limit' => 6]);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Dashboard Petugas</h1>
    <p>Petugas dapat memverifikasi, memproses, menolak, dan menyelesaikan laporan.</p>
</div>

<div class="grid-4">
    <div class="card stat"><h3>Total Laporan</h3><p><?= $total ?></p></div>
    <div class="card stat"><h3>Menunggu</h3><p><?= $menunggu ?></p></div>
    <div class="card stat"><h3>Diproses</h3><p><?= $diproses ?></p></div>
    <div class="card stat"><h3>Selesai</h3><p><?= $selesai ?></p></div>
</div>

<div class="card">
    <a href="/petugas/laporan.php" class="btn dark">Kelola Semua Laporan</a>
    <a href="/petugas/rekap.php" class="btn green">Lihat Rekap Petugas</a>
</div>

<div class="card">
    <h2>Laporan Terbaru</h2>
    <table>
        <tr>
            <th>Pelapor</th>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Status</th>
            <th>Prioritas</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($laporanBaru as $laporan): ?>
        <tr>
            <td><?= safe($laporan['nama_pelapor']) ?></td>
            <td><?= safe($laporan['judul']) ?></td>
            <td><?= safe($laporan['kategori']) ?></td>
            <td><?= badgeStatus($laporan['status']) ?></td>
            <td><?= safe($laporan['prioritas']) ?></td>
            <td><a class="btn orange" href="/petugas/proses_laporan.php?id=<?= $laporan['_id'] ?>">Proses</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
