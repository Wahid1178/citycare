<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $usersCollection->findOne(['email' => $_POST['email']]);

    if ($user && password_verify($_POST['password'], $user['password'])) {
        if (($user['status'] ?? 'Aktif') != 'Aktif') {
            $error = "Akun Anda belum aktif atau dinonaktifkan admin.";
        } else {
            $_SESSION['user'] = [
                'id' => (string)$user['_id'],
                'nama' => $user['nama'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            header("Location: /index.php");
            exit;
        }
    } else {
        $error = "Email atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login CityCare Pro</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="auth-card">
        <h1>Login CityCare Pro</h1>
        <p>Masuk untuk mengakses sistem pengaduan fasilitas umum.</p>

        <?php if ($error): ?>
            <div class="warning-box"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
            <a href="/auth/register.php" class="btn gray">Registrasi Masyarakat</a>
        </form>

        <div class="info">
            <b>Akun Demo:</b><br>
            Admin: admin@citycare.com / 123456<br>
            Petugas: petugas@citycare.com / 123456<br>
            Masyarakat: user@citycare.com / 123456
        </div>
    </div>
</div>
</body>
</html>
