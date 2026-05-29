<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Humas']);

$keyword = $_GET['keyword'] ?? '';
$status = $_GET['status'] ?? '';

$filter = [
    '$or' => [
        ['status_humas' => 'Valid'],
        ['status_lapangan' => 'Menunggu Verifikasi Humas']
    ]
];

if (!empty($keyword)) {
    $filter['$and'] = [
        [
            '$or' => [
                ['judul' => new MongoDB\BSON\Regex($keyword, 'i')],
                ['kategori' => new MongoDB\BSON\Regex($keyword, 'i')],
                ['wilayah' => new MongoDB\BSON\Regex($keyword, 'i')],
                ['nama_pelapor' => new MongoDB\BSON\Regex($keyword, 'i')],
                ['pegawai_nama' => new MongoDB\BSON\Regex($keyword, 'i')]
            ]
        ]
    ];
}

if (!empty($status)) {
    $filter['status_lapangan'] = $status;
}

$laporanList = $laporanCollection->find(
    $filter,
    ['sort' => ['updated_at' => -1]]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Assign & Verifikasi Pegawai</h1>
    <p>
        Humas dapat menugaskan pegawai lapangan sekaligus memverifikasi pekerjaan
        yang sudah dilaporkan selesai oleh pegawai.
    </p>
</div>

<div class="card">
    <form method="GET" class="filter-grid">
        <input 
            type="text" 
            name="keyword" 
            placeholder="Cari laporan, wilayah, kategori, pelapor, atau pegawai..."
            value="<?= safe($keyword) ?>"
        >

        <select name="status">
            <option value="">Semua Status Lapangan</option>
            <option value="Belum Ditugaskan" <?= $status == 'Belum Ditugaskan' ? 'selected' : '' ?>>Belum Ditugaskan</option>
            <option value="Sedang Dikerjakan" <?= $status == 'Sedang Dikerjakan' ? 'selected' : '' ?>>Sedang Dikerjakan</option>
            <option value="Menunggu Verifikasi Humas" <?= $status == 'Menunggu Verifikasi Humas' ? 'selected' : '' ?>>Menunggu Verifikasi Humas</option>
            <option value="Diverifikasi Humas" <?= $status == 'Diverifikasi Humas' ? 'selected' : '' ?>>Diverifikasi Humas</option>
        </select>

        <button type="submit">Filter</button>
        <a href="/humas/assign_pegawai.php" class="btn gray">Reset</a>
    </form>
</div>

<div class="card">
    <table>
        <tr>
            <th>Judul</th>
            <th>Pelapor</th>
            <th>Kategori</th>
            <th>Wilayah</th>
            <th>Pegawai</th>
            <th>Status Lapangan</th>
            <th>Progress</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($laporanList as $laporan): ?>
        <tr>
            <td><?= safe($laporan['judul']) ?></td>
            <td><?= safe($laporan['nama_pelapor']) ?></td>
            <td><?= safe($laporan['kategori']) ?></td>
            <td><?= safe($laporan['wilayah']) ?></td>
            <td><?= safe($laporan['pegawai_nama'] ?: '-') ?></td>
            <td><?= safe($laporan['status_lapangan']) ?></td>
            <td>
                <div class="progress-bar">
                    <div 
                        class="progress-fill" 
                        style="width: <?= (int)($laporan['persentase_progress'] ?? 0) ?>%;"
                    ></div>
                </div>
                <small><?= (int)($laporan['persentase_progress'] ?? 0) ?>%</small>
            </td>
            <td>
                <a class="btn orange" href="/humas/detail_laporan.php?id=<?= $laporan['_id'] ?>">
                    Kelola
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>