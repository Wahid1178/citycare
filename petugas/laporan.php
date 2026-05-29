<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
cekRole(['Petugas']);

$keyword = $_GET['keyword'] ?? '';
$kategori = $_GET['kategori'] ?? '';
$status = $_GET['status'] ?? '';
$prioritas = $_GET['prioritas'] ?? '';
$wilayah = $_GET['wilayah'] ?? '';

$filter = [];
if ($keyword) $filter['judul'] = new MongoDB\BSON\Regex($keyword, 'i');
if ($kategori) $filter['kategori'] = $kategori;
if ($status) $filter['status'] = $status;
if ($prioritas) $filter['prioritas'] = $prioritas;
if ($wilayah) $filter['wilayah'] = new MongoDB\BSON\Regex($wilayah, 'i');

$data = $laporanCollection->find($filter, ['sort' => ['created_at' => -1]]);
$kategoriList = $kategoriCollection->find(['status' => 'Aktif'], ['sort' => ['nama_kategori' => 1]]);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Data Laporan Masyarakat</h1>
    <p>Gunakan filter untuk mencari laporan berdasarkan kategori, status, prioritas, atau wilayah.</p>
</div>

<div class="card">
    <form method="GET" class="filter-grid">
        <input type="text" name="keyword" placeholder="Cari judul..." value="<?= safe($keyword) ?>">

        <select name="kategori">
            <option value="">Semua Kategori</option>
            <?php foreach ($kategoriList as $kat): ?>
                <option value="<?= safe($kat['nama_kategori']) ?>" <?= $kategori == $kat['nama_kategori'] ? 'selected' : '' ?>>
                    <?= safe($kat['nama_kategori']) ?>
                </option>
            <?php endforeach; ?>
            <option value="Lainnya" <?= $kategori == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
        </select>

        <select name="status">
            <option value="">Semua Status</option>
            <?php foreach (['Menunggu','Diverifikasi','Diproses','Selesai','Ditolak'] as $s): ?>
                <option value="<?= $s ?>" <?= $status == $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>

        <select name="prioritas">
            <option value="">Semua Prioritas</option>
            <?php foreach (['Rendah','Sedang','Tinggi','Darurat'] as $p): ?>
                <option value="<?= $p ?>" <?= $prioritas == $p ? 'selected' : '' ?>><?= $p ?></option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="wilayah" placeholder="Wilayah..." value="<?= safe($wilayah) ?>">

        <button type="submit">Filter</button>
    </form>
</div>

<div class="card">
    <table>
        <tr>
            <th>Pelapor</th>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Wilayah</th>
            <th>Status</th>
            <th>Prioritas</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($data as $laporan): ?>
        <tr>
            <td><?= safe($laporan['nama_pelapor']) ?></td>
            <td><?= safe($laporan['judul']) ?></td>
            <td><?= safe($laporan['kategori']) ?></td>
            <td><?= safe($laporan['wilayah']) ?></td>
            <td><?= badgeStatus($laporan['status']) ?></td>
            <td><?= safe($laporan['prioritas']) ?></td>
            <td>
                <a class="btn orange" href="/petugas/proses_laporan.php?id=<?= $laporan['_id'] ?>">Proses</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
