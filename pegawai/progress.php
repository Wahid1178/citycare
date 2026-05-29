<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Pegawai Lapangan']);

$pegawaiId = $_SESSION['user']['id'];

/*
|--------------------------------------------------------------------------
| Jika progress.php dibuka tanpa id
| maka tampilkan daftar tugas dulu
|--------------------------------------------------------------------------
*/
if (!isset($_GET['id']) || empty($_GET['id'])) {

    $tugasList = $laporanCollection->find(
        ['pegawai_id' => $pegawaiId],
        ['sort' => ['updated_at' => -1]]
    );

    include __DIR__ . '/../partials/header.php';
    ?>

    <div class="page-header">
        <h1>Pilih Tugas untuk Update Progress</h1>
        <p>Pilih salah satu tugas yang sudah ditugaskan oleh Humas.</p>
    </div>

    <div class="card">
        <table>
            <tr>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Wilayah</th>
                <th>Status Lapangan</th>
                <th>Progress</th>
                <th>Aksi</th>
            </tr>

            <?php foreach ($tugasList as $tugas): ?>
            <tr>
                <td><?= safe($tugas['judul']) ?></td>
                <td><?= safe($tugas['kategori']) ?></td>
                <td><?= safe($tugas['wilayah']) ?></td>
                <td><?= safe($tugas['status_lapangan']) ?></td>
                <td>
                    <div class="progress-bar">
                        <div 
                            class="progress-fill" 
                            style="width: <?= (int)$tugas['persentase_progress'] ?>%;"
                        ></div>
                    </div>
                    <small><?= (int)$tugas['persentase_progress'] ?>%</small>
                </td>
                <td>
                    <a 
                        href="/pegawai/progress.php?id=<?= $tugas['_id'] ?>" 
                        class="btn orange"
                    >
                        Update Progress
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <?php
    include __DIR__ . '/../partials/footer.php';
    exit;
}

/*
|--------------------------------------------------------------------------
| Jika ada id laporan
|--------------------------------------------------------------------------
*/
$id = new MongoDB\BSON\ObjectId($_GET['id']);

$laporan = $laporanCollection->findOne([
    '_id' => $id,
    'pegawai_id' => $pegawaiId
]);

if (!$laporan) {
    include __DIR__ . '/../partials/header.php';
    ?>

    <div class="page-header">
        <h1>Laporan Tidak Ditemukan</h1>
        <p>Laporan ini tidak ditemukan atau bukan tugas Anda.</p>
    </div>

    <div class="card">
        <a href="/pegawai/tugas_saya.php" class="btn gray">Kembali ke Tugas Saya</a>
    </div>

    <?php
    include __DIR__ . '/../partials/footer.php';
    exit;
}

