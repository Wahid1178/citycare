<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

cekRole(['Super Admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kategoriCollection->insertOne([
        'nama_kategori' => $_POST['nama_kategori'],
        'keterangan' => $_POST['keterangan'],
        'status' => $_POST['status'],
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    header("Location: /admin/kategori/index.php");
    exit;
}

include __DIR__ . '/../../partials/header.php';
?>

<div class="page-header">
    <h1>Tambah Kategori</h1>
    <p>Tambahkan kategori baru untuk laporan masyarakat.</p>
</div>

<div class="card">
    <form method="POST">
        <label>Nama Kategori</label>
        <input type="text" name="nama_kategori" required>

        <label>Keterangan</label>
        <textarea name="keterangan" rows="5" required></textarea>

        <label>Status</label>
        <select name="status" required>
            <option value="Aktif">Aktif</option>
            <option value="Nonaktif">Nonaktif</option>
        </select>

        <button type="submit">Simpan Kategori</button>
        <a href="/admin/kategori/index.php" class="btn gray">Kembali</a>
    </form>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>