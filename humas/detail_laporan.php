<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Humas']);

if (empty($_GET['id'])) {
    die("ID laporan tidak ditemukan.");
}

try {
    $id = new MongoDB\BSON\ObjectId($_GET['id']);
} catch (Exception $e) {
    die("ID laporan tidak valid.");
}

$laporan = $laporanCollection->findOne(['_id' => $id]);

if (!$laporan) {
    die("Laporan tidak ditemukan.");
}

$pegawaiList = $usersCollection->find([
    'role' => 'Pegawai Lapangan',
    'status' => 'Aktif'
]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $aksi = $_POST['aksi'] ?? '';

    // =========================
    // AKSI ASSIGN / VALIDASI
    // =========================
    if ($aksi == 'assign') {

        // Jika laporan sudah valid dan sudah punya pegawai, jangan izinkan validasi/assign diubah lagi
        if (
            ($laporan['status_humas'] ?? '') == 'Valid' &&
            !empty($laporan['pegawai_id'])
        ) {
            die("Laporan sudah divalidasi dan ditugaskan, sehingga tidak dapat diubah lagi.");
        }

        $statusHumas = $_POST['status_humas'] ?? 'Menunggu Validasi';
        $pegawaiId = $_POST['pegawai_id'] ?? '';
        $pegawaiNama = '';

        if (!empty($pegawaiId)) {
            $pegawai = $usersCollection->findOne([
                '_id' => new MongoDB\BSON\ObjectId($pegawaiId)
            ]);

            $pegawaiNama = $pegawai['nama'] ?? '';
        }

        if ($statusHumas == 'Ditolak') {
            $statusUmum = 'Ditolak';
            $statusLapangan = 'Tidak Ditugaskan';
            $statusFinal = 'Ditolak';
        } elseif ($statusHumas == 'Valid') {
            $statusUmum = 'Diproses';
            $statusLapangan = !empty($pegawaiId)
                ? 'Sedang Dikerjakan'
                : 'Belum Ditugaskan';
            $statusFinal = 'Belum Selesai';
        } else {
            $statusUmum = 'Menunggu Validasi';
            $statusLapangan = 'Belum Ditugaskan';
            $statusFinal = 'Menunggu Validasi';
        }

        $laporanCollection->updateOne(
            ['_id' => $id],
            [
                '$set' => [
                    'status' => $statusUmum,
                    'status_humas' => $statusHumas,
                    'status_lapangan' => $statusLapangan,
                    'status_final' => $statusFinal,

                    'pegawai_id' => $pegawaiId,
                    'pegawai_nama' => $pegawaiNama,

                    'estimasi_biaya' => (int)($_POST['estimasi_biaya'] ?? 0),
                    'catatan_humas' => $_POST['catatan_humas'] ?? '',

                    'humas_id' => $_SESSION['user']['id'],
                    'humas_nama' => $_SESSION['user']['nama'],

                    'locked_by_humas' => $statusHumas == 'Valid',
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ]
        );

        if ($statusHumas == 'Valid' && !empty($pegawaiId)) {

            $trackingCollection->updateOne(
                [
                    'laporan_id' => (string)$id
                ],
                [
                    '$set' => [
                        'laporan_id' => (string)$id,
                        'judul_laporan' => $laporan['judul'],
                        'pegawai_id' => $pegawaiId,
                        'pegawai_nama' => $pegawaiNama,

                        'latitude' => '',
                        'longitude' => '',

                        'latitude_tujuan' => $laporan['latitude_tujuan'] ?? '',
                        'longitude_tujuan' => $laporan['longitude_tujuan'] ?? '',

                        'status_tracking' => 'Menunggu Pegawai Memulai Perjalanan',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]
                ],
                ['upsert' => true]
            );

            $notifCollection->insertOne([
                'user_id' => $pegawaiId,
                'judul' => 'Tugas Baru',
                'pesan' => 'Anda mendapat tugas baru untuk laporan "' . $laporan['judul'] . '".',
                'dibaca' => false,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        $notifCollection->insertOne([
            'user_id' => $laporan['user_id'],
            'judul' => 'Update Validasi Laporan',
            'pesan' => 'Laporan "' . $laporan['judul'] . '" sekarang berstatus ' . $statusFinal . '.',
            'dibaca' => false,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        header("Location: /humas/assign_pegawai.php");
        exit;
    }

    // =========================
    // AKSI VERIFIKASI HASIL PEGAWAI
    // =========================
    if ($aksi == 'verifikasi') {

        $keputusan = $_POST['keputusan'] ?? '';
        $catatanVerifikasi = $_POST['catatan_verifikasi'] ?? '';

        if ($keputusan == 'Setujui') {

            $statusUmum = 'Menunggu Verifikasi Masyarakat';
            $statusLapangan = 'Diverifikasi Humas';
            $statusFinal = 'Menunggu Verifikasi Masyarakat';
            $verifikasiHumas = true;
            $progressLocked = true;

        } else {

            $statusUmum = 'Perlu Perbaikan Pegawai';
            $statusLapangan = 'Sedang Dikerjakan';
            $statusFinal = 'Perlu Perbaikan Pegawai';
            $verifikasiHumas = false;
            $progressLocked = false;
        }

        $laporanCollection->updateOne(
            ['_id' => $id],
            [
                '$set' => [
                    'status' => $statusUmum,
                    'status_lapangan' => $statusLapangan,
                    'status_final' => $statusFinal,
                    'verifikasi_humas' => $verifikasiHumas,
                    'progress_locked' => $progressLocked,
                    'catatan_verifikasi_humas' => $catatanVerifikasi,
                    'tanggal_verifikasi_humas' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ]
        );

        if (!empty($laporan['pegawai_id'])) {
            $notifCollection->insertOne([
                'user_id' => $laporan['pegawai_id'],
                'judul' => 'Verifikasi Hasil Pekerjaan',
                'pesan' => 'Humas telah memverifikasi laporan "' . $laporan['judul'] . '" dengan status ' . $statusFinal . '.',
                'dibaca' => false,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        $notifCollection->insertOne([
            'user_id' => $laporan['user_id'],
            'judul' => 'Update Verifikasi Humas',
            'pesan' => 'Laporan "' . $laporan['judul'] . '" sekarang berstatus ' . $statusFinal . '.',
            'dibaca' => false,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        header("Location: /humas/assign_pegawai.php");
        exit;
    }
}

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Kelola Laporan</h1>
    <p>Validasi laporan, assign pegawai, dan verifikasi hasil pekerjaan pegawai.</p>
</div>

<div class="grid-2">

    <div class="card">
        <h2><?= safe($laporan['judul']) ?></h2>

        <p><b>Pelapor:</b><br><?= safe($laporan['nama_pelapor']) ?></p>
        <p><b>Kategori:</b><br><?= safe($laporan['kategori']) ?></p>
        <p><b>Wilayah:</b><br><?= safe($laporan['wilayah']) ?></p>
        <p><b>Alamat:</b><br><?= safe($laporan['alamat_lokasi']) ?></p>
        <p><b>Status Humas:</b><br><?= safe($laporan['status_humas'] ?? '-') ?></p>
        <p><b>Status Lapangan:</b><br><?= safe($laporan['status_lapangan'] ?? '-') ?></p>
        <p><b>Status Final:</b><br><?= safe($laporan['status_final'] ?? '-') ?></p>
        <p><b>Pegawai:</b><br><?= safe($laporan['pegawai_nama'] ?? '-') ?></p>

        <p><b>Progress:</b></p>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= (int)($laporan['persentase_progress'] ?? 0) ?>%;"></div>
        </div>
        <small><?= (int)($laporan['persentase_progress'] ?? 0) ?>%</small>

        <p style="margin-top:20px;"><b>Deskripsi:</b><br><?= nl2br(safe($laporan['deskripsi'])) ?></p>

        <?php if (!empty($laporan['foto_laporan'])): ?>
            <img src="/uploads/<?= safe($laporan['foto_laporan']) ?>" style="width:100%;border-radius:18px;max-height:320px;object-fit:cover;">
        <?php endif; ?>

        <?php if (!empty($laporan['foto_selesai'])): ?>
            <p style="margin-top:20px;"><b>Foto Hasil Pekerjaan:</b></p>
            <img src="/uploads/<?= safe($laporan['foto_selesai']) ?>" style="width:100%;border-radius:18px;max-height:320px;object-fit:cover;">
        <?php endif; ?>
    </div>

    <div class="card">

        <?php
        $sudahDivalidasi = ($laporan['status_humas'] ?? '') == 'Valid' && !empty($laporan['pegawai_id']);
        ?>

        <h2>Validasi & Assign Pegawai</h2>

        <?php if ($sudahDivalidasi): ?>

            <div style="padding:15px;border-radius:14px;background:#e8f5e9;color:#1b5e20;margin-bottom:20px;">
                Laporan sudah divalidasi dan ditugaskan ke pegawai. Data validasi tidak dapat diubah lagi.
            </div>

            <p><b>Status Validasi:</b><br><?= safe($laporan['status_humas'] ?? '-') ?></p>
            <p><b>Pegawai Ditugaskan:</b><br><?= safe($laporan['pegawai_nama'] ?? '-') ?></p>
            <p><b>Estimasi Biaya:</b><br>Rp <?= number_format((int)($laporan['estimasi_biaya'] ?? 0), 0, ',', '.') ?></p>
            <p><b>Catatan Humas:</b><br><?= nl2br(safe($laporan['catatan_humas'] ?? '-')) ?></p>

        <?php else: ?>

            <form method="POST">
                <input type="hidden" name="aksi" value="assign">

                <label>Status Validasi</label>
                <select name="status_humas" required>
                    <?php foreach (['Menunggu Validasi', 'Valid', 'Ditolak'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($laporan['status_humas'] ?? '') == $s ? 'selected' : '' ?>>
                            <?= $s ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Pilih Pegawai Lapangan</label>
                <select name="pegawai_id">
                    <option value="">Pilih Pegawai</option>

                    <?php foreach ($pegawaiList as $pegawai): ?>
                        <option value="<?= $pegawai['_id'] ?>" <?= ($laporan['pegawai_id'] ?? '') == (string)$pegawai['_id'] ? 'selected' : '' ?>>
                            <?= safe($pegawai['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Estimasi Biaya</label>
                <input type="number" name="estimasi_biaya" min="0" value="<?= safe($laporan['estimasi_biaya'] ?? 0) ?>">

                <label>Catatan Humas</label>
                <textarea name="catatan_humas" rows="5"><?= safe($laporan['catatan_humas'] ?? '') ?></textarea>

                <button type="submit">Simpan Validasi / Assign</button>
            </form>

        <?php endif; ?>

        <?php
        $progress = (int)($laporan['persentase_progress'] ?? 0);
        $statusLapangan = $laporan['status_lapangan'] ?? '';
        $verifikasiHumas = $laporan['verifikasi_humas'] ?? null;

        $bisaVerifikasiHumas =
    $progress >= 100 &&
    ($verifikasiHumas === null || $verifikasiHumas === false) &&
    in_array($statusLapangan, [
        'Menunggu Verifikasi Humas',
        'Selesai Dikerjakan'
    ]);
    ?>

        <?php if ($bisaVerifikasiHumas): ?>
            <hr style="margin:25px 0;">

            <h2>Verifikasi Hasil Pegawai</h2>

            <div style="padding:15px;border-radius:14px;background:#fff3cd;color:#664d03;margin-bottom:20px;">
                Pegawai sudah menyelesaikan pekerjaan hingga 100%. Silakan setujui atau minta perbaikan ulang.
            </div>

            <form method="POST">
                <input type="hidden" name="aksi" value="verifikasi">

                <label>Keputusan Humas</label>
                <select name="keputusan" required>
                    <option value="Setujui">Setujui dan Kirim ke Masyarakat</option>
                    <option value="Perbaikan Ulang">Minta Pegawai Perbaikan Ulang</option>
                </select>

                <label>Catatan Verifikasi</label>
                <textarea name="catatan_verifikasi" rows="5" required><?= safe($laporan['catatan_verifikasi_humas'] ?? '') ?></textarea>

                <button type="submit" class="btn green">Simpan Verifikasi</button>
            </form>
        <?php endif; ?>

        <?php if (
    ($laporan['verifikasi_humas'] ?? null) === true &&
    ($laporan['verifikasi_masyarakat'] ?? false) !== true &&
    ($laporan['status_final'] ?? '') !== 'Selesai'
): ?>

<?php if (
    ($laporan['verifikasi_masyarakat'] ?? false) === true ||
    ($laporan['status_final'] ?? '') === 'Selesai' ||
    ($laporan['status'] ?? '') === 'Selesai'
): ?>

<hr style="margin:25px 0;">

<div style="padding:15px;border-radius:14px;background:#e8f5e9;color:#1b5e20;">
    ✅ Laporan telah selesai sepenuhnya dan sudah diverifikasi oleh masyarakat.
</div>

<?php endif; ?>
            <hr style="margin:25px 0;">

            <div style="padding:15px;border-radius:14px;background:#e8f5e9;color:#1b5e20;">
                Hasil pekerjaan sudah disetujui Humas dan sedang menunggu verifikasi masyarakat.
            </div>
        <?php endif; ?>

        <?php if (($laporan['verifikasi_humas'] ?? null) === false): ?>
            <hr style="margin:25px 0;">

            <div style="padding:15px;border-radius:14px;background:#ffebee;color:#b71c1c;">
                Hasil pekerjaan ditolak Humas. Pegawai dapat melakukan perbaikan ulang.
            </div>
        <?php endif; ?>

    </div>
</div>

<a href="/humas/assign_pegawai.php" class="btn gray">Kembali</a>

<?php include __DIR__ . '/../partials/footer.php'; ?>