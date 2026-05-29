<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Humas']);

$humasId = $_SESSION['user']['id'];
$humasNama = $_SESSION['user']['nama'];

$totalLaporan = $laporanCollection->countDocuments();

$menungguValidasi = $laporanCollection->countDocuments([
    'status_humas' => 'Menunggu Validasi'
]);

$valid = $laporanCollection->countDocuments([
    'status_humas' => 'Valid'
]);

$ditolak = $laporanCollection->countDocuments([
    'status_humas' => 'Ditolak'
]);

$sedangDikerjakan = $laporanCollection->countDocuments([
    'status_lapangan' => 'Sedang Dikerjakan'
]);

$menungguVerifikasiHumas = $laporanCollection->countDocuments([
    'status_lapangan' => 'Menunggu Verifikasi Humas'
]);

$diverifikasiHumas = $laporanCollection->countDocuments([
    'status_lapangan' => 'Diverifikasi Humas'
]);

$totalBiaya = $laporanCollection->aggregate([
    [
        '$group' => [
            '_id' => null,
            'total_estimasi_biaya' => ['$sum' => '$estimasi_biaya'],
            'rata_estimasi_biaya' => ['$avg' => '$estimasi_biaya'],
            'total_titik' => ['$sum' => '$jumlah_titik'],
            'rata_progress' => ['$avg' => '$persentase_progress']
        ]
    ]
])->toArray();

$summary = $totalBiaya[0] ?? [
    'total_estimasi_biaya' => 0,
    'rata_estimasi_biaya' => 0,
    'total_titik' => 0,
    'rata_progress' => 0
];

$rekapKategori = $laporanCollection->aggregate([
    [
        '$group' => [
            '_id' => '$kategori',
            'jumlah' => ['$sum' => 1],
            'total_biaya' => ['$sum' => '$estimasi_biaya'],
            'rata_progress' => ['$avg' => '$persentase_progress']
        ]
    ],
    [
        '$sort' => [
            'jumlah' => -1
        ]
    ]
])->toArray();

$rekapWilayah = $laporanCollection->aggregate([
    [
        '$group' => [
            '_id' => '$wilayah',
            'jumlah' => ['$sum' => 1],
            'total_biaya' => ['$sum' => '$estimasi_biaya']
        ]
    ],
    [
        '$sort' => [
            'jumlah' => -1
        ]
    ],
    [
        '$limit' => 10
    ]
])->toArray();

$rekapPegawai = $laporanCollection->aggregate([
    [
        '$match' => [
            'pegawai_id' => [
                '$ne' => ''
            ]
        ]
    ],
    [
        '$group' => [
            '_id' => '$pegawai_nama',
            'total_tugas' => ['$sum' => 1],
            'sedang_dikerjakan' => [
                '$sum' => [
                    '$cond' => [
                        ['$eq' => ['$status_lapangan', 'Sedang Dikerjakan']],
                        1,
                        0
                    ]
                ]
            ],
            'menunggu_verifikasi' => [
                '$sum' => [
                    '$cond' => [
                        ['$eq' => ['$status_lapangan', 'Menunggu Verifikasi Humas']],
                        1,
                        0
                    ]
                ]
            ],
            'diverifikasi' => [
                '$sum' => [
                    '$cond' => [
                        ['$eq' => ['$status_lapangan', 'Diverifikasi Humas']],
                        1,
                        0
                    ]
                ]
            ],
            'rata_progress' => ['$avg' => '$persentase_progress']
        ]
    ],
    [
        '$sort' => [
            'total_tugas' => -1
        ]
    ]
])->toArray();

