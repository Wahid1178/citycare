<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Kepala Bidang']);

$performa = $laporanCollection->aggregate([
    [
        '$match' => [
            'pegawai_id' => [
                '$exists' => true,
                '$ne' => ''
            ]
        ]
    ],
    [
        '$group' => [
            '_id' => '$pegawai_id',

            'pegawai_nama' => [
                '$first' => '$pegawai_nama'
            ],

            'total_tugas' => [
                '$sum' => 1
            ],

            'selesai_lapangan' => [
                '$sum' => [
                    '$cond' => [
                        [
                            '$in' => [
                                '$status_lapangan',
                                [
                                    'Selesai Dikerjakan',
                                    'Diverifikasi Humas',
                                    'Selesai Final',
                                    'Selesai'
                                ]
                            ]
                        ],
                        1,
                        0
                    ]
                ]
            ],

            'selesai_final' => [
                '$sum' => [
                    '$cond' => [
                        [
                            '$in' => [
                                '$status_final',
                                [
                                    'Selesai Final',
                                    'Selesai'
                                ]
                            ]
                        ],
                        1,
                        0
                    ]
                ]
            ],

            'rata_progress' => [
                '$avg' => '$persentase_progress'
            ],

            'jumlah_rating' => [
                '$sum' => [
                    '$cond' => [
                        [
                            '$and' => [
                                [
                                    '$eq' => [
                                        '$sudah_rating',
                                        true
                                    ]
                                ],
                                [
                                    '$gt' => [
                                        '$rating_pegawai',
                                        0
                                    ]
                                ]
                            ]
                        ],
                        1,
                        0
                    ]
                ]
            ],

            'rata_rating' => [
                '$avg' => [
                    '$cond' => [
                        [
                            '$and' => [
                                [
                                    '$eq' => [
                                        '$sudah_rating',
                                        true
                                    ]
                                ],
                                [
                                    '$gt' => [
                                        '$rating_pegawai',
                                        0
                                    ]
                                ]
                            ]
                        ],
                        '$rating_pegawai',
                        null
                    ]
                ]
            ]
        ]
    ],
    [
        '$sort' => [
            'rata_rating' => -1,
            'selesai_final' => -1,
            'total_tugas' => -1
        ]
    ]
])->toArray();

$chartPegawai = [];

foreach ($performa as $item) {
    $chartPegawai[] = [
        'nama' => $item['pegawai_nama'] ?? '-',
        'total' => (int)($item['total_tugas'] ?? 0),
        'selesai' => (int)($item['selesai_final'] ?? 0),
        'rating' => isset($item['rata_rating'])
            ? round((float)$item['rata_rating'], 1)
            : 0
    ];
}

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Performa Pegawai Lapangan</h1>
    <p>
        Evaluasi kinerja pegawai berdasarkan jumlah tugas,
        progress rata-rata, laporan selesai final, dan rating masyarakat.
    </p>
</div>

<div class="card">
    <h2>Grafik Rating Pegawai</h2>
    <canvas id="chartRating" height="120"></canvas>
</div>

<div class="card">
    <h2>Grafik Kinerja Pegawai</h2>
    <canvas id="chartPegawai" height="120"></canvas>
</div>

<div class="card">
    <h2>Ranking Pegawai</h2>

    <table>
        <tr>
            <th>Nama Pegawai</th>
            <th>Total Tugas</th>
            <th>Selesai Lapangan</th>
            <th>Selesai Final</th>
            <th>Rata-rata Progress</th>
            <th>Jumlah Rating</th>
            <th>Rating Masyarakat</th>
            <th>Status Kinerja</th>
        </tr>

        <?php foreach ($performa as $item): ?>
            <?php
            $rating = (float)($item['rata_rating'] ?? 0);

            if ($rating >= 4.5) {
                $statusKinerja = 'Sangat Baik';
            } elseif ($rating >= 3.5) {
                $statusKinerja = 'Baik';
            } elseif ($rating >= 2.5) {
                $statusKinerja = 'Cukup';
            } elseif ($rating > 0) {
                $statusKinerja = 'Kurang';
            } else {
                $statusKinerja = '-';
            }
            ?>

            <tr>
                <td><?= safe($item['pegawai_nama'] ?? '-') ?></td>

                <td><?= (int)($item['total_tugas'] ?? 0) ?></td>

                <td><?= (int)($item['selesai_lapangan'] ?? 0) ?></td>

                <td><?= (int)($item['selesai_final'] ?? 0) ?></td>

                <td>
                    <?= number_format((float)($item['rata_progress'] ?? 0), 1) ?>%
                </td>

                <td>
                    <?= (int)($item['jumlah_rating'] ?? 0) ?>
                </td>

                <td>
                    <?php if (!empty($item['rata_rating'])): ?>
                        ⭐ <?= number_format((float)$item['rata_rating'], 1) ?> / 5
                    <?php else: ?>
                        Belum ada rating
                    <?php endif; ?>
                </td>

                <td>
                    <?= $statusKinerja ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const dataPegawai = <?= json_encode($chartPegawai) ?>;

// ==========================
// GRAFIK RATING PEGAWAI
// ==========================
new Chart(document.getElementById('chartRating'), {
    type: 'bar',
    data: {
        labels: dataPegawai.map(item => item.nama),
        datasets: [
            {
                label: 'Rating Masyarakat',
                data: dataPegawai.map(item => item.rating),
                borderWidth: 1
            }
        ]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        scales: {
            x: {
                beginAtZero: true,
                max: 5
            }
        }
    }
});

// ==========================
// GRAFIK KINERJA PEGAWAI
// ==========================
new Chart(document.getElementById('chartPegawai'), {
    type: 'bar',
    data: {
        labels: dataPegawai.map(item => item.nama),
        datasets: [
            {
                label: 'Total Tugas',
                data: dataPegawai.map(item => item.total),
                borderWidth: 1
            },
            {
                label: 'Selesai Final',
                data: dataPegawai.map(item => item.selesai),
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>