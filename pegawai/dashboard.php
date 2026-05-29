<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Pegawai Lapangan']);

$pegawaiId = $_SESSION['user']['id'];

$totalTugas = $laporanCollection->countDocuments([
    'pegawai_id' => $pegawaiId
]);

$sedangDikerjakan = $laporanCollection->countDocuments([
    'pegawai_id' => $pegawaiId,
    'status_lapangan' => 'Sedang Dikerjakan'
]);

$selesaiLapangan = $laporanCollection->countDocuments([
    'pegawai_id' => $pegawaiId,
    'status_lapangan' => 'Selesai Dikerjakan'
]);

$perluPerbaikan = $laporanCollection->countDocuments([
    'pegawai_id' => $pegawaiId,
    'status_final' => 'Perlu Perbaikan Ulang'
]);

$tugasTerbaru = $laporanCollection->find(
    ['pegawai_id' => $pegawaiId],
    [
        'sort' => ['updated_at' => -1],
        'limit' => 6
    ]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="hero-dashboard">
    <h1>Dashboard Pegawai Lapangan</h1>
    <p>
        Pantau tugas lapangan, update progress pekerjaan,
        dan unggah dokumentasi perbaikan fasilitas umum.
    </p>
</div>

<div class="grid-4">

    <div class="card stat">
        <h3>Total Tugas</h3>
        <p><?= $totalTugas ?></p>
    </div>

    <div class="card stat">
        <h3>Sedang Dikerjakan</h3>
        <p><?= $sedangDikerjakan ?></p>
    </div>

    <div class="card stat">
        <h3>Selesai Lapangan</h3>
        <p><?= $selesaiLapangan ?></p>
    </div>

    <div class="card stat">
        <h3>Perlu Perbaikan</h3>
        <p><?= $perluPerbaikan ?></p>
    </div>

</div>

<div class="card">

    <a href="/pegawai/tugas_saya.php" class="btn dark">
        Lihat Semua Tugas
    </a>

</div>

<div class="card">

    <h2>Tugas Terbaru</h2>

    <table>

        <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Wilayah</th>
            <th>Status</th>
            <th>Progress</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($tugasTerbaru as $laporan): ?>

        <tr>

            <td><?= safe($laporan['judul']) ?></td>

            <td><?= safe($laporan['kategori']) ?></td>

            <td><?= safe($laporan['wilayah']) ?></td>

            <td><?= safe($laporan['status_lapangan']) ?></td>

            <td>

                <div class="progress-bar">

                    <div
                        class="progress-fill"
                        style="width:<?= (int)$laporan['persentase_progress'] ?>%;"
                    ></div>

                </div>

                <small>
                    <?= (int)$laporan['persentase_progress'] ?>%
                </small>

            </td>

            <td>

                <a
                    href="/pegawai/update_progress.php?id=<?= $laporan['_id'] ?>"
                    class="btn orange"
                >
                    Update
                </a>

            </td>

        </tr>

        <?php endforeach; ?>

    </table>

</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>