<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Humas']);

$id = new MongoDB\BSON\ObjectId($_GET['id']);

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

    $pegawaiNama = '';

    if (!empty($_POST['pegawai_id'])) {
        $pegawai = $usersCollection->findOne([
            '_id' => new MongoDB\BSON\ObjectId($_POST['pegawai_id'])
        ]);

        $pegawaiNama = $pegawai['nama'] ?? '';
    }

    $statusLapangan = !empty($_POST['pegawai_id'])
        ? 'Sedang Dikerjakan'
        : 'Belum Ditugaskan';

    $statusFinal = $_POST['status_humas'] == 'Ditolak'
        ? 'Ditolak'
        : 'Belum Selesai';

    $laporanCollection->updateOne(
        ['_id' => $id],
        ['$set' => [
            'status' => $_POST['status_humas'],
            'status_humas' => $_POST['status_humas'],
            'status_lapangan' => $statusLapangan,
            'status_final' => $statusFinal,

            'pegawai_id' => $_POST['pegawai_id'],
            'pegawai_nama' => $pegawaiNama,

            'estimasi_biaya' => (int) $_POST['estimasi_biaya'],
            'catatan_humas' => $_POST['catatan_humas'],

            'humas_id' => $_SESSION['user']['id'],
            'humas_nama' => $_SESSION['user']['nama'],

            'updated_at' => date('Y-m-d H:i:s')
        ]]
    );

    if (!empty($_POST['pegawai_id'])) {

        $trackingCollection->updateOne(
            [
                'laporan_id' => (string)$id
            ],
            [
                '$set' => [
                    'laporan_id' => (string)$id,
                    'judul_laporan' => $laporan['judul'],
                    'pegawai_id' => $_POST['pegawai_id'],
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
            'user_id' => $_POST['pegawai_id'],
            'judul' => 'Tugas Baru',
            'pesan' => 'Anda mendapat tugas baru untuk laporan "' . $laporan['judul'] . '".',
            'dibaca' => false,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    $notifCollection->insertOne([
        'user_id' => $laporan['user_id'],
        'judul' => 'Laporan Diproses',
        'pesan' => 'Laporan "' . $laporan['judul'] . '" sudah divalidasi dan ditugaskan ke pegawai.',
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

        $keputusan = $_POST['keputusan'];

        if ($keputusan == 'Setujui') {
            $statusUmum = 'Menunggu Verifikasi Masyarakat';
            $statusLapangan = 'Diverifikasi Humas';
            $statusFinal = 'Menunggu Verifikasi Masyarakat';
            $verifikasiHumas = true;
        } else {
            $statusUmum = 'Perlu Perbaikan Pegawai';
            $statusLapangan = 'Sedang Dikerjakan';
            $statusFinal = 'Perlu Perbaikan Pegawai';
            $verifikasiHumas = false;
        }

        $laporanCollection->updateOne(
            ['_id' => $id],
            ['$set' => [
                'status' => $statusUmum,
                'status_lapangan' => $statusLapangan,
                'status_final' => $statusFinal,
                'verifikasi_humas' => $verifikasiHumas,
                'catatan_humas' => $_POST['catatan_verifikasi'],
                'updated_at' => date('Y-m-d H:i:s')
            ]]
        );

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
        <h2>Validasi & Assign Pegawai</h2>

        <form method="POST">
            <input type="hidden" name="aksi" value="assign">

            <label>Status Validasi</label>
            <select name="status_humas">
                <?php foreach (['Menunggu Validasi','Valid','Ditolak'] as $s): ?>
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

        <?php if (($laporan['status_lapangan'] ?? '') == 'Menunggu Verifikasi Humas'): ?>
            <hr style="margin:25px 0;">

            <h2>Verifikasi Hasil Pegawai</h2>

            <form method="POST">
                <input type="hidden" name="aksi" value="verifikasi">

                <label>Keputusan Humas</label>
                <select name="keputusan" required>
                    <option value="Setujui">Setujui dan Kirim ke Masyarakat</option>
                    <option value="Perbaikan Ulang">Minta Pegawai Perbaikan Ulang</option>
                </select>

                <label>Catatan Verifikasi</label>
                <textarea name="catatan_verifikasi" rows="5" required><?= safe($laporan['catatan_humas'] ?? '') ?></textarea>

                <button type="submit" class="btn green">Simpan Verifikasi</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<a href="/humas/assign_pegawai.php" class="btn gray">Kembali</a>

<?php include __DIR__ . '/../partials/footer.php'; ?>