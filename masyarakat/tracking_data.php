<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

cekRole(['Masyarakat']);

header('Content-Type: application/json');

$id = $_GET['id'] ?? '';

if (empty($id)) {
    echo json_encode([
        'success' => false,
        'message' => 'ID laporan kosong'
    ]);
    exit;
}

$laporan = $laporanCollection->findOne([
    '_id' => new MongoDB\BSON\ObjectId($id),
    'user_id' => $_SESSION['user']['id']
]);

if (!$laporan) {
    echo json_encode([
        'success' => false,
        'message' => 'Laporan tidak ditemukan'
    ]);
    exit;
}

$tracking = $trackingCollection->findOne([
    'laporan_id' => $id
]);

if (!$tracking) {
    echo json_encode([
        'success' => false,
        'message' => 'Tracking belum tersedia. Pegawai belum memulai perjalanan.'
    ]);
    exit;
}

echo json_encode([
    'success' => true,

    'pegawai_nama' => $tracking['pegawai_nama'] ?? '-',
    'status_tracking' => $tracking['status_tracking'] ?? 'Belum mulai',

    'latitude' => $tracking['latitude'] ?? '',
    'longitude' => $tracking['longitude'] ?? '',

    'latitude_tujuan' => $tracking['latitude_tujuan'] ?? ($laporan['latitude_tujuan'] ?? ''),
    'longitude_tujuan' => $tracking['longitude_tujuan'] ?? ($laporan['longitude_tujuan'] ?? ''),

    'updated_at' => $tracking['updated_at'] ?? '-'
]);
?>