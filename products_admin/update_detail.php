<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

// 驗證商品 ID
$id = $_POST['id'] ?? null;
if (!$id || !is_numeric($id)) {
  die('缺少或無效的商品 ID');
}

// 更新商品詳細說明
$description = $_POST['description'] ?? '';
$stmt = $pdo->prepare("UPDATE products SET description = ?, updated_at = NOW() WHERE id = ?");
$stmt->execute([$description, $id]);

// 處理圖片上傳（多張）
$imageDir = __DIR__ . '/../uploads/product_images/';
if (!is_dir($imageDir)) mkdir($imageDir, 0777, true);

if (!empty($_FILES['images']['name'][0])) {
  foreach ($_FILES['images']['tmp_name'] as $idx => $tmpPath) {
    if (is_uploaded_file($tmpPath)) {
      $filename = uniqid() . '_' . basename($_FILES['images']['name'][$idx]);
      $targetPath = $imageDir . $filename;
      if (move_uploaded_file($tmpPath, $targetPath)) {
        $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
        $stmt->execute([$id, "uploads/product_images/" . $filename]);
      }
    }
  }
}

// 處理 PDF 上傳（多檔）
$pdfDir = __DIR__ . '/../uploads/product_files/';
if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);

if (!empty($_FILES['pdfs']['name'][0])) {
  foreach ($_FILES['pdfs']['tmp_name'] as $idx => $tmpPath) {
    if (is_uploaded_file($tmpPath)) {
      $filename = uniqid() . '_' . basename($_FILES['pdfs']['name'][$idx]);
      $targetPath = $pdfDir . $filename;
      if (move_uploaded_file($tmpPath, $targetPath)) {
        $stmt = $pdo->prepare("INSERT INTO product_files (product_id, file_url, file_name) VALUES (?, ?, ?)");
        $stmt->execute([$id, "uploads/product_files/" . $filename, $_FILES['pdfs']['name'][$idx]]);
      }
    }
  }
}

header("Location: detail/view.php?id=" . $id);
exit;