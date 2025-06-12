<?php
require_once __DIR__ . '/../db.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
$pdo = connect();

$id = $_POST['id'] ?? 0;
file_put_contents(__DIR__ . '/log_debug.txt', print_r($_POST, true) . "\n\n" . print_r($_FILES, true));
if (!$id) {
  http_response_code(400);
  echo 'Missing product ID';
  exit;
}

// 基本欄位
$brand_id       = isset($_POST['brand_id']) && $_POST['brand_id'] !== '' ? $_POST['brand_id'] : null;
$category_id    = $_POST['category_id'] ?? null;
$series_id      = ($_POST['series_id'] ?? '') !== '' ? $_POST['series_id'] : null;
$name           = $_POST['name'] ?? '';
$sku            = $_POST['sku'] ?? '';
$short_desc     = $_POST['short_desc'] ?? '';
$stock_quantity = $_POST['stock_quantity'] ?? 0;
$status         = ($_POST['status'] ?? '') === 'active' ? 'active' : 'inactive';

// 價格處理
$price_types = ['msrp', 'vip', 'vvip', 'wholesale', 'cost', 'emma'];
$prices = [];
foreach ($price_types as $type) {
  $key = 'price_' . $type;
  if (isset($_POST[$key]) && $_POST[$key] !== '') {
    $prices[$type] = floatval($_POST[$key]);
  }
}

// 處理封面圖（如有上傳）
$cover_sql_fragment = '';
$cover_sql_params = [];
if (!empty($_FILES['cover_img']['name'])) {
  $ext  = strtolower(pathinfo($_FILES['cover_img']['name'], PATHINFO_EXTENSION));
  $file = uniqid('cover_') . '.' . $ext;
  $destFolder = __DIR__ . '/../uploads/products_cover/';
  if (!is_dir($destFolder)) mkdir($destFolder, 0777, true);
  $dest = $destFolder . $file;

  if (move_uploaded_file($_FILES['cover_img']['tmp_name'], $dest)) {
    $cover_path = 'uploads/products_cover/' . $file;
    $cover_sql_fragment = ", cover_img = ?";
    $cover_sql_params[] = $cover_path;
  }
}

try {
  $pdo->beginTransaction();

  // 更新商品主檔
  $sql = "UPDATE products 
          SET brand_id=?, category_id=?, series_id=?, name=?, sku=?, short_desc=?, stock_quantity=?, status=?, updated_at=NOW()
          $cover_sql_fragment
          WHERE id=?";
  $params = [
    $brand_id, $category_id, $series_id,
    $name, $sku, $short_desc, $stock_quantity, $status
  ];
  $params = array_merge($params, $cover_sql_params, [$id]);
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);

  // 僅當價格變動時，才新增新紀錄
    foreach ($prices as $type => $value) {
      // 查詢目前的最新價格
      $stmt = $pdo->prepare("SELECT price FROM prices 
                            WHERE product_id = ? AND price_type = ? AND is_latest = 1 
                            ORDER BY start_at DESC LIMIT 1");
      $stmt->execute([$id, $type]);
      $current_price = $stmt->fetchColumn();

      if ($current_price === false || floatval($current_price) !== floatval($value)) {
        // ✅ 強制結束所有尚未結束的紀錄，保證唯一性
        $stmt = $pdo->prepare("UPDATE prices 
                              SET is_latest = 0, end_at = NOW() 
                              WHERE product_id = ? AND price_type = ? AND end_at IS NULL");
        $stmt->execute([$id, $type]);

        // 插入新紀錄
        $stmt = $pdo->prepare("INSERT INTO prices 
          (product_id, price_type, price, start_at, is_latest, end_at)
          VALUES (?, ?, ?, NOW(), 1, NULL)");
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