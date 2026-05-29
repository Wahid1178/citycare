<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Pegawai Lapangan']);

$pegawaiId = $_SESSION['user']['id'];

$status = $_GET['status'] ?? '';
$keyword = $_GET['keyword'] ?? '';

$filter = [
    'pegawai_id' => $pegawaiId
];

if (!empty($status)) {
    $filter['status_lapangan'] = $status;
}

if (!empty($keyword)) {
    $filter['$or'] = [
        ['judul' => new MongoDB\BSON\Regex($keyword, 'i')],
        ['kategori' => new MongoDB\BSON\Regex($keyword, 'i')],
        ['wilayah' => new MongoDB\BSON\Regex($keyword, 'i')]
    ];
}

$tugas = $laporanCollection->find(
    $filter,
    [
        'sort' => ['updated_at' => -1]
    ]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">

    <h1>Tugas Lapangan Saya</h1>

    <p>
        Daftar seluruh laporan yang telah ditugaskan kepada Anda.
    </p>

</div>

<div class="card">

    <form method="GET" class="filter-grid">

        <input
            type="text"
            name="keyword"
            placeholder="Cari tugas..."
            value="<?= safe($keyword) ?>"
        >

        <select name="status">

            <option value="">
                Semua Status
            </option>

            <?php foreach ([
                'Sedang Dikerjakan',
                'Selesai Dikerjakan'
            ] as $s): ?>

                <option
                    value="<?= $s ?>"
                    <?= $status == $s ? 'selected' : '' ?>
                >
                    <?= $s ?>
                </option>

            <?php endforeach; ?>

        </select>

        <button type="submit">
            Filter
        </button>

        <a
            href="/pegawai/tugas_saya.php"
            class="btn gray"
        >
            Reset
        </a>

    </form>

</div>

<div class="card">

    <table>

        <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Wilayah</th>
            <th>Progress</th>
            <th>Status Final</th>
            <th>Aksi </th>
           
        </tr>

        <?php foreach ($tugas as $laporan): ?>

        <tr>

            <td><?= safe($laporan['judul']) ?></td>

            <td><?= safe($laporan['kategori']) ?></td>

            <td><?= safe($laporan['wilayah']) ?></td>

            <td>

                <div class="progress-bar">

                    <div
                        class="progress-fill"
                        style="width:<?= (int)$laporan['persentase_progress'] ?>%;"
                    ></div>

                </div>

                <small>
                    <?= (int)$laporan['persentase_progress'] ?>%
                </small>

            </td>

            <td>
                <?= safe($laporan['status_final']) ?>
            </td>

            <td>

                <a
                    href="/pegawai/update_progress.php?id=<?= $laporan['_id'] ?>"
                    class="btn orange"
                >
                    Update Progress
                </a>
                <a href="/pegawai/start_tracking.php?id=<?= $laporan['_id'] ?>"class="btn green">Mulai Tracking</a>
            </td>

        </tr>

        <?php endforeach; ?>

    </table>

</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>