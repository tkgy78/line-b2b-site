<?php
// 最前面清空任何可能的緩衝
while (ob_get_level()) ob_end_clean();
header('Content-Type: text/plain; charset=UTF-8');

require_once __DIR__ . '/../db.php';
$pdo = connect();

$type = $_GET['type'] ?? null;
$pid = $_GET['pid'] ?? null;

if (!in_array($type, ['spec', 'manual']) || !$pid || !is_numeric($pid)) {
  http_response_code(400);
  echo "參數錯誤";
  exit;
}

$column = $type === 'spec' ? 'spec_file' : 'manual_file';

// 撈資料
$stmt = $pdo->prepare("SELECT $column FROM product_details WHERE product_id = ?");
$stmt->execute([$pid]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || empty($row[$column])) {
  http_response_code(404);
  echo "找不到對應檔案紀錄";
  exit;
}

$filePath = __DIR__ . '/../' . $row[$column];

// 刪除檔案（不報錯）
if (file_exists($filePath)) {
  @unlink($filePath);
}

// 更新資料庫
$updateStmt = $pdo->prepare("UPDATE product_details SET $column = NULL WHERE product_id = ?");
$updateStmt->execute([$pid]);

// 👇 這行非常重要，只輸出這 7 個字元
echo 'success';
exit;