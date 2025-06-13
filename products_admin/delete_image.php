<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$id = $_GET['id'] ?? null;
$productId = $_GET['pid'] ?? null;

if (!$id || !$productId || !is_numeric($id) || !is_numeric($productId)) {
  http_response_code(400); // Bad Request
  exit;
}

// 撈圖片路徑
$stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE id = ? AND product_id = ?");
$stmt->execute([$id, $productId]);
$image = $stmt->fetch();

if ($image) {
  $filePath = __DIR__ . '/../' . $image['image_url'];

  // 刪除圖片檔案
  if (file_exists($filePath)) {
    unlink($filePath);
  }

  // 刪除資料表紀錄
  $deleteStmt = $pdo->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
  $deleteStmt->execute([$id, $productId]);

  http_response_code(200); // ✅ 成功
  exit;
}

http_response_code(404); // Not Found
exit;