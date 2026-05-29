<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

cekRole(['Super Admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cekEmail = $usersCollection->findOne(['email' => $_POST['email']]);

    if ($cekEmail) {
        $error = "Email sudah digunakan.";
    } else {
        $usersCollection->insertOne([
            'nama' => $_POST['nama'],
            'email' => $_POST['email'],
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role' => $_POST['role'],
            'status' => $_POST['status'],
            'wilayah_tugas' => $_POST['wilayah_tugas'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        header("Location: /superadmin/users/index.php");
        exit;
    }
}

include __DIR__ . '/../../partials/header.php';
?>

<div class="page-header">
    <h1>Tambah User</h1>
    <p>Super Admin dapat membuat akun untuk seluruh role sistem.</p>
</div>

<div class="card">
    <?php if (!empty($error)): ?>
        <div class="alert-info"><?= safe($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Role</label>
        <select name="role" required>
            <?php foreach (['Super Admin','Kepala Bidang','Humas','Pegawai Lapangan','Masyarakat'] as $r): ?>
                <option value="<?= $r ?>"><?= $r ?></option>
            <?php endforeach; ?>
        </select>

        <label>Status</label>
        <select name="status" required>
            <option value="Aktif">Aktif</option>
            <option value="Nonaktif">Nonaktif</option>
        </select>

        <label>Wilayah Tugas</label>
        <input type="text" name="wilayah_tugas" placeholder="Contoh: Semua Wilayah / Kecamatan Melati">

        <button type="submit">Simpan User</button>
        <a href="/superadmin/users/index.php" class="btn gray">Kembali</a>
    </form>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>