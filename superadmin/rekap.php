<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Super Admin']);

$totalLaporan = $laporanCollection->countDocuments();
$totalUser = $usersCollection->countDocuments();
$totalDarurat = $laporanCollection->countDocuments(['prioritas' => 'Darurat']);
$totalSelesaiFinal = $laporanCollection->countDocuments(['status_final' => 'Selesai Final']);

$rekapUtama = $laporanCollection->aggregate([
    ['$group' => [
        '_id' => null,
        'total_titik' => ['$sum' => '$jumlah_titik'],
        'rata_titik' => ['$avg' => '$jumlah_titik'],
        'max_titik' => ['$max' => '$jumlah_titik'],
        'total_biaya' => ['$sum' => '$estimasi_biaya'],
        'rata_rating' => ['$avg' => '$rating']
    ]]
])->toArray();

$summary = $rekapUtama[0] ?? [
    'total_titik' => 0,
    'rata_titik' => 0,
    'max_titik' => 0,
    'total_biaya' => 0,
    'rata_rating' => 0
];

$byKategori = $laporanCollection->aggregate([
    ['$group' => [
        '_id' => '$kategori',
        'jumlah' => ['$sum' => 1],
        'total_titik' => ['$sum' => '$jumlah_titik'],
        'total_biaya' => ['$sum' => '$estimasi_biaya']
    ]],
    ['$sort' => ['jumlah' => -1]]
])->toArray();

$byStatusFinal = $laporanCollection->aggregate([
    ['$group' => [
        '_id' => '$status_final',
        'jumlah' => ['$sum' => 1]
    ]]
])->toArray();

$byWilayah = $laporanCollection->aggregate([
    ['$group' => [
        '_id' => '$wilayah',
        'jumlah' => ['$sum' => 1]
    ]],
    ['$sort' => ['jumlah' => -1]],
    ['$limit' => 10]
])->toArray();

$byPegawai = $laporanCollection->aggregate([
    ['$match' => ['pegawai_id' => ['$ne' => '']]],
    ['$group' => [
        '_id' => '$pegawai_nama',
        'total_tugas' => ['$sum' => 1],
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
    ]],
    ['$sort' => ['selesai_final' => -1]]
])->toArray();

$laporanGreaterThan = $laporanCollection->find(
    ['jumlah_titik' => ['$gt' => 3]],
    ['sort' => ['jumlah_titik' => -1]]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="hero-dashboard">
    <h1>Rekapitulasi Super Admin</h1>
    <p>Analisis lengkap laporan fasilitas umum, bencana lingkungan, performa pegawai, dan status final sistem.</p>
</div>

<div class="grid-4">
    <div class="card stat"><h3>Total Laporan</h3><p><?= $totalLaporan ?></p></div>
    <div class="card stat"><h3>Total User</h3><p><?= $totalUser ?></p></div>
    <div class="card stat"><h3>Laporan Darurat</h3><p><?= $totalDarurat ?></p></div>
    <div class="card stat"><h3>Selesai Final</h3><p><?= $totalSelesaiFinal ?></p></div>
</div>

<div class="grid-4">
    <div class="card stat"><h3>Total Titik / SUM</h3><p><?= $summary['total_titik'] ?></p></div>
    <div class="card stat"><h3>Rata-rata Titik / AVG</h3><p><?= number_format($summary['rata_titik'], 2) ?></p></div>
    <div class="card stat"><h3>Titik Terbanyak / MAX</h3><p><?= $summary['max_titik'] ?></p></div>
    <div class="card stat"><h3>Total Biaya</h3><p><?= rupiah($summary['total_biaya']) ?></p></div>
</div>

<div class="grid-2">
    <div class="card">
        <h2>Grafik Laporan Berdasarkan Kategori</h2>
        <canvas id="chartKategori"></canvas>
    </div>

    <div class="card">
        <h2>Grafik Status Final</h2>
        <canvas id="chartFinal"></canvas>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <h2>Top 10 Wilayah</h2>
        <canvas id="chartWilayah"></canvas>
    </div>

    <div class="card">
        <h2>Performa Pegawai</h2>
        <canvas id="chartPegawai"></canvas>
    </div>
</div>

<div class="card">
    <h2>Rekap Berdasarkan Kategori</h2>
    <table>
        <tr>
            <th>Kategori</th>
            <th>Jumlah</th>
            <th>Total Titik</th>
            <th>Total Biaya</th>
        </tr>

        <?php foreach ($byKategori as $item): ?>
        <tr>
            <td><?= safe($item['_id']) ?></td>
            <td><?= $item['jumlah'] ?></td>
            <td><?= $item['total_titik'] ?></td>
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
            <th>Total Tugas</th>
            <th>Selesai Final</th>
            <th>Rata-rata Progress</th>
        </tr>

        <?php foreach ($byPegawai as $item): ?>
        <tr>
            <td><?= safe($item['_id']) ?></td>
            <td><?= $item['total_tugas'] ?></td>
            <td><?= $item['selesai_final'] ?></td>
            <td><?= number_format($item['rata_progress'], 1) ?>%</td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card">
    <h2>Laporan dengan Jumlah Titik Lebih dari 3</h2>
    <table>
        <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Wilayah</th>
            <th>Jumlah Titik</th>
            <th>Status Final</th>
        </tr>

        <?php foreach ($laporanGreaterThan as $laporan): ?>
        <tr>
            <td><?= safe($laporan['judul']) ?></td>
            <td><?= safe($laporan['kategori']) ?></td>
            <td><?= safe($laporan['wilayah']) ?></td>
            <td><?= safe($laporan['jumlah_titik']) ?></td>
            <td><?= safe($laporan['status_final']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const kategoriData = <?= json_encode($byKategori) ?>;
const finalData = <?= json_encode($byStatusFinal) ?>;
const wilayahData = <?= json_encode($byWilayah) ?>;
const pegawaiData = <?= json_encode($byPegawai) ?>;

new Chart(document.getElementById('chartKategori'), {
    type: 'bar',
    data: {
        labels: kategoriData.map(x => x._id),
        datasets: [{
            label: 'Jumlah Laporan',
            data: kategoriData.map(x => x.jumlah),
            borderWidth: 1
        }]
    }
});

new Chart(document.getElementById('chartFinal'), {
    type: 'doughnut',
    data: {
        labels: finalData.map(x => x._id),
        datasets: [{
            data: finalData.map(x => x.jumlah)
        }]
    }
});

new Chart(document.getElementById('chartWilayah'), {
    type: 'polarArea',
    data: {
        labels: wilayahData.map(x => x._id),
        datasets: [{
            data: wilayahData.map(x => x.jumlah)
        }]
    }
});

new Chart(document.getElementById('chartPegawai'), {
    type: 'bar',
    data: {
        labels: pegawaiData.map(x => x._id),
        datasets: [
            {
                label: 'Total Tugas',
                data: pegawaiData.map(x => x.total_tugas),
                borderWidth: 1
            },
            {
                label: 'Selesai Final',
                data: pegawaiData.map(x => x.selesai_final),
                borderWidth: 1
            }
        ]
    }
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>