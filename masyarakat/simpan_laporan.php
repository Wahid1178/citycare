<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Masyarakat']);

$foto = '';

if (!empty($_FILES['foto']['name'])) {
    $folder = __DIR__ . '/../uploads/';

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $ekstensi = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $namaFile = time() . '_' . uniqid() . '.' . $ekstensi;

    move_uploaded_file(
        $_FILES['foto']['tmp_name'],
        $folder . $namaFile
    );

    $foto = $namaFile;
}

$laporanCollection->insertOne([
    'user_id' => $_SESSION['user']['id'],
    'nama_pelapor' => $_SESSION['user']['nama'],

    'judul' => $_POST['judul'],
    'kategori' => $_POST['kategori'],
    'wilayah' => $_POST['wilayah'],
    'alamat_lokasi' => $_POST['alamat_lokasi'],
    'latitude_tujuan' => $_POST['latitude_tujuan'],
    'longitude_tujuan' => $_POST['longitude_tujuan'],
    'jumlah_titik' => (int)$_POST['jumlah_titik'],
    'dampak' => $_POST['dampak'],
    'prioritas' => $_POST['prioritas'],

    'status' => 'Menunggu Validasi',
    'status_humas' => 'Menunggu Validasi',
    'status_lapangan' => 'Belum Ditugaskan',
    'status_final' => 'Belum Selesai',

    'humas_id' => '',
    'humas_nama' => '',
    'pegawai_id' => '',
    'pegawai_nama' => '',

    'estimasi_biaya' => 0,
    'persentase_progress' => 0,

    'kepala_bidang_approval' => false,
    'verifikasi_masyarakat' => false,

    'foto_laporan' => $foto,
    'foto_selesai' => '',
    'bukti_masyarakat' => '',

    'rating' => 0,
    'ulasan' => '',

    'catatan_humas' => '',
    'catatan_pegawai' => '',
    'catatan_kepala_bidang' => '',
    'catatan_masyarakat' => '',

    'deskripsi' => $_POST['deskripsi'],
    'tanggal_laporan' => date('Y-m-d'),
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
]);

catatAktivitas(
    $aktivitasCollection,
    'Tambah Laporan',
    'Masyarakat membuat laporan baru: ' . $_POST['judul']
);

header("Location: /masyarakat/laporan_saya.php");
exit;
?>