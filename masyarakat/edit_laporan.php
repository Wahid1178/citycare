<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
cekRole(['Masyarakat']);

$id = new MongoDB\BSON\ObjectId($_GET['id']);
$laporan = $laporanCollection->findOne([
    '_id' => $id,
    'user_id' => $_SESSION['user']['id'],
    'status' => 'Menunggu'
]);

$kategoriList = $kategoriCollection->find(['status' => 'Aktif'], ['sort' => ['nama_kategori' => 1]]);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Edit Laporan</h1>
    <p>Laporan hanya bisa diedit jika status masih Menunggu.</p>
</div>

<div class="card">
    <form action="/masyarakat/update_laporan.php" method="POST">
        <input type="hidden" name="id" value="<?= $laporan['_id'] ?>">

        <div class="form-grid">
            <div>
                <label>Judul Laporan</label>
                <input type="text" name="judul" value="<?= safe($laporan['judul']) ?>" required>
            </div>

            <div>
                <label>Kategori</label>
                <select name="kategori" required>
                    <?php foreach ($kategoriList as $kat): ?>
                        <option value="<?= safe($kat['nama_kategori']) ?>" <?= $laporan['kategori'] == $kat['nama_kategori'] ? 'selected' : '' ?>>
                            <?= safe($kat['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="Lainnya" <?= $laporan['kategori'] == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </div>

            <div>
                <label>Wilayah</label>
                <input type="text" name="wilayah" value="<?= safe($laporan['wilayah']) ?>" required>
            </div>

            <div>
                <label>Alamat Lokasi</label>
                <input type="text" name="alamat_lokasi" value="<?= safe($laporan['alamat_lokasi']) ?>" required>
            </div>

            <div>
                <label>Jumlah Titik</label>
                <input type="number" name="jumlah_titik" min="1" value="<?= safe($laporan['jumlah_titik']) ?>" required>
            </div>

            <div>
                <label>Dampak</label>
                <select name="dampak">
                    <?php foreach (['Rendah','Sedang','Tinggi'] as $d): ?>
                        <option value="<?= $d ?>" <?= $laporan['dampak'] == $d ? 'selected' : '' ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label>Prioritas</label>
                <select name="prioritas">
                    <?php foreach (['Rendah','Sedang','Tinggi','Darurat'] as $p): ?>
                        <option value="<?= $p ?>" <?= $laporan['prioritas'] == $p ? 'selected' : '' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <label>Deskripsi</label>
        <textarea name="deskripsi" rows="5"><?= safe($laporan['deskripsi']) ?></textarea>

        <button type="submit">Update Laporan</button>
        <a href="/masyarakat/laporan_saya.php" class="btn gray">Kembali</a>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
