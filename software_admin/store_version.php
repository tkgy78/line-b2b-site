<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

// 驗證必填欄位
if (
  empty($_POST['software_id']) ||
  empty($_POST['version']) ||
  !isset($_FILES['file'])
) {
  die('缺少必要欄位');
}

$software_id = intval($_POST['software_id']);
$version = trim($_POST['version']);
$changelog = trim($_POST['changelog'] ?? '');
$is_latest = isset($_POST['is_latest']) ? 1 : 0;

// 處理上傳檔案
$upload_dir = 'uploads/software_versions/';
if (!is_dir($upload_dir)) {
  mkdir($upload_dir, 0777, true);
}

$filename = $_FILES['file']['name'];
$tmpname = $_FILES['file']['tmp_name'];
$dest_path = $upload_dir . uniqid() . '_' . basename($filename);

if (!move_uploaded_file($tmpname, $dest_path)) {
  die('檔案上傳失敗');
}

try {
  $pdo->beginTransaction();

  if ($is_latest) {
    // 將其他版本設為不是最新版
    $stmt = $pdo->prepare("UPDATE software_versions SET is_latest = 0 WHERE software_id = ?");
    $stmt->execute([$software_id]);
  }

  // 新增版本資料
  $stmt = $pdo->prepare("INSERT INTO software_versions 
    (software_id, version, file_path, changelog, is_latest) 
    VALUES (?, ?, ?, ?, ?)");
  $stmt->execute([
    $software_id,
    $version,
    $dest_path,
    $changelog,
    $is_latest
  ]);

  $pdo->commit();
  header("Location: versions.php?id={$software_id}");
  exit;

} catch (Exception $e) {
  $pdo->rollBack();
  echo '儲存失敗：' . $e->getMessage();
}