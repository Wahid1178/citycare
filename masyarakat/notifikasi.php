<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

cekRole(['Masyarakat']);

$userId = $_SESSION['user']['id'];

$notifList = $notifCollection->find(
    ['user_id' => $userId],
    [
        'sort' => ['created_at' => -1]
    ]
);

$notifCollection->updateMany(
    ['user_id' => $userId],
    ['$set' => ['dibaca' => true]]
);

include __DIR__ . '/../partials/header.php';
?>

<div class="page-header">

    <h1>Notifikasi Saya</h1>

    <p>
        Semua update terbaru mengenai progress laporan Anda.
    </p>

</div>

<div class="card">

    <?php foreach ($notifList as $notif): ?>

        <div class="notif-item">

            <div class="notif-header">

                <h3>
                    <?= safe($notif['judul']) ?>
                </h3>

                <small>
                    <?= safe($notif['created_at']) ?>
                </small>

            </div>

            <p>
                <?= safe($notif['pesan']) ?>
            </p>

        </div>

    <?php endforeach; ?>

</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>