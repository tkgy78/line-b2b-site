<?php
// products_admin/update.php

require_once __DIR__ . '/../db.php';
$pdo = connect();

// 1. 驗證 & 取值
//    因為是編輯，所以一定要有 id
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
  header('Location: index.php');
  exit;
}
$productId   = intval($_POST['id']);
$brand_id    = isset($_POST['brand_id'])    ? intval($_POST['brand_id'])    : null;
$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
$series_id   = isset($_POST['series_id'])   && $_POST['series_id'] !== '' 
               ? intval($_POST['series_id']) 
               : null;
$sku         = trim($_POST['sku'] ?? '');
$name        = trim($_POST['name'] ?? '');
$stock_qty   = isset($_POST['stock_quantity']) 
               ? intval($_POST['stock_quantity']) 
               : 0;
$status      = isset($_POST['status']) ? 1 : 0;
$short_desc  = trim($_POST['short_desc'] ?? '');

// 2. 處理上傳 Cover Image（如果有）
$coverPath = $product_cover = '';
// 先把舊的 cover_img 拿出來，以防沒有上傳新圖時要保留
$oldStmt = $pdo->prepare("SELECT cover_img FROM products WHERE id = :id");
$oldStmt->execute([':id' => $productId]);
$row = $oldStmt->fetch(PDO::FETCH_ASSOC);
$oldCover = $row['cover_img'] ?? 'uploads/product_files/no-image.png';

// 如果使用者有上傳新圖，則存新圖並覆蓋路徑；否則保留舊路徑
if (!empty($_FILES['cover_img']['name']) && 
    is_uploaded_file($_FILES['cover_img']['tmp_name'])) {

  // 產生一個唯一檔名，放到 uploads/product_files/ 底下
  $ext = pathinfo($_FILES['cover_img']['name'], PATHINFO_EXTENSION);
  $fn  = uniqid('p_') . '.' . $ext;
  $dest = __DIR__ . '/uploads/product_files/' . $fn;

  if (move_uploaded_file($_FILES['cover_img']['tmp_name'], $dest)) {
    $coverPath = 'uploads/product_files/' . $fn;
    // （不強制刪舊檔，可自行考慮要不要刪除舊圖檔）
  } else {
    // 若上傳失敗，保持舊路徑
    $coverPath = $oldCover;
  }
} else {
  // 沒有上傳新圖
  $coverPath = $oldCover;
}

// 3. 更新 products table
$sql = "
  UPDATE products 
  SET brand_id = :brand_id,
      category_id = :category_id,
      series_id = :series_id,
      sku = :sku,
      name = :name,
      short_desc = :short_desc,
      cover_img = :cover_img,
      stock_quantity = :stock_qty,
      status = :status,
      updated_at = NOW()
  WHERE id = :id
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  ':brand_id'    => $brand_id,
  ':category_id' => $category_id,
  ':series_id'   => $series_id,
  ':sku'         => $sku,
  ':name'        => $name,
  ':short_desc'  => $short_desc,
  ':cover_img'   => $coverPath,
  ':stock_qty'   => $stock_qty,
  ':status'      => $status,
  ':id'          => $productId,
]);

// 4. 更新價格（這裡示範最簡單的做法：
//    先把所有該產品的舊 prices 刪除掉，再重插一次）
$pdo->beginTransaction();
try {
  $delStmt = $pdo->prepare("DELETE FROM prices WHERE product_id = :pid");
  $delStmt->execute([':pid' => $productId]);

  // 接著一樣像新增時那樣：只有「有輸入」才插入
  $price_fields = [
    'price_msrp'      => 'msrp',
    'price_vip'       => 'vip',
    'price_vvip'      => 'vvip',
    'price_wholesale' => 'wholesale',
    'price_cost'      => 'cost'
  ];
  $priceSql = "INSERT INTO prices 
               (product_id, price_type, price, start_at) 
               VALUES (:pid, :ptype, :price, NOW())";
  $pstmt = $pdo->prepare($priceSql);

  foreach ($price_fields as $field => $type) {
    if (isset($_POST[$field]) && $_POST[$field] !== '') {
      $pstmt->execute([
        ':pid'    => $productId,
        ':ptype'  => $type,
        ':price'  => floatval($_POST[$field]),
      ]);
    }
  }

  $pdo->commit();
} catch (Exception $e) {
  $pdo->rollBack();
  die("價格更新失敗：" . $e->getMessage());
}

// 5. 更新完成後，帶訊息回到商品列表
header('Location: index.php?msg=' . urlencode('商品編輯成功'));
exit;