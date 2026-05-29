<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

cekRole(['Super Admin']);

$id = new MongoDB\BSON\ObjectId($_GET['id']);

$kategori = $kategoriCollection->findOne([
    '_id' => $id
]);

if (!$kategori) {
    die("Kategori tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kategoriCollection->updateOne(
        ['_id' => $id],
        ['$set' => [
            'nama_kategori' => $_POST['nama_kategori'],
            'keterangan' => $_POST['keterangan'],
            'status' => $_POST['status'],
            'updated_at' => date('Y-m-d H:i:s')
        ]]
    );

    header("Location: /admin/kategori/index.php");
    exit;
}

include __DIR__ . '/../../partials/header.php';
?>

<div class="page-header">
    <h1>Edit Kategori</h1>
    <p>Perbarui kategori laporan.</p>
</div>

<div class="card">
    <form method="POST">
        <label>Nama Kategori</label>
        <input type="text" name="nama_kategori" value="<?= safe($kategori['nama_kategori']) ?>" required>

        <label>Keterangan</label>
        <textarea name="keterangan" rows="5" required><?= safe($kategori['keterangan']) ?></textarea>

        <label>Status</label>
        <select name="status" required>
            <option value="Aktif" <?= $kategori['status'] == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="Nonaktif" <?= $kategori['status'] == 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>

        <button type="submit">Update Kategori</button>
        <a href="/admin/kategori/index.php" class="btn gray">Kembali</a>
    </form>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>