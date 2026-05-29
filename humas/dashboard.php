<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Humas']);

$totalMasuk = $laporanCollection->countDocuments([
    'status_humas' => 'Menunggu Validasi'
]);

$totalDiproses = $laporanCollection->countDocuments([
    'status_lapangan' => 'Sedang Dikerjakan'
]);

$totalSelesai = $laporanCollection->countDocuments([
    'status_final' => 'Selesai Final'
]);

$laporanTerbaru = $laporanCollection->find(
    [],
    [
        'sort' => ['created_at' => -1],
        'limit' => 6
    ]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="hero-dashboard">
    <h1>Dashboard Humas</h1>
    <p>
        Kelola laporan masyarakat,
        lakukan validasi,
        tentukan estimasi biaya,
        dan tunjuk pegawai lapangan.
    </p>
</div>

<div class="grid-3">

    <div class="card stat">
        <h3>Menunggu Validasi</h3>
        <p><?= $totalMasuk ?></p>
    </div>

    <div class="card stat">
        <h3>Sedang Diproses</h3>
        <p><?= $totalDiproses ?></p>
    </div>

    <div class="card stat">
        <h3>Selesai</h3>
        <p><?= $totalSelesai ?></p>
    </div>

</div>

<div class="card">

    <h2>Laporan Terbaru</h2>

    <table>

        <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Wilayah</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($laporanTerbaru as $item): ?>

        <tr>

            <td><?= safe($item['judul']) ?></td>

            <td><?= safe($item['kategori']) ?></td>

            <td><?= safe($item['wilayah']) ?></td>

            <td><?= safe($item['status_humas']) ?></td>

            <td>
                <a
                    href="/humas/detail_laporan.php?id=<?= $item['_id'] ?>"
                    class="btn dark"
                >
                    Kelola
                </a>
            </td>

        </tr>

        <?php endforeach; ?>

    </table>

</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>