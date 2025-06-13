<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

header('Content-Type: application/json');

// 上傳檢查
if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
  echo json_encode(['success' => false, 'message' => '請選擇要匯入的 CSV 檔案']);
  exit;
}

// 嘗試讀取 CSV
$file = $_FILES['csv_file']['tmp_name'];
$handle = fopen($file, 'r');
if (!$handle) {
  echo json_encode(['success' => false, 'message' => '無法讀取上傳的檔案']);
  exit;
}

// 轉碼函式
function safe_utf8($text) {
  return mb_convert_encoding($text, 'UTF-8', 'UTF-8, BIG-5, ISO-8859-1');
}

$headers = fgetcsv($handle);
$rowCount = 0;
$errors = [];

while (($row = fgetcsv($handle)) !== false) {
  $rowCount++;
  $row = array_map('trim', array_map('safe_utf8', $row));

  [$brandName, $categoryName, $seriesName, $name, $sku,
   $short_desc, $unit, $barcode, $stock,
   $msrp, $vip, $vvip, $wholesale, $cost, $emma] = array_pad($row, 15, '');

  // 跳過空 SKU
  if ($sku === '') {
    $errors[] = "第 $rowCount 行：SKU 為空，略過";
    continue;
  }

  // 取得品牌
  $brandStmt = $pdo->prepare("SELECT id FROM brands WHERE name = ?");
  $brandStmt->execute([$brandName]);
  $brand = $brandStmt->fetch();
  if (!$brand) {
    $errors[] = "第 $rowCount 行：找不到品牌「$brandName」";
    continue;
  }
  $brand_id = $brand['id'];

  // 取得分類
  $catStmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
  $catStmt->execute([$categoryName]);
  $cat = $catStmt->fetch();
  if (!$cat) {
    $errors[] = "第 $rowCount 行：找不到分類「$categoryName」";
    continue;
  }
  $category_id = $cat['id'];

  // 查找系列（只依 brand 查，不連動 category）
  $series_id = null;
  if ($seriesName !== '') {
    $seriesStmt = $pdo->prepare("SELECT id FROM series WHERE name = ? AND brand_id = ?");
    $seriesStmt->execute([$seriesName, $brand_id]);
    $series = $seriesStmt->fetch();
    if (!$series) {
      $errors[] = "第 $rowCount 行：找不到系列「$seriesName」（品牌：$brandName）";
      continue;
    }
    $series_id = $series['id'];
  }

  // 檢查是否已有該商品
  $productStmt = $pdo->prepare("SELECT * FROM products WHERE brand_id = ? AND name = ? AND sku = ?");
  $productStmt->execute([$brand_id, $name, $sku]);
  $existing = $productStmt->fetch(PDO::FETCH_ASSOC);

  if ($existing) {
    // 更新基本欄位
    $updateStmt = $pdo->prepare("UPDATE products SET category_id=?, series_id=?, short_desc=?, unit=?, barcode=?, stock_quantity=?, updated_at=NOW() WHERE id=?");
    $updateStmt->execute([
      $category_id, $series_id, $short_desc, $unit, $barcode, $stock ?: 0, $existing['id']
    ]);
    $product_id = $existing['id'];
  } else {
    // 新增商品
    $insert = $pdo->prepare("INSERT INTO products
      (brand_id, category_id, series_id, name, sku, model, short_desc,
       stock_quantity, unit, barcode, cover_img, status, created_at, updated_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'uploads/product_images/no-image.png', 'active', NOW(), NOW())");
    $insert->execute([
      $brand_id, $category_id, $series_id,
      $name, $sku, $name, $short_desc,
      $stock ?: 0, $unit, $barcode
    ]);
    $product_id = $pdo->lastInsertId();
  }

  // 價格欄位處理（只新增變更過的價格）
  $priceMap = [
    'msrp'      => $msrp,
    'vip'       => $vip,
    'vvip'      => $vvip,
    'wholesale' => $wholesale,
    'cost'      => $cost,
    'emma'      => $emma,
  ];
  foreach ($priceMap as $type => $val) {
    if ($val === '') continue;
    $val = floatval($val);

    $checkStmt = $pdo->prepare("SELECT id, price FROM prices WHERE product_id = ? AND price_type = ? AND is_latest = 1");
    $checkStmt->execute([$product_id, $type]);
    $old = $checkStmt->fetch();

    if ($old && floatval($old['price']) === $val) continue;

    if ($old) {
      $pdo->prepare("UPDATE prices SET is_latest = 0, end_at = NOW() WHERE id = ?")->execute([$old['id']]);
    }

    $pdo->prepare("INSERT INTO prices (product_id, price_type, price, start_at, is_latest)
                   VALUES (?, ?, ?, NOW(), 1)")
        ->execute([$product_id, $type, $val]);
  }
}

fclose($handle);

// 回傳結果
if (count($errors)) {
  echo json_encode(['success' => false, 'message' => '部分資料匯入失敗', 'errors' => $errors]);
} else {
  echo json_encode(['success' => true, 'message' => '匯入成功']);
}