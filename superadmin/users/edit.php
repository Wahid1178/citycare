<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

cekRole(['Super Admin']);

$id = new MongoDB\BSON\ObjectId($_GET['id']);
$user = $usersCollection->findOne(['_id' => $id]);

if (!$user) {
    die("User tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $update = [
        'nama' => $_POST['nama'],
        'email' => $_POST['email'],
        'role' => $_POST['role'],
        'status' => $_POST['status'],
        'wilayah_tugas' => $_POST['wilayah_tugas'] ?? '',
        'updated_at' => date('Y-m-d H:i:s')
    ];

    if (!empty($_POST['password'])) {
        $update['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    $usersCollection->updateOne(
        ['_id' => $id],
        ['$set' => $update]
    );

    header("Location: /superadmin/users/index.php");
    exit;
}

include __DIR__ . '/../../partials/header.php';
?>

<div class="page-header">
    <h1>Edit User</h1>
    <p>Perbarui data akun pengguna.</p>
</div>

<div class="card">
    <form method="POST">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" value="<?= safe($user['nama']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= safe($user['email']) ?>" required>

        <label>Password Baru</label>
        <input type="password" name="password" placeholder="Kosongkan jika tidak diganti">

        <label>Role</label>
        <select name="role" required>
            <?php foreach (['Super Admin','Kepala Bidang','Humas','Pegawai Lapangan','Masyarakat'] as $r): ?>
                <option value="<?= $r ?>" <?= $user['role'] == $r ? 'selected' : '' ?>><?= $r ?></option>
            <?php endforeach; ?>
        </select>

        <label>Status</label>
        <select name="status" required>
            <option value="Aktif"
<?= (($user['status'] ?? 'Aktif') == 'Aktif') ? 'selected' : '' ?>>
Aktif
</option>

<option value="Nonaktif"
<?= (($user['status'] ?? 'Aktif') == 'Nonaktif') ? 'selected' : '' ?>>
Nonaktif
</option>
        </select>

        <label>Wilayah Tugas</label>
        <input type="text" name="wilayah_tugas" value="<?= safe($user['wilayah_tugas'] ?? '') ?>">

        <button type="submit">Update User</button>
        <a href="/superadmin/users/index.php" class="btn gray">Kembali</a>
    </form>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>