/*
|--------------------------------------------------------------------------
| Simpan progress
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $fotoProgress = '';

    if (!empty($_FILES['foto_progress']['name'])) {
        $folder = __DIR__ . '/../uploads/';

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $ekstensi = pathinfo($_FILES['foto_progress']['name'], PATHINFO_EXTENSION);
        $namaFile = time() . '_' . uniqid() . '.' . $ekstensi;

        move_uploaded_file(
            $_FILES['foto_progress']['tmp_name'],
            $folder . $namaFile
        );

        $fotoProgress = $namaFile;
    }

    $persentase = (int) $_POST['persentase_progress'];

    if ($persentase < 0) {
        $persentase = 0;
    }

    if ($persentase > 100) {
        $persentase = 100;
    }

$statusLapangan = 'Sedang Dikerjakan';
$statusFinal = 'Belum Selesai';
$statusUmum = 'Diproses';

if ($persentase >= 100) {
    $persentase = 100;
    $statusLapangan = 'Menunggu Verifikasi Humas';
    $statusFinal = 'Menunggu Verifikasi Humas';
    $statusUmum = 'Menunggu Verifikasi Humas';
}

    $fotoSelesai = $laporan['foto_selesai'] ?? '';

    if ($persentase >= 100 && !empty($fotoProgress)) {
        $fotoSelesai = $fotoProgress;
    }

    $laporanCollection->updateOne(
    ['_id' => $id],
    ['$set' => [
        'status' => $statusUmum,
'status_lapangan' => $statusLapangan,
'status_final' => $statusFinal,
'verifikasi_humas' => false,
        'persentase_progress' => $persentase,
        'catatan_pegawai' => $_POST['catatan_pegawai'],
        'foto_selesai' => $fotoSelesai,
        'updated_at' => date('Y-m-d H:i:s')
    ]]
);

    $progressCollection->insertOne([
        'laporan_id' => (string) $id,
        'pegawai_id' => $_SESSION['user']['id'],
        'pegawai_nama' => $_SESSION['user']['nama'],
        'keterangan' => $_POST['catatan_pegawai'],
        'persentase' => $persentase,
        'foto_progress' => $fotoProgress,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    $notifCollection->insertOne([
        'user_id' => $laporan['user_id'],
        'judul' => 'Progress Laporan Diperbarui',
        'pesan' => 'Progress laporan "' . $laporan['judul'] . '" telah diperbarui menjadi ' . $persentase . '%.',
        'dibaca' => false,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    $activityCollection->insertOne([
        'laporan_id' => (string) $id,
        'user_nama' => $_SESSION['user']['nama'],
        'role' => $_SESSION['user']['role'],
        'aktivitas' => 'Pegawai memperbarui progress menjadi ' . $persentase . '%',
        'created_at' => date('Y-m-d H:i:s')
    ]);

    header("Location: /pegawai/tugas_saya.php");
    exit;
}

$progressList = $progressCollection->find(
    ['laporan_id' => (string) $id],
    ['sort' => ['created_at' => -1]]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Update Progress Lapangan</h1>
    <p>Update perkembangan pekerjaan di lapangan dan unggah foto bukti progress.</p>
</div>

<div class="grid-2">
    <div class="card">
        <h2><?= safe($laporan['judul']) ?></h2>

        <p><b>Kategori:</b><br><?= safe($laporan['kategori']) ?></p>
        <p><b>Wilayah:</b><br><?= safe($laporan['wilayah']) ?></p>
        <p><b>Alamat Lokasi:</b><br><?= safe($laporan['alamat_lokasi']) ?></p>
        <p><b>Pelapor:</b><br><?= safe($laporan['nama_pelapor']) ?></p>
        <p><b>Deskripsi:</b><br><?= nl2br(safe($laporan['deskripsi'])) ?></p>

        <p><b>Progress Saat Ini:</b></p>
        <div class="progress-bar">
            <div 
                class="progress-fill" 
                style="width: <?= (int)$laporan['persentase_progress'] ?>%;"
            ></div>
        </div>
        <small><?= (int)$laporan['persentase_progress'] ?>%</small>

        <?php if (!empty($laporan['foto_laporan'])): ?>
            <p style="margin-top:20px;"><b>Foto Laporan dari Masyarakat:</b></p>
            <img 
                src="/uploads/<?= safe($laporan['foto_laporan']) ?>"
                style="width:100%;max-height:330px;object-fit:cover;border-radius:18px;"
            >
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Form Update Progress</h2>

        <form method="POST" enctype="multipart/form-data">

            <label>Persentase Progress</label>
            <input 
                type="number" 
                name="persentase_progress" 
                min="0" 
                max="100"
                value="<?= safe($laporan['persentase_progress']) ?>"
                required
            >

            <label>Catatan Pegawai</label>
            <textarea 
                name="catatan_pegawai" 
                rows="5" 
                required
            ><?= safe($laporan['catatan_pegawai'] ?? '') ?></textarea>

            <label>Foto Progress / Bukti Perbaikan</label>
            <input 
                type="file" 
                name="foto_progress" 
                accept="image/*"
            >

            <button type="submit">Simpan Progress</button>

            <a href="/pegawai/tugas_saya.php" class="btn gray">Kembali</a>
        </form>
    </div>
</div>

<div class="card">
    <h2>Riwayat Progress</h2>

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
                    <a 
                        href="/uploads/<?= safe($progress['foto_progress']) ?>" 
                        target="_blank" 
                        class="btn gray"
                    >
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

<?php include __DIR__ . '/../partials/footer.php'; ?>