<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$product_id = $_POST['product_id'] ?? null;
if (!$product_id || !is_numeric($product_id)) {
  die('缺少或無效的商品 ID');
}

// 更新詳細說明
$detailed_desc = $_POST['detail_html'] ?? '';
$stmt = $pdo->prepare("INSERT INTO product_details (product_id, detail_html, updated_at)
  VALUES (?, ?, NOW())
  ON DUPLICATE KEY UPDATE detail_html = VALUES(detail_html), updated_at = NOW()");
$stmt->execute([$product_id, $detailed_desc]);

// 上傳圖片
$imageDir = __DIR__ . '/../uploads/product_images/';
if (!is_dir($imageDir)) mkdir($imageDir, 0777, true);

if (!empty($_FILES['images']['name'][0])) {
  foreach ($_FILES['images']['tmp_name'] as $idx => $tmpPath) {
    if (is_uploaded_file($tmpPath)) {
      $filename = uniqid() . '_' . basename($_FILES['images']['name'][$idx]);
      $targetPath = $imageDir . $filename;
      if (move_uploaded_file($tmpPath, $targetPath)) {
        $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
        $stmt->execute([$product_id, "uploads/product_images/" . $filename]);
      }
    }
  }
}

// 上傳 PDF：規格書 (spec_file)
$pdfDir = __DIR__ . '/../uploads/product_files/';
if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);

if (!empty($_FILES['spec_file']['tmp_name'])) {
  if (is_uploaded_file($_FILES['spec_file']['tmp_name'])) {
    $filename = uniqid() . '_' . basename($_FILES['spec_file']['name']);
    $targetPath = $pdfDir . $filename;
    if (move_uploaded_file($_FILES['spec_file']['tmp_name'], $targetPath)) {
      $stmt = $pdo->prepare("UPDATE product_details SET spec_file = ? WHERE product_id = ?");
      $stmt->execute(["uploads/product_files/" . $filename, $product_id]);
    }
  }
}

// 上傳 PDF：使用手冊 (manual_file)
if (!empty($_FILES['manual_file']['tmp_name'])) {
  if (is_uploaded_file($_FILES['manual_file']['tmp_name'])) {
    $filename = uniqid() . '_' . basename($_FILES['manual_file']['name']);
    $targetPath = $pdfDir . $filename;
    if (move_uploaded_file($_FILES['manual_file']['tmp_name'], $targetPath)) {
      $stmt = $pdo->prepare("UPDATE product_details SET manual_file = ? WHERE product_id = ?");
      $stmt->execute(["uploads/product_files/" . $filename, $product_id]);
    }
  }
}

header("Location: detail/view.php?id=" . $product_id);
exit;