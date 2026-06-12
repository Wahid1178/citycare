<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Masyarakat']);

$id = new MongoDB\BSON\ObjectId($_GET['id']);

$userId = $_SESSION['user']['id'];

$laporan = $laporanCollection->findOne([
    '_id' => $id,
    'user_id' => $userId
]);

if (!$laporan) {
    die("Laporan tidak ditemukan.");
}

// =====================================
// VERIFIKASI MASYARAKAT
// =====================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $bukti = '';

    if (!empty($_FILES['bukti_masyarakat']['name'])) {

        $folder = __DIR__ . '/../uploads/';

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $ext = pathinfo(
            $_FILES['bukti_masyarakat']['name'],
            PATHINFO_EXTENSION
        );

        $namaFile =
            time() .
            '_' .
            uniqid() .
            '.' .
            $ext;

        move_uploaded_file(
            $_FILES['bukti_masyarakat']['tmp_name'],
            $folder . $namaFile
        );

        $bukti = $namaFile;
    }

    $statusFinal =
        $_POST['verifikasi'] == 'Sesuai'
        ? 'Selesai Final'
        : 'Perlu Perbaikan Ulang';

    $laporanCollection->updateOne(
    ['_id' => $id],
    ['$set' => [

        'verifikasi_masyarakat' =>
            $_POST['verifikasi'] == 'Sesuai',

        'status' =>
            $_POST['verifikasi'] == 'Sesuai'
            ? 'Selesai Final'
            : 'Perlu Perbaikan Ulang',

        'status_lapangan' =>
            $_POST['verifikasi'] == 'Sesuai'
            ? 'Selesai Final'
            : 'Sedang Dikerjakan',

        'status_final' => $statusFinal,

        'bukti_masyarakat' => $bukti,

        'rating_pegawai' => (int)$_POST['rating'],

        'ulasan_pegawai' => $_POST['ulasan'] ?? '',

        'sudah_rating' => true,

        'tanggal_rating' => date('Y-m-d H:i:s'),

        'catatan_masyarakat' => $_POST['catatan_masyarakat'] ?? '',

        'updated_at' => date('Y-m-d H:i:s')

    ]]
);

    // NOTIFIKASI
    $notifCollection->insertOne([
    'user_id' => $laporan['pegawai_id'],
    'judul' => 'Verifikasi Masyarakat',
    'pesan' => 'Masyarakat telah memberikan verifikasi untuk laporan "' . $laporan['judul'] . '".',
    'dibaca' => false,
    'created_at' => date('Y-m-d H:i:s')
]);

    // ACTIVITY
    $activityCollection->insertOne([

        'laporan_id' => (string)$id,

        'user_nama' => $_SESSION['user']['nama'],

        'role' => 'Masyarakat',

        'aktivitas' =>
            'Masyarakat melakukan verifikasi hasil perbaikan.',

        'created_at' => date('Y-m-d H:i:s')

    ]);

    header("Location: /masyarakat/laporan_saya.php");
    exit;
}

