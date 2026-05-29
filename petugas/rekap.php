<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
cekRole(['Petugas']);

$petugasId = $_SESSION['user']['id'];

$totalDitangani = $laporanCollection->countDocuments(['petugas_id' => $petugasId]);
$selesai = $laporanCollection->countDocuments(['petugas_id' => $petugasId, 'status' => 'Selesai']);
$diproses = $laporanCollection->countDocuments(['petugas_id' => $petugasId, 'status' => 'Diproses']);

$rekap = $laporanCollection->aggregate([
    ['$match' => ['petugas_id' => $petugasId]],
    ['$group' => [
        '_id' => '$kategori',
        'jumlah_laporan' => ['$sum' => 1],
        'total_titik' => ['$sum' => '$jumlah_titik'],
        'rata_titik' => ['$avg' => '$jumlah_titik']
    ]],
    ['$sort' => ['jumlah_laporan' => -1]]
]);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Rekap Petugas</h1>
    <p>Rekapitulasi laporan yang sudah ditangani oleh petugas.</p>
</div>

<div class="grid-3">
    <div class="card stat"><h3>Total Ditangani</h3><p><?= $totalDitangani ?></p></div>
    <div class="card stat"><h3>Diproses</h3><p><?= $diproses ?></p></div>
    <div class="card stat"><h3>Selesai</h3><p><?= $selesai ?></p></div>
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
        <?php foreach ($rekap as $item): ?>
        <tr>
            <td><?= safe($item['_id']) ?></td>
            <td><?= $item['jumlah_laporan'] ?></td>
            <td><?= $item['total_titik'] ?></td>
            <td><?= number_format($item['rata_titik'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
