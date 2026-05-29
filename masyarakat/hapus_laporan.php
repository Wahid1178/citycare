<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
cekRole(['Masyarakat']);

$id = new MongoDB\BSON\ObjectId($_GET['id']);

$laporanCollection->deleteOne([
    '_id' => $id,
    'user_id' => $_SESSION['user']['id'],
    'status' => 'Menunggu'
]);

catatAktivitas($aktivitasCollection, 'Hapus Laporan', 'Masyarakat menghapus laporan yang masih menunggu.');

header("Location: /masyarakat/laporan_saya.php");
exit;
?>
