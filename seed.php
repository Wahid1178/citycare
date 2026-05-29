<?php
require_once __DIR__ . '/config/database.php';

$usersCollection->deleteMany([]);
$kategoriCollection->deleteMany([]);
$laporanCollection->deleteMany([]);
$aktivitasCollection->deleteMany([]);
$notifCollection->deleteMany([]);
$activityCollection->deleteMany([]);
$progressCollection->deleteMany([]);

$users = [
    [
        'nama' => 'Super Admin',
        'email' => 'superadmin@citycare.com',
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'role' => 'Super Admin',
        'status' => 'Aktif',
        'wilayah_tugas' => 'Semua Wilayah',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'nama' => 'Kepala Bidang',
        'email' => 'kepalabidang@citycare.com',
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'role' => 'Kepala Bidang',
        'status' => 'Aktif',
        'wilayah_tugas' => 'Semua Wilayah',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'nama' => 'Humas Layanan Publik',
        'email' => 'humas@citycare.com',
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'role' => 'Humas',
        'status' => 'Aktif',
        'wilayah_tugas' => 'Semua Wilayah',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'nama' => 'Pegawai Lapangan 1',
        'email' => 'pegawai@citycare.com',
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'role' => 'Pegawai Lapangan',
        'status' => 'Aktif',
        'wilayah_tugas' => 'Kecamatan Melati',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'nama' => 'Masyarakat Demo',
        'email' => 'user@citycare.com',
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'role' => 'Masyarakat',
        'status' => 'Aktif',
        'wilayah_tugas' => '',
        'created_at' => date('Y-m-d H:i:s')
    ]
];

$result = $usersCollection->insertMany($users);
$ids = $result->getInsertedIds();

$userId = (string)$ids[4];

$kategoriCollection->insertMany([
    [
        'nama_kategori' => 'Jalan Rusak',
        'keterangan' => 'Jalan berlubang, retak, amblas, atau membahayakan pengguna jalan.',
        'status' => 'Aktif',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'nama_kategori' => 'Lampu Jalan',
        'keterangan' => 'Lampu penerangan jalan mati, redup, rusak, atau korsleting.',
        'status' => 'Aktif',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'nama_kategori' => 'Drainase dan Banjir',
        'keterangan' => 'Saluran air tersumbat, genangan, atau banjir.',
        'status' => 'Aktif',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'nama_kategori' => 'Longsor',
        'keterangan' => 'Tanah longsor, tebing rawan longsor, atau material longsor menutup jalan.',
        'status' => 'Aktif',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'nama_kategori' => 'Sampah dan Kebersihan',
        'keterangan' => 'Sampah menumpuk, TPS penuh, bau tidak sedap, atau lingkungan kotor.',
        'status' => 'Aktif',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'nama_kategori' => 'Pohon Tumbang',
        'keterangan' => 'Pohon tumbang, pohon miring, atau ranting berbahaya.',
        'status' => 'Aktif',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'nama_kategori' => 'Fasilitas Umum Rusak',
        'keterangan' => 'Kerusakan taman, halte, trotoar, jembatan, bangku publik, atau fasilitas kota.',
        'status' => 'Aktif',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'nama_kategori' => 'Air Bersih dan Sanitasi',
        'keterangan' => 'Gangguan air bersih, toilet umum rusak, atau fasilitas sanitasi bermasalah.',
        'status' => 'Aktif',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'nama_kategori' => 'Keamanan Lingkungan',
        'keterangan' => 'Fasilitas keamanan rusak, lokasi rawan, atau gangguan ketertiban umum.',
        'status' => 'Aktif',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'nama_kategori' => 'Lainnya',
        'keterangan' => 'Laporan lain yang berkaitan dengan fasilitas umum dan lingkungan.',
        'status' => 'Aktif',
        'created_at' => date('Y-m-d H:i:s')
    ]
]);

$laporanCollection->insertOne([
    'user_id' => $userId,
    'nama_pelapor' => 'Masyarakat Demo',
    'judul' => 'Jalan berlubang di depan sekolah',
    'kategori' => 'Jalan Rusak',
    'wilayah' => 'Kecamatan Melati',
    'alamat_lokasi' => 'Jl. Merdeka depan SD Melati',
    'jumlah_titik' => 2,
    'dampak' => 'Tinggi',
    'prioritas' => 'Tinggi',

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

    'foto_laporan' => '',
    'foto_selesai' => '',
    'bukti_masyarakat' => '',

    'rating' => 0,
    'ulasan' => '',

    'catatan_humas' => '',
    'catatan_pegawai' => '',
    'catatan_kepala_bidang' => '',
    'catatan_masyarakat' => '',

    'deskripsi' => 'Jalan berlubang cukup besar dan membahayakan pengguna jalan, terutama saat malam hari.',
    'tanggal_laporan' => date('Y-m-d'),
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
]);

echo "<h2>Seed berhasil dibuat.</h2>";
echo "Database: <b>citycare_pro_db</b><br><br>";

echo "<b>Akun Demo:</b><br>";
echo "Super Admin: superadmin@citycare.com / 123456<br>";
echo "Kepala Bidang: kepalabidang@citycare.com / 123456<br>";
echo "Humas: humas@citycare.com / 123456<br>";
echo "Pegawai Lapangan: pegawai@citycare.com / 123456<br>";
echo "Masyarakat: user@citycare.com / 123456<br><br>";

echo "<a href='/auth/login.php'>Login Sekarang</a>";
?>