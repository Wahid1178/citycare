<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: /auth/login.php");
    exit;
}

$role = $_SESSION['user']['role'];

if ($role == 'Super Admin') {
    header("Location: /superadmin/dashboard.php");
} elseif ($role == 'Kepala Bidang') {
    header("Location: /kepala_bidang/dashboard.php");
} elseif ($role == 'Humas') {
    header("Location: /humas/dashboard.php");
} elseif ($role == 'Pegawai Lapangan') {
    header("Location: /pegawai/dashboard.php");
} else {
    header("Location: /masyarakat/dashboard.php");
}

exit;
?>