// =====================================
// PROGRESS
// =====================================
$progressList = $progressCollection->find(
    [
        'laporan_id' => (string)$id
    ],
    [
        'sort' => [
            'created_at' => -1
        ]
    ]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="public-hero">

    <h1>
        Detail Progress Laporan
    </h1>

    <p>
        Pantau progress penanganan laporan Anda
        secara real-time dari Humas dan Pegawai Lapangan.
    </p>

</div>

<div class="grid-2">

    <!-- DETAIL -->
    <div class="card">

        <h2>
            <?= safe($laporan['judul']) ?>
        </h2>

        <!-- TIMELINE -->
        <div class="timeline">

            <div class="
                timeline-item
                <?= $laporan['status_humas'] == 'Menunggu Validasi' ? 'timeline-active' : '' ?>
            ">
                Menunggu Validasi
            </div>

            <div class="
                timeline-item
                <?= $laporan['status_humas'] == 'Valid' ? 'timeline-active' : '' ?>
            ">
                Validasi Humas
            </div>

            <div class="
                timeline-item
                <?= $laporan['status_lapangan'] == 'Sedang Dikerjakan' ? 'timeline-active' : '' ?>
            ">
                Diproses Lapangan
            </div>

            <div class="
                timeline-item
                <?= $laporan['status_lapangan'] == 'Selesai Dikerjakan' ? 'timeline-active' : '' ?>
            ">
                Menunggu Verifikasi
            </div>

            <div class="
                timeline-item
                <?= $laporan['status_final'] == 'Selesai Final' ? 'timeline-active' : '' ?>
            ">
                Selesai Final
            </div>

        </div>

        <p>
            <b>Kategori:</b><br>
            <?= safe($laporan['kategori']) ?>
        </p>

        <p>
            <b>Wilayah:</b><br>
            <?= safe($laporan['wilayah']) ?>
        </p>

        <p>
            <b>Status Humas:</b><br>
            <?= safe($laporan['status_humas']) ?>
        </p>

        <p>
            <b>Status Lapangan:</b><br>
            <?= safe($laporan['status_lapangan']) ?>
        </p>

        <p>
            <b>Progress:</b>
        </p>

        <div class="progress-bar">
            <div
                class="progress-fill"
                style="width:<?= (int)$laporan['persentase_progress'] ?>%;"
            ></div>
        </div>

        <small>
            <?= (int)$laporan['persentase_progress'] ?>%
        </small>

        <p style="margin-top:20px;">
            <b>Deskripsi:</b><br>
            <?= nl2br(safe($laporan['deskripsi'])) ?>
        </p>

        <!-- FOTO AWAL -->
        <?php if (!empty($laporan['foto_laporan'])): ?>

            <p>
                <b>Foto Awal:</b>
            </p>

            <img
                src="/uploads/<?= safe($laporan['foto_laporan']) ?>"
                style="
                    width:100%;
                    border-radius:18px;
                    max-height:300px;
                    object-fit:cover;
                "
            >

        <?php endif; ?>

        <!-- FOTO SELESAI -->
        <?php if (!empty($laporan['foto_selesai'])): ?>

            <p style="margin-top:20px;">
                <b>Foto Hasil Perbaikan:</b>
            </p>

            <img
                src="/uploads/<?= safe($laporan['foto_selesai']) ?>"
                style="
                    width:100%;
                    border-radius:18px;
                    max-height:300px;
                    object-fit:cover;
                "
            >

        <?php endif; ?>

    </div>

    <!-- VERIFIKASI -->
    <div class="card">

        <h2>
            Verifikasi Masyarakat
        </h2>

        <?php if (
    ($laporan['verifikasi_humas'] ?? false) === true &&
    ($laporan['status_final'] ?? '') == 'Menunggu Verifikasi Masyarakat'
): ?>

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <label>
                    Hasil Perbaikan
                </label>

                <select name="verifikasi">

                    <option value="Sesuai">
                        Sudah Sesuai
                    </option>

                    <option value="Belum Sesuai">
                        Belum Sesuai
                    </option>

                </select>

                <label>
                    Upload Bukti
                </label>

                <input
                    type="file"
                    name="bukti_masyarakat"
                    accept="image/*"
                >

                <label>
                    Rating Pelayanan
                </label>

                <select name="rating">

                    <?php for ($i=1; $i<=5; $i++): ?>

                        <option value="<?= $i ?>">
                            <?= $i ?>
                        </option>

                    <?php endfor; ?>

                </select>

                <label>
                    Ulasan
                </label>

                <textarea
                    name="ulasan"
                    rows="4"
                ></textarea>

                <label>
                    Catatan Tambahan
                </label>

                <textarea
                    name="catatan_masyarakat"
                    rows="4"
                ></textarea>

                <button type="submit">
                    Kirim Verifikasi
                </button>

            </form>

        <?php else: ?>

            <div class="alert-info">

                Verifikasi masyarakat akan tersedia
                setelah pegawai menyelesaikan pekerjaan.

            </div>

        <?php endif; ?>

    </div>

</div>

<!-- RIWAYAT -->
<div class="card">

    <h2>
        Riwayat Progress Lapangan
    </h2>

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

            <td>
                <?= safe($progress['created_at']) ?>
            </td>

            <td>
                <?= safe($progress['pegawai_nama']) ?>
            </td>

            <td>
                <?= safe($progress['persentase']) ?>%
            </td>

            <td>
                <?= safe($progress['keterangan']) ?>
            </td>

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