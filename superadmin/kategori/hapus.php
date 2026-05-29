<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

cekRole(['Super Admin']);

$id = new MongoDB\BSON\ObjectId($_GET['id']);

$kategoriCollection->deleteOne([
    '_id' => $id
]);

header("Location: /admin/kategori/index.php");
exit;
?>