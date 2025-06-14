<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$data = json_decode(file_get_contents("php://input"), true);
$order = $data['order'] ?? [];

if (!is_array($order)) {
  http_response_code(400);
  echo "Invalid data";
  exit;
}

foreach ($order as $i => $imgId) {
  $stmt = $pdo->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
  $stmt->execute([$i + 1, $imgId]);  // sort_order 從 1 開始
}

echo "success";