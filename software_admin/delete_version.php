<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$id = $_GET['id'] ?? 0;
$software_id = $_GET['software_id'] ?? 0;

if (!$id || !$software_id) {
  die('參數錯誤');
}

// 撈取資料以刪檔案
$stmt = $pdo->prepare("SELECT file_path FROM software_versions WHERE id = ?");
$stmt->execute([$id]);
$file = $stmt->fetchColumn();

// 刪除資料庫記錄
$stmt = $pdo->prepare("DELETE FROM software_versions WHERE id = ?");
$stmt->execute([$id]);

// 刪除實體檔案（若存在）
if ($file && file_exists($file)) {
  unlink($file);
}

// 回到版本頁面
header("Location: versions.php?id=$software_id");
exit;