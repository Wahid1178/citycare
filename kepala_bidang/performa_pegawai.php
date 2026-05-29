<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Kepala Bidang']);

$performa = $laporanCollection->aggregate([
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
            'selesai_lapangan' => [
                '$sum' => [
                    '$cond' => [
                        ['$eq' => ['$status_lapangan', 'Selesai Dikerjakan']],
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

$chartPegawai = [];

foreach ($performa as $item) {
    $chartPegawai[] = [
        'nama' => $item['_id'],
        'total' => $item['total_tugas'],
        'selesai' => $item['selesai_final']
    ];
}

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Performa Pegawai Lapangan</h1>
    <p>
        Evaluasi kinerja pegawai berdasarkan jumlah tugas,
        progress rata-rata, dan laporan selesai final.
    </p>
</div>

<div class="card">
    <h2>Grafik Performa Pegawai</h2>
    <canvas id="chartPegawai" height="110"></canvas>
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
        </tr>

        <?php foreach ($performa as $item): ?>
        <tr>
            <td><?= safe($item['_id']) ?></td>
            <td><?= $item['total_tugas'] ?></td>
            <td><?= $item['selesai_lapangan'] ?></td>
            <td><?= $item['selesai_final'] ?></td>
            <td><?= number_format($item['rata_progress'], 1) ?>%</td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const dataPegawai = <?= json_encode($chartPegawai) ?>;

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
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>