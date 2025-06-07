<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$id = $_GET['id'] ?? 0;
$software_id = $_GET['software_id'] ?? 0;

if (!$id || !$software_id) {
  die('參數錯誤');
}

// 將該軟體下所有版本設為非最新版
$pdo->prepare("UPDATE software_versions SET is_latest = 0 WHERE software_id = ?")->execute([$software_id]);

// 將指定版本設為最新版
$pdo->prepare("UPDATE software_versions SET is_latest = 1 WHERE id = ?")->execute([$id]);

header("Location: versions.php?id=$software_id");
exit;