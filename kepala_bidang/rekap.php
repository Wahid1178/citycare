<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Kepala Bidang']);

$totalLaporan = $laporanCollection->countDocuments();

$totalValid = $laporanCollection->countDocuments([
    'status_humas' => 'Valid'
]);

$totalDiproses = $laporanCollection->countDocuments([
    'status_lapangan' => 'Sedang Dikerjakan'
]);

$totalMenungguVerifikasi = $laporanCollection->countDocuments([
    'status_lapangan' => 'Menunggu Verifikasi Humas'
]);

$totalDiverifikasiHumas = $laporanCollection->countDocuments([
    'status_lapangan' => 'Diverifikasi Humas'
]);

$totalSelesaiFinal = $laporanCollection->countDocuments([
    'status_final' => 'Selesai Final'
]);

$totalPerbaikan = $laporanCollection->countDocuments([
    'status_final' => 'Perlu Perbaikan Pegawai'
]);

$totalDarurat = $laporanCollection->countDocuments([
    'prioritas' => 'Darurat'
]);

$rekapUtama = $laporanCollection->aggregate([
    [
        '$group' => [
            '_id' => null,
            'total_biaya' => ['$sum' => '$estimasi_biaya'],
            'rata_biaya' => ['$avg' => '$estimasi_biaya'],
            'total_titik' => ['$sum' => '$jumlah_titik'],
            'rata_progress' => ['$avg' => '$persentase_progress'],
            'rata_rating' => ['$avg' => '$rating']
        ]
    ]
])->toArray();

$summary = $rekapUtama[0] ?? [
    'total_biaya' => 0,
    'rata_biaya' => 0,
    'total_titik' => 0,
    'rata_progress' => 0,
    'rata_rating' => 0
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
            'diverifikasi_humas' => [
                '$sum' => [
                    '$cond' => [
                        ['$eq' => ['$status_lapangan', 'Diverifikasi Humas']],
                        1,
                        0
                    ]
                ]
            ],
            'selesai_final' => [
                '$sum' => [
                    '$cond' => [
                        ['$eq' => ['$status_final', 'Selesai Final']],
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
            'selesai_final' => -1,
            'total_tugas' => -1
        ]
    ]
])->toArray();

$rekapHumas = $laporanCollection->aggregate([
    [
        '$match' => [
            'humas_id' => [
                '$ne' => ''
            ]
        ]
    ],
    [
        '$group' => [
            '_id' => '$humas_nama',
            'total_ditangani' => ['$sum' => 1],
            'valid' => [
                '$sum' => [
                    '$cond' => [
                        ['$eq' => ['$status_humas', 'Valid']],
                        1,
                        0
                    ]
                ]
            ],
            'ditolak' => [
                '$sum' => [
                    '$cond' => [
                        ['$eq' => ['$status_humas', 'Ditolak']],
                        1,
                        0
                    ]
                ]
            ],
            'total_biaya' => ['$sum' => '$estimasi_biaya']
        ]
    ],
    [
        '$sort' => [
            'total_ditangani' => -1
        ]
    ]
])->toArray();

$laporanDarurat = $laporanCollection->find(
    [
        'prioritas' => 'Darurat'
    ],
    [
        'sort' => [
            'created_at' => -1
        ],
        'limit' => 10
    ]
);

