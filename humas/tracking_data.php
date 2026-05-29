<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

cekRole(['Humas']);

header('Content-Type: application/json');

$laporanId = $_GET['laporan_id'] ?? '';

$filter = [];

if (!empty($laporanId)) {
    $filter['laporan_id'] = $laporanId;
}

$trackingList = $trackingCollection->find(
    $filter,
    ['sort' => ['updated_at' => -1]]
);

$data = [];

foreach ($trackingList as $track) {
    $laporan = null;

    if (!empty($track['laporan_id'])) {
        try {
            $laporan = $laporanCollection->findOne([
                '_id' => new MongoDB\BSON\ObjectId($track['laporan_id'])
            ]);
        } catch (Exception $e) {
            $laporan = null;
        }
    }

    $data[] = [
        'pegawai_id' => $track['pegawai_id'] ?? '',
        'pegawai_nama' => $track['pegawai_nama'] ?? '',
        'laporan_id' => $track['laporan_id'] ?? '',
        'judul_laporan' => $laporan['judul'] ?? 'Laporan tidak diketahui',

        'latitude' => $track['latitude'] ?? '',
        'longitude' => $track['longitude'] ?? '',

        'latitude_tujuan' => $track['latitude_tujuan'] ?? '',
        'longitude_tujuan' => $track['longitude_tujuan'] ?? '',

        'status_tracking' => $track['status_tracking'] ?? '',
        'updated_at' => $track['updated_at'] ?? ''
    ];
}

echo json_encode($data);
?>