$laporanButuhVerifikasi = $laporanCollection->find(
    [
        'status_lapangan' => 'Menunggu Verifikasi Humas'
    ],
    [
        'sort' => [
            'updated_at' => -1
        ],
        'limit' => 10
    ]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="hero-dashboard">
    <h1>Rekap Humas</h1>
    <p>
        Ringkasan validasi laporan, assign pegawai, estimasi biaya,
        dan verifikasi pekerjaan lapangan.
    </p>
</div>

<div class="grid-4">
    <div class="card stat">
        <h3>Total Laporan</h3>
        <p><?= $totalLaporan ?></p>
    </div>

    <div class="card stat">
        <h3>Menunggu Validasi</h3>
        <p><?= $menungguValidasi ?></p>
    </div>

    <div class="card stat">
        <h3>Laporan Valid</h3>
        <p><?= $valid ?></p>
    </div>

    <div class="card stat">
        <h3>Ditolak</h3>
        <p><?= $ditolak ?></p>
    </div>
</div>

<div class="grid-4">
    <div class="card stat">
        <h3>Sedang Dikerjakan</h3>
        <p><?= $sedangDikerjakan ?></p>
    </div>

    <div class="card stat">
        <h3>Menunggu Verifikasi</h3>
        <p><?= $menungguVerifikasiHumas ?></p>
    </div>

    <div class="card stat">
        <h3>Diverifikasi Humas</h3>
        <p><?= $diverifikasiHumas ?></p>
    </div>

    <div class="card stat">
        <h3>Rata-rata Progress</h3>
        <p><?= number_format($summary['rata_progress'], 1) ?>%</p>
    </div>
</div>

<div class="grid-4">
    <div class="card stat">
        <h3>Total Estimasi Biaya</h3>
        <p><?= rupiah($summary['total_estimasi_biaya']) ?></p>
    </div>

    <div class="card stat">
        <h3>Rata-rata Biaya</h3>
        <p><?= rupiah($summary['rata_estimasi_biaya']) ?></p>
    </div>

    <div class="card stat">
        <h3>Total Titik Masalah</h3>
        <p><?= $summary['total_titik'] ?></p>
    </div>

    <div class="card stat">
        <h3>Humas Aktif</h3>
        <p><?= safe($humasNama) ?></p>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <h2>Grafik Status Humas</h2>
        <canvas id="chartStatusHumas"></canvas>
    </div>

    <div class="card">
        <h2>Grafik Status Lapangan</h2>
        <canvas id="chartStatusLapangan"></canvas>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <h2>Rekap Kategori</h2>

        <table>
            <tr>
                <th>Kategori</th>
                <th>Jumlah</th>
                <th>Total Biaya</th>
                <th>Rata Progress</th>
            </tr>

            <?php foreach ($rekapKategori as $item): ?>
            <tr>
                <td><?= safe($item['_id']) ?></td>
                <td><?= $item['jumlah'] ?></td>
                <td><?= rupiah($item['total_biaya']) ?></td>
                <td><?= number_format($item['rata_progress'], 1) ?>%</td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="card">
        <h2>Top Wilayah Laporan</h2>

        <table>
            <tr>
                <th>Wilayah</th>
                <th>Jumlah</th>
                <th>Total Biaya</th>
            </tr>

            <?php foreach ($rekapWilayah as $item): ?>
            <tr>
                <td><?= safe($item['_id']) ?></td>
                <td><?= $item['jumlah'] ?></td>
                <td><?= rupiah($item['total_biaya']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<div class="card">
    <h2>Performa Pegawai Berdasarkan Tugas dari Humas</h2>

    <table>
        <tr>
            <th>Pegawai</th>
            <th>Total Tugas</th>
            <th>Sedang Dikerjakan</th>
            <th>Menunggu Verifikasi</th>
            <th>Diverifikasi Humas</th>
            <th>Rata Progress</th>
        </tr>

        <?php foreach ($rekapPegawai as $item): ?>
        <tr>
            <td><?= safe($item['_id']) ?></td>
            <td><?= $item['total_tugas'] ?></td>
            <td><?= $item['sedang_dikerjakan'] ?></td>
            <td><?= $item['menunggu_verifikasi'] ?></td>
            <td><?= $item['diverifikasi'] ?></td>
            <td><?= number_format($item['rata_progress'], 1) ?>%</td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card">
    <h2>Laporan yang Menunggu Verifikasi Humas</h2>

    <table>
        <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Wilayah</th>
            <th>Pegawai</th>
            <th>Progress</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($laporanButuhVerifikasi as $laporan): ?>
        <tr>
            <td><?= safe($laporan['judul']) ?></td>
            <td><?= safe($laporan['kategori']) ?></td>
            <td><?= safe($laporan['wilayah']) ?></td>
            <td><?= safe($laporan['pegawai_nama'] ?: '-') ?></td>
            <td><?= (int)($laporan['persentase_progress'] ?? 0) ?>%</td>
            <td>
                <a 
                    href="/humas/detail_laporan.php?id=<?= $laporan['_id'] ?>" 
                    class="btn orange"
                >
                    Verifikasi
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const statusHumasData = {
    labels: ['Menunggu Validasi', 'Valid', 'Ditolak'],
    datasets: [{
        data: [
            <?= $menungguValidasi ?>,
            <?= $valid ?>,
            <?= $ditolak ?>
        ],
        borderWidth: 1
    }]
};

new Chart(document.getElementById('chartStatusHumas'), {
    type: 'doughnut',
    data: statusHumasData,
    options: {
        responsive: true
    }
});

const statusLapanganData = {
    labels: ['Sedang Dikerjakan', 'Menunggu Verifikasi', 'Diverifikasi Humas'],
    datasets: [{
        data: [
            <?= $sedangDikerjakan ?>,
            <?= $menungguVerifikasiHumas ?>,
            <?= $diverifikasiHumas ?>
        ],
        borderWidth: 1
    }]
};

new Chart(document.getElementById('chartStatusLapangan'), {
    type: 'bar',
    data: statusLapanganData,
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>