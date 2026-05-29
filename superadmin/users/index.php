<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

cekRole(['Super Admin']);

$keyword = $_GET['keyword'] ?? '';
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';

$filter = [];

if (!empty($keyword)) {
    $filter['$or'] = [
        ['nama' => new MongoDB\BSON\Regex($keyword, 'i')],
        ['email' => new MongoDB\BSON\Regex($keyword, 'i')],
        ['wilayah_tugas' => new MongoDB\BSON\Regex($keyword, 'i')]
    ];
}

if (!empty($role)) {
    $filter['role'] = $role;
}

if (!empty($status)) {
    $filter['status'] = $status;
}

$users = $usersCollection->find(
    $filter,
    ['sort' => ['created_at' => -1]]
);

include __DIR__ . '/../../partials/header.php';
?>

<div class="page-header">
    <h1>Manajemen User</h1>
    <p>Kelola akun Super Admin, Kepala Bidang, Humas, Pegawai Lapangan, dan Masyarakat.</p>
</div>

<div class="card">
    <form method="GET" class="filter-grid">
        <input type="text" name="keyword" placeholder="Cari nama, email, atau wilayah..." value="<?= safe($keyword) ?>">

        <select name="role">
            <option value="">Semua Role</option>
            <?php foreach (['Super Admin','Kepala Bidang','Humas','Pegawai Lapangan','Masyarakat'] as $r): ?>
                <option value="<?= $r ?>" <?= $role == $r ? 'selected' : '' ?>><?= $r ?></option>
            <?php endforeach; ?>
        </select>

        <select name="status">
            <option value="">Semua Status</option>
            <option value="Aktif" <?= $status == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="Nonaktif" <?= $status == 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>

        <button type="submit">Filter</button>
        <a href="/superadmin/users/index.php" class="btn gray">Reset</a>
        <a href="/superadmin/users/tambah.php" class="btn green">+ Tambah User</a>
    </form>
</div>

<div class="card">
    <table>
        <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Wilayah Tugas</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= safe($user['nama']) ?></td>
            <td><?= safe($user['email']) ?></td>
            <td><?= safe($user['role']) ?></td>
            <td><?= safe($user['wilayah_tugas'] ?? '-') ?></td>
            <td><?= safe($user['status']) ?></td>
            <td class="actions">
                <a href="/superadmin/users/edit.php?id=<?= $user['_id'] ?>" class="btn orange">Edit</a>
                <a href="/superadmin/users/hapus.php?id=<?= $user['_id'] ?>" class="btn red" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>