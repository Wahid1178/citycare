<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Super Admin']);

$totalUser = $usersCollection->countDocuments();
$totalLaporan = $laporanCollection->countDocuments();
$totalKategori = $kategoriCollection->countDocuments();

$totalMenungguValidasi = $laporanCollection->countDocuments([
    'status_humas' => 'Menunggu Validasi'
]);

$totalDiproses = $laporanCollection->countDocuments([
    'status_lapangan' => 'Sedang Dikerjakan'
]);

$totalSelesaiLapangan = $laporanCollection->countDocuments([
    'status_lapangan' => 'Selesai Dikerjakan'
]);

$totalSelesaiFinal = $laporanCollection->countDocuments([
    'status_final' => 'Selesai Final'
]);

$totalDarurat = $laporanCollection->countDocuments([
    'prioritas' => 'Darurat'
]);

$laporanTerbaru = $laporanCollection->find(
    [],
    [
        'sort' => ['created_at' => -1],
        'limit' => 8
    ]
);

$aktivitasTerbaru = $aktivitasCollection->find(
    [],
    [
        'sort' => ['created_at' => -1],
        'limit' => 6
    ]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="hero-dashboard">
    <h1>Super Admin Dashboard</h1>
    <p>
        Pusat kontrol utama untuk memantau seluruh laporan fasilitas umum,
        bencana lingkungan, user, kategori, dan aktivitas sistem.
    </p>
</div>

<div class="grid-4">
    <div class="card stat">
        <h3>Total User</h3>
        <p><?= $totalUser ?></p>
    </div>

    <div class="card stat">
        <h3>Total Laporan</h3>
        <p><?= $totalLaporan ?></p>
    </div>

    <div class="card stat">
        <h3>Menunggu Validasi</h3>
        <p><?= $totalMenungguValidasi ?></p>
    </div>

    <div class="card stat">
        <h3>Selesai Final</h3>
        <p><?= $totalSelesaiFinal ?></p>
    </div>
</div>

<div class="grid-4">
    <div class="card stat">
        <h3>Sedang Diproses</h3>
        <p><?= $totalDiproses ?></p>
    </div>

    <div class="card stat">
        <h3>Selesai Lapangan</h3>
        <p><?= $totalSelesaiLapangan ?></p>
    </div>

    <div class="card stat">
        <h3>Laporan Darurat</h3>
        <p><?= $totalDarurat ?></p>
    </div>

    <div class="card stat">
        <h3>Total Kategori</h3>
        <p><?= $totalKategori ?></p>
    </div>
</div>

<div class="card">
    <a href="/superadmin/laporan.php" class="btn dark">Kelola Semua Laporan</a>
    <a href="/superadmin/rekap.php" class="btn green">Rekap Sistem</a>
    <a href="/superadmin/users/index.php" class="btn orange">Manajemen User</a>
    <a href="/superadmin/kategori/index.php" class="btn gray">Kategori Laporan</a>
</div>

<div class="grid-2">
    <div class="card">
        <h2>Laporan Terbaru</h2>

        <table>
            <tr>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Wilayah</th>
                <th>Status Humas</th>
                <th>Progress</th>
                <th>Aksi</th>
            </tr>

            <?php foreach ($laporanTerbaru as $laporan): ?>
            <tr>
                <td><?= safe($laporan['judul']) ?></td>
                <td><?= safe($laporan['kategori']) ?></td>
                <td><?= safe($laporan['wilayah']) ?></td>
                <td><?= safe($laporan['status_humas']) ?></td>
                <td>
                    <div class="progress-bar">
                        <div 
                            class="progress-fill" 
                            style="width: <?= (int)$laporan['persentase_progress'] ?>%;"
                        ></div>
                    </div>
                    <small><?= (int)$laporan['persentase_progress'] ?>%</small>
                </td>
                <td>
                    <a href="/superadmin/detail_laporan.php?id=<?= $laporan['_id'] ?>" class="btn gray">
                        Detail
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="card">
        <h2>Aktivitas Sistem Terbaru</h2>

        <table>
            <tr>
                <th>User</th>
                <th>Aksi</th>
                <th>Waktu</th>
            </tr>

            <?php foreach ($aktivitasTerbaru as $item): ?>
            <tr>
                <td><?= safe($item['user_nama'] ?? '-') ?></td>
                <td><?= safe($item['aksi'] ?? '-') ?></td>
                <td><?= safe($item['created_at'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>