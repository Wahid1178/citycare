<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Kepala Bidang']);

$id = new MongoDB\BSON\ObjectId($_GET['id']);

$laporan = $laporanCollection->findOne([
    '_id' => $id
]);

if (!$laporan) {
    die("Laporan tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $approval = $_POST['approval'] == 'Setuju';

    $laporanCollection->updateOne(
        ['_id' => $id],
        ['$set' => [
            'kepala_bidang_approval' => $approval,
            'catatan_kepala_bidang' => $_POST['catatan_kepala_bidang'],
            'updated_at' => date('Y-m-d H:i:s')
        ]]
    );

    $activityCollection->insertOne([
        'laporan_id' => (string)$id,
        'user_nama' => $_SESSION['user']['nama'],
        'role' => $_SESSION['user']['role'],
        'aktivitas' => $approval
            ? 'Kepala Bidang menyetujui hasil monitoring laporan.'
            : 'Kepala Bidang meminta evaluasi/perbaikan ulang.',
        'created_at' => date('Y-m-d H:i:s')
    ]);

    if (!empty($laporan['user_id'])) {
        $notifCollection->insertOne([
            'user_id' => $laporan['user_id'],
            'judul' => 'Update Kepala Bidang',
            'pesan' => 'Laporan "' . $laporan['judul'] . '" telah ditinjau oleh Kepala Bidang.',
            'dibaca' => false,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    header("Location: /kepala_bidang/monitoring.php");
    exit;
}

$progressList = $progressCollection->find(
    ['laporan_id' => (string)$id],
    ['sort' => ['created_at' => -1]]
);

$riwayat = $activityCollection->find(
    ['laporan_id' => (string)$id],
    ['sort' => ['created_at' => -1]]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Detail Monitoring Laporan</h1>
    <p>
        Kepala Bidang dapat melihat detail laporan,
        progress pegawai, dan memberikan catatan evaluasi.
    </p>
</div>

<div class="grid-2">
    <div class="card">
        <h2><?= safe($laporan['judul']) ?></h2>

        <div class="timeline">
            <?php foreach (['Menunggu Validasi','Valid','Sedang Dikerjakan','Selesai Dikerjakan','Selesai Final'] as $step): ?>
                <div class="timeline-item 
                    <?=
                        $laporan['status_humas'] == $step ||
                        $laporan['status_lapangan'] == $step ||
                        $laporan['status_final'] == $step
                        ? 'timeline-active'
                        : ''
                    ?>
                ">
                    <?= safe($step) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <p><b>Pelapor:</b><br><?= safe($laporan['nama_pelapor']) ?></p>
        <p><b>Kategori:</b><br><?= safe($laporan['kategori']) ?></p>
        <p><b>Wilayah:</b><br><?= safe($laporan['wilayah']) ?></p>
        <p><b>Alamat:</b><br><?= safe($laporan['alamat_lokasi']) ?></p>
        <p><b>Status Humas:</b><br><?= safe($laporan['status_humas']) ?></p>
        <p><b>Status Lapangan:</b><br><?= safe($laporan['status_lapangan']) ?></p>
        <p><b>Status Final:</b><br><?= safe($laporan['status_final']) ?></p>
        <p><b>Humas:</b><br><?= safe($laporan['humas_nama'] ?: '-') ?></p>
        <p><b>Pegawai:</b><br><?= safe($laporan['pegawai_nama'] ?: '-') ?></p>
        <p><b>Estimasi Biaya:</b><br><?= rupiah($laporan['estimasi_biaya']) ?></p>

        <p><b>Progress:</b></p>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= (int)$laporan['persentase_progress'] ?>%;"></div>
        </div>
        <small><?= (int)$laporan['persentase_progress'] ?>%</small>

        <p style="margin-top:20px;"><b>Deskripsi:</b><br><?= nl2br(safe($laporan['deskripsi'])) ?></p>

        <?php if (!empty($laporan['foto_laporan'])): ?>
            <p><b>Foto Awal:</b></p>
            <img 
                src="/uploads/<?= safe($laporan['foto_laporan']) ?>"
                style="width:100%;max-height:320px;object-fit:cover;border-radius:18px;"
            >
        <?php endif; ?>

        <?php if (!empty($laporan['foto_selesai'])): ?>
            <p><b>Foto Hasil Pekerjaan:</b></p>
            <img 
                src="/uploads/<?= safe($laporan['foto_selesai']) ?>"
                style="width:100%;max-height:320px;object-fit:cover;border-radius:18px;"
            >
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Evaluasi Kepala Bidang</h2>

        <form method="POST">
            <label>Approval</label>
            <select name="approval">
                <option value="Setuju" <?= ($laporan['kepala_bidang_approval'] ?? false) ? 'selected' : '' ?>>
                    Setujui
                </option>
                <option value="Tidak Setuju" <?= !($laporan['kepala_bidang_approval'] ?? false) ? 'selected' : '' ?>>
                    Perlu Evaluasi
                </option>
            </select>

            <label>Catatan Kepala Bidang</label>
            <textarea name="catatan_kepala_bidang" rows="6"><?= safe($laporan['catatan_kepala_bidang'] ?? '') ?></textarea>

            <button type="submit">Simpan Evaluasi</button>
            <a href="/kepala_bidang/monitoring.php" class="btn gray">Kembali</a>
        </form>

        <div class="info" style="margin-top:20px;">
            <b>Catatan:</b><br>
            Approval Kepala Bidang digunakan sebagai pengawasan internal terhadap kualitas pekerjaan pegawai dan validasi Humas.
        </div>
    </div>
</div>

<div class="card">
    <h2>Riwayat Progress Pegawai</h2>

    <table>
        <tr>
            <th>Waktu</th>
            <th>Pegawai</th>
            <th>Progress</th>
            <th>Keterangan</th>
            <th>Foto</th>
        </tr>

        <?php foreach ($progressList as $progress): ?>
        <tr>
            <td><?= safe($progress['created_at']) ?></td>
            <td><?= safe($progress['pegawai_nama']) ?></td>
            <td><?= safe($progress['persentase']) ?>%</td>
            <td><?= safe($progress['keterangan']) ?></td>
            <td>
                <?php if (!empty($progress['foto_progress'])): ?>
                    <a href="/uploads/<?= safe($progress['foto_progress']) ?>" target="_blank" class="btn gray">
                        Lihat Foto
                    </a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card">
    <h2>Riwayat Aktivitas Laporan</h2>

    <table>
        <tr>
            <th>Waktu</th>
            <th>User</th>
            <th>Role</th>
            <th>Aktivitas</th>
        </tr>

        <?php foreach ($riwayat as $item): ?>
        <tr>
            <td><?= safe($item['created_at']) ?></td>
            <td><?= safe($item['user_nama']) ?></td>
            <td><?= safe($item['role']) ?></td>
            <td><?= safe($item['aktivitas']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>