<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
cekRole(['Masyarakat']);

$id = new MongoDB\BSON\ObjectId($_POST['id']);

$laporanCollection->updateOne(
    [
        '_id' => $id,
        'user_id' => $_SESSION['user']['id'],
        'status' => 'Menunggu'
    ],
    ['$set' => [
        'judul' => $_POST['judul'],
        'kategori' => $_POST['kategori'],
        'wilayah' => $_POST['wilayah'],
        'alamat_lokasi' => $_POST['alamat_lokasi'],
        'jumlah_titik' => (int)$_POST['jumlah_titik'],
        'dampak' => $_POST['dampak'],
        'prioritas' => $_POST['prioritas'],
        'deskripsi' => $_POST['deskripsi'],
        'updated_at' => date('Y-m-d H:i:s')
    ]]
);

catatAktivitas($aktivitasCollection, 'Update Laporan', 'Masyarakat mengubah laporan.');

header("Location: /masyarakat/laporan_saya.php");
exit;
?>
