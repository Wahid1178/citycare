<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cek = $usersCollection->findOne(['email' => $_POST['email']]);

    if ($cek) {
        $error = "Email sudah terdaftar.";
    } elseif ($_POST['password'] != $_POST['konfirmasi_password']) {
        $error = "Konfirmasi password tidak sama.";
    } else {
        $usersCollection->insertOne([
            'nama' => $_POST['nama'],
            'email' => $_POST['email'],
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role' => 'Masyarakat',
            'no_hp' => $_POST['no_hp'],
            'alamat' => $_POST['alamat'],
            'status' => 'Aktif',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $success = "Registrasi berhasil. Silakan login.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi CityCare Pro</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="auth-card">
        <h1>Registrasi Masyarakat</h1>

        <?php if ($error): ?><div class="warning-box"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="info"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <form method="POST">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>No HP</label>
            <input type="text" name="no_hp" required>

            <label>Alamat</label>
            <textarea name="alamat" rows="3" required></textarea>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Konfirmasi Password</label>
            <input type="password" name="konfirmasi_password" required>

            <button type="submit">Daftar</button>
            <a href="/auth/login.php" class="btn gray">Kembali Login</a>
        </form>
    </div>
</div>
</body>
</html>
