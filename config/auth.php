<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cekLogin() {
    if (!isset($_SESSION['user'])) {
        header("Location: /auth/login.php");
        exit;
    }
}

function cekRole($roles) {
    cekLogin();
    if (!in_array($_SESSION['user']['role'], $roles)) {
        die("Akses ditolak. Role Anda tidak sesuai.");
    }
}

function userLogin() {
    return $_SESSION['user'] ?? null;
}
?>
