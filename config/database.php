<?php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $mongoUri = getenv('MONGODB_URI');

    if (!$mongoUri) {
        $mongoUri = "mongodb+srv://mhidayatnw123_db_user:Wahid178@citycare.xivrvco.mongodb.net/?retryWrites=true&w=majority&appName=citycare";
    }

    $client = new MongoDB\Client($mongoUri);

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