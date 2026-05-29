<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Pegawai Lapangan']);

$id = new MongoDB\BSON\ObjectId($_GET['id']);
$pegawaiId = $_SESSION['user']['id'];

$laporan = $laporanCollection->findOne([
    '_id' => $id,
    'pegawai_id' => $pegawaiId
]);

if (!$laporan) {
    die("Laporan tidak ditemukan atau bukan tugas Anda.");
}

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

    if ($persentase >= 100) {
        $statusLapangan = 'Selesai Dikerjakan';
    }

    $fotoSelesai = $laporan['foto_selesai'] ?? '';

    if ($persentase >= 100 && !empty($fotoProgress)) {
        $fotoSelesai = $fotoProgress;
    }

    $laporanCollection->updateOne(
        ['_id' => $id],
        ['$set' => [
            'status_lapangan' => $statusLapangan,
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
    <p>
        Update perkembangan pekerjaan di lapangan dan unggah foto bukti progress.
    </p>
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

            <button type="submit">
                Simpan Progress
            </button>

            <a href="/pegawai/tugas_saya.php" class="btn gray">
                Kembali
            </a>

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