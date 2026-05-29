<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
cekRole(['Masyarakat']);

$userId = $_SESSION['user']['id'];

$totalLaporan = $laporanCollection->countDocuments(['user_id' => $userId]);
$totalSelesai = $laporanCollection->countDocuments(['user_id' => $userId, 'status' => 'Selesai']);
$totalDiproses = $laporanCollection->countDocuments(['user_id' => $userId, 'status' => 'Diproses']);
$totalMenunggu = $laporanCollection->countDocuments(['user_id' => $userId, 'status' => 'Menunggu']);

$rekapKategori = $laporanCollection->aggregate([
    ['$match' => ['user_id' => $userId]],
    ['$group' => [
        '_id' => '$kategori',
        'jumlah_laporan' => ['$sum' => 1],
        'total_titik' => ['$sum' => '$jumlah_titik'],
        'rata_titik' => ['$avg' => '$jumlah_titik']
    ]],
    ['$sort' => ['jumlah_laporan' => -1]]
]);

$laporanBesar = $laporanCollection->find(
    [
        'user_id' => $userId,
        'jumlah_titik' => ['$gt' => 3]
    ],
    ['sort' => ['jumlah_titik' => -1]]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Rekap Laporan Saya</h1>
    <p>Ringkasan laporan pribadi berdasarkan status, kategori, dan jumlah titik masalah.</p>
</div>

<div class="grid-4">
    <div class="card stat"><h3>Total Laporan</h3><p><?= $totalLaporan ?></p></div>
    <div class="card stat"><h3>Menunggu</h3><p><?= $totalMenunggu ?></p></div>
    <div class="card stat"><h3>Diproses</h3><p><?= $totalDiproses ?></p></div>
    <div class="card stat"><h3>Selesai</h3><p><?= $totalSelesai ?></p></div>
</div>

<div class="card">
    <h2>Rekap Berdasarkan Kategori</h2>
    <table>
        <tr>
            <th>Kategori</th>
            <th>Jumlah Laporan</th>
            <th>Total Titik</th>
            <th>Rata-rata Titik</th>
        </tr>

        <?php foreach ($rekapKategori as $item): ?>
        <tr>
            <td><?= safe($item['_id']) ?></td>
            <td><?= $item['jumlah_laporan'] ?></td>
            <td><?= $item['total_titik'] ?></td>
            <td><?= number_format($item['rata_titik'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card">
    <h2>Laporan Saya dengan Jumlah Titik Lebih dari 3</h2>
    <table>
        <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Wilayah</th>
            <th>Jumlah Titik</th>
            <th>Status</th>
        </tr>

        <?php foreach ($laporanBesar as $laporan): ?>
        <tr>
            <td><?= safe($laporan['judul']) ?></td>
            <td><?= safe($laporan['kategori']) ?></td>
            <td><?= safe($laporan['wilayah']) ?></td>
            <td><?= safe($laporan['jumlah_titik']) ?></td>
            <td><?= badgeStatus($laporan['status']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>