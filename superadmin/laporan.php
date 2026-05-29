<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Super Admin']);

$keyword = $_GET['keyword'] ?? '';
$kategori = $_GET['kategori'] ?? '';
$wilayah = $_GET['wilayah'] ?? '';
$statusHumas = $_GET['status_humas'] ?? '';
$statusLapangan = $_GET['status_lapangan'] ?? '';
$statusFinal = $_GET['status_final'] ?? '';
$prioritas = $_GET['prioritas'] ?? '';

$filter = [];

if (!empty($keyword)) {
    $filter['$or'] = [
        ['judul' => new MongoDB\BSON\Regex($keyword, 'i')],
        ['kategori' => new MongoDB\BSON\Regex($keyword, 'i')],
        ['wilayah' => new MongoDB\BSON\Regex($keyword, 'i')],
        ['nama_pelapor' => new MongoDB\BSON\Regex($keyword, 'i')]
    ];
}

if (!empty($kategori)) {
    $filter['kategori'] = $kategori;
}

if (!empty($wilayah)) {
    $filter['wilayah'] = new MongoDB\BSON\Regex($wilayah, 'i');
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

if (!empty($prioritas)) {
    $filter['prioritas'] = $prioritas;
}

$laporanList = $laporanCollection->find(
    $filter,
    ['sort' => ['created_at' => -1]]
);

$kategoriList = $kategoriCollection->find(
    ['status' => 'Aktif'],
    ['sort' => ['nama_kategori' => 1]]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Kelola Semua Laporan</h1>
    <p>
        Super Admin dapat memantau seluruh laporan, melihat status triple check,
        dan mengakses detail laporan.
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

        <select name="kategori">
            <option value="">Semua Kategori</option>
            <?php foreach ($kategoriList as $kat): ?>
                <option 
                    value="<?= safe($kat['nama_kategori']) ?>"
                    <?= $kategori == $kat['nama_kategori'] ? 'selected' : '' ?>
                >
                    <?= safe($kat['nama_kategori']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input 
            type="text" 
            name="wilayah" 
            placeholder="Cari wilayah..."
            value="<?= safe($wilayah) ?>"
        >

        <select name="status_humas">
            <option value="">Status Humas</option>
            <?php foreach (['Menunggu Validasi','Valid','Ditolak'] as $s): ?>
                <option value="<?= $s ?>" <?= $statusHumas == $s ? 'selected' : '' ?>>
                    <?= $s ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status_lapangan">
            <option value="">Status Lapangan</option>
            <?php foreach (['Belum Ditugaskan','Sedang Dikerjakan','Selesai Dikerjakan'] as $s): ?>
                <option value="<?= $s ?>" <?= $statusLapangan == $s ? 'selected' : '' ?>>
                    <?= $s ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status_final">
            <option value="">Status Final</option>
            <?php foreach (['Belum Selesai','Selesai Final','Perlu Perbaikan Ulang'] as $s): ?>
                <option value="<?= $s ?>" <?= $statusFinal == $s ? 'selected' : '' ?>>
                    <?= $s ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="prioritas">
            <option value="">Prioritas</option>
            <?php foreach (['Rendah','Sedang','Tinggi','Darurat'] as $p): ?>
                <option value="<?= $p ?>" <?= $prioritas == $p ? 'selected' : '' ?>>
                    <?= $p ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filter</button>
        <a href="/admin/laporan.php" class="btn gray">Reset</a>
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
            <th>Status Final</th>
            <th>Progress</th>
            <th>Prioritas</th>
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
            <td><?= safe($laporan['status_final']) ?></td>
            <td>
                <div class="progress-bar">
                    <div 
                        class="progress-fill" 
                        style="width: <?= (int)$laporan['persentase_progress'] ?>%;"
                    ></div>
                </div>
                <small><?= (int)$laporan['persentase_progress'] ?>%</small>
            </td>
            <td><?= safe($laporan['prioritas']) ?></td>
            <td class="actions">
                <a href="/admin/detail_laporan.php?id=<?= $laporan['_id'] ?>" class="btn orange">
                    Detail
                </a>
                <a 
                    href="/admin/hapus_laporan.php?id=<?= $laporan['_id'] ?>" 
                    onclick="return confirm('Yakin ingin menghapus laporan ini?')"
                    class="btn red"
                >
                    Hapus
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>