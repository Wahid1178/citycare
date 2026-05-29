<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
cekRole(['Admin']);

$id = new MongoDB\BSON\ObjectId($_GET['id']);
$laporanCollection->deleteOne(['_id' => $id]);

catatAktivitas($aktivitasCollection, 'Hapus Laporan', 'Admin menghapus data laporan.');

header("Location: /admin/laporan.php");
exit;
?>
