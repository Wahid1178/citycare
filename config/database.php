<?php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $db = $client->citycare_pro_db;

    $db = $client->citycare;

    $usersCollection = $db->users;
    $kategoriCollection = $db->kategori_laporan;
    $laporanCollection = $db->laporan;
    $trackingCollection = $db->tracking_pegawai;
    $aktivitasCollection = $db->aktivitas;
    $notifCollection = $db->notifications;
    $activityCollection = $db->activities;
    $progressCollection = $db->progress_laporan;

} catch (Exception $e) {
    die("Koneksi MongoDB gagal: " . $e->getMessage());
}
?>