<?php
function safe($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function rupiah($angka) {
    return "Rp " . number_format((int)$angka, 0, ',', '.');
}

function badgeStatus($status) {
    $class = 'badge';
    if ($status == 'Menunggu') $class .= ' wait';
    if ($status == 'Diverifikasi') $class .= ' verify';
    if ($status == 'Diproses') $class .= ' process';
    if ($status == 'Selesai') $class .= ' done';
    if ($status == 'Ditolak') $class .= ' reject';
    return "<span class='$class'>" . safe($status) . "</span>";
}

function catatAktivitas($aktivitasCollection, $aksi, $deskripsi) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $user = $_SESSION['user'] ?? ['nama' => 'System', 'role' => 'System'];

    $aktivitasCollection->insertOne([
        'user_nama' => $user['nama'],
        'role' => $user['role'],
        'aksi' => $aksi,
        'deskripsi' => $deskripsi,
        'created_at' => date('Y-m-d H:i:s')
    ]);
}
?>
