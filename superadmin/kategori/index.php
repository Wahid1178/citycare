<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

cekRole(['Super Admin']);

$keyword = $_GET['keyword'] ?? '';
$status = $_GET['status'] ?? '';

$filter = [];

if (!empty($keyword)) {
    $filter['$or'] = [
        ['nama_kategori' => new MongoDB\BSON\Regex($keyword, 'i')],
        ['keterangan' => new MongoDB\BSON\Regex($keyword, 'i')]
    ];
}

if (!empty($status)) {
    $filter['status'] = $status;
}

$kategoriList = $kategoriCollection->find(
    $filter,
    ['sort' => ['nama_kategori' => 1]]
);

include __DIR__ . '/../../partials/header.php';
?>

<div class="page-header">
    <h1>Kategori Laporan</h1>
    <p>Kelola kategori laporan fasilitas umum dan bencana lingkungan.</p>
</div>

<div class="card">
    <form method="GET" class="filter-grid">
        <input type="text" name="keyword" placeholder="Cari kategori..." value="<?= safe($keyword) ?>">

        <select name="status">
            <option value="">Semua Status</option>
            <option value="Aktif" <?= $status == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="Nonaktif" <?= $status == 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>

        <button type="submit">Filter</button>
        <a href="/admin/kategori/index.php" class="btn gray">Reset</a>
        <a href="/admin/kategori/tambah.php" class="btn green">+ Tambah Kategori</a>
    </form>
</div>

<div class="card">
    <table>
        <tr>
            <th>Nama Kategori</th>
            <th>Keterangan</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($kategoriList as $kategori): ?>
        <tr>
            <td><?= safe($kategori['nama_kategori']) ?></td>
            <td><?= safe($kategori['keterangan']) ?></td>
            <td><?= safe($kategori['status']) ?></td>
            <td class="actions">
                <a href="/admin/kategori/edit.php?id=<?= $kategori['_id'] ?>" class="btn orange">Edit</a>
                <a href="/admin/kategori/hapus.php?id=<?= $kategori['_id'] ?>" class="btn red" onclick="return confirm('Yakin hapus kategori ini?')">Hapus</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>