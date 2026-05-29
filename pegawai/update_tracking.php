<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

cekRole(['Pegawai Lapangan']);

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$laporanId = $data['laporan_id'] ?? '';

if (empty($laporanId)) {
    echo json_encode([
        'success' => false,
        'message' => 'laporan_id kosong'
    ]);
    exit;
}

$laporan = $laporanCollection->findOne([
    '_id' => new MongoDB\BSON\ObjectId($laporanId),
    'pegawai_id' => $_SESSION['user']['id']
]);

if (!$laporan) {
    echo json_encode([
        'success' => false,
        'message' => 'Laporan bukan tugas pegawai ini'
    ]);
    exit;
}

$trackingCollection->updateOne(
    [
        'laporan_id' => $laporanId
    ],
    [
        '$set' => [
            'laporan_id' => $laporanId,
            'judul_laporan' => $laporan['judul'],

            'pegawai_id' => $_SESSION['user']['id'],
            'pegawai_nama' => $_SESSION['user']['nama'],

            'latitude' => (float)$data['latitude'],
            'longitude' => (float)$data['longitude'],

            'latitude_tujuan' => (float)($laporan['latitude_tujuan'] ?? 0),
            'longitude_tujuan' => (float)($laporan['longitude_tujuan'] ?? 0),

            'status_tracking' => 'Menuju Lokasi',
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ],
    ['upsert' => true]
);

echo json_encode([
    'success' => true
]);
?>