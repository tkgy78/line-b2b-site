<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$id           = $_POST['id'] ?? 0;
$software_id  = $_POST['software_id'] ?? 0;
$version      = trim($_POST['version'] ?? '');
$changelog    = trim($_POST['changelog'] ?? '');

if (!$id || !$software_id || !$version) {
  die('缺少必要資料');
}

// 先撈舊資料
$stmt = $pdo->prepare("SELECT * FROM software_versions WHERE id = ?");
$stmt->execute([$id]);
$current = $stmt->fetch();

if (!$current) {
  die('找不到資料');
}

$file_path = $current['file_path'];

// 如果有上傳新檔案
if (!empty($_FILES['file']['tmp_name'])) {
  $upload_dir = 'uploads/software_files/';
  if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
  }

  $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
  $filename = uniqid('ver_') . '.' . $ext;
  $dest = $upload_dir . $filename;

  if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
    // 如果成功上傳新檔案，刪除舊檔（如果存在）
    if (file_exists($file_path)) {
      unlink($file_path);
    }
    $file_path = $dest;
  } else {
    die('檔案上傳失敗');
  }
}

// 更新資料
$stmt = $pdo->prepare("UPDATE software_versions SET version = ?, changelog = ?, file_path = ?, updated_at = NOW() WHERE id = ?");
$stmt->execute([$version, $changelog, $file_path, $id]);

header("Location: versions.php?id=$software_id");
exit;