$laporanPerluPerhatian = $laporanCollection->find(
    [
        '$or' => [
            ['status_final' => 'Perlu Perbaikan Pegawai'],
            ['status_lapangan' => 'Menunggu Verifikasi Humas'],
            ['prioritas' => 'Darurat']
        ]
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

<style>
.kepala-hero{
    background:linear-gradient(135deg,#0f172a,#1d4ed8,#14b8a6);
    color:white;
    padding:42px;
    border-radius:30px;
    margin-bottom:26px;
    box-shadow:0 24px 60px rgba(15,23,42,.22);
    position:relative;
    overflow:hidden;
}

.kepala-hero::after{
    content:"";
    position:absolute;
    width:230px;
    height:230px;
    border-radius:50%;
    right:-70px;
    top:-70px;
    background:rgba(255,255,255,.14);
}

.kepala-hero h1{
    margin:0 0 10px;
    font-size:36px;
}

.kepala-hero p{
    max-width:760px;
    line-height:1.7;
    color:#dbeafe;
}

.kepala-insight{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
    margin-bottom:25px;
}

.insight-box{
    background:white;
    border-radius:24px;
    padding:24px;
    box-shadow:0 14px 34px rgba(15,23,42,.08);
    border:1px solid #e2e8f0;
    transition:.25s;
}

.insight-box:hover{
    transform:translateY(-6px);
}

.insight-box span{
    font-size:13px;
    color:#64748b;
    font-weight:700;
}

.insight-box h3{
    margin:8px 0 0;
    font-size:24px;
    color:#0f172a;
}

.chart-card{
    min-height:360px;
}

@media(max-width:900px){
    .kepala-insight{
        grid-template-columns:1fr;
    }
}
</style>

<div class="kepala-hero">
    <h1>Rekap Kepala Bidang</h1>
    <p>
        Dashboard pengawasan strategis untuk memantau performa Humas,
        Pegawai Lapangan, status laporan, biaya penanganan, dan laporan prioritas.
    </p>
</div>

<div class="grid-4">
    <div class="card stat">
        <h3>Total Laporan</h3>
        <p><?= $totalLaporan ?></p>
    </div>

    <div class="card stat">
        <h3>Laporan Valid</h3>
        <p><?= $totalValid ?></p>
    </div>

    <div class="card stat">
        <h3>Sedang Diproses</h3>
        <p><?= $totalDiproses ?></p>
    </div>

    <div class="card stat">
        <h3>Selesai Final</h3>
        <p><?= $totalSelesaiFinal ?></p>
    </div>
</div>

<div class="grid-4">
    <div class="card stat">
        <h3>Menunggu Verifikasi</h3>
        <p><?= $totalMenungguVerifikasi ?></p>
    </div>

    <div class="card stat">
        <h3>Diverifikasi Humas</h3>
        <p><?= $totalDiverifikasiHumas ?></p>
    </div>

    <div class="card stat">
        <h3>Perlu Perbaikan</h3>
        <p><?= $totalPerbaikan ?></p>
    </div>

    <div class="card stat">
        <h3>Laporan Darurat</h3>
        <p><?= $totalDarurat ?></p>
    </div>
</div>

<div class="kepala-insight">
    <div class="insight-box">
        <span>Total Estimasi Biaya</span>
        <h3><?= rupiah($summary['total_biaya']) ?></h3>
    </div>

    <div class="insight-box">
        <span>Rata-rata Progress</span>
        <h3><?= number_format($summary['rata_progress'], 1) ?>%</h3>
    </div>

    <div class="insight-box">
        <span>Rata-rata Rating Masyarakat</span>
        <h3><?= number_format($summary['rata_rating'], 1) ?>/5</h3>
    </div>
</div>

<div class="grid-2">
    <div class="card chart-card">
        <h2>Status Laporan</h2>
        <canvas id="chartStatus"></canvas>
    </div>

    <div class="card chart-card">
        <h2>Top Wilayah Laporan</h2>
        <canvas id="chartWilayah"></canvas>
    </div>
</div>

<div class="grid-2">
    <div class="card chart-card">
        <h2>Rekap Kategori</h2>
        <canvas id="chartKategori"></canvas>
    </div>

    <div class="card chart-card">
        <h2>Performa Pegawai</h2>
        <canvas id="chartPegawai"></canvas>
    </div>
</div>

<div class="card">
    <h2>Laporan yang Perlu Perhatian</h2>

    <table>
        <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Wilayah</th>
            <th>Prioritas</th>
            <th>Status Lapangan</th>
            <th>Status Final</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($laporanPerluPerhatian as $laporan): ?>
        <tr>
            <td><?= safe($laporan['judul']) ?></td>
            <td><?= safe($laporan['kategori']) ?></td>
            <td><?= safe($laporan['wilayah']) ?></td>
            <td><?= safe($laporan['prioritas']) ?></td>
            <td><?= safe($laporan['status_lapangan']) ?></td>
            <td><?= safe($laporan['status_final']) ?></td>
            <td>
                <a href="/kepala_bidang/detail_laporan.php?id=<?= $laporan['_id'] ?>" class="btn orange">
                    Detail
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="grid-2">
    <div class="card">
        <h2>Rekap Humas</h2>

        <table>
            <tr>
                <th>Nama Humas</th>
                <th>Total Ditangani</th>
                <th>Valid</th>
                <th>Ditolak</th>
                <th>Total Biaya</th>
            </tr>

            <?php foreach ($rekapHumas as $item): ?>
            <tr>
                <td><?= safe($item['_id']) ?></td>
                <td><?= $item['total_ditangani'] ?></td>
                <td><?= $item['valid'] ?></td>
                <td><?= $item['ditolak'] ?></td>
                <td><?= rupiah($item['total_biaya']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="card">
        <h2>Ranking Pegawai Lapangan</h2>

        <table>
            <tr>
                <th>Pegawai</th>
                <th>Total</th>
                <th>Selesai</th>
                <th>Rata Progress</th>
            </tr>

            <?php foreach ($rekapPegawai as $item): ?>
            <tr>
                <td><?= safe($item['_id']) ?></td>
                <td><?= $item['total_tugas'] ?></td>
                <td><?= $item['selesai_final'] ?></td>
                <td><?= number_format($item['rata_progress'], 1) ?>%</td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<div class="card">
    <h2>Rekap Berdasarkan Kategori</h2>

    <table>
        <tr>
            <th>Kategori</th>
            <th>Jumlah Laporan</th>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const kategoriData = <?= json_encode($rekapKategori) ?>;
const wilayahData = <?= json_encode($rekapWilayah) ?>;
const pegawaiData = <?= json_encode($rekapPegawai) ?>;

new Chart(document.getElementById('chartStatus'), {
    type: 'doughnut',
    data: {
        labels: [
            'Valid',
            'Diproses',
            'Menunggu Verifikasi',
            'Selesai Final',
            'Perbaikan',
            'Darurat'
        ],
        datasets: [{
            data: [
                <?= $totalValid ?>,
                <?= $totalDiproses ?>,
                <?= $totalMenungguVerifikasi ?>,
                <?= $totalSelesaiFinal ?>,
                <?= $totalPerbaikan ?>,
                <?= $totalDarurat ?>
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true
    }
});

new Chart(document.getElementById('chartWilayah'), {
    type: 'bar',
    data: {
        labels: wilayahData.map(item => item._id),
        datasets: [{
            label: 'Jumlah Laporan',
            data: wilayahData.map(item => item.jumlah),
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

new Chart(document.getElementById('chartKategori'), {
    type: 'polarArea',
    data: {
        labels: kategoriData.map(item => item._id),
        datasets: [{
            data: kategoriData.map(item => item.jumlah)
        }]
    },
    options: {
        responsive: true
    }
});

new Chart(document.getElementById('chartPegawai'), {
    type: 'bar',
    data: {
        labels: pegawaiData.map(item => item._id),
        datasets: [
            {
                label: 'Total Tugas',
                data: pegawaiData.map(item => item.total_tugas),
                borderWidth: 1
            },
            {
                label: 'Selesai Final',
                data: pegawaiData.map(item => item.selesai_final),
                borderWidth: 1
            }
        ]
    },
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