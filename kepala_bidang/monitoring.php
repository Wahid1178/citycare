<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Kepala Bidang']);

$keyword = $_GET['keyword'] ?? '';
$statusHumas = $_GET['status_humas'] ?? '';
$statusLapangan = $_GET['status_lapangan'] ?? '';
$statusFinal = $_GET['status_final'] ?? '';

$filter = [];

if (!empty($keyword)) {
    $filter['$or'] = [
        ['judul' => new MongoDB\BSON\Regex($keyword, 'i')],
        ['kategori' => new MongoDB\BSON\Regex($keyword, 'i')],
        ['wilayah' => new MongoDB\BSON\Regex($keyword, 'i')],
        ['nama_pelapor' => new MongoDB\BSON\Regex($keyword, 'i')]
    ];
}

if (!empty($statusHumas)) {
    $filter['status_humas'] = $statusHumas;
}

if (!empty($statusLapangan)) {
    $filter['status_lapangan'] = $statusLapangan;
}

if (!empty($statusFinal)) {
    $filter['status_final'] = $statusFinal;
}

$laporanList = $laporanCollection->find(
    $filter,
    ['sort' => ['updated_at' => -1]]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Monitoring Laporan</h1>
    <p>
        Kepala Bidang dapat memantau seluruh laporan,
        status validasi Humas, dan progress lapangan.
    </p>
</div>

<div class="card">
    <form method="GET" class="filter-grid">
        <input 
            type="text" 
            name="keyword" 
            placeholder="Cari judul, kategori, wilayah, atau pelapor..."
            value="<?= safe($keyword) ?>"
        >

        <select name="status_humas">
            <option value="">Semua Status Humas</option>
            <?php foreach (['Menunggu Validasi','Valid','Ditolak'] as $s): ?>
                <option value="<?= $s ?>" <?= $statusHumas == $s ? 'selected' : '' ?>>
                    <?= $s ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status_lapangan">
            <option value="">Semua Status Lapangan</option>
            <?php foreach (['Belum Ditugaskan','Sedang Dikerjakan','Selesai Dikerjakan'] as $s): ?>
                <option value="<?= $s ?>" <?= $statusLapangan == $s ? 'selected' : '' ?>>
                    <?= $s ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status_final">
            <option value="">Semua Status Final</option>
            <?php foreach (['Belum Selesai','Selesai Final','Perlu Perbaikan Ulang'] as $s): ?>
                <option value="<?= $s ?>" <?= $statusFinal == $s ? 'selected' : '' ?>>
                    <?= $s ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filter</button>
        <a href="/kepala_bidang/monitoring.php" class="btn gray">Reset</a>
    </form>
</div>

<div class="card">
    <table>
        <tr>
            <th>Judul</th>
            <th>Pelapor</th>
            <th>Kategori</th>
            <th>Wilayah</th>
            <th>Status Humas</th>
            <th>Status Lapangan</th>
            <th>Progress</th>
            <th>Final</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($laporanList as $laporan): ?>
        <tr>
            <td><?= safe($laporan['judul']) ?></td>
            <td><?= safe($laporan['nama_pelapor']) ?></td>
            <td><?= safe($laporan['kategori']) ?></td>
            <td><?= safe($laporan['wilayah']) ?></td>
            <td><?= safe($laporan['status_humas']) ?></td>
            <td><?= safe($laporan['status_lapangan']) ?></td>
            <td>
                <div class="progress-bar">
                    <div 
                        class="progress-fill" 
                        style="width: <?= (int)$laporan['persentase_progress'] ?>%;"
                    ></div>
                </div>
                <small><?= (int)$laporan['persentase_progress'] ?>%</small>
            </td>
            <td><?= safe($laporan['status_final']) ?></td>
            <td>
                <a class="btn orange" href="/kepala_bidang/detail_laporan.php?id=<?= $laporan['_id'] ?>">
                    Detail
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>