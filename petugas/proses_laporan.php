<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
cekRole(['Petugas']);

$id = new MongoDB\BSON\ObjectId($_GET['id']);
$laporan = $laporanCollection->findOne(['_id' => $id]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $laporanCollection->updateOne(
        ['_id' => $id],
        ['$set' => [
            'status' => $_POST['status'],
            'catatan_petugas' => $_POST['catatan_petugas'],
            'petugas_id' => $_SESSION['user']['id'],
            'petugas_nama' => $_SESSION['user']['nama'],
            'updated_at' => date('Y-m-d H:i:s')
        ]]
    );

    catatAktivitas($aktivitasCollection, 'Proses Laporan', 'Petugas memperbarui status laporan: ' . $laporan['judul']);

    header("Location: /petugas/laporan.php");
    exit;
}

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Proses Laporan</h1>
    <p>Petugas dapat melihat detail laporan dan memperbarui status penanganan.</p>
</div>

<div class="grid-2">
    <div class="card">
        <h2><?= safe($laporan['judul']) ?></h2>
        <p><b>Pelapor:</b><br><?= safe($laporan['nama_pelapor']) ?></p>
        <p><b>Kategori:</b><br><?= safe($laporan['kategori']) ?></p>
        <p><b>Wilayah:</b><br><?= safe($laporan['wilayah']) ?></p>
        <p><b>Alamat:</b><br><?= safe($laporan['alamat_lokasi']) ?></p>
        <p><b>Jumlah Titik:</b><br><?= safe($laporan['jumlah_titik']) ?></p>
        <p><b>Dampak:</b><br><?= safe($laporan['dampak']) ?></p>
        <p><b>Prioritas:</b><br><?= safe($laporan['prioritas']) ?></p>
        <p><b>Estimasi Biaya:</b><br><?= rupiah($laporan['estimasi_biaya']) ?></p>
        <p><b>Deskripsi:</b><br><?= nl2br(safe($laporan['deskripsi'])) ?></p>
    </div>

    <div class="card">
        <h2>Update Penanganan</h2>
        <form method="POST">
            <label>Status Laporan</label>
            <select name="status" required>
                <?php foreach (['Menunggu','Diverifikasi','Diproses','Selesai','Ditolak'] as $s): ?>
                    <option value="<?= $s ?>" <?= $laporan['status'] == $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>

            <label>Catatan Petugas</label>
            <textarea name="catatan_petugas" rows="6"><?= safe($laporan['catatan_petugas'] ?? '') ?></textarea>

            <button type="submit">Simpan Proses</button>
            <a href="/petugas/laporan.php" class="btn gray">Kembali</a>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
