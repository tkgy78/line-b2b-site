<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

// 驗證商品 ID
$id = $_POST['id'] ?? 0;
if (!$id) {
  http_response_code(400);
  echo 'Missing product ID';
  exit;
}

// 處理欄位
$brand_id       = $_POST['brand_id'] ?? null;
$category_id    = $_POST['category_id'] ?? null;
$series_id      = ($_POST['series_id'] ?? '') !== '' ? $_POST['series_id'] : null;
$name           = $_POST['name'] ?? '';
$sku            = $_POST['sku'] ?? '';
$short_desc     = $_POST['short_desc'] ?? '';
$stock_quantity = $_POST['stock_quantity'] ?? 0;

// ✅ 修正 checkbox 的邏輯：若沒勾選就變成 inactive
$status = ($_POST['status'] ?? '') === 'active' ? 'active' : 'inactive';

// 價格欄位
$price_types = ['msrp', 'vip', 'vvip', 'wholesale', 'cost'];
$prices = [];
foreach ($price_types as $type) {
  $key = 'price_' . $type;
  if (isset($_POST[$key]) && $_POST[$key] !== '') {
    $prices[$type] = floatval($_POST[$key]);
  }
}

try {
  $pdo->beginTransaction();

  // 更新 products
  $stmt = $pdo->prepare("UPDATE products SET brand_id=?, category_id=?, series_id=?, name=?, sku=?, short_desc=?, stock_quantity=?, status=?, updated_at=NOW() WHERE id=?");
  $stmt->execute([
    $brand_id, $category_id, $series_id,
    $name, $sku, $short_desc, $stock_quantity,
    $status, $id
  ]);

  // 刪除舊價格
  $stmt = $pdo->prepare("DELETE FROM prices WHERE product_id = ?");
  $stmt->execute([$id]);

  // 插入新價格
  if (!empty($prices)) {
    $stmt = $pdo->prepare("INSERT INTO prices (product_id, price_type, price, start_at) VALUES (?, ?, ?, NOW())");
    foreach ($prices as $type => $value) {
      $stmt->execute([$id, $type, $value]);
    }
  }

  $pdo->commit();
  echo 'success';

} catch (Exception $e) {
  $pdo->rollBack();
  http_response_code(500);
  echo '更新失敗：' . $e->getMessage();
}