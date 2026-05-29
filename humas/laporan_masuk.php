<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Humas']);

$laporan = $laporanCollection->find(
    [],
    [
        'sort' => ['created_at' => -1]
    ]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Laporan Masuk</h1>
    <p>
        Daftar laporan masyarakat
        yang masuk ke sistem.
    </p>
</div>

<div class="card">

    <table>

        <tr>
            <th>Judul</th>
            <th>Pelapor</th>
            <th>Kategori</th>
            <th>Wilayah</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($laporan as $item): ?>

        <tr>

            <td><?= safe($item['judul']) ?></td>

            <td><?= safe($item['nama_pelapor']) ?></td>

            <td><?= safe($item['kategori']) ?></td>

            <td><?= safe($item['wilayah']) ?></td>

            <td><?= safe($item['status_humas']) ?></td>

            <td>

                <a
                    href="/humas/detail_laporan.php?id=<?= $item['_id'] ?>"
                    class="btn dark"
                >
                    Detail
                </a>

            </td>

        </tr>

        <?php endforeach; ?>

    </table>

</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>