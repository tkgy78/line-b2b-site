<?php
require_once __DIR__.'/../db.php';
$pdo = connect();
$id = intval($_GET['id'] ?? 0);
if ($id) {
    $pdo->prepare("DELETE FROM prices WHERE product_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
}
header("Location: index.php?msg=" . urlencode('刪除完成'));
exit;
