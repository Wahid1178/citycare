<?php

$jumlahNotif = 0;

if (isset($_SESSION['user'])) {

    $jumlahNotif = $notifCollection->countDocuments([
        'user_id' => $_SESSION['user']['id'],
        'dibaca' => false
    ]);
}

if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;
$current = $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CityCare Pro</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="layout">
    <?php if ($user): ?>
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">CC</div>
            <div>
                <h2>CityCare Pro</h2>
                <small>NoSQL Public Service</small>
            </div>
        </div>

        <div class="profile-box">
            <b><?= htmlspecialchars($user['nama']) ?></b>
            <small><?= htmlspecialchars($user['role']) ?></small>
        </div>

        <nav class="menu">
    <?php if ($user['role'] == 'Super Admin'): ?>
        <a href="/superadmin/dashboard.php">Dashboard</a>
        <a href="/superadmin/users/index.php">Manajemen User</a>
        <a href="/superadmin/kategori/index.php">Kategori Laporan</a>
        <a href="/superadmin/rekap.php">Rekap Sistem</a>

    <?php elseif ($user['role'] == 'Kepala Bidang'): ?>
        <a href="/kepala_bidang/dashboard.php">Dashboard</a>
        <a href="/kepala_bidang/monitoring.php">Monitoring Laporan</a>
        <a href="/kepala_bidang/performa_pegawai.php">Performa Pegawai</a>
        <a href="/kepala_bidang/rekap.php">Rekap Kepala Bidang</a>

    <?php elseif ($user['role'] == 'Humas'): ?>
        <a href="/humas/dashboard.php">Dashboard</a>
        <a href="/humas/laporan_masuk.php">Laporan Masuk</a>
        <a href="/humas/assign_pegawai.php">Assign Pegawai</a>
        <a href="/humas/rekap.php">Rekap Humas</a>
        <a href="/humas/live_tracking.php">Live Tracking Pegawai</a>

    <?php elseif ($user['role'] == 'Pegawai Lapangan'): ?>
    <a href="/pegawai/dashboard.php">Dashboard</a>
    <a href="/pegawai/tugas_saya.php">Tugas Saya</a>
    <a href="/pegawai/progress.php">Update Progress</a>

    <?php else: ?>
        <a href="/masyarakat/dashboard.php">Dashboard</a>
        <a href="/masyarakat/tambah_laporan.php">Buat Laporan</a>
        <a href="/masyarakat/laporan_saya.php">Laporan Saya</a>
        <a href="/masyarakat/rekap.php">Rekap Laporan Saya</a>
        <a href="/masyarakat/notifikasi.php">
    Notifikasi
    <?php if ($jumlahNotif > 0): ?>
        <span class="badge">
            <?= $jumlahNotif ?>
        </span>
    <?php endif; ?>
</a>

    <?php endif; ?>

    <a href="/auth/logout.php" class="logout">Logout</a>
</nav>
    </aside>
    <?php endif; ?>

    <main class="<?= $user ? 'main-content' : 'main-full' ?>">
